<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class SiteControllerNew extends Controller
{


  public function update_report_location(Request $request){

    $master_report_data = DB::table('report_location_site')
    ->select('*')
    // ->where('delivery_date','>', '2018-10-01')
    // ->where('distance','=', null)
    ->get();

    $result = json_decode($master_report_data, true);
    if ($result==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_DATA_NOT_FOUND';
      return response($res);
    }

    $x=0;
    foreach ($result as $param => $row) {

      $site_data = DB::table('site')
      ->select('*')
      ->where('site_id','=',$row['site_id'])
      ->first();

      if ($site_data!=null) {
        $get_distance =round(@$this->distance($row['new_lat'], $row['new_lon'], @$site_data->latitude, @$site_data->longitude, 'K'), 3) ;

        if (@$get_distance<1) {
          $res['success'] = false;
          $res['message'] = 'JARAK_SITE_KURANG_DARI_1_KM';
          return response($res);
        }

        $res[$x]['report_id'] = $row['report_id'];
        $res[$x]['site_id'] = $row['site_id'];
        $res[$x]['delivery_date'] = $row['delivery_date'];
        $res[$x]['get_distance'] = $get_distance;
        $res[$x]['user_latitude'] = $row['new_lat'];
        $res[$x]['user_longitude'] = $row['new_lon'];
        $res[$x]['latitude'] = @$site_data->latitude;
        $res[$x]['longitude'] = @$site_data->longitude;
        $res[$x]['rtpo'] = @$site_data->rtpo;
        $res[$x]['regional'] = @$site_data->regional;
        
        $editSite = DB::table('report_location_site')
        ->where('report_id','=',$row['report_id'])
        ->update(
          [
            'old_lat' => @$site_data->latitude,
            'old_lon' => @$site_data->longitude,
            'distance' => @$get_distance,
            'rtpo' => @$site_data->rtpo,
            'regional' => @$site_data->regional,
          ]
        );
      }
      $x=$x+1;      
    }
    return response($res);
  }


  public function get_site_name(Request $request){
    $site_id = $request->input('site_id'); 

    $_site = DB::table('site')
    ->select('site_name')
    ->where('site_id',$site_id)
    ->first();
    if (@$_site->site_name==null) {

      $res['success'] = false;
      $res['message'] = 'Data Not Found';
      return response($res);
    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $_site->site_name;
    return response($res);
  }

  public function getMaintenanceOTP(Request $request){

    # masukan parameter phone nya
    // $phone = $request->input('phone');
    # masukan parameter username nya
    // $username = $request->input('username');
    # masukan parameter sik_no nya
    $sik_no = $request->input('sik_no');
    $username = $request->input('username');
    $input_secret_code = $request->input('secret_code');
    $user_type = $request->input('user_type');
    $maintenance_status=1;

    # cek apakah data dengan phone, username, sik_no di atas ada di tabel "sik_site" ?
      # bila ada maka ambil otp_induknya dan site_idnya
    $sik_data = DB::table('sik_site as ss')
    ->select('*')
    ->where('sik_no','=',$sik_no)
    ->first();


    if ($sik_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_SIK_NOT_FOUND';
      return response($res);
    }


    if ($sik_data->otp_id=='') {
      $res['success'] = false;
      $res['message'] = 'FAILED_OTP_NULL';
      return response($res);
    }

    if ($sik_data->otp_id==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_OTP_NULL';
      return response($res);
    }


    if ($user_type!=null) {
      if ($user_type=='3') { //member
        # code... input secret code and check
        $check_secret_code = DB::table('secret_code_maintenance')
        ->select('*')
        ->where('secret_code','=',$input_secret_code)
        ->first();

        if ($check_secret_code==null) {
          $maintenance_status=0;
        }else{
          $maintenance_status=3;
        }
          

      }elseif ($user_type=='2') { //leader
        # code... create secret code
        $secretcode = md5($sik_no.''.$username.''.$sik_data->otp_id);
        $secretcode = substr($secretcode, 0, 6);
        $insertsecretcode = DB::table('secret_code_maintenance')
        ->insert(
          [
            'sik' => $sik_no,
            'username_leader' => $username,
            'secret_code' => $secretcode,
          ]
        );
        if (!$insertsecretcode) {
          $secretcode=null;
          $maintenance_status=0;
        }else{
          $maintenance_status=2;
        }

      }else{ //solo
        # code...
      }
    }

    $site_data = DB::table('site as ms')
    ->select('*')
    ->where('site_id','=',$sik_data->site_id)
    ->first();

    $revision_data = DB::table('sik_site as ss')
    ->select('*')
    ->where('ss.sik_no','=',$sik_no)
    ->where('ss.mt_status','=',1)
    ->first();

    if ($revision_data) {
      $maintenance_status = 3;
    }

    $data['otp'] = $sik_data->otp_id;
    $data['site_id'] = $sik_data->site_id;
    $data['site_name'] = $sik_data->site_name;
    $data['sik_number'] = $sik_data->sik_no;
    $data['kriteria_site'] = @$site_data->kriteria_site[0];

    $data['secret_code'] = @$secretcode;
    $data['maintenance_status'] = @$maintenance_status;

    if ("C2"==@$site_data->kriteria_site) {
      $data['kriteria_site'] = @$site_data->kriteria_site;
    }
    // $data['kriteria_site'] = $data['kriteria_site'][0];
    if ($data['kriteria_site']==null) {
      $data['kriteria_site'] = "C";
    }
    

    # return otp_induk
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);
  }

  public function checkReportToLogin(Request $request){

    $username = $request->input('username');
    $sik_no = $request->input('sik_no');

    $sik_data = DB::table('sik_site')
    ->select('*')
    ->where('sik_no','=',$sik_no)
    ->first();
    
      // $res['success'] = false;
      // $res['message'] = 'FAILED_SIK_NOT_FOUND';
      // return response($res);

    if ($sik_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_SIK_NOT_FOUND';
      return response($res);
    }

    # cek apakah ada report where username = username dia, site_id = site_id sik dia dan statusnya masih null
    $report_data = DB::table('report_location_site')
    ->select('*')
    ->where('send_by','=',$username)
    ->where('sik_no','=',$sik_no)
    // ->where('approval','=',1)
    ->first();

    if ($report_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_REPORT_NOT_FOUND_OR_IS_NOT_APPROVED';
      $data['site_id from sik'] = $sik_data->site_id;
      $data['username'] = $username;
      $res['data'] = $data;
      return response($res);
    }

    if ($report_data->approval==0) {
      $tmp_status = 'REJECTED';
    }elseif ($report_data->approval==1) {
      $tmp_status = 'APPROVED';
    }else{
      $tmp_status = 'WAITING';  
    }

    $data['status'] = $tmp_status;
    $data['status int'] = $report_data->approval;
    $data['site_id from sik'] = $sik_data->site_id;

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);
  }
  
  public function getAllSite(Request $request){

    $data_site = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude')
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
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude')
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
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status','site.kriteria_site', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude')
    ->where('rtpo_id','=',$rtpo_id)
    ->get();


            // "site_id": "JBR203",
            // "site_name": "AMBULU3PTI",
            // "class_name": "silver",
            // "mbp_name": "",
            // "status": 0,
            // "latitude": -8.33081,
            // "longitude": 113.60972,
            // "distance": "12602.86 km",
            // "duration": "",
            // "distancevalue": "",
            // "durationvalue": "",
            // "node": "Bukan Simpul"


            // "site_id": "BDO001",
            // "site_name": "BONDOWOSO",
            // "status": 0,
            // "class_name": "PLATINUM",
            // "latitude": -7.91539,
            // "longitude": 113.823

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
  public function getMySiteAll(Request $request){

    $rtpo_id = $request->input('rtpo_id');
    $mbp_id = $request->input('mbp_id');

      // $data_site = DB::table('site')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','0')->get();

    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp.mbp_id','=',$mbp_id)
    // ->where('status','=','1')
    ->first();
    if ($mbp_data==null) {
      $lat1 = 0;
      $lon1 = 0;
    }else{
      $lat1 = $mbp_data->latitude;
      $lon1 = $mbp_data->longitude;
    }

    $data_site = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('is_allocated','=','0')
    ->orderBy('site_name', 'asc')
    ->get();


    $site_result = json_decode($data_site, true);
    if ($site_result==null) {
      // $res['success'] = false;
      // $res['message'] = 'SITE_DATA_NOT_FOUND';
      // return response($res);
      
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      return response($res);
    }
    
    foreach ($site_result as $param => $row) {

      // $lat1=$result[0]['latitude'].'';
      // $lon1=$result[0]['longitude'].'';
      
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
      $distancevalue[$param] = /*$get_distance*/'';
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = /*$result[0]['mbp_name']*/'';
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = /*$duration[$param]*/'';
      $tmp_site[$param]['distancevalue'] = /*$distancevalue[$param]*/'';
      $tmp_site[$param]['durationvalue'] = /*$durationvalue[$param]*/'';

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 'Simpul';
      }else {
        $node[$param] = 'Bukan Simpul';
      }

      // switch ($site_result[$param]['class_name']) {
      //   case "Platinum":
      //   $class[$param] = 4;
      //   break;
      //   case "Gold":
      //   $class[$param] = 3;
      //   break;
      //   case "Silver":
      //   $class[$param] = 2;
      //   break;
      //   case "Bronze":
      //   $class[$param] = 1;
      //   break;
      //   default:
      //   $class[$param] = 0;
      // }

      $tmp_site[$param]['node'] = $node[$param];

      // // return response('class : '.$class[$param].'/n'.'node : '.$node[$param].'/n'.'distancevalue : '.$distancevalue[$param].'/n');
    }


    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $polys['success'] = false;
      $polys['message'] = 'Cannot find polys!';
      
      return response($btss);
    }
  }
  public function getMySiteDown(Request $request){


    $rtpo_id = $request->input('rtpo_id');
    $mbp_id = $request->input('mbp_id');

    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();
    if ($mbp_data==null) {
      $lat1 = 0;
      $lon1 = 0;
    }else{
      $lat1 = $mbp_data->latitude;
      $lon1 = $mbp_data->longitude;
    }

    $data_site = DB::table('site')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude','site.node')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('is_allocated','=','0')
    ->orderBy('site_name', 'asc')
    ->get();


    $site_result = json_decode($data_site, true);
    if ($site_result==null) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      return response($res);
    }
    
    foreach ($site_result as $param => $row) {
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';


      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = /*$get_distance*/'';
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = /*$result[0]['mbp_name']*/'';
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = /*$duration[$param]*/'';
      $tmp_site[$param]['distancevalue'] = /*$distancevalue[$param]*/'';
      $tmp_site[$param]['durationvalue'] = /*$durationvalue[$param]*/'';

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 'Simpul';
      }else {
        $node[$param] = 'Bukan Simpul';
      }


      $tmp_site[$param]['node'] = $node[$param];

    }


    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $polys['success'] = false;
      $polys['message'] = 'Cannot find polys!';
      
      return response($btss);
    }
  }

  public function getMySiteCorrective(Request $request){


    $rtpo_id = $request->input('rtpo_id');
    $mbp_id = $request->input('mbp_id');
    $page = $request->input('page');
    $search = $request->input('search');

    //$limit = 20;
    //$offset = ($page-1)*$limit;

    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();
    if ($mbp_data==null) {
      $lat1 = 0;
      $lon1 = 0;
    }else{
      $lat1 = $mbp_data->latitude;
      $lon1 = $mbp_data->longitude;
    }

    if ($search=='' || $search==null){
      $data_site = DB::table('site')
      ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude','site.node','site.alarm')
      ->where('rtpo_id','=',$rtpo_id)
      ->orderBy('site_name', 'asc')
      //->offset($offset)
      //->limit($limit)
      ->get();
    } else{
      $data_site = DB::table('site')
      ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude','site.node','site.alarm')
      ->whereraw('rtpo_id = '.$rtpo_id.' and (site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
      ->orderBy('site_name', 'asc')
      //->limit(20)
      ->get();
    }


    $site_result = json_decode($data_site, true);
    if ($site_result==null) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      return response($res);
    }
    
    foreach ($site_result as $param => $row) {
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';


      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = /*$get_distance*/'';
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = /*$result[0]['mbp_name']*/'';
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = /*$duration[$param]*/'';
      $tmp_site[$param]['distancevalue'] = /*$distancevalue[$param]*/'';
      $tmp_site[$param]['durationvalue'] = /*$durationvalue[$param]*/'';
      $tmp_site[$param]['alarm'] = $site_result[$param]['alarm'];

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 'Simpul';
      }else {
        $node[$param] = 'Bukan Simpul';
      }


      $tmp_site[$param]['node'] = $node[$param];

    }


    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $polys['success'] = false;
      $polys['message'] = 'Cannot find polys!';
      
      return response($btss);
    }
  }
  public function setSiteMainsFail(Request $request){

    date_default_timezone_set("Asia/Jakarta");

    $site_id = $request->input('site_id');
    $site_status = $request->input('site_status');

    $data_site = DB::table('site')
    ->join('rtpo','site.rtpo_id','rtpo.rtpo_id')
    ->select('site.site_id','rtpo.rtpo_id','rtpo.rtpo_name','site.site_name','site.status','site.latitude','site.longitude','site.node')
    ->where('site.site_id','=',$site_id)
    ->first();

    if ($data_site->status == $site_status) {
      $res['success'] = false;
      $res['message'] = 'STATUS_SITE_SAME';
      return response($res);
    }

    if ($data_site) {

      if ($site_status==0) {
        $editSite = DB::table('site')
        ->where('site.site_id','=',$site_id)
        ->update(
          [
            'status' => $site_status,
            'date_mainsfail' =>  date('Y-m-d H:i:s')
          ]
        );
      }else{
        $editSite = DB::table('site')
        ->where('site.site_id','=',$site_id)
        ->update(
          [
            'status' => $site_status,
            // 'date_mainsfail' =>  date('Y-m-d H:i:s')
          ]
        );
      }

      if ($editSite) {

        if ($site_status == 0) {
          # Tampilkan Notifikasi bila site 'MainsFail' ke RTPO terkait site tersebut

        $notificationController = new NotificationController;
        $topic = '/topics/'.$notificationController->checkMyRTPOtopic($data_site->rtpo_name);

                                                      // $title,$body,$to_token_id,$type_name, $type_id,$type
        $tmp = $notificationController->sendNotifFast('Site MainsFail','Site '.$data_site->site_name.' mengalami MainsFail',$topic,'','','SITE_MAINSFAIL');

          if (/* notifikasi sudah selesai maka tampilkan sukses*/ true) {
            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            // $res['status'] = 'down';
            $res['data'] = $data_site;
            return response($res);
          }
        }

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data_site;
        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'UPDATE_DATA_SITE_FAILED';
        return response($res);
      }

    }else{
      $res['success'] = false;
      $res['message'] = 'DATA_SITE_NOT_FOUND';
      return response($res);
    }
    // cocokan apakah nama site name itu ada?
    // bila ada maka update dan kirim ke tabel notif
    //   dlam tabel notif akan di kirim notif ke RTPO_PROB/ sesuai rtponya..:D
  }

  //--------------------------------------- report bila ada -------------------------------------

  public function sendNewLocSite(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');
    $date_report_id = date('ymd-His');

    $send_by = $request->input('send_by');
    $sik_no = $request->input('sik_no');
    $new_lat = $request->input('new_lat');
    $new_lon = $request->input('new_lon');
    $device_acuration = $request->input('device_acuration');
    $delivery_date = $date_now;
    $report_id = "RPT".$date_report_id;
    $site_id_tmp = explode("/",$sik_no);
    $site_id = $site_id_tmp[1];

    if (substr($sik_no, 0,3) =='GS/') {
      $spk_data = DB::table('spk_sparepart')
      ->select('*')
      ->where('spk_no',$sik_no)
      ->first();

      $site_id = $spk_data->site_id;
    }

    #(M) chek apakh sik ada di tabel "sik_site"? bila ada maka ambil rtpo_id dan site_id nya
    $sik_data = DB::table('site')
    ->select('*')
    ->where('site_id','=',$site_id)
    ->first();   

    if ($sik_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_SIK_NOT_FOUND';
      return response($res);
    }

    $report_data = DB::table('report_location_site')
    ->select('*')
    ->where('send_by','=',$send_by)
    ->where('sik_no','=',$sik_no)
    ->first();

    $chepApprovaldata = DB::table('report_location_site')
    ->select('*')
    ->where('approval','=',1)
    ->where('sik_no','=',$sik_no)
    ->orderBy('delivery_date','asc')
    ->first();

    $tmp_approval = 5;
    $responseBy = null;
    if (@$chepApprovaldata != null) {
      $tmp_approval = $chepApprovaldata->approval;
                    // $kalimat="Sedang serius belajar PHP di duniailkom";
      $posisi=strpos($chepApprovaldata->respon_by,"approval");
      if ($posisi !== FALSE){
                      // echo "Ketemu";
        $responseBy = @$chepApprovaldata->respon_by;
      }
      else {
                      // echo "Tidak ketemu";
        $responseBy = 'approval by system based on '.@$chepApprovaldata->respon_by.' approval data';
      }
      
    }
    // if ($report_data->report_id == null) {
    //                 // insert
    //   $query = mysqli_query($con, "INSERT INTO `report_location_site` 
    //     
    //(`report_id`, `send_by`, `sik_no`, `new_lat`, `new_lon`, `rtpo_id`, `site_id`, `approval`, `device_acuration`, `delivery_date`,`base_url`,`fname`,`respon_by`) 
    //     VALUES 
    //     
    //('$report_id','$send_by','$sik_no','$new_lat','$new_lon','$rtpo_id','$site_id','$tmp_approval','$device_acuration','$delivery_date','http://103.253.107.45/semeru-api/maintenance/images_relocation_site/','$pname','$responseBy')");

    // }else{
    //                 // update
    //   $query = mysqli_query($con, "UPDATE `report_location_site` SET 
    //     `report_id`='$report_id', `new_lat`='$new_lat', `new_lon`='$new_lon', `rtpo_id`='$rtpo_id', `site_id`='$site_id', `approval`='$tmp_approval', `device_acuration`='$device_acuration', `delivery_date`='$delivery_date',`fname`='$pname',`respon_by`='$responseBy' WHERE `send_by`='$send_by' AND `sik_no`='$sik_no'");
    // }


    if ($report_data == null) {
      $sendReport = DB::table('report_location_site')->insert(
        [
          'report_id' => $report_id, 
          'send_by' => $send_by,
          'sik_no' => $sik_no,
          'new_lat' => $new_lat,
          'new_lon' => $new_lon,

        #(M) masukan rtpo_id nya
          'rtpo_id' => $sik_data->rtpo_id,
          'rtpo' => @$sik_data->rtpo,
        #(M) masukan site_id nya
          'site_id' => $sik_data->site_id,
          'approval' => $tmp_approval,//-------------------------- nnti di ganti 5 ketika user rtpo siap

          'device_acuration' => $device_acuration,
          'delivery_date' => $delivery_date,
          'respon_by' => @$responseBy,
          'is_offline' => 1,
        ]
      );
    }else{

      $sendReport = DB::table('report_location_site')
      ->where('send_by','=',$send_by)
      ->where('sik_no','=',$sik_no)
      ->update(
        [
          'report_id' => $report_id, 
          'new_lat' => $new_lat,
          'new_lon' => $new_lon,

        #(M) masukan rtpo_id nya
          'rtpo_id' => $sik_data->rtpo_id,
          'rtpo' => @$sik_data->rtpo,
        #(M) masukan site_id nya
          'site_id' => $sik_data->site_id,
          'approval' => $tmp_approval,//-------------------------- nnti di ganti 5 ketika user rtpo siap

          'device_acuration' => $device_acuration,
          'delivery_date' => $delivery_date,
          'respon_by' => @$responseBy,
          'is_offline' => 1,
        ]
      );

    }

    if ($tmp_approval==1) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return response($res);
    }
    if ($sendReport) {


      //  ()> $type       : NEW_LOCATION_SITE_FROM_FMC
      //  ()> $type_id    : 
      //  ()> $type_name  : report_id
      //  ()> $to_token_id: #toRTPOid
      //  ()> $body       : (user fmc ...) dari (fmc ...) menyatakan bahwa koordinat sebenarnya dari (site ...). 
      //  ()> $title      : Permintaan Update Lokasi Site

      $users_data = DB::table('users')
      ->join('user_rtpo','users.username','user_rtpo.username')
      ->select('*')
      ->where('rtpo_id','=',$sik_data->rtpo_id)
      ->get();

      $notificationController = new NotificationController;


      $result = json_decode($users_data, true);

      $to_token_id = array();
      foreach ($result as $param => $row) {
        $tmp = $notificationController->setNotificationV1($send_by, $row['username'], 'NEW_LOCATION_SITE_FROM_FMC', 'report_id', $report_id, 'Permintaan Update Lokasi Site', 'NEW_LOCATION_SITE_FROM_FMC', $send_by.' menyatakan bahwa koordinat sebenarnya dari site '.$sik_data->site_name,0,'MAINTENANCE');

        array_push($to_token_id,$row['firebase_token']);
      }


      // $user_rtpo_data = mysqli_query($con, "SELECT * from users as u join user_rtpo as ur on u.username = ur.username
        // where ur.rtpo_id = '$rtpo_id' and firebase_token != ''");
                  // $rtporow = $user_rtpo_data->fetch_assoc();
                  // $fbt = $rtporow['firebase_token'];

      // $to_token_id = array();
      // while($row = $user_rtpo_data->fetch_assoc()){
      //   array_push($to_token_id,$row['firebase_token']);
      // }


      // $topic = '/topics/'.$notificationController->checkMyRTPOtopic($sik_data->rtpo);

      $fbc = new FireBaseController;
      $tmp_fb = $fbc->sendNotification('Permintaan Update Lokasi Site',$send_by.' menyatakan bahwa koordinat sebenarnya dari site '.$sik_data->site_name,$to_token_id,'report_id',$report_id,'NEW_LOCATION_SITE_FROM_FMC');

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return response($res);

    }else{

      $res['success'] = false;
      $res['message'] = 'FAILED';
      $res['data'] = $report_data;
      return response($res);
    }
  }

  public function listReportNewSite(Request $request){

    $rtpo_id = $request->input('rtpo_id');

    # get seluruh report where belum di approve dan rtpo_id = rtpo_id yang pengan lihat..:D
    # tampilakn site_id, username or nama yang mengajukan nama di ajukan pada tanggal..:D
    $report_data = DB::table('report_location_site as rls')
    ->join('site as s','rls.site_id','s.site_id')
    ->select('rls.send_by','rls.site_id','s.site_name','rls.sik_no','rls.device_acuration','rls.delivery_date','rls.report_id','rls.is_offline','rls.approval')
    ->where('rls.rtpo_id','=',$rtpo_id)
    // ->whereNotBetween('rls.approval', [1, 0])
    ->where('rls.approval','>',1)
    ->orderBy('rls.delivery_date','desc')
    ->get();

    # return all
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $report_data;
    return response($res);
  }

  public function listHistoryReportNewSite(Request $request){

    $rtpo_id = $request->input('rtpo_id');

    # get seluruh report where belum di approve dan rtpo_id = rtpo_id yang pengan lihat..:D
    # tampilakn site_id, username or nama yang mengajukan nama di ajukan pada tanggal..:D
    $report_data = DB::table('report_location_site as rls')
    ->join('site as s','rls.site_id','s.site_id')
    ->select('rls.send_by','rls.site_id','s.site_name','rls.sik_no','rls.device_acuration','rls.delivery_date','rls.report_id')
    ->where('rls.rtpo_id','=',$rtpo_id)
    ->where('rls.approval','=',1)
    ->orderBy('rls.respon_by_rtpo_at','desc')
    ->get();

    # return all
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $report_data;
    return response($res);
  }

  public function detail_report_site(Request $request){

    # masukan parameter report_id nya
    $report_id = $request->input('report_id');

    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    # return tampilkan semua datanya where repoert_id nya sama dengan yang diinputkan..:D
    $report_data = DB::table('report_location_site as rls')
    ->join('site as s','rls.site_id','s.site_id')
    ->select('rls.*', 's.latitude as old_lat', 's.longitude as old_lon', 'rls.base_url','s.site_name', 'rls.fname')
    ->where('rls.report_id','=',$report_id)
    ->offset($offset)
    ->limit($limit)
    ->get();

    if ($report_data == null) {
      $res['success'] = 'NOT OK';
      $res['message'] = 'FAILED_SIK_NOT_FOUND';
      // $res['data'] = $report_data;
      return response($res);
    }else{

      $data['report_id'] = $report_data->report_id;
      $data['is_offline'] = $report_data->is_offline;
      $data['send_by'] = $report_data->send_by;
      $data['delivery_date'] = $this->tanggal_bulan_tahun_indo($report_data->delivery_date); //$report_data->delivery_date;
      $data['site_id'] = $report_data->site_id;
      $data['site_name'] = $report_data->site_name;
      $data['sik_no'] = $report_data->sik_no;
      $data['rtpo_id'] = $report_data->rtpo_id;
      $data['new_lat'] = $report_data->new_lat;
      $data['new_lon'] = $report_data->new_lon;
      $data['device_acuration'] = $report_data->device_acuration;
      $data['approval'] = $report_data->approval;
      $data['respon_by'] = $report_data->respon_by;
      $data['respon_at'] = $report_data->respon_at;
      $data['last_update'] = $report_data->last_update;
      $data['is_sync'] = $report_data->is_sync;
      $data['last_sync'] = $report_data->last_sync;
      $data['id_sync'] = $report_data->id_sync;
      $data['old_lat'] = $report_data->old_lat;
      $data['old_lon'] = $report_data->old_lon;
      $data['image_url'] = @$report_data->base_url."".$report_data->fname;

      $res['success'] = 'OK';
      $res['message'] = 'Success';
      // $res['data'] = $report_data;
      $res['data'] = $data;
      return response($res);
    }
  }


  public function checkReportSite(Request $request){
    // $x=['data'=>'joos'];
    // return $x;
    # masukan parameter report_id nya
    $sik_no = @$request->input('sik_no');
    $site_id_tmp = $request->input('site_id');
    $username = $request->input('username');
    $latitude = @$request->input('latitude');
    $longitude = @$request->input('longitude');
    $sik_split = explode("/",$sik_no);
    $site_id = @$sik_split[1];
    if ($site_id_tmp!=null) {
      $site_id = $site_id_tmp;

      // $data['site_aya'] = "yes";
    }

    //DISINI DI CEK DENGANDATA SITE TERBARU
    $checksite = DB::table('site')
    ->select('site_id','latitude','longitude')
    ->where('site_id','=',$site_id)
    ->first();
    $jaraks = @$this->distance($latitude, $longitude, @$checksite->latitude, @$checksite->longitude, "K");

    if (@$jaraks<1) {
      $res['success'] = true;
      $res['message'] = 'success site';


      $data['approval'] = 1;
      $data['desc_approval'] = "APPROVED";
      
      $data['sik_no'] = @$sik_no;
      $data['rtpo_id'] = @$report_data->rtpo_id;
      $data['new_lat'] = @$checksite->latitude;
      $data['new_lon'] = @$checksite->longitude;

      $res['data'] =$data;
      $res['jarak1'] =$jaraks . " km";
      return response($res); 
    }



    # return tampilkan semua datanya where site_id nya sama dengan yang diinputkan..:D    
    $report_data = DB::table('report_location_site as rls')
    ->join('site as s','rls.site_id','s.site_id')
    ->select('rls.*', 's.latitude as old_lat', 's.longitude as old_lon', 'rls.base_url', 'rls.fname')
    ->where('rls.site_id','=',$site_id)
    ->where('rls.approval','=',1)
    ->orderBy('rls.respon_by_rtpo_at','=','asc')
    ->first();

    if ($report_data == null) {
      // $res['success'] = false;
      // $res['message'] = 'FAILED_SIK_NOT_FOUND';
      // // $res['data'] = $report_data;
      // return response($res);

      $data['approval'] = 0;
      $data['desc_approval'] = "NOT FOUND";
      
      $data['sik_no'] = @$report_data->sik_no;
      $data['rtpo_id'] = @$report_data->rtpo_id;
      $data['new_lat'] = @$report_data->new_lat;
      $data['new_lon'] = @$report_data->new_lon;
      // $data['device_acuration'] = $report_data->device_acuration;
    }else{

      // $data['report_id'] = $report_data->report_id;
      // $data['send_by'] = $report_data->send_by;
      // $data['delivery_date'] = $report_data->delivery_date;
      // $data['site_id'] = $report_data->site_id;

      $data['approval'] = @$report_data->approval;
      if ($data['approval']==5) {
        $data['desc_approval'] = "NOT APPROVED";
      }else{

        $data['desc_approval'] = "APPROVED";
        //============== cek apakah jarak antara user dengan data site yang di approve rtpo < 1km ?
        $get_distance = $this->distance($latitude, $longitude, @$report_data->new_lat, @$report_data->new_lon, 'K');
        if ($get_distance>1) {
          $data['desc_approval'] = "NOT APPROVED";
        }

        $data['jarak'] = $get_distance;
      }
      $data['sik_no'] = @$report_data->sik_no;
      $data['rtpo_id'] = @$report_data->rtpo_id;
      $data['new_lat'] = @$report_data->new_lat;
      $data['new_lon'] = @$report_data->new_lon;
      // $data['device_acuration'] = $report_data->device_acuration;
      $data['respon_by'] = @$report_data->respon_by;
      // $data['respon_at'] = $report_data->respon_at;
      // $data['last_update'] = $report_data->last_update;
      // $data['is_sync'] = $report_data->is_sync;
      // $data['last_sync'] = $report_data->last_sync;
      // $data['id_sync'] = $report_data->id_sync;
      // $data['old_lat'] = $report_data->old_lat;
      // $data['old_lon'] = $report_data->old_lon;
      // $data['image_url'] = @$report_data->base_url."".$report_data->fname;

    }
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);
  }

  public function approve_report_new_loc_site(Request $request){
    
    date_default_timezone_set("Asia/Jakarta");
    $date_now = date('Y-m-d H:i:s');

    # masukan parameter report_id nya
    $report_id = $request->input('report_id');
    # masukan parameter username_rtpo sebagai respon_by
    $username_rtpo = $request->input('username_rtpo');

    $approval = $request->input('approval');

    # mengupdate tabel report_location_site 
      # menambahkan respon_by dan apakah ini di approve atau tidak?
        #(+) bila di approve (= 1) maka dia bisa login ke menu maintenance dan bisa melakukan maintenance
        #(-) bila tidak maka (= 0 / null) maka dia tidak bisa melakukan maintenance (harap di approve sebagai laporan kami tentang lokasi site sebenarnya)
    if ($approval == 1) { // di approve
      $status = 1;
    }else if ($approval == 0) { //tidak di approve
      $status = 0;
    }else{
      $status = null;
    }


    // $xx = DB::table('report_location_site')
    // ->select('*')
    // ->where('report_id','=',$report_id)
    // ->get();

    // $dt['report_id']=$report_id;
    // $dt['approve_by']=$username_rtpo;
    // $dt['approval']=$approval;
    // $dt['return']=$xx;

    // return response($dt);
    // exit();


    $updateReport = DB::table('report_location_site')
    ->where('report_id','=',$report_id)
    ->update(
      [
        'respon_by' => @$username_rtpo,
        'approval' => @$status,
        'respon_at' => @$date_now,
        'respon_by_rtpo_at' => @$date_now,
        'approval_by_rtpo'=>'1'
      ]
    );

    # return semua data pada report_id tersebut bawha sudah di approve
    if ($updateReport) {

      $report_data = DB::table('report_location_site')
      ->join('users','report_location_site.send_by','users.username')
      ->select('*')
      ->where('report_id','=',$report_id)
      ->first();

      $notificationController = new NotificationController;

      if ($approval == 1) {

        $tmp = $notificationController->setNotificationV1($username_rtpo, $report_data->send_by, 'NEW_LOCATION_SITE_APPROVED_FROM_RTPO', 'report_id', $report_id, 'Pengajuan koordinat site anda telah disetujui', 'NEW_LOCATION_SITE_APPROVED_FROM_RTPO', $username_rtpo.' menyatakan bahwa pengauan koordinat anda telah disetujui.',0,'MAINTENANCE');

        // $topic = '/topics/'.$this->checkMyFMCtopic($data['fmc_id']);
        $notificationController->sendNotifFast('Pengajuan koordinat site anda telah disetujui',$username_rtpo.' menyatakan bahwa pengauan koordinat anda telah disetujui.',$report_data->firebase_token,'report_id', $report_id, 'NEW_LOCATION_SITE_APPROVED_FROM_RTPO');

        //  ()> $type       : NEW_LOCATION_SITE_APPROVED_FROM_RTPO
        //  ()> $type_id    : 
        //  ()> $type_name  : report_id
        //  ()> $to_token_id: #toUserFmcTerkait
        //  ()> $body       : (user rtpo ...) dari (rtpo ...) menyatakan bahwa pengauan koordinat anda telah disetujui. 
        //  ()> $title      : Pengajuan koordinat site anda telah disetujui
      }elseif ($approval == 0) {

        $tmp = $notificationController->setNotificationV1($username_rtpo, $report_data->send_by, 'NEW_LOCATION_SITE_NOT_APPROVED_FROM_RTPO', 'report_id', $report_id, 'Pengajuan koordinat site anda tidak disetujui', 'NEW_LOCATION_SITE_NOT_APPROVED_FROM_RTPO', $username_rtpo.' menyatakan bahwa pengauan koordinat anda telah disetujui.',0,'MAINTENANCE');

        // $topic = '/topics/'.$this->checkMyFMCtopic($data['fmc_id']);
        $notificationController->sendNotifFast('Pengajuan koordinat site anda telah disetujui',$username_rtpo.' menyatakan bahwa pengauan koordinat anda tidak disetujui.',$report_data->firebase_token,'report_id', $report_id, 'NEW_LOCATION_SITE_NOT_APPROVED_FROM_RTPO');
        //  ()> $type       : NEW_LOCATION_SITE_NOT_APPROVED_FROM_RTPO
        //  ()> $type_id    : 
        //  ()> $type_name  : report_id
        //  ()> $to_token_id: #toUserFmcTerkait
        //  ()> $body       : (user rtpo ...) dari (rtpo ...) menyatakan bahwa pengauan koordinat anda telah disetujui. 
        //  ()> $title      : Pengajuan koordinat site anda tidak disetujui
      }


      $res['success'] = 'OK';
      $res['message'] = 'Success';
      // $res['data'] = $report_data;
      return response($res);
    }else{
      $res['success'] = 'NOT OK';
      $res['message'] = 'FAILED_SIK_NOT_FOUND';
      return response($res);
    }
  }

  public function loginMenuMaintenanceFromReport(Request $request){

    # masukan parameter phone nya
    $phone = $request->input('phone');
    # masukan parameter username nya
    $username = $request->input('username');
    # masukan parameter sik_no nya
    $sik_no = $request->input('sik_no');
    $input_secret_code = $request->input('secret_code');
    $user_type = $request->input('user_type');
    $maintenance_status=1;

    # cek apakah data dengan phone, username, sik_no di atas ada di tabel "sik_site" ?
      # bila ada maka ambil otp_induknya dan site_idnya
    $sik_data = DB::table('sik_site')
    ->select('*')
    ->where('sik_no','=',$sik_no)
    ->first();

    if ($sik_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_SIK_NOT_FOUND';
      return response($res);
    }

    # cek apakah ada report where username = username dia, site_id = site_id sik dia dan statusnya masih null
    $report_data = DB::table('report_location_site')
    ->select('*')
    ->where('send_by','=',$username)
    ->where('site_id','=',$sik_data->site_id)
    ->where('approval','=',1)
    ->first();

    if ($report_data==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_REPORT_NOT_FOUND_OR_IS_NOT_APPROVED';
      return response($res);
    }


    if ($sik_data->otp_id==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_OTP_NULL';
      return response($res);
    }


    if ($user_type!=null) {
      if ($user_type=='3') { //member
        # code... input secret code and check
        $check_secret_code = DB::table('secret_code_maintenance')
        ->select('*')
        ->where('secret_code','=',$input_secret_code)
        ->first();

        if ($check_secret_code==null) {
          $maintenance_status=0;
        }else{
          $maintenance_status=3;
        }
          

      }elseif ($user_type=='2') { //leader
        # code... create secret code
        $secretcode = md5($sik_no.''.$username.''.$sik_data->otp_id);
        $secretcode = substr($secretcode, 0, 6);
        $insertsecretcode = DB::table('secret_code_maintenance')
        ->insert(
          [
            'sik' => $sik_no,
            'username_leader' => $username,
            'secret_code' => $secretcode,
          ]
        );
        if (!$insertsecretcode) {
          $secretcode=null;
          $maintenance_status=0;
        }else{
          $maintenance_status=2;
        }

      }else{ //solo
        # code...
      }
    }

    $site_data = DB::table('site as ms')
    ->select('*')
    ->where('site_id','=',$sik_data->site_id)
    ->first();

    $revision_data = DB::table('sik_site as ss')
    ->select('*')
    ->where('ss.sik_no','=',$sik_no)
    ->where('ss.mt_status','=',1)
    ->first();

    if ($revision_data) {
      $maintenance_status = 3;
    }

    $data['otp'] = $sik_data->otp_id;
    $data['site_id'] = $sik_data->site_id;
    $data['site_name'] = $sik_data->site_name;
    $data['sik_number'] = $sik_data->sik_no;
    //-----------------------------------------------------------------
    $data['kriteria_site'] = @$site_data->kriteria_site[0];

    $data['secret_code'] = @$secretcode;
    $data['maintenance_status'] = @$maintenance_status;

    if ("C2"==@$site_data->kriteria_site) {
      $data['kriteria_site'] = @$site_data->kriteria_site;
    }
    // $data['kriteria_site'] = $data['kriteria_site'][0];
    if ($data['kriteria_site']==null) {
      $data['kriteria_site'] = "C";
    }
    //------------------------------------------------------------------    

    # return otp_induk
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);
  }

  // public function getMaintenanceOTP(Request $request){

  //   # masukan parameter phone nya
  //   // $phone = $request->input('phone');
  //   # masukan parameter username nya
  //   // $username = $request->input('username');
  //   # masukan parameter sik_no nya
  //   $sik_no = $request->input('sik_no');
  //   $username = $request->input('username');
  //   $input_secret_code = $request->input('secret_code');
  //   $user_type = $request->input('user_type');
  //   $maintenance_status=1;

  //   # cek apakah data dengan phone, username, sik_no di atas ada di tabel "sik_site" ?
  //     # bila ada maka ambil otp_induknya dan site_idnya
  //   $sik_data = DB::table('sik_site as ss')
  //   ->select('*')
  //   ->where('sik_no','=',$sik_no)
  //   ->first();


  //   if ($sik_data==null) {
  //     $res['success'] = false;
  //     $res['message'] = 'FAILED_SIK_NOT_FOUND';
  //     return response($res);
  //   }


  //   if ($sik_data->otp_id=='') {
  //     $res['success'] = false;
  //     $res['message'] = 'FAILED_OTP_NULL';
  //     return response($res);
  //   }

  //   if ($sik_data->otp_id==null) {
  //     $res['success'] = false;
  //     $res['message'] = 'FAILED_OTP_NULL';
  //     return response($res);
  //   }


  //   if ($user_type!=null) {
  //     if ($user_type=='3') { //member
  //       # code... input secret code and check
  //       $check_secret_code = DB::table('secret_code_maintenance')
  //       ->select('*')
  //       ->where('secret_code','=',$input_secret_code)
  //       ->first();

  //       if ($check_secret_code==null) {
  //         $maintenance_status=0;
  //       }else{
  //         $maintenance_status=3;
  //       }
          

  //     }elseif ($user_type=='2') { //leader
  //       # code... create secret code
  //       $secretcode = md5($sik_no.''.$username.''.$sik_data->otp_id);
  //       $secretcode = substr($secretcode, 0, 6);
  //       $insertsecretcode = DB::table('secret_code_maintenance')
  //       ->insert(
  //         [
  //           'sik' => $sik_no,
  //           'username_leader' => $username,
  //           'secret_code' => $secretcode,
  //         ]
  //       );
  //       if (!$insertsecretcode) {
  //         $secretcode=null;
  //         $maintenance_status=0;
  //       }else{
  //         $maintenance_status=2;
  //       }

  //     }else{ //solo
  //       # code...
  //     }
  //   }

  //   $site_data = DB::table('site as ms')
  //   ->select('*')
  //   ->where('site_id','=',$sik_data->site_id)
  //   ->first();

  //   $revision_data = DB::table('sik_site as ss')
  //   ->select('*')
  //   ->where('ss.sik_no','=',$sik_no)
  //   ->where('ss.mt_status','=',1)
  //   ->first();

  //   if ($revision_data) {
  //     $maintenance_status = 3;
  //   }

  //   $data['otp'] = $sik_data->otp_id;
  //   $data['site_id'] = $sik_data->site_id;
  //   $data['site_name'] = $sik_data->site_name;
  //   $data['sik_number'] = $sik_data->sik_no;
  //   $data['kriteria_site'] = @$site_data->kriteria_site[0];

  //   $data['secret_code'] = @$secretcode;
  //   $data['maintenance_status'] = @$maintenance_status;

  //   if ("C2"==@$site_data->kriteria_site) {
  //     $data['kriteria_site'] = @$site_data->kriteria_site;
  //   }
  //   // $data['kriteria_site'] = $data['kriteria_site'][0];
  //   if ($data['kriteria_site']==null) {
  //     $data['kriteria_site'] = "C";
  //   }
    

  //   # return otp_induk
  //   $res['success'] = true;
  //   $res['message'] = 'SUCCESS';
  //   $res['data'] = $data;
  //   return response($res);
  // }

  // public function checkReportToLogin(Request $request){

  //   $username = $request->input('username');
  //   $sik_no = $request->input('sik_no');

  //   $sik_data = DB::table('sik_site')
  //   ->select('*')
  //   ->where('sik_no','=',$sik_no)
  //   ->first();

  //     // $res['success'] = false;
  //     // $res['message'] = 'FAILED_SIK_NOT_FOUND';
  //     // return response($res);

  //   if ($sik_data==null) {
  //     $res['success'] = false;
  //     $res['message'] = 'FAILED_SIK_NOT_FOUND';
  //     return response($res);
  //   }

  //   # cek apakah ada report where username = username dia, site_id = site_id sik dia dan statusnya masih null
  //   $report_data = DB::table('report_location_site')
  //   ->select('*')
  //   ->where('send_by','=',$username)
  //   ->where('site_id','=',$sik_data->site_id)
  //   // ->where('approval','=',1)
  //   ->first();

  //   if ($report_data==null) {
  //     $res['success'] = false;
  //     $res['message'] = 'FAILED_REPORT_NOT_FOUND_OR_IS_NOT_APPROVED';
  //     $data['site_id from sik'] = $sik_data->site_id;
  //     $data['username'] = $username;
  //     $res['data'] = $data;
  //     return response($res);
  //   }

  //   if ($report_data->approval==0) {
  //     $tmp_status = 'REJECTED';
  //   }elseif ($report_data->approval==1) {
  //     $tmp_status = 'APPROVED';
  //   }else{
  //     $tmp_status = 'WAITING';  
  //   }

  //   $data['status'] = $tmp_status;
  //   $data['status int'] = $report_data->approval;
  //   $data['site_id from sik'] = $sik_data->site_id;

  //   $res['success'] = true;
  //   $res['message'] = 'SUCCESS';
  //   $res['data'] = $data;
  //   return response($res);
  // }

  public function setDataSitefromDStoMaster(Request $request){

    $master_site_data = DB::table('master_site')
    ->select('*')
    ->get();

    $result = json_decode($master_site_data, true);
    if ($result==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED_DATA_NOT_FOUND';
      return response($res);
    }

    foreach ($result as $param => $row) {

      $site_data = DB::table('site')
      ->select('*')
      ->where('site_id','=',$row['site_id'])
      ->first();

      if ($site_data==null) {
        # insert code...
        // $updateMasterMbp = DB::table('site')
        // ->insert(
        //   [
        //     'site_id' => $row['site_id'],           //------------
        //     'site_name' => $row['site_name'],           //------------
        //     'rtpo_id' => $row['rtpo_id'],               //------------
        //     'rtpo' => $row['rtpo'],                     //------------
        //     'class_id' => $row['site_class'],           // class id di site == site_class di master_site
        //     'type_id' => null,                          //------------
        //     'latitude' => $row['latitude'],             //------------
        //     'longitude' => $row['longitude'],           //------------
        //     'cluster_fmc_id' => $row['cluster_fmc_id'], //------------
        //     'cluster_fmc' => $row['cluster_fmc'],       //------------
        //     'divisi' => $row['divisi'],                 //------------  
        //     'tec_opr_id' => $row['tec_opr_id'],         //------------
        //     'wil_opr_id' => $row['wil_opr_id'],         //------------
        //     'ns_id' => $row['ns_id'],                   //------------
        //     'ns' => $row['ns'],                         //------------
        //     'regional' => $row['regional'],             //------------
        //     // 'Kolom 19' => $row['Kolom 19'],
        //     'branch_id' => $row['branch_id'],           //------------
        //     'branch' => $row['branch'],                 //------------
        //     'cluster_id' => $row['cluster_id'],         //------------
        //     'cluster' => $row['cluster'],               //------------
        //     'pic_nik' => $row['pic_nik'],               //------------
        //     'pic_cn' => $row['pic_cn'],                 //------------
        //     'pic_approval_nik' => $row['pic_approval_nik'],//---------
        //     'pic_approval_cn' => $row['pic_approval_cn'],//-----------
        //     'site_class' => $row['site_class'],         //------------
        //     'site_class_periode' => $row['site_class_periode'],//-----
        //     'site_class_revenue' => $row['site_class_revenue'],//-----
        //     'frekuensi' => $row['frekuensi'],           //------------
        //     'kriteria_site' => $row['kriteria_site'],   //------------
        //     'status' => '1',                            //------------
        //     'is_allocated' => '0',                      //------------
        //     // 'date_mainsfail' => $row['date_mainsfail'],
        //     'revenue' => $row['site_class_revenue'],    //------------
        //     'node' => '0',                              //------------
        //     'update_by' => 'admin_semeru',              //------------
        //     'last_update' => $row['last_update'],       //------------
        //   ]
        // );
      }else{
        # update code...
        $updateMasterMbp = DB::table('site')
        ->where('site_id','=',$row['site_id'])
        ->update(
          [
            // 'rtpo_id' => $row['rtpo_id'],               //------------
            // 'rtpo' => $row['rtpo'],                     //------------
            // 'class_id' => $row['site_class'],           // class id di site == site_class di master_site
            // 'type_id' => null,                          //------------
            // 'site_name' => $row['site_name'],           //------------
            // 'latitude' => $row['latitude'],             //------------
            // 'longitude' => $row['longitude'],           //------------

            'cluster_fmc_id' => $row['cluster_fmc_id'], //------------
            'cluster_fmc' => $row['cluster_fmc'],       //------------
            
            // 'divisi' => $row['divisi'],                 //------------  
            // 'tec_opr_id' => $row['tec_opr_id'],         //------------
            // 'wil_opr_id' => $row['wil_opr_id'],         //------------
            // 'ns_id' => $row['ns_id'],                   //------------
            // 'ns' => $row['ns'],                         //------------
            // 'regional' => $row['regional'],             //------------
            // // 'Kolom 19' => $row['Kolom 19'],
            // 'branch_id' => $row['branch_id'],           //------------
            // 'branch' => $row['branch'],                 //------------
            // 'cluster_id' => $row['cluster_id'],         //------------
            // 'cluster' => $row['cluster'],               //------------
            // 'pic_nik' => $row['pic_nik'],               //------------
            // 'pic_cn' => $row['pic_cn'],                 //------------
            // 'pic_approval_nik' => $row['pic_approval_nik'],//---------
            // 'pic_approval_cn' => $row['pic_approval_cn'],//-----------
            // 'site_class' => $row['site_class'],         //------------
            // 'site_class_periode' => $row['site_class_periode'],//-----
            // 'site_class_revenue' => $row['site_class_revenue'],//-----
            // 'frekuensi' => $row['frekuensi'],           //------------
            // 'kriteria_site' => $row['kriteria_site'],   //------------
            // // 'status' => $row['status'],                 //------------
            // // 'is_allocated' => $row['is_allocated'],
            // // 'date_mainsfail' => $row['date_mainsfail'],
            // 'revenue' => $row['site_class_revenue'],    //------------
            // // 'node' => $row['node'],
            // 'update_by' => 'admin_semeru',
            // 'last_update' => $row['last_update'],       //------------
          ]
        );

      }
    }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    // $res['data'] = $data;
    return response($res);
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

  public function updateSiteMainsFail(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $site_ids = $request->input('site_ids');

    $site_data = DB::table('site')
    ->where('status',1)
    ->whereIn('site_id',$site_ids)
    ->update(
      [
        'status' => 0,
        'date_mainsfail' => $date_now,
      ]
    );


    $site_data = DB::table('site')
    ->where('status',0)
    ->whereNotIn('site_id',$site_ids)
    ->update(
      [
        'status' => 1,
        'date_mainsfail' => $date_now,
      ]
    );

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    // $res['data'] = $site_data;
    return response($res); 
  }

  public function getDetailSiteFromSIK(Request $request){

    $sik_no = $request->input('sik_no');

    $tmp = explode("/",$sik_no);
    $site_id = $tmp[1];

    $site_data = DB::table('site')
    ->select('*')
    ->where('site_id','=',$site_id)
    ->first();

    if ($site_data!=null) {
      $res['site_id'] = $site_id;
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $site_data;
      return response($res); 
    }else{
      $res['site_id'] = $site_id;
      $res['success'] = true;
      $res['message'] = 'SITE DATA NOT FOUND';
      $res['data'] = $site_data;
      return response($res);   
    }

  }

  // public function updateSiteMainsFail(Request $request){

  //   date_default_timezone_set("Asia/Jakarta");
  //   $date_now =date('Y-m-d H:i:s');

  //   $site_ids = $request->input('site_ids');

  

  //   $res['success'] = true;
  //   $res['message'] = 'SUCCESS';
  //   // $res['data'] = $site_data;
  //   return response($res);
  // }

  public function updateSiteDownJatim(Request $request){
    $last_update = $request->input('last_update');
    $data = $request->input('data');
    $tmp = $this->updateSiteDown($data, "JATIM",@$last_update);
    return $tmp;
  }

  public function updateSiteDownJateng(Request $request){
    // $last_update = $request->input('last_update');

    date_default_timezone_set("Asia/Jakarta");
    $last_update = date("Y-m-d H:i:s");

    $data = $request->input('data');
    $tmp = $this->updateSiteDown($data, "JATENG-DIY",@$last_update);
    return $tmp;
  }


    public function cekFileMt(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $last_update = date("Y-m-d H:i:s");
    $month_tmp = date("m");
    $year_tmp = date("Y");

      $username = app('request')->input('username');
      $sik_no = app('request')->input('sik_no');
      $site_id = app('request')->input('site_id'); 
      $otp = app('request')->input('otp'); 
      $fname = app('request')->input('fname'); 
      
      $checkXml = DB::table('log_maintenance');

      if ($username!=null) {
        $checkXml = $checkXml->where('username','like','%'.$username.'%');
      }
      if ($sik_no!=null) {
        $checkXml = $checkXml->where('sik_no','like','%'.$sik_no.'%');
      }
      if ($site_id!=null) {
        $checkXml = $checkXml->where('site_id','like','%'.$site_id.'%');
      }
      if ($otp!=null) {
        $checkXml = $checkXml->where('otp','like','%'.$otp.'%');
      }
      if ($fname!=null) {
        $checkXml = $checkXml->where('fname','like','%'.$fname.'%');
      }

      $checkXml = $checkXml->select('username','site_id','sik_no',/*'otp',*/'fname','status','msg_status as keterangan',  'date as waktu_upload','last_update as dicek_server')
      ->orderBy('date', 'DESC')
      ->whereMonth('date',$month_tmp)
      ->whereYear('date',$year_tmp)
      ->limit(15)
      ->get();
      foreach($checkXml as $key => $value)
      {
        if ($value->sik_no==null) {
          $value->status = 0;
        } 
      }

      $res['username'] = @$username;
      $res['sik_no'] = @$sik_no;
      $res['site_id'] = @$site_id;
      $res['otp'] = @$otp;
      $res['fname'] = @$fname;
      $res['data'] = $checkXml;
      return response($res);      
    }

    public function cekFileMaintenance(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $last_update = date("Y-m-d H:i:s");
    $month_tmp = date("m");
    $year_tmp = date("Y");

    // $data = $request->input('data');
      $username = @$request->input('username');
      $date = @$request->input('date');
      $sik_no = @$request->input('sik_no');
      $site_id = @$request->input('site_id'); 
      $otp = @$request->input('otp'); 
      $fname = @$request->input('fname'); 
      $fmc_id = @$request->input('fmc_id');
      $length = @$request->input('length'); 
      $start = @$request->input('start'); 
      $status = @$request->input('status'); 
      // $fname = app('request')->input('fname'); 
      if(empty($start)) $start=0;
      if(empty($length)) $length=10;

      $checkXml = DB::table('log_maintenance');

      if ($username!=null) {
        $checkXml = $checkXml->where('username','like','%'.$username.'%');
      }
      if ($fmc_id!=null) {
        $checkXml = $checkXml->where('fmc_id','=',$fmc_id);
      }
      if ($sik_no!=null) {
        $checkXml = $checkXml->where('sik_no','like','%'.$sik_no.'%');
      }
      if ($site_id!=null) {
        $checkXml = $checkXml->where('site_id','like','%'.$site_id.'%');
      }
      if ($otp!=null) {
        $checkXml = $checkXml->where('otp','like','%'.$otp.'%');
      }
      if ($fname!=null) {
        $checkXml = $checkXml->where('fname','like','%'.$fname.'%');
      }
      if ($status!=null) {
        $checkXml = $checkXml->where('status','=',$status);
      }

      if(empty($date)){
        $checkXml = $checkXml->where('date', 'like',date('Y-m').'%');
      }else{
        $checkXml = $checkXml->where('date', 'like',$date.'%');
      }
      $checkXml = $checkXml->orderBy('date', 'DESC');

      $count = $checkXml->select('username','site_id','sik_no','otp','uri','fname','status','msg_status',  'date','last_update')->count();


      $checkXml = $checkXml->offset($start);
      $checkXml = $checkXml->limit($length);

      // ->whereMonth('date',$month_tmp)
      // ->whereYear('date',$year_tmp)
      $checkXml = $checkXml->get();


      foreach($checkXml as $key => $value)
      {
        if ($value->msg_status=="SIK Not Found;") {
          $value->status = 0;
        } 
      }
      // $res['username'] = @$username;
      // $res['sik_no'] = @$sik_no;
      // $res['site_id'] = @$site_id;
      // $res['otp'] = @$otp;
      // $res['fname'] = @$fname;
      $res['count'] = $count;
      $res['data'] = $checkXml;
      return response($res);      
    }

     public function cekFileReplacement(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $last_update = date("Y-m-d H:i:s");
    $month_tmp = date("m");
    $year_tmp = date("Y");

    // $data = $request->input('data');
      $username = @$request->input('username');
      $date = @$request->input('date');
      $spk_no = @$request->input('spk_no');
      $site_id = @$request->input('site_id');
      $genset_id = @$request->input('gnst_id'); 
      $otp = @$request->input('otp'); 
      $fname = @$request->input('fname'); 
      $fmc_id = @$request->input('fmc_id');
      $length = @$request->input('length'); 
      $start = @$request->input('start'); 
      $status = @$request->input('status'); 
      // $fname = app('request')->input('fname'); 
      if(empty($start)) $start=0;
      if(empty($length)) $length=10;

      $checkXml = DB::table('log_sparepart');

      if ($username!=null) {
        $checkXml = $checkXml->where('username','like','%'.$username.'%');
      }
      if ($genset_id!=null) {
        $checkXml = $checkXml->where('genset_id','=',$genset_id);
      }
      if ($fmc_id!=null) {
        $checkXml = $checkXml->where('fmc_id','=',$fmc_id);
      }
      if ($spk_no!=null) {
        $checkXml = $checkXml->where('spk_no','=',$spk_no);
      }
      if ($site_id!=null) {
        $checkXml = $checkXml->where('site_id','like','%'.$site_id.'%');
      }
      if ($otp!=null) {
        $checkXml = $checkXml->where('otp','like','%'.$otp.'%');
      }
      if ($fname!=null) {
        $checkXml = $checkXml->where('fname','like','%'.$fname.'%');
      }
      if ($status!=null) {
        $checkXml = $checkXml->where('status','=',$status);
      }

      if(empty($date)){
        $checkXml = $checkXml->where('date', 'like',date('Y-m').'%');
      }else{
        $checkXml = $checkXml->where('date', 'like',$date.'%');
      }
      $checkXml = $checkXml->orderBy('date', 'DESC');

      $count = $checkXml->select('username','genset_id as gnst_id','site_id','spk_no','otp','uri','fname','status','msg_status',  'date','last_update')->count();


      $checkXml = $checkXml->offset($start);
      $checkXml = $checkXml->limit($length);

      // ->whereMonth('date',$month_tmp)
      // ->whereYear('date',$year_tmp)
      $checkXml = $checkXml->get();
      
      foreach($checkXml as $key => $value)
      {
        if ($value->msg_status=="SPK Not Found;") {
          $value->status = 0;
        } 
      }

      // $res['username'] = @$username;
      // $res['sik_no'] = @$sik_no;
      // $res['site_id'] = @$site_id;
      // $res['otp'] = @$otp;
      // $res['fname'] = @$fname;
      $res['count'] = $count;
      $res['data'] = $checkXml;
      return response($res);      
    }

  public function updateSiteDownBali(Request $request){
    $last_update = $request->input('last_update');
    $data = $request->input('data');
    $tmp = $this->updateSiteDown($data, "BALI NUSRA",@$last_update);
    return $tmp;
  }


  public function getSiteDownJateng(Request $request){
    // $data = $request->input('data');
    // $tmp = $this->updateSiteDown($data, "JATENG-DIY");

    $site_data = DB::table('site')
    ->select('site_id', 'site_name', 'alarm', 'status','last_update')
    ->where('regional',"JATENG-DIY")
    ->where('status',"0")
    ->get();
    return $site_data;
  }

  public function updateSiteDown($data,$regional,$last_update){

    date_default_timezone_set("Asia/Jakarta");
    $date_now = date("Y-m-d H:i:s");

    $x_count = 0;
    if (@$data[0]==null) {
      $res['success'] = false;
      $res['message'] = 'parameter data null';
    // $res['data'] = $site_data;
      return response($res); 
    }
    foreach ($data as $param => $row) {
      
      if ($row['alarm']=='MODULE RECT FAIL' || $row['alarm']=='MODULE RECTI FAIL') {
      // if ($row['alarm']=='MODULE RECT FAIL') {
      
      }else{
        
        $site_data = DB::table('site')
        ->where('regional',$regional)
        ->where('site_id',$row['site_id'])
        ->update(
          [
            'status' => 0,
            'date_mainsfail' => @$row['alarm_date'],
            'alarm' => @$row['alarm'],
            'last_update' => @$last_update,
            // 'band' => @$row['band'],
          ]
        );        
        
        $x_site_id[$x_count]=$row['site_id'];
      }

      $x_count=$x_count+1;
    }

    
    $x_count=$x_count+1;
    $x_site_id[$x_count]='SBZ351';

    $site_data = DB::table('site')
    ->where('status',0)
    ->where('regional',$regional)
    ->where('last_update','!=',@$last_update)
    ->whereNotIn('site_id',$x_site_id)
    ->update(
      [
        'status' => 1,
        'date_mainsfail' => null,
        'alarm' => null,
      ]
    );

    //cek di sp, apakah sp yang masih aktif sitenya masih down?
    // bila sudah g down, maka kirim notif ke yang menerima tiket fp bahwa site 

    // $SP_data = DB::table('supplying_power as sp')
    // ->join('users as u','sp.user_mbp_cn','u.username')
    // ->select('sp.*','u.fmc','u.fmc_id','u.firebase_token','u.username')
    // ->where('finish','=',null)
    // ->get();

    // $x=0;
    // foreach ($SP_data as $value) {

    //   $cek_alarm_site = DB::table('site')
    //   ->select('*')
    //   ->where('site_id','=',$value->site_id)
    //   ->where('alarm','=',null)
    //   ->first();

    //   if (@$cek_alarm_site->alarm == null) {

    //     // queue_firebase

    //     $cek_queue_fb = DB::table('queue_firebase')
    //     ->select('*')
    //     ->where('sp_id','=',@$value->sp_id)
    //     ->first();
    //     if (@$cek_queue_fb->sp_id==null) {
          
    //       $insert_queue_firebase = DB::table('queue_firebase')
    //       ->insert(
    //         [
    //           'fmc_id' => @$value->fmc_id,
    //           'sp_id' => @$value->sp_id,
    //           'mbp_id' => @$value->mbp_id,
    //           'cluster_id' => $cek_alarm_site->cluster_id,
    //           'rtpo_id' => $cek_alarm_site->rtpo_id,

    //           'subject' => 'ALARM_OFF',
    //           'send_to' => @$value->username,
    //           'fb_token' => @$value->firebase_token,
    //           'message' => 'site '.$value->site_id.' dinyatakan telah normal kembali',

    //           'sent' => 0,
    //           'create_at' => $date_now,
    //         ]
    //       );
    //     }




    //     // masukkan ke queue firebase dan bila di queue firebase udah ada yang sp_idnya sama, maka g perlu di masukan lagi.. ok..:D
    //   }

    // }

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    // $res['data'] = $site_data;
    return response($res); 
  }

  public function getMySiteCorrectivePaginate(Request $request){


    $rtpo_id = $request->input('rtpo_id');
    $mbp_id = $request->input('mbp_id');
    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();
    if ($mbp_data==null) {
      $lat1 = 0;
      $lon1 = 0;
    }else{
      $lat1 = $mbp_data->latitude;
      $lon1 = $mbp_data->longitude;
    }

    $data_site = DB::table('site')
    ->select('site.site_id','site.site_name','site.status', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude','site.node','site.alarm')
    ->where('rtpo_id',$rtpo_id)
    ->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
    ->orderBy('site_name', 'asc')
    ->offset($offset)
    ->limit($limit)
    ->get();


    $site_result = json_decode($data_site, true);
    if ($site_result==null) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $site_result;
      return response($res);
    }
    
    foreach ($site_result as $param => $row) {
      
      $lat2=$site_result[$param]['latitude'].'';
      $lon2=$site_result[$param]['longitude'].'';


      $get_distance = $this->distance($lat1, $lon1, $lat2, $lon2, 'K');

      
      $distance[$param] = round($get_distance,2).' km';
      $duration[$param] = '';
      $distancevalue[$param] = /*$get_distance*/'';
      $durationvalue[$param] = '';
      $revenuevalue[$param] = '';      
      
      $tmp_site[$param]['site_id'] = $site_result[$param]['site_id'];
      $tmp_site[$param]['site_name'] = $site_result[$param]['site_name'];
      $tmp_site[$param]['class_name'] = strtolower($site_result[$param]['class_name']);
      $tmp_site[$param]['mbp_name'] = /*$result[0]['mbp_name']*/'';
      $tmp_site[$param]['status'] = $site_result[$param]['status'];
      $tmp_site[$param]['latitude'] = $site_result[$param]['latitude'];
      $tmp_site[$param]['longitude'] = $site_result[$param]['longitude'];
      $tmp_site[$param]['distance'] = $distance[$param];
      $tmp_site[$param]['duration'] = /*$duration[$param]*/'';
      $tmp_site[$param]['distancevalue'] = /*$distancevalue[$param]*/'';
      $tmp_site[$param]['durationvalue'] = /*$durationvalue[$param]*/'';
      $tmp_site[$param]['alarm'] = $site_result[$param]['alarm'];

      if ($tmp_site[$param]['alarm']==null) {
        $tmp_site[$param]['alarm']='';
      }

      if ($site_result[$param]['node']=='1') {
        $node[$param] = 'Simpul';
      }else {
        $node[$param] = 'Bukan Simpul';
      }


      $tmp_site[$param]['node'] = $node[$param];

    }


    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $tmp_site;
      
      return response($res);
    }else{
      $polys['success'] = false;
      $polys['message'] = 'Cannot find polys!';
      
      return response($btss);
    }
  }

  public function list_report_new_site_paginate(Request $request){

    $rtpo_id = $request->input('rtpo_id');

    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    # get seluruh report where belum di approve dan rtpo_id = rtpo_id yang pengan lihat..:D
    # tampilakn site_id, username or nama yang mengajukan nama di ajukan pada tanggal..:D
    $report_data = DB::table('report_location_site as rls')
    ->join('site as s','rls.site_id','s.site_id')
    ->select('rls.send_by','rls.site_id','s.site_name','rls.sik_no','rls.device_acuration','rls.delivery_date','rls.report_id','rls.is_offline','rls.approval')
    ->where('rls.rtpo_id','=',$rtpo_id)
    // ->whereNotBetween('rls.approval', [1, 0])
    ->where('rls.approval','>',1)
    ->offset($offset)
    ->limit($limit)
    ->orderBy('rls.delivery_date','desc')
    ->get();

    foreach ($report_data as $key => $value) {
      $delivery_date2 = $this->tanggal_bulan_tahun_indo($value->delivery_date);
      $value->delivery_date = $delivery_date2;
    }
    
    # return all
    $res['success'] = 'OK';
    $res['message'] = 'Success';
    $res['data'] = $report_data;
    return response($res);
  }

  public function list_history_report_new_site_paginate(Request $request){

    $rtpo_id = $request->input('rtpo_id');

    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;

    # get seluruh report where belum di approve dan rtpo_id = rtpo_id yang pengan lihat..:D
    # tampilakn site_id, username or nama yang mengajukan nama di ajukan pada tanggal..:D
    $report_data = DB::table('report_location_site as rls')
    ->join('site as s','rls.site_id','s.site_id')
    ->select('rls.send_by','rls.site_id','s.site_name','rls.sik_no','rls.device_acuration','rls.delivery_date','rls.report_id')
    ->where('rls.rtpo_id','=',$rtpo_id)
    ->where('rls.approval','=',1)
    ->whereraw('(rls.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->orderBy('rls.respon_by_rtpo_at','desc')
    ->get();

    
    foreach ($report_data as $key => $value) {
      $delivery_date2 = $this->tanggal_bulan_tahun_indo_tiga_char($value->delivery_date);
      $value->delivery_date = $delivery_date2;
    }
    

    # return all
    $res['success'] = 'OK';
    $res['message'] = 'Success';
    $res['data'] = $report_data;
    return response($res);
  }

  public function deleteRlsDummy(Request $request)
  {
    $delete_dummy = DB::table('report_location_site')
    ->where('rtpo_id',42)
    ->delete();

    $res['success'] = true;
    $res['message'] = 'SUCCESS_DELETE_DUMMY_DATA';
    return response($res);  
  }

  public function getMySitePaginate(Request $request){
    $rtpo_id = $request->input('rtpo_id');

    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $data_site = DB::table('site')
    ->select('site.site_id','site.site_name','site.status','site.kriteria_site', 'site.class_id as class_name', /*'class.revenue',*/'site.latitude','site.longitude','site.is_allocated')
    ->where('rtpo_id','=',$rtpo_id)
    ->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->get();

    foreach ($data_site as $key => $value) {
      $value->class_name = ($value->class_name==null) ? '' : $value->class_name;
    }

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data_site;
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Server Error!';
      
      return response($res);
    }
  }

  public function getListSite(Request $request)
  {
    $username = $request->input('username');

    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;



    $user_rtpo = DB::table('user_rtpo')->where(['username'=> $username,'status'=>1])->first();
    
    $DB = DB::table('site')
      ->select('site_id','site_name','latitude','longitude','cluster_id','cluster','rtpo_id','rtpo','ns_id','ns','regional','site.status', 'site.class_id as class_name')
      ->whereraw('(site_id like "%'.$search.'%" or site_name like "%'.$search.'%")')
      ->offset($offset)
      ->limit($limit);
    
    if($user_rtpo){
      $DB = $DB->where('rtpo_id',$user_rtpo->rtpo_id);
    }else{
      $data_user = DB::table('users')
      ->select('cluster_id')
      ->where('username',$username)
      ->first();

      $DB = $DB->where('cluster_id',$data_user->cluster_id);

    }

    $data_site = $DB->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data_site;

      foreach ($data_site as $i => $site) {
        $data_site[$i]->class_name = is_null($site->class_name)?'-':$site->class_name;
      }
      
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Server Error!';
      
      return response($res);
    }
  }

  function bulan_indo($param=1)
  {
    $bulan = [
     '',
     'Januari',
     'Februari',
     'Maret',
     'April',
     'Mei',
     'Juni',
     'Juli',
     'Agustus',
     'September',
     'Oktober',
     'November',
     'Desember',
    ];
    return @$bulan[(int)$param];
  }

  function tanggal_bulan_tahun_indo($param)
  {
    $param2 = explode(' ', $param);
    list($jam,$menit) = explode(':', $param2[1]);
    list($y,$m,$d) = explode('-', $param2[0]);
    return $d.' '.$this->bulan_indo($m).' '.$y.', '.$jam.':'.$menit;
  }

  function bulan_indo_tiga_char($param=1)
  {
    $bulan = [
     '',
     'Jan',
     'Feb',
     'Mar',
     'Apr',
     'Mei',
     'Jun',
     'Jul',
     'Agu',
     'Sep',
     'Okt',
     'Nov',
     'Des',
    ];
    return @$bulan[(int)$param];
  }

  function tanggal_bulan_tahun_indo_tiga_char($param)
  {
    $param2 = explode(' ', $param);
    list($jam,$menit) = explode(':', $param2[1]);
    list($y,$m,$d) = explode('-', $param2[0]);
    return $d.' '.$this->bulan_indo_tiga_char($m).' '.$y.', '.$jam.':'.$menit;
  }
}