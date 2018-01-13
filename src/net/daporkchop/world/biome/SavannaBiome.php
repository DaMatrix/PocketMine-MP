<?php

namespace net\daporkchop\world\biome;

use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\generator\normal\biome\GrassyBiome;
use pocketmine\block\Sapling;

class SavannaBiome extends GrassyBiome  {
    public function __construct()   {
        parent::__construct();
        
        $tallGrass = new TallGrass();
        $tallGrass->setBaseAmount(12);
        
        $this->addPopulator($tallGrass);
        
        $trees = new Tree(Sapling::ACACIA);
        $trees->setBaseAmount(1);
        $this->addPopulator($trees);
        
        $this->setElevation(65, 67);
    }
    
    public function getName()  : string  {
        return "savanna";
    }
}
