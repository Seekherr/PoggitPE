<?php


namespace seeker\poggitpe\commands;


use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use seeker\poggitpe\PoggitPE;

class BlacklistedCommand extends PluginCommand
{

    /** @var PoggitPE */
    private $poggit;

    public function __construct(string $name, Plugin $owner)
    {
        parent::__construct($name, $owner);
        $this->poggit = $owner;
        $this->setDescription("Manage poggit blacklists.");
        $this->setAliases(["pbl", "poggitbl"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender->isOp()) return;
        if(count($args) > 2){
            $sender->sendMessage(TextFormat::RED . "Too many arguments!");
            $sender->sendMessage(TextFormat::GREEN . "Commands:\n/poggitblacklist [set/blacklist/add] playerName\n/poggitblacklist [unset/delete/remove] playerName");
            return;
        }
        if(!isset($args[0]) || !isset($args[1])){
            $sender->sendMessage(TextFormat::RED . "Too less arguments!");
            $sender->sendMessage(TextFormat::GREEN . "Commands:\n/poggitblacklist [set/blacklist/add] playerName\n/poggitblacklist [unset/delete/remove] playerName");
            return;
        }
        switch($args[0]){
            case "set":
            case "blacklist":
            case "add":
                $this->poggit->blacklist($args[1]);
                $sender->sendMessage(TextFormat::GREEN . $args[1] . " has been blacklisted.");
                break;
            case "unset":
            case "delete":
            case "remove":
                $this->poggit->removeFromBlacklist($args[1]);
                $sender->sendMessage(TextFormat::RED . $args[1] . " has been removed from the blacklist.");
                break;
            default:
                $sender->sendMessage(TextFormat::RED . "Invalid argument!");
                $sender->sendMessage(TextFormat::GREEN . "Commands:\n/poggitblacklist [set/blacklist/add] playerName\n/poggitblacklist [unset/delete/remove] playerName");$sender->sendMessage(TextFormat::GREEN . "Commands:\n/poggitblacklist <set/blacklist/add> playerName\n/poggitblacklist <unset/delete/remove> playerName");
                break;
        }
    }
}