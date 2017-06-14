<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class UserRequestForSession extends Model
{
    protected $fillable = [
    'session_id', 'user_id', 'status'
    ];

    protected $table = 'user_request_for_session';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    public function user_request_for_session_session()
	{
		return $this->hasOne('App\Modules\Events\Models\Sessions', 'id', 'session_id');
	}

	public function user_request_for_session_user()
	{
		return $this->hasOne('App\Modules\Events\Models\User', 'id', 'user_id');
	}
}
