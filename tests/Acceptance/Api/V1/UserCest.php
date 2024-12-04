<?php

namespace Acceptance\Api\V1;

use App\Tests\Support\AcceptanceTester;
use Codeception\Util\HttpCode;

class UserCest
{
    private const BASE_URL = '/api/v1/';
    private const USER_EMAIL = 'puma@example.com';
    private const USER_PASSWORD = 'My_password1905';

    public function testAddUserAction(AcceptanceTester $I): void
    {
        $token = $I->getUserToken();
        $I->amBearerAuthenticated($token);

        $response = $I->sendPost(self::BASE_URL . 'user', [
            'name' => 'Puma',
            'email' => self::USER_EMAIL,
            'address' => 'Санкт-Петербург',
            'password' => self::USER_PASSWORD,
            'roles' => ["ROLE_USER", "ROLE_ADMIN"],
        ]);

        $I->canSeeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseMatchesJsonType(['userId' => 'integer:>0']);

        $responseArray = json_decode($response, true);
        $I->sendDelete(self::BASE_URL . "user/{$responseArray['userId']}");
    }
}
