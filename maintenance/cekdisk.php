<?php

// folder to check
$images_backup_dir = '/backup/images_backup/';
// $dir = '/backup/images_backup/supplying_power/';
$semeru_api_dir = '/var/www/html/semeru-api/';
// $semeru_api_dir = '../maintenance/backup/';

$ln = "\n";
// this function will convert bytes value to KB, MB, GB and TB
function convertSize( $bytes )
{
        $sizes = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        for( $i = 0; $bytes >= 1024 && $i < ( count( $sizes ) -1 ); $bytes /= 1024, $i++ );
                return( round( $bytes, 2 ) . " " . $sizes[$i] );
}

function getDiskSize($path){
  $str = ""; $ln = "\n";
  $total = disk_total_space($path);
  $free = disk_free_space($path);
  $used = $total-$free;
  $percentage_used = sprintf('%.2f',($used / $total) * 100);

  $f_total = convertSize($total);
  $f_free = convertSize($free);
  $f_used = convertSize($used);
  $f_percentage_used = $percentage_used."%";

  $str = 
    "Dir  : ".$path.$ln.
    "Used : ".$f_used." / ".$f_total." (".$f_percentage_used.")".$ln.
    "Free : ".$f_free.$ln;
    return $str;
}

// $sparator="-----------------------------";
$sparator="-";
$diskstatus =  
'<b>[ STATUS DISK SIZE ]</b>'.$ln.
$sparator.$ln.
getDiskSize($images_backup_dir).
$sparator.$ln.
getDiskSize($semeru_api_dir)
.$sparator.$ln.
"Updated At : ".date('Y-m-d H:i:s');

$arr_receiver=[
  // '388100585',//agus
  '-1001176006050',//programmer
  '-1001318581621'
];
echo "<pre>";
// echo $diskstatus;
// exit;
//============ kirim ke telegram ===============

 $TOKEN  = "499271668:AAHDVOjfsKVW4dF92x02FhTeHCGsYWR74x4";  // ganti token ini dengan token bot mu
      // $chatid = "442420933"; // ini id saya di telegram @hasanudinhs silakan diganti dan disesuaikan
      // $chatid = '-1001176006050'; // grup programmer
      //$chatid = '631197799'; rio
      // $chatid = '388100585'; // agus
      $pesan  = $diskstatus;

      // ----------- code -------------

      $method = "sendMessage";
      $url    = "https://api.telegram.org/bot" . $TOKEN . "/". $method;//499271668:
      //https://api.telegram.org/botAAHDVOjfsKVW4dF92x02FhTeHCGsYWR74x4 

      $header = [
      	"X-Requested-With: XMLHttpRequest",
      	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
      ];

      // hapus 1 baris ini:
      // die('Hapus baris ini sebelum bisa berjalan, terimakasih.');

      foreach($arr_receiver as $chatid){
        $post = [
          'chat_id' => $chatid,
         'parse_mode' => 'HTML', // aktifkan ini jika ingin menggunakan format type HTML, bisa juga diganti menjadi Markdown
          'text' => $pesan
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_REFERER, $refer);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $datas = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
      }

      $debug['text'] = $pesan;
      $debug['code'] = $status;
      $debug['status'] = $error;
      $debug['respon'] = json_decode($datas, true);

      echo $diskstatus;

?>


