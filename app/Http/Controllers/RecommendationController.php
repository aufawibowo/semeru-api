<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use DB;
class RecommendationController extends Controller
{
    public function calculateDistance(Request $request){


      // $latitude1 = -7.2574719;
      // $longitude1 = 112.7520883;

      // $latitude2 = -7.943795;
      // $longitude2 = 112.659256;


      $latitude1 = $request->input('latitude1');
      $longitude1 = $request->input('longitude1');

      $latitude2 = $request->input('latitude2');
      $longitude2 = $request->input('longitude2');

      $dataJson = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=".$latitude1.",".$longitude1."&destinations=".$latitude2.",".$longitude2."&key=AIzaSyB_Zn_RnqmIhhIu75Fay1RIOZJXV5C1n6U");

      $data = json_decode($dataJson,true);
      $data_traffic['distance'] = $data['rows'][0]['elements'][0]['distance']['text'];
      $data_traffic['duration'] = $data['rows'][0]['elements'][0]['duration']['text'];

      // $nilaiJarak = $data['rows'][0]['elements'][0]['distance']['text'];
      // $nilaiDurasi = $data['rows'][0]['elements'][0]['duration']['text'];

        if ($dataJson) {
          $res['success'] = true;
          $res['message'] = 'Success!';
          $res['data'] = $data_traffic;
          // $res['durasi'] = $nilaiDurasi;
        
          return response($res);
        }else{
          $res['success'] = false;
          $res['message'] = 'Cannot find route!';
        
          return response($res);
        }
    }
}