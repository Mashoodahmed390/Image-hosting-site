<?php

namespace App\Http\Middleware;

use App\Models\Photo;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Closure;
use Illuminate\Http\Request;

class PhotoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $image=Photo::where('attachment',strval($request->filename))->first();
            if($image->privacy=='private'){
                if(!empty(request()->bearerToken()))
                {
                    $decoded = JWT::decode(request()->bearerToken(), new Key(config('constant.secret'), 'HS256'));
                    // $token_exist= DB::select("select * from auth_token where user_id='{$decoded->data->id}'");
                    $token_exist = User::find($decoded->data->id);
                    if($token_exist){
                            $email=explode(',',$image->share_with);
                            if (in_array($decoded->data->email,$email))
                            {
                                request()->merge(['image'=>$image]);
                                return $next($request);
                            }
                    }else{
                        return response()->error('You are not Authorized',404);
                    }
                }
            }
            if ($image->visibility=='public')
            {
                request()->merge(['image'=>$image]);
                return $next($request);
            }else
            {
                return response()->error('You are not Authorized',404);
            }
    }
}
