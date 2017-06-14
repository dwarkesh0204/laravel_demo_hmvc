<?php

namespace App\Modules\Storesolution\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $fillable = ['name', 'description','cover_image','venue', 'email', 'phone_no', 'sections', 'status'];
	protected $table    = 'stores';
    protected $connection;

    public function __construct() {
        $pid = \Session::get('project_conn_id');
        \Session::get('project_conn'.$pid);
        $this -> connection = \Session::get('project_conn'.$pid);
    }
}
