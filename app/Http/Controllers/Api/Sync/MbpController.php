<?php
namespace App\Http\Controllers\Api\Sync;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use DB;
// use App\Jobs\SendNotification;
use App\Http\Controllers\Controller;
use DateTime;
use App\User;
use App\Libraries\SendNotifLib;
use App\Helpers\AppHelper;

class MbpController extends Controller {


  public function mbp_update(Request $request){


    $mbp_data = $request->input('data');
    // print_r($_POST);
    // exit;
	  foreach ($mbp_data as $param => $row) {
      
      $master_mbp_data = DB::table('mbp')->where('mbp_id',@$row['mbp_id'])->first();

      $lookup_ns = DB::table('lookup_fmc_cluster')->where(['rtpo_id'=>@$row['rtpo_id'], 'status'=>1])->first();
      
      if(!$lookup_ns){
        $res['success'] = false;
        $res['message'] = 'NS_NOT_FOUND';
        return response($res);
      }
      
      $ns_id = $lookup_ns->ns_id;
      $mbp_data = [
        'mbp_id' => @$row['mbp_id'],
        'mbp_name' => @$row['mbp_name'],
        'jenis_rh' => @$row['jenis_rh'],
        'merk_mbp' => @$row['merk_mbp'],
        'kapasitas' => @$row['kapasitas'],


        'fmc_id' => @$row['fmc_id'],
        'fmc' => @$row['fmc'],
        'cluster_id' => @$row['cluster_id'],
        'cluster' => @$row['cluster'],
        'active' => @$row['status'],
        'created_by' =>@ $row['created_by'],
        'date_created' => @$row['date_created'],
        'update_by' => @$row['update_by'],
        'last_update' => @$row['last_update'],
        
        'rtpo_id_home' => $row['rtpo_id'],
        'rtpo_id' => @$row['rtpo_id'],
        
        'ns_id_home' => @$ns_id,
        'ns' => @$lookup_ns->ns,
        'ns_id' => @$ns_id,

        'regional' => @$row['regional'],
        'regional_home' => @$row['regional'],
      ];

      // return $row['mbp_id'];
      // exit;
      if ($master_mbp_data) {


        unset($mbp_data['mbp_id']);
        unset($mbp_data['ns_id']);
        unset($mbp_data['rtpo_id']);
        unset($mbp_data['regional']);
        DB::table('mbp')->where('mbp_id',@$row['mbp_id'])->update($mbp_data);
        
      } else{
        $insertMasterMbp = DB::table('mbp')->insert($mbp_data);
        if (!$insertMasterMbp) {
          $res['success'] = false;
          $res['message'] = 'FAILED_INSERT_DATA_MBP';
          $res['data'] = $row;
          return response($res);
        }
      }
    }
    $res['success'] = true;
    $res['message'] = 'SUCCESS';
    return response($res);
  }

}