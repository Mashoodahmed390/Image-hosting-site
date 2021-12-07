<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UserRequest;
use App\Models\Password_reset;
use App\Models\User;
use App\Service\jwtservice as ServiceJwtservice;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function signup(UserRequest $request)
    {
        try
        {
            $validated = $request->validated();
            $validated['password'] = bcrypt($validated['password']);
            $signinUserData = New User();
            $signinUserData->name = $validated["name"];
            $signinUserData->email = $validated["email"];
            $signinUserData->password = $validated["password"];
            $signinUserData->age = $validated["age"];
            $signinUserData->profilePicture = 'storage/path/public/user.jpg';
            if($validated["profilePicture"])
            {
                 $image = $validated["profilePicture"];  // your base64 encoded
                 $imageName = Str::random(10) . '.jpg';
                 $path = 'storage/path/public/'.$imageName;
                 Storage::disk('local')->put($imageName, base64_decode($image));
                 $signinUserData->profilePicture = $path;
            }
            $signinUserData->save();
            return response()->json(["message"=>"SignUp Successfully"],201);
        }
        catch(Exception $e)
        {
            return response()->json($e->getMessage(),404);
        }
    }
    public function login(LoginRequest $request)
    {
        try
        {
            $validated = $request->validated();
            if($user = User::where("email",$validated["email"])->first())
            {

            }
            else
            {
                return response()->json("Error invalid email");
            }
            if(Hash::check($validated["password"], $user->password))
            {
                $data = [
                    "id"=>$user->_id,
                    "email"=>$validated["email"],
                    "password"=>$validated["password"],
                    "age"=>$user->age
                ];
                //dd($user);
                $jwt = (new ServiceJwtservice)->jwt_encode($data);
                $user->remember_token = $jwt;
                $user->save();
                $user = array_merge($user->toArray(),array("password"=>$validated["password"]));
                // $user->save();
                return response()->json($user,200);
            }
            else
            {
                throw new Exception("Password is wrong");
            }
        }
        catch(Exception $e)
        {
            return response()->json($e->getMessage(),404);
        }
    }
    public function forgetPassword(ForgetPasswordRequest $request)
    {
        try{
        if(User::where("email",$request->email)->exists())
        {
            $resetPassword = new Password_reset();
            $resetPassword->email = $request->email;
            $resetPassword->token = Str::random(10);
            $resetPassword->save();
            $data = ['Verification_link'=>url('user/'.$resetPassword->email.'/'.$resetPassword->token)];
            \Mail::to($request->email)->send(new \App\Mail\MyTestMail($data));
            return response()->json("Password reset mail has been sent",200);
        }
        else
        {
            return response()->json("Email Does not exist");
        }
           }
           catch(Exception $e)
           {
               return response()->json($e->getMessage());
           }
    }
    public function updatepassword(UpdatePasswordRequest $request,$email,$token)
    {
        try{
        if(Password_reset::where('token',$token)->exists())
        {
            $deleteToken = Password_reset::where('token',$token)->first();
            $deleteToken->delete();
            $validated = $request->validated();
            $user = User::where('email',$email)->first();
            $validated['password'] = bcrypt($validated['password']);
            $user->password =$validated['password'];
            $user->save();
            return response()->json("Password Updated",200);
        }
        else
        {
            return response()->json("Unauthorized",404);
        }
           }
           catch(Exception $e)
           {
               return response()->json($e->getMessage(),404);
           }
    }
    public function updateuser(UserRequest $request)
    {
        $validated = $request->validated();
        $decoded = $request->decoded;
        $user = User::where('email',$decoded->data->email)->first();
            if($request->has('name'))
            {
            $user->name = $validated['name'];
            }
            if($request->has('email'))
            {
                $user->name = $validated['name'];
            }
            if($request->has('password'))
            {
                $user->name = $validated['password'];
            }
            if($request->has('age'))
            {
                $user->name = $validated['age'];
            }

            $user->save();
            $m = [
                "status"=>"success",
                "message"=>"profile updated"
            ];
            return response()->json($m,201);
    }
}
