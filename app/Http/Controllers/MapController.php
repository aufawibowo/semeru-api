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
    // $data_site = DB::table('site')
    //         ->join('class', 'site.class_id', '=', 'class.class_id')
    //         ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
    //         ->where('rtpo_id','=',$rtpo_id)
    //         ->get();

    $data_mbp = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    $data_site = DB::table('site')
    ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select(['site.site_id','site.site_name', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude',DB::raw('(case when (status > 0) then "NORMAL" else "MAINS FAIL" end) as status'),DB::raw('(case when (booked > 0) then "booked" else "no" end) as booked')])
    ->where('rtpo_id','=',$rtpo_id)
    ->get();


                // "site_id": 9,
                // "site_name": "bts_prb03",
                // "status": 0,
                // "class_name": "silver",
                // "latitude": -7.758308,
                // "longitude": 113.186582

            // ['mbp_id','user_id','rtpo_id','cluster_id','latitude','longitude','mbp_name',DB::raw('(case when (status > 0) then "on progress" else "available" end) as status'),DB::raw('(case when (waiting > 0) then "waiting" else "available" end) as waiting')]

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

  // public function getMyMbp(Request $request){


  //   $rtpo_id = $request->input('rtpo_id');

  //   $data_mbp = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
  //   $data_site = DB::table('site')
  //           ->join('class', 'site.class_id', '=', 'class.class_id')
  //           ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
  //           ->where('rtpo_id','=',$rtpo_id)
  //           ->get();

  //   $data['data_site'] = $data_site;
  //   $data['data_mbp'] = $data_mbp;


  //   if ($data_site && $data_mbp) {
  //     $res['success'] = true;
  //     $res['message'] = 'Success!';
  //     $res['data'] = $data;
  
  //     return response($res);
  //   }else{
  //     $res['success'] = false;
  //     $res['message'] = 'Cannot find data!';
  
  //     return response($res);
  //   }
  // }

  // public function getMySite(Request $request){


  //   $rtpo_id = $request->input('rtpo_id');

  //     // $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->get();


  //   $data_site = DB::table('site')
  //           ->join('class', 'site.class_id', '=', 'class.class_id')
  //           ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
  //           ->where('rtpo_id','=',$rtpo_id)
  //           ->get();


  //   if ($data_site) {
  //     $res['success'] = true;
  //     $res['message'] = 'Success!';
  //     $res['data'] = $data_site;
  
  //     return response($res);
  //   }else{
  //     $polys['success'] = false;
  //     $polys['message'] = 'Cannot find polys!';
  
  //     return response($btss);
  //   }

  // }

}