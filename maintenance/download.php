<?php
  
if(file_exists('./packing/'.date("Ymd").'.zip')){
  $filezip = 'http://103.253.107.45/semeru-api/maintenance/packing/'.date("Ymd").'.zip';  
  header("Location : $filezip");
}

?>