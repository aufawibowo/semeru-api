<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DateTime;
// use App\Bts;
use DB;
class PlaygroundController extends Controller{
    public function getListFilterName(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        $area = @$request->input('area');
        $regional = @$request->input('regional');
        $ns_id = @$request->input('ns_id');

        if ($area!=''){
            $list_regional = DB::table('regional')
            ->select('regional as scope_id','regional_name as scope_name')
            ->get();

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['data'] = $list_regional;            
        } elseif($regional!=''){
            $list_ns = DB::table('ns')
            ->select('ns_id as scope_id','ns_name as scope_name')
            ->where('regional',$regional)
            ->where('status',1)
            ->whereNotIn('ns_id',[26,27])
            ->get();

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['data'] = $list_ns;
        } elseif($ns_id!=''){
            $list_rtpo = DB::table('lookup_fmc_cluster')
            ->select('rtpo_id as scope_id','rtpo as scope_name')
            ->where('ns_id',$ns_id)
            ->where('status',1)
            ->whereNotIn('ns_id',[26,27])
            ->groupBy('rtpo_id')
            ->get();

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['data'] = $list_rtpo;
        }

        return response($res);
    }

    public function getMbpSite(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $month_now = date('Y-m');

        $now = date('Y-m-d H:i:s');
        $min2Day = date('Y-m-d H:i:s', strtotime($now.' - 2 days'));
        $data=[];
        // echo "string";
        // print_r($request->input('fmc_id'));
        // exit;
        // $query_mbp = DB::table('mbp');
        
        // if(!empty($request->input('fmc_id'))){
        //  $query_mbp->where(['fmc_id'=>$request->input('fmc_id')]);
        // }
        // if(!empty($request->input('cluster_id'))){
        //  $query_mbp->where(['cluster_id'=>$request->input('cluster_id')]);
        // }
        
        if(empty($request->input('arr_cluster_id'))){
            return response(['success'=>false,'message'=>'Cluster cannot be null']);
        }
        // exit('work');
        $arr_cluster_id = $request->input('arr_cluster_id');
        foreach ($arr_cluster_id as $cluster_id) {
            
            $query_mbp = DB::table('mbp')->select('cluster',DB::raw("COUNT(status) as count_status"),'status');
            $query_mbp->where('cluster_id',$cluster_id);
            $query_mbp->groupBy('status');
            $query_mbp->orderBy('cluster_id');
            $res_mbp = $query_mbp->get();



            $site_mainfail = DB::table('site')->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 'alarm', 'band','cluster');
            $site_mainfail->where('cluster_id',$cluster_id);
            $site_mainfail->where('date_mainsfail','>',$min2Day);
            $site_mainfail->groupBy('site_id');
            $ref_site = $site_mainfail->get();

            $count_site_main_fail = 0;
            $count_site_down = 0;

            $res_site=[];
            foreach ($ref_site as $v) {
                
                $info_alarm="";
                $alarms = explode(", ",@$v->alarm);
                $band_2g = 0; $band_3g = 0; $band_4g = 0;
                $off_2g = 0; $off_3g = 0; $off_4g = 0;

                $bands = explode("-",$v->band);

                foreach (@$bands as $band) {
                    $keyfix = str_replace(' ','',$band);
                    switch ($keyfix) {
                        case "2G": $band_2g = 1; break;
                        case "3G": $band_3g = 1; break;
                        case "4G": $band_4g = 1; break;
                        default:  break;
                    }
                }

                foreach ($alarms as $alarm) {
                    // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                    $keyfix = str_replace(' ','',$alarm);
                    switch ($keyfix) {
                        case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                        case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                        case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                        case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; break;
                        case "MODULERECTFAIL": $tmp = "RECTI FAIL"; break;
                        case "MAINSFAIL": $tmp = "PLN OFF"; break;
                        case "GENSETFAILED": $tmp = "GENSET FAIL"; break;
                        default: $tmp=$keyfix; break;
                    }
                    $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
                }

                if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                    $v->status='DOWN';
                    $count_site_down++;
                }else{
                    $v->status = 'MAINS FAIL';
                    $count_site_main_fail++;
                } 
                $v->info_alarm = $info_alarm;
                $res_site[] = $v;
            }

            $res_site = [
                (object)['site_status'=>'MAINS FAIL','count'=>$count_site_main_fail],
                (object)['site_status'=>'DOWN','count'=>$count_site_down],
            ];

            $data[$cluster_id]['cluster'] = @$ref_site[0]->cluster;
            $data[$cluster_id]['mbp'] = @$res_mbp;
            $data[$cluster_id]['site'] = @$res_site;
        }
        
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
        
    }

    public function getMbpSiteRTPO(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $month_now = date('Y-m');

        $now = date('Y-m-d H:i:s');
        $min2Day = date('Y-m-d H:i:s', strtotime($now.' - 2 days'));
        $data=[];
        
        if(empty($request->input('rtpo_id'))){
            return response(['success'=>false,'message'=>'RTPO cannot be null']);
        }
        $rtpo_id = $request->input('rtpo_id');

        //get mbp detail by rtpo_id
        $query_mbp = DB::table('mbp')->select('rtpo_id',DB::raw("COUNT(status) as count_status"),'status');
        $query_mbp->where('rtpo_id',$rtpo_id);
        $query_mbp->where('active',1);
        $query_mbp->groupBy('status');
        $query_mbp->orderBy('rtpo_id');
        $res_mbp = $query_mbp->get();

        $query_site = DB::table('site')->select('rtpo_id');
        $query_site->where('rtpo_id', $rtpo_id);
        $query_site->where('date_mainsfail','>',$min2Day);
        $query_site->where('status',0);
        //$res_site = $query_site->count();

        $query_site_detail = DB::table('site')->select('site_id','site_name','latitude','longitude','rtpo_id','ns_id','alarm','band','status','kriteria_site','site_class as class_name','is_allocated');
        $query_site_detail->where('rtpo_id', $rtpo_id);
        $query_site_detail->where('date_mainsfail','>',$min2Day);
        $query_site_detail->where('status',0);
        $res_site_detail = $query_site_detail->get();

        $count_site_down = 0;
        $count_site_main_fail = 0;
        $res_site_dmf=[];

        foreach ($res_site_detail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $count_site_main_fail++;
                $res_site_dmf[] = $v;
            }

            if ($v->class_name==null) {
                $v->class_name = '-';
            }

        }

        $res_site = $count_site_down+$count_site_main_fail;

        $res_site_temp = [
            (object)['site_status'=>'DOWN','count'=>$count_site_down],
            (object)['site_status'=>'MAIN FAIL','count'=>$count_site_main_fail],
        ];

        $query_mbp_detail = DB::table('mbp as m')->select('m.mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','m.cluster_id','m.cluster','u.username as operator_name');
        $query_mbp_detail->join('user_mbp as u', 'm.mbp_id', 'u.mbp_id');
        $query_mbp_detail->where('rtpo_id', $rtpo_id);
        $query_mbp_detail->where('active',1);
        $res_mbp_detail = $query_mbp_detail->get();

        $query_mbp_pinjaman = DB::table('mbp')->select('mbp_id');
        $query_mbp_pinjaman->where('rtpo_id_home','!=',$rtpo_id);
        $query_mbp_pinjaman->where('rtpo_id','=',$rtpo_id);
        $query_mbp_pinjaman->where('active',1);
        $res_mbp_pinjaman = $query_mbp_pinjaman->count();

        $query_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id');
        $query_mbp_dipinjamkan->where('rtpo_id_home','=',$rtpo_id);
        $query_mbp_dipinjamkan->where('rtpo_id','!=',$rtpo_id);
        $query_mbp_dipinjamkan->where('active',1);
        $res_mbp_dipinjamkan = $query_mbp_dipinjamkan->count();

        $detail_mbp_pinjaman = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster')
        ->where('rtpo_id_home','!=',$rtpo_id)
        ->where('rtpo_id','=',$rtpo_id)
        ->where('active',1)
        ->get();

        $detail_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster')
        ->where('rtpo_id_home','=',$rtpo_id)
        ->where('rtpo_id','!=',$rtpo_id)
        ->where('active',1)
        ->get();

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('rtpo_id',$rtpo_id)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('rtpo_id',$rtpo_id)
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        //$data[$cluster_id]['cluster'] = @$ref_site[0]->cluster;
        $data['mbp'] = @$res_mbp;
        $data['site'] = @$res_site;
        $data['site_down'] = @$res_site_temp;
        $data['site_detail'] = @$res_site_dmf;
        $data['mbp_detail'] = @$res_mbp_detail;
        $data['mbp_pinjaman'] = @$res_mbp_pinjaman;
        $data['mbp_dipinjamkan'] = @$res_mbp_dipinjamkan;
        $data['detail_mbp_pinjaman'] = @$detail_mbp_pinjaman;
        $data['detail_mbp_dipinjamkan'] = @$detail_mbp_dipinjamkan;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;

        //-----------------------
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
        
    }

    public function getMbpSiteNS(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $month_now = date('Y-m');

        $now = date('Y-m-d H:i:s');
        $min2Day = date('Y-m-d H:i:s', strtotime($now.' - 2 days'));
        $data=[];
        
        if(empty($request->input('ns_id'))){
            return response(['success'=>false,'message'=>'NS cannot be null']);
        }
        $ns_id = $request->input('ns_id');

        $query_mbp = DB::table('mbp')->select('ns_id',DB::raw("COUNT(status) as count_status"),'status');
        $query_mbp->where('ns_id',$ns_id);
        $query_mbp->where('active',1);
        $query_mbp->groupBy('status');
        $query_mbp->orderBy('ns_id');
        $res_mbp = $query_mbp->get();

        $query_site = DB::table('site')->select('ns_id');
        $query_site->where('ns_id', $ns_id);
        $query_site->where('date_mainsfail','>',$min2Day);
        $query_site->where('status',0);
        $res_site = $query_site->count();

        $query_site_detail = DB::table('site')->select('site_id','site_name','latitude','longitude','rtpo_id','ns_id','alarm','band','status','kriteria_site','site_class as class_name','is_allocated');
        $query_site_detail->where('ns_id', $ns_id);
        $query_site_detail->where('date_mainsfail','>',$min2Day);
        $query_site_detail->where('status',0);
        $res_site_detail = $query_site_detail->get();

        $count_site_down = 0;
        $count_site_main_fail = 0;
        $res_site_dmf=[];

        foreach ($res_site_detail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $count_site_main_fail++;
                $res_site_dmf[] = $v;
            }

            if ($v->class_name==null) {
                $v->class_name = '-';
            }

        }

        $res_site = $count_site_down+$count_site_main_fail;

        $res_site_temp = [
            (object)['site_status'=>'DOWN','count'=>$count_site_down],
            (object)['site_status'=>'MAIN FAIL','count'=>$count_site_main_fail],
        ];

        $query_mbp_detail = DB::table('mbp as m')->select('m.mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','m.cluster_id','m.cluster','u.username as operator_name');
        $query_mbp_detail->join('user_mbp as u', 'm.mbp_id', 'u.mbp_id');
        $query_mbp_detail->where('ns_id', $ns_id);
        $query_mbp_detail->where('active',1);
        $res_mbp_detail = $query_mbp_detail->get();

        $query_mbp_pinjaman = DB::table('mbp')->select('mbp_id');
        $query_mbp_pinjaman->where('ns_id_home','!=',$ns_id);
        $query_mbp_pinjaman->where('ns_id','=',$ns_id);
        $query_mbp_pinjaman->where('active',1);
        $res_mbp_pinjaman = $query_mbp_pinjaman->count();

        $query_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id');
        $query_mbp_dipinjamkan->where('ns_id_home','=',$ns_id);
        $query_mbp_dipinjamkan->where('ns_id','!=',$ns_id);
        $query_mbp_dipinjamkan->where('active',1);
        $res_mbp_dipinjamkan = $query_mbp_dipinjamkan->count();

        $detail_mbp_pinjaman = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster')
        ->where('ns_id_home','!=',$ns_id)
        ->where('ns_id','=',$ns_id)
        ->where('active',1)
        ->get();

        $detail_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster')
        ->where('ns_id_home','=',$ns_id)
        ->where('ns_id','!=',$ns_id)
        ->where('active',1)
        ->get();

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('ns_id',$ns_id)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('ns_id',$ns_id)
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        //$data[$cluster_id]['cluster'] = @$ref_site[0]->cluster;
        $data['mbp'] = @$res_mbp;
        $data['site'] = @$res_site;
        $data['site_down'] = @$res_site_temp;
        $data['site_detail'] = @$res_site_dmf;
        $data['mbp_detail'] = @$res_mbp_detail;
        $data['mbp_pinjaman'] = @$res_mbp_pinjaman;
        $data['mbp_dipinjamkan'] = @$res_mbp_dipinjamkan;
        $data['detail_mbp_pinjaman'] = @$detail_mbp_pinjaman;
        $data['detail_mbp_dipinjamkan'] = @$detail_mbp_dipinjamkan;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;

        //-----------------------
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
        
    }

    public function getMbpSiteRegional(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $regional = $request->input('regional');
        $debug = $request->input('debug');

        $month_now = date('Y-m');

        $now = date('Y-m-d H:i:s');
        $min2Day = date('Y-m-d H:i:s', strtotime($now.' - 2 days'));
        $data=[];
        
        if(empty($regional)){
            return response([
                'success'=>false,
                'message'=>'Regional cannot be null']);
        }

        $query_mbp = DB::table('mbp')
                    ->select('regional',DB::raw("COUNT(status) as count_status"),'status');
        $query_mbp->where('regional',$regional);
        $query_mbp->where('active',1);
        if (!$debug){
            $query_mbp->whereNotIn('ns_id_home', [26,27]);
        } //dummy
        $query_mbp->groupBy('status');
        $query_mbp->orderBy('regional');
        $res_mbp = $query_mbp->get();

        $query_site = DB::table('site')
                    ->select('regional')
                    ->where('regional', $regional)
                    ->where('date_mainsfail','>',$min2Day)
                    ->where('status',0)
                    ->get();
        $res_site = $query_site->count();

        $query_site_detail = DB::table('site')
                            ->select(
                                'site_id',
                                'site_name',
                                'latitude',
                                'longitude',
                                'rtpo_id',
                                'ns_id',
                                'regional',
                                'alarm',
                                'band',
                                'status',
                                'kriteria_site',
                                'site_class as class_name',
                                'is_allocated');
                            ->where('regional', $regional)
                            ->where('date_mainsfail','>',$min2Day)
                            ->where('status',0)
                            ->get();
        $res_site_detail = $query_site_detail;

        $count_site_down = 0;
        $count_site_main_fail = 0;
        $res_site_dmf=[];

        foreach ($res_site_detail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $count_site_main_fail++;
                $res_site_dmf[] = $v;
            }

            if ($v->class_name==null) {
                $v->class_name = '-';
            }

        }
        
        $res_site = $count_site_down+$count_site_main_fail;

        $res_site_temp = [
            (object)['site_status'=>'DOWN','count'=>$count_site_down],
            (object)['site_status'=>'MAIN FAIL','count'=>$count_site_main_fail],
        ];

        $query_mbp_detail = DB::table('mbp as m')->select('m.mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','m.cluster_id','m.cluster','u.username as operator_name');
        $query_mbp_detail->join('user_mbp as u', 'm.mbp_id', 'u.mbp_id');
        $query_mbp_detail->where('regional', $regional);
        $query_mbp_detail->where('active',1);
        if (!$debug) $query_mbp_detail->whereNotIn('ns_id_home', [26,27]); //dummy
        $res_mbp_detail = $query_mbp_detail->get();

        $query_mbp_pinjaman = DB::table('mbp')->select('mbp_id');
        $query_mbp_pinjaman->where('regional_home','!=',$regional);
        $query_mbp_pinjaman->where('regional','=',$regional);
        $query_mbp_pinjaman->where('active',1);
        if (!$debug) $query_mbp_pinjaman->whereNotIn('ns_id_home', [26,27]); //dummy
        $res_mbp_pinjaman = $query_mbp_pinjaman->count();

        $query_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id');
        $query_mbp_dipinjamkan->where('regional_home','=',$regional);
        $query_mbp_dipinjamkan->where('regional','!=',$regional);
        $query_mbp_dipinjamkan->where('active',1);
        if (!$debug) $query_mbp_dipinjamkan->whereNotIn('ns_id_home', [26,27]); //dummy
        $res_mbp_dipinjamkan = $query_mbp_dipinjamkan->count();

        $detail_mbp_pinjaman = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster');
        $detail_mbp_pinjaman->where('regional_home','!=',$regional);
        $detail_mbp_pinjaman->where('regional','=',$regional);
        $detail_mbp_pinjaman->where('active',1);
        if (!$debug) $detail_mbp_pinjaman->whereNotIn('ns_id_home', [26,27]);
        $res_detail_mbp_pinjaman = $detail_mbp_pinjaman->get();

        $detail_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster');
        $detail_mbp_dipinjamkan->where('regional_home','=',$regional);
        $detail_mbp_dipinjamkan->where('regional','!=',$regional);
        $detail_mbp_dipinjamkan->where('active',1);
        if (!$debug) $detail_mbp_dipinjamkan->whereNotIn('ns_id_home', [26,27]);
        $res_detail_mbp_dipinjamkan = $detail_mbp_dipinjamkan->get();

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('regional',$regional)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('regional',$regional)
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        //$data[$cluster_id]['cluster'] = @$ref_site[0]->cluster;
        $data['mbp'] = @$res_mbp;
        $data['site'] = @$res_site;
        $data['site_down'] = @$res_site_temp;
        $data['site_detail'] = @$res_site_dmf;
        $data['mbp_detail'] = @$res_mbp_detail;
        $data['mbp_pinjaman'] = @$res_mbp_pinjaman;
        $data['mbp_dipinjamkan'] = @$res_mbp_dipinjamkan;
        $data['detail_mbp_pinjaman'] = @$res_detail_mbp_pinjaman;
        $data['detail_mbp_dipinjamkan'] = @$res_detail_mbp_dipinjamkan;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;
        $data['rtpo_owner'] = $rtpo_owner; 
        $data['fmc_name'] = $fmc_name; 
        $data['operator_name'] = $operator_name;
        //-----------------------
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
        
    }

    public function getMbpSiteRegional1(Request $request){

        $now = date('Y-m-d H:i:s');
        $min2Day = date('Y-m-d H:i:s', strtotime($now.' - 2 days'));
        $data=[];
        
        if(empty($request->input('regional'))){
            return response(['success'=>false,'message'=>'Regional cannot be null']);
        }
        $regional = $request->input('regional');

        $query_mbp = DB::table('mbp')->select('regional',DB::raw("COUNT(status) as count_status"),'status');
        $query_mbp->where('regional',$regional);
        $query_mbp->groupBy('status');
        $query_mbp->orderBy('regional');
        $res_mbp = $query_mbp->get();

        $query_site = DB::table('site')->select('regional');
        $query_site->where('regional', $regional);
        $query_site->where('date_mainsfail','>',$min2Day);
        $query_site->where('status',0);
        $res_site = $query_site->count();

        $query_site_detail = DB::table('site')->select('site_id','site_name','latitude','longitude','rtpo_id','ns_id','alarm','band','status');
        $query_site_detail->where('regional', $regional);
        $query_site_detail->where('date_mainsfail','>',$min2Day);
        $query_site_detail->where('status',0);
        $res_site_detail = $query_site_detail->get();

        $count_site_down = 0;
        $count_site_main_fail = 0;
        $res_site_dmf=[];

        foreach ($res_site_detail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $count_site_main_fail++;
                $res_site_dmf[] = $v;
            }

        }

        $res_site_temp = [
            (object)['site_status'=>'DOWN','count'=>$count_site_down],
            (object)['site_status'=>'MAIN FAIL','count'=>$count_site_main_fail],
        ];

        $query_mbp_detail = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id_home as rtpo_id','ns_id_home as ns_id','regional_home as regional');
        $query_mbp_detail->where('regional', $regional);
        $res_mbp_detail = $query_mbp_detail->get();

        $query_mbp_pinjaman = DB::table('mbp')->select('mbp_id');
        $query_mbp_pinjaman->where('regional_home','!=',$regional);
        $query_mbp_pinjaman->where('regional','=',$regional);
        $res_mbp_pinjaman = $query_mbp_pinjaman->count();

        $query_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id');
        $query_mbp_dipinjamkan->where('regional_home','=',$regional);
        $query_mbp_dipinjamkan->where('regional','!=',$regional);
        $res_mbp_dipinjamkan = $query_mbp_dipinjamkan->count();

        //$data[$cluster_id]['cluster'] = @$ref_site[0]->cluster;
        $data['mbp'] = @$res_mbp;
        $data['site'] = @$res_site;
        $data['site_status'] = @$res_site_temp;
        $data['site_detail'] = @$res_site_dmf;
        $data['mbp_detail'] = @$res_mbp_detail;
        $data['mbp_pinjaman'] = @$res_mbp_pinjaman;
        $data['mbp_dipinjamkan'] = @$res_mbp_dipinjamkan;

        //-----------------------
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
        
    }

    public function getMbpSiteArea(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $month_now = date('Y-m');

        $now = date('Y-m-d H:i:s');
        $min2Day = date('Y-m-d H:i:s', strtotime($now.' - 2 days'));
        $data=[];
        
        $debug = $request->input('debug');

        $query_mbp = DB::table('mbp')->select(DB::raw("COUNT(status) as count_status"),'status');
        $query_mbp->where('active',1);
        if (!$debug) $query_mbp->whereNotIn('rtpo_id', [42,43]); //dummy
        $query_mbp->groupBy('status');
        $res_mbp = $query_mbp->get();

        $query_site = DB::table('site')->select('site_id');
        $query_site->where('date_mainsfail','>',$min2Day);
        $query_site->where('status',0);
        $res_site = $query_site->count();

        $query_site_detail = DB::table('site')->select('site_id','site_name','latitude','longitude','rtpo_id','ns_id','regional','alarm','band','status');
        $query_site_detail->where('date_mainsfail','>',$min2Day);
        $query_site_detail->where('status',0);
        $res_site_detail = $query_site_detail->get();

        $count_site_down = 0;
        $count_site_main_fail = 0;
        $res_site_dmf=[];

        foreach ($res_site_detail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $count_site_main_fail++;
                $res_site_dmf[] = $v;
            }

        }
        
        $res_site = $count_site_down+$count_site_main_fail;

        $res_site_temp = [
            (object)['site_status'=>'DOWN','count'=>$count_site_down],
            (object)['site_status'=>'MAIN FAIL','count'=>$count_site_main_fail],
        ];

        $query_mbp_detail = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster');
        $query_mbp_detail->where('active',1);
        if (!$debug) $query_mbp_detail->whereNotIn('rtpo_id', [42,43]); //dummy
        $res_mbp_detail = $query_mbp_detail->get();

        /*
        $query_mbp_pinjaman = DB::table('mbp')->select('mbp_id');
        $query_mbp_pinjaman->where('regional_home','!=',$regional);
        $query_mbp_pinjaman->where('regional','=',$regional);
        $query_mbp_pinjaman->where('active',1);
        if (!$debug) $query_mbp_pinjaman->whereNotIn('ns_id_home', [26,27]); //dummy
        $res_mbp_pinjaman = $query_mbp_pinjaman->count();

        $query_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id');
        $query_mbp_dipinjamkan->where('regional_home','=',$regional);
        $query_mbp_dipinjamkan->where('regional','!=',$regional);
        $query_mbp_dipinjamkan->where('active',1);
        if (!$debug) $query_mbp_dipinjamkan->whereNotIn('ns_id_home', [26,27]); //dummy
        $res_mbp_dipinjamkan = $query_mbp_dipinjamkan->count();

        $detail_mbp_pinjaman = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster');
        $detail_mbp_pinjaman->where('regional_home','!=',$regional);
        $detail_mbp_pinjaman->where('regional','=',$regional);
        $detail_mbp_pinjaman->where('active',1);
        if (!$debug) $detail_mbp_pinjaman->whereNotIn('ns_id_home', [26,27]);
        $res_detail_mbp_pinjaman = $detail_mbp_pinjaman->get();

        $detail_mbp_dipinjamkan = DB::table('mbp')->select('mbp_id','mbp_name','latitude','longitude','status','rtpo_id','ns_id','rtpo_id_home','ns_id_home','regional','regional_home','cluster_id','cluster');
        $detail_mbp_dipinjamkan->where('regional_home','=',$regional);
        $detail_mbp_dipinjamkan->where('regional','!=',$regional);
        $detail_mbp_dipinjamkan->where('active',1);
        if (!$debug) $detail_mbp_dipinjamkan->whereNotIn('ns_id_home', [26,27]);
        $res_detail_mbp_dipinjamkan = $detail_mbp_dipinjamkan->get();
        */

        //$data[$cluster_id]['cluster'] = @$ref_site[0]->cluster;

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        $data['mbp'] = @$res_mbp;
        $data['site'] = @$res_site;
        $data['site_down'] = @$res_site_temp;
        $data['site_detail'] = @$res_site_dmf;
        $data['mbp_detail'] = @$res_mbp_detail;
        $data['mbp_pinjaman'] = '-'; //@$res_mbp_pinjaman;
        $data['mbp_dipinjamkan'] = '-'; //@$res_mbp_dipinjamkan;
        $data['detail_mbp_pinjaman'] = '-'; //@$res_detail_mbp_pinjaman;
        $data['detail_mbp_dipinjamkan'] = '-'; //@$res_detail_mbp_dipinjamkan;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;

        //-----------------------
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
        
    }

    

    public function appDashboard(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        //debug
        //$month_now = '2019-04';

        $username = $request->input('username');

        $data_user = DB::table('users')
        ->select('*')
        ->where('username',$username)
        ->first();

        if ($data_user->user_type=='AREA') {
            return response($this->appDashboardUserArea($username));
        }

        if ($data_user->regional=='JATIM') {
            $link_support = 'https://t.me/joinchat/A8MX30pMwFSuWG8fkbOSEg';
        } else if ($data_user->regional=='JATENG-DIY') {
            $link_support = 'https://t.me/joinchat/B9qgLhK3RVsZx4wTLYQCQg';
        } else if ($data_user->regional=='BALI NUSRA') {
            $link_support = 'https://t.me/joinchat/BHF_51JPpijlZtcowh2t-A';
        } else{
            $link_support = '-';
        }

        if (!$data_user) {
            $res['success'] = false;
            $res['message'] = 'Data user tidak ditemukan!';
            return response($res);
        }

        $rtpo = '-';
        $cluster = '-';

        if ($data_user->user_type=='RTPO') {
            $data_user_rtpo = DB::table('user_rtpo')
            ->select('*')
            ->where('username',$username)
            ->first();

            $rtpo = ($data_user_rtpo->rtpo_name==null) ? '-' : $data_user_rtpo->rtpo_name;
            $rtpo_id = $data_user_rtpo->rtpo_id;
            $scope = ($data_user_rtpo->rtpo_name==null) ? '-' : $data_user_rtpo->rtpo_name;


            $jumlah_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('flag',1)
            ->count();

            $complete_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',7)
            ->where('flag',1)
            ->count();

            $incomplete_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->whereraw('(respond_status=0 or respond_status=6)')
            ->where('flag',1)
            ->count();

            $on_review_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',8)
            ->where('flag',1)
            ->count();

            $approved_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->whereraw('(respond_status=2 or respond_status=4)')
            ->where('flag',1)
            ->count();

            $rejected_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->whereraw('(respond_status=3 or respond_status=5)')
            ->where('flag',1)
            ->count();

            $reassign_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',1)
            ->where('flag',1)
            ->count();

            $auto_approve_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',4)
            ->where('flag',1)
            ->count();

            $auto_reject_MT = DB::table('sik_site')
            ->select('*')
            ->where('rtpo_id',$rtpo_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',5)
            ->where('flag',1)
            ->count();

            $complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

            if ($jumlah_MT==0) {
                $prosentase_pencapaian = 0;
            }
            else{
                $prosentase_pencapaian = $complete_MT/$jumlah_MT;
            }

        } else {
            $cluster = ($data_user->cluster==null) ? '-' : $data_user->cluster;
            $cluster_id = $data_user->cluster_id;
            $scope = ($data_user->cluster==null) ? '-' : $data_user->cluster;


            $jumlah_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('flag',1)
            ->count();

            $complete_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',7)
            ->where('flag',1)
            ->count();

            $incomplete_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->whereraw('(respond_status=0 or respond_status=6)')
            ->where('flag',1)
            ->count();

            $on_review_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',8)
            ->where('flag',1)
            ->count();

            $approved_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->whereraw('(respond_status=2 or respond_status=4)')
            ->where('flag',1)
            ->count();

            $rejected_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->whereraw('(respond_status=3 or respond_status=5)')
            ->where('flag',1)
            ->count();

            $reassign_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',1)
            ->where('flag',1)
            ->count();

            $auto_approve_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',4)
            ->where('flag',1)
            ->count();

            $auto_reject_MT = DB::table('sik_site')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
            ->where('respond_status',5)
            ->where('flag',1)
            ->count();

            $complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

            if ($jumlah_MT==0) {
                $prosentase_pencapaian = 0;
            }
            else{
                $prosentase_pencapaian = $complete_MT/$jumlah_MT;
            }

            $data_cluster = DB::table('lookup_fmc_cluster')
            ->select('*')
            ->where('cluster_id',$cluster_id)
            ->where('status','1')
            ->first();

            $rtpo_id = @$data_cluster->rtpo_id;

        }

        $total_mbp = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('rtpo_id',@$rtpo_id)
        ->orWhere('rtpo_id_home','=',@$rtpo_id)
        ->count();

        $mbp_organik = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id_home',@$rtpo_id)
        ->where('active',1)
        ->count();

        $mbp_available = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',@$rtpo_id)
        ->where('status','AVAILABLE')
        ->where('active',1)
        ->count();

        $mbp_unavailable = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',@$rtpo_id)
        ->where('status','UNAVAILABLE')
        ->where('active',1)
        ->count();

        $mbp_waiting = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',@$rtpo_id)
        ->where('status','WAITING')
        ->where('active',1)
        ->count();  

        $mbp_on_progress = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',@$rtpo_id)
        ->where('status','ON_PROGRESS')
        ->where('active',1)
        ->count();

        $mbp_check_in = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',@$rtpo_id)
        ->where('status','CHECK_IN')
        ->where('active',1)
        ->count();

        $mbp_dipinjamkan = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id_home',@$rtpo_id)
        ->where('rtpo_id','!=',@$rtpo_id)
        ->where('active',1)
        ->count();

        $mbp_pinjaman = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id_home','!=',@$rtpo_id)
        ->where('rtpo_id',@$rtpo_id)
        ->where('active',1)
        ->count();

        $mbp_dummy = DB::table('mbp')
        ->select('mbp_id','rtpo_id','rtpo_id_home')
        ->where('rtpo_id_home',@$rtpo_id)
        ->where('active',1)
        ->get();

        $site_mainfail = DB::table('site')
        ->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 'alarm', 'band','cluster')
        ->where('rtpo_id',@$rtpo_id)
        ->where('date_mainsfail','>',$min2Day)
        ->groupBy('site_id')
        ->get();

        $site_main_fail = 0;
        $count_site_down = 0;
        $count_site_main_fail = 0;

        foreach ($site_mainfail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $site_main_fail++;
                $res_site_dmf[] = $v;
            }

        }

        $data_pengumuman = DB::table('pengumuman')
        ->select('*')
        ->where('date_expired','>',$date_now)
        ->orderBy('id','desc')
        ->first();

        if(!$data_pengumuman){
            $flag_pengumuman=false;
            $pengumuman = '-';
        } else{
            $flag_pengumuman=true;
            $pengumuman = $data_pengumuman->pengumuman;
        }

        $array_faq = DB::table('faq')
        ->select('*')
        ->where('id','<',6)
        ->get();
        
        $count_faq = DB::table('faq')
        ->select('*')
        ->count();

        $random_int = (rand(1,1000)%$count_faq)+1;

        $data_faq = DB::table('faq')
        ->select('*')
        ->where('id',$random_int)
        ->first();

        $id_random_faq = $data_faq->kategori_id;
        $url_faq = $data_faq->answer;
        $url_image_random_faq = $data_faq->image;
        $judul = $data_faq->question;
        

        /*$data['id_random_faq'] = @$id_random_faq;
        $data['url_faq'] = @$url_faq;
        $data['url_image_random_faq'] = @$url_image_random_faq;
        $data['judul_random_faq'] = @$judul;
        $data['content_random_faq'] = '-';*/
        $data['scope'] = $scope;
        $data['cluster'] = $cluster;
        $data['rtpo'] = $rtpo;
        $data['periode'] = $this->bulan_tahun_indo($month_now);

        $data['jumlah_MT'] = $jumlah_MT;
        $data['incomplete_MT'] = $incomplete_MT;
        $data['complete_MT'] = $complete_MT;
        $data['on_review_MT'] = $on_review_MT;
        $data['approved_MT'] = $approved_MT;
        $data['reassign_MT'] = $reassign_MT;
        $data['rejected_MT'] = $rejected_MT;
        $data['auto_approve_MT'] = $auto_approve_MT;
        $data['auto_reject_MT'] = $auto_reject_MT;
        $data['total_pencapaian'] = $complete_MT.'/'.$jumlah_MT;
        $data['prosentase_pencapaian'] = number_format((float)$prosentase_pencapaian*100,2).'%';

        $data['total_mbp'] = $total_mbp;
        $data['mbp_organik'] = $mbp_organik;
        $data['mbp_available'] = $mbp_available;
        $data['mbp_unavailable'] = $mbp_unavailable;
        $data['mbp_waiting'] = $mbp_waiting;
        $data['mbp_on_progress'] = $mbp_on_progress;
        $data['mbp_check_in'] = $mbp_check_in;
        $data['mbp_dipinjamkan'] = $mbp_dipinjamkan;
        $data['mbp_pinjaman'] = $mbp_pinjaman; 
        $data['site_main_fail'] = $site_main_fail;

        $data['foto_profil'] = '-';
        $data['link_support'] = @$link_support;
        $data['flag_pengumuman'] = $flag_pengumuman;
        $data['pengumuman'] = $pengumuman;
        $data['array_faq'] = $array_faq;
        //$data['mbp_dummy'] = $mbp_dummy;

        $data['jumlah_tiket_MBP'] = 0;
        $data['tiket_meet_SLA'] = 0;
        $data['tiket_over_SLA'] = 0;
        $data['tiket_auto_close'] = 0;
        $data['tiket_tidak_dikerjakan'] = 0;
        $data['tiket_complete'] = 0;

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
    }

    public function getListNS(Request $request){
        $data_ns = DB::table('ns')
        ->select('ns_id','ns_name')
        ->where('status',1)
        ->get();

        $data['ns'] = $data_ns;

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return response($res);
    }

    public function appDashboardUserArea($username){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        //start ambil data perfomance
        $jumlah_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',7)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $incomplete_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=0 or respond_status=6)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $on_review_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',8)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $approved_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=2 or respond_status=4)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $rejected_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=3 or respond_status=5)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $reassign_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',1)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_approve_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',4)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_reject_MT = DB::table('sik_site')
        ->select('*')
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',5)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

        if ($jumlah_MT==0) {
            $prosentase_pencapaian = 0;
        }
        else{
            $prosentase_pencapaian = $complete_MT/$jumlah_MT;
        }
        //end ambil data perfomance

        //start ambil data mbp
        $total_mbp = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_organik = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_available = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('status','AVAILABLE')
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_unavailable = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('status','UNAVAILABLE')
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_waiting = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('status','WAITING')
        ->whereNotIn('rtpo_id', [42,43])
        ->count();  

        $mbp_on_progress = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('status','ON_PROGRESS')
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_check_in = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('status','CHECK_IN')
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_dipinjamkan = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('rtpo_id_home',@$rtpo_id)
        ->where('rtpo_id','!=',@$rtpo_id)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_pinjaman = DB::table('mbp')
        ->select('*')
        ->where('active',1)
        ->where('rtpo_id_home','!=',@$rtpo_id)
        ->where('rtpo_id',@$rtpo_id)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data mbp

        //start ambil data site main fail
        $site_mainfail = DB::table('site')
        ->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 'alarm', 'band','cluster')
        ->whereNotIn('rtpo_id', [42,43])
        ->where('date_mainsfail','>',$min2Day)
        ->groupBy('site_id')
        ->get();

        $site_main_fail = 0;
        $count_site_down = 0;
        //$count_site_main_fail = 0;

        foreach ($site_mainfail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $site_main_fail++;
                $res_site_dmf[] = $v;
            }
        }
        //end ambil data site main fail

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,5,6])
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        $data_pengumuman = DB::table('pengumuman')
        ->select('*')
        ->where('date_expired','>',$date_now)
        ->orderBy('id','desc')
        ->first();

        if(!$data_pengumuman){
            $flag_pengumuman=false;
            $pengumuman = '-';
        } else{
            $flag_pengumuman=true;
            $pengumuman = $data_pengumuman->pengumuman;
        }

        $array_faq = DB::table('faq')
        ->select('*')
        ->where('id','<',6)
        ->get();

        $data['scope'] = 'AREA';
        $data['cluster'] = '-';
        $data['rtpo'] = '-';
        $data['periode'] = $this->bulan_tahun_indo($month_now);
        $data['jumlah_MT'] = $jumlah_MT;
        $data['incomplete_MT'] = $incomplete_MT;
        $data['complete_MT'] = $complete_MT;
        $data['on_review_MT'] = $on_review_MT;
        $data['approved_MT'] = $approved_MT;
        $data['reassign_MT'] = $reassign_MT;
        $data['rejected_MT'] = $rejected_MT;
        $data['total_pencapaian'] = $complete_MT.'/'.$jumlah_MT;
        $data['prosentase_pencapaian'] = number_format((float)$prosentase_pencapaian*100,2).'%';
        $data['auto_approve_MT'] = $auto_approve_MT;
        $data['auto_reject_MT'] = $auto_reject_MT;

        $data['total_mbp'] = $total_mbp;
        $data['mbp_organik'] = '-';
        $data['mbp_available'] = $mbp_available;
        $data['mbp_unavailable'] = $mbp_unavailable;
        $data['mbp_waiting'] = $mbp_waiting;
        $data['mbp_on_progress'] = $mbp_on_progress;
        $data['mbp_check_in'] = $mbp_check_in;
        $data['mbp_dipinjamkan'] = '-';
        $data['mbp_pinjaman'] = '-'; 
        $data['site_main_fail'] = $site_main_fail;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_tidak_dikerjakan'] = 0;
        $data['tiket_complete'] = 0;

        $data['flag_pengumuman'] = $flag_pengumuman;
        $data['pengumuman'] = $pengumuman;
        $data['array_faq'] = $array_faq;
        $data['foto_profil'] = '-';
        $data['link_support'] = '-';

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return $res;
    }

    public function dashboardFilter(Request $request){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));
        
        //$scope_level = @$request->input('scope_level');
        $regional = @$request->input('regional');
        $ns_id = @$request->input('ns_id');
        $rtpo_id = @$request->input('rtpo_id');

        if ($regional=='' && $ns_id=='' && $rtpo_id=='') {
            $regional = 'JATIM';
        }

        if ($regional!='') {
            $res = $this->dashboardFilterRegional($regional);
        } else if ($ns_id!='') {
            $res = $this->dashboardFilterNS($ns_id);
        } else if ($rtpo_id!='') {
            $res = $this->dashboardFilterRTPO($rtpo_id);
        }

        return $res;
    }

    public function dashboardFilterRegional($regional){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        if ($regional=='' or $regional==null) {
            $regional = 'JATIM';
        }

        //$data_regional

        $data_ns = DB::table('ns')
        ->select('*')
        ->where('regional',$regional)
        ->where('status',1)
        ->whereNotIn('ns_id',[26,27])
        ->first();

        //start ambil data perfomance
        $jumlah_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',7)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $incomplete_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=0 or respond_status=6)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $on_review_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',8)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $approved_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=2 or respond_status=4)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $rejected_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=3 or respond_status=5)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $reassign_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',1)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_approve_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',4)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_reject_MT = DB::table('sik_site')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',5)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

        if ($jumlah_MT==0) {
            $prosentase_pencapaian = 0;
        }
        else{
            $prosentase_pencapaian = $complete_MT/$jumlah_MT;
        }
        //end ambil data perfomance

        //start ambil data mbp
        $total_mbp = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_organik = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_available = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('status','AVAILABLE')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_unavailable = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('status','UNAVAILABLE')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_waiting = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('status','WAITING')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();  

        $mbp_on_progress = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('status','ON_PROGRESS')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_check_in = DB::table('mbp')
        ->select('*')
        ->where('regional',$regional)
        ->where('status','CHECK_IN')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_dipinjamkan = DB::table('mbp')
        ->select('*')
        ->where('regional_home',@$regional)
        ->where('regional','!=',@$regional)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_pinjaman = DB::table('mbp')
        ->select('*')
        ->where('regional_home','!=',@$regional)
        ->where('regional',@$regional)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data mbp

        //start ambil data site main fail
        $site_mainfail = DB::table('site')
        ->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 'alarm', 'band','cluster')
        ->where('date_mainsfail','>',$min2Day)
        ->whereNotIn('rtpo_id', [42,43])
        ->groupBy('site_id')
        ->get();

        $site_main_fail = 0;
        $count_site_down = 0;
        //$count_site_main_fail = 0;

        foreach ($site_mainfail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $site_main_fail++;
                $res_site_dmf[] = $v;
            }
        }
        //end ambil data site main fail

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,5,6])
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('regional',$regional)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        $data_pengumuman = DB::table('pengumuman')
        ->select('*')
        ->where('date_expired','>',$date_now)
        ->orderBy('id','desc')
        ->first();

        if(!$data_pengumuman){
            $flag_pengumuman=false;
            $pengumuman = '-';
        } else{
            $flag_pengumuman=true;
            $pengumuman = $data_pengumuman->pengumuman;
        }

        $array_faq = DB::table('faq')
        ->select('*')
        ->where('id','<',6)
        ->get();

        $data['cluster'] = '-';
        $data['rtpo'] = '-';
        $data['periode'] = $this->bulan_tahun_indo($month_now);
        $data['jumlah_MT'] = $jumlah_MT;
        $data['incomplete_MT'] = $incomplete_MT;
        $data['complete_MT'] = $complete_MT;
        $data['on_review_MT'] = $on_review_MT;
        $data['approved_MT'] = $approved_MT;
        $data['reassign_MT'] = $reassign_MT;
        $data['rejected_MT'] = $rejected_MT;
        $data['total_pencapaian'] = $complete_MT.'/'.$jumlah_MT;
        $data['prosentase_pencapaian'] = number_format((float)$prosentase_pencapaian*100,2).'%';
        $data['auto_approve_MT'] = $auto_approve_MT;
        $data['auto_reject_MT'] = $auto_reject_MT;

        $data['total_mbp'] = $total_mbp;
        $data['mbp_organik'] = @$mbp_organik;
        $data['mbp_available'] = $mbp_available;
        $data['mbp_unavailable'] = $mbp_unavailable;
        $data['mbp_waiting'] = $mbp_waiting;
        $data['mbp_on_progress'] = $mbp_on_progress;
        $data['mbp_check_in'] = $mbp_check_in;
        $data['mbp_dipinjamkan'] = @$mbp_dipinjamkan;
        $data['mbp_pinjaman'] = @$mbp_pinjaman; 
        $data['site_main_fail'] = $site_main_fail;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_tidak_dikerjakan'] = 0;
        $data['tiket_complete'] = 0;

        $data['scope_id'] = $regional;
        $data['scope_name'] = $regional;
        $data['scope_level'] = 'regional';
        $data['next_scope_id'] = $data_ns->ns_id;
        $data['next_scope_level'] = 'ns_id';

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return $res;
    }

    public function dashboardFilterNS($ns_id){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        $data_rtpo = DB::table('lookup_fmc_cluster')
        ->select('rtpo_id','rtpo')
        ->where('ns_id',$ns_id)
        ->where('status',1)
        ->groupBy('rtpo_id')
        ->first();

        $data_ns = DB::table('ns')
        ->select('ns_id','ns_name')
        ->where('ns_id',$ns_id)
        ->first();

        $ns_name = $data_ns->ns_name;

        //start ambil data perfomance
        $jumlah_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',7)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $incomplete_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=0 or respond_status=6)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $on_review_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',8)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $approved_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=2 or respond_status=4)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $rejected_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=3 or respond_status=5)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $reassign_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',1)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_approve_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',4)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_reject_MT = DB::table('sik_site')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',5)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

        if ($jumlah_MT==0) {
            $prosentase_pencapaian = 0;
        }
        else{
            $prosentase_pencapaian = $complete_MT/$jumlah_MT;
        }
        //end ambil data perfomance

        //start ambil data mbp
        $total_mbp = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_organik = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_available = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('status','AVAILABLE')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_unavailable = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('status','UNAVAILABLE')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_waiting = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('status','WAITING')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();  

        $mbp_on_progress = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('status','ON_PROGRESS')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_check_in = DB::table('mbp')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->where('status','CHECK_IN')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_dipinjamkan = DB::table('mbp')
        ->select('*')
        ->where('ns_id_home',@$ns_id)
        ->where('ns_id','!=',@$ns_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_pinjaman = DB::table('mbp')
        ->select('*')
        ->where('ns_id_home','!=',@$ns_id)
        ->where('ns_id',@$ns_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data mbp

        //start ambil data site main fail
        $site_mainfail = DB::table('site')
        ->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 'alarm', 'band','cluster')
        ->where('ns_id',$ns_id)
        ->where('date_mainsfail','>',$min2Day)
        ->whereNotIn('rtpo_id', [42,43])
        ->groupBy('site_id')
        ->get();

        $site_main_fail = 0;
        $count_site_down = 0;
        //$count_site_main_fail = 0;

        foreach ($site_mainfail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $site_main_fail++;
                $res_site_dmf[] = $v;
            }
        }
        //end ambil data site main fail

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,5,6])
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('ns_id',$ns_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        $data_pengumuman = DB::table('pengumuman')
        ->select('*')
        ->where('date_expired','>',$date_now)
        ->orderBy('id','desc')
        ->first();

        if(!$data_pengumuman){
            $flag_pengumuman=false;
            $pengumuman = '-';
        } else{
            $flag_pengumuman=true;
            $pengumuman = $data_pengumuman->pengumuman;
        }

        $array_faq = DB::table('faq')
        ->select('*')
        ->where('id','<',6)
        ->get();

        $data['cluster'] = '-';
        $data['rtpo'] = '-';
        $data['periode'] = $this->bulan_tahun_indo($month_now);
        $data['jumlah_MT'] = $jumlah_MT;
        $data['incomplete_MT'] = $incomplete_MT;
        $data['complete_MT'] = $complete_MT;
        $data['on_review_MT'] = $on_review_MT;
        $data['approved_MT'] = $approved_MT;
        $data['reassign_MT'] = $reassign_MT;
        $data['rejected_MT'] = $rejected_MT;
        $data['total_pencapaian'] = $complete_MT.'/'.$jumlah_MT;
        $data['prosentase_pencapaian'] = number_format((float)$prosentase_pencapaian*100,2).'%';
        $data['auto_approve_MT'] = $auto_approve_MT;
        $data['auto_reject_MT'] = $auto_reject_MT;

        $data['total_mbp'] = $total_mbp;
        $data['mbp_organik'] = @$mbp_organik;
        $data['mbp_available'] = $mbp_available;
        $data['mbp_unavailable'] = $mbp_unavailable;
        $data['mbp_waiting'] = $mbp_waiting;
        $data['mbp_on_progress'] = $mbp_on_progress;
        $data['mbp_check_in'] = $mbp_check_in;
        $data['mbp_dipinjamkan'] = @$mbp_dipinjamkan;
        $data['mbp_pinjaman'] = @$mbp_pinjaman; 
        $data['site_main_fail'] = $site_main_fail;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_tidak_dikerjakan'] = 0;
        $data['tiket_complete'] = 0;

        $data['scope_id'] = $ns_id;
        $data['scope_name'] = $ns_name;
        $data['scope_level'] = 'ns_id';
        $data['next_scope_id'] = $data_rtpo->rtpo_id;
        $data['next_scope_level'] = 'rtpo_id';

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return $res;
    }

    public function dashboardFilterRTPO($rtpo_id){
        date_default_timezone_set("Asia/Jakarta");
        $date_now = date('Y-m-d H:i:s');
        $month_now = date('Y-m');
        $min2Day = date('Y-m-d H:i:s', strtotime($date_now.' - 2 days'));

        $data_rtpo = DB::table('rtpo')
        ->select('rtpo_id','rtpo_name')
        ->where('rtpo_id',$rtpo_id)
        ->first();

        $rtpo_name = $data_rtpo->rtpo_name;

        //start ambil data perfomance
        $jumlah_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',7)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $incomplete_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=0 or respond_status=6)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $on_review_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',8)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $approved_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=2 or respond_status=4)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $rejected_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->whereraw('(respond_status=3 or respond_status=5)')
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $reassign_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',1)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_approve_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',4)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $auto_reject_MT = DB::table('sik_site')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('maintenance_schedule like "%'.$month_now.'%"')
        ->where('respond_status',5)
        ->where('flag',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $complete_MT = $approved_MT+$on_review_MT+$rejected_MT+$reassign_MT;

        if ($jumlah_MT==0) {
            $prosentase_pencapaian = 0;
        }
        else{
            $prosentase_pencapaian = $complete_MT/$jumlah_MT;
        }
        //end ambil data perfomance

        //start ambil data mbp
        $total_mbp = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_organik = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_available = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('status','AVAILABLE')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_unavailable = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('status','UNAVAILABLE')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_waiting = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('status','WAITING')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();  

        $mbp_on_progress = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('status','ON_PROGRESS')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_check_in = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->where('status','CHECK_IN')
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_dipinjamkan = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id_home',@$rtpo_id)
        ->where('rtpo_id','!=',@$rtpo_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $mbp_pinjaman = DB::table('mbp')
        ->select('*')
        ->where('rtpo_id_home','!=',@$rtpo_id)
        ->where('rtpo_id',@$rtpo_id)
        ->where('active',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data mbp

        //start ambil data site main fail
        $site_mainfail = DB::table('site')
        ->select('site_id','is_allocated','status','site_name', 'class_id', 'latitude', 'longitude', 'alarm', 'band','cluster')
        ->where('rtpo_id',$rtpo_id)
        ->where('date_mainsfail','>',$min2Day)
        ->whereNotIn('rtpo_id', [42,43])
        ->groupBy('site_id')
        ->get();

        $site_main_fail = 0;
        $count_site_down = 0;
        //$count_site_main_fail = 0;

        foreach ($site_mainfail as $v) {
            $info_alarm="";
            $alarms = explode(", ",@$v->alarm);
            $band_2g = 0; $band_3g = 0; $band_4g = 0;
            $off_2g = 0; $off_3g = 0; $off_4g = 0;
            $flag_main_fail = 0;

            $bands = explode("-",$v->band);

            foreach (@$bands as $band) {
                $keyfix = str_replace(' ','',$band);
                switch ($keyfix) {
                    case "2G": $band_2g = 1; break;
                    case "3G": $band_3g = 1; break;
                    case "4G": $band_4g = 1; break;
                    default:  break;
                }
            }

            foreach ($alarms as $alarm) {
                // disini cek apakah di "band" ada berapa alarm dan cek alarm tersebut aktif semua? bila ia maka katakan down. bila tidak maka jangan katakakn down
                $keyfix = str_replace(' ','',$alarm);
                switch ($keyfix) {
                    case "UMTSCellUnavailable": $tmp = "3G OFF"; $off_3g = 1; break;
                    case "GSMCelloutofService": $tmp = "2G OFF"; $off_2g = 1; break;
                    case "CellUnavailable": $tmp = "4G OFF"; $off_4g = 1; break;
                    case "MODULERECTIFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MODULERECTFAIL": $tmp = "RECTI FAIL"; $flag_main_fail=1; break;
                    case "MAINSFAIL": $tmp = "PLN OFF"; $flag_main_fail=1; break;
                    case "GENSETFAILED": $tmp = "GENSET FAIL"; $flag_main_fail=1; break;
                    case "LOWFUEL": $tmp = "LOW FUEL"; $flag_main_fail=1; break;
                    case "LOWBATT": $tmp = "LOW BATT"; $flag_main_fail=1; break;
                    case "BATTFUSEFAIL": $tmp = "BATT FUSE FAIL"; $flag_main_fail=1; break;
                    case "BATTSTOLEN": $tmp = "BATT STOLEN"; $flag_main_fail=1; break;
                    case "LOADFUSEFAIL": $tmp = "LOAD FUSE FAIL"; $flag_main_fail=1; break;
                    default: $tmp=$keyfix; break;
                }
                $info_alarm .= empty($info_alarm) ? $tmp : ', '.$tmp;
            }

            $v->info_alarm = $info_alarm;

            if( !empty($bands) && ($band_2g.'-'.$band_3g.'-'.$band_4g == $off_2g.'-'.$off_3g.'-'.$off_4g) ){
                $v->status='DOWN';
                $count_site_down++;
                $res_site_dmf[] = $v;
            } else if($flag_main_fail==1){
                $v->status='MAIN FAIL';
                $site_main_fail++;
                $res_site_dmf[] = $v;
            }
        }
        //end ambil data site main fail

        //start ambil data tiket mbp
        $jumlah_tiket_MBP = DB::table('supplying_power')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,5,6])
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_auto_close = DB::table('supplying_power')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->where('detail_finish',5)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_meet_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',1)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();

        $tiket_over_SLA = DB::table('supplying_power')
        ->select('*')
        ->where('rtpo_id',$rtpo_id)
        ->whereraw('date_waiting like "%'.$month_now.'%"')
        ->wherein('detail_finish',[1,6])
        ->where('meet_sla',0)
        ->whereNotIn('rtpo_id', [42,43])
        ->count();
        //end ambil data tiket mbp

        $data_pengumuman = DB::table('pengumuman')
        ->select('*')
        ->where('date_expired','>',$date_now)
        ->orderBy('id','desc')
        ->first();

        if(!$data_pengumuman){
            $flag_pengumuman=false;
            $pengumuman = '-';
        } else{
            $flag_pengumuman=true;
            $pengumuman = $data_pengumuman->pengumuman;
        }

        $array_faq = DB::table('faq')
        ->select('*')
        ->where('id','<',6)
        ->get();

        $data['cluster'] = '-';
        $data['rtpo'] = '-';
        $data['periode'] = $this->bulan_tahun_indo($month_now);
        $data['jumlah_MT'] = $jumlah_MT;
        $data['incomplete_MT'] = $incomplete_MT;
        $data['complete_MT'] = $complete_MT;
        $data['on_review_MT'] = $on_review_MT;
        $data['approved_MT'] = $approved_MT;
        $data['reassign_MT'] = $reassign_MT;
        $data['rejected_MT'] = $rejected_MT;
        $data['total_pencapaian'] = $complete_MT.'/'.$jumlah_MT;
        $data['prosentase_pencapaian'] = number_format((float)$prosentase_pencapaian*100,2).'%';
        $data['auto_approve_MT'] = $auto_approve_MT;
        $data['auto_reject_MT'] = $auto_reject_MT;

        $data['total_mbp'] = $total_mbp;
        $data['mbp_organik'] = @$mbp_organik;
        $data['mbp_available'] = $mbp_available;
        $data['mbp_unavailable'] = $mbp_unavailable;
        $data['mbp_waiting'] = $mbp_waiting;
        $data['mbp_on_progress'] = $mbp_on_progress;
        $data['mbp_check_in'] = $mbp_check_in;
        $data['mbp_dipinjamkan'] = @$mbp_dipinjamkan;
        $data['mbp_pinjaman'] = @$mbp_pinjaman; 
        $data['site_main_fail'] = $site_main_fail;
        $data['jumlah_tiket_MBP'] = $jumlah_tiket_MBP;
        $data['tiket_meet_SLA'] = $tiket_meet_SLA;
        $data['tiket_over_SLA'] = $tiket_over_SLA;
        $data['tiket_auto_close'] = $tiket_auto_close;
        $data['tiket_tidak_dikerjakan'] = 0;
        $data['tiket_complete'] = 0;
        
        $data['scope_id'] = $rtpo_id;
        $data['scope_name'] = $rtpo_name;
        $data['scope_level'] = 'rtpo_id';
        $data['next_scope_id'] = '-';
        $data['next_scope_level'] = '-';

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
        return $res;
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

    function bulan_tahun_indo($param)
    {
        list($y,$m) = explode('-', $param);
        return $this->bulan_indo($m).' '.$y;
    }

}