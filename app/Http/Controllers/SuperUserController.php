<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class SuperUserController extends Controller
{

  public function getListRegional(Request $request){

    $data['regional'][0] = 'JATIM';
    $data['regional'][1] = 'JATENG-DIY';
    $data['regional'][2] = 'BALI NUSRA';

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $data;
    return response($res);
  }

  public function getListRtpoRegional(Request $request){

    $regional = $request->input('regional');
    $username = $request->input('username');

    if ($regional==null) {


      $checkSuperUser = DB::table('users')
      ->select('su_regional as regional')
      ->where('username',$username)
      ->where('su_regional','!=',null)
      ->first();

      if ($checkSuperUser ==null) {
        $res['success'] = false;
        $res['message'] = 'REGIONAL ON USER NOT FOUND ';
        // $res['data'] = $list_rtpo;
        return response($res);
      }
      $regional = $checkSuperUser->regional;
    }

    $list_rtpo = DB::table('rtpo')
    ->select('rtpo_id as id','rtpo_name as name')
    ->where('regional',$regional)
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }
  public function getListNsaRegional(Request $request){

    $regional = $request->input('regional');
    $username = $request->input('username');

    if ($regional==null) {


      $checkSuperUser = DB::table('users')
      ->select('su_regional as regional')
      ->where('username',$username)
      ->where('su_regional','!=',null)
      ->first();

      if ($checkSuperUser ==null) {
        $res['success'] = false;
        $res['message'] = 'REGIONAL ON USER NOT FOUND ';
        // $res['data'] = $list_rtpo;
        return response($res);
      }
      $regional = $checkSuperUser->regional;
    }

    // $list_rtpo = DB::table('rtpo')
    // ->select('rtpo_id as id','rtpo_name as name')
    // ->where('regional',$regional)
    // ->get();


    $list_rtpo = DB::table('lookup_fmc_cluster as lfc')
    ->select('ns_id as id','ns as name')
    ->where('regional',$regional)
    ->groupBy('ns_id')
    ->orderBy('ns','ASC')
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }

  public function getListFmcRegional(Request $request){

    $regional = $request->input('regional');
    $username = $request->input('username');

    if ($regional==null) {


      $checkSuperUser = DB::table('users')
      ->select('su_regional as regional')
      ->where('username',$username)
      ->where('su_regional','!=',null)
      ->first();

      if ($checkSuperUser ==null) {
        $res['success'] = false;
        $res['message'] = 'REGIONAL ON USER NOT FOUND ';
        // $res['data'] = $list_rtpo;
        return response($res);
      }
      $regional = $checkSuperUser->regional;
    }

    $list_rtpo = DB::table('fmc')
    ->select('fmc_id as id','fmc_name as name')
    ->where('regional',$regional)
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }
  public function getListFmccluster(Request $request){

    $fmc_id = $request->input('fmc_id');
    // $username = $request->input('username');    

    $list_cluster = DB::table('lookup_fmc_cluster')
    ->select('fmc','cluster_id','cluster')
    ->where('fmc_id',$fmc_id)
    ->where('periode','2018-2021')
    ->where('status','1')
    ->groupBy('cluster')
    ->orderBy('cluster','ASC')
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_cluster;
    return response($res);
  }

  public function getListRtpoRegionalCpo(Request $request){

    $regional = $request->input('regional');
    $username = $request->input('username');

    if ($regional==null) {


      $checkSuperUser = DB::table('users')
      ->select('regional')
      ->where('username',$username)
      ->where('regional','!=',null)
      ->first();

      if ($checkSuperUser ==null) {
        $res['success'] = false;
        $res['message'] = 'REGIONAL ON USER NOT FOUND ';
        // $res['data'] = $list_rtpo;
        return response($res);
      }
      $regional = $checkSuperUser->regional;
    }

    $list_rtpo = DB::table('rtpo')
    ->select('rtpo_id as id','rtpo_name as name')
    ->where('regional',$regional)
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }
  public function getListFmcRegionalCpo(Request $request){

    $regional = $request->input('regional');
    $username = $request->input('username');

    if ($regional==null) {


      $checkSuperUser = DB::table('users')
      ->select('regional')
      ->where('username',$username)
      ->where('regional','!=',null)
      ->first();

      if ($checkSuperUser ==null) {
        $res['success'] = false;
        $res['message'] = 'REGIONAL ON USER NOT FOUND ';
        // $res['data'] = $list_rtpo;
        return response($res);
      }
      $regional = $checkSuperUser->regional;
    }

    $list_rtpo = DB::table('fmc')
    ->select('fmc_id as id','fmc_name as name')
    ->where('regional',$regional)
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }


  public function getListRtpoRegionalNsa(Request $request){

    // $regional = $request->input('regional');
    $username = $request->input('username');


    $list_rtpo = DB::table('user_mng_nsa as ums')
    ->join('lookup_fmc_cluster as lfc', 'ums.ns_id', 'lfc.ns_id')
    ->select('lfc.rtpo_id as id','lfc.rtpo as name')
    ->where('ums.username',$username)
    ->where('lfc.status','1')
    ->groupBy('lfc.rtpo_id')
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }
  public function getListFmcRegionalNsa(Request $request){

    // $regional = $request->input('regional');
    $username = $request->input('username');

    
    $list_rtpo = DB::table('user_mng_nsa as ums')
    ->join('lookup_fmc_cluster as lfc', 'ums.ns_id', 'lfc.ns_id')
    ->select('fmc_id as id','fmc as name')
    ->where('ums.username',$username)
    ->where('lfc.status','1')
    ->groupBy('lfc.fmc_id')
    ->get();

    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    $res['data'] = $list_rtpo;
    return response($res);
  }

  public function signinSuperUser2(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $username = $request->input('username');
    $regional = $request->input('regional');
    $roles = $request->input('roles'); // berupa array
    $rtpo_fmc_id = $request->input('rtpo_fmc_id'); // berupa array
    $firebase_token = $request->input('firebase_token'); // berupa array

    $checkSuperUser = DB::table('users')
    ->select('*')
    ->where('username','=',$username)
    ->first();

    // echo "string";
    // exit();
    
    if ($checkSuperUser==null) {
      $res['success'] = false;
      $res['message'] = 'SUPER USER NOT FOUND';
      // $res['data'] = $list_rtpo;
    }


    $RtrRoles = '';
    // foreach ($roles as $value) {
    $isRtpo = 0;
    foreach ($roles as $param => $row) {

      // $mbps[$param]  = $row['mbp_id'].''; 

      $RtrRoles .=','.$row['role'].'';

      if ($row['role']=='4') {
        $isRtpo=1;
      }

    }

    $dataUpdateUser['roles_id'] = $RtrRoles;
    
    if (/*in_array('4', $roles)*/$isRtpo==1) {
      $dataUpdateUser['user_type'] = 'RTPO';

      $checkDataLookup = DB::table('lookup_fmc_cluster')
      ->select('*')
      ->where('rtpo_id','=',$rtpo_fmc_id)
      ->first();

      if ($checkDataLookup==null) {
        $res['success'] = false;
        $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
      }

      // insert update di userrtpo
      # delete dulu data lama
      DB::table('user_rtpo')->where('username','=',$username)->delete();
      # isi data baru
      $insert_user_rtpo_data = DB::table('user_rtpo')
      ->insert(
        [
          'username'=> @$username,
          'cluster_name'=> @$checkDataLookup->cluster, 
          'cluster_id'=> @$checkDataLookup->cluster_id,
          'rtpo_id'=> @$rtpo_fmc_id,
          'rtpo_name'=> @$checkDataLookup->rtpo, 
          'last_update'=> @$date_now, 
          'status'=> 1,
        ]
      );

    }else{
      $dataUpdateUser['user_type'] = 'MBP';

      // insert update di user_mbp_mt

      $checkDataLookup = DB::table('lookup_fmc_cluster')
      ->select('*')
      ->where('fmc_id','=',$rtpo_fmc_id)
      ->first();

      if ($checkDataLookup==null) {
        $res['success'] = false;
        $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
      }

      # delete dulu data lama
      DB::table('user_mbp_mt')->where('mbp_mt_username','=',$username)->delete();
      # isi data baru
      $insert_user_mt_data = DB::table('user_mbp_mt')
      ->insert(
        [
          'mbp_mt_nik'=> $checkSuperUser->id, 
          'mbp_mt_cn'=> @$username,
          'mbp_mt_username'=> @$username,
          'last_update'=> @$date_now, 
          'status'=> 1,

          'fmc_id'=> @$rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc, 
          'cluster'=> @$checkDataLookup->cluster, 
          'cluster_id'=> @$checkDataLookup->cluster_id, 
        ]
      );

          # update baru
      $register = DB::table('users')
      ->where('id','=',$checkSuperUser->id)
      ->update(
        [
          'fmc_id'=> $rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc,
          'regional'=> $regional,
        ]
      );
    }

    if($checkSuperUser->su==2){
     $dataUpdateUser['regional'] = $regional; 
    }

    $updateSuperUser = DB::table('users')
    ->where('username',$username)
    ->update($dataUpdateUser);

    if (!$updateSuperUser) {
      $res['success'] = false;
      $res['message'] = 'GAGAL EDIT';
      $res['data'] = $dataUpdateUser;
      $res['USR'] = $updateSuperUser;
    }

    
    #setelah sukses update maka panggil fungsi login
    return $this->login($username,$firebase_token,'kagfs');
    

    // $res['success'] = true;
    // $res['message'] = 'SUCCESS';
    // $res['data'] = $dataUpdateUser;
    // return response($res);
  }

public function signinSuperUser(Request $request){
    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $username = $request->input('username');
    $regional = $request->input('regional');
    $mbp_id_req = $request->input('mbp_id');
    $cluster_id = $request->input('cluster_id');
    $roles = $request->input('roles'); // berupa array
    $rtpo_fmc_id = $request->input('rtpo_fmc_id'); // berupa array
    $firebase_token = $request->input('firebase_token'); // berupa array
    $ns_id = $request->input('ns_id');



    $checkSuperUser = DB::table('users')
    ->select('*')
    ->where('username','=',$username)
    ->first();

    if ($checkSuperUser==null) {
      $res['success'] = false;
      $res['message'] = 'SUPER USER NOT FOUND';
    }

    if (@$checkSuperUser->su == 1) {
      $regional = @$checkSuperUser->su_regional;
    }

    DB::table('user_mbp')->where('username','=',$username)->delete();
    DB::table('user_mbp_mt')->where('mbp_mt_username','=',$username)->delete();
    DB::table('user_tsra')->where('tsra_username','=',$username)->delete();
    DB::table('user_rtpo')->where('username','=',$username)->delete();
    DB::table('user_cpo')->where('username','=',$username)->delete();
    DB::table('user_mng_nsa')->where('username','=',$username)->delete();

    $RtrRoles = ','.$roles;
    // foreach ($roles as $value) {
    // $isRtpo = 0;
    // foreach ($roles as $param => $row) {

    //   // $mbps[$param]  = $row['mbp_id'].''; 

    //   $RtrRoles .=','.$row;

    //   if ($row['roles']=='4') {
    //     // $isRtpo=1;
    //   }

    // }

    // return($RtrRoles);
    //ngecek dia role apa..
    if ($RtrRoles == ',4') {          #RTPO

      $dataUpdateUser['user_type'] = 'RTPO';  //----------------------------------------------
      $checkDataLookup = DB::table('lookup_fmc_cluster')
      ->select('*')
      ->where('rtpo_id','=',$rtpo_fmc_id)
      ->where('periode','=',"2018-2021") 
      ->first();
      if ($checkDataLookup==null) {
        $res['success'] = false;
        $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
      }
      DB::table('user_rtpo')->where('username','=',$username)->delete();
      # isi data baru
      $insert_user_rtpo_data = DB::table('user_rtpo')
      ->insert(
        [
          'username'=> @$username,
          'cluster_name'=> @$checkDataLookup->cluster, 
          'cluster_id'=> @$checkDataLookup->cluster_id,
          'rtpo_id'=> @$rtpo_fmc_id,
          'rtpo_name'=> @$checkDataLookup->rtpo, 
          'last_update'=> @$date_now, 
          'status'=> 1,
        ]
      );

    }else if ($RtrRoles == ',7') {    #TM
      $dataUpdateUser['user_type'] = 'MBP';
      // insert update di user_mbp_mt
      $checkDataLookup = DB::table('lookup_fmc_cluster')
      ->select('*')
      ->where('fmc_id','=',$rtpo_fmc_id) 
      ->where('periode','=',"2018-2021") 
      ->where('cluster_id','=',$cluster_id)
      ->first();
      if ($checkDataLookup==null) {
        $res['success'] = false;
        $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
      }
      # isi data baru
      $insert_user_mt_data = DB::table('user_mbp_mt')
      ->insert(
        [
          'mbp_mt_nik'=> $checkSuperUser->id, 
          'mbp_mt_cn'=> @$username,
          'mbp_mt_username'=> @$username,
          'last_update'=> @$date_now, 
          'status'=> 1,

          'fmc_id'=> @$rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc, 
          'regional'=> @$checkDataLookup->regional, 
          'cluster'=> @$checkDataLookup->cluster, 
          'cluster_id'=> @$checkDataLookup->cluster_id, 
        ]
      );
          # update baru
      $register = DB::table('users')
      ->where('id','=',$checkSuperUser->id)
      ->update(
        [
          'fmc_id'=> $rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc,
          'cluster_id'=> @$checkDataLookup->cluster_id,
          'cluster'=> @$checkDataLookup->cluster,
          'regional'=> $regional,
          'roles_id'=> $RtrRoles,
        ]
      );

    }else if ($RtrRoles == ',8') {    #MBP
      $dataUpdateUser['user_type'] = 'MBP';
      $checkDataLookup = DB::table('lookup_fmc_cluster')
      ->select('*')
      ->where('fmc_id','=',$rtpo_fmc_id)
      ->where('periode','=',"2018-2021") 
      ->where('cluster_id','=',$cluster_id)
      ->first();
      if ($checkDataLookup==null) {
        $res['success'] = false;
        $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
      }
      # isi data baru
      // $insert_user_mt_data = DB::table('user_mbp')
      // ->insert(
      //   [
      //     'mbp_mt_nik'=> $checkSuperUser->id, 
      //     'username'=> @$username,
      //     'mbp_mt_cn'=> @$username,
      //     'last_update'=> @$date_now, 
      //     'status_user_mbp'=> 1,
      //     'mbp_id'=>$mbp_id_req,

      //     'fmc_id'=> @$rtpo_fmc_id,
      //     'fmc'=> @$checkDataLookup->fmc, 
      //     'cluster'=> @$checkDataLookup->cluster, 
      //     'cluster_id'=> @$checkDataLookup->cluster_id, 
      //   ]
      // );
          # update baru
      $register = DB::table('users')
      ->where('id','=',$checkSuperUser->id)
      ->update(
        [
          'fmc_id'=> $rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc,
          'cluster_id'=> @$checkDataLookup->cluster_id,
          'cluster'=> @$checkDataLookup->cluster,
          'regional'=> $regional,
          'roles_id'=> $RtrRoles,
        ]
      );

    }else if ($RtrRoles == ',11') {   #TSRA
      $dataUpdateUser['user_type'] = 'MBP';
      $checkDataLookup = DB::table('lookup_fmc_cluster')
      ->select('*')
      ->where('fmc_id','=',$rtpo_fmc_id)
      ->where('periode','=',"2018-2021") 
      ->where('cluster_id','=',$cluster_id)
      ->first();

      if ($checkDataLookup==null) {
        $res['success'] = false;
        $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
      }
      # isi data baru
      $insert_user_mt_data = DB::table('user_tsra')
      ->insert(
        [
          'tsra_nik'=> $checkSuperUser->id, 
          'tsra_cn'=> @$username,
          'tsra_username'=> @$username,
          'last_update'=> @$date_now, 
          'status'=> 1,

          'fmc_id'=> @$rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc,
          'regional'=> @$checkDataLookup->regional, 
          'cluster'=> @$checkDataLookup->cluster, 
          'cluster_id'=> @$checkDataLookup->cluster_id, 
        ]
      );
          # update baru
      $register = DB::table('users')
      ->where('id','=',$checkSuperUser->id)
      ->update(
        [
          'fmc_id'=> $rtpo_fmc_id,
          'fmc'=> @$checkDataLookup->fmc,
          'regional'=> $regional,
          'cluster_id'=> @$checkDataLookup->cluster_id,
          'cluster'=> @$checkDataLookup->cluster, 
          'roles_id'=> $RtrRoles,
        ]
      );
    }else if ($RtrRoles == ',10') {   #CPO
      $dataUpdateUser['user_type'] = '';
      # isi data baru
      $insert_user_mt_data = DB::table('user_cpo')
      ->insert(
        [
          'user_nik'=> $checkSuperUser->id, 
          'username'=> @$username,
          'user_cn'=> @$username,
          'regional'=> @$regional, 
          'status'=> 1,
          'last_update'=> @$date_now, 
        ]
      );
          # update baru
      $register = DB::table('users')
      ->where('id','=',$checkSuperUser->id)
      ->update(
        [
          'regional'=> $regional,
          'roles_id'=> $RtrRoles,
        ]
      );
    }else if ($RtrRoles == ',2') {   #NSA
      $dataUpdateUser['user_type'] = '';
      # isi data baru

    $ns_data = DB::table('lookup_fmc_cluster as lfc')
    ->select('lfc.*')
    // ->where('lfc.ns_id',@$ns_id)
    ->where('lfc.ns_id',@$rtpo_fmc_id)
    ->first();

    // return(@$ns_data->ns);

      $insert_user_mt_data = DB::table('user_mng_nsa')
      ->insert(
        [
          // 'user_nik'=> $checkSuperUser->id, 
          'username'=> @$username,
          'ns'=> @$ns_data->ns,
          'ns_id'=> @$ns_data->ns_id,
          'regional'=> @$ns_data->regional,
          'last_update'=> @$date_now, 
        ]
      );
          # update baru
      $register = DB::table('users')
      ->where('id','=',$checkSuperUser->id)
      ->update(
        [
          'regional'=> $regional,
          'roles_id'=> $RtrRoles,
        ]
      );
    }


    // $res['rtpo_fmc_id'] = $rtpo_fmc_id;
    // return response($res);

    $dataUpdateUser['roles_id'] = $RtrRoles;
    $dataUpdateUser['fmc_id'] = @$rtpo_fmc_id;
    // $dataUpdateUser['fmc'] = $checkDataLookup->fmc;
    
    // if (/*in_array('4', $roles)*/$isRtpo==1) {
    //   $dataUpdateUser['user_type'] = 'RTPO';

    //   $checkDataLookup = DB::table('lookup_fmc_cluster')
    //   ->select('*')
    //   ->where('rtpo_id','=',$rtpo_fmc_id)
    //   ->first();

    //   if ($checkDataLookup==null) {
    //     $res['success'] = false;
    //     $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
    //   }

    //   // insert update di userrtpo
    //   # delete dulu data lama
    //   DB::table('user_rtpo')->where('username','=',$username)->delete();
    //   # isi data baru
    //   $insert_user_rtpo_data = DB::table('user_rtpo')
    //   ->insert(
    //     [
    //       'username'=> @$username,
    //       'cluster_name'=> @$checkDataLookup->cluster, 
    //       'cluster_id'=> @$checkDataLookup->cluster_id,
    //       'rtpo_id'=> @$rtpo_fmc_id,
    //       'rtpo_name'=> @$checkDataLookup->rtpo, 
    //       'last_update'=> @$date_now, 
    //       'status'=> 1,
    //     ]
    //   );

    // }else{
    //   $dataUpdateUser['user_type'] = 'MBP';

    //   // insert update di user_mbp_mt

    //   $checkDataLookup = DB::table('lookup_fmc_cluster')
    //   ->select('*')
    //   ->where('fmc_id','=',$rtpo_fmc_id)
    //   ->first();

    //   if ($checkDataLookup==null) {
    //     $res['success'] = false;
    //     $res['message'] = 'DATA LOOKUP TIDAK DITEMUKAN';
    //   }

    //   # delete dulu data lama
    //   DB::table('user_mbp_mt')->where('mbp_mt_username','=',$username)->delete();
    //   # isi data baru
    //   $insert_user_mt_data = DB::table('user_mbp_mt')
    //   ->insert(
    //     [
    //       'mbp_mt_nik'=> $checkSuperUser->id, 
    //       'mbp_mt_cn'=> @$username,
    //       'mbp_mt_username'=> @$username,
    //       'last_update'=> @$date_now, 
    //       'status'=> 1,

    //       'fmc_id'=> @$rtpo_fmc_id,
    //       'fmc'=> @$checkDataLookup->fmc, 
    //       'cluster'=> @$checkDataLookup->cluster, 
    //       'cluster_id'=> @$checkDataLookup->cluster_id, 
    //     ]
    //   );

    //       # update baru
    //   $register = DB::table('users')
    //   ->where('id','=',$checkSuperUser->id)
    //   ->update(
    //     [
    //       'fmc_id'=> $rtpo_fmc_id,
    //       'fmc'=> @$checkDataLookup->fmc,
    //       'regional'=> $regional,
    //     ]
    //   );
    // }

    if(@$checkSuperUser->su==2){
     $dataUpdateUser['regional'] = $regional; 
    }

    $updateSuperUser = DB::table('users')
    ->where('username',$username)
    ->update($dataUpdateUser);

    if (!$updateSuperUser) {
      $res['success'] = false;
      $res['message'] = 'GAGAL EDIT';
      $res['data'] = $dataUpdateUser;
      $res['USR'] = $updateSuperUser;
    }

    
    #setelah sukses update maka panggil fungsi login
    return $this->login($username,$firebase_token,'kagfs');
    

    // $res['success'] = true;
    // $res['message'] = 'SUCCESS';
    // $res['data'] = $dataUpdateUser;
    // return response($res);
  }



    public function login($username, $firebase_token, $password){
      // $hasher = app()->make('hash');
      // $username = $request->input('username');
      // $password = $request->input('password');
      // $firebase_token = $request->input('firebase_token');
      // $otp = $request->input('password');
      $otp = $password;
      // $data = null;

      $login = DB::table('users')
      ->select('*')
      ->where('username','=',$username)
      ->first();


      if (!$login) {
        $res['success'] = false;
        $res['message'] = 'INCORRECT_USERNAME_PASSWORD';
        return response($res);
      }else{

        $tmp_access_right = '';
        $tmp_roles = explode(",",$login->roles_id);
        foreach ($tmp_roles as $value) {

          if ($value == '4') {
            $tmp_access_right = $tmp_access_right.'tktrtpo,mntrtpo,crtrtpo,splrtpo,';
                  // $data['acces_right'] += ',tktrtpo,mntrtpo,crtrtpo';
          }else if ($value == '5') {
          }else if ($value == '6') {
            $tmp_access_right = $tmp_access_right.'';
                  // $data['acces_right'] += ',crtfmc';
          }else if ($value == '7') {
            $tmp_access_right = $tmp_access_right.'mntfmc,crtfmc,';
                  // $data['acces_right'] += ',mntfmc';
          }else if ($value == '8') {
            $tmp_access_right = $tmp_access_right.'tktfmc,';
                  // $data['acces_right'] += ',tktfmc';
          }else if ($value == '11') {
            $tmp_access_right = $tmp_access_right.'crtfmc,';
                  // $data['acces_right'] += ',tktfmc';
          }else if ($value == '10') {
            $tmp_access_right = $tmp_access_right.'cpo,';
                  // $data['acces_right'] += ',tktfmc';
          }else if ($value == '2') {
            $tmp_access_right = $tmp_access_right.'nsa,';
                  // $data['acces_right'] += ',tktfmc';
          }else {
          }
        }
        $data['acces_right'] = $tmp_access_right;

        // mengambil data otp
        $otp_login_data = DB::table('user_otp_app')
        ->select('*')
        ->where('username','=',$username)
        ->first();

        // if ($hasher->check($password, $login->password)) {//---------------------------> GANTI MD5(CEK HASIL MD5, BILA SAMA MAKA 
        if (/*md5($password) == $login->password*/ true) {//---------------------------> GANTI MD5(CEK HASIL MD5, BILA SAMA MAKA LANJUT)
           if ($tmp_access_right=='cpo,') {

            $api_token = sha1(time());

            $data['user_id']=$login->id;
            $data['name']=$login->name;
            $data['username']=$login->username;
            $data['email']=$login->email;
            $data['roles']=$login->roles_id;
            $data['regional']=@$login->regional;

            $data['fmc_name']="CPO ".@$login->regional;
            $data['fmc']="CPO ".@$login->regional;
            $data['user_type']="CPO";
            $data['user_type_name']="CPO ".@$login->regional;
            $data['user_type_id']="CPO ".@$login->regional;
            $data['rtpo_id']="CPO ".@$login->regional;
            $data['rtpo_latitude']=null;
            $data['rtpo_longitude']=null;
            $data['phone']=@$login->phone;

            // jatim : -7.7212455,112.6922222
            // jateng : -7.2889125,110.1303092
            // balnus : -8.4699925,117.2378972
            if ($login->regional == 'JATIM') {
              $data['cpo_latitude']="-7.7212455";
              $data['cpo_longitude']="112.6922222";
            }
            if ($login->regional == 'JATENG-DIY') {
              $data['cpo_latitude']="-7.2889125";
              $data['cpo_longitude']="110.1303092";
            }
            if ($login->regional == 'BALI NUSRA') {
              $data['cpo_latitude']="-8.4699925";
              $data['cpo_longitude']="117.2378972";
            }

            $data['subscribe_all_rtpo_topic']=null;

            $res['success'] = true;
            $res['api_token'] = $api_token;
            $res['message'] = 'Success!';
            $res['data'] = $data;
            return response($res);
          }else if($tmp_access_right=='nsa,') {

            $api_token = sha1(time());


            $ums_data = DB::table('user_mng_nsa as ums')
            ->select('*')
            ->where('username','=',$login->username)
            ->first();

            $data['user_id']=$login->id;
            $data['name']=$login->name;
            $data['username']=$login->username;
            $data['email']=$login->email;
            $data['roles']=$login->roles_id;
            $data['regional']=@$login->regional;

            $data['fmc_name']=null;
            $data['fmc']=null;
            $data['user_type']="NSA";
            $data['user_type_name']=@$ums_data->ns;
            $data['user_type_id']=@$ums_data->ns_id;
            $data['rtpo_id']=null;
            $data['rtpo_latitude']=null;
            $data['rtpo_longitude']=null;
            $data['phone']=@$login->phone;

            // jatim : -7.7212455,112.6922222
            // jateng : -7.2889125,110.1303092
            // balnus : -8.4699925,117.2378972
            if ($login->regional == 'JATIM') {
              $data['cpo_latitude']="-7.7212455";
              $data['cpo_longitude']="112.6922222";
            }
            if ($login->regional == 'JATENG-DIY') {
              $data['cpo_latitude']="-7.2889125";
              $data['cpo_longitude']="110.1303092";
            }
            if ($login->regional == 'BALI NUSRA') {
              $data['cpo_latitude']="-8.4699925";
              $data['cpo_longitude']="117.2378972";
            }

            $data['subscribe_all_rtpo_topic']=null;

            $res['success'] = true;
            $res['api_token'] = $api_token;
            $res['message'] = 'Success!';
            $res['data'] = $data;
            return response($res);
          }

          if ($login->user_type=='RTPO') {

          // $check_rtpo = DB::table('rtpo')->select('*')->where('user_id','=',$login->id)->first();
            $check_rtpo = DB::table('rtpo')
            ->join('user_rtpo', 'rtpo.rtpo_id', '=', 'user_rtpo.rtpo_id')
            ->select('rtpo.rtpo_name','rtpo.rtpo_id','rtpo.latitude','rtpo.longitude', 'user_rtpo.fmc_id')
            ->where('user_rtpo.username','=',$login->username)
            ->first();

            if ($check_rtpo) {

              $data['user_id']=$login->id;
              $data['name']=$login->name;
              $data['username']=$login->username;
              $data['email']=$login->email;
              $data['roles']=$login->roles_id;
              $data['fmc_name']=$this->checkMyFMCtopic($login->fmc_id);
              $data['fmc']=$check_rtpo->fmc_id;
              $data['user_type']=$login->user_type;
              $data['user_type_name']=$check_rtpo->rtpo_name;
              $data['user_type_id']=$check_rtpo->rtpo_id;
              $data['rtpo_id']=$check_rtpo->rtpo_id;
              $data['rtpo_latitude']=$check_rtpo->latitude;
              $data['rtpo_longitude']=$check_rtpo->longitude;


              $data['regional']=@$login->regional;
              $data['phone']=@$login->phone;

              $data['subscribe_all_rtpo_topic']='RTPO_ALL';

              $fireBaseController = new FireBaseController;
              $myrtpo = $fireBaseController->checkMyRTPOtopic($check_rtpo->rtpo_name);
              $data['subscribe_my_rtpo_topic']=$myrtpo;

              $api_token = sha1(time());
              $create_token = DB::table('users')
              ->where('id','=',$login->id)
              ->update(
                [
                  'firebase_token' => $firebase_token,
                  'api_token' => $api_token,
                ]
              );

              if ($create_token) {
                $res['success'] = true;
                $res['api_token'] = $api_token;
                $res['message'] = 'Success!';
                $res['data'] = $data;
                return response($res);
              }
            }
            $res['success'] = false;
            $res['message'] = 'USER_DATA_NOT_FOUND';
            return response($res);

          }else{
          // $check_mbp = DB::table('mbp')->select('*')->where('user_id','=',$login->id)->first();
            $check_mbp = DB::table('mbp')
            ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
            ->select('mbp.mbp_name','mbp.mbp_id','mbp.rtpo_id')
            ->where('user_mbp.username','=',$login->username)
            ->first();

            $list_mbp = DB::table('mbp')
            ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
            ->select('mbp.mbp_name','mbp.mbp_id','mbp.rtpo_id')
            ->where('user_mbp.username','=',$login->username)
            ->get();


            $result = json_decode($list_mbp, true);
            if ($result==null) {
              $tmp_list_mbp =null;
            }else{
              $tmp_list_mbp = '';
              foreach ($result as $param => $row) {
                $tmp_list_mbp .= $row['mbp_name'];
                if(!((count($result)-1)==$param)){
                  $tmp_list_mbp .=', ';
                }
              }  
            }
            

            if ($check_mbp != null) {
              $data['user_id']=$login->id;
              $data['name']=$login->name;
              $data['username']=$login->username;
              $data['email']=$login->email;
              $data['roles']=$login->roles_id;
              $data['fmc_name']=$this->checkMyFMCtopic($login->fmc_id);
              $data['fmc']=$login->fmc_id;
              $data['user_type']=$login->user_type;
              $data['user_type_name']=$tmp_list_mbp;
              $data['user_type_id']=$check_mbp->mbp_id;
              $data['rtpo_id']=$check_mbp->rtpo_id;
              $data['rtpo_latitude']='';
              $data['rtpo_longitude']='';
              $data['subscribe_my_rtpo_topic']='';
              $data['subscribe_all_rtpo_topic']='';
              $data['subscribe_my_fmc_topic'] = $this->checkMyFMCtopic($login->fmc_id);


              $data['regional']=@$login->regional;
              $data['phone']=@$login->phone;

              $api_token = sha1(time());
              $create_token = DB::table('users')
              ->where('id','=',$login->id)
              ->update(
                [
                  'api_token' => $api_token,
                  'firebase_token' => $firebase_token,
                ]
              );
              if ($create_token) {
                $res['success'] = true;
                $res['api_token'] = $api_token;
                $res['message'] = 'Success!';
                $res['Firebase token'] = $firebase_token;
                $res['token']=$create_token;
                $res['data'] = $data;
                return response($res);
              }
            } else{
              $data['user_id']=$login->id;
              $data['name']=$login->name;
              $data['username']=$login->username;
              $data['email']=$login->email;
              $data['roles']=$login->roles_id;
              $data['fmc_name']=$this->checkMyFMCtopic($login->fmc_id);
              $data['fmc']=$login->fmc_id;
              $data['user_type']=$login->user_type;
              $data['user_type_name']=$tmp_list_mbp;
              $data['user_type_id']=null;
              $data['rtpo_id']=null;
              $data['rtpo_latitude']='';
              $data['rtpo_longitude']='';
              $data['subscribe_my_rtpo_topic']='';
              $data['subscribe_all_rtpo_topic']='';
              $data['subscribe_my_fmc_topic'] = $this->checkMyFMCtopic($login->fmc_id);

              
              $data['regional']=@$login->regional;
              $data['phone']=@$login->phone;

              $api_token = sha1(time());
              $create_token = DB::table('users')
              ->where('id','=',$login->id)
              ->update(
                [
                  'api_token' => $api_token,
                  'firebase_token' => $firebase_token,
                ]
              );
              if ($create_token) {
                $res['success'] = true;
                $res['api_token'] = $api_token;
                $res['message'] = 'Success!';
                $res['Firebase token'] = $firebase_token;
                $res['token']=$create_token;
                $res['data'] = $data;
                return response($res);
              }
            }
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'INCORRECT_USERNAME_PASSWORD';
          return response($res);
        }
      }
    }

    function checkMyFMCtopic($fmc_id){
      switch ($fmc_id) {
        case "1":
        $myfmc = 'TIN';
        break;
        case "2":
        $myfmc = 'IDE';
        break;
        case "3":
        $myfmc = 'XTE';
        break;
        case "4":
        $myfmc = 'TBA';
        break;
        case "5":
        $myfmc = 'BMG';
        break;
        case "6":
        $myfmc = 'KIS';
        break;
        case "7":
        $myfmc = 'SPM';
        break;
        default:

        $fmc_data = DB::table('fmc')
        ->select('*')
        ->where('fmc_id','=',$fmc_id)
        ->first();

        $myfmc = @$fmc_data->fmc_alias.'_'.@$fmc_data->regional;


        $myfmc = str_replace(' ', '_', $myfmc);
        break;
      }
      return($myfmc);
    }







//--------------------------------- fungsi untuk memantau 
    public function cekAdn(Request $request){  
      // $username = $request->input('username');

      $checkConfig = DB::table('config')
      ->select('*')
      ->first();

      $res['data'] = @$checkConfig;
      return response($res);

    }
    public function gantiAdn(Request $request){
      $adn_number = app('request')->input('adn_number');
      // $adn_delay = app('request')->input('adn_delay');
      // $gps_accuration = app('request')->input('gps_accuration');
      // $req_size = app('request')->input('req_size');
      // $force_logout = app('request')->input('force_logout');

      $updateConfig = DB::table('config')
      ->where('id','=','1')
      ->update(
        [
          'adn_number' => $adn_number,
        ]
      );

      $checkConfig = DB::table('config')
      ->select('*')
      ->first();

      $res['data'] = @$checkConfig;
      return response($res);
    }

        public function cekGambarMtBulanIni(Request $request){
      $key = app('request')->input('key');
      $month = @app('request')->input('month');
      
      $checkImage = DB::table('image_maintenance')
      ->where('fname', 'like', '%'.$key.'%')
      ->select('fname','date');


      if ($month!=null) {
        $checkImage = $checkImage->wheremonth('date',$month);
      }

      $checkImage = $checkImage->orderBy('date', 'DESC')
      ->limit(50)
      ->get();

      $res['key'] = $key;
      $res['data'] = $checkImage;
      return response($res);
    }



    public function cekGambarMt(Request $request){

      $key = $request->input('fname');
      $periode = $request->input('periode');
      
      $checkImage = DB::table('image_maintenance')
      ->where('fname', 'like', '%'.$key.'%')
      ->select('fname','date');


      if ($periode!=null) {
        $checkImage = $checkImage->where('date', 'like', $periode.'%');
      }

      $checkImage = $checkImage->orderBy('date', 'DESC')
      ->limit(50)
      ->get();

      $res['key'] = $key;
      $res['data'] = $checkImage;
      return response($res);
    }
    public function cekXmlMtBulanIni(Request $request){
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

      $checkXml = $checkXml->select('username','site_id','sik_no','otp','fname','status','msg_status','last_update',  'date')
      ->orderBy('date', 'DESC')
      ->limit(50)
      ->get();

      $res['username'] = @$username;
      $res['sik_no'] = @$sik_no;
      $res['site_id'] = @$site_id;
      $res['otp'] = @$otp;
      $res['fname'] = @$fname;
      $res['data'] = $checkXml;
      return response($res);      
    }

    public function cekSiteTerakhirDiboking(Request $request){
      
    }
}