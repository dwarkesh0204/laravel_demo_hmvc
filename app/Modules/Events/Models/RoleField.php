<?php

namespace App\Modules\Events\Models;

use Illuminate\Database\Eloquent\Model;

use Role;

class RoleField extends Model
{
    //
    protected $fillable = ['display_name', 'field_name', 'field_type', 'items', 'multiple', 'active', 'required', 'updated_at', 'created_at'];
    protected $table = 'role_fields';
    protected $connection;

    public function __construct() {
        $this -> connection = \Session::get('mysql_events_database_conn');
    }

	public function RoleFields()
	{
	    return $this->hasOne('App\Modules\Events\Models\Role');
	}

}
