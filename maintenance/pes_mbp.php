<?php
	include_once "koneksi.php";

    date_default_timezone_set("Asia/Jakarta");
    
    $date_now = date('Y-m-d');

$cek124 = mysqli_query($con, "UPDATE log_maintenance AS lm SET lm.`status` = 0
WHERE lm.msg_status LIKE 'gagal%' AND lm.DATE LIKE '".$date_now."%' AND lm.`status` = 4");
	// $cek123row = $cek123->fetch_assoc();

			// echo $cek1row['mbp_id']."\n";

	if ($cek124==1) {
		echo "ada maintenance yang gagal menyimpan query ke 54 ".$date_now."\n";
		// exit();
	}else{
		echo "tidak ada maintenance yang gagal menyimpan query ke 54 ".$date_now."\n";
	}
	
$cek123 = mysqli_query($con, "UPDATE supplying_power AS sp JOIN lookup_fmc_cluster AS lf ON sp.cluster = lf.cluster
SET sp.cluster_id=lf.cluster_id, sp.regional = lf.regional, sp.is_sync = 0
WHERE lf.`status` = 1 AND sp.cluster_id IS NULL");
	// $cek123row = $cek123->fetch_assoc();

			// echo $cek1row['mbp_id']."\n";

	if ($cek123==1) {
		echo "ada sp yang di update cluster id dan regionalnya\n";
		// exit();
	}else{
		echo "tidak ada sp yang di update cluster id dan regionalnya \n";	
	}
	



	$cekoverduew = @mysqli_query($con, "update corrective as c
SET c.overdue_flag = 1, c.is_sync = 0
where c.end_status = 0 and c.is_sync = 1 and c.overdue_flag = 0
and c.end_corrective_date < now()");

			// echo $cek1row['mbp_id']."\n";

	if ($cekoverduew!=1) {
		echo "tidak ada update corrective overduew\n";
	}else{
		echo "update corrective overdue sukses [ok] ".@$cekoverduew." \n\n ----------------------------------------- \n \n";
	}

	$cek0 = @mysqli_query($con, "update log_maintenance as lm SET lm.sik_no = '' where lm.sik_no like 'GS_%'");

			// echo $cek1row['mbp_id']."\n";

	if ($cek0!=1) {
		echo "update site log_maintenance\n";
	}else{
		echo "update site log_maintenance sukses [ok]  ".@$cek0."\n\n ----------------------------------------- \n \n";
	}



	$cek5 = mysqli_query($con, "select s.site_id from site as s where s.is_allocated = 1
and s.site_id not in (select sp.site_id from supplying_power as sp 
where sp.finish is null
group by sp.site_id)
order by s.site_id");

	// $response['cek1'] = $cek1->num_rows;
	$x=0;
	$site_id_tmp_txt = "";
	while($row = $cek5->fetch_assoc()) {
		if ($x>0) {
			$site_id_tmp_txt = $site_id_tmp_txt.",";
		}
		
		$response['site'][$x] = $row["site_id"];
		if (@$row["site_id"]!="") {
			$site_id_tmp_txt = $site_id_tmp_txt."'".$row["site_id"]."'";
		}
		
		$x = $x+1;
	}
	// echo $site_id_tmp_txt ."\n";
	// 	exit();

	if (@$response['site'][0]!=null) {
		echo "ada site yang teralokasi tanpa tiket mbp seperti site ".$site_id_tmp_txt." [ok] \n";

	$cek2 = mysqli_query($con, "update site as s1
set s1.is_allocated = 0
where s1.site_id in (".$site_id_tmp_txt.")");

			// echo $cek1row['mbp_id']."\n";

	if ($cek2!=1) {
		echo "update site gagal\n";
	}else{
		echo "update site ".$site_id_tmp_txt." sukses [ok] \n\n ----------------------------------------- \n \n";
	}

	}else{
		echo "tidak ada site yang teralokasi tanpa tiket mbp\n\n ----------------------------------------- \n \n";
	}

	//-----------------------------------------------------

	$cek1 = mysqli_query($con, "select * from mbp as m where m.status = 'unavailable' and m.active_at is null");
	$cek1row = $cek1->fetch_assoc();

			// echo $cek1row['mbp_id']."\n";

	if ($cek1row==null) {
		echo "tidak ada mbp yang unavailable tanpa active_at\n";
		exit();
	}
	echo "ada mbp yang unavailable tanpa active_at seperti mbp ".$cek1row['mbp_id']." [ok] \n";

	$cek2 = mysqli_query($con, "select * from supplying_power as sp 
		where sp.mbp_id in (select m.mbp_id from mbp as m where m.status = 'unavailable' and m.active_at is null)
		and sp.finish is null
		order by sp.sp_id DESC");
	$cek2row = $cek2->fetch_assoc();

			// echo $cek2row['mbp_id']."\n";

	if ($cek2row!=null) {
		echo "ada sp yang masih aktif\n";
		exit();
	}
	echo "tidak ada sp yang masih aktif  [ok] \n";

	$cek3 = mysqli_query($con, "select * from mbp_trouble as mt 
		where mt.mbp_id in (select m.mbp_id from mbp as m where m.`status` = 'unavailable' and m.active_at is null)
		and mt.is_active =1");
	$cek3row = $cek3->fetch_assoc();

			// echo $cek3row['mbp_id']."\n";

	if ($cek3row!=null) {
		echo "ada pengajuan yang masih aktif\n";
		exit();
	}
	echo "tidak ada pengajuan yang masih aktif [ok] \n";



	$cek4 = mysqli_query($con, "update mbp as m
		set m.status = 'AVAILABLE'
		where m.status = 'UNAVAILABLE' and m.active_at is null");
		// $cek4row = $cek4->fetch_assoc();

			// echo $cek4."\n";

	if ($cek4!=1) {
		echo "update mbp gagal\n";
	}else{
		echo "update mbp sukses [ok] \n";
	}

		
	


?>