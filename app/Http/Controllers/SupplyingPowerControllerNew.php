<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
use DateTime;

class SupplyingPowerControllerNew extends Controller{

	// public function get_list_history_supplying_power(Request $request){

	// 	$user_id = $request->input('user_id');
	// 	//$user_id = 1;
	// 	date_default_timezone_set("Asia/Jakarta");

	// 	// cari suertype
	// 	$check_type = DB::table('users')
	// 				->select('*')
	// 				->where('id','=',$user_id)
	// 				->first();

	// 	if($check_type->user_type=='RTPO'){

	// 		$check_rtpo = DB::table('user_rtpo')
	// 					->select('*')
	// 					->where('username','=',$check_type->username)
	// 					->first();

	// 		$btss = DB::table('supplying_power as sp')
    //         ->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
    //         ->join('user_rtpo', 'supplying_power.rtpo_id', '=', 'user_rtpo.rtpo_id')
    //         ->join('rtpo', 'rtpo.rtpo_id', '=', 'user_rtpo.rtpo_id')
    //         ->join('site', 'site.site_id', '=', 'supplying_power.site_id')
    //         ->select('supplying_power.sp_id',
    //             'supplying_power.unique_id',
    //             'site.site_name',
    //             'site.site_id',
	// 			'supplying_power_log.status',
	// 			'sp.date_waiting as ticker_creation_time')
    //         ->where('supplying_power.rtpo_id','=',$check_rtpo->rtpo_id)
    //         ->where('supplying_power.finish','!=',NULL)
    //         ->orderBy('supplying_power.sp_id', 'desc')
    //         ->limit(25)
    //         ->get();

	// 		$result = json_decode($btss, "OK");
	// 		if ($result==NULL) {
	// 			$res['success'] = "OK";
	// 			$res['message'] = 'Success';
	// 			$res['data'] = $btss;
	// 			return response($res);
	// 		}
	// 		foreach ($result as $param => $row) {
	// 			$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
	// 			$data[$param]['sp_id']        = $row['sp_id'];
	// 			$data[$param]['unique_id']    = $row['unique_id'];
	// 			$data[$param]['site_name']    = $row['site_name'];
	// 			$data[$param]['site_id']      = $row['site_id'];
	// 			$data[$param]['status']       = $row['status'];
	// 			$data[$param]['ticket_creation_time'] = $newDate; //hardcode
	// 		}

	// 		if ($btss) {
	// 			$res['success'] = "OK";
	// 			$res['message'] = 'Success';
	// 			$res['data'] = $data;

	// 			return response($res);
	// 		}else{
	// 			$polys['success'] = false;
	// 			$polys['message'] = 'Cannot find polys!';

	// 			return response($btss);
	// 		}

	// 	}
	// 	else if($check_type->user_type=='MBP'){

	// 		$check_mbp = DB::table('user_mbp')
    //               ->select('*')
    //               ->where('username','=',$check_type->username)
    //               ->get(); 

	// 		$btss = null;

	// 		$mbp_result = json_decode($check_mbp, "OK");

	// 		if ($mbp_result==null) {

	// 			$res['success'] = false;
	// 			$res['message'] = 'USER_MBP_NOT_FOUND';
	// 			return response($res);
	// 		}

	// 		foreach ($mbp_result as $param => $row) {
	// 			if($btss!=NULL){
	// 				$btss = DB::table('supplying_power as sp')
	// 				->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
	// 				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
	// 				->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	// 				->select('supplying_power.sp_id',
	// 						'supplying_power.unique_id',
	// 						'site.site_name',
	// 						'site.site_id',
	// 						'supplying_power_log.status',
	// 						'supplying_power.date_waiting as ticker_creation_time')
	// 				->where('supplying_power.mbp_id','=',$mbp_result[$param]['mbp_id'])
	// 				->where('supplying_power.finish','!=',NULL)
	// 				->orderBy('supplying_power.sp_id', 'desc')
	// 				->limit(15)
	// 				->get();

	// 				$tmp = json_decode($btss, "OK");

	// 				$resultSP = array_merge($resultSP ,$tmp);
	// 			}
	// 			else{
	// 				$btss = DB::table('supplying_power as sp')
	// 				->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
	// 				->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
	// 				->join('site', 'supplying_power.site_id', '=', 'site.site_id')
	// 				->select('supplying_power.sp_id',
	// 						'supplying_power.unique_id',
	// 						'site.site_name',
	// 						'site.site_id',
	// 						'supplying_power_log.status',
	// 						'supplying_power.date_waiting as ticker_creation_time')
	// 				->where('mbp.mbp_id','=',$mbp_result[$param]['mbp_id'])
	// 				->where('supplying_power.finish','!=',NULL)
	// 				->orderBy('supplying_power.sp_id', 'desc')
	// 				->limit(15)
	// 				->get();

	// 				$resultSP = json_decode($btss, "OK");
	// 			}
	// 		}


	// 		$result = $resultSP;
	// 		if ($result==null) {

	// 			$res['success'] = "OK";
	// 			$res['message'] = 'Success';
	// 			$res['data'] = $btss;
	// 			return response($res);
	// 		}
	// 		// $result = json_decode($btss, "OK");

	// 		foreach ($result as $param => $row) {
	// 			$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
	// 			$data[$param]['sp_id']        = $row['sp_id'];
	// 			$data[$param]['unique_id']    = $row['unique_id'];
	// 			$data[$param]['site_name']    = $row['site_name'];
	// 			$data[$param]['site_id']      = $row['site_id'];
	// 			$data[$param]['status']       = $row['status'];
	// 			$data[$param]['ticket_creation_time'] = $newDate; //hardcode
	// 		}

	// 		array_multisort($id, SORT_DESC, $data);

	// 		if ($btss) {
	// 			$res['success'] = "OK";
	// 			$res['message'] = 'Success';
	// 			$res['data'] = $data;

	// 			return response($res);
	// 		}else{
	// 			$polys['success'] = false;
	// 			$polys['message'] = 'Cannot find polys!';

	// 			return response($btss);
	// 		}

	// 	}
	// 	else{

	// 		$res['success'] = false;
	// 		$res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
			
	// 		return response($res);
	// 	}
	// }

  public function get_list_history_supplying_power(Request $request){

		$user_id = $request->input('user_id');
		//$user_id = 1;
		date_default_timezone_set("Asia/Jakarta");
		$date_now = date('Y-m-d H:i:s');

		$page = $request->input('page');
		$search = $request->input('search');
  
		$limit = 20;
		$offset = ($page-1)*$limit;

		// cari suertype
		$check_type = DB::table('users')
					->select('*')
					->where('id','=',$user_id)
					->first();

		if($check_type->user_type=='RTPO'){

			$check_rtpo = DB::table('user_rtpo')
						->select('*')
						->where('username','=',$check_type->username)
						->first();

			$btss = DB::table('supplying_power as sp')
					->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
					->join('user_rtpo', 'supplying_power.rtpo_id', '=', 'user_rtpo.rtpo_id')
					->join('rtpo', 'rtpo.rtpo_id', '=', 'user_rtpo.rtpo_id')
					->join('site', 'site.site_id', '=', 'supplying_power.site_id')
					->select('supplying_power.sp_id',
							'supplying_power.unique_id',
							'site.site_name',
							'site.site_id',
							'supplying_power_log.status',
							'supplying_power.date_waiting as ticker_creation_time')
					->where('supplying_power.rtpo_id','=',$check_rtpo->rtpo_id)
					->where('supplying_power.date_finish','<',$date_now)
					->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
					->offset($offset)
					->limit($limit)
					->orderBy('supplying_power.sp_id', 'desc')
					->get();

			$btss_count = DB::table('supplying_power as sp')
			->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
			->join('user_rtpo', 'supplying_power.rtpo_id', '=', 'user_rtpo.rtpo_id')
			->join('rtpo', 'rtpo.rtpo_id', '=', 'user_rtpo.rtpo_id')
			->join('site', 'site.site_id', '=', 'supplying_power.site_id')
			->select('supplying_power.sp_id',
					'supplying_power.unique_id',
					'site.site_name',
					'site.site_id',
					'supplying_power_log.status',
					'supplying_power.date_waiting as ticker_creation_time')
			->where('supplying_power.rtpo_id','=',$check_rtpo->rtpo_id)
			->where('supplying_power.date_finish','<',$date_now)
			->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
			->offset($offset)
			->limit($limit)
			->count();


			$totalPage = $btss_count / $limit;
			if(is_float($totalPage)){
				$totalPage = ceil($totalPage);
			}
			else{
				$totalPage = floor($totalPage);
			}

			$result = json_decode($btss, "OK");
			if ($result==NULL) {
				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['total_page'] = $btss_count;
				$res['data'] = $btss;
				return response($res);
			}
			foreach ($result as $param => $row) {
				$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
				$data[$param]['sp_id']        = $row['sp_id'];
				$data[$param]['unique_id']    = $row['unique_id'];
				$data[$param]['site_name']    = $row['site_name'];
				$data[$param]['site_id']      = $row['site_id'];
				$data[$param]['status']       = $row['status'];
				$data[$param]['ticket_creation_time'] = $newDate; //hardcode
			}

			if ($btss) {
				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['total_page'] = $btss_count;
				$res['data'] = $btss;
				return response($res);
			}else{
				$polys['success'] = false;
				$polys['message'] = 'Cannot find polys!';

				return response($btss);
			}

		}
		else if($check_type->user_type=='MBP'){

			$check_mbp = DB::table('user_mbp')
			->select('*')
			->where('username','=',$check_type->username)
			->get(); 

			$btss = null;

			$mbp_result = json_decode($check_mbp, "OK");

			if ($mbp_result==null) {

				$res['success'] = false;
				$res['message'] = 'USER_MBP_NOT_FOUND';
				return response($res);
			}

			foreach ($mbp_result as $param => $row) {
				if($btss!=NULL){
					$btss = DB::table('supplying_power as sp')
					->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
					->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
					->join('site', 'supplying_power.site_id', '=', 'site.site_id')
					->select('supplying_power.sp_id',
							'supplying_power.unique_id',
							'site.site_name',
							'site.site_id',
							'supplying_power_log.status',
							'supplying_power.date_waiting as ticker_creation_time')
					->where('supplying_power.mbp_id','=',$mbp_result[$param]['mbp_id'])
					->where('supplying_power.date_finish','<',$date_now)
					->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
					->orderBy('supplying_power.sp_id', 'desc')
					->offset($offset)
					->limit($limit)
					->get();

					$btss_count = DB::table('supplying_power as sp')
					->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
					->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
					->join('site', 'supplying_power.site_id', '=', 'site.site_id')
					->select('supplying_power.sp_id',
							'supplying_power.unique_id',
							'site.site_name',
							'site.site_id',
							'supplying_power_log.status',
							'supplying_power.date_waiting as ticker_creation_time')
					->where('supplying_power.mbp_id','=',$mbp_result[$param]['mbp_id'])
					->where('supplying_power.date_finish','<',$date_now)
					->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
					->orderBy('supplying_power.sp_id', 'desc')
					->offset($offset)
					->limit($limit)
					->count();

					$tmp = json_decode($btss, "OK");

					$resultSP = array_merge($resultSP ,$tmp);
				}
				else{
					$btss = DB::table('supplying_power as sp')
					->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
					->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
					->join('site', 'supplying_power.site_id', '=', 'site.site_id')
					->select('supplying_power.sp_id',
							'supplying_power.unique_id',
							'site.site_name',
							'site.site_id',
							'supplying_power_log.status',
							'supplying_power.date_waiting as ticker_creation_time')
					->where('mbp.mbp_id','=',$mbp_result[$param]['mbp_id'])
					->where('supplying_power.finish','!=',NULL)
					->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
					->orderBy('supplying_power.sp_id', 'desc')
					->offset($offset)
					->limit($limit)
					->get(); 

					$btss_count = DB::table('supplying_power as sp')
					->join('supplying_power_log','supplying_power.sp_id','=','supplying_power_log.sp_id')
					->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')
					->join('site', 'supplying_power.site_id', '=', 'site.site_id')
					->select('supplying_power.sp_id',
							'supplying_power.unique_id',
							'site.site_name',
							'site.site_id',
							'supplying_power_log.status',
							'supplying_power.date_waiting as ticker_creation_time')
					->where('mbp.mbp_id','=',$mbp_result[$param]['mbp_id'])
					->where('supplying_power.finish','!=',NULL)
					->whereraw('(site.site_id like "%'.$search.'%" or site.site_name like "%'.$search.'%")')
					->orderBy('supplying_power.sp_id', 'desc')
					->offset($offset)
					->limit($limit)
					->count(); 

					$resultSP = json_decode($btss, "OK");
				}
			}

			$totalPage = $btss_count / $limit;
			if(is_float($totalPage)){
				$totalPage = ceil($totalPage);
			}
			else{
				$totalPage = floor($totalPage);
			}

			$result = $resultSP;
			if ($result==null) {
				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['total_page'] = $btss_count;
				$res['data'] = $btss;
				return response($res);
			}
			// $result = json_decode($btss, "OK");

			foreach ($result as $param => $row) {
				$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
				$data[$param]['sp_id']        = $row['sp_id'];
				$data[$param]['unique_id']    = $row['unique_id'];
				$data[$param]['site_name']    = $row['site_name'];
				$data[$param]['site_id']      = $row['site_id'];
				$data[$param]['status']       = $row['status'];
				$data[$param]['ticket_creation_time'] = $newDate; //hardcode
			}

			array_multisort($id, SORT_DESC, $data);

			if ($btss) {
				$res['success'] = "OK";
				$res['message'] = 'Success';
				$res['total_page'] = $btss_count;
				$res['data'] = $btss;
				return response($res);
			}else{
				$polys['success'] = false;
				$polys['message'] = 'Cannot find polys!';

				return response($btss);
			}

		}
		else{

			$res['success'] = false;
			$res['message'] = 'FAILED_TYPE_APPROVAL_WRONG';
			
			return response($res);
		}
  }

//   public function get_list_history_supplying_power_area(Request $request){

//     date_default_timezone_set("Asia/Jakarta");

//     $btss = DB::table('supplying_power as sp')
//           ->join('users as u', 'sp.user_id', '=', 'u.id')
//           ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
//           ->join('site as s', 'sp.site_id', '=', 's.site_id')
//           ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
// 		  ->select(	'sp.sp_id', 
// 					'sp.unique_id', 
// 					's.site_name', 
// 					's.site_id', 
// 					'spl.status', 
// 					'sp.date_waiting as ticker_creation_time')
//           ->where('sp.finish','!=',NULL)
//           ->orderBy('sp.sp_id', 'desc')
//           ->limit(50)
//           ->get();

//     $result = json_decode($btss, "OK");
//     if ($result==NULL) {
//       $res['success'] = "OK";
//       $res['message'] = 'Success';
//       $res['data'] = $btss;
//       return response($res);
//     }

//     foreach ($result as $param => $row) {
// 		$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
// 		$data[$param]['sp_id']        = $row['sp_id'];
// 		$data[$param]['unique_id']    = $row['unique_id'];
// 		$data[$param]['site_name']    = $row['site_name'];
// 		$data[$param]['site_id']      = $row['site_id'];
// 		$data[$param]['status']       = $row['status'];
// 		$data[$param]['ticket_creation_time'] = $newDate; //hardcode
//     }

//     if ($btss) {
//       $res['success'] = "OK";
//       $res['message'] = 'Success';
//       $res['data'] = $data;

//       return response($res);
//     }

//   }

  public function get_list_history_supplying_power_area(Request $request){

    date_default_timezone_set("Asia/Jakarta");

    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $btss = DB::table('supplying_power as sp')
    ->join('users as u', 'sp.user_id', '=', 'u.id')
    ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
    ->join('site as s', 'sp.site_id', '=', 's.site_id')
    ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
	->select(	'sp.sp_id', 
				'sp.unique_id', 
				's.site_name', 
				's.site_id', 
				'spl.status', 
				'sp.date_waiting as ticker_creation_time')
    ->where('sp.finish','!=',NULL)
    ->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->orderBy('sp.sp_id', 'desc')
	->get();
	
	$btss_count = DB::table('supplying_power as sp')
    ->join('users as u', 'sp.user_id', '=', 'u.id')
    ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
    ->join('site as s', 'sp.site_id', '=', 's.site_id')
    ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
	->select(	'sp.sp_id', 
				'sp.unique_id', 
				's.site_name', 
				's.site_id', 
				'spl.status', 
				'sp.date_waiting as ticker_creation_time')
    ->where('sp.finish','!=',NULL)
    ->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->count();

	$totalPage = $btss_count / $limit;
    if(is_float($totalPage)){
      $totalPage = ceil($totalPage);
    }
    else{
      $totalPage = floor($totalPage);
    }

    $result = json_decode($btss, "OK");
    if ($result==NULL) {
		$res['success'] = "OK";
		$res['message'] = 'Success';
		$res['total_page'] = $btss_count;
		$res['data'] = $btss;
		return response($res);
    }

    foreach ($result as $param => $row) {
		$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
		$data[$param]['sp_id']        = $row['sp_id'];
		$data[$param]['unique_id']    = $row['unique_id'];
		$data[$param]['site_name']    = $row['site_name'];
		$data[$param]['site_id']      = $row['site_id'];
		$data[$param]['status']       = $row['status'];
		$data[$param]['ticket_creation_time'] = $newDate; //hardcode
    }

    if ($btss) {
		$res['success'] = "OK";
		$res['message'] = 'Success';
		$res['total_page'] = $btss_count;
		$res['data'] = $btss;
		return response($res);
    }

  }


//   public function get_list_history_supplying_power_ns(Request $request){

//     $ns_id = $request->input('ns_id');
//     date_default_timezone_set("Asia/Jakarta");

//     $btss = DB::table('supplying_power as sp')
//           ->join('users as u', 'sp.user_id', '=', 'u.id')
//           ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
//           ->join('site as s', 'sp.site_id', '=', 's.site_id')
//           ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
// 		  ->select(	'sp.sp_id', 
// 					  'sp.unique_id', 
// 					  's.site_name', 
// 					  's.site_id', 
// 					  'spl.status', 
// 					  'sp.date_waiting as ticker_creation_time')
//           ->where('sp.ns_id','=',$ns_id)
//           ->where('sp.finish','!=',NULL)
//           ->orderBy('sp.sp_id', 'desc')
//           ->limit(50)
//           ->get();

//     $result = json_decode($btss, "OK");
//     if ($result==NULL) {
//       $res['success'] = "OK";
//       $res['message'] = 'Success';
//       $res['data'] = $btss;
//       return response($res);
//     }

//     foreach ($result as $param => $row) {
// 		$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
// 		$data[$param]['sp_id']        = $row['sp_id'];
// 		$data[$param]['unique_id']    = $row['unique_id'];
// 		$data[$param]['site_name']    = $row['site_name'];
// 		$data[$param]['site_id']      = $row['site_id'];
// 		$data[$param]['status']       = $row['status'];
// 		$data[$param]['ticket_creation_time'] = $newDate; //hardcode
//     }

//     if ($btss) {
//       $res['success'] = "OK";
//       $res['message'] = 'Success';
//       $res['data'] = $data;

//       return response($res);
//     }

//   }

  public function get_list_history_supplying_power_ns(Request $request){

    $ns_id = $request->input('ns_id');
    date_default_timezone_set("Asia/Jakarta");

    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $btss = DB::table('supplying_power as sp')
    ->join('users as u', 'sp.user_id', '=', 'u.id')
    ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
    ->join('site as s', 'sp.site_id', '=', 's.site_id')
    ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
	->select(	'sp.sp_id', 
				'sp.unique_id', 
				's.site_name', 
				's.site_id', 
				'spl.status', 
				'sp.date_waiting as ticket_creation_time')
    ->where('sp.ns_id','=',$ns_id)
    ->where('sp.finish','!=',NULL)
    ->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->orderBy('sp.sp_id', 'desc')
	->get();
	
	$btss_count = DB::table('supplying_power as sp')
    ->join('users as u', 'sp.user_id', '=', 'u.id')
    ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
    ->join('site as s', 'sp.site_id', '=', 's.site_id')
    ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
	->select(	'sp.sp_id', 
				'sp.unique_id', 
				's.site_name', 
				's.site_id', 
				'spl.status', 
				'sp.date_waiting as ticket_creation_time')
    ->where('sp.ns_id','=',$ns_id)
    ->where('sp.finish','!=',NULL)
    ->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
	->count();

	$totalPage = $btss_count / $limit;
    if(is_float($totalPage)){
      $totalPage = ceil($totalPage);
    }
    else{
      $totalPage = floor($totalPage);
    }

    $result = json_decode($btss, "OK");
    if ($result==NULL) {
		$res['success'] = "OK";
		$res['message'] = 'Success';
		$res['total_page'] = $btss_count;
		$res['data'] = $btss;
		return response($res);
    }

    foreach ($result as $param => $row) {
		//$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
		$data[$param]['sp_id']        = $row['sp_id'];
		$data[$param]['unique_id']    = $row['unique_id'];
		$data[$param]['site_name']    = $row['site_name'];
		$data[$param]['site_id']      = $row['site_id'];
		$data[$param]['status']       = $row['status'];
		$data[$param]['ticket_creation_time'] = $this->setDatedMYHis($row['ticket_creation_time']); //hardcode
    }

    if ($btss) {
		$res['success'] = "OK";
		$res['message'] = 'Success';
		$res['total_page'] = $btss_count;
		$res['data'] = $btss;
		return response($res);
    }

  }

//   public function get_list_history_supplying_power_regional(Request $request){

//     $regional = $request->input('regional');
//     date_default_timezone_set("Asia/Jakarta");

//     $btss = DB::table('supplying_power as sp')
//     ->join('users as u', 'sp.user_id', '=', 'u.id')
//     ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
//     ->join('site as s', 'sp.site_id', '=', 's.site_id')
//     ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
// 	->select(	'sp.sp_id', 
// 				'sp.unique_id', 
// 				's.site_name', 
// 				's.site_id', 
// 				'spl.status', 
// 				'sp.date_waiting as ticker_creation_time')
//     ->where('sp.regional','=',$regional)
//     ->where('sp.finish','!=',NULL)
//     ->orderBy('sp.sp_id', 'desc')
//     ->limit(50)
//     ->get();

//     $result = json_decode($btss, "OK");
//     if ($result==NULL) {
//       $res['success'] = "OK";
//       $res['message'] = 'Success';
//       $res['data'] = $btss;
//       return response($res);
//     }

//     foreach ($result as $param => $row) {
// 		$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
// 		$data[$param]['sp_id']        = $row['sp_id'];
// 		$data[$param]['unique_id']    = $row['unique_id'];
// 		$data[$param]['site_name']    = $row['site_name'];
// 		$data[$param]['site_id']      = $row['site_id'];
// 		$data[$param]['status']       = $row['status'];
// 		$data[$param]['ticket_creation_time'] = $newDate; //hardcode
//     }

//     if ($btss) {
//       $res['success'] = "OK";
//       $res['message'] = 'Success';
//       $res['data'] = $data;

//       return response($res);
//     }

//   }

  public function get_list_history_supplying_power_regional(Request $request){

    $regional = $request->input('regional');
    date_default_timezone_set("Asia/Jakarta");

    $page = $request->input('page');
    $search = $request->input('search');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $btss = DB::table('supplying_power as sp')
    ->join('users as u', 'sp.user_id', '=', 'u.id')
    ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
    ->join('site as s', 'sp.site_id', '=', 's.site_id')
    ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
	->select(	'sp.sp_id', 
				'sp.unique_id',
				's.site_name', 's.site_id', 'spl.status', 'sp.date_waiting as ticker_creation_time')
    ->where('sp.regional','=',$regional)
    ->where('sp.finish','!=',NULL)
    ->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->orderBy('sp.sp_id', 'desc')
	->get();
	
	$btss_count = DB::table('supplying_power as sp')
    ->join('users as u', 'sp.user_id', '=', 'u.id')
    ->join('mbp as m', 'sp.mbp_id', '=', 'm.mbp_id')
    ->join('site as s', 'sp.site_id', '=', 's.site_id')
    ->join('supplying_power_log as spl', 'spl.sp_id', '=', 'sp.sp_id')
	->select(	'sp.sp_id', 
				'sp.unique_id',
				's.site_name', 's.site_id', 'spl.status', 'sp.date_waiting as ticker_creation_time')
    ->where('sp.regional','=',$regional)
    ->where('sp.finish','!=',NULL)
    ->whereraw('(sp.site_id like "%'.$search.'%" or s.site_name like "%'.$search.'%")')
    ->offset($offset)
    ->limit($limit)
    ->count();

	$totalPage = $btss_count / $limit;
	if(is_float($totalPage)){
		$totalPage = ceil($totalPage);
	}
	else{
		$totalPage = floor($totalPage);
	}

    $result = json_decode($btss, "OK");
    if ($result==NULL) {
		$res['success'] = "OK";
		$res['message'] = 'Success';
		$res['total_page'] = $btss_count;
		$res['data'] = $btss;
		return response($res);
    }

    foreach ($result as $param => $row) {
		$newDate = $this->setDatedMYHis($row['ticker_creation_time'].'');
		$data[$param]['sp_id']        = $row['sp_id'];
		$data[$param]['unique_id']    = $row['unique_id'];
		$data[$param]['site_name']    = $row['site_name'];
		$data[$param]['site_id']      = $row['site_id'];
		$data[$param]['status']       = $row['status'];
		$data[$param]['ticket_creation_time'] = $newDate; //hardcode
    }

    if ($btss) {
		$res['success'] = "OK";
		$res['message'] = 'Success';
		$res['total_page'] = $btss_count;
		$res['data'] = $btss;
		return response($res);
    }

  }

  public function setDatedMYHis($date){
	if ($date==null) {
	  return "-";
	}else if ($date=='0000-00-00 00:00:00') {
	  return "-";
	}else{
	  //$date = date("d-M-Y H:i:s", strtotime($date.''));
	  $date = date(strtotime($date));
	  //$date = date("d-m H:i");
	  return $date;
	  //return date_format($date,"d/M H:i");
	  // return strtotime($date.'');
	}
  }

}