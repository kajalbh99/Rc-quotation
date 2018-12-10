<?php
	//error_reporting(0);
    $servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "exif_data";

	require_once "vendor/autoload.php";
    use GoogleCloudVisionPHP\GoogleCloudVision;
    // Add your key
    $key = "AIzaSyDoZfEKqPm5RTmQ2gg5KROFyz5N5EzDuz0";
	
	$realPath = realpath(dirname(__FILE__));
	$url = "http://www.travelwithstar.com/crop-number-plate";
	
	if(is_array($_FILES)) {
	if(is_uploaded_file($_FILES['file']['tmp_name'])) {
	$sourcePath = $_FILES['file']['tmp_name'];
	$targetPath = $realPath."/images/".$_FILES['file']['name'];
	$sourcePath = str_replace(" ", "", $sourcePath);
	$targetPath = str_replace(" ", "", $targetPath);
    if(exif_read_data($sourcePath))
	{
		$exif = exif_read_data($sourcePath);
  		
		function resize($width, $height, $last_id){
		  /* Get original image x y*/
		  list($w, $h) = getimagesize($_FILES['file']['tmp_name']);
		  /* calculate new image size with ratio */
		  $ratio = max($width/$w, $height/$h);
		  $h = ceil($height / $ratio);
		  $x = ($w - $width / $ratio) / 2;
		  $w = ceil($width / $ratio);
		  /* new file name */
		  $last_id = $last_id + 1;
		  $path = 'UploadedImages/'.$last_id."_Google64.jpg";
		  /* read binary data from image file */
		  $imgString = file_get_contents($_FILES['file']['tmp_name']);
		  /* create image from string */
		  $image = imagecreatefromstring($imgString);
		  $tmp = imagecreatetruecolor($width, $height);
		  imagecopyresampled($tmp, $image, 0, 0, $x, 0, $width, $height, $w, $h);
		  /* Save image */
		  switch ($_FILES['file']['type']) {
			case 'image/jpeg':
			  imagejpeg($tmp, $path, 100);
			  break;
			case 'image/png':
			  imagepng($tmp, $path, 0);
			  break;
			case 'image/gif':
			  imagegif($tmp, $path);
			  break;
			default:
			  exit;
			  break;
		  }
		  return $path;
		  /* cleanup memory */
		  imagedestroy($image);
		  imagedestroy($tmp);
		}
		$last_id = 5;
		 if( '1024' <= $exif['COMPUTED']['Height'] ){
			 $w = 1024;
			 $h = 768;
			 resize($w, $h, $last_id);
		}
		if( '1024' <= $exif['COMPUTED']['Width'] && '1024' <= $exif['COMPUTED']['Width'] ){
			 $w = 1024;
			 $h = 768;
			 resize($w, $h, $last_id);
		}
		if( '1024' <= $exif['COMPUTED']['Width'] ){
			 $w = 768;
			 $h = 1024;
			 resize($w, $h, $last_id);
		} 
	

		if(move_uploaded_file($sourcePath,$targetPath))
		{
			?>
			<h3>Uploaded Image:</h3>
			<img class="image-preview upload-preview" src="<?php echo str_replace(" ","",$url."/images/".$_FILES['file']['name']); ?>"  />
			<?php

			
			function getGps($exifCoord, $hemi) {
				$degrees = count($exifCoord) > 0 ? gps2Num($exifCoord[0]) : 0;
				$minutes = count($exifCoord) > 1 ? gps2Num($exifCoord[1]) : 0;
				$seconds = count($exifCoord) > 2 ? gps2Num($exifCoord[2]) : 0;
				$flip = ($hemi == 'W' or $hemi == 'S') ? - 1 : 1;
				return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

			}

			function gps2Num($coordPart) {
				$parts = explode('/', $coordPart);
				if (count($parts) <= 0)
					return 0;

				if (count($parts) == 1)
					return $parts[0];
				return floatval($parts[0]) / floatval($parts[1]);
			}
			
		   if(isset($exif["GPSLongitude"]))
			{
				$lon = getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
				$lat = getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
				//echo "GPS Latitude :".$lat."</br>"; 
				//echo "GPS Longitude :".$lon."</br>"; 
			}
			else
			{
				//echo "Not GPS";
			}
			
			
			
			$Orientation = '';
			if(!empty($exif['Orientation']))
			{
				$Orientation = $exif['Orientation'];
			}
			
			$longnitude= '';
			if(!empty($lon))
			{
				$longnitude = $lon; 
			}
			
			$latitude= '';
			if(!empty($lat))
			{
				$latitude = $lat; 
			}
				
			$description= '';
			if(!empty($exif['ImageDescription']))
			{
				$description = $exif['ImageDescription']; 
			}
			
			$date = date("y-m-d h:i:s",$exif['FileDateTime']);
			$size = $exif['FileSize']/1024;
			$formattedNum = number_format($size, 2);
			$kbsize = $formattedNum."Kb";
			
			  $gcv = new GoogleCloudVision();
			  $gcv->setKey($key);
			  $gcv->setImage($targetPath);
			  $gcv->addFeatureLabelDetection(1);		  
			  $data = $gcv->request();

			  $Vehicle = $data['responses'][0]['labelAnnotations'][0]['description'];
			  if($Vehicle == 'car' ||$Vehicle == 'bus' ||$Vehicle == 'truck')
			  {
				  $VehiceType = $Vehicle;
			  }else{
				  $VehiceType = "car,bus,truck";
			  }
			  //echo "vhi==".$VehiceType;

			$gcv->addFeatureImageProperty(1);
			$colors = $gcv->request();
			$imagecolor = $colors['responses'][0]['imagePropertiesAnnotation']['dominantColors']['colors'][0]['color'];
			//print_r($imagecolor);
			
		}
	
		$gcv = new GoogleCloudVision();
		$gcv->setKey($key);
		$gcv->setImage($targetPath);
		$gcv->addFeatureOCR(1);
		$response = $gcv->request();
		
		if(count($response['responses'][0]) > 0)
		{	
			$textAnnotations = $response['responses'][0]['textAnnotations'];
			
			foreach($textAnnotations as $key=> $value){
				$vertices[] = $value['boundingPoly']['vertices'];
				$vertices[$key]['description'] = $value['description'];
			}			
			//print_r($vertices);
			array_shift($vertices);
			//print_r($vertices); die;
			
			foreach($vertices as $key => $item)
			{  
				foreach($item as $value){
					if(isset($value['y'])){
						$y_index[] = $value['y'];							
					}
				}
				$y[] = array_unique($y_index);
				foreach($y as $index){
					foreach($index as $value){
						$arr[] = group($value,$vertices);					
					}								
				}
			}
			
			$diff1 = 1;
			$diff2 = 1;
			$diff3 = 1;
			$x1=0;$x2=0;$x3=0;$y1=0;$y2=0;$y3=0;$width_new1=0;$width_new2=0;$width_new3=0;$height_new1=0;$height_new2=0;$height_new3=0;
			foreach($arr as $key => $coordinates){
				
				$X = array();
				$Y = array();
				$description = array();
				if(count($coordinates)>0){
					foreach($coordinates as $value){
						if( isset($value['x']) && isset($value['y']) ){
							$X[]= $value['x'];
							$Y[]= $value['y'];
						}
						if(is_string($value)){ $description[] = $value ;}
					}
					
					//get image coordinates
					$min_x = min($X);
					$min_y = min($Y);
					$max_x = max($X);
					$max_y = max($Y);
									
					//get image points						
					$width = $max_x-$min_x;
					$height = $max_y-$min_y;
					$ratio = $width/$height;
					$diff_new1 = abs(3 - $ratio);
					$diff_new2 = abs(3.72- $ratio);
					$diff_new3 = abs(4.42 - $ratio);
					if($diff_new1<$diff1){
						$diff1 = $diff_new1;
						$x1 = $min_x;
						$y1 = $min_y;	
						$width_new1 = $max_x-$min_x;
						$height_new1 = $max_y-$min_y;
						$description1 = $description;						
					}
					if($diff_new2<$diff2){
						$diff2 = $diff_new2;
						$x2 = $min_x;
						$y2 = $min_y;	
						$width_new2 = $max_x-$min_x;
						$height_new2 = $max_y-$min_y;
						$description2 = $description;
					} 
					if($diff_new3<$diff3){
						$diff3 = $diff_new3;
						$x3 = $min_x;
						$y3 = $min_y;	
						$width_new3 = $max_x-$min_x;
						$height_new3 = $max_y-$min_y;
						$description3 = $description;
					}
				}
			} 

			if ($diff1 < $diff2)
			{
				if ($diff1 < $diff3)
				{
					$x = $x1;
					$y = $y1;
					$width_new = $width_new1;
					$height_new = $height_new1;
					$plate_number = $description1;
				}
				else
				{
					$x = $x3;
					$y = $y3;
					$width_new = $width_new3;
					$height_new = $height_new3;
					$plate_number = $description3;
				}
			}
			else if ($diff2 < $diff3)
			{
				$x = $x2;
				$y = $y2;
				$width_new = $width_new2;
				$height_new = $height_new2;
				$plate_number = $description2;
			}
			else
			{
				$x = $x3;
				$y = $y3;
				$width_new = $width_new3;
				$height_new = $height_new3;
				$plate_number = $description3;
			}
			
			    //print_r(array_unique($plate_number));
				echo "plate number is : ";
				if(!empty($plate_number)) {
					$plate_number = array_unique($plate_number);					
					foreach($plate_number as $value){
						echo $value." ";
					};										 
				}else{
					echo "Failed to recognise Plate Number";
				} 
			?> 
			
			<?php 
				if( $width_new != 0 && $height_new != 0 && $x != 0 && $y != 0 ){
					
					$newImage = imagecreatetruecolor($width_new,$height_new);
					$fileType = $_FILES["file"]["type"];
					$fileName = basename($_FILES["file"]["name"]);
					$largeImageLoc = $url.'/images/'.$fileName;
					$largeImageLoc = str_replace(" ", "", $largeImageLoc);
					$thumbImageLoc = $realPath.'/images/plate_number/'.$fileName;
					$thumbImageLoc = str_replace(" ", "", $thumbImageLoc);
					
					switch($fileType) {
						case "image/gif":
							$source = imagecreatefromgif($largeImageLoc); 
							break;
						case "image/pjpeg":
						case "image/jpeg":
						case "image/jpg":
							$source = imagecreatefromjpeg($largeImageLoc); 
							break;
						case "image/png":
						case "image/x-png":
							$source = imagecreatefrompng($largeImageLoc); 
							break;
					   }
				
				
					imagecopyresampled($newImage,$source,0,0,$x,$y,$width_new,$height_new,$width_new,$height_new);
			
					switch($fileType) {
						case "image/gif":
							imagegif($newImage,$thumbImageLoc); 
							break;
						case "image/pjpeg":
						case "image/jpeg":
						case "image/jpg":
							imagejpeg($newImage,$thumbImageLoc,90); 
							break;
						case "image/png":
						case "image/x-png":
							imagepng($newImage,$thumbImageLoc);  
							break;
					}
					imagedestroy($newImage);
					echo "<h3>Plate Number: </h3><img src=".str_replace(" ","",$url."/images/plate_number/".$fileName).">";
				}else		
					echo "Failed to recognise Plate Number";
		} else{		
			echo "Failed to recognise Plate Number";
		}	                  
	}else{		
		echo "Upload Another image";
	}	
}
}



function array_column(array $input, $columnKey, $indexKey = null) {
	$array = array();
	foreach ($input as $value) {
		/* if ( ! isset($value[$columnKey])) {
			trigger_error("Key \"$columnKey\" does not exist in array");
			return false;
		} */
		if (is_null($indexKey)) {
			if (isset($value[$columnKey])) {
				$array[] = $value[$columnKey];
			}
		}
		else {
			if ( ! isset($value[$indexKey])) {
				trigger_error("Key \"$indexKey\" does not exist in array");
				return false;
			}
			if ( ! is_scalar($value[$indexKey])) {
				trigger_error("Key \"$indexKey\" does not contain scalar value");
				return false;
			}
			$array[$value[$indexKey]] = $value[$columnKey];
		}
	}
	return $array;
}


function group($y,$vertices){
	$result = array();
	foreach($vertices as $key => $item){
		for($i=0;$i<=10;$i++){
			$y_value = $y+$i;
			if(in_array($y_value, array_column($item, 'y'))){
				$result[] = $vertices[$key];
			}			
		}				
	}
	foreach($result as  $item){
		foreach($item as  $value){
			$final[] = $value;			
		}				
	} 
		return $final;
}
?>