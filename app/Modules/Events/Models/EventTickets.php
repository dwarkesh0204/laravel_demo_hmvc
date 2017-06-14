<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EventTickets extends Model
{
    protected $table    = 'events_tickets';
    protected $fillable = ['event_id', 'title', 'short_desc','full_desc','ticket_price','ticket_currency','ticket_image','status'];
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @des RELATIONSHIP FROM POLL QUESTION TO POLL ANSWER WITH MANY
     */
    public function eventtickets_events()
    {
        return $this->hasMany('App\Modules\Events\Models\Events', 'eventid');
    }
}
