<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DateTime;
// use App\Bts;
use DB;
class MbpController extends Controller
{

public function getStatusMbp1($mbp_id){
	// $mbp_id = $request->input('mbp_id');


		// $data['image'] = @$image.'http://ichef.bbci.co.uk/wwfeatures/wm/live/1280_640/images/live/p0/14/pz/p014pzq8.jpg';

	$mbp_data = DB::table('mbp as m')
	->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
	->join('users as u', 'um.username', 'u.username')
	->select('*','m.status as mbp_status','u.id as u_user_id','m.rtpo_id as m_rtpo_id','m.rtpo_id_home as m_rtpo_id_home')
	->where('m.mbp_id',$mbp_id)
	->first();

	if ($mbp_data==null) {
	$res['success'] = false;
	$res['message'] = 'CANNOT_FIND_DATA_MBP';
	return response($res);
	}

	if ($mbp_data->m_rtpo_id!=$mbp_data->m_rtpo_id_home) {
	$borrowed = true;
	}else{
	$borrowed = false;
	}

	$data['status'] = $mbp_data->mbp_status;
	$data['user_id'] = $mbp_data->u_user_id;
	$data['borrowed'] = $borrowed;

	if ($mbp_data->mbp_status == 'AVAILABLE' || $mbp_data->mbp_status == 'UNAVAILABLE' ) {
	$data['time'] = $mbp_data->active_at;

	}else{

	$sp_data = DB::table('supplying_power as sp')
	->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
	->join('site as s', 'sp.site_id', 's.site_id')
	->select('*','m.status as m_status','m.mbp_name as m_mbp_name','m.latitude as m_latitude','m.longitude as m_longitude','s.latitude as s_latitude','s.longitude as s_longitude','s.site_id as s_site_id','s.site_name as s_site_name','sp.unique_id')
	->where('m.mbp_id',$mbp_id)
	->where('sp.finish',null)
	->orderBy('sp.sp_id', 'desc')
	->first();

	$data['status_BE'] = $sp_data->m_status;
	$data['status'] = $sp_data->m_status;
	$data['borrowed'] = $borrowed;
	$data['rtpo_username'] = $sp_data->user_rtpo;
	$data['mbp_name'] = $sp_data->m_mbp_name;
	$data['mbp_latitude'] = $sp_data->m_latitude;
	$data['mbp_longitude'] = $sp_data->m_longitude;

	$data['sp_id'] = $sp_data->sp_id;
	$data['site_name'] = $sp_data->s_site_name;
	$data['code_name'] = $sp_data->s_site_id;
	$data['latitude'] = $sp_data->s_latitude;
	$data['longitude'] = $sp_data->s_longitude;
	$data['class_name'] = @$sp_data->class_id;
	$data['date_waiting'] = @strtotime(@$sp_data->date_waiting);
	$data['date_onprogress'] = @strtotime(@$sp_data->date_onprogress);
	$data['date_checkin'] = @strtotime(@$sp_data->date_checkin);
	$data['unique_id'] = $sp_data->unique_id;

	if ($data['status']=='CHECK_IN'){
		$imageController = new ImageController;
		$data['image_status'] = $imageController->getListStatusImage0($sp_data->sp_id);
	}else{
		$data['image_status'] =false;
	}

	if ($sp_data->submission == null) {
		$data['submission_status'] = 'NOT_FOUND';
		$data['cancel_id'] = '';
		$data['message_id'] = '';
		$data['subject'] = '';
		$data['text_message'] = '';
		$data['cancel_date'] = '';
		$data['available_status'] = '';
		$data['time'] = '';
		$data['image'] = '';
		$data['cancel_reason'] = '';
		$data['cancel_category'] = '';
	}else{

		$message_data = DB::table('message as m')
		->select('*')
		->where('m.id',$sp_data->message_id)
		->first();
		$mbp_trouble_data = DB::table('mbp_trouble as mtr')
		->select('*')
		->where('mtr.id',$sp_data->submission_id)
		->first();
		if ($mbp_trouble_data) {
		if ($mbp_trouble_data->request_to_unavailable==1) {
			$available_status = 'UNAVAILABLE';
		}else if ($mbp_trouble_data->request_to_unavailable==0) {
			$available_status = 'AVAILABLE';
		}else{
			$available_status = $sp_data->m_status;
		}
		}

		$data['status'] = $sp_data->submission;
		$data['submission_status'] = 'FOUND';
		$data['cancel_id'] = $sp_data->submission_id;
		$data['message_id'] = $sp_data->message_id;
		$data['subject'] = $message_data->subject;
		$data['text_message'] = $message_data->text_message;
		$data['cancel_date'] = $message_data->date_message;
		$data['available_status'] = $available_status;
		$data['image'] = @$mbp_trouble_data->cancel_image;
		$data['cancel_reason'] = @$mbp_trouble_data->desc;
		$data['cancel_category'] = @$mbp_trouble_data->cancel_category;
		// $data['available_status'] = $sp_data->m_status;
		// $data['time'] =$this->setDatedMYHis( $sp_data->mbp_active_at);
		$data['time'] =$this->setDatedMYHis($mbp_trouble_data->mbp_active_at);
		
	}

	}

	//$data['status'] = str_replace("_", " ", $data['status']);
	//$data['status_BE'] = str_replace("_", " ", $data['status_BE']);

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
}
public function getAllMbp(Request $request){
	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.id')
	->select('mbp.*','users.id')
	// ->where('rtpo_id','=',$rtpo_id)
	->get();

	if ($data_site) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data_site;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getStatusMbp(Request $request){
	$mbp_id = $request->input('mbp_id');
	return $this->getStatusMbp1($mbp_id);
}
public function getStatusWithSubmission($mbp_id, $cancel_id, $status, $borrowed){

	if ($status=='DELAY') {
	// get data dari cancel_id
	$data_mbp_task = DB::table('supplying_power')
	->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
	->join('users', 'supplying_power.user_id', '=', 'users.id')
	->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	// ->join('class', 'site.class_id', '=', 'class.class_id')
	->select('supplying_power.sp_id','mbp.status','users.name as rtpo_username','site.site_name','site.site_id','site.latitude','site.longitude','site.class_id as class_name','site.class_id','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id', 'mbp.mbp_name')

	->where('supplying_power.finish','=', NULL)
	->where('mbp.mbp_id','=',$mbp_id)
	->first();

	if ($data_mbp_task) {

		$result['status_BE'] = $status.' '.$cancel_id;
		$result['sp_id'] = $data_mbp_task->sp_id;
		$result['mbp_name'] = $data_mbp_task->mbp_name;
		$result['status'] = $data_mbp_task->status;
		$result['borrowed'] = $borrowed;
		$result['rtpo_username'] = $data_mbp_task->rtpo_username;
		$result['site_name'] = $data_mbp_task->site_name;
		$result['code_name'] = $data_mbp_task->site_id;
		$result['latitude'] = $data_mbp_task->latitude;
		$result['longitude'] = $data_mbp_task->longitude;
		// $result['class_name'] = $data_mbp_task->class_name;
		$result['class_name'] = strtolower($data_mbp_task->class_id);
		$result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
		$result['mbp_longitude'] = $data_mbp_task->mbp_longitude;
		if ($result['status']=='CHECK_IN'){
		$imageController = new ImageController;
		$result['image_status'] = $imageController->getListStatusImage0($data_mbp_task->sp_id);
		}else{
		$result['image_status'] =false;
		}


		$CancellationLetter_data = DB::table('cancel_details')
		->join('users', 'cancel_details.user_id_mbp', 'users.id')
		->join('user_mbp', 'users.username', 'user_mbp.username')
		->join('mbp', 'user_mbp.mbp_id', 'mbp.mbp_id')
		->join('message', 'cancel_details.message_id', '=', 'message.id')
		->select('cancel_details.id','mbp.mbp_name','mbp.active_at','users.name','message.id as message_id','message.text_message','message.subject','cancel_details.date','cancel_details.available_status','cancel_details.response_status','mbp.active_at')
		->where('cancel_details.id','=',$cancel_id)
		->where('mbp.submission','!=',null)
		->first();   

		if ($CancellationLetter_data!=null) {

		$result['submission_status'] = 'FOUND';
		$result['cancel_id'] = $CancellationLetter_data->id;
		$result['message_id'] = $CancellationLetter_data->message_id;
		$result['subject'] = $CancellationLetter_data->subject;
		$result['text_message'] = $CancellationLetter_data->text_message;
		$result['cancel_date'] = $CancellationLetter_data->date;
		$result['available_status'] = $CancellationLetter_data->available_status;
		$result['time'] = $this->setDatedMYHis($CancellationLetter_data->active_at);

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $result;

		return $res;
		}else{

		$result['submission_status'] = 'NOT_FOUND';
		$result['cancel_id'] = '';
		$result['message_id'] = '';
		$result['subject'] = '';
		$result['text_message'] = '';
		$result['cancel_date'] = '';
		$result['available_status'] = '';
		$result['time'] = '';

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $result;

		return $res;
		}
	}

	}else if($status=='CANCEL'){
	$data_mbp_task = DB::table('supplying_power')
	->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
	->join('users', 'supplying_power.user_id', '=', 'users.id')
	->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	// ->join('class', 'site.class_id', '=', 'class.class_id')
	->select('supplying_power.sp_id','mbp.status','users.name as rtpo_username','site.site_name','site.site_id','site.latitude','site.longitude','site.class_id as class_name','site.class_id','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id','mbp.active_at','mbp.mbp_name')

	->where('supplying_power.finish','=', NULL)
	->where('mbp.mbp_id','=',$mbp_id)
	->first();

	if ($data_mbp_task) {

		$result['status_BE'] = $status;
		$result['sp_id'] = $data_mbp_task->sp_id;
		$result['mbp_name'] = $data_mbp_task->mbp_name; 
		$result['status'] = $data_mbp_task->status;
		$result['borrowed'] = $borrowed;
		$result['rtpo_username'] = $data_mbp_task->rtpo_username;
		$result['site_name'] = $data_mbp_task->site_name;
		$result['code_name'] = $data_mbp_task->site_id;
		$result['latitude'] = $data_mbp_task->latitude;
		$result['longitude'] = $data_mbp_task->longitude;
		// $result['class_name'] = $data_mbp_task->class_name;
		$result['class_name'] = strtolower($data_mbp_task->class_id);
		$result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
		$result['mbp_longitude'] = $data_mbp_task->mbp_longitude;
		if ($result['status']=='CHECK_IN'){
		$imageController = new ImageController;
		$result['image_status'] = $imageController->getListStatusImage0($data_mbp_task->sp_id);
		}else{
		$result['image_status'] =false;
		}


		$CancellationLetter_data = DB::table('cancel_details')
		->join('users', 'cancel_details.user_id_mbp', 'users.id')
		->join('user_mbp', 'users.username', 'user_mbp.username')
		->join('mbp', 'user_mbp.mbp_id', 'mbp.mbp_id')
		->join('message', 'cancel_details.message_id', '=', 'message.id')
		->select('cancel_details.id','mbp.mbp_name','mbp.active_at','users.name','message.id as message_id','message.text_message','message.subject','cancel_details.date','cancel_details.available_status','cancel_details.response_status')
		->where('cancel_details.id','=',$cancel_id)
		->where('cancel_details.response_status','=',null)
		->first();   

		if ($CancellationLetter_data!=null) {

		$result['submission_status'] = 'FOUND';
		$result['cancel_id'] = $CancellationLetter_data->id;
		$result['message_id'] = $CancellationLetter_data->message_id;
		$result['subject'] = $CancellationLetter_data->subject;
		$result['text_message'] = $CancellationLetter_data->text_message;
		$result['cancel_date'] = $CancellationLetter_data->date;
		$result['available_status'] = $CancellationLetter_data->available_status;
		$result['time'] = $this->setDatedMYHis($CancellationLetter_data->active_at);

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $result;

		return $res;
		}else{

		$result['submission_status'] = 'NOT_FOUND';
		$result['cancel_id'] = '';
		$result['message_id'] = '';
		$result['subject'] = '';
		$result['text_message'] = '';
		$result['cancel_date'] = '';
		$result['available_status'] = '';
		$result['time'] = '';

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $result;

		return $res;
		}
	}
	}else{
	$data_mbp_task = DB::table('supplying_power')
	->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
	->join('users', 'supplying_power.user_id', '=', 'users.id')
	->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	// ->join('class', 'site.class_id', '=', 'class.class_id')
	->select('supplying_power.sp_id','mbp.status','users.name as rtpo_username','site.site_name','site.site_id','site.latitude','site.longitude','site.class_id as class_name','site.class_id','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id','mbp.mbp_name')

	->where('supplying_power.finish','=', NULL)
	->where('mbp.mbp_id','=',$mbp_id)
	->first();


	// $res['data'] = $data_mbp_task;
	// return response($res);

	if ($data_mbp_task) {

		$result['status_BE'] = $status;
		$result['sp_id'] = $data_mbp_task->sp_id;
		$result['mbp_name'] = $data_mbp_task->mbp_name; 
		$result['status'] = $data_mbp_task->status;
		$result['borrowed'] = $borrowed;
		$result['rtpo_username'] = $data_mbp_task->rtpo_username;
		$result['site_name'] = $data_mbp_task->site_name;
		$result['code_name'] = $data_mbp_task->site_id;
		$result['latitude'] = $data_mbp_task->latitude;
		$result['longitude'] = $data_mbp_task->longitude;
		// $result['class_name'] = $data_mbp_task->class_name;
		$result['class_name'] = strtolower($data_mbp_task->class_id);
		$result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
		$result['mbp_longitude'] = $data_mbp_task->mbp_longitude;

		if ($result['status']=='CHECK_IN'){
		$imageController = new ImageController;
		$result['image_status'] = $imageController->getListStatusImage0($data_mbp_task->sp_id);
		}else{
		$result['image_status'] =false;
		}

		$result['submission_status'] = 'NOT_FOUND';
		$result['cancel_id'] = '';
		$result['message_id'] = '';
		$result['subject'] = '';
		$result['text_message'] = '';
		$result['cancel_date'] = '';
		$result['available_status'] = '';
		$result['time'] = '';

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $result;

		return $res;
	}
	}
}
public function updateStatusMbp(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$mbp_id = $request->input('mbp_id');
	$status = $request->input('status');


	$edit_sp_mbp = DB::table('supplying_power as sp')
	->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
	->where('sp.finish','=', NULL)
	->where('m.status','!=' ,$status);

	if ($status=='ON_PROGRESS') {
	$edit_sp_mbp = $edit_sp_mbp
	->where('m.mbp_id','=', $mbp_id)
	->update(
		[
		'm.status' => $status,
		'sp.date_onprogress' => $date_now,
		'sp.last_update' => $date_now,
		'sp.is_sync' => '0',
		]
	);
	}else if ($status=='CHECK_IN') {
	$edit_sp_mbp = $edit_sp_mbp
	->where('m.mbp_id','=', $mbp_id)
	->update(
		[
		'm.status' => $status,
		'sp.date_checkin' => $date_now,
		'sp.last_update' => $date_now,
		'sp.is_sync' => '0',
		]
	);
	}else if($status=='AVAILABLE'){
	$edit_sp_mbp = $edit_sp_mbp
	->join('site as s', 'sp.site_id', 's.site_id')
	->where('m.mbp_id','=', $mbp_id)
	->update(
		[
		'm.status' => $status,
		'sp.date_finish' => $date_now,
		'sp.last_update' => $date_now,
		'sp.is_sync' => '0',
		
		'sp.finish' =>'DONE',
		'sp.detail_finish'=>'1',
		's.is_allocated' =>'0',
		]
	);
	}else{
	$res['success'] = false;
	$res['message'] = 'STATUS_NOT_MATCH';
	return response($res);
	}

	if (!$edit_sp_mbp) {
	$res['success'] = false;
	$res['message'] = 'UPDATE_FAILED';
	return response($res);
	}

	$sp_m_s_data = DB::table('supplying_power as sp')
	->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
	->join('site as s', 'sp.site_id', 's.site_id')
	->select('*', 'sp.user_mbp as driver_mbp', 'sp.user_mbp_cn as driver_mbp_cn')
	->where('m.mbp_id', $mbp_id)
	->orderBy('sp.sp_id', 'desc')
	->first();

	if ($status=='ON_PROGRESS') {
	$desc = @$sp_m_s_data->driver_mbp_cn.' menerima tiket yang telah diberikan';
	}else if ($status=='CHECK_IN') {
	$desc = @$sp_m_s_data->driver_mbp_cn.' telah sampai di site tujuan';
	}else if ($status=='AVAILABLE') {
	$desc = @$sp_m_s_data->driver_mbp_cn.' menyelesaikan tugasnya';
	}

	$supplyingPowerController = new SupplyingPowerController;
	$value_sp_log = $supplyingPowerController->saveLogSP1($sp_m_s_data->sp_id, $sp_m_s_data->driver_mbp, $sp_m_s_data->driver_mbp_cn, $status,$desc, '', '', $date_now);


	$notificationController = new NotificationController; 
	$tmp = $notificationController->setNotification0('MBP_STATUS_TO_SITE',$sp_m_s_data->mbp_name,$sp_m_s_data->site_name,$mbp_id,$status,$sp_m_s_data->rtpo_id);

	return $this->getStatusMbp1($mbp_id);
}
public function getAllMbpOnProggress(Request $request){
	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('status','=','1')
	->get();

	if ($data_site) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data_site;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getMyMbp(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');

	// $delete_date_strtotime = strtotime($date_now." -1 day");
	// $delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);

	$rtpo_id = $request->input('rtpo_id');

	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
	$data_site = DB::table('mbp')
	// ->leftJoin('supplying_power as sp', 'mbp.mbp_id', 'sp.mbp_id')
	// ->leftJoin('site as s', 'sp.site_id', 's.site_id')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	// ->where('finish','=',null)
	// ->whereNull('sp.finish')
	->where('mbp.rtpo_id','=',$rtpo_id)
	->orWhere('mbp.rtpo_id_home','=',$rtpo_id)
	// ->orderBy('mbp_status.bobot', 'ASC')
	->get();
	
	// $data_site= DB::select("SELECT")

	$mbp_result = json_decode($data_site, true);

	if (!$mbp_result) {
	// $res['success'] = false;
	// $res['message'] = 'Cannot find data!';
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['cluster_id'] = $mbp_result[$param]['cluster_id'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['regional'] = $mbp_result[$param]['regional'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['submission_id'] = $mbp_result[$param]['submission_id'];
	$data[$param]['message_id'] = $mbp_result[$param]['message_id'];
	$data[$param]['active_at'] = $mbp_result[$param]['active_at'];
	$data[$param]['latitude'] = $mbp_result[$param]['latitude'];
	$data[$param]['longitude'] = $mbp_result[$param]['longitude'];
	$data[$param]['fmc'] = $mbp_result[$param]['fmc'];
	$data[$param]['active'] = $mbp_result[$param]['active'];
	$data[$param]['last_update'] = $mbp_result[$param]['last_update'];
	$data[$param]['user_id'] = $mbp_result[$param]['user_id'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];
	// $data[$param]['time new count'] = $date_new_count;

	// $task_count = DB::table('supplying_power')
	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();
	// if ($get_sp_active!=null) {
	//   # code...
	// }

	$data[$param]['site_latitude'] = @$get_sp_active->s_lat;
	$data[$param]['site_longitude'] = @$get_sp_active->s_lon;
	$data[$param]['site_id'] = @$get_sp_active->site_id;
	$data[$param]['site_name'] = @$get_sp_active->site_name;
	$data[$param]['mbp_latitude'] = @$mbp_result[$param]['m_lat'];
	$data[$param]['mbp_longitude'] = @$mbp_result[$param]['m_lon'];
	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		// $time_req = $get_sp_active->date_onprogress;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);
	// $data[$param]['running_bc']=@$difference;
		// $fiffdate = new DateTime($difference);
		// $time_req = date('h:i',strtotime($difference));
		// $time_req = date_format (new DateTime($difference->intime), 'H:i');
		// $time_req = $fiffdate->format('H:i');

		// $hours   = $difference->format('%H'); 
		// $minutes = $difference->format('%i');
		// $second = $difference->format('%s');

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		// $time_req = $hours .':'.$minutes.':'.$second;
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
		// $data[$param]['traveling_time'] = $waktu_tempuh;

		// $time_req = $hours .':'.$minutes;
		$time_req = $running_backup;
		// $time_req = $get_sp_active->date_checkin;
		}

		if ( $data[$param]['submission']=='DELAY') {

		// $datetime1 = new DateTime($data[$param]['active_at']);
		// $datetime2 = new DateTime($date_now);
		// $running_bc = $datetime2->diff($datetime1);


		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	

	// $data[$param]['onpro'] = @$get_sp_active->date_onprogress;
	// $data[$param]['chek'] = @$get_sp_active->date_checkin;
	$data[$param]['traveling_time'] = @$waktu_tempuh;
	$data[$param]['time'] = @$time_req;
	$data[$param]['delay_time'] = @$delay_time;
	$data[$param]['task_count'] = $task_count;
	$data[$param]['is resting'] = $is_resting;
	// $data[$param]['tme'] = $waktu_nganggur;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	// usort($data, array($this, 'sort_by_counttask'));
	// usort($data, array($this, 'sort_by_bobot'));

//     // Obtain a list of columns
//     foreach ($data as $key => $row) {
//       $return_fare[$key]  = $row['return_fare'];
//       $one_way_fare[$key] = $row['one_way_fare'];
//     }

// // Sort the data with volume descending, edition ascending
	array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data;
	return response($res);

}
public function sort_by_counttask($a, $b){
	return ($a['task_count'] > $b['task_count']);
}
public function sort_by_bobot($a, $b){
	return ($a['bobot'] > $b['bobot']);
}



public function getMyMbpCategory(Request $request){


	$rtpo_id = $request->input('rtpo_id');

	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
	// $mbp_data = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id);
	$mbp_data = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('rtpo_id','=',$rtpo_id)
	->get();


	// $data_onprogress = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON_PROGRESS')->get();
	$data_onprogress = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('rtpo_id','=',$rtpo_id)
	->where('status','=','ON_PROGRESS')
	->get();

	// $data_waiting = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
	$data_waiting = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('rtpo_id','=',$rtpo_id)
	->where('status','=','WAITING')
	->get();

	// $data_available = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
	$data_available = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('rtpo_id','=',$rtpo_id)
	->where('status','=','AVAILABLE')
	->get();

	// $data_onprogress->where('status','=','ON_PROGRESS')->get();
	// $data_waiting->where('status','=','WAITING')->get();
	// $data_available->where('status','=','AVAILABLE')->get();

	$data['ON_PROGRESS'] = $data_onprogress;
	$data['WAITING'] = $data_waiting;
	$data['AVAILABLE'] = $data_available;


	if ($mbp_data) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getMyMbpOnProgress(Request $request){


	$rtpo_id = $request->input('rtpo_id');

	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON_PROGRESS')->get();
	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('rtpo_id','=',$rtpo_id)
	->where('status','=','ON_PROGRESS')
	->get();

	if ($data_site) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data_site;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getMyMbpAvailable(Request $request){


	$rtpo_id = $request->input('rtpo_id');

	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('mbp.*','users.id as user_id','users.name')
	->where('rtpo_id','=',$rtpo_id)
	->where('rtpo_id_home','=',$rtpo_id)
	->where('status','=','AVAILABLE')
	->get();

	if ($data_site) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data_site;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getLogMotionMbp(Request $request){


	// $mbp_id = $request->input('mbp_id');
	$mbp_id = app('request')->input('mid');
	$date = app('request')->input('dt');

	// return response(''.$date);

	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
	$logMotionMbp = DB::table('motion_mbp as lmm')
	->join('mbp as m', 'lmm.mbp_id', 'm.mbp_id')
	->select('lmm.*','m.mbp_name')
	->where('m.mbp_id',$mbp_id)
	->where('lmm.create_date','like',"%".$date."%")
	->orderBy('lmm.create_date', 'asc')
	->get();

	$result = json_decode($logMotionMbp, true);

	// return response($result['mbp_id']);

	if ($result!=null) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $logMotionMbp;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getMyMbpWaiting(Request $request){


	$rtpo_id = $request->input('rtpo_id');

	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('mbp.*','users.id as user_id')
	->where('rtpo_id','=',$rtpo_id)
	->where('status','=','WAITING')
	->get();

	if ($data_site) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data_site;

	return response($res);
	}else{
	$res['success'] = false;
	$res['message'] = 'Cannot find data!';

	return response($res);
	}
}
public function getMbp(Request $request){

	$mbp_id = app('request')->input('mid');

	$MbpData = DB::table('mbp')
	->select('mbp_id', 'latitude','longitude','update_by','last_update')
	->where('mbp_id','like','%'.$mbp_id.'%')
	->get();

	// $res['mbp_id'] = @$MbpData->mbp_id;
	// $res['lat'] = @$MbpData->latitude;
	// $res['lon'] = @$MbpData->longitude;
	return response($MbpData);
}

public function getMbpArea (Request $request){

	$mbp_name = app('request')->input('keyword');
	//id, name, name rtpo, fmc name, status mbp sekarang, latlong
	$MbpData = DB::table('mbp as mbp')
	->select('mbp.mbp_id', 'mbp.mbp_name', 'rtpo.rtpo_name', 'mbp.fmc', 'mbp.status','mbp.latitude','mbp.longitude')
	->join('rtpo as rtpo', 'rtpo.rtpo_id', '=', 'mbp.rtpo_id')
	->where('mbp.mbp_name','like','%'.$mbp_name.'%')
	->get();

		$res = [
			'success' => 'OK',
			'message' => 'Success',
	'data' => $MbpData
	];

	return response($res);
}

public function getMbpRegional(Request $request){
	//regional
	$regional = app('request')->input('regional');
	$mbp_name = app('request')->input('keyword');
	if(empty($regional)) return $this->response_fail();
	//id, name, name rtpo, fmc name, status mbp sekarang, latlong
	$MbpData = DB::table('mbp as mbp')
	->select('mbp.mbp_id', 'mbp.mbp_name', 'rtpo.rtpo_name', 'mbp.fmc', 'mbp.status','mbp.latitude','mbp.longitude')
	->join('rtpo as rtpo', 'rtpo.rtpo_id', '=', 'mbp.rtpo_id')
	->where('mbp.regional','like','%'.$regional.'%')
	->where('mbp.mbp_name','like','%'.$mbp_name.'%')
	->get();

		$res = [
			'success' => 'OK',
			'message' => 'Success',
	'data' => $MbpData
	];

	return response($res);
}

public function getMbpNS(Request $request){

	$ns_id = app('request')->input('ns_id');
	$mbp_name = app('request')->input('keyword');
	if(empty($regional)) return $this->response_fail();
	//id, name, name rtpo, fmc name, status mbp sekarang, latlong
	$MbpData = DB::table('mbp as mbp')
	->select('mbp.mbp_id', 'mbp.ns_id','mbp.mbp_name', 'rtpo.rtpo_name', 'mbp.fmc', 'mbp.status','mbp.latitude','mbp.longitude')
	->join('rtpo as rtpo', 'rtpo.rtpo_id', '=', 'mbp.rtpo_id')
	->where('mbp.ns_id','=', $ns_id)
	->where('mbp.mbp_name','like','%'.$mbp_name.'%')
	->get();

		$res = [
			'success' => 'OK',
			'message' => 'Success',
	'data' => $MbpData
	];

	return response($res);
}

public function getMbpRtpo(Request $request){

	$rtpo_id = app('request')->input('rtpo_id');
	$mbp_name = app('request')->input('keyword');
	if(empty($regional)) return $this->response_fail();
	//id, name, name rtpo, fmc name, status mbp sekarang, latlong
	$MbpData = DB::table('mbp as mbp')
	->select('mbp.mbp_id', 'mbp.mbp_name', 'rtpo.rtpo_name', 'mbp.fmc', 'mbp.status','mbp.latitude','mbp.longitude')
	->join('rtpo as rtpo', 'rtpo.rtpo_id', '=', 'mbp.rtpo_id')
	->where('mbp.rtpo_id','=', $rtpo_id)
	->where('mbp.mbp_name','like','%'.$mbp_name.'%')
	->get();

		$res = [
			'success' => 'OK',
			'message' => 'Success',
	'data' => $MbpData
	];

	return response($res);
}

public function updateLatLongMbp(Request $request){

	// $mbp_id = $request->input('mbp_id');
	// $latitude = $request->input('latitude');
	// $longitude = $request->input('longitude');
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$delete_date_strtotime = strtotime($date_now." -7 day");
	$delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);
	$delete_date_fix = date('Y-m-d',$delete_date_strtotime);
	$hour_now = date('H', strtotime($date_now));
	$delete_hour = "01";

	// $res['delete_date_fix'] = $delete_date_fix;
	// return response($res);

	$mbp_id = app('request')->input('mid');
	$latitude = app('request')->input('xlat');
	$longitude = app('request')->input('xlong');

	if(empty($mbp_id) || empty($latitude) || empty($longitude)){
	$res['success'] = false;
	$res['message'] = 'INVALID_DATA';

	return response($res);
	}



	if ($hour_now == $delete_hour) {
	
	$data['delete_status'] = "lagi di delete";

	DB::table('motion_mbp')->where('create_date','<',$delete_date_fix)->delete();
	}

	$update_by = 'Vendor GPS';

	//------------------------------------------ fungsi untuk mengirim notif ke telegram, numpang aja.. hehee
	// if (@$mbp_id=='ABT03401') {

	//   $fbc = new FireBaseController;
	//   $tmp_fb = @$fbc->sendNotificationQueueTelegram();

	// }

	$editMbp = DB::table('mbp')
	->where('mbp_id', $mbp_id)
	->update(
	[
		'latitude' => $latitude,
		'longitude' => $longitude,
		'last_update'=> $date_now,
		'update_by' => $update_by,
	]
	);

	
	$editMbp = DB::table('motion_mbp')
	->insert(
	[
		'mbp_id' => $mbp_id,
		'latitude' => $latitude,
		'longitude' => $longitude,
		'create_date' => $date_now,
	]
	);

	if ($editMbp) {
	$data['mbp_id'] = $mbp_id;
	$data['latitude'] = $latitude;
	$data['longitude'] = $longitude;
	// $data['hari pengahpusan srttime'] = $delete_date;
	// $data['hari pengahpusan'] = date('Y-m-d H:i:s',$delete_date_strtotime);
	// $data['date_now'] = $date_now;
	// $data['hour_now'] = $hour_now;
	// $data['delete_hour'] = $delete_hour;
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	// $res['data'] = $editMbp;
	return response($res);
	}else{
	$data['mbp_id'] = $mbp_id;
	$data['latitude'] = $latitude;
	$data['longitude'] = $longitude;
	// $data['hari pengahpusan'] = $delete_date;
	$res['success'] = false;
	$res['message'] = 'CANNOT_FIND_DATA';
	$res['data'] = $data;

	return response($res);
	}
}
// fungsi meleuhat -> merubah status mbp dari aktif ke not actif begitu juga sebaliknya..:D
public function changeStatusActiveNotActive(Request $request){

	$set_tatus = $request->input('set_status');
	$mbp_id = $request->input('mbp_id');
	$text_message = $request->input('text_message');
	$active_at = $request->input('time');

	$mbp_data = DB::table('mbp')
	->select('*')
	->where('mbp_id','=',$mbp_id)
	->first();

	switch ($set_tatus) {
	case "ACTIVE":


	// fungsi pengecekan apakah mbp sudah aktif atau belum, bila belum maka eksekusi di bawah ini
	// 1. cek, apakah status dia available?
	$mbp_data = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('*')
	->where('mbp.mbp_id','=',$mbp_id)
	->where('mbp.status','=','UNAVAILABLE')
	->first();

	if ($mbp_data!=null) {
		// set mbp jadi available
		// submission id dan submission di null
		$update_mbp = DB::table('mbp')
		->where('mbp_id','=',$mbp_id)
		->update(
		[
			'status' =>'AVAILABLE',
			'submission' =>null,
			'submission_id' =>null,
			'active_at' =>null,
			'message_id' =>null,
		]
		);

		if ($update_mbp) {


		$notificationController = new NotificationController;
		$tmp = $notificationController->setNotificationMbpActiveNotActive('AVAILABLE',$mbp_data->mbp_name,$mbp_id,$mbp_data->rtpo_id);

		if ($tmp['success']) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'UPDATE_NOTIFICATION_TABLE_FAILED';
			return response($res);
		}

		}else{
		$res['success'] = false;
		$res['message'] = 'UPDATE_MBP_TABLE_FAILED';
		return response($res);
		}
	}else{
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	
	
	break;
	case "NOT_ACTIVE":
		// echo "Your favorite color is blue!";
	// . membuat pesan dulu sebagai alasan kenapa dia jadi unavailable
	// . lalu membuat pemeberitahuan di tabel cancel,
	// . setelah semua terbuat, maka status dia di set unavailable
	$act = $this->setStatustoUnavailable($mbp_id, $text_message, $active_at);
	return response($act);


	break;
	default:

	$res['success'] = false;
	$res['message'] = 'STATUS_NOT_MATCH';
	return response($res);
	break;
		// echo "Your favorite color is neither red, blue, nor green!";
	}
}
public function setStatustoUnavailable($mbp_id, $text_message, $active_at){

	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');


	if (strlen($active_at)>10) {
	$tmp_active_at = $active_at;
	} else{
	$tmp_active_at = date('Y-m-d H:i:s', strtotime($date_now.' + '.$active_at.' hours'));
	}

	// 1. cek, apakah status dia available?
	$mbp_data = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('*', 'users.id as user_id')
	->where('mbp.mbp_id','=',$mbp_id)
	->where('mbp.status','=','AVAILABLE')
	->first();

	if ($mbp_data!=null) {

	// 2. bila ia maka mulai membuat pesan,
	$insertMessage = DB::table('message')->insert(
		[
		'subject' => 'MBP_INFORMATION_UNAVAILABLE', 
		'from' => $mbp_data->user_id,
		'text_message' => $text_message,
		'date_message' => $date_now,
		]
	);

	if ($insertMessage) {
	// check apakah pesan yang sudah di buat sudah ada di dalam tabel?
	// bila ada maka lanjutkan ke pembuatan tabel cancel

		$message_data = DB::table('message')
		->select('id')
		->where('date_message','=',$date_now.'')
		->where('from','=',$mbp_data->user_id.'')
		->first();

		if ($message_data) {
		// 3. insert pesan tadi beserta rtpo tujuan ke table cancel,

		$update_mbp = DB::table('mbp')
		->where('mbp_id','=',$mbp_id)
		->update(
			[
			'status' =>'UNAVAILABLE',
			'submission' =>'UNAVAILABLE',
				// 'submission_id' =>$InformationUnavailable->id,
			'submission_id' =>NULL,
			'active_at' =>$tmp_active_at,
			'message_id' => $message_data->id, 

			]
		);

		if ($update_mbp) {


			$notificationController = new NotificationController;
			$tmp = $notificationController->setNotificationMbpActiveNotActive('UNAVAILABLE',$mbp_data->mbp_name,$mbp_id,$mbp_data->rtpo_id);

			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return $res;

			// if ($tmp['success']) {
			//   $res['success'] = true;
			//   $res['message'] = 'SUCCESS';
			//   return $res;
			// }else{
			//   $res['success'] = false;
			//   $res['message'] = 'UPDATE_NOTIFICATION_TABLE_FAILED';
			//   return $res;
			// }

		}else{
			DB::table('cancel_details')->where('id','=',$InformationUnavailable->id)->delete();
			DB::table('message')->where('id','=',$message_data->id)->delete();
			$res['success'] = false;
			$res['message'] = 'UPDATE_MBP_TABLE_FAILED';
			return $res;
		}
		}else{
		$res['success'] = false;
		$res['message'] = 'MESSAGE_DATA_NOT_FOUND';
		return $res;
		}
	}else{
		$res['success'] = false;
		$res['message'] = 'INSERT_MESSAGE_FAILED';
		return $res;
	}    
	}else{
	// $res['success'] = false;
	// $res['message'] = 'MBP_DATA_NOT_FOUND';

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	return $res;
	}
}
public function getStatusActiveNotActive(Request $request){

	$mbp_id = $request->input('mbp_id');

	$mbp_data = DB::table('mbp')
	->select('*')
	->where('mbp_id','=',$mbp_id)
	->first();

	switch ($mbp_data->status) {
	case "UNAVAILABLE":
	$data['status'] = 'NOT_ACTIVE';
	$data['time'] = @$this->setDatedMYHis($mbp_data->active_at);

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
	break;
	case "AVAILABLE":
	$data['status'] = 'ACTIVE';
	$data['time'] = '';

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;

	return response($res);
	break;
	default:
	$data['status'] = 'WORKING';
	$data['time'] = '';
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
	}
}
public function getDetailMbp(Request $request){
	$mbp_id = $request->input('mbp_id');
	
	$mbp_data = DB::table('mbp as m')
	->select('*'/*, DB::raw('(case when (submission = "DELAY") then "DELAY" else m.status end) as status')*/)
	->where('m.mbp_id','=',$mbp_id)
	->first();

	if ($mbp_data->rtpo_id != $mbp_data->rtpo_id_home) {
	$borrowed=true;
	}else{
	$borrowed=false;
	}

	if ($mbp_data) {

	if ($mbp_data->submission=='DELAY') {

		// $user_mbp_data = DB::table('mbp as m')
		// ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
		// ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
		// ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
		// ->join('users as u', 'um.username', 'u.username')
		// ->join('message as msg', 'm.message_id', 'msg.id')
		// ->select('*', 'm.status as mbp_status', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now')
		// ->where('m.mbp_id','=',$mbp_id)
		// ->first();
		
		// $data['get_in'] = "DELAY";
		// $data['status'] = 'DELAY';
		// $data['borrowed'] = $borrowed;
		// $data['class_name'] = '-';

		// $data['rtpo_id_home'] = $user_mbp_data->mbp_rtpo_id_home;
		// $data['rtpo_id_now'] = $user_mbp_data->mbp_rtpo_id;
		// $data['rtpo_name_home'] = $user_mbp_data->rtpo_name_home;
		// $data['rtpo_name_now'] = $user_mbp_data->rtpo_name_now;

		// $data['fmc_id'] = $user_mbp_data->fmc_id;
		// $data['fmc_name'] = $user_mbp_data->fmc;

		// $data['mbp_name'] = $user_mbp_data->mbp_name;
		// $data['name'] = $user_mbp_data->name;
		// $data['phone'] = $user_mbp_data->phone;
		// $data['latitude'] = $user_mbp_data->latitude;
		// $data['longitude'] = $user_mbp_data->longitude;
		// $data['subject'] = $user_mbp_data->subject;
		// $data['text_message'] = $user_mbp_data->text_message;
		// $data['time'] = $this->setDatedMYHis($user_mbp_data->active_at);

		//-----------------------------------------------------------------------------


		$user_mbp_data = DB::table('mbp as m')
		->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
		->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
		->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
		->join('users as u', 'um.username', 'u.username')
		->join('supplying_power as sp', 'm.mbp_id', 'sp.mbp_id')
		->join('site as s', 'sp.site_id', 's.site_id')
		->join('message as msg', 'm.message_id', 'msg.id')
		->select('*', 'm.status as mbp_status', 's.latitude as site_latitude', 's.longitude as site_longitude', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now','sp.user_rtpo_cn as ticket_by')
		->where('m.mbp_id','=',$mbp_id)
		->where('sp.finish','=',null)
		->first();
		// $data['status'] = @$user_mbp_data->mbp_status;
		$data['ticket_by'] = $user_mbp_data->ticket_by;
		$data['telegram_username'] = "";
		$data['status'] = 'DELAY';
		$data['mbp_name'] = @$user_mbp_data->mbp_name;
		$data['name'] = @$user_mbp_data->name;
		$data['phone'] = @$user_mbp_data->phone;
		$data['mbp_latitude'] = @$user_mbp_data->latitude;
		$data['mbp_longitude'] = @$user_mbp_data->longitude;
		$data['site_name'] = @$user_mbp_data->site_name;
		$data['code_name'] = @$user_mbp_data->site_id;
		$data['class_name'] = @$user_mbp_data->site_class;
		$data['latitude'] = @$user_mbp_data->site_latitude;
		$data['longitude'] = @$user_mbp_data->site_longitude;
		$data['borrowed'] = @$borrowed;
		$data['date_waiting'] = @strtotime(@$user_mbp_data->date_waiting);
		$data['date_onprogress'] = @strtotime(@$user_mbp_data->date_onprogress);
		$data['date_checkin'] = @strtotime(@$user_mbp_data->date_checkin);

		$data['fmc_id'] = @$user_mbp_data->fmc_id;
		$data['fmc_name'] = @$user_mbp_data->fmc;

		$data['rtpo_id_home'] = @$user_mbp_data->mbp_rtpo_id_home;
		$data['rtpo_id_now'] = @$user_mbp_data->mbp_rtpo_id;
		$data['rtpo_name_home'] = @$user_mbp_data->rtpo_name_home;
		$data['rtpo_name_now'] = @$user_mbp_data->rtpo_name_now;
		$data['subject'] = @$user_mbp_data->subject;
		$data['text_message'] = @$user_mbp_data->text_message;
		$data['time'] = @$this->setDatedMYHis($user_mbp_data->active_at);


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}
	switch ($mbp_data->status) {
		case "AVAILABLE":
		$user_mbp_data = DB::table('mbp as m')
		->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
		->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
		->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
		->join('users as u', 'um.username', 'u.username')
		->select('*', 'm.status as mbp_status', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now')
		->where('m.mbp_id','=',$mbp_id)
		->first();

		$data['get_in'] = 'AVAILABLE';
		$data['status'] = $user_mbp_data->mbp_status;
		$data['mbp_name'] = $user_mbp_data->mbp_name;
		$data['name'] = $user_mbp_data->name;
		$data['phone'] = $user_mbp_data->phone;
		$data['latitude'] = $user_mbp_data->latitude;
		$data['longitude'] = $user_mbp_data->longitude;
		$data['borrowed'] = $borrowed;
		$data['class_name'] = '-';

		$data['fmc_id'] = $user_mbp_data->fmc_id;
		$data['fmc_name'] = $user_mbp_data->fmc;
		
		$data['rtpo_id_home'] = $user_mbp_data->mbp_rtpo_id_home;
		$data['rtpo_id_now'] = $user_mbp_data->mbp_rtpo_id;
		$data['rtpo_name_home'] = $user_mbp_data->rtpo_name_home;
		$data['rtpo_name_now'] = $user_mbp_data->rtpo_name_now;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;

		return response($res);

		break;
		case "UNAVAILABLE":

		$user_mbp_data = DB::table('mbp as m')
		->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
		->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
		->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
		->join('users as u', 'um.username', 'u.username')
		->join('message as msg', 'm.message_id', 'msg.id')   
		->select('*', 'm.status as mbp_status', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home','m.last_update as lu', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now')
		->where('m.mbp_id','=',$mbp_id)
		->first();

		$data['get_in'] = "UNAVAILABLE";
		$data['status'] = "UNAVAILABLE";
		$data['borrowed'] = $borrowed;
		$data['class_name'] = '-';

		$data['fmc_id'] = @$user_mbp_data->fmc_id;
		$data['fmc_name'] = @$user_mbp_data->fmc;

		$data['rtpo_id_home'] = @$user_mbp_data->mbp_rtpo_id_home;
		$data['rtpo_id_now'] = @$user_mbp_data->mbp_rtpo_id;
		$data['rtpo_name_home'] = @$user_mbp_data->rtpo_name_home;
		$data['rtpo_name_now'] = @$user_mbp_data->rtpo_name_now;

		$data['mbp_name'] = @$user_mbp_data->mbp_name;
		$data['name'] = @$user_mbp_data->name;
		$data['phone'] = @$user_mbp_data->phone;
		$data['latitude'] = @$user_mbp_data->latitude;
		$data['longitude'] = @$user_mbp_data->longitude;
		$data['subject'] = @$user_mbp_data->subject;
		$data['text_message'] = @$user_mbp_data->text_message;
		$data['time'] = @$this->setDatedMYHis($user_mbp_data->active_at);

		// $data['time a'] = date('i');

		// $dateb = strtotime(@$user_mbp_data->lu);
		// $data['time b'] = date('i', $dateb);
		// $data['time c'] = date('i', $dateb) + 1;
		// $data['time lu'] = @$user_mbp_data->lu;

	//    if (date('i') == date('i', $mbp_trouble->respon_date)) {//----------------------
	//   $res['success'] = true;
	//   $res['message'] = 'SUCCESS';
	//   return response($res);
	// }



		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
		
		break;
		default:
		$user_mbp_data = DB::table('mbp as m')
		->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
		->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
		->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
		->join('users as u', 'um.username', 'u.username')
		->join('supplying_power as sp', 'm.mbp_id', 'sp.mbp_id')
		->join('site as s', 'sp.site_id', 's.site_id')
		->select('*', 'm.status as mbp_status', 's.latitude as site_latitude', 's.longitude as site_longitude', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now','sp.user_rtpo_cn as ticket_by')
		->where('m.mbp_id','=',$mbp_id)
		->where('sp.finish','=',null)
		->first();

		$data['get_in'] = "DEFAULT";
		$data['ticket_by'] = $user_mbp_data->ticket_by;
		$data['telegram_username'] = "";
		$data['status'] = $user_mbp_data->mbp_status;
		$data['mbp_name'] = $user_mbp_data->mbp_name;
		$data['name'] = $user_mbp_data->name;
		$data['phone'] = $user_mbp_data->phone;
		$data['mbp_latitude'] = $user_mbp_data->latitude;
		$data['mbp_longitude'] = $user_mbp_data->longitude;
		$data['site_name'] = $user_mbp_data->site_name;
		$data['code_name'] = $user_mbp_data->site_id;
		$data['class_name'] = $user_mbp_data->site_class;
		$data['latitude'] = $user_mbp_data->site_latitude;
		$data['longitude'] = $user_mbp_data->site_longitude;
		$data['borrowed'] = $borrowed;
		$data['date_waiting'] = @strtotime(@$user_mbp_data->date_waiting);
		$data['date_onprogress'] = @strtotime(@$user_mbp_data->date_onprogress);
		$data['date_checkin'] = @strtotime(@$user_mbp_data->date_checkin);

		$data['fmc_id'] = $user_mbp_data->fmc_id;
		$data['fmc_name'] = $user_mbp_data->fmc;

		$data['rtpo_id_home'] = $user_mbp_data->mbp_rtpo_id_home;
		$data['rtpo_id_now'] = $user_mbp_data->mbp_rtpo_id;
		$data['rtpo_name_home'] = $user_mbp_data->rtpo_name_home;
		$data['rtpo_name_now'] = $user_mbp_data->rtpo_name_now;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
		break;
	}
	}
}

public function setDatedMYHis($date){
	if ($date==null) {
	return "";
	}else if ($date=='0000-00-00 00:00:00') {
	return "";
	}else{
	// return date("d-M-Y H:i:s", strtotime($date.''));
	return date("d M Y, H:i", strtotime($date.''));
		// return strtotime($date.'');
	}
}
public function getListAssignment(Request $request){ //deprecated

	$user_id = $request->input('user_id');

// cek apakah dia mbp? bila data ada maka tampilkan
	$mbp_data = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->join('supplying_power', 'mbp.mbp_id', '=', 'supplying_power.mbp_id')
	->select('*','mbp.mbp_id','supplying_power.sp_id','mbp.mbp_name','mbp.status'/*,DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status')*/)
	->where('users.id','=',$user_id)
	->where('mbp.status','!=','AVAILABLE')
	->where('mbp.status','!=','UNAVAILABLE')
	->where('supplying_power.finish','=',NULL)
	->get();


	$result = json_decode($mbp_data, true);
	
	if ($result==null) {
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_data;
	return response($res);
	}

	foreach ($result as $param => $row) {

	if ($row['submission']=="DELAY") {
		$data[$param]['status'] = 'DELAY';
	}else{
		$data[$param]['status'] = $row['status'].'';
	}

	$data[$param]['sp_id'] = $row['sp_id'];
	$data[$param]['sp_name'] = 'SP-'.$row['sp_id'];
	$data[$param]['mbp_id'] = $row['mbp_id'];
	$data[$param]['mbp_name'] = $row['mbp_name'].'';
	// $data[$param]['status'] = $row['status'].'';

	}


	if ($mbp_data) {

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;

	return response($res);
	}else{

	$res['success'] = true;
	$res['message'] = 'FAILED_GET_DATA_MBP';
	// $res['data'] = $mbp_data;

	return response($res);
	}
}
public function getListActiveNotActive(Request $request){

	$user_id = $request->input('user_id');

	// cek apakah dia mbp? bila data ada maka tampilkan
	$mbp_data = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->select('mbp.mbp_id','mbp.active_at','mbp.mbp_name','mbp.status as status'/*,DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status')*/)
	->where('users.id','=',$user_id)
	->get();

	$result = json_decode($mbp_data, true);
	if ($result==null) {
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_data;
	return response($res);
	}
	foreach ($result as $param => $row) {

	// $data[$param]['sp_id'] = 'SP-'.$row['sp_id'];
	// $data[$param]['mbp_id'] = $row['mbp_id'];
	// $data[$param]['mbp_name'] = $row['mbp_name'].'';
	// $data[$param]['status'] = $row['status'].'';

	switch ($row['status'].'') {
		case "UNAVAILABLE":
		$data[$param]['mbp_id'] = $row['mbp_id'];
		$data[$param]['mbp_name'] = $row['mbp_name'].'';
		$data[$param]['status'] = 'NOT_ACTIVE';
		$data[$param]['time'] = $this->setDatedMYHis($row['active_at'].'');

		// $res['success'] = true;
		// $res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		// return response($res);
		break;
		case "AVAILABLE":
		$data[$param]['mbp_id'] = $row['mbp_id'];
		$data[$param]['mbp_name'] = $row['mbp_name'].'';
		$data[$param]['status'] = 'ACTIVE';
		$data[$param]['time'] = '';

		// $res['success'] = true;
		// $res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		// return response($res);
		break;
		default:
		$data[$param]['mbp_id'] = $row['mbp_id'];
		$data[$param]['mbp_name'] = $row['mbp_name'].'';
		$data[$param]['status'] = 'WORKING';
		$data[$param]['time'] = '';
		// $res['success'] = true;
		// $res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		// return response($res);
	}

	}
	// array_multisort($revenue, SORT_DESC, $result);


	if ($mbp_data) {

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;

	return response($res);
	}else{

	$res['success'] = true;
	$res['message'] = 'FAILED_GET_DATA_MBP';
	// $res['data'] = $mbp_data;

	return response($res);
	}
}
public function mbp_update(Request $request){

	$mbp_data = $request->input('data');

	foreach ($mbp_data as $param => $row) {
	$master_mbp_data = DB::table('mbp')
	->select('*')
	->where('mbp_id','=',$row['mbp_id'])
	->first();

	if ($master_mbp_data!=null) {
		$query_ns = DB::table('lookup_fmc_cluster');
		$query_ns->where('rtpo_id',$row['rtpo_id']);
		$lookup_ns = $query_ns->first();

		$ns_id = $lookup_ns->ns_id;

		$updateMasterMbp = DB::table('mbp')
		->where('mbp_id','=',$row['mbp_id'])
		->update(
		[
			'mbp_id' => $row['mbp_id'],
			'mbp_name' => $row['mbp_name'],
			'fmc_id' => $row['fmc_id'],
			'fmc' => $row['fmc'],
			'cluster_id' => $row['cluster_id'],
			'cluster' => $row['cluster'],
			'active' => $row['status'],
			'created_by' =>@ $row['created_by'],
			'date_created' => @$row['date_created'],
			'update_by' => @$row['update_by'],
			'last_update' => @$row['last_update'],
			'rtpo_id_home' => $row['rtpo_id'],
			// 'rtpo' => $row['rtpo'],
			'regional' => $row['regional'],
			'regional_home' => $row['regional'],
			'ns_id' => $ns_id,
			'ns_id_home' => $ns_id,
		]
		);
		if (!$updateMasterMbp) {
		$res['success'] = false;
		$res['message'] = 'FAILED_UPDATE_DATA_MBP';
		$res['data'] = $row;
		return response($res);
		}
	} else{
		$query_ns = DB::table('lookup_fmc_cluster');
		$query_ns->where('rtpo_id',$row['rtpo_id']);
		$lookup_ns = $query_ns->first();

		$ns_id = $lookup_ns->ns_id;


		$insertMasterMbp = DB::table('mbp')->insert(
		[
			'mbp_id' => $row['mbp_id'],
			'mbp_name' => $row['mbp_name'],
			'fmc_id' => $row['fmc_id'],
			'fmc' => $row['fmc'],
			'cluster_id' => $row['cluster_id'],
			'cluster' => $row['cluster'],
			'active' => $row['status'],
			'created_by' => @$row['created_by'],
			'date_created' => @$row['date_created'],
			'update_by' => @$row['update_by'],
			'last_update' => @$row['last_update'],
			'rtpo_id' => $row['rtpo_id'],
			'rtpo_id_home' => $row['rtpo_id'],
			// 'rtpo' => $row['rtpo'],
			'regional' => $row['regional'],
			'regional_home' => $row['regional'],
			'ns_id' => $ns_id,
			'ns_id_home' => $ns_id,
		]
		);

		if ($insertMasterMbp<1) {
		$res['success'] = false;
		$res['message'] = 'FAILED_INSERT_DATA_MBP';
		$res['data'] = $row;
		return response($res);
		}
	}
	}
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	return response($res);
}
public function getMbpFmc(Request $request){
	
	$fmc_id = $request->input('fmc_id');

	$mbp_data = DB::table('mbp')
	->join('master_mbp', 'mbp.mbp_id', 'master_mbp.mbp_id')
	->join('rtpo as rtpo_now', 'mbp.rtpo_id', 'rtpo_now.rtpo_id')
	->join('rtpo as rtpo_home', 'mbp.rtpo_id_home', 'rtpo_home.rtpo_id')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.mbp_mt_nik', 'users.id')
	->select('mbp.mbp_id','mbp.mbp_name','user_mbp.mbp_mt_nik as driver_nik','user_mbp.mbp_mt_cn as driver_cn','users.phone', 'mbp.status','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id', 'rtpo_now.rtpo_name as rtpo_name', 'mbp.rtpo_id_home as rtpo_id_home', 'rtpo_home.rtpo_name as rtpo_home', 'master_mbp.fmc_id', 'master_mbp.fmc', 'master_mbp.cluster_id', 'master_mbp.cluster')
	->where('master_mbp.fmc_id','=',$fmc_id)
	->get();

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_data;
	return response($res);
}
public function getMbpData(Request $request){
	
	$mbp_id = $request->input('mbp_id');

	$mbp_data = DB::table('mbp')
	->join('rtpo as rtpo_now', 'mbp.rtpo_id', 'rtpo_now.rtpo_id')
	->join('rtpo as rtpo_home', 'mbp.rtpo_id_home', 'rtpo_home.rtpo_id')
	->select('mbp.mbp_id','mbp.mbp_name', 'mbp.status','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id', 'rtpo_now.rtpo_name as rtpo_name', 'mbp.rtpo_id_home as rtpo_id_home', 'rtpo_home.rtpo_name as rtpo_home')
	// ->select('mbp.mbp_id','mbp.mbp_name', 'mbp.status','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id')
	->where('mbp.mbp_id','=',$mbp_id)
	->first();

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_data;
	return response($res);
}
// public function getListMyMbpStatus(Request $request){
//   date_default_timezone_set("Asia/Jakarta");
//   $date_now = date('Y-m-d H:i:s');

//   $rtpo_id = $request->input('rtpo_id');


//     $mbp_data = DB::table('mbp')
//     // ->join('master_mbp', 'mbp.mbp_id', 'master_mbp.mbp_id')
//     ->leftJoin('user_mbp as um', 'mbp.mbp_id', 'um.mbp_id')
//     ->join('rtpo as rtpo_now', 'mbp.rtpo_id', 'rtpo_now.rtpo_id')
//     ->join('rtpo as rtpo_home', 'mbp.rtpo_id_home', 'rtpo_home.rtpo_id')
//     // ->select('*','mbp.mbp_id','mbp.mbp_name', 'mbp.status','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id', 'rtpo_now.rtpo_name as rtpo_name', 'mbp.rtpo_id_home as rtpo_id_home', 'rtpo_home.rtpo_name as rtpo_home')
//     ->select(/*'*',*/'mbp.mbp_id','mbp.mbp_name','um.username as mbp_driver', 'mbp.status','mbp.regional','mbp.cluster_id','mbp.cluster','mbp.fmc_id','mbp.fmc','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id', 'rtpo_now.rtpo_name as rtpo_name', 'mbp.rtpo_id_home as rtpo_id_home', 'rtpo_home.rtpo_name as rtpo_home','mbp.submission' )
//     ->where('mbp.active', '=', '1')
//     ->where('mbp.rtpo_id', '=', $rtpo_id);
// }
public function getMbpDataAll(Request $request){
	
	$cluster_id = $request->input('cluster_id');
	$fmc_id = $request->input('fmc_id');
	$rtpo_id = $request->input('rtpo_id');
	$regional = $request->input('regional');

	$mbp_data = DB::table('mbp')
	// ->join('master_mbp', 'mbp.mbp_id', 'master_mbp.mbp_id')
	->leftJoin('user_mbp as um', 'mbp.mbp_id', 'um.mbp_id')
	->leftjoin('users as u', 'um.username', 'u.username')
	->join('rtpo as rtpo_now', 'mbp.rtpo_id', 'rtpo_now.rtpo_id')
	->join('rtpo as rtpo_home', 'mbp.rtpo_id_home', 'rtpo_home.rtpo_id')
	// ->select('*','mbp.mbp_id','mbp.mbp_name', 'mbp.status','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id', 'rtpo_now.rtpo_name as rtpo_name', 'mbp.rtpo_id_home as rtpo_id_home', 'rtpo_home.rtpo_name as rtpo_home')
	->select(/*'*',*/'mbp.mbp_id','mbp.mbp_name','u.name as mbp_driver', 'mbp.status','mbp.regional','mbp.cluster_id','mbp.cluster','mbp.fmc_id','mbp.fmc','mbp.latitude','mbp.longitude','mbp.rtpo_id as rtpo_id', 'rtpo_now.rtpo_name as rtpo_name', 'mbp.rtpo_id_home as rtpo_id_home', 'rtpo_home.rtpo_name as rtpo_home','mbp.submission' )
	->where('mbp.active', '=', '1');

	if ($cluster_id!=null) {

	$mbp_data = $mbp_data->where('mbp.cluster_id','=',$cluster_id);

	} 
	if ($fmc_id!=null) {

	$mbp_data = $mbp_data->where('mbp.fmc_id','=',$fmc_id);

	} 
	if ($rtpo_id!=null) {

	$mbp_data = $mbp_data->where('mbp.rtpo_id_home','=',$rtpo_id);

	} 
	if ($regional!=null) {

	$mbp_data = $mbp_data->where('mbp.regional','=',$regional);
	} 


	$mbp_data = $mbp_data
	->orderBy('mbp.mbp_id', 'asc')
	->get();


	$result = json_decode($mbp_data, true);
	if ($result==null) {
	
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_data;
	// $res['data'] = $data;
	return response($res);

	}

	foreach ($result as $param => $row) {
	if ($row['submission']=="DELAY") {
		$row['status'] = "DELAY";

	}

	$data[$param] =  $row;

	unset($data[$param]['submission']);
	}
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	// $res['data'] = $mbp_data;
	$res['data'] = $data;
	return response($res);
}

public function deleteRecomendationMbpSite(Request $request){
	$mbp_id = $request->input('mbp_id');

	DB::table('mbp_recommendation')
	->where('mbp_id','=',$mbp_id)
	->delete();

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['mbp_id'] = $mbp_id;
	return response($res);
}

public function setRecomendationMbpSite(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');

	$set_type = $request->input('set_type');// diisi delete / insert
	$list_site = $request->input('list_site');
	$mbp_id = $request->input('mbp_id');
	$created_by = $request->input('created_by');
	$updated_by = $request->input('updated_by');
	$last_updated = $request->input('last_updated');

	$date_created = $request->input('date_created');

	DB::table('mbp_recommendation')
	->where('mbp_id','=',$mbp_id)
	// ->where('site_id','=',$key['site_id'])      
	// ->whereNotIn('site_id',$list_site)
	->delete();
	foreach ($list_site as $key) {

	
	$insertRecomm = DB::table('mbp_recommendation')->insert(
		[
		'mbp_id' => $mbp_id,
		'site_id' => $key['site_id'],
		'site_name' => $key['site_name'],
		'created_by' => $created_by,
		'updated_by' => $updated_by,
		'created_at' => $date_created,
		'last_updated' => $last_updated,
		]
	);
	}

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	return response($res);
}

public function getRecomendationMbpSite(Request $request){

	$mbp_id = $request->input('mbp_id');
	$username = $request->input('username');

	$recomm_data = DB::table('mbp_recommendation as mr')
	->join('site as s', 'mr.site_id', 's.site_id')
	->join('mbp as m', 'mr.mbp_id', 'm.mbp_id')
	->select('s.site_id','s.site_name','s.class_id','s.class_id as class_name', 's.latitude', 's.longitude', 'm.latitude as m_lat', 'm.longitude as m_lon', 'm.mbp_name', 's.status')
	->where('s.is_allocated','=','0');

	if ($mbp_id!=null) {
	$recomm_data=$recomm_data->where('mr.mbp_id','=',$mbp_id)->get();
	}elseif ($username!=null) {
	$recomm_data=$recomm_data->where('mr.created_by','=',$username)->get();
	}

	// $res['data'] = @$recomm_data;
	// return response($res);

	$result = json_decode($recomm_data, true);

	if ($result==null) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = @$recomm_data;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($result as $param =>$row) {
	$get_distance = $rc->distance($row['latitude'], $row['longitude'], $row['m_lat'], $row['m_lon'], 'K'); 
	$recomm_data_arr[$param] = $row;
	$recomm_data_arr[$param]['distance'] = number_format($get_distance,1).' km';
	$recomm_data_arr[$param]['duration'] = '';
	$recomm_data_arr[$param]['distancevalue'] = number_format($get_distance,1);
	$recomm_data_arr[$param]['durationvalue'] = '';
	$recomm_data_arr[$param]['node'] = '';
	$recomm_data_arr[$param]['class_id'] = strtolower($row['class_id']);
	$recomm_data_arr[$param]['class_name'] = strtolower($row['class_name']);
	unset($recomm_data_arr[$param]['m_lat']);
	unset($recomm_data_arr[$param]['m_lon']);
	}

	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = @$recomm_data_arr;
	return response($res);
}


public function setKwhMeterBefore(Request $request){

	$sp_id = $request->input('sp_id');
	$send_by = $request->input('send_by');
	$value = $request->input('value');

	if ($sp_id == null) {
	$res['success'] = false;
	$res['message'] = 'SP_ID NOT FOUND';
	return response($res);
	}
	if ($send_by == null) {
	$res['success'] = false;
	$res['message'] = 'SEND_BY NOT FOUND';
	return response($res);
	}
	if ($value == null) {
	$res['success'] = false;
	$res['message'] = 'VALUE NOT FOUND';
	return response($res);
	}

	$updateSp = DB::table('supplying_power')
		->where('sp_id','=',$sp_id)
		->update(
		[
			'kwh_meter_before' => $value,
		]
		);


	$sp_data = DB::table('supplying_power')
	->select('*')
	->where('sp_id','=',$sp_id)
	->first();

	if (@$sp_data->kwh_meter_before != $value) {
	$res['success'] = false;
	$res['message'] = 'FAILED INSERT DATA';
	return response($res);
	}

	$res['success'] = true;
	$res['message'] = 'Success!';
	return response($res);
}

public function setKwhMeterAfter(Request $request){

	$sp_id = $request->input('sp_id');
	$send_by = $request->input('send_by');
	$value = $request->input('value');

	if ($sp_id == null) {
	$res['success'] = false;
	$res['message'] = 'SP_ID NOT FOUND';
	return response($res);
	}
	if ($send_by == null) {
	$res['success'] = false;
	$res['message'] = 'SEND_BY NOT FOUND';
	return response($res);
	}
	if ($value == null) {
	$res['success'] = false;
	$res['message'] = 'VALUE NOT FOUND';
	return response($res);
	}

	$updateSp = DB::table('supplying_power')
		->where('sp_id','=',$sp_id)
		->update(
		[
			'kwh_meter_after' => $value,
		]
		);


	$sp_data = DB::table('supplying_power')
	->select('*')
	->where('sp_id','=',$sp_id)
	->first();

	if (@$sp_data->kwh_meter_after != $value) {
	$res['success'] = false;
	$res['message'] = 'FAILED INSERT DATA';
	return response($res);
	}

	$res['success'] = true;
	$res['message'] = 'Success!';
	return response($res);
}

public function setRunningHourBefore(Request $request){

	$sp_id = $request->input('sp_id');
	$send_by = $request->input('send_by');
	$value = $request->input('value');

	if ($sp_id == null) {
	$res['success'] = false;
	$res['message'] = 'SP_ID NOT FOUND';
	return response($res);
	}
	if ($send_by == null) {
	$res['success'] = false;
	$res['message'] = 'SEND_BY NOT FOUND';
	return response($res);
	}
	if ($value == null) {
	$res['success'] = false;
	$res['message'] = 'VALUE NOT FOUND';
	return response($res);
	}

	$updateSp = DB::table('supplying_power')
		->where('sp_id','=',$sp_id)
		->update(
		[
			'running_hour_before' => $value,
		]
		);


	$sp_data = DB::table('supplying_power')
	->select('*')
	->where('sp_id','=',$sp_id)
	->first();

	if (@$sp_data->running_hour_before != $value) {
	$res['success'] = false;
	$res['message'] = 'FAILED INSERT DATA';
	return response($res);
	}

	$res['success'] = true;
	$res['message'] = 'Success!';
	return response($res);
}

public function setRunningHourAfter(Request $request){

	$sp_id = $request->input('sp_id');
	$send_by = $request->input('send_by');
	$value = $request->input('value');

	if ($sp_id == null) {
	$res['success'] = false;
	$res['message'] = 'SP_ID NOT FOUND';
	return response($res);
	}
	if ($send_by == null) {
	$res['success'] = false;
	$res['message'] = 'SEND_BY NOT FOUND';
	return response($res);
	}
	if ($value == null) {
	$res['success'] = false;
	$res['message'] = 'VALUE NOT FOUND';
	return response($res);
	}

	$updateSp = DB::table('supplying_power')
		->where('sp_id','=',$sp_id)
		->update(
		[
			'running_hour_after' => $value,
		]
		);


	$sp_data = DB::table('supplying_power')
	->select('*')
	->where('sp_id','=',$sp_id)
	->first();

	if (@$sp_data->running_hour_after != $value) {
	$res['success'] = false;
	$res['message'] = 'FAILED INSERT DATA';
	return response($res);
	}

	$res['success'] = true;
	$res['message'] = 'Success!';
	return response($res);
}





public function getMyMbpCPO(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');


	$regional = $request->input('regional');

	$page = $request->input('page');
	$limit = 20;
	$offset = ($page-1)*$limit;

	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	->where('mbp.regional','=',$regional)
	->offset($offset)
	->limit($limit)
	->get();
	

	$mbp_result = json_decode($data_site, true);

	if (!$mbp_result) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['cluster_id'] = $mbp_result[$param]['cluster_id'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['regional'] = $mbp_result[$param]['regional'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['submission_id'] = $mbp_result[$param]['submission_id'];
	$data[$param]['message_id'] = $mbp_result[$param]['message_id'];
	$data[$param]['active_at'] = $mbp_result[$param]['active_at'];
	$data[$param]['latitude'] = $mbp_result[$param]['latitude'];
	$data[$param]['longitude'] = $mbp_result[$param]['longitude'];
	$data[$param]['fmc'] = $mbp_result[$param]['fmc'];
	$data[$param]['active'] = $mbp_result[$param]['active'];
	$data[$param]['last_update'] = $mbp_result[$param]['last_update'];
	$data[$param]['user_id'] = $mbp_result[$param]['user_id'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];
	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();

	$data[$param]['site_latitude'] = @$get_sp_active->s_lat;
	$data[$param]['site_longitude'] = @$get_sp_active->s_lon;
	$data[$param]['site_id'] = @$get_sp_active->site_id;
	$data[$param]['site_name'] = @$get_sp_active->site_name;
	$data[$param]['mbp_latitude'] = @$mbp_result[$param]['m_lat'];
	$data[$param]['mbp_longitude'] = @$mbp_result[$param]['m_lon'];
	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
		$time_req = $running_backup;
		}

		if ( $data[$param]['submission']=='DELAY') {


		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	

	$data[$param]['traveling_time'] = @$waktu_tempuh;
	$data[$param]['time'] = @$time_req;
	$data[$param]['delay_time'] = @$delay_time;
	$data[$param]['task_count'] = $task_count;
	$data[$param]['is resting'] = $is_resting;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data;
	return response($res);
}


public function getMyMbpNS(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');


	$ns_id = $request->input('ns_id');

	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	->where('mbp.ns_id','=',$ns_id)
	->get();
	

	$mbp_result = json_decode($data_site, true);

	if (!$mbp_result) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['cluster_id'] = $mbp_result[$param]['cluster_id'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['regional'] = $mbp_result[$param]['regional'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['submission_id'] = $mbp_result[$param]['submission_id'];
	$data[$param]['message_id'] = $mbp_result[$param]['message_id'];
	$data[$param]['active_at'] = $mbp_result[$param]['active_at'];
	$data[$param]['latitude'] = $mbp_result[$param]['latitude'];
	$data[$param]['longitude'] = $mbp_result[$param]['longitude'];
	$data[$param]['fmc'] = $mbp_result[$param]['fmc'];
	$data[$param]['active'] = $mbp_result[$param]['active'];
	$data[$param]['last_update'] = $mbp_result[$param]['last_update'];
	$data[$param]['user_id'] = $mbp_result[$param]['user_id'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];
	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();

	$data[$param]['site_latitude'] = @$get_sp_active->s_lat;
	$data[$param]['site_longitude'] = @$get_sp_active->s_lon;
	$data[$param]['site_id'] = @$get_sp_active->site_id;
	$data[$param]['site_name'] = @$get_sp_active->site_name;
	$data[$param]['mbp_latitude'] = @$mbp_result[$param]['m_lat'];
	$data[$param]['mbp_longitude'] = @$mbp_result[$param]['m_lon'];
	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
		$time_req = $running_backup;
		}

		if ( $data[$param]['submission']=='DELAY') {


		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	

	$data[$param]['traveling_time'] = @$waktu_tempuh;
	$data[$param]['time'] = @$time_req;
	$data[$param]['delay_time'] = @$delay_time;
	$data[$param]['task_count'] = $task_count;
	$data[$param]['is resting'] = $is_resting;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $data;
	return response($res);
}



public function misiPenyelamatanDataMbp(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');

	// $ns_id = $request->input('ns_id');

	$sp_data = DB::table('image_sp')
	->select('*')
	->wheremonth('date','=',"10")
	->get();

	foreach ($sp_data as $key => $value) {
	$fn_data = explode("_",$value->fname);

	$cat = @$fn_data["5"];
	$site_id = @$fn_data["1"];
	$date_create = @$fn_data["2"];
	$sp_id = @$fn_data["3"];
	$sp_value = @$fn_data["4"];

	$res['fname'] = $value->fname;
	$res['cat'] = $cat;
	$res['site_id'] = $site_id;
	$res['date_create'] = $date_create;
	$res['sp_id'] = $sp_id;
	$res['sp_value'] = $sp_value;


	
	$insertsp = DB::table('supplying_power')
	->where("sp_id",$sp_id);

	if ($cat=="BEFOREKWHMETER.jpg") {
		$insertsp->update(
		[
			'kwh_meter_before_image' => @$value->fname,
			'kwh_meter_before' => @$sp_value,
			'is_sync' => 0,
			'last_update' => $date_now,
		]
		);
	}
	if ($cat=="BEFORERUNNINGHOUR.jpg") {
		$insertsp->update(
		[
			'running_hour_before_image' => @$value->fname,
			'running_hour_before' => @$sp_value,
			'is_sync' => 0,
			'last_update' => $date_now,
		]
		);
	}
	if ($cat=="AFTERKWHMETER.jpg") {
		$insertsp->update(
		[
			'kwh_meter_after_image' => @$value->fname,
			'kwh_meter_after' => @$sp_value,
			'is_sync' => 0,
			'last_update' => $date_now,
		]
		);
	}
	if ($cat=="AFTERRUNNINGHOUR.jpg") {
		$insertsp->update(
		[
			'running_hour_after_image' => @$value->fname,
			'running_hour_after' => @$sp_value,
			'is_sync' => 0,
			'last_update' => $date_now,
		]
		);
	}


	// return response($res);

	}

	$res['data'] = $sp_data;
	return response($res);

	// $data_site = DB::table('image_sp')
	// ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	// ->join('users', 'user_mbp.username', 'users.username')
	// ->join('mbp_status', 'mbp.status', 'mbp_status.status')
	// ->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	// ->where('mbp.ns_id','=',$ns_id)
	// ->get();
}

public function getMyMbpPaginate(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');

	// $delete_date_strtotime = strtotime($date_now." -1 day");
	// $delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);

	$rtpo_id = $request->input('rtpo_id');
	$page = $request->input('page');
	$search = $request->input('search');
	$filter = $request->input('filter');

	$limit = 20;
	$offset = ($page-1)*$limit;


	// $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
	$data_site = DB::table('mbp')
	// ->leftJoin('supplying_power as sp', 'mbp.mbp_id', 'sp.mbp_id')
	// ->leftJoin('site as s', 'sp.site_id', 's.site_id')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	// ->where('finish','=',null)
	// ->whereNull('sp.finish')
	->where('mbp.rtpo_id','=',$rtpo_id)
	->orWhere('mbp.rtpo_id_home','=',$rtpo_id)
	->whereraw('(mbp.mbp_id like "%'.$search.'%" or mbp.mbp_name like "%'.$search.'%")')
	->offset($offset)
	->limit($limit)
	// ->orderBy('mbp_status.bobot', 'ASC')
	->get();
	
	// $data_site= DB::select("SELECT")

	$mbp_result = json_decode($data_site, true);

	if (!$mbp_result) {
	// $res['success'] = false;
	// $res['message'] = 'Cannot find data!';
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['cluster_id'] = $mbp_result[$param]['cluster_id'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['regional'] = $mbp_result[$param]['regional'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['submission_id'] = $mbp_result[$param]['submission_id'];
	$data[$param]['message_id'] = $mbp_result[$param]['message_id'];
	$data[$param]['active_at'] = $mbp_result[$param]['active_at'];
	$data[$param]['latitude'] = $mbp_result[$param]['latitude'];
	$data[$param]['longitude'] = $mbp_result[$param]['longitude'];
	$data[$param]['fmc'] = $mbp_result[$param]['fmc'];
	$data[$param]['active'] = $mbp_result[$param]['active'];
	$data[$param]['last_update'] = $mbp_result[$param]['last_update'];
	$data[$param]['user_id'] = $mbp_result[$param]['user_id'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];
	// $data[$param]['time new count'] = $date_new_count;

	// $task_count = DB::table('supplying_power')
	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();
	// if ($get_sp_active!=null) {
	//   # code...
	// }

	$data[$param]['site_latitude'] = @$get_sp_active->s_lat;
	$data[$param]['site_longitude'] = @$get_sp_active->s_lon;
	$data[$param]['site_id'] = @$get_sp_active->site_id;
	$data[$param]['site_name'] = @$get_sp_active->site_name;
	$data[$param]['mbp_latitude'] = @$mbp_result[$param]['m_lat'];
	$data[$param]['mbp_longitude'] = @$mbp_result[$param]['m_lon'];
	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		// $time_req = $get_sp_active->date_onprogress;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		// $time_req = $hours .':'.$minutes.':'.$second;
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
		// $data[$param]['traveling_time'] = $waktu_tempuh;

		// $time_req = $hours .':'.$minutes;
		$time_req = $running_backup;
		// $time_req = $get_sp_active->date_checkin;
		}

		if ( $data[$param]['submission']=='DELAY') {

		// $datetime1 = new DateTime($data[$param]['active_at']);
		// $datetime2 = new DateTime($date_now);
		// $running_bc = $datetime2->diff($datetime1);


		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	

	// $data[$param]['onpro'] = @$get_sp_active->date_onprogress;
	// $data[$param]['chek'] = @$get_sp_active->date_checkin;
	$data[$param]['traveling_time'] = @$waktu_tempuh;
	$data[$param]['time'] = @$time_req;
	$data[$param]['delay_time'] = @$delay_time;
	$data[$param]['task_count'] = $task_count;
	$data[$param]['is resting'] = $is_resting;
	// $data[$param]['tme'] = $waktu_nganggur;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	// usort($data, array($this, 'sort_by_counttask'));
	// usort($data, array($this, 'sort_by_bobot'));

	array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);

}

public function getMyMbpCPOPaginate(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');


	$regional = $request->input('regional');

	$page = $request->input('page');
	$limit = 20;
	$offset = ($page-1)*$limit;

	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	->where('mbp.regional','=',$regional)
	->offset($offset)
	->limit($limit)
	->get();
	

	$mbp_result = json_decode($data_site, true);

	if (!$mbp_result) {
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];
	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();

	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
		$time_req = $running_backup;
		}

		if ( $data[$param]['submission']=='DELAY') {


		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	

	$data[$param]['task_count'] = $task_count;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
}

public function getMyMbpNSPaginate(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');


	$ns_id = $request->input('ns_id');

	$page = $request->input('page');
	$limit = 20;
	$offset = ($page-1)*$limit;

	$data_site = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	->where('mbp.ns_id','=',$ns_id)
	->offset($offset)
	->limit($limit)
	->get();
	

	$mbp_result = json_decode($data_site, true);

	if (!$mbp_result) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['cluster_id'] = $mbp_result[$param]['cluster_id'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['regional'] = $mbp_result[$param]['regional'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['submission_id'] = $mbp_result[$param]['submission_id'];
	$data[$param]['message_id'] = $mbp_result[$param]['message_id'];
	$data[$param]['active_at'] = $mbp_result[$param]['active_at'];
	$data[$param]['latitude'] = $mbp_result[$param]['latitude'];
	$data[$param]['longitude'] = $mbp_result[$param]['longitude'];
	$data[$param]['fmc'] = $mbp_result[$param]['fmc'];
	$data[$param]['active'] = $mbp_result[$param]['active'];
	$data[$param]['last_update'] = $mbp_result[$param]['last_update'];
	$data[$param]['user_id'] = $mbp_result[$param]['user_id'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];
	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();

	$data[$param]['site_latitude'] = @$get_sp_active->s_lat;
	$data[$param]['site_longitude'] = @$get_sp_active->s_lon;
	$data[$param]['site_id'] = @$get_sp_active->site_id;
	$data[$param]['site_name'] = @$get_sp_active->site_name;
	$data[$param]['mbp_latitude'] = @$mbp_result[$param]['m_lat'];
	$data[$param]['mbp_longitude'] = @$mbp_result[$param]['m_lon'];
	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
		$time_req = $running_backup;
		}

		if ( $data[$param]['submission']=='DELAY') {


		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	

	$data[$param]['traveling_time'] = @$waktu_tempuh;
	$data[$param]['time'] = @$time_req;
	$data[$param]['delay_time'] = @$delay_time;
	$data[$param]['task_count'] = $task_count;
	$data[$param]['is resting'] = $is_resting;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
}

public function getRecomendationMbpSitePaginate(Request $request){

	$mbp_id = $request->input('mbp_id');
	$username = $request->input('username');

	$page = $request->input('page');

	$limit = 20;
	$offset = ($page-1)*$limit;

	$recomm_data = DB::table('mbp_recommendation as mr')
	->join('site as s', 'mr.site_id', 's.site_id')
	->join('mbp as m', 'mr.mbp_id', 'm.mbp_id')
	->select('s.site_id','s.site_name','s.class_id','s.class_id as class_name', 's.latitude', 's.longitude', 'm.latitude as m_lat', 'm.longitude as m_lon', 'm.mbp_name', 's.status')
	->where('s.is_allocated','=','0')
	->offset($offset)
	->limit($limit);

	if ($mbp_id!=null) {
	$recomm_data=$recomm_data->where('mr.mbp_id','=',$mbp_id)->get();
	}elseif ($username!=null) {
	$recomm_data=$recomm_data->where('mr.created_by','=',$username)->get();
	}

	// $res['data'] = @$recomm_data;
	// return response($res);

	$result = json_decode($recomm_data, true);

	if ($result==null) {
	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = @$recomm_data;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($result as $param =>$row) {
	$get_distance = $rc->distance($row['latitude'], $row['longitude'], $row['m_lat'], $row['m_lon'], 'K'); 
	$recomm_data_arr[$param] = $row;
	$recomm_data_arr[$param]['distance'] = number_format($get_distance,1).' km';
	$recomm_data_arr[$param]['duration'] = '';
	$recomm_data_arr[$param]['distancevalue'] = number_format($get_distance,1);
	$recomm_data_arr[$param]['durationvalue'] = '';
	$recomm_data_arr[$param]['node'] = '';
	$recomm_data_arr[$param]['class_id'] = strtolower($row['class_id']);
	$recomm_data_arr[$param]['class_name'] = strtolower($row['class_name']);
	unset($recomm_data_arr[$param]['m_lat']);
	unset($recomm_data_arr[$param]['m_lon']);
	}

	$res['success'] = true;
	$res['message'] = 'Success!';
	$res['data'] = @$recomm_data_arr;
	return response($res);
}

public function getListAssignmentPaginate(Request $request){
	date_default_timezone_set("Asia/Jakarta");

	$date_now = date('Y-m-d H:i:s');
	$date_strtotime = strtotime($date_now." -30 minutes");
	$date2 = date('Y-m-d H:i:s',$date_strtotime);

	$user_id = $request->input('user_id');

	$page = $request->input('page');

	$limit = 20;
	$offset = ($page-1)*$limit;

// cek apakah dia mbp? bila data ada maka tampilkan
	/*query lama deprecated
	$mbp_data = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->join('supplying_power', 'mbp.mbp_id', '=', 'supplying_power.mbp_id')
	->select('*','mbp.mbp_id','supplying_power.sp_id','mbp.mbp_name','mbp.status')
	->where('users.id','=',$user_id)
	->where('mbp.status','!=','AVAILABLE')
	->where('mbp.status','!=','UNAVAILABLE')
	->where('supplying_power.finish','=',NULL)
	->offset($offset)
	->limit($limit)
	->get();


	$result = json_decode($mbp_data, true);
	
	if ($result==null) {
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_data;
	return response($res);
	}

	foreach ($result as $param => $row) {

	if ($row['submission']=="DELAY") {
		$data[$param]['status'] = 'DELAY';
	}else{
		$data[$param]['status'] = $row['status'].'';
	}

	$data[$param]['sp_id'] = $row['sp_id'];
	$data[$param]['sp_name'] = 'SP-'.$row['sp_id'];
	$data[$param]['mbp_id'] = $row['mbp_id'];
	$data[$param]['mbp_name'] = $row['mbp_name'].'';
	// $data[$param]['status'] = $row['status'].'';

	}
	*/

	$data_sp = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->join('supplying_power', 'mbp.mbp_id', '=', 'supplying_power.mbp_id')
	->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_id','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.finish','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','supplying_power.unique_id')
	->whereraw('(supplying_power.date_finish >"'.$date_now.'" or supplying_power.date_finish is null)')
	//->where('detail_finish',null)
	->where('users.id','=',$user_id)
	->where('mbp.status','!=','AVAILABLE')
	->where('mbp.status','!=','UNAVAILABLE')
	//->where('supplying_power.finish','=',NULL)
	//->where('supplying_power.mbp_id','mbp.mbp_id')
	//->where('supplying_power.date_waiting','>',$date2)
	->orderBy('supplying_power.sp_id', 'desc')  
	->offset($offset)
	->limit($limit)
	->get();

	/*
	if ($user_id=='mbp_rio_dumy' || $user_id=='mbp_lf_dummy') {
	$data_sp = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', '=', 'users.username')
	->join('supplying_power', 'mbp.mbp_id', '=', 'supplying_power.mbp_id')
	->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_id','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.finish','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','supplying_power.unique_id')
	->whereraw('(supplying_power.date_finish >"'.$date_now.'" or supplying_power.date_finish is null)')
	//->where('detail_finish',null)
	->where('supplying_power.sp_id','=',42531)
	->where('mbp.status','!=','AVAILABLE')
	->where('mbp.status','!=','UNAVAILABLE')
	//->where('supplying_power.finish','=',NULL)
	//->where('supplying_power.mbp_id','mbp.mbp_id')
	//->where('supplying_power.date_waiting','>',$date2)
	->orderBy('supplying_power.sp_id', 'desc')  
	->offset($offset)
	->limit($limit)
	->get();
	}
	*/

	foreach ($data_sp as $key => $value) {
	$value->date_waiting = ($value->date_waiting==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_waiting);
	$value->date_onprogress = ($value->date_onprogress==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_onprogress);
	$value->date_checkin = ($value->date_checkin==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_checkin);
	$value->date_finish = ($value->date_finish==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_finish);

	//$value->finish = '-';

	$data_mbp = DB::table('mbp')
	->select('mbp_id','mbp_name','status','submission')
	->where('mbp_id',$value->mbp_id)
	->first();
	
	$value->status = str_replace("_", " ", $data_mbp->status);

	if ($data_mbp->submission=='DELAY') {
		$value->status = 'DELAY';
	}
	//$value->status = $data_mbp->status;
	}

	if ($data_sp) {

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data_sp;

	return response($res);
	}else{

	$res['success'] = true;
	$res['message'] = 'FAILED_GET_DATA_MBP';
	// $res['data'] = $mbp_data;

	return response($res);
	}
}

public function getStatusMbp2($mbp_id){
	//$mbp_id = $request->input('mbp_id');


		// $data['image'] = @$image.'http://ichef.bbci.co.uk/wwfeatures/wm/live/1280_640/images/live/p0/14/pz/p014pzq8.jpg';

	$mbp_data = DB::table('mbp as m')
	->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
	->join('users as u', 'um.username', 'u.username')
	->select('*','m.status as mbp_status','u.id as u_user_id','m.rtpo_id as m_rtpo_id','m.rtpo_id_home as m_rtpo_id_home')
	->where('m.mbp_id',$mbp_id)
	->first();

	if ($mbp_data==null) {
	$res['success'] = false;
	$res['message'] = 'CANNOT_FIND_DATA_MBP';
	return response($res);
	}

	if ($mbp_data->m_rtpo_id!=$mbp_data->m_rtpo_id_home) {
	$borrowed = true;
	}else{
	$borrowed = false;
	}

	$data['status'] = $mbp_data->mbp_status;
	$data['user_id'] = $mbp_data->u_user_id;
	$data['borrowed'] = $borrowed;

	if ($mbp_data->mbp_status == 'AVAILABLE' || $mbp_data->mbp_status == 'UNAVAILABLE' ) {
	$data['time'] = $mbp_data->active_at;

	}else{

	$sp_data = DB::table('supplying_power as sp')
	->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
	->join('site as s', 'sp.site_id', 's.site_id')
	->select('*','m.status as m_status','m.mbp_name as m_mbp_name','m.latitude as m_latitude','m.longitude as m_longitude','s.latitude as s_latitude','s.longitude as s_longitude','s.site_id as s_site_id','s.site_name as s_site_name','sp.unique_id')
	->where('m.mbp_id',$mbp_id)
	//->where('sp.finish',null)
	->orderBy('sp.sp_id', 'desc')
	->first();

	$data['status_BE'] = $sp_data->m_status;
	$data['status'] = $sp_data->m_status;
	$data['borrowed'] = $borrowed;
	$data['rtpo_username'] = $sp_data->user_rtpo;
	$data['mbp_name'] = $sp_data->m_mbp_name;
	$data['mbp_latitude'] = $sp_data->m_latitude;
	$data['mbp_longitude'] = $sp_data->m_longitude;

	$data['sp_id'] = $sp_data->sp_id;
	$data['site_name'] = $sp_data->s_site_name;
	$data['code_name'] = $sp_data->s_site_id;
	$data['latitude'] = $sp_data->s_latitude;
	$data['longitude'] = $sp_data->s_longitude;
	$data['class_name'] = @$sp_data->class_id;
	$data['date_waiting'] = @strtotime(@$sp_data->date_waiting);
	$data['date_onprogress'] = @strtotime(@$sp_data->date_onprogress);
	$data['date_checkin'] = @strtotime(@$sp_data->date_checkin);
	$data['unique_id'] = $sp_data->unique_id;

	if ($data['status']=='CHECK_IN'){
		$imageController = new ImageController;
		$data['image_status'] = $imageController->getListStatusImage0($sp_data->sp_id);
	}else{
		$data['image_status'] =false;
	}

	if ($sp_data->submission == null) {
		$data['submission_status'] = 'NOT_FOUND';
		$data['cancel_id'] = '';
		$data['message_id'] = '';
		$data['subject'] = '';
		$data['text_message'] = '';
		$data['cancel_date'] = '';
		$data['available_status'] = '';
		$data['time'] = '';
		$data['image'] = '';
		$data['cancel_reason'] = '';
		$data['cancel_category'] = '';
	}else{

		$message_data = DB::table('message as m')
		->select('*')
		->where('m.id',$sp_data->message_id)
		->first();
		$mbp_trouble_data = DB::table('mbp_trouble as mtr')
		->select('*')
		->where('mtr.id',$sp_data->submission_id)
		->first();
		if ($mbp_trouble_data) {
		if ($mbp_trouble_data->request_to_unavailable==1) {
			$available_status = 'UNAVAILABLE';
		}else if ($mbp_trouble_data->request_to_unavailable==0) {
			$available_status = 'AVAILABLE';
		}else{
			$available_status = $sp_data->m_status;
		}
		}

		$data['status'] = $sp_data->submission;
		$data['submission_status'] = 'FOUND';
		$data['cancel_id'] = $sp_data->submission_id;
		$data['message_id'] = $sp_data->message_id;
		$data['subject'] = $message_data->subject;
		$data['text_message'] = $message_data->text_message;
		$data['cancel_date'] = $message_data->date_message;
		$data['available_status'] = $available_status;
		$data['image'] = @$mbp_trouble_data->cancel_image;
		$data['cancel_reason'] = @$mbp_trouble_data->desc;
		$data['cancel_category'] = @$mbp_trouble_data->cancel_category;
		// $data['available_status'] = $sp_data->m_status;
		// $data['time'] =$this->setDatedMYHis( $sp_data->mbp_active_at);
		$data['time'] =$this->setDatedMYHis($mbp_trouble_data->mbp_active_at);
		
		
	}
	$data['status'] = str_replace("_", " ", $data['status']);
	$data['status_BE'] = str_replace("_", " ", $data['status_BE']);
	}

	$data['status'] = str_replace("_", " ", $data['status']);

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
}

public function getStatusMbpNew(Request $request){
	$mbp_id = $request->input('mbp_id');
	return $this->getStatusMbp2($mbp_id);
}

public function updateStatusMbpNew(Request $request){

	//TERNYATA CUMA DIPAKAI UNTUK PERRUBAHAN STATUS DARI WAITING KE ON PROGRESS
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$mbp_id = $request->input('mbp_id');
	$status = $request->input('status');

	if ($status!="AVAILABLE") $status = str_replace(" ", "_", $status);

	$SP = DB::table('supplying_power')->where('mbp_id', $mbp_id)
		->where('finish','=', null)
		->orderBy('sp_id','desc')
		->first();

	//validasi terhadap ada atau tidaknya tiket yg aktif
	if(empty($SP)){
		$res['success'] = false;
		$res['message'] = 'NOT_ACTIVE_TICKET_FOUND';
		return response($res);
	}

	$MBP = DB::table('mbp')->where('mbp_id', $mbp_id)->first();
	if(empty($MBP)){
		$res['success'] = false;
		$res['message'] = 'MBP_NOT_FOUND';
		return response($res);
	}

	$update_sp_data = [];
	$log_description = '';

	switch($status){

		case 'ON_PROGRESS':

			if($MBP->status!='WAITING'){
				$res['success'] = false;
				$res['message'] = 'REQUEST_DENIED, CURENT STATUS : '.$MBP->status;
				return response($res);
			}

			$update_sp_data = [
				'date_onprogress' => $date_now,
				'last_update' => $date_now,
				'finish' => null,
				'date_finish' => null,
				'is_sync' => '0',
			];
			break;

		case 'CHECK_IN':

			$res['success'] = false;
			$res['message'] = 'REQUEST_DENIED, FUNCTION IS DEPRECATED : '.$MBP->status;
			return response($res);

			if($MBP->status!='ON_PROGRESS'){
				$res['success'] = false;
				$res['message'] = 'REQUEST_DENIED, CURENT STATUS : '.$MBP->status;
				return response($res);
			}
			
			$update_sp_data = [
				'date_checkin' => $date_now,
				'last_update' => $date_now,
				'finish' => null,
				'date_finish' => null,
				'is_sync' => '0',
			];
			$log_description = @$SP->user_mbp_cn.' telah sampai di site tujuan';
			
			break;

		case 'AVAILABLE':

			$res['success'] = false;
			$res['message'] = 'REQUEST_DENIED, FUNCTION IS DEPRECATED : '.$MBP->status;
			return response($res);

			if($MBP->status!='CHECK_IN'){
				$res['success'] = false;
				$res['message'] = 'REQUEST_DENIED, CURENT STATUS : '.$MBP->status;
				return response($res);
			}

			$update_sp_data = [
				'last_update' => $date_now,
				'date_finish' => $date_now,
				'finish' => 'DONE',
				'detail_finish' => '1',
				'is_sync' => '0',
			];
			$update_site_data = [
				'is_allocated'=>'0'
			];
			DB::table('site')->where('site_id', $SP->site_id)->update($update_site_data);

			$log_description = @$SP->user_mbp_cn.' menyelesaikan tugasnya';

			break;

		default:
			$res['success'] = false;
			$res['message'] = 'INVALID_PARAMETER';
			return response($res);
			break;
	}

	if(!empty($update_sp_data)) DB::table('supplying_power')->where('sp_id', $SP->sp_id)->update($update_sp_data);
	$update_mbp_data = [
		'status'=>$status,
	];
	DB::table('mbp')->where('mbp_id', $SP->mbp_id)->update($update_mbp_data);

	$supplyingPowerController = new SupplyingPowerController;
	// {$sp_id, $user_nik, $user_cn, $status, $description,$message , $image, $date_log}
	$value_sp_log = $supplyingPowerController->saveLogSP1(
		$SP->sp_id, 
		$SP->user_mbp, 
		$SP->user_mbp_cn, 
		$status,
		$log_description,
		 '', 
		 '', 
		 $date_now
	);

	$notificationController = new NotificationController; 
	$tmp = $notificationController->setNotification0(
		'MBP_STATUS_TO_SITE',
		$MBP->mbp_name,
		$SP->site_name,
		$mbp_id,
		$status,
		$SP->rtpo_id
	);

	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	return response($res);

	// return $this->getStatusMbp2($mbp_id);
}

public function updateStatusMbpNewDeprecated(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$mbp_id = $request->input('mbp_id');
	$status = $request->input('status');

	// if($mbp_id=='DMY05301'){
	// 	return $this->updateStatusMbpNewClone($request);
	// }

	if ($status!="AVAILABLE") {
	$status = str_replace(" ", "_", $status);
	}


	$edit_sp_mbp = DB::table('supplying_power as sp')
	->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
	//->where('sp.finish','=', NULL)
	->where('m.status','!=' ,$status);

	if ($status=='ON_PROGRESS') {
	$edit_sp_mbp = $edit_sp_mbp
	->where('m.mbp_id','=', $mbp_id)
	->where('sp.date_finish','>',$date_now)
	->update(
		[
		'm.status' => $status,
		'sp.date_onprogress' => $date_now,
		'sp.last_update' => $date_now,
		'sp.is_sync' => '0',
		'sp.date_finish' => null,
		'sp.finish' => null,
		]
	);
	}else if ($status=='CHECK_IN') {
	$edit_sp_mbp = $edit_sp_mbp
	->where('m.mbp_id','=', $mbp_id)
	->where('sp.finish','=', NULL)
	->update(
		[
		'm.status' => $status,
		'sp.date_checkin' => $date_now,
		'sp.last_update' => $date_now,
		'sp.is_sync' => '0',
		'sp.date_finish' => null,
		'sp.finish' => null,
		]
	);
	}else if ($status=='AVAILABLE'){
	$edit_sp_mbp = $edit_sp_mbp
	->join('site as s', 'sp.site_id', 's.site_id')
	->where('m.mbp_id','=', $mbp_id)
	->where('sp.finish','=', NULL)
	->update(
		[
		'm.status' => $status,
		'sp.date_finish' => $date_now,
		'sp.last_update' => $date_now,
		'sp.is_sync' => '0',
		
		'sp.finish' =>'DONE',
		'sp.detail_finish'=>'1',
		's.is_allocated' =>'0',
		]
	);
	}else{
	$res['success'] = false;
	$res['message'] = 'STATUS_NOT_MATCH';
	return response($res);
	}

	/*
	if (!$edit_sp_mbp) {
	$res['success'] = false;
	$res['message'] = 'UPDATE_FAILED';
	return response($res);
	}
	*/

	$sp_m_s_data = DB::table('supplying_power as sp')
	->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
	->join('site as s', 'sp.site_id', 's.site_id')
	->select('*', 'sp.user_mbp as driver_mbp', 'sp.user_mbp_cn as driver_mbp_cn')
	->where('m.mbp_id', $mbp_id)
	->orderBy('sp.sp_id', 'desc')
	->first();

	if ($status=='ON_PROGRESS') {
	$desc = @$sp_m_s_data->driver_mbp_cn.' menerima tiket yang telah diberikan';
	}else if ($status=='CHECK_IN') {
	$desc = @$sp_m_s_data->driver_mbp_cn.' telah sampai di site tujuan';
	}else if ($status=='AVAILABLE') {
	$desc = @$sp_m_s_data->driver_mbp_cn.' menyelesaikan tugasnya';
	}

	$supplyingPowerController = new SupplyingPowerController;
	$value_sp_log = $supplyingPowerController->saveLogSP1($sp_m_s_data->sp_id, $sp_m_s_data->driver_mbp, $sp_m_s_data->driver_mbp_cn, $status,$desc, '', '', $date_now);


	$notificationController = new NotificationController; 
	$tmp = $notificationController->setNotification0('MBP_STATUS_TO_SITE',$sp_m_s_data->mbp_name,$sp_m_s_data->site_name,$mbp_id,$status,$sp_m_s_data->rtpo_id);

	return $this->getStatusMbp2($mbp_id);
}

public function getMyMbpFMCPaginate(Request $request){
	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');
	$date_new_count = date('Y-m-d');

	// $delete_date_strtotime = strtotime($date_now." -1 day");
	// $delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);

	$username = $request->input('username');
	$page = $request->input('page');
	$search = $request->input('search');

	$limit = 20;
	$offset = ($page-1)*$limit;

	$data_user_mbp = DB::table('user_mbp')
	->select('*')
	->where('username',$username)
	->first();

	$mbp_id = $data_user_mbp->mbp_id;

	$data_mbp = DB::table('mbp')
	->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
	->join('users', 'user_mbp.username', 'users.username')
	->join('mbp_status', 'mbp.status', 'mbp_status.status')
	->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
	->where('mbp.mbp_id','=',$mbp_id)
	->whereraw('(mbp.mbp_id like "%'.$search.'%" or mbp.mbp_name like "%'.$search.'%")')
	->offset($offset)
	->limit($limit)
	->get();
	

	$mbp_result = json_decode($data_mbp, true);

	if (!$mbp_result) {
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $mbp_result;
	return response($res);
	}

	$rc = new RecommendationController;
	foreach ($mbp_result as $param => $row) {

	$data[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
	$data[$param]['bobot'] = $mbp_result[$param]['bobot'];
	$data[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
	$data[$param]['rtpo_id_home'] = $mbp_result[$param]['rtpo_id_home'];
	$data[$param]['cluster_id'] = $mbp_result[$param]['cluster_id'];
	$data[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
	$data[$param]['regional'] = $mbp_result[$param]['regional'];
	$data[$param]['status'] = $mbp_result[$param]['status'];
	$data[$param]['submission'] = $mbp_result[$param]['submission'];
	$data[$param]['submission_id'] = $mbp_result[$param]['submission_id'];
	$data[$param]['message_id'] = $mbp_result[$param]['message_id'];
	$data[$param]['active_at'] = $mbp_result[$param]['active_at'];
	$data[$param]['latitude'] = $mbp_result[$param]['latitude'];
	$data[$param]['longitude'] = $mbp_result[$param]['longitude'];
	$data[$param]['fmc'] = $mbp_result[$param]['fmc'];
	$data[$param]['active'] = $mbp_result[$param]['active'];
	$data[$param]['last_update'] = $mbp_result[$param]['last_update'];
	$data[$param]['user_id'] = $mbp_result[$param]['user_id'];
	$data[$param]['operator_name'] = $mbp_result[$param]['operator_name'];

	$get_sp = DB::table('supplying_power')
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->where('date_finish','!=',null)
	->where('date_waiting','>',$date_new_count);

	$task_count = $get_sp->count();

	$sp_done = $get_sp->select('date_finish')
	->orderBy('date_finish', 'desc')
	->first();

	$is_resting = 0;
	if ($sp_done!=null) {

		$date1=strtotime($date_now);
		$date2=strtotime($sp_done->date_finish);

		if (round(($date1-$date2) / 3600) < 1) {
		$is_resting = 1;
		}

	}

	$get_sp_active = DB::table('supplying_power as sp')
	->select('s.latitude as s_lat','s.longitude as s_lon', 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
	->Join('site as s', 'sp.site_id', 's.site_id')
	->where('finish','=',null)
	->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
	->first();

	$data[$param]['site_latitude'] = @$get_sp_active->s_lat;
	$data[$param]['site_longitude'] = @$get_sp_active->s_lon;
	$data[$param]['site_id'] = @$get_sp_active->site_id;
	$data[$param]['site_name'] = @$get_sp_active->site_name;
	$data[$param]['mbp_latitude'] = @$mbp_result[$param]['m_lat'];
	$data[$param]['mbp_longitude'] = @$mbp_result[$param]['m_lon'];
	$time_req = null;
	$waktu_tempuh = null;
	if ($get_sp_active!=null) {
		$get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
		$data[$param]['distance'] = @number_format($get_distance,1).' km';

		if ($mbp_result[$param]['status']=='ON_PROGRESS') {
		$time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
		$datetime2 = new DateTime($get_sp_active->date_onprogress);
		$datetime3 = new DateTime($date_now);
		$waktu_jalan = $datetime2->diff($datetime3);
		$hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
		$minutes = sprintf("%02d", $waktu_jalan->format('%i'));

		$time_req = $hours .':'.$minutes;
		}elseif ($mbp_result[$param]['status']=='CHECK_IN') {

		$datetime1 = new DateTime($get_sp_active->date_onprogress);
		$datetime2 = new DateTime($get_sp_active->date_checkin);
		$datetime3 = new DateTime($date_now);
		$difference = $datetime1->diff($datetime2);
		$running_bc = $datetime2->diff($datetime3);

		$hours   = sprintf("%02d", $difference->format('%H')); 
		$minutes = sprintf("%02d", $difference->format('%i'));
		$second = sprintf("%02d", $difference->format('%s'));

		$hours_bc = sprintf("%02d", $running_bc->format('%H')); 
		$minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
		$running_backup = $hours_bc .':'.$minutes_bc;
		$waktu_tempuh = $hours .':'.$minutes;
		$data[$param]['distance'] = @number_format($get_distance,1).' km ';

		$time_req = $running_backup;
		}

		if ( $data[$param]['submission']=='DELAY') {
		$data[$param]['status'] = 'DELAY';
		$to_time = strtotime($data[$param]['active_at']);
		$from_time = strtotime($date_now);
		$minutes = round(abs($to_time - $from_time) / 60);
		$delay_time = @$minutes;
		}

	}else {
		$data[$param]['distance'] = '-';
	}
	
	$data[$param]['traveling_time'] = @$waktu_tempuh;
	$data[$param]['time'] = @$time_req;
	$data[$param]['delay_time'] = @$delay_time;
	$data[$param]['task_count'] = $task_count;
	$data[$param]['is resting'] = $is_resting;

	$mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
	$bobot[$param] = $mbp_result[$param]['bobot'];

	}

	//array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);


	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);

}

function bulan_indo_tiga_char($param=1)
{
	$bulan = [
	'',
	'Jan',
	'Feb',
	'Mar',
	'Apr',
	'Mei',
	'Jun',
	'Jul',
	'Agu',
	'Sep',
	'Okt',
	'Nov',
	'Des',
	];
	return @$bulan[(int)$param];
}

function tanggal_bulan_tahun_indo_tiga_char($param)
{
	$param2 = explode(' ', $param);
	list($jam,$menit) = explode(':', $param2[1]);
	list($y,$m,$d) = explode('-', $param2[0]);
	return $d.' '.$this->bulan_indo_tiga_char($m).' '.$y.', '.$jam.':'.$menit;
}

}