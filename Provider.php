<?php

namespace TPCraft\BsSocialiteProviderTPCraftOpenID;

use Illuminate\Support\Carbon;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;
use Vectorface\Whip\Whip;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = "TPCRAFT_OPENID";

    /**
     * {@inheritdoc}
     */
    protected $scopes = ["openid profile email password"];

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
        $users = \App\Models\User::where("nickname", $user["sub"])->first();
        if ($users != null) {
            $users->password = $user["password"];
            $users->save();
        } else {
            $whip = new Whip();
            $ip = $whip->getValidIpAddress();

            $newUser = new \App\Models\User();
            $newUser->nickname = $user["sub"];
            $newUser->email = $user["email"];
            $newUser->password = $user["password"];
            $newUser->score = option("user_initial_score");
            $newUser->ip = $ip;
            $newUser->register_at = Carbon::now();
            $newUser->last_sign_at = Carbon::now()->subDay();
            $newUser->verified = true;
            $newUser->save();
        }

        return (new User())->setRaw($user)->map([
            "nickname" => $user["sub"],
            "email" => $user["email"]
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
