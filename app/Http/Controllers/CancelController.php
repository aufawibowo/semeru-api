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
    public function sendCancellationLetterToRtpo(Request $request)
    {
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
      ->where('cancel_details.user_id_rtpo','=',null)
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
    }

    public function sendDelayLetterToRtpo(Request $request)
    {
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
      ->where('cancel_details.user_id_rtpo','=',null)
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
                  // 'available_status' => $available_status,
                  'date' => date('Y-m-d H:i:s'),
                ]
              );


              if ($insertCancellationLetter) {

                $editMbp = DB::table('mbp')
                ->where('mbp_id', $mbp_id)
                ->update(['delay' => 1]);

                if ($editMbp) {

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


    public function acceptCancellationLetterfromMbp(Request $request)
    {
      // ketika eksekusi ini, maka tabel sp di kolom 'finish' diisi 'CANCEL' dan 'date_finish' juga terisi
      // lalu status mbp berubah jadi 'UNAVAILABLE' 
      // site bagian is_allocated berubah menjadi '0' kembali

      date_default_timezone_set("Asia/Jakarta");

      $cancel_letter_id = $request->input('cancel_letter_id'); //1
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
      ->where('cancel_details.user_id_rtpo', NULL)
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
      ->select('cancel_details.id','mbp.mbp_name','users.name','message.id as message_id','message.subject','cancel_details.date')
      // ->select('*')
      ->where('cancel_details.user_id_rtpo','=',null)
      ->get();

      if ($CancellationLetter_data) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS_GET_MESSAGE';
        $res['data'] = $CancellationLetter_data;

        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_GET_MESSAGE';
        
        return response($res);
      }
    }

    public function deleteCancellationLetterFromMbp(Request $request){

      $user_id_mbp = $request->input('user_id');
      $cancel_type = $request->input('cancel_type');

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('message', 'cancel_details.message_id', '=', 'message.id')
      ->where('cancel_details.user_id_rtpo','=',null)
      ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      ->first();


      if ($cancel_type=='DELAY') {
        $editMbp = DB::table('mbp')
        ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->where('user_mbp.user_id', $user_id_mbp)
        ->update(['delay' => 0]);  

        $edit_cancel_details = DB::table('cancel_details')
        // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->where('user_id_mbp', $user_id_mbp)
        ->update([
          'response_status' => 1,
          'user_id_responders' =>$user_id_mbp
        ]);  

        if ($editMbp && $edit_cancel_details) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          return response($res);

        }else{

          $res['success'] = true;
          $res['message'] = 'FAILED_UPDATE_DELAY_MBP';
          return response($res);
        }
      }else{
          // return response($CancellationLetter_data);

        if ($CancellationLetter_data) {

          $CancellationLetter_delete = DB::table('cancel_details')
        // ->join('message', 'cancel_details.message_id', '=', 'message.id')
          ->where('user_id_rtpo','=',null)
          ->where('user_id_mbp','=',$user_id_mbp)
          ->delete();

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
    }


    public function delayIsOver(Request $request){

      $user_id_mbp = $request->input('user_id');

      $editMbp = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->where('user_mbp.user_id', $user_id_mbp)
      ->update(['delay' => 0]);

      // $CancellationLetter_data = DB::table('cancel_details')
      // ->join('message', 'cancel_details.message_id', '=', 'message.id')
      // ->where('cancel_details.user_id_rtpo','=',null)
      // ->where('cancel_details.user_id_mbp','=',$user_id_mbp)
      // ->first();
        // return response($CancellationLetter_data);
      if ($editMbp) {

        $CancellationLetter_delete = DB::table('cancel_details')
        // ->join('message', 'cancel_details.message_id', '=', 'message.id')
        // ->where('user_id_rtpo','=',null)
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

  }