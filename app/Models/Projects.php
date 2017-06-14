<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    protected $fillable = ['user_id','title', 'type', 'secret', 'db', 'db_user', 'db_password','description', 'status', 'created_at', 'updated_at'];
	protected $table    = 'projects';
    protected $connection = "mysql";
}
