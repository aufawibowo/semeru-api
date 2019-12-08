<?php
  
if(file_exists('./packing/manual'.date("Ymd").'.zip')){
  unlink('./packing/manual'.date("Ymd").'.zip');
  echo "Delete Sukses";
}

?>