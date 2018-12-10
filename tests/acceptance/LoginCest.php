<?php

/**
 * Class LoginCest
 */
class LoginCest
{
    /**
     * @param AcceptanceTester $I
     * @throws Exception
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function loginTest(AcceptanceTester $I)
    {
        $I->login();
        $I->see('Codeception');
    }
}
