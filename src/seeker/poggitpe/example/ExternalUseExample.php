<?php

declare(strict_types=1);

namespace seeker\poggitpe\example;

use seeker\poggitpe\PoggitPE;

class ExternalUseExample
{

    public function fetchStatus() : void
    {
        var_dump(PoggitPE::getInstance()->isServerUnderCooldown()); //external use example
    }
}