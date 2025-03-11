<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class AuthController extends \Jlab\Auth\Http\AuthController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            [],
        ];
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

        // If it wasn't a POST request, just return the form
        // We have to preserve the return parameter in the form.
        return view('auth.login');
    }
}
