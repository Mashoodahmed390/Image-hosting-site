<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhotoRequest;
use App\Models\Photo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PhotoController extends Controller
{
    public function uploadPhoto(Request $request)
    {
        try{
            $base64encode = $request->image;
            $decoded = $request->decoded;
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
            //$path = $files->storeAs('images',$filename,'public');
            //store image file into directory and db
            $photo = new Photo();
            $photo->path = url("storage")."/images/".$imageName;
            $photo->privacy = "public";
            $photo->user()->associate($decoded->data->id);
            $photo->save();
            $m=["message"=>"picture uploaded successfully"];
            return response()->success($m,201);
            }
           }
           catch(Exception $e)
           {
               return response()->error($e->getMessage(),400);
           }
    }
    public function deletePhoto(PhotoRequest $request)
    {
        try
        {
           $photo = Photo::where('_id',$request->id)->first();
           $photo->delete();
           return response()->success("Photo deleted successfully",201);
        }
        catch(Exception $e)
        {
            return response()->error($e,403);
        }
    }
    public function displayallpicture(Request $request,$image)
    {
        try
        {
        $path = url("storage")."/images/".$image;
        $photo = Photo::select("path")->where("path",$path)->first();
        // $photos = Photo::select("path")->get()->toArray();
        // $photo_path_url=url('storage');
        // $index=0;
        // foreach($photos as $photo)
        // {
        //     $data[$index]["id"]= $photo["_id"];
        //     $data[$index]["path"]= $photo_path_url."/".$photo["path"];
        //     $index++;
        // }
        return response()->success($photo,200);
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
    }
}
