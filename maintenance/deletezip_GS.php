<?php
// $data = json_decode(file_get_contents('php://input'), true);
$data = $_POST;

// echo "========================================="; echo "\n";
// echo "DELETE FILE ON DS"; echo "\n";
// echo "========================================="; echo "\n";
echo "[DS Response] Try to delete file please Wait...\n";
echo "count data ".count($data);

if(count($data)>0){
	if(isset($data['arr_xml']) && !empty(@$data['arr_xml'])){
		$delete_file_xml_success=array();
		$delete_file_xml_failed=array();
		foreach ($data['arr_xml'] as $fname) {
			$name = 'packing_GS/'.$fname;
			// echo 'name : '.$name."<br/>";
			if(file_exists($name) ){
				if(@unlink($name)){
					$delete_file_xml_success[] = "[UNLINK SUCCESS] : ".$name;
					//echo "=> DELETE ".$name." SUCCESS \n";
				}else{
					$delete_file_xml_failed[] = "[UNLINK FAILED] : ".$name;
					// echo "=> DELETE ".$name." FAILED \n";
				}
			}else{
				$delete_file_xml_failed[] = "[FILE NOT FOUND] : ".$name;
				// echo "=> FILE ".$name." NOT FOUND \n";
			}
		}
		echo "=> DELETE FILE XML SUCCESS : ".count($delete_file_xml_success)."/".count($data['arr_xml'])."\n";
		echo "=> DELETE FILE XML FAILED : ".count($delete_file_xml_failed)."\n";
		foreach ($delete_file_xml_failed as $fail) {
			echo " -".$fail,"\n";
		}
		// echo "\n";
	}

	if(isset($data['xml']) && !empty(@$data['xml'])){
		$name = 'packing_GS/'.$data['xml'];
		//echo 'name : '.$name."<br/>";
		if(file_exists($name) ){
			if(@unlink($name)){
				echo "=> DELETE FILE XML ZIP ".$name." SUCCESS \n";
			}
			else{
				echo "=> DELETE FILE XML ZIP ".$name." FAILED \n";
			}
		}else{
			echo "=> FILE XML ZIP ".$name." NOT FOUND \n";
		}
		// echo "\n";
	}


	if(isset($data['arr_img']) && !empty(@$data['arr_img'])){
		$delete_file_img_success=array();
		$delete_file_img_failed=array();
		foreach ($data['arr_img'] as $fname) {
			$name = 'packing_GS/images/'.$fname;
			// echo 'name : '.$name."<br/>";
			if(file_exists($name) ){
				if(@unlink($name)){
					$delete_file_img_success[] = "[UNLINK SUCCESS] : ".$name;
					// echo "=> DELETE ".$name." SUCCESS \n"; echo "<br>";
				} else{
					$delete_file_img_failed[] = "[UNLINK FAILED] : ".$name;
					// echo "=> DELETE ".$name." FAILED \n"; echo "<br>";
				}
			}else{
				$delete_file_img_failed[] = "[FILE NOT FOUND] : ".$name;
				// echo "=> FILE ".$name." NOT FOUND \n";  echo "<br>";
			}
		}
		echo "=> DELETE FILE IMG SUCCESS : ".count($delete_file_img_success)."/".count($data['arr_img'])."\n";
		echo "=> DELETE FILE IMG FAILED : ".count($delete_file_img_failed)."\n";
		foreach ($delete_file_img_failed as $fail) {
			echo " -".$fail,"\n";
		}
		// echo "\n";
	}


	if(isset($data['img']) && !empty(@$data['img'])){
		$name = 'packing_GS/images/'.$data['img'];
		// echo 'name : '.$name."<br/>";
		if(file_exists($name)){
			if(@unlink($name)){
				echo "=> DELETE FILE IMG ZIP ".$name." SUCCESS \n";
			}
			else{
				echo "=> DELETE FILE IMG ZIP ".$name." FAILED \n";
			}
		}else{
			echo "=> FILE IMG ZIP ".$name." NOT FOUND \n";
		}
		// echo "\n";
	}
}
  
/*if(file_exists('./packing/'.date("Ymd").'.zip')){
  unlink('./packing/'.date("Ymd").'.zip');
  echo "Delete Sukses";
}*/

?>