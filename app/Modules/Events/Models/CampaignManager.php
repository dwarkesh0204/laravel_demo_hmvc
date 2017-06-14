<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignManager extends Model
{
    protected $fillable = ['title', 'description', 'status'];
	protected $table    = 'campaign_managers';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
