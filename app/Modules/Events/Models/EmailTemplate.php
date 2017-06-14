<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['title', 'subject', 'message', 'type', 'tags', 'from', 'from_name', 'reply_to', 'reply_to_name', 'created_by', 'status', 'created_at', 'updated_at'];
	protected $table    = 'email_template';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
