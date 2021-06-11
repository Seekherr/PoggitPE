<?php

declare(strict_types=1);

namespace seeker\poggitpe;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use seeker\poggitpe\commands\BlacklistedCommand;
use seeker\poggitpe\commands\PoggitCommand;
use seeker\poggitpe\commands\RequestsCommand;
/*use seeker\poggitpe\example\EventListener;
use seeker\poggitpe\example\ExternalUseExample;*/
use seeker\poggitpe\tasks\ResetRequestsTask;

class PoggitPE extends PluginBase
{

    /** @var Config */
    private $settings;

    /** @var Config */
    private $blacklisted;

    private $requests = [];

    public $files = [];

    /** @var bool */
    private $serverCd = false;

    /** @var string[] */
    private $cooldown = [];

    private static $instance;

    public function onEnable() : void
    {
        self::$instance = $this;
        /*$example = new ExternalUseExample();
        $example->fetchStatus();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);*/
        $this->saveResource('settings.yml');
        //i dearly apologize to all the developers i am hurting by doing this, unfortunately, this was a speedrun and i didn't have time to use sql so config abuse go BRRRRRRRR
        $this->saveResource('blacklisted.json');
        $this->settings = new Config($this->getDataFolder() . 'settings.yml', Config::YAML);
        //i apologize again :(, this hurts me too
        $this->blacklisted = new Config($this->getDataFolder() . 'blacklisted.json', Config::JSON);
        $this->getServer()->getCommandMap()->register("poggit", new PoggitCommand("poggit", $this));
        $this->getServer()->getCommandMap()->register("poggit", new BlacklistedCommand("poggitblacklist", $this));
        $this->getServer()->getCommandMap()->register("poggit", new RequestsCommand("poggitrequests", $this));
        $this->getScheduler()->scheduleRepeatingTask(new ResetRequestsTask($this), 3600 * 20);
    }

    public static function getInstance() : self
    {
        return self::$instance;
    }

    /**
     * @return Config
     */
    public function getSettings() : Config
    {
        return $this->settings;
    }

    /**
     * @return Config
     */
    public function getBlacklisted() : Config
    {
        return $this->blacklisted;
    }

    /**
     * @return array
     */
    public function getRequests() : array
    {
        return $this->requests;
    }

    public function resetRequests() : void
    {
        unset($this->requests);
        $this->requests = [];
    }

    /**
     * @param string $player
     * @return int|null
     */
    public function getRequestsByPlayer(string $player) : ?int
    {
        if(!isset($this->requests[$player])) return null;
        return (int) $this->requests[$player];
    }

    /**
     * @param string $player
     */
    public function incrementRequests(string $player) : void
    {
        if(!isset($this->requests[$player])) $this->requests[$player] = 0;
        (int) ++$this->requests[$player];
    }

    /**
     * @return bool
     */
    public function isServerUnderCooldown() : bool
    {
        return $this->serverCd;
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isCooldownActive(string $player) : bool
    {
        return isset($this->cooldown[$player]);
    }

    /**
     * @param string $player
     * @return bool
     */
    public function isBlacklisted(string $player) : bool
    {
        return (bool) $this->blacklisted->get($player);
    }

    /**
     * @param string $player
     */
    public function blacklist(string $player) : void
    {
        if($this->isBlacklisted($player)) return;
        $this->blacklisted->set($player, $this->getRequestsByPlayer($player));
        $this->blacklisted->save();
    }

    /**
     * @param string $player
     */
    public function removeFromBlacklist(string $player) : void
    {
        if(!$this->isBlacklisted($player)) return;
        $this->blacklisted->remove($player);
        $this->blacklisted->save();
    }

    public function setServerOnCooldown() : void
    {
        $this->serverCd = true;
    }

    public function removeServerFromCooldown() : void
    {
        $this->serverCd = false;
    }

    /**
     * @param string $player
     */
    public function addPlayerToCooldown(string $player) : void
    {
        if($this->isCooldownActive($player)) return;
        $this->cooldown[$player] = true; //lazy lolololol ;D
    }

    /**
     * @param string $player
     */
    public function removePlayerFromCooldown(string $player) : void
    {
        if(!$this->isCooldownActive($player)) return;
        unset($this->cooldown[$player]);
    }
}