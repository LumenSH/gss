<?php

namespace GSS\Component\Session;

/**
 * Class FlashMessenger.
 */
class FlashMessenger
{
    private $session;

    /**
     * CodaFlashMessenger constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Adds a success message.
     *
     * @param $title
     * @param $message
     */
    public function addSuccess($title, $message)
    {
        $this->addMessage('success', $title, $message);
    }

    /**
     * Adds a Error Message.
     *
     * @param $title
     * @param $message
     */
    public function addError($title, $message)
    {
        $this->addMessage('error', $title, $message);
    }

    /**
     * Add a Warning message.
     *
     * @param $title
     * @param $message
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function addWarning($title, $message)
    {
        $this->addMessage('warning', $title, $message);
    }

    /**
     * Adds a Info message.
     *
     * @param $title
     * @param $message
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function addInfo($title, $message)
    {
        $this->addMessage('info', $title, $message);
    }

    /**
     * Adds a message.
     *
     * @param string $type
     * @param string $title
     * @param string $message
     */
    private function addMessage($type = 'success', $title = '', $message = '')
    {
        $this->session->getFlashBag()->add($type, $title . '|' . $message);
    }
}
