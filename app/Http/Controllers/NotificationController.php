<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class NotificationController extends Controller
{
  public function get_bts_off(Request $request){

    $btss = DB::table('bts')->select('*')->where('status','=','0')->get();

    if ($btss) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $btss;

      return response($res);
    }else{
      $polys['success'] = false;
      $polys['message'] = 'Cannot find polys!';

      return response($btss);
    }
  }
  public function getNotification(){
    
  }
}