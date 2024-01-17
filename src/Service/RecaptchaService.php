<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaService
{
    public function __construct(
        private HttpClientInterface $http,
        private ParameterBagInterface $config
    )
    {
    }

    public function validateResponse(Request $request): array
    {
        $secret = $this->config->get('recaptcha.secret_key');

        if (!$secret) {
            return ['success' => true];
        }

        return $this->http->request(
            'POST',
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'body' => [
                    'secret' => $secret,
                    'response' => $request->get('g-recaptcha-response'),
                    'remoteip' => $request->getClientIp(),
                ],
            ],
        )->toArray();
    }
}
