<?php


include_once "koneksi.php";

    date_default_timezone_set("Asia/Jakarta");
    // $date_now = date('Y-m-d H:i:s');
    $now = date('Y-m-d H:i:s');

    $date_now = date('Y-m-d');
    $delete_date_strtotime = strtotime($date_now." -20 day");
    $delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);



	// $image_data = mysqli_query($con, "SELECT * FROM image_maintenance WHERE date < '$delete_date'");
	$image_data = mysqli_query($con, "SELECT * FROM log_maintenance  WHERE is_del = 0 and status = 3 and uri_tmp = 'landing_v2/' order by date asc");
	$imagerow = $image_data->fetch_assoc();

	// $user_cn = $imagerow['username'];

		// echo $imagerow['fname'].'  ';
	if ($imagerow==null) {
		echo "Tidak ada file yang di delete";
	}



	foreach ($image_data as $i => $fa) {
		// echo $fa['fname'].'  ';

		$fname = $fa['fname'];
		$file_uri = $fa['uri_tmp'].$fa['fname'];
		echo "\n";
		// echo "lokasi file $file_uri";


		if(is_file($file_uri)) {
		   if (!unlink($file_uri))
			{
				echo "Error deleting $file_uri";

			}
			else
			{
				echo "Deleted $file_uri";
				// $delete_image_data = mysqli_query($con, "DELETE FROM image_maintenance WHERE fname = '$fname'");
			}
		}else{
			echo "file Not Found $file_uri ";
				// $delete_image_data = mysqli_query($con, "DELETE FROM image_maintenance WHERE fname = '$fname'");
				// mysqli_query($con, "DELETE FROM image_maintenance WHERE fname = '$fname'");
		}
	}

	$delete_image_data = mysqli_query($con, "UPDATE log_maintenance SET is_del=1 WHERE is_del = 0 and status = 3");
	// $delete_image_data = mysqli_query($con, "DELETE FROM image_maintenance WHERE date < '$delete_date'");

?>