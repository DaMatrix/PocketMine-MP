<?php
namespace net\daporkchop\world;

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
use pocketmine\level\generator\noise\Simplex;

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
    
    private $random;
    
    private $noise1;
    private $noise2;
    private $noise3;
    private $noise;
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
        $this->level = $level;
        $this->selector = new PorkBiomeSelector($this->random, Biome::getBiome(Biome::OCEAN));
        $this->generationPopulators[] = new GroundCover();
        $ores = new Ore();
        $ores->setOreTypes([
            new OreType(BlockFactory::get(Block::COAL_ORE), 20, 16, 0, 128),
            new OreType(BlockFactory::get(Block::IRON_ORE), 20, 8, 0, 64),
            new OreType(BlockFactory::get(Block::REDSTONE_ORE), 8, 7, 0, 16),
            new OreType(BlockFactory::get(Block::LAPIS_ORE), 1, 6, 0, 32),
            new OreType(BlockFactory::get(Block::GOLD_ORE), 2, 8, 0, 32),
            new OreType(BlockFactory::get(Block::DIAMOND_ORE), 1, 7, 0, 16),
            new OreType(BlockFactory::get(Block::DIRT), 20, 32, 0, 128),
            new OreType(BlockFactory::get(Block::GRAVEL), 10, 16, 0, 128)
        ]);
        $this->populators[] = $ores;
        
        $this->gen1 = new Simplex($this->random, 16, 0.5);
        $this->gen2 = new Simplex($this->random, 16, 0.5);
        $this->gen3 = new Simplex($this->random, 8, 0.5);
        $this->gen4 = new Simplex($this->random, 4, 0.5);
        $this->gen5 = new Simplex($this->random, 4, 0.5);
        $this->gen6 = new Simplex($this->random, 10, 0.5);
        $this->gen7 = new Simplex($this->random, 16, 0.5);
        $this->genTrees = new Simplex($this->random, 8, 0.5);
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

    public function generateChunk(int $chunkX, int $chunkZ)
    {
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        
        for($x = 0; $x < 16; ++$x){
            for($z = 0; $z < 16; ++$z){
                $chunk->setBiomeId($chunkX * 16 + $x, $chunkZ * 16 + $z, $this->pickBiome($chunkX * 16 + $x, $chunkZ * 16 + $z)->getId());
            }
        }
        
        $byte0 = 4;
        $oceanHeight = 64;
        $k = byte0 + 1;
        $b2 = 17;
        $l = byte0 + 1;
        $this->initNoiseField($array);
        
        foreach ($this->generationPopulators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
        }
    }
    
    public function initNoiseField($xPos, $yPos, $zPos, $xSize, $ySize, $zSize)   {
        $this->noise = array($xSize * $ySize * $zSize);
        $d0 = 684.412;
        $d1 = 684.412;
        
        $this->noise6 = Generator::getFastNoise2D($this->gen6, $xSize, $zSize, 0.5605, $posX, -1, $posZ);
        $this->noise7 = Generator::getFastNoise2D($this->gen7, $xSize, $zSize, 100, $posX, -1, $posZ);
        $this->noise3 = Generator::getFastNoise3D($this->gen3, $xSize, $ySize, $zSize, $d0 / 80, $d1 / 160, $d0 / 80, $posX, $posY, $posZ);
        $this->noise1 = Generator::getFastNoise3D($this->gen1, $xSize, $ySize, $zSize, $d0, $d1, $d0, $posX, $posY, $posZ);
        $this->noise2 = Generator::getFastNoise3D($this->gen2, $xSize, $ySize, $zSize, $d0, $d1, $d0, $posX, $posY, $posZ);
        
        $k1 = 0;
        $l1 = 0;
        $i2 = 16 / $xSize;
        
        for($x = 0; $x < xSize; $x++) {
            $k2 = $x * $i2 + $i2 / 2;
            for($z = 0; $z < $zSize; $z++) {
                $i3 = $z * $i2 + $i2 / 2;
                $d2 = $temp[$k2 * 16 + $i3];
                $d3 = $rain[$k2 * 16 + $i3] * $d2;
                $d4 = 1.0 - $d3;
                $d4 *= $d4;
                $d4 *= $d4;
                $d4 = 1.0 - $d4;
                $d5 = ($this->noise6[$l1] + 256) / 512;
                $d5 *= $d4;
                if($d5 > 1.0) {
                    $d5 = 1.0;
                }
                $d6 = $this->noise7[$l1] / 8000;
                if($d6 < 0.0) {
                    $d6 = -$d6 * 0.3;
                }
                $d6 = $d6 * 3 - 2;
                if($d6 < 0.0) {
                    $d6 /= 2;
                    if($d6 < -1) {
                        $d6 = -1;
                    }
                    $d6 /= 1.4;
                    $d6 /= 2;
                    $d5 = 0.0;
                } else {
                    if($d6 > 1.0) {
                        $d6 = 1.0;
                    }
                    $d6 /= 8;
                }
                if($d5 < 0.0) {
                    $d5 = 0.0;
                }
                $d5 += 0.5;
                $d6 = ($d6 * $ySize) / 16;
                $d7 = $ySize / 2 + $d6 * 4;
                $l1++;
                for($y = 0; $y < $ySize; $y++) {
                    $d8 = 0.0;
                    $d9 = (($y - $d7) * 12) / $d5;
                    if($d9 < 0.0) {
                        $d9 *= 4;
                    }
                    $d10 = $this->noise1[$k1] / 512;
                    $d11 = $this->noise2[$k1] / 512;
                    $d12 = ($this->noise3[$k1] / 10 + 1.0) / 2;
                    if($d12 < 0.0) {
                        $d8 = $d10;
                    } elseif($d12 > 1.0) {
                        $d8 = $d11;
                    } else {
                        $d8 = $d10 + ($d11 - $d10) * $d12;
                    }
                    $d8 -= $d9;
                    if($y > $ySize - 4) {
                        $d13 = (($y - ($ySize - 4)) / 3);
                        $d8 = $d8 * (1.0 - $d13) + -10 * $d13;
                    }
                    $this->noise[$k1] = $d8;
                    $k1++;
                }
            }
        }
    }

    public function pickBiome(int $x, int $z)
    {
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
        
        return $this->selector->pickBiome($x + $xNoise - 1, $z + $zNoise - 1);
    }
}
