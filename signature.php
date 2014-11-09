<?php

/**
 * @author : Kinshuk Bairagi < me@kinshuk.in >
 */

/**
 * Configurations
 */

// 500px Sizes
$thumbnailWidth = 140; 
$thumbnailHeight = 140;
$defaultNumberOfImages = 5;
$consumerKey = "" ; // get your consumerr key from www.500px.com
$sort = "rating" ;
$shuffle = true;
$outputThumbSize = 100;

/**
 * User Configurations Complete. Change Below at your own risk.
 */

$username = $_GET["user"];
if(empty($username)){
	header('HTTP/1.0 400 Bad Request');
	die("Username cannot be empty");
}

$filename = "output/$username.jpg";

$ftime = file_exists($filename) ? filemtime($filename): 0;
$now = time();
$diff = $now - $ftime;

if ((file_exists($filename)) and ( $diff < 3600) and (!$_GET['override'])){

	header('Content-Type: image/jpeg');
	header('Content-transfer-encoding: binary');
	header('Content-Disposition: filename="Signature.jpg"');    

	// Serve the cached file.
	readfile($filename);

} else {

	$show  = empty($_GET['show'])? $defaultNumberOfImages : $_GET['show'];

	$totalWidth = $thumbnailWidth * $show;
	$newimg = imagecreatetruecolor($totalWidth,$thumbnailHeight);

	$url = "https://api.500px.com/v1/photos?feature=user&username=$username&sort=$sort&sort_direction=desc&rpp=50&image_size=2&consumer_key=$consumerKey";
	$json = file_get_contents($url);
	$data = json_decode($json);


	$photos = $data->photos ;
	if($shuffle) { 
		shuffle($photos);
	}

	$count = 0;
	foreach($photos as $row){
		$img = imagecreatefromjpeg($row->image_url);
		$pos = $thumbnailWidth * $count;
		imagecopy($newimg,$img,$pos,0,0,0,$thumbnailWidth,$thumbnailHeight);
		imagedestroy($img);
		$count++;
		if($count > $show)
			break;
	}

	$height = empty($_GET['height'])?  $outputThumbSize : $_GET['height'];

	$scale = $height / $thumbnailHeight;
	$new_width = floor($scale*$totalWidth);
	$new_height = floor($scale*$thumbnailHeight);
	$tmp_img = imagecreatetruecolor($new_width,$new_height);
            // gd 2.0.1 or later: imagecopyresampled
            // gd less than 2.0: imagecopyresized
	if(function_exists(imagecopyresampled)) {
		imagecopyresampled($tmp_img, $newimg, 0,0,0,0,$new_width,$new_height,$totalWidth,$thumbnailHeight);
	} else {
		imagecopyresized($tmp_img, $newimg, 0,0,0,0,$new_width,$new_height,$totalWidth,$thumbnailHeight);
	}

	imagedestroy($newimg);
	$newimg = $tmp_img;

	imagejpeg($newimg,$filename, 100);
	chmod($filename,0644);

	header('Content-Type: image/jpeg');
	header('Content-transfer-encoding: binary');
	header('Content-Disposition: filename="Signature.jpg"');

	imagejpeg($newimg);
	imagedestroy($newimg);

}

