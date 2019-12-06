<?php

	$files = glob('packing_v2/xml_zipping/*'); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file))
    	unlink($file); // delete file
	}

?>