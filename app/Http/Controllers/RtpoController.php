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
                    $res['success'] = true;
                    $res['message'] = 'SUCCESS_INSERT_TO_DATABASE';
          // $res['data'] = $data_site;
                    return response($res);
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
}