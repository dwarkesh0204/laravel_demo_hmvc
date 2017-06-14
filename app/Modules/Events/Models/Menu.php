<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    //
    protected $fillable = ['title', 'descriptions', 'view_name', 'parent', 'default_page', 'status', 'order', 'icon', 'login_requuired', 'screens', 'extra_data', 'cmsPage', 'webLink', 'HTMLContent', 'display_icon', 'cat_id', 'updated_at', 'created_at'];
	protected $table    = 'menu';
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}
