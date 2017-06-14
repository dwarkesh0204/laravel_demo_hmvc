<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Configuration extends Model
{

    protected $table = 'configuration';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

    /**
     * Use to Store Config.
     */
    public function StoreConfig($configArray){

    	foreach ($configArray as $key => $config) {
    		$UpdateData = DB::table('configuration')->where('name', '=', $key)->update((array('value' => $config)));
    	}

		return true;

    }
}
