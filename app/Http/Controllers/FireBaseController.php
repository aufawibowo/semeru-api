<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class FireBaseController extends Controller
{
    /**
     * Get user by id
     *
     * URL /user/{id}
     */
    // public function sendNotification(Request $request)
    // {

    //   $btss = DB::table('bts')->select('*')->where('status','=','0')->get();


    //   if ($btss) {
    //     $res['success'] = true;
    //     $res['message'] = 'Success!';
    //     $res['data'] = $btss;
        
    //     return response($res);
    //   }else{
    //     $polys['success'] = false;
    //     $polys['message'] = 'Cannot find polys!';
        
    //     return response($btss);
    //   }
    // }

    public function sendNotification($title,$body/*,$array_tokenIDs*/)
    {

      // API access key from Google API's Console
      define( 'API_ACCESS_KEY', 'AAAAo6mi6uY:APA91bF5Jrgp7pqCX40LO0WQb6v-eLKd5xIP0xjxivSdlpDg5_iOisegSNQR0GSYwmeICJnumEbckFR6RextiSTkhUA0xBKk-HfMMNzRAWmyXPZzi5FxJvaYescfgyD4s3YTUwB9X78o
        ' );


      $getToken['id'] = 'e8pkSvLMX_g:APA91bHoiZg3xXOE14TdyNQZf5AEpd43-3loEsbJmPCv50g2KtXiiJ8j6wO12TTHtUzcRuMQvBH5cwzstg2lIDdneyWfUORbtnaBDFiUWJ1MZHmq4uJY0S706TNX7jpKUY3niwk1uY';
      // $getToken['id'] = 'ecR8aMstmLU:APA91bHu_D1u98v5WCVSPVXbP4dhOKUOyLQsiDYnLXtx4xnb3OOUAs-No2e7ylNSXSoCJtlqJICNvDkKX3f2PvYn_61RjMO8HsuUzM5xqWkO2cZEaiSxO87bhu65C6WbZEWPHszr_pNv';
      
      $registrationIds = array( $getToken['id'] );
      // $registrationIds = array( 'ecR8aMstmLU:APA91bHu_D1u98v5WCVSPVXbP4dhOKUOyLQsiDYnLXtx4xnb3OOUAs-No2e7ylNSXSoCJtlqJICNvDkKX3f2PvYn_61RjMO8HsuUzM5xqWkO2cZEaiSxO87bhu65C6WbZEWPHszr_pNv' );

      // prep the bundle
      $msg = array
      (
        // 'message'   => 'here is a message. message',
        // 'body'   => 'here is a message. message',
        // 'title'   => 'This is a title. title'
        'body'   => $body,
        'title'   => $title
        // 'subtitle'  => 'This is a subtitle. subtitle',
        // 'tickerText'  => 'Ticker text here...Ticker text here...Ticker text here',
        // 'vibrate' => 1,
        // 'sound'   => 1,
        // 'largeIcon' => 'large_icon',
        // 'smallIcon' => 'small_icon'
      );

      $fields = array
      (
        'registration_ids'  => $registrationIds,
        'notification'      => $msg
      );

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
      $result = curl_exec($ch );
      curl_close( $ch );

      // echo $result;

      $data = json_decode($result, true);
      return $data;
      // return response($result);

    }
  }