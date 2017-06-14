<?php
namespace App\Modules\Ips\Models;

use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Floorplan extends Eloquent
{
    protected $table    = 'floorplan';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_ips_database_conn');
    }
}
