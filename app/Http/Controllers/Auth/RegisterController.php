<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Models\Projects;
use App\Http\Controllers\ProjectsController;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    protected function registered(Request $request, $user)
    {
        $modules_array = \Config::get('constants.project_types');
        $user_id = $user->id;
        $projects = Projects::where('user_id',$user_id)->count('id');

        if($projects == 0)
        {
            foreach($modules_array as $modules => $modval)
            {
                $secret = (new ProjectsController)->unique_secret();
                $infra_data = array(
                    'user_id'=> $user_id,
                    'title'=> $modules,
                    'type'=> $modval,
                    'secret'=>$secret,
                    'description'=>'',
                    'status'=>1
                );
                $infra_insert = Projects::create($infra_data)->id;
                $db_response_infra =  (new ProjectsController)->prepare_database($secret,$modval);
                Projects::where('id',$infra_insert)->update($db_response_infra);
            }
        }
    }
}
