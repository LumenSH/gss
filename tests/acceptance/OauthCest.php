<?php

/**
 * Class OauthCest
 */
class OauthCest
{
    /**
     * Redirection Testing
     * @param AcceptanceTester $I
     */
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/oauth?service=google');
        $I->seeInCurrentUrl('google');

        $I->amOnPage('/oauth?service=discord');
        $I->seeInPageSource('Discord');

        $I->amOnPage('/oauth?service=facebook');
        $I->seeInPageSource('facebook');
    }
}
