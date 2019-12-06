<?php




	header("Content-type: application/json; charset=utf-8");
define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);
date_default_timezone_set('Asia/Jakarta');
// function sync_semeru_move($sumber = '', $tujuan = '', $del=false) {
	$files = scandir("packing/images");
    // $source = $sumber."/";
    // $destination = $tujuan."/";
	$fname = array();

	if (count($files) > 0) {
		foreach ($files as $file) {
			if (in_array($file, array(".",".."))) continue;
            if(pathinfo($file, PATHINFO_EXTENSION)=='zip') continue;

            if(in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg','JPG','jpeg','JPEG','png','PNG'])){

            	$fname[] = $file;

    //         	if (copy($source.$file, $destination.$file)) {
				// 	$delete[] = $source.$file;
				// }
            }
		}


		$response['count'] = count($fname);
		die(json_encode($response));
	}
?>