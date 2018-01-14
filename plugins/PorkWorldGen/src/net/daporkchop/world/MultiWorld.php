<?php

namespace net\daporkchop\world;

use net\daporkchop\world\generator\PorkWorld;
use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class MultiWorld extends PluginBase {
    public static $instance;
    
    public function onEnable()  {
        self::$instance = $this;
        
        Generator::addGenerator(PorkWorld::class, "porkworld");

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new SendMOTD($this), 100);
        echo("done xd");
    }
}

class SendMOTD extends PluginTask {
    
    public $motds;
    
    public function __construct($plugin)    {
        parent::__construct($plugin);
        
        $this->motds = [
            "&a> Did you know?",
            "somebody set us up the bomb",
            "666 KILL HITLER 666",
            "you're mom gay",
            "autismo supremo",
            "pocket edition has been disabled due to an exploit",
            "&4REEEEEEEEEEEEEEEEEEEE",
            "0 YEARS",
            "bepis > conk",
            "&ftotally sponsored by &9team &cpepsi",
            "&a> implying",
            "&a> meme arrow",
            "time to smoke weed and eat pizza!",
            "time to smoke pizza and eat weed!",
            "Doom is upon you.",
            "you are the reason this place is on a watchlist",
            "stop &bHECKING &cswearing",
            "to be to te dat urg",
            "This won't hurt a bit!",
            "sponsored by &6Bitcoin",
            "oh.",
            "why am i still making MOTDs",
            "oh no, it's retarded",
            "nigger bitch fuck shit &alol mcpe censored that"
        ];
        
        for ($i = 0; $i < sizeof($this->motds); $i++){
            $motd = $this->motds[$i];
            if (strpos($motd, "&") === 0)   {
                $this->motds[$i] = TextFormat::colorize($motd);
            } else {
                $this->motds[$i] = TextFormat::colorize("&c" . $motd);
            }
        }
    }
    
    public function onRun(int $currentTick)
    {
        $this->owner->getServer()->getNetwork()->setName($this->motds[mt_rand(0, sizeof($this->motds) - 1)]);
    }
}
