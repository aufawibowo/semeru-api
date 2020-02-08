<?php
namespace App\Http\Controllers\Api;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use DB;
// use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;
use App\Libraries\SendNotifLib;
use App\Helpers\AppHelper;
use App\User;
use App\Models\SikSite;
use App\Models\UserRtpo;
use App\Models\UserMngNsa;
use App\Models\Mbp;
use App\Models\LookupFmcCluster;

class DashboardController extends Controller {

	public function get_data(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));
		$periode = date('Y-m');

		$username = $request->input('username');
		$scope = '-';
		$user = User::where(['username'=>$username])->first();
		if(empty($user)){
			return $this->response_fail();
		}

		//duplicate of app/Http/Controllers/DashboardController.php
		$data_user = DB::table('users')
					->select('*')
					->where('username',$username)
					->first();

		$rtpo = '-';
		$cluster = '-';

		if ($data_user->user_type=='RTPO') {
			$data_user_rtpo = DB::table('user_rtpo')
			->select('*')
			->where('username',$username)
			->first();

			$rtpo = ($data_user_rtpo->rtpo_name==null) ? '-' : $data_user_rtpo->rtpo_name;
			$rtpo_id = $data_user_rtpo->rtpo_id;
			$scope = ($data_user_rtpo->rtpo_name==null) ? '-' : $data_user_rtpo->rtpo_name;

            $jumlah_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('flag',1)
            ->count();

			$complete_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',7)
			->where('flag',1)
			->count();

			$incomplete_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->whereraw('(respond_status=0 or respond_status=6)')
			->where('flag',1)
			->count();

			$on_review_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',8)
			->where('flag',1)
			->count();

			$approved_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->whereraw('(respond_status=2 or respond_status=4)')
			->where('flag',1)
			->count();

			$rejected_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->whereraw('(respond_status=3 or respond_status=5)')
			->where('flag',1)
			->count();

			$reassign_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',1)
			->where('flag',1)
			->count();

			$auto_approve_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',4)
			->where('flag',1)
			->count();

			$auto_reject_MT = DB::table('sik_site')
			->select('*')
			->where('rtpo_id',$rtpo_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',5)
			->where('flag',1)
			->count();

			$complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

			if ($jumlah_MT==0) {
				$prosentase_pencapaian = 0;
			}
			else{
				$prosentase_pencapaian = $complete_MT/$jumlah_MT;
			}

		} else {
			$cluster = ($data_user->cluster==null) ? '-' : $data_user->cluster;
			$cluster_id = $data_user->cluster_id;
			$scope = ($data_user->cluster==null) ? '-' : $data_user->cluster;

			$jumlah_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('flag',1)
            ->count();

			$complete_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',7)
			->where('flag',1)
			->count();

			$incomplete_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->whereraw('(respond_status=0 or respond_status=6)')
			->where('flag',1)
			->count();

			$on_review_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',8)
			->where('flag',1)
			->count();

			$approved_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->whereraw('(respond_status=2 or respond_status=4)')
			->where('flag',1)
			->count();

			$rejected_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->whereraw('(respond_status=3 or respond_status=5)')
			->where('flag',1)
			->count();

			$reassign_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',1)
			->where('flag',1)
			->count();

			$auto_approve_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',4)
			->where('flag',1)
			->count();

			$auto_reject_MT = DB::table('sik_site')
			->select('*')
			->where('cluster_id',$cluster_id)
			->whereraw('maintenance_schedule like "%'.$month_now.'%"')
			->where('respond_status',5)
			->where('flag',1)
			->count();

			$complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

			if ($jumlah_MT==0) {
				$prosentase_pencapaian = 0;
			}
			else{
				$prosentase_pencapaian = $complete_MT/$jumlah_MT;
			}

			$data_cluster = DB::table('lookup_fmc_cluster')
			->select('*')
			->where('cluster_id',$cluster_id)
			->where('status','1')
			->first();

			$rtpo_id = @$data_cluster->rtpo_id;

		}

		$total_mbp = DB::table('mbp')
		->select('*')
		->where('active',1)
		->where('rtpo_id',@$rtpo_id)
		->orWhere('rtpo_id_home','=',@$rtpo_id)
		->count();

		$mbp_organik = DB::table('mbp')
		->select('*')
		->where('rtpo_id_home',@$rtpo_id)
		->where('active',1)
		->count();

		$mbp_available = DB::table('mbp')
		->select('*')
		->where('rtpo_id',@$rtpo_id)
		->where('status','AVAILABLE')
		->where('active',1)
		->count();

		$mbp_unavailable = DB::table('mbp')
		->select('*')
		->where('rtpo_id',@$rtpo_id)
		->where('status','UNAVAILABLE')
		->where('active',1)
		->count();

		$mbp_waiting = DB::table('mbp')
		->select('*')
		->where('rtpo_id',@$rtpo_id)
		->where('status','WAITING')
		->where('active',1)
		->count();  

		$mbp_on_progress = DB::table('mbp')
		->select('*')
		->where('rtpo_id',@$rtpo_id)
		->where('status','ON_PROGRESS')
		->where('active',1)
		->count();

		$mbp_check_in = DB::table('mbp')
		->select('*')
		->where('rtpo_id',@$rtpo_id)
		->where('status','CHECK_IN')
		->where('active',1)
		->count();

		$mbp_dipinjamkan = DB::table('mbp')
		->select('*')
		->where('rtpo_id_home',@$rtpo_id)
		->where('rtpo_id','!=',@$rtpo_id)
		->where('active',1)
		->count();

		$mbp_pinjaman = DB::table('mbp')
		->select('*')
		->where('rtpo_id_home','!=',@$rtpo_id)
		->where('rtpo_id',@$rtpo_id)
		->where('active',1)
		->count();
		//duplicate of app/Http/Controllers/DashboardController.php

		$res = [
			'success' => 'OK',
			'message' => 'Success',
			'data' => [
				'sql_periode' => $periode,
				'periode' => AppHelper::bulan_tahun_indo($periode),
				// 'cluster' => '-',
				// 'rtpo' => '-',
				'user_type' => $user->user_type,
			],
		];

		$filter=[];
		$filter_mbp=[];

		switch ($user->user_type) {
			case 'AREA':
				$scope='VP NOQM AREA 3';
				break;
			case 'NOS':
			case 'GM': 
				if(empty($user->regional)){
					return $this->response_fail();
				}
				$scope = 'REGIONAL '.$user->regional;
				$filter=['regional'=>$user->regional];
				break;
			case 'CPO': 
				if(empty($user->regional)){
					return $this->response_fail();
				}
				$scope = 'REGIONAL '.$user->regional;
				$filter=['regional'=>$user->regional];
				break;
			case 'MBP': 
				if(empty($user->cluster_id)){
					return $this->response_fail();
				}
				$scope = 'CLUSTER '.$user->cluster;
				$filter=['cluster_id'=>$user->cluster_id];
				break;
			case 'RTPO':
				$rtpo = UserRtpo::where(['username'=>$username,'status'=>1])->first();
				if(empty($rtpo)){
					return $this->response_fail();
				}
				$scope = $rtpo->rtpo_name;
				$filter=['rtpo_id'=>$rtpo->rtpo_id];
				break;
			case 'MNG_NSA':
				$ns = UserMngNsa::where(['username'=>$username,'status'=>1])->first();
				if(empty($ns)){
					return $this->response_fail();
				}
				$scope = $ns->ns;
				$filter=['ns_id'=>$ns->ns_id];
				break;
			default: 
				return $this->response_fail(); 
				break;
		}

		//total status maintenance
		$arr_cetegory_status_mt = ['target', 'completed', 'on_review', 'approved', 'auto_approved', 'reassign', 'rejected', 'auto_rejected'];

		foreach ($arr_cetegory_status_mt as $cat) {
			$maintenance[$cat] = $this->get_count_mr($cat, $periode, $filter);
		}
		$maintenance['incomplete']				 = $maintenance['target'] - $maintenance['completed'];
		$total_approve							 = $maintenance['approved'] + $maintenance['auto_approved'];
		$maintenance['achievement']				 = $total_approve.'/'.$maintenance['target'];

		if($maintenance['target'] == 0){
			$maintenance['percentage_achievement'] = '0%';
		}
		else{
			$maintenance['percentage_achievement']	 = round( $total_approve*100 / $maintenance['target'],2).'%';
		}
		
		// ($maintenance['approved']+$maintenance['auto_approved'])/$maintenance['target'];

		//total status mbp
		$arr_cetegory_status_mbp = ['total','available', 'waiting', 'on_progress', 'check_in', 'unavailable', 'pinjaman', 'dipinjamkan'];

		foreach ($arr_cetegory_status_mbp as $cat) {
			$mbp[$cat] = $this->get_count_mbp($cat, $filter);
		}
		$mbp['site_main_fail'] = $this->get_site_off('site_main_fail', $filter);
		
		//ticket mbp
		$arr_category_tiket_mbp = ['target','meet_sla','over_sla','auto_close','tidak_dikerjakan','complete','incomplete'];
		foreach ($arr_category_tiket_mbp as $cat) {
			$tiket_mbp[$cat] = $this->get_count_ticket_mbp($cat, $periode);
		}

		//link support
		switch ($user->regional) {
			case 'JATIM': $link_support = 'https://t.me/joinchat/A8MX30pMwFSuWG8fkbOSEg'; break;
			case 'JATENG-DIY': $link_support = 'https://t.me/joinchat/B9qgLhK3RVsZx4wTLYQCQg'; break;
			case 'BALI NUSRA': $link_support = 'https://t.me/joinchat/BHF_51JPpijlZtcowh2t'; break;
			default: $link_support = '-'; break;
		}

		//pengumuman
		$data_pengumuman = DB::table('pengumuman')->where('date_expired','>',$date_now)->orderBy('id','desc')->first();

		//faq
		$array_faq = DB::table('faq')->get();
        // $count_faq = DB::table('faq')->count();
        // $random_int = (rand(1,1000)%$count_faq)+1;
        // $data_faq = DB::table('faq')->where('id',$random_int)->first();

		$res['data']['scope']				= $scope;
		$res['data']['maintenance']			= $maintenance;
		$res['data']['tiket_mbp']			= $tiket_mbp;
		$res['data']['mbp']					= $mbp;
		$res['data']['foto_profil']			= '-';
        $res['data']['link_support']		= $link_support;
        $res['data']['flag_pengumuman']		= empty($data_pengumuman) ? false : true;
        $res['data']['pengumuman']			= $data_pengumuman;
        $res['data']['array_faq']			= $array_faq;
		$res['data']['incomplete_MT']		= $incomplete_MT;
		$res['data']['complete_MT']			= $complete_MT;
		$res['data']['on_review_MT']		= $on_review_MT;
		$res['data']['approved_MT']			= $approved_MT;
		$res['data']['reassign_MT']			= $reassign_MT;
		$res['data']['rejected_MT']			= $rejected_MT;
		$res['data']['prosentase_pencapaian']= number_format((float)$prosentase_pencapaian*100,2).'%';
		$res['data']['total_pencapaian']	= $complete_MT.'/'.$jumlah_MT;;
		$res['data']['site_main_fail']		= $mbp['site_main_fail'];
		$res['data']['total_mbp']			= $total_mbp;
		$res['data']['mbp_organik']			= $mbp_organik;
		$res['data']['mbp_available']		= $mbp_available;
		$res['data']['mbp_unavailable']		= $mbp_unavailable;
		$res['data']['mbp_waiting']			= $mbp_unavailable;
		$res['data']['mbp_on_progress']		= $mbp_on_progress;
		$res['data']['mbp_check_in']		= $mbp_check_in;
		$res['data']['mbp_dipinjamkan']		= $mbp_dipinjamkan;
		$res['data']['mbp_pinjaman']		= $mbp_pinjaman;

		return response($res);
	}

	public function get_data_filter(Request $request)
	{
		$date_now = date('Y-m-d');
		$periode = date('Y-m');
		$ns_id = $request->input('ns_id');
		$username = $request->input('username');
		$rtpo_id = $request->input('rtpo_id');
		$regional = $request->input('regional');

		$scope = '-';
		$scope_id = '-';
		$next_id = '-';
		$user = User::where(['username'=>$username])->first();
		if(empty($user)) return $this->response_fail();

		$res = [
			'success' => 'OK',
			'message' => 'Success',
				'data' => [
				'sql_periode' => $periode,
				'periode' => AppHelper::bulan_tahun_indo($periode),
			],
		];

		if(!is_null($regional)){
			$scope='REGIONAL';
		}elseif(!is_null($ns_id)){
			$scope='MNG_NSA';
		}elseif(!is_null($rtpo_id)){
			$scope='RTPO';
		}
		$filter=[]; $filter_mbp=[];

		switch ($scope) {

			case 'REGIONAL': 
				if($regional=='*'){
					$regional='JATIM';
				}
				$scope_id = $regional;
				$scope = 'REGIONAL '.$regional;
				$filter=['regional'=>$regional];

				// $ns = UserMngNsa::where('regional', $regional)->orderBy('id')->first();
				$ns = LookupFmcCluster::where(['regional'=>$regional,'status'=>1])
					->groupBy('ns_id')
					->orderBy('ns')
					->first();
				$next_id = $ns->ns_id;
				break;

			// case 'MBP': 
			// 	if(empty($cluster_id)) return $this->response_fail();
			// 	$scope = 'CLUSTER '.$user->cluster;
			// 	$filter=['cluster_id'=>$user->cluster_id];
			// 	break;

			case 'RTPO':
				if($rtpo_id=="*"){
					// $rtpo = UserRtpo::where('regional', $user->regional)->orderBy('id')->first();
					$rtpo = LookupFmcCluster::where(['regional'=>$user->regional,'status'=>1])
						->groupBy('rtpo_id')
						->orderBy('rtpo')
						->first();
				}else{
					// $rtpo = UserRtpo::where(['rtpo_id'=>$rtpo_id,'status'=>1])->first();
					$rtpo = LookupFmcCluster::where(['rtpo_id'=>$rtpo_id,'status'=>1])
						->groupBy('rtpo_id')
						->orderBy('rtpo')
						->first();
				}
				$scope_id = $rtpo->rtpo_id;
				if(empty($rtpo)) return $this->response_fail();
				$scope = $rtpo->rtpo;
				$filter=['rtpo_id'=>$rtpo->rtpo_id];
				break;

			case 'MNG_NSA':
				if($ns_id=="*"){
					// $ns = UserMngNsa::where('regional', $user->regional)->orderBy('id')->first();
					$ns = LookupFmcCluster::where(['regional'=>$user->regional,'status'=>1])
						->groupBy('ns_id')
						->orderBy('ns')
						->first();
				}else{
					// $ns = UserMngNsa::where(['ns_id'=>$ns_id])->first();
					$ns = LookupFmcCluster::where(['ns_id'=>$ns_id,'status'=>1])
						->groupBy('rtpo_id')
						->orderBy('rtpo')
						->first();
				}
				$scope_id = $ns->ns_id;
				if(empty($ns)) return $this->response_fail();

				$scope = $ns->ns;
				$filter=['ns_id'=>$ns->ns_id];

				// $rtpo = UserRtpo::where(['ns_id'=>$ns->ns_id, 'status'=>1])->orderBy('id')->first();
				$rtpo = LookupFmcCluster::where(['ns_id'=>$ns->ns_id,'status'=>1])
						->groupBy('rtpo_id')
						->orderBy('rtpo')
						->first();
				$next_id = $rtpo->rtpo_id;
				break;

			default: return $this->response_fail(); break;
		}

		//total status maintenance
		$arr_cetegory_status_mt = ['target', 'completed', 'on_review', 'approved', 'auto_approved', 'reassign', 'rejected', 'auto_rejected'];

		foreach ($arr_cetegory_status_mt as $cat) {
			$maintenance[$cat] = $this->get_count_mr($cat, $periode, $filter);
		}
		$maintenance['incomplete'] = $maintenance['target'] - $maintenance['completed'];
		$total_approve = $maintenance['approved']+$maintenance['auto_approved'];
		$maintenance['achievement'] = $total_approve.'/'.$maintenance['target'];
		$maintenance['percentage_achievement'] = round(($total_approve*100)/$maintenance['target'],2).'%';
		// ($maintenance['approved']+$maintenance['auto_approved'])/$maintenance['target'];

		//total status mbp
		$arr_cetegory_status_mbp = ['total','available', 'waiting', 'on_progress', 'check_in', 'unavailable', 'pinjaman', 'dipinjamkan'];

		foreach ($arr_cetegory_status_mbp as $cat) {
			$mbp[$cat] = $this->get_count_mbp($cat, $filter);
		}
		$mbp['site_main_fail'] = $this->get_site_off('site_main_fail', $filter);
		
		//ticket mbp
		$arr_category_tiket_mbp = ['target','meet_sla','over_sla','auto_close','tidak_dikerjakan','complete','incomplete'];
		foreach ($arr_category_tiket_mbp as $cat) {
			$tiket_mbp[$cat] = $this->get_count_ticket_mbp($cat, $periode);
		}

		$res['data']['next_id'] = $next_id;
		$res['data']['scope'] = $scope;
		$res['data']['scope_id'] = $scope_id;
		$res['data']['maintenance'] = $maintenance;
		$res['data']['tiket_mbp'] = $tiket_mbp;
		$res['data']['mbp'] = $mbp;
		

		return response($res);
	}	


	public function get_filter(Request $request)
	{
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        $area = @$request->input('area');
        $regional = @$request->input('regional');
        $ns_id = @$request->input('ns_id');

        $username = $request->input('username');
        $scope = $request->input('scope');

		$user = User::where(['username'=>$username])->first();
		if(empty($user)) return $this->response_fail();


        $res['success'] = 'OK';
        $res['message'] = 'Sucess';
        
        if ($area!=''){
            $list_regional = DB::table('regional')
            ->select('regional as scope_id','regional_name as scope_name')
            ->get();

            $res['data'] = $list_regional;  

        } elseif($regional!=''){
        	//* ditentukan server berdasarkan regional
        	if($regional=='*'){
        		if(empty($user->regional)) return $this->response_fail();
        		$regional = $user->regional;
        	}

            $list_ns = DB::table('ns')
            ->select('ns_id as scope_id','ns_name as scope_name')
            ->where('regional',$regional)
            ->where('status',1)
            ->whereNotIn('ns_id',[26,27])
            ->get();

            $res['data'] = $list_ns;

        } elseif($ns_id!=''){

        	if($ns_id=='*'){
        		if(empty($user->regional)) return $this->response_fail();
        		
        		$list_rtpo = DB::table('lookup_fmc_cluster')
		            ->select('rtpo_id as scope_id','rtpo as scope_name')
		            ->where('regional',$user->regional)
		            ->where('ns_id',$ns_id)
		            ->where('status',1)
		            ->whereNotIn('ns_id',[26,27])
		            ->groupBy('rtpo_id')
		            ->get();
        	}else{
        		$list_rtpo = DB::table('lookup_fmc_cluster')
		            ->select('rtpo_id as scope_id','rtpo as scope_name')
		            ->where('ns_id',$ns_id)
		            ->where('status',1)
		            ->whereNotIn('ns_id',[26,27])
		            ->groupBy('rtpo_id')
		            ->get();
        	}

            $res['data'] = $list_rtpo;
        }else{
	        $res['success'] = 'Invalid_Parameter';
        	$res['message'] = 'Data yang dikirim tidak sesuai.';
        }

        return response($res);
    }



	private function response_fail()
	{
		$res = [
			'success'=>'Incomplete_Data',
			'message'=>'Data yang Anda minta tidak lengkap, silahkan hubungi pihak developer'
		];
		return response($res);
	}

	private function get_site_off($category, $filter)
	{
		$date_now = date('Y-m-d H:i:s');
        // $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

		$site_mainfail = DB::table('site')
	        ->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 
	        	'alarm', 'band','cluster')
	        ->whereNotIn('rtpo_id', [42,43])
	        ->where($filter)
	        ->where('date_mainsfail','>',$min2Day)
	        ->groupBy('site_id')
	        ->get();

	    $count_site_mainfail = 0;
        $count_site_down = 0;

        foreach ($site_mainfail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $count_site_mainfail++;
            }
        }

        if($category=='site_main_fail') return $count_site_mainfail;
        elseif($category=='site_down') return $count_site_down;
        return ($count_site_mainfail+$count_site_down);
	}

	private function get_count_mbp($category='available', $filter=[])
	{

		$mbp = Mbp::where(['active'=>1])->whereNotIn('rtpo_id', [42,43]);

        if(in_array($category, ['dipinjamkan', 'pinjaman'])){
        	//untuk user mitra harusnya filternya cluster_id dan cluster_id_home
        	//berhubung datanya tidak tersedia dan jika mau dilengkapi bnyak yg harus dirubah 
        	//dgn sngat terpaksa filternya diganti pakai rtpo_id
        	//untk beberapa rtpo yg punya lbh dari 1 cluster berpotensi tidak sesuai datanya
        	//agus 19-10-03
        	if(empty($filter)) return 0;

        	if(array_key_exists('cluster_id', $filter)){
        		$mapping = DB::table('lookup_fmc_cluster')
        			->where('cluster_id',$filter['cluster_id'])
            		->where('status','1')->first();

            	$filter['rtpo_id'] = $mapping->rtpo_id;
            	unset($filter['cluster_id']);
			}

			if(array_key_exists('rtpo_id', $filter)){

				if($category=='dipinjamkan')
            		$mbp = $mbp->where('rtpo_id_home',$filter['rtpo_id'])->where('rtpo_id','!=','rtpo_id_home');
            	elseif($category=='pinjaman')
            		$mbp = $mbp->where('rtpo_id',$filter['rtpo_id'])->where('rtpo_id','!=','rtpo_id_home');
			
			}elseif(array_key_exists('ns_id', $filter)){

				if($category=='dipinjamkan')
            		$mbp = $mbp->where('ns_id_home',$filter['ns_id'])->where('ns_id','!=','ns_id_home');
            	elseif($category=='pinjaman')
            		$mbp = $mbp->where('ns_id',$filter['ns_id'])->where('ns_id','!=','ns_id_home');
			
			}elseif(array_key_exists('regional', $filter)){

				if($category=='dipinjamkan')
            		$mbp = $mbp->where('regional_home',$filter['regional'])->where('regional','!=','regional_home');
            	elseif($category=='pinjaman')
            		$mbp = $mbp->where('regional',$filter['regional'])->where('regional','!=','regional_home');
			}

			return $mbp->count();
        }

		switch ($category) {
			case 'total': break;
			case 'available':
				$mbp = $mbp->where(['status'=>'AVAILABLE']);
				break;
			case 'unavailable':
				$mbp = $mbp->where(['status'=>'UNAVAILABLE']);
				break;
			case 'waiting':
				$mbp = $mbp->where(['status'=>'WAITING']);
				break;
			case 'on_progress':
				$mbp = $mbp->where(['status'=>'ON_PROGRESS']);
				break;
			case 'check_in':
				$mbp = $mbp->where(['status'=>'CHECK_IN']);
				break;
			default: return 0; break;
		}
		return $mbp->count();
	}

	private function get_count_mr($category='target', $periode='2019-10', $filter=[])
	{
		$sikSite = SikSite::where($filter)
			->where('maintenance_schedule','like', $periode.'%')
			->where(['flag'=>1]);

		switch ($category) {
			case 'target': break;
			case 'completed':
				$sikSite = $sikSite->where(['mt_status'=>1,'mr_complete'=>1]);
				break;
			case 'on_review':
				$sikSite = $sikSite->where(['mt_status'=>1,'respond_status'=>8]);
				break;
			case 'approved':
				$sikSite = $sikSite->where(['mt_status'=>1,'respond_status'=>2]);
				break;
			case 'rejected':
				$sikSite = $sikSite->where(['mt_status'=>1,'respond_status'=>3]);
				break;
			case 'auto_approved':
				$sikSite = $sikSite->where(['mt_status'=>1,'respond_status'=>4]);
				break;
			case 'auto_rejected':
				$sikSite = $sikSite->where(['mt_status'=>1,'respond_status'=>5]);
				break;
			default: return 0; break;
		}

		return $sikSite->count();
	}

	private function get_count_ticket_mbp($category, $periode, $filter=[])
	{
		if($category=='target'){

			$targetTiket = DB::table('supplying_power')
				->whereraw('date_finish like "'.$periode.'%"')
	        	->where($filter)->whereNotIn('rtpo_id', [42,43])
	        	->whereIn('finish',['DONE','TIDAK DIKERJAKAN','AUTO CLOSE'])->count();

	        $tiketIncomplete = DB::table('supplying_power')
				->whereraw('date_waiting like "'.$periode.'%"')
	        	->where($filter)->whereNotIn('rtpo_id', [42,43])
	        	->where('finish',null)->count();

			return $targetTiket+$tiketIncomplete;

		}elseif($category=='incomplete'){

			$tiketIncomplete = DB::table('supplying_power')
				->whereraw('date_waiting like "'.$periode.'%"')
	        	->where($filter)->whereNotIn('rtpo_id', [42,43])
	        	->where('finish',null)->count();

			return $tiketIncomplete;
		}

		$supplyingPower = DB::table('supplying_power')
			->whereraw('date_finish like "'.$periode.'%"')
	        ->where($filter)->whereNotIn('rtpo_id', [42,43]);

	    switch ($category) {
	    	//'total','meet_sla','over_sla','auto_close','tidak_dikerjakan','complete'
	    	case 'complete': 
	    		//,'TIDAK DIKERJAKAN','AUTO CLOSE'
	    		$supplyingPower = $supplyingPower->whereIn('finish',['DONE']);
	    		break;
	    	case 'complete': 
	    		//,'TIDAK DIKERJAKAN','AUTO CLOSE'
	    		$supplyingPower = $supplyingPower->whereIn('finish',['DONE']);
	    		break;
	    	case 'meet_sla':
	    		$supplyingPower = $supplyingPower->where('finish','DONE')->where('meet_sla',1);
	    		break;
	    	case 'over_sla':
	    		$supplyingPower = $supplyingPower->where('finish','DONE')->where('meet_sla',0);
	    		break;
	    	case 'auto_close':
	    		$supplyingPower = $supplyingPower->where('finish','AUTO CLOSE');
	    		break;	
	    	case 'tidak_dikerjakan':
	    		$supplyingPower = $supplyingPower->where('finish','TIDAK DIKERJAKAN');
	    		break;
	    	default: return 0; break;
	    }

		return $supplyingPower->count();
	}
}