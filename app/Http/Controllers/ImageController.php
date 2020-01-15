<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class ImageController extends Controller
{

public function sendImageValue(Request $request){

	date_default_timezone_set("Asia/Jakarta");
	$date_now = date('Y-m-d H:i:s');

	$sp_id = $request->input('sp_id');
	$value_name = $request->input('value_name');
	$value = $request->input('value');
	$fname = $request->input('image_name');
	$latitude = $request->input('latitude');
	$longitude = $request->input('longitude');

	$data['sp_id'] = $sp_id;
	$data['fname'] = $fname;
	$data['value_name'] = $value_name;
	$data['value'] = $value;
	$data['latitude'] = $latitude;
	$data['longitude'] = $longitude;
	$data['date'] = $date_now;
	// $data['host'] = '';
	// $data['uri'] = '';
	// $data['name'] = '';

	//nama tabel : tiketing_image

	$insertLogSP = DB::table('tiketing_image')
	->insert(
	[
		'sp_id' => $data['sp_id'],
		'fname' => $data['fname'],
		'value_name' => $data['value_name'],
		'value' => $data['value'],
		'latitude' => $data['latitude'],
		'longitude' => $data['longitude'],
		'date' => $data['date'],
	]
	);


	// $tmp = $this->getListStatusImage0($sp_id);    
	$res['success'] = true;
	$res['message'] = 'SUCCESS';
	$res['data'] = $data;
	return response($res);
}

public function getListStatusImage(Request $request){

	$sp_id = $request->input('sp_id');

	$tmp = $this->getListStatusImage0($sp_id);
	$res['success'] = $tmp;
	return response($res);
}


public function getListStatusImage0($sp_id){

	// $sp_id = $request->input('sp_id');

	$data_image = DB::table('image')
	->join('supplying_power', 'image.sp_id', '=', 'supplying_power.sp_id')
	->select('*')
	->where('image.sp_id','=',$sp_id)
	->get();

	// return response($data_image);

	$result = json_decode($data_image, true);
	$check['BEFORE_KWH_METER'] = false;
	$check['BEFORE_RUNNING_HOUR'] = false;
	$check['AFTER_KWH_METER'] = false;
	$check['AFTER_RUNNING_HOUR'] = false;

	foreach ($result as $param => $row) {

	$tmp = $row['name'].'';

	switch ($tmp) {
		case 'BEFORE_KWH_METER':
		$check['BEFORE_KWH_METER'] = true;
		break;
		case 'AFTER_KWH_METER':
		$check['AFTER_KWH_METER'] = true;
		break;
		case 'BEFORE_RUNNING_HOUR':
		$check['BEFORE_RUNNING_HOUR'] = true;
		break;
		case 'AFTER_RUNNING_HOUR':
		$check['AFTER_RUNNING_HOUR'] = true;
		break;
		default:

		break;
	}
	}

	if ($check['BEFORE_KWH_METER'] && $check['AFTER_KWH_METER'] && $check['BEFORE_RUNNING_HOUR'] && $check['AFTER_RUNNING_HOUR']) {
	return (true);
	}else{
	return (false);
	}

	// return ($check);
}

}