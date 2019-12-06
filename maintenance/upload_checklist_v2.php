<?php
// include "conn-ceklist.php";

include_once "koneksi.php";
$maintenance = 0;
$target_path = "upload_checklist/";
$response = array();

	date_default_timezone_set("Asia/Jakarta");
	header("Content-type: application/json; charset=utf-8");
		// $file_upload_url = '/u/itjatim/sites/itjatim.com/www/syncsemeru/landing/';

// $tmp_landing = 'landing/';
$tmp_landing = 'landing_v2/';
$file_upload_url = $tmp_landing;
$date = date("Y-m-d H:i:s");
//$date = "2018-08-22 16:19:38";
$today = date("Ymd");

$send_by_1 = isset($_POST['send_by']) ? $_POST['send_by'] : '';
$sik_no_1 = isset($_POST['sik_no']) ? $_POST['sik_no'] : '';

if ($maintenance == 0) {
			if (isset($_FILES['ceklist']['name'])) {


				$tmp_count = 0;
				$success_count = 0;
				foreach ($_FILES['ceklist']['name'] as $f => $name) {  
					if ($_FILES['ceklist']['tmp_name'][$tmp_count]==null) {
						break;
					}

					$link = basename($_FILES['ceklist']['name'][$tmp_count]);
					// $link = basename($name['name']);
					$file_upload_url .= $link;


					try {

						//cek apakah file yang sama sudah ada, bila ia maka nama di ganti agar tidak tereplace
						$x=0;
						$check_photo = false;
						foreach(glob($tmp_landing.'*.*') as $fname){
							$file['file'][$x] = $fname;
							if ($fname == $tmp_landing.$_FILES['ceklist']['name'][$tmp_count]) {

								$randd = rand(1111,9999);
								$link = basename($_FILES['ceklist']['name'][$tmp_count]);

								$link = $randd.$link;

								$file_upload_url = $tmp_landing.$link;

							}
							$x=$x+1;
						}


							$dirname = date("Ymd");
							$backup = "backup/";
							$backup_dir = $backup.$dirname;
							if (!file_exists($backup_dir)) {
								mkdir($backup . $dirname, 0777);
								$result['dir']="In Bakcup : the directory ".$dirname." was successfully created.";
							} else {
								$result['dir']="In Bakcup : the directory ".$dirname." exists.";
							}

							if (!copy($_FILES['ceklist']['tmp_name'][$tmp_count], $backup_dir."/".$link)) {
								$response[$tmp_count]['success'] = false;
								$response[$tmp_count]['message'] = 'File tidak dapat upload';
							}

						if (!move_uploaded_file($_FILES['ceklist']['tmp_name'][$tmp_count], $file_upload_url)) {
							$response[$tmp_count]['success'] = false;
							$response[$tmp_count]['message'] = 'File tidak dapat upload';
						} else {


							//cek apakah filesudah terupload? bila ia, maka tampilkan filenya..:D
							$x=0;
							$check_photo = false;
							foreach(glob($tmp_landing.'*.*') as $fname){
								$file['file'][$x] = $fname;

								// if ($fname == 'landing/'.$_FILES['ceklist']['name'][$f]) {
								if ($fname == $tmp_landing.$_FILES['ceklist']['name'][$tmp_count]) {
									$check_photo = true;
									
								}
								$x=$x+1;
							}

							if ($check_photo) {
								$success_count = $success_count+1;
								$data['file success'][$tmp_count] = $tmp_landing.$_FILES['ceklist']['name'][$tmp_count];
								$data['file failed'][$tmp_count] = null;

								// $fname_upload = $_FILES['ceklist']['name'][$tmp_count];
								$fname_upload = $link;
								$uri_upload ='backup/'.$today.'/';
								$send_by_tmp = explode('_',$fname_upload,5);
								$send_by_tmp2 = explode('_',$send_by_tmp[4],-1);
								
								$checklist_cat = $send_by_tmp[0];
								$site_id = $send_by_tmp[1];
								$otp = $send_by_tmp[3];
								$sik_no = @$sik_no_1;
								if (empty(@$sik_no_1)) {
									$getQuery = mysqli_query($con, "SELECT * FROM sik_site WHERE otp_id = '$otp'");
									$sikrow = $getQuery->fetch_assoc();
									$sik_no = $sikrow['sik_no'];
								}


								$send_by='';
								$xt = 0;
								foreach ($send_by_tmp2 as $key) {

									$send_by .= $key;

									if (@$send_by_tmp2[$xt+1]!=null) {
										$send_by .='_';
									}
									$xt = $xt+1;
								}

								if ($send_by_1!=null) {
									$send_by =$send_by_1;
								}


								$getQueryFmc = mysqli_query($con, "SELECT * FROM user_mbp_mt WHERE mbp_mt_username = '$send_by'");
								$sikrowfmc = $getQueryFmc->fetch_assoc();
								$fmc_id = $sikrowfmc['fmc_id'];
								$fmc = $sikrowfmc['fmc'];

								// $send_by = $send_by_tmp2[0];

								// if ($checklist_cat=="GS") {
								if (strpos( $checklist_cat, 'GS' )==4) {

									$DELETEQuery = mysqli_query($con, "DELETE FROM `log_sparepart` WHERE `fname`='$fname_upload'");
									$query = mysqli_query($con, "INSERT INTO `log_sparepart` 
										(`username`, `uri`, `fname`, `spk_no`, `genset_id`, `otp`, `date`, `status`,`uri_tmp`, `host`,`fmc_id`, `fmc`) 
										VALUES 
										('$send_by','$uri_upload','$fname_upload','$sik_no','$site_id','$otp','$date','0','$tmp_landing', 'http://103.253.107.45/semeru-api/maintenance/','$fmc_id','$fmc')");
									
								}else{
									$DELETEQuery = mysqli_query($con, "DELETE FROM `log_maintenance` WHERE `fname`='$fname_upload'");
									$query = mysqli_query($con, "INSERT INTO `log_maintenance` 
										(`username`, `uri`, `fname`, `sik_no`, `site_id`, `otp`, `date`, `status`,`uri_tmp`, `host`,`fmc_id`, `fmc`) 
										VALUES 
										('$send_by','$uri_upload','$fname_upload','$sik_no','$site_id','$otp','$date','0','$tmp_landing', 'http://103.253.107.45/semeru-api/maintenance/','$fmc_id','$fmc')");

								}
								// $DELETEQuery = mysqli_query($con, "DELETE FROM `log_maintenance` WHERE `fname`='$fname_upload'");
								// $query = mysqli_query($con, "INSERT INTO `log_maintenance` 
								// 			(`username`, `uri`, `fname`, `sik_no`, `site_id`, `otp`, `date`, `status`,`uri_tmp`, `host`,`fmc_id`, `fmc`) 
								// 			VALUES 
								// 			('$send_by','$uri_upload','$fname_upload','$sik_no','$site_id','$otp','$date','0','$tmp_landing', 'http://103.253.107.45/semeru-api/maintenance/','$fmc_id','$fmc')");

								$response['fname_upload'] = $fname_upload;
								$response['uri_upload'] = $uri_upload;
								// $response['send_by_tmp'] = $send_by_tmp;
								// $response['send_by_tmp2'] = $send_by_tmp2;
								$response['send_by'] = $send_by;

							}else{
								$data['file success'][$tmp_count] = null;
								$data['file failed'][$tmp_count] = $tmp_landing.$_FILES['ceklist']['name'][$tmp_count];
							}
						}
					} catch (Exception $e) {
						$response[$tmp_count]['success'] = false;
						$response[$tmp_count]['message'] = $e;
					}
					$tmp_count = $tmp_count +1;
					$file_upload_url = $tmp_landing;
				}
				if ($success_count == $tmp_count) {
					$response['100% success'] = true;
					$response['file success'] = $data['file success'];
					$response['file failed'] = $data['file failed'];

					$send_by = isset($_POST['username']) ? $_POST['username'] : '';
					$sik_no = isset($_POST['sik_no']) ? $_POST['sik_no'] : '';

					if ($sik_no!=null) {

						// $uri_upload = $url_file_month.'/';
						// $fname_upload = $_FILES['images']['name'][$i];

						$query = mysqli_query($con, "INSERT INTO `log_maintenance` 
											(`username`, `sik_no`) 
											VALUES 
											('$send_by','$sik_no')");

					}
				// $response[$tmp_count]['tmp_count'] = $tmp_count;
				}else{
					$response['100% success'] = false;
					$response['file success'] = $data['file success'];
					$response['file failed'] = $data['file failed'];
				}
			} else {
				$response['success'] = false;
				$response['message'] = 'Tidak Terima file!';
			}

			// $f = $file_upload_url;
			// chown($f, 'ngsemeru');

			die(json_encode($response));
			// die(json_encode($multiUP));
} else if ($maintenance == 1) {
	// echo "Sorry, We are under maintenance";
	$response['success'] = false;
	$response['message'] = 'Sorry, We are under maintenance';
	die(json_encode($response));
}


?>