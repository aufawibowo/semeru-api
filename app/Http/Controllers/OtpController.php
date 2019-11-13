<?php
namespace App\Http\Controllers;

// use Freshdesk;
use Illuminate\Http\Request;
use App\Bts;
use DB;

class OtpController extends Controller 
{

	public function setOtpMaintenance(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$username = $request->input('username');
		$otp = $request->input('otp');
		$sik_no = $request->input('sik_no');
		$site_id = $request->input('site_id');


		$data_otp = DB::table('user_otp_maintenance')
		->select('*')
		->where('username','=',$username)
		->first();

		if ($data_otp!=null) {
			$updateOtpMaintenance = DB::table('user_otp_maintenance')
			->where('username','=',$username)
			->update(
				[
					'date_create' => $date_now, 
					'otp' => $otp,
				]
			);
			if ($updateOtpMaintenance) {
				
				$res['success'] = true;
				$res['message'] = 'SUCCESS';
				return response($res);
			}else{
				$res['success'] = false;
				$res['message'] = 'OTP_HAS_BEN_CREATED';
				return response($res);
			}
		}

		$insertOtpMaintenance = DB::table('user_otp_maintenance')->insert(
			[
				'date_create' => $date_now, 
				'otp' => $otp,
				'username' => $username,
				'site_id' => $site_id,
				'sik_no' => $sik_no,
			]
		);

		if ($insertOtpMaintenance) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}
	}


	// utnuk pengecekan
	// lakukan pengecekan otp, bila otp ada maka lanjut 
	// bila masa berlaku !otp>date_now lanjutkan berikan dia akses otp tersebut.
	public function checkOtpMaintenance(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		
		$username = $request->input('username');
		$otp = $request->input('otp');

		$data_otp = DB::table('user_otp_maintenance')
		->select('*')
		->where('otp','=',$otp)
		->first();

		if ($data_otp==null) {
			$res['success'] = false;
			$res['message'] = 'OTP_NOT_FOUND';
			return response($res);
		}

		$str_date_now = strtotime($date_now);
		$str_expired_date = strtotime($data_otp->expired_date);
		
		if ($data_otp->username!=$username) {
			$res['success'] = false;
			$res['message'] = 'USERNAME_NOT_MATCH';
			return response($res);
		}

		if ($str_expired_date>$str_date_now) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'OTP_EXPIRED';
			return response($res);
		}

	}

	public function setOtpTicketing(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$username = $request->input('username');
		$otp = $request->input('otp');

		$tmp_otp_expired = date('Y-m-d H:i:s', strtotime($date_now.' + 1 day'));

		$data_otp = DB::table('user_otp_tikecting')
		->select('*')
		->where('otp','=',$otp)
		->first();

		if ($data_otp!=null) {
			$res['success'] = false;
			$res['message'] = 'OTP_HAS_BEN_CREATED';
			return response($res);
		}

		$insertOtpMaintenance = DB::table('user_otp_tikecting')->insert(
			[
				'create_date' => $date_now, 
				'expired_date' => $tmp_otp_expired,
				'otp' => $otp,
				'username' => $username,
			]
		);

		if ($insertOtpMaintenance) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}
	}


	// utnuk pengecekan
	// lakukan pengecekan otp, bila otp ada maka lanjut 
	// bila masa berlaku !otp>date_now lanjutkan berikan dia akses otp tersebut.
	public function checkOtpTicketing(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		
		$username = $request->input('username');
		$otp = $request->input('otp');

		$data_otp = DB::table('user_otp_tikecting')
		->select('*')
		->where('otp','=',$otp)
		->first();

		if ($data_otp==null) {
			$res['success'] = false;
			$res['message'] = 'OTP_NOT_FOUND';
			return response($res);
		}

		$str_date_now = strtotime($date_now);
		$str_expired_date = strtotime($data_otp->expired_date);
		
		if ($data_otp->username!=$username) {
			$res['success'] = false;
			$res['message'] = 'USERNAME_NOT_MATCH';
			return response($res);
		}

		if ($str_expired_date>$str_date_now) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'OTP_EXPIRED';
			return response($res);
		}

	}




	public function setOtpLoginApp(Request $request){
		// exit('work');
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$username = $request->input('username');
		$otp = $request->input('otp');
		// $sik_no = $request->input('sik_no');
		// $site_id = $request->input('site_id');


		$data_otp = DB::table('user_otp_app')
		->select('*')
		->where('username','=',$username)
		->first();

		if ($data_otp!=null) {
			$updateOtpMaintenance = DB::table('user_otp_app')
			->where('username','=',$username)
			->update(
				[
					'date_create' => $date_now, 
					'otp' => $otp,
				]
			);
			if ($updateOtpMaintenance) {
				
				$res['success'] = true;
				$res['message'] = 'SUCCESS';
				return response($res);
			}else{
				$res['success'] = false;
				$res['message'] = 'OTP_HAS_BEN_CREATED';
				return response($res);
			}
		}

		$insertOtpMaintenance = DB::table('user_otp_app')->insert(
			[
				'date_create' => $date_now, 
				'otp' => $otp,
				'username' => $username,
				// 'site_id' => $site_id,
				// 'sik_no' => $sik_no,
			]
		);

		if ($insertOtpMaintenance) {
			$res['success'] = true;
			$res['message'] = 'SUCCESS';
			return response($res);
		}else{
			$res['success'] = false;
			$res['message'] = 'SUCCESS';
			return response($res);
		}
	}

}