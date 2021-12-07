<?php

namespace App\Http\Controllers;

use App\Http\Requests\validatePasswoedRequest;
use App\Models\Admin;
use App\Models\Article;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Newspaper;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{

    use GeneralTrait ;


    // add like to article
    public function addLike(Request $request)
    {

        if ( User::find($request->input('user_id')) && Article::find($request->input('article_id')))
        {
            Like::create([
                'user_id'  => $request->input('user_id'),
                'article_id'  => $request->input('article_id'),
            ]);
            return $this->returnSuccessMessage('like saved successfully','201');
        }

         return $this->returnError('404','input valid') ;
    }


    // add follow to newspaper
    public function addFollow(Request $request)
    {
        if ( User::find($request->input('user_id')) && Newspaper::find($request->input('newspaper_id')))
        {
            Follow::create([
                'user_id'  => $request->input('user_id'),
                'newspaper_id'  => $request->input('newspaper_id'),
            ]);
            return $this->returnSuccessMessage('follow saved successfully','201');
        }

        return $this->returnError('404','input valid') ;
    }


    // add article to saved
    public function addToSaved(Request $request)
    {

        if ( User::find($request->input('user_id')) && Article::find($request->input('article_id')))
        {
            $user = User::findOrFail($request->user_id);
            $user->article()->attach($request->article_id);
            return $this->returnSuccessMessage('article saved successfully','201');
        }

        return $this->returnError('404','input valid') ;
    }


    // get all saved for specific user
    public function getAllSaved(Request $request)
    {
        if ( User::find($request->input('user_id')) &&  User::findOrFail($request->input('user_id'))->article->count()>0)
        {
            $articles = User::findOrFail($request->input('user_id'))->article ;
            return $this->returnData('saved',$articles,'There are all saved','201');
        }
        return $this->returnError('404','No articles') ;
    }






    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }




    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }


    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }


    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }



    public function updateprofile(Request $request)
    {
        //validation
        $user  = User::find(Auth::id());
        if($user->email !=$request->email){
            $validator = Validator::make($request->all(), ['email' => 'required|string|email|max:100|unique:users']);
            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }
        }
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|between:2,100',
            'last_name' => 'required|string|between:2,100',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        //update
        if ($user)
            $user->first_name = $request->first_name ;
            $user->last_name = $request->last_name ;
            $user->email = $request->email ;
            $user->save();

            return 'done';
    }


    public function updatepassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'newPassword' => 'required|min:8',
            'oldPassword' => 'required|min:8',
    ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user  = User::find(Auth::id());
        if ( Hash::check($request->oldPassword, $user->password)) {
            $user->password =Hash::make($request->newPassword);
            $user->save();
            return "updated";
        }
        else{
            return 'failed';
        }

    }



    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);


        $status = Password::sendResetLink(
            $request->only('email')
        );


        if ($status == Password::RESET_LINK_SENT) {
            return [
                'status' => __($status)
            ];
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);

    }



    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),

                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response([
                'message'=> 'Password reset successfully'
            ]);
        }

        return response([
            'message'=> __($status)
        ], 500);

    }




//public function reset($token)
//{
//    return $token;
//}


    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }



    public function __construct() {
<<<<<<< HEAD
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
=======
        $this->middleware('auth:api', ['except' => ['login', 'register','forgotPassword','reset']]);
>>>>>>> ed78d2a6ddaee3aa39a8658f62f7e9068576f63d
        auth()->setDefaultDriver('api');
    }


}
