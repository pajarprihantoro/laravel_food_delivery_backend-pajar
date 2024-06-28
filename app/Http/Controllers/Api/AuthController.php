<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //user register
    public function userRegister (Request $request){
        $validated = $request->validate(([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string',
        ]));

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data['roles'] = 'user';
        
        $user = User::create($data);
        
        return response()-> json([
            'status' => 'success',
            'massage' => 'User register successfully',
            'data' => $user
        ]);
    }

    // login
    public function login(Request $request) {

        // $request->merge([
        // 'email' => trim($request->email),
        // 'password' => trim($request->password),
        // ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if(!$user || Hash::check($request->password, $user->password)){
            return response()->json([
                'status' => 'failed',
                'massage' => 'Invalid credentials'
            ]);
        }

        $token = $user ->createToken ('auth_token')->plainTextToken;

        return response()-> json([
            'status' => 'success',
            'massage' => 'login success',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    // logout
    public function logout(Request $request){
        $request -> user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'massage' => 'Logout Success'
        ]);
    }

    // Restaurant Register
    public function restaurantRegister (Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string',
            'restaurant_name' => 'required|string',
            'restaurant_address' => 'required|string',
            'roles' => 'required|string',
            'photo' => 'required|image',
            'latlong' => 'required|string',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data ['roles'] = 'restaurant';

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }

        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'massage' => 'Restaurant Registered Successfully',
            'data' => $user
        ]);
    }

    // driver Register
    public function driverRegister (Request $request){
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'required|string',
            'license_plate' => 'required|string',
            'photo' => 'required|image',
        ]);

        $data = $request->all();
        $data['password'] = Hash::make($data['password']);
        $data ['roles'] = 'driver';
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
    
        $user = User::create($data);

        return response()->json([
            'status' => 'success',
            'massage' => 'Driver Registered Successfully',
            'data' => $user
        ]);
    }


    public function updateLatlong(Request $request){
        $request->validate([
            'latlong'=> 'required|string',
        ]);

        $user = $request->user();
        $user->latlong = $request->latlong;
        $user->save();

        return response()->json([
            'status' => 'success',
            'massage' => 'Latlong updated successfully',
            'data' => $user
        ]);
    }
    

    // get all restaurant
    public function getRestaurant(){
        $restaurant = User::where('roles', 'restaurant')->get();

        return response()->json([
            'status' => 'success',
            'massage' => 'get all restaurant',
            'data' => $restaurant
        ]);
    }
}
