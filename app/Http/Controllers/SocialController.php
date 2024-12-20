<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;


class SocialController extends Controller
{
    public function redirectToGoogle($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    // Xử lý callback từ Google
    public function callback($provider)
    {

        $getInfo = Socialite::driver($provider)->user();

        $user = $this->createUser($getInfo, $provider);

        auth()->login($user);

        return redirect()->to('/upload');
    }
    function createUser($getInfo, $provider)
    {

        $user = User::where('provider_id', $getInfo->id)->first();

        if (!$user) {
            $user = User::create([
                'fullname'     => $getInfo->name,
                'email'    => $getInfo->email,
                'del_flag'    => true,
                'provider' => $provider,
                'provider_id' => $getInfo->id
            ]); 
        }
        return $user;
    }
    public function redirectToFacebook($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleFacebookCallback($provider)
    {
        // try {
            $getInfo = Socialite::driver($provider)->user();
            $user = $this->createUser($getInfo, $provider);
            auth()->login($user);
    
            return redirect()->intended('/upload');
        // } catch (\Exception $e) {
        //     return redirect()->to('/upload')->with('error', 'Đăng nhập thất bại, vui lòng thử lại.');
        // }
    }
    
}
