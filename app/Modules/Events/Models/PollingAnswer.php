<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class PollingAnswer extends Model
{
    protected $fillable = ['answer', 'question_id', 'session_id', 'answer_index', 'status'];
    protected $table    = 'poll_answer';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des RELATIONSHIP FROM POLL ANSWER TO POLL QUESTION WITH ONE
     */
    public function poll_question()
    {
        return $this->hasOne('App\Modules\Events\Models\PollingQuestion', 'id', 'question_id');
    }
}
