<?php

namespace TPCraft\BsSocialiteProviderTPCraftOpenID;

use SocialiteProviders\Manager\SocialiteWasCalled;

class TPCraftOpenIDExtendSocialite
{
    /**
     * Execute the provider.
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite("tpcraft-openid", __NAMESPACE__ . '\Provider');
    }
}
