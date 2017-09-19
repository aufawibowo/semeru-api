<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class RtpoController extends Controller
{
  public function requestMbpToSiteDown(Request $request){

    $mbp_id = $request->input('mbp_id');
    $site_id = $request->input('site_id');
    $user_id = $request->input('user_id');
    $fireBaseControlle = new FireBaseController;


      // fungsi cek apakah mbp atau site masih memungkinkan untuk di order
    $site_data = DB::table('site')
    ->select('*')
    ->where('site_id','=',$site_id)
    ->get();

    if($site_data){

      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.*','user_mbp.user_id')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->get();
      
      if($mbp_data){

        $mbp_result = json_decode($mbp_data, true);
        $site_result = json_decode($site_data, true);

        $mbp_status =$mbp_result[0]['status'].'';
        $site_status=$site_result[0]['status'].'';
        $site_is_allocated=$site_result[0]['is_allocated'].'';

        if ($mbp_status=='AVAILABLE') {
          if($site_status=='0'){
            if($site_is_allocated=='0'){
      // set waktu menjadi waktu indonesia barat
              date_default_timezone_set("Asia/Jakarta");

      // mengambil waktu dekarang dengan format 'yyyy-mm-dd'
              $mydate=getdate(date("U"));
              $date_now = $mydate['year'].'-'.$mydate['mon'].'-'.$mydate['mday'].' '.$mydate['hours'].':'.$mydate['minutes'].':'.$mydate['seconds'];

      // fungsi edit status mbp to WAITING
              $editSite = DB::table('site')
              ->where('site_id', $site_id)
              ->update(['is_allocated' => '1']);

              if ($editSite) {

                $editMbp = DB::table('mbp')
                ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
                ->where('mbp.mbp_id', $mbp_id)
                ->update(['status' => 'WAITING']);

                if ($editMbp) {
        // fungsi create new suppliyinf power
                  $insertSP = DB::table('supplying_power')->insert(
                    [
                      'mbp_id' => $mbp_id, 
                      'site_id' => $site_id,
                      'user_id' => $user_id,
                      'date_waiting' => date('Y-m-d H:i:s'),
                    ]
                  );

                  if ($insertSP) {
                    // $datax = $this->$fireBaseController->sendNotification();

                    // $body = 'Anda ditugaskan menuju '.$site_result[0]['site_name'].'';
                    // $tittle = 'Anda ditugaskan menuju '.$site_result[0]['site_name'].'';
                    // $datax =$fireBaseControlle->sendNotification($tittle, $body);

                    $notificationController = new NotificationController;
                    $tmp = $notificationController->setNotification0('MBP_ASSIGNMENT_TO_SITE',$mbp_result[0]['mbp_name'].'',$site_result[0]['site_name'].'',$mbp_id,'','');

                    if ($tmp['message']=='SUCCESS') { 
                      $res['success'] = true;
                      $res['message'] = 'SUCCESS_INSERT_TO_DATABASE';
                      // $res['data'] =  $datax;
                      return response($res);

                    }else{


                      $editMbp = DB::table('mbp')
                      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
                      ->where('mbp.mbp_id', $mbp_id)
                      ->update(['status' => 'AVAILABLE']);


                      $editSite = DB::table('site')
                      ->where('site_id', $site_id)
                      ->update(['is_allocated' => '0']);

                      $editSite = DB::table('supplying_power')
                      ->where('mbp_id', $mbp_id)
                      ->where('site_id', $site_id)
                      ->where('user_id', $user_id)
                      ->where('finish', null)
                      ->delete();

                      return response($tmp);

                    }

                  }else{

                    $editMbp = DB::table('mbp')
                    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
                    ->where('mbp.mbp_id', $mbp_id)
                    ->update(['status' => 'AVAILABLE']);

                    if($editMbp){
                      $res['success'] = false;
                      $res['message'] = 'FAILED_INSERT_TO_SUPPLAYING_POWER';
                      return response($res);
                    }else{
                      $res['success'] = false;
                      $res['message'] = 'FAILED_INSERT_TO_SUPPLAYING_POWER_AND_MBP_DATA_CAN_NOT_BE_RETURNED';
                      return response($res);
                    }
                  }
                }else{

                  $editSite = DB::table('site')
                  ->where('site_id', $site_id)
                  ->update(['is_allocated' => '0']);

                  if($editSite){
                    $res['success'] = false;
                    $res['message'] = 'MBP_IS_ALLOCATED';
                    return response($res);
                  }else{
                    $res['success'] = false;
                    $res['message'] = 'MBP_IS_ALLOCATED_AND_SITE_DATA_CAN_NOT_BE_RETURNED';
                    return response($res);
                  }
                }
              }else{
                $res['success'] = false;
                $res['message'] = 'FAILED_EDIT_DATA_SITE';
                return response($res);
              }

            }else{
              $res['success'] = false;
              $res['message'] = 'SITE_IS_ALLOCATED';
              return response($res);
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'SITE_STATUS_NORMAL';
            return response($res);
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'MBP_IS_ALLOCATED';
          return response($res);
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'DATA_MBP_NOT_FOUND';
        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'DATA_SITE_NOT_FOUND';
      return response($res);
    }
  }
  public function cancelRequestMbpToSiteDown(Request $request){
    /*
    ketika rtpo membatalkan penugasan mbp terhadap site tertentu, maka :
    > mbp status dirubah jadi available
    > site allocation dirubah jadi '0'
    > tabel sp di set cancel..:D*/

    $mbp_id = $request->input('mbp_id');

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
      $tmp = $this->CancelRequestMbp($mbp_id);
      return response($tmp);
    }
  }
  public function CancelRequestMbp($mbp_id){

    $mbp_data = DB::table('supplying_power')                        // jadikan finish = cancel
    ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')      // jadikan mbp kembali available #sesuaikan status"nya
    ->join('site', 'supplying_power.site_id', '=', 'site.site_id')  // jadikan status alokasinya jadi '0' kembali
    ->where('supplying_power.mbp_id','=',$mbp_id)
    ->where('supplying_power.finish','=',null)
    ->update(
      [
        'supplying_power.finish' =>'CANCEL',
        'supplying_power.date_finish' =>date('d-M-Y H:i:s'),
        'mbp.status' =>'AVAILABLE',
        'mbp.submission' =>null,
        'mbp.submission_id' =>null,
        'site.is_allocated' =>'0',
      ]
    );

    if ($mbp_data) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] =  $datax;
      return $res;
    }
  }

}