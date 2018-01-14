<?php

/*
 *
 * ____ _ _ __ __ _ __ __ ____
 * | _ \ ___ ___| | _____| |_| \/ (_)_ __ ___ | \/ | _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * | __/ (_) | (__| < __/ |_| | | | | | | | __/_____| | | | __/
 * |_| \___/ \___|_|\_\___|\__|_| |_|_|_| |_|\___| |_| |_|_|
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
declare(strict_types = 1);
namespace pocketmine\level\generator\hell;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Ore;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\block\NetherQuartzOre;
use net\daporkchop\world\noise\NoiseGeneratorOctaves3D;
use pocketmine\level\format\Chunk;
use net\daporkchop\world\PorkMethods;

class Nether extends Generator
{

    /** @var Populator[] */
    private $populators = [];

    /** @var ChunkManager */
    private $level;

    /** @var Random */
    private $random;

    /** @var Populator[] */
    private $generationPopulators = [];

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    private $lperlinNoise1;

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    private $lperlinNoise2;

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    private $perlinNoise1;

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    private $slowsandGravelNoiseGen;

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    private $netherrackExculsivityNoiseGen;

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    public $scaleNoise;

    /**
     *
     * @var NoiseGeneratorOctaves3D
     */
    public $depthNoise;

    public $pnr;

    public $ar;

    public $br;

    public $noiseData4;

    public $dr;

    public $buffer = [];

    private $slowsandNoise = array(
        256
    );

    private $gravelNoise = array(
        256
    );

    private $depthBuffer = array(
        256
    );

    public function __construct(array $options = [])
    {}

    public function getName(): string
    {
        return "nether";
    }

    public function getSettings(): array
    {
        return [];
    }

    public function init(ChunkManager $level, Random $random)
    {
        $this->level = $level;
        $this->random = $random;
        $this->random->setSeed($this->level->getSeed());
        $this->lperlinNoise1 = new NoiseGeneratorOctaves3D(16, $random);
        $this->lperlinNoise2 = new NoiseGeneratorOctaves3D(16, $random);
        $this->perlinNoise1 = new NoiseGeneratorOctaves3D(8, $random);
        $this->slowsandGravelNoiseGen = new NoiseGeneratorOctaves3D(4, $random);
        $this->netherrackExculsivityNoiseGen = new NoiseGeneratorOctaves3D(4, $random);
        $this->scaleNoise = new NoiseGeneratorOctaves3D(10, $random);
        $this->depthNoise = new NoiseGeneratorOctaves3D(16, $random);
        
        $ores = new Ore();
        $ores->setOreTypes([
            new OreType(new NetherQuartzOre(), 25, 16, 0, 128)
        ]);
        $this->populators[] = $ores;
    }

    public function generateChunk(int $x, int $z)
    {
        $this->random->setSeed(0xdeadbeef ^ ($x << 8) ^ $z ^ $this->level->getSeed());
        $chunk = $this->level->getChunk($x, $z);
        
        $this->prepareHeights($x, $z, $chunk);
        //$this->buildSurfaces($x, $z, $chunk);
        
        for ($x = 0; $x < 16; $x ++) {
            for ($z = 0; $z < 16; $z ++) {
                $chunk->setBiomeId($x, $z, Biome::HELL);
            }
        }
        
        foreach ($this->generationPopulators as $populator) {
            $populator->populate($this->level, $x, $z, $this->random);
        }
    }

    public function buildSurfaces($p_185937_1_, $p_185937_2_, Chunk $chunk)
    {
        $i = 63 + 1;
        $d0 = 0.03125;
        $this->slowsandNoise = $this->slowsandGravelNoiseGen->generateNoiseArray(     $p_185937_1_ * 16, $p_185937_2_ * 16, 0,                 16, 16, 1,  0.03125,   0.03125, 1.0);
        $this->gravelNoise = $this->slowsandGravelNoiseGen->generateNoiseArray(       $p_185937_1_ * 16, 109,               $p_185937_2_ * 16, 16, 1,  16, 0.03125,   1.0,     0.03125);
        $this->depthBuffer = $this->netherrackExculsivityNoiseGen->generateNoiseArray(   $p_185937_1_ * 16, $p_185937_2_ * 16,                    0,  16, 16, 1,         0.0625, 0.0625,  0.0625);
        
        for ($j = 0; $j < 16; ++ $j) {
            for ($k = 0; $k < 16; ++ $k) {
                $flag = $this->slowsandNoise[$j + $k * 16] + $this->random->nextFloat() * 0.2 > 0.0;
                $flag1 = $this->gravelNoise[$j + $k * 16] + $this->random->nextFloat() * 0.2 > 0.0;
                $l = ($this->depthBuffer[$j + $k * 16] / 3.0 + 3.0 + $this->random->nextFloat() * 0.25);
                $i1 = - 1;
                $iblockstate = Block::NETHERRACK;
                $iblockstate1 = Block::NETHERRACK;
                
                for ($j1 = 127; $j1 >= 0; -- $j1) {
                    if ($j1 < 127 - $this->random->nextBoundedInt(5) && $j1 > $this->random->nextBoundedInt(5)) {
                        $iblockstate2 = $chunk->getBlockId($k, $j1, $j);
                        
                        if ($iblockstate2 == Block::NETHERRACK) {
                            if ($i1 == - 1) {
                                if ($l <= 0) {
                                    $iblockstate = Block::AIR;
                                    $iblockstate1 = Block::NETHERRACK;
                                } else if ($j1 >= $i - 4 && $j1 <= $i + 1) {
                                    $iblockstate = Block::NETHERRACK;
                                    $iblockstate1 = Block::NETHERRACK;
                                    
                                    if ($flag1) {
                                        $iblockstate = Block::GRAVEL;
                                        $iblockstate1 = Block::NETHERRACK;
                                    }
                                    
                                    if ($flag) {
                                        $iblockstate = Block::SOUL_SAND;
                                        $iblockstate1 = Block::SOUL_SAND;
                                    }
                                }
                                
                                if ($j1 < $i && $iblockstate == Block::AIR) {
                                    $iblockstate = Block::LAVA;
                                }
                                
                                $i1 = $l;
                                
                                if ($j1 >= $i - 1) {
                                    $chunk->setBlockId($k, $j1, $j, $iblockstate);
                                } else {
                                    $chunk->setBlockId($k, $j1, $j, $iblockstate1);
                                }
                            } else if ($i1 > 0) {
                                -- $i1;
                                $chunk->setBlockId($k, $j1, $j, $iblockstate1);
                            }
                        }
                    } else {
                        $chunk->setBlockId($k, $j1, $j, Block::BEDROCK);
                    }
                }
            }
        }
    }

    public function prepareHeights($p_185936_1_, $p_185936_2_, Chunk $primer)
    {
        $i = 4;
        $j = floor(63 / 2) + 1;
        $k = 5;
        $l = 17;
        $i1 = 5;
        $this->buffer = $this->getHeights($this->buffer, $p_185936_1_ * 4, 0, $p_185936_2_ * 4, 5, 17, 5);
        
        for ($j1 = 0; $j1 < 4; ++ $j1) {
            for ($k1 = 0; $k1 < 4; ++ $k1) {
                for ($l1 = 0; $l1 < 16; ++ $l1) {
                    $d0 = 0.125;
                    $d1 = $this->buffer[(($j1 + 0) * 5 + $k1 + 0) * 17 + $l1 + 0];
                    $d2 = $this->buffer[(($j1 + 0) * 5 + $k1 + 1) * 17 + $l1 + 0];
                    $d3 = $this->buffer[(($j1 + 1) * 5 + $k1 + 0) * 17 + $l1 + 0];
                    $d4 = $this->buffer[(($j1 + 1) * 5 + $k1 + 1) * 17 + $l1 + 0];
                    $d5 = ($this->buffer[(($j1 + 0) * 5 + $k1 + 0) * 17 + $l1 + 1] - $d1) * 0.125;
                    $d6 = ($this->buffer[(($j1 + 0) * 5 + $k1 + 1) * 17 + $l1 + 1] - $d2) * 0.125;
                    $d7 = ($this->buffer[(($j1 + 1) * 5 + $k1 + 0) * 17 + $l1 + 1] - $d3) * 0.125;
                    $d8 = ($this->buffer[(($j1 + 1) * 5 + $k1 + 1) * 17 + $l1 + 1] - $d4) * 0.125;
                    
                    for ($i2 = 0; $i2 < 8; ++ $i2) {
                        $d9 = 0.25;
                        $d10 = $d1;
                        $d11 = $d2;
                        $d12 = ($d3 - $d1) * 0.25;
                        $d13 = ($d4 - $d2) * 0.25;
                        
                        for ($j2 = 0; $j2 < 4; ++ $j2) {
                            $d14 = 0.25;
                            $d15 = $d10;
                            $d16 = ($d11 - $d10) * 0.25;
                            
                            for ($k2 = 0; $k2 < 4; ++ $k2) {
                                $block = 0;
                                
                                if ($l1 * 8 + $i2 < $j) {
                                    $block = Block::LAVA;
                                }
                                
                                if ($d15 > 0.0) {
                                    $block = Block::NETHERRACK;
                                }
                                
                                $l2 = $j2 + $j1 * 4;
                                $i3 = $i2 + $l1 * 8;
                                $j3 = $k2 + $k1 * 4;
                                $primer->setBlockId($l2, $i3, $j3, $block);
                                $d15 += $d16;
                            }
                            
                            $d10 += $d12;
                            $d11 += $d13;
                        }
                        
                        $d1 += $d5;
                        $d2 += $d6;
                        $d3 += $d7;
                        $d4 += $d8;
                    }
                }
            }
        }
    }

    private function getHeights($p_185938_1_, $p_185938_2_, $p_185938_3_, $p_185938_4_, $p_185938_5_, $p_185938_6_, $p_185938_7_): array
    {
        if ($p_185938_1_ == null) {
            $p_185938_1_ = array(
                $p_185938_5_ * $p_185938_6_ * $p_185938_7_
            );
        }
        
        $d0 = 684.412;
        $d1 = 2053.236;
        $this->noiseData4 = $this->scaleNoise->generateNoiseArray($p_185938_2_, $p_185938_3_, $p_185938_4_, $p_185938_5_, 1, $p_185938_7_, 1.0, 0.0, 1.0);
        $this->dr = $this->depthNoise->generateNoiseArray($p_185938_2_, $p_185938_3_, $p_185938_4_, $p_185938_5_, 1, $p_185938_7_, 100.0, 0.0, 100.0);
        $this->pnr = $this->perlinNoise1->generateNoiseArray($p_185938_2_, $p_185938_3_, $p_185938_4_, $p_185938_5_, $p_185938_6_, $p_185938_7_, 8.555150000000001, 34.2206, 8.555150000000001);
        $this->ar = $this->lperlinNoise1->generateNoiseArray($p_185938_2_, $p_185938_3_, $p_185938_4_, $p_185938_5_, $p_185938_6_, $p_185938_7_, 684.412, 2053.236, 684.412);
        $this->br = $this->lperlinNoise2->generateNoiseArray($p_185938_2_, $p_185938_3_, $p_185938_4_, $p_185938_5_, $p_185938_6_, $p_185938_7_, 684.412, 2053.236, 684.412);
        $i = 0;
        $adouble = array(
            $p_185938_6_
        );
        
        for ($j = 0; $j < $p_185938_6_; ++ $j) {
            $adouble[$j] = cos($j * 3.1415926 * 6.0 / $p_185938_6_) * 2.0;
            $d2 = $j;
            
            if ($j > $p_185938_6_ / 2) {
                $d2 = ($p_185938_6_ - 1 - $j);
            }
            
            if ($d2 < 4.0) {
                $d2 = 4.0 - $d2;
                $adouble[$j] -= $d2 * $d2 * $d2 * 10.0;
            }
        }
        
        for ($l = 0; $l < $p_185938_5_; ++ $l) {
            for ($i1 = 0; $i1 < $p_185938_7_; ++ $i1) {
                $d3 = 0.0;
                
                for ($k = 0; $k < $p_185938_6_; ++ $k) {
                    $d4 = $adouble[$k];
                    $d5 = $this->ar[$i] / 512.0;
                    $d6 = $this->br[$i] / 512.0;
                    $d7 = ($this->pnr[$i] / 10.0 + 1.0) / 2.0;
                    $d8;
                    
                    if ($d7 < 0.0) {
                        $d8 = $d5;
                    } else if ($d7 > 1.0) {
                        $d8 = $d6;
                    } else {
                        $d8 = $d5 + ($d6 - $d5) * $d7;
                    }
                    
                    $d8 = $d8 - $d4;
                    
                    if ($k > $p_185938_6_ - 4) {
                        $d9 = ($k - ($p_185938_6_ - 4)) / 3.0;
                        $d8 = $d8 * (1.0 - $d9) + - 10.0 * $d9;
                    }
                    
                    if ($k < 0.0) {
                        $d10 = (0.0 - $k) / 4.0;
                        $d10 = PorkMethods::clamp($d10, 0.0, 1.0);
                        $d8 = $d8 * (1.0 - $d10) + - 10.0 * $d10;
                    }
                    
                    $p_185938_1_[$i] = $d8;
                    ++ $i;
                }
            }
        }
        
        return $p_185938_1_;
    }

    public function populateChunk(int $chunkX, int $chunkZ)
    {
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        foreach ($this->populators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
        }
        
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $biome = Biome::getBiome($chunk->getBiomeId(7, 7));
        $biome->populateChunk($this->level, $chunkX, $chunkZ, $this->random);
    }

    public function getSpawn(): Vector3
    {
        return new Vector3(127.5, 128, 127.5);
    }
}
