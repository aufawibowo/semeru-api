<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class NotificationController extends Controller
{

  public function testnotifv1(Request $request){

      // $topic = '/topics/'.$this->checkMyFMCtopic($app_corrective_data->fmc_id);
     $fb= $this->sendNotifFast('Tiket Corrective', 'agus_b dari IDE menyetujui pengajuan pending anda','/topics/IDE','corrective_id','corrective_id','RTPO_APPROVE_PENDING_TICKET_CORRECTIVE_FROM_FMC');


      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $fb;
      return $res;
  }

  public function getTelegramQueue(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $queue_telegram_data = DB::table('queue_telegram')
    ->select('id','chat_id','message', 'create_at as date_created')
    ->where('sent','=',0)
    ->get();

    foreach ($queue_telegram_data as $param) {

      $update_queue_telegram_data = DB::table('queue_telegram')
      ->where('id', $param->id)
      ->update(
        [
          'sent' => '1',
          'send_at' => $date_now,
        ]
      );
    }

    $res['success'] = true;
    $res['data'] = $queue_telegram_data;
    return response($res);
  }

  public function setNotification(Request $request){

    $type = $request->input('type');

    switch ($type) {
      case "MBP_ASSiGNMENT_TO_SITE":
      // echo "Your favorite color is red!";
      // buat notifikasinya, isi datanya dan tentukan tujuannya siapa aja..:D

      $mbp_name = $request->input('mbp_name');
      $site_name = $request->input('site_name');
      $mbp_id = $request->input('mbp_id');
      $tmp = $this->setNotificationMbpAssignment($mbp_name,$site_name,$mbp_id);
      return response($tmp);

      break;
      case "MBP_STATUS_TO_SITE":
      // echo "Your favorite color is blue!";
      break;
      case "MBP_SUBMISSION":
      // echo "Your favorite color is green!";
      break;
      case "MBP_STATUS_ACTIVE_NOT_ACTIVE":
      // echo "Your favorite color is green!";
      break;
      default:
      // echo "Your favorite color is neither red, blue, nor green!";
    }
  }
  public function setNotification0($type,$mbp_name,$site_name,$mbp_id,$mbp_status,$rtpo_id){

    // $type = $request->input('type');

    switch ($type) {
      case "MBP_ASSIGNMENT_TO_SITE":
      // echo "Your favorite color is red!";
      // buat notifikasinya, isi datanya dan tentukan tujuannya siapa aja..:D
      $tmp = $this->setNotificationMbpAssignment($mbp_name,$site_name,$mbp_id);
      return $tmp;

      break;
      case "MBP_STATUS_TO_SITE": //dimana mbp menerima tugas sampia semua tugas"nya selesai dilaksanakan
      $tmp = $this->setNotificationMbpStatusWork($mbp_name,$site_name,$mbp_id,$mbp_status,$rtpo_id);
      return $tmp;
      // echo "Your favorite color is blue!";
      break;
      case "MBP_SUBMISSION":
      // $tmp = $this->setNotificationMbpStatusWork($mbp_name,$site_name,$mbp_id,$mbp_status,$rtpo_id);
      // return $tmp;
      // echo "Your favorite color is green!";
      break;
      case "MBP_STATUS_ACTIVE_NOT_ACTIVE":
      // echo "Your favorite color is green!";
      break;
      default:
      // echo "Your favorite color is neither red, blue, nor green!";
    }
  }
  // RTPO memberikan tugas dan notif dikirim ke user pemegang mbp tersebut dan ke semua user rtpo pemilik mbp
  public function setNotificationMbpAssignment($mbp_name,$site_name,$mbp_id){

    // $mbp_name=$mbp_result[0]['mbp_name'].'';
    // $site_name=$site_result[0]['site_name'].''
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_ASSIGNMENT_TO_SITE', 
        'name_type_id' => 'mbp_id',
        'type_id' => $mbp_id,
        'tittle' => 'Penugasan MBP',
        'subject' => 'REQUEST MBP TO SITE DOWN',
        'text' => $mbp_name.' telah ditugaskan menuju '.$site_name,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_ASSiGNMENT_TO_SITE')
      // ->where('text','=',$mbp_name.' telah ditugaskan menuju '.$site_name)
      ->first();

      if ($notif_data) {

      # menentukan siapa saja yang akan dikirimi notifikasi
      // cari setiap user pada rtpo_sendiri
      // get semua 
      // dan insert ke tabel user_notification user_id + notification_id dengan perulangan

        $mbp_data = DB::table('mbp')
        ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
        ->join('users', 'user_mbp.username', '=', 'users.username')
        ->select('*', 'users.username as user_id')
        ->where('mbp.mbp_id','=',$mbp_id)
        ->first();

        if ($mbp_data) {
          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $mbp_data->user_id,
              'read_status' => 'UNREAD',
            ]
          );

          // $firebaseController = new FireBaseController;
          // $tmp = $firebaseController->sendNotification('Penugasan MBP','Tugas untuk '.$mbp_name.' menuju site '.$site_name,$mbp_data->firebase_token,$notif_data->name_type_id,$notif_data->type_id,'MBP_ASSIGNMENT_TO_SITE');


          // $fireBaseController = new FireBaseController;

          // $user_rtpo_data = DB::table('users as u')
          // ->join('user_rtpo as ur', 'u.username', 'ur.user_mbp')
          // ->select('*')
          // ->where('ur.rtpo_id','=',$mbp_data->rtpo_id)
          // ->where('u.firebase_token','!=','')
          // ->get();


          $fbc = new FireBaseController;
          $tmp_fb = $fbc->sendNotification('MBP','Tugas untuk '.$mbp_name.' menuju site '.$site_name,$mbp_data->firebase_token,1,$notif_data->type_id,'MBP_ASSIGNMENT_TO_SITE');

          if ($insertUN) {

            $rtpo_id = $mbp_data->rtpo_id;
            $rtpo_data = DB::table('user_rtpo')
            ->join('users', 'user_rtpo.username', '=', 'users.username')
            ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
            ->select('*', 'users.username as user_id')
            ->where('user_rtpo.rtpo_id','=',$rtpo_id)
            ->get();

            $to_token_id = array();
            // while($row = @$rtpo_data->fetch_assoc()){

            $result = json_decode($rtpo_data, true);
            foreach ($result as $param => $row) {
              array_push($to_token_id,$row['firebase_token']);
            }
            // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);

            $fbc = new FireBaseController;
            $fbc->sendNotification('MBP',$mbp_name.' telah ditugaskan menuju '.$site_name,$to_token_id,1,$notif_data->type_id,'MBP_ASSIGNMENT_TO_SITE');
            

            $result = json_decode($rtpo_data, true);
            
            foreach ($result as $param => $row) {
              $user_id_rtpo[$param]  = $row['user_id'].'';
              $firebase_token[$param]  = $row['firebase_token'].'';

              $insertUN = DB::table('user_notification')->insert(
                [
                  'notification_id' => $notif_data->id, 
                  'user_id' => $row['user_id'].'',
                  'read_status' => 'UNREAD',
                ]
              );

              if ($insertUN) {
                
                // $firebaseController = new FireBaseController;
                // $tmp = $firebaseController->sendNotification('Penugasan MBP',$mbp_name.' telah ditugaskan menuju '.$site_name,$row['firebase_token'].'',$notif_data->name_type_id,$notif_data->type_id,'MBP_ASSIGNMENT_TO_SITE');
                // if($tmp){

                // }

              }else{
                DB::table('user_notification')
                ->where('notification_id','=',$notif_data->id)
                ->delete();

                DB::table('notification')
                ->where('id','=',$notif_data->id)
                ->delete();

                $res['success'] = false;
                $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
                return $res;
              }
            }

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            return $res;
          }
        }
      }
    }
  }
  // MBP mengirim perubahan status mbp (waiting -> Done) , ke seluruh user rtpo pemilik mbp
  public function setNotificationMbpStatusWork($mbp_name,$site_name,$mbp_id, $mbp_status,$rtpo_id){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    if ($mbp_status=='AVAILABLE') {
      $text = $mbp_name.' telah menyelesaikan tugasnya dan siap bekerja kembali ';
    }else if ($mbp_status=='ON_PROGRESS'){
      $text = $mbp_name.' menerima tugas dan siap berangkat ke '.$site_name;
    }else if ($mbp_status=='CHECK_IN'){
      $text = $mbp_name.' tiba di '.$site_name;
    }else {
      $text = 'Status '.$mbp_name.' menuju '.$site_name.' menjadi '.$mbp_status;
    }

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_STATUS_TO_SITE', 
        'name_type_id' => 'mbp_id',
        'type_id' => $mbp_id,
        'tittle' => 'Status Penugasan',
        'subject' => 'STATUS MBP TO '.$mbp_status,
        'text' => $text,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_STATUS_TO_SITE')
      // ->where('text','=',$mbp_name.' telah '.$mbp_status.' di '.$site_name)
      ->first();

      if ($notif_data) {

        $rtpo_data = DB::table('user_rtpo')
        ->join('users', 'user_rtpo.username', 'users.username')
        ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
        ->select('*', 'users.username as user_id')
        ->where('user_rtpo.rtpo_id','=',$rtpo_id)
        // ->select('*')
        // ->where('rtpo_id','=',$rtpo_id)
        ->get();

        // $firebaseController = new FireBaseController;
        // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        // $tmp = $this->sendNotifFast
        
        $to_token_id = array();
        // while($row = $rtpo_data->fetch_assoc()){

        $result = json_decode($rtpo_data, true);
        foreach ($result as $param => $row) {
          array_push($to_token_id,$row['firebase_token']);
        }
            // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        
        $fbc = new FireBaseController;
        $fbc->sendNotification('MBP',$text,$to_token_id,1,$notif_data->type_id,'MBP_STATUS_TO_SITE');
        // if($tmp){

        // }

        // $fireBaseController = new FireBaseController;
        // $topic = '/topics/'.$fireBaseController->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        // $fireBaseController->sendNotifFast('Status Penugasan',$text,$topic,$notif_data->name_type_id,$notif_data->type_id,'MBP_STATUS_TO_SITE');

        $result = json_decode($rtpo_data, true);

        foreach ($result as $param => $row) {
          $user_id_rtpo[$param]  = $row['user_id'].'';

          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $row['user_id'].'',
              'read_status' => 'UNREAD',
            ]
          );

          if ($insertUN) {
            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification('Status Penugasan',$text,$row['firebase_token'].'',$notif_data->name_type_id,$notif_data->type_id,'MBP_STATUS_TO_SITE');
            // if($tmp){

            // }
          }else{
            DB::table('user_notification')
            ->where('notification_id','=',$notif_data->id)
            ->delete();

            DB::table('notification')
            ->where('id','=',$notif_data->id)
            ->delete();

            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
            return $res;
          }
        }

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;

      }
    }
  }
  // MBP mengirim info penundaan dan pembatalan , notif di kirim ke seluruh user rtpo pemilik mbp
  public function setNotificationMbpSubmission($mbp_name,$site_name,$rtpo_id, $message_id, $submission_type){

    // $mbp_name=$mbp_result[0]['mbp_name'].'';
    // $site_name=$site_result[0]['site_name'].''
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    if ($submission_type=='DELAY') {
      $title = 'Informasi Penundaan';
      $text = $mbp_name.' mengajukan penundaan untuk penugasannya menuju '.$site_name;

      $insertSP = DB::table('notification')->insert(
        [
          'type' => 'MBP_SUBMISSION', 
          'name_type_id' => 'rtpo_id',
          'type_id' => $rtpo_id,
          'tittle' => 'Pengajuan Penundaan',
          'subject' => 'DELAY',
          'text' => $mbp_name.' mengajukan penundaan untuk penugasannya menuju '.$site_name,
          'date' => $date_now,
          'category_id' => 1,
          'category' => 'MBP',
        ]
      );
    }else if ($submission_type=='CANCEL') {
      $title = 'Pengajuan Pembatalan';
      $text = $mbp_name.' mengajukan pembatalan untuk penugasannya menuju '.$site_name;

      $insertSP = DB::table('notification')->insert(
        [
          'type' => 'MBP_SUBMISSION', 
          'name_type_id' => 'rtpo_id',
          'type_id' => $rtpo_id,
          'tittle' => 'Pengajuan Pembatalan',
          'subject' => 'CANCEL',
          'text' => $mbp_name.' mengajukan pembatalan untuk penugasannya menuju '.$site_name,
          'date' => $date_now,
          'category_id' => 1,
          'category' => 'MBP',
        ]
      );
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_SUBMISSION_TYPE_NOT_MATCH';
      return $res;
    }

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_SUBMISSION')
      // ->where('text','=','Status penugasan '.$mbp_name.' menuju '.$site_name.' menjadi '.$mbp_status)
      ->first();

      if ($notif_data) {

        $rtpo_data = DB::table('user_rtpo')
        ->join('users', 'user_rtpo.username', '=', 'users.username')
        ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
        ->select('*', 'users.username as user_id')
        ->where('user_rtpo.rtpo_id','=',$rtpo_id)
        ->get();

        // $firebaseController = new FireBaseController;
        // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        // $tmp = $this->sendNotifFast($title,$text,$topic,$notif_data->name_type_id,$notif_data->type_id,'MBP_SUBMISSION');
        // if($tmp){

        // }


        $to_token_id = array();
        // while($row = $rtpo_data->fetch_assoc()){

        $result = json_decode($rtpo_data, true);
        foreach ($result as $param => $row) {
          array_push($to_token_id,$row['firebase_token']);
        }
            // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        
        $fbc = new FireBaseController;
        $fbc->sendNotification('MBP',$text,$to_token_id,1,$notif_data->type_id,'MBP_SUBMISSION');

        $result = json_decode($rtpo_data, true);

        foreach ($result as $param => $row) {
          $user_id_rtpo[$param]  = $row['user_id'].'';

          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $row['user_id'].'',
              'read_status' => 'UNREAD',
            ]
          );

          if ($insertUN) {
            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification($title,$text,$row['firebase_token'].'',$notif_data->name_type_id,$notif_data->type_id,'MBP_SUBMISSION');
            // if($tmp){

            // }
          }else{
            DB::table('user_notification')
            ->where('notification_id','=',$notif_data->id)
            ->delete();

            DB::table('notification')
            ->where('id','=',$notif_data->id)
            ->delete();

            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
            return $res;
          }
        }

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;

      }
    }
  }
  // MBP mengganti dari aktif ke tdak aktif dan sebaliknya, notif di kirim ke seluruh user rtpo pemilik 
  public function setNotificationMbpActiveNotActive($mbp_status,$mbp_name,$mbp_id,$rtpo_id){

    // $mbp_name=$mbp_result[0]['mbp_name'].'';
    // $site_name=$site_result[0]['site_name'].''
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    if ($mbp_status=='AVAILABLE') {
      $text = $mbp_name.' dapat bertugas kembali';
    }else if ($mbp_status=='UNAVAILABLE'){
      $text = $mbp_name.' tidak dapat bertugas untuk sementara waktu';
    }else {
      $text = $mbp_name.' telah merubah statusnya';
    }

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_STATUS_ACTIVE_NOT_ACTIVE', 
        'name_type_id' => 'mbp_id',
        'type_id' => $mbp_id,
        'tittle' => 'Perubahan Status MBP',
        'subject' => 'MBP_INFORMATION_AVAILABLE',
        'text' => $text,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_STATUS_ACTIVE_NOT_ACTIVE')
      // ->where('text','=',$mbp_name.' telah '.$mbp_status.' di '.$site_name)
      ->first();

      if ($notif_data) {

        $rtpo_data = DB::table('user_rtpo')
        ->join('users', 'user_rtpo.username', '=', 'users.username')
        ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
        ->select('*', 'users.username as user_id')
        ->where('user_rtpo.rtpo_id','=',$rtpo_id)
        ->get();
        // $firebaseController = new FireBaseController;
        // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        // $tmp = $this->sendNotifFast('Perubahan Status MBP',$text,$topic,'mbp_id',$mbp_id,'MBP_STATUS_ACTIVE_NOT_ACTIVE');
        // if($tmp){

        // }


        $to_token_id = array();
        // while($row = $rtpo_data->fetch_assoc()){

        $result = json_decode($rtpo_data, true);
        foreach ($result as $param => $row) {
          array_push($to_token_id,$row['firebase_token']);
        }
            // $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        
        $fbc = new FireBaseController;
        $fbc->sendNotification('MBP',$text,$to_token_id,1,$mbp_id,'MBP_STATUS_ACTIVE_NOT_ACTIVE');


        $result = json_decode($rtpo_data, true);

        foreach ($result as $param => $row) {
          $user_id_rtpo[$param]  = $row['user_id'].'';

          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $row['user_id'].'',
              'read_status' => 'UNREAD',
            ]
          );

          if ($insertUN) {
            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification('Perubahan Status MBP',$text,$row['firebase_token'].'','mbp_id',$mbp_id,'MBP_STATUS_ACTIVE_NOT_ACTIVE');
            // if($tmp){

            // }
          }else{
            DB::table('user_notification')
            ->where('notification_id','=',$notif_data->id)
            ->delete();

            DB::table('notification')
            ->where('id','=',$notif_data->id)
            ->delete();

            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
            return $res;
          }
        }

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;

      }
    }
  }
  // RTPO membatalkan penugasan, notif dikirim ke user pemegang mbp tersebut 
  public function setNotificationCancelMbpAssignment($mbp_name,$mbp_id){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_CANCEL_ASSIGNMENT_TO_SITE', 
        'name_type_id' => 'mbp_id', 
        'type_id' => $mbp_id,
        'tittle' => 'Pembatalan Penugasan MBP',
        'subject' => 'CANCEL REQUEST MBP TO SITE DOWN',
        'text' => 'Pembatalan penugasan untuk '.$mbp_name,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_CANCEL_ASSIGNMENT_TO_SITE')
      // ->where('text','=',$mbp_name.' telah ditugaskan menuju '.$site_name)
      ->first();

      if ($notif_data) {

      # menentukan siapa saja yang akan dikirimi notifikasi
      // cari setiap user pada rtpo_sendiri
      // get semua 
      // dan insert ke tabel user_notification user_id + notification_id dengan perulangan

        $mbp_data = DB::table('mbp')
        ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
        ->join('users', 'user_mbp.username', '=', 'users.username')
        ->select('*', 'users.username as user_id')
        ->where('mbp.mbp_id','=',$mbp_id)
        ->first();

        if ($mbp_data) {
          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $mbp_data->user_id,
              'read_status' => 'UNREAD',
            ]
          );

          if($insertUN){
            // $firebaseController = new FireBaseController;
            $fbc = new FireBaseController;
            $tmp_fb = $fbc->sendNotification('MBP','Tugas untuk '.$mbp_name.' dibatalkan',$mbp_data->firebase_token,1,$mbp_id,'MBP_CANCEL_ASSIGNMENT_TO_SITE');

            if ($tmp_fb) {
              $res['success'] = true;
              $res['message'] = 'SUCCESS';
              return $res;
            }else{
              $res['success'] = false;
              $res['message'] = 'FAILED_SEND_NOTIFICATION';
              return $res;
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_DATA_NOTIFICAION';
            return $res;  
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'MBP_DATA_NOT_FOUND';
          return $res;
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'NOTIFICATION_DATA_NOT_FOUND';
        return $res;
      }
    }
  }
  // RTPO Cancel/Delay Approve/Deny dan notif dirikim ke user pemilik mbp tersebut 
  public function setNotificationSubmissionAgreement($type_agreement,$mbp_name,$mbp_id){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    switch ($type_agreement) {
      case "APPROVE_CANCEL":
      $type     = 'RTPO_APPROVE_CANCEL_ASSIGNMENT';
      $title    = 'Pengajuan Pembatalan Disetujui';
      $subject  = 'SUBMISSION APPROVAL';
      $text     = 'RTPO menyetujui pengajuan pembatalan anda';
      break;
      case "DENY_CANCEL":
      $type     = 'RTPO_DENY_CANCEL_ASSIGNMENT';
      $title    = 'Pengajuan Pembatalan Tidak Disetujui';
      $subject  = 'SUBMISSION APPROVAL';
      $text     = 'RTPO tidak menyetujui pengajuan pembatalan anda';
      break;
      case "APPROVE_DELAY":
      $type     = 'RTPO_APPROVE_DELAY_ASSIGNMENT';
      $title    = 'Penundaan Disetujui';
      $subject  = 'SUBMISSION APPROVAL';
      $text     = 'RTPO menyetujui pengajuan penundaan anda';
      break;
      case "DENY_DELAY":
      $type     = 'RTPO_DENY_DELAY_ASSIGNMENT';
      $title    = 'Penundaan Tidak Disetujui';
      $subject  = 'SUBMISSION APPROVAL';
      $text     = 'RTPO tidak menyetujui pengajuan penundaan anda';
      break;
      default:
    }

    $insertSP = DB::table('notification')->insert(
      [
        'type' => $type, 
        'name_type_id' => 'mbp_id',
        'type_id' => $mbp_id,
        'tittle' => $title,
        'subject' => $subject,
        'text' => $text,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=',$type)
      // ->where('text','=',$mbp_name.' telah ditugaskan menuju '.$site_name)
      ->first();

      if ($notif_data) {

      # menentukan siapa saja yang akan dikirimi notifikasi
      // cari setiap user pada rtpo_sendiri
      // get semua 
      // dan insert ke tabel user_notification user_id + notification_id dengan perulangan

        $mbp_data = DB::table('mbp')
        ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
        ->join('users', 'user_mbp.username', '=', 'users.username')
        ->select('*', 'users.username as user_id')
        ->where('mbp.mbp_id','=',$mbp_id)
        ->first();

        if ($mbp_data) {
          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $mbp_data->user_id,
              'read_status' => 'UNREAD',
            ]
          );

          if($insertUN){
            $fbc = new FireBaseController;
            $tmp_fb = $fbc->sendNotification('MBP',$text,$mbp_data->firebase_token,1,$mbp_id,$type);

            if ($tmp_fb) {
              $res['success'] = true;
              $res['message'] = 'SUCCESS';
              return $res;
            }else{
              $res['success'] = false;
              $res['message'] = 'FAILED_SEND_NOTIFICATION';
              return $res;
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_DATA_NOTIFICAION';
            return $res;  
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'MBP_DATA_NOT_FOUND';
          return $res;
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'NOTIFICATION_DATA_NOT_FOUND';
        return $res;
      }
    }
  }
  // (!) RTPO SEND_SOS, SEND_MBP(belum ngirim notif ke driver mbpnya), RTPO_CANCEL_SEND_SOS(belum ngirim notif ke driver mbpnya), RTPO_RETURN_MBP(belum ngirim notif ke driver mbpnya)
  public function setNotificationSendSosAndMbp($type_task,$array_mbp_id,$sos_id,$rtpo_id_from,$rtpo_id_to){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');


    $rtpo_from = DB::table('rtpo')
    ->select('rtpo_name','regional')
    ->where('rtpo_id','=',$rtpo_id_from)
    ->first();

    $rtpo_to = DB::table('rtpo')
    ->select('rtpo_name')
    ->where('rtpo_id','=',$rtpo_id_to)
    ->first();

    switch ($type_task) {
      case "SEND_SOS":
      $type     = 'RTPO_SEND_SOS';
      $type_id  = $sos_id;
      $name_type_id     = 'sos_id';
      $title    = 'Permintaan peminjaman mbp';
      $subject  = 'SEND SOS';
      $text     = $rtpo_from->rtpo_name.' membutuhkan bantuan mbp';
      break;
      case "SEND_MBP":
      $type     = 'RTPO_SEND_MBP_TO_SOS';
      $type_id  = $rtpo_to->rtpo_name;
      $name_type_id     = 'rtpo_id';
      $title    = 'Meminjamkan MBP';
      $subject  = 'SEND MBP TO SOS';
      $text     = $rtpo_from->rtpo_name.' mengirimkan MBP kepada '.$rtpo_to->rtpo_name;
      break;
      case "RTPO_CANCEL_SEND_SOS":
      $type     = 'RTPO_CANCEL_SEND_SOS';
      $type_id  = '';
      $name_type_id     = '';
      $title    = 'Pembatalan Permintaan Bantuan';
      $subject  = 'RTPO CANCEL SEND SOS';
      $text     = $rtpo_from->rtpo_name.' membatalkan permintaan bantuan ';
      break;
      case "RTPO_RETURN_MBP":
      $type     = 'RTPO_RETURN_MBP';
      $type_id  = $rtpo_to->rtpo_name;
      $name_type_id     = 'rtpo_id';
      $title    = 'Mengembalikan MBP';
      $subject  = 'RTPO RETURN MBP';
      $text     = $rtpo_from->rtpo_name.' mengembalikan MBP kepada '.$rtpo_to->rtpo_name;
      break;
      default:
    }

    $insertSP = DB::table('notification')->insert(
      [
        'type' => $type, 
        'name_type_id' => $name_type_id,
        'type_id' => $type_id,
        'tittle' => $title,
        'subject' => $subject,
        'text' => $text,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=',$type)
      // ->where('text','=',$mbp_name.' telah '.$mbp_status.' di '.$site_name)
      ->first();


      if ($notif_data) {

        $rtpo_data = DB::table('user_rtpo')
        ->join('users', 'user_rtpo.username', '=', 'users.username')
        ->select('*','users.username as user_id')
        ->where('users.regional','=',@$rtpo_from->regional)
        ->get();

        $result = json_decode($rtpo_data, true);

        $to_token_id = array();

        foreach ($result as $param => $row) {

          array_push($to_token_id,$row['firebase_token']);
          
          $user_id_rtpo[$param]  = $row['user_id'].'';

          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $row['user_id'].'',
              'read_status' => 'UNREAD',
            ]
          );

          if ($insertUN) {
            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification($title,$text,$row['firebase_token'].'',$type);

            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification($title,$text,$row['firebase_token'].'','sos_id',$sos_id,$type);

            // if($tmp){

            // }
          }else{
            DB::table('user_notification')
            ->where('notification_id','=',$notif_data->id)
            ->delete();

            DB::table('notification')
            ->where('id','=',$notif_data->id)
            ->delete();

            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
            return $res;
          }
        }

        // $firebaseController = new FireBaseController;
        // $topic = '/topics/RTPO_ALL';
        // $tmp = $this->sendNotifFast($title,$text,$topic,$name_type_id,$type_id,$type);
        
        $fbc = new FireBaseController;
        $fbc->sendNotification('MBP',$text,$to_token_id,1,$type_id,$type);


        // if($tmp){

          if ($array_mbp_id!=null) {
            $this->setNotificationMbpSosActivity($type_task,$array_mbp_id,$sos_id,$rtpo_id_from,$rtpo_id_to);
          }

        // }

        

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;

      }
    }
  }

  public function checkNotif(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');


    $array_mbp_id = $request->input('array_mbp_id');       
    $rtpo_id_from = $request->input('rtpo_id_from');
    $rtpo_id_to = $request->input('rtpo_id_to');
    $sos_id = $request->input('sos_id');       
    $type_task = $request->input('type_task');

    // if ($array_mbp_id==NULL || $array_mbp_id=='') {
    //   $res['message'] = 'array_mbp_id empty';
    //   $res['success'] = false;
    //   return response($res);
    // }
    if ($rtpo_id_from==NULL || $rtpo_id_from=='') {
      $res['message'] = 'rtpo_id_from empty';
      $res['success'] = false;
      return response($res);
    }
    if ($rtpo_id_to==NULL || $rtpo_id_to=='') {
      $res['message'] = 'array_mbp_id empty';
      $res['success'] = false;
      return response($res);
    }
    if ($sos_id==NULL || $sos_id=='') {
      $res['message'] = 'sos_id empty';
      $res['success'] = false;
      return response($res);
    }
    if ($type_task==NULL || $type_task=='') {
      $res['message'] = 'type_task empty';
      $res['success'] = false;
      return response($res);
    }

    // $this->setNotificationMbpSosActivity($type_task,$array_mbp_id,$sos_id,$rtpo_id_from,$rtpo_id_to);
    $this->setNotificationSendSosAndMbp($type_task,$array_mbp_id,$sos_id,$rtpo_id_from,$rtpo_id_to);

    $res['message'] = 'SUCCESS';
    $res['success'] = true;
    return response($res);
  }

  public function setNotificationComplatedOrChanged($type_task, $sos_id){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    // pastikan dulu tipenya apa? (SOS_COMPLATED/SOS_CHANGED)
      // SET type -> (SOS_COMPLATED/SOS_CHANGED)
    switch ($type_task) {
      case "SOS_COMPLETED":
      $type     = 'SOS_COMPLETED';
      $type_id  = $sos_id;
      $name_type_id = 'sos_id';
      $title    = 'SOS Terpenuhi';
      $subject  = 'SOS CSOMPLETED';
      $text     = 'Permintaan SOS RTPO anda terpenuhi';
      break;
      case "SOS_CHANGED":
      $type     = 'SOS_CHANGED';
      $type_id  = $sos_id;
      $name_type_id = 'sos_id';
      $title    = 'Permintaan SOS Dirubah';
      $subject  = 'SOS CHANGED';
      $text     = 'Permintaan SOS RTPO anda telah dirubah';
      break;
      default:
      // echo "Your favorite color is neither red, blue, nor green!";
    }

    //insert ke tabel notifikasi
    $insertSP = DB::table('notification')->insert(
      [
        'type' => $type, 
        'name_type_id' => $name_type_id,
        'type_id' => $type_id,
        'tittle' => $title,
        'subject' => $subject,
        'text' => $text,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    // if insert notification complated then
    if ($insertSP) {
      // dari user notifikasinya
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=',$type)
      ->first();

      // bila ketemu maka
      if ($notif_data) {

      // lanjut ke cari user_id pemilik sos tersebut (get rtpo_id from)
      // masukan "get"nya ke dalam foreach dan looping sebanyak user itu
      // bila get tidak menghasilkan apapun, maka di return true aja tanpa melakukan notif
      // dan lakukan fast notification dengan topic "RTPO_***" sesuai rtpo pemilik sos tersebut

        $rtpo_data = DB::table('user_rtpo')
        ->join('sos', 'user_rtpo.rtpo_id', '=', 'sos.rtpo_id')
        ->join('users', 'user_rtpo.username', '=', 'users.username')
        ->select('*','users.username as user_id')
        ->where('sos.id','=',$sos_id)
        ->get();

        $result = json_decode($rtpo_data, true);

        foreach ($result as $param => $row) {
          $user_id_rtpo[$param]  = $row['user_id'].'';

          $insertUN = DB::table('user_notification')->insert(
            [
              'notification_id' => $notif_data->id, 
              'user_id' => $row['user_id'].'',
              'read_status' => 'UNREAD',
            ]
          );

          if ($insertUN) {
            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification($title,$text,$row['firebase_token'].'',$type);

            // $firebaseController = new FireBaseController;
            // $tmp = $firebaseController->sendNotification($title,$text,$row['firebase_token'].'','sos_id',$sos_id,$type);

            // if($tmp){

            // }
          }else{
            DB::table('user_notification')
            ->where('notification_id','=',$notif_data->id)
            ->delete();

            DB::table('notification')
            ->where('id','=',$notif_data->id)
            ->delete();

            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
            return $res;
          }
        }

        // $firebaseController = new FireBaseController;
        // $topic = '/topics/RTPO_ALL';        
        $topic = '/topics/'.$this->checkMyRTPOtopic($rtpo_data[0]->rtpo_name);
        //unfinished
        $fbc = new FireBaseController;
        $tmp_fb = $fbc->sendNotification('MBP',$text,$topic,1,$type_id,$type);

      }

      // lanjut ke cari user_id pemilik sos tersebut (get rtpo_id from)
      // masukan "get"nya ke dalam foreach dan looping sebanyak user itu
      // bila get tidak menghasilkan apapun, maka di return true aja tanpa melakukan notif
      // dan lakukan fast notification dengan topic "RTPO_***" sesuai rtpo pemilik sos tersebut

    }
  }

  public function setNotificationMbpSosActivity($type_task,$array_mbp_id,$sos_id,$rtpo_id_from,$rtpo_id_to){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $rtpo_from = DB::table('rtpo')
    ->select('rtpo_name')
    ->where('rtpo_id','=',$rtpo_id_from)
    ->first();

    $rtpo_to = DB::table('rtpo')
    ->select('rtpo_name')
    ->where('rtpo_id','=',$rtpo_id_to)
    ->first();

    switch ($type_task) {
      case "SEND_SOS":
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return $res;
      break;
      case "SEND_MBP":
      $type     = 'SEND_MY_MBP_TO_SOS';
      // $type_id  = $mbp_id;
      $type_id  = 'mbp_id';
      $name_type_id     = 'mbp_id';
      $title    = 'Meminjamkan MBP';
      $subject  = 'END MBP TO SOS';
      $text     = $rtpo_from->rtpo_name.' mengirimkan MBP anda kepada '.$rtpo_to->rtpo_name;
      break;
      case "RTPO_CANCEL_SEND_SOS":
      $type     = 'RETURN_MY_MBP_FROM_SOS';
      // $type_id  = $mbp_id;
      $type_id  = 'mbp_id';
      $name_type_id     = 'mbp_id';
      $title    = 'Mengembalikan MBP';
      $subject  = 'RTPO RETURN MBP';
      $text     = $rtpo_from->rtpo_name.' mengembalikan MBP anda kepada rtpo asalnya';
      break;
      case "RTPO_RETURN_MBP":
      $type     = 'RETURN_MY_MBP_FROM_SOS';
      // $type_id  = $mbp_id;
      $type_id  = 'mbp_id';
      $name_type_id     = 'mbp_id';
      $title    = 'Mengembalikan MBP';
      $subject  = 'RTPO RETURN MBP';
      $text     = $rtpo_from->rtpo_name.' mengembalikan MBP anda kepada '.$rtpo_to->rtpo_name;
      break;
      default:
    }

    $insertSP = DB::table('notification')->insert(
      [
        'type' => $type, 
        'name_type_id' => $name_type_id,
        'type_id' => $type_id,
        'tittle' => $title,
        'subject' => $subject,
        'text' => $text,
        'date' => $date_now,
        'category_id' => 1,
        'category' => 'MBP',
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=',$type)
      ->first();


      if ($notif_data) {

        foreach ($array_mbp_id as $param => $row) {
          //get data mbp, cari usernamenya, dan cari firebase tokennya
          $mbp_data = DB::table('user_mbp')
          ->join('users', 'user_mbp.username', '=', 'users.username')
          ->select('*','users.username as user_id')
          ->where('user_mbp.mbp_id', '=', $row['mbp_id'])
          ->get();

          if ($mbp_data[0]!= null) {
            $insertUN = DB::table('user_notification')->insert(
              [
                'notification_id' => $notif_data->id, 
                'user_id' => $mbp_data[0]->user_id.'',
                'read_status' => 'UNREAD',
              ]
            );

            if ($insertUN) {
            # kirim notifnya melalui firebase..:D
              $fbc = new FireBaseController;
              $tmp_fb = $fbc->sendNotification('MBP',$text,$mbp_data[0]->firebase_token,1,$type_id,$type);

            }else{
              DB::table('user_notification')
              ->where('notification_id','=',$notif_data->id)
              ->delete();

              DB::table('notification')
              ->where('id','=',$notif_data->id)
              ->delete();

              $res['success'] = false;
              $res['message'] = 'FAILED_INSERT_USER_NOTIFICATION_ON_LOOPING';
              return $res;
            }
          }
        }
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;
      }
    }
  }

  public function getListNotification(Request $request){

    $user_id = $request->input('user_id');

    $notif_data = DB::table('user_notification')
    ->join('notification', 'user_notification.notification_id', '=', 'notification.id')
    ->select('*')
    ->where('user_notification.user_id','=',$user_id)
    ->where('user_notification.read_status','=','UNREAD')
    ->orderBy('notification.id', 'desc')
    ->limit(15)
    // ->offset(3)
    ->get();

    $result = json_decode($notif_data, true);
    if ($result==NULL) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $notif_data;
      // $res['data_real'] =  $notif_data;
      return response($res);
    }
    foreach ($result as $param => $row) { 
      $type_name = $row['name_type_id'];
      $data[$param]['id']       = $row['id'];
      $data[$param]['type']     = $row['type'];
      $data[$param]["$type_name"]  = (int)$row['type_id'];
      $data[$param]['type_id']  = (int)$row['type_id'];
      $data[$param]['title']    = $row['tittle'].'';
      $data[$param]['text']     = $row['text'].'';
      $data[$param]['date']     = $this->setDatedMYHis($row['date'].'');
    }

    // array_multisort($data, SORT_DESC);

    if ($notif_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] =  $data;
      return response($res);

    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA';
      return response($res);
    }
  }
  public function setDatedMYHis($date){
    if ($date==null) {
      return "";
    }else if ($date=='0000-00-00 00:00:00') {
      return "";
    }else{
      return date("d M Y, H:i", strtotime($date.''));
        // return strtotime($date.'');
    }
  }
  public function deleteNotification($notification_id){
    // hapus di table notifikasi dimana id == $notification_id
    // hapus semua data dimana notification_id == $notification_id

    $del_notification = DB::table('user_notification')
    ->where('notification_id','=',$notification_id)
    ->delete();

    if ($del_notification) {

      $del_user_notification = DB::table('notification')
      ->where('id','=',$notification_id)
      ->delete();

      if ($del_user_notification) {

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;

      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_DELETE_NOTIFICATION';
        return $res;        
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_DELETE_USER_NOTIFICATION';
      return $res;
    }   
  }
  public function getNotificationHomeRTPO($rtpo_id, $user_id){

    // $rtpo_id = $request->input('rtpo_id');
    // $user_id = $request->input('user_id');

    # 1. notifikasi mainfail site
    //cek site yang mainfail di rtpo dia


    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $rtpo_data = DB::table('rtpo')
    ->where('rtpo_id','=',$rtpo_id)
    ->first();

    $count_data_site_down = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','0')
    ->where('date_mainsfail','>',$date2)
    ->count();

    # 2. notifikasi sos
    //cek sos yang belum lengkap
    $count_data_sos = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->where('sos.status','=',NULL)
    ->where('rtpo.regional','=',$rtpo_data->regional)
    ->where('date','>',$date2)
    ->orderBy('date', 'desc')
    ->count();


    # 3. notifikasi pengajuan
    //cek list pengajuan rtpo yang belum di tanda tangani
    $count_submission_data = DB::table('mbp_trouble as mtr')
    ->where('mtr.is_active','=',1)
    ->where('mtr.send_to_rtpo_id','=',$rtpo_id)
    ->select('*')
    ->count();

    # 4. notifikasi umu pojok kanan atas
    // cek list notifikasi umum di atas berusukan update status terbaru mbp yang ditugaskan atau mbp sudah di kembalikan dr rtpo yang dipinjamkan
    $count_notif_data = DB::table('user_notification')
    ->join('notification', 'user_notification.notification_id', '=', 'notification.id')
    ->where('user_notification.user_id','=',$user_id)
    ->where('user_notification.read_status','=','UNREAD')
    ->orderBy('notification.id', 'desc')
    ->limit(15)
    ->count();


    $check_mbp_unavailable = DB::table('mbp')
    ->join('user_rtpo', 'mbp.rtpo_id', '=', 'user_rtpo.rtpo_id')
    ->join('users', 'user_rtpo.username', '=', 'users.username')
    ->select('*')
    ->where('users.username','=',$user_id)
    ->where('mbp.status','=','UNAVAILABLE')
    ->get();


    // $result = json_decode($check_mbp_unavailable, true);
    // $mbp_actived = null;
    // if ($result!=null) {
    //   foreach ($result as $param => $row) {
    //     $checkingController = new CheckingController;
    //     $mbp_actived = $checkingController->CheckActiveMbp($row['active_at'], $row['mbp_id'], $row['status']);
    //   }
    // }

    $data['site_down'] = $count_data_site_down;
    $data['sos_data'] = $count_data_sos;
    $data['submission_data'] = $count_submission_data;
    $data['count_notif_data'] = $count_notif_data;
    $data['check_mbp_unavailable'] = $check_mbp_unavailable;

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return $res;
  }
  public function getNotificationHomeMBP($mbp_id, $user_id){

    // $mbp_id = $request->input('mbp_id');
    // $user_id = $request->input('user_id');

    # 1. notifikasi di sedang di tugaskan
    //cek site yang mainfail di rtpo dia

    # 2. notifikasi umum 
    //cek notifikasi umum mbp dimana isinya dia sedang ditugaskan dan pengajuan yang telah di setujui dan dia sedang dipinjamkan ke rtpo lain

    // date_default_timezone_set("Asia/Jakarta");
    // $date_now = date('Y-m-d H:i:s');    

    // $updateUnavailableMbp = DB::table('mbp')
    // ->where('status','=','UNAVAILABLE')
    // ->where('active_at','<',$date_now)
    // ->update(
    //   [
    //     'status' => 'AVAILABLE',
    //     'submission' => null,
    //     'submission_id' => null,
    //     'message_id' => null,
    //     'active_at' => null,
    //   ]
    // );


    $count_notif_data = DB::table('user_notification')
    ->join('notification', 'user_notification.notification_id', '=', 'notification.id')
    ->where('user_notification.user_id','=',$user_id)
    ->where('user_notification.read_status','=','UNREAD')
    ->orderBy('notification.id', 'desc')
    ->limit(15)
    ->count();

    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    // ->select('mbp.status')
    ->where('users.username','=',$user_id)
    ->where('mbp.status','!=','AVAILABLE')
    ->where('mbp.status','!=','UNAVAILABLE')
    ->count();

    $count_unavailable_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->where('users.username','=',$user_id)
    ->where('mbp.status','=','UNAVAILABLE')
    ->count();

    $check_mbp_unavailable = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('*')
    ->where('users.username','=',$user_id)
    ->where('mbp.status','=','UNAVAILABLE')
    ->get();

    $list_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('*')
    ->where('users.username','=',$user_id)
    ->get();

    $result = json_decode($list_mbp, true);
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


    // $result = json_decode($check_mbp_unavailable, true);
    // $mbp_actived = null;
    // if ($result!=null) {
    //   foreach ($result as $param => $row) {
    //     $checkingController = new CheckingController;
    //     $mbp_actived = $checkingController->CheckActiveMbp($row['active_at'], $row['mbp_id'], $row['status']);
    //   }
    // }

    $data['count_notif_data'] = $count_notif_data;
    $data['assignment_data'] = $mbp_data;
    $data['your_mbp_name'] = $tmp_list_mbp;
    $data['check_mbp_unavailable'] = $check_mbp_unavailable;

    $data['count_unavailable_data'] = @$count_unavailable_data;

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return $res;
  }
  public function getNotificationHome(Request $request){


    $user_id = $request->input('username');

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');    

    $updateUnavailableMbp = DB::table('mbp')
    ->where('status','=','UNAVAILABLE')
    ->where('active_at','<',$date_now)
    ->update(
      [
        'status' => 'AVAILABLE',
        'submission' => null,
        'submission_id' => null,
        'message_id' => null,
        'active_at' => null,
      ]
    );

    
    # 1. cek apakah user adalah mbp atau rtpo
      #lalu ambil id mbp atau rtponya.

    $check_type = DB::table('users')
    ->select('*')
    ->where('username','=',$user_id)
    ->first();

    $user_type = $check_type->user_type;
    // return response($user_type);
        # 2. bila dia RTPO maka berikan notifikasi rtpo
        # 3. bila dia MBP maka berikan dia notifikasi MBP
    switch ($user_type) {
      case "MBP":
      // echo "Your favorite color is red!";
      $check_type_id = DB::table('user_mbp')
      ->select('*', 'username as user_id')
      ->where('username','=',$user_id)
      ->first();
      $notif = $this->getNotificationHomeMBP($check_type_id->mbp_id, $user_id);
      return response($notif);
      break;
      case "RTPO":
      // echo "Your favorite color is blue!";
      $check_type_id = DB::table('user_rtpo')
      ->select('*', 'username as user_id')
      ->where('username','=',$user_id)
      ->first();
      $notif = $this->getNotificationHomeRTPO($check_type_id->rtpo_id, $user_id);
      return response($notif);
      break;
      default:
      // echo "USER_TYPE_NOT_VALID";
      return response('USER_TYPE_NOT_VALID');
    }

    // jangan lupa return response($notif)
  }


  public function setNotificationV1($send_by, $send_to, $type, $name_type_id, $type_id, $title, $subject, $text, $category_id, $category){
  // public function setNotificationV1(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    // $send_by = $request->input('send_by'); //username
    // $send_to = $request->input('send_to');
    // $type = $request->input('type');
    // $name_type_id = $request->input('name_type_id');
    // $type_id = $request->input('type_id');
    // $title = $request->input('title');
    // $subject = $request->input('subject');
    // $text = $request->input('text');
    

    $date = $date_now;
    $last_update = $date_now;

    $rtpo_id = null;
    $rtpo_name = null; 

    $fmc_id = null;
    $fmc = null; 


    $user_data_send_to = DB::table('users')
    ->select('*')
    ->where('username','=',$send_to)
    ->first();
    if ($user_data_send_to == null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_USER_DATA_SEND_TO_NOT_FOUND';
      return response($res);    
    }

    $user_data = DB::table('users')
    ->select('*')
    ->where('username','=',$send_by)
    ->first();

    if ($user_data == null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_USER_DATA_NOT_FOUND';
      return response($res);    
    }
    $arr_roles = explode (",",$user_data->roles_id);

    $tmp_roles = null;
    foreach ($arr_roles as $value) {

      if ($value==4) { //rtpo

        $user_rtpo_data = DB::table('user_rtpo')
        ->select('*')
        ->where('username','=',$send_by)
        ->first();

        if ($user_rtpo_data == null) {
        }else{
          $rtpo_id = $user_rtpo_data->rtpo_id;
          $rtpo_name = $user_rtpo_data->rtpo_name; 
        }

      }elseif ($value==6) {
        # code... get fmc_id dan fmc
        $user_fmc_data = DB::table('user_fmc')
        ->select('*')
        ->where('fmc_cn','=',$send_by)
        ->first();

        if ($user_fmc_data == null) {
        }else{
          $fmc_id = $user_fmc_data->fmc_id;
          $fmc = $user_fmc_data->fmc; 
        }

      }elseif ($value==7) {
        # code... get fmc_id dan fmc
        $user_mbp_mt_data = DB::table('user_mbp_mt')
        ->select('*')
        ->where('mbp_mt_cn','=',$send_by)
        ->first();

        if ($user_mbp_mt_data == null) {
        }else{
          $fmc_id = $user_mbp_mt_data->fmc_id;
          $fmc = $user_mbp_mt_data->fmc; 
        }
      }elseif ($value==8) {
        # code... get fmc_id dan fmc
        $user_mbp_data = DB::table('user_mbp')
        ->select('*')
        ->where('mbp_mt_cn','=',$send_by)
        ->first();

        if ($user_mbp_data == null) {
        }else{
          $fmc_id = $user_mbp_data->fmc_id;
          $fmc = $user_mbp_data->fmc; 
        }
      }

    }
    #1 variabel" masukan ke tabel notifikasi 
    //insert ke tabel notifikasi
    $insertNotification = DB::table('notification')->insert(
      [
        'type' => $type, 
        'send_by' => $send_by,
        'send_to' => $send_to,
        'name_type_id' => $name_type_id,
        'type_id' => $type_id,
        'tittle' => $title,
        'subject' => $subject,
        'text' => $text,
        'date' => $date,
        'category_id' => $category_id,
        'category' => $category,
      ]
    );
    // if insert notification complated then
    if ($insertNotification) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date)
      ->first();

      if ($notif_data!=null) {
        $insertUN = DB::table('user_notification')->insert(
          [
            'notification_id' => $notif_data->id, 
            'user_id' => $send_to,
            'read_status' => 'UNREAD',
          ]
        );
      }
    }
    #2 kirimkan ke tabel user_notifikasi ke setiap user yang bersangkutan dengan notifikasi tersebut


    // $tmp = $this->sendNotifFast($title,$text,$firebase_token,$name_type_id,$type_id,$type);
    #3 panggil fungsi notif firrebase agar dapat mengirimkan notif ke setiap client device.

    $data['send_by'] = $send_by;

    $data['roles'] = $user_data->roles_id;

    $data['rtpo_id'] = $rtpo_id;
    $data['rtpo_name'] = $rtpo_name;
    $data['fmc_id'] = $fmc_id;
    $data['fmc'] = $fmc;

    $data['send_to'] = $send_to;
    $data['type'] = $type;
    $data['name_type_id'] = $name_type_id;
    $data['type_id'] = $type_id;
    $data['title'] = $title;
    $data['subject'] = $subject;
    $data['text'] = $text;
    $data['date'] = $date;
    $data['last_update'] = $last_update;

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);  

  }
  public function sendNotifFast($title,$body,$to_token_id,$type_name, $type_id,$type){


    $title = $title;
    $body = $body;
    $to_token_id = $to_token_id;
    $type_name = $type_name;
    $type_id = $type_id;
    $type = $type;


    if (!defined('API_ACCESS_KEY')){
     define('API_ACCESS_KEY', 'AAAAo6mi6uY:APA91bF5Jrgp7pqCX40LO0WQb6v-eLKd5xIP0xjxivSdlpDg5_iOisegSNQR0GSYwmeICJnumEbckFR6RextiSTkhUA0xBKk-HfMMNzRAWmyXPZzi5FxJvaYescfgyD4s3YTUwB9X78o');
    }


   if ($to_token_id=='') {
      $to_token_id = 'frMgfkXK4KE:APA91bHK76rxHLyiIC2VUYcjJUAdxqJdYC2HoQqqwFxBJ6GiUN3b5BFkj9RYTaLZ9mQi8dYU4SwhEp_NAHwmGibH-3sGnA6pwi4_nSP5oUcDUeYshRYKwDPlvYZQ5MlsQ2aCmW7nS35W';
    }


    $msg = array
    (
      'Message'   => $body,
      'Title'   => $title,
      "$type_name"   => $type_id,
      'Type'   => $type,
    );

    if (strlen($to_token_id)>20) { 
      $getToken['id'] = $to_token_id;
      $registrationIds = array( $getToken['id'] );
      $fields = array
      (
        'registration_ids'  => $registrationIds,
        'data'      => $msg
      );
    }else{
      $fields = array
      (
        'to'  => $to_token_id,
        'data'      => $msg
      );
    }

    $headers = array
    (
      'Authorization: key=' . API_ACCESS_KEY,
      'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
    curl_setopt( $ch,CURLOPT_POST, true );
    curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
    // curl_setopt($ch, CURLOPT_TIMEOUT_MS, 800);


    $result = curl_exec($ch );
    curl_close( $ch );

    $data['data'] = json_decode($result, true);
    $data['token to'] = $to_token_id;
    return $data;
  }
  
  function checkMyRTPOtopic($rtpo_name){
      switch ($rtpo_name) {
        case "RTPO PROBOLINGGO":
        $myrtpo = 'RTPO_PROB';
        break;
        case "RTPO MALANG":
        $myrtpo = 'RTPO_MALANG';
        break;
        case "RTPO JEMBER":
        $myrtpo = 'RTPO_JEMBER';
        break;
        case "RTPO BANYUWANGI":
        $myrtpo = 'RTPO_BANYUWANGI';
        break;
        case "RTPO MADIUN":
        $myrtpo = 'RTPO_MADIUN';
        break;
        case "RTPO LAMONGAN":
        $myrtpo = 'RTPO_LAMONGAN';
        break;
        case "RTPO BANGKALAN":
        $myrtpo = 'RTPO_BANGKALAN';
        break;
        case "RTPO TULUNGAGUNG":
        $myrtpo = 'RTPO_TULUNGAGUNG';
        break;
        case "RTPO PASURUAN":
        $myrtpo = 'RTPO_PASURUAN';
        break;
        case "RTPO PONOROGO":
        $myrtpo = 'RTPO_PONOROGO';
        break;
        case "RTPO SIDOARJO":
        $myrtpo = 'RTPO_SIDOARJO';
        break;
        case "RTPO SURABAYA SELATAN":
        $myrtpo = 'RTPO_SURABAYA_SELATAN';
        break;
        case "RTPO SURABAYA PUSUTA":
        $myrtpo = 'RTPO_SURABAYA_PUSUTA';
        break;
        case "RTPO SURABAYA BARAT":
        $myrtpo = 'RTPO_SURABAYA_BARAT';
        break;
        case "RTPO SURABAYA TIMUR":
        $myrtpo = 'RTPO_SURABAYA_TIMUR';
        break;
        case "RTPO KEDIRI":
        $myrtpo = 'RTPO_KEDIRI';
        break;
        case "RTPO PAMEKASAN":
        $myrtpo = 'RTPO_PAMEKASAN';
        break;
        default:
        // $myrtpo = null;
        // $fmc_data = DB::table('fmc')
        // ->select('*')
        // ->where('fmc_id','=',$fmc_id)
        // ->first();
        // $myfmc = @$fmc_data->fmc_alias.'_'.@$fmc_data->regional;
        $myrtpo = str_replace(' ', '_', $rtpo_name);
        break;
      }
      return($myrtpo);
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
    }
    return($myfmc);
  }

  function checkMyClusterFMCtopic($fmc_id,$cluster,$role_code){
    
    $fmc_topic = DB::table('firebase_topic')
    ->where('fmc_id','=',$fmc_id)
    ->where('cluster','=',$cluster)
    ->where('role_code','=',$role_code)
    ->first();

    return(@$fmc_topic->topic);
  }


  public function getReportLocationSiteCount(Request $request){

    $rtpo_id = $request->input('rtpo_id');

    $report_location_site_data = DB::table('report_location_site')
    ->where('rtpo_id','=',$rtpo_id)
    // ->where('approval','!=','1')
    ->where('approval','>',1)
    ->count();



    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $report_location_site_data ;
    return response($res);

  }

  // public function getActiveCorrectiveCount(Request $request){

  //   $rtpo_id = $request->input('rtpo_id');

  //   $corrective_data = DB::table('app_corrective')
  //   ->where('rtpo_id','=',$rtpo_id)
  //   ->where('end_status','=',0)
  //   ->count();



  //   $res['success'] = true;
  //   $res['message'] = 'SUCCESS';
  //   $res['data'] = $corrective_data ;
  //   return response($res);

  // }


  public function getActiveCorrectiveCount(Request $request){


    $type_id = $request->input('type_id'); //FMC / RTPO
    $id = $request->input('id');

    if ($type_id =='FMC') {
      $corrective_data = DB::table('app_corrective')
      ->where('fmc_id','=',$id)
      ->where('end_status','=',0)
      ->count();
    }elseif ($type_id =='RTPO') {
      $corrective_data = DB::table('app_corrective')
      ->where('rtpo_id','=',$id)
      ->where('end_status','=',0)
      ->count();
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_TYPEID_NOT_FOUND';
      // $res['data'] = $corrective_data ;
      return response($res);
    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $corrective_data ;
    return response($res);

  }

  public function getListNotificationPaginate(Request $request){

    $user_id = $request->input('user_id');
    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $notif_data = DB::table('user_notification')
    ->join('notification', 'user_notification.notification_id', '=', 'notification.id')
    ->select('*')
    ->where('user_notification.user_id','=',$user_id)
    ->where('user_notification.read_status','=','UNREAD')
    ->orderBy('notification.id', 'desc')
    ->offset($offset)
    ->limit($limit)
    // ->offset(3)
    ->get();

    $result = json_decode($notif_data, true);
    if ($result==NULL) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $notif_data;
      // $res['data_real'] =  $notif_data;
      return response($res);
    }
    foreach ($result as $param => $row) { 
      $data[$param]['id'] = $row['id'];
      $data[$param]['text'] = $row['text'].'';
      $data[$param]['date'] = $this->setDatedMYHis($row['date'].'');
      $data[$param]['category_id'] = $row['category_id'];
    }

    // array_multisort($data, SORT_DESC);

    if ($notif_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] =  $data;
      return response($res);

    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA';
      return response($res);
    }
  }

}