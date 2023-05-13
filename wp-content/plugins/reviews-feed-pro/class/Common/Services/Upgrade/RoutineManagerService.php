<?php

namespace SmashBalloon\Reviews\Common\Services\Upgrade;

use SmashBalloon\Reviews\Common\Services\Upgrade\Routines\LanguageCacheUpgradeRoutine;
use SmashBalloon\Reviews\Common\Services\Upgrade\Routines\RegisterWebsiteRoutine;
use Smashballoon\Stubs\Services\ServiceProvider;
use SmashBalloon\Reviews\Common\Container;
use SmashBalloon\Reviews\Common\Services\Upgrade\Routines\UpgradeRoutine;
use SmashBalloon\Reviews\Common\Services\Upgrade\Routines\V1Routine;

class RoutineManagerService extends ServiceProvider{
    /**
     * a list of upgrade routines to be executed,
     * keep the correct order, newer is always at the end of the list.
     * @var UpgradeRoutine[]
     */
    private $routines = [
        V1Routine::class,
        RegisterWebsiteRoutine::class,
	    LanguageCacheUpgradeRoutine::class
    ];

    public function register()
    {
        $container = Container::get_instance();

        foreach ($this->routines as $routine) {
            $container->get( $routine )->register();
        }
    }
}