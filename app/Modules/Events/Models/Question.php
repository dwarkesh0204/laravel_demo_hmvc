<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['question', 'user_id', 'status', 'session_id'];
    protected $table    = 'question';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des QUESTION TO USER RELATION
     */
    public function user()
    {
        return $this->hasOne('App\Modules\Events\Models\User', 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des QUESTION TO SESSION RELATION
     */
    public function question_session()
    {
        return $this->hasOne('App\Modules\Events\Models\Sessions','id','session_id');
    }


}
