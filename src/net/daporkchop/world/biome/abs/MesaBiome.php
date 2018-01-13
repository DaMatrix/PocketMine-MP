<?php

namespace net\daporkchop\world\biome\abs;

use pocketmine\level\generator\normal\biome\NormalBiome;
use pocketmine\block\BlockFactory;
use pocketmine\block\Block;

 abstract class MesaBiome extends NormalBiome {
    public function __construct(){
        $this->setGroundCover([
            BlockFactory::get(Block::TERRACOTTA, 1),
            BlockFactory::get(Block::TERRACOTTA, 14),
            BlockFactory::get(Block::TERRACOTTA, 1),
            BlockFactory::get(Block::TERRACOTTA, 1),
            BlockFactory::get(Block::TERRACOTTA, 4),
            BlockFactory::get(Block::TERRACOTTA, 14),
            BlockFactory::get(Block::TERRACOTTA, 1),
            BlockFactory::get(Block::TERRACOTTA, 15),
            BlockFactory::get(Block::TERRACOTTA, 1)
        ]);
        
        $this->temperature = 2.5;
        $this->rainfall = 0;
    }
}
