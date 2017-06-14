<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class PollingQuestion extends Model
{
    protected $fillable = ['question', 'session_id', 'status'];
    protected $table    = 'poll_question';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @des RELATIONSHIP FROM POLL QUESTION TO POLL ANSWER WITH MANY
     */
    public function poll_answers()
    {
        return $this->hasMany('App\Modules\Events\Models\PollingAnswer', 'question_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des RELATIONSHIP FROM POLL QUESTION TO SESSION WITH ONE
     */
    public function question_session()
    {
        return $this->hasOne('App\Modules\Events\Models\Sessions','id','session_id');
    }
}
