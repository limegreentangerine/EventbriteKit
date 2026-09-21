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

    public function __construct()
    {
        $this->pkg = Package::getByHandle('eventbrite');
        $this->rf = Core::make(\Concrete\Core\Http\ResponseFactoryInterface::class);
        $this->config = $this->pkg->getFileConfig();
        $this->logger = Core::make(\Eventbrite\Log\EventbriteLogger::class)->getLogger();
        $this->rf = Core::make(\Concrete\Core\Http\ResponseFactoryInterface::class);

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

    private function respond(\ClassKit\Api\Response\Response $response)
    {
        $body = json_decode($response->getContent(), true);

        return $response->getStatusCode() === 200
            ? $this->rf->json(['success' => true, 'data' => $body])
            : $this->rf->json(['success' => false, 'message' => $body]);
    }

    public function getMe(array $params = [])
    {
        return $this->respond($this->request('/users/me', $params));
    }

    public function getEvents(array $params = [])
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

    public function getOrganization(array $params = [])
    {
        return $this->respond($this->request('/users/me/organizations', $params));
    }
}
