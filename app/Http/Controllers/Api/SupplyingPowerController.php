<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
// use App\Bts;
use DB;
use App\Http\Controllers\Controller;
use App\Models\Mbp;
use App\Models\SupplyingPower;
use App\Models\LookupFmcCluster;
use App\Models\UserMbp;
use App\User;
use App\Models\Site;

class SupplyingPowerController extends Controller
{

	private function response_fail($msg=null)
	{
		if(is_null($msg)) $msg = 'Data yang Anda minta tidak lengkap, silahkan hubungi pihak developer';
		$res = [
			'success'=>false,
			'message'=>$msg
		];
		return response($res);
	}

	public function create_ticket(Request $request){

	    $date_now =date('Y-m-d H:i:s');
	    $date_strtotime = strtotime($date_now." +30 minutes");
	    $date2 = date('Y-m-d H:i:s',$date_strtotime);

	    $mbp_id = $request->input('mbp_id');
	    $site_id = $request->input('site_id');
	    $user_id = $request->input('user_id');

	    //get data mbp
	    $mbp_data = Mbp::where('mbp_id',$mbp_id)->first();
	    if(empty($mbp_data)) return $this->response_fail();
	    //validasi jika mbp sedang ditugaskan
	    if($mbp_data->status!='AVAILABLE') return $this->response_fail('Mbp Tidak Dapat Ditugaskan');

	    //get data rtpo asal mbp
		$rtpo_home_data = LookupFmcCluster::where(['rtpo_id'=>$mbp_data->rtpo_id_home,'status'=>1])
			->groupBy('rtpo_id')
			->first();
		if(empty($rtpo_home_data)) return $this->response_fail();

	    //get data user mbp yang ditugaskan
	    $user_mbp_data = UserMbp::where('mbp_id',$mbp_data->mbp_id)->first();
	    if(empty($user_mbp_data)) return $this->response_fail();

	    //get data user mbp yang ditugaskan secara detail
		$users_data = User::where('username',$user_mbp_data->username)->first();
		if(empty($user_mbp_data)) return $this->response_fail();

		//ambil data rtpo saat ini
		$rtpo_data = LookupFmcCluster::where(['rtpo_id'=>$mbp_data->rtpo_id,'status'=>1])
			->groupBy('rtpo_id')
			->first();
		if(empty($rtpo_data)) return $this->response_fail();

		//ambil data user rtpo
		$rtpo_users_data = User::where('id',$user_id)->first();
		if(empty($rtpo_users_data)) return $this->response_fail();
		//ambil data site
	    $site_data = Site::where('site_id', $site_id)->first();
	    if(empty($site_data)) return $this->response_fail('Data site tidak ditemukan');

	    //siapkan data sp yg akan diinsert
		$data_insert_sp=[
	        'unique_id' => 'SPP_'.$mbp_data->mbp_id.'_'.SUBSTR($date_now, 2,2).SUBSTR($date_now, 5,2).SUBSTR($date_now, 8,2).SUBSTR($date_now, 11,2).SUBSTR($date_now, 14,2).SUBSTR($date_now, 17,2) ,
	        'site_id' => $site_data->site_id.'_',
	        'site_name' => $site_data->site_name,
	        'lokasi_site' => @$site_data->lokasi_site,

	        'date_mainsfail' => $site_data->date_mainsfail,

	        'user_id' => $rtpo_users_data->id,
	        'rtpo_id' => $rtpo_data->rtpo_id,
	        'user_rtpo' => $rtpo_users_data->id,
	        'user_rtpo_cn' => $rtpo_users_data->username,
	        'rtpo_name' => $rtpo_data->rtpo,//gnti

	        'cluster_id' => $site_data->cluster_id,
	        'cluster_fmc_id' => $site_data->cluster_fmc_id,
	        'cluster_fmc' => $site_data->cluster_fmc,
	        'ns_id' => $site_data->ns_id,
	        'ns' => $site_data->ns,
	        'branch_id' => $site_data->branch_id,
	        'branch' => $site_data->branch,
	        'regional' => $site_data->regional,

	        'cluster_site' => $site_data->cluster, 
	        'cluster_id_site' => $site_data->cluster_id, 

        	'mbp_id' => $mbp_data->mbp_id, 

	        'cluster_mbp' => $mbp_data->cluster, 
	        'cluster_id_mbp' => $mbp_data->cluster_id, 
	        
	        'rtpo_id_home' => $rtpo_home_data->rtpo_id,
	        'rtpo_name_home' => $rtpo_home_data->rtpo,//gnti
	        'cluster' => $site_data->cluster,
	        'fmc_id' => $mbp_data->fmc_id,
	        'fmc' => $mbp_data->fmc,
	        'user_mbp' => $users_data->id,
	        'user_mbp_cn' => $users_data->username,

	        'date_waiting' => $date_now,
	        'last_update' => $date_now,
	        'is_sync' =>'0',

	        'finish' => 'AUTO CLOSE',
	        'date_finish' => $date2,
      	];
    //query insert tiket mbp
    // $insertSP = SupplyingPower::insert($data_insert_sp);
      	// return response($insertSP);
      	return response($data_insert_sp);
      	// DB::table('supplying_power')   
	    // ->insert(
	    //   $data_insert_sp
	    // ); 

    if (!$insertSP) {
      $res['success'] = false;
      $res['message'] = 'FAILED_USER_OR_SITE_DATA_NOT_FOUND';
      return response($res);
    }

    //update status mbp
    $editMbp = DB::table('mbp')
    ->where('mbp_id', $mbp_id)
    ->update([
      'status' => 'WAITING',
      'last_update' => $date_now,
    ]);

    if (!$editMbp) {
      $deletesp = DB::table('supplying_power')
      ->where('date_waiting', '=', $date_now)
      ->delete();

      $editMbp = DB::table('mbp')
      ->where('mbp_id', $mbp_id)
      ->update([
        'status' => 'AVAILABLE',
        'last_update' => $date_now,
      ]);

      $res['success'] = false;
      $res['message'] = 'FAILED_UPDATE_STATUS_MBP';
      return response($res);
    }

    //update site
    $editSite = DB::table('site')
    ->where('site_id', $site_id)
    ->update(['is_allocated' => '1']);

    if (!$editSite) {
      $deletesp = DB::table('supplying_power')
      ->where('date_waiting', '=', $date_now)
      ->delete();

      $editMbp = DB::table('mbp')
      ->where('mbp_id', $mbp_id)
      ->update(['status' => 'AVAILABLE',
      'last_update' => $date_now,]);


      $editSite = DB::table('site')
      ->where('site_id', $site_id)
      ->update([
        'is_allocated' => '0',
        'last_update' => $date_now,
      ]);

      $res['success'] = false;
      $res['message'] = 'FAILED_UPDATE_STATUS_SITE';
      return response($res);
    }

    
    $sp_data = DB::table('supplying_power as sp')
    ->select('*')
    ->where('sp.date_waiting','=',$date_now)
    ->first();

    if ($sp_data!=null) {
      $supplyingPowerController = new SupplyingPowerController;
      $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $rtpo_users_data->id, $rtpo_users_data->username, 'WAITING', $rtpo_users_data->name.' menugaskan anda bersama '.$mbp_data->mbp_name.' menuju site '.$site_data->site_name,'', '', $date_now);
    }

    //push token firebase ke array
    $to_token_id = array();
    array_push($to_token_id,@$users_data->firebase_token);

    $fbc = new FireBaseController;
    $tmp_fb =    $fbc->sendNotification('MBP','Tugas untuk '.@$mbp_data->mbp_name.' menuju site '.@$site_data->site_name,$to_token_id,1,@$mbp_data->mbp_id,'MBP_ASSIGNMENT_TO_SITE');

    $notificationController = new NotificationController;
    $tmp = $notificationController->setNotificationV1($rtpo_users_data->username, $users_data->username, 'MBP_ASSIGNMENT_TO_SITE', 'mbp_id', $mbp_id, 'Penugasan MBP', 'MBP_ASSIGNMENT_TO_SITE', @$rtpo_users_data->username.' dari '.@$rtpo_data->rtpo_name.' menugaskan anda menuju site '.$site_id,1,'MBP');
    //ingat data admin ada di table user_admin_fmc bukan users by agus 19-10-04
    $admin_fmc_data = DB::table('user_admin_fmc')
    ->select('*')
    ->where('fmc_id','=',$sp_data->fmc_id)
    ->where('cluster','=',$sp_data->cluster)
    ->where('chat_id','!=',null)
    ->where('chat_id','!=',"")
    ->get();

    foreach ($admin_fmc_data as $param) {


      if (@$param->username!=null) {

        $subject_telegram = 'sendTicketMBP';

        $text_telegram = "[ <b>TIKET MBP</b> ] \nHalo,\nada Tiket MBP untuk ".@$site_users_data->site_name." cluster ".@$sp_data->cluster.", dibuat oleh ".$sp_data->user_rtpo_cn." dari ".$sp_data->rtpo_name." pada tanggal ".@$sp_data->date_waiting.".\n \nJangan lupa untuk mengingatkan User ".@$sp_data->user_mbp_cn." mengenai hal ini.\nTerima Kasih.\n\n-NGSemeru Team-";
        // $text_telegram = urlencode($text_telegram);

        @$inserQueueTelegram = DB::table('queue_telegram')   
        ->insert(
          [
            'subject' => @$subject_telegram,
            'message' => @$text_telegram,
            'chat_id' => @$param->chat_id,

            'send_to' => @$param->username,
            'fmc_id' => @$param->fmc_id,
            'cluster_id' => @$param->cluster_id,
            'rtpo_id' => @$param->rtpo_id,

            'create_at' => @$date_now,

          ]
        );
      }
    }
    $res['success'] = true;
    $res['message'] = 'SUCCESS_INSERT_TO_DATABASE';
    return response($res);  
  }

}

?>