<?php

declare(strict_types=1);

namespace seeker\poggitpe\tasks;

use pocketmine\scheduler\Task;
use seeker\poggitpe\PoggitPE;

class ResetRequestsTask extends Task
{

    /** @var PoggitPE */
    private $poggit;

    public function __construct(PoggitPE $poggit)
    {
        $this->poggit = $poggit;
    }

    public function onRun(int $currentTick)
    {
        $this->poggit->resetRequests();
    }
}