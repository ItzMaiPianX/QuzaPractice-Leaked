<?php

namespace owonico\skin\libs\converter;

use owonico\skin\converter\libs\ArrayUtils as Arr;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use RuntimeException;

final class PngConverter{
    private function __construct(){
    }

    /** @param resource $image */
    public static function toSkinImage($image) : SkinImage{
        $height = imagesy($image);
        $width = imagesx($image);

        $skinData = "";
        for($y = 0; $y < $height; $y++){
            for($x = 0; $x < $width; $x++){
                $rgba = imagecolorat($image, $x, $y);
                $a = (127 - (($rgba >> 24) & 0x7F)) * 2;
                $r = ($rgba >> 16) & 0xff;
                $g = ($rgba >> 8) & 0xff;
                $b = $rgba & 0xff;
                $skinData .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        imagedestroy($image);
        return new SkinImage($height, $width, $skinData);
    }

    public static function toSkinImageFromFile(string $filepath) : SkinImage{
        $resource = imagecreatefrompng($filepath);
        if($resource === false){
            throw new RuntimeException("Failed to read $filepath");
        }

        return self::toSkinImage($resource);
    }

    /** @return resource|null */
    public static function toPng(SkinImage $skinImage){
        $width = $skinImage->getWidth();
        $height = $skinImage->getHeight();
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagesavealpha($image, true);

        Arr::from(str_split($skinImage->getData()))
            ->map(function(string $char) : int{ return ord($char); })
            ->chunk(4)
            ->forEach(function(int $index, array $colorChunk) use ($image, $width){
                $colorChunk[] = 127 - intdiv(array_pop($colorChunk), 2);
                imagesetpixel($image, $index % $width, (int) ($index / $width), imagecolorallocatealpha($image, ...$colorChunk));
            });
        return $image;
    }

    /** @return resource|null */
    public static function toPngFromSkinData(SkinData $skinData){
        return self::toPng($skinData->getSkinImage());
    }
}