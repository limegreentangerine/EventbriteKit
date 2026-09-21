<?php defined('C5_EXECUTE') or die('Access Denied.');
if (in_array($controller->getTask(), ['add', 'edit', 'details', 'save'])) {
    View::element(
        'dashboard/events/details',
        [
            'c' => $c,
            'token' => $token,
            'form' => $form,
            'entity' => $entity ?? null,
            'v' => [
                'id' => (isset($entity)) ? $entity->getID() : ((isset($formContent) && isset($formContent['id'])) ? $formContent['id'] : ''),
                'eventbriteId' => (isset($entity)) ? $entity->getEventbriteId() : ((isset($formContent) && isset($formContent['eventbriteId'])) ? $formContent['eventbriteId'] : ''),
                'name' => (isset($entity)) ? $entity->getName() : ((isset($formContent) && isset($formContent['name'])) ? $formContent['name'] : ''),
                'venue' => (isset($entity)) ? $entity->getVenue() : ((isset($formContent) && isset($formContent['venue'])) ? $formContent['venue'] : ''),
                'url' => (isset($entity)) ? $entity->getUrl() : ((isset($formContent) && isset($formContent['url'])) ? $formContent['url'] : ''),
                'startDate' => (isset($entity)) ? $entity->getStartDateFormatted('l jS F Y, H:i') : ((isset($formContent) && isset($formContent['startDate'])) ? $formContent['startDate'] : ''),
                'endDate' => (isset($entity)) ? $entity->getEndDateFormatted('l jS F Y, H:i') : ((isset($formContent) && isset($formContent['endDate'])) ? $formContent['endDate'] : ''),
                'description' => (isset($entity)) ? $entity->getDescription() : ((isset($formContent) && isset($formContent['description'])) ? $formContent['description'] : ''),
                'image' => (isset($entity)) ? $entity->getImage() : ((isset($formContent) && isset($formContent['image'])) ? $formContent['image'] : ''),
            ],
        ],
        'eventbrite',
    );
} else {
    View::element(
        'dashboard/entity/list',
        [
            'token' => $token,
            'params' => $params,
            'items' => $items,
            'result' => $result,
            'pagination' => $pagination,
        ],
    );
}
