<?php
namespace net\daporkchop\world\generator;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\biome\Biome;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use net\daporkchop\world\noise\NoiseGeneratorOctaves3D;
use net\daporkchop\world\PorkBiomeSelector;
use pocketmine\block\Stone;

/**
 * this class is painstakingly ported over to PHP from https://github.com/Barteks2x/173generator/blob/master/src/main/java/com/github/barteks2x/b173gen/generator/ChunkProviderGenerate.java
 * thanks barteks
 */
class PorkWorld extends Generator
{

    private $selector;

    private $level;

    private $populators = [];

    private $generationPopulators = [];

    /**
     * 
     * @var Random
     */
    private $random;

    private $noise1;

    private $noise2;

    private $noise3;

    private $noise;

    private $sandNoise;

    private $gravelNoise;

    private $stoneNoise;

    private $noise6;

    private $noise7;

    private $gen1;

    private $gen2;

    private $gen3;

    private $gen4;

    private $gen5;

    private $gen6;

    private $gen7;

    private $genTrees;

    public function __construct(array $settings = [])
    {}

    public function init(ChunkManager $level, Random $random)
    {
        $this->random = $random;
        $this->random->setSeed($level->getSeed());
        $this->level = $level;
        $this->selector = new PorkBiomeSelector($this->random, Biome::getBiome(Biome::OCEAN));
        $this->generationPopulators[] = new GroundCover();
        $ores = new Ore();
        $ores->setOreTypes([
            new OreType(BlockFactory::get(Block::COAL_ORE), 20, 16, 0, 128),
            new OreType(BlockFactory::get(Block::IRON_ORE), 20, 8, 0, 64),
            new OreType(BlockFactory::get(Block::REDSTONE_ORE), 1, 7, 0, 16),
            new OreType(BlockFactory::get(Block::LAPIS_ORE), 2, 6, 0, 32),
            new OreType(BlockFactory::get(Block::GOLD_ORE), 4, 8, 0, 32),
            new OreType(BlockFactory::get(Block::DIAMOND_ORE), 2, 7, 0, 16),
            new OreType(BlockFactory::get(Block::DIRT), 20, 32, 0, 128),
            new OreType(BlockFactory::get(Block::GRAVEL), 10, 16, 0, 128),
            new OreType(BlockFactory::get(Block::STONE, Stone::DIORITE), 6, 32, 0, 128),
            new OreType(BlockFactory::get(Block::STONE, Stone::ANDESITE), 6, 32, 0, 128),
            new OreType(BlockFactory::get(Block::STONE, Stone::GRANITE), 6, 32, 0, 128),
        ]);
        $this->populators[] = $ores;
        
        $this->random->setSeed($level->getSeed());
        $this->gen1 = new NoiseGeneratorOctaves3D(16, $this->random);
        $this->gen2 = new NoiseGeneratorOctaves3D(16, $this->random);
        $this->gen3 = new NoiseGeneratorOctaves3D(8, $this->random);
        $this->gen4 = new NoiseGeneratorOctaves3D(4, $this->random);
        $this->gen5 = new NoiseGeneratorOctaves3D(4, $this->random);
        $this->gen6 = new NoiseGeneratorOctaves3D(10, $this->random);
        $this->gen7 = new NoiseGeneratorOctaves3D(16, $this->random);
        $this->genTrees = new NoiseGeneratorOctaves3D(8, $this->random);
    }

    public function getName(): string
    {
        return "porkworld";
    }

    public function getSpawn(): Vector3
    {
        return new Vector3(0.5, 128, 0.5);
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

    public function getSettings(): array
    {
        return [];
    }

    public function generateChunk(int $x, int $z)
    {
        $chunk = $this->level->getChunk($x, $z);
        $this->random->setSeed(0xdeadbeef ^ ($x << 8) ^ $z ^ $this->level->getSeed());
        
        /*
         * $temp = array(
         * 256
         * );
         * $rain = array(
         * 256
         * );
         *
         * for ($xx = 0; $xx < 16; ++ $xx) {
         * for ($zz = 0; $zz < 16; ++ $zz) {
         * $out = $this->pickBiome($x * 16 + $xx, $z * 16 + $zz);
         * $chunk->setBiomeId($xx, $zz, $out->biome->getId());
         * $temp[$xx * 16 + $zz] = $out->temp;
         * $rain[$xx * 16 + $zz] = $out->rain;
         * }
         * }
         */
        
        $byte0 = 4;
        $oceanHeight = 64;
        $k = $byte0 + 1;
        $b2 = 17;
        $l = $byte0 + 1;
        $this->initNoiseField($x * $byte0, 0, $z * $byte0, $k, $b2, $l);
        
        for ($xPiece = 0; $xPiece < $byte0; $xPiece ++) {
            for ($zPiece = 0; $zPiece < $byte0; $zPiece ++) {
                for ($yPiece = 0; $yPiece < 16; $yPiece ++) {
                    $d = 0.125;
                    $d1 = $this->noise[(($xPiece + 0) * $l + ($zPiece + 0)) * $b2 + ($yPiece + 0)];
                    $d2 = $this->noise[(($xPiece + 0) * $l + ($zPiece + 1)) * $b2 + ($yPiece + 0)];
                    $d3 = $this->noise[(($xPiece + 1) * $l + ($zPiece + 0)) * $b2 + ($yPiece + 0)];
                    $d4 = $this->noise[(($xPiece + 1) * $l + ($zPiece + 1)) * $b2 + ($yPiece + 0)];
                    $d5 = ($this->noise[(($xPiece + 0) * $l + ($zPiece + 0)) * $b2 + ($yPiece + 1)] - $d1) * $d;
                    $d6 = ($this->noise[(($xPiece + 0) * $l + ($zPiece + 1)) * $b2 + ($yPiece + 1)] - $d2) * $d;
                    $d7 = ($this->noise[(($xPiece + 1) * $l + ($zPiece + 0)) * $b2 + ($yPiece + 1)] - $d3) * $d;
                    $d8 = ($this->noise[(($xPiece + 1) * $l + ($zPiece + 1)) * $b2 + ($yPiece + 1)] - $d4) * $d;
                    for ($l1 = 0; $l1 < 8; $l1 ++) {
                        $d9 = 0.25;
                        $d10 = $d1;
                        $d11 = $d2;
                        $d12 = ($d3 - $d1) * $d9;
                        $d13 = ($d4 - $d2) * $d9;
                        for ($i2 = 0; $i2 < 4; $i2 ++) {
                            $xLoc = $i2 + $xPiece * 4;
                            $yLoc = $yPiece * 8 + $l1;
                            $zLoc = 0 + $zPiece * 4;
                            $d14 = 0.25;
                            $d15 = $d10;
                            $d16 = ($d11 - $d10) * $d14;
                            for ($k2 = 0; $k2 < 4; $k2 ++) {
                                $d17 = 1 - ($yPiece / 16);
                                $block = Block::AIR;
                                if ($yLoc < 64) {
                                    if ($d17 < 0.5 && $yLoc >= 64 - 1) {
                                        $block = Block::ICE;
                                    } else {
                                        $block = Block::WATER;
                                    }
                                }
                                if ($d15 > 0.0) {
                                    $block = Block::STONE;
                                }
                                $chunk->setBlockId($xLoc, $yLoc, $zLoc, $block);
                                $zLoc ++;
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
        
        for ($xx = 0; $xx < 16; $xx ++) {
            for ($zz = 0; $zz < 16; $zz ++) {
                $highest = $chunk->getHighestBlockAt($xx, $zz);
                $chunk->setBiomeId($xx, $zz, $this->pickBiome($x * 16 + $xx, $z * 16 + $zz, $chunk->getBlockId($xx, $highest, $zz) == Block::WATER ? 1 : $highest)->biome->getId());
            }
        }
        
        foreach ($this->generationPopulators as $populator) {
            $populator->populate($this->level, $x, $z, $this->random);
        }
    }

    public function initNoiseField($posX, $posY, $posZ, $xSize, $ySize, $zSize)
    {
        $this->noise = array(
            $xSize * $ySize * $zSize
        );
        $d0 = 684.412;
        $d1 = 684.412;
        
        $this->noise6 = $this->gen6->generateNoiseArray2($posX, $posZ, $xSize, $zSize, 1.121, 1.121, 0.5);
        $this->noise7 = $this->gen7->generateNoiseArray2($posX, $posZ, $xSize, $zSize, 200, 200, 0.5);
        $this->noise3 = $this->gen3->generateNoiseArray($posX, $posY, $posZ, $xSize, $ySize, $zSize, $d0 / 80, $d1 / 160, $d0 / 80);
        $this->noise1 = $this->gen1->generateNoiseArray($posX, $posY, $posZ, $xSize, $ySize, $zSize, $d0, $d1, $d0);
        $this->noise2 = $this->gen2->generateNoiseArray($posX, $posY, $posZ, $xSize, $ySize, $zSize, $d0, $d1, $d0);
        
        $k1 = 0;
        $l1 = 0;
        $i2 = 16 / $xSize;
        
        for ($x = 0; $x < $xSize; $x ++) {
            $k2 = $x * $i2 + $i2 / 2;
            for ($z = 0; $z < $zSize; $z ++) {
                $i3 = $z * $i2 + $i2 / 2;
                $d2 = 1;
                $d3 = 1;
                // $d2 = $temp[$k2 * 16 + $i3];
                // $d3 = $rain[$k2 * 16 + $i3] * $d2;
                $d4 = 1.0 - $d3;
                $d4 *= $d4;
                $d4 *= $d4;
                $d4 = 1.0 - $d4;
                $d5 = ($this->noise6[$l1] + 256) / 512;
                $d5 *= $d4;
                if ($d5 > 1.0) {
                    $d5 = 1.0;
                }
                $d6 = $this->noise7[$l1] / 8000;
                if ($d6 < 0.0) {
                    $d6 = - $d6 * 0.3;
                }
                $d6 = $d6 * 3 - 2;
                if ($d6 < 0.0) {
                    $d6 /= 2;
                    if ($d6 < - 1) {
                        $d6 = - 1;
                    }
                    $d6 /= 1.4;
                    $d6 /= 2;
                    $d5 = 0.0;
                } else {
                    if ($d6 > 1.0) {
                        $d6 = 1.0;
                    }
                    $d6 /= 8;
                }
                if ($d5 < 0.0) {
                    $d5 = 0.0;
                }
                $d5 += 0.5;
                $d6 = ($d6 * $ySize) / 16;
                $d7 = $ySize / 2 + $d6 * 4;
                $l1 ++;
                for ($y = 0; $y < $ySize; $y ++) {
                    $d8 = 0.0;
                    $d9 = (($y - $d7) * 12) / $d5;
                    if ($d9 < 0.0) {
                        $d9 *= 4;
                    }
                    $d10 = $this->noise1[$k1] / 512;
                    $d11 = $this->noise2[$k1] / 512;
                    $d12 = ($this->noise3[$k1] / 10 + 1.0) / 2;
                    if ($d12 < 0.0) {
                        $d8 = $d10;
                    } elseif ($d12 > 1.0) {
                        $d8 = $d11;
                    } else {
                        $d8 = $d10 + ($d11 - $d10) * $d12;
                    }
                    $d8 -= $d9;
                    if ($y > $ySize - 4) {
                        $d13 = (($y - ($ySize - 4)) / 3);
                        $d8 = $d8 * (1.0 - $d13) + - 10 * $d13;
                    }
                    $this->noise[$k1] = $d8;
                    $k1 ++;
                }
            }
        }
    }

    public function pickBiome(int $x, int $z, $height)
    {
        // return Biome::getBiome(Biome::MOUNTAINS);
        $hash = $x * 2345803 ^ $z * 9236449 ^ $this->level->getSeed();
        $hash *= $hash + 223;
        $xNoise = $hash >> 20 & 3;
        $zNoise = $hash >> 22 & 3;
        if ($xNoise == 3) {
            $xNoise = 1;
        }
        if ($zNoise == 3) {
            $zNoise = 1;
        }
        
        return $this->selector->pickBiomeNew($x + $xNoise - 1, $z + $zNoise - 1, $height);
    }
}
