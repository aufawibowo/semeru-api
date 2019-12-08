<?php 
date_default_timezone_set('Asia/Jakarta');

ini_set("memory_limit","64M");

$base_dir='images_backup';
$xml_dir='packing_v2';

function delete_image_new($dir)
{
	$date_now = date('Y-m-d');
	$delete_date_strtotime = strtotime($date_now." -3 day");
	$delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);

	$list_file = scandir($dir, true);
	foreach ($list_file as $files) {
		if(in_array($files, [".",".."] )) continue;
		if (is_dir($dir.'/'.$files)) {
			// echo "folder ".$dir.'/'.$files; echo "<br>";
			// if(strpos($files, '2018')!==false){
			// 	$r = rmdir($dir.'/'.$files);
			// 	print_r($r);
			// 	echo "###<hr>";
			// }
			delete_image_new($dir.'/'.$files);
		} else{
			// echo "file"; 
			// continue;
			$_file_created = date('Y-m-d H:i:s', filemtime($dir.'/'.$files));
			if($_file_created<$delete_date) {
	        	unlink($dir.'/'.$files);
				echo "hapus : ".$dir.'/'.$files."<br>";
	        }
		}
	}
}

function delete_xml($dir)
{
	$date_now = date('Y-m-d');
	$delete_date_strtotime = strtotime($date_now." -4 month");
	$delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);

	$list_file = scandir($dir, true);
	foreach ($list_file as $files) {
		if(in_array($files, [".",".."] )) continue;
		if (is_dir($dir.'/'.$files)) {
			//echo "folder";
			delete_xml($dir.'/'.$files);
		} else{
			//echo "file";
			$_file_created = date('Y-m-d H:i:s', filemtime($dir.'/'.$files));
			if($_file_created<$delete_date) {
	        	unlink($dir.'/'.$files);
				echo "hapus : ".$dir.'/'.$files."\n";
	        }
		}
	}
}

// delete_image_new($base_dir);
//delete_xml($xml_dir);


$_path='images_backup';
echo "Processing...<pre>";
function test($path){
	// echo "start--";
	// echo "<pre>";
	// $path = 'images_backup';
	$max=0;
	$result = scandir($path, true);
	foreach ($result as $key => $file) {
		
		if(in_array($file, [".",".."] )) continue;
		// if($max>1) continue;
		// if(!is_file($path.'/'.$file)) continue;
		// echo $path.'/'.$file.'<br>';
		if (is_dir($path.'/'.$file)) {
			// echo "#dir<br>";
			
			// echo $path.'/'.$file.'<br>';
			
			// $pos = strpos($path.'/'.$file, '2019/10');
			// if( $pos!==false && $pos==21){
			rmdir($path.'/'.$file);
				// test($path.'/'.$file);

			// }
			
			
		}else{
			echo $path.'/'.$file.'<br>';
			$pos = strpos($path.'/'.$file, '2019/10');
			if( $pos!==false && $pos==21){
				$max++;
				// echo $path.'/'.$file.' pos: '.$pos.'<br>';
				$str = explode('/', $path.'/'.$file);
				$site_id = strtoupper($str[1]);

				$target_dir='/var/www/html/semeru-api/images_backup/maintenance/2019/10/'.$site_id;
				if (!file_exists($target_dir)) {
					// $a=
					mkdir($target_dir, 0777, true);

				}

				copy($path.'/'.$file, $target_dir.'/'.$file);
				// echo "=>Copying...<br>";
				if(file_exists($target_dir.'/'.$file)){
					// echo "==>file exist<br>";

					$unlink = unlink($path.'/'.$file);
					// if($unlink){
					// 	echo "===>Unlink SUccess<be>";
					// }else{
					// 	echo "===>Unlink Failed<be>";
					// }
				}else{
					// echo "==>file not exist<br>";
				}
			}
			// echo $path."<br>";
			// echo $file.'<hr>';
		}
		
	}
	// $total_file = count($result);
	// echo "total file : ".$total_file;
	// echo "work";
	// foreach ($variable as $key => $value) {
	// 	# code...
	// }

}

test($_path);

?>