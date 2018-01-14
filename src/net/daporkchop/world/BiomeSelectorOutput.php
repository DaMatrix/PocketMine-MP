<?php
namespace net\daporkchop\world;

use pocketmine\level\generator\biome\Biome;

class BiomeSelectorOutput
{

    /**
     *
     * @var Biome
     */
    public $biome;

    public $temp;

    public $rain;

    public function __construct($biome, $temp, $rain)
    {
        $this->biome = $biome;
        $this->temp = $temp;
        $this->rain = $rain;
    }
}

