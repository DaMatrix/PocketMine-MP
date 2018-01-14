<?php

namespace net\daporkchop\world;

use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\utils\Random;

class PorkBiomeSelector extends BiomeSelector   {
    public $fallback;
    
    public function __construct(Random $random, Biome $fallback)  {
        parent::__construct($random, function($temp, $rain) {}, $fallback);
        
        $this->fallback = $fallback;
    }
    
    public function recalculate(){
        
    }
    
    public function pickBiomeNew($x, $z, $height){
        $temperature = $this->getTemperature($x, $z);
        $rainfall = $this->getRainfall($x, $z);
        
        $biomeId = 0;
        
        if ($height == 1)    {
            $biomeId = Biome::OCEAN;
        } elseif ($height <= 64){
            $biomeId = Biome::BEACH;
        } else {
            if ($temperature > 0.8) {
                if ($rainfall > 0.85){
                    $biomeId = Biome::JUNGLE;
                } elseif ($rainfall > 0.7)  {
                    $biomeId = Biome::SWAMP;
                } elseif ($rainfall > 0.55)  {
                    $biomeId = Biome::SAVANNA;
                } elseif ($rainfall > 0.4) {
                    $biomeId = Biome::MESA;
                } else {
                    $biomeId = Biome::DESERT;
                }
            } elseif ($temperature > 0.6)   {
                if ($rainfall > 0.5){
                    if ($rainfall > 0.75){
                        $biomeId = Biome::BIRCH_FOREST;
                    } else {
                        $biomeId = Biome::FOREST;
                    }
                } else {
                    $biomeId = Biome::PLAINS;
                }
            } else {
                if ($rainfall > 0.75){
                    $biomeId = Biome::TAIGA;
                } elseif ($rainfall < 0.5){
                    $biomeId = Biome::MOUNTAINS;
                } else {
                    $biomeId = Biome::ICE_PLAINS;
                }
            }
        }
        
        return new BiomeSelectorOutput(Biome::getBiome($biomeId), $temperature, $rainfall);
    }
}
