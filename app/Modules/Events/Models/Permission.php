<?php
namespace App\Modules\Events\Models;

use Zizaco\Entrust\EntrustPermission;


class Permission extends EntrustPermission
{
	protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }
}