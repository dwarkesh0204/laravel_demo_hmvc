<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Sessions extends Model
{
	protected $fillable = ['name', 'description', 'status', 'updated_at', 'created_at','start_time','end_time','event_id','date','capacity','type','venue'];
	protected $table    = 'sessions';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @des RELATIONSHIP FROM SESSION TO USER WITH ONE
	 */
	public function user()
	{
		return $this->hasOne('App\Modules\Events\Models\User', 'id', 'speaker_id');
	}

	public function session_sessions_speakers()
	{
		return $this->hasMany('App\Modules\Events\Models\SessionsSpeakers', 'session_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 * @des RELATIONSHIP FROM POLLING QUESTION TO SESSION WITH MANY
	 */
	public function session_polling_question()
	{
		return $this->hasMany('App\Modules\Events\Models\PollingQuestion', 'session_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 * @des RELATIONSHIP FROM FEEDBACK TO SESSION WITH MANY
	 */
	public function session_feedback()
	{
		return $this->hasMany('App\Modules\Events\Models\Feedback', 'session_id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 * @des RELATIONSHIP FROM QUESTION TO SESSION WITH MANY
	 */
	public function session_question()
	{
		return $this->hasMany('App\Modules\Events\Models\Question', 'session_id');
	}


	/**
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 * @des RELATIONSHIP FROM SESSION TO EVENT WITH ONE
	 */
	public function events()
	{
		return $this->hasOne('App\Modules\Events\Models\Events', 'eventid', 'event_id');
	}

	public function sessions_user_request_for_session()
    {
        return $this->hasMany('App\Modules\Events\Models\UserRequestForSession', 'session_id');
    }
}
