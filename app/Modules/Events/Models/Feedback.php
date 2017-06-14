<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['feedback', 'user_id', 'status', 'session_id', 'star_rating'];
    protected $table    = 'feedback';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des RELATIONSHIP FROM USER TO FEEDBACK WITH ONE
     */
    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @des RELATIONSHIP FROM SESSION TO FEEDBACK WITH ONE
    */
    public function feedback_session()
    {
        return $this->hasOne('App\Models\Sessions','id','session_id');
    }


}
