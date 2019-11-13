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
use App\Models\Concern;
use App\Models\ConcernImage;
use App\Libraries\SendNotifLib;
use App\Helpers\AppHelper;

class ConcernController extends Controller {

	public function submit_concern(Request $request)
	{
		// $now = date('Y-m-d H:i:s');
		$periode = date('Y-m');
		$errorMessage = '';

		$username = $request->input('username');
		$concern_text = $request->input('concern_text');
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');
		$images = $request->file('images');
		$lac = $request->input('lac');
		$ci = $request->input('ci');


		$user = User::where('username',$username)->first();

		$concertPath = storage_path('uploads/concern/'.$periode);

		//truncate image wwkwk
		// $a = scandir($concertPath);
		// foreach ($a as $key => $v) {
		// 	if(in_array($v, ['..','.'])) continue;
		// 	if(is_dir($concertPath.'/'.$v)){
		// 		rmdir($concertPath.'/'.$v);
		// 	}else{
		// 		unlink($concertPath.'/'.$v);
		// 	}
		// 	print_r($v); echo "<hr>";
		// }

		$res = [
			'success'=>'OK',
			'message'=>'Success',
			'params'=>$_POST,
			'files'=>$_FILES,
		];

		$Concern = new Concern;
		$Concern->send_by = $username;
		$Concern->laporan = $concern_text;
		$Concern->latitude = $latitude;
		$Concern->longitude = $longitude;
		$Concern->lac = $lac;
		$Concern->ci = $ci;
		$Concern->is_sync = 0;
		$saved = 0;
		try{
			$saved = $Concern->save();
		}catch(\Exception $e){
       		$errorMessage =  $e->getMessage();   // insert query
    	}

		if(!$saved){
			$res['success'] = 'Failed';
			$res['message'] = 'Tidak dapat menyimpan data concern';
			$res['error'] = $errorMessage;
			return response($res);
		}
		$concern_id = $Concern->id;

		if(!is_dir($concertPath)){
			mkdir($concertPath, 0777, true);
		}
	

		if($request->hasFile('images')){

			$imgCount = count($images);
			$successCount = 0;

			foreach ($images as $i => $img) {
				// gunakan ini jika mau rename file
				// $fname = 'concern_'.$periode.'_'.time().rand(100000,1001238912).'.'.$img->getClientOriginalExtension();
				$fname = $img->getClientOriginalName();//tanpa rename
				$uploaded = $img->move($concertPath,$fname);
				if($uploaded){

					$ConcernImage = new ConcernImage;
					$ConcernImage->concern_id = $concern_id;
					$ConcernImage->uri = 'http://103.253.107.45/semeru-api/storage/uploads/concern/'.$periode.'/';
					$ConcernImage->fname = basename($fname);
					$ConcernImage->concern_id = $concern_id;
					$ConcernImage->is_sync = 0;
					$ConcernImage->save();
					$successCount++;
				}
			}

			if($successCount!=$imgCount){
				//hapus data concern dan img jika ada sebagian foto yg gagal upload
				Concern::where('id', $concern_id)->delete();
				ConcernImage::where('concern_id', $concern_id)->delete();
				$res['success'] = 'Failed';
				$res['message'] = 'Tidak dapat mengunggah semua foto, foto terunggah : '.$successCount.'/'.$imgCount;
				return response($res);
			}
		}	

		$telegram_message = "Halo,\nAda Laporan Concern dari <b>".$user->name."</b>.\nTanggal : ".date('d/m/Y H:i')."\nLatitude : <b>".$latitude."</b>\nLongitude : <b>".$longitude."</b>\nLac : <b>".$lac."</b>\nCi : <b>".$ci."</b>\n\nBerikut isi laporannya: \n<i>".$concern_text."</i>\n\n-NGSemeru Team-";

		$sendNotifLib = new SendNotifLib();
		$sendCode = $sendNotifLib->send_telegram_message('@ngsemeru_concern', $telegram_message);

		return response($res);
	}

}

?>