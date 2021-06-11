<?php

namespace seeker\poggitpe\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use seeker\poggitpe\PoggitPE;
use seeker\poggitpe\RequestsOverloadEvent;
use seeker\poggitpe\tasks\GetInformationTask;
use seeker\poggitpe\tasks\InstallPluginTask;

class PoggitCommand extends PluginCommand
{

    /**
     * @var PoggitPE
     */
    private $poggit;

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->poggit = $owner;
        $this->setDescription("Make queries to Poggit!");
        if ($this->poggit->getSettings()->get('only-op') == false) $this->setPermission('poggit.command.pe');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($this->poggit->getSettings()->get('only-op') == true) if (!$sender->isOp()) return;
        if (count($args) > 2) {
            $sender->sendMessage(TextFormat::RED . 'Too many arguments!');
            $sender->sendMessage(TextFormat::GREEN . "Commands list: \n- /poggit [get/install/download] [pluginName]\n- /poggit [status/information/info [pluginName]");
            return;
        }
        if (!isset($args[0]) || !isset($args[1])) {
            $sender->sendMessage(TextFormat::RED . 'Required argument(s) not found!');
            $sender->sendMessage(TextFormat::GREEN . "Commands list: \n- /poggit [get/install/download] [pluginName]\n- /poggit [status/information/info [pluginName]");
            return;
        }
        switch ($args[0]) {
            case "get":
            case "install":
            case 'download':
                if($this->poggit->isBlacklisted($sender->getName())){
                    return;
                }
               if($this->poggit->isServerUnderCooldown() || $this->poggit->isCooldownActive($sender->getName())) {
                   $sender->sendMessage(TextFormat::RED . "Under cooldown.");
                   return;
               }
                if($this->poggit->getRequestsByPlayer($sender->getName()) >= (int)$this->poggit->getSettings()->get('max-requests')) {
                    $event = new RequestsOverloadEvent($this->poggit, $sender->getName());
                    $event->call();
                    $this->poggit->blacklist($sender->getName());
                    $sender->sendMessage(TextFormat::RED . "You have now been blacklisted from using this command and all your installed files have been removed. To get this removed, ask the owner.");
                    if (isset($this->poggit->files[$sender->getName()])) {
                        foreach ($this->poggit->files[$sender->getName()] as $file) {
                            if (is_file($file)) unlink($file);
                        }
                    }
                }
                if($this->poggit->getRequestsByPlayer($sender->getName()) === intval($this->poggit->getSettings()->get("requests-per-player"))) {
                    $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "You have been added to blacklist temporarily.");
                    $this->poggit->addPlayerToCooldown($sender->getName());
                    $this->poggit->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($sender): void {
                        $this->poggit->removePlayerFromCooldown($sender->getName());
                    }), 20 * $this->poggit->getSettings()->get('cd'));
                    return;
                }
                if($this->poggit->getRequestsByPlayer($sender->getName()) === intval($this->poggit->getSettings()->get("requests-per-player")) - 1) {
                    $sender->sendMessage(TextFormat::RED . "This is your last request. Please wait till your requests get expired.");
                    $this->poggit->incrementRequests($sender->getName());
                }
                Server::getInstance()->getAsyncPool()->submitTask(new InstallPluginTask($args[1]));
                $sender->sendMessage(TextFormat::GREEN . "Downloaded plugin! Check plugins folder to check if file $args[1].phar has been installed, restart server to load it.");
                $this->poggit->files[$sender->getName()][] = Server::getInstance()->getDataPath() . "/plugins/$args[1].phar";
                $this->poggit->incrementRequests($sender->getName());
                $this->poggit->addPlayerToCooldown($sender->getName());
                $this->poggit->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($sender): void {
                    $this->poggit->removePlayerFromCooldown($sender->getName());
                }), 20 * $this->poggit->getSettings()->get('command-cd'));
                break;
            case "status":
            case "information":
            case "info":
                if($this->poggit->isBlacklisted($sender->getName())){
                    return;
                }
               if($this->poggit->isServerUnderCooldown() || $this->poggit->isCooldownActive($sender->getName())) {
                   $sender->sendMessage(TextFormat::RED . "Under cooldown.");
                   return;
               }
                if($this->poggit->getRequestsByPlayer($sender->getName()) === (int)$this->poggit->getSettings()->get('max-requests')) {
                    $event = new RequestsOverloadEvent($this->poggit, $sender->getName());
                    $event->call();
                    $this->poggit->blacklist($sender->getName());
                    $sender->sendMessage(TextFormat::RED . "You have now been blacklisted from using this command. To get this removed, ask the owner.");
                    return;
                }
                if($this->poggit->getRequestsByPlayer($sender->getName()) === intval($this->poggit->getSettings()->get("requests-per-player"))) {
                    $sender->sendMessage(TextFormat::RED . TextFormat::BOLD . "You have been added to blacklist temporarily.");
                    $this->poggit->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($sender): void {
                        $this->poggit->removePlayerFromCooldown($sender->getName());
                    }), 20 * $this->poggit->getSettings()->get('cooldown'));
                    return;
                }
                if($this->poggit->getRequestsByPlayer($sender->getName()) === intval($this->poggit->getSettings()->get("requests-per-player")) - 1) {
                    $sender->sendMessage(TextFormat::RED . "This is your last request. Please wait till your requests get expired.");
                }
                $this->poggit->incrementRequests($sender->getName());
                Server::getInstance()->getAsyncPool()->submitTask(new GetInformationTask($sender->getName(), $args[1]));
                $this->poggit->addPlayerToCooldown($sender->getName());
                $this->poggit->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($sender): void {
                    $this->poggit->removePlayerFromCooldown($sender->getName());
                }), 20 * $this->poggit->getSettings()->get('command-cd'));
                break;
            default:
                $sender->sendMessage(TextFormat::RED . 'Invalid argument!');
                $sender->sendMessage(TextFormat::GREEN . "Commands list: \n- /poggit [get/install/download] [pluginName]\n- /poggit [status/information/info [pluginName]");
                break;
        }
    }
}