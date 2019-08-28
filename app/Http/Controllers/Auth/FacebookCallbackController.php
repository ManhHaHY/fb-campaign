<?php
/**
 * Created by PhpStorm.
 * User: MyPC
 * Date: 28/08/2019
 * Time: 1:22 SA
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\UserRepository;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class FacebookCallbackController extends Controller
{
    private $user;
    private $fb;

    public function __construct(UserRepository $user, Facebook $facebook)
    {
        $this->user = $user;
        $this->fb = $facebook;
    }

    public function callback(Request $request)
    {
        $helper = $this->fb->getRedirectLoginHelper();

        if (isset($request->state)) {
            $helper->getPersistentDataHandler()->set('state', $request->state);
        }

        try {
            $accessToken = $helper->getAccessToken();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return $this->output(Response::HTTP_FORBIDDEN, null, $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return $this->output(Response::HTTP_FORBIDDEN, null, $e->getMessage());
        }

        if (!isset($accessToken)) {
            return $this->output($helper->getErrorCode(), $helper->getError(), $helper->getErrorReason());
        }

        if (! $accessToken->isLongLived()) {
            // OAuth 2.0 client handler
            $oauth_client = $this->fb->getOAuth2Client();

            // Extend the access token.
            try {
                $accessToken = $oauth_client->getLongLivedAccessToken($accessToken);
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                return $this->output(Response::HTTP_FORBIDDEN, null, $e->getMessage());
            }
        }

        $this->fb->setDefaultAccessToken($accessToken);

        // Save for later
        Session::put('fb_user_access_token', (string) $accessToken);

        // Get basic info on the user from Facebook.
        try {
            $response = $this->fb->get('/me?fields=id,name,email,picture{url}');
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return $this->output(Response::HTTP_FORBIDDEN, null, $e->getMessage());
        }

        // Convert the response to a `Facebook/GraphNodes/GraphUser` collection
        $facebook_user = $response->getGraphUser();

        // Create the user if it does not exist or update the existing entry.
        $userData = [
            'name' => $facebook_user->getName(),
            'email' => $facebook_user->getEmail() != '' ? $facebook_user->getEmail() : $facebook_user->getId() . '@facebook.com',
            'facebook_id' => $facebook_user->getId(),
            'facebook_token' => $accessToken->getValue(),
            'avatar' => $facebook_user->getPicture()->getUrl(),
            'password' => bcrypt('123456'),
        ];
        $user = User::whereEmail($userData['email'])->first();
        if(!$user){
            $user = User::create($userData);
        }

        // Log the user into Laravel
        Auth::login($user);

        return redirect('/')->with('message', 'Successfully logged in with Facebook');
    }
}