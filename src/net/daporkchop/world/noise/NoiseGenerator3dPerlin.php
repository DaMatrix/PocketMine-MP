<?php
namespace net\daporkchop\world\noise;

class NoiseGenerator3dPerlin
{

    public $randomDX;

    public $randomDY;

    public $randomDZ;

    private $permutations = [];

    public function __construct($random)
    {
        $this->permutations = array(
            512
        );
        $this->randomDX = $random->nextFloat() * 255;
        $this->randomDY = $random->nextFloat() * 255;
        $this->randomDZ = $random->nextFloat() * 255;
        for ($i = 0; $i < 256; $i ++) {
            $this->permutations[$i] = $i;
        }
        // array of random values
        for ($j = 0; $j < 256; $j ++) {
            $k = $random->nextRange(0, 255 - $j) + $j;
            $l = $this->permutations[$j];
            $this->permutations[$j] = $this->permutations[$k];
            $this->permutations[$k] = $l;
            $this->permutations[$j + 256] = $this->permutations[$j];
        }
    }

    public function generateNoise(float $xPos, float $yPos, float $zPos) :array
    {
        $x = $xPos + $this->randomDX;
        $y = $yPos + $this->randomDY;
        $z = $zPos + $this->randomDZ;
        $intX = floor($x);
        $intY = floor($y);
        $intZ = floor($z);
        if ($x < $intX) {
            $intX --;
        }
        if ($y < $intY) {
            $intY --;
        }
        if ($z < $intZ) {
            $intZ --;
        }
        $p1 = $intX & 0xff;
        $p2 = $intY & 0xff;
        $p3 = $intZ & 0xff;
        $x -= $intX;
        $y -= $intY;
        $z -= $intZ;
        $fx = $x * $x * $x * ($x * ($x * 6 - 15) + 10);
        $fy = $y * $y * $y * ($y * ($y * 6 - 15) + 10);
        $fz = $z * $z * $z * ($z * ($z * 6 - 15) + 10);
        $a1 = $this->permutations[$p1] + $p2;
        $a2 = $this->permutations[$a1] + $p3;
        $a3 = $this->permutations[$a1 + 1] + $p3;
        $a4 = $this->permutations[$p1 + 1] + $p2;
        $a5 = $this->permutations[$a4] + $p3;
        $a6 = $this->permutations[$a4 + 1] + $p3;
        return $this->lerp($fz, $this->lerp($fy, $this->lerp($fx, $this->grad3d($this->permutations[$a2], $x, $y, $z), $this->grad3d($this->permutations[$a5], $x - 1.0, $y, $z)), $this->lerp($fx, $this->grad3d($this->permutations[$a3], $x, $y - 1.0, $z), $this->grad3d($this->permutations[$a6], $x - 1.0, $y - 1.0, $z))), $this->lerp($fy, $this->lerp($fx, $this->grad3d($this->permutations[$a2 + 1], $x, $y, $z - 1.0), $this->grad3d($this->permutations[$a5 + 1], $x - 1.0, $y, $z - 1.0)), $this->lerp($fx, $this->grad3d($this->permutations[$a3 + 1], $x, $y - 1.0, $z - 1.0), $this->grad3d($this->permutations[$a6 + 1], $x - 1.0, $y - 1.0, $z - 1.0))));
    }

    public final function lerp(float $d, float $d1, float $d2) : float
    {
        return $d1 + $d * ($d2 - $d1);
    }

    public final function grad2d(float $i, float $x, float $z): float
    {
        $j = $i & 0xf;
        $d2 = (1 - (($j & 8) >> 3)) * $x;
        $d3 = $j >= 4 ? $j != 12 && $j != 14 ? $z : $x : 0.0;
        return (($j & 1) != 0 ? - $d2 : $d2) + (($j & 2) != 0 ? - $d3 : $d3);
    }

    public final function grad3d(float $i, float $x, float $y, float $z): float
    {
        $j = $i & 0xf;
        $d3 = $j >= 8 ? $y : $x;
        $d4 = $j >= 4 ? $j != 12 && $j != 14 ? $z : $x : $y;
        return (($j & 1) != 0 ? - $d3 : $d3) + (($j & 2) != 0 ? - $d4 : $d4);
    }

    public function generateNoise2($d, $d1)
    {
        return generateNoise($d, $d1, 0.0);
    }

    public function generateNoiseArray($array, $xPos, $yPos, $zPos, $xSize, $ySize, $zSize, float $gridX, float $gridY, float $gridZ, float $a): array
    {
        if ($ySize == 1) {
            $index = 0;
            $amplitude = 1.0 / $a;
            for ($dx = 0; $dx < $xSize; $dx ++) {
                $x = ($xPos + $dx) * $gridX + $this->randomDX;
                $intX = floor($x);
                if ($x < $intX) {
                    $intX --;
                }
                $k4 = $intX & 0xff;
                $x -= $intX;
                // x^3(6x^2-15x+10)
                $d17 = $x * $x * $x * ($x * ($x * 6 - 15) + 10);
                for ($dz = 0; $dz < $zSize; $dz ++) {
                    $z = ($zPos + $dz) * $gridZ + $this->randomDZ;
                    $intZ = floor($z);
                    if ($z < $intZ) {
                        $intZ --;
                    }
                    $l5 = $intZ & 0xff;
                    $z -= $intZ;
                    // x^3(6x^2-15x+10)
                    $d21 = $z * $z * $z * ($z * ($z * 6 - 15) + 10);
                    $l = $this->permutations[$k4] + 0;
                    $j1 = $this->permutations[$l] + $l5;
                    $k1 = $this->permutations[$k4 + 1] + 0;
                    $l1 = $this->permutations[$k1] + $l5;
                    $d9 = $this->lerp($d17, $this->grad2d($this->permutations[$j1], $x, $z), $this->grad3d($this->permutations[$l1], $x - 1.0, 0.0, $z));
                    $d11 = $this->lerp($d17, $this->grad3d($this->permutations[$j1 + 1], $x, 0.0, $z - 1.0), $this->grad3d($this->permutations[$l1 + 1], $x - 1.0, 0.0, $z - 1.0));
                    $value = $this->lerp($d21, $d9, $d11);
                    $array[$index ++] += $value * $amplitude;
                }
            }
            
            return $array;
        }
        
        $i1 = 0;
        $amplitude = 1.0 / $a;
        $i2 = - 1;
        $d13 = 0.0;
        $d15 = 0.0;
        $d16 = 0.0;
        $d18 = 0.0;
        for ($dx = 0; $dx < $xSize; $dx ++) {
            $x = ($xPos + $dx) * $gridX + $this->randomDX;
            $intX = floor($x);
            if ($x < $intX) {
                $intX --;
            }
            $i6 = $intX & 0xff;
            $x -= $intX;
            $d22 = $x * $x * $x * ($x * ($x * 6 - 15) + 10);
            for ($dz = 0; $dz < $zSize; $dz ++) {
                $z = ($zPos + $dz) * $gridZ + $this->randomDZ;
                $k6 = floor($z);
                if ($z < $k6) {
                    $k6 --;
                }
                $l6 = $k6 & 0xff;
                $z -= $k6;
                $d25 = $z * $z * $z * ($z * ($z * 6 - 15) + 10);
                for ($dy = 0; $dy < $ySize; $dy ++) {
                    $y = ($yPos + $dy) * $gridY + $this->randomDY;
                    // farlands don't exist on y axis
                    $j7 = floor($y);
                    if ($y < $j7) {
                        $j7 --;
                    }
                    $k7 = $j7 & 0xff;
                    $y -= $j7;
                    $d27 = $y * $y * $y * ($y * ($y * 6 - 15) + 10);
                    if ($dy == 0 || $k7 != $i2) {
                        $i2 = $k7;
                        $j2 = $this->permutations[$i6] + $k7;
                        $k2 = $this->permutations[$j2] + $l6;
                        $l2 = $this->permutations[$j2 + 1] + $l6;
                        $i3 = $this->permutations[$i6 + 1] + $k7;
                        $k3 = $this->permutations[$i3] + $l6;
                        $l3 = $this->permutations[$i3 + 1] + $l6;
                        $d13 = $this->lerp($d22, $this->grad3d($this->permutations[$k2], $x, $y, $z), $this->grad3d($this->permutations[$k3], $x - 1.0, $y, $z));
                        $d15 = $this->lerp($d22, $this->grad3d($this->permutations[$l2], $x, $y - 1.0, $z), $this->grad3d($this->permutations[$l3], $x - 1.0, $y - 1.0, $z));
                        $d16 = $this->lerp($d22, $this->grad3d($this->permutations[$k2 + 1], $x, $y, $z - 1.0), $this->grad3d($this->permutations[$k3 + 1], $x - 1.0, $y, $z - 1.0));
                        $d18 = $this->lerp($d22, $this->grad3d($this->permutations[$l2 + 1], $x, $y - 1.0, $z - 1.0), $this->grad3d($this->permutations[$l3 + 1], $x - 1.0, $y - 1.0, $z - 1.0));
                    }
                    $d28 = $this->lerp($d27, $d13, $d15);
                    $d29 = $this->lerp($d27, $d16, $d18);
                    $value = $this->lerp($d25, $d28, $d29);
                    $array[$i1 ++] += $value * $amplitude;
                }
            }
        }
        
        return $array;
    }
}
