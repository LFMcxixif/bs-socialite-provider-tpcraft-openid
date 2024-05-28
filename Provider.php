<?php

namespace TPCraft\BsSocialiteProviderTPCraftOpenID;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = "TPCRAFT_OPENID";

    /**
     * {@inheritdoc}
     */
    protected $scopes = ["openid", "profile", "email"];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase("https://openid.tpcraft.net/oauth2/authorize", $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return "https://openid.tpcraft.net/oauth2/token";
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get("https://openid.tpcraft.net/userinfo", [
            "headers" => [
                "Authorization" => "Bearer " . $token,
            ],
        ]);

        $user = json_decode($response->getBody(), true);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            "id" => null,
            "nickname" => $user["sub"],
            "name" => null,
            "email" => $user["email"],
            "avatar" => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            "grant_type" => "authorization_code"
        ]);
    }
}
