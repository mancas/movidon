<?php

namespace Movidon\ImageBundle\Util;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Dumper;

class FileHelper
{
    const PATH = "/var/www/html/cartujano/web/uploads";
    const TMP_IMAGE_PATH = "/tmp/";
    const TMP_IMAGE_NAME = "fetched_image.jpg";

    public static function uploadAndReplaceFile($objAnterior, $objNuevo, $subdirectory, $idObjetoPrincipal, $subNombreArchivo = null)
    {
        if ($objNuevo) {
            $directoryDestino = self::PATH . '/' . $subdirectory . '/' . self::getDirectoryNameFromId($idObjetoPrincipal);
            if ($objAnterior) {
                $pathObjAnterior = $directoryDestino . '/' . $objAnterior;
                @unlink($pathObjAnterior);
            }
            $nombreArchivo = self::getFileName($objNuevo, $idObjetoPrincipal, $subNombreArchivo);
            $objNuevo->move($directoryDestino, $nombreArchivo);

            return $nombreArchivo;
        }
    }
    
    public static function getFileName($newObject, $idObjetoPrincipal, $subFileName = null)
    {
        $type = $newObject->getExtension();
        if ($type == "") {
            $type = explode('.', $newObject->getClientOriginalName());
            $type = strtolower($type[count($type) - 1]);
        }
        if (!$subFileName) {
            $fileName = $idObjetoPrincipal . $subFileName . '.' . $type;
        } else {
            $fileName = $idObjetoPrincipal . '-' . $subFileName . '.' . $type;
        }

        return $fileName;
    }

    public static function getDirectoryNameFromId($id)
    {
        return sprintf('%03s', substr($id, -3));
    }

    public static function removeFile($obj, $subdirectory, $idObjetoPrincipal, $subNombreArchivo = null)
    {
        $path = self::PATH . '/' . $subdirectory . '/'
                . self::getDirectoryNameFromId($idObjetoPrincipal) . '/'
                . $obj;
        @unlink($path);
    }

    public static function removeFileFromDirectory($objName, $directory)
    {
        $path = self::PATH . '/' . $directory . '/' . $objName;
        @unlink($path);
    }

    public static function executeConvert($path, $originalFilePath, $width, $height, $quality, $crop = false)
    {
        $size = getimagesize($originalFilePath);

        if ($size === false) {
            throw new \Exception("No puede obtenerse las medidas de $originalFilePath");
        }
        $scale = min($size[0] / $width, $size[1] / $height);
        $dim = self::getResizeValues($size[0], $size[1], $width, $height, $crop);
        if ($scale <= 0) {
            throw new \Exception("Medidas de images erroneas en: $originalFilePath: " . implode($size, ", "));
        }
        $oldWidth = $size[0] / $scale;
        $oldHeight = $size[1] / $scale;

        $command = "convert -resize " . $dim[0] . "x" . $dim[1] . " -background none "
                . $originalFilePath . " " . $path;

        exec($command, $output, $result);

        if($crop){
            self::cropImage($path, $width, $height);
        }

        if ($result != 0) {
            throw new \Exception("Conversion de ImageMagick erronea " . $result);
        }
    }

    /**
     * Get the width and height values of a new image scaled from the original limit by max width and height values
     * See http://stackoverflow.com/questions/5222711/image-resize-in-c-sharp-algorith-to-determine-resize-dimensions-height-and-wi
     *
     * @param integer $originalWidth
     * @param integer $originalHeight
     * @param integer $maxWidth
     * @param integer $maxHeight
     *
     * @return array<width, height> $dimensions
     */
    public static function getResizeValues($originalWidth, $originalHeight, $maxWidth, $maxHeight, $crop)
    {
        $width = $originalWidth;
        $height = $originalHeight;

        if ($crop) {
            $ratio = $originalWidth / $originalHeight;
            if($maxWidth > $maxHeight){
                if($ratio >= 1.6){//imagenes muy panorámicas
                    $maxWidth = $maxWidth + ($width/10);    
                }
                $height = $height * $maxWidth / $width;
                $width = $maxWidth;
            }else{
                if($ratio <= 0.6){//imagenes muy verticales
                    $maxHeight = $maxHeight  + ($height/10);    
                }
                $width = $width * $maxHeight / $height;
                $height = $maxHeight;
            }

        }else{

            if ($width > $maxWidth) {
                $height = $maxWidth / $width * $height;
                $width = $maxWidth;
            }

            if ($height > $maxHeight) {
                $width = $maxHeight / $height * $width;
                $height = $maxHeight;
            }

        }
        
        return array(round($width), round($height));

    }

    public static function resizeAndSaveImage($image, $width, $height, $directory, $sufix, $crop=false)
    {
        $fileName = $directory . '/' . $image;

        $auxArray = explode('.', $image);
        $type = $auxArray[1];

        list($originalWidth, $originalHeight) = getimagesize($fileName);
        list($_width, $_height) = self::intelligentResizeThumbnail($width, $height, $originalWidth, $originalHeight, $crop);

        $thumb = imagecreatetruecolor($_width, $_height);
        imagesavealpha($thumb, true);
        imagealphablending($thumb, false);
        $white = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefill($thumb, 0, 0, $white);

        if (($type == 'jpg') | ($type == 'jpeg')) {
            $original = imagecreatefromjpeg($fileName);
            imagecopyresampled($thumb, $original, 0, 0, 0, 0, $_width, $_height, $originalWidth, $originalHeight);
            $fileName = $directory . '/' . self::generateThumbFileName($image, $sufix);
            imagejpeg($thumb, $fileName, 100);
        } elseif ($type == 'png') {
            $original = imagecreatefrompng($fileName);
            imagecopyresampled($thumb, $original, 0, 0, 0, 0, $_width, $_height, $originalWidth, $originalHeight);
            $fileName = $directory . '/' . self::generateThumbFileName($image, $sufix);
            imagepng($thumb, $fileName, 0, 100);
        } elseif ($type=='gif') {
            $original = imagecreatefromgif($fileName);
            imagecopyresampled($thumb, $original, 0, 0, 0, 0, $_width, $_height, $originalWidth, $originalHeight);
            $fileName = $directory . '/' . self::generateThumbFileName($image, $sufix);
            imagegif($thumb, $fileName, 100);
        }

        if($crop){
            self::cropImage($fileName, $width, $height);
        }

    }

    public static function intelligentResizeThumbnail($width, $height, $originalWidth, $originalHeight, $crop)
    {
        /*if ($maximize) {
            $scale1 = $originalWidth / $width;
            $scale2 = $originalHeight / $height;
            if ($scale1 > $scale2) {
                $_width = $originalWidth / $scale2;
                $_height = $height;
            } else {
                $_width = $width;
                $_height = $originalHeight / $scale1;
            }
        } else {
            if ($originalWidth > $originalHeight) {
                $_porcentaje = $originalWidth / $width;
                $_height = ceil($originalHeight / $_porcentaje);
                $_width = $width;
            } else {
                $_porcentaje = $originalHeight / $height;
                $_width = ceil($originalWidth / $_porcentaje);
                $_height = $height;
            }
        }

        return array($_width, $_height);*/

        return self::getResizeValues($originalWidth, $originalHeight, $width, $height, $crop);

    }

    public static function cropImage($file, $width, $height){

        //convert <imagen original> -gravity center -crop <ancho>x<largo>+0+0 <imagen-final>
        $command = "convert ".$file." -gravity center -crop " . $width . "x" . $height . "+0+0 " . $file;
        exec($command, $output, $result);

        if ($result != 0) {
            throw new \Exception("Conversion de ImageMagick erronea " . $result);
        }

    }

    public static function generateThumbFileName($image, $sufix)
    {
        $auxArray = explode('.', $image);

        return $fileName = $auxArray[0] . '-'.$sufix.'.' . $auxArray[1];
    }

    public static function escapeUrl($url)
    {
        $url = urlencode($url);
        $url = str_replace("%2F", "/", $url);
        $url = str_replace("%3A", ":", $url);
        $url = str_replace('+', '%20', $url);

        return $url;
    }

    public static function ImageCrop($originalFilePath, $width, $height, $xoffset, $yoffset)
    {
        $command = "convert -crop " . $width . "x" . $height . "+" . $xoffset
                . "+" . $yoffset . " " . $originalFilePath . " -quality 100 +repage "
                . $originalFilePath;
        exec($command, $output, $result);
    }

    public static function getWidthFromWebFile($webPath)
    {
        return self::getSize(self::PATH.'/'.$webPath, 0);
    }

    public static function getHeightFromWebFile($webPath)
    {
        return self::getSize(self::PATH.'/'.$webPath, 1);
    }

    public static function getSize($imgPath, $key)
    {
        $size = @getimagesize($imgPath);

        return $size[$key];
    }

    /**
     * Download the image from the URL and save it in the given path
     *
     * @param string $imgUrl A valid URL
     * @param string $path Path where to save in
     */
    public static function fetch($imgUrl)
    {
        $ch = curl_init(self::escapeUrl($imgUrl));
        $fp = fopen(self::TMP_IMAGE_PATH.self::TMP_IMAGE_NAME, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
