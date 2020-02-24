<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class VandalismController extends Controller{

	public function get_data_vandalism(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		// $corrective_id = $request->input('corrective_id'); 

		$vandalism_raw = DB::table('vandalism')
		->select('*')
		->get();

        $vandalism = json_decode($vandalism_raw, true);
        if ($vandalism==null) {
        	
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $vandalism_raw;
		return response($res);
        }

        foreach ($vandalism as $param => $row) {
        	$data[$param]['id_vandalism'] = $row['id_vandalism'];
        	$data[$param]['site_id'] = $row['site_id'];
        	$data[$param]['site_name'] = $row['site_name'];
        	$data[$param]['fmc_id'] = $row['fmc_id'];
        	$data[$param]['fmc'] = $row['fmc'];
        	$data[$param]['fmc_nik'] = $row['fmc_nik'];
        	$data[$param]['cluster_id'] = $row['cluster_id'];
        	$data[$param]['cluster'] = $row['cluster'];
        	$data[$param]['cluster_fmc_id'] = $row['cluster_fmc_id'];
        	$data[$param]['ns_id'] = $row['ns_id'];
        	$data[$param]['ns'] = $row['ns'];
        	$data[$param]['rtpo_id'] = $row['rtpo_id'];
        	$data[$param]['rtpo'] = $row['rtpo'];
        	$data[$param]['branch_id'] = $row['branch_id'];
        	$data[$param]['branch'] = $row['branch'];
        	$data[$param]['regional'] = $row['regional'];
        	$data[$param]['tgl_transaksi'] = $row['tgl_transaksi'];
        	$data[$param]['tgl_pelaporan'] = $row['tgl_pelaporan'];
        	$data[$param]['description'] = $row['description'];

        	$images_vandalism = DB::table('vandalism_img')
        	->select('*')
        	->where('id_vandalism','=',$row['id_vandalism'])
        	->get();

        	$data[$param]['images_data'] = $images_vandalism;
        }

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function delete_data_vandalism(Request $request){
		
		$array_pencurian_id = $request->input('array_pencurian_id'); 


		foreach ($array_pencurian_id as $param => $row) {

			DB::table('vandalism')
        	->where('pencurian_id','=',$row['pencurian_id'])
			->delete();

			DB::table('vandalism_img')
        	->where('pencurian_id','=',$row['pencurian_id'])
			->delete();

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		return response($res);
	}

	public function delete_image_vandalism(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$dir = "/var/www/html/semeru-api/pencurian/backup/";
		$dir2 = "/var/www/html/semeru-api/pencurian/images/";

		// $t = time() - 4770000;
		// echo($t . "<br>");
		// echo(date("Y-m-d",$t));
		// exit();
		
		$mydir = opendir($dir);

		// echo "my dir <br>";
		while($file = readdir($mydir)) {

		// echo "while my dir ".$dir.$file."<br>";
			// echo " ".$file;
			if($file != "." && $file != "..") {
				chmod($dir.$file, 0777);
				if(is_dir($dir.$file)) {

					// echo "while my dir adalah direktori <br>";
				}
				else{

					// echo "while my dir bukan direktori ".$dir.$file."<br>" ;
					if(date("U",filectime($dir.$file) <= time() - 7776000 ))
						{
							unlink($dir.$file);
							echo "hapus ".$file."<br>";
							// echo "".filectime($dir.$file)." <= ".(time() - 5184000)."<br>"."<br>";
						}else{

							echo "tidak di hapus ".$file."<br>";
							// echo "".filectime($dir.$file)." <= ".(time() - 5184000)."<br>"."<br>";
						}
					}

				}
		}
		closedir($mydir);


		$mydir2 = opendir($dir2);
		while($file2 = readdir($mydir2)) {

			// echo " ".$file;
			if($file2 != "." && $file2 != "..") {
				chmod($dir2.$file2, 0777);
				if(is_dir($dir2.$file2)) {
				}
				else{
					if(date("U",filectime($dir2.$file2) <= time() - 7776000))
						{
							unlink($dir2.$file2);
							echo "hapus ".$file2."<br>";
							// echo "".filectime($dir2.$file2)." <= ".(time() - 5184000)."<br>"."<br>";
						}else{

							echo "tidak di hapus ".$file2."<br>";
						}
					}

				}
		}
		closedir($mydir2);
	}

	public function submit_data_vandalism(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$date_now =date('Y-m-d H:i:s');

		$image 	= $request->input('image');
		$name 	= $request->input('name');
		$sp_id 	= $request->input('sp_id');
		$lat 	= $request->input('latitude');
		$lon 	= $request->input('longitude');
		// $sp_id = $request->input('sp_id');

		if($image==''){
			$response = new emp();
			$res['success'] = false;
			$res['message'] = "Please dont empty image";
			die(json_encode($res));
		}

		switch ($name) {
			case 'BEFORE_KWH_METER':
					# code...
				$curretUploadState = 'BEFORE_RUNNING_HOUR'; 
			break;
			case 'BEFORE_RUNNING_HOUR':
					# code...
				$curretUploadState = 'AFTER_KWH_METER';
			break;
			case 'AFTER_KWH_METER':
					# code...
				$curretUploadState = 'AFTER_RUNNING_HOUR';
			break;
				case 'AFTER_RUNNING_HOUR':
					# code...
				$curretUploadState = 'DONE';
			break;
			default:
				$response = new emp();
				$response->success = false;
				$response->message = "NAME_NOT_MATCH"; 
				die(json_encode($response));
			break;
		}

		if (empty($name)) { 
			$response = new emp();
			$res['success'] = false;
			$res['message'] = "Please dont empty Name";
			die(json_encode($res));
		} 
		else {
			$random = random_word(20);

			$path = $random.".png";

			$host = "http://103.253.107.45/semeru-api/";
			$uri = "upload_image/php/images/";
			$fname = $path.'';

			file_put_contents('images/'.$fname,base64_decode($image));
			$x=0;
			$check_photo = false;
			foreach(glob('images/*.*') as $filename){
				$file['file'][$x] = $filename;

				if ($filename == 'images/'.$fname) {
					$check_photo = true;
					$query = mysqli_query($con, "INSERT INTO image (sp_id,date,name,host,uri,fname, latitude, longitude) VALUES ('$sp_id','$date_now','$name','$host','$uri','$fname', '$lat', '$lon')");

				}
				$x=$x+1;
			}

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
				$res['message'] = "Error Upload image";
				die(json_encode($res)); 
			}
		}	

		if($success){
			$res['success'] = 'OK';
			$res['message'] = 'Success';
			return response($res);
		}
		else{
			$res['success'] = 'Failed';
			$res['message'] = 'Tidak dapat menyimpan data vandalism';
			return response($res);
		}

	}

	public function submit_images_vandalism(Request $request){

	$image 	= $request->input('image');
	$name 	= $request->input('name');
	$sp_id 	= $request->input('sp_id');
	$lat 	= $request->input('latitude');
	$lon 	= $request->input('longitude');
	// $sp_id = $request->input('sp_id');

	date_default_timezone_set("Asia/Jakarta");
	$date_now =date('Y-m-d H:i:s');

	if(empty($image)){
		$res['success'] = false;
		$res['message'] = "Please dont empty image";
		return response(json_encode($res));
	}

	switch ($name) {
		case 'BEFORE_KWH_METER':
				# code...
			$curretUploadState = 'BEFORE_RUNNING_HOUR'; 
		break;
		case 'BEFORE_RUNNING_HOUR':
				# code...
			$curretUploadState = 'AFTER_KWH_METER';
		break;
		case 'AFTER_KWH_METER':
				# code...
			$curretUploadState = 'AFTER_RUNNING_HOUR';
		break;
			case 'AFTER_RUNNING_HOUR':
				# code...
			$curretUploadState = 'DONE';
		break;
		default:
			$response->success = false;
			$response->message = "NAME_NOT_MATCH"; 

			return response(json_encode($res));
		break;
	}

	if (empty($name)) { 
		$res['success'] = false;
		$res['message'] = "Please dont empty Name";
		return response(json_encode($res));
	} 
	else {
		$random = random_word(20);

		$path = $random.".png";

		$host = "http://103.253.107.45/semeru-api/";
		$uri = "upload_image/php/images/";
		$fname = $path.'';

		file_put_contents('images/'.$fname,base64_decode($image));
		$x=0;
		$check_photo = false;
		foreach(glob('images/*.*') as $filename){
			$file['file'][$x] = $filename;

			if ($filename == 'images/'.$fname) {
				$check_photo = true;
				$query = mysqli_query($con, "INSERT INTO image (sp_id,date,name,host,uri,fname, latitude, longitude) VALUES ('$sp_id','$date_now','$name','$host','$uri','$fname', '$lat', '$lon')");

			}
			$x=$x+1;
		}

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
			$res['message'] = "Error Upload image";
			die(json_encode($res)); 
		}
	}}

	// fungsi random string pada gambar untuk menghindari nama file yang sama
	function random_word($id = 20){
		$pool = '1234567890abcdefghijkmnpqrstuvwxyz';

		$word = '';
		for ($i = 0; $i < $id; $i++){
			$word .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
		}
		return $word; 
	}
}