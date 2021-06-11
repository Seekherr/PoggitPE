<?php

declare(strict_types=1);

namespace seeker\poggitpe\tasks;


use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class GetInformationTask extends AsyncTask
{

    /** @var string */
    private $target;

    /** @var string */
    private $name;

    public function __construct(string $target, string $name)
    {
        $this->target = $target;
        $this->name = $name;
    }

    public function onRun()
    {
        $url = Internet::getURL('https://poggit.pmmp.io/releases.json?name=' . $this->name);
        if ($url !== false) {
            $info = json_decode($url, true);
            if ($info === null || !$info) {
                $this->setResult(null);
                return;
            }
            $this->setResult([$info[0]["name"], $info[0]["version"], $info[0]["api"][0]["from"], $info[0]["api"][0]["to"], $info[0]["repo_name"], $info[0]["artifact_url"]]);
        }
    }

    public function onCompletion(Server $server)
    {
        $player = $server->getPlayerExact($this->target);
        if ($this->getResult() === null) {
            if ($player instanceof Player) $player->sendMessage(TextFormat::RED . "Plugin $this->name is not available on poggit.");
            else $server->getLogger()->info(TextFormat::RED . "Plugin $this->name is not available on poggit.");
            return;
        }
        [$name, $version, $from, $to, $repo_name, $download_url] = $this->getResult();
        if ($player instanceof Player) {
            $player->sendMessage(
                TextFormat::YELLOW . "=====" . TextFormat::AQUA . "PoggitPE" . TextFormat::YELLOW . "=====" . TextFormat::GREEN . "\nName: " . TextFormat::YELLOW . $name . TextFormat::GREEN . "\nVersion: " . TextFormat::YELLOW . $version . TextFormat::GREEN . "\nSupported API versions: " . TextFormat::YELLOW . "$from - $to" . TextFormat::GREEN . "\nGithub repository name: " . TextFormat::YELLOW . $repo_name . TextFormat::GREEN . "\nDownload URL: " . TextFormat::YELLOW . $download_url
            );
        } else {
            $server->getLogger()->info(
                TextFormat::YELLOW . "\n=====" . TextFormat::AQUA . "PoggitPE" . TextFormat::YELLOW . "=====" . TextFormat::GREEN . "\nName: " . TextFormat::YELLOW . $name . TextFormat::GREEN . "\nVersion: " . TextFormat::YELLOW . $version . TextFormat::GREEN . "\nSupported API versions: " . TextFormat::YELLOW . "$from - $to" . TextFormat::GREEN . "\nGithub repository name: " . TextFormat::YELLOW . $repo_name . TextFormat::GREEN . "\nDownload URL: " . TextFormat::YELLOW . $download_url
            );
        }
    }
}