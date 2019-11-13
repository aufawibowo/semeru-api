<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class RecommendationController extends Controller
{
  public function calculateDistance(Request $request){


    $latitude1 = $request->input('latitude1');
    $longitude1 = $request->input('longitude1');

    $latitude2 = $request->input('latitude2');
    $longitude2 = $request->input('longitude2');

    $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$latitude1.",".$longitude1."&destinations=".$latitude2.",".$longitude2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");

    $data = json_decode($dataJson,true);
    $data_traffic['distance'] = $data['rows'][0]['elements'][0]['distance']['text'];
    $data_traffic['duration'] = $data['rows'][0]['elements'][0]['duration']['text'];

    if ($dataJson) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_traffic;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }
  }
  public function getMySiteDownAndMyMbpAvailable(Request $request){



    $rtpo_id = $request->input('rtpo_id');

    $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','0')->where('is_allocated','=','0')->get();

    // $data_mbp = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','0')->get();
    $data_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','0')
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['site_down'] = $data_site;
      $res['mbp_available'] = $data_mbp;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }
  }
  public function getRecomendationClassAllSiteDown(Request $request){

    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status','site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude')
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();
    
    $result = json_decode($site_data, true);
    foreach ($result as $param => $row) {
      $revenue[$param]  = $row['revenue'].'';
      $site_name[$param] = $row['site_name'].'';
    }
    array_multisort($revenue, SORT_DESC, $result);




    if ($site_data) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $result;
          // $res['mbp_available'] = $data_mbp;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }
  }
  public function getListRecomendationMbp(Request $request){

    $site_id = 2;

    $site_data = DB::table('site')->select('*')->where('site_id','=',$site_id)->get();
    $site_result = json_decode($site_data, true);
    
    $rtpo_id = 3;

    // $mbp_data = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','1')->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);

    foreach ($result as $param => $row) {

      $lat1=$site_result[0]['latitude'].'';
      $lon1=$site_result[0]['longitude'].'';
      
      $lat2=$result[$param]['latitude'].'';
      $lon2=$result[$param]['longitude'].'';

      $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      $google_api = json_decode($dataJson, true);

      $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_mbp[$param]['mbp_id'] = $result[$param]['mbp_id'];
      $tmp_mbp[$param]['mbp_name'] = $result[$param]['mbp_name'];
      $tmp_mbp[$param]['status'] = $result[$param]['status'];
      $tmp_mbp[$param]['latitude'] = $result[$param]['latitude'];
      $tmp_mbp[$param]['longitude'] = $result[$param]['longitude'];
      $tmp_mbp[$param]['distance'] = $distance[$param];
      $tmp_mbp[$param]['duration'] = $duration[$param];

    }

    array_multisort($duration, SORT_ASC, $tmp_mbp);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_mbp;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }
  }
  public function getListDistanceRecomendationSite(Request $request){

    $rtpo_id = 3;

    $site_data = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','1')->get();
    $site_result = json_decode($site_data, true);

    $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)->where('status','=','1')->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);

    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      $google_api = json_decode($dataJson, true);

      $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];

    }

    array_multisort($duration, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }
  }
  public function getSiteTerdekatDariMbp(Request $request){


    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);


      // $rtpo_id = 3;
    
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();

    $site_result = json_decode($site_data, true);
    if ($site_result==null) {
      # code...

      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      
      return response($res);
    }

    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      $google_api = json_decode($dataJson, true);

      $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

    }

    array_multisort($distancevalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }
    // getSiteTercepatDariMbp
  public function getSiteTercepatDariMbp(Request $request){


    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);


      // $rtpo_id = 3;
    
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();

    $site_result = json_decode($site_data, true);


    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      $google_api = json_decode($dataJson, true);

      $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

    }

    array_multisort($durationvalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }
   // getSiteClassTertinggiDariMbpTercepat

    // getSiteClassTertinggiDariMbpTerdekat
  public function getSiteClassTertinggiDariMbpTerdekat(Request $request){
    


    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();

    $site_result = json_decode($site_data, true);
    if ($site_result==null) {
      # code...

      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      
      return response($res);
    }

    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 1;
        $txtNode = 'Simpul';
      }else {
        $node[$param] = 0;
        $txtNode = 'Bukan Simpul';
      }

      $tmp_site[$param]['node'] = $txtNode;

    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    array_multisort(/*$node, SORT_DESC,*/ $distancevalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }    
  }

  // getSiteClassTertinggiDariMbpTercepat
  public function getSiteClassTertinggiDariMbpTercepat(Request $request){


    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();

    $site_result = json_decode($site_data, true);

    if ($site_result==null) {
      # code...

      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      
      return response($res);
    }

    // $res['data'] = $result;
    // $res['site_data'] = $site_data;

    // return response($res);

    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 1;
        $txtNode = 'Simpul';
      }else {
        $node[$param] = 0;
        $txtNode = 'Bukan Simpul';
      }

      $tmp_site[$param]['node'] = $txtNode;

    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    array_multisort(/*$node, SORT_DESC,*/ $distancevalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }// getSiteClassTertinggiDariMbpTercepat
  public function getSiteRecomendationClassandNode(Request $request){
    date_default_timezone_set("Asia/Jakarta");


    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);
    if ($result==null) {
      $res['success'] = false;
      $res['message'] = 'MBP_DATA_NOT_FOUND';
      return response($res);
    }


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->where('date_mainsfail','>',$date2)
    ->get();

    $site_result = json_decode($site_data, true);
    if ($site_result==null) {
      // $res['success'] = false;
      // $res['message'] = 'SITE_DATA_NOT_FOUND';
      // return response($res);


      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = $site_result;
      
      return response($res);
    }


    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] =strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 1;
        $txtNode = 'Simpul';
      }else {
        $node[$param] = 0;
        $txtNode = 'Bukan Simpul';
      }

      switch (strtolower($site_result[$param]['class_name'])) {
        case "platinum":
        $class[$param] = 4;
        break;
        case "gold":
        $class[$param] = 3;
        break;
        case "silver":
        $class[$param] = 2;
        break;
        case "bronze":
        $class[$param] = 1;
        break;
        default:
        $class[$param] = 0;
      }

      $tmp_site[$param]['node'] = $txtNode;

      // return response('class : '.$class[$param].'/n'.'node : '.$node[$param].'/n'.'distancevalue : '.$distancevalue[$param].'/n');
    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    array_multisort(/*$node, SORT_DESC,*/ $class, SORT_DESC, /*$distancevalue, SORT_ASC,*/ $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = array_slice($tmp_site, 0, 10, true);
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }
  public function getSiteRecomendationDistance(Request $request){

    date_default_timezone_set("Asia/Jakarta");

    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;


    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);
    if ($result==null) {
      $res['success'] = false;
      $res['message'] = 'MBP_DATA_NOT_FOUND';
      return response($res);
    }


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->where('date_mainsfail','>',$date2)
    ->get();

    $site_result = json_decode($site_data, true);
    if ($site_result==null) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = $site_result;
      return response($res);
    }


    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 1;
        $txtNode = 'Simpul';
      }else {
        $node[$param] = 0;
        $txtNode = 'Bukan Simpul';
      }

      switch (strtolower($site_result[$param]['class_name'])) {
        case "platinum":
        $class[$param] = 4;
        break;
        case "gold":
        $class[$param] = 3;
        break;
        case "silver":
        $class[$param] = 2;
        break;
        case "bronze":
        $class[$param] = 1;
        break;
        default:
        $class[$param] = 0;
      }

      $tmp_site[$param]['node'] = $txtNode;

      // return response('class : '.$class[$param].'/n'.'node : '.$node[$param].'/n'.'distancevalue : '.$distancevalue[$param].'/n');
    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    array_multisort(/*$node, SORT_DESC, $class, SORT_DESC, */$distancevalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = array_slice($tmp_site, 0, 10, true);
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }
  public function getETAandDistanceMbpToSite(Request $request){

    $mbp_id = $request->input('mbp_id');
    // $mbp_lat = $request->input('mbp_lat');
    // $mbp_lon = $request->input('mbp_lon');
    $site_lat = $request->input('site_lat');
    $site_lon = $request->input('site_lon');

    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp_id','=',$mbp_id)
    ->first();


    $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$mbp_data->latitude.",".$mbp_data->longitude."&destinations=".$site_lat.",".$site_lon."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
    
    $google_api = json_decode($dataJson, true);

      // return response($google_api);
    $data['distance'] = @$google_api['rows'][0]['elements'][0]['distance']['text'];
    $data['duration'] = @$google_api['rows'][0]['elements'][0]['duration']['text'];

    if ($google_api!=null) {
      if ($data['distance']==null) {
        
        $data['distance'] = '-';
        $data['duration'] = '-';
      }
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);
    }else{
      // $res['success'] = false;
      // $res['message'] = 'FAILED_GET_ETA_AND_DISTANCE';

      $data['distance'] = '-';
      $data['duration'] = '-';
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);      
    }
  }
    // getSiteClassTertinggiDariMbpTerdekat
  public function getSiteDariMbpTerdekat(Request $request){


    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();

    $site_result = json_decode($site_data, true);

    if ($site_result==null) {
      # code...

      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      
      return response($res);
    }
    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      $google_api = json_decode($dataJson, true);

      $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      $revenuevalue[$param] = $site_result[$param]['revenue'];

      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

    }

    array_multisort(/*$revenuevalue, SORT_DESC*//*, $durationvalue, SORT_ASC,*/$distancevalue, SORT_ASC, $tmp_site);

    if ($site_data) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }

   public function getMbpDariSiteTerdekat(Request $request){

    $site_id = $request->input('site_id');

    $site_data = DB::table('site')
    ->select('site.site_id','site.site_name','site.status','site.rtpo_id', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    // ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('site_id','=',$site_id)
    // ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->get();
    $site_result = json_decode($site_data, true);

    if (!$site_result) {

      $res['success'] = false;
      $res['message'] = 'site terboking';
      return response($res);
    }

    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.rtpo_id','=',$site_result[0]['rtpo_id'])
    ->where('mbp.status','=','AVAILABLE')
    ->where('mbp.active','=','1')
    ->get();
    $result = json_decode($mbp_data, true);
    

    foreach ($result as $param => $row) {

      $lat1=$result[$param]['latitude'].'';
      $lon1=$result[$param]['longitude'].'';
      
      $lat2=$site_result[0]['latitude'].'';
      $lon2=$site_result[0]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp[$param]['mbp_id'] = $result[$param]['mbp_id'];
      $tmp[$param]['driver'] = $result[$param]['user_id'];
      $tmp[$param]['distance'] =  round($get_distance,2).' km';
      $tmp[$param]['status'] = $result[$param]['status'];

      // $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      // $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      // $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      // $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      // $tmp_site[$param]['status'] = $site_result[$param]['status'];
      // $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      // $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      // $tmp_site[$param]['distance'] = $distance[$param];
      // $tmp_site[$param]['duration'] = $duration[$param];
      // $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      // $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      // if ($site_result[$param]['node']=='1') {
      //   $node[$param] = 1;
      //   $txtNode = 'Simpul';
      // }else {
      //   $node[$param] = 0;
      //   $txtNode = 'Bukan Simpul';
      // }

      // $tmp_site[$param]['node'] = $txtNode;

    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    // array_multisort(/*$node, SORT_DESC,*/ $distancevalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }    
  }


  public function localCalculateDistance($latitude1,$longitude1,$latitude2,$longitude2 ){

    $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$latitude1.",".$longitude1."&destinations=".$latitude2.",".$longitude2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");

    $data = json_decode($dataJson,true);
    $data_traffic['distance'] = $data['rows'][0]['elements'][0]['distance']['text'];
    $data_traffic['duration'] = $data['rows'][0]['elements'][0]['duration']['text'];
    $duration = $data['rows'][0]['elements'][0]['duration']['text'];
    
    return ($duration);
  }
  public function distance($lat1, $lon1, $lat2, $lon2, $unit) {

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }

  public function getSiteRecomendationDistancePaginate(Request $request){

    date_default_timezone_set("Asia/Jakarta");

    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);
    if ($result==null) {
      $res['success'] = false;
      $res['message'] = 'MBP_DATA_NOT_FOUND';
      return response($res);
    }


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->where('date_mainsfail','>',$date2)
    ->offset($offset)
    ->limit($limit)
    ->get();

    $site_result = json_decode($site_data, true);
    if ($site_result==null) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = $site_result;
      return response($res);
    }


    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 1;
        $txtNode = 'Simpul';
      }else {
        $node[$param] = 0;
        $txtNode = 'Bukan Simpul';
      }

      switch (strtolower($site_result[$param]['class_name'])) {
        case "platinum":
        $class[$param] = 4;
        break;
        case "gold":
        $class[$param] = 3;
        break;
        case "silver":
        $class[$param] = 2;
        break;
        case "bronze":
        $class[$param] = 1;
        break;
        default:
        $class[$param] = 0;
      }

      $tmp_site[$param]['node'] = $txtNode;

      // return response('class : '.$class[$param].'/n'.'node : '.$node[$param].'/n'.'distancevalue : '.$distancevalue[$param].'/n');
    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    array_multisort(/*$node, SORT_DESC, $class, SORT_DESC, */$distancevalue, SORT_ASC, $tmp_site);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = array_slice($tmp_site, 0, 10, true);
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }

  public function getSiteRecomendationClassandNodePaginate(Request $request){
    date_default_timezone_set("Asia/Jakarta");


    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $mbp_id = $request->input('mbp_id');
      // $mbp_id = 2;

    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    // $mbp_data = DB::table('mbp')->select('*')->where('mbp_id','=',$mbp_id)/*->where('status','=','1')*/->get();
    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->get();

    $result = json_decode($mbp_data, true);
    if ($result==null) {
      $res['success'] = false;
      $res['message'] = 'MBP_DATA_NOT_FOUND';
      return response($res);
    }


      // $rtpo_id = 3; 
    $site_data = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', 'site.site_class_revenue as revenue','site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$result[0]['rtpo_id'])
    ->where('status','=','0')
    ->where('is_allocated','=','0')
    ->where('date_mainsfail','>',$date2)
    ->offset($offset)
    ->limit($limit)
    ->get();

    $site_result = json_decode($site_data, true);
    if ($site_result==null) {
      // $res['success'] = false;
      // $res['message'] = 'SITE_DATA_NOT_FOUND';
      // return response($res);


      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = $site_result;
      
      return response($res);
    }


    foreach ($site_result as $param => $row) {

      $lat1=$result[0]['latitude'].'';
      $lon1=$result[0]['longitude'].'';
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';

      // $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$lat1.",".$lon1."&destinations=".$lat2.",".$lon2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");
      // $google_api = json_decode($dataJson, true);

      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      // $distance[$param] = $google_api['rows'][0]['elements'][0]['distance']['text'];
      // $duration[$param] = $google_api['rows'][0]['elements'][0]['duration']['text'];
      // $distancevalue[$param] = $google_api['rows'][0]['elements'][0]['distance']['value'];
      // $durationvalue[$param] = $google_api['rows'][0]['elements'][0]['duration']['value'];
      // $revenuevalue[$param] = $site_result[$param]['revenue'];
      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = $get_distance;
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] =strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = $result[0]['mbp_name'];
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = $duration[$param];
      $tmp_site[$param]['distancevalue'] = $distancevalue[$param];
      $tmp_site[$param]['durationvalue'] = $durationvalue[$param];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 1;
        $txtNode = 'Simpul';
      }else {
        $node[$param] = 0;
        $txtNode = 'Bukan Simpul';
      }

      switch (strtolower($site_result[$param]['class_name'])) {
        case "diamond":
        $class[$param] = 5;
        break;
        case "platinum":
        $class[$param] = 4;
        break;
        case "gold":
        $class[$param] = 3;
        break;
        case "silver":
        $class[$param] = 2;
        break;
        case "bronze":
        $class[$param] = 1;
        break;
        default:
        $class[$param] = 0;
      }

      $tmp_site[$param]['node'] = $txtNode;

      // return response('class : '.$class[$param].'/n'.'node : '.$node[$param].'/n'.'distancevalue : '.$distancevalue[$param].'/n');
    }

    // array_multisort($revenuevalue, SORT_DESC, $durationvalue, SORT_ASC,/*$distancevalue, SORT_ASC,*/ $tmp_site);
    array_multisort(/*$node, SORT_DESC,*/ $class, SORT_DESC, /*$distancevalue, SORT_ASC,*/ $tmp_site, SORT_ASC);

    if (true) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      // $res['data'] = $tmp_site;
      $res['data'] = array_slice($tmp_site, 0, 10, true);
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find route!';
      
      return response($res);
    }  
  }
  
}