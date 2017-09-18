<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MbpController extends Controller
{


  public function getAllMbp(Request $request){
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    // ->where('rtpo_id','=',$rtpo_id)
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function getStatusMbp(Request $request){
    $mbp_id = $request->input('mbp_id');

    // cek status mbp
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.status','user_mbp.user_id','mbp.status','mbp.submission_id','mbp.submission')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();


    if ($mbp_data) {

      if($mbp_data->submission!=null){
        $status = $mbp_data->submission.'';
      }else{
        $status = $mbp_data->status.'';

      }

      switch ($status) {
        case "AVAILABLE":
        // echo "Your favorite color is red!";
        $data['status'] = $mbp_data->status;
        $data['user_id'] = $mbp_data->user_id;
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);

        break;
        case "UNAVAILABLE":
        // echo "Your favorite color is blue!";
        $data['status'] = $mbp_data->status;
        $data['user_id'] = $mbp_data->user_id;
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);

        break;
        case "DELAY":
        // echo "Your favorite color is green!";
        $updateDelay = $this->getStatusWithSubmission($mbp_id, $mbp_data->submission_id,$status);
        return response($updateDelay);

        break;
        case "CANCEL":
        // echo "Your favorite color is green!";
        $updateCancel = $this->getStatusWithSubmission($mbp_id, $mbp_data->submission_id,$status);
        return response($updateCancel);

        break;
        default:
        // echo "Your favorite color is neither red, blue, nor green!";

        // return response($status);
        $updateCancel = $this->getStatusWithSubmission($mbp_id, $mbp_data->submission_id,$status);
        return response($updateCancel);
        break;

      }

    }else{

      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA_MBP';
      return response($res);
    }
  }
  public function getStatusWithSubmission($mbp_id, $cancel_id, $status){

    if ($status=='DELAY') {
      // get data dari cancel_id
      $data_mbp_task = DB::table('supplying_power')
      ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
      ->join('users', 'supplying_power.user_id', '=', 'users.id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('class', 'site.class_id', '=', 'class.class_id')
      ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id')

      ->where('supplying_power.finish','=', NULL)
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      if ($data_mbp_task) {

        $result['status'] = $data_mbp_task->status;
        $result['rtpo_username'] = $data_mbp_task->rtpo_username;
        $result['site_name'] = $data_mbp_task->site_name;
        $result['latitude'] = $data_mbp_task->latitude;
        $result['longitude'] = $data_mbp_task->longitude;
        $result['class_name'] = $data_mbp_task->class_name;
        $result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
        $result['mbp_longitude'] = $data_mbp_task->mbp_longitude;

        $CancellationLetter_data = DB::table('cancel_details')
        ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
        ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
        ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
        ->join('message', 'cancel_details.message_id', '=', 'message.id')
        ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.text_message','message.subject','cancel_details.date','cancel_details.available_status','cancel_details.response_status')
        ->where('cancel_details.id','=',$cancel_id)
        ->where('mbp.submission','!=',null)
        ->first();   

        if ($CancellationLetter_data!=null) {

          $result['submission_status'] = 'FOUND';
          $result['cancel_id'] = $CancellationLetter_data->id;
          $result['message_id'] = $CancellationLetter_data->message_id;
          $result['subject'] = $CancellationLetter_data->subject;
          $result['text_message'] = $CancellationLetter_data->text_message;
          $result['cancel_date'] = $CancellationLetter_data->date;
          $result['available_status'] = $CancellationLetter_data->available_status;

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $result;

          return $res;
        }else{

          $result['submission_status'] = 'NOT_FOUND';
          $result['cancel_id'] = '';
          $result['message_id'] = '';
          $result['subject'] = '';
          $result['text_message'] = '';
          $result['cancel_date'] = '';
          $result['available_status'] = '';

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $result;

          return $res;
        }
      }

    }else if($status=='CANCEL'){
      $data_mbp_task = DB::table('supplying_power')
      ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
      ->join('users', 'supplying_power.user_id', '=', 'users.id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('class', 'site.class_id', '=', 'class.class_id')
      ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id')

      ->where('supplying_power.finish','=', NULL)
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      if ($data_mbp_task) {

        $result['status'] = $data_mbp_task->status;
        $result['rtpo_username'] = $data_mbp_task->rtpo_username;
        $result['site_name'] = $data_mbp_task->site_name;
        $result['latitude'] = $data_mbp_task->latitude;
        $result['longitude'] = $data_mbp_task->longitude;
        $result['class_name'] = $data_mbp_task->class_name;
        $result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
        $result['mbp_longitude'] = $data_mbp_task->mbp_longitude;

        $CancellationLetter_data = DB::table('cancel_details')
        ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
        ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
        ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
        ->join('message', 'cancel_details.message_id', '=', 'message.id')
        ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.text_message','message.subject','cancel_details.date','cancel_details.available_status','cancel_details.response_status')
        ->where('cancel_details.id','=',$cancel_id)
        ->where('cancel_details.response_status','=',null)
        ->first();   

        if ($CancellationLetter_data!=null) {

          $result['submission_status'] = 'FOUND';
          $result['cancel_id'] = $CancellationLetter_data->id;
          $result['message_id'] = $CancellationLetter_data->message_id;
          $result['subject'] = $CancellationLetter_data->subject;
          $result['text_message'] = $CancellationLetter_data->text_message;
          $result['cancel_date'] = $CancellationLetter_data->date;
          $result['available_status'] = $CancellationLetter_data->available_status;

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $result;

          return $res;
        }else{

          $result['submission_status'] = 'NOT_FOUND';
          $result['cancel_id'] = '';
          $result['message_id'] = '';
          $result['subject'] = '';
          $result['text_message'] = '';
          $result['cancel_date'] = '';
          $result['available_status'] = '';

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $result;

          return $res;
        }
      }
    }else{
      $data_mbp_task = DB::table('supplying_power')
      ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
      ->join('users', 'supplying_power.user_id', '=', 'users.id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('class', 'site.class_id', '=', 'class.class_id')
      ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id')

      ->where('supplying_power.finish','=', NULL)
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      if ($data_mbp_task) {

        $result['status'] = $data_mbp_task->status;
        $result['rtpo_username'] = $data_mbp_task->rtpo_username;
        $result['site_name'] = $data_mbp_task->site_name;
        $result['latitude'] = $data_mbp_task->latitude;
        $result['longitude'] = $data_mbp_task->longitude;
        $result['class_name'] = $data_mbp_task->class_name;
        $result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
        $result['mbp_longitude'] = $data_mbp_task->mbp_longitude;
        $result['submission_status'] = 'NOT_FOUND';
        $result['cancel_id'] = '';
        $result['message_id'] = '';
        $result['subject'] = '';
        $result['text_message'] = '';
        $result['cancel_date'] = '';
        $result['available_status'] = '';

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $result;

        return $res;
      }
    }
  }
  public function updateStatusMbptoOnProgress(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $mbp_id = $request->input('mbp_id');

    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_id', $mbp_id)
    ->update(['status' => 'ON_PROGRESS']);

    if ($editMbp) {


      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.status')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      // fungsi create new suppliyinf power
      $insertSP = DB::table('supplying_power')
      ->where('mbp_id', $mbp_id)
      ->where('finish', NULL)
      ->update(
        [
          'date_onprogress' => date('Y-m-d H:i:s'),
        ]
      );

      if ($mbp_data && $insertSP) {

        if ($mbp_data->status!='AVAILABLE') {

          $data_mbp_task = DB::table('supplying_power')
          ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
          ->join('users', 'supplying_power.user_id', '=', 'users.id')
          ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
          ->join('class', 'site.class_id', '=', 'class.class_id')

          ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name')

          ->where('finish', NULL)
          ->where('supplying_power.mbp_id', $mbp_id)
          ->first();

          if ($data_mbp_task) {

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['data'] = $data_mbp_task;

            return response($res);
          }else{
            $res['success'] = false;
            $res['message'] = 'CANNOT_FIND_DATA';

            return response($res);
          }  

        }else{
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $mbp_data;

          return response($res);
        }


      }else{
        $res['success'] = false;
        $res['message'] = 'CANNOT_FIND_DATA';

        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }
  public function updateStatusMbp(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $mbp_id = $request->input('mbp_id');
    $status = $request->input('status');

    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_id', $mbp_id)
    ->update(['status' => $status]);

    if ($editMbp) {


      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.status','user_mbp.user_id')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      if ($status=='ON_PROGRESS') {

        $insertSP = DB::table('supplying_power')
        ->where('mbp_id', $mbp_id)
        ->where('finish', NULL)
        ->update(
          [
            'date_onprogress' => date('Y-m-d H:i:s'),
          ]
        );

      }else if ($status=='CHECK_IN') {

        // isi date checkinnya
        $insertSP = DB::table('supplying_power')
        ->where('mbp_id', $mbp_id)
        ->where('finish', NULL)
        ->update(
          [
            'date_checkin' => date('Y-m-d H:i:s'),
          ]
        );

      }else if($status=='AVAILABLE'){
        $insertSP = DB::table('supplying_power')
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id') 
        ->where('supplying_power.mbp_id', $mbp_id)
        ->where('supplying_power.finish', NULL)
        ->update(
          [
            'supplying_power.date_finish' => date('Y-m-d H:i:s'),
            'supplying_power.finish' =>'DONE',
            'site.is_allocated' =>'0',
          ]
        );
      }else{

        $res['success'] = false;
        $res['message'] = 'STATUS_NOT_MATCH';

        return response($res);
      }

      if ($mbp_data && $insertSP) {

        if ($mbp_data->status!='AVAILABLE') {

          $data_mbp_task = DB::table('supplying_power')
          ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
          ->join('users', 'supplying_power.user_id', '=', 'users.id')
          ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
          ->join('class', 'site.class_id', '=', 'class.class_id')

          ->select('mbp.status','mbp.submission_id','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude','users.id as user_id', 'mbp.mbp_id', 'mbp.mbp_name')

          ->where('supplying_power.finish', NULL)
          ->where('supplying_power.mbp_id', $mbp_id)
          ->first();

          if ($data_mbp_task) {


            $result['status'] = $data_mbp_task->status;
            $result['rtpo_username'] = $data_mbp_task->rtpo_username;
            $result['site_name'] = $data_mbp_task->site_name;
            $result['latitude'] = $data_mbp_task->latitude;
            $result['longitude'] = $data_mbp_task->longitude;
            $result['class_name'] = $data_mbp_task->class_name;
            $result['mbp_latitude'] = $data_mbp_task->mbp_latitude;
            $result['mbp_longitude'] = $data_mbp_task->mbp_longitude;

            $CancellationLetter_data = DB::table('cancel_details')
            ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
            ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
            ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
            ->join('message', 'cancel_details.message_id', '=', 'message.id')
            ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.text_message','message.subject','cancel_details.date','cancel_details.available_status')

            ->where('cancel_details.id','=',$data_mbp_task->submission_id)
            ->where('cancel_details.response_status','=',null)
            ->first();

            $fireBaseControlle = new FireBaseController;
            $body = 'Status '.$data_mbp_task->mbp_name.' menuju site '.$data_mbp_task->site_name.' adalah '.$data_mbp_task->status.'';
            $tittle = 'Status Penugasan terbaru '.$data_mbp_task->mbp_name.'';
            $datax =$fireBaseControlle->sendNotification($tittle, $body);



            if ($CancellationLetter_data!=null) {

              $result['submission_status'] = 'FOUND';
              $result['cancel_id'] = $CancellationLetter_data->id;
              $result['message_id'] = $CancellationLetter_data->message_id;
              $result['subject'] = $CancellationLetter_data->subject;
              $result['text_message'] = $CancellationLetter_data->text_message;
              $result['cancel_date'] = $CancellationLetter_data->date;
              $result['available_status'] = $CancellationLetter_data->available_status;

            // available_status

              $res['success'] = true;
              $res['message'] = 'SUCCESS';
              $res['data'] = $result;

              return response($res);
            }else{

              $result['submission_status'] = 'NOT_FOUND';
              $result['cancel_id'] = '';
              $result['message_id'] = '';
              $result['subject'] = '';
              $result['text_message'] = '';
              $result['cancel_date'] = '';
              $result['available_status'] = '';

              $res['success'] = true;
              $res['message'] = 'SUCCESS';
              $res['data'] = $result;

              return response($res);
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'CANNOT_FIND_DATA';
          // $res['data'] = $data_mbp_task;

            return response($res);
          }  

        }else{
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $mbp_data;

          return response($res);
        }


      }else{
        $res['success'] = false;
        $res['message'] = 'CANNOT_FIND_DATA';

        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }
  public function updateStatusMbptoCheckin(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $mbp_id = $request->input('mbp_id');

    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_id', $mbp_id)
    ->update(['status' => 'CHECK_IN']);

    if ($editMbp) {

      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.status','user_mbp.user_id')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();
      // fungsi create new suppliyinf power
      $insertSP = DB::table('supplying_power')
      ->where('mbp_id', $mbp_id)
      ->where('finish', NULL)
      ->update(
        [
          'date_checkin' => date('Y-m-d H:i:s'),
        ]
      );


      if ($mbp_data && $insertSP) {

        if ($mbp_data->status!='AVAILABLE') {

          $data_mbp_task = DB::table('supplying_power')
          ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
          ->join('users', 'supplying_power.user_id', '=', 'users.id')
          ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
          ->join('class', 'site.class_id', '=', 'class.class_id')

          ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name')

          ->where('supplying_power.finish', NULL)
          ->where('supplying_power.mbp_id', $mbp_id)
          ->first();

          if ($data_mbp_task) {

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['data'] = $data_mbp_task;

            return response($res);
          }else{
            $res['success'] = false;
            $res['message'] = 'CANNOT_FIND_DATA';

            return response($res);
          }  

        }else{
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $mbp_data;

          return response($res);
        }


      }else{
        $res['success'] = false;
        $res['message'] = 'CANNOT_FIND_DATA';

        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }
  public function updateStatusMbptoDone(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $mbp_id = $request->input('mbp_id');

    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_id', $mbp_id)
    ->update(['status' => 'AVAILABLE']);

    if ($editMbp) {

      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.status','user_mbp.user_id')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();
  // fungsi create new suppliyinf power
      $insertSP = DB::table('supplying_power')
      ->where('mbp_id', $mbp_id)
      ->where('finish', NULL)
      ->update(
        [
          'date_finish' => date('Y-m-d H:i:s'),
          'finish' =>'DONE',
        ]
      );


      if ($mbp_data) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_data;

        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'CANNOT_FIND_DATA';

        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }
  public function getAllMbpOnProggress(Request $request){
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('status','=','1')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function getMyMbp(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function getMyMbpCategory(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    // $mbp_data = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id);
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();


    // $data_onprogress = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON_PROGRESS')->get();
    $data_onprogress = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','ON_PROGRESS')
    ->get();

    // $data_waiting = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
    $data_waiting = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','WAITING')
    ->get();

    // $data_available = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
    $data_available = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','AVAILABLE')
    ->get();

    // $data_onprogress->where('status','=','ON_PROGRESS')->get();
    // $data_waiting->where('status','=','WAITING')->get();
    // $data_available->where('status','=','AVAILABLE')->get();

    $data['ON_PROGRESS'] = $data_onprogress;
    $data['WAITING'] = $data_waiting;
    $data['AVAILABLE'] = $data_available;


    if ($mbp_data) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function getMyMbpOnProgress(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON_PROGRESS')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','ON_PROGRESS')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function getMyMbpAvailable(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','AVAILABLE')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function getMyMbpWaiting(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','WAITING')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }
  public function updateLatLongMbp(Request $request){

    $mbp_name = $request->input('mbp_name');
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_name', $mbp_name)
    ->update(
      [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ]
    );

    if ($editMbp) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] = $editMbp;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }
// fungsi meleuhat -> merubah status mbp dari aktif ke not actif begitu juga sebaliknya..:D
  public function changeStatusActiveNotActive(Request $request){

    $set_tatus = $request->input('set_status');
    $mbp_id = $request->input('mbp_id');
    $text_message = $request->input('text_message');

    switch ($set_tatus) {
      case "ACTIVE":
        // echo "Your favorite color is red!";
    // set mbp jadi available
    // submission id dan submission di null
      $update_mbp = DB::table('mbp')
      ->where('mbp_id','=',$mbp_id)
      ->update(
        [
          'status' =>'AVAILABLE',
          'submission' =>null,
          'submission_id' =>null,
        ]
      );

      if ($update_mbp) {

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return $res;
      }else{
        $res['success'] = false;
        $res['message'] = 'UPDATE_MBP_TABLE_FAILED';
        return $res;
      }

      break;
      case "NOT_ACTIVE":
        // echo "Your favorite color is blue!";
    // . membuat pesan dulu sebagai alasan kenapa dia jadi unavailable
    // . lalu membuat pemeberitahuan di tabel cancel,
    // . setelah semua terbuat, maka status dia di set unavailable
      $act = $this->setStatustoUnavailable($mbp_id,$text_message);
      return response($act);


      break;
      default:

      $res['success'] = false;
      $res['message'] = 'STATUS_NOT_MATCH';
      return response($res);
      break;
        // echo "Your favorite color is neither red, blue, nor green!";
    }
  }
  public function setStatustoUnavailable($mbp_id,$text_message){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    // 1. cek, apakah status dia available?
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.user_id', '=', 'users.id')
    ->select('*')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->where('mbp.status','=','AVAILABLE')
    ->first();

    if ($mbp_data!=null) {

    // 2. bila ia maka mulai membuat pesan,
      $insertMessage = DB::table('message')->insert(
        [
          'subject' => 'MBP_INFORMATION_UNAVAILABLE', 
          'from' => $mbp_data->user_id,
          'text_message' => $text_message,
          'date_message' => $date_now,
        ]
      );

      if ($insertMessage) {
      // check apakah pesan yang sudah di buat sudah ada di dalam tabel?
      // bila ada maka lanjutkan ke pembuatan tabel cancel

        $message_data = DB::table('message')
        ->select('id')
        ->where('date_message','=',$date_now.'')
        ->where('from','=',$mbp_data->user_id.'')
        ->first();

        if ($message_data) {
        // 3. insert pesan tadi beserta rtpo tujuan ke table cancel,

          $insertInformationUnavailable = DB::table('cancel_details')
          ->insert(
            [
              'message_id' => $message_data->id, 
              'rtpo_id' => $mbp_data->rtpo_id,
              'user_id_mbp' => $mbp_data->user_id,
              'submission_type' => 'UNAVAILABLE',
              'available_status' => 'UNAVAILABLE',
            // 'sp_id' => $supplying_power_data->sp_id,
              'date' => $date_now,
            ]
          );

          if ($insertInformationUnavailable) {

            $InformationUnavailable = DB::table('cancel_details')
            ->select('id')
            ->where('message_id','=',$message_data->id.'')
            ->where('date','=',$date_now.'')
            ->where('submission_type','=','UNAVAILABLE')
            ->first();

            if ($InformationUnavailable) {
            // 4. setelah itu rubah status mbp menjadi unavailable + masukkan submission = unavailable dan submission id berupa cancel id yang di kirim ke rtpo

              $update_mbp = DB::table('mbp')
              ->where('mbp_id','=',$mbp_id)
              ->update(
                [
                  'status' =>'UNAVAILABLE',
                  'submission' =>'UNAVAILABLE',
                  'submission_id' =>$InformationUnavailable->id,
                ]
              );

              if ($update_mbp) {

                $res['success'] = true;
                $res['message'] = 'SUCCESS';
                return $res;
              }else{
                DB::table('cancel_details')->where('id','=',$InformationUnavailable->id)->delete();
                DB::table('message')->where('id','=',$message_data->id)->delete();
                $res['success'] = false;
                $res['message'] = 'UPDATE_MBP_TABLE_FAILED';
                return $res;
              }
            }else{
              DB::table('message')->where('id','=',$message_data->id)->delete();
              $res['success'] = false;
              $res['message'] = 'MESSAGE_TABLE_CANCEL_DATA_NOT_FOUND';
              return $res;            
            }
          }else{
            DB::table('message')->where('id','=',$message_data->id)->delete();
            $res['success'] = false;
            $res['message'] = 'INSERT_CANCEL_TABLE_FAILED';
            return $res;
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'MESSAGE_DATA_NOT_FOUND';
          return $res;
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'INSERT_MESSAGE_FAILED';
        return $res;
      }    
    }else{
      $res['success'] = false;
      $res['message'] = 'MBP_DATA_NOT_FOUND';
      return $res;
    }
  }
  public function getStatusActiveNotActive(Request $request){

    $mbp_id = $request->input('mbp_id');

    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp_id','=',$mbp_id)
    ->first();

    switch ($mbp_data->status) {
      case "UNAVAILABLE":
      $data['status'] = 'NOT_ACTIVE';
      $data['time'] = '05.59';

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);
      break;
      case "AVAILABLE":
      $data['status'] = 'ACTIVE';
      $data['time'] = '';

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;

      return response($res);
      break;
      default:
      $data['status'] = 'WORKING';
      $data['time'] = '';
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);
    }
  }
  public function getDetailMbp(Request $request){

    $mbp_id = $request->input('mbp_id');

    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.user_id', '=', 'users.id')
    // ->select(DB::raw('(case when (delay > "0") then "DELAY" else mbp.status end) as status'),'mbp.mbp_name','users.name','users.phone','mbp.latitude','mbp.longitude'/*,'mbp.mbp_name','mbp.mbp_name','mbp.mbp_name',*/)
    ->select(DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status'),'mbp.mbp_name','users.name','users.phone','mbp.latitude','mbp.longitude')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();

    if ($mbp_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $mbp_data;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }  
  }

}