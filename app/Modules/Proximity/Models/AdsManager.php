<?php

namespace App\Modules\Proximity\Models;

use Illuminate\Database\Eloquent\Model;

class AdsManager extends Model
{
    protected $fillable = ['campaign_manager_id', 'beacon_id', 'title', 'description', 'link', 'start_date', 'end_date', 'start_time', 'end_time', 'image','status'];
	protected $table    = 'ads_managers';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_proximity_database_conn');
    }
}
