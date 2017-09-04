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
    public function register(Request $request)
    {
      $hasher = app()->make('hash');
      $username = $request->input('username');
      $name = $request->input('name');
      $email = $request->input('email');
      $password = $hasher->make($request->input('password'));
      $user_type = $request->input('user_type'); // RTPO / MBP
      $user_type_id = $request->input('user_type_id'); // id_rtpo atau id_mbp

      $register = DB::table('users')->insert(
        [
          'username'=> $username,
          'name'=> $name,
          'email'=> $email,
          'user_type'=> $user_type,
          'password'=> $password,
        ]
      );

      if ($register) {

        $login = DB::table('users')->select('*')->where('username','=',$username)->where('password','=',$password)->first();

        if ($user_type=='RTPO') {
          $register_type = DB::table('user_rtpo')->insert(
            [
              'user_id'=> $login->id,
              'rtpo_id'=> $user_type_id,
            ]
          );  

        }else{
          $register_type = DB::table('user_mbp')->insert(
            [
              'user_id'=> $login->id,
              'mbp_id'=> $user_type_id,
            ]
          );

        }

        if ($register_type) {
         $res['success'] = true;
         $res['message'] = 'REGISTRATION_SUCCEEDED';
         return response($res);
       }else{

        DB::table('users')->where('username','=',$username)->delete();
        $res['success'] = false;
        $res['message'] = 'REGISTRATION_FAILED';
        return response($res);  
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'REGISTRATION_FAILED';
      return response($res);
    }

  }

     /**
     * Get user by id
     *
     * URL /user/{id}
     */
     public function get_user(Request $request, $id)
     {
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

    public function login(Request $request)
    {
      $hasher = app()->make('hash');
      $username = $request->input('username');
      $password = $request->input('password');

      $login = DB::table('users')->select('*')->where('username','=',$username)->first();


      if (!$login) {
        $res['success'] = false;
        $res['message'] = 'INCORRECT_USERNAME_PASSWORD';
        return response($res);
      }else{

        if ($hasher->check($password, $login->password)) {

          if ($login->user_type=='RTPO') {

          // $check_rtpo = DB::table('rtpo')->select('*')->where('user_id','=',$login->id)->first();
            $check_rtpo = DB::table('rtpo')
            ->join('user_rtpo', 'rtpo.rtpo_id', '=', 'user_rtpo.rtpo_id')
            ->select('rtpo.rtpo_name')
            ->where('user_rtpo.user_id','=',$login->id)
            ->first();


            if ($check_rtpo) {
              $data['user_id']=$login->id;
              $data['name']=$login->name;
              $data['username']=$login->username;
              $data['email']=$login->email;
              $data['user_type']=$login->user_type;
              $data['user_type_name']=$check_rtpo->rtpo_name;

              $api_token = sha1(time());
              $create_token = DB::table('users')->where('id','=',$login->id)->update(['api_token' => $api_token]);
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
            ->select('mbp.mbp_name')
            ->where('user_mbp.user_id','=',$login->id)
            ->first();

            if ($check_mbp) {
              $data['user_id']=$login->id;
              $data['name']=$login->name;
              $data['username']=$login->username;
              $data['email']=$login->email;
              $data['user_type']=$login->user_type;
              $data['user_type_name']=$check_mbp->mbp_name;

              $api_token = sha1(time());
              $create_token = DB::table('users')->where('id','=',$login->id)->update(['api_token' => $api_token]);
              if ($create_token) {
                $res['success'] = true;
                $res['api_token'] = $api_token;
                $res['message'] = 'Success!';
                $res['data'] = $data;
                return response($res);
              }
            }
          // kembalikan sebagai user yang data rtponya tidak ditemukan, hubungi cs
            $res['success'] = false;
            $res['message'] = 'USER_DATA_NOT_FOUND';
            return response($res);
          }
        }else{
          $res['success'] = false;
          $res['message'] = 'INCORRECT_USERNAME_PASSWORD';
          return response($res);
        }
      }
    }
  }