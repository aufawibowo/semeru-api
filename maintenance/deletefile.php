<?php
	$data = json_decode(file_get_contents('php://input'), true);
	if(!is_array($data) && empty($data)){
		exit( "data tidak ada" );
	}

	$accept_xml=$data['xml'];
	$accept_img=$data['img'];

	$xml_dir = "landing"; // folder xml
	$img_dir = "images"; // folder img

	if($accept_xml){ // jika data xml dikehendaki utk di hapus
		sleep(1);
		recursiveRemoveDirectory($xml_dir);
		$xml_sukses="sukses hapus file xml di DS<br/>";
		echo $xml_sukses;
	}

	if($accept_img){ // jika data img dikehendaki utk di hapus
		// echo "hapus img";
		sleep(1);
		recursiveRemoveDirectory($img_dir);
		$img_sukses="sukses hapus file img di DS<br/>";
		echo $img_sukses;
	}

	// echo json_encode(array($xml_sukses,$img_sukses));

	function recursiveRemoveDirectory($directory){
	    foreach(glob("{$directory}/*") as $file)
	    {
	        if(is_dir($file)) { 
	            $this->recursiveRemoveDirectory($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($directory);
	}
?>