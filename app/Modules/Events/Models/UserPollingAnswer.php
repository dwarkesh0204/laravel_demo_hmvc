<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class UserPollingAnswer extends Model
{
    protected $fillable = ['user_id', 'session_id', 'poll_question_id', 'poll_answer_id'];
	protected $table    = 'user_polling_answer';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
