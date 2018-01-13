<?php
namespace net\daporkchop\world;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\level\generator\Generator;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\generator\biome\BiomeSelector;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\GroundCover;
use pocketmine\level\generator\populator\Ore;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class WorldGenerator extends Generator
{

    private $populators = [];

    /** @var ChunkManager */
    private $level;

    /** @var Random */
    private $random;

    /** @var int */
    private $waterHeight = 63;

    private $generationPopulators = [];

    /** @var BiomeSelector */
    private $selector;

    private $noiseGen1;

    private $noiseGen2;

    private $noiseGen3;

    private $noiseGen4;

    private $noiseGen5;

    private $noiseGen6;

    private $noiseGen7;

    private $treeNoise;

    private $noise = [];

    private $sandNoise = array(
        256
    );

    private $gravelNoise = array(
        256
    );

    private $stoneNoise = array(
        256
    );

    private $noise1 = [];

    private $noise2 = [];

    private $noise3 = [];

    private $noise6 = [];

    private $noise7 = [];

    public function init(ChunkManager $level, Random $random)
    {
        $this->level = $level;
        $this->random = $random;
        $this->random->setSeed($this->level->getSeed());
        $this->noise1 = new Simplex($this->random, 16, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->noise2 = new Simplex($this->random, 16, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->noise3 = new Simplex($this->random, 8, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->noise4 = new Simplex($this->random, 4, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->noise5 = new Simplex($this->random, 4, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->noise6 = new Simplex($this->random, 10, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->noise7 = new Simplex($this->random, 16, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->treeNoise = new Simplex($this->random, 8, 1 / 4);
        $this->random->setSeed($this->level->getSeed());
        $this->random->setSeed($this->level->getSeed());
        $this->selector = new BiomeSelector($this->random, function ($temperature, $rainfall) {
            if ($rainfall < 0.25) {
                if ($temperature < 0.7) {
                    return Biome::OCEAN;
                } elseif ($temperature < 0.85) {
                    return Biome::RIVER;
                } else {
                    return Biome::SWAMP;
                }
            } elseif ($rainfall < 0.60) {
                if ($temperature < 0.25) {
                    return Biome::ICE_PLAINS;
                } elseif ($temperature < 0.75) {
                    return Biome::PLAINS;
                } else {
                    return Biome::DESERT;
                }
            } elseif ($rainfall < 0.80) {
                if ($temperature < 0.25) {
                    return Biome::TAIGA;
                } elseif ($temperature < 0.75) {
                    return Biome::FOREST;
                } else {
                    return Biome::BIRCH_FOREST;
                }
            } else {
                // FIXME: This will always cause River to be used since the rainfall is always greater than 0.8 if we
                // reached this branch. However I don't think that substituting temperature for rainfall is correct given
                // that mountain biomes are supposed to be pretty cold.
                if ($rainfall < 0.25) {
                    return Biome::MOUNTAINS;
                } elseif ($rainfall < 0.70) {
                    return Biome::SMALL_MOUNTAINS;
                } else {
                    return Biome::RIVER;
                }
            }
        }, Biome::getBiome(Biome::OCEAN));
        
        $this->selector->addBiome(Biome::getBiome(Biome::OCEAN));
        $this->selector->addBiome(Biome::getBiome(Biome::PLAINS));
        $this->selector->addBiome(Biome::getBiome(Biome::DESERT));
        $this->selector->addBiome(Biome::getBiome(Biome::MOUNTAINS));
        $this->selector->addBiome(Biome::getBiome(Biome::FOREST));
        $this->selector->addBiome(Biome::getBiome(Biome::TAIGA));
        $this->selector->addBiome(Biome::getBiome(Biome::SWAMP));
        $this->selector->addBiome(Biome::getBiome(Biome::RIVER));
        $this->selector->addBiome(Biome::getBiome(Biome::ICE_PLAINS));
        $this->selector->addBiome(Biome::getBiome(Biome::SMALL_MOUNTAINS));
        $this->selector->addBiome(Biome::getBiome(Biome::BIRCH_FOREST));
        
        $this->selector->recalculate();
        
        $cover = new GroundCover();
        $this->generationPopulators[] = $cover;
        
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
    }

    public function __construct(array $options = [])
    {}

    public function getName(): string
    {
        return "porkworld";
    }

    public function getSettings(): array
    {
        return [];
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

    public function generateChunk(int $chunkX, int $chunkZ)
    {
        $this->random->setSeed(0xdeadbeef ^ ($chunkX << 8) ^ $chunkZ ^ $this->level->getSeed());
        $chunk = $this->level->getChunk($chunkX, $chunkZ);
        
        $byte0 = 4;
        $oceanHeight = 64;
        $k = byte0 + 1;
        $b2 = 17;
        $l = byte0 + 1;
        $this->noise = $this->initNoiseField($this->noise, $x * $byte0, 0, $z * $byte0, $k, $b2, $l);
        
        for ($x = 0; $x < 16; ++ $x) {
            for ($z = 0; $z < 16; ++ $z) {
                $height = ($this->mainPerlinNoise->getNoise2D($chunkX * 16 + $x, $chunkZ * 16 + $z) * 128) + 128;
                for ($y = 0; $y < 256; ++ $y) {
                    $chunk->setBlockId($x, $y, $z, Block::STONE);
                }
                
                /*
                 * for ($y = 0; $y < 128; ++ $y) {
                 * if ($y === 0) {
                 * $chunk->setBlockId($x, $y, $z, Block::BEDROCK);
                 * continue;
                 * }
                 * $noiseValue = $noise[$x][$z][$y] - 1 / $smoothHeight * ($y - $smoothHeight - $minSum);
                 *
                 * if ($noiseValue > 0) {
                 * $chunk->setBlockId($x, $y, $z, Block::STONE);
                 * } elseif ($y <= $this->waterHeight) {
                 * $chunk->setBlockId($x, $y, $z, Block::STILL_WATER);
                 * }
                 * }
                 */
            }
        }
        
        foreach ($this->generationPopulators as $populator) {
            $populator->populate($this->level, $chunkX, $chunkZ, $this->random);
        }
    }

    public function initNoiseField($array, $xPos, $yPos, $zPos, $xSize, $ySize, $zSize)
    {
        $array = array($xSize * $ySize * $zSize);
        $d0 = 684.412;
        $d1 = 684.412;
        
        $this->noise6 = Generator::getFastNoise2D($this->noiseGen6, $xSize, $zSize, 1.121, 1.121, $xPos, $zPos);
        $this->noise7 = Generator::getFastNoise2D($this->noiseGen7, $xSize, $zSize, 1.121, 1.121, $xPos, $zPos);
        
        return $array;
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
