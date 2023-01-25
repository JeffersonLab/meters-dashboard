<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;

class AuthController extends \Jlab\Auth\Http\AuthController
{

    /**
     * Processes a login request. Sends the user to the login form if necessary
     *
     */
    public function login(Request $request)
    {
        if ($this->isPostRequest()) {

            $validator = $this->getLoginValidator();

            if ($validator->passes()) {
                $credentials = $this->getLoginCredentials();
                if (Auth::attempt($credentials)) {
                    $request->session()->flash('success', 'Login successful!');

                    return redirect()->intended("/");
                }
            }
            $request->session()->now('error', 'The username or password is invalid');

            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        //If it wasn't a POST request, just return the form
        //We have to preserve the return parameter in the form.
        return view('auth.login');
    }

    public function logout(Request $request){
        if ($this->doLogout()){
            $request->session()->flash('success', 'Logout successful!');
        }
        return redirect()->back();
    }



}
