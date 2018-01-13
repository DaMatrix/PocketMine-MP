<?php

namespace net\daporkchop\world\biome;

use net\daporkchop\world\biome\abs\MesaBiome;

class MesaPlateauBiome extends MesaBiome    {
    public function __construct(){
        parent::__construct();
        
        $this->setElevation(90, 94);
    }
    
    public function getName() : string   {
        return "mesa_plateau";
    }
}
