<?php


include_once "koneksi.php";

    date_default_timezone_set("Asia/Jakarta");
    // $date_now = date('Y-m-d H:i:s');
    $now = date('Y-m-d H:i:s');

    $date_now = date('Y-m-d');
    $delete_date_strtotime = strtotime($date_now." -5 day");
    $delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);



	// $image_data = mysqli_query($con, "SELECT * FROM image_maintenance WHERE date < '$delete_date'");
	$image_data = mysqli_query($con, "SELECT * FROM image_maintenance WHERE date < '$delete_date'");
	$imagerow = $image_data->fetch_assoc();

	// $user_cn = $imagerow['username'];

		// echo $imagerow['fname'].'  ';
	if ($imagerow==null) {
		echo "Tidak ada file yang di delete";
	}



	foreach ($image_data as $i => $fa) {
		// echo $fa['fname'].'  ';

		$fname = $fa['fname'];
		$file_uri = $fa['uri'].$fa['fname'];
		echo "\n";


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

	$delete_image_data = mysqli_query($con, "DELETE FROM image_maintenance WHERE date < '$delete_date'");


?>