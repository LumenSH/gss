<?php

namespace GSS\Component\Reader;

class McReader extends CfgReader
{
    protected $lineTemplate = "%s=%s\n";
    protected $seperator = '=';
}
