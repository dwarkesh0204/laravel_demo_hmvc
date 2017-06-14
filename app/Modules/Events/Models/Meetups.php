<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Meetups extends Model
{
    protected $fillable   = ['user_id', 'to_user_id', 'event_id', 'meetup_agenda', 'status', 'date', 'time', 'updated_at', 'created_at'];
    protected $table = 'meetups';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
