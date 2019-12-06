<?php
	include_once "koneksi.php";


	header("Content-type: application/json; charset=utf-8");
	// $files = glob('packing_v2/xml_zipping/*'); // get all file names


    date_default_timezone_set("Asia/Jakarta");
    //$now =date('Y-m-d');
    $now =date('Y-m');

	$cek5 = mysqli_query($con, "SELECT * FROM log_maintenance AS lm 
		WHERE lm.msg_status = 'XML Not Found;' AND lm.DATE LIKE '".$now."%' AND lm.status = 4
		GROUP BY lm.fname  ORDER BY lm.DATE DESC");
	$response['date_now'] = $now;

	// $response['cek1'] = $cek1->num_rows;
	$x=0;
	$site_id_tmp_txt = "";
	while($row = $cek5->fetch_assoc()) {


	$response['files'] = $row["fname"];
	$lokasi_dir = "backup/".date("Ymd", strtotime($row["date"]))."/".$row["fname"];
	$target_dir = "landing_v2/".$row["fname"];

	// $response['files'] = $row["fname"];
	$response['lokasi_dir'] = "backup/".date("Ymd", strtotime($row["date"]))."/".$row["fname"];
	$response['$target_dir'] = "landing_v2/".$row["fname"];

	$status_copy = copy($lokasi_dir, $target_dir);

	$cek6 = mysqli_query($con, "UPDATE log_maintenance AS lm 
	SET lm.`status` = 0
	WHERE lm.fname = '".$row["fname"]."'");


		// if ($x>0) {
		// 	$site_id_tmp_txt = $site_id_tmp_txt.",";
		// }
		
		// $response['site'][$x] = $row["site_id"];
		// if (@$row["site_id"]!="") {
		// 	$site_id_tmp_txt = $site_id_tmp_txt."'".$row["site_id"]."'";
		// }
		
		$x = $x+1;
	}


	$cek7 = mysqli_query($con, "UPDATE log_maintenance AS lm 
	SET lm.`status` = 0
	WHERE lm.status = 4 AND lm.DATE LIKE '".$now."%' AND lm.msg_status LIKE 'Gagal menyimpan%'");

		die(json_encode($response));


	$response['files'] = $files;	

	foreach($files as $file){ 
		$site_id = explode("_",$file);
		$site_id = $site_id[1];
		$lokasi_dir = "images_backup/".$site_id."/2018/11/".$file; 
		$target_dir = "images/".$file;

		$status_copy = copy($lokasi_dir, $target_dir);

		$response['file'] = $file;
		$response['site_id'] = $site_id;
		$response['lokasi_dir'] = $lokasi_dir;
		$response['target_dir'] = $target_dir;
		$response['status_copy'] = $status_copy;
	

	}	
	
		die(json_encode($response));
	


?>