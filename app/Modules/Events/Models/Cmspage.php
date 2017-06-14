<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Cmspage extends Model
{
	protected $fillable = ['cat_id', 'display_name', 'name', 'description', 'active', 'updated_at', 'created_at'];
	protected $table    = 'cmspage';

	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
