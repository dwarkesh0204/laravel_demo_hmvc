<?php

namespace App\Modules\Beaconmanager\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacture extends Model
{
    protected $fillable = ['title', 'description','mac_series', 'status', 'created_at', 'updated_at'];
    protected $table    = 'manufacturer';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_bm_database_conn');
    }
}
