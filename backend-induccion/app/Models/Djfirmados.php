<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Djfirmados extends Model
{
    protected $table = 'djfirmados';
    protected $connection = 'induccion';
    protected $primaryKey = 'id'; 

    protected $fillable = ['iduser', 'file_name', 'file_url', 'created_at', 'updated_at'];

}
