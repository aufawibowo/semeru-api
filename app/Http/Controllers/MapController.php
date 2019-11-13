<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class MapController extends Controller
{

  public function getMyMbpSiteToMAp(Request $request){

    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $rtpo_id = $request->input('rtpo_id');
    // $data_mbp = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    $data_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    // ->leftJoin('supplying_power', 'mbp.mbp_id', '=', 'supplying_power.mbp_id')
    ->select('mbp.mbp_id','mbp.submission','mbp.rtpo_id','mbp.rtpo_id_home','mbp.cluster_id','mbp.mbp_name',/*DB::raw('(case when (submission = "DELAY") then "DELAY" else mbp.status end) as status'),*/'mbp.status','mbp.latitude','mbp.longitude','user_mbp.mbp_mt_nik','users.name as operator_name'/*,'supplying_power.finish','supplying_power.site_id'*/)
    ->where('mbp.rtpo_id','=',$rtpo_id)
    // ->where('supplying_power.finish','=',null)
    ->get();


    $mbp_result = json_decode($data_mbp, true);
    
    if ($mbp_result!=null) {
      // $res['success'] = false;
      // $res['message'] = 'FAILED GET DATA';
      // $res['data'] = $mbp_data;
      // return response($res);
      

      foreach ($mbp_result as $dm => $row)
      {
        if ($row['submission'] == 'DELAY') {
          $mbp_data[$dm]['status'] = 'DELAY';
        }else{
          $mbp_data[$dm]['status'] = $row['status'];
        }

        $mbp_data[$dm]['mbp_id'] = $row['mbp_id'];
        $mbp_data[$dm]['rtpo_id'] = $row['rtpo_id'];
        $mbp_data[$dm]['rtpo_id_home'] = $row['rtpo_id_home'];
        $mbp_data[$dm]['cluster_id'] = $row['cluster_id'];
        $mbp_data[$dm]['mbp_name'] = $row['mbp_name'];
    // $mbp_data['status'] = $data_mbp->status;
        $mbp_data[$dm]['latitude'] = $row['latitude'];
        $mbp_data[$dm]['longitude'] = $row['longitude'];
        $mbp_data[$dm]['id'] = $row['mbp_mt_nik'];
        $mbp_data[$dm]['operator_name'] = $row['operator_name'];


        $get_sp_active = DB::table('supplying_power as sp')
        ->select('sp.site_id', 'sp.finish')
        // ->Join('site as s', 'sp.site_id', 's.site_id')
        ->where('finish','=',null)
        ->where('mbp_id','=',$row['mbp_id'])
        ->first();

        // $mbp_data[$dm]['finish'] = @$get_sp_active->finish;
        $mbp_data[$dm]['site_id'] = @$get_sp_active->site_id;
      }
    }else{
      $mbp_data=$data_mbp;
    }

    


    $data_site = DB::table('site')
    // ->join('class', 'site.class_id', '=', 'class.class_id')
    ->select('site.site_id','site.is_allocated','site.status','site.site_name', 'site.class_id', /*'class.revenue',*/'site.latitude','site.longitude','site.alarm','site.band','site.kriteria_site')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('date_mainsfail','=',null)
    ->get();

    $data_site_mainfail_update = DB::table('site')
    ->select('site.site_id','site.is_allocated','site.status','site.site_name', 'site.class_id', /*'class.revenue',*/'site.latitude','site.longitude','site.alarm','site.band','site.kriteria_site')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('date_mainsfail','>',$date2)
    ->get();

    $a = json_decode($data_site, true);
    $b = json_decode($data_site_mainfail_update, true);
    $site_result = array_merge($a, $b); 

    
    if ($site_result==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED GET DATA';
      $res['data'] = $mbp_data;
      return response($res);
    }


    foreach ($site_result as $ds => $row)
    {
      if ($row['status'] > 0) {
        $site_data[$ds]['status'] = 'NORMAL';
      }else{
        $site_data[$ds]['status'] = 'MAINS FAIL';
      }
      if ($row['is_allocated'] > 0) {
        $site_data[$ds]['is_allocated'] = 'true';
      }else{
        $site_data[$ds]['is_allocated'] = 'false';
      }

      $site_data[$ds]['site_id'] = $row['site_id'];
      $site_data[$ds]['site_name'] = $row['site_name'];
      $site_data[$ds]['code_name'] = $row['site_id'];
      $site_data[$ds]['class_name'] = $row['class_id'];
      $site_data[$ds]['latitude'] = $row['latitude'];
      $site_data[$ds]['longitude'] = $row['longitude'];
      $site_data[$ds]['kriteria_site'] = $row['kriteria_site'];

      $alarm = explode(", ",@$row['alarm']);

      $band = explode("-",@$row['band']);

      $tmp='';
      $tmp_alarm='';
      $sd1=null;
      $sd2=null;
      $sd3=null;

      $bd1=null;
      $bd2=null;
      $bd3=null;

      $sitedown_status=null;
      $x=0;

      foreach (@$band as $bkey) {
         switch ($bkey) {
          case "2G":
          $bd1 = 1;
          break;
          case "3G":
          $bd2 = 3;
          break;
          case "4G":
          $bd3 = 5;
          break;
          default:
          $bd1 = null;
          break;
        }
      }

      foreach ($alarm as $key) {

        // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down

        $keyfix = str_replace(' ','',$key);

        switch ($keyfix) {
          case "UMTSCellUnavailable":
          $tmp = "3G OFF";
          $sd1 = 1;
          break;
          case "GSMCelloutofService":
          $tmp = "2G OFF";
          $sd2 = 3;
          break;
          case "CellUnavailable":
          $tmp = "4G OFF";
          $sd3 = 5;
          break;
          case "MODULERECTIFAIL":
          $tmp = "RECTI FAIL";
          break;
          case "MODULERECTFAIL":
          $tmp = "RECTI FAIL";
          break;
          case "MAINSFAIL":
          $tmp = "PLN OFF";
          break;
          case "GENSETFAILED":
          $tmp = "GENSET FAIL";
          break;
          default:
          $tmp=$keyfix;
          break;
        }
        // $cek = $key+1;
        // if (next($alarm)==null) {
        //   $tmp_alarm.=$tmp.'';
        // }else{
        //   $tmp_alarm.=$tmp.', ';
        // }

        if (@$alarm[$x+1]==null) {
          $tmp_alarm.=$tmp;
        }else{
          $tmp_alarm.=$tmp.', ';
        }
        $x=$x+1;
      }

      $sitedown_status = $sd1+$sd2+$sd3;
      $band_status = $bd1+$bd2+$bd3;
      $site_data[$ds]['c_band'] = @$band_status;
      $site_data[$ds]['c_alarm'] = @$sitedown_status;
      if (@$band_status!="") {      
        if ($sitedown_status==@$band_status) {
          $site_data[$ds]['status'] = 'DOWN';
        }
      }

      // $site_data[$ds]['site_down_count'] = $sitedown_status;
      $site_data[$ds]['alarm'] = $tmp_alarm;
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




  public function getMbpSiteDownCPO(Request $request){

    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $regional = $request->input('regional');

    $data_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.mbp_id','mbp.submission','mbp.rtpo_id','mbp.rtpo_id_home','mbp.cluster_id','mbp.mbp_name','mbp.status','mbp.latitude','mbp.longitude','user_mbp.mbp_mt_nik','users.name as operator_name')
    ->where('mbp.regional','=',$regional)
    ->get();


    $data_site = DB::table('site')
    ->select('site.site_id','site.is_allocated','site.status','site.site_name', 'site.class_id', /*'class.revenue',*/'site.latitude','site.longitude','site.alarm','site.band','site.kriteria_site')
    ->where('regional','=',$regional)
    ->where('date_mainsfail','>',$date2)
    ->get();

    $site_result = json_decode($data_site, true);
    $mbp_result = json_decode($data_mbp, true);
    



    if ($mbp_result==null) {
      #kasi pemberitahuan bahwa hasilnya null pentingkah?
      $mbp_data=$data_mbp;
    }
    foreach ($mbp_result as $dm => $row){

      if ($row['submission'] == 'DELAY') {
        $mbp_data[$dm]['status'] = 'DELAY';
      }else{
        $mbp_data[$dm]['status'] = $row['status'];
      }

      $mbp_data[$dm]['mbp_id'] = $row['mbp_id'];
      $mbp_data[$dm]['rtpo_id'] = $row['rtpo_id'];
      $mbp_data[$dm]['rtpo_id_home'] = $row['rtpo_id_home'];
      $mbp_data[$dm]['cluster_id'] = $row['cluster_id'];
      $mbp_data[$dm]['mbp_name'] = $row['mbp_name'];
      $mbp_data[$dm]['latitude'] = $row['latitude'];
      $mbp_data[$dm]['longitude'] = $row['longitude'];
      $mbp_data[$dm]['id'] = $row['mbp_mt_nik'];
      $mbp_data[$dm]['operator_name'] = $row['operator_name'];


      $get_sp_active = DB::table('supplying_power as sp')
      ->select('sp.site_id', 'sp.finish')
      ->where('finish','=',null)
      ->where('mbp_id','=',$row['mbp_id'])
      ->first();

      $mbp_data[$dm]['site_id'] = @$get_sp_active->site_id;
    }




    if ($site_result==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED GET DATA';
      $res['data'] = $mbp_data;
      return response($res);
    }


    foreach ($site_result as $ds => $row){
      if ($row['status'] > 0) {
        $site_data[$ds]['status'] = 'NORMAL';
      }else{
        $site_data[$ds]['status'] = 'MAINS FAIL';
      }
      if ($row['is_allocated'] > 0) {
        $site_data[$ds]['is_allocated'] = 'true';
      }else{
        $site_data[$ds]['is_allocated'] = 'false';
      }

      $site_data[$ds]['site_id'] = $row['site_id'];
      $site_data[$ds]['site_name'] = $row['site_name'];
      $site_data[$ds]['code_name'] = $row['site_id'];
      $site_data[$ds]['class_name'] = $row['class_id'];
      $site_data[$ds]['latitude'] = $row['latitude'];
      $site_data[$ds]['longitude'] = $row['longitude'];
      $site_data[$ds]['kriteria_site'] = $row['kriteria_site'];

      $alarm = explode(", ",@$row['alarm']);

      $band = explode("-",@$row['band']);

      $tmp='';
      $tmp_alarm='';
      $sd1=null;
      $sd2=null;
      $sd3=null;

      $bd1=null;
      $bd2=null;
      $bd3=null;

      $sitedown_status=null;
      $x=0;

      foreach (@$band as $bkey) {
       switch ($bkey) {
        case "2G":
        $bd1 = 1;
        break;
        case "3G":
        $bd2 = 3;
        break;
        case "4G":
        $bd3 = 5;
        break;
        default:
        $bd1 = null;
        break;
      }
    }

    foreach ($alarm as $key) {

        // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down

      $keyfix = str_replace(' ','',$key);

      switch ($keyfix) {
        case "UMTSCellUnavailable":
        $tmp = "3G OFF";
        $sd1 = 1;
        break;
        case "GSMCelloutofService":
        $tmp = "2G OFF";
        $sd2 = 3;
        break;
        case "CellUnavailable":
        $tmp = "4G OFF";
        $sd3 = 5;
        break;
        case "MODULERECTIFAIL":
        $tmp = "RECTI FAIL";
        break;
        case "MODULERECTFAIL":
        $tmp = "RECTI FAIL";
        break;
        case "MAINSFAIL":
        $tmp = "PLN OFF";
        break;
        case "GENSETFAILED":
        $tmp = "GENSET FAIL";
        break;
        default:
        $tmp=$keyfix;
        break;
      }
      if (@$alarm[$x+1]==null) {
        $tmp_alarm.=$tmp;
      }else{
        $tmp_alarm.=$tmp.', ';
      }
      $x=$x+1;
    }

    $sitedown_status = $sd1+$sd2+$sd3;
    $band_status = $bd1+$bd2+$bd3;
    $site_data[$ds]['c_band'] = @$band_status;
    $site_data[$ds]['c_alarm'] = @$sitedown_status;
    if (@$band_status!="") {      
      if ($sitedown_status==@$band_status) {
        $site_data[$ds]['status'] = 'DOWN';
      }
    }

    $site_data[$ds]['alarm'] = $tmp_alarm;
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



 public function getMbpSiteDownNS(Request $request){

    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $ns_id = $request->input('ns_id');

    $data_mbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->join('lookup_fmc_cluster as lfc', 'mbp.cluster_id', '=', 'lfc.cluster_id')
    ->select('mbp.mbp_id','mbp.submission','mbp.rtpo_id','mbp.rtpo_id_home','mbp.cluster_id','mbp.mbp_name','mbp.status','mbp.latitude','mbp.longitude','user_mbp.mbp_mt_nik','users.name as operator_name','lfc.ns_id')
    ->where('lfc.ns_id','=',$ns_id)
    ->groupBy('mbp.mbp_id')
    ->get();


    // return response($data_mbp);


    $data_site = DB::table('site')
    ->select('site.site_id','site.is_allocated','site.status','site.site_name', 'site.class_id', /*'class.revenue',*/'site.latitude','site.longitude','site.alarm','site.band','site.kriteria_site')
    ->where('ns_id','=',$ns_id)
    ->where('date_mainsfail','>',$date2)
    ->get();

    $site_result = json_decode($data_site, true);
    $mbp_result = json_decode($data_mbp, true);
    



    if ($mbp_result==null) {
      #kasi pemberitahuan bahwa hasilnya null pentingkah?
      $mbp_data=$data_mbp;
    }
    foreach ($mbp_result as $dm => $row){

      if ($row['submission'] == 'DELAY') {
        $mbp_data[$dm]['status'] = 'DELAY';
      }else{
        $mbp_data[$dm]['status'] = $row['status'];
      }

      $mbp_data[$dm]['mbp_id'] = $row['mbp_id'];
      $mbp_data[$dm]['rtpo_id'] = $row['rtpo_id'];
      $mbp_data[$dm]['rtpo_id_home'] = $row['rtpo_id_home'];
      $mbp_data[$dm]['cluster_id'] = $row['cluster_id'];
      $mbp_data[$dm]['mbp_name'] = $row['mbp_name'];
      $mbp_data[$dm]['latitude'] = $row['latitude'];
      $mbp_data[$dm]['longitude'] = $row['longitude'];
      $mbp_data[$dm]['id'] = $row['mbp_mt_nik'];
      $mbp_data[$dm]['operator_name'] = $row['operator_name'];


      $get_sp_active = DB::table('supplying_power as sp')
      ->select('sp.site_id', 'sp.finish')
      ->where('finish','=',null)
      ->where('mbp_id','=',$row['mbp_id'])
      ->first();

      $mbp_data[$dm]['site_id'] = @$get_sp_active->site_id;
    }




    if ($site_result==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED GET DATA';
      $res['data'] = $mbp_data;
      return response($res);
    }


    foreach ($site_result as $ds => $row){
      if ($row['status'] > 0) {
        $site_data[$ds]['status'] = 'NORMAL';
      }else{
        $site_data[$ds]['status'] = 'MAINS FAIL';
      }
      if ($row['is_allocated'] > 0) {
        $site_data[$ds]['is_allocated'] = 'true';
      }else{
        $site_data[$ds]['is_allocated'] = 'false';
      }

      $site_data[$ds]['site_id'] = $row['site_id'];
      $site_data[$ds]['site_name'] = $row['site_name'];
      $site_data[$ds]['code_name'] = $row['site_id'];
      $site_data[$ds]['class_name'] = $row['class_id'];
      $site_data[$ds]['latitude'] = $row['latitude'];
      $site_data[$ds]['longitude'] = $row['longitude'];
      $site_data[$ds]['kriteria_site'] = $row['kriteria_site'];

      $alarm = explode(", ",@$row['alarm']);

      $band = explode("-",@$row['band']);

      $tmp='';
      $tmp_alarm='';
      $sd1=null;
      $sd2=null;
      $sd3=null;

      $bd1=null;
      $bd2=null;
      $bd3=null;

      $sitedown_status=null;
      $x=0;

      foreach (@$band as $bkey) {
       switch ($bkey) {
        case "2G":
        $bd1 = 1;
        break;
        case "3G":
        $bd2 = 3;
        break;
        case "4G":
        $bd3 = 5;
        break;
        default:
        $bd1 = null;
        break;
      }
    }

    foreach ($alarm as $key) {

        // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down

      $keyfix = str_replace(' ','',$key);

      switch ($keyfix) {
        case "UMTSCellUnavailable":
        $tmp = "3G OFF";
        $sd1 = 1;
        break;
        case "GSMCelloutofService":
        $tmp = "2G OFF";
        $sd2 = 3;
        break;
        case "CellUnavailable":
        $tmp = "4G OFF";
        $sd3 = 5;
        break;
        case "MODULERECTIFAIL":
        $tmp = "RECTI FAIL";
        break;
        case "MODULERECTFAIL":
        $tmp = "RECTI FAIL";
        break;
        case "MAINSFAIL":
        $tmp = "PLN OFF";
        break;
        case "GENSETFAILED":
        $tmp = "GENSET FAIL";
        break;
        default:
        $tmp=$keyfix;
        break;
      }
      if (@$alarm[$x+1]==null) {
        $tmp_alarm.=$tmp;
      }else{
        $tmp_alarm.=$tmp.', ';
      }
      $x=$x+1;
    }

    $sitedown_status = $sd1+$sd2+$sd3;
    $band_status = $bd1+$bd2+$bd3;
    $site_data[$ds]['c_band'] = @$band_status;
    $site_data[$ds]['c_alarm'] = @$sitedown_status;
    if (@$band_status!="") {      
      if ($sitedown_status==@$band_status) {
        $site_data[$ds]['status'] = 'DOWN';
      }
    }

    $site_data[$ds]['alarm'] = $tmp_alarm;
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


  public function getAlarmSiteCorrective(Request $request){

    $date_now = date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);    

    $rtpo_id = $request->input('rtpo_id');

    $data_site_mainfail_update = DB::table('site')
    ->select('site_id','is_allocated','status','site_name', 'class_id', /*'class.revenue',*/'latitude','longitude','alarm','kriteria_site')
    ->where('rtpo_id',$rtpo_id)
    ->where('date_mainsfail','>',$date2)
    ->get();

    $site_result = json_decode($data_site_mainfail_update, true);


    if ($site_result==null) {
      $res['success'] = false;
      $res['message'] = 'FAILED GET DATA';
      //$res['data'] = $mbp_data;
      return response($res);
    }

    $dx = 0;
    foreach ($site_result as $ds => $row)
    {
      if ($row['status'] > 0) {
        $site_data[$dx]['status'] = 'NORMAL';
      }else{
        $site_data[$dx]['status'] = 'MAINS FAIL';
      }
      if ($row['is_allocated'] > 0) {
        $site_data[$dx]['is_allocated'] = 'true';
      }else{
        $site_data[$dx]['is_allocated'] = 'false';
      }

      $site_data[$dx]['site_id'] = $row['site_id'];
      $site_data[$dx]['site_name'] = $row['site_name'];
      $site_data[$dx]['code_name'] = $row['site_id'];
      $site_data[$dx]['class_name'] = $row['class_id'];
      $site_data[$dx]['latitude'] = $row['latitude'];
      $site_data[$dx]['longitude'] = $row['longitude'];
      $site_data[$dx]['kriteria_site'] = $row['kriteria_site'];
      $site_data[$dx]['alarm'] = $row['alarm'];

      $alarm = explode(", ",$row['alarm']);

      $tmp='';
      $tmp_alarm='';
      $sd1=null;
      $sd2=null;
      $sd3=null;
      $sitedown_status=null;
      $x=0;
      foreach ($alarm as $key) {

        $keyfix = str_replace(' ','',$key);

        switch ($keyfix) {
          case "UMTSCellUnavailable":
          // $keyfix = str_replace(' ','',$key);
          $tmp = "3G OFF";
          $sd1 = 1;
          // if (@$alarm[$x+1]==null) {
          //   $tmp_alarm.=$tmp;
          // }else{
          //   $tmp_alarm.=$tmp.', ';
          // }
          break;
          case "GSMCelloutofService":
          // $keyfix = str_replace(' ','',$key);
          $tmp = "2G OFF";
          $sd2 = 1;
          // if (@$alarm[$x+1]==null) {
          //   $tmp_alarm.=$tmp;
          // }else{
          //   $tmp_alarm.=$tmp.', ';
          // }
          break;
          case "CellUnavailable":
          // $keyfix = str_replace(' ','',$key);
          $tmp = "4G OFF";
          $sd3 = 1;
          // if (@$alarm[$x+1]==null) {
          //   $tmp_alarm.=$tmp;
          // }else{
          //   $tmp_alarm.=$tmp.', ';
          // }
          break;
          case "MODULERECTIFAIL":
          $tmp = "";
          break;
          case "MODULERECTFAIL":
          $tmp = "";
          break;
          case "MAINSFAIL":
          $tmp = "";
          break;
          case "GENSETFAILED":
          $tmp = "";
          break;
          default:
          $tmp=$keyfix;
          break;
        }

        $alarmtmp1 = str_replace(' ','',@$alarm[$x+1]);
        if (@$alarmtmp1=="UMTSCellUnavailable") {
          $tmp_alarm.=$tmp.', ';
        }elseif (@$alarmtmp1=="GSMCelloutofService") {
          $tmp_alarm.=$tmp.', ';
        }elseif (@$alarmtmp1=="CellUnavailable") {
          $tmp_alarm.=$tmp.', ';
        }else{
          $tmp_alarm.=$tmp;
        }
        $x=$x+1;
        
      }

      $sitedown_status = $sd1+$sd2+$sd3;
      if ($sitedown_status>=2) {
        
        // $site_data[$ds]['site_down'] = true;
        $site_data[$dx]['status'] = 'DOWN';
        //$site_data[$dx]['alarm'] = $tmp_alarm;
        $dx = $dx+1;

      }else{
        // $site_data[$ds]['site_down'] = false;
        
        unset($site_data[$dx]);
      }
      // $site_data[$ds]['site_down_count'] = $sitedown_status;
    }

    $data['data_site'] = $site_data;


    if ($data_site_mainfail_update) {
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