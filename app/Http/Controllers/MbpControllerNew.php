<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DateTime;
// use App\Bts;
use DB;

class MbpControllerNew extends Controller{
	
public function get_detail_mbp(Request $request){
	$mbp_id = $request->input('mbp_id');
	date_default_timezone_set("Asia/Jakarta");

	$mbp_data = DB::table('mbp as m')
				->select('*')
				->where('m.mbp_id','=',$mbp_id)
				->first();

	if ($mbp_data->rtpo_id != $mbp_data->rtpo_id_home) {
		$borrowed = true;
	}else{
		$borrowed = false;
	}

	if ($mbp_data) {
		if ($mbp_data->submission=='DELAY') {
			/*
			select *
			from mbp as m
			join rtpo as rh on m.rtpo_id_home = rh.rtpo_id
			join rtpo as rn  on  m.rtpo_id = rn.rtpo_id
			join user_mbp as um  on m.mbp_id = um.mbp_id
			join users as u  on um.username = u.username
			join supplying_power as sp on m.mbp_id = sp.mbp_id
			join site as s on sp.site_id = s.site_id
			join message as msg on m.message_id = msg.id
			where sp.finish = NULL
			*/
			$result = DB::table('mbp as m')
							->join('rtpo as rh', 'm.rtpo_id_home', '=', 'rh.rtpo_id')
							->join('rtpo as rn', 'm.rtpo_id', '=', 'rn.rtpo_id')
							->join('user_mbp as um', 'm.mbp_id', '=', 'um.mbp_id')
							->join('users as u', 'um.username', '=', 'u.username')
							->join('supplying_power as sp', 'm.mbp_id', '=', 'sp.mbp_id')
							->join('site as s', 'sp.site_id', '=', 's.site_id')
							->join('message as msg', 'm.message_id', '=', 'msg.id')
							->select('*', 'm.status as mbp_status', 's.latitude as site_latitude', 's.longitude as site_longitude', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now','sp.user_rtpo_cn as ticket_by')
							->where('m.mbp_id','=',$mbp_id)
							->where('sp.finish','=',null)
							->first();

			$result = json_decode($user_mbp_data, "OK");
			if ($result==NULL) {
				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['data'] = $user_mbp_data;
				return response($res);
			}

			foreach ($result as $param => $row) {
				// $data['status'] = mbp_status;
				$data[$param]['ticket_by']           = $row['ticket_by'];
				$data[$param]['telegram_username']   = "";
				$data[$param]['status']              = 'DELAY';
				$data[$param]['mbp_name']            = $row['mbp_name'];
				$data[$param]['name']                = $row['name'];
				$data[$param]['phone']               = $row['phone'];
				$data[$param]['mbp_latitude']        = $row['latitude'];
				$data[$param]['mbp_longitude']       = $row['longitude'];
				$data[$param]['site_name']           = $row['site_name'];
				$data[$param]['code_name']           = $row['site_id'];
				$data[$param]['class_name']          = $row['site_class'];
				$data[$param]['latitude']            = $row['site_latitude'];
				$data[$param]['longitude']           = $row['site_longitude'];
				$data[$param]['borrowed']            = $borrowed;
				$data[$param]['date_waiting']        = strtotime($row['date_waiting']);
				$data[$param]['date_onprogress']     = strtotime($row['date_onprogress']);
				$data[$param]['date_checkin']        = strtotime($row['date_checkin']);
				$data[$param]['fmc_id']              = $row['fmc_id'];
				$data[$param]['fmc_name']            = $row['fmc'];
				$data[$param]['rtpo_id_home']        = $row['mbp_rtpo_id_home'];
				$data[$param]['rtpo_id_now']         = $row['mbp_rtpo_id'];
				$data[$param]['rtpo_name_home']      = $row['rtpo_name_home'];
				$data[$param]['rtpo_name_now']       = $row['rtpo_name_now'];
				$data[$param]['subject']             = $row['subject'];
				$data[$param]['text_message']        = $row['text_message'];
				$data[$param]['time']                = setDatedMYHis($row['active_at']);
			}

			if ($result) {
				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['data'] = $user_mbp_data;
				return response($res);
			}
		}
		switch ($mbp_data->status) {
			case "AVAILABLE":
				$user_mbp_data = DB::table('mbp as m')
								->join('rtpo as rh', 'm.rtpo_id_home', '=', 'rh.rtpo_id')
								->join('rtpo as rn', 'm.rtpo_id', '=', 'rn.rtpo_id')
								->join('user_mbp as um', 'm.mbp_id', '=', 'um.mbp_id')
								->join('users as u', 'um.username', '=', 'u.username')
								->select('*', 
								'm.status as mbp_status', 
								'm.rtpo_id as mbp_rtpo_id', 
								'm.rtpo_id_home as mbp_rtpo_id_home',
								'rh.rtpo_name as rtpo_name_home', 
								'rn.rtpo_name as rtpo_name_now')
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
				->join('rtpo as rh', 'm.rtpo_id_home', '=', 'rh.rtpo_id')
				->join('rtpo as rn', 'm.rtpo_id', '=', 'rn.rtpo_id')
				->join('user_mbp as um', 'm.mbp_id', '=', 'um.mbp_id')
				->join('users as u', 'um.username', '=', 'u.username')
				->join('message as msg', 'm.message_id', '=', 'msg.id')   
				->select('*', 
						'm.status as mbp_status', 
						'm.rtpo_id as mbp_rtpo_id',
						'm.rtpo_id_home as mbp_rtpo_id_home',
						'm.last_update as lu', 
						'rh.rtpo_name as rtpo_name_home', 
						'rn.rtpo_name as rtpo_name_now')
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

				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['data'] = $data;
				return response($res);
				
			break;
			default:
				$user_mbp_data = DB::table('mbp as m')
				->join('rtpo as rh', 'm.rtpo_id_home', '=', 'rh.rtpo_id')
				->join('rtpo as rn', 'm.rtpo_id', '=', 'rn.rtpo_id')
				->join('user_mbp as um', 'm.mbp_id', '=', 'um.mbp_id')
				->join('users as u', 'um.username', '=', 'u.username')
				->join('supplying_power as sp', 'm.mbp_id', '=', 'sp.mbp_id')
				->join('site as s', 'sp.site_id', '=', 's.site_id')
				->select('*', 
				'm.status as mbp_status', 
				's.latitude as site_latitude', 
				's.longitude as site_longitude', 
				'm.rtpo_id as mbp_rtpo_id', 
				'm.rtpo_id_home as mbp_rtpo_id_home',
				'rh.rtpo_name as rtpo_name_home', 
				'rn.rtpo_name as rtpo_name_now',
				'sp.user_rtpo_cn as ticket_by')
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

public function get_detail_mbp_tiket(Request $request){
	$sp_id = $request->input('sp_id');

	/*
	select sp.sp_id, 
	mbp.mbp_name, 
	sp.site_name, 
	sp.finish as `status`, 
	sp.date_finish as date_complete,
	user_rtpo.username,
	sp.cancel_reason,
	sp.reason_by,
	sp.cancel_approved_by,
	sp.date_onprogress, 
	sp.date_waiting, 
	sp.date_checkin, 
	sp.date_onprogress,
	sp.date_finish, 
	sp.running_hour_before,
	sp.running_hour_after
	from supplying_power as sp
	join mbp on sp.mbp_id = mbp.mbp_id
	join user_rtpo on sp.rtpo_id = user_rtpo.rtpo_id
	*/

	$btss = DB::table('supplying_power as sp')
	->join('mbp as mbp', 'sp.mbp_id', '=', 'mbp.mbp_id')
	->join('user_rtpo as user_rtpo', 'sp.rtpo_id', '=', 'user_rtpo.rtpo_id')
	->select(
	'sp.unique_id as id_ticket', 
	'mbp.mbp_name', 
	'sp.site_name as site_tujuan', 
	'sp.finish as status', 
	'sp.date_finish as date_complete',
	'user_rtpo.username as pembuat_tiket',
	'sp.cancel_reason as alasan_pembatalan',
	'sp.reason_by as yang_mengajukan',
	'sp.cancel_approved_by as yang_menyetujui',
	'sp.date_onprogress', 
	'sp.date_waiting', 
	'sp.date_checkin', 
	'sp.date_onprogress',
	'sp.date_finish', 
	'sp.running_hour_before',
	'sp.running_hour_after'
	)
	->where('sp.sp_id','=',$sp_id)
	->get();

	$result = json_decode($btss, "OK");

	$res['success'] = "OK";
	$res['message'] = 'Success';
	$res['data'] = $btss;

	foreach ($result as $param => $row) {
		$data[$param]['id_ticket']                 = $row['id_ticket'];
		$data[$param]['mbp_name']                  = $row['mbp_name'];
		$data[$param]['site_tujuan']               = $row['site_tujuan'];
		$data[$param]['status']                    = $row['status'];
		$data[$param]['date_complete']             = $row['date_complete'];
		$data[$param]['pembuat_tiket']             = $row['pembuat_tiket'];
		$data[$param]['alasan_pembatalan']         = $row['alasan_pembatalan'];
		$data[$param]['yang_mengajukan']           = $row['yang_mengajukan'];
		//$data[$param]['tanggal_pengajuan']       = $row['ticket_by'];
		$data[$param]['yang_menyetujui']           = $row['yang_menyetujui'];
		//$data[$param]['tanggal_persetujuan']     = $row['ticket_by'];      
		//$data[$param]['pembuatan_waktu']         = $row['ticket_by'];
		$data[$param]['waktu_respon']              = strtotime($row['date_onprogress']) - strtotime($row['date_waiting']);
		$data[$param]['waktu_menuju_site']         = strtotime($row['date_checkin']) - strtotime($row['date_onprogress']);
		if($data[$param]['status'] == 'DONE'){
			$data[$param]['waktu_backup_site']         = strtotime($row['date_finish']) - strtotime($row['date_checkin']);
		}
		else{
			$data[$param]['waktu_backup_site']         = NULL;
		}
		$data[$param]['running_hour_before']	   = $row['running_hour_before'];
		$data[$param]['running_hour_after']		   = $row['running_hour_after'];
	}

	return response($res);
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



}