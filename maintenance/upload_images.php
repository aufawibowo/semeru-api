<?php
// include "conn-ceklist.php";
// exit('work');

include_once "koneksi.php";
// ini_set('display_errors',1);
// error_reporting(E_ALL);
$maintenance = 0;
$response = array();

header("Content-type: application/json; charset=utf-8");

$date = date("Y-m-d H:i:s");
$today = date("Ymd");
$prefix_path = '/var/www/html/semeru-api/';


if ($maintenance == 0) {
	if (isset($_FILES['images']['name'])) {

		$tmp_count = count($_FILES['images']['name']);
		$success_count = 0;
		// $failed_count = 0;

		foreach ($_FILES['images']['name'] as $i => $name) {

			$fname = basename($_FILES['images']['name'][$i]);
			$explodeFname = (explode("_",$fname));
			$dir_image_type = strtoupper("".@$explodeFname[0]);
			
			if ($dir_image_type=="MT") {
				$dir_siteid = strtoupper("".@$explodeFname[1]);
				$img_path = 'images/';
				$img_backup_path = 'images_backup/maintenance/';
			}
			elseif($dir_image_type=="GS"){
				$dir_siteid = strtoupper("".@$explodeFname[1]);
				$img_path = 'images_GS/';
				$img_backup_path = 'images_backup/replacement_part_genset/';
			}
			else{
				continue;
			}

			$real_img_backup_path = $prefix_path.$img_backup_path.date("Y").'/'.date("m").'/'.$dir_siteid;
			$img_backup_path .= date("Y").'/'.date("m").'/'.$dir_siteid;

			if (!file_exists($real_img_backup_path)) {
				// $a=
				mkdir($real_img_backup_path, 0777, true);
				// exit($a);
			}

			// echo $folder_path;
			// exit;
			
			$target_img_url = $img_path.'/'.$fname;
			$target_img_backup_url = $real_img_backup_path.'/'.$fname;

			try {
				$upload_to_backup = move_uploaded_file($_FILES['images']['tmp_name'][$i], $target_img_backup_url);
				if (!$upload_to_backup) {
					//$response[$tmp_count]['success'] = false; uncomment this to revert to its original version
					$response[$tmp_count]['message'] = 'File tidak dapat upload';
					$response[$tmp_count]['target lokasi'] = $target_img_backup_url;
					$response[$tmp_count]['error'] = $_FILES["images"]["error"][$i];
					$response[$tmp_count]['uploaded'] = $upload_to_backup;
					$response['100% success'] = false;
					// $failed_count = 1;
					die(json_encode($response));
				} 
				else {
					$x=0;
					$check_photo = false;

					if (copy($target_img_backup_url, $target_img_url)) {
						if(!file_exists($target_img_url) || !is_file($target_img_url)){
							// $response[$tmp_count]['success'] = false; // uncomment this to revert to its original version
							$response[$tmp_count]['message'] = 'Tidak dapat menyalin file dari backup ke images';
							// $response[$tmp_count]['target lokasi'] = $target_img_backup_url;
							// $response[$tmp_count]['error'] = $_FILES["images"]["error"][$i];
							$response[$tmp_count]['uploaded'] = $upload_to_backup;
							$response['100% success'] = false;
							// $failed_count = 1;
							die(json_encode($response));
						}

						$success_count++;
						// $check_photo = true;

						// $response['file awal'] = $url_file_month.'/'.$_FILES['images']['name'][$tmp_count];
						// $response['file akhir'] = 'images/'.$_FILES['images']['name'][$tmp_count];
						$uri_upload = $img_backup_path.'/';
						
						#insert ke tabel image_maintenance

						if ($dir_image_type=="MT") {
							$query = mysqli_query($con, "INSERT INTO `image_maintenance`( `host`, `uri`, `fname`, `date`) VALUES ('http://103.253.107.45/semeru-api/','$uri_upload','$fname','$date')");
						}else if ($dir_image_type=="GS") {				
							$query = mysqli_query($con, "INSERT INTO `image_sparepart`( `host`, `uri`, `fname`, `date`) VALUES ('http://103.253.107.45/semeru-api/','$uri_upload','$fname','$date')");				
						}
					}

				}
			} catch (Exception $e) {
				//$response[$tmp_count]['success'] = false; uncomment this to revert to its original version
				$response[$tmp_count]['message'] = $e;
			}
			// $tmp_count = $tmp_count +1;
			// $file_upload_url = 'images/';
		}
		

		//$response['success'] = true;
		$response['message'] = 'Success';
		$response['100% success'] = true;

		if($tmp_count!=$success_count){
			//$response['success'] = false; uncomment this to revert to its original version
			$response['100% success'] = false;
		}

	} else {
		//$response['success'] = false;
		$response['100% success'] = false;
		$response['message'] = 'Tidak Terima file!';
	}

	exit(json_encode($response));

} elseif ($maintenance == 1) {

	// echo "Sorry, We are under maintenance";
	//$response['success'] = false; uncomment this to revert to its original version
	$response['100% success'] = false;
	$response['message'] = 'Sorry, We are under maintenance';
	exit(json_encode($response));
}


?>