<?php

namespace GSS\Component\Structs;

class GameQResult
{
    /**
     * @var array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $data;

    /**
     * GameQResult constructor.
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
    public function getAddress()
    {
        return $this->data['gq_address'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function isOnline()
    {
        return $this->data['gq_online'];
    }

    /**
     * @return mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGame()
    {
        return $this->data['gq_name'];
    }

    /**
     * @return mixed|string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getHostname()
    {
        if (!empty($this->data['servername'])) {
            return $this->data['servername'];
        }

        if (!empty($this->data['gq_hostname'])) {
            return $this->data['gq_hostname'];
        }

        if (!empty($this->data['hostname'])) {
            return $this->data['hostname'];
        }

        return '';
    }

    /**
     * @return int|mixed
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getCurrentPlayers()
    {
        if (!empty($this->data['numplayers'])) {
            return (int) $this->data['numplayers'];
        }

        if (!empty($this->data['num_players'])) {
            return (int) $this->data['num_players'];
        }

        if (!empty($this->data['gq_num_players'])) {
            return (int) $this->data['gq_num_players'];
        }

        if (!empty($this->data['gq_numplayers'])) {
            return (int) $this->data['gq_numplayers'];
        }

        return 0;
    }

    /**
     * @return mixed|string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getMapName()
    {
        if (!empty($this->data['gq_mapname'])) {
            return $this->data['gq_mapname'];
        }

        if (!empty($this->data['map'])) {
            return $this->data['map'];
        }

        return '';
    }

    /**
     * @return mixed|string
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function getGameType()
    {
        if (!empty($this->data['gametype'])) {
            return $this->data['gametype'];
        }

        if (!empty($this->data['gq_gametype'])) {
            return $this->data['gq_gametype'];
        }

        return '';
    }
}
