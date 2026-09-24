<?php

namespace Eventbrite\Api;

use Core;
use Package;
use ClassKit\Api\Enum\RequestMethod;
use ClassKit\Api\ConnectionController;

class Eventbrite extends ConnectionController
{
    /**
     * @var Package
     */
    protected $pkg;
    /**
     * @var object
     */
    protected $config;
    /**
     * @var \Concrete\Core\Http\ResponseFactory
     */
    protected $rf;
    /**
     * @var \Eventbrite\Log\EventbriteLogger
     */
    protected $logger;

    /**
     * Configure the client from the package file config: base URL, JSON format and a bearer-token Authorization header.
     */
    public function __construct()
    {
        $this->pkg = Package::getByHandle('eventbrite');
        $this->rf = Core::make(\Concrete\Core\Http\ResponseFactoryInterface::class);
        $this->config = $this->pkg->getFileConfig();
        $this->logger = Core::make(\Eventbrite\Log\EventbriteLogger::class)->getLogger();

        parent::__construct(
            $this->config->get('eventbrite.base_url'),
            'json',
            ['Authorization' => 'Bearer ' . $this->config->get('eventbrite.api_key')],
        );
    }

    /**
     * GET an endpoint. Transport failures (timeouts, DNS) become error responses instead of exceptions.
     */
    private function request(string $endpoint, array $params): \ClassKit\Api\Response\Response
    {
        $url = count($params) > 0 ? sprintf('%s?%s', $endpoint, http_build_query($params)) : $endpoint;

        try {
            return $this->makeRequest(RequestMethod::GET->value, $url);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            return \ClassKit\Api\Response\ErrorResponse::fromType(
                $this->format,
                ['message' => $e->getMessage()],
                \ClassKit\Api\Response\Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    /**
     * Wrap an API response as `{success: true, data}` for HTTP 200, otherwise `{success: false, message}`.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    private function respond(\ClassKit\Api\Response\Response $response): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $body = json_decode($response->getContent(), true);

        return $response->getStatusCode() === 200
            ? $this->rf->json(['success' => true, 'data' => $body])
            : $this->rf->json(['success' => false, 'message' => $body]);
    }

    /**
     * Get the authenticated user (`/users/me`).
     *
     * @param array<string, mixed> $params Query-string parameters
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMe(array $params = []): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->respond($this->request('/users/me', $params));
    }

    /**
     * Get the first organisation's live events, with venue expanded, sorted by UTC start time.
     *
     * Returns `success: false` when the organisation lookup fails or finds no organisation.
     *
     * @param array<string, mixed> $params Extra query-string parameters; `expand=venue` is always added
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getEvents(array $params = []): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $organisation = json_decode($this->getOrganization()->getContent(), true);
        $organisationID = $organisation['data']['organizations'][0]['id'] ?? null;

        if (!($organisation['success'] ?? false) || $organisationID === null) {
            return $this->rf->json([
                'success' => false,
                'message' => $organisation['message'] ?? 'No Eventbrite organisation found for this account.',
            ]);
        }

        $params = array_merge($params, [
            'expand' => 'venue',
        ]);
        $response = $this->request(sprintf('/organizations/%s/events', $organisationID), $params);

        if ($response->getStatusCode() !== 200) {
            return $this->respond($response);
        }

        $body = json_decode($response->getContent(), true);
        $events = array_values(array_filter(
            $body['events'] ?? [],
            static fn($event) => ($event['status'] ?? null) === 'live',
        ));
        usort($events, static fn($a, $b) => strtotime($a['start']['utc']) <=> strtotime($b['start']['utc']));

        return $this->rf->json(['success' => true, 'data' => $events]);
    }

    /**
     * Get the organisations the authenticated user belongs to (`/users/me/organizations`).
     *
     * @param array<string, mixed> $params Query-string parameters
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOrganization(array $params = []): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return $this->respond($this->request('/users/me/organizations', $params));
    }
}
