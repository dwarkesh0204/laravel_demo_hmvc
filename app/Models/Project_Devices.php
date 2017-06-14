<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project_Devices extends Model
{
    protected $fillable = ['project_id','device_id', 'mac_address', 'created_at', 'updated_at'];
    protected $table    = 'project_devices';
    protected $connection = "mysql";
}
