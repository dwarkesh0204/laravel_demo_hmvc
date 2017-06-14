<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class MyAgenda extends Model
{
    protected $fillable = [
    'user_id', 'event_id', 'session_id', 'exhibitor_id', 'note_text', 'date', 'time', 'meetup_id', 'type'
    ];

    protected $table = 'my_agenda';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
