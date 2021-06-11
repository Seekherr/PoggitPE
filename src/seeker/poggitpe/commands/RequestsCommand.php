<?php

declare(strict_types=1);

namespace seeker\poggitpe\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\TextFormat;
use seeker\poggitpe\PoggitPE;

class RequestsCommand extends PluginCommand
{

    /** @var PoggitPE */
    private $poggit;

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->setDescription("Requests command for POggitPE!");
        $this->setAliases(["preqs", "pogreqs"]);
        $this->poggit = $owner;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        //there will be no args to modify requests because this can be abused
        if(!$sender->isOp()) return;
        if(count($args) > 2){
            $sender->sendMessage(TextFormat::RED . "Too many arguments!");
            $sender->sendMessage(TextFormat::GREEN . "Commands:\n/poggitrequests list [playerName/server]\n/poggitrequests cooldown <player/server>");
            return;
        }
        if(!isset($args[0]) || !isset($args[1])){
            $sender->sendMessage(TextFormat::RED . "Too less arguments!");
            $sender->sendMessage(TextFormat::GREEN . "Commands:\n/poggitrequests list [playerName/server]\n/poggitrequests cooldown <player/server>");
            return;
        }
        switch($args[0]){
            case "list":
                if($args[1] == "server"){
                    $count = 0;
                    foreach(array_values($this->poggit->getRequests()) as $values){
                        $count += $values;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "Requests made in server: " . TextFormat::YELLOW . $count);
                }
                else {
                    $requests = $this->poggit->getRequestsByPlayer($args[1]);
                    $sender->sendMessage(TextFormat::GREEN . "Requests made by $args[1]: " . TextFormat::YELLOW . $requests);
                }
                break;
            case "cooldown":
                if($args[1] == "server"){
                    $this->poggit->setServerOnCooldown();
                    $sender->sendMessage("Server added to cooldown.");
                    $this->poggit->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($sender) : void{
                        $this->poggit->removeServerFromCooldown();
                    }), 20 * $this->poggit->getSettings()->get('cd'));
                }
                else {
                    $this->poggit->addPlayerToCooldown($args[1]);
                    $sender->sendMessage("Player $args[1] added to cooldown.");
                    $this->poggit->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($sender) : void{
                        $this->poggit->removePlayerFromCooldown($sender->getName());
                    }), 20 * $this->poggit->getSettings()->get('cd'));
                }
                break;
        }
    }
}