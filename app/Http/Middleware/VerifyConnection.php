<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Models\Projects;
use Config;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class VerifyConnection
{
    public function handle($request, Closure $next)
    {
        //$module_array = Config::get('constants.project_types');

        if (isset(Auth::User()->id))
        {
            $this->ConnectionManager();
            return $next($request);
        }
        else {
            return redirect('session_error');
        }
    }
    public function ConnectionManager()
    {
        //$file_path = base_path("resources/assets/google_credential/".\Auth::user()->id."/indolytics_".\Auth::user()->id.".json");
        $file_path = public_path('uploads/google_credential/'.\Auth::user()->id.'/indolytics_'.\Auth::user()->id.'.json');
        $seted = Config::set("google.service.file", $file_path);

        $cm_data = Projects::where('user_id', \Auth::User()->id)->get();

        if(count($cm_data))
        {
            foreach($cm_data as $cm)
            {
                $schemaName = $cm->db;
                $secret = $cm->db_user;
                $password = \Crypt::decrypt($cm->db_password);
                $type = $cm->type;

                Config::set('database.connections.' . $schemaName, array(
                    'driver' => 'mysql',
                    'host' => 'localhost',
                    'database' => $schemaName,
                    'username' => $secret,
                    'password' => $password,
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix' => '',
                ));
                /*System project connection variables start*/
                Session::set('mysql_'.$type.'_database_conn', $schemaName);
                Session::set($type.'_conn_id', $cm->id);
                /*System project connection variables ends*/
            }
        }
        else
        {
            return redirect('system_error');
        }
    }
}
