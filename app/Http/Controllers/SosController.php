<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Bts;
use DB;
class SosController extends Controller
{

  public function getBorrowedMbpList(Request $request){

    $rtpo_id = $request->input('rtpo_id');

    $data_mbp = DB::table('mbp')
    // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    // ->join('users as m', 'user_mbp.user_id', '=', 'm.id')
    // ->join('user_rtpo', 'mbp.rtpo_id', '=', 'user_rtpo.rtpo_id')
    // ->join('users as r', 'user_rtpo.user_id', '=', 'r.id')
    ->join('rtpo', 'mbp.rtpo_id', '=', 'rtpo.rtpo_id')
    // ->select('mbp.*','rtpo.*','m.name as user_mbp_name','r.name as user_rtpo_name')
    ->select('mbp.*','rtpo.*'/*,'m.name as user_mbp_name','r.name as user_rtpo_name'*/)
    ->where('mbp.rtpo_id','=',$rtpo_id)
    ->get();


    $mbp_result = json_decode($data_mbp, true);

    foreach ($mbp_result as $param => $row) {
      // echo "The number is: $x <br>";
      $mbp_loan[$param]['mbp_id'] = $mbp_result[$param]['mbp_id'];
      $mbp_loan[$param]['mbp_name'] = $mbp_result[$param]['mbp_name'];
      $mbp_loan[$param]['rtpo_id'] = $mbp_result[$param]['rtpo_id'];
      $mbp_loan[$param]['rtpo_name'] = $mbp_result[$param]['rtpo_name'];
      $mbp_loan[$param]['mbp_status'] = $mbp_result[$param]['status'];
      if ($mbp_result[$param]['status']=='AVAILABLE') {
        $mbp_loan[$param]['site_id'] = '';
        $mbp_loan[$param]['site_name'] = '';
      }else if($mbp_result[$param]['status']=='UNAVAILABLE'){
        $mbp_loan[$param]['site_id'] = '';
        $mbp_loan[$param]['site_name'] = '';
      }else{
        $mbp_loan[$param]['site_id'] = rand(1,9).'_dummy';
        $mbp_loan[$param]['site_name'] = 'btsProb_dummy';  
      }
      // $sos[$param]['rtpo_id'] = $rtpo_result[$param]['rtpo_id'];
      // $sos[$param]['rtpo_name'] = $rtpo_result[$param]['rtpo_name'];
      // // $sos[$param]['date_time'] = date("d-M-Y H:i:s", strtotime($date_now.''));
      // $sos[$param]['date_time'] = date('d-M-Y H:i:s', strtotime($date_now.' - '.rand(1,9).' hours'));
      // $sos[$param]['needs_mbp'] = rand(1,9);
    } 

    if ($data_mbp) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $mbp_loan;

      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'Cannot find data!';

      return response($res);
    }
  }

  /** fungsi" yang dibutuhkan.. hehee..:D */

  public function sendRequestSOS(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $rtpo_id = $request->input('rtpo_id');
    $need_mbp = $request->input('need_mbp');
    // $status = $request->input('status');

    //pastiken rtpo ini tidak memiliki sos yangs edang aktif
    // 1. cek apakah di tabel sos ada sos yang masih belum aktif? bila masih maka matikan.

    $check_sos_data = DB::table('sos')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=',null)
    ->first();
    if ($check_sos_data==null) {

      $insertSos = DB::table('sos')->insert(
        [
          'rtpo_id' => $rtpo_id, 
          'need_mbp' => $need_mbp,
          'date' => $date_now,
          'status' => NULL,
        ]
      );

      $rtpo_data = DB::table('rtpo')
      ->select('rtpo_id','neighbor')
      ->where('rtpo_id',$rtpo_id)
      ->first();

      $neighbor_data = $rtpo_data->neighbor;
      $neighbor = explode(',',$neighbor_data);

      foreach ($neighbor as $row) {
        //print_r($row.' ');
        $insertAnswer = DB::table('sos_answer')
        ->insert([
          'sos_id' => $insertSos,
          'rtpo_id' => $row,
          'to_rtpo_id' => $rtpo_id,
          'date_created' => $date_now,
          'date_updated' => $date_now,
        ]);
        if (!$insertAnswer) {
          return('ERROR');
        }
      }

      if ($insertSos) {
        $sos_data = DB::table('sos')
      // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->select('*')
        ->where('rtpo_id','=',$rtpo_id)
        ->where('date','=',$date_now)
        ->first();

        if ($sos_data) {

          //$notificationController = new NotificationController;
          //$tmp = $notificationController->setNotificationSendSosAndMbp('SEND_SOS',null,$sos_data->id,$rtpo_id,'');

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
        // $res['data'] = $sos_data;
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_GET_DATA_SOS';
        // $res['data'] = $sos_data;
          return response($res);      
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_DATASOS';
        // $res['data'] = $sos_data;
        return response($res);      
      }
    }else{
        // $res['success'] = false;
        // $res['message'] = 'FAILED_BECAUSE_SOS_DATA_FOUND';

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'FAILED_BECAUSE_SOS_DATA_FOUND';
        return response($res);      
    }

  }
  #saat memanggil getListSOS maka di return juga apakah user masih melakukan permintaan sos atau tidak?
  #bila masih melakukan permintaan sos maka pengajuan sos = true;
  #bila pengajuan sos yang masih aktif tidak ditemukan atau tidak ada pengajuan sos sama sekali maka pengajuan sos = false
  public function getListSOS(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);


    $rtpo_id = $request->input('rtpo_id');

    
    // array_multisort($sos['id'], SORT_ASC, $sos);

    $register = DB::table('sos')
    ->where('date','<',$date2)
    ->update(
      [
          'status' => 'COMPLETED'
      ]
    );


    $check_sos_submission = DB::table('sos')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=',NULL)
    ->first();

    $rtpo_data = DB::table('rtpo')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->first();

    // $scs['check'] = $check_sos_submission ;

      // return response($scs);

    if ($check_sos_submission==NULL) {
      $is_requesting = false;

      $check_sos_submission = DB::table('sos')
      ->select('*')
      ->where('rtpo_id','=',$rtpo_id)
      ->where('status','=',NULL)
      ->first();
      if ($check_sos_submission==NULL) {
        $is_requesting = false;
      }else{
        $is_requesting = true; 
      }

    }else{
      $is_requesting = true;
    }

    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('*')
    // ->where('sos.rtpo_id','!=',$rtpo_id)
    ->where('sos.status','=',NULL)
    ->where('rtpo.regional','=',@$rtpo_data->regional)
    ->orderBy('date', 'desc')
    ->get();

    $result = json_decode($sos_data, true);
    if ($result==null) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['is_requesting'] = $is_requesting;
      $res['data'] = $sos_data;  
      return response($res);
    }


    // $result = json_decode($sos_data, true);
    $count = 0;
    $sos= array();
    foreach ($result as $param => $row) {

      # disini cek apakah ada sos yang expired? kl ada maka di tandai expired dan tidak di tampilkan..:D
      # fungsi expired ada di checkingController = new CheckingController()
      # checkingController->CheckExpiredSos(); ->return['result']->EXPIRED maka 'continue;' atau tidak di tampilkan..:D


      # aktifkan kembali d bawah ini bila expired di aktifkan kembali
      // $checkingController = new CheckingController;
      // $tmp = $checkingController->CheckExpiredSos($result[$param]['id'],$result[$param]['date']);
      // if ($tmp['result']=='EXPIRED') {
      //   continue;
      // }else{
      //   $sos[$count]['id'] = $result[$param]['id'];
      //   $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
      //   $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
      //   $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
      //   $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
      //   $sos[$count]['status'] = $result[$param]['status'];
      //   $sos[$count]['result'] = $tmp['result'];
      //   $count=$count+1;
      // }

      # hapus d bawah ini bila expired di aktifkan kembali
        $sos[$count]['id'] = $result[$param]['id'];
        $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
        $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
        $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
        $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
        $sos[$count]['status'] = $result[$param]['status'];
        // $sos[$count]['result'] = $tmp['result'];
        $count=$count+1;
    } 

    if ($sos_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['is_requesting'] = $is_requesting;
      $res['data'] = $sos;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
        // $res['data'] = $sos_data;
      return response($res);      
    }
  }
  public function getDetilSOS(Request $request){

    $sos_id = $request->input('sos_id');

    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('sos.id','sos.rtpo_id', 'rtpo.rtpo_name', 'sos.need_mbp', 'sos.date', 'sos.status')
    ->where('sos.id','=',$sos_id)
    ->where('sos.status','=',null)
    ->first();

    if ($sos_data) {

      $data['sos_id'] = $sos_data->id;
      $data['rtpo_id'] = $sos_data->rtpo_id;
      $data['rtpo_name'] = $sos_data->rtpo_name;
      $data['need_mbp'] = $sos_data->need_mbp;
      $data['date sos'] = $sos_data->date;

      if ($sos_data->status==null) {
        $data['status'] = 'NOT_COMPLETED';
      }else{
        $data['status'] = $sos_data->status;  
      }
      // $data['mbp_borrowed'] = /*$sos_data->status*/;
      $borrow_data = DB::table('borrow')
      ->join('mbp', 'borrow.mbp_id', '=', 'mbp.mbp_id')
      ->join('rtpo', 'borrow.rtpo_id_from', '=', 'rtpo.rtpo_id')
      ->select('mbp.mbp_id','mbp.mbp_name','rtpo.rtpo_id','rtpo.rtpo_name','borrow.borrowed')
      ->where('borrow.sos_id','=',$sos_id)
      ->where('borrow.returned','=',null)
      ->get();
      // return response($borrow_data);

      $bd = count($borrow_data);
      $snm = $sos_data->need_mbp;
      $needs_now = ($snm-$bd);

      $data['needs_now'] = $needs_now;
      $data['mbp_obtained'] = $bd;

      $result = json_decode($borrow_data, true);
      if ($result==null) {
      $data['mbp_borrowed'] = NULL;
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res); 
      }
      foreach ($result as $param => $row) {
        $mbp_borrow[$param]['mbp_id']  = $row['mbp_id'].'';
        $mbp_borrow[$param]['mbp_name'] = $row['mbp_name'].'';
        $mbp_borrow[$param]['from_rtpo_id'] = $row['rtpo_id'].'';
        $mbp_borrow[$param]['from_rtpo_name'] = $row['rtpo_name'].'';
        $mbp_borrow[$param]['borrowed'] = $row['borrowed'].'';
      }
      $data['mbp_borrowed'] = $mbp_borrow;

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $data;
      return response($res);

    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
      return response($res);      
    }
  }
  public function sendMBPtoRTPOsos(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $sos_id = $request->input('sos_id');
    $rtpo_id_from = $request->input('rtpo_id_from');
    $rtpo_id_to = $request->input('rtpo_id_to');
    $array_mbp = $request->input('array_mbp');
    //$array_mbp = json_encode(urldecode($request->input('array_mbp[]')));
    $by_cpo = @$request->input('by_cpo');

    $all_input = json_encode($request->input());
    $ll = json_decode($all_input, true);

    //$array_mbp = $ll['array_mbp'];

    //print_r($ll['array_mbp']);
    //exit();
    
    //$logging = DB::table('log_login_old')
    //->insert([
    //  'dump_log' => $ll,
    //  'date' => $date_now,
    //]);


    $query_ns = DB::table('lookup_fmc_cluster');
    $query_ns->where('rtpo_id',$rtpo_id_to);
    $lookup_ns = $query_ns->first();

    $ns_id = $lookup_ns->ns_id;
    $regional = $lookup_ns->regional;

    // ngecek berapa kebutuhan mbp skrg 
    $sos_data = DB::table('sos')
    ->select('*')
    ->where('id','=',$sos_id)
    ->where('status','=',NULL)
    ->first();

    $borrow_data = DB::table('borrow')
    ->select('*')
    ->where('sos_id','=',$sos_id)
    ->where('returned','=',null)
    ->count();

    $bd = $borrow_data;
    $snm = $sos_data->need_mbp;
    $needs_now = ($snm-$bd);

    // bila jumlah mbp yang akan di kirim melebihi dari jumlah yang disediakan maka hapus semua mbp yang rtpo_kirim
    $hasil = $needs_now - count($array_mbp);

    if ($hasil>=0) {
      // $res['respon'] = true;
      // disini mulai melakukan insert peminjaman mbp

      $insertSos = false;

      // $res['success'] = false;
      // $res['message'] = $array_mbp;
      // return response($res);

      foreach ($array_mbp as $param => $row) {
        $mbps[$param]  = $row['mbp_id'].''; 

        // disini cek apakah mbp tersebut sudah di kirimkan ke rtpo sos?
        $check_borow = DB::table('borrow')  
        ->where('mbp_id', '=', $row['mbp_id'].'')
        ->where('returned', '=', NULL)
        ->first();

        if ($check_borow==null) {     
          $insertSos = DB::table('borrow')->insert(
            [
              'sos_id' => $sos_id, 
              'mbp_id' => $row['mbp_id'].'',
              'rtpo_id_from' => $rtpo_id_from,
              'rtpo_id_to' => $rtpo_id_to,
              'by_cpo' => @$by_cpo,
              'borrowed' => $date_now,
              'returned' => NULL,
            ]
          );
          if ($insertSos) {

            $editMbp = DB::table('mbp')
            ->where('mbp_id', $row['mbp_id'].'')
            ->update(
              [
                'rtpo_id' => $rtpo_id_to,
                'ns_id' => $ns_id,
                'regional' => $regional,
              ]
            );

            if ($insertSos) {

            }else{
              $res['success'] = false;
              $res['message'] = 'FAILED_UPDATE_TABLE_MBP';
              return response($res);
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_TABLE_BORROW';
            return response($res);
          }
        } 
      }
      if ($insertSos) {

        $sos_data = DB::table('sos')
        ->select('*')
        ->where('id','=',$sos_id)
        ->where('status','=',NULL)
        ->first();

        $borrow_data = DB::table('borrow')
        ->select('*')
        ->where('sos_id','=',$sos_id)
        ->where('returned','=',null)
        ->count();

        $bd = $borrow_data;
        $snm = $sos_data->need_mbp;
        $needs_now = ($snm-$bd);

        if ($hasil==0) {
          $editMbp = DB::table('sos')
          ->where('id','=',$sos_id)
          ->update(
            [
              'status' => 'COMPLETED'
            ]
          );
        }

        $insertAccept = DB::table('sos_answer')
        ->where('sos_id',$sos_id)
        ->where('rtpo_id',$rtpo_id_from)
        ->update([
          'action' => 1,
          'date_updated' => $date_now,
        ]);

        // $notificationController = new NotificationController;
        // $tmp = $notificationController->setNotificationSendSosAndMbp('SEND_MBP',$sos_id,$rtpo_id_from,$rtpo_id_to);

        $notificationController = new NotificationController;
        $tmp = $notificationController->setNotificationSendSosAndMbp('SEND_MBP',$array_mbp,$sos_id,$rtpo_id_from,$rtpo_id_to);
        
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return response($res);
      }else{

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'FAILED_INSERT_TABLE_BORROW_0';
        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'REQUEST_IS_FULL';
      return response($res);
    }
  }
  public function deleteMBPtoRTPOsos(Request $request){

    $sos_id = $request->input('sos_id');
    $rtpo_id = $request->input('rtpo_id');
    $array_mbp=null;

    # pertama cek tabelsosnya ada g?$sos_data = DB::table('sos')
    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('sos.id','sos.rtpo_id', 'rtpo.rtpo_name', 'sos.need_mbp', 'sos.date', 'sos.status')
    ->where('sos.id','=',$sos_id)
    ->where('sos.status','=',null)
    ->first();

    if ($sos_data!=null) {

      # lalu cek apakah ada mbp yang di pinjam?      
      $mbp_data = DB::table('mbp')
      // ->join('mbp', 'borrow.mbp_id', '=', 'mbp.mbp_id')
      ->select('mbp_id','rtpo_id_home')
      ->where('rtpo_id','=',$rtpo_id)
      ->where('rtpo_id_home','!=',$rtpo_id)
      ->get();

      $result = json_decode($mbp_data, true);
      if ($result!=NULL) {
        
        # lalu hapus tabel borrownya dan
        $returned_Mbp = DB::table('borrow')
        ->where('sos_id', $sos_id)
        ->where('returned','=',null)
        ->delete();

        foreach ($result as $param => $row) {

          // $res['success'] = true;
          // $res['message'] = $param;
          // return response($res);

          if ($array_mbp==null) {
            $array_mbp[$param]=array("mbp_id"=>$row['mbp_id']);
          }else{
            // array_push($array_mbp[$param],"mbp_id"=>$row['mbp_id']);
            $array_mbp[$param]=array("mbp_id"=>$row['mbp_id']);
          }

          $returned_Mbp = DB::table('mbp')
          ->where('mbp_id', $row['mbp_id'].'')
          ->update(
            [
              'rtpo_id' => $row['rtpo_id_home'].'',
              'ns_id' => $row['ns_id_home'].'',
              'regional' => $row['regional_home'].'',
            ]
          );
        }

          // $res['success'] = true;
          // $res['message'] = $array_mbp;
          // return response($res);

        # lalu hapus tabel borrownya dan
        $returned_Mbp = DB::table('sos')
        ->where('id', $sos_id)
        ->delete();

        if ($returned_Mbp && $returned_Mbp) {

          $notificationController = new NotificationController;
          $tmp = $notificationController->setNotificationSendSosAndMbp('RTPO_CANCEL_SEND_SOS',$array_mbp,$sos_id,$rtpo_id,'');

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_DELETE_SOS';
          return response($res);
        }  
      }else{
        # lalu hapus tabel borrownya dan
        // $returned_Mbp = DB::table('borrow')
        // ->where('sos_id', $sos_id)
        // ->delete();
        // foreach ($result as $param => $row) {

        //   $returned_Mbp = DB::table('mbp')
        //   ->where('mbp_id', $row['mbp_id'].'')
        //   ->update(
        //     [
        //       'rtpo_id' => $row['rtpo_id_home'].'',
        //     ]
        //   );
        // }
        # lalu hapus tabel borrownya dan
        $returned_Mbp = DB::table('sos')
        ->where('id', $sos_id)
        ->delete();

        if ($returned_Mbp && $returned_Mbp) {

          $notificationController = new NotificationController;
          $tmp = $notificationController->setNotificationSendSosAndMbp('RTPO_CANCEL_SEND_SOS',$array_mbp,$sos_id,$rtpo_id,'');

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_DELETE_SOS';
          return response($res);
        }
      }
    }else{

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['success'] = false;
      $res['wall'] = 'SOS_DATA_NOT_FOUND';
      return response($res);
      # bila ada maka kembalikan terlebih dahulu semua mbp
        # lalu hapus data sosnya
    }
  }
  public function getListBorrowedMbp(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $rtpo_id = $request->input('rtpo_id');

    // $mbp_data = DB::table('mbp')
    // ->select('*')
    // ->where('rtpo_id','=',$rtpo_id)
    // ->where('rtpo_id_home','!=',$rtpo_id)
    // ->get();


    $mbp_data = DB::table('mbp')
    ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
    ->join('users', 'user_mbp.username', '=', 'users.username')
    ->select('mbp.*','users.id as user_id','users.name as operator_name')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('rtpo_id_home','!=',$rtpo_id)
    ->get();

    if ($mbp_data) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $mbp_data;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_MBP';
      return response($res);  
    }
  }
  public function returnedMbp(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $rtpo_id = $request->input('rtpo_id');
    $mbp_id = $request->input('mbp_id');

    $array_mbp[0] = array("mbp_id"=>$mbp_id); 

    // $res['success'] = false;
    // $res['message'] = $array_mbp;
    // return response($res);

    // disini, inshaaAlah bisalah..
    // sebelum mengembalikan, pastikan mbp tersebut adalah mbp pinjaman
    // (where mbp.rtpo_id_home != mbp.rtpo_id) == berarti mbp tersebut adalah mbp pinjaman
    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('rtpo_id_home','!=',$rtpo_id)
    ->where('mbp_id','=',$mbp_id)
    ->first();
    //   jika memang mbp pinjaman, maka 
    if ($mbp_data!=null) {  
      //     cek tabel borrowed ada enggak mbp tersebut? 
      $rtpo_id_home = $mbp_data->rtpo_id_home;
      $regional_home = $mbp_data->regional_home;
      $ns_id_home = $mbp_data->ns_id_home;

      $borrow_data = DB::table('borrow')
      ->join('mbp', 'borrow.mbp_id', 'borrow.mbp_id')
      ->select('borrow.*')
      ->where('borrow.mbp_id','=',$mbp_id)
      ->where('mbp.rtpo_id_home','!=',$rtpo_id)
      ->where('borrow.returned','=',null)
      ->first();

          // $res['success'] = false;
          // $res['message'] = 'FAILED_EDIT_BORROW_AND_MBP';
          // $res['borrow_data'] = $borrow_data;
          // $res['mbp_id'] = $mbp_id;
          // $res['rtpo_id'] = $rtpo_id;
          // return response($res);
      //    bila ada, maka
      if ($borrow_data) {
        //       edit dari tabel borrowed, bila sudah di hapus dari tabel borrowed, maka
        //         edit mbp dimana rtpo_id = rtpo_id_home dan sukses
        $sos_id = $borrow_data->sos_id;
        $edit_borrow_and_mbp = DB::table('borrow')
        ->join('mbp', 'borrow.mbp_id','mbp.mbp_id')
        ->where('borrow.mbp_id','=', $mbp_id)
        ->where('borrow.sos_id','=', $sos_id)
        ->where('borrow.returned','=',null)
        ->update(
          [
            'borrow.returned' => $date_now,
            'mbp.rtpo_id' => $rtpo_id_home,
            'mbp.regional' => $regional_home,
            'mbp.ns_id' => $ns_id_home,
          ]
        );
        if ($edit_borrow_and_mbp) {

          $notificationController = new NotificationController;
          $tmp = $notificationController->setNotificationSendSosAndMbp('RTPO_RETURN_MBP',$array_mbp,$sos_id,$rtpo_id,$rtpo_id_home);
          
          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          // $res['data'] = $mbp_data;
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_EDIT_BORROW_AND_MBP';
          $res['borrow_data'] = $borrow_data;
          $res['mbp_id'] = $mbp_id;
          $res['rtpo_id'] = $rtpo_id;
          return response($res);
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'BORROW_DATA_NOT_FOUND';
        return response($res);
      }
    }else{

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      // $res['success'] = false;
      $res['wall'] = 'MBP_DATA_NOT_FOUND';
      return response($res);
    }
  }
  // jangan lupa rubah db bagian mbpnya ia.. hehee..
  // trus bagian fungsi menampilkan mbp di map, mbp di list mbp, mbp di detil mbp;
  
  public function returnedMbp_fixing(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $rtpo_id = $request->input('rtpo_id');
    $mbp_id = $request->input('mbp_id');

    // disini, inshaaAlah bisalah..
    // sebelum mengembalikan, pastikan mbp tersebut adalah mbp pinjaman
    // (where mbp.rtpo_id_home != mbp.rtpo_id) == berarti mbp tersebut adalah mbp pinjaman
    $mbp_data = DB::table('mbp')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('rtpo_id_home','!=',$rtpo_id)
    ->where('mbp_id','=',$mbp_id)
    ->first();
    //   jika memang mbp pinjaman, maka 
    if ($mbp_data) {  
      //     cek tabel borrowed ada enggak mbp tersebut? 
      $rtpo_id_home = $mbp_data->rtpo_id_home;

      $borrow_data = DB::table('borrow')
      ->join('mbp', 'borrow.mbp_id', 'borrow.mbp_id')
      ->select('borrow.*')
      ->where('borrow.mbp_id','=',$mbp_id)
      ->where('mbp.rtpo_id_home','!=',$rtpo_id)
      ->where('borrow.returned','=',null)
      ->first();

          // $res['success'] = false;
          // $res['message'] = 'FAILED_EDIT_BORROW_AND_MBP';
          // $res['borrow_data'] = $borrow_data;
          // $res['mbp_id'] = $mbp_id;
          // $res['rtpo_id'] = $rtpo_id;
          // return response($res);
      //    bila ada, maka
      if ($borrow_data) {
        //       edit dari tabel borrowed, bila sudah di hapus dari tabel borrowed, maka
        //         edit mbp dimana rtpo_id = rtpo_id_home dan sukses
        $sos_id = $borrow_data->sos_id;
        $edit_borrow_and_mbp = DB::table('borrow')
        ->join('mbp', 'borrow.mbp_id','mbp.mbp_id')
        ->where('borrow.mbp_id','=', $mbp_id)
        ->where('borrow.sos_id','=', $sos_id)
        ->where('borrow.returned','=',null)
        ->update(
          [
            'borrow.returned' => $date_now,
            'mbp.rtpo_id' => $rtpo_id_home,
          ]
        );
        if ($edit_borrow_and_mbp) {

          $notificationController = new NotificationController;
          $tmp = $notificationController->setNotificationSendSosAndMbp('RTPO_RETURN_MBP',$sos_id,$rtpo_id,$rtpo_id_home);

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          // $res['data'] = $mbp_data;
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_EDIT_BORROW_AND_MBP';
          $res['borrow_data'] = $borrow_data;
          $res['mbp_id'] = $mbp_id;
          $res['rtpo_id'] = $rtpo_id;
          return response($res);
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'BORROW_DATA_NOT_FOUND';
        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'MBP_DATA_NOT_FOUND';
      return response($res);
    }    
  }

  public function editSos(Request $request){

    //param : sos_id, jumlah editan
    
    //fungsi untuk mengedit sos bagian jumlah kebutuhannya aja
      //> pastikan bila jumlah yang di inputkan sama dengan jumlah sekarang, maka lemparkan sukses dengan wall inputan sama
      //> bila jumlah inputan lebih sedikit dr skrg dan mbp sudah terisi, maka kembalikan false dengan mbp sudah terisi melibihi permintaan pengurangan kapasitas yang anda ajukan sekarang, kembalikan mbp terlebih dahulu untuk melakukan pengurangan kapasitas
      // selain itu maka ia tinggal di edit aja, sukses..:D

    $sos_id = $request->input('sos_id');
    $need_mbp = $request->input('need_mbp');

    $sos_data = DB::table('sos')
    ->select('*')
    ->where('id','=',$sos_id)
    ->where('status','=',null)
    ->first();
    
    if ($sos_data!=null) {
      if ($sos_data->need_mbp == $need_mbp) {
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'SAME_NEED_MBP';
        return response($res);
      }else if ($sos_data->need_mbp < $need_mbp) {
        $edit_SOS = DB::table('sos')
        ->where('id','=',$sos_id)
        ->update(
          [
            'need_mbp' => $need_mbp
          ]
        );
        if ($edit_SOS) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
          // $res['needs_now'] = $needs_now;
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'EDIT_SOS_FAILED';
          return response($res);
        }
        // success
      }else if ($sos_data->need_mbp > $need_mbp) {
        // cek apakah jumlah mbp yang dibuth

        $borrow_data = DB::table('borrow')
        ->join('mbp', 'borrow.mbp_id', '=', 'mbp.mbp_id')
        ->join('rtpo', 'borrow.rtpo_id_from', '=', 'rtpo.rtpo_id')
        ->select('mbp.mbp_id','mbp.mbp_name','rtpo.rtpo_id','rtpo.rtpo_name','borrow.borrowed')
        ->where('borrow.sos_id','=',$sos_id)
        ->where('borrow.returned','=',null)
        ->get();
      // return response($borrow_data);

        $bd = count($borrow_data);
        $snm = $sos_data->need_mbp;
        $needs_now = ($snm-$bd);

        if ($bd<=$need_mbp) {
          $edit_SOS = DB::table('sos')
          ->where('id','=',$sos_id)
          ->update(
            [
              'need_mbp' => $need_mbp
            ]
          );
          if ($edit_SOS) {

            $sos_data = DB::table('sos')
            ->select('*')
            ->where('id','=',$sos_id)
            ->where('status','=',NULL)
            ->first();

            $borrow_data = DB::table('borrow')
            ->select('*')
            ->where('sos_id','=',$sos_id)
            ->where('returned','=',null)
            ->count();

            $bd = $borrow_data;
            $snm = $sos_data->need_mbp;
            $needs_now = ($snm-$bd);

            if ($needs_now==0) {
              $editMbp = DB::table('sos')
              ->where('id','=',$sos_id)
              ->update(
                [
                  'status' => 'COMPLETED'
                ]
              );
              $res['success'] = true;
              $res['message'] = 'SUCCESS';
              $res['needs_now'] = $needs_now;
              //notif dengan status dan text sos sudah terpenuhi

              return response($res);
            }

            $res['success'] = true;
            $res['message'] = 'SUCCESS';
            $res['needs_now'] = $needs_now;
              //notif dengan status dan text permintaan sos telah diubah
            return response($res);
          }else{
            $res['success'] = false;
            $res['message'] = 'EDIT_SOS_FAILED';
            return response($res);
          }
        }else{
          // tidak bisa di edit karena mbp yang terisi lebih banyak daripada kebutuhan yang anda ingin ubah
          $res['success'] = false;
          $res['message'] = 'MBP_SOS_>_NEED_MBP_NOW';
          $res['needs_now'] = $needs_now;
          $res['bd'] = $bd;
          return response($res);
        }
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'SOS_DATA_NOT_FOUND';
      // $res['wall'] = 'SOS_COMPLETED_OR_EXPIRED';
      return response($res);
    }
  }
  
  public function closedSos(Request $request){

    // cek bila sos sudah expired, maka tampilkan sukses dengan wall sos sudah expired sebelumnya
    // bila belum expired maka ia tinggal di edit ke expired
    $sos_id = $request->input('sos_id');

    $sos_data = DB::table('sos')
    ->select('*')
    ->where('id','=',$sos_id)
    ->where('status','=',null)
    ->first();
    if ($sos_data!=null) {
      $edit_SOS = DB::table('sos')
      ->where('id','=',$sos_id)
      ->update(
        [
          'status' => 'COMPLETED'
        ]
      );
      if ($edit_SOS) { 
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        //notif dengan status dan text sos sudah terpenuhi

        

        return response($res);
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_EDIT_SOS';
        return response($res);
      }
    }else{
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['wall'] = 'SOS_COMPLETED_OR_EXPIRED';
      return response($res);
    }
  }

  public function rejectSos(Request $request)
  {
    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $sos_id = $request->input('sos_id');
    $rtpo_id = $request->input('rtpo_id');
    $alasan = $request->input('alasan');

    $insertReject = DB::table('sos_answer')
    ->where('sos_id',$sos_id)
    ->where('rtpo_id',$rtpo_id)
    ->update([
      'action' => 0,
      'alasan' => @$alasan,
      'date_updated' => $date_now,
    ]);

    if ($insertReject>0) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
    } else{
      $res['success'] = false;
      $res['message'] = 'FAILED (Tidak bisa menolak permintaan!)';
    }
    return response($res);
  }

  public function getListSOSneighbor(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);

    $rtpo_id = $request->input('rtpo_id');

    $register = DB::table('sos')
    ->where('date','<',$date2)
    ->update(
      [
          'status' => 'COMPLETED'
      ]
    );


    $check_sos_submission = DB::table('sos')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=',NULL)
    ->first();

    $rtpo_data = DB::table('rtpo')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->first();

    $neighbor = '('.$rtpo_data->neighbor.')';

    if ($check_sos_submission==NULL) {
      $is_requesting = false;
    }else{
      $is_requesting = true;
    }

    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('*')
    ->whereraw('sos.status is NULL AND sos.rtpo_id in '.$neighbor)
    //->whereraw('sos.rtpo_id in '.$neighbor)
    ->orderBy('date', 'desc')
    ->get();
    
    $result = json_decode($sos_data, true);
    if ($result==null) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['is_requesting'] = $is_requesting;
      $res['data'] = $sos_data;  
      return response($res);
    }

    $count = 0;
    $sos= array();
    foreach ($result as $param => $row) {
        $sos[$count]['id'] = $result[$param]['id'];
        $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
        $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
        $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
        $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
        $sos[$count]['status'] = $result[$param]['status'];
        $count=$count+1;
    } 

    if ($sos_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['is_requesting'] = $is_requesting;
      $res['data'] = @$sos;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
      return response($res);      
    }
  }

  public function CPOgetListSOS(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -30 min");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);


    $regional = $request->input('regional');

    $rtpo_data = DB::table('rtpo')
    ->select('*')
    ->where('regional','=',$regional)
    ->get();

    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('*')
    ->where('sos.status','=',NULL)
    ->where('rtpo.regional','=',$regional)
    ->where('date','<',$date2)
    ->orderBy('date', 'desc')
    ->get();

    $result = json_decode($sos_data, true);
    if ($result==null) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $sos_data;  
      return response($res);
    }


    // $result = json_decode($sos_data, true);
    $count = 0;
    $sos= array();
    foreach ($result as $param => $row) {
      $sosStatus = DB::table('sos_answer')
      ->where('sos_id',$result[$param]['id'])
      ->where('action',null)
      ->count();

      if ($sosStatus>1) {
        $sos[$count]['id'] = $result[$param]['id'];
        $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
        $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
        $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
        $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
        $sos[$count]['status'] = $result[$param]['status'];
        // $sos[$count]['result'] = $tmp['result'];
        $count=$count+1;
      }
    } 

    if ($sos_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $sos;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
        // $res['data'] = $sos_data;
      return response($res);      
    }
  }

  public function CPOgetListRTPOSosAnswer(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -30 min");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);


    $regional = $request->input('regional');
    $sos_id = $request->input('sos_id');

    $list_rtpo = DB::table('sos_answer')
    ->join('rtpo','sos_answer.rtpo_id','rtpo.rtpo_id')
    ->select('rtpo.rtpo_id','rtpo_name','rtpo.regional')
    ->where('sos_id',$sos_id)
    ->whereraw('action is null')
    ->get();

    if ($list_rtpo) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $list_rtpo;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
      return response($res);      
    }

  }

  public function sendRequestSOSneighbor(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $rtpo_id = $request->input('rtpo_id');
    $need_mbp = $request->input('need_mbp');
    // $status = $request->input('status');

    //pastiken rtpo ini tidak memiliki sos yangs edang aktif
    // 1. cek apakah di tabel sos ada sos yang masih belum aktif? bila masih maka matikan.

    $check_sos_data = DB::table('sos')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=',null)
    ->first();
    if ($check_sos_data==null) {

      $insertSos = DB::table('sos')->insertgetid(
        [
          'rtpo_id' => $rtpo_id, 
          'need_mbp' => $need_mbp,
          'date' => $date_now,
          'status' => NULL,
        ]
      );

      $rtpo_data = DB::table('rtpo')
      ->select('rtpo_id','neighbor')
      ->where('rtpo_id',$rtpo_id)
      ->first();

      $neighbor_data = $rtpo_data->neighbor;
      $neighbor = explode(',',$neighbor_data);

      foreach ($neighbor as $row) {
        //print_r($row.' ');
        $insertAnswer = DB::table('sos_answer')
        ->insert([
          'sos_id' => $insertSos,
          'rtpo_id' => $row,
          'to_rtpo_id' => $rtpo_id,
          'date_created' => $date_now,
          'date_updated' => $date_now,
        ]);
        if (!$insertAnswer) {
          return('ERROR');
        }
      }

      if ($insertSos) {
        $sos_data = DB::table('sos')
      // ->join('user_mbp', 'mbp.mbp_id', '=', 'user_mbp.mbp_id')
        ->select('*')
        ->where('rtpo_id','=',$rtpo_id)
        ->where('date','=',$date_now)
        ->first();

        if ($sos_data) {

          $res['success'] = true;
          $res['message'] = 'SUCCESS';
        // $res['data'] = $sos_data;
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'FAILED_GET_DATA_SOS';
        // $res['data'] = $sos_data;
          return response($res);      
        }
      }else{
        $res['success'] = false;
        $res['message'] = 'FAILED_INSERT_DATASOS';
        // $res['data'] = $sos_data;
        return response($res);      
      }
    }else{
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'FAILED_BECAUSE_SOS_DATA_FOUND';
        return response($res);      
    }

  }

  public function getListSOSPaginate(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -2 day");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);


    $rtpo_id = $request->input('rtpo_id');

    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    
    // array_multisort($sos['id'], SORT_ASC, $sos);

    $register = DB::table('sos')
    ->where('date','<',$date2)
    ->update(
      [
          'status' => 'COMPLETED'
      ]
    );


    $check_sos_submission = DB::table('sos')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->where('status','=',NULL)
    ->first();

    $rtpo_data = DB::table('rtpo')
    ->select('*')
    ->where('rtpo_id','=',$rtpo_id)
    ->first();

    // $scs['check'] = $check_sos_submission ;

      // return response($scs);

    if ($check_sos_submission==NULL) {
      $is_requesting = false;

      $check_sos_submission = DB::table('sos')
      ->select('*')
      ->where('rtpo_id','=',$rtpo_id)
      ->where('status','=',NULL)
      ->first();
      if ($check_sos_submission==NULL) {
        $is_requesting = false;
      }else{
        $is_requesting = true; 
      }

    }else{
      $is_requesting = true;
    }

    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('*')
    // ->where('sos.rtpo_id','!=',$rtpo_id)
    ->where('sos.status','=',NULL)
    ->where('rtpo.regional','=',@$rtpo_data->regional)
    ->offset($offset)
    ->limit($limit)
    ->orderBy('date', 'desc')
    ->get();

    $result = json_decode($sos_data, true);
    if ($result==null) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['is_requesting'] = $is_requesting;
      $res['data'] = $sos_data;  
      return response($res);
    }


    // $result = json_decode($sos_data, true);
    $count = 0;
    $sos= array();
    foreach ($result as $param => $row) {

      # disini cek apakah ada sos yang expired? kl ada maka di tandai expired dan tidak di tampilkan..:D
      # fungsi expired ada di checkingController = new CheckingController()
      # checkingController->CheckExpiredSos(); ->return['result']->EXPIRED maka 'continue;' atau tidak di tampilkan..:D


      # aktifkan kembali d bawah ini bila expired di aktifkan kembali
      // $checkingController = new CheckingController;
      // $tmp = $checkingController->CheckExpiredSos($result[$param]['id'],$result[$param]['date']);
      // if ($tmp['result']=='EXPIRED') {
      //   continue;
      // }else{
      //   $sos[$count]['id'] = $result[$param]['id'];
      //   $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
      //   $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
      //   $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
      //   $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
      //   $sos[$count]['status'] = $result[$param]['status'];
      //   $sos[$count]['result'] = $tmp['result'];
      //   $count=$count+1;
      // }

      # hapus d bawah ini bila expired di aktifkan kembali
        $sos[$count]['id'] = $result[$param]['id'];
        $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
        $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
        $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
        $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
        $sos[$count]['status'] = $result[$param]['status'];
        // $sos[$count]['result'] = $tmp['result'];
        $count=$count+1;
    } 

    if ($sos_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['is_requesting'] = $is_requesting;
      $res['data'] = $sos;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
        // $res['data'] = $sos_data;
      return response($res);      
    }
  }

  public function CPOgetListSOSPaginate(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');
    $date_strtotime = strtotime($date_now." -30 min");
    $date2 = date('Y-m-d H:i:s',$date_strtotime);


    $regional = $request->input('regional');

    $page = $request->input('page');

    $limit = 20;
    $offset = ($page-1)*$limit;

    $rtpo_data = DB::table('rtpo')
    ->select('*')
    ->where('regional','=',$regional)
    ->get();

    $sos_data = DB::table('sos')
    ->join('rtpo', 'sos.rtpo_id', '=', 'rtpo.rtpo_id')
    ->select('*')
    ->where('sos.status','=',NULL)
    ->where('rtpo.regional','=',$regional)
    ->where('date','<',$date2)
    ->offset($offset)
    ->limit($limit)
    ->orderBy('date', 'desc')
    ->get();

    $result = json_decode($sos_data, true);
    if ($result==null) {
      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $sos_data;  
      return response($res);
    }


    // $result = json_decode($sos_data, true);
    $count = 0;
    $sos= array();
    foreach ($result as $param => $row) {
      $sosStatus = DB::table('sos_answer')
      ->where('sos_id',$result[$param]['id'])
      ->where('action',null)
      ->count();

      if ($sosStatus>1) {
        $sos[$count]['id'] = $result[$param]['id'];
        $sos[$count]['rtpo_id'] = $result[$param]['rtpo_id'];
        $sos[$count]['rtpo_name'] = $result[$param]['rtpo_name'];
        $sos[$count]['date_time'] = date('d M Y, H:i', strtotime($result[$param]['date']));
        $sos[$count]['needs_mbp'] = $result[$param]['need_mbp'];
        $sos[$count]['status'] = $result[$param]['status'];
        // $sos[$count]['result'] = $tmp['result'];
        $count=$count+1;
      }
    } 

    if ($sos_data) {

      $res['success'] = true;
      $res['message'] = 'SUCCESS';
      $res['data'] = $sos;
      return response($res);
    }else{
      $res['success'] = false;
      $res['message'] = 'FAILED_GET_DATA_SOS';
        // $res['data'] = $sos_data;
      return response($res);      
    }
  }

  public function sendMBPtoRTPOsosNew(Request $request){

    date_default_timezone_set("Asia/Jakarta");
    $date_now =date('Y-m-d H:i:s');

    $sos_id = $request->input('sos_id');
    $rtpo_id_from = $request->input('rtpo_id_from');
    $rtpo_id_to = $request->input('rtpo_id_to');
    //$array_mbp = $request->input('array_mbp');
    $array_mbp = json_encode(urldecode($request->input('array_mbp[]')));
    $by_cpo = @$request->input('by_cpo');

    $all_input = json_encode($request->input());
    $ll = json_decode($all_input, true);

    $array_mbp = $ll['array_mbp'];

    //print_r($ll['array_mbp']);
    //exit();
    
    //$logging = DB::table('log_login_old')
    //->insert([
    //  'dump_log' => $ll,
    //  'date' => $date_now,
    //]);


    $query_ns = DB::table('lookup_fmc_cluster');
    $query_ns->where('rtpo_id',$rtpo_id_to);
    $lookup_ns = $query_ns->first();

    $ns_id = $lookup_ns->ns_id;
    $regional = $lookup_ns->regional;

    // ngecek berapa kebutuhan mbp skrg 
    $sos_data = DB::table('sos')
    ->select('*')
    ->where('id','=',$sos_id)
    ->where('status','=',NULL)
    ->first();

    $borrow_data = DB::table('borrow')
    ->select('*')
    ->where('sos_id','=',$sos_id)
    ->where('returned','=',null)
    ->count();

    $bd = $borrow_data;
    $snm = $sos_data->need_mbp;
    $needs_now = ($snm-$bd);

    // bila jumlah mbp yang akan di kirim melebihi dari jumlah yang disediakan maka hapus semua mbp yang rtpo_kirim
    $hasil = $needs_now - count($array_mbp);

    if ($hasil>=0) {
      // $res['respon'] = true;
      // disini mulai melakukan insert peminjaman mbp

      $insertSos = false;

      // $res['success'] = false;
      // $res['message'] = $array_mbp;
      // return response($res);

      foreach ($array_mbp as $param => $row) {
        $mbps[]['mbp_id']  = $row; 

        // disini cek apakah mbp tersebut sudah di kirimkan ke rtpo sos?
        $check_borow = DB::table('borrow')  
        ->where('mbp_id', '=', $row)
        ->where('returned', '=', NULL)
        ->first();

        if ($check_borow==null) {     
          $insertSos = DB::table('borrow')->insert(
            [
              'sos_id' => $sos_id, 
              'mbp_id' => $row,
              'rtpo_id_from' => $rtpo_id_from,
              'rtpo_id_to' => $rtpo_id_to,
              'by_cpo' => @$by_cpo,
              'borrowed' => $date_now,
              'returned' => NULL,
            ]
          );
          if ($insertSos) {

            $editMbp = DB::table('mbp')
            ->where('mbp_id', $row)
            ->update(
              [
                'rtpo_id' => $rtpo_id_to,
                'ns_id' => $ns_id,
                'regional' => $regional,
              ]
            );

            if ($insertSos) {

            }else{
              $res['success'] = false;
              $res['message'] = 'FAILED_UPDATE_TABLE_MBP';
              return response($res);
            }
          }else{
            $res['success'] = false;
            $res['message'] = 'FAILED_INSERT_TABLE_BORROW';
            return response($res);
          }
        } 
      }
      if ($insertSos) {

        $sos_data = DB::table('sos')
        ->select('*')
        ->where('id','=',$sos_id)
        ->where('status','=',NULL)
        ->first();

        $borrow_data = DB::table('borrow')
        ->select('*')
        ->where('sos_id','=',$sos_id)
        ->where('returned','=',null)
        ->count();

        $bd = $borrow_data;
        $snm = $sos_data->need_mbp;
        $needs_now = ($snm-$bd);

        if ($hasil==0) {
          $editMbp = DB::table('sos')
          ->where('id','=',$sos_id)
          ->update(
            [
              'status' => 'COMPLETED'
            ]
          );
        }

        $insertAccept = DB::table('sos_answer')
        ->where('sos_id',$sos_id)
        ->where('rtpo_id',$rtpo_id_from)
        ->update([
          'action' => 1,
          'date_updated' => $date_now,
        ]);

        // $notificationController = new NotificationController;
        // $tmp = $notificationController->setNotificationSendSosAndMbp('SEND_MBP',$sos_id,$rtpo_id_from,$rtpo_id_to);

        //print_r($mbps);
        //exit();

        $notificationController = new NotificationController;
        $tmp = $notificationController->setNotificationSendSosAndMbp('SEND_MBP',$mbps,$sos_id,$rtpo_id_from,$rtpo_id_to);
        
        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        return response($res);
      }else{

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['wall'] = 'FAILED_INSERT_TABLE_BORROW_0';
        return response($res);
      }
    }else{
      $res['success'] = false;
      $res['message'] = 'REQUEST_IS_FULL';
      return response($res);
    }
  }

}