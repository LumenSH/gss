<?php

/**
 * Class ShopCest
 */
class ShopCest
{
    /**
     * @param AcceptanceTester $I
     * @throws Exception
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function buyPassive(AcceptanceTester $I)
    {
        $I->login();

        $I->openMenuItem('Shop');

        $I->click('Passive Gameservers');

        $I->click('Create Multi Theft Auto: San Andreas server now');

        $I->click('Create now');

        $I->canSeeInCurrentUrl('server');

        $I->waitForElementVisible('#gsOffline', 100);
        $I->click('a[data-start-server="true"]');

        $I->waitForElementVisible('#gsOnline', 100);

        $I->click('a[data-stop-server="true"]');

        $I->waitForElementVisible('#gsOffline', 100);

        $I->click('a[data-target="#modalDelete"]');

        $I->waitForElementVisible('#modalDelete');

        $I->click('Delete');
    }
}
