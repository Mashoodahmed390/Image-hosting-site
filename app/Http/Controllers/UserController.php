<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Password_reset;
use App\Models\User;
use App\Service\jwtservice as ServiceJwtservice;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Http\File;



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
            $signinUserData->profilePicture = "C:\\xampp\htdocs\Image-hosting-site\public//storage//images//user.jpg";
            // $signinUserData->shareablelink = url('storage')."/images/".'user.jpg';
            $signinUserData->verify = 0;
            if($validated["profilePicture"])
            {
                $base64encode = $validated['profilePicture'];
                $pos  = strpos($base64encode, ';');
                $replace = substr($base64encode, 0, strpos($base64encode, ',')+1);
                $image = str_replace($replace, '', $base64encode);
                $type = explode(':', substr($base64encode, 0, $pos))[1];
                $ext=explode('/',$type);
                $allowedfileExtension=['pdf','jpg','png','jpeg'];
                $check = in_array($ext[1],$allowedfileExtension);
                if($check)
                {
                $imageName = Str::random(10) .".".$ext[1];
                $url= $_SERVER['HTTP_HOST'];
                $path='https://'.$url."/photo/storage/".$imageName;
               // $path=storage_path('app\\photos').'\\'.$fileName;
                Storage::disk('local')->put($imageName,base64_decode($image));
                // $path = public_path().'//storage//images//'.$imageName;
                // file_put_contents($path,base64_decode($image));
                $signinUserData->profilePicture = $path;
                }
            }
            $signinUserData->save();
            $user = [
                'name' => $validated['name'],
                'info' => 'Press the Following Link to Verify Email',
                'Verification_link'=>url('user/verifyEmail/'.$validated['email'])
            ];
           dispatch(new \App\Jobs\SendEmailJob($validated['email'],$user));
            return response()->success("SignUp Successfully",200);
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),403);
        }
    }
    public function login(LoginRequest $request)
    {
        try
        {
            $validated = $request->validated();

            if($user = User::where("email",$validated["email"])->first())
            {
                if(Hash::check($validated["password"], $user->password))
            {
                if($user->verify)
                {
                $data = [
                    "id"=>$user->_id,
                    "email"=>$validated["email"],
                    "password"=>$validated["password"],
                    "age"=>$user->age
                ];
                $jwt = (new ServiceJwtservice)->jwt_encode($data);
                $user->remember_token = $jwt;
                $user->save();
                $user = array_merge($user->toArray(),array("password"=>$validated["password"]));
                return response()->success($user,200);
                }
                else
                {
                    return response()->error('Account not verified',400);
                }
            }
            else
            {
                throw new Exception("Password is wrong");
            }
            }
            else
            {
                throw new Exception("Error invalid email");
            }

        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),404);
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
            return response()->success("Password reset mail has been sent",200);
        }
        else
        {
            throw new Exception("Email Does not exist");
        }
           }
           catch(Exception $e)
           {
               return response()->error($e->getMessage());
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

            return response()->success("Password Updated",200);
        }
        else
        {
            return response()->error("Unauthorized",404);
        }
           }
           catch(Exception $e)
           {
               return response()->error($e->getMessage(),404);
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
                $user->name = $validated['email'];
            }
            if($request->has('password'))
            {
                $user->name = $validated['password'];
            }
            if($request->has('age'))
            {
                $user->name = $validated['age'];
            }
            if($request->hasFile('profilePicture'))
            {
                $before=$user['profilePicture'];
                if($before == "C:\\xampp\htdocs\Image-hosting-site\public//storage//images//user.jpg")
                {
                $base64encode = $validated['profilePicture'];
                $pos  = strpos($base64encode, ';');
                $replace = substr($base64encode, 0, strpos($base64encode, ',')+1);
                $image = str_replace($replace, '', $base64encode);
                $type = explode(':', substr($base64encode, 0, $pos))[1];
                $ext=explode('/',$type);
                $allowedfileExtension=['pdf','jpg','png','jpeg'];
                $check = in_array($ext[1],$allowedfileExtension);
                if($check)
                {
                $imageName = Str::random(10) .".".$ext[1];
                $path = public_path().'//storage//images//'.$imageName;
                file_put_contents($path,base64_decode($image));
                $user->profilePicture = $path;
                }
                else{
                    throw new Exception("invalid_picture_format");
                }
                }
                else
                {
                    $base64encode = $validated['profilePicture'];
                    $pos  = strpos($base64encode, ';');
                    $replace = substr($base64encode, 0, strpos($base64encode, ',')+1);
                    $image = str_replace($replace, '', $base64encode);
                    $type = explode(':', substr($base64encode, 0, $pos))[1];
                    $ext=explode('/',$type);
                    $allowedfileExtension=['pdf','jpg','png','jpeg'];
                    $check = in_array($ext[1],$allowedfileExtension);
                    if($check)
                    {
                    $imageName = Str::random(10) .".".$ext[1];
                    $path = public_path().'//storage//images//'.$imageName;
                    file_put_contents($path,base64_decode($image));
                    $user->profilePicture = $path;
                    }
                    else{
                        throw new Exception("invalid_picture_format");
                    }
                }
            }
            $data = [
                "id"=>$user->_id,
                "email"=>$validated["email"],
                "password"=>$validated["password"],
                "age"=>$user->age
            ];
            $jwt = (new ServiceJwtservice)->jwt_encode($data);
            $user->remember_token = $jwt;
            $user->save();
            $data = [
                'token'=>$jwt,
                'message'=>'profile Updated'
            ];
            return response()->success([$data],201);

        }
    public function verify($email)
            {
                if(User::where("email",$email)->value('verify') == 1)
                {
                    $m = ["You have already verified your account"];
                    return response()->error($m,404);
                }
                else
                {
                    $update=User::where("email",$email)->update(["verify"=>1]);
                    if($update){
                        return response()->success("Account verified",200);
                    }else{
                        return response()->error("Failed",400);
                    }
                }
            }
            public function resource(Request $request)
            {
                $decoded = $request->decoded;
                $user = User::find($decoded->data->id);
                return new UserResource($user);
            }
}
