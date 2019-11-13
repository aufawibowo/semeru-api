<?php 

namespace App\Helpers;

class AppHelper{

	public static function bulan_tahun_indo($periode)
	{
		list($y, $m) = explode('-', $periode);

		$bulan = [
		   '',
		   'Januari',
		   'Februari',
		   'Maret',
		   'April',
		   'Mei',
		   'Juni',
		   'Juli',
		   'Agustus',
		   'September',
		   'Oktober',
		   'November',
		   'Desember',
		];
  		return @$bulan[(int)$m].' '.$y;
	}





}

?>