<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class SessionsSpeakers extends Model
{
    protected $fillable = ['session_id', 'speaker_id'];
    protected $table    = 'sessions_speakers';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    public function sessions_speakers_session()
    {
        return $this->hasMany('App\Modules\Events\Models\Sessions', 'id');
    }

    public function sessions_speakers_users()
    {
        return $this->hasOne('App\Modules\Events\Models\User', 'id', 'speaker_id');
    }
}
