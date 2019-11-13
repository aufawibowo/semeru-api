<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ConcernImage;

class Concern extends Model
{
	protected $table = "concern";
	protected $primaryKey = 'id';

	public function getImage(){
        return $this->hasMany(ConcernImage::class,'concern_id');
    }
}


 ?>
