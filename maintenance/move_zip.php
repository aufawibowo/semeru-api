<?php
  include "function.php";
$today = date("Ym");


  $landing = "landing";
  $images = "images";
  $packing = "packing";
  $backup = "backup/";
  $filezip = './packing/'.date("Ymd").'.zip';
  $filezipXML = './packing/xml/'.date("Ymd").'.zip';
  $filezipImages = './packing/images/'.date("Ymd").'.zip';

// if (!file_exists($backup."/".$today)) {
//   $old = umask(0);
//   mkdir($backup."/".$today, 0777, true);
//   umask($old);
// 	}

$result=array();
if(!isset($_GET['a'])){
  $result=array("no action found");
}else{
  $apa=$_GET['a'];
  if($apa=='all'){
    $x=zip_xml();
    $result=array_merge($result, $x);
    // sleep(1);
    $i=zip_img();
    $result=array_merge($result, $i);
  }elseif($apa=='xml'){
    $result=zip_xml();
  }elseif($apa=='img'){
    $result=zip_img();
  }
}
echo json_encode($result);



// $a = 
// zip_xml();
// print_r($a);





// echo "<br/>";
// echo (sync_semeru_move($landing, $backup.$dirname)) ? "Proses moving file xml ke backup berhasil" : "Proses moving file ke backup xml gagal";
// echo "<br>";
// sleep(1);
// echo (sync_semeru_move($landing, $packing.'/xml', true)) ? "Proses moving file xml berhasil" : "Proses moving file xml gagal";
// echo "<br>";
// sleep(1);
// echo (sync_semeru_zip($packing."/xml", $filezipXML, true)) ? "Proses zip berhasil" : "Proses zip gagal";
// echo "<br>";
// sleep(1);

// echo (sync_semeru_move($images, $packing.'/images', true)) ? "Proses moving file images berhasil" : "Proses moving file images gagal";
// echo "<br>";
// sleep(1);
// echo (sync_semeru_zip($packing.'/images', $filezipImages, true)) ? "Proses zip berhasil" : "Proses zip gagal";
// echo "<br>";
function zip_xml(){
  $landing = "landing";
  $images = "images";
  $packing = "packing";
  $backup = "backup/";
  $filezip = './packing/'.date("Ymd").'.zip';
  $filezipXML = './packing/xml/'.date("Ymd").'.zip';
  $filezipImages = './packing/images/'.date("Ymd").'.zip';


  $dirname = date("Ymd");
  $backup = "backup/";
  $backup_dir = $backup.$dirname;
  if (!file_exists($backup_dir)) {
      mkdir($backup . $dirname, 0777);
      $result['dir']="In Bakcup : the directory ".$dirname." was successfully created.";
  } else {
      $result['dir']="In Bakcup : the directory ".$dirname." exists.";
  }
  $result['move_backup'] = sync_semeru_move($landing, $backup.$dirname) ? 1 : 0;
  $result['move_xml'] = sync_semeru_move($landing, $packing.'/xml', true) ? 1 : 0;
  $result['zip_xml'] = sync_semeru_zip($packing."/xml", $filezipXML, true) ? 1 : 0;
  return $result;
}
function zip_img(){
  $landing = "landing";
  $images = "images";
  $packing = "packing";
  $backup = "backup/";
  $filezip = './packing/'.date("Ymd").'.zip';
  $filezipXML = './packing/xml/'.date("Ymd").'.zip';
  $filezipImages = './packing/images/'.date("Ymd").'.zip';


  $result['move_img'] = sync_semeru_move($images, $packing.'/images', true) ? 1 : 0;
  // sleep(1);
  $result['zip_img'] = sync_semeru_zip($packing.'/images', $filezipImages, true) ? 1 : 0;
  return $result;
}

?>