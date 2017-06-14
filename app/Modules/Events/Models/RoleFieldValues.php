<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class RoleFieldValues extends Model
{
	protected $fillable = ['user_id', 'field_id', 'role_id', 'event_id', 'value', 'is_privacy'];
	protected $table    = 'role_field_values';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
