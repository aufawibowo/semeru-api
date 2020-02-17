<?php
namespace App\Http\Controllers\Api;

// use Freshdesk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use DB;
use App\Http\Controllers\Controller;
use DateTime;
use App\User;
use App\Helpers\AppHelper;

class FirebaseController extends Controller {

    public function send_notif(Request $req){
        //tanpa sleep 104sms
        sleep(3);//3.09s
        $res = [
            'success'=>false,
            'data'=>@$req->notif_id,
            'message'=>'Failed to sending notification'
        ];
        return response($res);
    }

}