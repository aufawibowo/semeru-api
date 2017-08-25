<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class SiteController extends Controller
{
    public function getAllSite(Request $request){

      $data_site = DB::table('site')
            ->join('class', 'site.class_id', '=', 'class.class_id')
            ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
            /*->where('status','=','0')*/
            ->get();
      // $data_site = DB::table('site')->select('*')->get();


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
      // $data_site = DB::table('site')->select('*')->where('status','=','0')->get();


      $data_site = DB::table('site')
            ->join('class', 'site.class_id', '=', 'class.class_id')
            ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
            ->where('status','=','0')
            ->get();

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

      // $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->get();


      $data_site = DB::table('site')
            ->join('class', 'site.class_id', '=', 'class.class_id')
            ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
            ->where('rtpo_id','=',$rtpo_id)
            ->get();


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

      // $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','0')->get();

      $data_site = DB::table('site')
            ->join('class', 'site.class_id', '=', 'class.class_id')
            ->select('site.site_id','site.site_name','site.status', 'class.class_name', /*'class.revenue',*/'site.latitude','site.longitude')
            ->where('rtpo_id','=',$rtpo_id)
            ->where('status','=','0')
            ->get();

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