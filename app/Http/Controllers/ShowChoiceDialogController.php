<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use App\Bts;
use DB;
class showChoiceDialogController extends Controller
{
	

	public function ListChoiceDialog(Request $request){

		// $latitude1 = $request->input('latitude1');

		#DATA FROM JAVA
		$choiceData['OK_NOTOK'] = $OK_NOTOK = array("OK", "NOT OK");
		$choiceData['OK_NOTOK_NA'] = $OK_NOTOK_NA = array("OK", "NOT OK", "N/A");
		$choiceData['BAIK_RUSAK'] = $BAIK_RUSAK = array("BAIK", "RUSAK");
		$choiceData['BAIK_RUSAK_NA'] = $BAIK_RUSAK_NA = array("BAIK", "RUSAK", "N/A");
		$choiceData['BERSIH_KOTOR'] = $BERSIH_KOTOR = array("BERSIH", "KOTOR");
		$choiceData['BOCOR_TIDAK'] = $BOCOR_TIDAK = array("TIDAK", "BOCOR");
		$choiceData['YA_TIDAK'] = $YA_TIDAK = array("YA", "TIDAK");
		$choiceData['ON_OFF'] = $ON_OFF = array("ON", "OFF");
		$choiceData['ADA_TIDAK'] = $ADA_TIDAK = array("ADA", "TIDAK");
		$choiceData['PHASE'] = $PHASE = array("R", "S", "T");
		$choiceData['CATUAN_UTAMA'] = $CATUAN_UTAMA = array("PLN", "NON PLN");
		$choiceData['TOWER_STATUS'] = $TOWER_STATUS = array("TELKOMSEL", "SEWA");
		$choiceData['TOWER_TYPE'] = $TOWER_TYPE = array("SST", "MONOPOLE");
		$choiceData['JENIS_CATUAN'] = $JENIS_CATUAN = array("REGULAR", "MULTIGUNA", "BUILDING", "KONSORSIUM", "TELKOM", "GENSET", "SOLAR CELL", "MICRO HYDRO", "SEWA DAYA");
		$choiceData['MERK_AC'] = $MERK_AC = array("DAIKIN", "PANASONIC", "SAMSUNG", "LG", "CHANGHONG", "SANYO", "SHARP", "TOSHIBA", "ELECTROLUX", "NATIONAL", "MASPION");
		$choiceData['MAIN_BACKUP'] = $MAIN_BACKUP = array("MAIN", "BACKUP");
		$choiceData['CONTROLLER'] = $CONTROLLER = array("DIGITAL", "ANALOG");
		$choiceData['NOMOR_SHELTER'] = $NOMOR_SHELTER = array("1", "2", "3", "4", "5");
		$choiceData['NOMOR_RECTIFIER'] = $NOMOR_RECTIFIER = array("1", "2", "3", "4", "5", "6", "7", "8");
		$choiceData['SAMPLING_RECTIFIER'] = $SAMPLING_RECTIFIER = array("1", "2", "3", "4", "5", "6", "7", "8", "All");
		$choiceData['NOMOR_AC'] = $NOMOR_AC = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10");
		$choiceData['BANK'] = $BANK = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15");
		$choiceData['NOMOR_MCB'] = $NOMOR_MCB = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31", "32", "33", "34", "35", "36", "37", "38", "39", "40");
		$choiceData['KAPASITAS_MCB'] = $KAPASITAS_MCB = array("2", "6", "10", "16", "20", "25", "32", "40", "50", "63", "80", "83", "100", "125", "200", "250");

		#DATA FROM XML belum ada
		$choiceData['NAMA_BEBAN'] = $NAMA_BEBAN = array();
		$choiceData['JENIS_RECTIFIER'] = $JENIS_RECTIFIER = array();
		$choiceData['MERK_BATERY'] = $MERK_BATERY = array();


		$res['success'] = true;
		$res['message'] = 'Success!';
		$res['data'] = $choiceData;

		return response($res);

		// if ($dataJson) {
		// 	$res['success'] = true;
		// 	$res['message'] = 'Success!';
		// 	$res['data'] = $choiceData;

		// 	return response($res);
		// }else{
		// 	$res['success'] = false;
		// 	$res['message'] = 'Cannot find route!';

		// 	return response($res);
		// }
	}

}