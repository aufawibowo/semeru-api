<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class SiteController extends Controller
{
    /**
     * Get user by id
     *
     * URL /user/{id}
     */
    // public function get_bts_off(Request $request)
    // {

    //     $btss = DB::table('bts')->select('*')->where('status','=','0')->get();


    //     if ($btss) {
    //           $res['success'] = true;
    //           $res['message'] = 'Success!';
    //           $res['data'] = $btss;
        
    //           return response($res);
    //     }else{
    //       $polys['success'] = false;
    //       $polys['message'] = 'Cannot find polys!';
        
    //       return response($btss);
    //     }
    // }

    public function getAllSite(Request $request){
      $data_site = DB::table('site')->select('*')->get();


        if ($data_site) {
          $res['success'] = true;
          $res['message'] = 'Success!';
          $res['data'] = $data_site;
        
          return response($res);
        }else{
          $polys['success'] = false;
          $polys['message'] = 'Cannot find polys!';
        
          return response($btss);
        }

    }
    
    public function getAllSiteDown(Request $request){
      $data_site = DB::table('site')->select('*')->where('status','=','0')->get();

        if ($data_site) {
          $res['success'] = true;
          $res['message'] = 'Success!';
          $res['data'] = $data_site;
        
          return response($res);
        }else{
          $polys['success'] = false;
          $polys['message'] = 'Cannot find polys!';
        
          return response($btss);
        }

    }

    public function getMySite(Request $request){


      $rtpo_id = $request->input('rtpo_id');

      $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->get();


        if ($data_site) {
          $res['success'] = true;
          $res['message'] = 'Success!';
          $res['data'] = $data_site;
        
          return response($res);
        }else{
          $polys['success'] = false;
          $polys['message'] = 'Cannot find polys!';
        
          return response($btss);
        }

    }

    public function getMySiteDown(Request $request){


      $rtpo_id = $request->input('rtpo_id');

      $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','0')->get();


        if ($data_site) {
          $res['success'] = true;
          $res['message'] = 'Success!';
          $res['data'] = $data_site;
        
          return response($res);
        }else{
          $polys['success'] = false;
          $polys['message'] = 'Cannot find polys!';
        
          return response($btss);
        }

    }
}