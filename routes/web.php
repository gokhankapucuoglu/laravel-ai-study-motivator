<?php

use App\Models\StudyLog;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // 1. Eğer URL'de ?lang=en veya ?lang=tr varsa, bunu oturuma (session) kaydet
    if (request()->has('lang') && in_array(request()->get('lang'), ['tr', 'en'])) {
        session()->put('locale', request()->get('lang'));
    }

    // 2. Oturumda bir dil varsa sistemi o dile ayarla, yoksa varsayılan 'tr' olsun
    $locale = session()->get('locale', 'tr');
    app()->setLocale($locale);

    return view('welcome', [
        'userCount' => User::count(),
        'analysisCount' => StudyLog::whereNotNull('ai_feedback')->count(),
    ]);
});
