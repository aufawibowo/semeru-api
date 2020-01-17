<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class LoginNewController extends Controller
{

    public function __construct(){
        $this->current_date_time = date('Y-m-d H:i:s');
        $this->current_date = date('Y-m-d');
        $this->current_year = date('Y');
        $this->current_month = date('m');
    }
    
    public function otp_exists($otp_id){
        $query_otp = DB::table('sik_site');
        $query_otp->where('otp_id', $otp_id);
        $otp = $query_otp->first();
        return $otp ? true : false;
    }

    public function otp_spk_exists($otp_id){
        $query_otp = DB::table('spk_sparepart');
        $query_otp->where('otp_id', $otp_id);
        $otp = $query_otp->first();
        return $otp ? true : false;
    }

    public function getDistanceBetween($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'Km') 
    { 
        $theta = $longitude1 - $longitude2; 
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
        $distance = acos($distance); 
        $distance = rad2deg($distance); 
        $distance = $distance * 60 * 1.1515; 
        switch($unit) 
        { 
            case 'Mi': break; 
            case 'Km' : $distance = $distance * 1.609344; break;
            case 'm' : $distance = ($distance * 1.609344) * 1000;  break;
        } 
        return (round($distance,2)); 
    }

    public function updateRespondLookup($id_lookup, $respond)
    {
        date_default_timezone_set("Asia/Jakarta");
        $now = date('Y-m-d H:i:s');

        $query_update = DB::table('lookup_mt_gs_new');
        $query_update->where('id',$id_lookup);
        $query_update->update([
            'respond' => $respond,
            'date_updated' => $now,
        ]);
    }

    public function otp_to_number($in){
        $arr = str_split('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $index = array_flip($arr);
        $data = str_split($in);
        $num = '';
        foreach ($data as $v) { $num.=$index[$v]; }
        $num = substr($num, 1, 6);
        return $num;
    }

    public function loginMaintSite(Request $request){
    	$content = $request->input('content');
        $str = explode(" ",$content);
        #doing
        $key = @$str[1];

        if( substr($key, 0,3) =='GS/' ){
            return($this->login_gs($key));
        }else{
            return($this->login_maint_site($key));
        }   
    }

    public function login_gs($content){
        date_default_timezone_set("Asia/Jakarta");
        $now = date('Y-m-d H:i:s');

        $current_date = date('Y-m-d');
        $current_date_time = date('Y-m-d H:i:s');
        $current_year = date('Y');
        $current_month = date('m');

        $inp = $content;

        //$content = explode(" ", $inp);
        $str = explode("#", $inp);

        $spk_no = @$str[0];
        $latitude = @$str[1];
        $longitude = @$str[2];
        $username = @$str[3];
        $bypas=false;

        $str2 = explode("/", @$spk_no);
        $gnst_id = @$str2[2];

        if(in_array($username, [
            'root', 'suJTM001', 'suJTM003', 'su', 'su23', 'sentanuR0', 'su_galihhari', 'su_geng','enggarrio'
        ])) $bypas=true;

        $id_lookup = DB::table('lookup_mt_gs_new')->insertGetId([
            'content' => $inp,
            'spk_no' => $spk_no,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'username' => $username,
            'date_created' => $now,
        ]);

        $msg="-"; $site_id="-"; $otp="-"; $range_valid=false;

        $query_spk = DB::table('spk_sparepart')->select('*');
        $query_spk->where('spk_no', $spk_no);
        $spk = $query_spk->first();

        if(empty($spk)){ 
            $this->updateRespondLookup($id_lookup,"NGS404".'#'.$gnst_id); 
            exit('NGS404'.'#'.$gnst_id); 
        } //spk not found
        if($spk->spk_no!=$spk_no){
            $this->updateRespondLookup($id_lookup,"NGS404".'#'.$gnst_id);
            exit('NGS404'.'#'.$gnst_id);
        }
        if($spk->flag==0){ 
            $this->updateRespondLookup($id_lookup,"NGS410".'#'.$gnst_id); 
            exit("NGS410".'#'.$gnst_id); 
        } //spk deactive

        $site_id = $spk->site_id;

        list($y,$m,$d) = explode('-', $spk->replacement_schedule);

        switch ($spk->respond_status) {

            case 0: //not yet
            case 1: //reassign
            case 6: //incomplete
            case 7: //pending submit
                if($m!=$current_month || $y!=$current_year) {
                    $this->updateRespondLookup($id_lookup,"NGS405".'#'.$gnst_id); 
                    exit('NGS405'.'#'.$gnst_id);
                }

                $start  = $spk->min_schedule;
                $end    = $spk->max_schedule;
                
                if($current_date<$start || $current_date>$end){
                    $this->updateRespondLookup($id_lookup,"NGS408#".$gnst_id."#".date('d-m-Y',$start)."#".date('d-m-Y',$end));
                    exit('NGS408#'.$gnst_id.'#'.date('d-m-Y',$start).'#'.date('d-m-Y',$end));
                }
                break;
            case 2: 
                $this->updateRespondLookup($id_lookup,"NGS406".'#'.$gnst_id); 
                exit('NGS406'.'#'.$gnst_id); 
            break; //Report Sudah diapprove
            case 3: 
                $this->updateRespondLookup($id_lookup,"NGS407".'#'.$gnst_id); 
                exit('NGS407'.'#'.$gnst_id); 
            break; //Report Telah direject Oleh RTPO
            case 4: 
                $this->updateRespondLookup($id_lookup,"NGS406".'#'.$gnst_id); 
                exit('NGS406'.'#'.$gnst_id); 
            break; //Report Sudah diapprove
            case 5: 
                $this->updateRespondLookup($id_lookup,"NGS407".'#'.$gnst_id); 
                exit('NGS407'.'#'.$gnst_id); 
            break; //Report Sudah reject by system
            case 8: 
                $this->updateRespondLookup($id_lookup,"NGS412".'#'.$gnst_id); 
                exit('NGS412'.'#'.$gnst_id); 
            break; //Report dalam masa review
            default: 
                $this->updateRespondLookup($id_lookup,"UNDEFINED".'#'.$gnst_id); 
                exit('UNDEFINED'.'#'.$gnst_id); 
            break; //Report Telah direject Oleh RTPO
        }

        $query_accountSU = DB::table('users')->select('*');
        $query_accountSU->where('username',$username);
        $query_accountSU->whereIn('su',[1,2]);
        $accountSU = $query_accountSU->first();

        $query_user = DB::table('users')->select('*');
        $query_user->join('user_mbp_mt','users.username','=','user_mbp_mt.mbp_mt_username');
        $query_user->where('username',$username);
        $query_user->where('user_mbp_mt.status',1);
        $user = $query_user->first();

        if(empty($user) && empty($accountSU)){ 
            $this->updateRespondLookup($id_lookup,"NGS401".'#'.$gnst_id); 
            exit('NGS401'.'#'.$gnst_id); 
        }

        $fmc_valid=false;
        if($user){
            $fmc_valid = ( $spk->fmc_id==$user->fmc_id) ? true:false;
        } elseif($accountSU){
            $fmc_valid=true;
        }

        if($fmc_valid==false && $bypas==false){ 
            $this->updateRespondLookup($id_lookup,"NGS403".'#'.$gnst_id); 
            exit('NGS403'.'#'.$gnst_id); 
        } // Akses Ditolak! ('jika ada validasi cluster tambahkan disini')

        //validasi cluster
        $cluster_valid=false;
        if($user){
            $cluster_valid = ( $spk->cluster_id==$user->cluster_id) ? true:false;
        } elseif($accountSU){
            $cluster_valid=true;
        }

        if ($bypas==false && $cluster_valid==false){
            $this->updateRespondLookup($id_lookup,"NGS403".'#'.$gnst_id);
            exit('NGS403'.'#'.$gnst_id);
        }

        if(empty($spk->otp_id)){
            $this->updateRespondLookup($id_lookup,"NGS500".'#'.$gnst_id);
            exit('NGS500'.'#'.$gnst_id);
        }

        $query_update_spk = DB::table('spk_sparepart');
        $query_update_spk->where('spk_no',$spk_no);
        $query_update_spk->update([
            'has_login' => 1
        ]);

        if ($id_lookup>0){
            $distance = $this->getDistanceBetween(doubleval($spk->latitude), doubleval($spk->longitude), doubleval($latitude), doubleval($longitude), 'Km');
            if($distance>1 && $bypas==false){
                //DISINI DI CEK DENGANDATA SITE TERBARU
                $checksite = DB::table('site')
                ->select('site_id','latitude','longitude')
                ->where('site_id','=',$site_id)
                ->first();
                $jaraks = @$this->getDistanceBetween(doubleval(@$checksite->latitude), doubleval(@$checksite->longitude), doubleval($latitude), doubleval($longitude), "Km");

                if (@$jaraks<1) {
                    $msg = "NGS200";
                } else{
                    $report_data = DB::table('report_location_site as rls')
                    ->join('site as s','rls.site_id','s.site_id')
                    ->select('rls.*', 's.latitude as old_lat', 's.longitude as old_lon')
                    ->where('rls.site_id','=',$site_id)
                    ->where('rls.approval','=',1)
                    ->orderBy('rls.respon_by_rtpo_at','=','asc')
                    ->first();
                    if ($report_data == null) {
                        $msg="NGS400"; //Out of Range
                    } else{
                        if (@$report_data->approval==5){
                            $msg="NGS400"; //Out of Range
                        } else{
                            $get_distance = $this->getDistanceBetween(doubleval(@$report_data->new_lat), doubleval(@$report_data->new_lon), doubleval($latitude), doubleval($longitude), 'Km');
                            if ($get_distance>1) {
                                $msg="NGS400"; //Out of Range
                            } else $msg="NGS200";
                        }
                    }
                }
            } else{
                $msg="NGS200";
            }
        } else{
            $this->updateRespondLookup($id_lookup,"NGS500".'#'.$gnst_id);
            exit('NGS500'.'#'.$gnst_id);
        }

        //print_r($msg.' ');

        $month = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        if ($msg=="NGS200"){
            $site_name = $spk->site_name; $otp_spk = $spk->otp_id;
            $otp_sik = "-"; $site_type = "-"; $utype = "-"; $team_code = "-"; $mandatory_form = "-"; $gt = "-"; $gr = "-"; $spk_no = $spk->spk_no;
            $this->updateRespondLookup($id_lookup,$msg.'#'.$site_id.'#'.$site_name.'#'.$otp_sik.'#'.$site_type.'#'.$utype.'#'.$team_code.'#'.$mandatory_form.'#'.$gt.'#'.$gr."#".$spk_no.'#'.$otp_spk);
            exit($msg.'#'.$site_id.'#'.$site_name.'#'.$otp_sik.'#'.$site_type.'#'.$utype.'#'.$team_code.'#'.$mandatory_form.'#'.$gt.'#'.$gr."#".$spk_no.'#'.$otp_spk.'#'.$month[(int)$current_month - 1].' '.$current_year);
        } else{
            $this->updateRespondLookup($id_lookup,$msg.'#'.$gnst_id);
            exit($msg.'#'.$gnst_id);
        }
    }

    public function login_maint_site($content)
    {
        date_default_timezone_set("Asia/Jakarta");
        $now = date('Y-m-d H:i:s');

        $current_date = date('Y-m-d');
        $current_date_time = date('Y-m-d H:i:s');
        $current_year = date('Y');
        $current_month = date('m');

        $inp = $content;

        //$content = explode(" ", $inp);
        $str = explode("#", $inp);

        $sik_no = @$str[0];
        $latitude = @$str[1];
        $longitude = @$str[2];
        $username = @$str[3];
        $utype = @$str[4];
        $team_code = @$str[5];
        $new_teamcode = "";
        $bypas=false;

        $str2 = explode("/", @$sik_no);
        $site_id = @$str2[1];

        $otp_num = @$str2[0];
        
        $id_lookup = DB::table('lookup_mt_gs_new')->insertGetId([
            'content' => $inp,
            'sik_no' => $sik_no,
            'site_id' => $site_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'username' => $username,
            'utype' => $utype,
            'team_code' => $team_code,
            'date_created' => $now,
        ]);

        if ($otp_num=='') {
            $this->updateRespondLookup($id_lookup,"NGS500"); 
            exit('NGS500'.'#'.$site_id);
        }

        if(in_array($username, [
            'root', 'suJTM001', 'suJTM003', 'su', 'su23', 'sentanuR0', 'su_galihhari', 'su_geng', 'enggarrio'
        ])) $bypas=true;

        $msg="-"; $otp="";
        $query_sik = DB::table('sik_site')->select('*');
        $query_sik->where('sik_no', $sik_no);
        $sik = $query_sik->first();

        if ($sik) {
            if ($sik->sik_no!=$sik_no) { $this->updateRespondLookup($id_lookup,"NGS404".'#'.$site_id); exit('NGS404'.'#'.$site_id); }

            if ($sik->flag==0) {$this->updateRespondLookup($id_lookup,"NGS410".'#'.$site_id); exit('NGS410'.'#'.$site_id);}
            
            list($y,$m,$d) = explode("-", $sik->maintenance_schedule);

            $site_name = $sik->site_name;
            $site_type = str_replace(" ", "", $sik->kriteria_site);

            if(empty($sik->team_code)){
                $new_teamcode = $this->otp_to_number($sik->otp_id);
            }

            if(!empty(@$new_teamcode)){
                $updateTeamCode = DB::table('sik_site')
                ->where('sik_no',$sik_no)
                ->update([
                    'team_code' => $new_teamcode,
                ]);
            }

            if($sik->mt_status==1){
                if($utype == 3){//member
                    if($sik->team_code=='' || $sik->team_code!=$team_code) {$this->updateRespondLookup($id_lookup,"NGS409".'#'.$site_id); exit('NGS409'.'#'.$site_id);}
                }
                $team_code=$sik->team_code;
                $utype = 3;
            } else{
                if($utype == 2){//leader
                    //$new_teamcode = sprintf("%06d", mt_rand(1, 999999));
                    $new_teamcode = $this->otp_to_number($sik->otp_id);
                    $team_code = $new_teamcode;
                }elseif($utype == 3){//member
                    if($sik->team_code=='' || $sik->team_code!=$team_code) {$this->updateRespondLookup($id_lookup,"NGS409".'#'.$site_id); exit('NGS409'.'#'.$site_id);}
                }
            }

            if($bypas==false){
                switch ($sik->respond_status) {

                    case 0: 
                    case 6: 
                    case 7:
                        $start_of_month = $y.'-'.$m.'-01';
                        $end_of_month  = date("Y-m-t",strtotime($y."-".$m));
                        $end_of_month2 = $end_of_month.'';
                        $range_valid=false;
                        $maintenance_schedule=$sik->maintenance_schedule;
                        $a=7; $b=7;
                        $awal = $sik->min_schedule;
                        $akhir = $sik->max_schedule;

                        /*
                        if (strtotime($start_of_month)>=strtotime($awal)) {
                            $start = $start_of_month;
                        } else{
                            $start = $awal;
                        }

                        
                        if (strtotime($end_of_month2)<=strtotime($akhir)) {
                            $end = $end_of_month2;
                        } else{
                            $end = $akhir;
                        }
                        */

                        $start = $start_of_month>=$awal ? $start_of_month : $awal;
                        $end = $end_of_month<=$akhir ? $end_of_month : $akhir;

                        if($current_date<$start || $current_date>$end) {
                            $this->updateRespondLookup($id_lookup,"NGS408#".$site_id."#".date('d-m-Y',strtotime($start))."#".date('d-m-Y',strtotime($end))); 
                            exit('NGS408#'.$site_id.'#'.date('d-m-Y',strtotime($start)).'#'.date('d-m-Y',strtotime($end)));
                        }
                        break;
                    
                    case 1:
                    // exit('^ _ ^');
                        if($m!=$current_month || $y!=$current_year) {$this->updateRespondLookup($id_lookup,"NGS405".'#'.$site_id); exit('NGS405'.'#'.$site_id);} //SIK Anda Tidak Berlaku!
                        break;
                    case 2: $this->updateRespondLookup($id_lookup,"NGS406"); exit('NGS406'.'#'.$site_id); break; //Maintenance Sudah diapprove
                    case 3: $this->updateRespondLookup($id_lookup,"NGS407"); exit('NGS407'.'#'.$site_id); break; //Maintenance Telah direject Oleh RTPO
                    case 4: $this->updateRespondLookup($id_lookup,"NGS406"); exit('NGS406'.'#'.$site_id); break; //Maintenance Sudah diapprove
                    case 5: $this->updateRespondLookup($id_lookup,"NGS407"); exit('NGS407'.'#'.$site_id); break; //Maintenance Sudah reject by system
                    case 8: $this->updateRespondLookup($id_lookup,"NGS412"); exit('NGS412'.'#'.$site_id); break; //Maintenance Report dalam masa review
                    default: $this->updateRespondLookup($id_lookup,"UNDEFINED"); exit('UNDEFINED'.'#'.$site_id); break; //Maintenance Telah direject Oleh RTPO
                }
            }

            $query_accountSU = DB::table('users')->select('*');
            $query_accountSU->where('username',$username);
            $query_accountSU->whereIn('su',[1,2]);
            $accountSU = $query_accountSU->first();

            //$site_id=@$sik->site_id;
            $site_name=@$sik->site_name;

            $query_user = DB::table('users')->select('*');
            $query_user->join('user_mbp_mt','users.username','=','user_mbp_mt.mbp_mt_username');
            $query_user->where('username',$username);
            $query_user->where('user_mbp_mt.status',1);
            $user = $query_user->first();

            if ($user || $accountSU){

                $fmc_valid=false;
                if($user){
                    $fmc_valid = ( $sik->fmc_id==$user->fmc_id) ? true:false;
                } elseif($accountSU){
                    $fmc_valid=true;
                }

                $cluster_valid=false;
                if($user){
                    $cluster_valid = ( $sik->cluster_id==$user->cluster_id) ? true:false;
                } elseif($accountSU){
                    $cluster_valid=true;
                }

                if ($bypas==false && $cluster_valid==false){
                    $this->updateRespondLookup($id_lookup,"NGS403".'#'.$site_id);
                    exit('NGS403'.'#'.$site_id);
                }

                if($fmc_valid || $bypas==true ) {
                    if(empty($sik->otp_id)){
                        $this->updateRespondLookup($id_lookup,"NGS500");
                        exit('NGS500'.'#'.$site_id);
                    }
                    //update has login+teamcode if teamcode!=''
                    $query_update_sik = DB::table('sik_site');
                    $query_update_sik->where('sik_no',$sik_no);
                    $query_update_sik->update([
                        'has_login' => 1
                    ]);

                    if ($team_code!=''){
                        $query_update_sik = DB::table('sik_site');
                        $query_update_sik->where('sik_no',$sik_no);
                        $query_update_sik->update([
                            'team_code' => $team_code
                        ]);
                    }

                    if ($id_lookup>0){
                        $distance = $this->getDistanceBetween(doubleval($sik->latitude), doubleval($sik->longitude), doubleval($latitude), doubleval($longitude), 'Km');

                        if($distance>1 && $bypas==false){

                            //DISINI DI CEK DENGANDATA SITE TERBARU
                            $checksite = DB::table('site')
                            ->select('site_id','latitude','longitude')
                            ->where('site_id','=',$site_id)
                            ->first();
                            $jaraks = @$this->getDistanceBetween(doubleval(@$checksite->latitude), doubleval(@$checksite->longitude), doubleval($latitude), doubleval($longitude), "Km");

                            if (@$jaraks<1) {
                                $msg = "NGS200";
                            } else{
                                $report_data = DB::table('report_location_site as rls')
                                ->join('site as s','rls.site_id','s.site_id')
                                ->select('rls.*', 's.latitude as old_lat', 's.longitude as old_lon', 'rls.base_url', 'rls.fname')
                                ->where('rls.site_id','=',$site_id)
                                ->where('rls.approval','=',1)
                                ->orderBy('rls.respon_by_rtpo_at','asc')
                                ->first();
                                if ($report_data == null) {
                                    $msg="NGS400"; //Out of Range
                                } else{
                                    if (@$report_data->approval==5){
                                        $msg="NGS400"; //Out of Range
                                    } else{
                                        $get_distance = $this->getDistanceBetween(doubleval(@$report_data->new_lat), doubleval(@$report_data->new_lon), doubleval($latitude), doubleval($longitude), 'Km');
                                        if ($get_distance>1) {
                                            $msg="NGS400"; //Out of Range
                                        } else $msg="NGS200";
                                    }
                                }
                            }
                        } else $msg="NGS200";
                    } else $msg="NGS500"; //Terjadi Kesalahan Pada Server
                } else $msg="NGS403"; // Akses Ditolak!
            } else $msg="NGS401"; //Akun Tidak Ditemukan
        } else $msg="NGS404"; //Nomor SIK Tidak Sesuai/Tdk ditemukan

        $month = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

        if ($msg=="NGS200"){
            $code_gt = ['GT1','GT2','GT3','GT4','GT5','GT6','GT7','GT8'];
            $code_gr = ['GR1','GR2'];
            $gt = in_array($sik->fix_genset_tsel, $code_gt) ? $sik->fix_genset_tsel : '-';
            $gr = in_array($sik->fix_genset_rent, $code_gr) ? $sik->fix_genset_rent : '-';
            $spk_no="-"; $otp_spk='-';

            $query_spk = DB::table('spk_sparepart')->select('*');
            $query_spk->where('site_id',$site_id);
            $query_spk->where('periode',$y.'-'.$m);
            $query_spk->where('flag',1);
            $spk = $query_spk->first();

            if(!empty($spk)){
                if($current_date>=$spk->min_schedule && $current_date<=$spk->max_schedule && in_array($spk->respond_status, [0,1,6,7])){
                    $spk_no = $spk->spk_no;
                    $gnst_id = $spk->gnst_id;

                    if(empty($spk->otp_id)){
                        $this->updateRespondLookup($id_lookup,"NGS500".'#'.$gnst_id);
                        exit('NGS500'.'#'.$gnst_id);
                    }

                    //update has login
                    $query_update_spk = DB::table('spk_sparepart');
                    $query_update_spk->where('spk_no',$spk->spk_no);
                    $query_update_spk->update([
                        'has_login' => 1
                    ]);

                    $update_spk_gs = DB::table('lookup_mt_gs_new')
                    ->where('id',$id_lookup)
                    ->update(['spk_no' => $spk->spk_no]);

                    if ($update_spk_gs==NULL || $update_spk_gs<1) {$this->updateRespondLookup($id_lookup,"NGS500"); exit('NGS500');}

                    $otp_spk = $spk->otp_id;
                }
            }

            $this->updateRespondLookup($id_lookup,$msg.'#'.$site_id.'#'.$site_name.'#'.$sik->otp_id.'#'.$site_type.'#'.$utype.'#'.$team_code.'#'.$sik->mandatory_form.'#'.$gt.'#'.$gr."#".$spk_no.'#'.$otp_spk.'#'.$month[(int)$current_month - 1].' '.$current_year);
            exit($msg.'#'.$site_id.'#'.$site_name.'#'.$sik->otp_id.'#'.$site_type.'#'.$utype.'#'.$team_code.'#'.$sik->mandatory_form.'#'.$gt.'#'.$gr."#".$spk_no.'#'.$otp_spk.'#'.$month[(int)$current_month - 1].' '.$current_year);
        } else{
            $this->updateRespondLookup($id_lookup,$msg.'#'.$site_id);
            exit($msg.'#'.$site_id);
        }
    }

    public function cekJarak(Request $request) 
    { 
        $latitude1 = $request->input('latitude1');
        $longitude1 = $request->input('longitude1');
        $latitude2 = $request->input('latitude2');
        $longitude2 = $request->input('longitude2');
        $unit = $request->input('unit');

        $theta = $longitude1 - $longitude2; 
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2)))  + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
        $distance = acos($distance); 
        $distance = rad2deg($distance); 
        $distance = $distance * 60 * 1.1515; 
        switch($unit) 
        { 
            case 'Mi': break; 
            case 'Km' : $distance = $distance * 1.609344; break;
            case 'm' : $distance = ($distance * 1.609344) * 1000;  break;
        } 
        return (round($distance,2)); 
    }

    /*  RESPON LOGIN APP
    NGS200 Success
    NGS401 Akun Tidak Ditemukan
    NGS402 Nomor Telpon Tidak Sesuai
    NGS403 Akses Ditolak
    NGS500 Terjadi Kesalahan Pada Server

    RESPON LOGIN MT
    NGS200 Success
    NGS400 OUT OF RANGE
    NGS401 Akun Tidak Ditemukan
    NGS402 Nomor Telpon Tidak Sesuai
    NGS403 Akses Ditolak
    NGS404 Nomor SIK Tidak Sesuai
    NGS405 Nomor SIK Tidak Berlaku
    NGS406 Maintenance Sudah di Approve
    NGS407 Maintenance Telah direject Oleh RTPO
    NGS408 Masa Berlaku SIK Anda mulai Tgl $awal sampai $akhir
    NGS409 Invalid Team Code
    NGS410 SIK Telah dinonaktifkan
    NGS500 Terjadi Kesalahan Pada Server
    */

}