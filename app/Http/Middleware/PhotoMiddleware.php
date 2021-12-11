<?php

namespace App\Http\Middleware;

use App\Models\Photo;
use App\Models\Sharephoto;
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
        // if($photos = Photo::select("path")->where("privacy","public")->get()->toArray())
        // {
        //     $index = 0;
        //     foreach($photos as $photo)
        //     {
        //         $data[$index]["_id"] = $photo["_id"];
        //         $data[$index]["path"] = $photo["path"];
        //         $index++;
        //     }
        // }
        // $request = $request->merge(array("Photodata"=>$data));
        //return $next($request);
        // if($photos = Sharephoto::where("privacy","private")->get()->toArray())
        // {

        // }
        // else
        // {
        return $next($request);
       // }
    }
}
