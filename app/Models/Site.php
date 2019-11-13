<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
	protected $table = "site";
	public $incrementing = false;//WAJIB atur false jika key bukan auto increment
	protected $primaryKey = 'site_id';
}


 ?>
