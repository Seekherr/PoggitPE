<?php

declare(strict_types=1);

namespace seeker\poggitpe\tasks;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class InstallPluginTask extends AsyncTask
{

    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function onRun()
    {
        $time = time();
        $url = Internet::getURL('https://poggit.pmmp.io/releases.json?name=' . $this->name);
        if($url !== false){
            $info = json_decode($url);
            if($info === null || !$info){
                $this->setResult(null);
                return;
            }
            $url = $info[0]->artifact_url;
            $get = Internet::getURL($url);
            $this->setResult($get);
            $end = time() - $time;
            echo "\n Execution time: $end. \n"; //Average time on local server: 2-3 depending on size. LMK if higher than that.
        }
    }

    public function onCompletion(Server $server)
    {
        $result = $this->getResult();
        if($result === null) return;
        file_put_contents($server->getDataPath() . 'plugins' . DIRECTORY_SEPARATOR . $this->name . '.phar', $result);
        $server->getLogger()->info(TextFormat::BOLD . TextFormat::GREEN . "Plugin {$this->name} downloaded!");
    }
}