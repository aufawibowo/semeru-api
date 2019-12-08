<?php

	$gaSql['user']       = "ngsemeru";
	$gaSql['password']   = "NGSemeru#2017";
	$gaSql['db']         = "telkomsel_semeru";
	$gaSql['server']     = "103.253.107.45";
	
	$gaSql['link'] =  mysqli_connect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		die( 'Could not open connection to server' );
	
	mysqli_connect( $gaSql['server'], $gaSql['user'], $gaSql['password']  ) or
		die( 'Could not open connection to server' );
	
	mysqli_select_db( $gaSql['link'], $gaSql['db'] ) or 
		die( 'Could not select database '. $gaSql['db'] );


?>
