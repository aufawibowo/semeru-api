<?php 
date_default_timezone_set('Asia/Jakarta');

// print_r($_POST);

$arrXmlName = @$_POST['xml_files'];
$landing_path = @$_POST['landing_path'];
$packing_path = @$_POST['packing_path'];
$filezip = $packing_path.date('YmdHis').'.zip';
$path_mt = 'maintenance/';

if(empty($arrXmlName)){
	exit(json_encode(['success'=>false,'zip_url'=>$path_mt.$filezip, 'msg'=>'list xml is empty']));
}else{
	$list_file_ok = [];
	foreach ($arrXmlName as $file_xml) {
		$source_xml = $file_xml;
		$cek = file_exists($source_xml);
		if($cek){ 

			if( copy($source_xml, $packing_path.basename($source_xml)) ){
				$list_file_ok[] = $source_xml;
			}
		}else{
			// echo "file tidak ada ".$source_xml ;
		}
	}
	// exit;

	if( empty($list_file_ok) ){ exit( json_encode(['success'=>true, 'zip_url'=>$path_mt.$filezip, 'msg'=>'Cannot copy file']) ); }
	// print_r($list_file_ok);


	$list_zip = scandir($packing_path, true);
	$_zip_lama = date('Y-m-d H:i:s', strtotime('-1 hours', strtotime(date('Y-m-d H:i:s'))) );
	foreach ($list_zip as $_file) {
		if(in_array($_file, [".",".."] )) continue;
        if(pathinfo($_file, PATHINFO_EXTENSION)!='zip') continue;
        $_file_created = date('Y-m-d H:i:s', filemtime($packing_path.'/'.$_file));
        if($_file_created<$_zip_lama) {
        	unlink($packing_path.$_file);
			// echo "hapus :".$_file_created."<br>";
        }else{
			// echo "ridak hapus :".$_file_created."<br>";
        }
		// echo date('Y-m-d H:i:s', filemtime($packing_path.'/'.$_file))."<br>";
	}

	$zip = new ZipArchive();
	$modeCreate = ZIPARCHIVE::CREATE;
	$modeOverwrite = ZIPARCHIVE::CREATE||ZIPARCHIVE::OVERWRITE;
	if($zip->open($filezip, $modeCreate ) !== true) { 
		exit(json_encode(['success'=>true, 'zip_url'=>$path_mt.$filezip, 'msg'=>'Cannot create zip'])); 
	}
	$list_delete_from_landing = [];
	foreach($list_file_ok as $source_xml) {
		// echo $source_xml.' : '.basename($source_xml)."<br>";
		$zip->addFile($source_xml, basename($source_xml));
		$list_delete_from_landing[] = $landing_path.basename($source_xml);
	}
	$zip->close();

	//delete from landing
	if(!empty($list_delete_from_landing)){
		foreach ($$list_delete_from_landing as $_file) { unlink($_file); }
	}
	exit(json_encode(['success'=>true, 'zip_url'=>$path_mt.$filezip, 'msg'=>'Ok!']));
}



 ?>