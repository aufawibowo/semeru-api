<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class CancelController extends Controller
{
 
    public function sendCancellationLetterToRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = @$request->input('rtpo_id');
      $user_id_mbp = @$request->input('user_id');
      $mbp_id = $request->input('mbp_id');
      $text_message = $request->input('text_message');
      $cancel_category = @$request->input('cancel_category');
      $available_status = $request->input('available_status');
      $active_at = $request->input('time');

      $sp_data = DB::table('supplying_power as sp')
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

      $data_mbp = DB::table('mbp')
      ->select('*')
      ->where('mbp_id',$mbp_id)
      ->first();

      $rtpo_id = $data_mbp->rtpo_id;
      
      if(empty($rtpo_id) || $rtpo_id==0) {
        $res['success'] = false;
        $res['message'] = 'EMPTY_RTPO! Silakan logout dan login kembali';
        return response($res);
      }

      $cek_duplicate = DB::table('mbp_trouble')
      ->select('*')
      ->where('sp_id',$sp_data->sp_id)
      ->where('is_active', '1')
      ->first();

      if ($cek_duplicate){
        $res['success'] = false;
        $res['message'] = 'Gagal! Duplikasi Tiket!';
        return response($res);
      }
      
      $insert_mbp_trouble = DB::table('mbp_trouble')
      ->where('sp_id', $sp_data->sp_id)
      ->where('is_active', '1')
      ->delete();

      $insert_message = DB::table('message')
      ->insert(
        [
          'subject' => @'CANCEL', 
          'from' => @$user_id_mbp,
          'text_message' => @$text_message,
          'date_message' => @$date_now.'',
        ]
      );

      if (!$insert_message) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MESSAGE_DATA';
        return response($res);
      }

      if ($available_status=='UNAVAILABLE') {
        $request_to_unavailable = 1;
        $mbp_active_at = date('Y-m-d H:i:s', strtotime($date_now.' + '.$active_at.' hours'));
      }elseif ($available_status=='AVAILABLE') {
        $request_to_unavailable = 0;
        $mbp_active_at = null;
      }

      $insert_mbp_trouble = DB::table('mbp_trouble')
      ->insert(
        [
          'send_to_rtpo_id' => $rtpo_id,
          'send_to_rtpo_name' => $sp_data->rtpo_name,
          
          'desc' => $text_message,
          'cancel_category' => @$cancel_category,
          'send_by_nik' => $user_id_mbp,
          'send_by_cn' => $sp_data->user_mbp_cn,
          'type' => 'CANCEL',
          'mbp_id' => $mbp_id,
          'sp_id' => $sp_data->sp_id,
          'send_date' => $date_now.'',
          'request_to_unavailable' => $request_to_unavailable,
          'mbp_active_at' => $mbp_active_at,
          'is_active' => 1,
        ]
      );

      if (!$insert_mbp_trouble) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MBP_TROUBLE';
        return response($res);
      }


      $after_in_data = DB::table('mbp_trouble as mtr')
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
      ->update(
        [
          'submission' => 'CANCEL',
          'submission_id' => $after_in_data->mtr_id,
          'active_at' => $after_in_data->mbp_active_at,
          'message_id' => $after_in_data->msg_id,
        ]
      );

      $supplyingPowerController = new SupplyingPowerController;
      $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $user_id_mbp, $sp_data->user_mbp_cn, 'SUBMIT_CANCEL', $sp_data->user_mbp_cn.' mengajukan pembatalan penugasan kepada rtpo dengan alasan sebagai berikut : '.$text_message,$text_message, '', $date_now);

      $notificationController = new NotificationController;
      $tmp = $notificationController->setNotificationMbpSubmission($sp_data->mbp_name,$sp_data->site_name,$rtpo_id, $sp_data->message_id, 'CANCEL');

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] = $mbp_data;
      return response($res);
    }
    public function sendDelayLetterToRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = $request->input('rtpo_id');
      $user_id_mbp = $request->input('user_id');
      $mbp_id = $request->input('mbp_id');
      $text_message = $request->input('text_message');
      // $available_status = $request->input('available_status');
      $active_at = $request->input('time');

      $data_mbp = DB::table('mbp')
      ->select('*')
      ->where('mbp_id',$mbp_id)
      ->first();

      $rtpo_id = $data_mbp->rtpo_id;

      if(empty($rtpo_id) || $rtpo_id==0) {
        $res['success'] = false;
        $res['message'] = 'EMPTY_RTPO! Silakan logout dan login kembali';
        return response($res);
      }


      $sp_data = DB::table('supplying_power as sp')
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
      ->insert(
        [
          'subject' => 'DELAY', 
          'from' => $user_id_mbp,
          'text_message' => $text_message,
          'date_message' => $date_now.'',
        ]
      );

      if (!$insert_message) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MESSAGE_DATA';
        return response($res);
      }

      $mbp_active_at = date('Y-m-d H:i:s', strtotime($date_now.' + '.$active_at.' hours'));
      $insert_mbp_trouble = DB::table('mbp_trouble')
      ->insert(
        [
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
        ]
      );

      if (!$insert_mbp_trouble) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MBP_TROUBLE';
        return response($res);
      }

      $after_in_data = DB::table('mbp_trouble as mtr')
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
      ->update(
        [
          'submission' => 'DELAY',
          'submission_id' => $after_in_data->mtr_id,
          'active_at' => $after_in_data->mbp_active_at,
          'message_id' => $after_in_data->msg_id,
        ]
      );

      $supplyingPowerController = new SupplyingPowerController;
      $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $user_id_mbp, $sp_data->user_mbp_cn, 'SUBMIT_DELAY', $sp_data->user_mbp_cn.' mengirimkan pengajuan delay kepada rtpo dengan pesan sebagai berikut : '.$text_message,$text_message, '', $date_now);

      $notificationController = new NotificationController;
      $tmp = $notificationController->setNotificationMbpSubmission($sp_data->mbp_name,$sp_data->site_name,$rtpo_id, $sp_data->message_id, 'DELAY');

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return response($res);
    }
    public function acceptCancellationLetterfromMbp(Request $request){
      // ketika eksekusi ini, maka tabel sp di kolom 'finish' diisi 'CANCEL' dan 'date_finish' juga terisi
      // lalu status mbp berubah jadi 'UNAVAILABLE' 
      // site bagian is_allocated berubah menjadi '0' kembali

      date_default_timezone_set("Asia/Jakarta");

      $cancel_letter_id = $request->input('cancel_id'); //1
      $user_id_rtpo = $request->input('user_id');       //1
      $status_mbp = $request->input('status_mbp');       //1

      $updateCancellationLetter = DB::table('cancel_details')
      ->join('supplying_power', 'cancel_details.sp_id', '=', 'supplying_power.sp_id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.username', '=', 'user_mbp.username')        //get name_mbp
      ->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
      ->join('message', 'cancel_details.message_id', '=', 'message.id')// get subject
      ->where('cancel_details.id', $cancel_letter_id)
      ->where('cancel_details.response_status','=',NULL)
      ->update(
        [
          'cancel_details.user_id_rtpo' =>$user_id_rtpo,
          'supplying_power.finish' =>'CANCEL',
          'supplying_power.date_finish' =>date('Y-m-d H:i:s'),
          'mbp.status' =>'cancel_details.available_status',
          'site.is_allocated' =>$status_mbp,
        ]
      );


      if ($updateCancellationLetter) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        // $res['data'] = $insertCancellationLetter;
        
        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS';
        
        return response($res);
      }
    }
    public function getCancellationLetter(Request $request){
      $rtpo_id = $request->input('rtpo_id');

      $mbp_trouble = DB::table('mbp_trouble as mtr')
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
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_MESSAGE';
        
        return response($res);
      }
    }

    //deprecated
    public function getCancellationLetter1(Request $request){
      $rtpo_id = $request->input('rtpo_id');

      $mbp_trouble = DB::table('mbp_trouble as mtr')
      ->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
      ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
      ->join('users as u', 'um.username', 'u.username')
      ->join('message as msg', 'mtr.send_date', 'msg.date_message')
      ->select('mtr.id as cancel_id','mtr.type as subject','mtr.desc as text_message','m.mbp_id as mbp_id','m.mbp_name','u.name','m.message_id as message_id','mtr.send_date as date')
      ->where('mtr.is_active',1)
      ->where('mtr.send_to_rtpo_id',$rtpo_id)
      ->get();

      if ($mbp_trouble) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_trouble;

        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_MESSAGE';
        
        return response($res);
      }
    }
    
    public function cancellationStatementRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      $type_approval = $request->input('type_approval');// (AGREE/DISAGREE)
      $cancel_id = $request->input('cancel_id');
      $user_id = $request->input('user_id');

      // echo "type_approval ". $type_approval;
      // echo "cancellationStatementRtpo /n";

      $mbp_trouble = DB::table('mbp_trouble as mtr')
      ->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
      ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
      ->join('users as u', 'um.username', 'u.username')
      ->join('message as msg', 'mtr.send_date', 'msg.date_message')
      ->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
      ->select('*','u.id as user_id','mtr.id as mtr_id','m.status as mbp_status','mtr.respon_date as respon_date','mtr.cancel_category','mtr.cancel_image')
      ->where('mtr.id',$cancel_id)
      ->first();

      if (!$mbp_trouble) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'FAILED_GET_USER_DATA';  
        return response($res);
      }

      $user_data = DB::table('users as u')
      ->select('*')
      ->where('u.id',$user_id)
      ->first();
      
      if (!$user_data) {
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_USER_DATA';
        return response($res);
      }

      // $update_mtr_m = DB::table('mbp_trouble as mtr')
      // ->join('mbp as m', 'mtr.id', 'm.submission_id')
      // ->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
      // ->join('site as s', 'sp.site_id', 's.site_id')
      // ->where('m.submission_id',$cancel_id);

      if ($type_approval=='AGREE') {

        $status = 'AVAILABLE';
        if ($mbp_trouble->request_to_unavailable == 1) {

          $status = 'UNAVAILABLE';

          $upd_mtr = DB::table('mbp_trouble as mtr')
          ->where('mtr.id', $cancel_id)
          ->update(
            [
              'mtr.respon_by_nik' => $user_data->id,
              'mtr.respon_by_cn' => $user_data->username,
              'mtr.respon_date' => $date_now,
              'mtr.is_approved' => 1,
              'mtr.is_active' => 0,
            ]
          );

          $upd_sp = DB::table('supplying_power as sp')
          ->where('sp.sp_id', $mbp_trouble->sp_id)
          ->update(
            ['sp.finish' =>'CANCEL',
              'sp.date_finish' =>$date_now,

              'sp.cancel_reason' => $mbp_trouble->desc,
              'sp.cancel_category' => @$mbp_trouble->cancel_category,
              'sp.reason_by' => $mbp_trouble->send_by_cn,
              
              'sp.cancel_image' => @$mbp_trouble->cancel_image,

              'sp.cancel_approved_by' => $user_data->username,
              'sp.detail_finish' =>4,
            ]
          );


          $upd_m = DB::table('mbp as m')
          ->where('m.mbp_id', $mbp_trouble->mbp_id)
          ->update(
            [
              'm.status' =>$status,
            ]
          );

          $upd_s = DB::table('site as s')
          ->where('s.site_id', $mbp_trouble->site_id)
          ->update(
            [
              's.is_allocated' => 0,
            ]
          );


        }else{

            $upd_mtr = DB::table('mbp_trouble as mtr')
          ->where('mtr.id', $cancel_id)
          ->update(
            [
              'mtr.respon_by_nik' => $user_data->id,
              'mtr.respon_by_cn' => $user_data->username,
              'mtr.respon_date' => $date_now,
              'mtr.is_approved' => 1,
              'mtr.is_active' => 0,
            ]
          );
          

          $upd_sp = DB::table('supplying_power as sp')
          ->where('sp.sp_id', $mbp_trouble->sp_id)
          ->update(
            ['sp.finish' =>'CANCEL',
              'sp.date_finish' =>$date_now,

              'sp.cancel_reason' => $mbp_trouble->desc,
              'sp.cancel_category' => @$mbp_trouble->cancel_category,
              'sp.reason_by' => $mbp_trouble->send_by_cn,
              
              'sp.cancel_image' => @$mbp_trouble->cancel_image,

              'sp.cancel_approved_by' => $user_data->username,
              'sp.detail_finish' =>4,
            ]
          );


          $upd_m = DB::table('mbp as m')
          ->where('m.mbp_id', $mbp_trouble->mbp_id)
          ->update(
            [
              'm.status' =>$status,
              'm.submission' =>null,
              'm.submission_id' =>null,
              'm.active_at' =>null,
              'm.message_id' =>null,
            ]
          );

          $upd_s = DB::table('site as s')
          ->where('s.site_id', $mbp_trouble->site_id)
          ->update(
            [
              's.is_allocated' => 0,
            ]
          );


        }

        $res['upd_mtr'] = $upd_mtr;
        $res['upd_m'] = $upd_m;
        $res['upd_s'] = $upd_s;
        $res['upd_sp'] = $upd_sp;

        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($mbp_trouble->sp_id, $mbp_trouble->user_id, $mbp_trouble->username, 'SUBMIT_CANCELING_APPROVED', 'Menyetujui pengajuan pembatalan terhadap mbp '.$mbp_trouble->mbp_name.' dengan alasan sebagai berikut : '.$mbp_trouble->desc, $mbp_trouble->desc,'', $date_now);

        $notificationController = new NotificationController;
        $tmp = $notificationController->setNotificationSubmissionAgreement('APPROVE_CANCEL',$mbp_trouble->mbp_name,$mbp_trouble->mbp_id);

      }

      if ($type_approval=='DISAGREE') {

        $update_mtr_m = DB::table('mbp_trouble as mtr')
        ->join('mbp as m', 'mtr.id', 'm.submission_id')
        ->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
        ->join('site as s', 'sp.site_id', 's.site_id')
        ->where('mtr.id',$cancel_id)
        ->update(
          [
            'mtr.respon_by_nik' => $user_data->id,
            'mtr.respon_by_cn' => $user_data->username,
            'mtr.respon_date' => $date_now,
            'mtr.is_approved' => 0,
            'mtr.is_active' => 0,

            'm.submission' =>null,
            'm.submission_id' =>null,
            'm.active_at' =>null,
            'm.message_id' =>null,
          ]
        );

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $update_mtr_m;
      return response($res);

        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($mbp_trouble->sp_id, $mbp_trouble->user_id, $mbp_trouble->username, 'SUBMIT_CANCELING_NOT_APPROVED', 'Tidak menyetujui pengajuan pembatalan terhadap mbp '.$mbp_trouble->mbp_name.' dengan alasan sebagai berikut : '.$mbp_trouble->desc, $mbp_trouble->desc,'', $date_now);

        $notificationController = new NotificationController;
        $tmp = $notificationController->setNotificationSubmissionAgreement('DENY_CANCEL',$mbp_trouble->mbp_name,$mbp_trouble->mbp_id);
      }


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

      $mbp_trouble = DB::table('mbp_trouble as mtr')
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

      $user_data = DB::table('users as u')
      ->select('*')
      ->where('u.id',$user_id)
      ->first();
      
      if (!$user_data) {
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_USER_DATA';
        return response($res);
      }

      $update_mtr_m = DB::table('mbp_trouble as mtr')
      ->join('mbp as m', 'mtr.id', 'm.submission_id')
      ->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
      ->join('site as s', 'sp.site_id', 's.site_id')
      ->where('m.submission_id',$cancel_id);

      if ($type_approval=='AGREE') {
        $update_mtr_m 
        ->update(
          [
            'mtr.respon_by_nik' => $user_data->id,
            'mtr.respon_by_cn' => $user_data->username,
            'mtr.respon_date' => $date_now,
            'mtr.is_approved' => 1,
            'mtr.is_active' => 0,
          ]
        );

        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($mbp_trouble->sp_id, $mbp_trouble->user_id, $mbp_trouble->username, 'SUBMIT_DELAY_APPROVED', 'rtpo menyetujui pengajuan delay terhadap mbp '.$mbp_trouble->mbp_name, '','', $date_now);

        $notificationController = new NotificationController;
        $tmp = $notificationController->setNotificationSubmissionAgreement('APPROVE_DELAY',$mbp_trouble->mbp_name,$mbp_trouble->mbp_id);

        // $supplyingPowerController = new SupplyingPowerController;
        // $value_sp_log = $supplyingPowerController->saveLogSP1($get_data_mbp->sp_id, $get_data_mbp->id, $user_data->username, $get_data_mbp->status, 'rtpo menyetujui pengajuan delay terhadap mbp '.$get_data_mbp->mbp_name,'' , '', $date_now);

        // if ($updateCancellationLetter) {
        //   $notificationController = new NotificationController;
        //   $tmp = $notificationController->setNotificationSubmissionAgreement('APPROVE_DELAY',$get_data_mbp->mbp_name,$get_data_mbp->mbp_id);

      }elseif ($type_approval=='CANCEL_TASK') {
        $update_mtr_m 
        ->update(
          [
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
          ]
        );

        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($mbp_trouble->sp_id, $mbp_trouble->user_id, $mbp_trouble->username, 'SUBMIT_DELAY_NOT_APPROVED', 'rtpo tidak menyetujui pengajuan delay terhadap mbp '.$mbp_trouble->mbp_name, '','', $date_now);

        $notificationController = new NotificationController;
        $tmp = $notificationController->setNotificationSubmissionAgreement('DENY_DELAY',$mbp_trouble->mbp_name,$mbp_trouble->mbp_id);
        // $supplyingPowerController = new SupplyingPowerController;
        // $value_sp_log = $supplyingPowerController->saveLogSP1($get_data_mbp->sp_id, $get_data_mbp->id, $user_data->username, $get_data_mbp->status, 'rtpo tidak menyetujui pengajuan delay terhadap mbp '.$get_data_mbp->mbp_name,'' , '', $date_now);

        // $notificationController = new NotificationController;
        // $tmp = $notificationController->setNotificationSubmissionAgreement('DENY_DELAY',$get_data_mbp->mbp_name,$get_data_mbp->mbp_id); 
      }

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return response($res);
    }
    public function approvedTheCancellationLetter($user_id, $cancel_id,$username){

      // $user_id = $request->input('user_id');
      // $cancel_id = $request->input('cancel_id');

      $getTableCancel = $this->getfirstTableCancel($cancel_id);
      if ($getTableCancel) {
        $updateCancel = $this->acceptCancelFromRTPO($cancel_id, $user_id, $getTableCancel->available_status,$username);
        if ($updateCancel=='OK') {
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          // $res['data'] = $getTableCancel;          
          return $res;
        }else{
          return $updateCancel;
        }
      }else{
        return $getTableCancel;
      }
    }
    public function didNotApproveOfTheCancellationLetter($cancel_id){

      // $user_id_rtpo = $request->input('user_id');
      // $cancel_id = $request->input('cancel_id');

      $updateCancellationLetter = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.username', '=', 'user_mbp.username')
      ->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
      ->where('cancel_details.id', $cancel_id)
      ->where('cancel_details.user_id_rtpo', NULL)
      ->delete();

      $editMbp = DB::table('mbp')
      ->where('mbp.submission_id', $cancel_id)
      ->update(
        [
          'submission' => NULL,
          'submission_id' => NULL,
          'active_at' =>NULL,
          'message_id' => NULL,
        ]
      ); 

      if ($updateCancellationLetter) {

        // $fireBaseControlle = new FireBaseController;
        // $body = 'Pengajuan Pembatalan anda tidak disetujui';
        // $tittle = 'Pengajuan Pembatalan';
        // $datax = $fireBaseControlle->sendNotification($tittle, $body);

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
          // $res['data'] = $getTableCancel;          
        return $res;
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS';

        return $res;
      }
    }
    public function acceptDelayFromRTPO($user_id_rtpo,$cancel_id){
      // $user_id_rtpo = $request->input('user_id');
      // $cancel_id = $request->input('cancel_id');

      $updateCancel = $this->acceptDelayRtpo($cancel_id, $user_id_rtpo);
      if ($updateCancel=='OK') {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
          // $res['data'] = $getTableCancel;          
        return $res;
      }else{
        return $updateCancel;
      }
    }
    public function finishDelayFromMbp(Request $request){
      $user_id_rtpo = $request->input('user_id');
      $cancel_id = $request->input('cancel_id');

      $user_data = DB::table('users')
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
      }else{
        return $updateCancel;
      }
    }
    public function getfirstTableCancel($cancel_id){
      $cancel_details_data = DB::table('cancel_details')
      ->select('*')
      ->where('id', $cancel_id)
      ->first();

      if ($cancel_details_data){
        return $cancel_details_data;
      }else{

        $res['success'] = false;
        $res['message'] = 'FAILED_GET_TABLE_CANCEL_DEAILS';

        return $res;
      }  
    }
    public function acceptCancelFromRTPO($cancel_id, $user_id_rtpo, $available_status, $username){

      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $cancel_data = DB::table('cancel_details')
      ->select('*')
      ->where('id', $cancel_id)
      ->first();

      if ($cancel_data->submission_type == 'DELAY') {
        $detail_finish = '3';
      }else if ($cancel_data->submission_type == 'CANCEL') {
        $detail_finish = '2';
      }


      if ($available_status=='AVAILABLE') {
        $updateCancellationLetter = DB::table('cancel_details')
        ->join('supplying_power', 'cancel_details.sp_id', '=', 'supplying_power.sp_id')
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
        ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
        // ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
        ->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
        ->join('message', 'cancel_details.message_id', '=', 'message.id')
        ->where('cancel_details.id', $cancel_id)
        ->where('cancel_details.user_id_rtpo', NULL)
        ->update(
          [
            'cancel_details.user_id_rtpo' =>$user_id_rtpo,
            'cancel_details.response_status' =>'1',
            'cancel_details.user_id_responders' =>$user_id_rtpo,

            'cancel_details.respon_by' => $username,//--------------------------------->ok
            'cancel_details.respon_time' =>date('Y-m-d H:i:s'),//--------------------->ok


            'supplying_power.finish' =>'CANCEL',
            'supplying_power.date_finish' => date('Y-m-d H:i:s'),
            'supplying_power.detail_finish' => $detail_finish,
            'mbp.status' =>$available_status,
            'mbp.submission' =>null,
            'mbp.submission_id' =>null,
            'mbp.active_at' =>null,
            'mbp.message_id' =>null,
            'site.is_allocated' =>'0',
          ]
        );
      }else if ($available_status=='UNAVAILABLE') {
        $updateCancellationLetter = DB::table('cancel_details')
        ->join('supplying_power', 'cancel_details.sp_id', '=', 'supplying_power.sp_id')
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
        ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
        // ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
        ->join('mbp', 'cancel_details.mbp_id', '=', 'mbp.mbp_id')
        ->join('message', 'cancel_details.message_id', '=', 'message.id')
        ->where('cancel_details.id', $cancel_id)
        ->where('cancel_details.user_id_rtpo', NULL)
        ->update(
          [
            'cancel_details.user_id_rtpo' =>$user_id_rtpo,
            'cancel_details.response_status' =>'1',
            'cancel_details.user_id_responders' =>$user_id_rtpo,

            'cancel_details.respon_by' => $username,//--------------------------------->ok
            'cancel_details.respon_time' =>date('Y-m-d H:i:s'),//--------------------->ok

            'supplying_power.finish' =>'CANCEL',
            'supplying_power.date_finish' => date('Y-m-d H:i:s'),
            'supplying_power.detail_finish' => $detail_finish,
            'mbp.status' =>$available_status,
            'mbp.submission' =>null,
            'mbp.submission_id' =>null,
            'site.is_allocated' =>'0',
          ]
        );
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS';

        return $res;
      }

      if ($updateCancellationLetter) {

        return 'OK';
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS';

        return $res;
      }
    }
    public function acceptDelayRtpo($cancel_id, $user_id_rtpo){

      date_default_timezone_set("Asia/Jakarta");


      $updateCancellationLetter = DB::table('cancel_details')
      ->where('cancel_details.id', $cancel_id)
      ->where('cancel_details.user_id_rtpo', NULL)
      ->update(
        [
          'cancel_details.user_id_rtpo' =>$user_id_rtpo,
          'cancel_details.response_status' =>'1',
          'cancel_details.user_id_responders' =>$user_id_rtpo,
          // 'mbp.delay' =>'0',
        ]
      );

      if ($updateCancellationLetter) {

        // $fireBaseControlle = new FireBaseController;
        // $body = 'Pengajuan Delay anda telah di setujui';
        // $tittle = 'Pengajuan Delay';
        // $datax =$fireBaseControlle->sendNotification($tittle, $body);
        return 'OK';
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS';

        return $res;
      }
    }
    public function acceptDelayFromMbp($cancel_id, $user_id_rtpo, $username){

      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      $checkCancellationLetter = DB::table('cancel_details')
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
    public function deleteCancellationLetterFromMbp(Request $request){

      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      $user_id_mbp = $request->input('user_id');
      $cancel_type = $request->input('cancel_type');
      $sp_id = $request->input('sp_id');

      $mbp_trouble_data = DB::table('supplying_power as sp')
      ->join('mbp as m', 'sp.mbp_id', 'm.mbp_id')
      ->join('mbp_trouble as mtr', 'm.submission_id', 'mtr.id')
      ->join('message as msg', 'm.message_id', 'msg.id')
      ->select('*', 'msg.id as msg_id', 'mtr.id as mtr_id', 'm.status as mbp_status')
      ->where('sp.sp_id', $sp_id)
      ->first();

      if (!$mbp_trouble_data) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'FAILED_INSERT_DATA';
        return response($res);
      }

      $user_data = DB::table('users as u')
      ->select('*')
      ->where('u.id',$user_id_mbp)
      ->first();
      
      if (!$user_data) {
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_USER_DATA';
        return response($res);
      }

      $update_mbp_trouble = DB::table('mbp_trouble as mtr')
      ->join('mbp as m', 'mtr.id', 'm.submission_id')
      ->where('id',$mbp_trouble_data->mtr_id);
      

      switch ($cancel_type) {
        case "CANCEL":
        $update_mbp_trouble 
        ->update(
          [
            'mtr.respon_by_nik' => $user_data->id,
            'mtr.respon_by_cn' => $user_data->username,
            'mtr.respon_date' => $date_now,
            'mtr.is_approved' => 2,
            'mtr.is_active' => 0,

            'm.submission' => NULL,
            'm.submission_id' => NULL,
            'm.active_at' => NULL,
            'm.message_id' => NULL,
          ]
        );

        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($mbp_trouble_data->sp_id, $user_data->id, $user_data->username, 'SUBMIT_CANCELING_DELETED', 'User membatalkan pengajuan cancelnya kepada RTPO','', '', $date_now);

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return response($res);

        break;
        case "DELAY":

        if ($mbp_trouble_data->respon_by_nik==null) {
          $update_mbp_trouble 
          ->update(
            [
            'mtr.respon_by_nik' => $user_data->id,
            'mtr.respon_by_cn' => $user_data->username,
              'mtr.respon_date' => $date_now,
              'mtr.is_approved' => 2,
              'mtr.is_active' => 0,

              'm.submission' => NULL,
              'm.submission_id' => NULL,
              'm.active_at' => NULL,
              'm.message_id' => NULL,
            ]
          );
        }else{
          $update_mbp_trouble 
          ->update(
            [
              'm.submission' => NULL,
              'm.submission_id' => NULL,
              'm.active_at' => NULL,
              'm.message_id' => NULL,
            ]
          );          
        }

        $supplyingPowerController = new SupplyingPowerController;
        $value_sp_log = $supplyingPowerController->saveLogSP1($mbp_trouble_data->sp_id, $user_data->id, $user_data->username, 'MBP_DELAY_FINISHED', 'User menyelesaikan pengajuan delaynya kepada RTPO','', '', $date_now);

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return response($res);
        break;
        default:
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_DATA';
        return response($res);
        break;
      }
    }
    public function delayIsOver(Request $request){

      $user_id_mbp = $request->input('user_id');


      $editMbp = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->join('users', 'user_mbp.username', '=', 'users.username')
      ->where('users.id', $user_id_mbp)
      ->update(['delay' => 0]);

      if ($editMbp) {

        $CancellationLetter_delete = DB::table('cancel_details')
        ->where('user_id_mbp','=',$user_id_mbp)
        ->update([
          'response_status' => '1',
          'user_id_responders' => $user_id_mbp
        ]);

        if ($CancellationLetter_delete) {

          $CancellationLetter_delete = DB::table('message')
        // ->join('message', 'cancel_details.message_id', '=', 'message.id')
          ->where('id','=',$CancellationLetter_data->message_id)
          // ->where('user_id_mbp','=',$user_id_mbp)
          ->delete();
          if ($CancellationLetter_delete) {
            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            // $res['data'] = $CancellationLetter_data;

            return response($res);
          }else{

            $res['success'] = false;
            $res['message'] = 'FAILED_DELETE_DATA_MESSAGE';

            return response($res);
          }
        }else{

          $res['success'] = false;
          $res['message'] = 'FAILED_DELETE_DATA_CANCELATION_LETTER';

          return response($res);
        }

      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_DELETE_DATA';

        return response($res);
      }
    }
    public function sendUnavailableLetterToRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now =date('Y-m-d H:i:s');


      $rtpo_id = $request->input('rtpo_id');
      $user_id_mbp = $request->input('user_id');
      $mbp_id = $request->input('mbp_id');
      $text_message = $request->input('text_message');

      // $CancellationLetter_data = DB::table('cancel_details')
      // ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      // ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
      // ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
      // ->join('message', 'cancel_details.message_id', '=', 'message.id')
      // ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.subject','cancel_details.date')
      // ->where('cancel_details.response_status','=',NULL)
      // ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      // ->first();


      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->join('users', 'user_mbp.username', '=', 'users.username')
      ->select('*','users.id as user_id')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->where('mbp.status','=','AVAILABLE')
      ->first();

      if ($CancellationLetter_data!=null) {
        $insertMessage = DB::table('message')->insert(
          [
            'subject' => 'UNAVAILABLE', 
            // 'from' => $user_id_mbp, 
            'from' => $mbp_data->user_id,
            'text_message' => $text_message,
            'date_message' => $date_now,
          ]
        );

        if($insertMessage){

          $message_data = DB::table('message')
          ->select('id')
          ->where('date_message','=',$date_now.'')
          ->where('from','=',$mbp_data->user_id.'')
          ->first();

          if ($message_data) {

            $insertCancellationLetter = DB::table('cancel_details')
            ->insert(
              [
                'message_id' => $message_data->id, 
                'rtpo_id' => $rtpo_id,
                'user_id_mbp' => $user_id_mbp,
                'sp_id' => $supplying_power_data->sp_id,
                'mbp_id' => $mbp_id,
                'date' => date('Y-m-d H:i:s'),
              ]
            );


            if ($insertCancellationLetter) {

              $cd = DB::table('cancel_details')
              ->select('*')
              ->where('message_id','=',$message_data->id)
              ->where('sp_id','=',$supplying_power_data->sp_id)
              ->where('user_id_mbp','=',$user_id_mbp)
              ->first();


              $editMbp = DB::table('mbp')
              ->where('mbp_id', $mbp_id)
              ->update(
                [
                  'submission' => 'DELAY',
                  'submission_id' => $cd->id
                ]
              );

              if ($editMbp) {

                $mbp_data = DB::table('mbp')
                ->select('*')
                ->where('mbp_id','=',$mbp_id)
                ->first();

                  // $fireBaseControlle = new FireBaseController;
                  // $body = $mbp_data->mbp_name.' mengajukan Penundaan bertugas untuk beberapa saat';
                  // $tittle = 'Pengajuan Penundaan';
                  // $datax =$fireBaseControlle->sendNotification($tittle, $body/*,$array_tokenIDs*/);
                $res['success'] = true;
                $res['message'] = 'SUCCESS';

                return response($res);
              }else{

                $res['success'] = false;
                $res['message'] = 'FAILED_INSERT_CANCELLATION_LETTER_EDIT_MBP';
                DB::table('message')->where('id','=',$message_data->id)->delete();
                DB::table('cancel_details')
                ->where('message_id','=',$message_data->id)
                ->where('user_id_mbp','=', $user_id_mbp)
                ->where('rtpo_id','=', $rtpo_id)
                ->delete();

                return response($res);
              }

            }else{
              $res['success'] = false;
              $res['message'] = 'FAILED_INSERT_CANCELLATION_LETTER_INSERT_CANCEL';
              DB::table('message')->where('id','=',$message_data->id)->delete();

              return response($res);
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'SP_ID_NOT_FOUND';
            DB::table('message')->where('id','=',$message_data->id)->delete();

            return response($res);  
          }
          
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_INSERT_MESSAGE';

          return response($res);  
        }
      }else{

        $res['success'] = false;
        $res['message'] = 'FAILED_DATA_CANCEL_DETAIL_FOUND';

        return response($res); 
      }
    }
    public function setMbpTrouble($mbp_id, $type, $text_message, $available_status, $active_at){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      // $rtpo_id = $request->input('rtpo_id');
      // $user_id_mbp = $request->input('user_id');


      // $mbp_id = $request->input('mbp_id');
      // $type = $request->input('type');
      // $text_message = $request->input('text_message');
      // $available_status = $request->input('available_status');
      // $active_at = $request->input('time');


      if($mbp_id==NULL){
        $res['success'] = false;
        $res['message'] = 'FAILED_MBP_ID_NULL';
        return response($res);
      }
      if($text_message==NULL){
        $res['success'] = false;
        $res['message'] = 'FAILED_TEXT_MESSAGE_NULL';
        return response($res);
      }
      if($available_status==NULL){
        $res['success'] = false;
        $res['message'] = 'FAILED_AVAILABLE_STATUS_NULL';
        return response($res);
      }
      if($active_at==NULL){
        $res['success'] = false;
        $res['message'] = 'FAILED_ACTIVE_AT_NULL';
        return response($res);
      }

      if ($available_status=='UNAVAILABLE') {
        $request_to_unavailable = 1;
      }else{
        $request_to_unavailable = 0;
      }

      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
      ->join('rtpo', 'mbp.rtpo_id', 'rtpo.rtpo_id')
      ->join('users', 'user_mbp.username', '=', 'users.username')
      ->select('*','users.id as user_id')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      if($mbp_data==NULL){
        $res['success'] = false;
        $res['message'] = 'FAILED_MBP_DATA_NOT_FOUND';
        return response($res);
      }

      $data['id'] = '';
      $data['mbp_id'] = $mbp_id;
      $data['send_by_nik'] = $mbp_data->user_id;

      $data['send_by_cn'] = $mbp_data->username;
      $data['send_date'] = $date_now;
      $data['send_to_rtpo_id'] = $mbp_data->rtpo_id;
      $data['send_to_rtpo_name'] = $mbp_data->rtpo_name;
      $data['desc'] = $text_message;

      $data['request_to_unavailable'] = $request_to_unavailable;
      $data['mbp_active_at'] = $active_at;
      $data['type'] = $type;
      $data['respon_by_nik'] = '';
      $data['respon_by_cn'] = '';
      $data['is_approved'] = '';

      $data['respon_date'] = '';
      $data['is_active'] = '';


      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);
    }

    public function getCancellationLetterPaginate(Request $request){
      $rtpo_id = $request->input('rtpo_id');
      $page = $request->input('page');

      $limit = 10;
      $offset = ($page-1)*$limit;

      $mbp_trouble = DB::table('mbp_trouble as mtr')
      ->join('mbp as m', 'mtr.mbp_id', 'm.mbp_id')
      ->join('supplying_power as sp', 'mtr.sp_id', 'sp.sp_id')
      ->select('mtr.id as cancel_id','mtr.type as subject','mtr.desc as text_message','m.mbp_id as mbp_id','m.mbp_name','sp.site_id','sp.site_name','mtr.send_date as date')
      ->where('mtr.is_active',1)
      ->where('mtr.send_to_rtpo_id',$rtpo_id)
      ->offset($offset)
      ->limit($limit)
      ->get();

      foreach ($mbp_trouble as $key => $value) {
        $date2 = $this->tanggal_bulan_tahun_indo_tiga_char($value->date);
        $value->date = $date2;
      }

      if ($mbp_trouble) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_trouble;

        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_MESSAGE';
        
        return response($res);
      }
    }

    public function sendCancellationLetterToRtpoNew(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = @$request->input('rtpo_id');
      $user_id_mbp = @$request->input('user_id');
      $mbp_id = $request->input('mbp_id');
      $text_message = $request->input('text_message');
      $cancel_category = @$request->input('cancel_category');
      $available_status = $request->input('available_status');
      $active_at = $request->input('time');

      $sp_data = DB::table('supplying_power as sp')
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

      $data_mbp = DB::table('mbp')
      ->select('*')
      ->where('mbp_id',$mbp_id)
      ->first();

      $rtpo_id = $data_mbp->rtpo_id;
      
      if(empty($rtpo_id) || $rtpo_id==0) {
        $res['success'] = false;
        $res['message'] = 'EMPTY_RTPO! Silakan logout dan login kembali';
        return response($res);
      }

      $cek_duplicate = DB::table('mbp_trouble')
      ->select('*')
      ->where('sp_id',$sp_data->sp_id)
      ->where('is_active', '1')
      ->first();

      if ($cek_duplicate){
        $res['success'] = false;
        $res['message'] = 'Gagal! Duplikasi Tiket!';
        return response($res);
      }
      
      $insert_mbp_trouble = DB::table('mbp_trouble')
      ->where('sp_id', $sp_data->sp_id)
      ->where('is_active', '1')
      ->delete();

      $insert_message = DB::table('message')
      ->insert(
        [
          'subject' => @'CANCEL', 
          'from' => @$user_id_mbp,
          'text_message' => @$text_message,
          'date_message' => @$date_now.'',
        ]
      );

      if (!$insert_message) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MESSAGE_DATA';
        return response($res);
      }

      if ($available_status=='UNAVAILABLE') {
        $request_to_unavailable = 1;
        $mbp_active_at = $active_at;
      }elseif ($available_status=='AVAILABLE') {
        $request_to_unavailable = 0;
        $mbp_active_at = null;
      }

      $insert_mbp_trouble = DB::table('mbp_trouble')
      ->insert(
        [
          'send_to_rtpo_id' => $rtpo_id,
          'send_to_rtpo_name' => $sp_data->rtpo_name,
          
          'desc' => $text_message,
          'cancel_category' => @$cancel_category,
          'send_by_nik' => $user_id_mbp,
          'send_by_cn' => $sp_data->user_mbp_cn,
          'type' => 'CANCEL',
          'mbp_id' => $mbp_id,
          'sp_id' => $sp_data->sp_id,
          'send_date' => $date_now.'',
          'request_to_unavailable' => $request_to_unavailable,
          'mbp_active_at' => $mbp_active_at,
          'is_active' => 1,
        ]
      );

      if (!$insert_mbp_trouble) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MBP_TROUBLE';
        return response($res);
      }


      $after_in_data = DB::table('mbp_trouble as mtr')
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
      ->update(
        [
          'submission' => 'CANCEL',
          'submission_id' => $after_in_data->mtr_id,
          'active_at' => $after_in_data->mbp_active_at,
          'message_id' => $after_in_data->msg_id,
        ]
      );

      $supplyingPowerController = new SupplyingPowerController;
      $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $user_id_mbp, $sp_data->user_mbp_cn, 'SUBMIT_CANCEL', $sp_data->user_mbp_cn.' mengajukan pembatalan penugasan kepada rtpo dengan alasan sebagai berikut : '.$text_message,$text_message, '', $date_now);

      $notificationController = new NotificationController;
      $tmp = $notificationController->setNotificationMbpSubmission($sp_data->mbp_name,$sp_data->site_name,$rtpo_id, $sp_data->message_id, 'CANCEL');

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] = $mbp_data;
      return response($res);
    }

    public function sendDelayLetterToRtpoNew(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = $request->input('rtpo_id');
      $user_id_mbp = $request->input('user_id');
      $mbp_id = $request->input('mbp_id');
      $text_message = $request->input('text_message');
      // $available_status = $request->input('available_status');
      $active_at = $request->input('time');

      $data_mbp = DB::table('mbp')
      ->select('*')
      ->where('mbp_id',$mbp_id)
      ->first();

      $rtpo_id = $data_mbp->rtpo_id;

      if(empty($rtpo_id) || $rtpo_id==0) {
        $res['success'] = false;
        $res['message'] = 'EMPTY_RTPO! Silakan logout dan login kembali';
        return response($res);
      }


      $sp_data = DB::table('supplying_power as sp')
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
      ->insert(
        [
          'subject' => 'DELAY', 
          'from' => $user_id_mbp,
          'text_message' => $text_message,
          'date_message' => $date_now.'',
        ]
      );

      if (!$insert_message) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MESSAGE_DATA';
        return response($res);
      }

      $mbp_active_at = date('Y-m-d H:i:s', strtotime($date_now.' + '.$active_at.' minutes'));
      $insert_mbp_trouble = DB::table('mbp_trouble')
      ->insert(
        [
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
        ]
      );

      if (!$insert_mbp_trouble) {
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_MBP_TROUBLE';
        return response($res);
      }

      $after_in_data = DB::table('mbp_trouble as mtr')
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
      ->update(
        [
          'submission' => 'DELAY',
          'submission_id' => $after_in_data->mtr_id,
          'active_at' => $after_in_data->mbp_active_at,
          'message_id' => $after_in_data->msg_id,
        ]
      );

      $supplyingPowerController = new SupplyingPowerController;
      $value_sp_log = $supplyingPowerController->saveLogSP1($sp_data->sp_id, $user_id_mbp, $sp_data->user_mbp_cn, 'SUBMIT_DELAY', $sp_data->user_mbp_cn.' mengirimkan pengajuan delay kepada rtpo dengan pesan sebagai berikut : '.$text_message,$text_message, '', $date_now);

      $notificationController = new NotificationController;
      $tmp = $notificationController->setNotificationMbpSubmission($sp_data->mbp_name,$sp_data->site_name,$rtpo_id, $sp_data->message_id, 'DELAY');

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
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