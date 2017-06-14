<?php

namespace App\Modules\Visitors\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = ['name', 'card_no', 'email', 'purpose', 'phone_no', 'mac_add', 'status'];
	protected $table    = 'visitors';
    protected $connection;

    public function __construct() {
        $pid = \Session::get('project_conn_id');
        \Session::get('project_conn'.$pid);
        $this -> connection = \Session::get('project_conn'.$pid);
    }
}
