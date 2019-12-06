<?php
  include "function_v2.php";

  header("Content-type: application/json; charset=utf-8");


$today = date("Ym");

if(!isset($_GET['zipname'])){
  return "zipname g ada";
}

  $zipname = $_GET['zipname'];

  $landing = "landing";
  $images = "images";
  $packing = "packing";
  $backup = "backup/";
  $filezip = './packing/'.$zipname.'.zip';
  $filezipXML = './packing/xml/'.date("Ymd").'.zip';
  $filezipImages = './packing/images/'.$zipname.'.zip';

  $result['move_img'] = sync_semeru_move($images, $packing.'/images', true) ? 1 : 0;
  // sleep(1);
  $result['zip_img'] = sync_semeru_zip($packing.'/images', $filezipImages, true) ? 1 : 0;
  exit(json_encode($result));
  // return $result;


?>