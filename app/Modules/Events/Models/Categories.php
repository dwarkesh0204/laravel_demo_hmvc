<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $fillable = ['title', 'description', 'parent', 'status', 'created_at', 'updated_at'];
	protected $table    = 'categories';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

	public function parent()
    {
        return $this->belongsTo('App\Categories', 'parent');
    }

    public function children()
    {
        return $this->hasMany('App\Categories', 'parent');
    }
}
