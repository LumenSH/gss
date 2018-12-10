<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * @throws Exception
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function login()
    {
        $this->amOnPage('/');
        $this->click('a[data-target="#modalLogin"]');
        $this->waitForElement('.modal-login');
        $this->wait(0.5);
        $this->fillField('_username', 'Codeception');
        $this->fillField('_password', '123456a');
        $this->click('.modal .btn-success');
        $this->wait(1);
    }

    /**
     * @param string $menuName
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function openMenuItem(string $menuName)
    {
        $this->click('a[data-menu="true"]');
        $this->wait(0.5);
        $this->click($menuName);
    }
}
