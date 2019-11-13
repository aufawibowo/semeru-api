<?php
namespace App\Http\Controllers;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use App\Bts;
use DB;
use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;

class CorrectiveController extends Controller {

	public function listCorrectiveJobs(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now=time();
		// $x = $request->input('x');
		$list_corrective_job_data = DB::table('list_corrective_job')
		->select('*')
		->get();


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $list_corrective_job_data;
		return response($res);
	}
	
	public function sendCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$diskripsi = $request->input('diskripsi');
		$level = $request->input('level');
		$time_limit = $request->input('time_limit');
		// $jobs_id = $request->input('job_id');		//-> dapat job_name
		$send_by = $request->input('send_by');		//-> rtpo_id dan -> rtpo_name
		$site_id = $request->input('site_id');		//-> fmc_id dan fmc_name


		$date_now = time();
		$Corrective_id = 'CRT'.$date_now;
		$create_at = date('Y-m-d H:i:s');

		//--------------------------------------------- DATA JOBS --------------------------------------------------
		
		// $job_data = DB::table('list_corrective_job')
		// ->select('*')
		// ->where('jobs_id','=',$jobs_id)
		// ->first();

		// if ($job_data==null) {
		// 	$res['success'] = false;
		// 	$res['message'] = 'FAILED_JOBS_DATA_NOT_FOUND';
		// 	return response($res);
		// }

		// $job_name = $job_data->jobs_name;
		// $diskripsi = $diskripsi;
		// $jobs_level = $job_data->jobs_level;
		// $time_limit = $job_data->time_limit;

		//--------------------------------------------- DATA RTPO --------------------------------------------------

		$rtpo_data = DB::table('user_rtpo')
		->select('*')
		->where('username','=',$send_by)
		->first();

		if ($rtpo_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILED_RTPO_DATA_NOT_FOUND';
			return response($res);
		}

		$rtpo_id = $rtpo_data->rtpo_id;
		$rtpo_name = $rtpo_data->rtpo_name;


		//--------------------------------------------- DATA SITE --------------------------------------------------

		$fmc_data = DB::table('lookup_fmc_cluster')
		->join('site', 'lookup_fmc_cluster.cluster', '=', 'site.cluster')
		->select('*')
		->where('site_id','=',$site_id)
		->first();

		if ($fmc_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILED_FMC_DATA_NOT_FOUND';
			return response($res);
		}

		$fmc_id = $fmc_data->fmc_id;
		$fmc = $fmc_data->fmc;

		// $data['Corrective_id'] = $Corrective_id;
		// $data['jobs_id'] = $jobs_id;
		// $data['job_name'] = $job_name;
		// $data['job_level'] = $jobs_level;
		// $data['send_by'] = $send_by;
		// $data['rtpo_id'] = $rtpo_id;
		// $data['rtpo_name'] = $rtpo_name;
		// $data['site_id'] = $site_id;
		// $data['fmc_id'] = $fmc_id;
		// $data['fmc_name'] = $fmc_name;
		// $data['create_at'] = $create_at;


		// wall
		$wall_corrective_data = DB::table('corrective')
		->select('*')
		->where('send_by','=',$send_by)
		->where('site_id','=',$site_id)
		->where('finish_at','=',null)
		->first();

		if ($wall_corrective_data!=null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['wall'] = 'DATA_FOUND';
			return response($res);
		}

		$insert_user_staff_nos_data = DB::table('corrective')
		->insert(
			[
				'corrective_id'=> $Corrective_id,
				'diskripsi'=>$diskripsi,
				// 'job_id'=> $jobs_id,
				// 'job_name'=> $job_name,
				'job_level'=> $level,
				'time_limit'=> $time_limit,
				'send_by'=> $send_by,
				'rtpo_id'=> $rtpo_id,
				'rtpo_name'=> $rtpo_name,
				'site_id'=> $site_id,
				'fmc_id'=> $fmc_id,
				'fmc'=> $fmc,
				'create_at'=> $create_at,
				'is_sync'=> '0',
			]
		);

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		return response($res);
	}

	// public function listCorrectiveFromFmc(Request $request){

	// 	$rtpo_id = $request->input('rtpo_id');
		
	// 	$list_corrective_data = DB::table('corrective')
	// 	->select('*')
	// 	->where('rtpo_id','=',$rtpo_id)
	// 	->where('approve_at','=',null)
	// 	->get();


	// 	$res['success'] = true;
	// 	$res['message'] = 'SUCCESS';
	// 	$res['data'] = $list_corrective_data;
	// 	return response($res);
	// }

	public function takeCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id');
		$take_by = $request->input('take_by');

		$take_at = date('Y-m-d H:i:s');

		$wall_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->where('take_by','=',$take_by)
		->first();
		if ($wall_corrective_data!=null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['wall'] = 'DATA_FOUND';
			return response($res);
		}

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[
				'take_by'=> $take_by,
				'take_at'=> $take_at,
				'is_sync'=> '0',
			]
		);

		if ($update_corrective_data) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'UPDATE_FAILED';
			return response($res);
		}
	}	

	public function approveCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id');
		$approve_by = $request->input('approve_by');

		$approve_at = date('Y-m-d H:i:s');

		$wall_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->where('approve_by','=',$approve_by)
		->first();
		if ($wall_corrective_data!=null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['wall'] = 'DATA_FOUND';
			return response($res);
		}

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[
				'approve_by'=> $approve_by,
				'approve_at'=> $approve_at,
				'is_sync'=> '0',
			]
		);

		if ($update_corrective_data) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'UPDATE_FAILED';
			return response($res);
		}
	}	


	public function rejectCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id');
		// $approve_by = $request->input('approve_by');

		$approve_at = date('Y-m-d H:i:s');

		$wall_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->where('finish_by','=',null)
		->first();
		if ($wall_corrective_data!=null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['wall'] = 'DATA_FOUND';
			return response($res);
		}

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[
				'finish_by'=> null,
				'finish_at'=> null,
				'is_sync'=> '0',
			]
		);

		if ($update_corrective_data) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'UPDATE_FAILED';
			return response($res);
		}
	}	

	public function finishCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id');
		$finish_by  = $request->input('finish_by');

		$finish_at = date('Y-m-d H:i:s');

		$wall_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->where('finish_by','=',$finish_by )
		->first();
		if ($wall_corrective_data!=null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['wall'] = 'DATA_FOUND';
			return response($res);
		}

		$corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		// $tmp_time_create =  strtotime($corrective_data->create_at);
		// $tmp_time_finish = strtotime($finish_at);
		// $tmp_hasil_pegurangan = $tmp_time_finish-$tmp_time_create;
		// $data['tmp_time_create_real'] = $corrective_data->create_at;
		// $data['tmp_time_create'] = $tmp_time_create;
		// $data['tmp_time_finish_real'] = $finish_at;
		// $data['tmp_time_finish'] = $tmp_time_finish;
		// $data['tmp_hasil_pegurangan'] = $tmp_hasil_pegurangan;
		// $data['tmp_hasil_dalam_date'] = date("H:i:s", $tmp_hasil_pegurangan);

		// $awal = $corrective_data->create_at;
		// $start_of_mounth = $finish_at;
		$start_date = new DateTime($corrective_data->create_at);
		$end_date = new DateTime($finish_at);
		$interval= $start_date->diff($end_date);

		$waktu_pengerjaan = $interval->h;
		$batas_pengerjaan = $corrective_data->time_limit;

		if ($waktu_pengerjaan<$batas_pengerjaan) {
			$overdue = 0;
		}else{
			$overdue = 1;
		}

		// return response($overdue);


		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[
				'finish_by'=> $finish_by,
				'finish_at'=> $finish_at,
				'overdue'=> $overdue,
				'is_sync'=> '0',
			]
		);

		if ($update_corrective_data) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'UPDATE_FAILED';
			return response($res);
		}
	}	

	public function listHistoryCorrectiveFromRTPO(Request $request){

		$rtpo_id = $request->input('rtpo_id');
		
		$list_corrective_data = DB::table('corrective')
		->select('*')
		->where('rtpo_id','=',$rtpo_id)
		->where('finish_at','!=',null)
		->get();




		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $list_corrective_data;
		return response($res);
	}

	public function detilCorrectiveFromRTPO(Request $request){

		$corrective_id = $request->input('corrective_id');
		
		$detil_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($detil_corrective_data == null) {
			$res['success'] = false;
			$res['message'] = 'FAILED_DATA_NOT_FOUND';
		}

		$data['corrective_id'] = $detil_corrective_data->corrective_id;
		$data['job_id'] =  $detil_corrective_data->job_id;
		$data['job_name'] =  $detil_corrective_data->job_name;
		$data['diskripsi'] =  $detil_corrective_data->diskripsi;
		$data['send_by'] =  $detil_corrective_data->send_by;
		$data['rtpo_id'] =  $detil_corrective_data->rtpo_id;
		$data['rtpo_name'] =  $detil_corrective_data->rtpo_name;
		$data['site_id'] =  $detil_corrective_data->site_id;
		$data['create_at'] =  $detil_corrective_data->create_at;
		$data['fmc_id'] =  $detil_corrective_data->fmc_id;
		$data['fmc'] =  $detil_corrective_data->fmc;
		$data['take_by'] =  $detil_corrective_data->take_by;
		$data['take_at'] =  $detil_corrective_data->take_at;
		$data['finish_by'] =  $detil_corrective_data->finish_by;
		$data['finish_at'] =  $detil_corrective_data->finish_at;
		$data['job_level'] =  $detil_corrective_data->job_level;
		$data['time_limit'] =  $detil_corrective_data->time_limit;
		$data['overdue'] =  $detil_corrective_data->overdue;
		$data['approve_by'] =  $detil_corrective_data->approve_by;
		$data['approve_at'] =  $detil_corrective_data->approve_at;

		if ($detil_corrective_data->finish_at != null) {
			# code...
			$data['corrective_status'] = 'WAITING_FOR_APPROVAL'; //ON_PROGRESS, WAITING_FOR_APPROVAL, RESOLVED
		}else{
			$data['corrective_status'] = 'ON_PROGRESS'; //ON_PROGRESS, WAITING_FOR_APPROVAL, RESOLVED
		}

		if ($detil_corrective_data->approve_at != null) {
			# code...
			$data['corrective_status'] = 'COMPLETED'; //ON_PROGRESS, WAITING_FOR_APPROVAL, RESOLVED
		}
		

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['data'] = $list_corrective_data;
		$res['data'] = $data;
		return response($res);
	}























//===================================================================== NEW CORRECTIVE =================================================================

	public function listCategoryCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now=time();
		// $x = $request->input('x');
		$list_corrective_job_data = DB::table('app_catagory_corrective')
		->select('id','Category as kategori')
		->where('status','=','1')
		->get();


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = @$list_corrective_job_data;
		return response($res);
	}
	public function appSendCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now =date('Y-m-d H:i:s');

		// $time_limit = $request->input('time_limit');

		$web = $request->input('web');
		$test['web'] = $web;

		if ($web==null) {
			// $res['success'] = true;
			// $res['message'] = 'NULL';
			// return response($res);
			$site_id = $request->input('site_id');	#-> site_name 
			$site_id = explode(" - ",$site_id);
			$site_id = $site_id[0];
			// $res['site_id'] = @$site_id;
			// return response($res);

			$description = $request->input('description');
			$category_id = $request->input('category_id');
			$corrective_type = $request->input('level'); #critical / minor
			$corrective_type = strtoupper($corrective_type."");
			$corrective_date = date('Y-m-d H:i:s');
			// // CMP001_180115023926
			//CORRECTIVE_180430124447
			$unique_id = 'CORRECTIVE_'.SUBSTR($date_now, 2,2).SUBSTR($date_now, 5,2).SUBSTR($date_now, 8,2).SUBSTR($date_now, 11,2).SUBSTR($date_now, 14,2).SUBSTR($date_now, 17,2);
			$send_by = $request->input('send_by'); #diisi username dan dapatkan nik #-> rtpo_id #-> rtpo_name #-> fmc_id #-> fmc_name
			$request_status = '0';



			$user_rtpo_data = DB::table('user_rtpo')
			->join('users', 'user_rtpo.username', 'users.username')
			// ->join('fmc', 'user_rtpo.fmc_id', 'fmc.fmc_id')
			->select('*')
			->where('user_rtpo.username','=',$send_by)
			->first();

				// return response($user_rtpo_data->username);

			if (@$user_rtpo_data->username==null) {
				$res['success'] = false;
				$res['send_by'] = $send_by;
				$res['message'] = 'FAILLED_DATA_USER_RTPO_NOT_FOUND';
				return response($res);
			}

			# cari data" yang dibutuhkan satu per satu
			$site_data = DB::table('site')
			->join('lookup_fmc_cluster', 'site.cluster_fmc_id', '=', 'lookup_fmc_cluster.cluster_fmc_id')
			->select('*')
			->where('site.site_id','=',$site_id)
			->where('site.rtpo_id','=',@$user_rtpo_data->rtpo_id)
			->where('lookup_fmc_cluster.status','=',1)
			->first();

			if ($site_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_DATA_SITE_NOT_MATCH';
				//.$site_id.' '.$description.' '.$category_id.' '.$send_by;
				$res['test'] = $test;
				return response($res);
			}

			$category_corrective_data = DB::table('app_catagory_corrective')
			->select('*')
			->where('status','=',1)
			->where('category_id','=',$category_id)
			->first();

			if ($category_corrective_data==null) {
				$res['success'] = false;
				$res['send_by'] = $send_by;
				$res['message'] = 'FAILLED_DATA_CATEGORY_NOT_FOUND';
				return response($res);
			}


			$data['site_id'] = $site_id;
			$data['site_name'] = $site_data->site_name;
			

			$data['category_id'] = @$category_corrective_data->category_id;
			$data['category'] = @$category_corrective_data->category;
			$data['target_role_id'] = @$category_corrective_data->target_role_id;
			$data['target_role'] = @$category_corrective_data->target_role;


			$data['description'] = $description;
			$data['corrective_type'] = $corrective_type;
			$data['corrective_date'] = $corrective_date;
			
			if (@$corrective_type=="CRITICAL") {
				$tmp_hour = 4;				
			}else if (@$corrective_type=="MINOR") {
				$tmp_hour = 6;
			}else{
				$tmp_hour = 0;
			}
			$tmp_end_corrective_date = strtotime($corrective_date."+".$tmp_hour." hour");
			$end_corrective_date = date('Y-m-d H:i:s',$tmp_end_corrective_date);
			$data['end_corrective_date'] = $end_corrective_date;

			$data['send_by'] = $user_rtpo_data->id;
			$data['send_by_cn'] = $send_by; // sendby telah diisi username
			$data['rtpo_id'] = $user_rtpo_data->rtpo_id;
			$data['rtpo'] = $user_rtpo_data->rtpo_name;
			$data['fmc_id'] = @$site_data->fmc_id.'';
			$data['fmc'] = @$site_data->fmc;
			$data['regional'] = $site_data->regional;
			$data['request_status'] = $request_status;

			// $data['regional'] = $site_data->regional;
			$data['cluster_id'] = $site_data->cluster_id;
			$data['cluster'] = $site_data->cluster;
			$data['cluster_fmc_id'] = $site_data->cluster_fmc_id;
			$data['cluster_fmc'] = $site_data->cluster_fmc;
			$data['ns_id'] = $site_data->ns_id;
			$data['ns'] = $site_data->ns;
			$data['branch_id'] = $site_data->branch_id;
			$data['branch'] = $site_data->branch;
			// CMP001_180115023926


			$cluster_code = sprintf("%'03d", $data['cluster_id']);

			$corrective_id = 'CMP'.$cluster_code.'_'.SUBSTR($date_now, 2,2).SUBSTR($date_now, 5,2).SUBSTR($date_now, 8,2).SUBSTR($date_now, 11,2).SUBSTR($date_now, 14,2).SUBSTR($date_now, 17,2);

			$data['id'] = $corrective_id;
			// $res['success'] = true;
			// $res['message'] = 'SUCCESS';
			// $res['data'] = $data;
			// return response($res);
			//----------------------------------------------------testing..

			//cek duplicate
			
			$delay_time = strtotime($date_now." -2 min");
		    $date2 = date('Y-m-d H:i:s',$delay_time);

			$cek_duplicate = DB::table('corrective')
			->select('*')
			->where('corrective_date','>',$date2)
			->where('site_id',$site_id)
			->whereNotIn('request_status',[8,10,11,12,13])
			->first();

			if ($cek_duplicate!=NULL){
				$res['success'] = false;
				$res['message'] = 'Gagal! Duplikasi Tiket!';
				return response($res);
			}
			
			

			#insert ke tabel "app_corrective" 
			$insert_app_corrective_data = DB::table('corrective')
			->insert(
				[
					// 'corrective_id'=> $Corrective_id,
					'site_id'=>$data['site_id'],
					'corrective_id'=>$corrective_id,
					'unique_id'=>@$unique_id,
					'site_name'=>$data['site_name'],


					'category_id'=>$data['category_id'],
					'category'=>$data['category'],
					'target_role_id'=>$data['target_role_id'],
					'target_role'=>$data['target_role'],


					'description'=>$data['description'],
					'corrective_type'=>$data['corrective_type'],
					'corrective_date'=>$data['corrective_date'],
					'end_corrective_date'=>$data['end_corrective_date'],
					'last_update'=>$data['corrective_date'],
					'send_by'=>$data['send_by'],
					'send_by_cn'=>$data['send_by_cn'],
					'rtpo_id'=>$data['rtpo_id'],
					'rtpo'=>$data['rtpo'],

					'regional'=>$data['regional'],
					'cluster_id'=>$data['cluster_id'],
					'cluster'=>$data['cluster'],
					'cluster_fmc_id'=>$data['cluster_fmc_id'],
					'cluster_fmc'=>$data['cluster_fmc'],
					'ns_id'=>$data['ns_id'],
					'ns'=>$data['ns'],
					'branch_id'=>$data['branch_id'],
					'branch'=>$data['branch'],

					'fmc_id'=>$data['fmc_id'],
					'fmc'=>$data['fmc'],
					'regional'=>$data['regional'],
					'request_status'=>$data['request_status'],
					'is_sync'=> '0',
				]
			);

			if (!$insert_app_corrective_data) {
			}

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_date','=',$data['corrective_date'])
			->first();

			if ($app_corrective_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
				return response($res);
			}

			$data['corrective_id'] = $app_corrective_data->corrective_id.'';
			$data['user_nik'] = $data['send_by'];
			$data['user_cn'] = $data['send_by_cn'];
			$data['status'] = $data['request_status'];
			$status_corrective = DB::table('status_corrective')
			->select('*')
			->where('status_id','=',$data['status'])
			->first();
			$data['status_desc'] = $status_corrective->status_desc;

			$data['description'] = $data['description'];
			$data['last_update'] = $data['corrective_date'];

			#dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$unique_id,
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>@$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> '0',
				]
			);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}


		}else {
			// $res['success'] = true;
			// $res['message'] = 'notnull';
			// return response($res);

				// $res['success'] = false;
				// $res['message'] = 'null';
				// return response(null);

			$data_corective = $request->input('data');

				// $res['message'] = $data_corective['log'];
				// return response($res);

			if ($data_corective==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_DATA_NULL';
				return response($res);
			}

			// $corective_result = json_decode($data_corective, true);
			$data['site_id'] = $data_corective['site_id'];
			$data['site_name'] = @$data_corective['site_name'];
			$data['category_id'] = @$data_corective['category_id'];
			$data['category'] = @$data_corective['category'];
			$data['target_role_id'] = @$data_corective['target_role_id'];
			$data['target_role'] = @$data_corective['target_role'];

			$data['description'] = @$data_corective['description'];
			$data['corrective_type'] = @$data_corective['corrective_type'];
			$data['corrective_date'] = @$data_corective['corrective_date'];


			if (@$corrective_type=="CRITICAL") {
				$tmp_hour = 4;				
			}else if (@$corrective_type=="MINOR") {
				$tmp_hour = 6;
			}else{
				$tmp_hour = 0;
			}
			$tmp_end_corrective_date = strtotime($data_corective['corrective_date']."+".$tmp_hour." hour");
			$end_corrective_date = date('Y-m-d H:i:s',$tmp_end_corrective_date);
			$data['end_corrective_date'] = $end_corrective_date;

			if (@$data_corective['end_corrective_date']!=null) {
				$data['end_corrective_date'] = $data_corective['end_corrective_date'];
			}


			$data['send_by'] = @$data_corective['send_by'];
			$data['send_by_cn'] = @$data_corective['send_by_cn']; // sendby telah diisi username
			$data['rtpo_id'] = @$data_corective['rtpo_id'];
			$data['rtpo'] = @$data_corective['rtpo'];
			$data['fmc_id'] = @$data_corective['fmc_id'];
			$data['fmc'] = @$data_corective['fmc'];
			$data['regional'] = @$data_corective['regional'];
			$data['request_status'] = @$data_corective['request_status'];

			$data['cluster_id'] = @$data_corective['cluster_id'];
			$data['cluster'] = @$data_corective['cluster'];
			$data['cluster_fmc_id'] = @$data_corective['cluster_fmc_id'];
			$data['cluster_fmc'] = @$data_corective['cluster_fmc'];
			$data['ns_id'] = @$data_corective['ns_id'];
			$data['ns'] = @$data_corective['ns'];
			$data['branch_id'] = @$data_corective['branch_id'];
			$data['branch'] = @$data_corective['branch'];

			// $data['unique_id'] = $data_corective['unique_id'];
			$data['corrective_id'] = $data_corective['corrective_id'];

			$data['is_sync'] = "1";
			$data['last_sync'] = @$data_corective['last_sync'];
			$data['id_sync'] = @$data_corective['id_sync'];

			



			// $data['site_id'] = $data_corective['site_id'];
			// $data['id'] = $data_corective['id'];
			// $data['unique_id'] = $data_corective['unique_id'];
			// $data['site_name'] = $data_corective['site_name'];
			// $data['description'] = $data_corective['description'];
			// $data['corrective_type'] = $data_corective['corrective_type'];
			// $data['corrective_date'] = $data_corective['corrective_date'];
			// $data['send_by'] = $data_corective['send_by'];
			// $data['send_by_cn'] = $data_corective['send_by_cn'];
			// $data['rtpo_id'] = $data_corective['rtpo_id'];
			// $data['rtpo'] = $data_corective['rtpo'];
			// $data['fmc_id'] = $data_corective['fmc_id'];
			// $data['fmc'] = $data_corective['fmc'];
			// $data['regional'] = $data_corective['regional'];
			// $data['request_status'] = $data_corective['request_status'];


			$insert_app_corrective_data = DB::table('corrective')
			->insert(
				[
				// 'corrective_id'=> $Corrective_id,

					'site_id'=>$data['site_id'],
					'corrective_id'=>$data['corrective_id'],
					'unique_id'=>@$data['unique_id'],
					'site_name'=>$data['site_name'],


					'category_id'=>$data['category_id'],
					'category'=>$data['category'],
					'target_role_id'=>$data['target_role_id'],
					'target_role'=>$data['target_role'],


					'description'=>$data['description'],
					'corrective_type'=>$data['corrective_type'],
					'corrective_date'=>$data['corrective_date'],
					
					'end_corrective_date'=>@$data['end_corrective_date'],

					'last_update'=>$data['corrective_date'],
					'send_by'=>$data['send_by'],
					'send_by_cn'=>$data['send_by_cn'],
					'rtpo_id'=>$data['rtpo_id'],
					'rtpo'=>$data['rtpo'],

					'regional'=>$data['regional'],
					'cluster_id'=>$data['cluster_id'],
					'cluster'=>$data['cluster'],
					'cluster_fmc_id'=>$data['cluster_fmc_id'],
					'cluster_fmc'=>$data['cluster_fmc'],
					'ns_id'=>$data['ns_id'],
					'ns'=>$data['ns'],
					'branch_id'=>$data['branch_id'],
					'branch'=>$data['branch'],

					'fmc_id'=>$data['fmc_id'],
					'fmc'=>$data['fmc'],
					'regional'=>$data['regional'],
					'request_status'=>$data['request_status'],
					'is_sync'=> '1',
				]
			);


			if (!$insert_app_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE';
				return response($res);
			}

			 $app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$data['corrective_id'])
			->first();

			if ($app_corrective_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
				return response($res);
			}

			$data['corrective_id'] = @$app_corrective_data->corrective_id;
			$data['user_nik'] = @$data_corective['log']['user_nik'];
			$data['user_cn'] = @$data_corective['log']['user_cn'];
			$data['status'] = @$data_corective['log']['status'];
			$data['status_desc'] = @$data_corective['log']['status_desc'];
			$data['description'] = @$data_corective['log']['description'];
			$data['last_update'] = @$data_corective['log']['last_update'];

			#dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$data['unique_id'],
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> "1",
					'last_sync'=> @$data_corective['log']['last_sync'],
					'id_sync'=> @$data_corective['log']['id_sync'],
				]
			);
			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				$res['data'] = $data;
				return response($res);
			}

		}


		// $res['success'] = true;
		// $res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		// return response($res);
		//--------------testing aja...................................

		// if ($data['target_role_id']==7) {
		// 	$users_data = DB::table('user_mbp_mt as ut')
		// 	->join('users as u', 'ut.mbp_mt_username', 'u.username')
		// 	->select('*', 'ut.mbp_mt_username as tsra_username')
		// 	->where('ut.fmc_id','=',$data['fmc_id'])
		// 	->where('ut.cluster','=',$data['cluster'])
		// 	->get();
		// }else if($data['target_role_id']==11) {
		// 	$users_data = DB::table('user_tsra as ut')
		// 	->join('users as u', 'ut.tsra_username', 'u.username')
		// 	->select('*')
		// 	->where('ut.fmc_id','=',$data['fmc_id'])
		// 	->where('ut.cluster','=',$data['cluster'])
		// 	->get();
		// }


		$notificationController = new NotificationController;

		// $to_token_id = array();
  //       $result = json_decode($users_data, true);
  //       foreach ($result as $param => $row) {
  //       	$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['tsra_username'], 'RTPO_SEND_CORRECTIVE_TICKET', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_SEND_CORRECTIVE_TICKET', $data['user_cn'].' dari '.$data['rtpo'].' mengirimkan tiket korektive kepada fmc anda');

		// 	// array_push($to_token_id,@$row['firebase_token']);
		// }


//================================================= di buka yang bawa kalao error
		// $fmc_fc_data = DB::table('users')
		// ->select('*')
		// ->where('fmc_id','=',$data['fmc_id'])
		// ->where('cluster','=',$data['cluster'])
		// ->where('firebase_token','!=',null)
		// ->get();


		if ($data['target_role_id']==7) {
			$fmc_fc_data = DB::table('user_mbp_mt as ut')
			->join('users as u', 'ut.mbp_mt_username', 'u.username')
			->select('*', 'ut.mbp_mt_username as tsra_username')
			->where('ut.fmc_id','=',$data['fmc_id'])
			->where('ut.cluster','=',$data['cluster'])
			->get();
		}else if($data['target_role_id']==11) {
			$fmc_fc_data = DB::table('user_tsra as ut')
			->join('users as u', 'ut.tsra_username', 'u.username')
			->select('*')
			->where('ut.fmc_id','=',$data['fmc_id'])
			->where('ut.cluster','=',$data['cluster'])
			->get();
		}

		$to_token_id = array();
        $result = json_decode($fmc_fc_data, true);
        foreach ($result as $param => $row) {

        	$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_SEND_CORRECTIVE_TICKET', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_SEND_CORRECTIVE_TICKET', $data['user_cn'].' dari '.$data['rtpo'].' mengirimkan tiket corrective kepada fmc anda',2,'CORRECTIVE');

			array_push($to_token_id,@$row['firebase_token']);
		}


		$fbc = new FireBaseController;
		$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$data['rtpo'].' mengirimkan tiket corrective kepada fmc anda',$to_token_id,2,$data['corrective_id'],'RTPO_SEND_CORRECTIVE_TICKET');

// 		$fmc_fc_data = DB::table('user_fmc')
// 		->select('*')
// 		->where('fmc_id','=',$data['fmc_id'])
// 		->where('cluster','=',$data['cluster'])
// 		->where('chat_id','!=',null)
// 		->get();

// 		foreach ($fmc_fc_data as $param) {
// 			$tmp_ftb = $fbc->formatTelegram('sendTicketCorrective', '
// Dari : '.$data['user_cn'].'
// RTPO : '.$data['rtpo'].'
// ID Site : '.$data['site_id'].'
// Nama site : '.$data['site_name'].'
// Cluster : '.$data['cluster'].'
// Deskripsi : '.$data['description'].'
// Tanggal : '.$data['corrective_date'].'

// Dihimbau untuk mengingatkan tim Corrective cluster tersebut, terimakasih.', $param->cluster);
// 			$tmp_tb = $fbc->sendNotificationTelegram($tmp_ftb, $param->chat_id);
// 		}

		  $admin_fmc_data = DB::table('users')
      ->select('*')
      ->where('fmc_id','=',$data['fmc_id'])
      ->where('cluster','=',$data['cluster'])
      ->where('chat_id','!=',null)
      ->where('chat_id','!=',"")
      ->get();

      foreach ($admin_fmc_data as $param) {

        if (@$param->username!=null) {
          
        $subject_telegram = 'sendTicketCorrective';
        // $title = strip_tags("<b><i> TIKET CORRECTIVE CLUSTER ".@$data['cluster']." </i></b>","<b>");
// 		$text_telegram = '[ TIKET CORRECTIVE CLUSTER '.@$data['cluster'].' ]
// Dari : '.$data['user_cn'].'
// RTPO : '.$data['rtpo'].'
// ID Site : '.$data['site_id'].'
// Nama site : '.$data['site_name'].'
// Cluster : '.$data['cluster'].'
// Deskripsi : '.$data['description'].'
// Tanggal : '.$data['corrective_date'].'

// Dihimbau untuk mengingatkan tim Corrective cluster tersebut, terimakasih.';

$text_telegram = "[ <b>TIKET CORRECTIVE</b> ] \nHalo, \nada Tiket Corrective untuk ".@$data["site_name"]." cluster ".@$data["cluster"]." dengan ID ".$data["site_id"].", dibuat oleh ".$data["user_cn"]." dari ".$data["rtpo"]." pada tanggal ".$data["corrective_date"].", dengan keterangan berikut : ".htmlspecialchars($data["description"]).". \n \nJangan lupa untuk mengingatkan tim corrective cluster ".@$data["cluster"]." mengenai hal ini. \nTerima Kasih. \n \n-NGSemeru Team-";
// $text_telegram = htmlspecialchars($text_telegram, ENT_QUOTES);

	        $inserQueueTelegram = DB::table('queue_telegram')   
	        ->insert(
	          [
	            'subject' => @$subject_telegram,
	            'message' => @$text_telegram,
	            'chat_id' => @$param->chat_id,

	            'send_to' => @$param->username,
	            'fmc_id' => @$param->fmc_id,
	            'cluster_id' => @$param->cluster_id,
	            'rtpo_id' => @$data['rtpo_id'],

	            'create_at' => @$date_now,

	          ]
	        );
        }
      }


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['user_fmc_data'] = $fmc_fc_data;
		// $res['tmp_fb'] = $tmp_fb;
		$res['data'] = $data;
		return response($res);
	}
	public function appSetStatusAcceptCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id'); 
		$respon_cn = $request->input('respon_by'); #diisi username dan dapatkan nik 
		$respon_time = date('Y-m-d H:i:s');
		$respon_desc = $request->input('description'); 
		$request_status = '1';


		$users_data = DB::table('users')
		->select('*')
		->where('username','=',$respon_cn)
		->first();

		if ($users_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->where('request_status',0)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		$start_date = new DateTime($app_corrective_data->corrective_date);

		$end_date = new DateTime($respon_time);

		$interval = date_diff($start_date,$end_date);
		$days = $interval->format('%a');
		$waktu_pengerjaan = ($days * 24 * 60) + ($interval->h * 60) + $interval->i;

		$data['interval'] = $waktu_pengerjaan ;
		$time_limit = 15;

		$batas_pengerjaan = $time_limit;
		if ($waktu_pengerjaan<$batas_pengerjaan) {
			$late_response_flag = 0;
		}else{
			$late_response_flag = 1;
		}



		$data['respon_nik'] = $users_data->id;
		$data['respon_cn'] = $respon_cn;
		$data['update_cn'] = $users_data->name;
		$data['respon_time'] = $respon_time;
		// $data['resolve_desc'] = $resolve_desc;
		$data['respon_desc'] = '';
		$data['request_status'] = $request_status;
		$data['late_response_flag'] = $late_response_flag;

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[

				// 'pending_nik'=> null,
				// 'pending_cn'=> null,
				// 'pending_time'=> null,
				// 'pending_desc'=> null,

				// 'rtpo_pending_nik'=> null,
				// 'rtpo_pending_desc'=> null,
				// 'rtpo_pending_cn'=> null,
				// 'rtpo_pending_time'=> null,
				// 'rtpo_pending_status'=> null,

				// 'respon_nik'=> $data['respon_nik'],
				// 'respon_cn'=> $data['respon_cn'],
				// 'respon_time'=> $data['respon_time'],
				// 'respon_desc'=> $data['respon_desc'],
				'update_by'=>@$data['respon_nik'],
				'update_cn'=>@$data['update_cn'],
				'request_status'=> $data['request_status'],
				'late_response_flag'=> $data['late_response_flag'],
				'last_update'=>$data['respon_time'],
				'is_sync'=> '0',
			]
		);
		if (!$update_corrective_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			return response($res);
		}

		$data['corrective_id'] = $corrective_id.'';
		$data['user_nik'] = $data['respon_nik'];
		$data['user_cn'] = $data['respon_cn'];
		$data['status'] = $data['request_status'];

		$status_corrective = DB::table('status_corrective')
		->select('*')
		->where('status_id','=',$data['status'])
		->first();
		$data['status_desc'] = @$status_corrective->status_desc;

		$data['description'] = $data['respon_desc'];
		$data['last_update'] = $data['respon_time'];


		$insert_app_corrective_log_data = DB::table('app_corrective_log')
		->insert(
			[
				'corrective_id'=>$data['corrective_id'].'',
				'user_nik'=>$data['user_nik'],
				'user_cn'=>$data['user_cn'],
				'status'=>$data['status'],
				'status_desc'=>@$data['status_desc'],
				'description'=>$data['description'],
				'last_update'=>$data['last_update'],
				'is_sync'=> '0',
			]
		);

		if (!$insert_app_corrective_log_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		$user_to_data = DB::table('users')
		->select('*')
		->where('username','=',$app_corrective_data->send_by_cn)
		->first();
		$coData = @$app_corrective_data->send_by_cn.' '.@$user_to_data->firebase_token;


		$notificationController = new NotificationController;

		$tmp = $notificationController->setNotificationV1($data['user_cn'], $app_corrective_data->send_by_cn, 'FMC_ACCEPT_TICKET_CORRECTIVE', 'corrective_id', $corrective_id, 'Tiket Corrective', 'FMC_ACCEPT_TICKET_CORRECTIVE', $data['user_cn'].' dari '.$users_data->fmc.' menyatakan bahwa tugas telah diterima',2,'CORRECTIVE');
			$to_token_id = array();
			array_push($to_token_id,@$user_to_data->firebase_token);
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.@$users_data->fmc.' menyatakan bahwa tugas telah di diterima',$to_token_id,2,$data['corrective_id'],'RTPO_SEND_CORRECTIVE_TICKET');


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}
	public function appSetStatusResolveCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id'); 
		$resolve_cn = $request->input('resolve_by'); #diisi username dan dapatkan nik 
		$resolve_time = date('Y-m-d H:i:s');
		$resolve_desc = $request->input('description'); 
		$request_status = '2';


		$users_data = DB::table('users')
		->select('*')
		->where('username','=',$resolve_cn)
		->first();

		if ($users_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		$start_date = new DateTime($app_corrective_data->corrective_date);
		$end_date = new DateTime($resolve_time);
		$interval= $start_date->diff($end_date);

		// $waktu_pengerjaan = $interval->h;
		// if ($app_corrective_data->corrective_type=='CRITICAL') {
		// 	$time_limit = 4;
		// }else if ($app_corrective_data->corrective_type=='MINOR') {
		// 	$time_limit = 6;
		// }

		// $batas_pengerjaan = $time_limit;

		// if ($waktu_pengerjaan<$batas_pengerjaan) {
		// 	$overdue = 0;
		// }else{
		// 	$overdue = 1;
		// 	if (@$app_corrective_data->request_status==6) {
		// 		$overdue = 0;
		// 	}
		// }



		$data['resolve_nik'] = $users_data->id;
		$data['update_cn'] = $users_data->name;
		$data['resolve_cn'] = $resolve_cn;
		$data['resolve_time'] = $resolve_time;
		// $data['resolve_desc'] = $resolve_desc;
		$data['resolve_desc'] = @$resolve_desc;
		$data['request_status'] = $request_status;
		$data['overdue_flag'] = @$overdue;

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[
				// 'resolve_nik'=> $data['resolve_nik'], update data pending jadi null semua

				// 'pending_nik'=> null,
				// 'pending_cn'=> null,
				// 'pending_time'=> null,
				// 'pending_desc'=> null,

				// 'rtpo_pending_nik'=> null,
				// 'rtpo_pending_desc'=> null,
				// 'rtpo_pending_cn'=> null,
				// 'rtpo_pending_time'=> null,
				// 'rtpo_pending_status'=> null,

				// 'resolve_nik'=> $data['resolve_nik'],
				// 'resolve_cn'=> $data['resolve_cn'],
				// 'resolve_time'=> $data['resolve_time'],
				'update_by'=>@$data['resolve_nik'],
				'update_cn'=>@$data['update_cn'],
				'reason'=> @$data['resolve_desc'],
				'request_status'=> $data['request_status'],
				// 'overdue_flag'=> $data['overdue_flag'],
				'last_update'=>$data['resolve_time'],
				'is_sync'=> '0',
			]
		);
		if (!$update_corrective_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			return response($res);
		}

		$data['corrective_id'] = $corrective_id.'';
		$data['user_nik'] = $data['resolve_nik'];
		$data['user_cn'] = $data['resolve_cn'];
		$data['status'] = $data['request_status'];

		$status_corrective = DB::table('status_corrective')
		->select('*')
		->where('status_id','=',$data['status'])
		->first();
		$data['status_desc'] = @$status_corrective->status_desc;

		$data['description'] = @$data['resolve_desc'];
		$data['last_update'] = $data['resolve_time'];


		#dan insert ke tabel "app_corrective_log" 
		$insert_app_corrective_log_data = DB::table('app_corrective_log')
		->insert(
			[
				'corrective_id'=>$data['corrective_id'].'',
				// 'unique_id'=>@$app_corrective_data->unique_id,
				'user_nik'=>$data['user_nik'],
				'user_cn'=>$data['user_cn'],
				'status'=>$data['status'],
				'status_desc'=>@$data['status_desc'],
				'description'=>@$data['description'],
				'last_update'=>$data['last_update'],
				'is_sync'=> '0',
			]
		);

		if (!$insert_app_corrective_log_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
			return response($res);
		}

		//get all user dimana fmc_id nya == $data['fmc_id']
		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		$user_to_data = DB::table('users')
		->select('*')
		->where('username','=',$app_corrective_data->send_by_cn)
		->first();
		$coData = @$app_corrective_data->send_by_cn.' '.@$user_to_data->firebase_token;


		$notificationController = new NotificationController;

        // $result = json_decode($users_data, true);
        // foreach ($result as $param => $row) {
		$tmp = $notificationController->setNotificationV1($data['user_cn'], $app_corrective_data->send_by_cn, 'FMC_RESOLVE_TICKET_CORRECTIVE', 'corrective_id', $corrective_id, 'Tiket Corrective', 'FMC_RESOLVE_TICKET_CORRECTIVE', $data['user_cn'].' dari '.$users_data->fmc.' menyatakan bahwa tugas telah di selesaikan',2,'CORRECTIVE');
		// }
		// $topic = '/topics/'.$this->checkMyFMCtopic($data['fmc_id']);

			// if (!is_null($user_to_data->firebase_token)) {
			// 	# code...
			// 	$notificationController->sendNotifFast('Tiket Corrective', $data['user_cn'].' dari '.$users_data->fmc.' menyatakan bahwa tugas telah di selesaikan',$user_to_data->firebase_token,'corrective_id',$data['corrective_id'],'RTPO_SEND_CORRECTIVE_TICKET');
			// }
			$to_token_id = array();
			array_push($to_token_id,@$user_to_data->firebase_token);
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.@$users_data->fmc.' menyatakan bahwa tugas telah di selesaikan',$to_token_id,2,$data['corrective_id'],'RTPO_SEND_CORRECTIVE_TICKET');

		//notif telegram
		$admin_fmc_data = DB::table('users')
		->select('*')
		->where('fmc_id','=',$users_data->fmc_id)
		->where('cluster','=',$users_data->cluster)
		->where('chat_id','!=',null)
      	->where('chat_id','!=',"")
		->get();

		foreach ($admin_fmc_data as $param) {
			if (@$param->username!=null) {
				$site_id = $app_corrective_data->site_id;
				$site_name = $app_corrective_data->site_name;
				$text_telegram = "[ <b>TIKET CORRECTIVE</b> ] \nHalo, \nTiket Corrective untuk site ".$site_id." - ".$site_name." sudah diselesaikan.\nTerima Kasih.\n\n-NGSemeru Team-";

				$date_now = date('Y-m-d H:i:s');
		        $insertQueueTelegram = DB::table('queue_telegram')   
		        ->insert(
		          [
		            'subject' => "TicketCorrectiveDone",
		            'message' => @$text_telegram,
		            'ticket_id' => @$corrective_id,
		            'chat_id' => @$param->chat_id,

		            'send_to' => @$param->username,
		            'fmc_id' => @$param->fmc_id,
		            'cluster_id' => @$param->cluster_id,
		            'rtpo_id' => @$param->rtpo_id,

		            'create_at' => $date_now,

		          ]
		        );
	    	}
    	}

		// ()> $type		: FMC_RESOLVE_TICKET_CORRECTIVE													//fmc menyatakan bahwa tugas telah di selesaikan
		// ()> $type_id	: 
		// ()> $type_name	: corrective_id
		// ()> $to_token_id: #1user #user_rtpo_terkait yang ada di tiket korektif tersebut
		// ()> $body		: (user fmc ini) dari (fmc ini) menyatakan bawha tugas telah di selesaikan
		// ()> $title		: FMC_RESOLVE_TICKET_CORRECTIVE

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		$res['co data'] = @$coData;
		$res['fbc'] = @$tmp_fb;
		return response($res);
	}

	public function appRTPOSetStatusCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$rtpo_resolve_time = date('Y-m-d H:i:s');

		$web = $request->input('web');

		if ($web==null) {
			$corrective_id = $request->input('corrective_id'); 
			$rtpo_resolve_cn = $request->input('response_by'); # diisi username dan dapatkan nik 
			$desc = $request->input('description'); 
			# 4: approve resolve, 5: reject resolve,
			$request_status = $request->input('request_status'); 
			$rtpo_resolve_time = date('Y-m-d H:i:s');
			if ($request_status==4) {
				$rtpo_resolve_status=1;
			}else if ($request_status==5) {
				$rtpo_resolve_status=0;
			}
			// $description = $request->input('description'); # optional

			$users_data = DB::table('users')
			->select('*')
			->where('username','=',$rtpo_resolve_cn)
			->first();

			if ($users_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
				return response($res);
			}

			$data['rtpo_resolve_nik'] = $users_data->id;
			$data['rtpo_resolve_cn'] = $rtpo_resolve_cn;
			$data['respon_nik'] = $users_data->name;
			$data['rtpo_resolve_time'] = $rtpo_resolve_time;
			$data['rtpo_resolve_status'] = $rtpo_resolve_status.'';
			$data['request_status'] = $request_status;
			$data['end_status'] = $rtpo_resolve_status;

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$corrective_id)
			->first();

			$data['overdue_flag'] = 0;
			$end_corrective_date = @$app_corrective_data->end_corrective_date;
			if (@$end_corrective_date<$rtpo_resolve_time) {
				$data['overdue_flag'] = 1;
			}

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$corrective_id)
			->update(
				[
					// 'rtpo_resolve_nik'=> $data['rtpo_resolve_nik'],
					// 'rtpo_resolve_cn'=> $data['rtpo_resolve_cn'],
					// 'rtpo_resolve_time'=> $data['rtpo_resolve_time'],
					'reason'=>@$desc,
					'update_by'=>@$data['respon_nik'],
					'update_cn'=>@$data['update_cn'],
					'last_update'=>$data['rtpo_resolve_time'],
					// 'rtpo_resolve_status'=> $data['rtpo_resolve_status'],
					'request_status'=> $data['request_status'],
					'end_status'=> $data['end_status'],

					'overdue_flag'=> $data['overdue_flag'],

					'is_sync'=> '0',
				]
			);
			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}


			$data['corrective_id'] = $corrective_id.'';
			$data['user_nik'] = $data['rtpo_resolve_nik'];
			$data['user_cn'] = $data['rtpo_resolve_cn'];
			$data['status'] = $data['request_status'];

			$status_corrective = DB::table('status_corrective')
			->select('*')
			->where('status_id','=',$data['status'])
			->first();
			$data['status_desc'] = @$status_corrective->status_desc;

			$data['description'] = $desc;
			$data['last_update'] = $data['rtpo_resolve_time'];


			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$data['corrective_id'])
			->first();

			#dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$app_corrective_data->unique_id,
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>@$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> '0',
				]
			);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}
		}else{

			$data_corective = $request->input('data');
			// return response($data_corective);

				// return response(null);


			$data['corrective_id'] = $data_corective['corrective_id'];
			$request_status = $data_corective['request_status'];
			$log_data = $data_corective['log'];
			unset($data_corective['log']);

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$data_corective['corrective_id']) // ganti ke corrective id
			->update($data_corective);


			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$data_corective['corrective_id'])
			->first();

			$data['overdue_flag'] = @$data_corective['overdue_flag'];
			if (@$data_corective['overdue_flag']==null) {
				$data['overdue_flag'] = 0;
				$end_corrective_date = @$app_corrective_data->end_corrective_date;
				if (@$end_corrective_date<=$rtpo_resolve_time) {
					$data['overdue_flag'] = 1;
				}
			}


			// $data['overdue_flag'] = @$data_corective['overdue_flag']

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$data['corrective_id'])
			->update(
				[
					'overdue_flag'=> @$data['overdue_flag'],
					'last_update'=> @$rtpo_resolve_time,
				]
			);

			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				$res['overdue_flag'] = @$data['overdue_flag'];
				$res['last_update'] = @$data_corective['last_update'];
				return response($res);
			}


			 $app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$data['corrective_id']) // ganti ke corrective id
			->first();

			$log_data['corrective_id'] = $app_corrective_data->corrective_id;

			$data['user_cn'] = $log_data['user_cn'];
			$data['corrective_id'] = $log_data['corrective_id'];
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert($log_data);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}

		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$data['corrective_id'])
		->first();

		// //get all user dimana fmc_id nya == $data['fmc_id']
		// $users_data = DB::table('users')
		// ->select('*')
		// ->where('fmc_id','=',$app_corrective_data->fmc_id)
		// ->where('cluster','=',$app_corrective_data->cluster_fmc)
		// ->get();

		if ($app_corrective_data->target_role_id == 11) {
			$users_data = DB::table('user_tsra as ut')
			->join('users as u', 'ut.tsra_username', 'u.username')
			->select('*')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}elseif ($app_corrective_data->target_role_id == 7) {
			$users_data = DB::table('user_mbp_mt as ut')
			->join('users as u', 'ut.mbp_mt_username', 'u.username')
			->select('*', 'ut.mbp_mt_username as tsra_username')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}

		if ($request_status==4) {

			$notificationController = new NotificationController;

			$to_token_id = array();
			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' menyatakan bahwa pekerjaan fmc anda telah di setujui',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);

			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' menyatakan bahwa pekerjaan fmc anda telah di setujui',$to_token_id,2,$data['corrective_id'],'RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC');

		}else if ($request_status==5) {

			$notificationController = new NotificationController;

			$to_token_id = array();
			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' menyatakan bahwa pekerjaan fmc anda tidak disetujui, harap dikerjakan kembali',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' menyatakan bahwa pekerjaan fmc anda tidak disetujui, harap dikerjakan kembali',$to_token_id,2,$data['corrective_id'],'RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC');
		}
		#4 maka approve, 5 reject
		// (v)> 3. 
		// 	()> $type		: RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC								//rtpo menyetujui hasil kerja fmc yang mereka katakan telah terselesaikan
		// 	()> $type_id	: 
		// 	()> $type_name	: corrective_id
		// 	()> $to_token_id: #allFmcTerkait
		// 	()> $body		: (user rtpo ...) dari (rtpo ...) menyatakan bahwa pekerjaan fmc anda telah di setujui
		// 	()> $title		: RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC

		// (v)> 4. 
		// 	()> $type		: RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC 								//rtpo mereject hasil kerja fmc yang mereka katakan telah terselesaikan
		// 	()> $type_id	: 
		// 	()> $type_name	: corrective_id
		// 	()> $to_token_id: #allFmcTerkait
		// 	()> $body		: (user rtpo ...) dari (rtpo ...) menyatakan bahwa pekerjaan fmc anda tidak disetujui, harap dikerjakan kembali
		// 	()> $title		: RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		$res['fbc'] = $tmp_fb;
		$res['users_data'] = $users_data;
		$res['app_corrective_data->target_role_id'] = $app_corrective_data->target_role_id;
		return response($res);
	}	

	public function listCorrectiveFrom(Request $request){

		$rtpo_id = $request->input('rtpo_id');

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('rtpo_id','=',$rtpo_id)
		// ->where('request_status','!=','4')
		->where('end_status','=','0')
		->orderBy('corrective_date','desc')
		->get();

		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;
			$data[$param]['site_id'] = $corrective_result[$param]['site_id'] ;
			$data[$param]['site_name'] = $corrective_result[$param]['site_name'] ;

			if ($corrective_result[$param]['request_status'] == '0') {
				$c_status = 'SUBMIT';
			}else if ($corrective_result[$param]['request_status'] == '1') {
				$c_status = 'ACCEPT';
			}else if ($corrective_result[$param]['request_status'] == '2') {
				$c_status = 'RESOLVED';
			}else if ($corrective_result[$param]['request_status'] == '3') {
				$c_status = 'PENDING';
			}else if ($corrective_result[$param]['request_status'] == '4') {
				$c_status = 'APPROVED';
			}else if ($corrective_result[$param]['request_status'] == '5') {
				$c_status = 'REJECT';
			}else if ($corrective_result[$param]['request_status'] == '6') {
				$c_status = 'APPROVED PENDING';
			}else if ($corrective_result[$param]['request_status'] == '7') {
				$c_status = 'REJECT PENDING';
			}else if ($corrective_result[$param]['request_status'] == '8') {
				$c_status = 'CANCEL CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '9') {
				$c_status = 'OPEN PENDING CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '10') {
				$c_status = 'UPGRADE TO WO';
			}else if ($corrective_result[$param]['request_status'] == '11') {
				$c_status = 'WO ACCEPTED';
			}else if ($corrective_result[$param]['request_status'] == '12') {
				$c_status = 'WO REJECTED';
			}else if ($corrective_result[$param]['request_status'] == '13') {
				$c_status = 'SPMS';
			}

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function listCorrectiveFromFmc(Request $request){

		$fmc_id = $request->input('fmc_id');

		$username = $request->input('username');

		if ($username==null) {
			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('fmc_id','=',$fmc_id)
		// ->where('request_status','!=','4')
			->where('end_status','=','0')
			->orderBy('corrective_date','desc')
			->get();
		}else{



			$user_data = DB::table('user_tsra')
			->select('*')
			->where('tsra_username','=',$username)
			->first();

			$cluster = @$user_data->cluster;
			$target_role_id = 11;

			if ($cluster==null) {
				$user_data = DB::table('user_mbp_mt')
				->select('*')
				->where('mbp_mt_username','=',$username)
				->first();

				$cluster = @$user_data->cluster;
				$target_role_id = 7;

			}

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('fmc_id','=',$fmc_id)
			->where('target_role_id','=',$target_role_id)
			->where('cluster','=',$cluster)
			->where('end_status','=',0)
			->get();
		}


		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;
			$data[$param]['cluster'] = $corrective_result[$param]['cluster'] ;
			$data[$param]['cluster_id'] = $corrective_result[$param]['cluster_id'] ;
			$data[$param]['regional'] = $corrective_result[$param]['regional'] ;

			if ($corrective_result[$param]['request_status'] == '0') {
				$c_status = 'SUBMIT';
			}else if ($corrective_result[$param]['request_status'] == '1') {
				$c_status = 'ACCEPT';
			}else if ($corrective_result[$param]['request_status'] == '2') {
				$c_status = 'RESOLVED';
			}else if ($corrective_result[$param]['request_status'] == '3') {
				$c_status = 'PENDING';
			}else if ($corrective_result[$param]['request_status'] == '4') {
				$c_status = 'APPROVED';
			}else if ($corrective_result[$param]['request_status'] == '5') {
				$c_status = 'REJECT';
			}else if ($corrective_result[$param]['request_status'] == '6') {
				$c_status = 'APPROVED PENDING';
			}else if ($corrective_result[$param]['request_status'] == '7') {
				$c_status = 'REJECT PENDING';
			}else if ($corrective_result[$param]['request_status'] == '8') {
				$c_status = 'CANCEL CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '9') {
				$c_status = 'OPEN PENDING CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '10') {
				$c_status = 'UPGRADE TO WO';
			}else if ($corrective_result[$param]['request_status'] == '11') {
				$c_status = 'WO ACCEPTED';
			}else if ($corrective_result[$param]['request_status'] == '12') {
				$c_status = 'WO REJECTED';
			}else if ($corrective_result[$param]['request_status'] == '13') {
				$c_status = 'SPMS';
			}

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['target_role_id'] = $target_role_id;
		$res['data'] = $data;
		return response($res);
	}

	public function detilCorrectiveFrom(Request $request){

		$corrective_id = $request->input('corrective_id');

		$app_corrective_data = DB::table('corrective')
		->join('users as u', 'corrective.send_by_cn', 'u.username')
		->select('corrective.*', 'u.name')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		$site_data = DB::table('site')
		->select('latitude','longitude')
		->where('site_id',$app_corrective_data->site_id)
		->first();


		$data['corrective_id'] = $app_corrective_data->corrective_id;
		$data['site_id'] = $app_corrective_data->site_id;
		$data['site_name'] = $app_corrective_data->site_name;
		$data['latitude'] = $site_data->latitude;
		$data['longitude'] = $site_data->longitude;
		$data['description'] = $app_corrective_data->description;
		$data['corrective_type'] = $app_corrective_data->corrective_type;
		$data['category'] = $app_corrective_data->category;
		$data['corrective_date'] = @$this->setDatedMYHis($app_corrective_data->corrective_date.'');
		$data['rtpo_id'] = $app_corrective_data->rtpo_id;
		$data['rtpo'] = $app_corrective_data->rtpo;
		$data['request_status'] = $app_corrective_data->request_status;

		if ($data['request_status'] == '0') {
			$c_status = 'SUBMIT';
		}else if ($data['request_status'] == '1') {
			$c_status = 'ACCEPT';
		}else if ($data['request_status'] == '2') {
			$c_status = 'RESOLVED';
		}else if ($data['request_status'] == '3') {
			$c_status = 'PENDING';
		}else if ($data['request_status'] == '4') {
			$c_status = 'APPROVED';
		}else if ($data['request_status'] == '5') {
			$c_status = 'REJECT';
		}else if ($data['request_status'] == '6') {
			$c_status = 'APPROVED PENDING';
		}else if ($data['request_status'] == '7') {
			$c_status = 'REJECT PENDING';
		}else if ($data['request_status'] == '8') {
			$c_status = 'CANCEL CORRECTIVE';
		}else if ($data['request_status'] == '9') {
			$c_status = 'OPEN PENDING CORRECTIVE';
		}else if ($data['request_status'] == '10') {
			$c_status = 'UPGRADE TO WO';
		}else if ($data['request_status'] == '11') {
			$c_status = 'WO ACCEPTED';
		}else if ($data['request_status'] == '12') {
			$c_status = 'WO REJECTED';
		}else if ($data['request_status'] == '13') {
			$c_status = 'spms';
		}
		$data['request_status'] = $c_status;		
		$data['fmc_id'] = $app_corrective_data->fmc_id;
		$data['fmc'] = $app_corrective_data->fmc;
		$data['send_by'] = $app_corrective_data->send_by;
		// $data['send_by_cn'] = $app_corrective_data->send_by_cn;
		$data['send_by_cn'] = $app_corrective_data->name;
		$data['cluster'] = $app_corrective_data->cluster;
		$data['cluster_id'] = $app_corrective_data->cluster_id;
		$data['regional'] = $app_corrective_data->regional;
		
		// $data['pending_nik'] = $app_corrective_data->pending_nik;
		// $data['pending_cn'] = $app_corrective_data->pending_cn;
		// $data['pending_time'] = $app_corrective_data->pending_time;
		// $data['pending_desc'] = $app_corrective_data->rtpo_pending_nik;
		
		// $data['rtpo_pending_cn'] = $app_corrective_data->rtpo_pending_cn;
		// $data['rtpo_pending_time'] = $app_corrective_data->rtpo_pending_time;
		// $data['rtpo_pending_status'] = $app_corrective_data->rtpo_pending_status;
		
		// $data['resolve_nik'] = $app_corrective_data->resolve_nik;
		// $data['resolve_cn'] = $app_corrective_data->resolve_cn;
		// $data['resolve_time'] = $app_corrective_data->resolve_time;
		// $data['resolve_desc'] = $app_corrective_data->resolve_desc;
		
		// $data['rtpo_resolve_nik'] = $app_corrective_data->rtpo_resolve_nik;
		// $data['rtpo_resolve_cn'] = $app_corrective_data->rtpo_resolve_cn;
		// $data['rtpo_resolve_time'] = $app_corrective_data->rtpo_resolve_time;
		// $data['rtpo_resolve_status'] = $app_corrective_data->rtpo_resolve_status;

		$data['overdue_flag'] = $app_corrective_data->overdue_flag;

		$corrective_log_data = DB::table('app_corrective_log')
		->join('users as u', 'app_corrective_log.user_cn', 'u.username')
		->select('app_corrective_log.*', 'u.name as user_cn')
		->where('corrective_id','=',$corrective_id)
		->orderBy('log_id', 'desc')
		->get();

		$x=0;
		foreach ($corrective_log_data as $key) {
			$corrective_log_data[$x]->last_update = @$this->setDatedMYHis($key->last_update);
			$corrective_log_data[$x]->last_sync = @$this->setDatedMYHis($key->last_sync);
			$x=$x+1;
		}
		// $log_result = json_decode($corrective_log_data, true);

		// $tmp['status'] = $corrective_log_data->status;

		// if ($log_result['status'] == '0') {
		// 	$c_status = 'SUBMIT';
		// }else if ($log_result['status'] == '1') {
		// 	$c_status = 'ACCEPT';
		// }else if ($log_result['status'] == '2') {
		// 	$c_status = 'RESOLVED';
		// }else if ($log_result['status'] == '3') {
		// 	$c_status = 'PENDING';
		// }else if ($log_result['status'] == '4') {
		// 	$c_status = 'APPROVED';
		// }else if ($log_result['status'] == '5') {
		// 	$c_status = 'REJECT';
		// }else if ($log_result['status'] == '6') {
		// 	$c_status = 'APPROVED PENDING';
		// }else if ($log_result['status'] == '7') {
		// 	$c_status = 'REJECT PENDING';
		// }else if ($log_result['status'] == '8') {
		// 	$c_status = 'CANCEL CORRECTIVE';
		// }

		// $log_result['status'] = $c_status;

		$data['last_log'] = $corrective_log_data;



		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function detilCorrectiveFromWeb(Request $request){

		$corrective_id = $request->input('corrective_id');

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		// $data[$param] = $row;


		// $log_corrective_data = DB::table('app_corrective_log')
		// ->select('*')
		// ->where('corrective_id','=',$row['corrective_id'])
		// ->get();

		$data = $app_corrective_data;

		// $data['corrective_log'] = $log_corrective_data;



		$corrective_log_data = DB::table('app_corrective_log')
		->where('corrective_id','=',$corrective_id)
		->orderBy('log_id', 'desc')
		->get();

		$data->last_log = $corrective_log_data;



		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function listHistoryCorrectiveFrom(Request $request){

		$rtpo_id = $request->input('rtpo_id');

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('rtpo_id','=',$rtpo_id)
		// ->where('request_status','!=','4')
		->where('end_status','=','1')
		->orderBy('last_update', 'desc')
		->limit(50)
		->get();

		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;

			if ($corrective_result[$param]['request_status'] == '0') {
				$c_status = 'SUBMIT';
			}else if ($corrective_result[$param]['request_status'] == '1') {
				$c_status = 'ACCEPT';
			}else if ($corrective_result[$param]['request_status'] == '2') {
				$c_status = 'RESOLVED';
			}else if ($corrective_result[$param]['request_status'] == '3') {
				$c_status = 'PENDING';
			}else if ($corrective_result[$param]['request_status'] == '4') {
				$c_status = 'APPROVED';
			}else if ($corrective_result[$param]['request_status'] == '5') {
				$c_status = 'REJECT';
			}else if ($corrective_result[$param]['request_status'] == '6') {
				$c_status = 'APPROVED PENDING';
			}else if ($corrective_result[$param]['request_status'] == '7') {
				$c_status = 'REJECT PENDING';
			}else if ($corrective_result[$param]['request_status'] == '8') {
				$c_status = 'CANCEL CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '9') {
				$c_status = 'OPEN PENDING CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '10') {
				$c_status = 'UPGRADE TO WO';
			}else if ($corrective_result[$param]['request_status'] == '11') {
				$c_status = 'WO ACCEPTED';
			}else if ($corrective_result[$param]['request_status'] == '12') {
				$c_status = 'WO REJECTED';
			}else if ($corrective_result[$param]['request_status'] == '13') {
				$c_status = 'SPMS';
			}

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function listHistoryCorrectiveFromFmc(Request $request){

		$fmc_id = $request->input('fmc_id');

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('fmc_id','=',$fmc_id)
		// ->where('request_status','!=','4')
		->where('end_status','=','1')
		->orderBy('last_update', 'desc')
		->get();

		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;

			if ($corrective_result[$param]['request_status'] == '0') {
				$c_status = 'SUBMIT';
			}else if ($corrective_result[$param]['request_status'] == '1') {
				$c_status = 'ACCEPT';
			}else if ($corrective_result[$param]['request_status'] == '2') {
				$c_status = 'RESOLVED';
			}else if ($corrective_result[$param]['request_status'] == '3') {
				$c_status = 'PENDING';
			}else if ($corrective_result[$param]['request_status'] == '4') {
				$c_status = 'APPROVED';
			}else if ($corrective_result[$param]['request_status'] == '5') {
				$c_status = 'REJECT';
			}else if ($corrective_result[$param]['request_status'] == '6') {
				$c_status = 'APPROVED PENDING';
			}else if ($corrective_result[$param]['request_status'] == '7') {
				$c_status = 'REJECT PENDING';
			}else if ($corrective_result[$param]['request_status'] == '8') {
				$c_status = 'CANCEL CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '9') {
				$c_status = 'OPEN PENDING CORRECTIVE';
			}else if ($corrective_result[$param]['request_status'] == '10') {
				$c_status = 'UPGRADE TO WO';
			}else if ($corrective_result[$param]['request_status'] == '11') {
				$c_status = 'WO ACCEPTED';
			}else if ($corrective_result[$param]['request_status'] == '12') {
				$c_status = 'WO REJECTED';
			}else if ($corrective_result[$param]['request_status'] == '13') {
				$c_status = 'SPMS';
			}

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}


	public function sendPendingCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id'); 
		$pending_desc = $request->input('pending_desc'); 
		$pending_cn = $request->input('send_by'); # diisi username dan dapatkan nik 
			// $pending_nik = //diisi nik
		$pending_time = date('Y-m-d H:i:s');
		$request_status = '3';


		$users_data = DB::table('users')
		->select('*')
		->where('username','=',$pending_cn)
		->first();

		if ($users_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
			return response($res);
		}

		$data['pending_nik'] = $users_data->id;
		$data['pending_cn'] = $pending_cn;
		$data['pending_time'] = $pending_time;
		$data['pending_desc'] = $pending_desc;
		$data['request_status'] = $request_status;

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[
				// 'pending_nik'=> $data['pending_nik'], uodate data resolved jadi null semua

				'resolve_nik'=> null,
				'resolve_cn'=> null,
				'resolve_time'=> null,
				'resolve_desc'=> null,
				'overdue_flag'=> null,

				'rtpo_resolve_nik'=> null,
				'rtpo_resolve_cn'=> null,
				'rtpo_resolve_time'=> null,
				'rtpo_resolve_status'=> null,
				'rtpo_resolve_desc'=> null,

				'pending_nik'=> $data['pending_nik'],
				'pending_cn'=> $data['pending_cn'],
				'pending_time'=> $data['pending_time'],
				'last_update'=>$data['pending_time'],
				'request_status'=> $data['request_status'],
				'pending_desc'=> $data['pending_desc'],
				'is_sync'=> '0',
			]
		);
		if (!$update_corrective_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			return response($res);
		}


		$data['corrective_id'] = $corrective_id.'';
		$data['user_nik'] = $data['pending_nik'];
		$data['user_cn'] = $data['pending_cn'];
		$data['status'] = $data['request_status'];

		$status_corrective = DB::table('status_corrective')
		->select('*')
		->where('status_id','=',$data['status'])
		->first();
		$data['status_desc'] = @$status_corrective->status_desc;

		$data['description'] = $data['pending_desc'];
		$data['last_update'] = $data['pending_time'];

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		#dan insert ke tabel "app_corrective_log" 
		$insert_app_corrective_log_data = DB::table('app_corrective_log')
		->insert(
			[
				'corrective_id'=>$data['corrective_id'].'',
				// 'unique_id'=>@$app_corrective_data->unique_id,
				'user_nik'=>$data['user_nik'],
				'user_cn'=>$data['user_cn'],
				'status'=>$data['status'],
				'status_desc'=>@$data['status_desc'],
				'description'=>$data['description'],
				'last_update'=>$data['last_update'],
				'is_sync'=> '0',
			]
		);

		if (!$insert_app_corrective_log_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
			return response($res);
		}


		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		// $user_to_data = DB::table('users')
		// ->select('*')
		// ->where('username','=',$app_corrective_data->send_by_cn)
		// ->first();

		$users_data = DB::table('user_rtpo as ur')
		->join('users as u', 'ur.username', 'u.username')
		->select('*')
		->where('ur.rtpo_id','=',@$app_corrective_data->rtpo_id)
		// ->where('ut.cluster','=',$data['cluster_fmc'])
		->get();


		$notificationController = new NotificationController;

		$to_token_id = array();
        $result = json_decode($users_data, true);
        foreach ($result as $param => $row) {
        	array_push($to_token_id,@$row['firebase_token']);
        }

		$tmp = $notificationController->setNotificationV1($data['user_cn'], $app_corrective_data->send_by_cn, 'FMC_SEND_PENDING_TICKET_CORRECTIVE', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'FMC_SEND_PENDING_TICKET_CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->fmc.' mengajukan pending terhadap tiket anda',2,'CORRECTIVE');
		
		// $to_token_id = array();
		// array_push($to_token_id,@$user_to_data->firebase_token);

		// if (!is_null($user_to_data->firebase_token)) {
		// 	$notificationController->sendNotifFast('Tiket Corrective', $data['user_cn'].' dari '.$users_data->fmc.' mengajukan pending terhadap tiket anda',$user_to_data->firebase_token,'corrective_id',$data['corrective_id'],'FMC_SEND_PENDING_TICKET_CORRECTIVE');
		// }

		// ()> $type		: FMC_SEND_PENDING_TICKET_CORRECTIVE 											//fmc menyatakan pending terhadap tiket korektiv yang di ajukan oleh rtpo
		// ()> $type_id	: 
		// ()> $type_name	: corrective_id
		// ()> $to_token_id: #1user #user_rtpo_terkait yang ada di tiket korektif tersebut
		// ()> $body		: (user fmc ...) dari (fmc ...) mengajukan pending terhadap tiket anda (user rtpo)
		// ()> $title		: FMC_SEND_PENDING_TICKET_CORRECTIVE


		$fbc = new FireBaseController;
		$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->fmc.' mengajukan pending terhadap tiket anda',$to_token_id,2,$data['corrective_id'],'FMC_SEND_PENDING_TICKET_CORRECTIVE');



		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		// $res['fb'] = $tmp_fb;
		// $res['tfb'] = $to_token_id;
		// $res['user data'] = $users_data;
		return response($res);
	}

	public function responPendingCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");


		$web = $request->input('web');

		if ($web==null) {

			$corrective_id = $request->input('corrective_id'); 
			$rtpo_pending_desc = $request->input('rtpo_pending_desc'); 
			$rtpo_pending_cn = $request->input('respon_by'); # diisi username dan dapatkan nik 
			$request_status = $request->input('request_status'); // 6 approve pending, 7 reject pending
			$rtpo_pending_time = date('Y-m-d H:i:s');

			if ($request_status == 6) {
				# code...
				$rtpo_pending_status = 0;
				$end_status = 0;
			}else if ($request_status == 7) {
				# code...
				$rtpo_pending_status = 1;
				$end_status = 0;
			}

			$users_data = DB::table('users')
			->select('*')
			->where('username','=',$rtpo_pending_cn)
			->first();

			if ($users_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
				return response($res);
			}

			$data['rtpo_pending_nik'] = $users_data->id;
			$data['rtpo_pending_cn'] = $rtpo_pending_cn;
			$data['rtpo_pending_time'] = $rtpo_pending_time;
			$data['rtpo_pending_desc'] = $rtpo_pending_desc;
			$data['rtpo_pending_status'] = $rtpo_pending_status.'';
			$data['request_status'] = $request_status;
			$data['end_status'] = $end_status.'';


			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$corrective_id)
			->update(
				[
					'rtpo_pending_nik'=> $data['rtpo_pending_nik'],
					'rtpo_pending_cn'=> $data['rtpo_pending_cn'],
					'rtpo_pending_time'=> $data['rtpo_pending_time'],
					'last_update'=>$data['rtpo_pending_time'],
					'rtpo_pending_status'=> $data['rtpo_pending_status'],
					'request_status'=> $data['request_status'],
					'end_status'=> $end_status,
					'is_sync'=> '0',
				]
			);
			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}

			$data['corrective_id'] = $corrective_id.'';
			$data['user_nik'] = $data['rtpo_pending_nik'];
			$data['user_cn'] = $data['rtpo_pending_cn'];
			$data['status'] = $data['request_status'];

			$status_corrective = DB::table('status_corrective')
			->select('*')
			->where('status_id','=',$data['status'])
			->first();
			$data['status_desc'] = @$status_corrective->status_desc;

			$data['description'] = $data['rtpo_pending_desc'];
			$data['last_update'] = $data['rtpo_pending_time'];

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$corrective_id)
			->first();

			$data['unique_id'] = @$app_corrective_data->unique_id;
			// dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$app_corrective_data->unique_id,
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>@$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> '0',
				]
			);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}


		}else{

			$data_corective = $request->input('data');


			$data['corrective_id'] = @$data_corective['corrective_id'];
			$request_status = $data_corective['request_status'];
			$log_data = $data_corective['log'];
			unset($data_corective['log']);

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',@$data_corective['corrective_id']) //ganti ke corrective id
			->update($data_corective);

			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}


			 $app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',@$data['corrective_id'])
			->first();

			$log_data['corrective_id'] = $app_corrective_data->corrective_id;

			$data['user_cn'] = $log_data['user_cn'];
			$data['corrective_id'] = $log_data['corrective_id'];
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert($log_data);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',@$data['corrective_id']) // ganti ke corrective id
		->first();

		// $user_to_data = DB::table('users')
		// ->select('*')
		// ->where('username','=',$app_corrective_data->send_by_cn)
		// ->get();

		// $users_data = DB::table('user_tsra as ut')
		// ->join('users as u', 'ut.tsra_username', 'u.username')
		// ->select('*')
		// ->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
		// ->where('ut.cluster','=',$app_corrective_data->cluster)
		// ->get();

		if ($app_corrective_data->target_role_id == 11) {
			$users_data = DB::table('user_tsra as ut')
			->join('users as u', 'ut.tsra_username', 'u.username')
			->select('*')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}elseif ($app_corrective_data->target_role_id == 7) {
			$users_data = DB::table('user_mbp_mt as ut')
			->join('users as u', 'ut.mbp_mt_username', 'u.username')
			->select('*', 'ut.mbp_mt_username as tsra_username')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}


			$to_token_id = array();
		if ($request_status == 6) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;
			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				// ($send_by, $send_to, $type, $name_type_id, $type_id, $title, $subject, $text)
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyetujui pengajuan pending anda',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyetujui pengajuan pending anda',$to_token_id,2,$data['corrective_id'],'RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC');

		}else if ($request_status == 7) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;

			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' tidak menyetujui pengajuan pending anda',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' tidak menyetujui pengajuan pending anda',$to_token_id,2,$data['corrective_id'],'RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC');
		}

		// (v)> 6. 
		// 	()> $type		: RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC  								//rtpo menyetujui pengajuan pending user fmc
		// 	()> $type_id	: 
		// 	()> $type_name	: corrective_id
		// 	()> $to_token_id: #1user #user_fmc_terkait
		// 	()> $body		: (user rtpo ...) dari (rtpo ...) menyetujui pengajuan pending anda
		// 	()> $title		: RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC

		// (v)> 7. 
		// 	()> $type		: RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC 								//rtpo tidak menyetujui pengajuan pending user fmc
		// 	()> $type_id	: 
		// 	()> $type_name	: corrective_id
		// 	()> $to_token_id: #1user #user_fmc_terkait
		// 	()> $body		: (user rtpo ...) dari rtpo ... tidak menyetujui pengajuan pending anda
		// 	()> $title		: RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		$res['fb'] = $tmp_fb;
		$res['tfb'] = $to_token_id;
		return response($res);
	}

	public function canceledCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");


		$web = $request->input('web');

		if ($web==null) {

			$corrective_id = $request->input('corrective_id'); 
			$canceled_by_cn = $request->input('canceled_by'); # diisi username dan dapatkan nik 
			$canceled_desc = $request->input('canceled_desc'); 
			$request_status = 8;
			$end_status = 1;
			$canceled_time = date('Y-m-d H:i:s');

			$users_data = DB::table('users')
			->select('*')
			->where('username','=',$canceled_by_cn)
			->first();

			if ($users_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
				return response($res);
			}

			$data['canceled_by_nik'] = $users_data->id.'';
			$data['update_cn'] = $users_data->name.'';
			$data['canceled_by_cn'] = $canceled_by_cn.'';
			$data['request_status'] = $request_status.'';
			$data['canceled_time'] = $canceled_time;
			$data['canceled_desc'] = $canceled_desc;
			$data['end_status'] = $end_status.''; //ini aja yang di pake

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$corrective_id)
			->update(
				[
					'update_by'=>@$data['canceled_by_nik'],
					'update_cn'=>@$data['update_cn'],
					'reason'=>@$data['canceled_desc'],
					'request_status'=> $data['request_status'],
					'end_status'=> $end_status,
					'last_update'=>$data['canceled_time'],
					'is_sync'=> '0',
				]
			);
			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}

			$data['corrective_id'] = $corrective_id.'';
			$data['user_nik'] = $data['canceled_by_nik'];
			$data['user_cn'] = $data['canceled_by_cn'];
			$data['status'] = $data['request_status'];

			$status_corrective = DB::table('status_corrective')
			->select('*')
			->where('status_id','=',$data['status'])
			->first();
			$data['status_desc'] = @$status_corrective->status_desc;

			$data['description'] = $data['canceled_desc'];
			$data['last_update'] = $data['canceled_time'];

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$corrective_id)
			->first();

			// dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$app_corrective_data->unique_id,
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>@$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> '0',
				]
			);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}

		}else{

			//==================================================


			$data_corective = $request->input('data');


			$data['corrective_id'] = @$data_corective['corrective_id'];
			$request_status = $data_corective['request_status'];
			$log_data = $data_corective['log'];
			unset($data_corective['log']);

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',@$data_corective['corrective_id'])// ganti ke corrective id
			->update($data_corective);

			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}


			 $app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',@$data['corrective_id']) // ganti ke corrective id
			->first();

			$log_data['corrective_id'] = $app_corrective_data->corrective_id;

			$data['user_cn'] = $log_data['user_cn'];
			$data['corrective_id'] = $log_data['corrective_id'];
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert($log_data);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}

			//==================================================

			// $data_corective = $request->input('data');

			// $data['unique_id'] = $data_corective['unique_id'];
			// $data['canceled_by_nik'] = $data_corective['canceled_by_nik'];
			// $data['canceled_by_cn'] = $data_corective['canceled_by_cn'];
			// $data['request_status'] = $data_corective['request_status'];
			// $data['canceled_time'] = $data_corective['canceled_time'];
			// $data['canceled_desc'] = $data_corective['canceled_desc'];
			// $data['end_status'] = $data_corective['end_status'];

			// $data['is_sync'] = $data_corective['is_sync'];
			// $data['last_sync'] = $data_corective['last_sync'];
			// $data['id_sync'] = $data_corective['id_sync'];

			// $update_corrective_data = DB::table('app_corrective')
			// ->where('corrective_id','=',$corrective_id)
			// ->update(
			// 	[
			// 		'end_status'=> $data['end_status'],
			// 		'last_update'=>$data['canceled_time'],
			// 		'is_sync'=> $data_corective['is_sync'],
			// 		'last_sync'=> $data_corective['last_sync'],
			// 		'id_sync'=> $data_corective['id_sync'],
			// 	]
			// );
			// if (!$update_corrective_data) {
			// 	$res['success'] = false;
			// 	$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			// 	return response($res);
			// }


			//  $app_corrective_data = DB::table('app_corrective')
			// ->select('*')
			// ->where('unique_id','=',$data['unique_id'])
			// ->first();

			// if ($app_corrective_data==null) {
			// 	$res['success'] = false;
			// 	$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			// 	return response($res);
			// }

			// $data['corrective_id'] = $app_corrective_data->corrective_id;
			// $data['user_nik'] = $data_corective['log']['user_nik'];
			// $data['user_cn'] = $data_corective['log']['user_cn'];
			// $data['status'] = $data_corective['log']['status'];
			// $data['description'] = $data_corective['log']['description'];
			// $data['last_update'] = $data_corective['log']['last_update'];

			// // dan insert ke tabel "app_corrective_log" 
			// $insert_app_corrective_log_data = DB::table('app_corrective_log')
			// ->insert(
			// 	[
			// 		'corrective_id'=>$data['corrective_id'].'',
			// 		'unique_id'=>$app_corrective_data->unique_id,
			// 		'user_nik'=>$data['user_nik'],
			// 		'user_cn'=>$data['user_cn'],
			// 		'status'=>$data['status'],
			// 		'description'=>$data['description'],
			// 		'last_update'=>$data['last_update'],
			// 		'is_sync'=> $data_corective['log']['is_sync'],
			// 		'last_sync'=> $data_corective['log']['last_sync'],
			// 		'id_sync'=> $data_corective['log']['id_sync'],
			// 	]
			// );

			// if (!$insert_app_corrective_log_data) {
			// 	$res['success'] = false;
			// 	$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
			// 	return response($res);
			// }

		}

		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
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


public function getDataCorrective(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    
    $end_status = $request->input('end_status');
    
    $corrective_data = DB::table('app_corrective')
    ->select('*')
    ->where('end_status','=',$end_status)
    ->get();

    // $res['success'] = true;
    // $res['message'] = 'SUCCESS';
    // $res['data'] = $corrective_data;
    // return response($res);

    $corrective_result = json_decode($corrective_data, true);

    foreach ($corrective_result as $param => $row) {

      $data[$param]['corrective_id'] = $row['corrective_id'];
      $data[$param]['site_id'] = $row['site_id'];
      $data[$param]['site_name'] = $row['site_name'];
      $data[$param]['description'] = $row['description'];

      $data[$param]['corrective_type'] = $row['corrective_type'];
      $data[$param]['request_status'] = $row['request_status'];
      $data[$param]['corrective_date'] = $row['corrective_date'];
      $data[$param]['rtpo_id'] = $row['rtpo_id'];
      $data[$param]['rtpo'] = $row['rtpo'];
      $data[$param]['regional'] = $row['regional'];
      $data[$param]['cluster_id'] = $row['cluster_id'];
      $data[$param]['cluster'] = $row['cluster'];
      $data[$param]['cluster_fmc_id'] = $row['cluster_fmc_id'];
      $data[$param]['cluster_fmc'] = $row['cluster_fmc'];

      $data[$param]['ns_id'] = $row['ns_id'];
      $data[$param]['ns'] = $row['ns'];
      $data[$param]['branch_id'] = $row['branch_id'];
      $data[$param]['branch'] = $row['branch'];
      $data[$param]['fmc_id'] = $row['fmc_id'];
      $data[$param]['fmc'] = $row['fmc'];
      $data[$param]['send_by'] = $row['send_by'];
      $data[$param]['send_by_cn'] = $row['send_by_cn'];
      $data[$param]['overdue_flag'] = $row['overdue_flag'];
      $data[$param]['pending_nik'] = $row['pending_nik'];

      $data[$param]['pending_cn'] = $row['pending_cn'];
      $data[$param]['pending_time'] = $row['pending_time'];
      $data[$param]['pending_desc'] = $row['pending_desc'];
      $data[$param]['rtpo_pending_nik'] = $row['rtpo_pending_nik'];
      $data[$param]['rtpo_pending_cn'] = $row['rtpo_pending_cn'];
      $data[$param]['rtpo_pending_time'] = $row['rtpo_pending_time'];
      $data[$param]['rtpo_pending_status'] = $row['rtpo_pending_status'];
      $data[$param]['resolve_nik'] = $row['resolve_nik'];
      $data[$param]['resolve_cn'] = $row['resolve_cn'];
      $data[$param]['resolve_time'] = $row['resolve_time'];

      $data[$param]['resolve_desc'] = $row['resolve_desc'];
      $data[$param]['rtpo_resolve_nik'] = $row['rtpo_resolve_nik'];
      $data[$param]['rtpo_resolve_cn'] = $row['rtpo_resolve_cn'];
      $data[$param]['rtpo_resolve_time'] = $row['rtpo_resolve_time'];
      $data[$param]['rtpo_resolve_status'] = $row['rtpo_resolve_status'];
      $data[$param]['end_status'] = $row['end_status'];
      $data[$param]['last_update'] = $row['last_update'];
      $data[$param]['is_sync'] = $row['is_sync'];
      $data[$param]['last_sync'] = @$row['last_sync'];
      $data[$param]['id_sync'] = @$row['id_sync'];

      $log_corrective_data = DB::table('app_corrective_log')
      ->select('*')
      ->where('corrective_id','=',$row['corrective_id'])
      ->get();

      $data[$param]['corrective_log'] = $log_corrective_data;
    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);
  }


public function getDataCorrectiveIsSync0(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    
    $site_id_tmp = $request->input('site_id');
    

    // $corrective_data = DB::table('corrective')
    // ->select('*');
    
    // $corrective_data = $corrective_data->where('is_sync','=',0)
    // ->where('site_id','=',$site_id_tmp)
    // ->get();

    // 	$res['data'] = $corrective_data;
    // 	return response($res);

    $corrective_data = DB::table('corrective')
    ->select('*');

    if ($site_id_tmp!=null) {
    	$corrective_data = $corrective_data->where('site_id','=',$site_id_tmp)
    	->where('is_sync','=',0)
    	->get();
    }else{

    	$corrective_data = $corrective_data->where('is_sync','=',0)
    	->get();
    }

    	// $res['data'] = $corrective_data;
    	// return response($res);

    // $corrective_data = DB::table('corrective')
    // ->select('*')
    // ->where('is_sync','=',0)
    // ->get();

    // $res['success'] = true;
    // $res['message'] = 'SUCCESS';
    // $res['data'] = $corrective_data;
    // return response($res);

    $corrective_result = json_decode($corrective_data, true);

    if ($corrective_result==null) {
    	$res['success'] = true;
    	$res['message'] = 'SUCCESS';
    	$res['data'] = $corrective_result;
    	return response($res);
    }
    // $res['data'] = $corrective_result;
    // return response($res);

    foreach ($corrective_result as $param => $row) {

    	$data[$param] = $row;

      
      $log_corrective_data = DB::table('app_corrective_log')
      ->select('*')
      ->where('corrective_id','=',$row['corrective_id'])
      ->get();

    	$data[$param]['corrective_log'] = $log_corrective_data;

    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    // $res['data1'] = $corrective_data;
    return response($res);
  }

  public function updateIsSyncCorrective(Request $request){
    $data_corective = $request->input('data');

    // print_r($data_corective);
    // exit();

    // $res['success'] = true;
    // $res['message'] = 'SUCCESS';
    // $res['data'] = $data_corective;
    // return response($res);


    foreach ($data_corective as $param => $row) {

  //   	print_r($row);
		// exit('string');
		$corrective_data = DB::table('corrective')
		->where('corrective_id','=',$row['corrective_id'])
		->where('request_status',$row['cek_status'])
		->first();

		if ($corrective_data) {
			unset($row['cek_status']);
			
			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$row['corrective_id'])
			->update($row);

			if (array_key_exists('log', $row)) {
				
				$log_data = $row['log'];
				unset($row['log']);

				foreach ($log_data as $param => $log_row) {
					$update_corrective_data = DB::table('app_corrective_log')
					->where('log_id','=',$log_row['log_id'])
					->update($log_row);
				}

			}
		}
    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    // $res['data'] = $data;
    return response($res);
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

  public function CloseTicketCorrectiveAfter24h(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $delete_date_strtotime = strtotime($date_now."-24hours");
    $delete_date_fix = date('Y-m-d  H:i:s',$delete_date_strtotime);

    $C_data = DB::table('corrective')
    ->select('*')
    ->where('end_status','=',0)
    ->where('corrective_date','<',$delete_date_fix)
    ->orderBy('corrective_date', 'asc')
    ->get();

    $x=0;
    foreach ($C_data as $value) {

    	$input_request_status = null;
    	$desc = null; 
      $data1[$x]['corrective_id']=$value->corrective_id;
      $data1[$x]['corrective_date']=$value->corrective_date;
      $data1[$x]['send_by']=$value->send_by_cn;
      $data1[$x]['request_status']=$value->request_status;
      $request_status = $value->request_status;

      //=================================================================================================

      		if ($request_status == 2) {
      			$input_request_status = 4;
      			$desc = 'close by system karena RTPO tidak menanggapi lebih dari 24 jam'; 
      		}
      		if ($request_status == 0) {
      			$input_request_status = 8;
      			$desc = 'close by system karena tidak di tanggapi fmc lebih dari 24 jam'; 
      		}
      		if ($request_status == 1) {
      			$input_request_status = 8;
      			$desc = 'close by system karena belum selesai dikerjakan fmc lebih dari 24 jam'; 
      		}
      		if ($request_status == 3) {
      			$input_request_status = 8;
      			$desc = 'close by system karena RTPO tidak menanggapi lebih dari 24 jam'; 
      		}
      		if ($request_status == 5) {
      			$input_request_status = 8;
      			$desc = 'close by system karena tidak dikerjakan kembali oleh fmc lebih dari 24 jam'; 
      		}
      		if ($request_status == 7) {
      			$input_request_status = 8;
      			$desc = 'close by system karena tidak dikerjakan kembali oleh fmc lebih dari 24 jam'; 
      		}

      $data1[$x]['akan berubah menjadi']=$input_request_status;
      $data1[$x]['dengan alasan']=$desc;

      		if ($request_status != 6) { 
      			//maka baru di proses auto closenya.
      			$corrective_id = $value->corrective_id;
      			$data['canceled_by_nik'] = 'system';
      			$data['canceled_by_cn'] = 'system';
      			$data['request_status'] = $input_request_status.'';
      			$data['canceled_time'] = $date_now;
      			$data['canceled_desc'] = $desc;
      			$data['end_status'] = '1';

      			$update_corrective_data = DB::table('corrective')
      			->where('corrective_id','=',$corrective_id)
      			->update(
      				[
      					'request_status'=> $data['request_status'],
      					'end_status'=> $data['end_status'],
      					'last_update'=>$data['canceled_time'],
      					'is_sync'=> '0',
      				]
      			);
      			if (!$update_corrective_data) {
      				$res['success'] = false;
      				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
      				return response($res);
      			}

      			$data['corrective_id'] = $corrective_id.'';
      			$data['user_nik'] = $data['canceled_by_nik'];
      			$data['user_cn'] = $data['canceled_by_cn'];
      			$data['status'] = $data['request_status'];

      			$status_corrective = DB::table('status_corrective')
      			->select('*')
      			->where('status_id','=',$data['status'])
      			->first();
      			$data['status_desc'] = @$status_corrective->status_desc;

      			$data['description'] = $data['canceled_desc'];
      			$data['last_update'] = $data['canceled_time'];

      			$app_corrective_data = DB::table('corrective')
      			->select('*')
      			->where('corrective_id','=',$corrective_id)
      			->first();

				// dan insert ke tabel "app_corrective_log" 
      			$insert_app_corrective_log_data = DB::table('app_corrective_log')
      			->insert(
      				[
      					'corrective_id'=>$data['corrective_id'].'',
						// 'unique_id'=>@$app_corrective_data->unique_id,
      					'user_nik'=>$data['user_nik'],
      					'user_cn'=>$data['user_cn'],
      					'status'=>$data['status'],
      					'status_desc'=>@$data['status_desc'],
      					'description'=>$data['description'],
      					'last_update'=>$data['last_update'],
      					'is_sync'=> '0',
      				]
      			);

      			if (!$insert_app_corrective_log_data) {
      				$res['success'] = false;
      				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
      				return response($res);
      			}
      		}
      		

      //=================================================================================================


      $x=$x+1;
    }


    $res['data'] = @$data1;
    return response($res);

  }



  public function CloseTicketCorrectiveTidakDiresponLebihDari15Menit(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $delete_date_strtotime = strtotime($date_now."-15 minutes");
    $delete_date_fix = date('Y-m-d H:i:s',$delete_date_strtotime);

    // $data['date now'] = $date_now;
    // $data['date -15m'] = $delete_date_fix;
    // return response($data);

    $C_data = DB::table('corrective')
    ->select('*')
    ->where('end_status','=',0)
    ->where('request_status','=',0)
    ->where('corrective_date','<',$delete_date_fix)
    ->orderBy('corrective_date', 'asc')
    ->get();

    $x=0;
    foreach ($C_data as $value) {

    	$input_request_status = null;
    	$desc = null; 
      $data1[$x]['corrective_id']=$value->corrective_id;
      $data1[$x]['corrective_date']=$value->corrective_date;
      $data1[$x]['send_by']=$value->send_by_cn;
      $data1[$x]['request_status']=$value->request_status;
      $request_status = $value->request_status;

      //=================================================================================================

      		if ($request_status == 0) {
      			$input_request_status = 8;
      			$desc = 'close by system karena tidak di tanggapi fmc lebih dari 15 menit'; 
      		}

      $data1[$x]['akan berubah menjadi']=$input_request_status;
      $data1[$x]['dengan alasan']=$desc;

      		if ($request_status != 6) { 
      			//maka baru di proses auto closenya.
      			$corrective_id = $value->corrective_id;
      			$data['canceled_by_nik'] = 'system';
      			$data['canceled_by_cn'] = 'system';
      			$data['request_status'] = $input_request_status.'';
      			$data['canceled_time'] = $date_now;
      			$data['canceled_desc'] = $desc;
      			$data['end_status'] = '1';

      			$update_corrective_data = DB::table('corrective')
      			->where('corrective_id','=',$corrective_id)
      			->update(
      				[
      					'request_status'=> $data['request_status'],
      					'end_status'=> $data['end_status'],
      					'last_update'=>$data['canceled_time'],
      					'is_sync'=> '0',
      				]
      			);
      			if (!$update_corrective_data) {
      				$res['success'] = false;
      				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
      				return response($res);
      			}

      			$data['corrective_id'] = $corrective_id.'';
      			$data['user_nik'] = $data['canceled_by_nik'];
      			$data['user_cn'] = $data['canceled_by_cn'];
      			$data['status'] = $data['request_status'];

      			$status_corrective = DB::table('status_corrective')
      			->select('*')
      			->where('status_id','=',$data['status'])
      			->first();
      			$data['status_desc'] = @$status_corrective->status_desc;

      			$data['description'] = $data['canceled_desc'];
      			$data['last_update'] = $data['canceled_time'];

      			$app_corrective_data = DB::table('corrective')
      			->select('*')
      			->where('corrective_id','=',$corrective_id)
      			->first();

				// dan insert ke tabel "app_corrective_log" 
      			$insert_app_corrective_log_data = DB::table('app_corrective_log')
      			->insert(
      				[
      					'corrective_id'=>$data['corrective_id'].'',
						// 'unique_id'=>@$app_corrective_data->unique_id,
      					'user_nik'=>$data['user_nik'],
      					'user_cn'=>$data['user_cn'],
      					'status'=>$data['status'],
      					'status_desc'=>@$data['status_desc'],
      					'description'=>$data['description'],
      					'last_update'=>$data['last_update'],
      					'is_sync'=> '0',
      				]
      			);

      			if (!$insert_app_corrective_log_data) {
      				$res['success'] = false;
      				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
      				return response($res);
      			}
      		}
      		

      //=================================================================================================


      $x=$x+1;
    }


    $res['data'] = @$data1;
    return response($res);

  }



	public function openPendingCorrectiveByFmc(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id'); 
		$respon_cn = $request->input('open_by'); #diisi username dan dapatkan nik 
		$respon_time = date('Y-m-d H:i:s');
		$respon_desc = $request->input('description'); 
		$request_status = '9';


		$users_data = DB::table('users')
		->select('*')
		->where('username','=',$respon_cn)
		->first();

		if ($users_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		$data['respon_nik'] = $users_data->id;
		$data['respon_cn'] = $respon_cn;
		$data['respon_time'] = $respon_time;
		// $data['resolve_desc'] = $resolve_desc;
		$data['respon_desc'] = '';
		$data['request_status'] = $request_status;
		// $data['late_response_flag'] = $late_response_flag;

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[

				'pending_nik'=> null,
				'pending_cn'=> null,
				'pending_time'=> null,
				'pending_desc'=> null,

				'rtpo_pending_nik'=> null,
				'rtpo_pending_desc'=> null,
				'rtpo_pending_cn'=> null,
				'rtpo_pending_time'=> null,
				'rtpo_pending_status'=> null,

				'respon_nik'=> $data['respon_nik'],
				'respon_cn'=> $data['respon_cn'],
				'respon_time'=> $data['respon_time'],
				'respon_desc'=> $data['respon_desc'],
				'request_status'=> $data['request_status'],
				// 'late_response_flag'=> $data['late_response_flag'],
				'last_update'=>$data['respon_time'],
				'is_sync'=> '0',
			]
		);
		if (!$update_corrective_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			return response($res);
		}

		$data['corrective_id'] = $corrective_id.'';
		$data['user_nik'] = $data['respon_nik'];
		$data['user_cn'] = $data['respon_cn'];
		$data['status'] = $data['request_status'];

		$status_corrective = DB::table('status_corrective')
		->select('*')
		->where('status_id','=',$data['status'])
		->first();
		$data['status_desc'] = @$status_corrective->status_desc;

		$data['description'] = $data['respon_desc'];
		$data['last_update'] = $data['respon_time'];


		$insert_app_corrective_log_data = DB::table('app_corrective_log')
		->insert(
			[
				'corrective_id'=>$data['corrective_id'].'',
				'user_nik'=>$data['user_nik'],
				'user_cn'=>$data['user_cn'],
				'status'=>$data['status'],
				'status_desc'=>@$data['status_desc'],
				// 'description'=>$data['description'],
				'description'=>'FMC menyatakan bahwa membuka kembali tiket yang telah di pending',
				'last_update'=>$data['last_update'],
				'is_sync'=> '0',
			]
		);

		if (!$insert_app_corrective_log_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		$user_to_data = DB::table('users')
		->select('*')
		->where('username','=',$app_corrective_data->send_by_cn)
		->first();
		$coData = @$app_corrective_data->send_by_cn.' '.@$user_to_data->firebase_token;


		$notificationController = new NotificationController;

		$tmp = $notificationController->setNotificationV1($data['user_cn'], $app_corrective_data->send_by_cn, 'FMC_OPEN_TICKET_PENDING_CORRECTIVE', 'corrective_id', $corrective_id, 'Tiket Corrective', 'FMC_OPEN_TICKET_PENDING_CORRECTIVE', $data['user_cn'].' dari '.$users_data->fmc.' menyatakan bahwa membuka kembali tiket yang telah di pending',2,'CORRECTIVE');
			$to_token_id = array();
			array_push($to_token_id,@$user_to_data->firebase_token);
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.@$users_data->fmc.' menyatakan bahwa membuka kembali tiket yang telah di pending',$to_token_id,2,$data['corrective_id'],'FMC_OPEN_TICKET_PENDING_CORRECTIVE');


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}


	public function upgradeTicketCorrectiveByFmc(Request $request){

		date_default_timezone_set("Asia/Jakarta");

		$corrective_id = $request->input('corrective_id'); 
		$upgrade_cn = $request->input('upgrade_by'); #diisi username dan dapatkan nik 
		$upgrade_time = date('Y-m-d H:i:s');
		$upgrade_desc = $request->input('description'); 
		$request_status = '10';


		$users_data = DB::table('users')
		->select('*')
		->where('username','=',$upgrade_cn)
		->first();

		if ($users_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		$data['upgrade_nik'] = $users_data->id;
		$data['upgrade_cn'] = $upgrade_cn;
		$data['upgrade_time'] = $upgrade_time;
		// $data['resolve_desc'] = $resolve_desc;
		$data['upgrade_desc'] = $upgrade_desc;
		$data['request_status'] = $request_status;
		// $data['late_response_flag'] = $late_response_flag;

		$update_corrective_data = DB::table('corrective')
		->where('corrective_id','=',$corrective_id)
		->update(
			[

				// 'pending_nik'=> null,
				// 'pending_cn'=> null,
				// 'pending_time'=> null,
				// 'pending_desc'=> null,

				'rtpo_upgrade_nik'=> null,
				'rtpo_upgrade_desc'=> null,
				'rtpo_upgrade_cn'=> null,
				'rtpo_upgrade_time'=> null,
				'rtpo_upgrade_status'=> null,

				'upgrade_nik'=> $data['upgrade_nik'],
				'upgrade_cn'=> $data['upgrade_cn'],
				'upgrade_time'=> $data['upgrade_time'],
				'upgrade_desc'=> $data['upgrade_desc'],
				'request_status'=> $data['request_status'],
				// 'late_response_flag'=> $data['late_response_flag'],
				'last_update'=>$data['upgrade_time'],
				'is_sync'=> '0',
			]
		);
		if (!$update_corrective_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			return response($res);
		}

		$data['corrective_id'] = $corrective_id.'';
		$data['user_nik'] = $data['upgrade_nik'];
		$data['user_cn'] = $data['upgrade_cn'];
		$data['status'] = $data['request_status'];

		$status_corrective = DB::table('status_corrective')
		->select('*')
		->where('status_id','=',$data['status'])
		->first();
		$data['status_desc'] = @$status_corrective->status_desc;

		// $data['description'] = $data['respon_desc'];
		$data['description'] = $data['upgrade_desc'];
		$data['last_update'] = $data['upgrade_time'];


		$insert_app_corrective_log_data = DB::table('app_corrective_log')
		->insert(
			[
				'corrective_id'=>$data['corrective_id'].'',
				'user_nik'=>$data['user_nik'],
				'user_cn'=>$data['user_cn'],
				'status'=>$data['status'],
				'status_desc'=>@$data['status_desc'],
				'description'=>$data['description'],
				'last_update'=>$data['last_update'],
				'is_sync'=> '0',
			]
		);

		if (!$insert_app_corrective_log_data) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
			return response($res);
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',$corrective_id)
		->first();

		$user_to_data = DB::table('users')
		->select('*')
		->where('username','=',$app_corrective_data->send_by_cn)
		->first();
		$coData = @$app_corrective_data->send_by_cn.' '.@$user_to_data->firebase_token;


		$notificationController = new NotificationController;

		$tmp = $notificationController->setNotificationV1($data['user_cn'], $app_corrective_data->send_by_cn, 'FMC_UPGRADE_TICKET_CORRECTIVE_TO_WORK_ORDER', 'corrective_id', $corrective_id, 'Tiket Corrective', 'FMC_OPEN_TICKET_PENDING_CORRECTIVE', $data['user_cn'].' dari '.$users_data->fmc.' mengajukan upgrade tiket Corrective untuk dilanjutkan ke Work Order',2,'CORRECTIVE');
			$to_token_id = array();
			array_push($to_token_id,@$user_to_data->firebase_token);
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.@$users_data->fmc.' mengajukan upgrade tiket Corrective untuk dilanjutkan ke Work Order',$to_token_id,2,$data['corrective_id'],'FMC_UPGRADE_TICKET_CORRECTIVE_TO_WORK_ORDER');


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}




	public function responUpgradeCorrective(Request $request){

		date_default_timezone_set("Asia/Jakarta");


		$web = $request->input('web');

		if ($web==null) {

			$corrective_id = $request->input('corrective_id'); 
			$rtpo_upgrade_desc = $request->input('rtpo_upgrade_desc'); 
			$rtpo_upgrade_cn = $request->input('respon_by'); # diisi username dan dapatkan nik 
			$request_status = $request->input('request_status'); // 11 approve upgrade, 12 reject upgrade
			$rtpo_upgrade_time = date('Y-m-d H:i:s');

			if ($request_status == 11) {
				# code...
				$rtpo_upgrade_status = 0;
				$end_status = 1;
			}else if ($request_status == 12) {
				# code...
				$rtpo_upgrade_status = 1;
				$end_status = 0;
			}

			$users_data = DB::table('users')
			->select('*')
			->where('username','=',$rtpo_upgrade_cn)
			->first();

			if ($users_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
				return response($res);
			}

			$data['rtpo_upgrade_nik'] = $users_data->id;
			$data['rtpo_upgrade_cn'] = $rtpo_upgrade_cn;
			$data['rtpo_upgrade_time'] = $rtpo_upgrade_time;
			$data['rtpo_upgrade_desc'] = $rtpo_upgrade_desc;
			$data['rtpo_upgrade_status'] = $rtpo_upgrade_status.'';
			$data['request_status'] = $request_status;
			$data['end_status'] = $end_status.'';


			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$corrective_id)
			->update(
				[
					'rtpo_upgrade_nik'=> $data['rtpo_upgrade_nik'],
					'rtpo_upgrade_cn'=> $data['rtpo_upgrade_cn'],
					'rtpo_upgrade_time'=> $data['rtpo_upgrade_time'],
					'last_update'=>$data['rtpo_upgrade_time'],
					'rtpo_upgrade_status'=> $data['rtpo_upgrade_status'],
					'request_status'=> $data['request_status'],
					'end_status'=> $end_status,
					'is_sync'=> '0',
				]
			);
			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}

			$data['corrective_id'] = $corrective_id.'';
			$data['user_nik'] = $data['rtpo_upgrade_nik'];
			$data['user_cn'] = $data['rtpo_upgrade_cn'];
			$data['status'] = $data['request_status'];

			$status_corrective = DB::table('status_corrective')
			->select('*')
			->where('status_id','=',$data['status'])
			->first();
			$data['status_desc'] = @$status_corrective->status_desc;

			$data['description'] = $data['rtpo_upgrade_desc'];
			$data['last_update'] = $data['rtpo_upgrade_time'];

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$corrective_id)
			->first();

			$data['unique_id'] = @$app_corrective_data->unique_id;
			// dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$app_corrective_data->unique_id,
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>@$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> '0',
				]
			);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}


		}else{

			$data_corective = $request->input('data');


			$data['corrective_id'] = @$data_corective['corrective_id'];
			$request_status = $data_corective['request_status'];
			$log_data = $data_corective['log'];
			unset($data_corective['log']);

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',@$data_corective['corrective_id']) //ganti ke corrective id
			->update($data_corective);

			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}


			 $app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',@$data['corrective_id'])
			->first();

			$log_data['corrective_id'] = $app_corrective_data->corrective_id;

			$data['user_cn'] = $log_data['user_cn'];
			$data['corrective_id'] = $log_data['corrective_id'];
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert($log_data);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',@$data['corrective_id']) // ganti ke corrective id
		->first();

		// $user_to_data = DB::table('users')
		// ->select('*')
		// ->where('username','=',$app_corrective_data->send_by_cn)
		// ->get();

		// $users_data = DB::table('user_tsra as ut')
		// ->join('users as u', 'ut.tsra_username', 'u.username')
		// ->select('*')
		// ->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
		// ->where('ut.cluster','=',$app_corrective_data->cluster)
		// ->get();

		if ($app_corrective_data->target_role_id == 11) {
			$users_data = DB::table('user_tsra as ut')
			->join('users as u', 'ut.tsra_username', 'u.username')
			->select('*')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}elseif ($app_corrective_data->target_role_id == 7) {
			$users_data = DB::table('user_mbp_mt as ut')
			->join('users as u', 'ut.mbp_mt_username', 'u.username')
			->select('*', 'ut.mbp_mt_username as tsra_username')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}


			$to_token_id = array();
		if ($request_status == 11) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;
			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				// ($send_by, $send_to, $type, $name_type_id, $type_id, $title, $subject, $text)
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_APPROVE_UPGRADE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyetujui pengajuan upgrade anda',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyetujui pengajuan upgrade anda',$to_token_id,2,$data['corrective_id'],'RTPO_APPROVE_UPGRADE_TICKET_CORRECTIVE_FROM_FMC');

		}else if ($request_status == 12) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;

			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_REJECT_UPGRADE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_REJECT_UPGRADE_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' tidak menyetujui pengajuan upgrade anda',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' tidak menyetujui pengajuan upgrade anda',$to_token_id,2,$data['corrective_id'],'RTPO_REJECT_UPGRADE_TICKET_CORRECTIVE_FROM_FMC');
		}

		// (v)> 6. 
		// 	()> $type		: RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC  								//rtpo menyetujui pengajuan pending user fmc
		// 	()> $type_id	: 
		// 	()> $type_name	: corrective_id
		// 	()> $to_token_id: #1user #user_fmc_terkait
		// 	()> $body		: (user rtpo ...) dari (rtpo ...) menyetujui pengajuan pending anda
		// 	()> $title		: RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC

		// (v)> 7. 
		// 	()> $type		: RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC 								//rtpo tidak menyetujui pengajuan pending user fmc
		// 	()> $type_id	: 
		// 	()> $type_name	: corrective_id
		// 	()> $to_token_id: #1user #user_fmc_terkait
		// 	()> $body		: (user rtpo ...) dari rtpo ... tidak menyetujui pengajuan pending anda
		// 	()> $title		: RTPO_REJECT_PENDING_TICKET_CORRECTIVE_FROM_FMC
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		$res['fb'] = @$tmp_fb;
		$res['tfb'] = $to_token_id;
		return response($res);
	}






	public function responCorrectiveByRtpo(Request $request){

		date_default_timezone_set("Asia/Jakarta");


		$web = $request->input('web');

		if ($web==null) {

			$corrective_id = $request->input('corrective_id'); 
			$desc = $request->input('description'); 
			$rtpo_upgrade_cn = $request->input('respon_by'); # diisi username dan dapatkan nik 
			$request_status = $request->input('request_status'); // 11 approve upgrade, 12 reject upgrade
			$respon_time = date('Y-m-d H:i:s');
			$end_status = 1; // 4:approve resolve, 11:eskalasi to WO, 13:spms


			if ($corrective_id==null) {
				$res['success'] = false;
				$res['message'] = 'corrective_id null';
				return response($res);
			}
			if ($rtpo_upgrade_cn==null) {
				$res['success'] = false;
				$res['message'] = 'respon_by null';
				return response($res);
			}
			if ($request_status==null) {
				$res['success'] = false;
				$res['message'] = 'request_status null';
				return response($res);
			}

			if ($request_status == 5) {
				$end_status = 0;
			}

			$users_data = DB::table('users')
			->select('*')
			->where('username','=',$rtpo_upgrade_cn)
			->first();

			if ($users_data==null) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_USER_DATA_NOT_FOUND';
				return response($res);
			}

			
			$corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id',$corrective_id)
			->first();
			$end_corrective_date = $corrective_data->end_corrective_date;
			$rtpo_resolve_time = date('Y-m-d H:i:s');

			if (@$end_corrective_date<$rtpo_resolve_time) {
				$data['overdue_flag'] = 1;
			}
			

			$data['rtpo_upgrade_nik'] = $users_data->id;
			$data['update_cn'] = $users_data->name;
			$data['rtpo_upgrade_cn'] = $rtpo_upgrade_cn;
			// $data['rtpo_upgrade_time'] = $rtpo_upgrade_time;
			$data['rtpo_upgrade_desc'] = $desc;
			// $data['rtpo_upgrade_status'] = $rtpo_upgrade_status.'';
			$data['request_status'] = $request_status;
			$data['end_status'] = $end_status.'';


			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',$corrective_id)
			->update(
				[
					// 'rtpo_upgrade_nik'=> $data['rtpo_upgrade_nik'],
					// 'rtpo_upgrade_cn'=> $data['rtpo_upgrade_cn'],
					// 'rtpo_upgrade_time'=> $data['rtpo_upgrade_time'],
					'last_update'=>$respon_time,
					'update_by'=>@$data['rtpo_upgrade_nik'],
					'update_cn'=>@$data['update_cn'],
					'reason'=> $data['rtpo_upgrade_desc'],
					'request_status'=> $data['request_status'],
					'end_status'=> $end_status,
					'is_sync'=> 0,
					'overdue_flag' => @$data['overdue_flag'],
				]
			);
			// if (!$update_corrective_data) {
			// 	$res['success'] = false;
			// 	$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
			// 	return response($res);
			// }

			$data['corrective_id'] = $corrective_id.'';
			$data['user_nik'] = $data['rtpo_upgrade_nik'];
			$data['user_cn'] = $data['rtpo_upgrade_cn'];
			$data['status'] = $data['request_status'];

			$status_corrective = DB::table('status_corrective')
			->select('*')
			->where('status_id','=',$data['status'])
			->first();
			$data['status_desc'] = @$status_corrective->status_desc;

			$data['description'] = $desc;
			$data['last_update'] = $respon_time;

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',$corrective_id)
			->first();

			$data['unique_id'] = @$app_corrective_data->unique_id;
			// dan insert ke tabel "app_corrective_log" 
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert(
				[
					'corrective_id'=>$data['corrective_id'].'',
					// 'unique_id'=>@$app_corrective_data->unique_id,
					'user_nik'=>$data['user_nik'],
					'user_cn'=>$data['user_cn'],
					'status'=>$data['status'],
					'status_desc'=>@$data['status_desc'],
					'description'=>$data['description'],
					'last_update'=>$data['last_update'],
					'is_sync'=> 0,
				]
			);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}


		}else{

			$data_corective = $request->input('data');


			$data['corrective_id'] = @$data_corective['corrective_id'];
			$request_status = $data_corective['request_status'];
			$log_data = $data_corective['log'];
			unset($data_corective['log']);

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',@$data_corective['corrective_id']) //ganti ke corrective id
			->update($data_corective);

			if (!$update_corrective_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_UPDATE_DATA_CORRECTIVE';
				return response($res);
			}


			 $app_corrective_data = DB::table('corrective')
			->select('*')
			->where('corrective_id','=',@$data['corrective_id'])
			->first();

			$log_data['corrective_id'] = $app_corrective_data->corrective_id;

			$data['user_cn'] = $log_data['user_cn'];
			$data['corrective_id'] = $log_data['corrective_id'];
			$insert_app_corrective_log_data = DB::table('app_corrective_log')
			->insert($log_data);

			if (!$insert_app_corrective_log_data) {
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT_DATA_CORRECTIVE_LOG';
				return response($res);
			}
		}

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('corrective_id','=',@$data['corrective_id']) // ganti ke corrective id
		->first();

		// $user_to_data = DB::table('users')
		// ->select('*')
		// ->where('username','=',$app_corrective_data->send_by_cn)
		// ->get();

		// $users_data = DB::table('user_tsra as ut')
		// ->join('users as u', 'ut.tsra_username', 'u.username')
		// ->select('*')
		// ->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
		// ->where('ut.cluster','=',$app_corrective_data->cluster)
		// ->get();

		if ($app_corrective_data->target_role_id == 11) {
			$users_data = DB::table('user_tsra as ut')
			->join('users as u', 'ut.tsra_username', 'u.username')
			->select('*')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}elseif ($app_corrective_data->target_role_id == 7) {
			$users_data = DB::table('user_mbp_mt as ut')
			->join('users as u', 'ut.mbp_mt_username', 'u.username')
			->select('*', 'ut.mbp_mt_username as tsra_username')
			->where('ut.fmc_id','=',$app_corrective_data->fmc_id)
			->where('ut.cluster','=',$app_corrective_data->cluster)
			->get();
		}


			$to_token_id = array();
		if ($request_status == 11) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;
			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				// ($send_by, $send_to, $type, $name_type_id, $type_id, $title, $subject, $text)
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_APPROVE_UPGRADE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_APPROVE_UPGRADE_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' melakukan eskalasi pada tiket Corrective ini',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyetujui pengajuan upgrade anda',$to_token_id,2,$data['corrective_id'],'RTPO_APPROVE_UPGRADE_TICKET_CORRECTIVE_FROM_FMC');

		}else if ($request_status == 4) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;

			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyatakan bahwa pekerjaan tiket Corrective anda telah di setujui',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyatakan bahwa pekerjaan fmc anda telah di setujui',$to_token_id,2,$data['corrective_id'],'RTPO_APPROVE_RESOLVE_TICKET_CORRECTIVE_FROM_FMC');

			//------------------------
		}else if ($request_status == 13) {
			//get all user dimana fmc_id nya == $data['fmc_id']
			$notificationController = new NotificationController;

			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_UPGRADE_SPMS_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_UPGRADE_SPMS_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyatakan bahwa pekerjaan tiket Corrective anda telah di setujui',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyatakan bahwa pekerjaan fmc anda telah di setujui',$to_token_id,2,$data['corrective_id'],'RTPO_UPGRADE_SPMS_TICKET_CORRECTIVE_FROM_FMC');

			//------------------------
		}else if ($request_status==5) {


			$notificationController = new NotificationController;

			$result = json_decode($users_data, true);
			foreach ($result as $param => $row) {
				$tmp = $notificationController->setNotificationV1($data['user_cn'], $row['username'], 'RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', 'corrective_id', $data['corrective_id'], 'Tiket Corrective', 'RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyatakan bahwa pekerjaan fmc anda tidak disetujui, harap dikerjakan kembali',2,'CORRECTIVE');
				array_push($to_token_id,@$row['firebase_token']);
			}
			// $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
			// $topic = '/topics/'.$notificationController->checkMyClusterFMCtopic($app_corrective_data->fmc_id,$app_corrective_data->cluster,'TSRA');
			
			$fbc = new FireBaseController;
			$tmp_fb = $fbc->sendNotification('CORRECTIVE', $data['user_cn'].' dari '.$app_corrective_data->rtpo.' menyatakan bahwa pekerjaan fmc anda tidak disetujui, harap dikerjakan kembali',$to_token_id,2,$data['corrective_id'],'RTPO_REJECT_RESOLVE_TICKET_CORRECTIVE_FROM_FMC');

			//---------------------------------------------
		}
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		$res['fb'] = @$tmp_fb;
		$res['tfb'] = $to_token_id;
		return response($res);
	}



	public function updateEndDateCorrective(){


		$app_corrective_data = DB::table('corrective')
		->select('*')
		->get();

		$x=0;
		foreach ($app_corrective_data as $value) {

			$corrective_type = $value->corrective_type;
			$corrective_date = $value->corrective_date;

			if (@$corrective_type=="CRITICAL") {
				$tmp_hour = 4;				
			}else if (@$corrective_type=="MINOR") {
				$tmp_hour = 6;
			}else{
				$tmp_hour = 0;
			}

			$tmp_end_corrective_date = strtotime($corrective_date."+".$tmp_hour." hour");
			$end_corrective_date = date('Y-m-d H:i:s',$tmp_end_corrective_date);
			// $data['end_corrective_date'] = $end_corrective_date;

			$update_corrective_data = DB::table('corrective')
			->where('corrective_id','=',@$value->corrective_id)
			->update(
				[
					'end_corrective_date'=> @$end_corrective_date,
				]
			);

		}
		return "dah";
	}



	public function check15mntResponCorrective(){


		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');
		$createNotif = strtotime($date_now."-15 minutes");
		$createNotif_fix = date('Y-m-d H:i:s',$createNotif);

		// $C_data = DB::table('corrective')
		// ->select('*')
		// ->where('end_status','=',0)
		// ->where('request_status','=',0)
		// ->where('corrective_date','<',$delete_date_fix)
		// ->orderBy('corrective_date', 'asc')
		// ->get();


		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('request_status','<', 1)
		->get();

		$x=0;
		foreach ($app_corrective_data as $value) {

			$corrective_id = $value->corrective_id;
			$corrective_date = $value->corrective_date;

			if ($corrective_date <= $createNotif_fix) {
				
				$check_queue = DB::table('queue_telegram')
				->select('*')
				->where('ticket_id', $corrective_id)
				->first();

				if (@$check_queue->ticket_id!=$corrective_id) {
					//=====================================


					$admin_fmc_data = DB::table('users')
					->select('*')
					->where('fmc_id','=',$value->fmc_id)
					->where('cluster','=',$value->cluster)
					->where('chat_id','!=',null)
      				->where('chat_id','!=',"")
					->get();

					foreach ($admin_fmc_data as $param) {

					$subject_telegram = 'sendTicketCorrective';
        // $title = strip_tags("<b><i> TIKET CORRECTIVE CLUSTER ".@$data['cluster']." </i></b>","<b>");
// 		$text_telegram = '[ <b>TIKET CORRECTIVE '.@$data['cluster'].' BELUM DIRESPON<b> ]
// Dari : '.$value->send_by_cn.'
// RTPO : '.$value->rtpo.'
// ID Site : '.$value->site_id.'
// Nama site : '.$value->site_name.'
// Cluster : '.$value->cluster.'
// Deskripsi : '.$value->description.'
// Tanggal : '.$value->corrective_date.'

// Dihimbau untuk mengingatkan tim Corrective cluster tersebut, terimakasih.';

	$text_telegram = "[ <b>TIKET CORRECTIVE BELUM DIRESPON</b> ] \nHalo, 
ada Tiket Corrective untuk ".@$value->site_name." cluster ".@$value->cluster." dengan ID ".$value->site_id.", dibuat oleh ".$value->send_by_cn." dari ".$value->rtpo." pada tanggal ".$value->description.", dengan keterangan berikut : ".$value->description.". \n\nJangan lupa untuk mengingatkan tim corrective cluster ".@$value->cluster." mengenai hal ini. \nTerima Kasih.\n\n-NGSemeru Team-";

	$date_now = date('Y-m-d H:i:s');
	        $inserQueueTelegram = DB::table('queue_telegram')   
	        ->insert(
	          [
	            'subject' => 'sendTicketCorrective15Mnts',
	            'message' => @$text_telegram,
	            'ticket_id' => @$corrective_id,
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

	public function listCorrectiveFromPaginate(Request $request){

		$rtpo_id = $request->input('rtpo_id');
		$page = $request->input('page');
	    $search = $request->input('search');

	    $limit = 20;
	    $offset = ($page-1)*$limit;

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('rtpo_id',$rtpo_id)
		->where('end_status','0')
		->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
		// ->where('request_status','!=','4')
		->offset($offset)
      	->limit($limit)
		->orderBy('corrective_date','desc')
		->get();
		

		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;
			$data[$param]['site_id'] = $corrective_result[$param]['site_id'] ;
			$data[$param]['site_name'] = $corrective_result[$param]['site_name'] ;

			$status_code = DB::table('status_corrective')
			->select('*')
			->where('status_id',$corrective_result[$param]['request_status'])
			->first();

			$c_status = $status_code->status_desc;

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function listCorrectiveFromFmcPaginate(Request $request){

		$fmc_id = $request->input('fmc_id');
		$username = $request->input('username');
		$rtpo_id = $request->input('rtpo_id');
		$page = $request->input('page');
	    $search = $request->input('search');

	    $limit = 20;
	    $offset = ($page-1)*$limit;

		if ($username==null) {
			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('fmc_id','=',$fmc_id)
			->where('rtpo_id',$rtpo_id)
		// ->where('request_status','!=','4')
			->where('end_status','=','0')
			->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
			->offset($offset)
	      	->limit($limit)
			->orderBy('corrective_date','desc')
			->get();
		}else{
			$user_data = DB::table('user_tsra')
			->select('*')
			->where('tsra_username','=',$username)
			->first();

			$cluster = @$user_data->cluster;
			$target_role_id = 11;

			if ($cluster==null) {
				$user_data = DB::table('user_mbp_mt')
				->select('*')
				->where('mbp_mt_username','=',$username)
				->first();

				$cluster = @$user_data->cluster;
				$target_role_id = 7;

			}

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('fmc_id','=',$fmc_id)
			->where('target_role_id','=',$target_role_id)
			->where('cluster','=',$cluster)
			->where('end_status','=',0)
			->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
			->offset($offset)
	      	->limit($limit)
			->get();
		}


		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;
			$data[$param]['cluster'] = $corrective_result[$param]['cluster'] ;
			$data[$param]['cluster_id'] = $corrective_result[$param]['cluster_id'] ;
			$data[$param]['regional'] = $corrective_result[$param]['regional'] ;
			$data[$param]['site_id'] = $corrective_result[$param]['site_id'] ;
			$data[$param]['site_name'] = $corrective_result[$param]['site_name'] ;

			$status_code = DB::table('status_corrective')
			->select('*')
			->where('status_id',$corrective_result[$param]['request_status'])
			->first();

			$c_status = $status_code->status_desc;

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['target_role_id'] = @$target_role_id;
		$res['data'] = $data;
		return response($res);
	}

	public function listHistoryCorrectiveFromPaginate(Request $request){

		$rtpo_id = $request->input('rtpo_id');
		$page = $request->input('page');
	    $search = $request->input('search');

	    $limit = 20;
	    $offset = ($page-1)*$limit;

		$app_corrective_data = DB::table('corrective')
		->select('*')
		->where('rtpo_id','=',$rtpo_id)
		// ->where('request_status','!=','4')
		->where('end_status','=','1')
		->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
		->orderBy('corrective_date', 'desc')
		->offset($offset)
	    ->limit($limit)
		->get();

		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;
			$data[$param]['site_id'] = $corrective_result[$param]['site_id'] ;
			$data[$param]['site_name'] = $corrective_result[$param]['site_name'] ;

			$status_code = DB::table('status_corrective')
			->select('*')
			->where('status_id',$corrective_result[$param]['request_status'])
			->first();

			$c_status = $status_code->status_desc;

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	public function listHistoryCorrectiveFromFmcPaginate(Request $request){

		$fmc_id = $request->input('fmc_id');
		$page = $request->input('page');
		$username = $request->input('username');
	    $search = $request->input('search');

	    $limit = 20;
	    $offset = ($page-1)*$limit;

	    if ($username==null) {
			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('fmc_id','=',$fmc_id)
			// ->where('request_status','!=','4')
			->where('end_status','=','1')
			->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
			->orderBy('corrective_date', 'desc')
			->offset($offset)
		    ->limit($limit)
			->get();
		} else{
			$user_data = DB::table('user_tsra')
			->select('*')
			->where('tsra_username','=',$username)
			->first();

			$cluster = @$user_data->cluster;
			$target_role_id = 11;

			if ($cluster==null) {
				$user_data = DB::table('user_mbp_mt')
				->select('*')
				->where('mbp_mt_username','=',$username)
				->first();

				$cluster = @$user_data->cluster;
				$target_role_id = 7;

			}

			$app_corrective_data = DB::table('corrective')
			->select('*')
			->where('fmc_id','=',$fmc_id)
			->where('target_role_id','=',$target_role_id)
			->where('cluster','=',$cluster)
			->where('end_status','=',1)
			->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
			->orderBy('corrective_date', 'desc')
			->offset($offset)
	      	->limit($limit)
			->get();
		}

		$corrective_result = json_decode($app_corrective_data, true);

		if ($corrective_result==null) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			$res['data'] = $app_corrective_data;
			return response($res);
		}

		// id, deskripsi, status, level, send_by, date, 

		foreach ($corrective_result as $param => $row) {

			$data[$param]['corrective_id'] = $corrective_result[$param]['corrective_id'] ;
			$data[$param]['corrective_id_alias'] = 'CRT-'.$corrective_result[$param]['corrective_id'] ;
			$data[$param]['deskripsi'] = $corrective_result[$param]['description'] ;
			$data[$param]['site_id'] = $corrective_result[$param]['site_id'] ;
			$data[$param]['site_name'] = $corrective_result[$param]['site_name'] ;

			$status_code = DB::table('status_corrective')
			->select('*')
			->where('status_id',$corrective_result[$param]['request_status'])
			->first();

			$c_status = $status_code->status_desc;

			$data[$param]['status'] = $c_status;
			$data[$param]['level'] = $corrective_result[$param]['corrective_type'] ;
			$data[$param]['send_by'] = $corrective_result[$param]['send_by_cn'] ;
			$data[$param]['date'] = @$this->setDatedMYHis($corrective_result[$param]['corrective_date'].'') ;
		}

		// $data['data'] = $app_corrective_data;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}

	//Fungsi detilCorrectiveFromNew untuk melihat detail tiket corrective
	public function detilCorrectiveFromNew(Request $request){

		$corrective_id = $request->input('corrective_id');

		$app_corrective_data = DB::table('corrective')
		->join('users as u', 'corrective.send_by_cn', 'u.username')
		->select('corrective.*', 'u.name')
		->where('corrective_id','=',$corrective_id)
		->first();

		if ($app_corrective_data==null) {
			$res['success'] = false;
			$res['message'] = 'FAILLED_CORRECTIVE_DATA_NOT_FOUND';
			return response($res);
		}

		$site_data = DB::table('site')
		->select('latitude','longitude')
		->where('site_id',$app_corrective_data->site_id)
		->first();


		$data['corrective_id'] = $app_corrective_data->corrective_id;
		$data['site_id'] = $app_corrective_data->site_id;
		$data['site_name'] = $app_corrective_data->site_name;
		$data['latitude'] = $site_data->latitude;
		$data['longitude'] = $site_data->longitude;
		$data['description'] = $app_corrective_data->description;
		$data['corrective_type'] = $app_corrective_data->corrective_type;
		$data['category'] = $app_corrective_data->category;
		$data['corrective_date'] = @$this->setDatedMYHis($app_corrective_data->corrective_date.'');
		$data['rtpo_id'] = $app_corrective_data->rtpo_id;
		$data['rtpo'] = $app_corrective_data->rtpo;
		$data['request_status'] = $app_corrective_data->request_status;

		$status_code = DB::table('status_corrective')
		->select('*')
		->where('status_id',$data['request_status'])
		->first();

		$c_status = $status_code->status_desc;

		$data['status_code'] = $app_corrective_data->request_status;
		$data['request_status'] = $c_status;		
		$data['fmc_id'] = $app_corrective_data->fmc_id;
		$data['fmc'] = $app_corrective_data->fmc;
		$data['send_by'] = $app_corrective_data->send_by;
		// $data['send_by_cn'] = $app_corrective_data->send_by_cn;
		$data['send_by_cn'] = $app_corrective_data->name;
		$data['cluster'] = $app_corrective_data->cluster;
		$data['cluster_id'] = $app_corrective_data->cluster_id;
		$data['regional'] = $app_corrective_data->regional;

		$data['overdue_flag'] = $app_corrective_data->overdue_flag;

		$corrective_log_data = DB::table('app_corrective_log')
		->join('users as u', 'app_corrective_log.user_cn', 'u.username')
		->select('app_corrective_log.*', 'u.name as user_cn')
		->where('corrective_id','=',$corrective_id)
		->orderBy('log_id', 'asc')
		->get();

		$resolved_date = '-';
		$corrective_actor = '-';
		$approved_date = '-';
		$approval_sender = '-';
		$approval_notes = '-';
		$reassigned_date = '-';
		$reassign_reason = '-';
		$reassign_sender = '-';
		$canceled_date = '-';
		$cancel_sender = '-';
		$cancel_reason = '-';
		$corrective_action = '-';

		$x=0;
		foreach ($corrective_log_data as $key) {
			//$corrective_log_data[$x]->last_update = @$this->setDatedMYHis($key->last_update);
			//$corrective_log_data[$x]->last_sync = @$this->setDatedMYHis($key->last_sync);

			if ($key->status==2){ //resolved
				$resolved_date = @$this->setDatedMYHis($key->last_update);
				$corrective_actor = $key->user_cn;
				$corrective_action = ($key->description==null || $key->description=='') ? '-' : $key->description;
			}

			if ($key->status==5){ //reassign
				$reassigned_date = @$this->setDatedMYHis($key->last_update);
				$reassign_reason = ($key->description==null || $key->description=='') ? '-' : $key->description ;
				$reassign_sender = $key->user_cn;
			}

			if ($key->status==4 || $key->status==11 || $key->status==13){ //approved/WO Accept/SPMS
				$approved_date = @$this->setDatedMYHis($key->last_update);
				$approval_sender = $key->user_cn;
				$approval_notes = ($key->description==null || $key->description=='') ? '-' : $key->description;
			}
			if ($key->status==8){ //cancel
				$canceled_date = @$this->setDatedMYHis($key->last_update);
				$cancel_reason = ($key->description==null || $key->description=='') ? '-' : $key->description;
				$cancel_sender = $key->user_cn;
			}
			$x=$x+1;
		}

		//$data['last_log'] = $corrective_log_data;

		$data['resolved_date'] = $resolved_date;
		$data['corrective_actor'] = $corrective_actor;
		$data['approved_date'] = $approved_date;
		$data['approval_sender'] = $approval_sender;
		$data['approval_notes'] = $approval_notes;
		$data['reassigned_date'] = $reassigned_date;
		$data['reassign_reason'] = $reassign_reason;
		$data['reassign_sender'] = $reassign_sender;
		$data['canceled_date'] = $canceled_date;
		$data['cancel_sender'] = $cancel_sender;
		$data['cancel_reason'] = $cancel_reason;
		$data['corrective_action'] = $corrective_action;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}
}
