<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Fields extends Model
{
	protected $fillable = ['display_name', 'field_name', 'field_type', 'items', 'default', 'validation_rule', 'multiple', 'active', 'required', 'searchable', 'filterable', 'privacy', 'type', 'group_id', 'updated_at', 'created_at'];
	protected $table    = 'fields';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
