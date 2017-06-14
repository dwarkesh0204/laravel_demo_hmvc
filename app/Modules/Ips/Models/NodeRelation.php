<?php

namespace App\Modules\Ips\Models;

use Illuminate\Database\Eloquent\Model;

class NodeRelation extends Model
{
    protected $fillable = ['id', 'node_id', 'rel_node_id', 'distance', 'floorplan_id'];
    protected $table    = 'node_relation';
    //protected $connection = 'ips_mysql';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_ips_database_conn');
    }
}
