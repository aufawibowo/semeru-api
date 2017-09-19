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

    // public function getMessageDetil(Request $request){

    //   $message_id = $request->input('message_id');

    //   $message_data = DB::table('message')
    //   ->select('*')
    //   ->where('id','=',$message_id)
    //   ->first();

    //   switch ($message_data->subject) {
    //     case "MBP_INFORMATION_UNAVAILABLE":
    //     // echo "Your favorite color is red!";
    //     $tmp = $this->getMessageDetilUnavailable($message_id);
    //     return response($tmp);
    //     break;
    //     case "CANCEL":
    //     // echo "Your favorite color is blue!";
    //     $tmp = $this->getMessageDetilCancelDelay($message_id);
    //     return response($tmp);
    //     break;
    //     case "DELAY":
    //     // echo "Your favorite color is green!";
    //     $tmp = $this->getMessageDetilCancelDelay($message_id);
    //     return response($tmp);
    //     break;
    //     default:
    //     // echo "Your favorite color is neither red, blue, nor green!";
    //   }
    // }

    public function getMessageDetil(Request $request){

      $cancel_id = $request->input('cancel_id');

      $message_data = DB::table('message')
      ->join('cancel_details', 'message.id', '=', 'cancel_details.message_id')
      ->select('*')
      ->where('cancel_details.id','=',$cancel_id)
      ->first();

      switch ($message_data->subject) {
        case "MBP_INFORMATION_UNAVAILABLE":
        // echo "Your favorite color is red!";
        $tmp = $this->getMessageDetilUnavailable($cancel_id);
        return response($tmp);
        break;
        case "CANCEL":
        // echo "Your favorite color is blue!";
        $tmp = $this->getMessageDetilCancelDelay($cancel_id);
        return response($tmp);
        break;
        case "DELAY":
        // echo "Your favorite color is green!";
        $tmp = $this->getMessageDetilCancelDelay($cancel_id);
        return response($tmp);
        break;
        default:
        // echo "Your favorite color is neither red, blue, nor green!";
      }
    }
    public function getMessageDetilCancelDelay($cancel_id){

      // $message_id = $request->input('message_id');

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id') 
      ->join('message', 'cancel_details.message_id', '=', 'message.id')
      ->join('supplying_power', 'cancel_details.sp_id', '=', 'supplying_power.sp_id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('class', 'site.class_id', '=', 'class.class_id')
      
      ->select('mbp.mbp_name','site.site_name','users.name as operator_name','message.subject','message.text_message','cancel_details.available_status')
      ->where('cancel_details.response_status','=',NULL)
      ->where('supplying_power.finish', NULL)
      ->where('cancel_details.id','=',$cancel_id)
      ->first();

      if ($CancellationLetter_data!=null) {


        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $CancellationLetter_data;

        return $res;
      }else{


        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $CancellationLetter_data;

        return $res;
      }

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] = $message_data;

      return $res;
    }
    public function getMessageDetilUnavailable($cancel_id){

      // $message_id = $request->input('message_id');

      $CancellationLetter_data = DB::table('cancel_details')
      ->join('users', 'cancel_details.user_id_mbp', '=', 'users.id')
      ->join('user_mbp', 'users.id', '=', 'user_mbp.user_id')
      ->join('mbp', 'user_mbp.mbp_id', '=', 'mbp.mbp_id') 
      ->join('message', 'cancel_details.message_id', '=', 'message.id')
      // ->join('supplying_power', 'cancel_details.sp_id', '=', 'supplying_power.sp_id')
      // ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      // ->join('class', 'site.class_id', '=', 'class.class_id')
      
      ->select('mbp.mbp_name'/*,'site.site_name'*/,'users.name as operator_name','message.subject','message.text_message','cancel_details.available_status')
      ->where('cancel_details.response_status','=',NULL)
      // ->where('supplying_power.finish', NULL)
      ->where('cancel_details.id','=',$cancel_id)
      ->first();

      if ($CancellationLetter_data!=null) {

        $data['mbp_name'] = $CancellationLetter_data->mbp_name;
        $data['site_name'] = '';
        $data['operator_name'] = $CancellationLetter_data->operator_name;
        $data['subject'] = $CancellationLetter_data->subject;
        $data['text_message'] = $CancellationLetter_data->text_message;
        $data['available_status'] = $CancellationLetter_data->available_status;


        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;

        return $res;
      }else{


        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;

        return $res;
      }

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] = $message_data;

      return $res;
    }
    public function sendMessage(Request $request){

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

