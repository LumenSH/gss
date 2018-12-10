<?php

namespace GSS\Component\Validator\Constraints;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Tests\Fixtures\ConstraintAValidator;

class ReCaptchaValidator extends ConstraintAValidator
{
    public function validate($value, Constraint $constraint)
    {
        global $kernel;

        $request = Request::createFromGlobals();

        $url = 'https://www.google.com/recaptcha/api/siteverify?' . \http_build_query([
                'secret' => $kernel->getContainer()->getParameter('recaptcha'),
                'response' => $request->request->get('g-recaptcha-response'),
                'remoteip' => $request->getClientIp(),
            ]);

        $captcha = \json_decode(\file_get_contents($url), true);

        if ($captcha['success'] == false) {
            $this->context->addViolation('Recaptcha Error');
        }
    }
}
