<?php

	$files = glob('packing_GS/images/*.zip'); // get all file names
	foreach($files as $file){ // iterate files
		if(is_file($file))
    	unlink($file); // delete file
	}

?>