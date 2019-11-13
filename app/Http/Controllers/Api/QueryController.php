<?php
namespace App\Http\Controllers\Api;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use App\Bts;
use DB;
use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;

class QueryController extends Controller {

	public function sp_get_by_id_sync(Request $request){

		$id_sync = @$request->input('id_sync');

		$data = DB::table('supplying_power')->where('id_sync', $id_sync)->first();
		$res = [
			'msg'=>'Data Not Found',
			'data'=>[],
		];
		if($data){
			$res['msg'] = 'OK';
			$res['data'] = $data;
		}
		return response($res);

	}
	public function sp_fix_meet_sla(Request $request){

		

		$execute = $request->input('execute');
		exit('work');
		$data = DB::table('supplying_power')
			->where('date_finish','like','2019-10%')
			->where('finish','DONE')
			// ->whereNull('meet_sla')->get();
			->where('unique_id','SPP_JAV04908_191005045418')->get();
		// \dd($data);
		// echo "work";
			foreach ($data as $key => $data_sp) {
				// print_r($data_sp->meet_sla); echo "<hr>";


				// $datetime1 = new DateTime($data_sp->date_waiting);
				$datetime2 = new DateTime($data_sp->date_onprogress);
				$datetime3 = new DateTime($data_sp->date_checkin);

				$time_to_site = $datetime2->diff($datetime3);

				$second = $time_to_site->h*3600+$time_to_site->i*60+$time_to_site->s;

				if ($second>7200){
					$meet_sla = 0;
				} else{
					$meet_sla = 1;
				}
				echo "status meet sla before ";
				print_r($data_sp->meet_sla); echo "<br>";
				echo "create : ".$data_sp->date_waiting."<br>";
				echo "onprogress : ".$data_sp->date_onprogress."<br>";
				echo "checkin : ".$data_sp->date_checkin."<br>";
				echo "sp id : ".$data_sp->sp_id." meet sla : ".$meet_sla."<hr>";

				if($execute){
					DB::table('supplying_power')
					->where('sp_id','=',$data_sp->sp_id)
					->update(
						[
							'meet_sla'=>$meet_sla,
							'is_sync'=>0
						]
					);
					echo "Execute";
				}
			}
	}

}
