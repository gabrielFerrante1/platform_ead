<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function create(Request $request) {
        $array = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8']
        ]);

        if($validator->fails()) {
            $array['error'] = $validator->messages()->first();
            return $array;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $newUser = new User();
        $newUser->name = $name;
        $newUser->email = $email;
        $newUser->password = password_hash($password, PASSWORD_DEFAULT); 
        $newUser->save();

        $creds = $request->only('email', 'password');
        $logged = Auth::attempt($creds); 

        $array['user'] = ['id' => $newUser->id, 'name' => $newUser->name, 'email' => $newUser->email, 'token' => $logged];
        
        return  $array;
    }

    public function login(Request $request) {
        $array = ['error' => ''];

        $creds = $request->only('email', 'password');

        $token = Auth::attempt($creds);

        if($token) {
            $array['token'] = $token;
            $user = Auth::user();
            $array['user'] = ['name' => $user->name, 'email' => $user->email,'id'=>$user->id];
        } else {
            $array['error'] = 'Email ou senha incorretos';
        } 

        return $array;
    }

    public function verifyAuthentication() {
        if(Auth::check()) {
            $auth = Auth::user();
        
            return ['name' => $auth->name, 'id' => $auth->id, 'email' => $auth->email, 'error' => false];
        } else {
            return response()->json(['error'=>true]);
        }
     

    }
}
