<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MessageControllerDummy extends Controller
{
	/**
	 * Get user by id
	 *
	 * URL /user/{id}
	 */


	public function setDatedMYHis($date){
	if ($date==null) {
		return "-";
	}else if ($date=='0000-00-00 00:00:00') {
		return "-";
	}else{
		// return date("d-M-Y H:i:s", strtotime($date.''));
		return date("d M Y, H:i", strtotime($date.''));
		// return strtotime($date.'');
	}
	}

	public function getMessage(Request $request){

	$user_id = $request->input('user_id');

	$message_data = DB::table('message')
	->select('*')
	->where('to','=',$user_id)
	->get();

	$res['success'] = true;
	$res['message'] = 'SUCCESS_GET_MESSAGE';
	$res['data'] = $message_data;

	return response($res);
	}

	public function getMessageDetil(Request $request){

	$cancel_id = $request->input('cancel_id');

	$message_data = DB::table('message as msg')
	->join('mbp_trouble as mtr', 'msg.date_message', 'mtr.send_date')
	->select('*')
	->where('mtr.id','=',$cancel_id)
	->first();

	// $res['data'] = $message_data;
	// return $res;

	switch ($message_data->subject) {
		case "MBP_INFORMATION_UNAVAILABLE":
		// echo "Your favorite color is red!";
		$tmp = $this->getMessageDetilUnavailable($cancel_id);
		return response($tmp);
		break;
		case "CANCEL":
		// echo "Your favorite color is blue!";
		$tmp = $this->getMessageDetilCancelDelay($cancel_id);
		return response($tmp);
		break;
		case "DELAY":
		// echo "Your favorite color is green!";
		$tmp = $this->getMessageDetilCancelDelay($cancel_id);
		return response($tmp);
		break;
		default:
		// echo "Your favorite color is neither red, blue, nor green!";
	}
	}
	public function getMessageDetilCancelDelay($cancel_id){

	$CancellationLetter_data = DB::table('mbp_trouble as mtr')
	->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
	->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
	->join('users as u', 'um.username', 'u.username')
	->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
	->join('site as s', 'sp.site_id', 's.site_id')
	->select('m.mbp_name','m.active_at as time','s.site_name','s.site_id','u.name as operator_name','mtr.request_to_unavailable as available_status', 'sp.user_rtpo_cn as ticket_by', 'mtr.cancel_image', 'mtr.desc', 'mtr.cancel_category','mtr.type')
	->where('mtr.id','=',$cancel_id)
	->first();


	if ($CancellationLetter_data->available_status==1) {
		$available_status = 'UNAVAILABLE';
	}else{
		$available_status = 'AVAILABLE';
	}

	if ($CancellationLetter_data!=null) {

		$data['mbp_name'] = $CancellationLetter_data->mbp_name;
		$data['time'] = $this->setDatedMYHis($CancellationLetter_data->time);
		$data['ticket_by'] = $CancellationLetter_data->ticket_by;
		$data['telegram_username'] = '';

		// $data['time'] = $CancellationLetter_data->time;
		$data['site_name'] = $CancellationLetter_data->site_name;
		$data['code_name'] = $CancellationLetter_data->site_id;
		$data['operator_name'] = $CancellationLetter_data->operator_name;
		$data['subject'] = $CancellationLetter_data->type;
		$data['text_message'] = $CancellationLetter_data->desc;
		$data['available_status'] = $available_status;
		// $data['image'] = @$image.'http://ichef.bbci.co.uk/wwfeatures/wm/live/1280_640/images/live/p0/14/pz/p014pzq8.jpg';
		$data['image'] = @$CancellationLetter_data->cancel_image;
		$data['desc'] = @$CancellationLetter_data->desc;
		$data['cancel_category'] = @$CancellationLetter_data->cancel_category;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['data'] = $CancellationLetter_data;
		$res['data'] = $data;

		return $res;
	}else{


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $CancellationLetter_data;

		return $res;
	}

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	// $res['data'] = $message_data;

	return $res;
	}
	public function getMessageDetilUnavailable($cancel_id){


	$CancellationLetter_data = DB::table('mbp_trouble as mtr')
	->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
	->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
	->join('users as u', 'um.username', 'u.username')

	->select('m.mbp_name'/*,'site.site_name'*/,'m.active_at','u.name as operator_name','mtr.available_status','mtr.cancel_image', 'mtr.desc', 'mtr.cancel_category')

	->where('mtr.id','=',$cancel_id)
	->first();


	if ($CancellationLetter_data->available_status==1) {
		$available_status = 'UNAVAILABLE';
	}else{
		$available_status = 'AVAILABLE';
	}

	if ($CancellationLetter_data!=null) {

		$data['mbp_name'] = $CancellationLetter_data->mbp_name;
		$data['site_name'] = '';
		$data['code_name'] = '';
		$data['operator_name'] = $CancellationLetter_data->operator_name;
		$data['subject'] = $CancellationLetter_data->type;
		$data['text_message'] = $CancellationLetter_data->desc;
		$data['available_status'] = $available_status;
		$data['time'] = $CancellationLetter_data->active_at;
		// $data['image'] = @$image.'http://ichef.bbci.co.uk/wwfeatures/wm/live/1280_640/images/live/p0/14/pz/p014pzq8.jpg';
		$data['image'] = @$CancellationLetter_data->cancel_image;
		$data['desc'] = @$CancellationLetter_data->desc;
		$data['cancel_category'] = @$CancellationLetter_data->cancel_category;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;

		return $res;
	}else{


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;

		return $res;
	}

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	// $res['data'] = $message_data;

	return $res;
	}
	public function sendMessage(Request $request){

	date_default_timezone_set("Asia/Jakarta");
	$subject = $request->input('subject');
	$from = $request->input('from');
	$type_to = $request->input('type_to');  // PERSONAL / RTPO / ALL_RTPO
	$to = $request->input('to');
	$text_message = $request->input('text_message');

	// $btss = DB::table('bts')->select('*')->where('status','=','0')->get();

	if($type_to=='PERSONAL'){


		$user_data = DB::table('users')
		->select('*')
		->where('id','=',$to)
		->first();

		if ($user_data) {
		$insertMessage = DB::table('message')->insert(
			[
			'subject' => $subject, 
			'from' => $from,
			'to' => $to,
			'text_message' => $text_message,
			'date_message' => date('Y-m-d H:i:s'),
			]
		);

		if($insertMessage) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS_SENDING_MESSAGE';
			$res['data'] = $insertMessage;
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'FAILED_SENDING_MESSAGE';

			return response($res);
		}
		}else{
		$res['success'] = false;
		$res['message'] = 'USER_DATA_NOT_FOUND';

		return response($res);
		}

	}else if ($type_to=='RTPO') {

		// get data seluruh user yang rtpo_inya 'ini',. hehee
		$user_rtpo_data = DB::table('user_rtpo')
		->join('users', 'user_rtpo.username', '=', 'users.username')
		->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
		->select('users.id','rtpo.rtpo_name'/*,'users.token_firebase'*/) // asumsi nnti ada token firebase juga.. hehee..
		->where('user_rtpo.rtpo_id','=',$to)
		->get();
		// fungsi perulangan mengirim sebanyak id yang terambil.. bismillah

		if($user_rtpo_data) {

			foreach ($user_rtpo_data as $param => $row) {

			$insertMessage = DB::table('message')->insert(
				[
			// $user_rtpo_data[$param]['id'].''
				'subject' => $subject, 
				'from' => $from,
				'to' => $user_rtpo_data[$param]->id,
				'text_message' => $text_message,
				'date_message' => date('Y-m-d H:i:s'),
				]
			);

			if($insertMessage) {
			// $res['success'] = true;
			// $res['message'] = 'SUCCESS_SENDING_MESSAGE';
			// $res['data'] = $insertMessage;
			// return response($res);
			}else{
				$res['success'] = false;
				$res['message'] = 'FAILED_SENDING_MESSAGE';

				return response($res);
			}
			}

			$res['success'] = true;
			$res['message'] = 'SUCCESS_SENDING_MESSAGE';
		// $res['data'] = $user_rtpo_data;

			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'USER_RTPO_NOT_FOUND';

			return response($res);
		}
		}else if($type_to=='ALL_RTPO'){

		// get data seluruh user yang rtpo_inya 'ini',. hehee
		$user_rtpo_data = DB::table('user_rtpo')
		->join('users', 'user_rtpo.username', '=', 'users.username')
		->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
		->select('users.id','rtpo.rtpo_name'/*,'users.token_firebase'*/) // asumsi nnti ada token firebase juga.. hehee..
		->where('users.user_type','=','RTPO')
		->where('users.id','!=',$from)
		->get();

		// fungsi perulangan mengirim sebanyak id yang terambil.. bismillah

		if($user_rtpo_data) {

			foreach ($user_rtpo_data as $param => $row) {

			$insertMessage = DB::table('message')->insert(
				[
			// $user_rtpo_data[$param]['id'].''
				'subject' => $subject, 
				'from' => $from,
				'to' => $user_rtpo_data[$param]->id,
				'text_message' => $text_message,
				'date_message' => date('Y-m-d H:i:s'),
				]
			);

			if($insertMessage) {
			// $res['success'] = true;
			// $res['message'] = 'SUCCESS_SENDING_MESSAGE';
			// $res['data'] = $insertMessage;
			// return response($res);
			}else{
				$res['success'] = false;
				$res['message'] = 'FAILED_SENDING_MESSAGE';

				return response($res);
			}
			}

			$res['success'] = true;
			$res['message'] = 'SUCCESS_SENDING_MESSAGE';
			// $res['data'] = $user_rtpo_data;

			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'USER_RTPO_NOT_FOUND';

			return response($res);
		}
		}else{
		$res['success'] = false;
		$res['message'] = 'PARAMETER_TYPE_TO_NOT_MATCH';

		return response($res);
		}
	}
	}

