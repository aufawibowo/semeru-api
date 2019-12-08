<?php

	header("Content-type: application/json; charset=utf-8");
	$response = array();
	$today = date("Ymd");
        $maintenance = 0;
	// $file_upload_url = '/u/itjatim/sites/itjatim.com/www/semeru1/';
	$file_upload_url = 'images/';
	//$file_upload_url = '/home/sloki/user/h250882/sites/rajawalistore.com/www/semeru1/';
	//$file_upload_url = '/home/sloki/user/itjatim/sites/itjatim.com/www/semeru1/';
	
if ($maintenance == 0) {
	// if (!file_exists($file_upload_url.$today)) {
	// 	$old = umask(0);
	// 	mkdir($file_upload_url.$today, 0777, true);
	// 	umask($old);
	// }

	if (isset($_FILES['image']['name'])) {
		$otp = isset($_POST['otp']) ? $_POST['otp'] : '';
		$kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
		$lac = isset($_POST['LAC']) ? $_POST['LAC'] : '';
		$ci = isset($_POST['CI']) ? $_POST['CI'] : '';
		$long = isset($_POST['longitude']) ? $_POST['longitude'] : '';
		$lat = isset($_POST['latitude']) ? $_POST['latitude'] : '';
		$filename = isset($_POST['filename']) ? $_POST['filename'] : '';
		$date = date('Y-m-d H:i:s');
		$link = $filename.'.jpg';
		$file_upload_url .= /*$today.'/'.*/$link;	
		try {
			if (!move_uploaded_file($_FILES['image']['tmp_name'], $file_upload_url)) {
				$response['succes'] = false;
				$response['message'] = 'Upload Gagal!';
			} else {
				
				$x=0;
				$check_photo = false;
				foreach(glob('images/'./*$today.'/'.*/'*.*') as $fname){
					$file['file'][$x] = $fname;

					if ($fname == 'images/'/*.$today.'/'*/.$filename.'.jpg') {
						$check_photo = true;
					}
					$x=$x+1;
				}

				if ($check_photo) {
					$response['succes'] = true;
					$response['message'] = 'Succes Upload';
					$response['filename'] = 'images/'/*.$today.'/'*/.$filename;
					$response['file'] = $file;
				}else{
					$response['succes'] = false;
					$response['message'] = 'Tidak Menerima File!';
					$response['filename'] = 'images/'/*.$today.'/'*/.$filename;
					$response['file'] = $file;
				}

				// $response['succes'] = true;
				// $response['message'] = 'Succes Upload';
			}
		} catch (Exception $e) {
			$response['succes'] = false;
		}
	} else {
		$response['succes'] = false;
		$response['message'] = 'Tidak Menerima File!';
	}
	// echo $response['message'];
	die(json_encode($response));
} else if ($maintenance == 1) {

		$response['succes'] = false;
		$response['message'] = 'Sorry, We are under maintenance';

		// echo "Sorry, We are under maintenance";
		die(json_encode($response));
	}
?>