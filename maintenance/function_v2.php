<?php



ini_set("memory_limit","64M");

define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
date_default_timezone_set('Asia/Jakarta');
function sync_semeru_move($sumber = '', $tujuan = '', $del=false) {
	$files = scandir($sumber);
    $source = $sumber."/";
    $destination = $tujuan."/";
	$delete = array();
	if (count($files) > 0) {
		foreach ($files as $file) {
			if (in_array($file, array(".",".."))) continue;
            if(pathinfo($file, PATHINFO_EXTENSION)=='zip') continue;

            if(in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg','JPG','jpeg','JPEG','png','PNG','xml'])){
            	if (copy($source.$file, $destination.$file)) {
					$delete[] = $source.$file;
				}
            }
		}
		if($del){
			foreach ($delete as $file) {
				unlink($file);
			}
		}
		return true;
	} else {
		return false;
	}
}
function sync_semeru_move_zip($sumber = '', $tujuan = '', $del=false) {
	$files = scandir($sumber);
    $source = $sumber."/";
    $destination = $tujuan."/";
	$delete = array();
	if (count($files) > 0) {
		foreach ($files as $file) {
			if (in_array($file, array(".",".."))) continue;
            // if(pathinfo($file, PATHINFO_EXTENSION)=='xml') continue;
            if(pathinfo($file, PATHINFO_EXTENSION)=='xml') {
            	$delete[] = $source.$file;
            	continue;
            }

            if(in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg','JPG','jpeg','JPEG','png','PNG','xml','zip','Zip','ZIP'])){
            	if (copy($source.$file, $destination.$file)) {
					$delete[] = $source.$file;
				}
            }
		}
		if($del){
			foreach ($delete as $file) {
				unlink($file);
			}
		}
		return true;
	} else {
		return false;
	}
}

function sync_semeru_zip($source = '', $filezip = '', $overwrite = false) {

	$modeOverwrite = ZIPARCHIVE::CREATE||ZIPARCHIVE::OVERWRITE;
	$modeCreate = ZIPARCHIVE::CREATE;
	$files = scandir($source);
	if(file_exists($filezip) && !$overwrite) { echo "false";/* return false;*/ }
	$valid_files = array();
	$max_count=500;
	$i = 0;
	$file_ada=0;
	foreach ($files as $file) {
		if(in_array($file, array(".",".."))) continue;
		$file_ada=1; break;
	}
	// print_r($files);
	// exit(json_encode($files));

	if($file_ada==0) return false;

	usort($files, function($a, $b){
        return filemtime($source.'/'.$a) > filemtime($source.'/'.$b);
    });

	// print_r($files);
	// exit;
	foreach ($files as $file) {
		if(in_array($file, array(".",".."))) continue;
		if(pathinfo($file, PATHINFO_EXTENSION)!='zip' && $i<$max_count){
			$valid_files[] = $file;

			
			
		// echo $file;

			$i++;
		} else{
			// echo "gagal ".$file."<br>";
		}


	}
	// echo "SOURCE : ".$source; echo "<br>";
	// echo "FILEZIP : ".$filezip; echo "<br>";
	// echo "OVERWRITE : ".$overwrite; echo "<br>";
	$delete=array();
	if(count($valid_files)) {

		if(file_exists($filezip)) unlink($filezip);
		$zip = new ZipArchive();
		if($zip->open($filezip, $overwrite ? $modeOverwrite : $modeCreate) !== true) {
			return false;
		}
		// echo $zip->open($filezip, $overwrite ? $modeOverwrite : $modeCreate); echo "<br>";
		foreach($valid_files as $file) {
			$zip->addFile($source.'/'.$file, $file);
			$delete[]=$source.'/'.$file;
			// echo "add file j ".$source.'/'.$file; echo "<br>";
		}
		$zip->close();

		// echo "coba cek file ini : ".$filezip."<br>";
		// echo "cek : ".file_exists($filezip);
		// exit;
		return file_exists($filezip) ? true : false;
		// if(count($delete)>0){
		// 	foreach ($delete as $v) {
		// 		unlink($v);
		// 	}
		// }
		// if(file_exists($filezip)){
		// 	// if(count($delete)>0){
		// 	// 	foreach ($delete as $v) {
		// 	// 		unlink($v);
		// 	// 	}
		// 	// }
		// 	return true;
		// }
	}else {
		return false;
	}
}
function sync_semeru_zip_xml($con = '',$source = '', $filezip = '', $overwrite = false) {

	$modeOverwrite = ZIPARCHIVE::CREATE||ZIPARCHIVE::OVERWRITE;
	$modeCreate = ZIPARCHIVE::CREATE;
	$files = scandir($source);
	if(file_exists($filezip) && !$overwrite) { echo "false";/* return false;*/ }
	$valid_files = array();
	$max_count=250;
	$i = 0;
	$tmp_fzip = explode("/",$filezip);
	$zip_name = $tmp_fzip[3];

	usort($files, function($a, $b){
        return filemtime($source.'/'.$a) > filemtime($source.'/'.$b);
    });
    
	foreach ($files as $file) {
		if(in_array($file, array(".",".."))) continue;
		if(pathinfo($file, PATHINFO_EXTENSION)!='zip' && $i<$max_count){
			$valid_files[] = $file;

			
			$query = mysqli_query($con, "UPDATE log_maintenance
				SET status='1', zip_name='$zip_name'
				WHERE fname='$file'  ");
			
		// echo $file;

			$i++;
		} else{
			// echo "gagal ".$file."<br>";
		}


	}
	// echo "SOURCE : ".$source; echo "<br>";
	// echo "FILEZIP : ".$filezip; echo "<br>";
	// echo "OVERWRITE : ".$overwrite; echo "<br>";
	$delete=array();
	if(count($valid_files)) {

		if(file_exists($filezip)) unlink($filezip);
		$zip = new ZipArchive();
		if($zip->open($filezip, $overwrite ? $modeOverwrite : $modeCreate) !== true) {
			return false;
		}
		// echo $zip->open($filezip, $overwrite ? $modeOverwrite : $modeCreate); echo "<br>";
		foreach($valid_files as $file) {
			$zip->addFile($source.'/'.$file, $file);
			$delete[]=$source.'/'.$file;
			// echo "add file j ".$source.'/'.$file; echo "<br>";
		}
		$zip->close();
		return file_exists($filezip) ? true : false;
		// if(count($delete)>0){
		// 	foreach ($delete as $v) {
		// 		unlink($v);
		// 	}
		// }
		// if(file_exists($filezip)){
		// 	// if(count($delete)>0){
		// 	// 	foreach ($delete as $v) {
		// 	// 		unlink($v);
		// 	// 	}
		// 	// }
		// 	return true;
		// }
	}else {
		return false;
	}
}

function sync_semeru_truncate($folder = '') {
	$files = scandir($folder);
	$delete = array();
	if (count($files) > 2) {
		foreach ($files as $file) {
			if (in_array($file, array(".",".."))) continue;
			$delete[] = $folder.'/'.$file;
		}
		foreach ($delete as $file) {
			//unlink($file);
		}
		return true;
	} else {
		return false;
	}
}