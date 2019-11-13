<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class OfflineController extends Controller
{

	public function readXml(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		// $type_approval = $request->input('type_approval'); 
		// require __DIR__.'/bootstrap/autoload.php';
		// $real_location = require_once __DIR__.'/bootstrap/app.php';


		$xml=(array)simplexml_load_file("http://localhost/semeru-api/directory_xml/KANIGARANPTI_mbp_prob04_suwandi_SP121.xml");
		// print_r($xml);

		$res['lokasi xml'] = 'http://localhost/semeru-api/directory_xml/';
		// $res['isi xml'] = $xml;
		$res['sp_id'] = $xml['sp_id'];
		$res['mbp_id'] = $xml['mbp_id'];
		// $res['date_check_in'] = $xml['date_check_in'];
		// $res['date_done'] = $xml['date_done'];
		$img['image 1'] = (array)$xml['image1'];
		$res['image 1 name'] = $img['image 1']['name'];
		$res['image 1 date'] = $img['image 1']['date'];
		// $res['image 1 image'] = $img['image 1']['image'];

		$img['image 2'] = (array)$xml['image2'];
		$res['image 2 name'] = $img['image 2']['name'];
		$res['image 2 date'] = $img['image 2']['date'];
		// $res['image 2 image'] = $img['image 2']['image'];

		$img['image 3'] = (array)$xml['image3'];
		$res['image 3 name'] = $img['image 3']['name'];
		$res['image 3 date'] = $img['image 3']['date'];
		// $res['image 3 image'] = $img['image 3']['image'];

		$img['image 4'] = (array)$xml['image4'];
		$res['image 4 name'] = $img['image 4']['name'];
		$res['image 4 date'] = $img['image 4']['date'];
		// $res['image 4 image'] = $img['image 4']['image'];

		file_put_contents("/coba1.png",base64_decode($img['image 1']['image']));

		return response($res);
	}


	public function insertDataOffline(Request $request){
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		// $type_approval = $request->input('type_approval'); 
		$mbp_data = DB::table('supplying_power')
		->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
		->join('site', 'supplying_power.site_id', '=', 'site.site_id')
		->where('supplying_power.mbp_id','=',$mbp_id)
		->where('supplying_power.finish','=',null)
		->update(
			[
				'supplying_power.finish' =>'CANCEL',
				'supplying_power.date_finish' =>date('Y-m-d H:i:s'),
				'mbp.status' =>'AVAILABLE',
				'mbp.submission' =>null,
				'mbp.submission_id' =>null,
				'mbp.active_at' =>null,
				'mbp.message_id' =>null,
				'site.is_allocated' =>'0',
			]
		);
	
		return response($res);
	}


}