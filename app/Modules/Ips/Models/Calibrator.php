<?php

namespace App\Modules\Ips\Models;

use Illuminate\Database\Eloquent\Model;

class Calibrator extends Model
{
    protected $fillable = ['name','floorplan_id','node_X', 'node_Y', 'display_X', 'display_Y'];
    protected $table    = 'calibrator';
    //protected $connection = 'ips_mysql';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_ips_database_conn');
    }
}
