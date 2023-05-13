<?php

namespace SmashBalloon\Reviews\Pro\Integrations\Providers;
use SmashBalloon\Reviews\Common\Integrations\Providers\BaseProvider;

class Facebook extends BaseProvider
{
    protected $name = 'facebook';
    protected $uses_connect = true;
    protected $friendly_name = 'Facebook';
}