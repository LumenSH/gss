<?php

namespace GSS\Component\Reader;

class CfgReader
{
    protected $seperator = ' ';
    protected $lineTemplate = "%s %s\n";
    private $data = [];

    public function __construct($string)
    {
        /*
         * Split by newLine
         */
        $tmpData = \explode("\n", $string);

        /*
         * Remove whitespaces or empty lines
         */
        foreach ($tmpData as $key => $line) {
            $tmpData[$key] = \trim($line);
            if (empty($line)) {
                unset($tmpData[$key]);
            }
        }

        foreach ($tmpData as $line) {
            $lineSplit = \explode($this->seperator, $line);

            /*
             * Detect invalid field
             */
            if (\count($lineSplit) < 1) {
                continue;
            }

            $key = $lineSplit[0];
            unset($lineSplit[0]);

            $this->data[$key] = \trim(\implode(' ', $lineSplit));
        }
    }

    public function get($key)
    {
        return !empty($this->data[$key]) ? $this->data[$key] : null;
    }

    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return true;
    }

    public function toString()
    {
        $output = '';

        foreach ($this->data as $key => $val) {
            $output .= \sprintf($this->lineTemplate, $key, $val);
        }

        return $output;
    }
}
