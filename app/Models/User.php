<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
	protected $table = "users";
	public $incrementing = false;//WAJIB atur false jika key bukan auto increment
	protected $primaryKey = 'id';
}


 ?>
