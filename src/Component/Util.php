<?php

namespace GSS\Component;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class Util.
 */
class Util
{
    /**
     * @var string
     */
    private $publicDir;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        $publicDir,
        Connection $connection,
        SessionInterface $session
    ) {
        $this->publicDir = $publicDir;
        $this->connection = $connection;
        $this->session = $session;
    }

    /**
     * Is TCP Server Online.
     *
     * @param $IP
     * @param $Port
     * @param int $timedOut
     *
     * @return bool
     */
    public static function isServerOnline($IP, $Port, $timedOut = 5)
    {
        return (@\fsockopen($IP, $Port, $timedOut) != false) ? true : false;
    }

    /**
     * Get Date MySQL Today.
     *
     * @return bool|string
     */
    public static function getDateMySQLToday()
    {
        return \date('Y-m-d');
    }

    /**
     * Get MySQL Password.
     *
     * @param $password
     *
     * @return string
     */
    public static function getMySQLPassword($password)
    {
        return '*' . \strtoupper(\hash('sha1', \pack('H*', \hash('sha1', $password))));
    }

    /**
     * Is Image.
     *
     * @param $file
     *
     * @return bool
     */
    public function isImage($file)
    {
        $data = \getimagesize($file);
        $image_type = $data[2];

        if (\in_array($image_type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return true;
        }

        return false;
    }

    /**
     * Create Thumbnail.
     *
     * @param $tempPath
     * @param $options
     *
     * @return bool|string
     */
    public function createThumbnail($tempPath, $options)
    {
        if (\preg_match('/[.](jpg)$/', $tempPath)) {
            $source = \imagecreatefromjpeg($tempPath);
        } elseif (\preg_match('/[.](gif)$/', $tempPath)) {
            $source = \imagecreatefromgif($tempPath);
        } elseif (\preg_match('/[.](png)$/', $tempPath)) {
            $source = \imagecreatefrompng($tempPath);
        } else {
            return false;
        }

        $x = $options['x'];
        $y = $options['y'];

        $destImg = \imagecreatetruecolor($options['width'], $options['height']);
        \imagecopyresized($destImg, $source, 0, 0, $x, $y, $options['width'], $options['height'], $options['width'], $options['height']);

        $img2x = \imagescale($destImg, 200, 200);

        $id = \uniqid();

        \imagepng($img2x, $this->publicDir . '/uploads/avatar/' . $id . '.png');
        \imagedestroy($destImg);
        \imagedestroy($source);

        return $id . '.png';
    }

    /**
     * Delete Avatar.
     *
     * @param $id
     */
    public function deleteAvatar($id)
    {
        @\unlink($this->publicDir . '/uploads/avatar/' . $id);
    }

    /**
     * Gets SQL Offset.
     *
     * @param $page
     * @param $limit
     *
     * @return int
     */
    public static function getSqlOffset($page, $limit)
    {
        if ($page == 1) {
            return 0;
        }

        return $limit * ($page - 1);
    }

    /**
     * Truncates the string.
     *
     * @param $string
     * @param int $length
     *
     * @return string
     */
    public function truncate($string, $length = 20)
    {
        if (\strlen($string) >= $length) {
            return \substr($string, 0, $length - 3) . '...';
        }

        return $string;
    }

    /**
     * @param $hostId
     *
     * @return bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    public function hasUnixAccount($hostId)
    {
        return (bool) $this->connection->fetchColumn('SELECT COUNT(*) FROM users_to_gameroot WHERE userID = ? AND hostID = ?', [
            $this->session->getUserID(),
            $hostId,
        ]);
    }
}
