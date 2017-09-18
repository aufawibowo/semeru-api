<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MapController extends Controller
{


  public function getMyMbpSiteToMAp(Request $request){

    $rtpo_id = $request->input('rtpo_id');
    // $data_mbp = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    $data_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.mbp_id','mbp.rtpo_id','mbp.cluster_id','mbp.mbp_name',DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status')/*,'mbp.status'*/,'mbp.latitude','mbp.longitude','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();


    $data_site = DB::table('site')
    ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select(['site.site_id','site.site_name', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude',DB::raw('(case when (status > 0) then "NORMAL" else "MAINS FAIL" end) as status'),DB::raw('(case when (is_allocated > 0) then "true" else "false" end) as is_allocated')])
    ->where('rtpo_id','=',$rtpo_id)
    ->get();

    $data['data_site'] = $data_site;
    $data['data_mbp'] = $data_mbp;


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