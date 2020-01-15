<?php
namespace App\Http\Controllers;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use App\Bts;
use DB;
use App\Jobs\SendNotification;
// use App\Jobs\testQueue;
use App\Http\Controllers\Controller;

class BtsController extends Controller 
{


	// use InteractsWithQueue, Queueable, SerializesModels;

	// $array = array();
	// //kirim
	// $array[] = //fetch result
	// //kirim lagi
	// $array[] = //fetch lagi

	// foreach($array di sini){
	//     //cek result, mungkin ada yang sukses atau gagal, tergantung respon dari tujuan
	//     if(){ //sukses contohnya
	//         //masukin, update db atau apapun yang ente butuhkan setelah pengiriman
	//     } else {
	//         //update db, kirim ulang atau lain - lain
	//     }
	// }

	/**
	 * Get user by id
	 *
	 * URL /user/{id}
	 */

	public function test(Request $request){

	$data = DB::table('master_site')
	->select('*')
	->get();
	
	$res['success'] = 'Success';
	$res['data'] = $data;
	return response($res);
	}
	public function fixingSiteToRTPO(Request $request){



	// $mbp_data = DB::table('mbp')
	//   ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	//   ->join('users', 'user_mbp.username', 'users.username')
	//   ->select('*')
	//   ->where('mbp.mbp_id','=','IDE09001')
	//   ->first();
	$CancellationLetter_data = DB::table('cancel_details')
		->join('users', 'cancel_details.user_id_mbp', 'users.id')
		// ->join('user_mbp', 'users.username', 'user_mbp.username')
		// ->join('mbp', 'user_mbp.mbp_id', 'mbp.mbp_id')
		// ->join('message', 'cancel_details.message_id', 'message.id')
		->select('*'/*'cancel_details.id','mbp.mbp_name','mbp.active_at','users.name','message.id as message_id','message.text_message','message.subject','cancel_details.date','cancel_details.available_status','cancel_details.response_status','mbp.active_at'*/)
		// ->where('cancel_details.id','=',$cancel_id)
		// ->where('mbp.submission','!=',null)
		->where('cancel_details.id','=','5')
		// ->where('mbp.submission','!=',null)
		->first();  

		$supplying_power_data = DB::table('supplying_power')
			->join('site', 'supplying_power.site_id', '=', 'site.site_id')
			->select('supplying_power.sp_id', 'site.*')
			->where('supplying_power.mbp_id','=','IDE09004')
			->where('supplying_power.finish','=',null)
			->first();

	$res['success'] = true;
	// $res['data'] = $CancellationLetter_data;
	$res['data2'] = $supplying_power_data;
	return response($res);

	// $CancellationLetter_data = DB::table('cancel_details')
	// ->select('*')
	$count_submission_data = DB::table('cancel_details')
	->where('cancel_details.response_status','=',NULL)
	->where('cancel_details.rtpo_id','=','11')
	->select('*')
	->count();

	$res['success'] = false;
	$res['data'] = $count_submission_data;
	return response($res);


	$data_otp = DB::table('user_otp_maintenance')
	->select('*')
	->where('otp','=','sdki')
	->first();

	if ($data_otp==null) {
	$res['success'] = false;
	return response($res);
	}else{
	$res['success'] = true;
	return response($res);
	}


		$rtpo_data = DB::table('mbp')
		->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
		->join('users', 'user_mbp.username', '=', 'users.username')
		// ->join('message', 'mbp.message_id', '=', 'message.id')
		// ->select(DB::raw('(case when (delay > "0") then "DELAY" else mbp.status end) as status'),'mbp.mbp_name','users.name','users.phone','mbp.latitude','mbp.longitude'/*,'mbp.mbp_name','mbp.mbp_name','mbp.mbp_name',*/)
		->select(DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status'),'mbp.mbp_name','users.name','users.phone','mbp.latitude','mbp.longitude',/*'message.subject','message.text_message',*/'mbp.active_at', 'mbp.rtpo_id as rtpo_id_now', 'mbp.rtpo_id_home as rtpo_id_home')
		->where('mbp.mbp_id','=','IDE09004')
		->first();

			// $mbp_data = DB::table('user_rtpo')
			// ->join('users', 'user_rtpo.username', '=', 'users.username')
			// ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
			// ->select('*')
			// ->where('user_rtpo.rtpo_id','=','11')
			// ->get();

	$data['mbp_data'] = $rtpo_data;
	return response($data);
	
	$user_data = DB::table('users')
	->select('*')
	->where('id','=', 14)
	->first();

	return response($user_data->username);

	$user_rtpo_data = DB::table('users')
	->join('user_rtpo', 'users.id', 'user_rtpo.user_id')
	->join('rtpo', 'user_rtpo.rtpo_id', 'rtpo.rtpo_id')
	->select('*')
	->where('id','=', 15)
	->get();

	// return response($user_rtpo_data);
	$rtpo_result = json_decode($user_rtpo_data, true);
	$user_rtpo =$rtpo_result[0]['username'].'';
	$rtpo_name =$rtpo_result[0]['rtpo_name'].'';

	$data['user_rtpo'] = $user_rtpo;
	$data['rtpo_name'] = $rtpo_name;
	
	return response($data);

	$pass = 'agus2';
	$passmd5 =md5($pass);
	$res['password'] = $pass;
	$res['md5 password'] = $passmd5;

	return response($res);
	
	
	$sos_data = DB::table('sos')
	->select('*')
	->where('id','=',4)
	->where('status','=',null)
	->first();

	if ($sos_data!=null) {
		$res['success'] = 'not_null';
		$res['data'] = $sos_data;
		return response($res);
	}else{
		$res['success'] = 'null';
		$res['data'] = $sos_data;
		return response($res);
	}

		// $rtpo_data = DB::table('user_rtpo')
		// ->join('users', 'user_rtpo.user_id', 'users.id')
		// ->select('*')
		// ->where('user_rtpo.rtpo_id','=','1')
		// // ->select('*')
		// // ->where('rtpo_id','=',$rtpo_id)
		// ->get();     sendNotification($title,$body,$to_token_id,$type_name, $type_id,$type)
	//   $rtpo_data = DB::table('user_rtpo')
	//   ->join('users', 'user_rtpo.user_id', '=', 'users.id')
	//   ->join('rtpo', 'user_rtpo.user_id', '=', 'rtpo.rtpo_id')
	//   ->select('*')
	//   ->where('user_rtpo.rtpo_id','=',1)
	//   ->get();
	//   if ($rtpo_data) {
	//     $fireBaseController = new FireBaseController;
	//     $topic = '/topics/'.$fireBaseController->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);

	//     // $sendNotification = (new SendNotification('coba','coba',$topic,'mbp_id', '7','MBP_ASSIGNMENT_TO_SITE'));

	//     // Queue::push(new SendNotification('coba','coba',$topic,'mbp_id', '7','MBP_ASSIGNMENT_TO_SITE'));

	//     $this->dispatch(new SendNotification('coba','coba',$topic,'mbp_id', '7','MBP_ASSIGNMENT_TO_SITE'));

	//     return response($rtpo_data);

	// }
	//   $rtpo_from = DB::table('rtpo')
	// ->select('rtpo_name')
	// ->where('rtpo_id','=',1)
	// ->first();

	// $rtpo_to = DB::table('rtpo')
	// ->select('rtpo_name')
	// ->where('rtpo_id','=',2)
	// ->first();

	// return response($rtpo_to->rtpo_name.' '.$rtpo_from->rtpo_name);

	//       $notificationController = new NotificationController;
	//       $tmp = $notificationController->setNotificationSendSosAndMbp('RTPO_RETURN_MBP','44','4','1');

	//   return response($tmp);


	//   $mbp_data = DB::table('mbp')
	// ->select('*')
	// ->where('mbp_id','=',30)
	// ->first();
	// //   jika memang mbp pinjaman, maka 
	// if ($mbp_data) {
	//   return response('ok');
	// }else{
	//   return response('not ok');
	// }


	// $sos_data = DB::table('sos')
	// ->select('*')
	// ->where('rtpo_id','=',4)
	// ->where('status','=',null)
	// ->first();
	// $data['data'] = $sos_data;


	//   return response($data);

	// $rtpo_data = DB::table('user_rtpo')
	// ->join('users', 'user_rtpo.user_id', '=', 'users.id')
	// ->join('rtpo', 'user_rtpo.user_id', '=', 'rtpo.rtpo_id')
	// ->select('*')
	// ->where('user_rtpo.rtpo_id','=',1)
	// ->get();
	//       // return response($rtpo_data);

	//       // echo json_encode($rtpo_data);
	// $fireBaseController = new FireBaseController;
	// // return response($rtpo_data[0]->rtpo_name);
	// // return response($fireBaseController->checkMyRTPOtopic($rtpo_data[0]->rtpo_name));
	// $topic = '/topics/'.$fireBaseController->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
	// return response($topic);
	// $tmp = $fireBaseController->sendNotification('coba','coba',$topic,'mbp_id', '7','MBP_ASSIGNMENT_TO_SITE');

	//------------------------------------------

	// $title = $request->input('title');
	// $body = $request->input('body');
	// $to_token_id = $request->input('to_token_id');
	// $type_name = $request->input('type_name');
	// $type_id = $request->input('type_id');
	// $type = $request->input('type');

	//------------------------------------------
	// $post = [
	//   'title' => $title,
	//   'body' => $body,
	//   'to_token_id'   => $to_token_id,
	//   'type_name'   => $type_name,
	//   'type_id'   => $type_id,
	//   'type'   => $type,
	// ];

	$post = [
		'title' => 'coba',
		'body' => 'coba',
		'to_token_id'   => '/topics/RTPO_ALL',
		'type_name'   => 'mbp_id',
		'type_id'   => '7',
		'type'   => 'MBP_ASSIGNMENT_TO_SITE',
	];


	$ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru-api/public/sendNotificationTest');
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru_api/public/sendNotificationTest');
								//sakkarep.web.id/semeru_api/public/sendNotificationTest
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru-api/public/sendNotificationTest');
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru_api/public/sendNotificationTest');
	// curl_setopt($ch, CURLOPT_URL, '203.114.74.129/semeru_api/public/sendNotificationTest');
	curl_setopt($ch, CURLOPT_URL, 'localhost/semeru-api/public/sendNotificationTest');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	// curl_setopt($ch, CURLOPT_TIMEOUT, 100);
	curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10);

	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	// curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	// curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30);
	// curl_setopt ($ch, CURLOPT_FRESH_CONNECT, true);
	// curl_setopt($ch, CURLOPT_TIMEOUT_MS, 10);
	// curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	// curl_setopt($ch, CURLOPT_NOSIGNAL, 10);
	// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 125);

	// CURLOPT_TIMEOUT and CURLOPT_CONNECTTIMEOUT
	// CURLOPT_TIMEOUT_MS and CURLOPT_CONNECTTIMEOUT_MS


	$response = curl_exec($ch);
	// var_export($response);
	// curl_close($ch);
	if($response==true){
	return response('true');
	}
	return response('false');

	// $ch = curl_init();
	// curl_setopt($ch, CURLOPT_URL, 'localhost/semeru-api/public/sendNotificationTest');
	// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	// curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	// curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
	// $response = curl_exec($ch);
	// var_export($response);

	// return response($rtpo_data);
	//  // exit();


	$fireBaseController = new FireBaseController;
	$tmp = $fireBaseController->sendNotifFast('coba','coba',$topic,'mbp_id','7','MBP_ASSIGNMENT_TO_SITE');
	return response($rtpo_data);

		// $result = json_decode($rtpo_data, true);
	$get_data_mbp = DB::table('cancel_details')
	->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
	->select('mbp.mbp_name','mbp.mbp_id')
	->select('*')
	->where('cancel_details.id', /*$cancel_id*/129)
	// ->where('cancel_details.user_id_rtpo', NULL)
	->first();


		$tmp["data" ] = $get_data_mbp;
		return response($tmp); 

		$mbp_data = DB::table('mbp')
		->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
		->join('users', 'user_mbp.user_id', '=', 'users.id')
		->select('*')
		->where('mbp.mbp_id','=',7)
		->first();

		$type_name = 'mbp_id';
		$tmp["$type_name" ] = $mbp_data;

		return response($mbp_data->firebase_token.'====================='.$mbp_data->firebase_token); 

		$rtpo_data = DB::table('user_rtpo')
		->join('users', 'user_rtpo.user_id', '=', 'users.id')
		->select('*')
		->where('user_rtpo.rtpo_id','=','1')
		// ->select('*')
		// ->where('rtpo_id','=',$rtpo_id)
		->get();

		$result = json_decode($rtpo_data, true);

		foreach ($result as $param => $row) {
		// $user_id_rtpo[$param]  = $row['user_id'].'';


		// foreach ($result as $param => $row) {
			$firebase[$param]['user_id']  = $row['user_id'].'';
			$firebase[$param]['nama']  = $row['name'].'';
			$firebase[$param]['token']  = $row['firebase_token'].'';
		}
		return response($firebase);      
	}

	public function convertSitetmpToQueryInsert(Request $request){
	// $rtpo_id= rtpo_id;

		$txt1 = "INSERT INTO `site` (`site_id`, `rtpo_id`, `site_name`, `latitude`, `longitude`, `status`, `node`) VALUES ";
		// $txt2 = "(1, 'BDO001', '1', '4', 'BONDOWOSO', 113.823, -7.91539, 0, 0),";

	$site_tmp = DB::table('site_tmp')
	->select('*')
	->get();
	$result = json_decode($site_tmp,true);
	
	$txt2 = " ";      
	foreach ($result as $param => $row) {
		$data[$param]['site_name']=$row['rtpo_id'].'';
		$data[$param]['rtpo_id_sebelum']=$row['rtpo_id'].'';

		$txt1 = $txt1."('".$row['site_id']."', '".$row['rtpo_id']."', '".$row['site_name']."', '".$row['latitude']."', '".$row['longitude']."', '".$row['status']."', '".$row['node']."'), ";
	}

	return response($txt1);  
	}
	public function setclassSite(Request $request){
	// $rtpo_id= rtpo_id;

		// $txt1 = "INSERT INTO `site` (`site_id`, `rtpo_id`, `site_name`, `latitude`, `longitude`, `status`, `node`) VALUES ";
		// $txt2 = "(1, 'BDO001', '1', '4', 'BONDOWOSO', 113.823, -7.91539, 0, 0),";

	$site_tmp = DB::table('site')
	->select('*')
	->get();
	$result = json_decode($site_tmp,true);
	
	// $txt2 = " ";      
	foreach ($result as $param => $row) {
		// $data[$param]['site_name']=$row['rtpo_id'].'';
		// $data[$param]['rtpo_id_sebelum']=$row['rtpo_id'].'';

		// $txt1 = $txt1."('".$row['site_id']."', '".$row['rtpo_id']."', '".$row['site_name']."', '".$row['latitude']."', '".$row['longitude']."', '".$row['status']."', '".$row['node']."'), ";
		// $lat = $row['longitude'];
		// $lon = $row['latitude'];

		$site_tmp = DB::table('site')
		->where('site_id', $row['site_id'].'')
		->update(
			[
			'latitude' => $row['longitude'],
			'longitude' => $row['latitude'],
			]
		);
	}

	return response($site_tmp);  
	}

	public function get_bts_off(Request $request){

	$data = DB::table('master_site')
	->select('*')
	->get();

	// return response($data);

	$result = json_decode($data,true);
	foreach ($result as $param => $row) {

		$calc_node = rand(1,4);

		// return response($calc_node);
		if ($calc_node==1) {
		$node = '1';
		// return response($node);
		}else{
		$node = '0';
		}

		$calc_down = rand(1,3);
		// return response($calc_node);
		if ($calc_node==2) {
		$nodedown = '1';
		// return response($node);
		}else{
		$nodedown = '0';
		}
		// return response($node);


		$class = rand(1,4);

		// $editSite = DB::table('site')
		// ->where('site.site_id','=',$row['site_id'])
		// ->update(
		//   [
		//     'class_id' => $class
		//     // 'date_mainsfail' =>  date('Y-m-d H:i:s')
		//   ]
		// );

		// return response($calc_node);
		if ($calc_node==2) {
		$nodedown = '1';
		// return response($node);
		}else{
		$nodedown = '0';
		}
		// return response($node);
		
		$insert_site_tmp = DB::table('site')
		->insert(
		[
			'site_id' => $row['site_id'].'', 

			// 'cluster_id' => $row['cluster'].'',
			// 'rtpo_id' => $row['rtpo'].'',

			'cluster_id' => $this->getClusterId($row['cluster'].''),
			'rtpo_id' => $this->getRTPOid($row['rtpo'].''),
			
			'class_id' => $class,
			'tec_opr_id' => $row['tec_opr_id'].'',
			'wil_opr_id' => $row['wil_opr_id'].'',
			'site_name' => $row['site_name'].'',
			'latitude' => $row['latitude'].'',
			'longitude' => $row['longitude'].'',
			'status' => 1,
			'node' => $node,  

		]
		);

		if ($insert_site_tmp) {
		// // $res['success'] = true;
		// // $res['message'] = 'Success!';
		// // $res['data'] = $btss;

		// return response($res);
		}else{
		$res['success'] = false;
		$res['message'] = 'gagal insert';

		return response($res);
		}
	}

	return response($result);
	if ($btss) {
		$res['success'] = true;
		$res['message'] = 'Success!';
		$res['data'] = $btss;
		
		return response($res);
	}else{
		$polys['success'] = false;
		$polys['message'] = 'Cannot find polys!';
		
		return response($btss);
	}
	}

	public function getRTPOid($rtpo){

	$id = 0;
	switch ($rtpo) {
		case "RTPO BANGKALAN":
		$id = 1;
		break;
		case "RTPO BANYUWANGI":
		$id = 2;
		break;
		case "RTPO JEMBER":
		$id = 3;
		break;
		case "RTPO KEDIRI":
		$id = 4;
		break;
		case "RTPO LAMONGAN":
		$id = 5;
		break;
		case "RTPO MADIUN":
		$id = 6;
		break;
		case "RTPO MALANG":
		$id = 7;
		break;
		case "RTPO PAMEKASAN":
		$id = 8;
		break;
		case "RTPO PASURUAN":
		$id = 9;
		break;
		case "RTPO PONOROGO":
		$id = 10;
		break;
		case "RTPO PROBOLINGGO":
		$id = 11;
		break;
		case "RTPO SIDOARJO":
		$id = 12;
		break;
		case "RTPO SURABAYA BARAT":
		$id = 13;
		break;
		case "RTPO SURABAYA PUSUTA":
		$id = 14;
		break;
		case "RTPO SURABAYA SELATAN":
		$id = 15;
		break;
		case "RTPO SURABAYA TIMUR":
		$id = 16;
		break;
		case "RTPO TULUNGAGUNG":
		$id = 17;
		break;
		default:
		// echo "Your favorite color is neither red, blue, nor green!";
	}

	return $id;
	}
	public function getClusterId($cluster){

	$id = 0;
	switch ($cluster) {
		case "BANYUWANGI":
		$id = 1;
		break;
		case "GRESIK":
		$id = 2;
		break;
		case "JEMBER":
		$id = 3;
		break;
		case "KEDIRI":
		$id = 4;
		break;
		case "MADIUN":
		$id = 5;
		break;
		case "MALANG":
		$id = 6;
		break;
		case "MOJOKERTO":
		$id = 7;
		break;
		case "PONOROGO":
		$id = 8;
		break;
		case "PROBOLINGGO":
		$id = 9;
		break;
		case "SIDOARJO":
		$id = 10;
		break;
		case "SURAMADU":
		$id = 11;
		break;
		case "TUBAN":
		$id = 12;
		break;
		case "TULUNGAGUNG":
		$id = 13;
		break;
		default:
		// echo "Your favorite color is neither red, blue, nor green!";
	}

	return $id;
	}
}