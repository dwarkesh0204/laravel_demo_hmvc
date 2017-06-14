<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class UserEvents extends Model
{
	protected $fillable = [
    'user_id', 'event_id','status'
    ];

    protected $table = 'user_events';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @des RELATIONSHIP FROM SESSION TO USER WITH ONE
	 */
	public function userevent_user()
	{
		return $this->hasOne('App\Modules\Events\Models\User', 'id', 'user_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @des RELATIONSHIP FROM SESSION TO USER WITH ONE
	 */
	public function userevent_event()
	{
		return $this->hasOne('App\Modules\Events\Models\Events', 'eventid', 'event_id');
	}
}
