<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DateTime;
// use App\Bts;
use DB;

class PlaygroundController extends Controller{
    public function getMyMbpPaginate(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $date_new_count = date('Y-m-d');
    
        // $delete_date_strtotime = strtotime($date_now." -1 day");
        // $delete_date = date('Y-m-d H:i:s',$delete_date_strtotime);
    
        $rtpo_id = $request->input('rtpo_id');
        $page = $request->input('page');
        $search = $request->input('search');
        $filter = $request->input('filter');

        $limit = 20;
        $offset = ($page-1)*$limit;
    
    
        // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
        $data_site = DB::table('mbp')
                    // ->leftJoin('supplying_power as sp', 'mbp.mbp_id', 'sp.mbp_id')
                    // ->leftJoin('site as s', 'sp.site_id', 's.site_id')
                    ->join('user_mbp', 'mbp.mbp_id', 'user_mbp.mbp_id')
                    ->join('users', 'user_mbp.username', 'users.username')
                    ->join('mbp_status', 'mbp.status', 'mbp_status.status')
                    ->select('mbp.*','users.id as user_id','users.name as operator_name','mbp.latitude as m_lat','mbp.longitude as m_lon','bobot')
                    // ->where('finish','=',null)
                    // ->whereNull('sp.finish')
                    ->where('mbp.rtpo_id','=',$rtpo_id)
                    ->where('mbp.rtpo_id_home','=',$rtpo_id)
                    ->whereraw('(mbp.mbp_id like "%'.$search.'%" or operator_name like "%'.$search.'%")')
                    ->offset($offset)
                    ->limit($limit)
                    // ->orderBy('mbp_status.bobot', 'ASC')
                    ->get();
        
        // $data_site= DB::select("SELECT")
    
        $mbp_result = json_decode($data_site, true);
    
        if (!$mbp_result) {
            // $res['success'] = false;
            // $res['message'] = 'Cannot find data!';
            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['data'] = $mbp_result;
            return response($res);
        }
    
        $rc = new RecommendationController;
        foreach ($mbp_result as $param => $row) {
        
            $data[$param]['mbp_id']         = $mbp_result[$param]['mbp_id'];
            $data[$param]['bobot']          = $mbp_result[$param]['bobot'];
            $data[$param]['rtpo_id']        = $mbp_result[$param]['rtpo_id'];
            $data[$param]['rtpo_id_home']   = $mbp_result[$param]['rtpo_id_home'];
            $data[$param]['cluster_id']     = $mbp_result[$param]['cluster_id'];
            $data[$param]['mbp_name']       = $mbp_result[$param]['mbp_name'];
            $data[$param]['regional']       = $mbp_result[$param]['regional'];
            $data[$param]['status']         = $mbp_result[$param]['status'];
            $data[$param]['submission']     = $mbp_result[$param]['submission'];
            $data[$param]['submission_id']  = $mbp_result[$param]['submission_id'];
            $data[$param]['message_id']     = $mbp_result[$param]['message_id'];
            $data[$param]['active_at']      = $mbp_result[$param]['active_at'];
            $data[$param]['latitude']       = $mbp_result[$param]['latitude'];
            $data[$param]['longitude']      = $mbp_result[$param]['longitude'];
            $data[$param]['fmc']            = $mbp_result[$param]['fmc'];
            $data[$param]['active']         = $mbp_result[$param]['active'];
            $data[$param]['last_update']    = $mbp_result[$param]['last_update'];
            $data[$param]['user_id']        = $mbp_result[$param]['user_id'];
            $data[$param]['operator_name']  = $mbp_result[$param]['operator_name'];
            // $data[$param]['time new count'] = $date_new_count;
        
            // $task_count = DB::table('supplying_power')
            $get_sp = DB::table('supplying_power')
                    ->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
                    ->where('date_finish','!=',null)
                    ->where('date_waiting','>',$date_new_count);
        
            $task_count = $get_sp->count();
        
            $sp_done = $get_sp->select('date_finish')
                    ->orderBy('date_finish', 'desc')
                    ->first();
        
            $is_resting = 0;
            if ($sp_done!=null) {
        
                $date1=strtotime($date_now);
                $date2=strtotime($sp_done->date_finish);
        
                if (round(($date1-$date2) / 3600) < 1) {
                $is_resting = 1;
                }
        
            }
        
            $get_sp_active = DB::table('supplying_power as sp')
                            ->select('s.latitude as s_lat','s.longitude as s_lon'/*,'mbp.latitude as m_lat','mbp.longitude as m_lon'*/, 'sp.finish', 'sp.site_id', 's.site_name', 'sp.date_onprogress', 'sp.date_checkin')
                            ->Join('site as s', 'sp.site_id', 's.site_id')
                            ->where('finish','=',null)
                            ->where('mbp_id','=',$mbp_result[$param]['mbp_id'])
                            ->first();
                            // if ($get_sp_active!=null) {
                            //   # code...
                            // }
        
            $data[$param]['site_latitude']  = @$get_sp_active->s_lat;
            $data[$param]['site_longitude'] = @$get_sp_active->s_lon;
            $data[$param]['site_id']        = @$get_sp_active->site_id;
            $data[$param]['site_name']      = @$get_sp_active->site_name;
            $data[$param]['mbp_latitude']   = @$mbp_result[$param]['m_lat'];
            $data[$param]['mbp_longitude']  = @$mbp_result[$param]['m_lon'];
            $time_req = null;
            $waktu_tempuh = null;
            if ($get_sp_active!=null) {
                $get_distance = @$rc->distance($get_sp_active->s_lat, $get_sp_active->s_lon, $mbp_result[$param]['m_lat'], $mbp_result[$param]['m_lon'], 'K');
                $data[$param]['distance'] = @number_format($get_distance,1).' km';
        
                if ($mbp_result[$param]['status']=='ON_PROGRESS') {
                    $time_req = date('H:i',strtotime($get_sp_active->date_onprogress));
                    $datetime2 = new DateTime($get_sp_active->date_onprogress);
                    $datetime3 = new DateTime($date_now);
                    $waktu_jalan = $datetime2->diff($datetime3);
                    $hours   = sprintf("%02d", $waktu_jalan->format('%H')); 
                    $minutes = sprintf("%02d", $waktu_jalan->format('%i'));
            
                    $time_req = $hours .':'.$minutes;
                    // $time_req = $get_sp_active->date_onprogress;
                }
                elseif ($mbp_result[$param]['status']=='CHECK_IN') {
        
                    $datetime1 = new DateTime($get_sp_active->date_onprogress);
                    $datetime2 = new DateTime($get_sp_active->date_checkin);
                    $datetime3 = new DateTime($date_now);
                    $difference = $datetime1->diff($datetime2);
                    $running_bc = $datetime2->diff($datetime3);
            
                    $hours   = sprintf("%02d", $difference->format('%H')); 
                    $minutes = sprintf("%02d", $difference->format('%i'));
                    $second = sprintf("%02d", $difference->format('%s'));
            
                    $hours_bc = sprintf("%02d", $running_bc->format('%H')); 
                    $minutes_bc = sprintf("%02d", $running_bc->format('%i')); 
                    // $time_req = $hours .':'.$minutes.':'.$second;
                    $running_backup = $hours_bc .':'.$minutes_bc;
                    $waktu_tempuh = $hours .':'.$minutes;
                    $data[$param]['distance'] = @number_format($get_distance,1).' km '/*.'(waktu tempuh : '.$waktu_tempuh.')'*/;
                    // $data[$param]['traveling_time'] = $waktu_tempuh;
            
                    // $time_req = $hours .':'.$minutes;
                    $time_req = $running_backup;
                    // $time_req = $get_sp_active->date_checkin;
                }
        
                if ( $data[$param]['submission']=='DELAY') {
        
                    // $datetime1 = new DateTime($data[$param]['active_at']);
                    // $datetime2 = new DateTime($date_now);
                    // $running_bc = $datetime2->diff($datetime1);
            
                    $data[$param]['status'] = 'DELAY';
                    $to_time = strtotime($data[$param]['active_at']);
                    $from_time = strtotime($date_now);
                    $minutes = round(abs($to_time - $from_time) / 60);
                    $delay_time = @$minutes;
                }
            }
            else {
                $data[$param]['distance'] = '-';
            }
            
            // $data[$param]['onpro'] = @$get_sp_active->date_onprogress;
            // $data[$param]['chek'] = @$get_sp_active->date_checkin;
            $data[$param]['traveling_time'] = @$waktu_tempuh;
            $data[$param]['time'] = @$time_req;
            $data[$param]['delay_time'] = @$delay_time;
            $data[$param]['task_count'] = $task_count;
            $data[$param]['is resting'] = $is_resting;
            // $data[$param]['tme'] = $waktu_nganggur;
        
            $mbp_id[$param]  = $mbp_result[$param]['mbp_id'];
            $bobot[$param] = $mbp_result[$param]['bobot'];
    
        }
    
        // usort($data, array($this, 'sort_by_counttask'));
        // usort($data, array($this, 'sort_by_bobot'));
    
        array_multisort($bobot, SORT_ASC, $mbp_id, SORT_ASC, $data);
    
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
    }

}