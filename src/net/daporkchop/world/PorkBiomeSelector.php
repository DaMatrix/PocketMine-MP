<?php

namespace net\daporkchop\world;

use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\utils\Random;
use pocketmine\level\generator\noise\Simplex;

class PorkBiomeSelector extends BiomeSelector   {
    public $heightNoise;
    public $fallback;
    
    public function __construct(Random $random, Biome $fallback)  {
        parent::__construct($random, function($temp, $rain) {}, $fallback);
        
        $this->heightNoise = new Simplex($random, 8, 1 / 16, 1 / 2048);
        $this->fallback = $fallback;
    }
    
    public function recalculate(){
        
    }
    
    public function getHeight($x, $z){
        return ($this->heightNoise->noise2D($x, $z, true) + 1) / 2;
    }
    
    public function pickBiome($x, $z) : Biome{
        $temperature = $this->getTemperature($x, $z);
        $rainfall = $this->getRainfall($x, $z);
        $height = $this->getHeight($x, $z);
        
        $biomeId = Biome::OCEAN;
        if ($height < 0.3){
            if ($height > 0.13)  {
                $biomeId = Biome::DEEP_OCEAN;
            }
        } elseif ($height < 0.35)    {
            $biomeId = Biome::RIVER;
        } elseif ($height > 0.75)    {
            if ($temperature > 0.5){
                if ($rainfall > 0.5){
                    $biomeId = Biome::MESA_PLATEAU;
                } else {
                    $biomeId = Biome::SAVANNA_M;
                }
            } else {
                if ($rainfall > 0.5)    {
                    $biomeId = Biome::SMALL_MOUNTAINS;
                } else {
                    $biomeId = Biome::MOUNTAINS;
                }
            }
        } else {
            if ($temperature > 0.6) {
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
            } elseif ($temperature > 0.3)   {
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
                if ($rainfall > 0.5){
                    $biomeId = Biome::TAIGA;
                } else {
                    $biomeId = Biome::ICE_PLAINS;
                }
            }
        }
        
        return Biome::getBiome($biomeId);
    }
}
