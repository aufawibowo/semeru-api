<?php
  include "function.php";
$today = date("Ym");


  $landing = "manual";
  $packing = "packing";
  $backup = "backup";
  $filezip = "./packing/manual_".date("Ymd").".zip";

if (!file_exists($backup."/".$today)) {
		mkdir($backup."/".$today, 0777, true);
	}

  echo (sync_semeru_move($landing, $packing)) ? "Proses moving file xml berhasil" : "Proses moving file xml gagal";
  echo "<br>";
  sleep(1);
  echo (sync_semeru_zip($packing, $filezip, true)) ? "Proses zip berhasil" : "Proses zip gagal";
  echo "<br>";
  echo (sync_semeru_move($packing, $backup."/".$today)) ? "Proses moving file xml ke backup berhasil" : "Proses moving file xml ke backup gagal";
  echo "<br>";
?>