<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class TwitchLoginController extends Controller
{
    public function redirect(): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('twitch')->redirect();
    }

    public function callback(): \Illuminate\Routing\Redirector|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $twitchUser = Socialite::driver('twitch')->user();

        $user = User::updateOrCreate([
            'twitch_id' => $twitchUser->id,
        ], [
            'username' => $twitchUser->name,
            'email' => $twitchUser->email
        ]);

        Auth::login($user);

        return redirect('/');
    }
}
