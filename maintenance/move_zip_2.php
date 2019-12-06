<?php
  include "function_v2.php";

  header("Content-type: application/json; charset=utf-8");


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

  $server   = "103.253.107.45";
  $username   = "ngsemeru";
  $password = "NGSemeru#2017";
  $database   = "telkomsel_semeru";

  
  // $server   = "localhost";
  // $username   = "root";
  // $password = "";
  // $database   = "telkomsel_semeru";
  
  $con = mysqli_connect($server, $username, $password, $database);
  if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

  #CONFIG BARU
  $landing = "landing_v2";
  $images = "images";
  $packing = "packing_v2";
  $backup = "backup/";
  $filezip = './packing_v2/'.date("Ymd").'.zip';
  $filezipXML = './packing_v2/xml_zipping/'.date("Ymdhis").'.zip';
  $filezipImages = './packing_v2/images/'.date("Ymd").'.zip';

   #CONFIG LAMA
  // $landing = "landing";
  // $images = "images";
  // $packing = "packing";
  // $backup = "backup/";
  // $filezip = './packing/'.date("Ymd").'.zip';
  // $filezipXML = './packing/xml/'.date("Ymd").'.zip';
  // $filezipImages = './packing/images/'.date("Ymd").'.zip';


  $dirname = date("Ymd");
  $backup = "backup/";
  $backup_dir = $backup.$dirname;
  if (!file_exists($backup_dir)) {
      mkdir($backup . $dirname, 0777);
      $result['dir']="In Bakcup : the directory ".$dirname." was successfully created.";
  } else {
      $result['dir']="In Bakcup : the directory ".$dirname." exists.";
  }

  #FUNGSI AMBIL ZIP BARU
  $result['move_backup'] = sync_semeru_move($landing, $backup.$dirname) ? 1 : 0;
  $result['move_xml'] = sync_semeru_move($landing, $packing.'/xml_zipping', true) ? 1 : 0;
  $result['zip_xml'] = sync_semeru_zip_xml($con, $packing."/xml_zipping", $filezipXML, true) ? 1 : 0;
  $result['move_zip'] = sync_semeru_move_zip($packing.'/xml_zipping', $packing.'/xml', true) ? 1 : 0;

  #FUNGSI AMBIL ZIP LAMA
  // $result['move_backup'] = sync_semeru_move($landing, $backup.$dirname) ? 1 : 0;
  // $result['move_xml'] = sync_semeru_move($landing, $packing.'/xml', true) ? 1 : 0;
  // $result['zip_xml'] = sync_semeru_zip($packing."/xml", $filezipXML, true) ? 1 : 0;



  // $getQuery = mysqli_query($con, "select zip_name as zipname from log_maintenance where uri_tmp like '%landing_v2/%' and status = 1 group by zipname;");
  // $zipname = array();
  // while($ziprow = $getQuery->fetch_assoc()){
  //   $zipname[] = $ziprow['zipname'];
  // }
  // $result['zip_ready'] = $zipname;

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