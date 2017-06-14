<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EventTicketsUser extends Model
{
    protected $table    = 'events_tickets_user';
    protected $fillable = ['ticket_id','event_id ','user_id'];
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
