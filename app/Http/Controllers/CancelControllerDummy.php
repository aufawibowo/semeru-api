<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class CancelControllerDummy extends Controller
{
	public function sendDelayLetterToRtpo(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
	
		$rtpo_id 		= $request->input('rtpo_id');
		$user_id_mbp 	= $request->input('user_id');
		$mbp_id 		= $request->input('mbp_id');
		$text_message 	= $request->input('text_message');
		// $available_status = $request->input('available_status');
		$active_at 		= $request->input('time');
	
		$data_mbp 	= DB::table('mbp')
					->select('*')
					->where('mbp_id',$mbp_id)
					->first();
	
		$rtpo_id = $data_mbp->rtpo_id;
	
		if(empty($rtpo_id) || $rtpo_id==0) {
			$res['success'] = false;
			$res['message'] = 'EMPTY_RTPO! Silakan logout dan login kembali';
			return response($res);
		}
	
		$sp_data 	= DB::table('supplying_power as sp')
					->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
					->join('site as s', 'sp.site_id', 's.site_id')
					->select('*')
					->where('m.mbp_id', $mbp_id)
					->orderBy('sp.sp_id', 'desc')
					->first();
	
		if ($sp_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILED_GET_SPA_DATA';
			return response($res);
		}

		$insert_mbp_trouble = DB::table('mbp_trouble')
							->where('send_by_cn','=', $user_id_mbp)
							->where('is_active','=', '1')
							->delete();
	
	
		$insert_message = DB::table('message')
		->insert([
			'subject' => 'DELAY', 
			'from' => $user_id_mbp,
			'text_message' => $text_message,
			'date_message' => $date_now.'',
		]);
	
		if (!$insert_message) {
			$res['success'] = false;
			$res['message'] = 'FAILED_INSERT_MESSAGE_DATA';
			return response($res);
		}
	
		$mbp_active_at = date('Y-m-d H:i:s', strtotime($date_now.' + '.$active_at.' hours'));
		$insert_mbp_trouble = DB::table('mbp_trouble')
		->insert([
			'send_to_rtpo_id' => $rtpo_id,
			'send_to_rtpo_name' => $sp_data->rtpo_name,
			'desc' => $text_message,
			'send_by_nik' => $user_id_mbp,
			'send_by_cn' => $sp_data->user_mbp_cn,
			'type' => 'DELAY',
			'mbp_id' => $mbp_id,
			'sp_id' => $sp_data->sp_id,
			'send_date' => $date_now.'',
			'request_to_unavailable' => 0,
			'mbp_active_at' => $mbp_active_at,
			'is_active' => 1,
		]);
	
		if (!$insert_mbp_trouble) {
			$res['success'] = false;
			$res['message'] = 'FAILED_INSERT_MBP_TROUBLE';
			return response($res);
		}
	
		$after_in_data 	= DB::table('mbp_trouble as mtr')
						->join('message as msg', 'mtr.send_date', 'msg.date_message')
						->select('*', 'msg.id as msg_id', 'mtr.id as mtr_id')
						->where('mtr.send_date', $date_now)
						->first();
	
		if (!$after_in_data) {
			$res['success'] = false;
			$res['message'] = 'FAILED_INSERT_DATA';
			return response($res);
		}
	
		$editMbp = DB::table('mbp')
				->where('mbp_id', $mbp_id)
				->update([
					'submission' => 'DELAY',
					'submission_id' => $after_in_data->mtr_id,
					'active_at' => $after_in_data->mbp_active_at,
					'message_id' => $after_in_data->msg_id,
				]);
	
		$supplyingPowerController = new SupplyingPowerController;
		$value_sp_log 	= $supplyingPowerController	->saveLogSP1(
														$sp_data->sp_id, 
														$user_id_mbp, 
														$sp_data->user_mbp_cn, 
														'SUBMIT_DELAY', 
														$sp_data->user_mbp_cn.' mengirimkan pengajuan delay kepada rtpo dengan pesan sebagai berikut : '.$text_message,$text_message, 
														'', 
														$date_now
													);
	
		$notificationController = new NotificationController;
		$tmp = $notificationController	->setNotificationMbpSubmission(
											$sp_data->mbp_name,
											$sp_data->site_name,
											$rtpo_id, 
											$sp_data->message_id, 
											'DELAY'
										);
	
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function delayStatementRtpo(Request $request){
		date_default_timezone_set("Asia/Jakarta");      
		$date_now = date('Y-m-d H:i:s');

		$type_approval = $request->input('type_approval');
		$user_id = $request->input('user_id');
		$cancel_id = $request->input('cancel_id');

		$mbp_trouble 	= DB::table('mbp_trouble as mtr')
						->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
						->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
						->join('users as u', 'um.username', 'u.username')
						->join('message as msg', 'mtr.send_date', 'msg.date_message')
						->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
						->select('*','u.id as user_id','mtr.id as mtr_id','m.status as mbp_status')
						->where('mtr.id',$cancel_id)
						->first();

		if (!$mbp_trouble) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['wall'] = 'FAILED_GET_USER_DATA';
			return response($res);
		}

		$user_data 	= DB::table('users as u')
					->select('*')
					->where('u.id',$user_id)
					->first();
		
		if (!$user_data) {
			$res['success'] = false;
			$res['message'] = 'FAILED_GET_USER_DATA';
			return response($res);
		}

		$update_mtr_m 	= DB::table('mbp_trouble as mtr')
						->join('mbp as m', 'mtr.id', 'm.submission_id')
						->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
						->join('site as s', 'sp.site_id', 's.site_id')
						->where('m.submission_id',$cancel_id);

		if ($type_approval=='AGREE') {
			$update_mtr_m 
			->update([
				'mtr.respon_by_nik' => $user_data->id,
				'mtr.respon_by_cn' => $user_data->username,
				'mtr.respon_date' => $date_now,
				'mtr.is_approved' => 1,
				'mtr.is_active' => 0,
			]);

			$supplyingPowerController = new SupplyingPowerController;
			$value_sp_log 	= $supplyingPowerController	->saveLogSP1(
															$mbp_trouble->sp_id, 
															$mbp_trouble->user_id, 
															$mbp_trouble->username, 
															'SUBMIT_DELAY_APPROVED', 
															'rtpo menyetujui pengajuan delay terhadap mbp '.$mbp_trouble->mbp_name, 
															'',
															'', 
															$date_now
														);

			$notificationController = new NotificationController;
			$tmp 	= $notificationController	->setNotificationSubmissionAgreement(
													'APPROVE_DELAY',
													$mbp_trouble->mbp_name,
													$mbp_trouble->mbp_id
												);

		}
		else if ($type_approval=='CANCEL_TASK') {
			$update_mtr_m 
			->update([
				'mtr.respon_by_nik' => $user_data->id,
				'mtr.respon_by_cn' => $user_data->username,
				'mtr.respon_date' => $date_now,
				'mtr.is_approved' => 0,
				'mtr.is_active' => 0,

				'm.submission' =>null,
				'm.submission_id' =>null,
				'm.active_at' =>null,
				'm.message_id' =>null,
				'm.status' =>'AVAILABLE',

				's.is_allocated' =>0,

				'sp.finish' =>'CANCEL',
				'sp.date_finish' =>$date_now,
				'sp.detail_finish' =>4,
			]);

			$supplyingPowerController = new SupplyingPowerController;
			$value_sp_log 	= $supplyingPowerController	->saveLogSP1(
															$mbp_trouble->sp_id, 
															$mbp_trouble->user_id, 
															$mbp_trouble->username, 
															'SUBMIT_DELAY_NOT_APPROVED', 
															'rtpo tidak menyetujui pengajuan delay terhadap mbp '.$mbp_trouble->mbp_name, 
															'',
															'', 
															$date_now
														);

			$notificationController = new NotificationController;
			$tmp 	= $notificationController	->setNotificationSubmissionAgreement(
													'DENY_DELAY',
													$mbp_trouble->mbp_name,
													$mbp_trouble->mbp_id
												);
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function getCancellationLetter(Request $request){
		$rtpo_id = $request->input('rtpo_id');

		$mbp_trouble 	= DB::table('mbp_trouble as mtr')
						->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
						->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
						->join('users as u', 'um.username', 'u.username')
						->select('mtr.id as cancel_id','mtr.type as subject','mtr.desc as text_message','m.mbp_id as mbp_id','m.mbp_name','u.name','m.message_id as message_id','mtr.send_date as date')
						->where('mtr.is_active',1)
						->where('mtr.send_to_rtpo_id',$rtpo_id)
						->get();

		foreach ($mbp_trouble as $key => $value) {
			if ($value->message_id==null) {
				$value->message_id=1;
			}
		}

		if ($mbp_trouble) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $mbp_trouble;

			return response($res);
		}
		else{
			$res['success'] = false;
			$res['message'] = 'FAILED_GET_MESSAGE';
			
			return response($res);
		}
	}

	public function finishDelayFromMbp(Request $request){
		$user_id_rtpo 	= $request->input('user_id');
		$cancel_id 		= $request->input('cancel_id');

		$user_data 	= DB::table('users')
					->select('*')
					->where('id','=', $user_id_rtpo)
					->first();

		$updateCancel = $this->acceptDelayFromMbp($cancel_id, $user_id_rtpo, $user_data->username);
		if ($updateCancel=='OK') {

			// $fireBaseControlle = new FireBaseController;
			// $body = 'Pengajuan telah selesai dan Mbp bisa lanjut bertugas';
			// $tittle = 'Pengajuan Penundaan';
			// $datax =$fireBaseControlle->sendNotification($tittle, $body);


			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			// $res['data'] = $getTableCancel;          
			return $res;
		}
		else{
			return $updateCancel;
		}
	}

	public function acceptDelayFromMbp($cancel_id, $user_id_rtpo, $username){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
	
		$checkCancellationLetter 	= DB::table('cancel_details')
									->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
									->join('user_mbp', 'users.username', '=', 'user_mbp.username')        //get name_mbp
									->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
									->select('*')
									->where('cancel_details.id', $cancel_id)
									->where('cancel_details.user_id_rtpo', NULL)
									->first();
	
		//bila cancel detil belum di tanda tangani maka
		if ($checkCancellationLetter!=null) {
	
			$supplyingPowerController = new SupplyingPowerController;
			$value_sp_log = $supplyingPowerController->saveLogSP1($checkCancellationLetter->sp_id, $checkCancellationLetter->id, $checkCancellationLetter->username, 'MBP_DELAY_FINISHED', 'user menyelesaikan delay mbpnya','' , '', $date_now);
	
			$updateCancellationLetter = DB::table('cancel_details')
			->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
			->join('user_mbp', 'users.username', '=', 'user_mbp.username')
			->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
			->where('cancel_details.id', $cancel_id)
			->where('cancel_details.user_id_rtpo', NULL)
			->update(
			[
			// 'cancel_details.user_id_rtpo' =>$user_id_rtpo,
				'cancel_details.response_status' =>'1',
				// 'cancel_details.respon_by' =>$username,
				// 'cancel_details.respon_time' =>date('Y-m-d H:i:s'),
				// 'cancel_details.user_id_responders' =>$user_id_rtpo,
				'mbp.submission' =>null,
				'mbp.submission_id' =>null,
				'mbp.active_at' =>NULL,
				'mbp.message_id' => NULL,
			// 'mbp.delay' =>'0',
			]
			);
	
			if ($updateCancellationLetter) {
	
			return 'OK';
			}else{
			$res['success'] = false;
			$res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS_1';
	
			return $res;
			}
		}else{//bila cancel detil sudah di tanda tangani maka
	
			$updateCancellationLetter = DB::table('cancel_details')
			->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
			->join('user_mbp', 'users.username', '=', 'user_mbp.username')
			->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
			->where('cancel_details.id', $cancel_id)
			// ->where('cancel_details.user_id_rtpo', NULL)
			->update(
			[
			// 'cancel_details.user_id_rtpo' =>$user_id_rtpo,
				// 'cancel_details.response_status' =>'1',
				// 'cancel_details.user_id_responders' =>$user_id_rtpo,
				'mbp.submission' =>null,
				'mbp.submission_id' =>null,
				'mbp.active_at' =>NULL,
				'mbp.message_id' => NULL,
			// 'mbp.delay' =>'0',
			]
			);
	
			if ($updateCancellationLetter) {
	
			return 'OK';
			}else{
			$res['success'] = false;
			$res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS_2';
	
			return $res;
			}
	
		}  
		}

}