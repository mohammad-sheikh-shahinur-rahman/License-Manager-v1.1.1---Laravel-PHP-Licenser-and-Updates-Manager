<?php

namespace Botble\SocialLoginEnvato\Drivers;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class EnvatoProvider extends AbstractProvider
{
    protected const API_URL = 'https://api.envato.com';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(self::API_URL . '/authorization', $state);
    }

    protected function getTokenUrl(): string
    {
        return self::API_URL . '/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(self::API_URL . '/v1/market/private/user/account.json', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $token],
        ]);

        $response = json_decode((string) $response->getBody(), true)['account'];
        $response['email'] = $this->getEmailByToken($token);
        $response['username'] = $this->getUsernameByToken($token);

        return $response;
    }

    protected function mapUserToObject(array $user): User
    {
        $email = Arr::get($user, 'email');

        return (new User())->setRaw($user)->map([
            'id' => hash('xxh128', $email),
            'nickname' => Arr::get($user, 'username'),
            'name' => trim(Arr::get($user, 'firstname') . ' ' . Arr::get($user, 'surname')),
            'email' => $email,
            'avatar' => Arr::get($user, 'image'),
        ]);
    }

    protected function getEmailByToken(string $token): string
    {
        $response = $this->getHttpClient()->get(self::API_URL . '/v1/market/private/user/email.json', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $token],
        ]);

        return json_decode((string) $response->getBody(), true)['email'];
    }

    protected function getUsernameByToken(string $token): string
    {
        $response = $this->getHttpClient()->get(self::API_URL . '/v1/market/private/user/username.json', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $token],
        ]);

        return json_decode((string) $response->getBody(), true)['username'];
    }
}
