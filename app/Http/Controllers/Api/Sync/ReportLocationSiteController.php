<?php
namespace App\Http\Controllers\Api\Sync;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use DB;
use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;

class ReportLocationSiteController extends Controller {

	public function getReportLocationSite(){

		// $report_location_site = DB::table('report_location_site')->where('site_id', 'SBZ355')->limit(20)->get();
		$report_location_site = DB::table('report_location_site')->where('is_sync', 0)->limit(20)->get();
		$res = [
			'success'=>true,
			'data'=>$report_location_site,
		];
		return response($res);
	}

	public function updateIsSyncReportLocationSite(Request $request){
		
		$report_id = @$request->input('report_id');
		$last_sync = @$request->input('last_sync');

		$res = [
			'success'=>false,
			'msg'=>'Data not found',
		];

		$report_location_site = DB::table('report_location_site')->where('report_id', $report_id)->first();
		if($report_location_site){
			$data=[
				'is_sync'=>1,
				'last_sync'=>@$last_sync,
			];
			DB::table('report_location_site')->where('report_id', $report_id)->update($data);
			$res['success'] = true;
			$res['msg'] = 'Success';
		}
		return response($res);
	}
	

}
