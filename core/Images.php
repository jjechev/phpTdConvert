<?php

class Image
{

    private $initialized = false;

    static function init()
    {
        
    }

    static function upload()
    {
        
    }

    static function image($name, $w, $h)
    {
        
    }

    public static function cropImage($thumbnail_width, $thumbnail_height, $imgSrc, $type2, $imgDest)
    {  //$imgSrc is a FILE - Returns an image resource.
        //getting the image dimensions 
        list($width_orig, $height_orig) = getimagesize($imgSrc);
        $myImage = imagecreatefromjpeg($imgSrc);
        $ratio_orig = $width_orig / $height_orig;

        if ($thumbnail_width / $thumbnail_height > $ratio_orig)
        {
            $new_height = $thumbnail_width / $ratio_orig;
            $new_width = $thumbnail_width;
        }
        else
        {
            $new_width = $thumbnail_height * $ratio_orig;
            $new_height = $thumbnail_height;
        }

        $x_mid = $new_width / 2;  //horizontal middle
        $y_mid = $new_height / 2; //vertical middle

        $process = imagecreatetruecolor(round($new_width), round($new_height));

        imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
        $thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
        imagecopyresampled($thumb, $process, 0, 0, ($x_mid - ($thumbnail_width / 2)), ($y_mid - ($thumbnail_height / 2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

        //   imagedestroy($process);
        //   imagedestroy($myImage);
        imagejpeg($thumb, $imgDest, 100);
        return 1;
    }

    public static function smart_resize_image($file, $width = 0, $height = 0, $file2, $proportional = false, $use_linux_command = false, $crop = 0, $fon = 0)
    {
        if ($height <= 0 && $width <= 0)
        {
            return false;
        }

        $info = getimagesize($file);
        $image = '';

        $final_width = 0;
        $final_height = 0;
        list($width_old, $height_old) = $info;

        if ($proportional)
        {

            $proportion = $width_old / $height_old;

            if ($width > $height && $height != 0)
            {
                $final_height = $height;
                $final_width = $final_height * $proportion;
            }
            elseif ($width < $height && $width != 0)
            {
                $final_width = $width;
                $final_height = $final_width / $proportion;
            }
            elseif ($width == 0)
            {
                $final_height = $height;
                $final_width = $final_height * $proportion;
            }
            elseif ($height == 0)
            {
                $final_width = $width;
                $final_height = $final_width / $proportion;
            }
            else
            {
                $final_width = $width;
                $final_height = $height;
            }
        }
        else
        {
            $final_width = ( $width <= 0 ) ? $width_old : $width;
            $final_height = ( $height <= 0 ) ? $height_old : $height;
        }

        switch ($info[2])
        {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($file);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($file);
                break;
            default:
                return false;
        }
//	if ($fon)	$image_resized = imagecreatetruecolor($width , $height);
        $image_resized = imagecreatetruecolor($final_width, $final_height);   // originalnia red

        imagecolortransparent($image_resized, imagecolorallocate($image_resized, 0, 0, 0));
        imagealphablending($image_resized, false);
        imagesavealpha($image_resized, true);

//		 $x1 = ($width-$final_width)/2;														// za 4ernia kvadrat
//		 $y1 = ($height-$final_height)/2;

        if (!$crop)
            imagecopyresampled($image_resized, $image, $x1, $y1, 0, 0, $final_width, $final_height, $width_old, $height_old);
        else
        {
            if ($height_old > $width_old)
            {
                $p = $height_old / $width_old;
                imagecopyresampled($image_resized, $image, $x1, $y1, 250, 0, $final_width, $final_height, $width_old - 250, $height_old);
            }
            else
                imagecopyresampled($image_resized, $image, $x1, $y1, 0, 0, $final_width, $final_height, $width_old, $height_old);
        }

        switch ($info[2])
        {
            case IMAGETYPE_GIF:
                imagegif($image_resized, $file2);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($image_resized, $file2);
                break;
            case IMAGETYPE_PNG:
                imagepng($image_resized, $file2);
                break;
            default:
                return false;
        }

        return true;
    }

}

// END OF CLASS
