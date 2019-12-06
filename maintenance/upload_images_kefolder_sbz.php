<?php
// include "conn-ceklist.php";

include_once "koneksi.php";
$maintenance = 0;
$target_path = "upload_checklist/";
$response = array();

header("Content-type: application/json; charset=utf-8");


$date = date("Y-m-d H:i:s");
$today = date("Ymd");

$_file_upload_url = 'images/';

$_file_upload_url = 'images/';

if ($maintenance == 0) {
	if (isset($_FILES['images']['name'])) {

		$tmp_count = 0;
		$success_count = 0;
		$failed_count = 0;

		foreach ($_FILES['images']['name'] as $i => $name) {

			$fname = basename($_FILES['images']['name'][$i]);

			$explodeFname = (explode("_",$fname));

			#buat folder site_id
			$dir_siteid = strtoupper("".$explodeFname[1]);
			// $url_file_siteid = 'images_backup/'.$dir_siteid;
			$url_file_siteid = 'images_backup/SBZ351';
			if (!file_exists($url_file_siteid)) {
				mkdir($url_file_siteid, 0777, true);
			}
			
			#buat folder tahun
			$url_file_year = $url_file_siteid.'/'.date("Y");
			if (!file_exists($url_file_year)) {
				mkdir($url_file_year, 0777, true);
			}
			
			#buat folder bulan
			$url_file_month = $url_file_year.'/'.date("m");
			if (!file_exists($url_file_month)) {
				mkdir($url_file_month, 0777, true);
			}
			$file_upload_url = $_file_upload_url.$fname;
			$file_upload_backup_url = $url_file_month.'/'.$fname;

			try {
				// echo $name;
				// echo $_FILES['images']['tmp_name'][$f];
				if (!move_uploaded_file($_FILES['images']['tmp_name'][$i], $file_upload_backup_url)) {
					$response[$tmp_count]['success'] = false;
					$response[$tmp_count]['message'] = 'File tidak dapat upload';
					$response[$tmp_count]['target lokasi'] = $file_upload_backup_url;
					$failed_count = 1;
				} else {


					$x=0;
					$check_photo = false;
					if (copy($file_upload_backup_url, $file_upload_url)) {
						$check_photo = true;

						$response['file awal'] = $url_file_month.'/'.$_FILES['images']['name'][$tmp_count];
						$response['file akhir'] = 'images/'.$_FILES['images']['name'][$tmp_count];
						$uri_upload = $url_file_month.'/';
						$fname_upload = $_FILES['images']['name'][$i];
						
						#insert ke tabel image_maintenance

						$query = mysqli_query($con, "INSERT INTO `image_maintenance`( `host`, `uri`, `fname`, `date`) VALUES ('http://103.253.107.45/semeru-api/maintenance/','$uri_upload','$fname_upload','$date')");


					}
				}
			} catch (Exception $e) {
				$response[$tmp_count]['success'] = false;
				$response[$tmp_count]['message'] = $e;
			}
			$tmp_count = $tmp_count +1;
			$file_upload_url = 'images/';
		}
		if ($success_count == $tmp_count) {
			$response['100% success'] = true;
			if ($failed_count==1) {
				$response['100% success'] = false;
			}
			// $response['file success'] = @$data['file success'];
			// $response['file failed'] = @$data['file failed'];
				// $response[$tmp_count]['tmp_count'] = $tmp_count;
		}else{
			$response['100% success'] = true;
			if ($failed_count==1) {
				$response['100% success'] = false;
			}
					// $response['100% success'] = false;
			// $response['file success'] = @$data['file success'];
			// $response['file failed'] = @$data['file failed'];
		}
	} else {
		$response['success'] = false;
		$response['message'] = 'Tidak Terima file!';
	}

	die(json_encode($response));
			// die(json_encode($multiUP));
} else if ($maintenance == 1) {
	// echo "Sorry, We are under maintenance";
	$response['success'] = false;
	$response['message'] = 'Sorry, We are under maintenance';
	die(json_encode($response));
}


?>