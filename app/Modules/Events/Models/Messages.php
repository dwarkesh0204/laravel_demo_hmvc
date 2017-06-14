<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
   	protected $fillable   = ['user_id', 'to_user_id', 'event_id', 'message', 'updated_at', 'created_at'];
    protected $table = 'messages';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
