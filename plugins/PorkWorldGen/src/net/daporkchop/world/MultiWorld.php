<?php

namespace net\daporkchop\world;

use pocketmine\plugin\PluginBase;
use pocketmine\level\generator\Generator;

class MultiWorld extends PluginBase {
    public static $instance;
    
    public function onEnable()  {
        self::$instance = $this;
        
        //Generator::addGenerator(WorldGenerator::class, "porkworld");

        echo("done xd");
    }
}
