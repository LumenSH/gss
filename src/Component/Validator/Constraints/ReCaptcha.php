<?php

namespace GSS\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ReCaptcha extends Constraint
{
    public function validatedBy()
    {
        return \get_class($this) . 'Validator';
    }
}
