<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class RtpoControllerNew extends Controller
{
  public function requestMbpToSiteDownOld(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $mbp_id = $request->input('mbp_id');
    $site_id = $request->input('site_id');
    $user_id = $request->input('user_id');

    $mbp_data = DB::table('mbp as m')
    ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
    ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
    // ->join('master_mbp as mm', 'm.mbp_id', 'mm.mbp_id')
    ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
    ->join('users as u', 'um.username', 'u.username')
    ->select('*', 'm.cluster as cluster', 'm.cluster_id as cluster_id', 'm.mbp_id', 'm.status as mbp_status', 'rh.rtpo_id as rtpo_id_home', 'rh.rtpo_name as rtpo_home', 'rn.rtpo_id as rtpo_id', 'rn.rtpo_name as rtpo' )
    ->where('m.mbp_id','=',$mbp_id)
    ->first();

    if ($mbp_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_MBP_DATA_NOT_FOUND';
      return response($res);
    }

    if ($mbp_data->mbp_status!='AVAILABLE') {
      $res['success'] = true;
      $res['message'] = 'SUCCESS_INSERT_TO_DATABASE';
      $res['wall'] = 'FAILED_MBP_TERBOKING';
      return response($res);
    }

    $site_users_data = DB::table('rtpo as r')
    ->join('site as s', 'r.rtpo_id', 's.rtpo_id')
    ->join('user_rtpo as ur', 'r.rtpo_id', 'ur.rtpo_id')
    ->join('users as u', 'ur.username', 'u.username')
    ->select('*','s.status as site_status', 's.cluster', 's.cluster_id')
    ->where('s.site_id','=',$site_id)
    ->where('u.id','=',$user_id)
    ->first();

    if ($site_users_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_USER_OR_SITE_DATA_NOT_FOUND';
      return response($res);
    }

    $insertSP = DB::table('supplying_power')   
    ->insert(
      [
        'unique_id' => 'SPP_'.$mbp_data->mbp_id.'_'.SUBSTR($date_now, 2,2).SUBSTR($date_now, 5,2).SUBSTR($date_now, 8,2).SUBSTR($date_now, 11,2).SUBSTR($date_now, 14,2).SUBSTR($date_now, 17,2) ,
        'site_id' => $site_users_data->site_id,
        'site_name' => $site_users_data->site_name,
        'lokasi_site' => @$site_users_data->lokasi_site,

        'date_mainsfail' => $site_users_data->date_mainsfail,

        'user_id' => $site_users_data->id,
        'rtpo_id' => $site_users_data->rtpo_id,
        'user_rtpo' => $site_users_data->id,
        'user_rtpo_cn' => $site_users_data->username,
        'rtpo_name' => $site_users_data->rtpo_name,
        'tec_opr_id' => $site_users_data->tec_opr_id,
        'wil_opr_id' => $site_users_data->wil_opr_id,
        'cluster_fmc_id' => $site_users_data->cluster_fmc_id,
        'cluster_fmc' => $site_users_data->cluster_fmc,
        'ns_id' => $site_users_data->ns_id,
        'ns' => $site_users_data->ns,
        'branch_id' => $site_users_data->branch_id,
        'branch' => $site_users_data->branch,
        'regional' => $site_users_data->regional,


        'cluster_site' => $site_users_data->cluster, 
        'cluster_id_site' => $site_users_data->cluster_id, 



        'mbp_id' => $mbp_data->mbp_id, 

        'cluster_mbp' => $mbp_data->cluster, 
        'cluster_id_mbp' => $mbp_data->cluster_id, 
        
        'rtpo_id_home' => $mbp_data->rtpo_id_home,
        'rtpo_name_home' => $mbp_data->rtpo_home,
        'cluster' => $mbp_data->cluster,
        'fmc_id' => $mbp_data->fmc_id,
        'fmc' => $mbp_data->fmc,
        'user_mbp' => $mbp_data->id,
        'user_mbp_cn' => $mbp_data->username,

        'date_waiting' => $date_now,
        'last_update' => $date_now,
        'is_sync' =>'0',
      ]
    );

    if (!$insertSP) {
      $res['success'] = false;
      $res['message'] = 'FAILED_USER_OR_SITE_DATA_NOT_FOUND';
      return response($res);
    }

    //update mbp
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
      ->join('users as u_rtpo', 'sp.user_rtpo_cn', 'u_rtpo.username')
      ->join('users as u_fmc', 'sp.user_mbp_cn', 'u_fmc.username')
      ->select('sp.*', 'u_rtpo.name as user_rtpo_cn', 'u_fmc.name as user_mbp_cn')
      ->where('sp.date_waiting','=',$date_now)
      ->first();

      if ($sp_data!=null) {
        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $site_users_data->id, $site_users_data->username, 'WAITING', $site_users_data->name.' menugaskan anda bersama '.$mbp_data->mbp_name.' menuju site '.$site_users_data->site_name,'', '', $date_now);
      }

      $insertSP = DB::table('queue_firebase')
      ->insert(
        [
          'type' => "mbp_id",
          'type_id' => @$mbp_data->mbp_id,
          'subject' => "Penugasan MBP",
          'fb_token' => @$mbp_data->firebase_token,
          'message' => 'Tugas untuk '.@$mbp_data->mbp_name.' menuju site '.@$site_users_data->site_name,
          'create_at' => $date_now,
        ]
      );

      //$fbc = new NotificationController;
      //$tmp_fb =    $fbc->sendNotification('MBP','Tugas untuk '.@$mbp_data->mbp_name.' menuju site '.@$site_users_data->site_name,@$mbp_data->firebase_token,1,@$mbp_data->mbp_id,'MBP_ASSIGNMENT_TO_SITE');

      $admin_fmc_data = DB::table('users')
      ->select('*')
      ->where('fmc_id','=',$sp_data->fmc_id)
      ->where('cluster','=',$sp_data->cluster)
      ->where('chat_id','!=',null)
      ->where('chat_id','!=',"")
      ->get();

      foreach ($admin_fmc_data as $param) {

        if (@$param->username!=null) {

          $subject_telegram = 'sendTicketMBP';

          $text_telegram = "[ <b>TIKET MBP</b> ] \nHalo,\nada Tiket MBP untuk ".@$site_users_data->site_name." cluster ".@$sp_data->cluster.", dibuat oleh ".$sp_data->user_rtpo_cn." dari ".$sp_data->rtpo_name." pada tanggal ".@$sp_data->date_waiting.".\n \nJangan lupa untuk mengingatkan User ".@$sp_data->user_mbp_cn." mengenai hal ini.\nTerima Kasih.\n-NGSemeru Team-";
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
  
  public function cancelRequestMbpToSiteDown(Request $request){
    /*
    ketika rtpo membatalkan penugasan mbp terhadap site tertentu, maka :
    > mbp status dirubah jadi available
    > site allocation dirubah jadi '0'
    > tabel sp di set cancel..:D*/

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $sp_id = $request->input('sp_id');
    $mbp_id = $request->input('mbp_id');
    $reason = @$request->input('reason');
    $cancel_by = @$request->input('send_by');
    $cancel_category = @$request->input('cancel_category');

    if (substr($cancel_category, 0, 5)=='Tidak') {
      $tidak_dikerjakan = 1;
    } else{
      $tidak_dikerjakan = 0;
    }

//-------------------------------------------------------
    // $sp_data = DB::table('supplying_power')
    // ->select('*')
    // ->where('mbp_id','=',$mbp_id)
    // ->where('finish','=',null)
    // ->first();
    // if ($sp_data!=null) {
    //   $supplyingPowerController = new SupplyingPowerController;
    //   $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $sp_data->user_rtpo, $sp_data->user_rtpo_cn, 'CANCEL','user RTPO '.$sp_data->user_rtpo_cn.' melakukan pembatalan penugasan dengan alasan '.$reason ,$reason, '', $date_now);
    // }

      // else{
    //   $res['message'] = 'test';
    // }
    // $res['success'] = true;
    // $res['data'] =  $sp_data;
    // return $res;  

    //--------------------------------------------------


    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp_id','=',$mbp_id)
    ->first();

    switch ($mbp_data->status) {
      case "UNAVAILABLE":
      // POPUP ANDA TIDAK SEDANG DITUGASKAN KARENA STATUS ANDA UNAVAILABLE
      $res['success'] = false;
      $res['message'] = 'YOUR_STATUS_UNAVAILABLE';
      // $res['data'] =  $datax;
      return response($res);
      break;
      case "AVAILABLE":
      // POPUP ANDA TIDAK SEDANG DITUGASKAN KARENA STATUS ANDA AVAILABLE
      $res['success'] = false;
      $res['message'] = 'YOUR_STATUS_AVAILABLE';
      // $res['data'] =  $datax;
      return response($res);
      break;
      default:
      // SET STATUS MBP KEMBALI KE AVAILABLE
      // SET TABEL SP JADI CANCEL DAN DONE
      // SET TABEL SITE MENJADI TIDAK DI ALOKASIKAN..:d
      if ($tidak_dikerjakan) {
        $tmp = $this->tiketMBPTidakDikerjakan($sp_id,$reason,$cancel_by);
      } else{
        $tmp = $this->CancelRequestMbp($mbp_data->mbp_name,$mbp_id, $reason,$cancel_by, $cancel_category);
      }
      return response($tmp);
    }
  }

  public function CancelRequestMbp($mbp_name,$mbp_id,$reason,$cancel_by, $cancel_category){


    // $notificationController = new NotificationController;
    // $tmp = $notificationController->setNotificationCancelMbpAssignment($mbp_name,$mbp_id);
    // $res['mbp_name'] = $mbp_name;
    // $res['mbp_id'] = $mbp_id;
    //     $res['data'] =  $tmp;
    // return $res; 

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');


     $sp_data = DB::table('supplying_power')
    ->select('*')
    ->where('mbp_id','=',$mbp_id)
    ->where('finish','=',null)
    ->first();

    if (@$cancel_by==null) {
      $cancel_by='RTPO';
    }

    $mbp_data = DB::table('supplying_power')                        // jadikan finish = cancel
    ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')      // jadikan mbp kembali available #sesuaikan status"nya
    ->join('site', 'supplying_power.site_id', '=', 'site.site_id')  // jadikan status alokasinya jadi '0' kembali
    ->where('supplying_power.mbp_id','=',$mbp_id)
    ->where('supplying_power.detail_finish','=',null)
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


        'supplying_power.cancel_reason' =>$reason,
        'supplying_power.cancel_category' =>@$cancel_category,
        'supplying_power.reason_by' => @$cancel_by,
        'supplying_power.cancel_approved_by' => @$cancel_by,
        // 'supplying_power.last_update' => $date_now,
      ]
    );


    //----------------------------------------------------------------------
    // $getCancellationLetter = DB::table('cancel_details')
    // ->select('*')
    // ->where('respon_time','!=', null)
    // ->where('mbp_id','=',$mbp_id)
    // ->first();


    $getCancellationLetter = DB::table('mbp_trouble')
    ->select('*')
    ->where('is_active','=',1)
    ->where('mbp_id','=',$mbp_id)
    ->first();


    if ($getCancellationLetter!=null) {
      $updateCancellationLetter = DB::table('mbp_trouble')
      ->where('is_active','=',1)
      ->where('mbp_id','=',$mbp_id)
      ->update(
        [
          // 'cancel_details.user_id_rtpo' =>$sp_data->user_rtpo,
          'is_active' =>'0',
          'respon_date' =>date('Y-m-d H:i:s'),
        ]
      );
    }

    //----------------------------------------------------------------------
    if ($mbp_data) {

      if ($sp_data!=null) {
        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $sp_data->user_rtpo, $sp_data->user_rtpo_cn, 'CANCEL','user RTPO '.$sp_data->user_rtpo_cn.' melakukan pembatalan penugasan dengan alasan '.@$reason ,''.@$reason, '', $date_now);
      }



      $notificationController = new NotificationController;
      $tmp = $notificationController->setNotificationCancelMbpAssignment($mbp_name,$mbp_id);
      if ($tmp) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
      // $res['data'] =  $datax;
        return $res;
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_SEND_NOTIFICATION';
        $res['data'] =  $tmp;
        return $res;  
      }
    }
  }

  public function cekSamplingFormula(Request $request){

    $checkXml = DB::table('sampling_site_formula')
    ->select('*')
    ->get();

    foreach ($checkXml as $key => $value) {
      $res[$key]['site_type'] = $value->site_type;
      $res[$key]['total'] = $value->bobot_ac_indoor_outdoor 
      + $value->bobot_ef_pemeliharaan_exhaust_fan
      + $value->bobot_sp_sistem_penerangan
      + $value->bobot_hk_hasil_kwh
      + $value->bobot_label_mcb_beban
      + $value->bobot_fungsi_alarm_recti
      + $value->bobot_kebersihan_halaman
      + $value->bobot_keberihan_shelter
      + $value->bobot_kebersihan_perangkat
      + $value->bobot_kebersihan_filter_air_intake
      + $value->bobot_engsel_pintu_roda_pagar
      + $value->bobot_kebersihan_saluran_air_dan_akses_site
      + $value->bobot_intergasi_grounding_perangkat
      + $value->bobot_intergasi_grounding_int_ekst
      + $value->bobot_data_radio_dan_transmisi_ft01
      + $value->bobot_data_infras_site_dan_sarana_pada_ft01
      + $value->bobot_kebersihan_gnst_ats_pemipaan
      + $value->bobot_oli_dan_filter_filter
      + $value->bobot_sistem_ats
      + $value->bobot_alarm_ext_gnst
      + $value->bobot_lvl_bbm;
      // return response($res);            
    }

    // $res['data'] = $checkXml;
    return response($res);      
  }

  public function getListSamplingSite(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $rtpo_id = $request->input('rtpo_id');
    $periode = @$request->input('periode');
    if (@$periode==null) {
      $periode = date('Y-m');
    }


    $checkSampling = DB::table('sampling_site')
    ->select('sampling_id','site_id','site_name','regional','status_sampling', 'genset', 'kriteria_site','nilai_total','periode')
    ->where('rtpo_id','=',$rtpo_id)
    //->wherein('periode',['2018-10','2018-11','2018-12','2019-01'])
    ->where('periode',$periode)
    ->where('flag','=','1')
    ->orderBy('periode','site_id')
    ->get();

    if ($rtpo_id==33) {
      $checkSampling = DB::table('sampling_site')
      ->select('sampling_id','site_id','site_name','regional','status_sampling', 'genset', 'kriteria_site','nilai_total','periode')
      ->where('rtpo_id','=',$rtpo_id)
      //->wherein('periode',['2018-10','2018-11','2018-12','2019-01'])
      ->wherein('periode',[$periode])
      ->where('flag','=','1')
      ->orderBy('periode','site_id')
      ->get();
    }

    foreach ($checkSampling as $key => $value) {
      $periode2 = $this->bulan_tahun_indo($value->periode);
      $value->periode = $periode2;
    }


    $res['success'] = true;
    $res['message'] = 'success'; 
    if (@$checkSampling==null) {
      $res['success'] = false;
      $res['message'] = 'data not found'; 
    }
    $res['data'] = @$checkSampling;

    return response($res); 

  }

  public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }

  public function checkDistanceSamplingSite(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $now = date('Y-m-d H:i:s');

    $res['success'] = true;
    $res['message'] = 'success site';
    //$res['login'] =1;      
    //return response($res); 

    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');
    $site_id = $request->input('site_id');

    
    $log_sampling = DB::table('log_masuk_sampling')->insertGetId([
      'latitude' => $latitude,
      'longitude' => $longitude,
      'site_id' => $site_id,
      'date_created' => $now
    ]);
    

    if ($latitude==null) {
      $res['success'] = false;
      $res['message'] = 'latitude is null'; 

      $update_log = DB::table('log_masuk_sampling')
      ->where('id',$log_sampling)
      ->update([
        'keterangan'=> $res['message'],
      ]);

      return response($res); 
    }
    if ($longitude==null) {
      $res['success'] = false;
      $res['message'] = 'longitude is null'; 

      $update_log = DB::table('log_masuk_sampling')
      ->where('id',$log_sampling)
      ->update([
        'keterangan'=> $res['message'],
      ]);

      return response($res); 
    }
    if ($site_id==null) {
      $res['success'] = false;
      $res['message'] = 'site_id is null'; 

      $update_log = DB::table('log_masuk_sampling')
      ->where('id',$log_sampling)
      ->update([
        'keterangan'=> $res['message'],
      ]);

      return response($res); 
    }

      $res['success'] = true;
      $res['message'] = 'success';
      $res['login'] =0;   


    $checksite = DB::table('site')
    ->select('site_id','latitude','longitude')
    ->where('site_id','=',$site_id)
    ->first();
    $res['lat'] = @$checksite->latitude;
    $res['lon'] = @$checksite->longitude;
    $jaraks = $this->distance($latitude, $longitude, @$checksite->latitude, @$checksite->longitude, "K");
    $res['jarak1'] =$jaraks . " km";

    $update_log = DB::table('log_masuk_sampling')
    ->where('id',$log_sampling)
    ->update([
      'lat_site' => $res['lat'],
      'lon_site' => $res['lon'],
      'jarak' => $jaraks,
    ]);

    if ($jaraks<1) {
      $res['success'] = true;
      $res['message'] = 'success site';
      $res['login'] =1;

      $update_log = DB::table('log_masuk_sampling')
      ->where('id',$log_sampling)
      ->update([
        'keterangan'=> 'SITE '.$res['login'],
      ]);

      return response($res); 
    }

    $checkreport = DB::table('report_location_site')
    ->select('site_id','new_lat','new_lon','delivery_date')
    ->where('site_id','=',$site_id)
    ->orderBy('delivery_date', 'desc')
    ->first();


    $res['new_lat'] = @$checkreport->new_lat;
    $res['new_lon'] = @$checkreport->new_lon;
    $jarak = $this->distance($latitude, $longitude, @$checkreport->new_lat, @$checkreport->new_lon, "K");
    $res['jarak'] =$jarak . " km";

    if ($jarak<1) {
      $res['success'] = true;
      $res['message'] = 'success report site';
      $res['login'] =1;     

      $update_log = DB::table('log_masuk_sampling')
      ->where('id',$log_sampling)
      ->update([
        'keterangan'=> 'RLS '.$res['login'],
      ]);

      $update_log = DB::table('log_masuk_sampling')
      ->where('id',$log_sampling)
      ->update([
        'keterangan'=> $res['login'],
      ]);

      return response($res); 
    }

    // $res['data'] = @$checkreport;

    // $res['success'] = true;
    // $res['message'] = 'success'; 
    // if (@$checkSampling==null) {
    //   $res['success'] = false;
    //   $res['message'] = 'data not found'; 
    // }

    return response($res); 

  }
   public function getDetailSamplingSite(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');
    $sampling_id = $request->input('sampling_id');


    $checkSampling = DB::table('sampling_site')
    ->select('*')
    ->where('sampling_id','=',$sampling_id)
    ->first();


    $res['success'] = true;
    $res['message'] = 'success'; 
    if (@$checkSampling->sik_no==null) {
      $res['success'] = false;
      $res['message'] = 'data not found'; 
    }
    $res['data'] = @$checkSampling;

    return response($res); 

  }


   public function getFinishSamplingSite(Request $request){

    $getSampling = DB::table('sampling_site')
    ->select('*')
    ->where('flag','=',1)
    ->where('is_finish','=',1)
    ->where('is_sync','=',0)
    ->limit(50)
    ->get();
    return response($getSampling); 
  }


   public function updateSyncSamplingSite(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    // $date_now = date('Y-m-d H:i:s');
    // $periode = date('Y-m');
    $data = $request->input('data');

    foreach ($data as $key => $value) {
      $date_now = date('Y-m-d H:i:s');
      $value['last_updated'] = date('Y-m-d H:i:s',strtotime("+".$key." second",strtotime($date_now)));

      $updateSampling = DB::table('sampling_site')
      ->where('sampling_id','=',$value['sampling_id'])
      ->update(['is_sync' =>'1',]);

      if ($updateSampling) {
        $res[$key]['sampling_site'] = $value['sampling_id'];
        $res[$key]['success'] = true;
        $res[$key]['message'] = 'success';
      }else{
        $res[$key]['sampling_site'] = $value['sampling_id'];
        $res[$key]['success'] = false;
        $res[$key]['message'] = 'is sync sudah 1';
      }

    }

    return response($res); 
  }

  public function createSamplingSite(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');
    $data = $request->input('data');

    foreach ($data as $key => $value) {

      $checkSampling = DB::table('sampling_site')
      ->select('site_id')
      ->where('site_id','=',$value['site_id'])
      ->where('periode','=',$value['periode'])
      ->first();

      if (@$checkSampling->site_id==null) {
        // $res['msg'] = "data gak onok pak eko";
        $value['date_created'] = $date_now;
        $insertsampling = DB::table('sampling_site')->insert([$value]);

        if ($insertsampling) {
        // $res['msg'] = "Alhamdulillah";  
          $res['success'] = true;
          $res['message'] = 'success';   
        }else{
          $res['success'] = false;
          $res['message'] = 'failed to insert data';
        // return response($res);    
        }
      }else{

        $updateSampling = DB::table('sampling_site')
        ->where('sampling_id','=',$value['sampling_id'])->update($value);
        
        $res['success'] = true;
        $res['message'] = 'success';
        
      }

    }
    return response($res);  
  }

  public function createSamplingSiteFlag(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');
    $data = $request->input('data');

    foreach ($data as $key => $value) {

    // return response($value['sampling_id']); 
      $checkSampling = DB::table('sampling_site')
      ->select('site_id')
      ->where('site_id','=',$value['site_id'])
      ->where('periode','=',$value['periode'])
      // ->where('created_date','like',$periode.'%')
      ->first();

      if (@$checkSampling->site_id==null) {
        // $value['last_updated'] = date('Y-m-d H:i:s'); 
        $res['msg'] = "data gak onok pak eko";

        $value['date_created'] = $date_now;
        $insertsampling = DB::table('sampling_site')
        ->insert([$value]);

        if ($insertsampling) {
        // $res['msg'] = "Alhamdulillah";  

          $res['success'] = true;
          $res['message'] = 'success';   
        }else{
          $res['success'] = false;
          $res['message'] = 'failed to insert data';
        // return response($res);    
        }

      }else{

        // $res['last_updated'][$key] = date('Y-m-d H:i:s',strtotime("+".$key." second",strtotime($date_now)));
        // return response($value);
      // $value['last_updated'] = $value['last_updated'];

        $updateSampling = DB::table('sampling_site')
        ->where('sampling_id','=',$value['sampling_id'])
        // ->where('created_date','like',$periode."%")
        ->update($value);

          $res['success'] = true;
          $res['message'] = 'success';
        // return response($res);  
        // $res['msg'] = "Alhamdulillah coba lagi yuk update";  

      }

    }
    return response($res);  
  }

  public function insertSamplingSite(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');
    // $data = $request->input('data');

    $logging = DB::table('log_sampling')
    ->insert([
      'isi' => json_encode($request->input()),
      'date' => $date_now,
    ]);

    $data['sampling_by']=@$request->input('sampling_by');
    $data['sampling_id']=@$request->input('sampling_id');
    $data['sampling_date']=$date_now;
    if ($data['sampling_by']==null) {
      $res['success'] = false;
      $res['message'] = 'sampling_by not found';
      return response($res); 
    }
    if ($data['sampling_id']==null) {
      $res['success'] = false;
      $res['message'] = 'sampling_id not found';
    }

    $data['nilai_kebersihan_ac']=$request->input('nilai_kebersihan_ac');
    $data['nilai_suhu_ac_25']=$request->input('nilai_suhu_ac_25');
    $data['nilai_kebersihan_exhaust_fan']=$request->input('nilai_kebersihan_exhaust_fan');
    $data['nilai_sp_lampu_shelter']=$request->input('nilai_sp_lampu_shelter');
    $data['nilai_sp_lampu_halaman']=$request->input('nilai_sp_lampu_halaman');
    $data['nilai_sp_lampu_tower']=$request->input('nilai_sp_lampu_tower');
    $data['nilai_segel_kwh']=$request->input('nilai_segel_kwh');
    $data['nilai_pencatatan_kwh']=$request->input('nilai_pencatatan_kwh');
    $data['nilai_putaran_piringan_kwh']=$request->input('nilai_putaran_piringan_kwh');
    
    $data['nilai_label_mcb_beban']=$request->input('nilai_label_mcb_beban');
    $data['nilai_fungsi_alarm_recti']=$request->input('nilai_fungsi_alarm_recti');
    $data['nilai_kebersihan_halaman']=$request->input('nilai_kebersihan_halaman');
    $data['nilai_kebersihan_shelter']=$request->input('nilai_kebersihan_shelter');
    $data['nilai_kebersihan_perangkat']=$request->input('nilai_kebersihan_perangkat');
    $data['nilai_kebersihan_filter_air_intake']=$request->input('nilai_kebersihan_filter_air_intake');
    $data['nilai_engsel_pintu_roda_pagar']=$request->input('nilai_engsel_pintu_roda_pagar');
    $data['nilai_kebersihan_saluran_air_dan_akses_site']=$request->input('nilai_kebersihan_saluran_air_dan_akses_site');
    $data['nilai_intergasi_grounding_perangkat']=$request->input('nilai_intergasi_grounding_perangkat');
    $data['nilai_intergasi_grounding_int_ekst']=$request->input('nilai_intergasi_grounding_int_ekst');
    $data['nilai_data_radio_dan_transmisi_ft01']=$request->input('nilai_data_radio_dan_transmisi_ft01');
    $data['nilai_data_infras_site_dan_sarana_pada_ft01']=$request->input('nilai_data_infras_site_dan_sarana_pada_ft01');
    $data['nilai_kebersihan_gnst_ats_pemipaan']=$request->input('nilai_kebersihan_gnst_ats_pemipaan');
    $data['nilai_oli_dan_filter_filter']=$request->input('nilai_oli_dan_filter_filter');
    $data['nilai_sistem_ats']=$request->input('nilai_sistem_ats');
    $data['nilai_alarm_ext_gnst']=$request->input('nilai_alarm_ext_gnst');
    $data['nilai_lvl_bbm']=$request->input('nilai_lvl_bbm');
    $data['is_finish']=$request->input('is_finish');
    $data['is_sync']=0;
    
    if ($data['sampling_by']==null) {  
      $res['success'] = false;
      $res['message'] = 'send by not found';
      return response($res); 
    }


    $checkSampling = DB::table('sampling_site')
    ->select('site_id','kriteria_site','genset')
    ->where('sampling_id','=',$data['sampling_id'])
    ->first();
    if (@$checkSampling->site_id==null) {
      $res['success'] = false;
      $res['message'] = 'data sampling not found';
      return response($res); 
    }

    $data['kriteria_site']=$checkSampling->kriteria_site;
    $data['genset']=$checkSampling->genset;

    $hasil = $this->hitungSampling($data);

    // $res['hasil'] = $hasil;
    // $res['data awal'] = $data;
    $data = array_merge((array)$data,(array)$hasil);

    // $res['data akhir'] = $data;
    // return response($res); 


    // $data['hasil_ac_indoor_outdoor'] = $hasil['hasil_ac_indoor_outdoor'];
    // $data['hasil_pemeliharaan_exhaust_fan'] = $hasil['hasil_pemeliharaan_exhaust_fan'];
    // $data['hasil_sistem_penerangan'] = $hasil['hasil_sistem_penerangan'];
    // $data['hasil_pemeriksaan_kwh'] = $hasil['hasil_pemeriksaan_kwh'];
    // $data['nilai_total'] = $hasil['hasil'];
    if (@$hasil['nilai_total']==0) {
      $data['status_sampling'] = "0";
    }else if (@$hasil['nilai_total']>=85) {
      $data['status_sampling'] = "1";
    }else{
      $data['status_sampling'] = "2";
    }
      $data['last_updated'] = $date_now;
      $data['sampling_date'] = $date_now;
      
      $updateSampling = DB::table('sampling_site')
      ->where('sampling_id','=',$data['sampling_id'])
      ->update($data);

      if ($updateSampling) {
        $res['success'] = true;
        $res['message'] = 'success';
      }else{

        $res['success'] = false;
        $res['message'] = 'failed to update data';
      }

    $getSampling = DB::table('sampling_site')
    ->select('*')
    ->where('sampling_id','=',$data['sampling_id'])
    ->first();


    $res['data'] = @$getSampling;

    return response($res); 

  }


  public function hitungSampling($data){

    $getFormula = DB::table('sampling_site')
    ->select('*')
    ->where('sampling_id','=',$data['sampling_id'])
    ->first();

    // $getFormula = json_decode($getFormula, true);

    // return (@$data['bobot_kebersihan_halaman']==null? 0 :$data['bobot_kebersihan_halaman']);
    // $x =  $data==2 ? 1 : 2;

    // $hasil_tmp = 0;
    //hitung sub bobot_ac_indoor_outdoor
    $ac['hasil_kebersihan_ac'] = ((@$data['nilai_kebersihan_ac']==null?0:$data['nilai_kebersihan_ac'])* $getFormula->bobot_kebersihan_ac / 100);
    $ac['hasil_suhu_ac_25'] = ((@$data['nilai_suhu_ac_25']==null?0:$data['nilai_suhu_ac_25'])* $getFormula->bobot_suhu_ac_25 / 100);
    $sumac=array_sum($ac);
    $tmp_bobot_ac_indoor_outdoor = $sumac * $getFormula->bobot_ac_indoor_outdoor / 100;
    $dt['hasil_ac_indoor_outdoor'] = $tmp_bobot_ac_indoor_outdoor;


    
    //hitung sub bobot_ef_pemeliharaan_exhaust_fan
    // $tmp_sub=0;
    $ef['hasil_kebersihan_exhaust_fan'] = ((@$data['nilai_kebersihan_exhaust_fan']==null?0:$data['nilai_kebersihan_exhaust_fan'])* $getFormula->bobot_kebersihan_exhaust_fan / 100);
    $sumef=array_sum($ef);
    $tmp_bobot_ef_pemeliharaan_exhaust_fan=$sumef * $getFormula->bobot_pemeliharaan_exhaust_fan / 100;
    $dt['hasil_pemeliharaan_exhaust_fan'] = $tmp_bobot_ef_pemeliharaan_exhaust_fan;


    //hitung sub bobot_sp_sistem_penerangan
    // $tmp_sub = 0;
    $sp['hasil_sp_lampu_shelter'] = ((@$data['nilai_sp_lampu_shelter']==null?0:$data['nilai_sp_lampu_shelter'])* $getFormula->bobot_sp_lampu_shelter / 100);
    $sp['hasil_sp_lampu_halaman'] = ((@$data['nilai_sp_lampu_halaman']==null?0:$data['nilai_sp_lampu_halaman'])* $getFormula->bobot_sp_lampu_halaman / 100);
    $sp['hasil_sp_lampu_tower'] = ((@$data['nilai_sp_lampu_tower']==null?0:$data['nilai_sp_lampu_tower'])* $getFormula->bobot_sp_lampu_tower / 100);
    $sumsp=array_sum($sp);
    $tmp_bobot_sp_sistem_penerangan=$sumsp * $getFormula->bobot_sistem_penerangan / 100;

    $dt['hasil_sistem_penerangan'] = $tmp_bobot_sp_sistem_penerangan;


    //hitung sub bobot_hk_hasil_kwh
    // $tmp_sub = 0;
    $kwh['hasil_segel_kwh'] = ((@$data['nilai_segel_kwh']==null?0:$data['nilai_segel_kwh'])* $getFormula->bobot_segel_kwh / 100);
    $kwh['hasil_pencatatan_kwh'] = ((@$data['nilai_pencatatan_kwh']==null?0:$data['nilai_pencatatan_kwh'])* $getFormula->bobot_pencatatan_kwh / 100);
    $kwh['hasil_putaran_piringan_kwh'] = ((@$data['nilai_putaran_piringan_kwh']==null?0:$data['nilai_putaran_piringan_kwh'])* $getFormula->bobot_putaran_piringan_kwh / 100);
    $sumkwh=array_sum($kwh);
    $tmp_bobot_hk_hasil_kwh=$sumkwh * $getFormula->bobot_pemeriksaan_kwh / 100;

    $dt['hasil_pemeriksaan_kwh'] = $tmp_bobot_hk_hasil_kwh;

    //cek data
    $dt['hasil_label_mcb_beban'] = ((@$data['nilai_label_mcb_beban']==null?0:$data['nilai_label_mcb_beban'])* $getFormula->bobot_label_mcb_beban / 100);
    $dt['hasil_fungsi_alarm_recti'] = ((@$data['nilai_fungsi_alarm_recti']==null?0:$data['nilai_fungsi_alarm_recti'])* $getFormula->bobot_fungsi_alarm_recti / 100);
    $dt['hasil_kebersihan_halaman'] = ((@$data['nilai_kebersihan_halaman']==null?0:$data['nilai_kebersihan_halaman'])* $getFormula->bobot_kebersihan_halaman / 100);
    $dt['hasil_kebersihan_shelter'] = ((@$data['nilai_kebersihan_shelter']==null?0:$data['nilai_kebersihan_shelter'])* $getFormula->bobot_kebersihan_shelter / 100);
    $dt['hasil_kebersihan_perangkat'] = ((@$data['nilai_kebersihan_perangkat']==null?0:$data['nilai_kebersihan_perangkat'])* $getFormula->bobot_kebersihan_perangkat / 100);

    $dt['hasil_kebersihan_filter_air_intake'] = ((@$data['nilai_kebersihan_filter_air_intake']==null?0:$data['nilai_kebersihan_filter_air_intake'])* $getFormula->bobot_kebersihan_filter_air_intake / 100);
    $dt['hasil_engsel_pintu_roda_pagar'] = ((@$data['nilai_engsel_pintu_roda_pagar']==null?0:$data['nilai_engsel_pintu_roda_pagar'])* $getFormula->bobot_engsel_pintu_roda_pagar / 100);
    $dt['hasil_kebersihan_saluran_air_dan_akses_site'] = ((@$data['nilai_kebersihan_saluran_air_dan_akses_site']==null?0:$data['nilai_kebersihan_saluran_air_dan_akses_site'])* $getFormula->bobot_kebersihan_saluran_air_dan_akses_site / 100);
    $dt['hasil_intergasi_grounding_perangkat'] = ((@$data['nilai_intergasi_grounding_perangkat']==null?0:$data['nilai_intergasi_grounding_perangkat'])* $getFormula->bobot_intergasi_grounding_perangkat / 100);
    $dt['hasil_intergasi_grounding_int_ekst'] = ((@$data['nilai_intergasi_grounding_int_ekst']==null?0:$data['nilai_intergasi_grounding_int_ekst'])* $getFormula->bobot_intergasi_grounding_int_ekst / 100);

    $dt['hasil_data_radio_dan_transmisi_ft01'] = ((@$data['nilai_data_radio_dan_transmisi_ft01']==null?0:$data['nilai_data_radio_dan_transmisi_ft01'])* $getFormula->bobot_data_radio_dan_transmisi_ft01 / 100);
    $dt['hasil_data_infras_site_dan_sarana_pada_ft01'] = ((@$data['nilai_data_infras_site_dan_sarana_pada_ft01']==null?0:$data['nilai_data_infras_site_dan_sarana_pada_ft01'])* $getFormula->bobot_data_infras_site_dan_sarana_pada_ft01 / 100);
    $dt['hasil_intergasi_grounding_int_ekst'] = ((@$data['nilai_intergasi_grounding_int_ekst']==null?0:$data['nilai_intergasi_grounding_int_ekst'])* $getFormula->bobot_intergasi_grounding_int_ekst / 100);

    $dt['hasil_kebersihan_gnst_ats_pemipaan'] = ((@$data['nilai_kebersihan_gnst_ats_pemipaan']==null?0:$data['nilai_kebersihan_gnst_ats_pemipaan'])* $getFormula->bobot_kebersihan_gnst_ats_pemipaan / 100);
    $dt['hasil_oli_dan_filter_filter'] = ((@$data['nilai_oli_dan_filter_filter']==null?0:$data['nilai_oli_dan_filter_filter'])* $getFormula->bobot_oli_dan_filter_filter / 100);

    $dt['hasil_sistem_ats'] = ((@$data['nilai_sistem_ats']==null?0:$data['nilai_sistem_ats'])* $getFormula->bobot_sistem_ats / 100);
    $dt['hasil_alarm_ext_gnst'] = ((@$data['nilai_alarm_ext_gnst']==null?0:$data['nilai_alarm_ext_gnst'])* $getFormula->bobot_alarm_ext_gnst / 100);
    $dt['hasil_lvl_bbm'] = ((@$data['nilai_lvl_bbm']==null?0:$data['nilai_lvl_bbm'])* $getFormula->bobot_lvl_bbm / 100);

    $x=array_sum($dt);
    $dt['nilai_total'] = round($x,2);

    $dt = array_merge((array)$ac,(array)$ef,(array)$sp,(array)$kwh,(array)$dt);
    return ($dt);

  }

  public function get_list_reschedule_sik(Request $request)
  {
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');

    $rtpo_id = $request->input('rtpo_id');
    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $data_sik = DB::table('propose_reschedule')
    ->select('*')
    ->where('rtpo_id',$rtpo_id)
    ->where('periode',$periode)
    ->where('status',0)
    ->offset($offset)
    ->limit($limit)
    ->get();

    foreach ($data_sik as $key => $value) {
      $date_created2 = $this->tanggal_bulan_tahun_indo_tiga_char($value->date_created);
      //$old_schedule2 = $this->tanggal_bulan_tahun_indo_tiga_char($value->old_schedule);
      //$new_schedule2 = $this->tanggal_bulan_tahun_indo_tiga_char($value->new_schedule);

      $value->date_created = $date_created2;
      //$value->old_schedule = $old_schedule2;
      //$value->new_schedule = $new_schedule2;
    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data_sik;

    return response($res); 
  }

  public function approve_reschedule_sik(Request $request)
  {
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');

    $sik_no = $request->input('sik_no');
    $username = $request->input('username');

    //$username = 'enggarrio';

    $rtpo_users_data = DB::table('users')
    ->select('*')
    ->where('username',$username)
    ->first();

    $rtpo_nik = $rtpo_users_data->id;
    $rtpo_cn = $rtpo_users_data->name;

    $approve = DB::table('propose_reschedule')
    ->where('sik_no',$sik_no)
    ->update([
      'status' => 1,
      'status_desc' => 'WAITING FOR NOS APPROVAL',
      'rtpo_nik' => $rtpo_nik,
      'rtpo_cn' => $rtpo_cn,
      'last_updated' => $date_now,
      'is_sync' => 0,
    ]);

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    
    return response($res); 
  }
  
  public function reject_reschedule_sik(Request $request)
  {
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $periode = date('Y-m');

    $sik_no = $request->input('sik_no');
    $reason = $request->input('reason');
    $username = $request->input('username');

    //$username = 'enggarrio';

    $rtpo_users_data = DB::table('users')
    ->select('*')
    ->where('username',$username)
    ->first();

    $rtpo_nik = $rtpo_users_data->id;
    $rtpo_cn = $rtpo_users_data->name;

    $approve = DB::table('propose_reschedule')
    ->where('sik_no',$sik_no)
    ->update([
      'status' => 2,
      'status_desc' => 'REJECTED BY RTPO',
      'reject_reason' => $reason,
      'rtpo_nik' => $rtpo_nik,
      'rtpo_cn' => $rtpo_cn,
      'last_updated' => $date_now,
      'is_sync' => 0,
    ]);

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    
    return response($res); 
  }

  public function requestMbpToSiteDown(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." +30 minutes");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $mbp_id = $request->input('mbp_id');
    $site_id = $request->input('site_id');
    $user_id = $request->input('user_id');

    //get data mbp
    $mbp_data = DB::table('mbp as m')
    ->select('*')
    ->where('mbp_id',$mbp_id)
    ->first();

    //get data rtpo asal mbp
    $rtpo_home_data = DB::table('rtpo')
    ->select('*')
    ->where('rtpo_id',$mbp_data->rtpo_id_home)
    ->first();

    //get data user mbp yang ditugaskan
    $user_mbp_data = DB::table('user_mbp')
    ->select('*')
    ->where('mbp_id',$mbp_data->mbp_id)
    ->first();

    //get data user mbp yang ditugaskan secara detail
    $users_data = DB::table('users')
    ->select('*')
    ->where('username',$user_mbp_data->username)
    ->first();

    if ($mbp_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_MBP_DATA_NOT_FOUND';
      return response($res);
    }

    if ($mbp_data->status!='AVAILABLE') {
      $res['success'] = true;
      $res['message'] = 'SUCCESS_INSERT_TO_DATABASE';
      $res['wall'] = 'FAILED_MBP_TERBOKING';
      return response($res);
    }

    $rtpo_data = DB::table('rtpo')
    ->select('*')
    ->where('rtpo_id',$mbp_data->rtpo_id)
    ->first();

    $rtpo_users_data = DB::table('users')
    ->select('*')
    ->where('id',$user_id)
    ->first();

    $site_data = DB::table('site')
    ->select('*')
    ->where('site_id',$site_id)
    ->first();

    if ($site_data==null || $rtpo_users_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_USER_OR_SITE_DATA_NOT_FOUND';
      return response($res);
    }

    //query insert tiket mbp
    $insertSP = DB::table('supplying_power')   
    ->insert(
      [
        'unique_id' => 'SPP_'.$mbp_data->mbp_id.'_'.SUBSTR($date_now, 2,2).SUBSTR($date_now, 5,2).SUBSTR($date_now, 8,2).SUBSTR($date_now, 11,2).SUBSTR($date_now, 14,2).SUBSTR($date_now, 17,2) ,
        'site_id' => $site_data->site_id,
        'site_name' => $site_data->site_name,
        'lokasi_site' => @$site_data->lokasi_site,

        'date_mainsfail' => $site_data->date_mainsfail,

        'user_id' => $rtpo_users_data->id,
        'rtpo_id' => $rtpo_data->rtpo_id,
        'user_rtpo' => $rtpo_users_data->id,
        'user_rtpo_cn' => $rtpo_users_data->username,
        'rtpo_name' => $rtpo_data->rtpo_name,
        //'tec_opr_id' => $site_users_data->tec_opr_id,
        //'wil_opr_id' => $site_users_data->wil_opr_id,
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
        'rtpo_name_home' => $rtpo_home_data->rtpo_name,
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
      ]
    );

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

  public function tiketMBPTidakDikerjakan($sp_id,$reason,$cancel_by){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    //$sp_id = $request->input('sp_id');
    if (@$cancel_by==null) {
      $cancel_by='RTPO';
    }

    $data_sp = DB::table('supplying_power')
    ->select('*')
    ->where('sp_id',$sp_id)
    ->first();

    $users_data = DB::table('users')
    ->select('*')
    ->where('username',$data_sp->user_mbp)
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
      'cancel_reason' => $reason,
      'cancel_category' => "Tidak dikerjakan",
      'reason_by' => $cancel_by,
      'cancel_approved_by' => $cancel_by,
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

    $sp_controller = new SupplyingPowerController;
    $value_sp_log = $sp_controller->saveLogSP1($sp_m_s_data->sp_id, $sp_m_s_data->user_rtpo, $sp_m_s_data->user_rtpo_cn, $status,$desc, '', '', $date_now);


    $notificationController = new NotificationController; 
    $tmp = $notificationController->setNotificationV1($data_sp->user_rtpo_cn, $data_sp->user_mbp, 'MBP_TIDAK_DIKERJAKAN', 'sp_id', $sp_id, 'Tiket MBP Tidak Dikerjakan', 'MBP_TIDAK_DIKERJAKAN', @$data_sp->user_rtpo_cn.' dari '.@$data_sp->rtpo_name.' menyatakan bahwa tiket MBP dibatalkan karena anda tidak mengerjakan tugas ke site '.$site_id,1,'MBP');

    //push token firebase ke array
    $to_token_id = array();
    array_push($to_token_id,@$users_data->firebase_token);

    $fbc = new FireBaseController;
    $tmp_fb =    $fbc->sendNotification('MBP','Tiket ke site '.$site_id.' dibatalkan karena RTPO menyatakan anda tidak mengerjakan tugas',$to_token_id,1,@$mbp_data->mbp_id,'MBP_TIDAK_DIKERJAKAN');
    
    $res['success'] = true;
    $res['message'] = 'SUCCESS';

    return $res;
  }

  public function updateRescheduleSIK(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $data_update = $request->input('data');

    $success_id = array();

    foreach ($data_update as $param => $row) {
      $master_reschedule_data = DB::table('propose_reschedule')
      ->select('*')
      ->where('reschedule_id','=',$row['reschedule_id'])
      ->first();

      if ($master_reschedule_data!=null) {
        $updateRescheduleSIK = DB::table('propose_reschedule')
        ->where('reschedule_id','=',$row['reschedule_id'])
        ->update([
          'sik_no' => @$row['sik_no'],
          'new_sik_no' => @$row['new_sik_no'],
          'old_schedule' => @$row['old_schedule'],
          'new_schedule' => @$row['new_schedule'],
          'site_id' => @$row['site_id'],
          'site_name' => @$row['site_name'],
          'reason' => @$row['reason'],
          'regional' => @$row['regional'],
          'rtpo_id' => @$row['rtpo_id'],
          'rtpo' => @$row['rtpo'],
          'cluster_id' => @$row['cluster_id'],
          'cluster' => @$row['cluster'],
          'fmc_id' => @$row['fmc_id'],
          'fmc' => @$row['fmc'],
          'periode' => @$row['periode'],
          'created_by' => @$row['created_by'],
          'created_cn' => @$row['created_cn'],
          'date_created' => @$row['date_created'],
          'rtpo_nik' => @$row['rtpo_nik'],
          'rtpo_cn' => @$row['rtpo_cn'],
          'nos_nik' => @$row['nos_nik'],
          'nos_cn' => @$row['nos_cn'],
          'status' => @$row['status'],
          'status_desc' => @$row['status_desc'],
          'respond_time' => @$row['respond_time'],
          'reject_reason' => @$row['reject_reason'],
          'last_updated' => @$row['last_updated'],
          'is_sync' => 1,
          'flag' => @$row['flag'],
        ]);
        array_push($success_id, $row['reschedule_id']);
      } else{
        $insertRescheduleSIK = DB::table('propose_reschedule')
        ->insert([
          'reschedule_id' => $row['reschedule_id'],
          'sik_no' => @$row['sik_no'],
          'new_sik_no' => @$row['new_sik_no'],
          'old_schedule' => @$row['old_schedule'],
          'new_schedule' => @$row['new_schedule'],
          'site_id' => @$row['site_id'],
          'site_name' => @$row['site_name'],
          'reason' => @$row['reason'],
          'regional' => @$row['regional'],
          'rtpo_id' => @$row['rtpo_id'],
          'rtpo' => @$row['rtpo'],
          'cluster_id' => @$row['cluster_id'],
          'cluster' => @$row['cluster'],
          'fmc_id' => @$row['fmc_id'],
          'fmc' => @$row['fmc'],
          'periode' => @$row['periode'],
          'created_by' => @$row['created_by'],
          'created_cn' => @$row['created_cn'],
          'date_created' => @$row['date_created'],
          'rtpo_nik' => @$row['rtpo_nik'],
          'rtpo_cn' => @$row['rtpo_cn'],
          'nos_nik' => @$row['nos_nik'],
          'nos_cn' => @$row['nos_cn'],
          'status' => @$row['status'],
          'status_desc' => @$row['status_desc'],
          'respond_time' => @$row['respond_time'],
          'reject_reason' => @$row['reject_reason'],
          'last_updated' => @$row['last_updated'],
          'is_sync' => 1,
          'flag' => @$row['flag'],
        ]);

        if ($insertRescheduleSIK<1) {
          $res['success'] = false;
          $res['message'] = 'FAILED_INSERT_DATA_MBP';
          return response($res);
        }

        array_push($success_id, $row['reschedule_id']);
      }
    }
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $success_id;
    return response($res);
  }

  public function getProposeRescheduleSIK(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $data_reschedule = DB::table('propose_reschedule')
    ->select('*')
    ->where('is_sync',0)
    ->limit(50)
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data_reschedule;
    return response($res);
  }

  function bulan_indo($param=1)
  {
    $bulan = [
     '',
     'Januari',
     'Februari',
     'Maret',
     'April',
     'Mei',
     'Juni',
     'Juli',
     'Agustus',
     'September',
     'Oktober',
     'November',
     'Desember',
    ];
  return @$bulan[(int)$param];
 }

 function bulan_tahun_indo($param)
 {
  list($y,$m) = explode('-', $param);
  return $this->bulan_indo($m).' '.$y;
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