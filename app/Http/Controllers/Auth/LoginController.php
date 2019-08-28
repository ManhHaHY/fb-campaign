<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Facebook\Facebook;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    private $fb;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * LoginController constructor.
     * @param Facebook $facebook
     */
    public function __construct(Facebook $facebook)
    {
        $this->middleware('guest')->except('logout');
        $this->fb = $facebook;
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        $helper = $this->fb->getRedirectLoginHelper();
        $permissions = ['public_profile','email'];
        $loginUrl = $helper->getLoginUrl(url('/auth/callback'), $permissions);
        return view('auth.login', compact('loginUrl'));
    }
}
