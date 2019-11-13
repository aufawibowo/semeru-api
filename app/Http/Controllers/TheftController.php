<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class TheftController extends Controller{

	public function getDataPencurian(Request $request){

		date_default_timezone_set("Asia/Jakarta");
		// $corrective_id = $request->input('corrective_id'); 

		$pencurian_data = DB::table('app_pencurian')
		->select('*')
		->get();

        $pencurian_result = json_decode($pencurian_data, true);
        if ($pencurian_result==null) {
        	
		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $pencurian_data;
		return response($res);
        }

        foreach ($pencurian_result as $param => $row) {
        	$data[$param]['pencurian_id'] = $row['pencurian_id'];
        	$data[$param]['site_id'] = $row['site_id'];
        	$data[$param]['site_name'] = $row['site_name'];
        	$data[$param]['fmc_id'] = $row['fmc_id'];
        	$data[$param]['fmc'] = $row['fmc'];
        	$data[$param]['fmc_cn'] = $row['fmc_cn'];
        	$data[$param]['fmc_nik'] = $row['fmc_nik'];
        	$data[$param]['cluster_id'] = $row['cluster_id'];
        	$data[$param]['cluster'] = $row['cluster'];
        	$data[$param]['cluster_fmc_id'] = $row['cluster_fmc_id'];
        	$data[$param]['cluster_fmc'] = $row['cluster_fmc'];
        	$data[$param]['ns_id'] = $row['ns_id'];
        	$data[$param]['ns'] = $row['ns'];
        	$data[$param]['rtpo_id'] = $row['rtpo_id'];
        	$data[$param]['rtpo'] = $row['rtpo'];
        	$data[$param]['branch_id'] = $row['branch_id'];
        	$data[$param]['branch'] = $row['branch'];
        	$data[$param]['regional'] = $row['regional'];
        	$data[$param]['tgl_transaksi'] = $row['tgl_transaksi'];
        	$data[$param]['tgl_pelaporan'] = $row['tgl_pelaporan'];
        	$data[$param]['description'] = $row['description'];

        	$images_pencurian_data = DB::table('app_pencurian_img')
        	->select('*')
        	->where('pencurian_id','=',$row['pencurian_id'])
        	->get();

        	$data[$param]['images_data'] = $images_pencurian_data;
        }

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		$res['data'] = $data;
		return response($res);
	}
	public function deleteDataPencurian(Request $request){
		
		$array_pencurian_id = $request->input('array_pencurian_id'); 


		foreach ($array_pencurian_id as $param => $row) {

			DB::table('app_pencurian')
        	->where('pencurian_id','=',$row['pencurian_id'])
			->delete();

			DB::table('app_pencurian_img')
        	->where('pencurian_id','=',$row['pencurian_id'])
			->delete();

		}

		$res['success'] = true;
		$res['message'] = 'SUCCESS';
		// $res['data'] = $data;
		return response($res);
	}

}