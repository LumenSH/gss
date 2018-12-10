<?php

namespace GSS\Component\HttpKernel;

/**
 * Class Request.
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * @var bool
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $sanitazed = false;

    /**
     * @var array
     *
     * @author Soner Sayakci <***REMOVED***>
     */
    private $sanitazedArray = [];

    /**
     * @param null $postName
     * @param null $default
     *
     * @return null|string
     */
    public function getPost($postName = null, $default = null)
    {
        if (empty($this->sanitazed)) {
            $this->sanitazedArray = $_POST;
            \array_walk_recursive($this->sanitazedArray, [$this, 'stripString']);
            $this->sanitazed = true;
        }

        if ($postName) {
            if (empty($this->sanitazedArray[$postName])) {
                if (isset($this->sanitazedArray[$postName]) && $this->sanitazedArray[$postName] == 0) {
                    return 0;
                }

                return $default;
            } elseif (\is_array($this->sanitazedArray[$postName])) {
                return $this->sanitazedArray[$postName];
            }

            return empty($this->sanitazedArray[$postName]) ? $default : $this->sanitazedArray[$postName];
        }

        return $this->sanitazedArray;
    }

    public function getPostHtml($post, $default = '')
    {
        if (empty($_POST[$post])) {
            return $default;
        }

        return $this->xss_clean($_POST[$post]);
    }

    public function stripString(&$string)
    {
        $string = \strip_tags($string);
    }

    /**
     * Gets the Raw POST.
     *
     * @return mixed
     */
    public function getAjaxPost()
    {
        return \json_decode(\file_get_contents('php://input'), true);
    }

    /**
     * Getter for $_GET.
     *
     * @param $name
     * @param null $default
     *
     * @return mixed|string
     */
    public function get($name, $default = null)
    {
        return $this->decodeValue(empty($_GET[$name]) ? $default : $_GET[$name]);
    }

    /**
     * Is Get.
     *
     * @return bool
     */
    public function isGet()
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Is Post.
     *
     * @return bool
     */
    public function isPost()
    {
        return $this->getMethod() === 'POST';
    }

    /**
     * Is Put.
     *
     * @return bool
     */
    public function isPut()
    {
        return $this->getMethod() === 'PUT';
    }

    /**
     * Is Delete.
     *
     * @return bool
     */
    public function isDelete()
    {
        return $this->getMethod() === 'DELETE';
    }

    public function xss_clean($data)
    {
        // Fix &entity\n;
        $data = \str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = \preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = \preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = \html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = \preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = \preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = \preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = \preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = \preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = \preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = \preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = \preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = \preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }

    /**
     * Parameter Decodierung.
     *
     * @param $value
     *
     * @return mixed|string
     */
    private function decodeValue($value)
    {
        if (!\is_string($value)) {
            return $value;
        }
        $json = \json_decode($value, true);

        return \json_last_error() === JSON_ERROR_NONE ? $json : $value;
    }
}
