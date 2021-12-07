<?php

namespace App\Http\Controllers;

use App\Mail\VerifyMail;
use App\Mail\VerifyPassword;
use App\Models\Admin;
use App\Models\User;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RulesPassword;

class VerificationController extends Controller
{
   use GeneralTrait ;

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Already Verified'
            ];
        }

        $request->user()->sendEmailVerificationNotification();

        return ['status' => 'verification-link-sent'];
    }



    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Email already verified'
            ];
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return [
            'message'=>'Email has been verified'
        ];
    }



    // send email to user for verification
    public function verifyuser(Request $request)
    {
        $user = User::find(Auth::id());
        if($user->email){
            Mail::to($user->email)->send(new VerifyMail());
            return $this->returnSuccessMessage('Email sent successfully','201') ;
        }
    }


    // get code from user to verify his email
    public function getcodeemail(Request $request)
    {

        $user = User::find(Auth::id());
        if($user->code == $request->code)
        {
            $user->email_verified_at = Carbon::now();
            $user->code = null ;
            $user->save() ;
            return $this->returnSuccessMessage('Verification Done','201') ;
        }
        else
        {
            return $this->returnError('404','Incorrect Code, Try Again') ;
        }
    }



    public function forgotPassword(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user){
            Mail::to($user->email)->send(new VerifyPassword());
            return $this->returnSuccessMessage('Email sent successfully','201') ;
        }
    }


    // get code from user to verify his email
    public function getcodepassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|min:4',
        ]);

        $user = User::where('email',$request->email)->first();


        if($user->code == $request->code)
        {
            $user->email_verified_at = Carbon::now();
            $user->code = null ;
            $user->save() ;
            return $this->returnData('userId',$user->id,'Verification Done','201') ;
        }
        else
        {
            return $this->returnError('404','Incorrect Code, Try Again') ;
        }

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


    public function reset(Request $request)
    {
        $user = User::where('id',$request->id)->first();

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













    public function hi($id){
        $user=User::where('code',$id)->first();
        if ($user){

            $user->email_verified_at = Carbon::now();
            $user->code =null;
            $user->save();
            return '<h2 style="text-align: center;color: darkred">your account verivied successfully</h2>';

        }else{
            return  '<h2 style="text-align: center;color: darkred">Error 404 not found ...  </h2>';
        }
    }















}
