<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class FireBaseController extends Controller
{

public function testCurl(){

	// $title = $request->input('title');
	// $body = $request->input('body');
	// $to_token_id = $request->input('to_token_id');
	// $type_name = $request->input('type_name');
	// $type_id = $request->input('type_id');
	// $type = $request->input('type');
	
	$post = [
	'title' => 'coba',
	'body' => 'coba',
	'to_token_id'   => '/topics/RTPO_ALL',
	'type_name'   => 'mbp_id',
	'type_id'   => 7,
	'type'   => 'RTPO_RETURN_MBP',
	];

	// $title = $title;
	// $body = $body;
	// $to_token_id = $to_token_id;
	// $type_name = $type_name;
	// $type_id = $type_id;
	// $type = $type;

	$title = 'coba';
	$body = 'coba';
	$to_token_id = '/topics/RTPO_ALL';
	$type_name = 'mbp_id';
	$type_id = 7;
	$type = 'RTPO_RETURN_MBP';


	if (!defined('API_ACCESS_KEY')){
	define('API_ACCESS_KEY', 'AAAAo6mi6uY:APA91bF5Jrgp7pqCX40LO0WQb6v-eLKd5xIP0xjxivSdlpDg5_iOisegSNQR0GSYwmeICJnumEbckFR6RextiSTkhUA0xBKk-HfMMNzRAWmyXPZzi5FxJvaYescfgyD4s3YTUwB9X78o');
	}


if ($to_token_id=='') {
	$to_token_id = 'frMgfkXK4KE:APA91bHK76rxHLyiIC2VUYcjJUAdxqJdYC2HoQqqwFxBJ6GiUN3b5BFkj9RYTaLZ9mQi8dYU4SwhEp_NAHwmGibH-3sGnA6pwi4_nSP5oUcDUeYshRYKwDPlvYZQ5MlsQ2aCmW7nS35W';
	}


	$msg = array
	(
	'Message'   => $body,
	'Title'   => $title,
	"$type_name"   => $type_id,
	'Type'   => $type,
	);

	if (strlen($to_token_id)>20) { 
	$getToken['id'] = $to_token_id;
	$registrationIds = array( $getToken['id'] );
	$fields = array
	(
		'registration_ids'  => $registrationIds,
		'data'      => $msg
	);
	}else{
	$fields = array
	(
		'to'  => $to_token_id,
		'data'      => $msg
	);
	}

	$headers = array
	(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json'
	);

	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
	curl_setopt( $ch,CURLOPT_POST, true );
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	// curl_setopt( $ch, CURLOPT_TIMEOUT_MS, 500);
	$result = curl_exec($ch );
	if(curl_errno($ch)){
	// return response('error:' . curl_error($ch));
	$data['error'] =  'error:' . curl_error($ch);
	}
	else{
	$data['error'] =  $result;  
	}


	curl_close( $ch );

	$data['data'] = json_decode($result, true);
	$data['token to'] = $to_token_id;
	return $data;
	// var_export($response);
	// curl_close($ch);

	// return response('ok');
	// if(curl_errno($ch)){
	//   // return response('error:' . curl_error($ch));
	//   echo 'error:' . curl_error($ch);
	// }
	// else{
	//   echo $content;  
	// }

}

public function sendNotifFast(/*Request $request*/$title,$body,$to_token_id,$type_name, $type_id,$type){

	// $title = $request->input('title');
	// $body = $request->input('body');
	// $to_token_id = $request->input('to_token_id');
	// $type_name = $request->input('type_name');
	// $type_id = $request->input('type_id');
	// $type = $request->input('type');
	
	$post = [
	'title' => $title,
	'body' => $body,
	'to_token_id'   => $to_token_id,
	'type_name'   => $type_name,
	'type_id'   => $type_id,
	'type'   => $type,
	];

	$ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru-api/public/sendNotificationTest');
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru_api/public/sendNotificationTest');
								//sakkarep.web.id/semeru_api/public/sendNotificationTest
	curl_setopt($ch, CURLOPT_URL, 'sakkarep.web.id/semeru_api/public/sendNotificationTest');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	// curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 120);

	// curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10);
	// curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	// curl_setopt($ch, CURLOPT_NOSIGNAL, 10);
	// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 125);

	// CURLOPT_TIMEOUT and CURLOPT_CONNECTTIMEOUT
	// CURLOPT_TIMEOUT_MS and CURLOPT_CONNECTTIMEOUT_MS


	$response = curl_exec($ch);
	// var_export($response);
	curl_close($ch);

	// return response('ok');
}

public function sendNotificationTest(Request $request)
{
	// $title,$body,$to_token_id,$type_name, $type_id ,$type
	$title = $request->input('title');
	$body = $request->input('body');
	$to_token_id = $request->input('to_token_id');
	$type_name = $request->input('type_name');
	$type_id = $request->input('type_id');
	$type = $request->input('type');


	if (!defined('API_ACCESS_KEY')){
	define('API_ACCESS_KEY', 'AAAAzsdFy4g:APA91bFW7k_m2qkp0o6F1PV_z_SwStripBsTwzIVptEFhWlkgh65DIvt6Bxj6pH-f7KrVxP9nUeFlw41LyeGabMDgOzhbBb-5tDD5fQOJ7NpHHIpQLrKUrHAFemNsZTCg4r4ifWNJc25');
	}


if ($to_token_id=='') {
	$to_token_id = 'da8Di-QHD-4:APA91bE8wJzYH4DxejqXj08lneteC0oL_fuEUKIf47_Dl-jGqn7v5fNvV2kzhbs45lYG6aejSpZFGl1WdAJmUD7keHXTlLqT3KjoFGr4JqJb8v1FbDRM5D-LK-HnlKx_831Kx64vk3le';
	}


	$msg = array
	(
	// 'message'   => $body,
	'title'   => $title,
	'type'   => $type,
	'body' => $body,
	);

	$fields = array
	(
		'registration_ids'  => $to_token_id,
		'data'      => $msg,
		//'notification'=>[
		//  'body'=>$body,
		//  'title'=>$title,
		//],
	);

	$headers = array
	(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json'
	);

	$ch = curl_init();
	curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
	// curl_setopt( $ch,CURLOPT_POST, true );


	$xfield='{
"to" : "dx9DUYNeOO0:APA91bGq0vLGnPPcY26uUHQZMzUvC2BfZs7qP2D_qmc-zQ0eq_FZyO_Ax_6O29Q60kw-zZMGiuvIHPbDMNGzLayg84MZIm15MYWaenpcMqr5SeK_fBdksg1GgcFHH7eVM8XZJrNJoZKB",
"notification" : {
	"body" : "Body of Your Notification",
	"title": "Title of Your Notification"
},
"data" : {
	"body" : "Body of Your Notification in Data",
	"title": "Title of Your Notification in Title",
	"key_1" : "Value for key_1",
	"key_2" : "dx9DUYNeOO0:APA91bGq0vLGnPPcY26uUHQZMzUvC2BfZs7qP2D_qmc-zQ0eq_FZyO_Ax_6O29Q60kw-zZMGiuvIHPbDMNGzLayg84MZIm15MYWaenpcMqr5SeK_fBdksg1GgcFHH7eVM8XZJrNJoZKB"
}
}';

//'{"to":"dx9DUYNeOO0:APA91bGq0vLGnPPcY26uUHQZMzUvC2BfZs7qP2D_qmc-zQ0eq_FZyO_Ax_6O29Q60kw-zZMGiuvIHPbDMNGzLayg84MZIm15MYWaenpcMqr5SeK_fBdksg1GgcFHH7eVM8XZJrNJoZKB",
//"data":{"message":"Iam Body","title":"NG-Semeru","type_name":"APR009","type":"Dummy","body":"Iam Body"}}';
// print_r(json_encode( $fields ));
// exit;
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
	curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	// curl_setopt( $ch,CURLOPT_POSTFIELDS, $xfield);
	curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	$result = curl_exec($ch );
	curl_close( $ch );

	$data['data'] = json_decode($result, true);
	$data['token to'] = $to_token_id;
	return $data;
}

	
public function sendNotification($title,$body,$to_token_id,$type, $type_id,$type_detail){

	if (!defined('API_ACCESS_KEY')){
	define('API_ACCESS_KEY', 'AAAAzsdFy4g:APA91bFW7k_m2qkp0o6F1PV_z_SwStripBsTwzIVptEFhWlkgh65DIvt6Bxj6pH-f7KrVxP9nUeFlw41LyeGabMDgOzhbBb-5tDD5fQOJ7NpHHIpQLrKUrHAFemNsZTCg4r4ifWNJc25');
}


	$msg = array
	(
	'title'   => $title,
	'body' => $body,
	'type'   => $type,
	);

$fields = array
(
	'registration_ids'  => $to_token_id,
	'data' => $msg,
	//'notification'=> [
	//  'body'=>$body,
	//  'title'=>$title,
	//],
);

$headers = array
(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json'
);

$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send ' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );

$data['data'] = json_decode($result, true);
$data['token to'] = $to_token_id;
return $data;
}



	function checkMyRTPOtopic($rtpo_name){
	switch ($rtpo_name) {
		case "RTPO PROBOLINGGO":
		$myrtpo = 'RTPO_PROB';
		break;
		case "RTPO MALANG":
		$myrtpo = 'RTPO_MALANG';
		break;
		case "RTPO JEMBER":
		$myrtpo = 'RTPO_JEMBER';
		break;
		case "RTPO BANYUWANGI":
		$myrtpo = 'RTPO_BANYUWANGI';
		break;
		case "RTPO MADIUN":
		$myrtpo = 'RTPO_MADIUN';
		break;
		case "RTPO LAMONGAN":
		$myrtpo = 'RTPO_LAMONGAN';
		break;
		case "RTPO BANGKALAN":
		$myrtpo = 'RTPO_BANGKALAN';
		break;
		case "RTPO TULUNGAGUNG":
		$myrtpo = 'RTPO_TULUNGAGUNG';
		break;
		case "RTPO PASURUAN":
		$myrtpo = 'RTPO_PASURUAN';
		break;
		case "RTPO PONOROGO":
		$myrtpo = 'RTPO_PONOROGO';
		break;
		case "RTPO SIDOARJO":
		$myrtpo = 'RTPO_SIDOARJO';
		break;
		case "RTPO SURABAYA SELATAN":
		$myrtpo = 'RTPO_SURABAYA_SELATAN';
		break;
		case "RTPO SURABAYA PUSUTA":
		$myrtpo = 'RTPO_SURABAYA_PUSUTA';
		break;
		case "RTPO SURABAYA BARAT":
		$myrtpo = 'RTPO_SURABAYA_BARAT';
		break;
		case "RTPO SURABAYA TIMUR":
		$myrtpo = 'RTPO_SURABAYA_TIMUR';
		break;
		case "RTPO KEDIRI":
		$myrtpo = 'RTPO_KEDIRI';
		break;
		case "RTPO PAMEKASAN":
		$myrtpo = 'RTPO_PAMEKASAN';
		break;
		default:
		// $myrtpo = null;
		// $fmc_data = DB::table('fmc')
		// ->select('*')
		// ->where('fmc_id','=',$fmc_id)
		// ->first();
		// $myfmc = @$fmc_data->fmc_alias.'_'.@$fmc_data->regional;
		$myrtpo = str_replace(' ', '_', $rtpo_name);
		break;
	}
	return($myrtpo);
	}

	function checkMyFMCtopic($fmc_id){
	switch ($fmc_id) {
		case "1":
		$myfmc = 'TIN';
		break;
		case "2":
		$myfmc = 'IDE';
		break;
		case "3":
		$myfmc = 'XTE';
		break;
		case "4":
		$myfmc = 'TBA';
		break;
		case "5":
		$myfmc = 'BMG';
		break;
		case "6":
		$myfmc = 'KIS';
		break;
		case "7":
		$myfmc = 'SPM';
		break;
		default:

		$fmc_data = DB::table('fmc')
		->select('*')
		->where('fmc_id','=',$fmc_id)
		->first();

		$myfmc = @$fmc_data->fmc_alias.'_'.@$fmc_data->regional;


		$myfmc = str_replace(' ', '_', $myfmc);
		break;
	}
	return($myfmc);
	}



	public function formatTelegram($messageId, $txt, $cluster){
	switch ($messageId) {
		case "sendTicketCorrective":
		$message = '[ TIKET CORRECTIVE CLUSTER '.$cluster.' ]
'.$txt;
		break;
		case "sendTicketMBP":
		$message = '[ TIKET MBP CLUSTER '.$cluster.' ]
'.$txt;
		break;
		case "ticketMBPDone":
		$message = 'XTE';
		break;
		case "TicketMBPCancel":
		$message = 'TBA';
		break;
		case "icketCorrective":
		$message = 'BMG';
		break;
		default:

		break;
	}
	return($message);
	}

	public function sendNotificationQueueTelegram(){

	date_default_timezone_set("Asia/Jakarta");
	$date_now =date('Y-m-d H:i:s');

	$queue_telegram_data = DB::table('queue_telegram')
	->select('*')
	->where('sent','=',0)
	->get();

	foreach ($queue_telegram_data as $param) {

		$this->sendNotificationTelegram($param->message, $param->chat_id);
		
		$update_queue_telegram_data = DB::table('queue_telegram')
		->where('id', $param->id)
		->update(
		[
			'sent' => '1',
			'send_at' => $date_now,
		]
		);
	}

	$res['success'] = true;
	$res['data'] = $queue_telegram_data;
	return response($res);

	}

	public function sendNotificationTelegram($txtMessage, $chatid){


		/* 
	Simple File untuk Ngetes Send Pesan ke Bot
	Memiliki banyak kegunaan dan tujuan
	
	misalnya ngetes pesan dengan format tertentu, line break, char khusus, dll.
	bisa dipergunakan juga untuk test hosting, cronjob, dan segala test lainnya. 
	
	Jika menggunakan mode GET :
	- Line break (ENTER) = %0A
	- Space = %20 
	Atau rawurlencode($string);
	
	Contoh dibawah ini menggunakan mode POST. Baris baru cukup dengan \n.
	
	* -----------------------
	* Grup @botphp
	* Jika ada pertanyaan jangan via PM
	* langsung di grup saja.
	* ----------------------
	
	*/

	$TOKEN  = "499271668:AAHDVOjfsKVW4dF92x02FhTeHCGsYWR74x4";  // ganti token ini dengan token bot mu
	// $chatid = "442420933"; // ini id saya di telegram @hasanudinhs silakan diganti dan disesuaikan
	$chatid = $chatid; // ini id saya di telegram @hasanudinhs silakan diganti dan disesuaikan
	$pesan  = $txtMessage;

	// ----------- code -------------

	$method = "sendMessage";
	$url    = "https://api.telegram.org/bot" . $TOKEN . "/". $method;
	$post = [
	'chat_id' => $chatid,
	// 'parse_mode' => 'HTML', // aktifkan ini jika ingin menggunakan format type HTML, bisa juga diganti menjadi Markdown
	'text' => $pesan
	];

	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	// hapus 1 baris ini:
	// die('Hapus baris ini sebelum bisa berjalan, terimakasih.');


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

	$debug['text'] = $pesan;
	$debug['code'] = $status;
	$debug['status'] = $error;
	$debug['respon'] = json_decode($datas, true);

	return($debug);

	/* 
	* by @hasanudinhs
	* Telegram @botphp
	* Last update: 27 Sept 2017 22:53
	*/

}

//    public function sendQueueNotificationFirebase(){

//     date_default_timezone_set("Asia/Jakarta");
//     $date_now =date('Y-m-d H:i:s');

//     $qf_data = DB::table('queue_firebase as qf')
//     ->select('*')
//     ->where('sent','=',0)
//     ->get();


//     $x=0;
//     foreach ($qf_data as $value) {
	
//       $to_token_id = null;
//       $to_token_id = array();
//       array_push($to_token_id,@$value->fb_token);
//       $fb_return = $this->sendNotification('Alarm Site Off',$value->message,$to_token_id,'sp_id', $value->sp_id,$value->subject);

//       $update_queue_telegram_data = DB::table('queue_firebase')
//       ->where('id', $value->id)
//       ->update(
//         [
//           'sent' => '1',
//           'send_at' => $date_now,
//         ]
//       );
// // $title,$body,$to_token_id,$type_name, $type_id,$type
//       $res['title'] = 'Alarm Site Off';
//       $res['body'] = $value->message;
//       $res['to_token_id'] = $value->fb_token;
//       $res['type_name'] = 'sp_id';
//       $res['type_id'] = $value->sp_id;
//       $res['type'] = $value->subject;
//     }

//     $res['success'] = true;
//     $res['data'] = @$fb_return;
//     return response($res);

//    }

	public function sendQueueNotificationFirebase(){

	date_default_timezone_set("Asia/Jakarta");
	$date_now =date('Y-m-d H:i:s');

	$qf_data = DB::table('queue_firebase as qf')
	->select('*')
	->where('sent','=',0)
	// ->where('send_to','=',"mbp_anu_dummy")
	->get();


	$x=0;
	foreach ($qf_data as $value) {
	
	$to_token_id = null;
	$to_token_id = array();
	array_push($to_token_id,@$value->fb_token);
	$fb_return = $this->sendNotification($value->subject,$value->message,$to_token_id,$value->type, $value->type_id,$value->subject);

	$update_queue_telegram_data = DB::table('queue_firebase')
	->where('id', $value->id)
	->update(
		[
		'sent' => '1',
		'send_at' => $date_now,
		]
	);
// $title,$body,$to_token_id,$type_name, $type_id,$type
	$res['title'] = 'Alarm Site Off';
	$res['body'] = $value->message;
	$res['to_token_id'] = $value->fb_token;
	$res['type_name'] = 'mbp_id';
	$res['type_id'] = $value->mbp_id;
	$res['type'] = $value->subject;
	}

	$res['success'] = true;
	$res['data'] = @$fb_return;
	return response($res);

}


public function move_zip(){


	
	// $url    = "localhost/semeru-api/maintenance/move_zip_2.php?a=xml";
	$url    = "103.253.107.45/semeru-api/maintenance/move_zip_2.php?a=xml";
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = json_decode($datas, true);

	return($debug);


}

public function delete_zip(){


	
	$url    = "103.253.107.45/semeru-api/maintenance/deletezip_v2.php";
	// $url    = "localhost/semeru-api/maintenance/deletezip_v2.php";
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = json_decode($datas, true);

	return($debug);


}
public function delete_zip_image(){


	
	$url    = "103.253.107.45/semeru-api/maintenance/deletezipimage_v1.php";
	// $url    = "localhost/semeru-api/maintenance/deletezipimage_v1.php";
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = json_decode($datas, true);

	return($debug);


}
public function delete_zip_image_GS(){


	
	$url    = "103.253.107.45/semeru-api/maintenance/deletezipimagegs.php";
	// $url    = "localhost/semeru-api/maintenance/deletezipimage_v1.php";
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = json_decode($datas, true);

	return($debug);


}
public function delete_xml(){


	
	$url    = "103.253.107.45/semeru-api/maintenance/deletexml_v2.php";
	// $url    = "localhost/semeru-api/maintenance/deletexml_v2.php";
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = json_decode($datas, true);

	return($debug);


}


public function proccess_zip_img($zipname){

	$url    = "103.253.107.45/semeru-api/maintenance/proccess_zip_img.php?zipname=".$zipname;
	// $url    = "localhost/semeru-api/maintenance/proccess_zip_img.php?zipname=".$zipname;
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	$datas = json_decode($datas);
	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = @$datas->zip_img;
	// print_r($datas);
	// exit;

	return $debug;


}


public function proccess_zip_img_GS($zipname){

	$url    = "103.253.107.45/semeru-api/maintenance/proccess_zip_img_GS.php?zipname=".$zipname;
	// $url    = "localhost/semeru-api/maintenance/proccess_zip_img.php?zipname=".$zipname;
	$header = [
	"X-Requested-With: XMLHttpRequest",
	"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	//curl_setopt($ch, CURLOPT_REFERER, $refer);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $post );   
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$datas = curl_exec($ch);
	$error = curl_error($ch);
	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	$datas = json_decode($datas);
	// $debug['text'] = $pesan;
	$debug['code'] = @$status;
	$debug['status'] = @$error;
	$debug['datas'] = @$datas;
	$debug['respon'] = @$datas->zip_img;
	// print_r($datas);
	// exit;

	return $debug;


}


}