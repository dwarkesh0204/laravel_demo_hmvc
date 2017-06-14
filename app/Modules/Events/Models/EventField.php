<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EventField extends Model
{
    protected $fillable = ['event_id', ' display_name', 'field_name','field_type','items','multiple','active','required'];
    protected $table = 'event_fields';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
