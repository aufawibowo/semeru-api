<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MessageController extends Controller
{
    /**
     * Get user by id
     *
     * URL /user/{id}
     */

    public function getMessage(Request $request){

      $user_id = $request->input('user_id');

      $message_data = DB::table('message')
      ->select('*')
      ->where('to','=',$user_id)
      ->get();

      $res['success'] = true;
      $res['message'] = 'SUCCESS_GET_MESSAGE';
      $res['data'] = $message_data;

      return response($res);
    }

    public function getMessageDetil(Request $request){

      // $user_id = $request->input('user_id');
      $message_id = $request->input('message_id');

      $message_data = DB::table('message')
      ->select('*')
      ->where('id','=',$message_id)
      ->get();

      $res['success'] = true;
      $res['message'] = 'SUCCESS_GET_MESSAGE';
      $res['data'] = $message_data;

      return response($res);
    }


    public function sendMessage(Request $request)
    {

      date_default_timezone_set("Asia/Jakarta");
      $subject = $request->input('subject');
      $from = $request->input('from');
      $type_to = $request->input('type_to');  // PERSONAL / RTPO / ALL_RTPO
      $to = $request->input('to');
      $text_message = $request->input('text_message');

      // $btss = DB::table('bts')->select('*')->where('status','=','0')->get();

      if($type_to=='PERSONAL'){


        $user_data = DB::table('users')
        ->select('*')
        ->where('id','=',$to)
        ->first();

        if ($user_data) {
          $insertMessage = DB::table('message')->insert(
            [
              'subject' => $subject, 
              'from' => $from,
              'to' => $to,
              'text_message' => $text_message,
              'date_message' => date('Y-m-d H:i:s'),
            ]
          );

          if($insertMessage) {
            $res['success'] = true;
            $res['message'] = 'SUCCESS_SENDING_MESSAGE';
            $res['data'] = $insertMessage;
            return response($res);
          }else{
            $res['success'] = false;
            $res['message'] = 'FAILED_SENDING_MESSAGE';

            return response($res);
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'USER_DATA_NOT_FOUND';

          return response($res);
        }

      }else if ($type_to=='RTPO') {

        // get data seluruh user yang rtpo_inya 'ini',. hehee
        $user_rtpo_data = DB::table('user_rtpo')
        ->join('users', 'user_rtpo.user_id', '=', 'users.id')
        ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
          ->select('users.id','rtpo.rtpo_name'/*,'users.token_firebase'*/) // asumsi nnti ada token firebase juga.. hehee..
          ->where('user_rtpo.rtpo_id','=',$to)
          ->get();
        // fungsi perulangan mengirim sebanyak id yang terambil.. bismillah

          if($user_rtpo_data) {

            foreach ($user_rtpo_data as $param => $row) {

              $insertMessage = DB::table('message')->insert(
                [
              // $user_rtpo_data[$param]['id'].''
                  'subject' => $subject, 
                  'from' => $from,
                  'to' => $user_rtpo_data[$param]->id,
                  'text_message' => $text_message,
                  'date_message' => date('Y-m-d H:i:s'),
                ]
              );

              if($insertMessage) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS_SENDING_MESSAGE';
            // $res['data'] = $insertMessage;
            // return response($res);
              }else{
                $res['success'] = false;
                $res['message'] = 'FAILED_SENDING_MESSAGE';

                return response($res);
              }
            }

            $res['success'] = true;
            $res['message'] = 'SUCCESS_SENDING_MESSAGE';
          // $res['data'] = $user_rtpo_data;

            return response($res);
          }else{
            $res['success'] = false;
            $res['message'] = 'USER_RTPO_NOT_FOUND';

            return response($res);
          }
        }else if($type_to=='ALL_RTPO'){

        // get data seluruh user yang rtpo_inya 'ini',. hehee
          $user_rtpo_data = DB::table('user_rtpo')
          ->join('users', 'user_rtpo.user_id', '=', 'users.id')
          ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
          ->select('users.id','rtpo.rtpo_name'/*,'users.token_firebase'*/) // asumsi nnti ada token firebase juga.. hehee..
          ->where('users.user_type','=','RTPO')
          ->where('users.id','!=',$from)
          ->get();

        // fungsi perulangan mengirim sebanyak id yang terambil.. bismillah

          if($user_rtpo_data) {

            foreach ($user_rtpo_data as $param => $row) {

              $insertMessage = DB::table('message')->insert(
                [
              // $user_rtpo_data[$param]['id'].''
                  'subject' => $subject, 
                  'from' => $from,
                  'to' => $user_rtpo_data[$param]->id,
                  'text_message' => $text_message,
                  'date_message' => date('Y-m-d H:i:s'),
                ]
              );

              if($insertMessage) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS_SENDING_MESSAGE';
            // $res['data'] = $insertMessage;
            // return response($res);
              }else{
                $res['success'] = false;
                $res['message'] = 'FAILED_SENDING_MESSAGE';

                return response($res);
              }
            }

            $res['success'] = true;
            $res['message'] = 'SUCCESS_SENDING_MESSAGE';
            // $res['data'] = $user_rtpo_data;

            return response($res);
          }else{
            $res['success'] = false;
            $res['message'] = 'USER_RTPO_NOT_FOUND';

            return response($res);
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'PARAMETER_TYPE_TO_NOT_MATCH';

          return response($res);
        }
      }
    }
  
