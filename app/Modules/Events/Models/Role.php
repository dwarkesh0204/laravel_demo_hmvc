<?php
namespace App\Modules\Events\Models;

use Zizaco\Entrust\EntrustRole;


class Role extends EntrustRole
{
	protected $fillable = ['name', 'display_name', 'description', 'updated_at', 'created_at'];
    protected $table = 'roles';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}