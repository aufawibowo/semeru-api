<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MbpController extends Controller
{


  public function getAllMbp(Request $request){
    $data_site = DB::table('mbp')->select('*')->get();


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
    $data_site = DB::table('mbp')->select('*')->where('status','=','1')->get();

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

    $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();


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
    $mbp_data = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id);
    $data_onprogress = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON PROGRESS')->get();
    $data_waiting = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
    $data_available = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();

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

    $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','1')->get();


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

  public function getMyMbpavailable(Request $request){


    $rtpo_id = $request->input('rtpo_id');

    $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','0')->get();


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
}