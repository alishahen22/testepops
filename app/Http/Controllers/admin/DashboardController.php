<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Article;
use App\Models\Newspaper;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    // add tag
    public function addTag(Request $request)
    {
        // validate
        $validation = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validation->fails())
        {
            return $this->returnError('404' ,"This input is required");
        }
        else
        {
            Tag::create(
                [
                    'name' => $request->name ,
                ]);
            return $this->returnSuccessMessage('tag added successfully','201');
        }
    }


    // add newspaper
    public function addNewspaper(Request $request)
    {
        // validate
        $validation = Validator::make($request->all(), [
            'title' => 'required',
            'logo' => 'required',
            'link' => 'required',
        ]);

        if ($validation->fails())
        {
            return $this->returnError('404' ,"There is empty input");
        }

        else
        {
            Newspaper::create(
                [
                    'title' => $request->title ,
                    'logo' => $request->logo ,
                    'link' => $request->link ,
                ]);
            return $this->returnSuccessMessage('newspaper added successfully','201');
        }
    }


    // add article
    // public function addArticle(Request $request)
    // {
    //     // validate
    //     $validation = Validator::make($request->all(), [
    //         'author' => 'required',
    //         'conclusion' => 'required',
    //         'link' => 'required',
    //         'media' => 'required',
    //         'title' => 'required',
    //         'publish_date' => 'required',
    //         'newspaper_id' => 'required',

    //     ]);

    //     if ($validation->fails())
    //     {
    //         return $this->returnError('404' ,"There is empty input");
    //     }

    //     else
    //     {
    //         Article::create(
    //             [
    //                 'author' => $request->author ,
    //                 'conclusion' => $request->conclusion ,
    //                 'link' => $request->link ,
    //                 'media' => $request->media ,
    //                 'title' => $request->title ,
    //                 'publish_date' => $request->publish_date ,
    //                 'newspaper_id' => $request->newspaper_id ,
    //             ]);
    //         return $this->returnSuccessMessage('article added successfully','201');
    //     }
    // }



    public function addCategory()
    {
        $admin = Auth::user();
        return view('admin.addCategory', compact('admin'));

    }


}
