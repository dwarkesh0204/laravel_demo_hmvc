<?php

namespace App\Modules\Events\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;

use Zizaco\Entrust\Traits\EntrustUserTrait;


class User extends Authenticatable

{

    use EntrustUserTrait;

    /**

     * The attributes that are mass assignable.

     *

     * @var array

     */

    protected $fillable = [

        'name', 'email', 'password', 'phone_number', 'user_avatar','device_id','device_type','device_token','reg_type','fb_id','google_id'

    ];


    /**

     * The attributes that should be hidden for arrays.

     *

     * @var array

     */

    protected $hidden = [

        'password', 'remember_token',

    ];

    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @des RELATIONSHIP FROM USER TO SESSIONS WITH MANY
     */
    public function sessions()
    {
        return $this->hasMany('App\Modules\Events\Models\Sessions', 'speaker_id');
    }

    public function feedback()
    {
        return $this->hasMany('App\Modules\Events\Models\Feedback', 'user_id');
    }

    public function question()
    {
        return $this->hasMany('App\Modules\Events\Models\Question', 'user_id');
    }

    public function user_sessions_speakers()
    {
        return $this->hasMany('App\Modules\Events\Models\SessionsSpeakers', 'speaker_id');
    }

    public function user_user_request_for_session()
    {
        return $this->hasMany('App\Modules\Events\Models\UserRequestForSession', 'user_id');
    }

    public function user_userevent()
    {
        return $this->hasMany('App\Modules\Events\Models\User', 'user_id');
    }
}