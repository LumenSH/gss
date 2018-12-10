<?php

namespace GSS\Component\Structs;

/**
 * Class ServerStats
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class ServerStats
{
    /**
     * @var array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $data;

    /**
     * ServerStats constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getHostname()
    {
        return $this->data['hostname'];
    }

    /**
     * @param int $since
     *
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getLoadAvg($since = 0)
    {
        return $this->data['cpu']['loadavg'][$since];
    }

    /**
     * @return string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCpuModel(): string
    {
        return $this->data['cpu']['model'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCpuCount(): int
    {
        return $this->data['cpu']['cores'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getTotalMemory()
    {
        return $this->data['mem']['total'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getAvailableMemory()
    {
        return $this->data['mem']['available'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUsedMemory()
    {
        return $this->data['mem']['in_use'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getUptime()
    {
        return $this->data['uptime']['sys'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getServerOnlineCount()
    {
        return $this->data['servers'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getConnectedSocketsCount()
    {
        return $this->data['sockets'];
    }
}
