<?php

declare(strict_types=1);

namespace seeker\poggitpe\example;

use seeker\poggitpe\RequestsOverloadEvent;

class EventListener implements \pocketmine\event\Listener
{
    public function onRequest(RequestsOverloadEvent $event) : void
    {
        $poggit = $event->getPoggitPE(); //PoggitPE instance
        $user = $event->getUser(); //string
        echo "Hi! This is an event for example! " . $poggit->getRequestsByPlayer($user);
    }
}