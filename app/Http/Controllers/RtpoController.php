<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class RtpoController extends Controller
{
    public function requestMbpToSiteDown(Request $request){

      $mbp_id = $request->input('mbp_id');
      $site_id = $request->input('site_id');

      // set waktu menjadi waktu indonesia barat
      date_default_timezone_set("Asia/Jakarta");

      // mengambil waktu dekarang dengan format 'yyyy-mm-dd'
      $mydate=getdate(date("U"));
      $date_now = $mydate['year'].'-'.$mydate['mon'].'-'.$mydate['mday'].' '.$mydate['hours'].':'.$mydate['minutes'].':'.$mydate['seconds'];

      // fungsi edit status mbp to 0
      $editMbp = DB::table('mbp')
            ->where('mbp_id', $mbp_id)
            ->update(['status' => '1']);

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
                    ->where('mbp_id', $mbp_id)
                    ->update(['status' => '0']);

          return response($res);
        }

      }else{
        $res['success'] = false;
        $res['message'] = 'Request mbp to Site Failed because mbp is still working!';
        return response($res);
      }

      
    }
}