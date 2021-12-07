<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function uploadPhoto(Request $request)
    {
        if($request->hasFile('image')){
            $photo =
            $filename = $request->image->getClientOriginalName();
            $request->image->storeAs('images',$filename,'public');
        }
    }
}
