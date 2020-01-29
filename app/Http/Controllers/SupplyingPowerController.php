<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
use DateTime;
class SupplyingPowerController extends Controller
{
		/**
		 * Get user by id
		 *
		 * URL /user/{id}
		 */
	public function getDataSP(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		
		$finish_status = $request->input('finish_status');

		if ($finish_status==1) {
		$SP_data = DB::table('supplying_power')
		->select('*')
		->where('finish','!=',null)
		->get();
		}elseif ($finish_status==0) {
		$SP_data = DB::table('supplying_power')
		->select('*')
		->where('finish','=',null)
		->get();
		}else {
		$res['success'] = false;
		$res['message'] = 'FAILED_STATUS_FINISH_NOT_VALID';
		return response($res);
		}

		

		$SP_result = json_decode($SP_data, true);

		foreach ($SP_result as $param => $row) {
		$data[$param]['sp_id'] = $row['sp_id'];
		$data[$param]['user_id'] = $row['user_id'];
		$data[$param]['user_rtpo'] = $row['user_rtpo'];
		$data[$param]['rtpo_id'] = $row['rtpo_id'];
		$data[$param]['rtpo_name'] = $row['rtpo_name'];
		$data[$param]['rtpo_id_home'] = $row['rtpo_id_home'];
		$data[$param]['rtpo_name_home'] = $row['rtpo_name_home'];

		$data[$param]['regional'] = $row['regional'];
		$data[$param]['cluster'] = $row['cluster'];
		$data[$param]['cluster_id'] = $row['cluster_id'];
		$data[$param]['cluster_fmc_id'] = $row['cluster_fmc_id'];
		$data[$param]['cluster_fmc'] = $row['cluster_fmc'];
		$data[$param]['ns_id'] = $row['ns_id'];
		$data[$param]['ns'] = $row['ns'];
		$data[$param]['branch_id'] = $row['branch_id'];
		$data[$param]['branch'] = $row['branch'];
		$data[$param]['user_rtpo_cn'] = $row['user_rtpo_cn'];
		$data[$param]['mbp_id'] = $row['mbp_id'];

		$data[$param]['user_mbp'] = $row['user_mbp'];
		$data[$param]['user_mbp_cn'] = $row['user_mbp_cn'];
		$data[$param]['site_id'] = $row['site_id'];
		$data[$param]['site_name'] = @$row['site_name'];

		$data[$param]['tec_opr_id'] = $row['tec_opr_id'];
		$data[$param]['wil_opr_id'] = $row['wil_opr_id'];
		$data[$param]['date_mainsfail'] = $row['date_mainsfail'];
		$data[$param]['date_waiting'] = $row['date_waiting'];
		$data[$param]['date_onprogress'] = $row['date_onprogress'];
		$data[$param]['date_checkin'] = $row['date_checkin'];
		$data[$param]['kwh_meter_before'] = $row['kwh_meter_before'];

		$data[$param]['kwh_meter_before_image'] = $row['kwh_meter_before_image'];
		$data[$param]['kwh_meter_after'] = $row['kwh_meter_after'];
		$data[$param]['kwh_meter_after_image'] = $row['kwh_meter_after_image'];
		$data[$param]['running_hour_before'] = $row['running_hour_before'];
		$data[$param]['running_hour_before_image'] = $row['running_hour_before_image'];
		$data[$param]['running_hour_after'] = $row['running_hour_after'];
		$data[$param]['running_hour_after_image'] = $row['running_hour_after_image'];
		$data[$param]['date_finish'] = $row['date_finish'];
		$data[$param]['finish'] = $row['finish'];
		$data[$param]['detail_finish'] = $row['detail_finish'];


		$data[$param]['last_update'] = $row['last_update'];
		$data[$param]['is_sync'] = $row['is_sync'];

		$log_SP_data = DB::table('supplying_power_log')
		->select('*')
		->where('sp_id','=',$row['sp_id'])
		->get();

		$data[$param]['supplying_power_log'] = $log_SP_data;
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['finish_status'] = $finish_status;
		$res['data'] = $data;
		return response($res);
	}

		public function saveLogSP(Request $request){
		
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");

		$sp_id = $request->input('sp_id');
		$user_nik = $request->input('user_nik');
		$user_cn = $request->input('user_cn');
		$status = $request->input('status');
		$description = $request->input('description');
		$image = $request->input('image'); // bila ada
		$date_log = $date_now;

		$data['sp_id'] = $sp_id;
		$data['user_nik'] = $user_nik;
		$data['user_cn'] = $user_cn;
		$data['status'] = $status;
		$data['description'] = $description;
		$data['image'] = $image;
		$data['date_log'] = $date_log;

		// $value_log = $this->saveLogSP1($sp_id, $user_nik, $user_cn, $status, $description, $image, $date_log);
		
		$insertLogSP = DB::table('supplying_power_log')
		->insert(
			[
			'sp_id' => $data['sp_id'],
			'user_nik' => $data['user_nik'],
			'user_cn' => $data['user_cn'],
			'status' => $data['status'],
			'description' => $data['description'],
			'image' => $data['image'],
			'date_log' => $data['date_log'],
			]
		);

		if (!$insertLogSP) {
			$res['success'] = false;
			$res['message'] = 'FAILED_INSERT_DATA_LOG_SP';
			return response($res);
		}

		// if (!$value_log) {
		//   $res['success'] = false;
		//   $res['message'] = 'FAILED_INSERT_DATA_LOG_SP';
		//   return response($res);
		// }

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
		}


		public function saveLogSP1($sp_id, $user_nik, $user_cn, $status, $description,$message , $image, $date_log){

		$data['sp_id'] = $sp_id;
		$data['user_nik'] = $user_nik;
		$data['user_cn'] = $user_cn;
		$data['status'] = $status;
		$data['description'] = $description;
		$data['message'] = $message;
		$data['image'] = $image;
		$data['date_log'] = $date_log;

		$insertLogSP = DB::table('supplying_power_log')
		->insert(
			[
			'sp_id' => $data['sp_id'],
			'user_nik' => $data['user_nik'],
			'user_cn' => $data['user_cn'],
			'status' => $data['status'],
			'description' => $data['description'],
			'message' => $data['message'],
			'image' => $data['image'],
			'date_log' => $data['date_log'],
			]
		);

		if (!$insertLogSP) {
			return false;
		}

		return true;
		}

		public function getListHistorySupplyingPowerRtpo(Request $request){

		$rtpo_id = $request->input('rtpo_id');
		date_default_timezone_set("Asia/Jakarta");

		$btss = DB::table('supplying_power as sp')
		->join('users as u', 'sp.user_id', '=', 'u.id')
		->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
		->join('site as s', 'sp.site_id', '=', 's.site_id')
		->select('sp.*', 'm.mbp_name', 's.site_name', 'u.name')
		->where('sp.rtpo_id','=',$rtpo_id)
		->where('sp.finish','!=',NULL)
		->orderBy('sp.sp_id', 'desc')
		->limit(25)
		->get();

		$result = json_decode($btss, true);
		if ($result==NULL) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
		}

		foreach ($result as $param => $row) {

			$newDate = $this->setDatedMYHis($row['date_waiting'].'');
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['name'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].''; //-------- G ADA
			$data[$param]['site_name']    = $row['site_name'].''; //-------- G ADA
			$data[$param]['code_name']    = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
		}

		if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
		}

		}

		public function getListHistorySupplyingPower(Request $request){

		$user_id = $request->input('user_id');
		date_default_timezone_set("Asia/Jakarta");

		// cari suertype
		$check_type = DB::table('users')
		->select('*')
		->where('id','=',$user_id)
		->first();


		if($check_type->user_type=='RTPO'){

			$check_rtpo = DB::table('user_rtpo')
			->select('*')
			->where('username','=',$check_type->username)
			->first();

			$btss = DB::table('supplying_power')
			->join('users', 'supplying_power.user_id', '=', 'users.id')
			->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
			->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
			->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
			->join('site', 'supplying_power.site_id', '=', 'site.site_id')
			->select('supplying_power.sp_id',
					'users.name as person_in_charge',
					'mbp.mbp_name', 
					'site.site_name',
					'site.site_id',
					'supplying_power.date_waiting',
					'supplying_power.finish')
			->where('supplying_power.rtpo_id','=',$check_rtpo->rtpo_id)
			->where('supplying_power.finish','!=',NULL)
			->orderBy('supplying_power.sp_id', 'desc')
			->limit(25)
			->get();

			$result = json_decode($btss, true);
			if ($result==NULL) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
			}

			foreach ($result as $param => $row) {

			$newDate = $this->setDatedMYHis($row['date_waiting'].'');
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['person_in_charge'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].'';
			$data[$param]['site_name']    = $row['site_name'].'';
			$data[$param]['code_name']    = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
			}

			if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
			}else{
			$polys['success'] = false;
			$polys['message'] = 'Cannot find polys!';

			return response($btss);
			}

		}else if($check_type->user_type=='MBP'){

			$check_mbp = DB::table('user_mbp')
			->select('*')
			->where('username','=',$check_type->username)
			->get(); 

			$btss = null;

			$mbp_result = json_decode($check_mbp, true);

			if ($mbp_result==null) {

			$res['success'] = false;
			$res['message'] = 'USER_MBP_NOT_FOUND';
			return response($res);
			}

			foreach ($mbp_result as $param => $row) {
			if($btss!=NULL){

				$btss = DB::table('supplying_power')
				->join('users', 'supplying_power.user_id', '=', 'users.id')
				->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
				->join('site', 'supplying_power.site_id', '=', 'site.site_id')
				->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.date_waiting','supplying_power.finish')
				->where('supplying_power.mbp_id','=',$mbp_result[$param]['mbp_id'])
				->where('supplying_power.finish','!=',NULL)
				->orderBy('supplying_power.sp_id', 'desc')
				->limit(15)
				->get();

				$tmp = json_decode($btss, true);

				$resultSP = array_merge($resultSP ,$tmp);
			}else{
				$btss = DB::table('supplying_power')
				->join('users', 'supplying_power.user_id', '=', 'users.id')
				->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
				->join('site', 'supplying_power.site_id', '=', 'site.site_id')
				->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.date_waiting','supplying_power.finish')
				->where('mbp.mbp_id','=',$mbp_result[$param]['mbp_id'])
				->where('supplying_power.finish','!=',NULL)
				->orderBy('supplying_power.sp_id', 'desc')
				->limit(15)
				->get();  

				$resultSP = json_decode($btss, true);
			}
			}


			$result = $resultSP;
			if ($result==null) {

			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
			}
			// $result = json_decode($btss, true);

			foreach ($result as $param => $row) {

			// $newDate = date("d-M-Y", strtotime($row['date_waiting'].''));
			$newDate = $this->setDatedMYHis($row['date_waiting'].'');

			$id[$param]        = $row['sp_id'];
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['person_in_charge'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].'';
			$data[$param]['site_name']    = $row['site_name'].'';
			$data[$param]['code_name']    = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
			}

			array_multisort($id, SORT_DESC, $data);

			if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
			}else{
			$polys['success'] = false;
			$polys['message'] = 'Cannot find polys!';

			return response($btss);
			}

		}else{

			$res['success'] = false;
			$res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
			
			return response($res);
		}
		}

		public function getDetailHistorySupplyingPower(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$sp_id = $request->input('sp_id');

		$btss = DB::table('supplying_power')
		->join('users', 'supplying_power.user_id', '=', 'users.id')
		->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
		->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')
		->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
		// // ->join('users', 'user_mbp.user_id', '=', 'users.id') DATE_FORMAT(NAME_COLUMN, "%d/%l/%Y %H:%i:%s") AS 'NAME'
		// 'DATE_FORMAT(supplying_power.date_checkin, %d/%M/%Y %H:%i:%s) AS date_checkin'
		->select('supplying_power.sp_id','users.name as rtpo_name','mbp.mbp_name', 'site.site_name'/*,'supplying_power.date_mainsfail'*/,'site.site_id','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','supplying_power.finish','supplying_power.cancel_reason','supplying_power.reason_by','supplying_power.cancel_approved_by','supplying_power.unique_id')
		// ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','supplying_power.date_waiting','supplying_power.finish')
		->where('supplying_power.sp_id','=',$sp_id)
		->where('supplying_power.finish','!=',NULL)
		->first();

		if ($btss) {

			// $date=date_create("2013-03-15");
			// $data = date_format($date,"d-M-Y H:i:s");

			$data['sp_id'] = $btss->sp_id.'';
			$data['rtpo_name'] = $btss->rtpo_name.'';
			$data['mbp_name'] = $btss->mbp_name.'';
			$data['site_name'] = $btss->site_name.'';
			$data['code_name'] = $btss->site_id.'';
			$data['send_date'] = $this->setDatedMYHis($btss->date_waiting.'');
			$data['date_waiting'] = $this->setDatedMYHis($btss->date_waiting.'');
			$data['date_onprogress'] = $this->setDatedMYHis($btss->date_onprogress.'');
			$data['date_checkin'] = $this->setDatedMYHis($btss->date_checkin.'')/*date("d-M-Y H:i:s", strtotime($btss->date_checkin.''))*/;
			$data['date_finish'] = $this->setDatedMYHis($btss->date_finish.'');
			$data['finish'] = $btss->finish.'';
			$data['unique_id'] = $btss->unique_id.'';
			
			$datetime1 = new DateTime($btss->date_waiting);
			$datetime2 = new DateTime($btss->date_onprogress);
			$datetime3 = new DateTime($btss->date_checkin);
			$datetime4 = new DateTime($btss->date_finish);

			$response_time_tmp = $datetime1->diff($datetime2);
			$time_to_site_tmp = $datetime2->diff($datetime3);
			$backup_time_tmp = $datetime3->diff($datetime4);

			
			$response_time_hours   = sprintf("%02d", $response_time_tmp->format('%H'));; 
			$response_time_minutes = sprintf("%02d", $response_time_tmp->format('%i'));;
			$response_time = $response_time_hours .':'.$response_time_minutes;
			
			$time_to_site_hours   = sprintf("%02d", $time_to_site_tmp->format('%H'));; 
			$time_to_site_minutes = sprintf("%02d", $time_to_site_tmp->format('%i'));;
			$time_to_site = $time_to_site_hours .':'.$time_to_site_minutes;

			$backup_time_hours   = sprintf("%02d", $backup_time_tmp->format('%H'));; 
			$backup_time_minutes = sprintf("%02d", $backup_time_tmp->format('%i'));;
			$backup_time = $backup_time_hours .':'.$backup_time_minutes;

			$data['response_time'] = $response_time.' ('.$this->setDateHi($btss->date_waiting.'').' - '.$this->setDateHi($btss->date_onprogress.'').')';
			$data['time_to_site'] = $time_to_site.' ('.$this->setDateHi($btss->date_onprogress.'').' - '.$this->setDateHi($btss->date_checkin.'').')';
			$data['backup_time'] = $backup_time.' ('.$this->setDateHi($btss->date_checkin.'').' - '.$this->setDateHi($btss->date_finish.'').')';

				$reason_c = null;
				$sendby_c = null;
				$date_c = null;

			if ($btss->finish=='CANCEL' || $btss->finish=='TIDAK DIKERJAKAN' || $btss->finish=='AUTO CLOSE') {

				if ($data['date_onprogress']=='-') {
				$data['response_time'] = '-';
				}

				if ($data['date_checkin']=='-') {
				$data['time_to_site'] = '-';
				$data['backup_time'] = '-';
				}
				$cancel_detil = DB::table('supplying_power_log')
				->join('users', 'supplying_power_log.user_nik', 'users.id')
				->join('mbp_trouble', 'supplying_power_log.sp_id', 'mbp_trouble.sp_id')
				->leftjoin('users as ua', 'mbp_trouble.respon_by_nik', 'ua.id')
				->select('users.*', 'supplying_power_log.*', 'ua.name as rtpo_name', 'mbp_trouble.respon_date', 'mbp_trouble.send_date')
				->where('supplying_power_log.sp_id','=',$data['sp_id'])
				->where('supplying_power_log.message','!=',"")
				->orderBy('date_log', 'desc')
				->first();
				if (@$cancel_detil->user_type == 'RTPO') {
				$is_rtpo = 'RTPO';
				}else{
				$is_rtpo = 'FMC';
				}
				

				
				if (@$cancel_detil->name==null) {
				$sendby_c  ='-';
				}else{
				$sendby_c  = @$cancel_detil->name.' ('.$is_rtpo.')';
				}

				
				if (@$this->setDatedMYHis($cancel_detil->respon_date.'')==null) {
				$approve_date_c  ='-';
				}else{
				$approve_date_c  = @$this->setDatedMYHis($cancel_detil->respon_date.'');
				}

				
				if (@$cancel_detil->message==null) {
				$reason_c = '-';
				}else{
				$reason_c = @$cancel_detil->message;
				}


				if (@$cancel_detil->rtpo_name==null) {
				$approved_by_c = '-';
				}else{
				$approved_by_c = @$cancel_detil->rtpo_name;
				}
				
				
				if (@$this->setDatedMYHis($cancel_detil->send_date.'')==null) {
				$date_c = '-';
				}else{
				$date_c = @$this->setDatedMYHis($cancel_detil->send_date.'');
				}

				if ($is_rtpo == 'RTPO') {
				$approve_date_c = '-';
				if (@$this->setDatedMYHis($cancel_detil->date_log.'')==null) {
					$date_c = '-';
				}else{
					$date_c = @$this->setDatedMYHis($cancel_detil->date_log.'');
				}
				}

			}


			$detil_pembatalan = DB::table('supplying_power')
			->join('users as u1', 'supplying_power.reason_by', 'u1.username')
			->join('users as u2', 'supplying_power.cancel_approved_by', 'u2.username')
			->select('u1.name as cancel_sendby','u2.name as approved_by')
			->where('supplying_power.sp_id','=',$data['sp_id'])
			->first();

			$cancel_sendby = @$detil_pembatalan->cancel_sendby;
			$approved_by = @$detil_pembatalan->approved_by;
			if (@$cancel_sendby==null) {

				$cancel_sendby = 'RTPO';
				$approved_by = 'RTPO';              

				if (@$approve_date_c == '-') {
				$approved_by = '';
				}
			}

			$cek_reason_by_system = DB::table('supplying_power')
			->select('reason_by','cancel_approved_by','cancel_reason','cancel_image','cancel_category')
			->where('supplying_power.sp_id','=',$data['sp_id'])
			->first();
			if (@$cek_reason_by_system->reason_by=="system") {
				$cancel_sendby=@$cek_reason_by_system->reason_by;
				$approved_by=@$cek_reason_by_system->reason_by;
			}

			$data['cancel_reason'] = (@$btss->cancel_reason==null) ? '-' : @$btss->cancel_reason;
			// $data['cancel_sendby'] = @$detil_pembatalan->cancel_sendby;
			// $data['approved_by'] = @$detil_pembatalan->approved_by;
			$data['cancel_sendby'] = @$cancel_sendby;
			$data['approved_by'] = @$approved_by;
			// $data['approved_by'] = @$cancel_detil->user_cn;
			$data['cancel_date'] = @$date_c;
			$data['approve_date'] = @$approve_date_c;

			// $data['desc'] = @$cek_reason_by_system->cancel_reason;
			$data['cancel_image'] = (@$cek_reason_by_system->cancel_image==null) ? '-' : @$cek_reason_by_system->cancel_image;
			$data['cancel_category'] = (@$cek_reason_by_system->cancel_category==null) ? '-' : @$cek_reason_by_system->cancel_category;


			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			// $res['data'] = $btss;
			$res['data'] = $data;

			return response($res);
		}else{
			$polys['success'] = false;
			$polys['message'] = 'CANNOT_FIND_DATA';
			$polys['data'] = $btss;

			return response($polys);
		}
		}
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
		public function setDateHi($date){
		if ($date==null) {
			return "-";
		}else if ($date=='0000-00-00 00:00:00') {
			return "-";
		}else{
			// return date("d-M-Y H:i:s", strtotime($date.''));
			return date("H:i", strtotime($date.''));
			// return strtotime($date.'');
		}
		}


		public function setEvidenceNumber(Request $request){

		$mbp_id = $request->input('mbp_id');
		$value = $request->input('value');
		$photo_type = $request->input('photo_type');

		if ($photo_type == 1) {
			# code...
			$insertSP = DB::table('supplying_power')
			->where('mbp_id', $mbp_id)
			->where('finish', NULL)
			->update(
			[
				'kwh_mete_before' => $value,
			]
			);
		}else if ($photo_type == 2) {
			# code...
			$insertSP = DB::table('supplying_power')
			->where('mbp_id', $mbp_id)
			->where('finish', NULL)
			->update(
			[
				'kwh_mete_after' => $value,
			]
			);
		}else if ($photo_type == 3) {
			# code...
			$insertSP = DB::table('supplying_power')
			->where('mbp_id', $mbp_id)
			->where('finish', NULL)
			->update(
			[
				'running_hour_before' => $value,
			]
			);
		}else if ($photo_type == 4) {
			# code...
			$insertSP = DB::table('supplying_power')
			->where('mbp_id', $mbp_id)
			->where('finish', NULL)
			->update(
			[
				'running_hour_after' => $value,
			]
			);
		}
		}

		public function UpdateSyncSP(Request $request){

		$SP_data = $request->input('data');

		foreach ($SP_data as $param => $row) {

			if (array_key_exists('log', $row)) {

			$log_data = $row['log'];
			unset($row['log']);

			foreach ($log_data as $param => $log_row) {
				$update_SP_data = DB::table('supplying_power_log')
				->where('sp_log_id','=',$log_row['sp_log_id'])
				->update($log_row);
			}
			}

			$update_SP_data = DB::table('supplying_power')
			->where('sp_id','=',$row['sp_id'])
			->update($row);
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
		}

		public function getDataSPIsSync0(Request $request){
		//api untuk menyediakan data sp yg belum diambil oleh server internal
		//updated by agus barizi 2019/10/01

		date_default_timezone_set("Asia/Jakarta");
		
		// $end_status = $request->input('end_status');
		
		$SP_data = DB::table('supplying_power')
		->select('*')
		->where('is_sync','=',0)
		->where('detail_finish','!=',null)
		// ->where('mbp_id','like',"IDE04809%")
		->limit(50)
		->orderBy('date_waiting', 'desc')
		->get();

		$SP_result = json_decode($SP_data, true);

		if ($SP_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $SP_data;
			return response($res);
		}

		foreach ($SP_result as $param => $row) {

			$data[$param] = $row;
			$log_SP_data = DB::table('supplying_power_log')
			->select('*')
			->where('sp_id','=',$row['sp_id'])
			->get();

			$data[$param]['log'] = $log_SP_data;
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
		}

	public function updateDataSpAdn(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		
		$date_now = date('Y-m-d H:i:s');
		$sp_id = $request->input('sp_id');
		$mbp_id = $request->input('mbp_id');
		$status = $request->input('status');

		$before_after = @$request->input('before_after');
		$kwh = @$request->input('kwh');
		$rh = @$request->input('rh');

		if(empty($sp_id) || empty($status)){
			$res['success'] = false;
			$res['message'] = 'INVALID_PARAMETER';
			return response($res);
		}

		$SP = DB::table('supplying_power')->where('sp_id', $sp_id)->first();
		if(empty($SP)){
			$res['success'] = false;
			$res['message'] = 'DATA_NOT_FOUND';
			return response($res);
		}

		$MBP = DB::table('mbp')->where('mbp_id', $SP->mbp_id)->first();
		if(empty($MBP)){
			$res['success'] = false;
			$res['message'] = 'DATA_NOT_FOUND';
			return response($res);
		}

		$update_sp_data = [];
		$update_site_data = [];
		$update_mbp_data = [];
		$log_description = '';

		switch($status){
			case 'ON_PROGRESS':

				if($MBP->status=='ON_PROGRESS'){
					$res['success'] = true;
					$res['message'] = 'Success';
					return response($res);
				}elseif($MBP->status=='WAITING'){
					
					$log_status = 'ON_PROGRESS';
					$update_mbp_data['status'] = $status;
					$update_sp_data = [
						'date_onprogress' => $date_now,
						'last_update' => $date_now,
						'finish' => null,
						'date_finish' => null,
						'is_sync' => '0',
					];
					$log_description = @$SP->user_mbp_cn.' telah menerima tiket [OFFLINE MODE]';

				}else{
					$res['success'] = false;
					$res['message'] = 'REQUEST_DENIED, current status : '.$MBP->status;
					return response($res);
				}

				break;
			case 'CHECK_IN':

				if($MBP->status=='CHECK_IN'){
					$res['success'] = true;
					$res['message'] = 'Success';
					return response($res);
				}elseif($MBP->status=='ON_PROGRESS'){
					
					$log_status = 'CHECK_IN';
					$update_mbp_data['status'] = $status;
					$update_sp_data = [
						'kwh_meter_before' => $kwh,
						'running_hour_before' => $rh,
						'date_checkin' => $date_now,
						'last_update' => $date_now,
						'finish' => null,
						'date_finish' => null,
						'is_sync' => '0',
					];
					$log_description = @$SP->user_mbp_cn.' telah sampai di site tujuan [OFFLINE MODE]';

				}else{
					$res['success'] = false;
					$res['message'] = 'REQUEST_DENIED, current status : '.$MBP->status;
					return response($res);
				}

				break;
			case 'AVAILABLE':

				if($MBP->status=='CHECK_IN'){
					
					$log_status = 'CHECK_OUT';
					$update_mbp_data['status'] = $status;
					$update_sp_data = [
						'kwh_meter_after' => $kwh,
						'running_hour_after' => $rh,
						'date_finish' => $date_now,
						'finish' => 'DONE',
						'detail_finish' => '1',
						'is_sync' => '0',
					];
					$update_site_data = ['is_allocated'=>'0'];
					$log_description = @$SP->user_mbp_cn.' telah menyelesaikan tugasnya [OFFLINE MODE]';
				
				}else{
					$res['success'] = false;
					$res['message'] = 'REQUEST_DENIED, current status : '.$MBP->status;
					return response($res);
				}

				break;
			default: 
				$res['success'] = false;
				$res['message'] = 'UNDEFINED_STATUS';
				return response($res);
				break;
		}
		if(!empty($update_sp_data)) DB::table('supplying_power')->where('sp_id', $SP->sp_id)->update($update_sp_data);
		if(!empty($update_mbp_data)) DB::table('mbp')->where('mbp_id', $SP->mbp_id)->update($update_mbp_data);
		if(!empty($update_site_data)) DB::table('site')->where('site_id', $SP->site_id)->update($update_site_data);


		$supplyingPowerController = new SupplyingPowerController;
		// {$sp_id, $user_nik, $user_cn, $status, $description,$message , $image, $date_log}
		$value_sp_log = $supplyingPowerController->saveLogSP1($SP->sp_id, $SP->user_mbp, $SP->user_mbp_cn, $log_status, 
			$log_description, '', '', $date_now
		);

		$notificationController = new NotificationController; 
		$tmp = $notificationController->setNotification0(
			'MBP_STATUS_TO_SITE', $MBP->mbp_name, $SP->site_name,
			$mbp_id, $status, $SP->rtpo_id
		);

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	// fungsi untuk close tiket yang sudah tiga hari g close juga

	public function closeSPTicketAfter3Day(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
		$delete_date_strtotime = strtotime($date_now." -3 day");
		$delete_date_fix = date('Y-m-d',$delete_date_strtotime);

		$SP_data = DB::table('supplying_power')
		->select('*')
		->where('finish','=',null)
		->orderBy('date_waiting', 'asc')
		->get();

		$x=0;
		foreach ($SP_data as $value) {
		$data[$x]['sp_id']=$value->sp_id;
		$data[$x]['date_waiting']=$value->date_waiting;
		$data[$x]['by']=$value->user_rtpo_cn;
		$x=$x+1;


		$mbp_data = DB::table('supplying_power')                        
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')      
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')  
		->where('supplying_power.mbp_id','=',$value->mbp_id)
		->where('supplying_power.date_waiting','<',$delete_date_fix)
		->where('supplying_power.finish','=',null)
		->update(
			[
			'supplying_power.finish' =>'CANCEL',
			'supplying_power.date_finish' =>date('Y-m-d H:i:s'),
			'supplying_power.detail_finish' => '4',
			'mbp.status' =>'AVAILABLE',
			'mbp.submission' =>null,
			'mbp.submission_id' =>null,
			'mbp.active_at' =>null,
			'mbp.message_id' =>null,
			'site.is_allocated' =>'0',
			'supplying_power.is_sync' =>'0',


			'supplying_power.cancel_reason' =>"tiket tidak di selesaikan melebihi 3 hari",
			'supplying_power.reason_by' => "system",
			'supplying_power.cancel_approved_by' => "system",
			]
		);


		$getCancellationLetter = DB::table('mbp_trouble')
		->select('*')
		->where('is_active','=',1)
		->where('mbp_id','=',$value->mbp_id)
		->where('send_date','<',$delete_date_fix)
		->first();


		if ($getCancellationLetter!=null) {
			$updateCancellationLetter = DB::table('mbp_trouble')
			->where('is_active','=',1)
			->where('mbp_id','=',$value->mbp_id)
			->update(
			[
				'is_active' =>'0',
				'respon_date' =>date('Y-m-d H:i:s'),
			]
			);
		}

		if ($value->date_waiting<$delete_date_fix) {
			$supplyingPowerController = new SupplyingPowerController;
			$value_sp_log = $supplyingPowerController->saveLogSP1($value->sp_id, "system", "system", 'CANCEL','system '."system".' dibatalkan dengan alasan tiket tidak di selesaikan melebihi 3 hari' ,'tiket tidak di selesaikan melebihi 3 hari', '', $date_now);
		}

		}

		$res['data'] = @$data;
		return response($res);
	}


		public function getListHistorySupplyingPowerCPO(Request $request){

		$regional = $request->input('regional');
		date_default_timezone_set("Asia/Jakarta");

		$btss = DB::table('supplying_power as sp')
		->join('users as u', 'sp.user_id', '=', 'u.id')
		->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
		->join('site as s', 'sp.site_id', '=', 's.site_id')
		->select('sp.*', 'm.mbp_name', 's.site_name', 'u.name')
		->where('sp.regional','=',$regional)
		->where('sp.finish','!=',NULL)
		->orderBy('sp.sp_id', 'desc')
		->limit(50)
		->get();

		$result = json_decode($btss, true);
		if ($result==NULL) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
		}

		foreach ($result as $param => $row) {

			$newDate = $this->setDatedMYHis($row['date_waiting'].'');
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['name'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].''; //-------- G ADA
			$data[$param]['site_name']    = $row['site_name'].''; //-------- G ADA
			$data[$param]['code_name']    = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
		}

		if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
		}

		}

	public function getListHistorySupplyingPowerNS(Request $request){

		$ns_id = $request->input('ns_id');
		date_default_timezone_set("Asia/Jakarta");

		$btss = DB::table('supplying_power as sp')
		->join('users as u', 'sp.user_id', '=', 'u.id')
		->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
		->join('site as s', 'sp.site_id', '=', 's.site_id')
		->select('sp.*', 'm.mbp_name', 's.site_name', 'u.name')
		->where('sp.ns_id','=',$ns_id)
		->where('sp.finish','!=',NULL)
		->orderBy('sp.sp_id', 'desc')
		->limit(50)
		->get();

		$result = json_decode($btss, true);
		if ($result==NULL) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
		}

		foreach ($result as $param => $row) {

			$newDate = $this->setDatedMYHis($row['date_waiting'].'');
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['name'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].''; //-------- G ADA
			$data[$param]['site_name']    = $row['site_name'].''; //-------- G ADA
			$data[$param]['code_name']    = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
		}

		if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
		}

		}


	public function check15mntResponMbp(){


		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
		$createNotif = strtotime($date_now."-15 minutes");
		$createNotif_fix = date('Y-m-d H:i:s',$createNotif);


		$getDataNotif = strtotime($date_now."-1500 minutes");
		$getDataNotif_fix = date('Y-m-d H:i:s',$getDataNotif);


		$app_mbp_data = DB::table('supplying_power')
		->select('*')
		->where('date_onprogress','=', null)
		->where('date_waiting','>', $getDataNotif_fix)
		->get();

		$x=0;
		foreach ($app_mbp_data as $value) {

		$sp_id = $value->sp_id;
		$sp_date = $value->date_waiting;

		if ($sp_date <= $createNotif_fix) {
			
			$check_queue = DB::table('queue_telegram')
			->select('*')
			->where('ticket_id', $sp_id)
			->first();

			if (@$check_queue->ticket_id!=$sp_id) {
			//=====================================

			$admin_fmc_data = DB::table('users')
			->select('*')
			->where('fmc_id','=',$value->fmc_id)
			->where('cluster','=',$value->cluster)
			->where('chat_id','!=',null)
			->where('chat_id','!=',"")
			->get();

			foreach ($admin_fmc_data as $param) {

			$subject_telegram = 'sendTicketMBP15Mnts';
			// $title = strip_tags("<b><i> TIKET CORRECTIVE CLUSTER ".@$data['cluster']." </i></b>","<b>");
			$text_telegram = '[ TIKET MBP CLUSTER '.@$value->cluster.' BELUM DIRESPON ]

	Dari : '.@$value->user_rtpo_cn.'
	RTPO : '.@$value->rtpo_name.'
	Cluster : '.@$value->cluster.'
	Menugaskan : '.@$value->user_mbp_cn.'
	ID MBP : '.@$value->mbp_id.'
	Menuju ID Site : '.@$value->site_id.'
	Pada tanggal : '.@$value->date_waiting.'

	Dihimbau untuk mengingatkan tim MBP dengan nama '.@$value->user_mbp_cn.', terimakasih.';

	$date_now = date('Y-m-d H:i:s');
			$inserQueueTelegram = DB::table('queue_telegram')   
			->insert(
				[
				'subject' => 'sendTicketMBP15Mnts',
				'message' => @$text_telegram,
				'ticket_id' => @$sp_id,
				'chat_id' => @$param->chat_id,

				'send_to' => @$param->username,
				'fmc_id' => @$param->fmc_id,
				'cluster_id' => @$param->cluster_id,
				'rtpo_id' => @$param->rtpo_id,

				'create_at' => @$date_now,

				]
			);
			}
			//======================================
			}

		}
		}
		return "selesai";
	}

	public function getListFinishedSP(Request $request)
	{
		$rtpo_id = $request->input('rtpo_id');

		$data_sp = DB::table('supplying_power')
		->join('users', 'supplying_power.user_id', '=', 'users.id')
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')
		->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_id','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.finish','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish')
		->where('supplying_power.rtpo_id',$rtpo_id)
		->where('supplying_power.finish',"DONE")
		->where('supplying_power.approved_by_rtpo',0)
		->orderBy('supplying_power.sp_id', 'desc')
		->limit(25)
		->get();

		if ($data_sp) {
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data_sp;
		} else{
		$res['success'] = false;
		$res['message'] = 'Server Error';
		$res['data'] = $data_sp;
		}
		return response($res);
	}

	public function getDetailFinishedSP(Request $request)
	{
		$sp_id = $request->input('sp_id');

		$data_sp = DB::table('supplying_power')
		->join('users', 'supplying_power.user_id', '=', 'users.id')
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')
		->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_id','mbp.mbp_name','site.site_id', 'site.site_name','supplying_power.finish','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','kwh_meter_before','kwh_meter_after','running_hour_before','running_hour_after','running_hour_before_image', 'running_hour_after_image')
		->where('supplying_power.sp_id',$sp_id)
		->first();

		if ($data_sp) {

		$datetime1 = new DateTime($data_sp->date_waiting);
		$datetime2 = new DateTime($data_sp->date_onprogress);
		$datetime3 = new DateTime($data_sp->date_checkin);
		$datetime4 = new DateTime($data_sp->date_finish);

		$response_time_tmp = $datetime1->diff($datetime2);
		$time_to_site_tmp = $datetime2->diff($datetime3);
		$backup_time_tmp = $datetime3->diff($datetime4);

		$response_time_hours   = sprintf("%02d", $response_time_tmp->format('%H'));; 
		$response_time_minutes = sprintf("%02d", $response_time_tmp->format('%i'));;
		$response_time = $response_time_hours .':'.$response_time_minutes;
		
		$time_to_site_hours   = sprintf("%02d", $time_to_site_tmp->format('%H'));; 
		$time_to_site_minutes = sprintf("%02d", $time_to_site_tmp->format('%i'));;
		$time_to_site = $time_to_site_hours .':'.$time_to_site_minutes;

		$backup_time_hours   = sprintf("%02d", $backup_time_tmp->format('%H'));; 
		$backup_time_minutes = sprintf("%02d", $backup_time_tmp->format('%i'));;
		$backup_time = $backup_time_hours .':'.$backup_time_minutes;

		$data['sp_id'] = $data_sp->sp_id;
		$data['person_in_charge'] = $data_sp->person_in_charge;
		$data['mbp_id'] = $data_sp->mbp_id;
		$data['mbp_name']  = $data_sp->mbp_name;
		$data['site_id'] = $data_sp->site_id;
		$data['site_name'] = $data_sp->site_name;
		$data['finish'] = $data_sp->finish;
		$data['date_waiting'] = $data_sp->date_waiting;
		$data['date_onprogress'] = $data_sp->date_onprogress;
		$data['date_checkin'] = $data_sp->date_checkin;
		$data['date_finish'] = $data_sp->date_finish;
		$data['kwh_meter_before'] = ($data_sp->kwh_meter_before==null) ? "" : $data_sp->kwh_meter_before;
		$data['kwh_meter_after'] = ($data_sp->kwh_meter_after==null) ? "" : $data_sp->kwh_meter_after;
		$data['running_hour_before'] = ($data_sp->running_hour_before==null) ? "" : $data_sp->running_hour_before;
		$data['running_hour_after'] = ($data_sp->running_hour_after==null) ? "" : $data_sp->running_hour_after;

		$data['response_time'] = $response_time.' ('.$this->setDateHi($data_sp->date_waiting.'').' - '.$this->setDateHi($data_sp->date_onprogress.'').')';
		$data['time_to_site'] = $time_to_site.' ('.$this->setDateHi($data_sp->date_onprogress.'').' - '.$this->setDateHi($data_sp->date_checkin.'').')';
		$data['backup_time'] = $backup_time.' ('.$this->setDateHi($data_sp->date_checkin.'').' - '.$this->setDateHi($data_sp->date_finish.'').')';

		$data['send_date'] = $this->setDatedMYHis($data_sp->date_waiting);

		$url = 'http://103.253.107.45/semeru-api/upload_image/php/images/';
		if ($data_sp->running_hour_before_image) {
			$data['rh_before_image'] = $url.$data_sp->running_hour_before_image;
		} else{
			$data['rh_before_image'] = "";
		}
		if ($data_sp->running_hour_after_image) {
			$data['rh_after_image'] = $url.$data_sp->running_hour_after_image;
		} else{
			$data['rh_after_image'] = "";
		}
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		} else{
		$res['success'] = false;
		$res['message'] = 'Server Error';
		$res['data'] = $data_sp;
		}
		return response($res);
	}

	public function approveFinishedSP(Request $request)
	{
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");

		$sp_id = $request->input('sp_id');
		$username = $request->input('username');
		$rh_before = $request->input('running_hour_before');
		$rh_after = $request->input('running_hour_after');

		$sp = DB::table('supplying_power')
		->select('*')
		->where('sp_id',$sp_id)
		->first();
		
		$data_sp = DB::table('supplying_power')
		->where('sp_id',$sp_id)
		->update([
		'running_hour_before' => $rh_before,
		'running_hour_after' => $rh_after,
		'approved_by_rtpo' => 1,
		]);

		$desc='';
		$update_desc='';
		if($sp->running_hour_before!=$rh_before){
		$update_desc.='nilai RH Before dari '.$sp->running_hour_before.' menjadi '.$rh_before;
		}
		if($sp->running_hour_after!=$rh_after){
		if(!empty($update_desc)) $update_desc.=' dan ';
		$update_desc.='nilai RH After dari '.$sp->running_hour_after.' menjadi '.$rh_after;
		}
		if(!empty($update_desc)){
		$desc=$username.' telah melakukan verifikasi dengan mengubah '.$update_desc;
		}else{
		$desc=$username.' telah melakukan verifikasi nilai Running Hour';
		}

		$value_sp_log = $this->saveLogSP1($sp_id, '', '', 'APPROVED', $desc,'', '', $date_now);
		

		$res['success'] = true;
		$res['message'] = 'SUCCESS';

		return response($res);
	}

	//get list tiket MBP
	public function getListNotApprovedSP(Request $request)
	{
		date_default_timezone_set("Asia/Jakarta");

		$date_now = date('Y-m-d H:i:s');
		$date_strtotime = strtotime($date_now." -30 minutes");
		$date2 = date('Y-m-d H:i:s',$date_strtotime);

		$rtpo_id = $request->input('rtpo_id');

		$page = $request->input('page');

		$limit = 20;
		$offset = ($page-1)*$limit;

		#bug
		$data_sp_autoclose = DB::table('supplying_power')
		->select('*')
		->where('finish','AUTO CLOSE')
		->where('date_finish','<',$date_now)
		->where('detail_finish',null)
		->get();

		foreach ($data_sp_autoclose as $param => $row) {
			$update_mbp = DB::table('mbp')
						->where('mbp_id',$row->mbp_id)
						->update([
							'status' => 'AVAILABLE',
						]);

			$update_site = DB::table('site')
						->where('site_id',$row->site_id)
						->update([
							'is_allocated' => 0,
						]);

			$update_sp = DB::table('supplying_power')
						->where('sp_id',$row->sp_id)
						->update([
							'last_update' => $date_now,
							'is_sync' => '0',
							
							'detail_finish'=>'5',
						]);

			$value_sp_log = $this->saveLogSP1($row->sp_id, 'system', 'system', 'AUTO_CLOSE', 'Auto close tiket oleh sistem karena tidak diterima dalam waktu 30 menit', '', '', $row->date_finish);
		}

		$data_sp = DB::table('supplying_power')
		->join('users', 'supplying_power.user_id', '=', 'users.id')
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')
		->select(
			'supplying_power.sp_id',
			'users.name as person_in_charge',
			'mbp.mbp_id',
			'mbp.mbp_name', 
			'mbp.status',
			'mbp.submission',
			'site.site_name',
			'site.site_id',
			'supplying_power.finish',
			'supplying_power.date_waiting',
			'supplying_power.date_onprogress',
			'supplying_power.date_checkin',
			'supplying_power.date_finish',
			'supplying_power.unique_id')
		->whereraw('(supplying_power.date_finish >"'.$date_now.'" or supplying_power.date_finish is null)')
		//->where('supplying_power.detail_finish',null)
		->where('supplying_power.rtpo_id',$rtpo_id)
		//->where('supplying_power.finish',null)
		//->where('supplying_power.date_waiting','>',$date2)
		->where('supplying_power.approved_by_rtpo',0)
		->orderBy('supplying_power.sp_id', 'desc')  
		->offset($offset)
		->limit($limit)
		->get();

		foreach ($data_sp as $key => $value) {
			$value->date_waiting 	= ($value->date_waiting==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_waiting);
			$value->date_onprogress = ($value->date_onprogress==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_onprogress);
			$value->date_checkin 	= ($value->date_checkin==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_checkin);
			$value->date_finish 	= ($value->date_finish==NULL)? '-' : $this->tanggal_bulan_tahun_indo_tiga_char($value->date_finish);

			$value->status = str_replace("_", " ", $value->status);

			if ($value->submission=='DELAY') {
				$value->status = 'DELAY';
			}
		}

		if ($data_sp) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data_sp;
		} else{
			$res['success'] = false;
			$res['message'] = 'Server Error';
			$res['data'] = $data_sp;
		}
		return response($res);
	}

	public function getDetailNotApprovedSP(Request $request)
	{
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
		$time_2hour = strtotime($date_now." -2 hours");
		$date_2hour = date('Y-m-d H:i:s',$time_2hour);

		$sp_id = $request->input('sp_id');

		$data_sp = DB::table('supplying_power')
		->join('users', 'supplying_power.user_id', '=', 'users.id')
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')
		->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_id','mbp.mbp_name','site.site_id', 'site.site_name','supplying_power.finish','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','kwh_meter_before','kwh_meter_after','running_hour_before','running_hour_after','running_hour_before_image', 'running_hour_after_image','supplying_power.unique_id')
		->where('supplying_power.sp_id',$sp_id)
		->first();

		if ($data_sp) {
		if (@$data_sp->date_onprogress==null || @$data_sp->date_onprogress>$date_2hour){
			$data['flag_2hour'] = 0;
		} else{
			$data['flag_2hour'] = 1;
		}

		$datetime1 = new DateTime($data_sp->date_waiting);
		$datetime2 = new DateTime($data_sp->date_onprogress);
		$datetime3 = new DateTime($data_sp->date_checkin);
		$datetime4 = new DateTime($data_sp->date_finish);

		$response_time_tmp = $datetime1->diff($datetime2);
		$time_to_site_tmp = $datetime2->diff($datetime3);
		$backup_time_tmp = $datetime3->diff($datetime4);

		$response_time_hours   = ($data_sp->date_onprogress==null || $data_sp->date_onprogress==null) ? 0 : sprintf("%01d", $response_time_tmp->format('%H'));; 
		$response_time_minutes = ($data_sp->date_onprogress==null || $data_sp->date_onprogress==null) ? 0 : sprintf("%01d", $response_time_tmp->format('%i'));;
		$response_time = $response_time_hours .' jam '.$response_time_minutes.' Menit';
		
		$time_to_site_hours   = ($data_sp->date_onprogress==null || $data_sp->date_checkin==null) ? 0 : sprintf("%01d", $time_to_site_tmp->format('%H'));; 
		$time_to_site_minutes = ($data_sp->date_onprogress==null || $data_sp->date_checkin==null) ? 0 : sprintf("%01d", $time_to_site_tmp->format('%i'));;
		$time_to_site = $time_to_site_hours .' jam '.$time_to_site_minutes.' Menit';

		$backup_time_hours   = ($data_sp->date_checkin==null || $data_sp->date_finish==null) ? 0 : sprintf("%01d", $backup_time_tmp->format('%H'));; 
		$backup_time_minutes = ($data_sp->date_checkin==null || $data_sp->date_finish==null) ? 0 : sprintf("%01d", $backup_time_tmp->format('%i'));;
		$backup_time = $backup_time_hours .' jam '.$backup_time_minutes.' Menit';

		$data['sp_id'] = $data_sp->sp_id;
		$data['person_in_charge'] = $data_sp->person_in_charge;
		$data['mbp_id'] = $data_sp->mbp_id;
		$data['mbp_name']  = $data_sp->mbp_name;
		$data['site_id'] = $data_sp->site_id;
		$data['site_name'] = $data_sp->site_name;
		$data['finish'] = $data_sp->finish;
		$data['date_waiting'] = ($data_sp->date_waiting==null) ? "-" : substr($this->tanggal_bulan_tahun_indo_tiga_char($data_sp->date_waiting),-5);
		$data['date_onprogress'] = ($data_sp->date_onprogress==null) ? "-" : substr($this->tanggal_bulan_tahun_indo_tiga_char($data_sp->date_onprogress),-5);
		$data['date_checkin'] = ($data_sp->date_checkin==null) ? "-" : substr($this->tanggal_bulan_tahun_indo_tiga_char($data_sp->date_checkin),-5);
		$data['date_finish'] = ($data_sp->date_finish==null) ? "-" : substr($this->tanggal_bulan_tahun_indo_tiga_char($data_sp->date_finish),-5);
		$data['kwh_meter_before'] = ($data_sp->kwh_meter_before==null) ? "-" : $data_sp->kwh_meter_before;
		$data['kwh_meter_after'] = ($data_sp->kwh_meter_after==null) ? "-" : $data_sp->kwh_meter_after;
		$data['running_hour_before'] = ($data_sp->running_hour_before==null) ? "-" : $data_sp->running_hour_before;
		$data['running_hour_after'] = ($data_sp->running_hour_after==null) ? "-" : $data_sp->running_hour_after;

		$data['response_time'] = $response_time.' ('.$this->setDateHi($data_sp->date_waiting.'').' - '.$this->setDateHi($data_sp->date_onprogress.'').')';
		$data['time_to_site'] = $time_to_site.' ('.$this->setDateHi($data_sp->date_onprogress.'').' - '.$this->setDateHi($data_sp->date_checkin.'').')';
		$data['backup_time'] = $backup_time.' ('.$this->setDateHi($data_sp->date_checkin.'').' - '.$this->setDateHi($data_sp->date_finish.'').')';

		$data['send_date'] = $this->setDatedMYHis($data_sp->date_waiting);
		$data['unique_id'] = $data_sp->unique_id;

		$url = 'http://103.253.107.45/semeru-api/upload_image/php/images/';
		if ($data_sp->running_hour_before_image) {
			$data['rh_before_image'] = $url.$data_sp->running_hour_before_image;
		} else{
			$data['rh_before_image'] = "-";
		}
		if ($data_sp->running_hour_after_image) {
			$data['rh_after_image'] = $url.$data_sp->running_hour_after_image;
		} else{
			$data['rh_after_image'] = "-";
		}

		$data_mbp = DB::table('mbp')
		->select('mbp_id','mbp_name','status','submission')
		->where('mbp_id',$data_sp->mbp_id)
		->first();

		$data['status'] = str_replace("_", " ", $data_mbp->status);

		if ($data_mbp->submission=='DELAY') {
			$data['status'] = 'DELAY';
		}
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		} else{
		$res['success'] = false;
		$res['message'] = 'Server Error';
		$res['data'] = $data_sp;
		}
		return response($res);
	}

	public function getListHistorySupplyingPowerPaginate(Request $request){

		$user_id = $request->input('user_id');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$page = $request->input('page');
		$search = $request->input('search');

		$limit = 20;
		$offset = ($page-1)*$limit;

		// cari suertype
		$check_type = DB::table('users')
		->select('*')
		->where('id','=',$user_id)
		->first();


		if($check_type->user_type=='RTPO'){

			$check_rtpo = DB::table('user_rtpo')
			->select('*')
			->where('username','=',$check_type->username)
			->first();

			// $btss = DB::table('supplying_power')
			// ->join('users', 'supplying_power.user_id', '=', 'users.id')
			// ->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
			// ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
			// ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
			// ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
			// ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.date_waiting','supplying_power.finish','supplying_power.unique_id')
			// ->where('supplying_power.rtpo_id','=',$check_rtpo->rtpo_id)
			// //->where('supplying_power.finish','!=',NULL)
			// ->where('supplying_power.date_finish','<',$date_now)
			// ->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
			// ->offset($offset)
			// ->limit($limit)
			// ->orderBy('supplying_power.sp_id', 'desc')
			// ->get();
			
			$btss = DB::table('supplying_power')
			->select(
				'supplying_power.sp_id', 
				'supplying_power.unique_id', 
				'supplying_power.mbp_id', 
				'mbp.mbp_name', 
				'supplying_power.site_id', 
				'supplying_power.site_name', 
				'supplying_power.finish', 
				'supplying_power.date_waiting' 
				)
				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
				->whereraw('(supplying_power.site_id like "%'.$search.'%" or supplying_power.site_name like "%'.$search.'%")')
				->where('supplying_power.rtpo_id', $check_rtpo->rtpo_id)
				->where('supplying_power.date_finish','<',$date_now)
				->offset($offset)
				->limit($limit)
				->orderBy('supplying_power.sp_id', 'desc')
				->get();


			//
			
			

			$result = json_decode($btss, true);
			if ($result==NULL) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
			}

			foreach ($result as $param => $row) {

			$newDate = $this->setDatedMYHis($row['date_waiting'].'');
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			// $data[$param]['rtpo_name']    = $row['person_in_charge'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].'';
			$data[$param]['site_name']    = $row['site_name'].'';
			$data[$param]['site_id']      = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
			$data[$param]['unique_id']    = $row['unique_id'].'';
			}

			if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;
			$res['date'] = $date_now;

			return response($res);
			}else{
			$polys['success'] = false;
			$polys['message'] = 'Cannot find polys!';

			return response($btss);
			}

		}elseif($check_type->user_type=='MBP'){

			$check_mbp = DB::table('user_mbp')
			->select('*')
			->where('username','=',$check_type->username)
			->get(); 

			$btss = null;

			$mbp_result = json_decode($check_mbp, true);

			if ($mbp_result==null) {

			$res['success'] = false;
			$res['message'] = 'USER_MBP_NOT_FOUND';
			return response($res);
			}

			foreach ($mbp_result as $param => $row) {
			if($btss!=NULL){

				$btss = DB::table('supplying_power')
				->join('users', 'supplying_power.user_id', '=', 'users.id')
				->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
				->join('site', 'supplying_power.site_id', '=', 'site.site_id')
				->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.date_waiting','supplying_power.finish','supplying_power.unique_id')
				->where('supplying_power.mbp_id','=',$mbp_result[$param]['mbp_id'])
				//->where('supplying_power.detail_finish','!=',NULL)
				->where('supplying_power.date_finish','<',$date_now)
				->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
				->orderBy('supplying_power.sp_id', 'desc')
				->offset($offset)
				->limit($limit)
				->get();

				$tmp = json_decode($btss, true);

				$resultSP = array_merge($resultSP ,$tmp);
			}else{
				$btss = DB::table('supplying_power')
				->join('users', 'supplying_power.user_id', '=', 'users.id')
				->join('user_rtpo', 'users.username', '=', 'user_rtpo.username')
				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
				->join('site', 'supplying_power.site_id', '=', 'site.site_id')
				->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','site.site_id','supplying_power.date_waiting','supplying_power.finish','supplying_power.unique_id')
				->where('mbp.mbp_id','=',$mbp_result[$param]['mbp_id'])
				->where('supplying_power.finish','!=',NULL)
				->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
				->orderBy('supplying_power.sp_id', 'desc')
				->offset($offset)
				->limit($limit)
				->get();  

				$resultSP = json_decode($btss, true);
			}
			}


			$result = $resultSP;
			if ($result==null) {

			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
			}
			// $result = json_decode($btss, true);

			foreach ($result as $param => $row) {

			// $newDate = date("d-M-Y", strtotime($row['date_waiting'].''));
			$newDate = $this->setDatedMYHis($row['date_waiting'].'');

			$id[$param]        = $row['sp_id'];
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['person_in_charge'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].'';
			$data[$param]['site_name']    = $row['site_name'].'';
			$data[$param]['site_id']      = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
			$data[$param]['unique_id']    = $row['unique_id'].'';
			}

			array_multisort($id, SORT_DESC, $data);

			if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
			}else{
			$polys['success'] = false;
			$polys['message'] = 'Server Error!';

			return response($btss);
			}

		}else{

			$res['success'] = false;
			$res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
			
			return response($res);
		}
		}

	public function getListHistorySupplyingPowerNSPaginate(Request $request){

		$ns_id = $request->input('ns_id');
		date_default_timezone_set("Asia/Jakarta");

		$page = $request->input('page');
		$search = $request->input('search');

		$limit = 20;
		$offset = ($page-1)*$limit;

		$btss = DB::table('supplying_power as sp')
		->join('users as u', 'sp.user_id', '=', 'u.id')
		->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
		->join('site as s', 'sp.site_id', '=', 's.site_id')
		->select('sp.*', 'm.mbp_name', 's.site_name', 'u.name')
		->where('sp.ns_id','=',$ns_id)
		->where('sp.finish','!=',NULL)
		->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
		->offset($offset)
		->limit($limit)
		->orderBy('sp.sp_id', 'desc')
		->get();

		$result = json_decode($btss, true);
		if ($result==NULL) {
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $btss;
		return response($res);
		}

		foreach ($result as $param => $row) {

		$newDate = $this->setDatedMYHis($row['date_waiting'].'');
		$data[$param]['sp_id']        = $row['sp_id'];
		$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
		$data[$param]['rtpo_name']    = $row['name'].'';
		$data[$param]['mbp_name']     = $row['mbp_name'].''; //-------- G ADA
		$data[$param]['site_name']    = $row['site_name'].''; //-------- G ADA
		$data[$param]['site_id']    = $row['site_id'].'';
		$data[$param]['date_request'] = $newDate;
		$data[$param]['finish']       = $row['finish'].'';
		}

		if ($btss) {
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;

		return response($res);
		}

	}

	public function getListHistorySupplyingPowerCPOPaginate(Request $request){

		$regional = $request->input('regional');
		date_default_timezone_set("Asia/Jakarta");

		$page = $request->input('page');
		$search = $request->input('search');

		$limit = 20;
		$offset = ($page-1)*$limit;

		$btss = DB::table('supplying_power as sp')
		->join('users as u', 'sp.user_id', '=', 'u.id')
		->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
		->join('site as s', 'sp.site_id', '=', 's.site_id')
		->select('sp.*', 'm.mbp_name', 's.site_name', 'u.name')
		->where('sp.regional','=',$regional)
		->where('sp.finish','!=',NULL)
		->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
		->offset($offset)
		->limit($limit)
		->orderBy('sp.sp_id', 'desc')
		->get();

		$result = json_decode($btss, true);
		if ($result==NULL) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $btss;
			return response($res);
		}

		foreach ($result as $param => $row) {

			$newDate = $this->setDatedMYHis($row['date_waiting'].'');
			$data[$param]['sp_id']        = $row['sp_id'];
			$data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
			$data[$param]['rtpo_name']    = $row['name'].'';
			$data[$param]['mbp_name']     = $row['mbp_name'].''; //-------- G ADA
			$data[$param]['site_name']    = $row['site_name'].''; //-------- G ADA
			$data[$param]['site_id']    = $row['site_id'].'';
			$data[$param]['date_request'] = $newDate;
			$data[$param]['finish']       = $row['finish'].'';
		}

		if ($btss) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $data;

			return response($res);
		}

		}

		public function submitValueSP(Request $request)
		{
			// $res['success'] = false;
			// $res['message'] = 'DEVELOPMENT';
	
			// return response($res);
			
			date_default_timezone_set("Asia/Jakarta");
			$date_now = date('Y-m-d H:i:s');
	

			$sp_id = $request->input('sp_id');
			$kwh_meter_before = @$request->input('kwh_meter_before');
			$kwh_meter_after = @$request->input('kwh_meter_after');
			$rh_before = @$request->input('rh_before');
			$rh_after = @$request->input('rh_after');
	
			$SP = DB::table('supplying_power')->where('sp_id',$sp_id)->first();
			if(empty($SP)){
				$res['success'] = false;
				$res['message'] = 'TICKET_NOT_FOUND';
				return response($res);
			}

			$MBP = DB::table('mbp')->where('mbp_id',$SP->mbp_id)->first();
			if(empty($MBP)){
				$res['success'] = false;
				$res['message'] = 'MBP_NOT_FOUND';
				return response($res);
			}

			$mbp_id = $SP->mbp_id;
			$site_id = $SP->site_id;
	
			// $sp_m_s_data = DB::table('supplying_power as sp')
			// 				->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
			// 				->join('site as s', 'sp.site_id', 's.site_id')
			// 				->select('*', 'sp.user_mbp as driver_mbp', 'sp.user_mbp_cn as driver_mbp_cn')
			// 				->where('m.mbp_id', $mbp_id)
			// 				->orderBy('sp.sp_id', 'desc')
			// 				->first();
	
			if (!is_null($kwh_meter_before) && !is_null($rh_before)) {
				$status = 'CHECK_IN';
				$log_status = 'CHECK_IN';

				if($MBP->status!='ON_PROGRESS'){
					$res['success'] = false;
					$res['message'] = 'REQUEST_DENIED, CURENT STATUS : '.$MBP->status;
					return response($res);
				}

				$SPvalue = DB::table('supplying_power')->where('sp_id',$sp_id)
					->update([
						'date_checkin' => $date_now,
						'last_update' => $date_now,
						'kwh_meter_before' => $kwh_meter_before,
						'running_hour_before' => $rh_before,
						'finish' => null,
						'date_finish' => null,
						'detail_finish' => null,
						'is_sync' => 0,
					]);
	
				$updateStatusMBP = DB::table('mbp')->where('mbp_id',$mbp_id)
					->update(['status' => $status,
				]);
				
				$log_description = @$SP->user_mbp_cn.' telah sampai di site tujuan';
				// $desc = @$sp_m_s_data->driver_mbp_cn.' telah sampai di site tujuan';
			
			}elseif( !is_null($kwh_meter_after) && !is_null($rh_after) ) {
				
				$status = 'AVAILABLE';
				$log_status = 'CHECK_OUT';

				if($MBP->status!='CHECK_IN'){
					$res['success'] = false;
					$res['message'] = 'REQUEST_DENIED, CURENT STATUS : '.$MBP->status;
					return response($res);
				}
			
				//CEK MEET SLA
				$datetime1 = new DateTime($SP->date_waiting);
				$datetime2 = new DateTime($SP->date_onprogress);
				$datetime3 = new DateTime($SP->date_checkin);
	
				$time_to_site = $datetime2->diff($datetime3);
	
				$second = $time_to_site->h*3600+$time_to_site->i*60+$time_to_site->s;
	
				$meet_sla = $second>7200 ? 0 : 1;
			
				$SPvalue = DB::table('supplying_power')->where('sp_id',$sp_id)
					->update([
						'date_finish' => $date_now,
						'last_update' => $date_now,
						'finish' => 'DONE',
						'detail_finish' => '1',
						'kwh_meter_after' => $kwh_meter_after,
						'running_hour_after' => $rh_after,
						'meet_sla' => $meet_sla,
						'is_sync' => 0,
					]);
	
				$updateStatusMBP = DB::table('mbp')->where('mbp_id',$mbp_id)
					->update([
						'status' => $status,
					]);
	
				$updateStatusSite = DB::table('site')->where('site_id',$site_id)
					->update([
						'is_allocated' =>'0',
					]);
				
				$log_description = @$SP->user_mbp_cn.' menyelesaikan tugasnya';
				// $desc = @$sp_m_s_data->driver_mbp_cn.' menyelesaikan tugasnya';
			}
			
			$supplyingPowerController = new SupplyingPowerController;
			$value_sp_log = $supplyingPowerController->saveLogSP1(
				$SP->sp_id, 
				$SP->user_mbp, 
				$SP->user_mbp_cn, 
				$log_status,
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
		}

	public function submitValueSPDeprecated(Request $request)
	{

		//PERLU REWORK FUNGSINYA 
		//KALAU KAYAK GINI GK JELAS ANTARA CHECKIN DAN CHECKOUT
		//SALAH PARAMETER MALAH BIKIN KACAU
		
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');



		$sp_id = $request->input('sp_id');
		$kwh_meter_before = @$request->input('kwh_meter_before');
		$kwh_meter_after = @$request->input('kwh_meter_after');
		$rh_before = @$request->input('rh_before');
		$rh_after = @$request->input('rh_after');

		if($sp_id='64852'){
			return $this->submitValueSPDev($request);
		}

		$query = DB::table('supplying_power as sp')
				->select('sp.running_hour_before')
				->where('sp.sp_id','=',$sp_id)
				->get();

		$query_results = json_decode($query, "OK");

		foreach($query_results as $p){
			$dbquery_running_hour_before = $p['running_hour_before'];
		}

		// if($dbquery_running_hour_before > $rh_after){
		// 	$res['success'] = false;
		// 	$res['message'] = 'Running Hour Sesudah Tidak Boleh Lebih Kecil Dari Sebelum';
		
		// 	return response($res);
		// }

		$data_sp = DB::table('supplying_power')
					->select('mbp_id','site_id')
					->where('sp_id',$sp_id)
					->first();

		$mbp_id = $data_sp->mbp_id;
		$site_id = $data_sp->site_id;

		$sp_m_s_data = DB::table('supplying_power as sp')
						->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
						->join('site as s', 'sp.site_id', 's.site_id')
						->select('*', 'sp.user_mbp as driver_mbp', 'sp.user_mbp_cn as driver_mbp_cn')
						->where('m.mbp_id', $mbp_id)
						->orderBy('sp.sp_id', 'desc')
						->first();

		if (!is_null($kwh_meter_before) && !is_null($rh_before)) {
			$SPvalue = DB::table('supplying_power')
						->where('sp_id',$sp_id)
						->update([
							'date_checkin' => $date_now,
							'last_update' => $date_now,
							'kwh_meter_before' => $kwh_meter_before,
							'running_hour_before' => $rh_before,
							'is_sync' => 0,
			]);

			$updateStatusMBP = DB::table('mbp')
								->where('mbp_id',$mbp_id)
								->update([
									'status' => 'CHECK_IN',
			]);
			$status = 'CHECK_IN';

			$desc = @$sp_m_s_data->driver_mbp_cn.' telah sampai di site tujuan';
		}
		elseif( !is_null($kwh_meter_after) && !is_null($rh_after) ) {
		
			$data_sp = DB::table('supplying_power')
						->select('*')
						->where('sp_id',$sp_id)
						->first();

			$datetime1 = new DateTime($data_sp->date_waiting);
			$datetime2 = new DateTime($data_sp->date_onprogress);
			$datetime3 = new DateTime($data_sp->date_checkin);

			$time_to_site = $datetime2->diff($datetime3);

			$second = $time_to_site->h*3600+$time_to_site->i*60+$time_to_site->s;

			if ($second>7200){
				$meet_sla = 0;
			}
			else{
				$meet_sla = 1;
			}
		
			$SPvalue = DB::table('supplying_power')
						->where('sp_id',$sp_id)
						->update([
							'date_finish' => $date_now,
							'last_update' => $date_now,
							'finish' => 'DONE',
							'detail_finish' => '1',
							'kwh_meter_after' => $kwh_meter_after,
							'running_hour_after' => $rh_after,
							'meet_sla' => $meet_sla,
							'is_sync' => 0,
						]);

			$updateStatusMBP = DB::table('mbp')
							->where('mbp_id',$mbp_id)
							->update(
								[
								'status' => 'AVAILABLE',
								]
							);
			$status = 'AVAILABLE';

			$updateStatusSite = DB::table('site')
			->where('site_id',$site_id)
			->update(
				[
				'is_allocated' =>'0',
				]
			);

			$desc = @$sp_m_s_data->driver_mbp_cn.' menyelesaikan tugasnya';
		}

		$value_sp_log = $this->saveLogSP1($sp_m_s_data->sp_id, $sp_m_s_data->driver_mbp, $sp_m_s_data->driver_mbp_cn, $status,$desc, '', '', $date_now);


		$notificationController = new NotificationController; 
		$tmp = $notificationController->setNotification0('MBP_STATUS_TO_SITE',$sp_m_s_data->mbp_name,$sp_m_s_data->site_name,$mbp_id,$status,$sp_m_s_data->rtpo_id);
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';

		return response($res);
	}

	public function autocloseSP(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
		$delete_date_strtotime = strtotime($date_now." -30 minutes");
		$delete_date_fix = date('Y-m-d H:i:s',$delete_date_strtotime);

		$SP_data = DB::table('supplying_power')
		->select('*')
		->where('finish','=',null)
		->where('rtpo_id',42)
		->orderBy('date_waiting', 'asc')
		->get();

		$x=0;
		foreach ($SP_data as $value) {
			$data[$x]['sp_id']=$value->sp_id;
			$data[$x]['date_waiting']=$value->date_waiting;
			$data[$x]['by']=$value->user_rtpo_cn;
			$x=$x+1;


			$mbp_data = DB::table('supplying_power')                        
			->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')      
			->join('site', 'supplying_power.site_id', '=', 'site.site_id')  
			->where('supplying_power.mbp_id','=',$value->mbp_id)
			->where('supplying_power.date_waiting','<',$delete_date_fix)
			->where('supplying_power.finish',null)
			->update(
				[
				'supplying_power.finish' =>'AUTO CLOSE',
				'supplying_power.date_finish' =>date('Y-m-d H:i:s'),
				'supplying_power.detail_finish' => '5',
				'mbp.status' =>'AVAILABLE',
				'mbp.submission' =>null,
				'mbp.submission_id' =>null,
				'mbp.active_at' =>null,
				'mbp.message_id' =>null,
				'site.is_allocated' =>'0',
				'supplying_power.is_sync' =>'0',


				'supplying_power.cancel_reason' =>"tiket tidak diterima melebihi 30 menit",
				'supplying_power.reason_by' => "system",
				'supplying_power.cancel_approved_by' => "system",
				]
			);


			$getCancellationLetter = DB::table('mbp_trouble')
			->select('*')
			->where('is_active','=',1)
			->where('mbp_id','=',$value->mbp_id)
			->where('send_date','<',$delete_date_fix)
			->first();


			if ($getCancellationLetter!=null) {
				$updateCancellationLetter = DB::table('mbp_trouble')
				->where('is_active','=',1)
				->where('mbp_id','=',$value->mbp_id)
				->update(
				[
					'is_active' =>'0',
					'respon_date' =>date('Y-m-d H:i:s'),
				]
				);
			}

			if ($value->date_waiting<$delete_date_fix) {
				$supplyingPowerController = new SupplyingPowerController;
				$value_sp_log = $supplyingPowerController->saveLogSP1($value->sp_id, "system", "system", 'CANCEL','system '."system".' dibatalkan dengan alasan tiket tidak diterima melebihi 30 menit' ,'tiket tidak diterima melebihi 30 menit', '', $date_now);
			}

		}

		$res['data'] = @$data;
		return response($res);
	}

	public function submitTiketTidakDikerjakan(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$sp_id = $request->input('sp_id');

		$data_sp = DB::table('supplying_power')
		->select('mbp_id','site_id')
		->where('sp_id',$sp_id)
		->first();

		$mbp_id = $data_sp->mbp_id;
		$site_id = $data_sp->site_id;

		$sp_m_s_data = DB::table('supplying_power as sp')
		->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
		->join('site as s', 'sp.site_id', 's.site_id')
		->select('*', 'sp.user_mbp as driver_mbp', 'sp.user_mbp_cn as driver_mbp_cn')
		->where('m.mbp_id', $mbp_id)
		->orderBy('sp.sp_id', 'desc')
		->first();

		$SPvalue = DB::table('supplying_power')
		->where('sp_id',$sp_id)
		->update([
		'date_finish' => $date_now,
		'last_update' => $date_now,
		'finish' => 'TIDAK DIKERJAKAN',
		'detail_finish' => '6',
		'meet_sla' => 0,
		'is_sync' => 0,
		]);

		$updateStatusMBP = DB::table('mbp')
		->where('mbp_id',$mbp_id)
		->update(
		[
			'status' => 'AVAILABLE',
		]
		);
		$status = 'AVAILABLE';

		$updateStatusSite = DB::table('site')
		->where('site_id',$site_id)
		->update(
		[
			'is_allocated' =>'0',
		]
		);

		$desc = @$sp_m_s_data->driver_mbp_cn.' tidak mengerjakan tugasnya';

		$value_sp_log = $this->saveLogSP1($sp_m_s_data->sp_id, $sp_m_s_data->driver_mbp, $sp_m_s_data->driver_mbp_cn, $status,$desc, '', '', $date_now);


		$notificationController = new NotificationController; 
		$tmp = $notificationController->setNotification0('MBP_STATUS_TO_SITE',$sp_m_s_data->mbp_name,$sp_m_s_data->site_name,$mbp_id,$status,$sp_m_s_data->rtpo_id);
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';

		return response($res);
	}

	public function loggingSPAutoClose(Request $request)
	{
		date_default_timezone_set("Asia/Jakarta");

		$date_now = date('Y-m-d H:i:s');
		$date_strtotime = strtotime($date_now." -30 minutes");
		$date2 = date('Y-m-d H:i:s',$date_strtotime);

		$rtpo_id = $request->input('rtpo_id');

		$page = $request->input('page');

		$limit = 20;
		$offset = ($page-1)*$limit;

		$data_sp_autoclose = DB::table('supplying_power')
		->select('*')
		->where('finish','AUTO CLOSE')
		->where('date_finish','<',$date_now)
		->get();

		foreach ($data_sp_autoclose as $param => $row) {
		$data_log_sp = DB::table('supplying_power_log')
		->select('sp_id')
		->where('sp_id',$row->sp_id)
		->where('status','AUTO_CLOSE')
		->first();

		if ($data_log_sp==null) {
			$value_sp_log = $this->saveLogSP1($row->sp_id, 'system', 'system', 'AUTO_CLOSE', 'Auto close tiket oleh sistem karena tidak diterima dalam waktu 30 menit', '', '', $row->date_finish);
		}
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';

		return response($res);
	}

	public function UpdateSyncImageSP(Request $request){

		$SP_data = $request->input('data');

		foreach ($SP_data as $param => $row) {

			$update_SP_data = DB::table('tiketing_image')
			->where('id','=',$row['id'])
			->update($row);
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
		}

	public function getImageSP(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
		$month_now = date('m');
		$year_now = date('Y');

		
		$data_image = DB::table('tiketing_image as t')
		->select('t.*','sp.id_sync','sp.site_id','sp.date_waiting')
		->join('supplying_power as sp','t.sp_id', 'sp.sp_id')
		->whereraw('month(date)>=07')
		->whereraw('year(date)='.$year_now)
		->where('t.is_sync',0)
		->limit(50)
		->get();

		/*
		$data_image = DB::table('tiketing_image as t')
		->select('t.*','sp.id_sync','sp.site_id','sp.date_waiting')
		->join('supplying_power as sp','t.sp_id', 'sp.sp_id')
		->where('t.sp_id',43071)
		->where('t.is_sync',0)
		->limit(2)
		->get();
		*/

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data_image;

		return response($res);
	}

	public function storeImage(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$data_sp = DB::table('supplying_power')
		->select('sp_id','kwh_meter_before_image','kwh_meter_after_image','running_hour_before_image','running_hour_after_image')
		->where('detail_finish',1)
		->whereraw('month(date_waiting)="09"')
		->whereraw('year(date_waiting)="2019"')
		->where('kwh_meter_before_image','!=',null)
		->orderBy('sp_id')
		->get();

		
		foreach ($data_sp as $key => $value) {
		$insert_image = DB::table('image_sp_new')
		->insert([
			'sp_id' => $value->sp_id,
			'host' => 'http://103.253.107.45/semeru-api/upload_image/php/image/',
			'fname' => $value->kwh_meter_before_image,
			'date_created' => $date_now,
			'is_sync' => 0,
		]);

		$insert_image = DB::table('image_sp_new')
		->insert([
			'sp_id' => $value->sp_id,
			'host' => 'http://103.253.107.45/semeru-api/upload_image/php/image/',
			'fname' => $value->kwh_meter_after_image,
			'date_created' => $date_now,
			'is_sync' => 0,
		]);

		$insert_image = DB::table('image_sp_new')
		->insert([
			'sp_id' => $value->sp_id,
			'host' => 'http://103.253.107.45/semeru-api/upload_image/php/image/',
			'fname' => $value->running_hour_before_image,
			'date_created' => $date_now,
			'is_sync' => 0,
		]);

		$insert_image = DB::table('image_sp_new')
		->insert([
			'sp_id' => $value->sp_id,
			'host' => 'http://103.253.107.45/semeru-api/upload_image/php/image/',
			'fname' => $value->running_hour_after_image,
			'date_created' => $date_now,
			'is_sync' => 0,
		]);
		}


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		//$res['data'] = $data_sp;

		return response($res);
	}

	public function getDataSPbySPID(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$sp_id = $request->input('sp_id');
		
		// $end_status = $request->input('end_status');
		
		
		$SP_result = DB::table('supplying_power')
		->select('*')
		->where('sp_id',$sp_id)
		->orderBy('date_waiting', 'desc')
		->first();
		
		if ($SP_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $SP_result;
			return response($res);
		}
		
		$log_SP_data = DB::table('supplying_power_log')
		->select('*')
		->where('sp_id','=',$sp_id)
		->get();

		$SP_result->log = $log_SP_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $SP_result;
		return response($res);
		}

	public function tesHitungSLA(Request $request){
		date_default_timezone_set("Asia/Jakarta");

		$date_now = date('Y-m-d H:i:s');
		$date_strtotime = strtotime($date_now." -30 minutes");
		$date2 = date('Y-m-d H:i:s',$date_strtotime);
		$month_now = date('m');
		$year_now = date('Y');

		$sp_id = @$request->input('sp_id');

		$data_sp = DB::table('supplying_power')
		->select('*')
		->where('detail_finish',1)
		->where('meet_sla',null) 
		->whereraw('year(date_waiting)='.$year_now)
		->whereraw('month(date_waiting)='.$month_now)
		->get();

		foreach ($data_sp as $param => $row) {
		$arr = $row->sp_id;

		$datetime1 = new DateTime($row->date_waiting);
		$datetime2 = new DateTime($row->date_onprogress);
		$datetime3 = new DateTime($row->date_checkin);

		$time_to_site = $datetime2->diff($datetime3);

		$second = $time_to_site->h*3600+$time_to_site->i*60+$time_to_site->s;

		if ($second>7200) {
			$meet_sla=0;
		} else{
			$meet_sla=1;
		}

		$update_meet_sla = DB::table('supplying_power')
		->where('sp_id',$row->sp_id)
		->update([
			'meet_sla' => $meet_sla,
		]);
		}

		//$data['data_sp'] = $data_sp;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		//$res['data'] = $data_sp;

		return response($res);
	}

	public function tesDelIm(Request $request){
		$tes = unlink('http://103.253.107.45/semeru-api/upload_image/php/images/SP_SBZ351_20190626_33949_20.99_AFTERRUNNINGHOUR.jpg');

		return $tes;
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