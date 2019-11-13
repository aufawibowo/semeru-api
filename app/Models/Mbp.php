<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mbp extends Model
{
	protected $table = "mbp";
	public $incrementing = false;//WAJIB atur false jika key bukan auto increment
	protected $primaryKey = 'mbp_id';
}


 ?>
