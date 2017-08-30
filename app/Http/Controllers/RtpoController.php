<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class RtpoController extends Controller
{
  public function requestMbpToSiteDown(Request $request){

    $mbp_id = $request->input('mbp_id');
    $site_id = $request->input('site_id');


      // fungsi cek apakah mbp atau site masih memungkinkan untuk di order
    $site_data = DB::table('site')
    ->select('*')
    ->where('site_id','=',$site_id)
    ->get();
    // $mbp_data = DB::table('mbp')
    // ->select('*')
    // ->where('mbp_id','=',$mbp_id)
    // ->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('mbp_id','=',$mbp_id)
    ->get();


    $mbp_result = json_decode($mbp_data, true);
    $site_result = json_decode($site_data, true);

    $mbp_status =$mbp_result[0]['status'].'';
    $site_status=$site_result[0]['status'].'';
    $site_booked=$site_result[0]['booked'].'';

    if ($mbp_status=='AVAILABLE') {
      if($site_status=='0'){
        if($site_booked=='0'){


      // set waktu menjadi waktu indonesia barat
          date_default_timezone_set("Asia/Jakarta");

      // mengambil waktu dekarang dengan format 'yyyy-mm-dd'
          $mydate=getdate(date("U"));
          $date_now = $mydate['year'].'-'.$mydate['mon'].'-'.$mydate['mday'].' '.$mydate['hours'].':'.$mydate['minutes'].':'.$mydate['seconds'];

      // fungsi edit status mbp to WAITING
          // $editMbp = DB::table('mbp')
          // ->where('mbp_id', $mbp_id)
          // ->update(['status' => 'WAITING']);

          $editMbp = DB::table('mbp')
          ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
          ->where('mbp_id', $mbp_id)
          ->update(['status' => 'WAITING']);


      // fungsi edit status mbp to WAITING
          $editMbp = DB::table('site')
          ->where('site_id', $site_id)
          ->update(['booked' => '1']);

          if ($editMbp) {
        // fungsi create new suppliyinf power
            $insertSP = DB::table('supplying_power')->insert(
              [
                'mbp_id' => $mbp_id, 
                'site_id' => $site_id,
                'date' => $date_now
              ]
            );

            if ($insertSP) {
              $res['success'] = true;
              $res['message'] = 'Request mbp to Site Success!';
          // $res['data'] = $data_site;
              return response($res);
            }else{
              $res['success'] = false;
              $res['message'] = 'Request mbp to Site Failed!';
              
              $editMbp = DB::table('mbp')
              ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
              ->where('mbp_id', $mbp_id)
              ->update(['status' => '0']);

              return response($res);
            }

          }else{
            $res['success'] = false;
            $res['message'] = 'Request mbp to Site Failed because mbp is still working!';
            return response($res);
          }

        }else{
          $res['success'] = false;
          $res['message'] = 'Site has been booked!';
          return response($res);
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'Site status normal!';
        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'Mbp not Available!';
      return response($res);
    }

  }
}