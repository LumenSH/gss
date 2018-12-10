<?php

namespace GSS\Component\Structs;

/**
 * Class GameserverStats
 *
 * @author Soner Sayakci <***REMOVED***>
 */
class GameserverStats implements \JsonSerializable
{
    private $data = [];

    /**
     * GameserverStats constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCpuUsage()
    {
        return $this->data['usage']['cpu'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getMemoryUsage()
    {
        return $this->data['usage']['mem'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getPid()
    {
        return $this->data['pid'];
    }

    /**
     * @return int
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getStartedAt()
    {
        return $this->data['started_at'];
    }

    /**
     * @return array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function jsonSerialize()
    {
        return [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
        ];
    }
}
