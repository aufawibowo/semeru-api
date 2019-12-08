<?php
include_once "koneksi.php";

class emp{}

header("Content-type: application/json; charset=utf-8");
// $image = $_POST['image'];
isset($_FILES['image']['name']);
$sik_number = $_POST['sik_number'];
$site_id = $_POST['site_id'];
$username = $_POST['username'];
$reason = $_POST['reason'];
$kategori = $_POST['kategori'];

$res['sik_number'] = $sik_number;


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

$today = date("Ymd");
if (empty($username)) { 
	$response = new emp();

		$res['success'] = false;
		$res['message'] = "Please dont empty Name";
	die(json_encode($res));
} else {
	date_default_timezone_set("Asia/Jakarta");
	$date = date("Ymd");
	$date_now = date('Y-m-d H:i:s');



	$today = date("Ymd");

	// $_file_upload_url = 'images/';
	// $_file_upload_backup_url = 'images_backup/'.$today;

	// if (!file_exists($_file_upload_backup_url)) {
	// 	mkdir($_file_upload_backup_url, 0777, true);
	// }





	$tmp = explode("/",$sik_number);
	$fname = basename($_FILES['image']['name']);


	$explodeFname = (explode("_",$fname));

			#buat folder site_id
	$url_file_siteid = 'images_backup/'.$explodeFname[1];
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
	// $file_upload_url = $_file_upload_url.$fname;
	// $file_upload_backup_url = $url_file_month.'/'.$fname;

	if (!move_uploaded_file($_FILES['image']['tmp_name'], $url_file_month.'/'.$fname)) {
		$response[$tmp_count]['success'] = false;
		$response[$tmp_count]['message'] = 'File tidak dapat upload';
		die(json_encode($res));
	} else {
		if (copy($url_file_month.'/'.$fname, 'images/'.$fname)) {

			$site_id= $tmp[1];

			$site_data = mysqli_query($con, "SELECT * FROM site WHERE site_id = '$tmp[1]'");
			$siterow = $site_data->fetch_assoc();

			$site_name = $siterow['site_name'];
			// $cluster_id = $siterow['cluster_id'];
			// $cluster_fmc_id = $siterow['cluster_fmc_id'];
			// $cluster = $siterow['cluster'];
			// $ns_id = $siterow['ns_id'];
			// $ns = $siterow['ns'];
			// $rtpo_id = $siterow['rtpo_id'];
			// $rtpo = $siterow['rtpo'];
			// $branch_id = $siterow['branch_id'];
			// $branch = $siterow['branch'];
			// $regional = $siterow['regional'];

			$user_mt_data = mysqli_query($con, "SELECT * FROM user_mbp_mt WHERE mbp_mt_username = '$username'");
			$userMTrow = $user_mt_data->fetch_assoc();
			$fmc_id = $userMTrow['fmc_id'];
			$cluster_id = $userMTrow['cluster_id'];

			// echo "".$cluster_fmc_id;

			$getQuery = mysqli_query($con, "SELECT * FROM lookup_fmc_cluster WHERE cluster_id = '$cluster_id'");
			$sikrow = $getQuery->fetch_assoc();
			// $fmc_id= $sikrow['fmc_id'];

			$cluster = $sikrow['cluster'];
			// $cluster_id = $sikrow['cluster_id'];
			$fmc= $sikrow['fmc'];
			$cluster_fmc_id= $sikrow['cluster_fmc_id'];
			$cluster_fmc= $sikrow['cluster_fmc'];

			$ns_id = $sikrow['ns_id'];
			$ns = $sikrow['ns'];
			$rtpo_id = $sikrow['rtpo_id'];
			$rtpo = $sikrow['rtpo'];
			$branch_id = $sikrow['branch_id'];
			$branch = $sikrow['branch'];
			$regional = $sikrow['regional'];
			

			// $query = mysqli_query($con, "INSERT INTO maintenance_reason (image_name, sik_number, site_id, username, reason, fmc_id, fmc, cluster_fmc_id, cluster_fmc,site_name,cluster_id,cluster,ns_id,ns,rtpo_id,rtpo,branch_id,branch,regional, date_create, last_update) VALUES ('$linkfname','$sik_number','$site_id','$username','$reason','$fmc_id','$fmc','$cluster_fmc_id','$cluster_fmc','$site_name','$cluster_id','$cluster','$ns_id','$ns','$rtpo_id','$rtpo','$branch_id','$branch','$regional','$date_now','$date_now')");
			$query = mysqli_query($con, "INSERT INTO maintenance_reason (image_name, sik_number, site_id, username, kategori, reason, fmc_id, fmc, cluster_fmc_id, cluster_fmc,site_name,cluster_id,cluster,ns_id,ns,rtpo_id,rtpo,branch_id,branch,regional, date_create, last_update) VALUES ('$fname','$sik_number','$site_id','$username', '$kategori', '$reason','$fmc_id','$fmc','$cluster_fmc_id','$cluster_fmc','$site_name','$cluster_id','$cluster','$ns_id','$ns','$rtpo_id','$rtpo','$branch_id','$branch','$regional','$date_now','$date_now')");

			$res['success'] = true;
			$res['message'] = "SUCCESS";
			die(json_encode($res));

		}else{
			$response[$tmp_count]['success'] = false;
			$response[$tmp_count]['message'] = 'File tidak dapat copy';
			die(json_encode($res));
		}
	}
}
