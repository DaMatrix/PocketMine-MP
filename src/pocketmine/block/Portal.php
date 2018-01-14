<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\entity\Entity;
use pocketmine\Server;
use pocketmine\level\Level;
use pocketmine\level\Position;

/**
 * Portal block
 */
class Portal extends Transparent{

	//protected $id = self::PORTAL;

	public function __construct(int $meta = 0){
		$this->meta = $meta;
		$this->id = self::PORTAL;
	}

	public function getName() : string{
		return "Portal";
	}

	public function canPassThrough() : bool{
		return true;
	}

	public function isBreakable(Item $item) : bool{
		return false;
	}

	public function canBeFlowedInto() : bool{
		return false;
	}

	public function canBeReplaced() : bool{
		return false;
	}

	public function canBePlaced() : bool{
		return true;
	}

	public function isSolid() : bool{
		return false;
	}

	public function getBoundingBox() : ?AxisAlignedBB{
		return null;
	}

	public function getHardness() : float{
		return -1;
	}

	public function getBlastResistance() : float{
		return 0;
	}
	
	public function hasEntityCollision() : bool{
	    return true;
	}
	
	public function onEntityCollide(Entity $entity) : void{
	    $ow = Server::getInstance()->getDefaultLevel();
	    $nether = Server::getInstance()->getLevelByName("nether");
	    if ($entity->level == $ow){
	        $newPos = $entity->divide(8);
	        $newX = $this->roundUpToAny($newPos->x);
	        $newZ = $this->roundUpToAny($newPos->z);
	        $newY = $this->getFirstGround($nether, $newX, $newZ);
	        $entity->teleport(new Position($newX, $newY, $newZ, $nether));
	    } elseif ($entity->level == $nether){
	        $newPos = $entity->multiply(8);
	        $newX = $this->roundUpToAny($newPos->x);
	        $newZ = $this->roundUpToAny($newPos->z);
	        $newY = $this->getFirstGround($ow, $newX, $newZ);
	        $entity->teleport(new Position($newX, $newY, $newZ, $ow));
	    } else {
	        echo "wrong dimension!";
	    }
	}
	
	function roundUpToAny($n,$x=128) {
	    return round(($n+$x/2)/$x)*$x;
	}
	
	function getFirstGround(Level $level, $x, $z){
	    for ($y = 120; $y > 3; $y--) {
	        if ($level->getBlockIdAt((int) floor($x), $y, (int) floor($z)) == Block::AIR)    {
	            return $y;
	        }
	    }
	    
	    return 64;
	}

}
