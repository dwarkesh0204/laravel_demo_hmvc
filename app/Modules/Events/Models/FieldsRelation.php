<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class FieldsRelation extends Model
{
    protected $fillable = ['role_id', 'event_id', 'field_id', 'updated_at', 'created_at'];
	protected $table    = 'fields_relation';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
