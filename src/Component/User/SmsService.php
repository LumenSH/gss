<?php

namespace GSS\Component\User;

use Psr\Log\LoggerInterface;
use Twilio\Rest\Client;

/**
 * Class SmsService.
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class SmsService
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $number;

    /**
     * @var LoggerInterface
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $logger;

    /**
     * SmsService constructor.
     *
     * @param Client $client
     * @param string $number
     */
    public function __construct(
        Client $client,
        string $number,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->number = $number;
        $this->logger = $logger;
    }

    /**
     * @param string $number
     * @param string $message
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function sendMessage($number, $message)
    {
        try {
            $msgInstance = $this->client->messages->create($number, [
                'from' => $this->number,
                'body' => $message,
            ]);
        } catch (\Exception $e) {
            $msgInstance = new \stdClass();
            $msgInstance->errorCode = $e->getCode();
            $msgInstance->errorMessage = $e->getMessage();
        }

        $context = [
            'data' => $msgInstance,
            'context' => ['number' => $number, 'message' => $message],
        ];

        if (empty($msgInstance->errorCode)) {
            $this->logger->info('SMS send', $context);
        } else {
            $this->logger->critical('SMS couldn\'t send', $context);
        }

        return empty($msgInstance->errorCode);
    }
}
