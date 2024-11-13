<?php

namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TokenRequestService
{
    private string $token;

    public function __construct(
        private readonly Security $security,
        private readonly AuthService $authService,
    ) {
        $this->token = $this->authService->getToken($this->security->getUser()->getUserIdentifier());
    }

    public function client(): HttpClientInterface
    {
        return HttpClient::create(['headers' => [
            'Authorization' => 'Bearer ' . $this->token,
        ]]);
    }
}
