<?php

declare(strict_types=1);

namespace App\Tests\Support;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    private ?string $userToken = null;
    private const USER_EMAIL = 'cuckoo@gmail.com';
    private const USER_PASSWORD = 'TSshark1957work$';

    public function getUserToken(): string
    {
        if ($this->userToken === null) {
            $this->generateUserToken();
        }

        return $this->userToken;
    }

    private function generateUserToken(): void
    {
        $I = $this;
        $I->sendPOST('/api/v1/token', ['username' => self::USER_EMAIL, 'password' => self::USER_PASSWORD]);
        $response = $I->grabResponse();
        $responseArray = json_decode($response, true);
        $this->userToken = $responseArray['token'];
    }
}
