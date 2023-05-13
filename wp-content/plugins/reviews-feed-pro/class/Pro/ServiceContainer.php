<?php

namespace SmashBalloon\Reviews\Pro;

use SmashBalloon\Reviews\Common\Container;
use SmashBalloon\Reviews\Pro\Integrations\LicenseManagerService;
use SmashBalloon\Reviews\Pro\Services\ShortcodeService;
use SmashBalloon\Reviews\Pro\Integrations\Providers\Facebook;
use SmashBalloon\Reviews\Pro\Integrations\Providers\TripAdvisor;


class ServiceContainer extends \SmashBalloon\Reviews\Common\ServiceContainer
{

    public function register(): void
    {
        //Register pro services
        $this->services[] = LicenseManagerService::class;
        $this->services[] = ShortcodeService::class;
        $this->services[] = Facebook::class;
        $this->services[] = TripAdvisor::class;

        foreach ($this->services as $service) {
            Container::getInstance()->get($service)->register();
        }
    }
}