<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class CheckingController extends Controller
{

  //fungsi untuk melihat mbp yang sedang delay atau unavailable dan ketika sudah waktunya aktif maka aktifkan..:D
  public function CheckActiveMbp($active_at, $mbp_id, $status){
    
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    if ($active_at!=NULL) {
      $time1 = strtotime($active_at);
      $time2 = strtotime($date_now);

      if ($status=='UNAVAILABLE') {

        if($time1<=$time2){

          $updateMbp = DB::table('mbp')
          ->where('mbp_id','=',$mbp_id)
          ->update(
            [
              'status' =>'AVAILABLE',
              'submission' =>NULL,
              'submission_id' =>NULL,
              'message_id' =>NULL,
              'active_at' =>NULL,
            ]
          );

          if ($updateMbp) {
            # kirim notif..:D mbp ini siap bertugas kembali
          }
        }

      }else{

        if($time1>=$time2){

          $updateMbp = DB::table('mbp')
          ->where('mbp_id','=',$mbp_id)
          ->update(
            [
              // 'status' =>'AVAILABLE',
              // 'submission' =>NULL,
              // 'submission_id' =>NULL,
              // 'message_id' =>NULL,
              // 'active_at' =>NULL,
            ]
          );

          if ($updateMbp) {
            # kirim notif..:D mbp ini siap bertugas kembali
          }
        }
      }

    }else{
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return $res;
    }
  }
  //
  public function CheckExpiredSos(/*Request $request,*/ $sos_id, $sos_date){
    date_default_timezone_set("Asia/Jakarta");

    $date_now = date('Y-m-d H:i:s');
    $date_now_strtime = strtotime($date_now);
    // $date_now = date('Y-m-d H:i:s',$date_now_strtime);

    // $sos_id = $request->input('sos_id');
    // $sos_date = $request->input('sos_date');

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $data['sos_id'] = $sos_id;
    
    $sos_date_strtime = strtotime($sos_date);
    $sos_expired_date_strtime = strtotime("+1 day",strtotime($sos_date));

    // $data['sos_date ststime'] = strtotime($sos_date);
    // $data['sos_date'] = date("Y-m-d H:i:s", strtotime($sos_date));
    // $data['sos_date ststime +1day'] = strtotime("+1 day",strtotime($sos_date));
    // $data['sos_date  +1day'] = date("Y-m-d H:i:s", strtotime("+1 day",strtotime($sos_date)));
    // $data['date_now ststime'] = $date_now_strtime;
    // $data['date_now'] = $date_now;
    // $res['data'] = $data;

    if ($date_now_strtime>=$sos_expired_date_strtime) {

      // return response($res); EXPIRED
      $edit_sos = DB::table('sos')
      ->where('id','=',$sos_id)
      ->update(
        [
          'status' => 'EXPIRED'
        ]
      );
      if ($edit_sos) {
        $res['result'] = 'EXPIRED';
      }

    }else{

      $res['result'] = 'ACTIVE';
      // return response('sos belum kadaluarsa');
    }

    return($res);
    // return response($res);
  }


  public function getMyMbpSiteToMAp(Request $request){

    $rtpo_id = $request->input('rtpo_id');
    // $data_mbp = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    $data_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.mbp_id','mbp.submission','mbp.rtpo_id','mbp.cluster_id','mbp.mbp_name',/*DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status'),*/'mbp.status','mbp.latitude','mbp.longitude','user_mbp.user_id','users.name as operator_name')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();

    foreach ($data_mbp as $dm)
    {
      if ($dm->submission == 'DELAY') {
        $mbp_data['status'] = 'DELAY';
      }else{
        $mbp_data['status'] = $dm->status;
      }
      $mbp_data['mbp_id'] = $dm->mbp_id;
      $mbp_data['rtpo_id'] = $dm->rtpo_id;
      $mbp_data['cluster_id'] = $dm->cluster_id;
      $mbp_data['mbp_name'] = $dm->mbp_name;
    // $mbp_data['status'] = $data_mbp->status;
      $mbp_data['latitude'] = $dm->latitude;
      $mbp_data['longitude'] = $dm->longitude;
      $mbp_data['id'] = $dm->id;
      $mbp_data['operator_name'] = $dm->operator_name;
    }


    


    $data_site = DB::table('site')
    ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('*','site.site_id','site.site_name', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();

    // "site_id": "BDO003",
    // "site_name": "BONDOWOSO2",
    // "code_name": "BDO003",
    // "class_name": "SILVER",
    // "latitude": -7.95871,
    // "longitude": 113.80548,

    // "status": "NORMAL",
    // "is_allocated": "false"

    foreach ($data_site as $ds)
    {
      if ($ds->status > 0) {
        $site_data['status'] = 'NORMAL';
      }else{
        $site_data['status'] = 'MAINS FAIL';
      }
      if ($ds->is_allocated > 0) {
        $site_data['is_allocated'] = 'true';
      }else{
        $site_data['is_allocated'] = 'false';
      }

      $site_data['site_id'] = $ds->site_id;
      $site_data['site_name'] = $ds->site_name;
      $site_data['code_name'] = $ds->code_name;
      $site_data['class_name'] = $ds->class_name;
      $site_data['latitude'] = $ds->latitude;
      $site_data['longitude'] = $ds->longitude;
    }

    $data['data_site'] = $site_data;
    $data['data_mbp'] = $mbp_data;


    if ($data_site && $data_mbp) {
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
}