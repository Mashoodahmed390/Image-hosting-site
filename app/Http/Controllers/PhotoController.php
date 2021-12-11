<?php

namespace App\Http\Controllers;

use App\Http\Requests\PhotoRequest;
use App\Models\Photo;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            $photo->path = $path;
            $photo->shareablelink = url("storage")."/images/".$imageName;
            $photo->privacy = "hidden";
            $photo->extension = $ext[1];
            $photo->user()->associate($decoded->data->id);
            $photo->save();
            $m=["message"=>"picture uploaded successfully","image_detail"=>$photo];
            return response()->success($m,201);
            }
           }
           catch(Exception $e)
           {
               return response()->error($e->getMessage(),400);
           }
    }
    public function deletePhoto(Request $request,$photo_id)
    {
        try
        {
           $photo = Photo::where('_id',$photo_id)->first();
           File::delete($photo->path);
           $photo->delete();
           return response()->success("Photo deleted successfully",201);
        }
        catch(Exception $e)
        {
            return response()->error($e,403);
        }
    }
    public function imageUpdate(Request $request,$photo_id)
    {
        $decoded = $request->decoded;
        $user = User::where('_id',$decoded->data->id)->first();
        try {
            $photo = Photo::where('_id',$photo_id)->where('user_id',$user->id)->first();
            if(isset($photo))
            {
                if($request->has('image'))
                {
                    File::delete($photo->path);
                    $base64encode = $request->image;
                    $pos  = strpos($base64encode, ';');
                    $replace = substr($base64encode, 0, strpos($base64encode, ',')+1);
                    $image = str_replace($replace, '', $base64encode);
                    $type = explode(':', substr($base64encode, 0, $pos))[1];
                    $ext=explode('/',$type);
                    $image = str_replace(' ', '+', $image);
                    $imageName = Str::random(10).'.'.$ext[1];
                    $shareablelink = url('storage/images/'.$imageName);
                    $allowedfileExtension=['pdf','jpg','png','jpeg'];
                    $check = in_array($ext[1],$allowedfileExtension);
                    if($check)
                    {
                    $path = public_path().'//storage//images//'.$imageName;
                    file_put_contents($path,base64_decode($image));
                    }
                    else{
                        throw new Exception('Invalid image format');
                    }

                        $data['path'] = $path;
                        $data['shareablelink'] = $shareablelink;
                        $data['extension'] = $ext[1];
                }
                if($request->has('privacy'))
                {
                        $data['privacy']=$request->privacy;
                }
                $photo->update($data);
                return response()->success('Image updated successfully',200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }
    public function displayallpicture(Request $request)
    {
        try
        {
        $photo = Photo::select("shareablelink")->where("privacy","public")->get();
        return response()->success($photo,200);
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),400);
        }
    }
    public function makingimageprivateorpublic(Request $request,$photo_id)
    {
        $photo = Photo::where('_id',$photo_id)->first();
        if($photo)
        {
        $photo->privacy = $request->privacy;
        return response()->success("privacy changed",201);
        }
        else
        {
            return response()->error("Not such image found",400);
        }
    }
    public function search(Request $request)
    {
        $decoded = $request->decoded;
        $user = User::where('_id',$decoded->data->id)->first();
        try {
            $image = Photo::where('user_id',$user->id);
            if($request->has('date'))
            {
               $image=$image->where('updated_at','like',date($request->date)."%");
            }
            if($request->has('time'))
            {
                $image=$image->where('updated_at','like',"%".date($request->time));
            }
            if($request->has('name'))
            {
                $image=$image->where('name',$request->name);
            }
            if($request->has('extension'))
            {
                $image=$image->where('extension',$request->extension);
            }
            if($request->has('privacy'))
            {
                $image=$image->where('privacy',$request->privacy);
            }
            if($request->has('hidden'))
            {
                $image=$image->where('hidden',$request->hidden);
            }
            $image=$image->get();
            if(!empty($image))
            {
                return response()->success($image,200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }

    }
}
