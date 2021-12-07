<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Newspaper;
use App\Models\Tag;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    use GeneralTrait ;



    // get all Categories ( true ) from tag
    public function getCategories()
    {
        $tags = Tag::where('is_category','true')->get();
        if (count($tags)>0)
        {
            return $this->returnData('Tags',$tags,'There are all tags','201');
        }
        else
        {
            return $this->returnError('404','no tags');
        }
    }


    // get all articles that belong to specific tag
    public function getArticlesTag(Request $request)
    {
        $articles = Article::with('tag','newspaper')->where('tag_id',$request->input('tag_id'))->get();
        if (count($articles)>0)
        {
            return $this->returnData('articles' , $articles,'There are all articles','201');
        }
        else
        {
            return $this->returnError('404','no articles');
        }
    }


    // get all articles that belong to specific newspaper
    public function getArticlesNews(Request $request)
    {
        $articles = Article::with('newspaper')->where('newspaper_id',$request->input('newspaper_id'))->get();
        if (count($articles)>0)
        {
            return $this->returnData('articles' , $articles,'There are all articles','201');
        }
        else
        {
            return $this->returnError('404','no articles');
        }
    }


    // get count of likes for every article
    public function countLikes(Request $request)
    {
        $likes = Like::where('article_id',$request->input('article_id'))->get()->count();
        return $likes ;
    }


    // get count of follows for every newspaper
    public function countFollows(Request $request)
    {
        $follows= Follow::where('newspaper_id',$request->input('newspaper_id'))->get()->count();
        return $follows ;
    }


    // get all articles in random ( home )
    public function getAllArticles()
    {
        $articles = Article::all();
        return $this->returnData('Articles',$articles,'There are all articles in random','201');
    }


}
