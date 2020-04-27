<?php

namespace App\Http\Controllers;

use App\User;
use App\Client;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTSubject;
use Tymon\JWTAuth\PayloadFactory;
use Tymon\JWTAuth\JWTManager as JWT;

class UserController extends Controller
{
    //crear la tabla ----- CDM ---- php artisan migrate
    public function register(Request $request){

        $validator = Validator::make($request->json()->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->json()->get('name'),
            'email' => $request->json()->get('email'),
            'password' => Hash::make($request->json()->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);

    }

    public function register2(Request $request){

        $validator = Validator::make($request->json()->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'passwd' => 'required|string|min:6',
            'img' => 'required|string|max:255',
            'rol' => 'required|integer',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $Client = new Client;

        $Client->firstname = $request->json()->get('firstname');
        $Client->lastname = $request->json()->get('lastname');
        $Client->email = $request->json()->get('email');
        $Client->passwd = Hash::make($request->json()->get('passwd'));
        $Client->img = $request->json()->get('img');
        $Client->rol = $request->json()->get('rol');
        $Client->status = '0';
        $Client->save();

        $token = JWTAuth::fromUser($Client);
        return response()->json(compact('client', 'token'), 201);
        /* $user = Client::create([
            'firstname' => $request->json()->get('firstname'),
            'lastname' => $request->json()->get('lastname'),
            'email' => $request->json()->get('email'),
            'passwd' => Hash::make($request->json()->get('passwd')),
            'img' => $request->json()->get('img'),
            'rol' => $request->json()->get('rol'),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201); */



        /* $user = array(
            $request->json()->get('firstname'),
            $request->json()->get('lastname'),
            $request->json()->get('email'),
            Hash::make($request->json()->get('passwd')),
            $request->json()->get('img'),
            $request->json()->get('rol'),
        );
        $results = DB::insert('insert into user (firstname, lastname, email, passwd, img, rol, status) values (?, ?, ?, ?, ?, ?, 0)', $user);
        if(response()->json($results)){
            $userToken = array(
                'firstname' => $request->json()->get('firstname'),
                'lastname' => $request->json()->get('lastname'),
                'email' => $request->json()->get('email'),
                'passwd' => Hash::make($request->json()->get('passwd')),
                'img' => $request->json()->get('img'),
                'rol' => $request->json()->get('rol'),
            );
            $token = JWTAuth::fromUser($userToken);
            return response()->json(compact('userToken', 'token'), 201);
        }
 */


    }

    public function login(Request $request){
        $credentials = $request->json()->all();

        try{
            if(! $token = JWTAuth::attempt($credentials)){
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        }catch(JWTException $e){
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'));
    }

    public function getAuthenticatedUser(){
        try{
            if(!$user = JWTAuth::parseToken()->authenticate()){
                return response()->json(['error' => 'user_not_found'], 404);
            }
        }catch(Tymon\JWTAuth\Exceptions\TokenExpiredException $e){
            return response()->json(['token_expired'], $e->getStatusCode());
        }catch(Tymon\JWTAuth\Exceptions\TokenInvalidException $e){
            return response()->json(['token_invalid'], $e->getStatusCode());
        }catch(Tymon\JWTAuth\Exceptions\TokenException $e){
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }
}
