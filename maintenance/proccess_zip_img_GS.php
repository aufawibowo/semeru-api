<?php
  include "function_v2.php";

  header("Content-type: application/json; charset=utf-8");


$today = date("Ym");

if(!isset($_GET['zipname'])){
  return "zipname g ada";
}

  $zipname = $_GET['zipname'];

  $landing = "landing_GS";
  $images = "images_GS";
  $packing = "packing_GS";
  $backup = "backup/";
  $filezip = './packing_GS/'.$zipname.'.zip';
  $filezipXML = './packing_GS/xml/'.date("Ymd").'.zip';
  $filezipImages = './packing_GS/images/'.$zipname.'.zip';

  $result['move_img'] = sync_semeru_move($images, $packing.'/images', true) ? 1 : 0;
  // sleep(1);
  $result['zip_img'] = sync_semeru_zip($packing.'/images', $filezipImages, true) ? 1 : 0;
  exit(json_encode($result));
  // return $result;


?>