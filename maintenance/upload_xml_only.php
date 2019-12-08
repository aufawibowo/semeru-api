<?php 
$tmp_count = 0;
$response = [];
foreach ($_FILES['ceklist']['name'] as $f => $name) {  
	
	$source = $_FILES['ceklist']['tmp_name'][$tmp_count];
	$filename = $_FILES['ceklist']['name'][$tmp_count];
	echo $filename;
	echo ' move to landing_v2/'.$filename;
	echo "#";
	$response[] = move_uploaded_file($_FILES['ceklist']['tmp_name'][$tmp_count], 'landing_v2/'.$filename);

	$tmp_count++;

}
print_r($response);


?>