<?php

namespace net\daporkchop\world\biome;

use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\biome\GrassyBiome;
use pocketmine\block\Sapling;

class JungleBiome extends GrassyBiome  {
    public function __construct()   {
        parent::__construct();
        
        $tallGrass = new TallGrass();
        $tallGrass->setBaseAmount(12);
        
        $this->addPopulator($tallGrass);
        
        $trees = new Tree(Sapling::JUNGLE);
        $trees->setBaseAmount(5);
        $this->addPopulator($trees);
        
        $this->setElevation(65, 80);
    }
    
    public function getName() : string   {
        return "jungle";
    }
}
