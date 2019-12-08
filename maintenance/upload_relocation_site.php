<?php
// include "conn-ceklist.php";
include_once "koneksi.php";

$maintenance = 0;
$target_path = "upload_checklist/";
$response = array();

header("Content-type: application/json; charset=utf-8");
		// $file_upload_url = '/u/itjatim/sites/itjatim.com/www/syncsemeru/landing/';

date_default_timezone_set("Asia/Jakarta");
$date_now = date('Y-m-d H:i:s');
$date_report_id = date('ymd-His');



$today = date("Ymd");

// $_file_upload_url = 'images/';
$_file_upload_backup_url = 'images_backup/'.$today;

if (!file_exists($_file_upload_backup_url)) {
	mkdir($_file_upload_backup_url, 0777, true);
}


$file_upload_url = 'images_relocation_site/';
// $file_upload_backup_url = 'images_backup/';
// $file_upload_backup_url = 'backup/images';
$date = date("Y-m-d H:i:s");
if ($maintenance == 0) {
			if (isset($_FILES['images']['name'])) {

// //----------------------------------------------------------------------- sementara maintenance -------------
// 				$response['success'] = false;
// 				$response['message'] = 'Tidak Terima file!';
// 	die(json_encode($response));
// //-----------------------------------------------------------------------

				$tmp_count = 0;
				$success_count = 0;
				$failed_count = 0;
				foreach ($_FILES['images']['name'] as $f => $name) {  
					if ($_FILES['images']['tmp_name'][$tmp_count]==null) {
						break;
					}

					$link = basename($_FILES['images']['name'][$tmp_count]);
					// $link = basename($name['name']);
					$file_upload_url .= $link;

					try {

						//cek apakah file yang sama sudah ada, bila ia maka nama di ganti agar tidak tereplace
						// $x=0;
						// $check_photo = false;
						// foreach(glob('images/*.*') as $fname){
						// 	$file['file'][$x] = $fname;
						// 	if ($fname == 'images/'.$_FILES['images']['name'][$tmp_count]) {
						// 		$link = basename($_FILES['images']['name'][$tmp_count]);
						// 		$file_upload_url = 'images/'.rand(0000,9999).$link;
						// 	}
						// 	$x=$x+1;
						// }

						if (!move_uploaded_file($_FILES['images']['tmp_name'][$tmp_count], $file_upload_url)) {
							$response[$tmp_count]['success'] = false;
							$response[$tmp_count]['message'] = 'File tidak dapat upload';
						} else {

							copy($file_upload_url, 'images/'.$link);
							copy($file_upload_url, $_file_upload_backup_url.'/'.$link);
							//cek apakah filesudah terupload? bila ia, maka tampilkan filenya..:D
							$x=0;
							$check_photo = false;
							foreach(glob('images_relocation_site/*.*') as $fname){
								$file['file'][$x] = $fname;

								// if ($fname == 'landing/'.$_FILES['ceklist']['name'][$f]) {
								if ($fname == 'images_relocation_site/'.$_FILES['images']['name'][$tmp_count]) {
									$check_photo = true;

									$pname = $_FILES['images']['name'][$tmp_count];

									$send_by = isset($_POST['send_by']) ? $_POST['send_by'] : '';
									$sik_no = isset($_POST['sik_no']) ? $_POST['sik_no'] : '';
									$new_lat = isset($_POST['new_lat']) ? $_POST['new_lat'] : '';
									$new_lon = isset($_POST['new_lon']) ? $_POST['new_lon'] : '';
									$device_acuration = isset($_POST['device_acuration']) ? $_POST['device_acuration'] : '';
									$delivery_date = $date_now;
									$report_id = "RPT".$date_report_id;

									$tmp = explode("/",$sik_no);									
									$site_id_form_sik = $tmp[1];

									// echo $pname;
									// echo $send_by;
									// echo $sik_no;
									// echo $new_lat;
									// echo $new_lon;
									// echo $device_acuration;


									$sik_data = mysqli_query($con, "SELECT * FROM site WHERE site_id = '$site_id_form_sik'");
									$sikrow = $sik_data->fetch_assoc();

									$rtpo_id = $sikrow['rtpo_id'];
									$rtpo_name = $sikrow['rtpo'];
									$site_id = $site_id_form_sik;

									// $response['rtpo'] = $rtpo_name;
									// $response['rtpo_id'] = $rtpo_id;
									// $response['site_id'] = $site_id_form_sik;
									// $response['site_id 1'] = $sikrow['site_id'];
									// die(json_encode($response));


									if ($sikrow['site_id']==null) {
										$res['site_id'] = $site_id_form_sik;
										$res['success'] = false;
										$res['message'] = 'FAILED_SIK_NOT_FOUND';
										die(json_encode($res));
									}


									// $report_data = mysqli_query($con, "SELECT * FROM report_location_site WHERE send_by = '$send_by' AND site_id = '$site_id_form_sik'");
									// $reportrow = $report_data->fetch_assoc();
									// $rtpo_id = $sikrow['rtpo_id'];
									// $rtpo_name = $sikrow['rtpo'];
									// $site_id = $sikrow['site_id'];




									$chepApprovaldata = mysqli_query($con, "SELECT * FROM report_location_site WHERE approval = 1 AND sik_no = '$sik_no' ORDER BY delivery_date ASC");
									$caRow = $chepApprovaldata->fetch_assoc();
									$tmp_approval = 5;
									$responseBy = null;
									if ($caRow['report_id'] != null) {
										$tmp_approval = $caRow['approval'];

										// $kalimat="Sedang serius belajar PHP di duniailkom";
										$posisi=strpos($caRow['respon_by'],"approval");
										if ($posisi !== FALSE){
											// echo "Ketemu";
											$responseBy = $caRow['respon_by'];
										}
										else {
											// echo "Tidak ketemu";
											$responseBy = 'approval by system based on '.$caRow['respon_by'].' approval data';
										}

										
									}

									

									$rtpoTopic = str_replace(' ', '_', $rtpo_name);

									if ($reportrow['report_id'] == null) {
										// insert
										$query = mysqli_query($con, "INSERT INTO `report_location_site` 
											(`report_id`, `send_by`, `sik_no`, `new_lat`, `new_lon`, `rtpo_id`, `site_id`, `approval`, `device_acuration`, `delivery_date`,`base_url`,`fname`,`respon_by`,`is_offline`) 
											VALUES 
											('$report_id','$send_by','$sik_no','$new_lat','$new_lon','$rtpo_id','$site_id','$tmp_approval','$device_acuration','$delivery_date','http://103.253.107.45/semeru-api/maintenance/images_relocation_site/','$pname','$responseBy','0')");

									}else{
										// update
										$query = mysqli_query($con, "UPDATE `report_location_site` SET 
											`report_id`='$report_id', `new_lat`='$new_lat', `new_lon`='$new_lon', `rtpo_id`='$rtpo_id', `site_id`='$site_id', `approval`='$tmp_approval', `device_acuration`='$device_acuration', `delivery_date`='$delivery_date',`fname`='$pname',`respon_by`='$responseBy',`is_offline`='0' WHERE `send_by`='$send_by' AND `sik_no`='$sik_no'");
									}

									if ($tmp_approval==1) {
										$response['100% success'] = true;
										$response['file success'] = @$data['file success'];
										$response['file failed'] = @$data['file failed'];
										die(json_encode($response));
									}

									$title = 'Permintaan Update Lokasi Site';
									$body = $send_by.' menyatakan bahwa koordinat sebenarnya dari site '.$sikrow['site_name'];
									// $to_token_id = '/topics/'.$rtpoTopic;
									// $to_token_id = '/topics/RTPO_YOGYAKARTA';


									$user_rtpo_data = mysqli_query($con, "SELECT * from users as u join user_rtpo as ur on u.username = ur.username
										where ur.rtpo_id = '$rtpo_id' and firebase_token != ''");
									// $rtporow = $user_rtpo_data->fetch_assoc();
									// $fbt = $rtporow['firebase_token'];

									$to_token_id = array();
									while($row = $user_rtpo_data->fetch_assoc()){
										array_push($to_token_id,$row['firebase_token']);
									}


									$type_name = 'report_id';
									$type_id = $report_id;
									$type = 'NEW_LOCATION_SITE_FROM_FMC';


									// echo $to_token_id[0];

									if (!defined('API_ACCESS_KEY')){
										define('API_ACCESS_KEY', 'AAAAo6mi6uY:APA91bF5Jrgp7pqCX40LO0WQb6v-eLKd5xIP0xjxivSdlpDg5_iOisegSNQR0GSYwmeICJnumEbckFR6RextiSTkhUA0xBKk-HfMMNzRAWmyXPZzi5FxJvaYescfgyD4s3YTUwB9X78o');
									}


									// if ($to_token_id=='') {
									// 	$to_token_id = 'frMgfkXK4KE:APA91bHK76rxHLyiIC2VUYcjJUAdxqJdYC2HoQqqwFxBJ6GiUN3b5BFkj9RYTaLZ9mQi8dYU4SwhEp_NAHwmGibH-3sGnA6pwi4_nSP5oUcDUeYshRYKwDPlvYZQ5MlsQ2aCmW7nS35W';
									// }


									$msg = array
									(
										'Message'   => $body,
										'Title'   => $title,
										"$type_name"   => $type_id,
										'Type'   => $type,
									);

									// if (strlen($to_token_id)>20) { 
									// 	$getToken['id'] = $to_token_id;
									// 	$registrationIds = array( $getToken['id'] );
									// 	$fields = array
									// 	(
									// 		'registration_ids'  => $registrationIds,
									// 		'data'      => $msg
									// 	);
									// }else{
									// 	$fields = array
									// 	(
									// 		'to'  => $to_token_id,
									// 		'data'      => $msg
									// 	);
									// }


									$fields = array
									(
										'registration_ids'  => $to_token_id,
										'data'      => $msg
									);
									$headers = array
									(
										'Authorization: key=' . API_ACCESS_KEY,
										'Content-Type: application/json'
									);

									$ch = curl_init();
									curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
									curl_setopt( $ch,CURLOPT_POST, true );
									curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
									curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
									curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
									curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    // curl_setopt($ch, CURLOPT_TIMEOUT_MS, 800);


									$result = curl_exec($ch );
									curl_close( $ch );

									$data['data'] = json_decode($result, true);
									$data['token to'] = $to_token_id;


								}
								$x=$x+1;
							}

							if ($check_photo) {
								$success_count = $success_count+1;
								$data['file success'][$success_count] = 'images/'.$_FILES['images']['name'][$tmp_count];
								// $data['file failed'][$tmp_count] = null;

							}else{
								$failed_count = $failed_count+1;
								// $data['file success'][$tmp_count] = null;
								$data['file failed'][$failed_count] = 'images/'.$_FILES['images']['name'][$tmp_count];
							}
						}
					} catch (Exception $e) {
						$response[$tmp_count]['success'] = false;
						$response[$tmp_count]['message'] = $e;
					}
					$tmp_count = $tmp_count +1;
					$file_upload_url = 'images_relocation_site/';
				}
				if ($success_count == $tmp_count) {
					$response['100% success'] = true;
					$response['file success'] = @$data['file success'];
					$response['file failed'] = @$data['file failed'];
					// $response['fb'] = $data['data'];
					// $response['fb to'] = $data['token to']; 

				// $response[$tmp_count]['tmp_count'] = $tmp_count;
				}else{
					$response['100% success'] = false;
					$response['file success'] = @$data['file success'];
					$response['file failed'] = @$data['file failed'];
					// $response['fb'] = $data['data'];
					// $response['fb to'] = $data['token to'];
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