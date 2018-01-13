<?php

namespace net\daporkchop\world\biome;

use net\daporkchop\world\biome\abs\MesaBiome;

class MesaNormalBiome extends MesaBiome  {
    public function __construct(){
        parent::__construct();
        
        $this->setElevation(67, 75);
    }
    
    public function getName() : string   {
        return "mesa";
    }
}
