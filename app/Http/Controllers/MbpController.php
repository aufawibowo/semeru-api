<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MbpController extends Controller
{


  public function getAllMbp(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    // ->where('rtpo_id','=',$rtpo_id)
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }

  }

  public function getAllMbpOnProggress(Request $request){
    // $data_site = DB::table('mbp')->select('*')->where('status','=','1')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('status','=','1')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }

  }

  public function getMyMbp(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';
      
      return response($res);
    }

  }

  public function getMyMbpCategory(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    // $mbp_data = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id);
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();


    // $data_onprogress = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON PROGRESS')->get();
    $data_onprogress = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','ON PROGRESS')
    ->get();

    // $data_waiting = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
    $data_waiting = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','WAITING')
    ->get();

    // $data_available = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
    $data_available = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','AVAILABLE')
    ->get();

    // $data_onprogress->where('status','=','ON PROGRESS')->get();
    // $data_waiting->where('status','=','WAITING')->get();
    // $data_available->where('status','=','AVAILABLE')->get();

    $data['ON PROGRESS'] = $data_onprogress;
    $data['WAITING'] = $data_waiting;
    $data['AVAILABLE'] = $data_available;


    if ($mbp_data) {
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

  public function getMyMbpOnProgress(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON PROGRESS')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','ON PROGRESS')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }

  }

  public function getMyMbpAvailable(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','AVAILABLE')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }

  }
  
  public function getMyMbpWaiting(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','WAITING')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }

  }

  public function updateLatLongMbp(Request $request){

    $mbp_name = $request->input('mbp_name');
    $latitude = $request->input('latitude');
    $longitude = $request->input('longitude');

    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_name', $mbp_name)
    ->update(
      [
        'latitude' => $latitude,
        'longitude' => $longitude,
      ]
    );

    if ($editMbp) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['data'] = $editMbp;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }
}