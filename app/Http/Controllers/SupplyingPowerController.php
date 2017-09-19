<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class SupplyingPowerController extends Controller
{
    /**
     * Get user by id
     *
     * URL /user/{id}
     */
    public function getListHistorySupplyingPower(Request $request){

      // $type_approval = $request->input('type_approval');
      // $type_id = $request->input('type_id');
      $user_id = $request->input('user_id');
      date_default_timezone_set("Asia/Jakarta");

      // cari suertype
      $check_type = DB::table('users')
      ->select('*')
      ->where('id','=',$user_id)
      ->first();

      // $type_approval['type'] = $check_type->user_type;
        
      if($check_type->user_type=='RTPO'){

        $check_rtpo = DB::table('user_rtpo')
        ->select('*')
        ->where('user_id','=',$check_type->id)
        ->first();

        $btss = DB::table('supplying_power')
        ->join('users', 'supplying_power.user_id', '=', 'users.id')
        ->join('user_rtpo', 'users.id', '=', 'user_rtpo.user_id')
        ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
        ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      // // ->join('users', 'user_mbp.user_id', '=', 'users.id')
      //   ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','supplying_power.date_mainsfail','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','supplying_power.finish')
        ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','supplying_power.date_waiting','supplying_power.finish')
        ->where('rtpo.rtpo_id','=',$check_rtpo->rtpo_id)
        ->where('supplying_power.finish','!=',NULL)
        ->get();

        $result = json_decode($btss, true);
        foreach ($result as $param => $row) {

          $newDate = date("d-m-Y", strtotime($row['date_waiting'].''));
          $data[$param]['sp_id']        = $row['sp_id'];
          $data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
          $data[$param]['rtpo_name']    = $row['person_in_charge'].'';
          $data[$param]['mbp_name']     = $row['mbp_name'].'';
          $data[$param]['site_name']    = $row['site_name'].'';
          $data[$param]['date_request'] = $newDate;
          $data[$param]['finish']       = $row['finish'].'';
        }

        if ($btss) {
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $data;

          return response($res);
        }else{
          $polys['success'] = false;
          $polys['message'] = 'Cannot find polys!';

          return response($btss);
        }

      }else if($check_type->user_type=='MBP'){

        $check_mbp = DB::table('user_mbp')
        ->select('*')
        ->where('user_id','=',$check_type->id)
        ->first();

        $btss = DB::table('supplying_power')
        ->join('users', 'supplying_power.user_id', '=', 'users.id')
        ->join('user_rtpo', 'users.id', '=', 'user_rtpo.user_id')
        ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
        ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      // ->join('users', 'user_mbp.user_id', '=', 'users.id')
        // ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','supplying_power.date_mainsfail','supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','supplying_power.finish')
        ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','supplying_power.date_waiting','supplying_power.finish')
        // ->where('rtpo.rtpo_id','=',$rtpo_id)
        ->where('mbp.mbp_id','=',$check_mbp->mbp_id)
        ->where('supplying_power.finish','!=',NULL)
        ->get();
        $result = json_decode($btss, true);

        foreach ($result as $param => $row) {

          $newDate = date("d-M-Y", strtotime($row['date_waiting'].''));

          $data[$param]['sp_id']        = $row['sp_id'];
          $data[$param]['sp_name']      = 'SP-'.$row['sp_id'];
          $data[$param]['rtpo_name']    = $row['person_in_charge'].'';
          $data[$param]['mbp_name']     = $row['mbp_name'].'';
          $data[$param]['site_name']    = $row['site_name'].'';
          $data[$param]['date_request'] = $newDate;
          $data[$param]['finish']       = $row['finish'].'';
        }

        if ($btss) {
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $data;

          return response($res);
        }else{
          $polys['success'] = false;
          $polys['message'] = 'Cannot find polys!';

          return response($btss);
        }

      }else{

        $res['success'] = false;
        $res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
        
        return response($res);
      }
    }
    public function getDetailHistorySupplyingPower(Request $request){

      date_default_timezone_set("Asia/Jakarta");
      $sp_id = $request->input('sp_id');

      $btss = DB::table('supplying_power')
      ->join('users', 'supplying_power.user_id', '=', 'users.id')
      ->join('user_rtpo', 'users.id', '=', 'user_rtpo.user_id')
      ->join('rtpo', 'user_rtpo.rtpo_id', '=', 'rtpo.rtpo_id')
      ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
      ->join('site', 'supplying_power.site_id', '=', 'site.site_id')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      // // ->join('users', 'user_mbp.user_id', '=', 'users.id') DATE_FORMAT(NAME_COLUMN, "%d/%l/%Y %H:%i:%s") AS 'NAME'
      // 'DATE_FORMAT(supplying_power.date_checkin, %d/%M/%Y %H:%i:%s) AS date_checkin'
        ->select('supplying_power.sp_id','users.name as rtpo_name','mbp.mbp_name', 'site.site_name'/*,'supplying_power.date_mainsfail'*/,'supplying_power.date_waiting','supplying_power.date_onprogress','supplying_power.date_checkin','supplying_power.date_finish','supplying_power.finish')
      // ->select('supplying_power.sp_id','users.name as person_in_charge','mbp.mbp_name', 'site.site_name','supplying_power.date_waiting','supplying_power.finish')
      ->where('supplying_power.sp_id','=',$sp_id)
      ->where('supplying_power.finish','!=',NULL)
      ->first();

      if ($btss) {

        // $date=date_create("2013-03-15");
        // $data = date_format($date,"d-M-Y H:i:s");

        $data['sp_id'] = $btss->sp_id.'';
        $data['rtpo_name'] = $btss->rtpo_name.'';
        $data['mbp_name'] = $btss->mbp_name.'';
        $data['site_name'] = $btss->site_name.'';
        $data['date_waiting'] = $this->setDatedMYHis($btss->date_waiting.'');
        $data['date_onprogress'] = $this->setDatedMYHis($btss->date_onprogress.'');
        $data['date_checkin'] = $this->setDatedMYHis($btss->date_checkin.'')/*date("d-M-Y H:i:s", strtotime($btss->date_checkin.''))*/;
        $data['date_finish'] = $this->setDatedMYHis($btss->date_finish.'');
        $data['finish'] = $btss->finish.'';


        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        // $res['data'] = $btss;
        $res['data'] = $data;

        return response($res);
      }else{
        $polys['success'] = false;
        $polys['message'] = 'CANNOT_FIND_DATA';

        return response($btss);
      }
    }
    public function setDatedMYHis($date){
      if ($date==null) {
        return "";
      }else if ($date=='0000-00-00 00:00:00') {
        return "";
      }else{
        return date("d-M-Y H:i:s", strtotime($date.''));
        // return strtotime($date.'');
      }
    }
  }