<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class CancelController extends Controller
{
    /**
     * Get user by id
     *
     * URL /user/{id}
     */
    /*public function sendCancellationLetterToRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = $request->input('rtpo_id');       //1
      $user_id_mbp = $request->input('user_id');       //7
      $mbp_id = $request->input('mbp_id');       //7
      $text_message = $request->input('text_message');
      $available_status = $request->input('available_status');

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
      ->join('message', 'cancel_details.message_id', '=', 'message.id')// get subject
      ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.subject','cancel_details.date')
      // ->select('*')
      ->where('cancel_details.response_status','=',NULL)
      ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      ->first();

      if ($CancellationLetter_data==null) {
        $insertMessage = DB::table('message')->insert(
          [
              // $user_rtpo_data[$param]['id'].''
            'subject' => 'CANCEL', 
            'from' => $user_id_mbp,
              // 'to' => $user_rtpo_data[$param]->id,
            'text_message' => $text_message,
            'date_message' => $date_now,
          ]
        );

        if($insertMessage){

          $message_data = DB::table('message')
          ->select('id')
          // ->where('text_message','=',$text_message)
          ->where('date_message','=',$date_now.'')
          ->where('from','=',$user_id_mbp.'')
          ->first();

          if($message_data){
            // 2. cari data mbp_id dia di tabel sp yang belum finish
            $supplying_power_data = DB::table('supplying_power')
            ->select('sp_id')
            ->where('mbp_id','=',$mbp_id)
            ->where('finish','=',null)
            ->first();

            if ($supplying_power_data) {

      // 4. buat data di tabel detil_cancel tanpa isi 'user_id_rtpo'

              $insertCancellationLetter = DB::table('cancel_details')
              ->insert(
                [
          // `message_id`, `rtpo_id`, `user_id_mbp`, `sp_id`, `date`available_status
                  'message_id' => $message_data->id, 
                  'rtpo_id' => $rtpo_id,
                  'user_id_mbp' => $user_id_mbp,
                  'sp_id' => $supplying_power_data->sp_id,
                  'available_status' => $available_status,
                  'date' => date('Y-m-d H:i:s'),
                ]
              );


              $fireBaseControlle = new FireBaseController;
              $body = $CancellationLetter_data->mbp_name.' mengajukan pembatalan tugas';
              $tittle = 'Pengajuan Pembatalan';
              $datax =$fireBaseControlle->sendNotification($tittle, $body);

              if ($insertCancellationLetter) {
                $res['success'] = true;
                $res['message'] = 'SUCCESS';
        // $res['data'] = $insertCancellationLetter;

                return response($res);
              }else{
                $res['success'] = false;
                $res['message'] = 'FAILED_INSERT_CANCELLATION_LETTER';
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
            $res['message'] = 'MESSAGE_DATA_NOT_FOUND';

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
    }*/

    public function sendCancellationLetterToRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = $request->input('rtpo_id');       //1
      $user_id_mbp = $request->input('user_id');       //7
      $mbp_id = $request->input('mbp_id');       //7
      $text_message = $request->input('text_message');
      $available_status = $request->input('available_status');

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
      ->join('message', 'cancel_details.message_id', '=', 'message.id')// get subject
      ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.subject','cancel_details.date')
      // ->select('*')
      ->where('cancel_details.response_status','=',NULL)
      ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      ->first();

      if ($CancellationLetter_data==null) {
        $insertMessage = DB::table('message')->insert(
          [
              // $user_rtpo_data[$param]['id'].''
            'subject' => 'CANCEL', 
            'from' => $user_id_mbp,
              // 'to' => $user_rtpo_data[$param]->id,
            'text_message' => $text_message,
            'date_message' => $date_now,
          ]
        );

        if($insertMessage){

          $message_data = DB::table('message')
          ->select('id')
          ->where('date_message','=',$date_now.'')
          ->where('from','=',$user_id_mbp.'')
          ->first();

          if($message_data){
            // 2. cari data mbp_id dia di tabel sp yang belum finish
            $supplying_power_data = DB::table('supplying_power')
            ->select('sp_id')
            ->where('mbp_id','=',$mbp_id)
            ->where('finish','=',null)
            ->first();

            if ($supplying_power_data) {

      // 4. buat data di tabel detil_cancel tanpa isi 'user_id_rtpo'

              $insertCancellationLetter = DB::table('cancel_details')
              ->insert(
                [
          // `message_id`, `rtpo_id`, `user_id_mbp`, `sp_id`, `date`available_status
                  'message_id' => $message_data->id, 
                  'rtpo_id' => $rtpo_id,
                  'user_id_mbp' => $user_id_mbp,
                  'sp_id' => $supplying_power_data->sp_id,
                  'available_status' => $available_status,
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
                    'submission' => 'CANCEL',
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
            $res['message'] = 'MESSAGE_DATA_NOT_FOUND';
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
    public function sendDelayLetterToRtpo(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');


      $rtpo_id = $request->input('rtpo_id');       //1
      $user_id_mbp = $request->input('user_id');       //7
      $mbp_id = $request->input('mbp_id');       //7
      $text_message = $request->input('text_message');
      // $available_status = $request->input('available_status');

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
      ->join('message', 'cancel_details.message_id', '=', 'message.id')// get subject
      ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.subject','cancel_details.date')
      // ->select('*')
      ->where('cancel_details.response_status','=',NULL)
      ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      ->first();

      if ($CancellationLetter_data==null) {
        $insertMessage = DB::table('message')->insert(
          [
              // $user_rtpo_data[$param]['id'].''
            'subject' => 'DELAY', 
            'from' => $user_id_mbp,
              // 'to' => $user_rtpo_data[$param]->id,
            'text_message' => $text_message,
            'date_message' => $date_now,
          ]
        );

        if($insertMessage){

          $message_data = DB::table('message')
          ->select('id')
          ->where('date_message','=',$date_now.'')
          ->where('from','=',$user_id_mbp.'')
          ->first();

          if($message_data){
            // 2. cari data mbp_id dia di tabel sp yang belum finish
            $supplying_power_data = DB::table('supplying_power')
            ->select('sp_id')
            ->where('mbp_id','=',$mbp_id)
            ->where('finish','=',null)
            ->first();

            if ($supplying_power_data) {

      // 4. buat data di tabel detil_cancel tanpa isi 'user_id_rtpo'

              $insertCancellationLetter = DB::table('cancel_details')
              ->insert(
                [
          // `message_id`, `rtpo_id`, `user_id_mbp`, `sp_id`, `date`available_status
                  'message_id' => $message_data->id, 
                  'rtpo_id' => $rtpo_id,
                  'user_id_mbp' => $user_id_mbp,
                  'sp_id' => $supplying_power_data->sp_id,
                  // 'available_status' => $available_status,
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
            $res['message'] = 'MESSAGE_DATA_NOT_FOUND';
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
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
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

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
      ->join('message', 'cancel_details.message_id', '=', 'message.id')// get subject
      ->select('cancel_details.id as cancel_id','message.subject','message.text_message','mbp.mbp_id as mbp_id','mbp.mbp_name','users.name','message.id as message_id','cancel_details.date')
      // ->select('*')
      ->where('cancel_details.response_status','=',NULL)
      ->where('cancel_details.rtpo_id','=',$rtpo_id)
      ->get();

      if ($CancellationLetter_data) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $CancellationLetter_data;

        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_MESSAGE';
        
        return response($res);
      }
    }
    public function cancellationStatementRtpo(Request $request){

      $type_approval = $request->input('type_approval');

      if ($type_approval=='AGREE') {

        $user_id = $request->input('user_id');
        $cancel_id = $request->input('cancel_id');
        // $this->approvedTheCancellationLetter($user_id, $cancel_id);
        return response($this->approvedTheCancellationLetter($user_id, $cancel_id));

      }else if ($type_approval=='DISAGREE') {

        $cancel_id = $request->input('cancel_id');

        return response($this->didNotApproveOfTheCancellationLetter($cancel_id));
        // $this->didNotApproveOfTheCancellationLetter($cancel_id);

      }else{

        $res['success'] = false;
        $res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
        
        return response($res);
      }
    }
    public function delayStatementRtpo(Request $request){

      $type_approval = $request->input('type_approval');
      $user_id = $request->input('user_id');
      $cancel_id = $request->input('cancel_id');

      if ($type_approval=='AGREE') {

        // $this->approvedTheCancellationLetter($user_id, $cancel_id);
        return response($this->acceptDelayFromRTPO($user_id, $cancel_id));

      }else if ($type_approval=='CANCEL_TASK') {

        $cancel_id = $request->input('cancel_id');

        return response($this->approvedTheCancellationLetter($user_id, $cancel_id));
        // $this->didNotApproveOfTheCancellationLetter($cancel_id);

      }else{

        $res['success'] = false;
        $res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
        
        return response($res);
      }
    }
    public function approvedTheCancellationLetter($user_id, $cancel_id){

      // $user_id = $request->input('user_id');
      // $cancel_id = $request->input('cancel_id');

      $getTableCancel = $this->getfirstTableCancel($cancel_id);
      if ($getTableCancel) {
        $updateCancel = $this->acceptCancelFromRTPO($cancel_id, $user_id, $getTableCancel->available_status);
        if ($updateCancel=='OK') {
          $res['success'] = false;
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
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
      ->where('cancel_details.id', $cancel_id)
      ->where('cancel_details.user_id_rtpo', NULL)
      ->delete();

      $editMbp = DB::table('mbp')
      ->where('mbp.submission_id', $cancel_id)
      ->update(
        [
          'submission' => NULL,
          'submission_id' => NULL,
        ]
      ); 

      if ($updateCancellationLetter) {

        $fireBaseControlle = new FireBaseController;
        $body = 'Pengajuan Pembatalan anda tidak disetujui';
        $tittle = 'Pengajuan Pembatalan';
        $datax = $fireBaseControlle->sendNotification($tittle, $body);

        $res['success'] = false;
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
        $res['success'] = false;
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

      $updateCancel = $this->acceptDelayFromMbp($cancel_id, $user_id_rtpo);
      if ($updateCancel=='OK') {

        $fireBaseControlle = new FireBaseController;
        $body = 'Pengajuan telah selesai dan Mbp bisa lanjut bertugas';
        $tittle = 'Pengajuan Penundaan';
        $datax =$fireBaseControlle->sendNotification($tittle, $body);

        $res['success'] = false;
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
    public function acceptCancelFromRTPO($cancel_id, $user_id_rtpo, $available_status){

      date_default_timezone_set("Asia/Jakarta");

      $updateCancellationLetter = DB::table('cancel_details')
      ->join('supplying_power', 'cancel_details.sp_id', '=', 'supplying_power.sp_id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
      ->join('message', 'cancel_details.message_id', '=', 'message.id')
      ->where('cancel_details.id', $cancel_id)
      ->where('cancel_details.user_id_rtpo', NULL)
      ->update(
        [
          'cancel_details.user_id_rtpo' =>$user_id_rtpo,
          'cancel_details.response_status' =>'1',
          'cancel_details.user_id_responders' =>$user_id_rtpo,
          'supplying_power.finish' =>'CANCEL',
          'supplying_power.date_finish' =>date('Y-m-d H:i:s'),
          'mbp.status' =>$available_status,
          'mbp.submission' =>null,
          'mbp.submission_id' =>null,
          'site.is_allocated' =>'0',
        ]
      );

      if ($updateCancellationLetter) {

      //   $fireBaseControlle = new FireBaseController;
      //   $body = 'Penugasan Anda telah di batalkan';
      //   $tittle = 'Pengajuan Pembatalan';
      // $datax =$fireBaseControlle->sendNotification($tittle, $body/*,$array_tokenIDs*/);

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
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
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

        $fireBaseControlle = new FireBaseController;
        $body = 'Pengajuan Delay anda telah di setujui';
        $tittle = 'Pengajuan Delay';
        $datax =$fireBaseControlle->sendNotification($tittle, $body);
        return 'OK';
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_UPDATE_TABEL_CANCEL_DETAILS';

        return $res;
      }
    }
    public function acceptDelayFromMbp($cancel_id, $user_id_rtpo){

      date_default_timezone_set("Asia/Jakarta");

      $checkCancellationLetter = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')        //get name_mbp
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')            //get name
      ->where('cancel_details.id', $cancel_id)
      ->where('cancel_details.user_id_rtpo', NULL)
      ->first();

      if ($checkCancellationLetter!=null) {
        $updateCancellationLetter = DB::table('cancel_details')
        ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
        ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
        ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
        ->where('cancel_details.id', $cancel_id)
        ->where('cancel_details.user_id_rtpo', NULL)
        ->update(
          [
          // 'cancel_details.user_id_rtpo' =>$user_id_rtpo,
            'cancel_details.response_status' =>'1',
            'cancel_details.user_id_responders' =>$user_id_rtpo,
            'mbp.submission' =>null,
            'mbp.submission_id' =>null,
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
      }else{

        $updateCancellationLetter = DB::table('cancel_details')
        ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
        ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
        ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id')
        ->where('cancel_details.id', $cancel_id)
        // ->where('cancel_details.user_id_rtpo', NULL)
        ->update(
          [
          // 'cancel_details.user_id_rtpo' =>$user_id_rtpo,
            // 'cancel_details.response_status' =>'1',
            // 'cancel_details.user_id_responders' =>$user_id_rtpo,
            'mbp.delay' =>'0',
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

      $user_id_mbp = $request->input('user_id');
      $cancel_type = $request->input('cancel_type');

      $Mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('*')
      ->where('user_mbp.user_id', $user_id_mbp)
      ->first(); 

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('message', 'cancel_details.message_id', '=', 'message.id')
      ->join('user_mbp', 'cancel_details.user_id_mbp', '=', 'user_mbp.user_id')
      ->where('cancel_details.id','=',$Mbp_data->submission_id)
      // ->where('cancel_details.response_status','=',NULL)
      // ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      ->first();


      $Mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('*')
      ->where('user_mbp.user_id', $user_id_mbp)
      ->first();  
      // return response($cancel_type);

      if ($cancel_type=='DELAY') {


        $edit_cancel_details = DB::table('cancel_details')
        // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->where('id', $Mbp_data->submission_id)
        ->update([
          'response_status' => '1',
          'user_id_responders' =>$user_id_mbp
        ]); 

        $editMbp = DB::table('mbp')
        ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->where('user_mbp.user_id', $user_id_mbp)
        ->update(
          [
            'submission' => NULL,
            'submission_id' => NULL,
          ]
        );  


        if ($editMbp && $edit_cancel_details) {


          // $fireBaseControlle = new FireBaseController;
          // $body = 'Pengajuan Delay '.$Mbp_data->mbp_name.' telah dibatalkan';
          // $tittle = 'Pengajuan Delay Dibatalkan';
          // $datax =$fireBaseControlle->sendNotification($tittle, $body);

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          return response($res);

        }else{

          $res['success'] = true;
          $res['message'] = 'FAILED_UPDATE_DELAY_MBP';
          return response($res);
        }
      }else if ($cancel_type=='CANCEL') {
          // return response($CancellationLetter_data);

        if ($CancellationLetter_data) {

          $CancellationLetter_delete = DB::table('cancel_details')
          ->where('user_id_rtpo','=',null)
          ->where('id', $Mbp_data->submission_id)
          ->delete();

          if ($CancellationLetter_delete) {

            $CancellationLetter_delete = DB::table('message')
            ->where('id','=',$CancellationLetter_data->message_id)
            ->delete();
            if ($CancellationLetter_delete) {

              $editMbp = DB::table('mbp')
              ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
              ->where('user_mbp.user_id', $user_id_mbp)
              ->update(
                [
                  'submission' => NULL,
                  'submission_id' => NULL,
                ]
              );  
              // $fireBaseControlle = new FireBaseController;
              // $body = 'Pengajuan '.$Mbp_data->mbp_name.' telah dibatalkan';
              // $tittle = 'Pengajuan Pembatalan';
              // $datax =$fireBaseControlle->sendNotification($tittle, $body);
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
    }
    public function delayIsOver(Request $request){

      $user_id_mbp = $request->input('user_id');


      $editMbp = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->where('user_mbp.user_id', $user_id_mbp)
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
      $date_now = date('Y-m-d H:i:s');


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
      ->join('users', 'user_mbp.user_id', '=', 'users.id')
      ->select('*')
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
  }