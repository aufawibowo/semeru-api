<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class NotificationController extends Controller
{

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

      // $mbp_name = $request->input('mbp_name');
      // $site_name = $request->input('site_name');
      // $mbp_id = $request->input('mbp_id');
      $tmp = $this->setNotificationMbpAssignment($mbp_name,$site_name,$mbp_id);
      return $tmp;

      break;
      case "MBP_STATUS_TO_SITE":
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

// 1
  public function setNotificationMbpAssignment($mbp_name,$site_name,$mbp_id){

    // $mbp_name=$mbp_result[0]['mbp_name'].'';
    // $site_name=$site_result[0]['site_name'].''
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_ASSiGNMENT_TO_SITE', 
        'type_id' => $mbp_id,
        'tittle' => 'Penugasan '.$mbp_name.'',
        'subject' => 'REQUEST MBP TO SITE DOWN',
        'text' => 'Penugasan '.$mbp_name.' menuju '.$site_name,
        'date' => $date_now,
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_ASSiGNMENT_TO_SITE')
      ->where('text','=','Penugasan '.$mbp_name.' menuju '.$site_name)
      ->first();

      if ($notif_data) {

      # menentukan siapa saja yang akan dikirimi notifikasi
      // cari setiap user pada rtpo_sendiri
      // get semua 
      // dan insert ke tabel user_notification user_id + notification_id dengan perulangan

        $mbp_data = DB::table('mbp')
        ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->select('*')
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

          if ($insertUN) {

            $rtpo_id = $mbp_data->rtpo_id;
            $rtpo_data = DB::table('user_rtpo')
            ->select('*')
            ->where('rtpo_id','=',$rtpo_id)
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
                # code...
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

// 2
  public function setNotificationMbpStatusWork($mbp_name,$site_name,$mbp_id, $mbp_status,$rtpo_id){

    // $mbp_name=$mbp_result[0]['mbp_name'].'';
    // $site_name=$site_result[0]['site_name'].''
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_STATUS_TO_SITE', 
        'type_id' => $mbp_id,
        'tittle' => 'Status '.$mbp_name.' menjadi '.$mbp_status,
        'subject' => 'STATUS MBP TO '.$mbp_status,
        'text' => 'Status penugasan '.$mbp_name.' menuju '.$site_name.' menjadi '.$mbp_status,
        'date' => $date_now,
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_STATUS_TO_SITE')
      ->where('text','=','Status penugasan '.$mbp_name.' menuju '.$site_name.' menjadi '.$mbp_status)
      ->first();

      if ($notif_data) {

        $rtpo_data = DB::table('user_rtpo')
        ->select('*')
        ->where('rtpo_id','=',$rtpo_id)
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
                # code...
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

// 3
  public function setNotificationMbpSubmission($mbp_name,$site_name,$mbp_id, $mbp_status,$rtpo_id, $message_id, $submission_type){

    // $mbp_name=$mbp_result[0]['mbp_name'].'';
    // $site_name=$site_result[0]['site_name'].''
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    $insertSP = DB::table('notification')->insert(
      [
        'type' => 'MBP_SUBMISSION', 
        'type_id' => $message_id,
        'tittle' => $mbp_name.' mengajukan '.$submission_type,
        'subject' => 'STATUS MBP TO '.$mbp_status,
        'text' => 'Status penugasan '.$mbp_name.' menuju '.$site_name.' menjadi '.$mbp_status,
        'date' => $date_now,
      ]
    );

    if ($insertSP) {
      $notif_data = DB::table('notification')
      ->select('*')
      ->where('date','=',$date_now)
      ->where('type','=','MBP_SUBMISSION')
      ->where('text','=','Status penugasan '.$mbp_name.' menuju '.$site_name.' menjadi '.$mbp_status)
      ->first();

      if ($notif_data) {

        $rtpo_data = DB::table('user_rtpo')
        ->select('*')
        ->where('rtpo_id','=',$rtpo_id)
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
                # code...
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

  public function getListNotification(Request $request){


    $user_id = $request->input('user_id');

    $notif_data = DB::table('user_notification')
    ->join('notification', 'user_notification.notification_id', '=', 'notification.id')
    ->select('notification.id','notification.type','notification.type_id','notification.tittle as title','notification.text','notification.date')
    ->where('user_notification.user_id','=',$user_id)
    ->where('user_notification.read_status','=','UNREAD')
    ->get();

    $result = json_decode($notif_data, true);

    foreach ($result as $param => $row) {

      $data[$param]['id']       = $row['id'];
      $data[$param]['type']     = $row['type'];
      $data[$param]['type_id']  = $row['type_id'].'';
      $data[$param]['title']    = $row['title'].'';
      $data[$param]['text']     = $row['text'].'';
      $data[$param]['date']     = $this->setDatedMYHis($row['date'].'');
    }

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
        return date("d-M-Y H:i:s", strtotime($date.''));
        // return strtotime($date.'');
      }
    }
}