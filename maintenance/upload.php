<?php
include_once "koneksi.php";

class emp{}

header("Content-type: application/json; charset=utf-8");
$image = $_POST['image'];
$sik_number = $_POST['sik_number'];
// $site_id = $_POST['site_id'];
$username = $_POST['username'];
$reason = $_POST['reason'];


$res['sik_number'] = $sik_number;

$getQuery = mysqli_query($con, "SELECT * FROM sik_site WHERE sik_no = '$sik_number'");
$sikrow = $getQuery->fetch_assoc();
$site_id= $sikrow['site_id'];
$fmc_id= $sikrow['fmc_id'];
$fmc= $sikrow['fmc'];
$cluster_fmc_id= $sikrow['cluster_fmc_id'];
$cluster_fmc= $sikrow['cluster_fmc'];

// $res['site_id'] = $site_id;
// $res['username'] = $username;
// $res['reason'] = $reason;

// $res['fmc_id'] = $fmc_id;
// $res['fmc'] = $fmc;
// $res['cluster_fmc_id'] = $cluster_fmc_id;
// $res['cluster_fmc'] = $cluster_fmc;

// $query = mysqli_query($con, "INSERT INTO maintenance_reason (image_name, sik_number, site_id, username, reason, fmc_id, fmc, cluster_fmc_id, cluster_fmc) VALUES ('$fname','$sik_number','$site_id','$username','$reason','$fmc_id','$fmc','$cluster_fmc_id','$cluster_fmc')");

// die(json_encode($res));


if($site_id==null){
	$response = new emp();
	$res['success'] = false;
	$res['message'] = "sik_number wrong";
	die(json_encode($res));
}

if(empty($sik_number)){
	$response = new emp();
	$res['success'] = false;
	$res['message'] = "Please dont empty sik_number";
	die(json_encode($res));
}

// if(empty($site_id)){
// 	$response = new emp();
// 	$res['success'] = false;
// 	$res['message'] = "Please dont empty site_id";
// 	die(json_encode($res));
// }

if(empty($username)){
	$response = new emp();
	$res['success'] = false;
	$res['message'] = "Please dont empty username";
	die(json_encode($res));
}

if(empty($reason)){
	$response = new emp();
	$res['success'] = false;
	$res['message'] = "Please dont empty reason";
	die(json_encode($res));
}

if($image==''){
	$response = new emp();
	$res['success'] = false;
	$res['message'] = "Please dont empty image";
	die(json_encode($res));
}


$today = date("Ymd");

// if (!file_exists('images/'.$today)) {
// 	mkdir('images/'.$today, 0777, true);
// }
if (empty($username)) { 
	$response = new emp();

		$res['success'] = false;
		$res['message'] = "Please dont empty Name";
	die(json_encode($res));
} else {
	$random = random_word(20);
	date_default_timezone_set("Asia/Jakarta");
	$date = date("Ymd");
	$date_now = date('Y-m-d H:i:s');

	$path = 'MT_'.$site_id.'_'.$date.'_'.date("hms")./*'_'.$sik_number.*/'_'.$username.'_'.".png";
	$fname = $path.'';

	$tmp = explode("/",$sik_number);


	file_put_contents('images/'/*.$today.'/'*/.$fname,base64_decode($image));
		// cek apakah foto masuk?
	$x=0;
	$check_photo = false;
	foreach(glob('images/'./*$today.'/'.*/'*.*') as $filename){
		$file['file'][$x] = $filename;

		if ($filename == 'images/'/*.$today.'/'*/.$fname) {
			$check_photo = true;

			file_put_contents('images_backup/'.$fname, file_get_contents('images/'.$fname));
			// file_put_contents('backup/images/'$fname, file_get_contents('images/'.$fname));

			// site_name, cluster_id, cluster, ns_id, ns, rtpo_id, rtpo, branch_id, branch, regional
			$site_data = mysqli_query($con, "SELECT * FROM master_site WHERE site_id = '$tmp[1]'");
			$siterow = $site_data->fetch_assoc();

			$site_name = $siterow['site_name'];
			$cluster_id = $siterow['cluster_id'];
			$cluster = $siterow['cluster'];
			$ns_id = $siterow['ns_id'];
			$ns = $siterow['ns'];
			$rtpo_id = $siterow['rtpo_id'];
			$rtpo = $siterow['rtpo'];
			$branch_id = $siterow['branch_id'];
			$branch = $siterow['branch'];
			$regional = $siterow['regional'];

			$query = mysqli_query($con, "INSERT INTO maintenance_reason (image_name, sik_number, site_id, username, reason, fmc_id, fmc, cluster_fmc_id, cluster_fmc,site_name,cluster_id,cluster,ns_id,ns,rtpo_id,rtpo,branch_id,branch,regional, date_create, last_update) VALUES ('$fname','$sik_number','$site_id','$username','$reason','$fmc_id','$fmc','$cluster_fmc_id','$cluster_fmc','$site_name','$cluster_id','$cluster','$ns_id','$ns','$rtpo_id','$rtpo','$branch_id','$branch','$regional','$date_now','$date_now')");
		}
		$x=$x+1;
	}

	// if ($query){
	if ($check_photo){

		$response = new emp();
		$res['success'] = true;
		$res['message'] = "SUCCESS";
		$res['curretUploadState'] = $curretUploadState;
		$res['path'] = $path;
		$res['check_photo'] = $check_photo;
		$res['file'] = $file;

		die(json_encode($res));
	} else{ 
		$response = new emp();

		$res['success'] = false;
		$res['message'] = $fname." Error Upload image";
		die(json_encode($res)); 
	}
}	

	// fungsi random string pada gambar untuk menghindari nama file yang sama
function random_word($id = 20){
	$pool = '1234567890abcdefghijkmnpqrstuvwxyz';

	$word = '';
	for ($i = 0; $i < $id; $i++){
		$word .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
	}
	return $word; 
}

mysqli_close($con);

?>	