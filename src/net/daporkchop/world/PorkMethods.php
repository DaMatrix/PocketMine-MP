<?php
namespace net\daporkchop\world;

use pocketmine\level\Position;
use pocketmine\block\Liquid;

class PorkMethods
{

    public static function getSafeSpawn(Position $basePos, $radius = 128): Position
    {
        $level = $basePos->level;
        $pos = new Position($basePos->getFloorX(), $basePos->getFloorY(), $basePos->getFloorZ(), $level);
        
        while (true) {
            $tryPos = new Position($pos->x + mt_rand(- $radius, $radius), mt_rand(2, 255), $pos->z + mt_rand(- $radius, $radius), $level);
            $block = $level->getBlock($tryPos);
            $up = $level->getBlock($tryPos->add(0, 1, 0));
            $down = $level->getBlock($tryPos->subtract(0, 1, 0));
            if (! $block->isSolid() and !($block instanceof Liquid) and ! $up->isSolid() and !($up instanceof Liquid) and $down->isSolid()) {
                // eclipse formatted this wierd idk
                $pos = Position::fromObject($tryPos->add(0.5, 0, 0.5), $tryPos->level);
                break;
            }
        }
        
        return $pos;
    }
    
    public function clamp (float $in, float $min, float $max):float{
        return $in > $max ? $max : $in < $min ? $min : $in;
    }
}

