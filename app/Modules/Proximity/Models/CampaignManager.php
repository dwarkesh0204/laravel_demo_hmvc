<?php

namespace App\Modules\Proximity\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignManager extends Model
{
    protected $fillable = ['title', 'description', 'status'];
	protected $table    = 'campaign_managers';
	protected $connection;

    public function __construct() {
        /*$pid = \Session::get('project_conn_id');
        \Session::get('project_conn'.$pid);
        $this -> connection = \Session::get('project_conn'.$pid);*/
        $this -> connection = \Session::get('mysql_proximity_database_conn');
    }
}
