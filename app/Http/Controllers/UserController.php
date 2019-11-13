<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use DB;
class UserController extends Controller
{
    /**
     * Register new user
     *
     * @param $request Request
     */


    // function checkMyFMCtopic($fmc_id){
    //   switch ($fmc_id) {
    //     case "1":
    //     $myfmc = 'TIN';
    //     break;
    //     case "2":
    //     $myfmc = 'IDE';
    //     break;
    //     case "3":
    //     $myfmc = 'XTE';
    //     break;
    //     case "4":
    //     $myfmc = 'TBA';
    //     break;
    //     case "5":
    //     $myfmc = 'BMG';
    //     break;
    //     case "6":
    //     $myfmc = 'KIS';
    //     break;
    //     case "7":
    //     $myfmc = 'SPM';
    //     break;
    //     default:
    //     $myfmc = null;
    //     break;
    //   }
    //   return($myfmc);
    // }
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

    public function register(Request $request){
      $hasher = app()->make('hash');
      $username = $request->input('username');
      $name = $request->input('name');
      $email = $request->input('email');
      $password = md5($request->input('password'));//---------------------------> GANTI MD5 OK :D
      $user_type = $request->input('user_type'); // RTPO / MBP
      $user_type_id = $request->input('user_type_id'); // id_rtpo atau id_mbp

      $nik = $request->input('nik');
      $phone = $request->input('phone');
      $status = $request->input('status'); 

      $login = DB::table('users')
      ->select('*')
      ->where('id','=',$nik)
      ->get();



      // $res['username'] = $tmp_username;
      // $res['login'] = $login;
      // return response($res);

      $login_result = json_decode($login, true);

      if ($login_result!=null) {
      //maka di update


        $tmp_username = $login[0]->username;
      // $tmp_username = $login_result[0]->username;

      // return response('bagian atas');
        $register = DB::table('users')
        ->where('id','=',$nik)
        ->update(
          [
            'id'=> $nik,
            'username'=> $username,
            'name'=> $name,
            'email'=> $email,
            'phone'=> $phone,
            'user_type'=> $user_type,
            'password'=> $password,
          ]
        );

      // return response($register);
        if (true) {

      // $login = DB::table('users')->select('*')->where('username','=',$username)->where('id','=',$nik)->first();

      // return response($tmp_username);

          if ($user_type=='RTPO') {
            $register_type = DB::table('user_rtpo')
            ->where('username','=',$tmp_username)
            ->update(
              [
                'username'=> $username,
                'rtpo_id'=> $user_type_id,
                'status'=> $status,
              ]
            );  

          }else{
            $register_type = DB::table('user_mbp')->insert(
              [
                'username'=> $login->username,
                'mbp_id'=> $user_type_id,
              ]
            );
          }

          if ($register_type) {
            $res['success'] = true;
            $res['message'] = 'REGISTRATION_SUCCEEDED';
            return response($res);
          }else{

          // DB::table('users')->where('username','=',$username)->delete();
            $res['success'] = true;
            $res['message'] = 'REGISTRATION_SUCCEEDED_WITH_NO_CHANGE_RTPO_ID';
          // $res['login->username'] = $login->username;
          // $res['user_type_id'] = $user_type_id;
          // $res['status'] = $status;
          // $res['message'] = 'REGISTRATION_FAILED';
            return response($res);  
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'REGISTRATION_FAILED 1.5';
        // $res['login->username'] = $login->username;
        // $res['user_type_id'] = $user_type_id;
        // $res['status'] = $status;
          return response($res);
        }

      }else{
      // return response('bagian bawah');
      //maka registrasi seperti biasa
        $register = DB::table('users')->insert(
          [
            'id'=> $nik,
            'username'=> $username,
            'name'=> $name,
            'email'=> $email,
            'phone'=> $phone,
            'user_type'=> $user_type,
            'password'=> $password,
          ]
        );

        if ($register) {
          $login = DB::table('users')->select('*')->where('username','=',$username)->where('password','=',$password)->first();

          if ($user_type=='RTPO') {
            $register_type = DB::table('user_rtpo')->insert(
              [
                'username'=> $login->username,
                'rtpo_id'=> $user_type_id,
                'status'=> $status,
              ]
            );  

          }else{
            $register_type = DB::table('user_mbp')->insert(
              [
                'username'=> $login->username,
                'mbp_id'=> $user_type_id,
              ]
            );
          }

          if ($register_type) {
            $res['success'] = true;
            $res['message'] = 'REGISTRATION_SUCCEEDED';
            return response($res);
          }else{
          // DB::table('users')->where('username','=',$username)->delete();
            $res['success'] = false;
            $res['message'] = 'REGISTRATION_FAILED 2';
            $res['login->username'] = $login->username;
            $res['user_type_id'] = $user_type_id;
            $res['status'] = $status;
            return response($res);  
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'REGISTRATION_FAILED';
          return response($res);
        }
      }
    }

     public function get_user(Request $request, $id){
        $user = User::where('id', $id)->get();
        if ($user) {
          $res['success'] = true;
          $res['message'] = $user;
          
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'Cannot find user!';
          
          return response($res);
        }
      }

    public function login(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      // $hasher = app()->make('hash');
      $username = $request->input('username');
      // $password = $request->input('password');
      $firebase_token = $request->input('firebase_token');
      $otp = $request->input('password');
      // $data = null;

      $logging = DB::table('log_login')
      ->insert([
        'username' => @$username,
        'otp' => @$otp,
        'firebase_token' => @$firebase_token,
        'date' => @$date_now,
      ]);

      $login = DB::table('users')
      ->select('*')
      ->where('username','=',$username)
      ->first();
      $broadcast_count=0;


      if (!$login) {
        $res['success'] = false;
        $res['message'] = 'Akun tidak ditemukan [DS:401]';
        return response($res);
      }else{

        $tmp_access_right = '';
        $tmp_roles = explode(",",$login->roles_id);
        foreach ($tmp_roles as $value) {

          if ($value == '4') {
            $tmp_access_right = $tmp_access_right.'tktrtpo,mntrtpo,crtrtpo,splrtpo,';
            $rtpo_data = DB::table('user_rtpo')
            ->select('*')
            ->where('username','=',$username)
            ->first();

            $bcRtpo = str_replace(array('.', ' ','-', "\n", "\t", "\r"), '_', $rtpo_data->rtpo_name);
            $bcRegional = str_replace(array('.', ' ','-', "\n", "\t", "\r"), '_', @$login->regional);
            
            // $ft_data = DB::table('firebase_topic')
            // ->select('*')
            // ->where('fmc_id','=',$login->fmc_id)
            // ->where('role_code','=','MBP')
            // ->first();
            $sbcBrc[$broadcast_count] = 'RTPO_'.@$bcRegional;
            $broadcast_count = $broadcast_count+1;
            $sbcBrc[$broadcast_count] = $bcRtpo;
            $broadcast_count = $broadcast_count+1;

                  // $data['acces_right'] += ',tktrtpo,mntrtpo,crtrtpo';
          }else if ($value == '5') {
          }else if ($value == '6') {
            $tmp_access_right = $tmp_access_right.'';
                  // $data['acces_right'] += ',crtfmc';
          }else if ($value == '7') {
            $tmp_access_right = $tmp_access_right.'mntfmc,crtfmc,';
            
            $ft_data = DB::table('firebase_topic')
            ->select('*')
            ->where('fmc_id','=',$login->fmc_id)
            ->where('role_code','=','TM')
            ->first();
            $sbcBrc[$broadcast_count] = @$ft_data->topic;
            $broadcast_count = $broadcast_count+1;
            // echo $sbcBrc[0].' ';
                  // $data['acces_right'] += ',mntfmc';
          }else if ($value == '8') {
            $tmp_access_right = $tmp_access_right.'tktfmc,';
            $ft_data = DB::table('firebase_topic')
            ->select('*')
            ->where('fmc_id','=',$login->fmc_id)
            ->where('role_code','=','MBP')
            ->first();
            $sbcBrc[$broadcast_count] = @$ft_data->topic;
            $broadcast_count = $broadcast_count+1;
            // echo $sbcBrc[0].' ';
                  // $data['acces_right'] += ',tktfmc';
          }else if ($value == '10') {
            $tmp_access_right = $tmp_access_right.'cpo,';
                  // $data['acces_right'] += ',tktfmc';
          }else if ($value == '11') {
            $tmp_access_right = $tmp_access_right.'crtfmc,';

            $ft_data = DB::table('firebase_topic')
            ->select('*')
            ->where('fmc_id','=',$login->fmc_id)
            ->where('role_code','=','TSRA')
            ->first();
            $sbcBrc[$broadcast_count] = @$ft_data->topic;
            $broadcast_count = $broadcast_count+1;
            // echo $sbcBrc[0].' ';
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

        

        // pengecekan OTP
        // if (condition) {
        //   # code...
        // }

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
            $data['phone']=$login->phone;

            $data['fmc_name']='-';
            $data['fmc']='-';
            $data['user_type']="CPO";
            $data['user_type_name']="CPO ".$login->regional;
            $data['user_type_id']='-';
            $data['rtpo_id']='-';
            $data['rtpo_latitude']='-';
            $data['rtpo_longitude']='-';

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

            $data['subscribe_all_rtpo_topic']='-';

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
            $data['phone']=$login->phone;

            $data['fmc_name']='-';
            $data['fmc']='-';
            $data['user_type']="NSA";
            $data['user_type_name']=@$ums_data->ns;
            $data['user_type_id']='-';
            $data['rtpo_id']='-';
            $data['rtpo_latitude']='-';
            $data['rtpo_longitude']='-';

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

            $data['subscribe_all_rtpo_topic']='-';

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
            ->select('rtpo.rtpo_name','rtpo.rtpo_id','rtpo.latitude','rtpo.longitude', 'user_rtpo.fmc_id', 'rtpo.regional')
            ->where('user_rtpo.username','=',$login->username)
            ->first();

            // $check_fmc_rtpo = DB::table('fmc_cluster')
            // ->join('user_rtpo', 'fmc_cluster.cluster_id', '=', 'user_rtpo.cluster_id')
            // ->select('rtpo.rtpo_name','rtpo.rtpo_id','rtpo.latitude','rtpo.longitude')
            // ->where('user_rtpo.username','=',$login->username)
            // ->first();


            if ($check_rtpo) {


              $data['user_id']=$login->id;
              $data['name']=$login->name;
              $data['username']=$login->username;
              $data['email']=$login->email;
              $data['roles']=$login->roles_id;
              $data['fmc_name']=$this->checkMyFMCtopic($login->fmc_id);
              $data['fmc']=($check_rtpo->fmc_id==null ) ? '-' : $check_rtpo->fmc_id;
              $data['user_type']=$login->user_type;
              $data['user_type_name']=$check_rtpo->rtpo_name;
              $data['user_type_id']=$check_rtpo->rtpo_id;
              $data['rtpo_id']=$check_rtpo->rtpo_id;
              $data['rtpo_latitude']=$check_rtpo->latitude;
              $data['rtpo_longitude']=$check_rtpo->longitude;

              $data['regional']=@$check_rtpo->regional;
              $data['phone']=$login->phone;

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
                
                $data['broadcast']=@$sbcBrc;
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
              // $res['success'] = false;
              // $res['message'] = 'USER_DATA_NOT_FOUND';
              // return response($res);
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

             $check_regional_fmc = DB::table('fmc')
            ->select('*')
            ->where('fmc_id','=',$login->fmc_id)
            ->first();            

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

              $data['regional']=@$check_regional_fmc->regional;
              $data['phone']=$login->phone;

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
                $data['broadcast']=@$sbcBrc;
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
              $data['phone']=$login->phone;

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

                
                $data['broadcast']=@$sbcBrc;
                $res['success'] = true;
                $res['api_token'] = $api_token;
                $res['message'] = 'Success!';
                $res['Firebase token'] = $firebase_token;
                $res['token']=$create_token;
                $res['data'] = $data;
                return response($res);
              }
            }
            // kembalikan sebagai user yang data rtponya tidak ditemukan, hubungi cs
            // $res['success'] = false;
            // $res['message'] = 'USER_DATA_NOT_FOUND';
            // return response($res);
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'INCORRECT_USERNAME_PASSWORD';
          // $res['password'] = $password;
          // $res['password_input'] = md5($login->password);
          // $password == md5($login->password
          return response($res);
        }
      }
    }

    public function sendOTP(Request $request){

      $username = $request->input('username');

        date_default_timezone_set("Asia/Jakarta");
        $date_now=time();

      $login = DB::table('users')
      ->select('*')
      ->where('username','=',$username)
      ->first();


      if (!$login) {
        $res['success'] = false;
        $res['message'] = 'INCORRECT_USERNAME_PASSWORD';
        return response($res);
      }else{

        switch ($login->user_type) {
          case "RTPO":
          $username;
          $date_now;
          $otp = '';// ngirim username dan date
          //
          // http://192.168.43.184/ng-semeru/auth/generate_otp/gatutpur/1507622459
          // {
          //   "success": true,
          //   "msg": "ylkTtiN"
          // }

          break;
          case "MBP":
          $username;
          $date_now;
          $otp = '';// ngirim username dan date
          break;
          default:
        }
      }
    }



    public function loginApp(Request $request){

      $username = $request->input('username');
      $firebase_token = $request->input('firebase_token');
      $otp = $request->input('password');  

      $dump_log = json_encode($request->input());
      $logging = DB::table('log_login')->insert(['dump_log' => @$dump_log,]);

      $user_data = DB::table('users')
      ->select('*')
      ->where('username','=',$username)
      ->first();

      if ($user_data == null) {
        $res['success'] = false;
        $res['message'] = 'FAILED_USER_DATA_NOT_FOUND';
        return response($res);    
      }
      $arr_roles = explode (",",$user_data->roles_id);

      $tmp_roles = null;
      foreach ($arr_roles as $value) {
        $role_data = DB::table('roles')
        ->select('*')
        ->where('role_id','=',$value)
        ->first();

        if ($role_data==null) {
          $res['success'] = false;
          $res['message'] = 'FAILED_ROLE_NOT_FOUND';
          return response($res);  
        }

        if ($tmp_roles == null) {
          $tmp_roles = array($role_data->role_name);
        }else{
          array_push($tmp_roles,$role_data->role_name);  
        }
        

      }

      $data['user_id'] = $user_data->id;
      $data['name'] = $user_data->name;
      $data['username'] = $user_data->username;
      $data['email'] = $user_data->email;
      $data['roles'] = $tmp_roles;
      $data['mbp_name'] = 'MBP_IDE_003';
      $data['mbp_id'] = 'IDE09003';
      $data['rtpo_name'] = '';
      $data['rtpo_id'] = '11';
      $data['rtpo_latitude'] = '-7.7722255';
      $data['rtpo_longitude'] = '113.1664559';
      $data['subscribe_my_rtpo_topic'] = '';
      $data['subscribe_my_fmc_topic'] = '';
      $data['subscribe_all_rtpo_topic'] = '';

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);    
    }


    public function users_update(Request $request)
    {
      // date_default_timezone_set("Asia/Jakarta"); tidak perlu set timezone lagi, timezone sudah diset di environtment
      $date_now = date('Y-m-d H:i:s');
      $debug=[];
      $post_data = $request->input('data');
      $res = [];

      foreach ($post_data as $param => $row) {

        $user_data = DB::table('users')->where('id','=',$row['nik'])->first();

        $update_data_user = [
          'id'=> $row['nik'],
          'username'=> $row['username'],
          'name'=> $row['name'],
          'email'=> $row['email'],
          'phone'=> $row['phone'],
          'roles_id'=> @$row['roles'],
          'su'=> @$row['su'],
          //'regional'=> @$row['regional'],
          'su_regional'=> @$row['su_regional'],
          'chat_id'=> @$row['chat_id_telegram'],
        ];

        if(isset($row['user_type'])){

          $update_data_user['user_type'] = $row['user_type'];
          if( $row['user_type'] == 'MBP_SP'){
            $update_data_user['user_type'] = 'MBP';
          }

        }

        if ($user_data == null) {
          # buat baru
          DB::table('users')->insert( $update_data_user );

        }else{
          # update baru
          # update informasi general user jika mau menambahkan data yg spesifik untuk role tertentu jgn ditambahkan disini
          // $debug=[
          //   'desc'=>'update data',
          //   'data'=>$row,
          // ];
          // return response($debug);
          DB::table('users')->where('id','=',$row['nik'])->update( $update_data_user );
        }

        if(array_key_exists('user_type', $row) && !is_null(@$row['user_type'])){

          switch ($row['user_type']) {

            case 'STAFF_NOS':
              DB::table('staff_nos')->where('staff_nos_username','=',$row['username'])->delete();
              DB::table('staff_nos')->insert(
                [
                  'staff_nos_nik'=> $row['nik'],
                  'staff_nos_username'=> $row['username'],
                  'staff_nos_cn'=> $row['username'],
                  'last_update'=> $date_now,
                  'status'=> $row['status'],
                ]
              );
              break;

            case 'MNG_NOS':
              break;

            case 'MNG_NSA':
              DB::table('user_mng_nsa')->where('username','=',$row['username'])->delete();
              DB::table('user_mng_nsa')->insert(
                [
                  'username'=> $row['username'],
                  'regional'=> @$row['regional'],
                  'ns'=> $row['ns_name'],
                  'ns_id'=> $row['ns_id'],
                  'last_update'=> $date_now,
                ]
              );
              break;

            case 'CPO':
              DB::table('user_cpo')->where('username','=',$row['username'])->delete();
              DB::table('user_cpo') ->insert(
                [
                  'username'=> @$row['username'],
                  'user_nik'=> @$row['nik'],
                  'user_cn'=> @$row['username'],
                  'regional'=> @$row['regional'],
                  'last_update'=> @$date_now,
                  'status'=> @$row['status'],
                ]
              );
              break;

            case 'RTPO':
              DB::table('user_rtpo')->where('username','=',$row['username'])->delete();
              DB::table('user_rtpo')->insert(
                [
                  'username'=> @$row['username'],
                  'cluster_name'=> @$row['cluster_name'],
                  'cluster_id'=> @$row['cluster_id'],
                  'rtpo_id'=> @$row['rtpo_id'],
                  'rtpo_name'=> @$row['rtpo_name'],
                  'last_update'=> @$date_now,
                  'status'=> @$row['status'],
                ]
              );
              break;

            case 'MBP_MT':
              DB::table('user_mbp_mt')->where('mbp_mt_username','=',$row['username'])->delete();
              DB::table('user_mbp_mt')->insert(
                [
                  'mbp_mt_nik'=> $row['nik'],
                  'mbp_mt_cn'=> @$row['username'],
                  'mbp_mt_username'=> @$row['username'],
                  'last_update'=> @$date_now,
                  'status'=> @$row['status'],
                  'fmc_id'=> @$row['fmc_id'],
                  'fmc'=> @$row['fmc_name'],
                  'regional'=> @$row['regional'],
                  'cluster'=> @$row['cluster_name'],
                  'cluster_id'=> @$row['cluster_id'],
                ]
              );
              DB::table('users')->where('id','=',$row['nik'])->update(
                [
                  'fmc_id'=> @$row['fmc_id'],
                  'fmc'=> @$row['fmc_name'],
                  'regional'=> @$row['regional'],
                  'cluster'=> @$row['cluster_name'],
                  'cluster_id'=> @$row['cluster_id'],
                ]
              );
              break;

            case 'TSRA':
              DB::table('user_tsra')->where('tsra_username','=',$row['username'])->delete();
              DB::table('user_tsra')->insert(
                [
                  'tsra_nik'=> $row['nik'],
                  'tsra_cn'=> @$row['username'],
                  'tsra_username'=> @$row['username'],
                  'last_update'=> @$date_now,
                  'status'=> @$row['status'],
                  'fmc_id'=> @$row['fmc_id'],
                  'fmc'=> @$row['fmc_name'],
                  'regional'=> @$row['regional'],
                  'cluster'=> @$row['cluster_name'],
                  'cluster_id'=> @$row['cluster_id'],
                ]
              );
              DB::table('users')->where('id','=',$row['nik'])->update(
                [
                  'fmc_id'=> @$row['fmc_id'],
                  'fmc'=> @$row['fmc_name'],
                  'regional'=> @$row['regional'],
                  'cluster'=> @$row['cluster_name'],
                  'cluster_id'=> @$row['cluster_id'],
                ]
              );
              break;

            case 'MBP_SP':
              
              # delete dulu data lama
              $check_user_mbp = DB::table('user_mbp as um')
                ->join('mbp', 'um.mbp_id', 'mbp.mbp_id')
                ->select('mbp.status')
                ->where('um.mbp_mt_cn','=',@$row['username'])
                ->where('mbp.status','!=','AVAILABLE')
                ->first();

              if (!$check_user_mbp) {
                DB::table('user_mbp')->where('username','=',$row['username'])->delete();
              }else{
                $res['success'] = false;
                $res['message'] = "UPDATE DATA FAILED";
                $res['reason'] = "User dalam penugasan";
                return response($res); 
              }

              
              if(isset($row['array_mbp_id'])){
                foreach ($row['array_mbp_id'] as $param => $mbp_row) {
                  
                  // $row['']id[$row['']param]  = $row['']row['id'].''; 
                  //cek mbp id yg mau dipasangkan
                  $check_user_mbp = DB::table('mbp')
                    ->select('mbp.status')
                    ->where('mbp.mbp_id','=',@$mbp_row['id'])
                    ->where('mbp.status','=','AVAILABLE')
                    ->first();

                  if ($check_user_mbp) {              
                    DB::table('user_mbp')->where('mbp_id','=',$mbp_row['id'].'')->delete();
                    $insert_user_mbp_data = DB::table('user_mbp')->insert(
                      [
                        'mbp_mt_nik'=> $row['nik'],
                        'mbp_mt_cn'=> $row['username'], //$row['name']
                        'username'=> $row['username'],
                        'last_update'=> $date_now,
                        'mbp_id'=> $mbp_row['id'].'',

                        'fmc_id'=> $row['fmc_id'],
                        'fmc'=> $row['fmc_name'],
                        'cluster'=> @$row['cluster'],
                        'cluster_id'=> @$row['cluster_id'],
                        'status_user_mbp'=> @$row['status'],
                      ]
                    );
                  }
                }
              }

              $update_usr_data = [
                'fmc_id'=> @$row['fmc_id'],
                'fmc'=> @$row['fmc_name'],
                'regional'=> @$row['regional'],
                'cluster'=> @$row['cluster_name'],
                'cluster_id'=> @$row['cluster_id'],
              ];
              
              DB::table('users')->where('id','=',$row['nik'])->update(
                $update_usr_data
              );
            
            break;

            case 'ADM_FMC':

              DB::table('user_admin_fmc')->where('username',$row['username'])->delete();
              $insert_data=[
                'nik' => @$row['nik'],
                'username' => @$row['username'],
                'chat_id' => @$row['chat_id_telegram'],
                'telegram_id' => @$row['telegram_id'],
                'fmc_id' => @$row['fmc_id'],
                'fmc' => @$row['fmc_name'],
                'cluster_id' => @$row['cluster_id'],
                'cluster' => @$row['cluster_name'],
                'rtpo_id' => @$row['rtpo_id'],
                'rtpo' => @$row['rtpo_name'],
                'regional' => @$row['regional'],
                'create_at' => $date_now,
                'last_update' => $date_now,
                'status' => 1,
              ];
              DB::table('user_admin_fmc')->insert($insert_data);
              $update_data=[
                  'fmc_id'=> @$row['fmc_id'],
                  'fmc'=> @$row['fmc_name'],
                  'regional'=> @$row['regional'],
                  'cluster'=> @$row['cluster_name'],
                  'cluster_id'=> @$row['cluster_id'],
                ];
              DB::table('users')->where('username',$row['nik'])->update(
                $update_data
              );
            //   print_r($update_data);
            // exit;
              break;

            default: break;
          }


        }
        // exit('work');
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return response($res);

        if (!array_key_exists('user_type', $row) || is_null(@$row['user_type'])) {
          
          // $res['success'] = true;
          // $res['message'] = 'SUCCESS';
          // $res['detil_message'] = 'SUCCESS_UPDATE_USER';
          // return response($res);    

        } else if ($row['user_type'] == "CPO") {

            # delete dulu data lama
            DB::table('user_cpo')->where('username','=',$row['username'])->delete();
            # isi data baru
            $insert_user_cpo_data = DB::table('user_cpo')
            ->insert(
              [
                'username'=> @$row['username'],
                'user_nik'=> @$row['nik'],
                'user_cn'=> @$row['username'],
                'regional'=> @$row['regional'],
                'last_update'=> @$date_now,
                'status'=> @$row['status'],
              ]
            );
          
          if ($insert_user_cpo_data) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_RTPO_DATA';
            // return response($res);    
          }

        }  else if ($row['user_type'] == "RTPO") {

          # ubah user_type menjadi RTPO
          $update_type = DB::table('users')
          ->where('id','=',$row['nik'])
          ->update(
            [
              'user_type'=> "RTPO",
              'fmc_id'=> null,
              'fmc'=> null,
            ]
            );

            # delete dulu data lama
            DB::table('user_rtpo')->where('username','=',$row['username'])->delete();
            # isi data baru
            $insert_user_rtpo_data = DB::table('user_rtpo')
            ->insert(
              [
                'username'=> @$row['username'],
                'cluster_name'=> @$row['cluster_name'],
                'cluster_id'=> @$row['cluster_id'],
                'rtpo_id'=> @$row['rtpo_id'],
                'rtpo_name'=> @$row['rtpo_name'],
                'last_update'=> @$date_now,
                'status'=> @$row['status'],
              ]
            );
          
          if ($insert_user_rtpo_data) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_RTPO_DATA';
            // return response($res);    
          }

        } else if ($row['user_type'] == "MBP_MT") {

          # delete dulu data lama
          DB::table('user_mbp_mt')->where('mbp_mt_username','=',$row['username'])->delete();
          # isi data baru
          $insert_user_mt_data = DB::table('user_mbp_mt')
          ->insert(
            [
              'mbp_mt_nik'=> $row['nik'],
              'mbp_mt_cn'=> @$row['username'],
              'mbp_mt_username'=> @$row['username'],
              'last_update'=> @$date_now,
              'status'=> @$row['status'],
              'fmc_id'=> @$row['fmc_id'],
              'fmc'=> @$row['fmc_name'],
              'regional'=> @$row['regional'],
              'cluster'=> @$row['cluster_name'],
              'cluster_id'=> @$row['cluster_id'],
            ]
          );

          # update baru
          $register = DB::table('users')
          ->where('id','=',$row['nik'])
          ->update(
            [
              'fmc_id'=> $row['fmc_id'],
              'fmc'=> $row['fmc_name'],
            ]
          );
          
          if ($insert_user_mt_data) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_MT_DATA';
            // return response($res);    
          }

        } else if ($row['user_type'] == "TSRA") {

          # delete dulu data lama
          DB::table('user_tsra')->where('tsra_username','=',$row['username'])->delete();
          # isi data baru
          $insert_user_mt_data = DB::table('user_tsra')
          ->insert(
            [
              'tsra_nik'=> $row['nik'],
              'tsra_cn'=> @$row['username'],
              'tsra_username'=> @$row['username'],
              'last_update'=> @$date_now,
              'status'=> @$row['status'],
              'fmc_id'=> @$row['fmc_id'],
              'fmc'=> @$row['fmc_name'],
              'regional'=> @$row['regional'],
              'cluster'=> @$row['cluster_name'],
              'cluster_id'=> @$row['cluster_id'],
            ]
          );

          # update baru
          $register = DB::table('users')
          ->where('id','=',$row['nik'])
          ->update(
            [
              'fmc_id'=> $row['fmc_id'],
              'fmc'=> $row['fmc_name'],
            ]
          );
          
          if ($insert_user_mt_data) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_MT_DATA';
            // return response($res);    
          }

        } else if ($row['user_type'] == "MBP_SP") {

          # ubah user_type menjadi MBP
          $update_type = DB::table('users')
          ->where('id','=',$row['nik'])
          ->update(
            [
              'user_type'=> "MBP",
              'fmc_id'=> $row['fmc_id'],
              'fmc'=> $row['fmc_name'],
            ]
          );


          # delete dulu data lama
          $check_user_mbp = DB::table('user_mbp as um')
          ->join('mbp', 'um.mbp_id', 'mbp.mbp_id')
          ->select('mbp.status')
          ->where('um.mbp_mt_cn','=',@$row['username'])
          ->where('mbp.status','!=','AVAILABLE')
          ->first();
          if (!$check_user_mbp) {
            
            DB::table('user_mbp')->where('username','=',$row['username'])->delete();
          }else{

            $res['success'] = false;
            $res['message'] = "UPDATE DATA FAILED";
            return response($res); 
          }

          foreach ($row['array_mbp_id'] as $param => $mbp_row) {
                // $row['']id[$row['']param]  = $row['']row['id'].''; 


          # delete dulu data lama
            $check_user_mbp = DB::table('mbp')
            ->select('mbp.status')
            ->where('mbp.mbp_id','=',@$mbp_row['id'])
            ->where('mbp.status','=','AVAILABLE')
            ->first();
            if ($check_user_mbp) {              
              DB::table('user_mbp')->where('mbp_id','=',$mbp_row['id'].'')->delete();
              $insert_user_mbp_data = DB::table('user_mbp')
              ->insert(
                [
                  'mbp_mt_nik'=> $row['nik'],
                  'mbp_mt_cn'=> $row['username'],
                  'username'=> $row['username'],
                  'last_update'=> $date_now,
                  'mbp_id'=> $mbp_row['id'].'',

                  'fmc_id'=> $row['fmc_id'],
                  'fmc'=> $row['fmc_name'],
                  'cluster'=> @$row['cluster'],
                  'cluster_id'=> @$row['cluster_id'],
                  'status_user_mbp'=> @$row['status'],

                ]
              );
            }else{
            }

            // DB::table('user_mbp')->where('mbp_id','=',$mbp_row['id'].'')->delete();
            # isi data baru
            
          }

          // if ($insert_user_mbp_data) {
          //   // $res['success'] = true;
          //   // $res['message'] = 'SUCCESS';
          //   // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_SP_DATA';
          //   // return response($res);    
          // }
          
        } else if ($row['user_type'] == "STAFF_NOS") {
          
          # delete dulu data lama
          DB::table('staff_nos')->where('staff_nos_username','=',$row['username'])->delete();
          # isi data baru
          $insert_user_staff_nos_data = DB::table('staff_nos')
          ->insert(
            [
              'staff_nos_nik'=> $row['nik'],
              'staff_nos_username'=> $row['username'],
              'staff_nos_cn'=> $row['username'],
              'last_update'=> $date_now,
              'status'=> $row['status'],
            ]
          );
          
          if ($insert_user_staff_nos_data) {
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_STAFF_NOS_DATA';
            // return response($res);    
          }
          
        } else if ($row['user_type'] == "MNG_NOS") {
          # code...
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_MNG_NOS_NULL';
            // return response($res);    
        } else if ($row['user_type'] == "MNG_NSA") {
          # delete dulu data lama
          DB::table('user_mng_nsa')->where('username','=',$row['username'])->delete();
          # isi data baru
          $insert_user_staff_nos_data = DB::table('user_mng_nsa')
          ->insert(
            [
              // 'staff_nos_nik'=> $row['nik'],
              'username'=> $row['username'],
              'regional'=> @$row['regional'],
              'ns'=> $row['ns_name'],
              'ns_id'=> $row['ns_id'],
              'last_update'=> $date_now,
            ]
          );

          # code...
            // $res['success'] = true;
            // $res['message'] = 'SUCCESS';
            // $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_MNG_NSA_NULL';
            // return response($res);    
        } else if ($row['user_type'] == "ADM_FMC") {
          # code...

          $cek_user = DB::table('user_admin_fmc')
          ->select('*')
          ->where('username',$row['username'])
          ->first();

          if ($cek_user!=null) {
            $update_admin_fmc = DB::table('user_admin_fmc')
            ->where('username',$row['username'])
            ->update([
              'nik' => @$row['nik'],
              'username' => @$row['username'],
              'chat_id' => @$row['chat_id'],
              'telegram_id' => @$row['telegram_id'],
              'fmc_id' => @$row['fmc_id'],
              'fmc' => @$row['fmc_name'],
              'cluster_id' => @$row['cluster_id'],
              'cluster' => @$row['cluster_name'],
              'rtpo_id' => @$row['rtpo_id'],
              'rtpo' => @$row['rtpo_name'],
              'regional' => @$row['regional'],
              'last_update' => $date_now,
              'status' => 1,
            ]);
          } else{

            $insert_admin_fmc = DB::table('user_admin_fmc')
            ->insert([
              'nik' => @$row['nik'],
              'username' => @$row['username'],
              'chat_id' => @$row['chat_id'],
              'telegram_id' => @$row['telegram_id'],
              'fmc_id' => @$row['fmc_id'],
              'fmc' => @$row['fmc_name'],
              'cluster_id' => @$row['cluster_id'],
              'cluster' => @$row['cluster_name'],
              'rtpo_id' => @$row['rtpo_id'],
              'rtpo' => @$row['rtpo_name'],
              'regional' => @$row['regional'],
              'created_at' => $date_now,
              'last_update' => $date_now,
              'status' => 1,
            ]);
          }

          # update baru
          $register = DB::table('users')
          ->where('id','=',$row['nik'])
          ->update(
            [
              'fmc_id'=> $row['fmc_id'],
              'fmc'=> $row['fmc'],
            ]
          );
          
            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['detil_message'] = 'SUCCESS_UPDATE_USER_AND_ADM_FMC_NULL';
            return response($res);    
        }
      }

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      return response($res); 
    }

    public function loginUserArea(Request $request)
    {
      $username = $request->input('username');
      $pin = $request->input('pin');
      $firebase_token = @$request->input('firebase_token');

      //validasi user dan pin
      $user_data = DB::table('users')
      ->select('*')
      ->where('username',$username)
      ->where('password',$pin)
      ->first();

      if ($user_data) {
        $update_token = DB::table('users')
        ->where('username',$username)
        ->update([
          'firebase_token' => @$firebase_token,
          'api_token' => sha1(time()),
        ]);

        $data['user_id'] = $user_data->id;
        $data['username'] = $user_data->username;
        $data['name'] = $user_data->name;
        $data['phone'] = $user_data->phone;
        $data['user_type'] = $user_data->user_type;
        $data['api_token'] = $user_data->api_token;

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data;
      } else{
        $res['success'] = false;
        $res['message'] = 'Username atau PIN salah!';
      }

      return response($res);
    }

    public function cekUserType(Request $request)
    {
      $username = $request->input('username');

      //cek username
      $user_data = DB::table('users')
      ->select('*')
      ->where('username',$username)
      ->first();

      $data['user_id'] = $user_data->id;
      $data['username'] = $user_data->username;
      $data['user_type'] = $user_data->user_type;

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;

      return response($res);
    }

    public function changePIN(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      $username = $request->input('username');
      $pin_lama = $request->input('pin_lama');
      $pin_baru = $request->input('pin_baru');

      //get user data
      $user_data = DB::table('users')
      ->select('*')
      ->where('username',$username)
      ->first();

      //cek apakah pin lama benar
      if ($pin_lama==@$user_data->password) {
        $change_PIN = DB::table('users')
        ->where('username',$username)
        ->update([
          'password' => $pin_baru,
          'updated_at' => $date_now,
        ]);

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
      } else{
        $res['success'] = false;
        $res['message'] = 'PIN Lama Salah!';
      }

      return $res;
    }

    public function resetPIN(Request $request){
      date_default_timezone_set("Asia/Jakarta");
      $date_now = date('Y-m-d H:i:s');

      $email = $request->input('email');

      //cek apakah email yang diinput ada
      $user_data = DB::table('users')
      ->select('*')
      ->where('email',$email)
      ->first();

      //
      $res['success'] = false;
      $res['message'] = 'Fitur ini belum tersedia';

      return $res;
    }

    public function cekUserPIN(Request $request)
    {
      $username = $request->input('username');
      $password = $request->input('pin');

      //cek username
      $user_data = DB::table('users')
      ->select('*')
      ->where('username',$username)
      ->where('password',$password)
      ->first();

      if ($user_data) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
      } else{
        
        $res['success'] = false;
        $res['message'] = 'PIN Anda Tidak Sesuai!';
      }

      return response($res);
    }

  }