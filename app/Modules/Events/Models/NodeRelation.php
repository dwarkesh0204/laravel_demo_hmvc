<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class NodeRelation extends Model
{
    protected $fillable = ['id', 'node_id', 'rel_node_id', 'distance', 'floorplan_id'];
    protected $table    = 'node_relation';
    //protected $connection = 'ips_mysql';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
