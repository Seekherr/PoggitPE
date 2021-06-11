<?php

declare(strict_types=1);

namespace seeker\poggitpe;

use pocketmine\event\plugin\PluginEvent;

class RequestsOverloadEvent extends PluginEvent
{

    /** @var PoggitPE */
    private $poggit;

    /** @var string */
    private $user;

    public function __construct(PoggitPE $plugin, string $user)
    {
        parent::__construct($plugin);
        $this->poggit = $plugin;
        $this->user = $user;
    }

    public function getPoggitPE() : PoggitPE
    {
        return $this->poggit;
    }

    public function getUser() : string
    {
        return $this->user;
    }
}