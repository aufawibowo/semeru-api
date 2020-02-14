<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class ConfigAppController extends Controller
{

	public function test_connection(){
		$res = [
			'success'=>true,
			'msg'=>'200 OK!'
		];
		return $res;
	}

	public function getNumberConfig(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$date = date("Y-m-d H:i:s");

			// $number = "3439";
			// $number = "3934";
			// $data['number'] = $number;
		//======================================================== membuat greeting
		$forceLogout = 0;
		$activedUser = 0;
		$hour = date('H');

		if (3 < $hour && $hour <= 10) {
			$greeting = "Selamat Pagi";
		}
		if (10 < $hour && $hour <= 15) {
			$greeting = "Selamat Siang";
		}
		if (15 < $hour && $hour <= 18) {
			$greeting = "Selamat Sore";
		}
		if (18 < $hour && $hour <= 23) {
			$greeting = "Selamat Malam";
		}
		if (00 < $hour && $hour <= 3) {
			$greeting = "Selamat Malam";
		}

		$username = $request->input('username');
		$device = $request->input('device');

		if (@$username!=null) {

			DB::table('device')
			->where('username','=',$username)
			->where('device','=',$device)
			->delete();

			$insertDevice = DB::table('device')->insert(
				[
					'username' => $username,
					'device' => $device,
					'create_at' => $date,
				]
			);
			$activedUser = 1;
		}

		$version_data = DB::table('verion_app')
		->select('*')
		->orderBy('version_id', 'desc')
		->first();
		
		$panggilan = '';

		if ($username!=null) {

			$getusers = DB::table('users')
			->select('*')
			->where('username', $username)
			->first();

			if (@$getusers->user_type=='MBP') {

				$getFmcId = DB::table('user_mbp')
				->select('fmc_id')
				->where('username', $username)
				->first();

				if ($getFmcId==null) {
					$getFmcId = DB::table('user_mbp_mt')
					->select('fmc_id')
					->where('mbp_mt_username', $username)
					->first();
				}

				if ($getFmcId!=null) {
					$getRegional = DB::table('fmc')
					->select('regional')
					->where('fmc_id', $getFmcId->fmc_id)
					->first();
				}
			}else if (@$getusers->user_type=='RTPO') {
				
				$getRegional = DB::table('user_rtpo')
				->join('rtpo','user_rtpo.rtpo_id','rtpo.rtpo_id')
				->select('regional')
				->where('username', $username)
				->first();
			}

			if (@$getusers!=null) {
				if ($getusers->regional=='JATIM') {
					$panggilan = ' Cak';
				}
				if ($getusers->regional=='JATENG-DIY') {
					$panggilan = ' Mas';
				}
				if ($getusers->regional=='BALI NUSRA') {
					$panggilan = ' Bli';
				}
			}

			//================================================= mencari roles
			$roles = @$getusers->roles_id; 

			//================================================= mencari roles
            $list_mbp = DB::table('mbp as m')
            ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
			->select('*')
			->where('um.username', $username)
			->get();

			$result = json_decode(@$list_mbp, true);
            if ($result==null) {
              $tmp_list_mbp =null;
            }else{
              $tmp_list_mbp = '';
              foreach ($result as $param => $row) {
                $tmp_list_mbp .= $row['mbp_name'];
                if(!((count($result)-1)==$param)){
                  $tmp_list_mbp .=', ';
                }
              }  
            }

			$mbp_name = $tmp_list_mbp;

		}
		
		$get_config = DB::table('config')
		->select('*')
		->first();

		$data['force_logout'] = 0;
		$data['desc_logout'] = "0";
		if (@$get_config->force_logout==1) {
			$data['force_logout'] = 1;
			$data['desc_logout'] = "1";
		} else if (@$getusers->is_banned==1) {
			$data['force_logout'] = 1;
			$data['desc_logout'] = "2";
		} else if (@$getusers->must_logout==1) {
			$data['force_logout'] = 1;
			$data['desc_logout'] = "3";
			$number_update = DB::table('users')
			->where('username','=',$username)
			->update(
				[
					'must_logout'=>0,
				]
			);
		}


		$get_status_code = DB::table('status_code')
		->select('code','desc')
		->get();

		$corrective_category = DB::table('app_catagory_corrective')
		->select('category_id','category', 'target_role_id', 'target_role')
		->where('status','=','1')
		->get();

		$get_mc_battery = DB::table('multiple_choice_battery')
		->select('*')
		// ->where('regional','=',@$getusers->regional)
		->get();
		$get_mc_rectifier = DB::table('multiple_choice_rectifier')
		->select('*')
		// ->where('regional','=',@$getusers->regional)
		->get();
		$get_mc_generator = DB::table('multiple_choice_generator')
		->select('id','brand','created_at','last_update')
		->get();
		$get_mc_genset = DB::table('multiple_choice_genset')
		->select('id','brand','created_at','last_update')
		->get();
		$get_mc_ats = DB::table('multiple_choice_ats')
		->select('id','brand','created_at','last_update')
		->get();
		$tower_type = DB::table('multiple_choice_tower')
		->select('id','tower_type','created_at','last_update')
		->get();

		// $get_mcm = DB::table('multiple_choice_maintenance')
		// ->select('question', 'preference')
		// // ->groupBy('question')
		// ->get();

		// foreach ($get_mcm as $key) {
			
		// 	// $mcm[''] 
		// }

		$data['number'] = @$get_config->adn_number;
		$data['latest_version'] = $version_data->version_name;
		$data['latest_version_id'] = $version_data->version_id;
		$data['minimum_version_id'] = $version_data->minimum_version_id;
		$data['download_url'] = $version_data->download_url;
		$data['GPS Accuracy'] = @$get_config->gps_accuration;
		$data['req_size'] = @$get_config->req_size;
		$data['greeting'] = $greeting.@$panggilan; 
		$data['regional'] = @$getusers->regional;
		$data['roles'] = @$roles.'';
		$data['mbp_name'] = @$mbp_name;
		$data['corrective_category'] = @$corrective_category;
		// $data['force_logout'] = @$get_config->force_logout;
		$data['user_is_actived'] = @$activedUser;
		$data['adn_delay'] = @$get_config->adn_delay;
		$data['status_code'] = @$get_status_code;

		$data['mc_battery'] = @$get_mc_battery;
		$data['mc_rectifier'] = @$get_mc_rectifier;
		$data['mc_generator'] = @$get_mc_generator;
		$data['mc_genset'] = @$get_mc_genset;
		$data['get_mc_ats'] = @$get_mc_ats;
		$data['tower_type'] = @$tower_type;
		
		// $data['multiple_choice_maintenance'] = @$get_mcm;

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;

		return response($res);
	}
	
	public function SetADNnumber(Request $Request){

		$adn_number = $Request->input('adn_number');

		$version_data = DB::table('verion_app')
		->select('version_name')
		->orderBy('version_id', 'desc')
		->first();
		
		if (!$version_data) {
			$res['success'] = false;
			$res['message'] = 'VERSION DATA NOT FOUND';
		}

		$number_update = DB::table('verion_app')
		->where('version_name','=',$version_data->version_name)
		->update(
			[
				'adn_number'=>$adn_number,
			]
		);

		$version_data = DB::table('verion_app')
		->select('*')
		->orderBy('version_id', 'desc')
		->first();

		$data['number'] = @$version_data->adn_number;
		$data['latest_version'] = $version_data->version_name;
		$data['latest_version_id'] = $version_data->version_id;
		$data['minimum_version_id'] = $version_data->minimum_version_id;
		$data['download_url'] = $version_data->download_url;
		$data['GPS Accuracy'] = 100;
		$data['req_size'] = 500;

		$res['success'] = false;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;

		return response($res);
	}

	 public function getSiknoKosong(Request $request){
  
    // $sp_id = $request->input('sp_id');
    // $img_sp_data = DB::table('image_sp')
    // ->select('*')
    // ->whereMonth('date', '=', '8')
    // ->get();

		date_default_timezone_set("Asia/Jakarta");
		$periode = date("Y-m");
		$bulan_sekarang = date("m");

	 	$lm_kosong = DB::table('log_maintenance')
	 	->select('site_id')
	 	->where('date', 'like', $periode."%")
	 	// ->where("status", 4)
	 	->where("sik_no", "")
	 	->groupby("site_id")
	 	->get();
    
    
	 	$res['periode'] = $periode;
	 	$res['data'] = $lm_kosong;
	 	return response($res);
	}
	 public function getSpknoKosong(Request $request){
  
    // $sp_id = $request->input('sp_id');
    // $img_sp_data = DB::table('image_sp')
    // ->select('*')
    // ->whereMonth('date', '=', '8')
    // ->get();

		date_default_timezone_set("Asia/Jakarta");
		$date = date("Y-m-d H:i:s");
		$periode = date("Y-m");
		$bulan_sekarang = date("m");

	 	$lm_kosong = DB::table('log_sparepart')
	 	->select('genset_id')
	 	->where('date', 'like', $periode."%")
	 	// ->where("status", 0)
	 	->where("spk_no", "")
	 	->groupby("genset_id")
	 	->get();
    
    
	 	$res['periode'] = $periode;
	 	$res['data'] = $lm_kosong;
	 	return response($res);
	}
	public function SetSpk(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$periode = date("Y-m");
		$bulan_sekarang = date("m");
		$date = date("Y-m-d H:i:s");

		$data = @$request->input('data');


		foreach ($data as $key => $value) {
			// $value->sik_no;
			// $value->site_id;
			// $value->otp;
			# code...

		// $lookup_fmc_cluster_data = DB::table('sik_site')
		// ->select('*')
		// ->where('sik_no','=',@$value["sik_no"])
		// ->first();

		// // echo "- 1 - ".$sik_no;
		// $status = "ada";
		// if ($lookup_fmc_cluster_data==null) {
		// 	$status = "gak ada";
		// 	$insert_sik_site = DB::table('sik_site')
		// 	->insert(
		// 		[
		// 			'sik_no'=>@$value["sik_no"],
		// 			'site_id'=>@$value["site_id"],
		// 			'otp_id'=>@$value["otp_id"],
		// 			'maintenance_schedule'=>@$value["maintenance_schedule"],
		// 		]
		// 	);
		// }

		$insert_sik_site = DB::table('log_sparepart')
	 	->where('date', 'like', $periode."%")
	 	->where("genset_id", $value["gnst_id"])
	 	->where("spk_no", "")
		->update(
			[
				'spk_no'=>$value["spk_no"],
				'site_id'=>$value["site_id"],
				'remark'=>"ditambahkan spk_no oleh fungsi set SPK pada ".date('Y-m-d H:i:s'),
				'last_update'=>$date,
				'status'=>0,
			]
		);


		}


		$res['status'] = @$status;
		$res['data'] = @$data;
		if ($insert_sik_site) {
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		}else{

		$res['success'] = false;
		$res['message'] = 'gagal update';
		}
		return response($res);
	}

	public function SetSik(Request $request){
		// exit('test ds');
		// $data = $request->input('data');

		// $id = $request->input('id');

		// $res['success'] = true;
		// $res['message'] = 'coba aja';
		// return response($res);


		date_default_timezone_set("Asia/Jakarta");
		$periode = date("Y-m");
		$bulan_sekarang = date("m");

		$data = @$request->input('data');


		foreach ($data as $key => $value) {
			// $value->sik_no;
			// $value->site_id;
			// $value->otp;
			# code...

		$lookup_fmc_cluster_data = DB::table('sik_site')
		->select('*')
		->where('sik_no','=',@$value["sik_no"])
		->first();

		// echo "- 1 - ".$sik_no;
		$status = "ada";
		if ($lookup_fmc_cluster_data==null) {
			$status = "gak ada";
			$insert_sik_site = DB::table('sik_site')
			->insert(
				[
					'sik_no'=>@$value["sik_no"],
					'site_id'=>@$value["site_id"],
					'otp_id'=>@$value["otp_id"],
					'maintenance_schedule'=>@$value["maintenance_schedule"],
				]
			);
		}

		$insert_sik_site = DB::table('log_maintenance')
	 	->where('date', 'like', $periode."%")
	 	->where("site_id", $value["site_id"])
	 	->where("sik_no", "")
		->update(
			[
				'sik_no'=>$value["sik_no"],
				'status'=>0,
				'remark'=>"Updated by crontab SetSik at ".date('Y-m-d H:i:s'),
			]
		);


		}


		$res['status'] = $status;
		$res['data'] = $data;
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
		// $sik_no = @$request->input('sik_no');
		// $site_id = @$request->input('site_id');
		// $otp_id = @$request->input('otp_id');
		// $rab_id = @$request->input('rab_id');
		// $maintenance_schedule = @$request->input('maintenance_schedule');
		// $site_name = @$request->input('site_name');
		// $latitude = @$request->input('latitude');
		// $longitude = @$request->input('longitude');
		// $wil_opr_id = @$request->input('wil_opr_id');
		// $regional = @$request->input('regional');
		// $branch_id = @$request->input('branch_id');
		// $branch = @$request->input('branch');
		// $cluster_id = @$request->input('cluster_id');
		// $cluster = @$request->input('cluster');
		// $tec_opr_id = @$request->input('tec_opr_id');
		// $divisi = @$request->input('divisi');
		// $ns_id = @$request->input('ns_id');
		// $ns = @$request->input('ns');
		// $rtpo_id = @$request->input('rtpo_id');
		// $rtpo = @$request->input('rtpo');
		// $site_class = @$request->input('site_class');
		// $site_class_periode = @$request->input('site_class_periode');
		// $site_class_revenue = @$request->input('site_class_revenue');
		// $frekuensi = @$request->input('frekuensi');
		// $cluster_fmc_id = @$request->input('cluster_fmc_id');
		// $cluster_fmc = @$request->input('cluster_fmc');
		// $fmc_id = @$request->input('fmc_id');
		// $fmc = @$request->input('fmc');
		// $kriteria_site = @$request->input('kriteria_site');
		// $pic_approval_nik = @$request->input('pic_approval_nik');
		// $pic_approval_cn = @$request->input('pic_approval_cn');
		// $mt_status = @$request->input('mt_status');
		// $mt_date = @$request->input('mt_date');
		// $respond_by = @$request->input('respond_by');
		// $respond_cn = @$request->input('respond_cn');
		// $respond_status = @$request->input('respond_status');
		// $respond_date = @$request->input('respond_date');
		// $reject_reason = @$request->input('reject_reason');

		// echo "- 0 - ".$sik_no;

		$lookup_fmc_cluster_data = DB::table('sik_site')
		->select('*')
		->where('sik_no','=',$sik_no)
		->first();

		// echo "- 1 - ".$sik_no;

		if ($lookup_fmc_cluster_data!=null) {
			#insert..

		// echo "- 2 - ".$sik_no;
			$insert_sik_site = DB::table('sik_site')
			->where('sik_no','=',$lookup_fmc_cluster_data->sik_no)
			->update(
				[
					'sik_no'=>$sik_no,
					'rab_id'=>$rab_id,
					'site_id'=>$site_id,
					'maintenance_schedule'=>$maintenance_schedule,
					'site_name'=>$site_name,
					'latitude'=>$latitude,
					'longitude'=>$longitude,
					'wil_opr_id'=>$wil_opr_id,
					'regional'=>$regional,
					'branch_id'=>$branch_id,
					'branch'=>$branch,
					'cluster_id'=>$cluster_id,
					'cluster'=>$cluster,
					'tec_opr_id'=>$tec_opr_id,
					'divisi'=>$divisi,
					'ns_id'=>$ns_id,
					'ns'=>$ns,
					'rtpo_id'=>$rtpo_id,
					'rtpo'=>$rtpo,
					'site_class'=>$site_class,
					'site_class_periode'=>$site_class_periode,
					'site_class_revenue'=>$site_class_revenue,
					'frekuensi'=>$frekuensi,
					'cluster_fmc_id'=>$cluster_fmc_id,
					'cluster_fmc'=>$cluster_fmc,
					'fmc_id'=>$fmc_id,
					'fmc'=>$fmc,
					'kriteria_site'=>$kriteria_site,
					'pic_approval_nik'=>$pic_approval_nik,
					'pic_approval_cn'=>$pic_approval_cn,
					'mt_status'=>$mt_status,
					'mt_date'=>$mt_date,
					'respond_by'=>$respond_by,
					'respond_cn'=>$respond_cn,
					'respond_status'=>$respond_status,
					'respond_date'=>$respond_date,
					'otp_id'=>$otp_id,
					'reject_reason'=>$reject_reason,
				]
			);

		// echo "- 3 - ".$sik_no;
			$res['success'] = true;
			$res['message'] = 'SUCCESS_UPDATE';
			return response($res);

			// if ($insert_sik_site) {
			// 	$res['success'] = true;
			// 	$res['message'] = 'SUCCESS_UPDATE';
			// 	return response($res);
			// }else {
			// 	$res['success'] = false;
			// 	$res['message'] = 'FAILLED_UPDATE';
			// 	return response($res);
			// }
		}else {

		// $res['success'] = false;
		// $res['message'] = $lookup_fmc_cluster_data;
		// // $res['data'] = $data;
		// return response($res);
			#update..

		// echo "- 4 - ".$sik_no;
			$insert_sik_site = DB::table('sik_site')
			->insert(
				[
					'sik_no'=>$sik_no,
					'rab_id'=>$rab_id,
					'site_id'=>$site_id,
					'maintenance_schedule'=>$maintenance_schedule,
					'site_name'=>$site_name,
					'latitude'=>$latitude,
					'longitude'=>$longitude,
					'wil_opr_id'=>$wil_opr_id,
					'regional'=>$regional,
					'branch_id'=>$branch_id,
					'branch'=>$branch,
					'cluster_id'=>$cluster_id,
					'cluster'=>$cluster,
					'tec_opr_id'=>$tec_opr_id,
					'divisi'=>$divisi,
					'ns_id'=>$ns_id,
					'ns'=>$ns,
					'rtpo_id'=>$rtpo_id,
					'rtpo'=>$rtpo,
					'site_class'=>$site_class,
					'site_class_periode'=>$site_class_periode,
					'site_class_revenue'=>$site_class_revenue,
					'frekuensi'=>$frekuensi,
					'cluster_fmc_id'=>$cluster_fmc_id,
					'cluster_fmc'=>$cluster_fmc,
					'fmc_id'=>$fmc_id,
					'fmc'=>$fmc,
					'kriteria_site'=>$kriteria_site,
					'pic_approval_nik'=>$pic_approval_nik,
					'pic_approval_cn'=>$pic_approval_cn,
					'mt_status'=>$mt_status,
					'mt_date'=>$mt_date,
					'respond_by'=>$respond_by,
					'respond_cn'=>$respond_cn,
					'respond_status'=>$respond_status,
					'respond_date'=>$respond_date,
					'otp_id'=>$otp_id,
					'reject_reason'=>$reject_reason,
				]
			);


		// echo "- 5 - ".$sik_no;
			if ($insert_sik_site) {
			# code...

		// echo "- 6 - ".$sik_no;
				$res['success'] = true;
				$res['message'] = 'SUCCESS_INSERT';
				return response($res);
			// $res['data'] = $data;
			}else {
			# code...

		// echo "- 7 - ".$sik_no;
				$res['success'] = false;
				$res['message'] = 'FAILLED_INSERT';
				return response($res);
			}
		}

		// echo "- 8 - ".$sik_no;
		$res['success'] = false;
		$res['message'] = 'FAILED';
		// $res['data'] = $data;
		return response($res);
	}

	public function fmc_cluster_update(Request $request){

		$data_fmc_cluster = $request->input('data');

		foreach ($data_fmc_cluster as $param => $row) {

			$lookup_fmc_cluster_data = DB::table('lookup_fmc_cluster')
			->select('*')
			->where('fmc_cluster_id','=',$row['fmc_cluster_id'])
			->first();

			if ($lookup_fmc_cluster_data!=null) {

				$updateLookupFmcClusterData = DB::table('lookup_fmc_cluster')
				->where('fmc_cluster_id','=',$row['fmc_cluster_id'])
				->update($row);

			}else{

				$insertLookupFmcClusterData = DB::table('lookup_fmc_cluster')
				->insert($row);
			}

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}



	public function download_img(Request $request){

		// $data_fmc_cluster = $request->input('data');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");
		$zipname = date("YmdHis");

		//manggil fungsi zip 
		$fbc = new FireBaseController;
		$tmp_fbd = @$fbc->delete_zip_image();
		$tmp_fbd = @$fbc->proccess_zip_img($zipname);
		// echo "respon " . @$tmp_fbd['respon'];
		// print_r($tmp_fbd);
		// exit;
		if (@$tmp_fbd['respon']) {
			// return response($tmp_fbd);
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
		}else{
			$res['success'] = false;
			$res['message'] = 'File Empty';
		}
		
		//return sukses zip + alamat zip gambarnya

		$res['url'] = '/maintenance/packing/images/'.$zipname.'.zip';
		return response($res);
	}

	public function download_img_GS(Request $request){

		// $data_fmc_cluster = $request->input('data');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");
		$zipname = date("YmdHis");

		//manggil fungsi zip 
		$fbc = new FireBaseController;
		$tmp_fbd = @$fbc->delete_zip_image_GS();
		$tmp_fbd = @$fbc->proccess_zip_img_GS($zipname);
		// echo "respon " . @$tmp_fbd['respon'];
		// print_r($tmp_fbd);
		// exit;
		if (@$tmp_fbd['respon']) {
			// return response($tmp_fbd);
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
		}else{
			$res['success'] = false;
			$res['message'] = 'File Empty';
		}
		
		//return sukses zip + alamat zip gambarnya

		$res['url'] = '/maintenance/packing_GS/images/'.$zipname.'.zip';
		return response($res);
	}

	public function cek_om(){
		// $dataxml = DB::table('log_maintenance')
		// ->where('uri_tmp','=','landing_v2/')
		// ->where('status','=','0')
		// ->orWhere('date','<','DATE_SUB( NOW(), INTERVAL 4 DAY)');
		// ->where('status','=','0');

		$query = "SELECT * from log_maintenance where (status = 0 or (status=2 and last_update<DATE_SUB(NOW(), INTERVAL 10 MINUTE) ) )";
		echo "CEK::";

		$query.='ORDER BY date ASC LIMIT 50 ';

		$dzip = DB::select($query);
		// $x = $dataxml->limit('50')->orderBy('date', 'asc');
		// $dzip = $dataxml->get();
		// echo $x->toSql();
		// echo "<hr>";
		echo "<pre>";
		print_r($dzip);
		echo "</pre>";
	}


	public function get_xml_pak_eko(Request $request){

		$site_id = $request->input('site_id');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");
		$fname = $request->input('fname');

		// $dataxml = DB::table('log_maintenance')
		// ->where('uri_tmp','=','landing_v2/')
		// ->where('status','<','3');

		//7jam
		 $query = "SELECT * FROM log_maintenance WHERE (
			status = 0 OR 
			(status = 2 AND last_update < DATE_SUB( DATE_ADD(NOW(), INTERVAL 8 HOUR ) , INTERVAL 10 MINUTE) ) 
			OR (status = 4 AND msg_status LIKE '%SIK Not Found;%' AND date > DATE_SUB( NOW(), INTERVAL 1 DAY) )
		) ";
			// OR (status = 2 AND last_update < DATE_SUB(NOW(), INTERVAL 10 MINUTE) )
// OR 
// 				(status = 4) 

		if (!empty($site_id)) {
			$query.=" AND site_id='".$site_id."' ";
			// @$dataxml->where('site_id','=',@$site_id);
		}else{
			// @$dataxml->whereNotIn('site_id',['SBZ351','SBZ352','SBZ353']);
		}
		if (!empty($fname)){
			$query.=" AND fname='".$fname."' ";
		}
		$query.=' ORDER BY date ASC LIMIT 50 ';
		$dzip =  DB::select($query);
		// echo $query;
		// $dzip = $dataxml->limit('50')->orderBy('date', 'asc')->get();
		// print_r($dzip);
		// exit();
		if(count($dzip)<1){//jangan pakai empty()
			exit(json_encode(['success'=>false, 'message'=>'Data Not Found Testttt', 'zip_url'=>@$zipping->zip_url, 'data'=>[] , 'query' => $query]));
		}

		// print_r($dzip);
		$data_xml = [];
		$post['title'] = ['Masok Pak Eko!'];
		$post['landing_path'] = 'landing_v2/';
		$post['packing_path'] = 'packing_v2/';

		
		foreach ($dzip as $key => $val) { 
			$post['xml_files'][] = $val->uri_tmp.$val->fname; 
			$data_xml[] = [
				'username'=>$val->username,
				'sik_no'=>$val->sik_no,
				'otp'=>$val->otp,
				'site_id'=>$val->site_id,
				'fname'=>$val->fname,
				'date'=>$val->date,
			];

			$arrayfname[$key] = $val->fname;
		}

		$set_zipname = DB::table('log_maintenance')
		->select('*')
		->whereIn('fname', $arrayfname)
		->update(
			[
				'status'=>"2",
				'last_update'=>$date_now,
			]
		);



		$number_update = DB::table('log_maintenance')
		->where('uri_tmp','=','landing_v2/')
		->where('status','=','1')
		->orderBy('date')
		->limit('5')
		->update(
			[
				'status'=>'2',
				'last_update'=>$date_now,
			]
		);


		// echo "work"; exit;
		
		$string_fields_post = http_build_query($post);
		$url    = "http://103.253.107.45/semeru-api/maintenance/move_zip_xml_pak_eko.php";
		$header = [
		"X-Requested-With: XMLHttpRequest",
		"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_REFERER, $refer);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $string_fields_post );   
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$datas = curl_exec($ch);
		$error = curl_error($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// echo $datas;
		// exit;
		$zipping = json_decode($datas);

		if($zipping->success){
			exit(json_encode(['success'=>true, 'message'=>'SUCCESS', 'zip_url'=>$zipping->zip_url, 'data'=>$data_xml, 'query'=>@$query ]));
		}else{
			exit(json_encode(['success'=>false, 'message'=>'FAILED', 'zip_url'=>$zipping->zip_url, 'data'=>$data_xml ]));
		}

	}


	public function get_xml_sparepart(Request $request){

		$genset_id = $request->input('genset_id');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");

		$dataxml = DB::table('log_sparepart')
		->where('uri_tmp','=','landing_v2/')
		->where('status','<','3');

		if (!empty($genset_id)) {
			@$dataxml->where('genset_id','=',@$genset_id);
		}else{
			// @$dataxml->whereNotIn('genset_id',['SBZ351','SBZ352','SBZ353']);
		}

		$dzip = $dataxml->limit('50')->orderBy('date', 'asc')->get();
		// print_r($dzip);
		// exit;
		if(count($dzip)<1){//jangan pakai empty()
			exit(json_encode(['success'=>false, 'message'=>'Data Not Found', 'zip_url'=>@$zipping->zip_url, 'data'=>[] ]));
		}

		// print_r($dzip);
		$data_xml = [];
		$post['title'] = ['Masok Pak Eko!'];
		$post['landing_path'] = 'landing_v2/';
		$post['packing_path'] = 'packing_GS/';

		
		foreach ($dzip as $key => $val) { 
			$post['xml_files'][] = $val->uri_tmp.$val->fname; 
			$data_xml[] = [
				'username'=>$val->username,
				'spk_no'=>$val->spk_no,
				'otp'=>$val->otp,
				'genset_id'=>$val->genset_id,
				'fname'=>$val->fname,
				'date'=>$val->date,
			];

			$arrayfname[$key] = $val->fname;
		}

		$set_zipname = DB::table('log_sparepart')
		->select('*')
		->whereIn('fname', $arrayfname)
		->update(
			[
				'status'=>"2",
				'last_update'=>$date_now,
			]
		);



		$number_update = DB::table('log_sparepart')
		->where('uri_tmp','=','landing_v2/')
		->where('status','=','1')
		->orderBy('date')
		->limit('5')
		->update(
			[
				'status'=>'2',
				'last_update'=>$date_now,
			]
		);


		// echo "work"; exit;
		
		$string_fields_post = http_build_query($post);
		$url    = "103.253.107.45/semeru-api/maintenance/move_zip_xml_GS.php";
		$header = [
		"X-Requested-With: XMLHttpRequest",
		"User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.84 Safari/537.36" 
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_REFERER, $refer);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $string_fields_post );   
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$datas = curl_exec($ch);
		$error = curl_error($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		// echo $datas;
		$zipping = json_decode($datas);


			// $res['datas'] = $datas;
			// $res['error'] = $error;
			// $res['status'] = $status;
			// $res['zipping'] = $zipping;
			// // $res['datasdd'] = $datas->success;
			// return response($res);

		if($zipping->success){
			exit(json_encode(['success'=>true, 'message'=>'SUCCESS', 'zip_url'=>$zipping->zip_url, 'data'=>$data_xml ]));
		}else{
			exit(json_encode(['success'=>false, 'message'=>'FAILED', 'zip_url'=>$zipping->zip_url, 'data'=>$data_xml ]));
		}

	}


	public function get_xml_ready(Request $request){

		// $data_fmc_cluster = $request->input('data');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");

		//manggil fungsi zip 
		$fbc = new FireBaseController;
		$tmp_fbd = @$fbc->delete_zip();
		$tmp_fb = @$fbc->move_zip();
		
			// return response($tmp_fbd);

		$dataxml = DB::table('log_maintenance')
		->where('uri_tmp','=','landing_v2/')
		->where('status','<','3');

		$dzip = $dataxml->select('zip_name as zipname')->groupBy('zip_name')->get();

		foreach ($dzip as $key => $value) {
			// $res['zip'][$key]['zipname'] = $value->zipname;
			// $res['data'][$key]['sik_'] ='packing_v2/xml/'.$value->zipname;
			$res['zip_url'] ='maintenance/packing_v2/xml/'.$value->zipname;
			$zipnametmp = @$value->zipname.'';
		}
	
		$dxml = DB::table('log_maintenance')
		->where('uri_tmp','=','landing_v2/')
		->where('zip_name','=',$zipnametmp)
		->select('username','sik_no','otp','site_id','fname','date')
		->orderBy('date', 'asc')
		->limit('5')
		->get();
		$res['data'] = $dxml;

		if (!$dzip) {
			$res['success'] = false;
			$res['message'] = 'get data xml error';
			return response($res);
		}


		$number_update = DB::table('log_maintenance')
		->where('uri_tmp','=','landing_v2/')
		->where('status','=','1')
		->orderBy('date')
		->limit('5')
		->update(
			[
				'status'=>'2',
				'last_update'=>$date_now,
			]
		);
	
		// $res['zip'] = $dzip;
		// $res['dataxml'] = $dxml;
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}


	public function get_return_xml(Request $request){

		// $data_fmc_cluster = $request->input('data');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");

		$return_xml_data = $request->input('data');
		$tmpmmsg = @json_encode($return_xml_data);

		$logging = DB::table('log_get_xml')
		->insert([
			'dump_log' => json_encode($request->input()),
			'date' => $date_now
		]);

		@$insertDummData = @DB::table('dum_log_maintenance')->insert(
			[
				'msg' => @$tmpmmsg,
			]
		);


		if ($return_xml_data==null) {
			$res['success'] = false;
			$res['message'] = 'data not found';
			return response($res);
		}

		//manggil fungsi zip 
		// $fbc = new FireBaseController;
		// $tmp_fb = @$fbc->move_zip();

		// $dataxml = DB::table('log_maintenance')
		// ->where('uri_tmp','=','landing_v2/')
		// ->where('status','<','3');

		// $dxml = $dataxml->select('fname','zip_name as zipname','date')->get();
		// $dzip = $dataxml->select('zip_name as zipname')->groupBy('zip_name')->get();

		foreach ($return_xml_data as $key => $value) {

			#disini fungsi update data xml berdasarkan fname

			// $res['data'] = $value;
			// $res['fname'] = $value['fname'];
			// $res['success'] = $value['success'];
			// $res['msg'] = $value['msg'];
			// return response($res);
			$status_tmp = 4;
			if ($value['success']==1) {
				$status_tmp = 3;
			}
			$number_update = DB::table('log_maintenance')
			->where('uri_tmp','=','landing_v2/')
			->where('fname','=',@$value['fname'])
			->update(
				[
					'status'=>$status_tmp,
					'msg_status'=>$value['msg'],
				]
			);
		}


		$fbc = new FireBaseController;
		$tmp_fbd = @$fbc->delete_xml();
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function get_return_xml_GS(Request $request){

		// $data_fmc_cluster = $request->input('data');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date("Y-m-d H:i:s");

		$return_xml_data = $request->input('data');
		$tmpmmsg = @json_encode($return_xml_data);

		@$insertDummData = @DB::table('dum_log_maintenance')->insert(
			[
				'msg' => @$tmpmmsg,
			]
		);


		if ($return_xml_data==null) {
			$res['success'] = false;
			$res['message'] = 'data not found';
			return response($res);
		}

		//manggil fungsi zip 
		// $fbc = new FireBaseController;
		// $tmp_fb = @$fbc->move_zip();

		// $dataxml = DB::table('log_maintenance')
		// ->where('uri_tmp','=','landing_v2/')
		// ->where('status','<','3');

		// $dxml = $dataxml->select('fname','zip_name as zipname','date')->get();
		// $dzip = $dataxml->select('zip_name as zipname')->groupBy('zip_name')->get();

		foreach ($return_xml_data as $key => $value) {

			#disini fungsi update data xml berdasarkan fname

			// $res['data'] = $value;
			// $res['fname'] = $value['fname'];
			// $res['success'] = $value['success'];
			// $res['msg'] = $value['msg'];
			// return response($res);
			$status_tmp = 4;
			if ($value['success']==1) {
				$status_tmp = 3;
			}
			$number_update = DB::table('log_sparepart')
			->where('uri_tmp','=','landing_v2/')
			->where('fname','=',@$value['fname'])
			->update(
				[
					'status'=>$status_tmp,
					'msg_status'=>$value['msg'],
				]
			);
		}


		// $fbc = new FireBaseController;
		// $tmp_fbd = @$fbc->delete_xml();
		
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function rtpoUpdate(Request $request){
		$data = $request->input('data');
		// print_r($data);
		// exit();


		foreach ($data as $param => $row) {


			$rtpo_id = $row['rtpo_id'].'';
			$rtpo_name = $row['rtpo_name'].'';
			$regional = $row['regional'].'';
			$latitude = @$row['latitude'].'';
			$longitude = @$row['longitude'].'';
			$status = @$row['status'].'';
			$last_update = @$row['last_update'].'';
			
			$rtpo_data = DB::table('rtpo')
			->select('*')
			->where('rtpo_id','=',$rtpo_id)
			->first();

			if ($rtpo_data!=null) {
				$updateRtpoData = DB::table('rtpo')
				->where('rtpo_id','=',$rtpo_id)
				->update(
					[
						'rtpo_id' => $rtpo_id,
						'rtpo_name' => $rtpo_name,
						'regional' => $regional,
						'latitude' => @$latitude,
						'longitude' => @$longitude,
						'status' => $status,
						'last_update' => $last_update,
					]
				);
			}else{
				$insertRtpoData = DB::table('rtpo')->insert(
					[
						'rtpo_id' => $rtpo_id,
						'rtpo_name' => $rtpo_name,
						'regional' => $regional,
						'latitude' => @$latitude,
						'longitude' => @$longitude,
						'status' => $status,
						'last_update' => $last_update,
					]
				);
			}

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function nsUpdate(Request $request){
		$data = $request->input('data');

		foreach ($data as $param => $row) {


			$ns_id = $row['ns_id'].'';
			$ns_name = @$row['ns_name'].'';
			$regional = @$row['regional'].'';
			$latitude = @$row['latitude'].'';
			$longitude = @$row['longitude'].'';
			$status = @$row['status'].'';
			$last_update = @$row['last_update'].'';
			
			$ns_data = DB::table('ns')
			->select('*')
			->where('ns_id','=',$ns_id)
			->first();

			$row_ns = [
				'ns_id' => $ns_id,
				'ns_name' => $ns_name,
				'regional' => $regional,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'status' => $status,
				'last_update' => $last_update,
			];

			if ($ns_data!=null) {
				$updateNsData = DB::table('ns')
				->where('ns_id','=',$ns_id)
				->update($row_ns);
			}else{
				$insertNsData = DB::table('ns')->insert($row_ns);
			}

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function fmcUpdate(Request $request){
		$data = $request->input('data');
		// print_r($data);
		// exit();


		foreach ($data as $param => $row) {


			$fmc_id = @$row['fmc_id'].'';
			$fmc_name = @$row['fmc_name'].'';
			$fmc_alias = @$row['fmc_alias'].'';
			$regional = @$row['regional'].'';
			// $latitude = @$row['latitude'].'';
			// $longitude = @$row['longitude'].'';
			$status = @$row['status'].'';
			$last_update = @$row['last_update'].'';
			
			$fmc_data = DB::table('fmc')
			->select('*')
			->where('fmc_id','=',$fmc_id)
			->first();

			if ($fmc_data!=null) {
				$updateFmcData = DB::table('fmc')
				->where('fmc_id','=',$fmc_id)
				->update(
					[
						'fmc_id' => $fmc_id,
						'fmc_name' => $fmc_name,
						'fmc_alias' => $fmc_alias,
						'regional' => $regional,
						// 'latitude' => @$latitude,
						// 'longitude' => @$longitude,
						'status' => $status,
						'last_update' => $last_update,
					]
				);
			}else{
				$insertFmcData = DB::table('fmc')->insert(
					[
						'fmc_id' => $fmc_id,
						'fmc_name' => $fmc_name,
						'fmc_alias' => $fmc_alias,
						'regional' => $regional,
						// 'latitude' => @$latitude,
						// 'longitude' => @$longitude,
						'status' => $status,
						'last_update' => $last_update,
					]
				);
			}

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function site_update(Request $request){
		$data = $request->input('data');

		$successCount = 0;
		print_r("count ". count($data));
		// $res['count'] = count($data);
		// $res['data'] = $data->site_id;
		// return response($res);
		// exit();

		foreach ($data as $param => $row) {

			// print_r($row);
			// exit();

			if (@$row['status']==1) {
				//============
					$site_data = DB::table('site')
					->select('*')
					->where('site_id','=',$row['site_id'])
					->first();

					if ($site_data!=null) {
						# update code...
				        $updateMasterMbp = DB::table('site')
				        ->where('site_id','=',$row['site_id'])
				        ->update(
				          [
				            'rtpo_id' => @$row['rtpo_id'],               //------------
				            'rtpo' => @$row['rtpo'],                     //------------
				            'class_id' => @$row['site_class'],           // class id di site == site_class di master_site
				            'type_id' => null,                          //------------
				            'site_name' => @$row['site_name'],           //------------
				            'latitude' => @$row['latitude'],             //------------
				            'longitude' => @$row['longitude'],           //------------
				            'cluster_fmc_id' => @$row['cluster_fmc_id'], //------------
				            'cluster_fmc' => @$row['cluster_fmc'],       //------------
				            'divisi' => @$row['divisi'],                 //------------  
				            // 'tec_opr_id' => $row['tec_opr_id'],         //------------
				            // 'wil_opr_id' => $row['wil_opr_id'],         //------------
				            'ns_id' => @$row['ns_id'],                   //------------
				            'ns' => @$row['ns'],                         //------------
				            'regional' => @$row['regional'],             //------------
				            // 'Kolom 19' => $row['Kolom 19'],
				            'branch_id' => @$row['branch_id'],           //------------
				            'branch' => @$row['branch'],                 //------------
				            'cluster_id' => @$row['cluster_id'],         //------------
				            'cluster' => @$row['cluster'],               //------------
				            'pic_nik' => @$row['pic_nik'],               //------------
				            'pic_cn' => @$row['pic_cn'],                 //------------
				            'pic_approval_nik' => @$row['pic_approval_nik'],//---------
				            'pic_approval_cn' => @$row['pic_approval_cn'],//-----------
				            'site_class' => @$row['site_class'],         //------------
				            'site_class_periode' => @$row['site_class_periode'],//-----
				            'site_class_revenue' => @$row['site_class_revenue'],//-----
				            'frekuensi' => @$row['frekuensi'],           //------------
				            'kriteria_site' => @$row['kriteria_site'],   //------------
				            // 'status' => $row['status'],                 //------------
				            // 'is_allocated' => $row['is_allocated'],
				            // 'date_mainsfail' => $row['date_mainsfail'],
				            'revenue' => @$row['site_class_revenue'],

				            'alamat' => @$row['alamat'],
				            'ketinggian_tower' => @$row['ketinggian_tower'],
				            'ukuran_shelter' => @$row['ukuran_shelter'],
				            'ukuran_base_frame' => @$row['ukuran_base_frame'],
				            'ukuran_ruang_genset' => @$row['ukuran_ruang_genset'],
				            'active' => @$row['status'],
				            'band' => @$row['band'],
				            'lokasi_site' => @$row['lokasi_site'],

				            // 'node' => $row['node'],
				            'update_by' => 'admin_semeru',
				            'last_update' => @$row['last_update'],       //------------
				          ]
				        );

				        	$successCount = $successCount+1;

					}else{
						 # insert code...
				        $updateMasterMbp = DB::table('site')
				        ->insert(
				          [	
				            'site_id' => @$row['site_id'],           //------------
				            'site_name' => @$row['site_name'],           //------------
				            'rtpo_id' => @$row['rtpo_id'],               //------------
				            'rtpo' => @$row['rtpo'],                     //------------
				            'class_id' => @$row['site_class'],           // class id di site == site_class di master_site
				            'type_id' => null,                          //------------
				            'latitude' => @$row['latitude'],             //------------
				            'longitude' => @$row['longitude'],           //------------
				            'cluster_fmc_id' => @$row['cluster_fmc_id'], //------------
				            'cluster_fmc' => @$row['cluster_fmc'],       //------------
				            'divisi' => @$row['divisi'],                 //------------  
				            // 'tec_opr_id' => $row['tec_opr_id'],         //------------
				            // 'wil_opr_id' => $row['wil_opr_id'],         //------------
				            'ns_id' => @$row['ns_id'],                   //------------
				            'ns' => @$row['ns'],                         //------------
				            'regional' => @$row['regional'],             //------------
				            // 'Kolom 19' => $row['Kolom 19'],
				            'branch_id' => @$row['branch_id'],           //------------
				            'branch' => @$row['branch'],                 //------------
				            'cluster_id' => @$row['cluster_id'],         //------------
				            'cluster' => @$row['cluster'],               //------------
				            'pic_nik' => @$row['pic_nik'],               //------------
				            'pic_cn' => @$row['pic_cn'],                 //------------
				            'pic_approval_nik' => @$row['pic_approval_nik'],//---------
				            'pic_approval_cn' => @$row['pic_approval_cn'],//-----------
				            'site_class' => @$row['site_class'],         //------------
				            'site_class_periode' => @$row['site_class_periode'],//-----
				            'site_class_revenue' => @$row['site_class_revenue'],//-----
				            'frekuensi' => @$row['frekuensi'],           //------------
				            'kriteria_site' => @$row['kriteria_site'],   //------------
				            'status' => '1',                            //------------
				            'is_allocated' => '0',                      //------------
				            // 'date_mainsfail' => $row['date_mainsfail'],
				            'revenue' => @$row['site_class_revenue'],    //------------
				            
				            'alamat' => @$row['alamat'],
				            'ketinggian_tower' => @$row['ketinggian_tower'],
				            'ukuran_shelter' => @$row['ukuran_shelter'],
				            'ukuran_base_frame' => @$row['ukuran_base_frame'],
				            'ukuran_ruang_genset' => @$row['ukuran_ruang_genset'],
				            'active' => @$row['status'],
				            'band' => @$row['band'],
				            'lokasi_site' => @$row['lokasi_site'],

				            'node' => '0',                              //------------
				            'update_by' => 'admin_semeru',              //------------
				            'last_update' => @$row['last_update'],       //------------
				          ]
				        );

				        if ($updateMasterMbp) {
				        	$successCount = $successCount+1;
				        }
					}

				//============
			}

			

		}

		print_r(" success count ". $successCount);

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}

	public function getLastVersion(){

		$version_data = DB::table('verion_app')
		->select('*')
		->orderBy('version_id', 'desc')
		->first();


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $version_data;
		return response($res);
	}

	public function getHistoryVersion(){

		$version_data = DB::table('verion_app')
		->select('*')
		->orderBy('version_id', 'desc')
		->get();


		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $version_data;
		return response($res);
	}

	public function getMaintenanceReason(Request $request){
		$mr_data = DB::table('maintenance_reason')
		->select('*')
		->where('is_sync',0)
		->get();

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $mr_data;
		return response($res);
	}

	public function deleteMaintenanceReason(Request $request){
		$array_mr_id = $request->input('array_mr_id'); 

		// $res['success'] = true;
		// $res['message'] = 'SUCCESS';
		// $res['data'] = $array_mr_id;
		// return response($res);

		foreach ($array_mr_id as $param => $row) {

			DB::table('maintenance_reason')
        	// ->where('reason_id','=',$row['reason_id'])
        	->where('reason_id','=',$row)
			->delete();

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		return response($res);
	}

	public function fmcClusterUpdate(Request $request){
		$data = $request->input('data');

		$successCount = 0;
		// print_r($data);
		print_r("count ". count($data));
		// $res['count'] = count($data);
		// $res['data'] = $data->site_id;
		// return response($res);
		// exit();

		foreach ($data as $param => $row) {

			// print_r($row);
			// exit();

			$lookup_fmc_cluster_data = DB::table('lookup_fmc_cluster')
			->select('*')
			->where('fmc_cluster_id','=',$row['fmc_cluster_id'])
			->first();

			if ($lookup_fmc_cluster_data!=null) {
				# update code...
				$updateLookupFmcCluster = DB::table('lookup_fmc_cluster')
				->where('fmc_cluster_id','=',$row['fmc_cluster_id'])
				->update(
					[
		            // 'fmc_cluster_id' => @$row['fmc_cluster_id'],
						'regional' => @$row['regional'],
						'fmc_id' => @$row['fmc_id'],
						'fmc' => @$row['fmc'],
						'cluster_id' => @$row['cluster_id'],
						'cluster' => @$row['cluster'],
						'rtpo_id' => @$row['rtpo_id'],
						'rtpo' => @$row['rtpo'],
						'cluster_fmc_id' => @$row['cluster_fmc_id'],
						'cluster_fmc' => @$row['cluster_fmc'],
						'branch_id' => @$row['branch_id'],
						'branch' => @$row['branch'],
						'ns_id' => @$row['ns_id'],
						'ns' => @$row['ns'],
						'periode' => @$row['periode'],
						'status' => @$row['status'],
						'last_update' => @$row['last_update'],
					]
				);
				$successCount = $successCount+1;

			}else{
				 # insert code...
				$updateLookupFmcCluster = DB::table('lookup_fmc_cluster')
				->insert(
					[
						'fmc_cluster_id' => @$row['fmc_cluster_id'],
						'regional' => @$row['regional'],
						'fmc_id' => @$row['fmc_id'],
						'fmc' => @$row['fmc'],
						'cluster_id' => @$row['cluster_id'],
						'cluster' => @$row['cluster'],
						'rtpo_id' => @$row['rtpo_id'],
						'rtpo' => @$row['rtpo'],
						'cluster_fmc_id' => @$row['cluster_fmc_id'],
						'cluster_fmc' => @$row['cluster_fmc'],
						'branch_id' => @$row['branch_id'],
						'branch' => @$row['branch'],
						'ns_id' => @$row['ns_id'],
						'ns' => @$row['ns'],
						'periode' => @$row['periode'],
						'status' => @$row['status'],
						'last_update' => @$row['last_update'],
					]
				);

				if ($updateLookupFmcCluster) {
					$successCount = $successCount+1;
				}
			}

		}

		print_r(" success count ". $successCount);

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);
	}


	public function registration_topic(Request $request){

		$getTopic = DB::table('firebase_topic')
		->select('*')
		->where('is_reg','=','0')
		->get();

		$x=0;
		foreach ($getTopic as $param => $row) {
			$topic = $row->topic;

			$topic = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $topic);
			// $topic = str_replace("\n", '', $row['topic']);
			// $topic = str_replace("\r", '', $topic);


			// Sets our destination URL
			$endpoint_url = 'https://iid.googleapis.com/iid/v1/dgx7_0DeB1k:APA91bEcDjEakFxappjsxBIVmns6nkx1XUbdDk1lyc6gQdxzyXm3Zgr6p8uvcuYJMhHKhA7OD0L3D2C-hSKpzrumDlIwy84xU34cN7IEjIl1VmvUquRvYLK9Iss_2Rs7GjWsttY7CeUe/rel/topics/'.$topic;
			// Creates our data array that we want to post to the endpoint

			// return response($endpoint_url);
			$data_to_post = [
				// 'field1' => 'foo',
				// 'field2' => 'bar',
				// 'field3' => 'spam',
				// 'field4' => 'eggs',
			];
			// Sets our options array so we can assign them all at once
			$options = [
				CURLOPT_URL        => $endpoint_url,
				CURLOPT_POST       => true,
				CURLOPT_POSTFIELDS => $data_to_post,
			];
			// Initiates the cURL object
			$curl = curl_init();
			// Assigns our options
			curl_setopt_array($curl, $options);


			$headers = [
				'Content-Type: application/json',
				'Authorization: key=AAAAo6mi6uY:APA91bF5Jrgp7pqCX40LO0WQb6v-eLKd5xIP0xjxivSdlpDg5_iOisegSNQR0GSYwmeICJnumEbckFR6RextiSTkhUA0xBKk-HfMMNzRAWmyXPZzi5FxJvaYescfgyD4s3YTUwB9X78o',
				
			];

			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


			// Executes the cURL POST
			$results = curl_exec($curl);
			// Be kind, tidy up!
			curl_close($curl);

			
			$data[$x]=$topic;

			$x=$x+1;
		}
		// $data = $getTopic;

		$res['data'] = $data;
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		return response($res);

	}

	public function set_topic(Request $request){

		// $get_fmc_data = DB::table('fmc')

		$get_fmc_data = DB::table('firebase_topic')
		->select('*')
		->get();

		$x=0;
		foreach ($get_fmc_data as $param => $row) {

			// $fmc_alias = $row->fmc_alias;
			// $regional = $row->regional;
			// echo $fmc_alias." \n";
			// echo $regional." \n";

			// $fmc_data = DB::table('fmc')
			// ->where('fmc_alias','=',$fmc_alias)
			// ->where('regional','=',$regional)
			// ->select('*')
			// ->first();

            $role_code = str_replace(array('.', ' ','-', "\n", "\t", "\r"), '', @$row->role_code);
            $fmc_alias = str_replace(array('.', ' ','-', "\n", "\t", "\r"), '', @$row->fmc_alias);
            $topic = str_replace(array('.', ' ','-', "\n", "\t", "\r"), '', @$row->topic);
			$updateLfirebase_topic = DB::table('firebase_topic')
			->where('id','=',$row->id)
			->update(
				[
					'role_code' => @$role_code,
					'fmc_alias' => @$fmc_alias,
					'topic' => @$topic,
				]
			);



			// $topic = str_replace(array('_'), '', $topic);
			// $tmp = explode("_",$topic);

			// $regional = $tmp[0];
			// $cluster = $tmp[1];
			// $fmc_alias = $tmp[2];
			// $role_code = @$tmp[3];	

			// lookup_fmc_cluster

			// $cluster_data = DB::table('lookup_fmc_cluster')
			// ->select('*')
			// ->get();

			// foreach ($cluster_data as $porom => $raw) {

			// 	$master_cluster = str_replace(array(' '), '', @$raw->cluster);
				
			// 	if ($master_cluster ==  $cluster) {
				
			// 		$updateLfirebase_topic = DB::table('firebase_topic')
			// 		->where('topic','=',$topic)
			// 		->update(
			// 			[
			// 				'cluster' => @$raw->cluster,
			// 			]
			// 		);
			// 	}
			// }

			// $updateLfirebase_topic = DB::table('firebase_topic')
			// ->where('topic','=',$topic)
			// ->update(
			// 	[
		 //            // 'fmc_cluster_id' => @$row['fmc_cluster_id'],
			// 		'regional' => @$regional,
			// 		'cluster' => @$cluster,
			// 		'fmc_alias' => @$fmc_alias,
			// 		'role_code' => @$role_code,
			// 	]
			// );
		}
	}

	public function delete_image(Request $request){

		// $file = "http://103.253.107.45/semeru-api/maintenance/images_backup/GSK027/2018/05/MT_GSK027_20180518_085855_PHM16_BEFORE.jpg";
		$file = "semeru-api/maintenance/images_backup/GSK027/2018/05/MT_GSK027_20180518_085855_PHM16_BEFORE.jpg";
		if (!unlink($file))
		{
			echo ("Error deleting $file");
		}
		else
		{
			echo ("Deleted $file");
		}


		// $res['files_delete'] = $data;
		// return response($res);

	}



	public function get_previous_data_maintenance(Request $request){
		$site_id = $request->input('site_id'); 

		//AMPUN OM
		$otp_id = $request->input('otp_id'); 
		$sik_no = $request->input('sik_no'); 

		/*
		$_sik = DB::table('sik_site')
		->select('sik_no','otp_id')
		->where(['sik_no'=>$sik_no,'otp_id'=>$otp_id])
		->first();
		
		$data_sik = [
			'sik_no'=>$sik_no,
			'otp_id'=>$otp_id,
			'site_id'=>$site_id,
		];
		if (!$_sik) {
			DB::table('sik_site')->insert($data_sik);
		}
		*/


		//AMPUN YA

		if ($site_id==null) {
			$res['success'] = false;
			$res['message'] = 'Site ID null';
			return response($res);
		}

		$pre_data = DB::table('mt_previous_work_value')
		->select('site_id','id_genset', 'kwh_meter', 'running_hour')
		->where('site_id' , $site_id)
		->first();

		if (!$pre_data) {
		$res['success'] = false;
		$res['message'] = 'Data not found';
		$res['data'] = null;
		return response($res);
		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $pre_data;
		return response($res);
	}

	public function SetSikNew(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$periode = date("Y-m");
		$bulan_sekarang = date("m");

		$sik_no = $request->input('sik_no');
		$maint_plan_id = $request->input('maint_plan_id');
		$rab_id = $request->input('rab_id');
		$site_id = $request->input('site_id');
		$maintenance_schedule = $request->input('maintenance_schedule');
		$min_schedule = $request->input('min_schedule');
		$max_schedule = $request->input('max_schedule');
		$otp_id = $request->input('otp_id');
		$site_name = $request->input('site_name');
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');
		$wil_opr_id = $request->input('wil_opr_id');
		$regional = $request->input('regional');
		$branch_id = $request->input('branch_id');
		$branch = $request->input('branch');
		$cluster_id = $request->input('cluster_id');
		$cluster = $request->input('cluster');
		$tec_opr_id = $request->input('tec_opr_id');
		$divisi = $request->input('divisi');
		$ns_id = $request->input('ns_id');
		$ns = $request->input('ns');
		$rtpo_id = $request->input('rtpo_id');
		$rtpo = $request->input('rtpo');
		$site_class = $request->input('site_class');
		$site_class_periode = $request->input('site_class_periode');
		$site_class_revenue = $request->input('site_class_revenue');
		$frekuensi = $request->input('frekuensi');
		$cluster_fmc_id = $request->input('cluster_fmc_id');
		$cluster_fmc = $request->input('cluster_fmc');
		$fmc_id = $request->input('fmc_id');
		$fmc = $request->input('fmc');
		$kriteria_site = $request->input('kriteria_site');
		$lokasi_site = $request->input('lokasi_site');
		$pemberdayaan_warga = $request->input('pemberdayaan_warga');
		$fix_genset_rent = $request->input('fix_genset_rent');
		$fix_genset_tsel = $request->input('fix_genset_tsel');
		$genset_only = $request->input('genset_only');
		$mandatory_form = $request->input('mandatory_form');
		$kapasitas_genset = $request->input('kapasitas_genset');
		$pic_approval_nik = $request->input('pic_approval_nik');
		$pic_approval_cn = $request->input('pic_approval_cn');
		$status_sik = $request->input('status_sik');
		$mt_status = $request->input('mt_status');
		$mt_date = $request->input('mt_date');
		$last_mt_date = $request->input('last_mt_date');
		$respond_by = $request->input('respond_by');
		$respond_cn = $request->input('respond_cn');
		$respond_status = $request->input('respond_status');
		$respond_date = $request->input('respond_date');
		$reject_reason = $request->input('reject_reason');
		$is_pending = $request->input('is_pending');
		$pending_date = $request->input('pending_date');
		$pending_reason = $request->input('pending_reason');
		$pending_by = $request->input('pending_by');
		$revision_reason = $request->input('revision_reason');
		$mr_complete = $request->input('mr_complete');
		$complete_date = $request->input('complete_date');
		$submit_by = $request->input('submit_by');
		$submit_date = $request->input('submit_date');
		$batas_maks_review = $request->input('batas_maks_review');
		$highlights = $request->input('highlights');
		$team_code = $request->input('team_code');
		$is_late = $request->input('is_late');
		$ref_sik = $request->input('ref_sik');
		$new_sik = $request->input('new_sik');
		$date_created = $request->input('date_created');
		$last_updated = $request->input('last_updated');
		$updated_by = $request->input('updated_by');
		$remark = $request->input('remark');
		$flag = $request->input('flag');

		$query_sik = DB::table('sik_site')->select('*');
		$query_sik->where('sik_no',$sik_no);
		$sik = $query_sik->first();

		if ($sik==NULL){
			$id_sik = DB::table('sik_site')->insertGetId([
				'sik_no' => $sik_no ,
				'maint_plan_id' => $maint_plan_id ,
				'rab_id' => $rab_id ,
				'site_id' => $site_id ,
				'maintenance_schedule' => $maintenance_schedule ,
				'min_schedule' => $min_schedule ,
				'max_schedule' => $max_schedule ,
				'otp_id' => $otp_id ,
				'site_name' => $site_name ,
				'latitude' => $latitude ,
				'longitude' => $longitude ,
				'wil_opr_id' => $wil_opr_id ,
				'regional' => $regional ,
				'branch_id' => $branch_id ,
				'branch' => $branch ,
				'cluster_id' => $cluster_id ,
				'cluster' => $cluster ,
				'tec_opr_id' => $tec_opr_id ,
				'divisi' => $divisi ,
				'ns_id' => $ns_id ,
				'ns' => $ns ,
				'rtpo_id' => $rtpo_id ,
				'rtpo' => $rtpo ,
				'site_class' => $site_class ,
				'site_class_periode' => $site_class_periode ,
				'site_class_revenue' => $site_class_revenue ,
				'frekuensi' => $frekuensi ,
				'cluster_fmc_id' => $cluster_fmc_id ,
				'cluster_fmc' => $cluster_fmc ,
				'fmc_id' => $fmc_id ,
				'fmc' => $fmc ,
				'kriteria_site' => $kriteria_site ,
				'lokasi_site' => $lokasi_site ,
				'pemberdayaan_warga' => $pemberdayaan_warga ,
				'fix_genset_rent' => $fix_genset_rent ,
				'fix_genset_tsel' => $fix_genset_tsel ,
				'genset_only' => $genset_only ,
				'mandatory_form' => $mandatory_form ,
				'kapasitas_genset' => $kapasitas_genset ,
				'pic_approval_nik' => $pic_approval_nik ,
				'pic_approval_cn' => $pic_approval_cn ,
				'status_sik' => $status_sik ,
				'mt_status' => $mt_status ,
				'mt_date' => $mt_date ,
				'last_mt_date' => $last_mt_date ,
				'respond_by' => $respond_by ,
				'respond_cn' => $respond_cn ,
				'respond_status' => $respond_status ,
				'respond_date' => $respond_date ,
				'reject_reason' => $reject_reason ,
				'is_pending' => $is_pending ,
				'pending_date' => $pending_date ,
				'pending_reason' => $pending_reason ,
				'pending_by' => $pending_by ,
				'revision_reason' => $revision_reason ,
				'mr_complete' => $mr_complete ,
				'complete_date' => $complete_date ,
				'submit_by' => $submit_by ,
				'submit_date' => $submit_date ,
				'batas_maks_review' => $batas_maks_review ,
				'highlights' => $highlights ,
				'team_code' => $team_code ,
				'is_late' => $is_late ,
				'ref_sik' => $ref_sik ,
				'new_sik' => $new_sik ,
				'date_created' => $date_created ,
				'last_updated' => $last_updated ,
				'updated_by' => $updated_by ,
				'remark' => $remark ,
				'flag' => $flag ,
			]);
			if ($id_sik>0) {
				$res['success'] = true;
				$res['message'] = 'SUCCESS_INSERT';
			} else{
				$res['success'] = false;
				$res['message'] = 'FAILED_INSERT';
			}
		} else{
			$update_sik_site = DB::table('sik_site')
			->where('sik_no',$sik_no)
			->update([
				'sik_no' => $sik_no ,
				'maint_plan_id' => $maint_plan_id ,
				'rab_id' => $rab_id ,
				'site_id' => $site_id ,
				'maintenance_schedule' => $maintenance_schedule ,
				'min_schedule' => $min_schedule ,
				'max_schedule' => $max_schedule ,
				'otp_id' => $otp_id ,
				'site_name' => $site_name ,
				'latitude' => $latitude ,
				'longitude' => $longitude ,
				'wil_opr_id' => $wil_opr_id ,
				'regional' => $regional ,
				'branch_id' => $branch_id ,
				'branch' => $branch ,
				'cluster_id' => $cluster_id ,
				'cluster' => $cluster ,
				'tec_opr_id' => $tec_opr_id ,
				'divisi' => $divisi ,
				'ns_id' => $ns_id ,
				'ns' => $ns ,
				'rtpo_id' => $rtpo_id ,
				'rtpo' => $rtpo ,
				'site_class' => $site_class ,
				'site_class_periode' => $site_class_periode ,
				'site_class_revenue' => $site_class_revenue ,
				'frekuensi' => $frekuensi ,
				'cluster_fmc_id' => $cluster_fmc_id ,
				'cluster_fmc' => $cluster_fmc ,
				'fmc_id' => $fmc_id ,
				'fmc' => $fmc ,
				'kriteria_site' => $kriteria_site ,
				'lokasi_site' => $lokasi_site ,
				'pemberdayaan_warga' => $pemberdayaan_warga ,
				'fix_genset_rent' => $fix_genset_rent ,
				'fix_genset_tsel' => $fix_genset_tsel ,
				'genset_only' => $genset_only ,
				'mandatory_form' => $mandatory_form ,
				'kapasitas_genset' => $kapasitas_genset ,
				'pic_approval_nik' => $pic_approval_nik ,
				'pic_approval_cn' => $pic_approval_cn ,
				'status_sik' => $status_sik ,
				'mt_status' => $mt_status ,
				'mt_date' => $mt_date ,
				'last_mt_date' => $last_mt_date ,
				'respond_by' => $respond_by ,
				'respond_cn' => $respond_cn ,
				'respond_status' => $respond_status ,
				'respond_date' => $respond_date ,
				'reject_reason' => $reject_reason ,
				'is_pending' => $is_pending ,
				'pending_date' => $pending_date ,
				'pending_reason' => $pending_reason ,
				'pending_by' => $pending_by ,
				'revision_reason' => $revision_reason ,
				'mr_complete' => $mr_complete ,
				'complete_date' => $complete_date ,
				'submit_by' => $submit_by ,
				'submit_date' => $submit_date ,
				'batas_maks_review' => $batas_maks_review ,
				'highlights' => $highlights ,
				'team_code' => $team_code ,
				'is_late' => $is_late ,
				'ref_sik' => $ref_sik ,
				'new_sik' => $new_sik ,
				'date_created' => $date_created ,
				'last_updated' => $last_updated ,
				'updated_by' => $updated_by ,
				'remark' => $remark ,
				'flag' => $flag ,
			]);
			$res['success'] = true;
			$res['message'] = 'SUCCESS_UPDATE';
		
		}
		return response($res);
	}

	public function SetSpkNew(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$periode = date("Y-m");
		$bulan_sekarang = date("m");
		$date = date("Y-m-d H:i:s");

		$replacement_plan_id = $request->input('replacement_plan_id');
		$spk_no = $request->input('spk_no');
		$otp_id = $request->input('otp_id');
		$periode = $request->input('periode');
		$gnst_id = $request->input('gnst_id');
		$gnst_merk_engine = $request->input('gnst_merk_engine');
		$gnst_capacity = $request->input('gnst_capacity');
		$gnst_type_id = $request->input('gnst_type_id');
		$site_id = $request->input('site_id');
		$site_name = $request->input('site_name');
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');
		$replacement_schedule = $request->input('replacement_schedule');
		$min_schedule = $request->input('min_schedule');
		$max_schedule = $request->input('max_schedule');
		$cluster_id = $request->input('cluster_id');
		$cluster = $request->input('cluster');
		$fmc_id = $request->input('fmc_id');
		$fmc = $request->input('fmc');
		$rtpo_id = $request->input('rtpo_id');
		$rtpo = $request->input('rtpo');
		$ns_id = $request->input('ns_id');
		$ns = $request->input('ns');
		$branch_id = $request->input('branch_id');
		$branch = $request->input('branch');
		$regional = $request->input('regional');
		$status_spk = $request->input('status_spk');
		$r_status = $request->input('r_status');
		$r_date = $request->input('r_date');
		$last_r_date = $request->input('last_r_date');
		$respond_status = $request->input('respond_status');
		$respond_by = $request->input('respond_by');
		$respond_cn = $request->input('respond_cn');
		$respond_date = $request->input('respond_date');
		$reject_reason = $request->input('reject_reason');
		$is_pending = $request->input('is_pending');
		$pending_date = $request->input('pending_date');
		$pending_reason = $request->input('pending_reason');
		$pending_by = $request->input('pending_by');
		$revision_reason = $request->input('revision_reason');
		$submit_by = $request->input('submit_by');
		$submit_date = $request->input('submit_date');
		$batas_maks_review = $request->input('batas_maks_review');
		$ref_spk = $request->input('ref_spk');
		$new_spk = $request->input('new_spk');
		$date_created = $request->input('date_created');
		$last_updated = $request->input('last_updated');
		$updated_by = $request->input('updated_by');
		$remark = $request->input('remark');
		$flag = $request->input('flag');

		$query_spk = DB::table('spk_sparepart')->select('*');
		$query_spk->where('spk_no',$spk_no);
		$spk = $query_spk->first();

		if ($spk==NULL){
			$id_spk = DB::table('spk_sparepart')->insertGetId([
				'replacement_plan_id' => $replacement_plan_id,
				'spk_no' => $spk_no,
				'otp_id' => $otp_id,
				'periode' => $periode,
				'gnst_id' => $gnst_id,
				'gnst_merk_engine' => $gnst_merk_engine,
				'gnst_capacity' => $gnst_capacity,
				'gnst_type_id' => $gnst_type_id,
				'site_id' => $site_id,
				'site_name' => $site_name,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'replacement_schedule' => $replacement_schedule,
				'min_schedule' => $min_schedule,
				'max_schedule' => $max_schedule,
				'cluster_id' => $cluster_id,
				'cluster' => $cluster,
				'fmc_id' => $fmc_id,
				'fmc' => $fmc,
				'rtpo_id' => $rtpo_id,
				'rtpo' => $rtpo,
				'ns_id' => $ns_id,
				'ns' => $ns,
				'branch_id' => $branch_id,
				'branch' => $branch,
				'regional' => $regional,
				'status_spk' => $status_spk,
				'r_status' => $r_status,
				'r_date' => $r_date,
				'last_r_date' => $last_r_date,
				'respond_status' => $respond_status,
				'respond_by' => $respond_by,
				'respond_cn' => $respond_cn,
				'respond_date' => $respond_date,
				'reject_reason' => $reject_reason,
				'is_pending' => $is_pending,
				'pending_date' => $pending_date,
				'pending_reason' => $pending_reason,
				'pending_by' => $pending_by,
				'revision_reason' => $revision_reason,
				'submit_by' => $submit_by,
				'submit_date' => $submit_date,
				'batas_maks_review' => $batas_maks_review,
				'ref_spk' => $ref_spk,
				'new_spk' => $new_spk,
				'date_created' => $date_created,
				'last_updated' => $last_updated,
				'updated_by' => $updated_by,
				'remark' => $remark,
				'flag' => $flag,
			]);
			if ($id_spk>0) {
				$res['success'] = true;
				$res['message'] = 'SUCCESS_INSERT';
			} else{
				$res['success'] = false;
				$res['message'] = 'FAILED_INSERT';
			}
		} else{
			$update_spk = DB::table('spk_sparepart')
			->where('spk_no',$spk_no)
			->update([
				'replacement_plan_id' => $replacement_plan_id,
				'spk_no' => $spk_no,
				'otp_id' => $otp_id,
				'periode' => $periode,
				'gnst_id' => $gnst_id,
				'gnst_merk_engine' => $gnst_merk_engine,
				'gnst_capacity' => $gnst_capacity,
				'gnst_type_id' => $gnst_type_id,
				'site_id' => $site_id,
				'site_name' => $site_name,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'replacement_schedule' => $replacement_schedule,
				'min_schedule' => $min_schedule,
				'max_schedule' => $max_schedule,
				'cluster_id' => $cluster_id,
				'cluster' => $cluster,
				'fmc_id' => $fmc_id,
				'fmc' => $fmc,
				'rtpo_id' => $rtpo_id,
				'rtpo' => $rtpo,
				'ns_id' => $ns_id,
				'ns' => $ns,
				'branch_id' => $branch_id,
				'branch' => $branch,
				'regional' => $regional,
				'status_spk' => $status_spk,
				'r_status' => $r_status,
				'r_date' => $r_date,
				'last_r_date' => $last_r_date,
				'respond_status' => $respond_status,
				'respond_by' => $respond_by,
				'respond_cn' => $respond_cn,
				'respond_date' => $respond_date,
				'reject_reason' => $reject_reason,
				'is_pending' => $is_pending,
				'pending_date' => $pending_date,
				'pending_reason' => $pending_reason,
				'pending_by' => $pending_by,
				'revision_reason' => $revision_reason,
				'submit_by' => $submit_by,
				'submit_date' => $submit_date,
				'batas_maks_review' => $batas_maks_review,
				'ref_spk' => $ref_spk,
				'new_spk' => $new_spk,
				'date_created' => $date_created,
				'last_updated' => $last_updated,
				'updated_by' => $updated_by,
				'remark' => $remark,
				'flag' => $flag,
			]);
			$res['success'] = true;
			$res['message'] = 'SUCCESS_UPDATE';
		}

		return response($res);
	}

	public function cekTanggal(Request $request)
	{
		date_default_timezone_set("Asia/Jakarta");
      	$date_now = date('Y-m-d H:i:s');

      	return $date_now;
	}

}
