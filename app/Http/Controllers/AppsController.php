<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class AppsController extends Controller
{
	public function __construct(){
        date_default_timezone_set("Asia/Jakarta");
        $this->current_date_time = date('Y-m-d H:i:s');
        $this->current_date = date('Y-m-d');
        $this->current_year = date('Y');
        $this->current_month = date('m');
    }

    public function downloadApk(Request $request)
    {
    	$link = DB::table('verion_app')
    	->select('download_url')
    	->orderBy('version_id','desc')
    	->first();

    	return redirect($link->download_url);
    }

    public function getListFaq(Request $request)
    {
        $kategori_id = $request->input('kategori_id');

        $data_faq = DB::table('faq')
        ->select('*')
        ->where('kategori_id',$kategori_id)
        ->get();

        $res['success'] = true;
        $res['message'] = 'SUCCESS';
        $res['data'] = $data_faq;

        return response($res);
    }
}