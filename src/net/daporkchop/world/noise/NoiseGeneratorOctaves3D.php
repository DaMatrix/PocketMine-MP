<?php
namespace net\daporkchop\world\noise;

class NoiseGeneratorOctaves3D {
    
    private $generatorCollection = [];
    private $octaves;
    
    public function __construct($i) {
        $this->octaves = $i;
        $this->generatorCollection = array($i);
        for($j = 0; $j < $i; $j++) {
            $this->generatorCollection[$j] = new NoiseGenerator3dPerlin();
        }
    }
    
    public function generateNoise(float $d, float $d1): float {
        $d2 = 0.0;
        $d3 = 1.0;
        for($i = 0; $i < $octaves; $i++) {
            $d2 += $generatorCollection[$i].generateNoise($d * $d3, $d1 * $d3) / $d3;
            $d3 /= 2;
        }
        
        return $d2;
    }
    
    public function generateNoiseArray($x, $y, $z, $xSize, $ySize, $zSize, float $gridX, float $gridY, float $gridZ): array {
        $ad = array(($xSize + 0) * ($ySize + 0) * ($zSize + 0));
        $frequency = 1.0;
        for($i1 = 0; $i1 < $this->octaves; $i1++) {
            $ad = $this->generatorCollection[$i1]->generateNoiseArray($x, $y, $z, $xSize, $ySize, $zSize, $gridX * $frequency, $gridY * $frequency, $gridZ * $frequency, $frequency);
            $frequency /= 2;
        }
        
        return $ad;
    }
    
    public function generateNoiseArray2($x, $z, $xSize, $zSize,
        $gridX, $gridZ, $d2): array {
            return $this->generateNoiseArray($x, 10, $z, $xSize, 1, $zSize, $gridX, 1.0, $gridZ);
    }
}
