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
        $user= Auth('api')->user();
        if($user->email){
            Mail::to($user->email)->send(new VerifyMail());
            return $this->returnSuccessMessage('Email sent successfully','201') ;
        }
    }


    // get code from user to verify his email
    public function getcodeemail(Request $request)
    {

        $user= Auth('api')->user();
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
            return $this->returnData('Email',$user->email,'Email sent successfully','201') ;
        }
    }


    // get code from user to verify his email
    public function getcodepassword(Request $request)
    {
        $user = User::where('email',$request->email)->first();

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



    public function reset(Request $request)
    {

        $user = User::where('email',$request->email)->first();

        if ($request->password == $request->password_confirmation)
        {
            $user->password = Hash::make($request->password);
            $user->save();
            return 'password reset successfully' ;
        }
        return 'failed';

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
