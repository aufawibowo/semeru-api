<?php
namespace App\Http\Controllers\Api;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use DB;
// use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;
use App\User;
use App\Libraries\SendNotifLib;
use App\Helpers\AppHelper;
//nyoba git issue
class AuthControllerNew extends Controller {

	public function login(Request $request)
	{
		$username = $request->input('username');
      	$otp = $request->input('otp');
      	$firebase_token = @$request->input('firebase_token');

	    $user_data = User::where(['username'=>$username,'otp'=>$otp])->first();

        $result=[
        	'success'=>'Failed',
        	'message'=>'Username atau OTP Anda tidak sesuai'
        ];
		
	    if($user_data){
	    	$api_token = sha1(time());
	    	User::where(['username'=>$username])->update([
	    		'firebase_token' => @$firebase_token,
          		'api_token' => $api_token,
          		'otp' => '',
      		]);

			if($user_data->user_type == 'RTPO'){
				$query = DB::table('users as users')
				->join('user_rtpo as user_rtpo', 'user_rtpo.username', '=', 'users.username')
				->select('user_rtpo.rtpo_id as user_type_id')
				->where('users.username','=',$username)
				->get();
				$query_results = json_decode($query, "OK");
			}
			else if($user_data->user_type == 'MBP'){
				$query = DB::table('users as users')
				->join('user_mbp', 'user_mbp.username', '=', 'users.username')
				->select('user_mbp.id as user_type_id')
				->where('users.username','=',$username)
				->get();
				$query_results = json_decode($query, "OK");
			}
			else if($user_data->user_type == 'CPO'){
				$query = DB::table('users')
				->join('user_cpo', 'user_cpo.username', '=', 'users.username')
				->select('user_cpo.regional as user_type_id')
				->where('users.username','=',$username)
				->get();
				$query_results = json_decode($query, "OK");
			}
			else if($user_data->user_type == 'TSRA'){
				$query = DB::table('users as users')
				->join('user_tsra', 'user_tsra.username', '=', 'users.username')
				->select('user_tsra.id as user_type_id')
				->where('users.username','=',$username)
				->get();
				$query_results = json_decode($query, "OK");
			}
			else{										//hardcode
				$query = DB::table('users')
				->select('users.user_type as user_type_id')
				->where('users.username','=',$username)
                ->get();
                $query_results = json_decode($query, "OK");
			}
			//ini harusnya masuk
			foreach($query_results as $p){
				$user_type_id = $p['user_type_id'];
			}//test
			
	    	$data = [
	    		// str_replace(search, replace, subject)
      			'user_id' => $user_data->id,
	        	'username' => $user_data->username,
	        	'name'  => ucwords(str_replace('_', ' ', $user_data->name)),
	        	'phone' => $user_data->phone,
				'user_type' => $user_data->user_type,
				'user_type_id' => $user_type_id,
				'api_token' => $api_token
			];

	        $result = [
	        	'success'=>'OK',
	        	'message'=>'Login sukses',
	        	'data'=>$data,
	        ];
	    }

    	return response($result);
	}

	public function get_otp(Request $request)
	{
		$username = $request->input('username');
		$user = User::where(['username'=>$username])->first();

		$response = [
			'success' => 'Username_Not_Found',
			'message' => 'Username tidak terdaftar. Untuk mendaftarkan username baru silahkan hubungi admin NG Semeru',
		];

		if($user){

			if(empty($user->chat_id)){
				$response['success'] = 'Chat_Id_Empty';
				$response['message'] = 'Akun Anda belum memiliki chat id telegram. Silahkan melengkapi chat id telegram melalui web NG Semeru';
				return response($response);
			}
			$otp = rand(1000,9999);
			$sendNotifLib = new SendNotifLib();
			$sendCode = $sendNotifLib->send_telegram_message($user->chat_id, "<b>OTP Login:</b> <a href=\"tel:".$otp."\">".$otp."</a>. Jangan berikan OTP ini ke orang lain! \nGunakan OTP ini untuk login ke aplikasi NG Semeru.\n\nJika Anda tidak meminta OTP mengunakan perangkat Anda, abaikan pesan ini.\n-NG Semeru Team-");

			if($sendCode==200){
				User::where(['username'=>$username])->update(['otp'=>$otp]);
				$response['success'] = 'OK';
				$response['message'] = 'OTP Anda akan kami kirim ke telegram';
			}else{
				$response['success'] = 'Failed';
				$response['message'] = 'Server kami tidak dapat mengirimkan OTP saat ini. Jika masalah tetap berlanjut hubungi pihak developer';
			}
			
		}

		return response($response);
	}


	public function get_user_info(Request $request)
	{
		$username = $request->input('username');
		$res=['username'=>$username];
		return response($res);
	}

}