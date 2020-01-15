<?php
namespace App\Http\Controllers\Api\Sync;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use DB;
use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;

class ConcernController extends Controller {

	public function getConcernData(){

		$concern = DB::table('concern')->where('is_sync', 0)->limit(20)->get();
		foreach($concern as $i => $v){
			$concern[$i]->concern_img = DB::table('concern_image')->where('concern_id', $v->id)->get();
		}   
        $res = [
			'success'=>true,
			'data'=>$concern,
		];
		return response($res);
	}

	public function updateIsSyncConcern(Request $request){
		
		$id = @$request->input('id');
		$id_sync = @$request->input('id_sync');
		$last_sync = @$request->input('last_sync');

		$res = [
			'success'=>false,
			'msg'=>'Data not found',
		];

		$concern = DB::table('concern')->where('id', $id)->first();
		if($concern){
			$data=[
				'is_sync'=>1,
				'id_sync'=>@$id_sync,
				'last_sync'=>@$last_sync,
			];
			DB::table('concern')->where('id', $id)->update($data);
			$res['success'] = true;
			$res['msg'] = 'Success';
		}
		return response($res);
	}
	

}
