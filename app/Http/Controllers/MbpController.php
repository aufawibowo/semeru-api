<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class MbpController extends Controller
{


  public function getAllMbp(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();
    $data_site = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.*','user_mbp.user_id')
    // ->where('rtpo_id','=',$rtpo_id)
    ->get();

    if ($data_site) {
      $res['success'] = true;
      $res['message'] = 'Success!';
      $res['data'] = $data_site;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }

  }

  public function getStatusMbp(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();

    $mbp_id = $request->input('mbp_id');

    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.status')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();

    if ($mbp_data) {

      if ($mbp_data->status!='AVAILABLE') {

        $data_mbp_task = DB::table('supplying_power')
        ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')               // get status mbp
        ->join('users', 'supplying_power.user_id', '=', 'users.id')                // get name
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')           // get site name, lat, lon
        ->join('class', 'site.class_id', '=', 'class.class_id')                   // get class name

        ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude')

        ->where('done_status','=','0') 
        ->where('cancellation_status','=','0')
        ->first();

        if ($data_mbp_task) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $data_mbp_task;

          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'CANNOT_FIND_DATA';

          return response($res);
        }  

      }else{
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_data;

        return response($res);
      }

      
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }

  }
  
  public function updateStatusMbptoOnProgress(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();

    date_default_timezone_set("Asia/Jakarta");
    $mbp_id = $request->input('mbp_id');
    
    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_id', $mbp_id)
    ->update(['status' => 'ON_PROGRESS']);

    if ($editMbp) {

      
      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.status')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      // fungsi create new suppliyinf power
      $insertSP = DB::table('supplying_power')
      ->where('mbp_id', $mbp_id)
      ->where('done_status', '0')
      ->where('cancellation_status', '0')
      ->update(
        [
          'date_onprogress' => date('Y-m-d H:i:s'),
        ]
      );

      if ($mbp_data && $insertSP) {

        if ($mbp_data->status!='AVAILABLE') {

          $data_mbp_task = DB::table('supplying_power')
        ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')               // get status mbp
        ->join('users', 'supplying_power.user_id', '=', 'users.id')                // get name
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')           // get site name, lat, lon
        ->join('class', 'site.class_id', '=', 'class.class_id')                   // get class name

        ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name')
        
        ->where('done_status','=','0') 
        ->where('cancellation_status','=','0')
        ->where('supplying_power.mbp_id', $mbp_id)
        ->first();

        if ($data_mbp_task) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $data_mbp_task;

          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'CANNOT_FIND_DATA';

          return response($res);
        }  

      }else{
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_data;

        return response($res);
      }

      
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }else{
    $res['success'] = false;
    $res['message'] = 'CANNOT_FIND_DATA';

    return response($res);
  }
}

public function updateStatusMbp(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();

    date_default_timezone_set("Asia/Jakarta");
    $mbp_id = $request->input('mbp_id');
    $status = $request->input('status');
    
    $editMbp = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->where('mbp.mbp_id', $mbp_id)
    ->update(['status' => $status]);

    if ($editMbp) {

      
      $mbp_data = DB::table('mbp')
      ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
      ->select('mbp.status')
      ->where('mbp.mbp_id','=',$mbp_id)
      ->first();

      if ($status=='ON_PROGRESS') {

        // isi date on proggressnya
        $insertSP = DB::table('supplying_power')
        ->where('mbp_id', $mbp_id)
        ->where('done_status', '0')
        ->where('cancellation_status', '0')
        ->update(
          [
            'date_onprogress' => date('Y-m-d H:i:s'),
          ]
        );

      }else if ($status=='CHECK_IN') {

        // isi date checkinnya
        $insertSP = DB::table('supplying_power')
        ->where('mbp_id', $mbp_id)
        ->where('done_status', '0')
        ->where('cancellation_status', '0')
        ->update(
          [
            'date_checkin' => date('Y-m-d H:i:s'),
          ]
        );

      }else if($status=='AVAILABLE'){
        // isi date donenya + status done = 1
        $insertSP = DB::table('supplying_power')            // get name
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id') 
        ->where('supplying_power.mbp_id', $mbp_id)
        ->where('supplying_power.done_status', '0')
        ->where('supplying_power.cancellation_status', '0')
        ->update(
          [
            'supplying_power.date_done' => date('Y-m-d H:i:s'),
            'supplying_power.done_status' =>'1',
            'site.is_allocated' =>'0',
          ]
        );
      }else{

        $res['success'] = false;
        $res['message'] = 'STATUS_NOT_MATCH';

        return response($res);
      }

      // fungsi create new suppliyinf power ============================= ini menyesuaikan statusnya
      // $insertSP = DB::table('supplying_power')
      // ->where('mbp_id', $mbp_id)
      // ->where('done_status', '0')
      // ->where('cancellation_status', '0')
      // ->update(
      //   [
      //     'date_onprogress' => date('Y-m-d H:i:s'),
      //   ]
      // );

      if ($mbp_data && $insertSP) {

        if ($mbp_data->status!='AVAILABLE') {

          $data_mbp_task = DB::table('supplying_power')
        ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')               // get status mbp
        ->join('users', 'supplying_power.user_id', '=', 'users.id')                // get name
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')           // get site name, lat, lon
        ->join('class', 'site.class_id', '=', 'class.class_id')                   // get class name

        ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name','mbp.latitude as mbp_latitude' ,'mbp.longitude as mbp_longitude')
        
        ->where('done_status','=','0') 
        ->where('cancellation_status','=','0')
        ->where('supplying_power.mbp_id', $mbp_id)
        ->first();

        if ($data_mbp_task) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $data_mbp_task;

          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'CANNOT_FIND_DATA';
          // $res['data'] = $data_mbp_task;

          return response($res);
        }  

      }else{
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_data;

        return response($res);
      }

      
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }else{
    $res['success'] = false;
    $res['message'] = 'CANNOT_FIND_DATA';

    return response($res);
  }
}

public function updateStatusMbptoCheckin(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();

  date_default_timezone_set("Asia/Jakarta");
  $mbp_id = $request->input('mbp_id');
  
  $editMbp = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->where('mbp.mbp_id', $mbp_id)
  ->update(['status' => 'CHECK_IN']);

  if ($editMbp) {

    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.status')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->first();

      // fungsi create new suppliyinf power
    $insertSP = DB::table('supplying_power')
    ->where('mbp_id', $mbp_id)
    ->where('done_status', '0')
    ->where('cancellation_status', '0')
    ->update(
      [
        'date_checkin' => date('Y-m-d H:i:s'),
      ]
    );


    if ($mbp_data && $insertSP) {

      if ($mbp_data->status!='AVAILABLE') {

        $data_mbp_task = DB::table('supplying_power')
        ->join('mbp', 'supplying_power.mbp_id', '=', 'mbp.mbp_id')               // get status mbp
        ->join('users', 'supplying_power.user_id', '=', 'users.id')                // get name
        ->join('site', 'supplying_power.site_id', '=', 'site.site_id')           // get site name, lat, lon
        ->join('class', 'site.class_id', '=', 'class.class_id')                   // get class name

        ->select('mbp.status','users.name as rtpo_username','site.site_name','site.latitude','site.longitude','class.class_name')
        
        ->where('done_status','=','0') 
        ->where('cancellation_status','=','0')
        ->where('supplying_power.mbp_id', $mbp_id)
        ->first();

        if ($data_mbp_task) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          $res['data'] = $data_mbp_task;

          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'CANNOT_FIND_DATA';

          return response($res);
        }  

      }else{
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $mbp_data;

        return response($res);
      }

      
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }else{
    $res['success'] = false;
    $res['message'] = 'CANNOT_FIND_DATA';

    return response($res);
  }
}


public function updateStatusMbptoDone(Request $request){
    // $data_site = DB::table('mbp')->select('*')->get();

  date_default_timezone_set("Asia/Jakarta");
  $mbp_id = $request->input('mbp_id');
  
  $editMbp = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->where('mbp.mbp_id', $mbp_id)
  ->update(['status' => 'AVAILABLE']);

  if ($editMbp) {

    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->select('mbp.status')
    ->where('mbp.mbp_id','=',$mbp_id)
    ->get();
  // fungsi create new suppliyinf power
    $insertSP = DB::table('supplying_power')
    ->where('mbp_id', $mbp_id)
    ->where('done_status', '0')
    ->where('cancellation_status', '0')
    ->update(
      [
        'date_done' => date('Y-m-d H:i:s'),
        'done_status' =>'1',
      ]
    );


    if ($mbp_data) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $mbp_data;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'CANNOT_FIND_DATA';

      return response($res);
    }
  }else{
    $res['success'] = false;
    $res['message'] = 'CANNOT_FIND_DATA';

    return response($res);
  }
}

public function getAllMbpOnProggress(Request $request){
    // $data_site = DB::table('mbp')->select('*')->where('status','=','1')->get();
  $data_site = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('status','=','1')
  ->get();

  if ($data_site) {
    $res['success'] = true;
    $res['message'] = 'Success!';
    $res['data'] = $data_site;

    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'Cannot find data!';

    return response($res);
  }

}

public function getMyMbp(Request $request){


  $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
  $data_site = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->get();

  if ($data_site) {
    $res['success'] = true;
    $res['message'] = 'Success!';
    $res['data'] = $data_site;
    
    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'Cannot find data!';
    
    return response($res);
  }

}

public function getMyMbpCategory(Request $request){


  $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->get();
    // $mbp_data = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id);
  $mbp_data = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->get();


    // $data_onprogress = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON_PROGRESS')->get();
  $data_onprogress = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->where('status','=','ON_PROGRESS')
  ->get();

    // $data_waiting = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
  $data_waiting = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->where('status','=','WAITING')
  ->get();

    // $data_available = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
  $data_available = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->where('status','=','AVAILABLE')
  ->get();

    // $data_onprogress->where('status','=','ON_PROGRESS')->get();
    // $data_waiting->where('status','=','WAITING')->get();
    // $data_available->where('status','=','AVAILABLE')->get();

  $data['ON_PROGRESS'] = $data_onprogress;
  $data['WAITING'] = $data_waiting;
  $data['AVAILABLE'] = $data_available;


  if ($mbp_data) {
    $res['success'] = true;
    $res['message'] = 'Success!';
    $res['data'] = $data;
    
    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'Cannot find data!';
    
    return response($res);
  }

}

public function getMyMbpOnProgress(Request $request){


  $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','ON_PROGRESS')->get();
  $data_site = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->where('status','=','ON_PROGRESS')
  ->get();

  if ($data_site) {
    $res['success'] = true;
    $res['message'] = 'Success!';
    $res['data'] = $data_site;

    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'Cannot find data!';

    return response($res);
  }

}

public function getMyMbpAvailable(Request $request){


  $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','AVAILABLE')->get();
  $data_site = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->where('status','=','AVAILABLE')
  ->get();

  if ($data_site) {
    $res['success'] = true;
    $res['message'] = 'Success!';
    $res['data'] = $data_site;

    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'Cannot find data!';

    return response($res);
  }

}

public function getMyMbpWaiting(Request $request){


  $rtpo_id = $request->input('rtpo_id');

    // $data_site = DB::table('mbp')->select('*')->where('rtpo_id','=',$rtpo_id)->where('status','=','WAITING')->get();
  $data_site = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->select('mbp.*','user_mbp.user_id')
  ->where('rtpo_id','=',$rtpo_id)
  ->where('status','=','WAITING')
  ->get();

  if ($data_site) {
    $res['success'] = true;
    $res['message'] = 'Success!';
    $res['data'] = $data_site;

    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'Cannot find data!';

    return response($res);
  }

}

public function updateLatLongMbp(Request $request){

  $mbp_name = $request->input('mbp_name');
  $latitude = $request->input('latitude');
  $longitude = $request->input('longitude');

  $editMbp = DB::table('mbp')
  ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
  ->where('mbp.mbp_name', $mbp_name)
  ->update(
    [
      'latitude' => $latitude,
      'longitude' => $longitude,
    ]
  );

  if ($editMbp) {
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
      // $res['data'] = $editMbp;

    return response($res);
  }else{
    $res['success'] = false;
    $res['message'] = 'CANNOT_FIND_DATA';

    return response($res);
  }
}
}