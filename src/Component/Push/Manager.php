<?php

namespace GSS\Component\Push;

use OneSignal\OneSignal;

/**
 * Class Manager
 * This class handles Goggle and Mozilla Pushnotifications.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class Manager
{
    /**
     * @var OneSignal
     */
    private $api;

    /**
     * Manager constructor.
     *
     * @author Soner Sayakci <***REMOVED***>
     *
     * @param OneSignal $api
     */
    public function __construct(
        OneSignal $api
    ) {
        $this->api = $api;
    }

    /**
     * Send Message to Users.
     *
     * @param $userIds
     * @param $title
     * @param $message
     * @param string $url
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function sendMessage($userIds, $title, $message, $url = 'https://gameserver-sponsor.me')
    {
        if (!\is_array($userIds)) {
            $userIds = [$userIds];
        }

        $filters = [];

        foreach ($userIds as $userId) {
            $filters[] = [
                'field' => 'tag',
                'key' => 'id',
                'relation' => '=',
                'value' => $userId,
            ];
            $filters[] = [
                'operator' => 'OR',
            ];
        }

        unset($filters[\count($filters) - 1]);

        $this->api->notifications->add([
            'headings' => [
                'en' => $title,
            ],
            'contents' => [
                'en' => $message,
            ],
            'included_segments' => ['All'],
            'filters' => $filters,
            'url' => $url,
        ]);

        return true;
    }
}
