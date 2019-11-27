<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DateTime;
// use App\Bts;
use DB;
class MbpControllerNew extends Controller
{
  public function getDetailMbp(Request $request){
    $mbp_id = $request->input('mbp_id');
    
    $mbp_data = DB::table('mbp as m')
    ->select('*'/*, DB::raw('(case when (submission = "DELAY") then "DELAY" else m.status end) as status')*/)
    ->where('m.mbp_id','=',$mbp_id)
    ->first();

    if ($mbp_data->rtpo_id != $mbp_data->rtpo_id_home) {
      $borrowed=true;
    }else{
      $borrowed=false;
    }

    if ($mbp_data) {

      if ($mbp_data->submission=='DELAY') {

        // $user_mbp_data = DB::table('mbp as m')
        // ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
        // ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
        // ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
        // ->join('users as u', 'um.username', 'u.username')
        // ->join('message as msg', 'm.message_id', 'msg.id')
        // ->select('*', 'm.status as mbp_status', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now')
        // ->where('m.mbp_id','=',$mbp_id)
        // ->first();
        
        // $data['get_in'] = "DELAY";
        // $data['status'] = 'DELAY';
        // $data['borrowed'] = $borrowed;
        // $data['class_name'] = '-';

        // $data['rtpo_id_home'] = $user_mbp_data->mbp_rtpo_id_home;
        // $data['rtpo_id_now'] = $user_mbp_data->mbp_rtpo_id;
        // $data['rtpo_name_home'] = $user_mbp_data->rtpo_name_home;
        // $data['rtpo_name_now'] = $user_mbp_data->rtpo_name_now;

        // $data['fmc_id'] = $user_mbp_data->fmc_id;
        // $data['fmc_name'] = $user_mbp_data->fmc;

        // $data['mbp_name'] = $user_mbp_data->mbp_name;
        // $data['name'] = $user_mbp_data->name;
        // $data['phone'] = $user_mbp_data->phone;
        // $data['latitude'] = $user_mbp_data->latitude;
        // $data['longitude'] = $user_mbp_data->longitude;
        // $data['subject'] = $user_mbp_data->subject;
        // $data['text_message'] = $user_mbp_data->text_message;
        // $data['time'] = $this->setDatedMYHis($user_mbp_data->active_at);

        //-----------------------------------------------------------------------------


        $user_mbp_data = DB::table('mbp as m')
        ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
        ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
        ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
        ->join('users as u', 'um.username', 'u.username')
        ->join('supplying_power as sp', 'm.mbp_id', 'sp.mbp_id')
        ->join('site as s', 'sp.site_id', 's.site_id')
        ->join('message as msg', 'm.message_id', 'msg.id')
        ->select('*', 'm.status as mbp_status', 's.latitude as site_latitude', 's.longitude as site_longitude', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now','sp.user_rtpo_cn as ticket_by')
        ->where('m.mbp_id','=',$mbp_id)
        ->where('sp.finish','=',null)
        ->first();
        // $data['status'] = @$user_mbp_data->mbp_status;
        $data['ticket_by'] = $user_mbp_data->ticket_by;
        $data['telegram_username'] = "";
        $data['status'] = 'DELAY';
        $data['mbp_name'] = @$user_mbp_data->mbp_name;
        $data['name'] = @$user_mbp_data->name;
        $data['phone'] = @$user_mbp_data->phone;
        $data['mbp_latitude'] = @$user_mbp_data->latitude;
        $data['mbp_longitude'] = @$user_mbp_data->longitude;
        $data['site_name'] = @$user_mbp_data->site_name;
        $data['code_name'] = @$user_mbp_data->site_id;
        $data['class_name'] = @$user_mbp_data->site_class;
        $data['latitude'] = @$user_mbp_data->site_latitude;
        $data['longitude'] = @$user_mbp_data->site_longitude;
        $data['borrowed'] = @$borrowed;
        $data['date_waiting'] = @strtotime(@$user_mbp_data->date_waiting);
        $data['date_onprogress'] = @strtotime(@$user_mbp_data->date_onprogress);
        $data['date_checkin'] = @strtotime(@$user_mbp_data->date_checkin);

        $data['fmc_id'] = @$user_mbp_data->fmc_id;
        $data['fmc_name'] = @$user_mbp_data->fmc;

        $data['rtpo_id_home'] = @$user_mbp_data->mbp_rtpo_id_home;
        $data['rtpo_id_now'] = @$user_mbp_data->mbp_rtpo_id;
        $data['rtpo_name_home'] = @$user_mbp_data->rtpo_name_home;
        $data['rtpo_name_now'] = @$user_mbp_data->rtpo_name_now;
        $data['subject'] = @$user_mbp_data->subject;
        $data['text_message'] = @$user_mbp_data->text_message;
        $data['time'] = @$this->setDatedMYHis($user_mbp_data->active_at);



        $res['success'] = "OK";
        $res['message'] = 'Success';
        $res['data'] = $data;
        return response($res);
      }
      switch ($mbp_data->status) {
        case "AVAILABLE":
        $user_mbp_data = DB::table('mbp as m')
        ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
        ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
        ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
        ->join('users as u', 'um.username', 'u.username')
        ->select('*', 'm.status as mbp_status', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now')
        ->where('m.mbp_id','=',$mbp_id)
        ->first();

        $data['get_in'] = 'AVAILABLE';
        $data['status'] = $user_mbp_data->mbp_status;
        $data['mbp_name'] = $user_mbp_data->mbp_name;
        $data['name'] = $user_mbp_data->name;
        $data['phone'] = $user_mbp_data->phone;
        $data['latitude'] = $user_mbp_data->latitude;
        $data['longitude'] = $user_mbp_data->longitude;
        $data['borrowed'] = $borrowed;
        $data['class_name'] = '-';

        $data['fmc_id'] = $user_mbp_data->fmc_id;
        $data['fmc_name'] = $user_mbp_data->fmc;
        
        $data['rtpo_id_home'] = $user_mbp_data->mbp_rtpo_id_home;
        $data['rtpo_id_now'] = $user_mbp_data->mbp_rtpo_id;
        $data['rtpo_name_home'] = $user_mbp_data->rtpo_name_home;
        $data['rtpo_name_now'] = $user_mbp_data->rtpo_name_now;

        $res['success'] = "OK";
        $res['message'] = 'Success';
        $res['data'] = $data;

        return response($res);

        break;
        case "UNAVAILABLE":

        $user_mbp_data = DB::table('mbp as m')
        ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
        ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
        ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
        ->join('users as u', 'um.username', 'u.username')
        ->join('message as msg', 'm.message_id', 'msg.id')   
        ->select('*', 'm.status as mbp_status', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home','m.last_update as lu', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now')
        ->where('m.mbp_id','=',$mbp_id)
        ->first();

        $data['get_in'] = "UNAVAILABLE";
        $data['status'] = "UNAVAILABLE";
        $data['borrowed'] = $borrowed;
        $data['class_name'] = '-';

        $data['fmc_id'] = @$user_mbp_data->fmc_id;
        $data['fmc_name'] = @$user_mbp_data->fmc;

        $data['rtpo_id_home'] = @$user_mbp_data->mbp_rtpo_id_home;
        $data['rtpo_id_now'] = @$user_mbp_data->mbp_rtpo_id;
        $data['rtpo_name_home'] = @$user_mbp_data->rtpo_name_home;
        $data['rtpo_name_now'] = @$user_mbp_data->rtpo_name_now;

        $data['mbp_name'] = @$user_mbp_data->mbp_name;
        $data['name'] = @$user_mbp_data->name;
        $data['phone'] = @$user_mbp_data->phone;
        $data['latitude'] = @$user_mbp_data->latitude;
        $data['longitude'] = @$user_mbp_data->longitude;
        $data['subject'] = @$user_mbp_data->subject;
        $data['text_message'] = @$user_mbp_data->text_message;
        $data['time'] = @$this->setDatedMYHis($user_mbp_data->active_at);

        // $data['time a'] = date('i');

        // $dateb = strtotime(@$user_mbp_data->lu);
        // $data['time b'] = date('i', $dateb);
        // $data['time c'] = date('i', $dateb) + 1;
        // $data['time lu'] = @$user_mbp_data->lu;

      //    if (date('i') == date('i', $mbp_trouble->respon_date)) {//----------------------
      //   $res['success'] = true;
      //   $res['message'] = 'SUCCESS';
      //   return response($res);
      // }



        $res['success'] = "OK";
        $res['message'] = 'Success';
        $res['data'] = $data;
        return response($res);
        
        break;
        default:
        $user_mbp_data = DB::table('mbp as m')
        ->join('rtpo as rh', 'm.rtpo_id_home', 'rh.rtpo_id')
        ->join('rtpo as rn', 'm.rtpo_id', 'rn.rtpo_id')
        ->join('user_mbp as um', 'm.mbp_id', 'um.mbp_id')
        ->join('users as u', 'um.username', 'u.username')
        ->join('supplying_power as sp', 'm.mbp_id', 'sp.mbp_id')
        ->join('site as s', 'sp.site_id', 's.site_id')
        ->select('*', 'm.status as mbp_status', 's.latitude as site_latitude', 's.longitude as site_longitude', 'm.rtpo_id as mbp_rtpo_id', 'm.rtpo_id_home as mbp_rtpo_id_home', 'rh.rtpo_name as rtpo_name_home', 'rn.rtpo_name as rtpo_name_now','sp.user_rtpo_cn as ticket_by')
        ->where('m.mbp_id','=',$mbp_id)
        ->where('sp.finish','=',null)
        ->first();

        $data['get_in'] = "DEFAULT";
        $data['ticket_by'] = $user_mbp_data->ticket_by;
        $data['telegram_username'] = "";
        $data['status'] = $user_mbp_data->mbp_status;
        $data['mbp_name'] = $user_mbp_data->mbp_name;
        $data['name'] = $user_mbp_data->name;
        $data['phone'] = $user_mbp_data->phone;
        $data['mbp_latitude'] = $user_mbp_data->latitude;
        $data['mbp_longitude'] = $user_mbp_data->longitude;
        $data['site_name'] = $user_mbp_data->site_name;
        $data['code_name'] = $user_mbp_data->site_id;
        $data['class_name'] = $user_mbp_data->site_class;
        $data['latitude'] = $user_mbp_data->site_latitude;
        $data['longitude'] = $user_mbp_data->site_longitude;
        $data['borrowed'] = $borrowed;
        $data['date_waiting'] = @strtotime(@$user_mbp_data->date_waiting);
        $data['date_onprogress'] = @strtotime(@$user_mbp_data->date_onprogress);
        $data['date_checkin'] = @strtotime(@$user_mbp_data->date_checkin);

        $data['fmc_id'] = $user_mbp_data->fmc_id;
        $data['fmc_name'] = $user_mbp_data->fmc;

        $data['rtpo_id_home'] = $user_mbp_data->mbp_rtpo_id_home;
        $data['rtpo_id_now'] = $user_mbp_data->mbp_rtpo_id;
        $data['rtpo_name_home'] = $user_mbp_data->rtpo_name_home;
        $data['rtpo_name_now'] = $user_mbp_data->rtpo_name_now;

        $res['success'] = "OK";
        $res['message'] = 'Success';
        $res['data'] = $data;
        return response($res);
        break;
      }
    }
  }



}