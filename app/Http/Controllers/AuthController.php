<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends \Jlab\Auth\Http\AuthController
{
    public function __construct()
    {
        // Authentication requirement to access methods of this controller.
        $this->middleware([]);
    }

    /**
     * Processes a login request. Sends the user to the login form if necessary
     */
    public function login()
    {
        if ($this->isPostRequest()) {
            $validator = $this->getLoginValidator();

            if ($validator->passes()) {
                $credentials = $this->getLoginCredentials();
                if (Auth::attempt($credentials)) {
                    session()->flash('success', 'Login attempt successful!');

                    return redirect()->intended('/');
                }
            }
            session()->flash('error', 'Login attempt failed. Verify that the username or password are both valid');

            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        //If it wasn't a POST request, just return the form
        //We have to preserve the return parameter in the form.
        return view('auth.login');
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($this->doLogout()) {
            session()->flash('success', 'User logged out successfully');
        }

        return redirect()->back();
    }
}
