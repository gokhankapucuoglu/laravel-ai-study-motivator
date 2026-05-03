<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Eğer Vite çalışmıyorsa tasarım bozulmasın diye Tailwind CDN (Geçici) -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>

<!-- ... <head> ve diğer kısımlar aynı kalacak ... -->

<body class="bg-gray-50  min-h-screen flex items-center justify-center p-4 antialiased">

    <!-- Sağ Üst Köşe - Şık Dil Seçimi (DİNAMİK BAYRAKLAR) -->
    @php $currentLang = app()->getLocale(); @endphp
    <div class="absolute top-6 right-6 flex items-center gap-3 z-50">
        <!-- TR Bayrağı -->
        <a href="?lang=tr" title="Türkçe"
            class="group relative w-10 h-10 rounded-full overflow-hidden shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border-2
               {{ $currentLang === 'tr' ? 'border-gray-400 ring-2 ring-primary-500/50 scale-110' : 'border-transparent opacity-50 grayscale-50 hover:opacity-100 hover:grayscale-0' }}">
            <img src="{{ asset('flags/tr.svg') }}" alt="Türkçe" class="w-full h-full object-cover">
        </a>

        <!-- EN Bayrağı -->
        <a href="?lang=en" title="English"
            class="group relative w-10 h-10 rounded-full overflow-hidden shadow-md transition-all duration-300 hover:-translate-y-1 hover:shadow-xl border-2
               {{ $currentLang === 'en' ? 'border-gray-400 ring-2 ring-primary-500/50 scale-110' : 'border-transparent opacity-50 grayscale-50 hover:opacity-100 hover:grayscale-0' }}">
            <img src="{{ asset('flags/en.svg') }}" alt="English" class="w-full h-full object-cover">
        </a>
    </div>

    <!-- Kutuların Taşıyıcısı -->
    <div class="w-full max-w-md flex flex-col gap-6">

        <!-- 1. SATIR: GİRİŞ YAP VE ÜYE OL BUTONLARI -->
        <div class="grid grid-cols-2 gap-4">
            <a href="/admin/login"
                class="flex flex-col items-center justify-center w-full py-6 px-4 rounded-xl bg-[#4A90E2] hover:bg-[#357ABD] text-white text-2xl font-bold shadow-lg transition-transform duration-300 hover:-translate-y-1 cursor-pointer text-center">
                {{ __('welcome.login') }}
            </a>

            <a href="/admin/register"
                class="flex flex-col items-center justify-center w-full py-6 px-4 rounded-xl bg-[#F5A623] hover:bg-[#E09612] text-white text-2xl font-bold shadow-lg transition-transform duration-300 hover:-translate-y-1 cursor-pointer text-center">
                {{ __('welcome.register') }}
            </a>
        </div>

        <!-- 2. KUTU: KIRMIZI (Sistemdeki Kullanıcı Sayısı) -->
        <div
            class="flex flex-col items-center justify-center w-full py-8 px-4 rounded-xl bg-[#E74C3C] hover:bg-[#C0392B] text-white shadow-lg transition-transform duration-300 hover:-translate-y-1">
            <span class="text-sm font-medium text-red-100 mb-2 uppercase tracking-wider text-center">
                {{ __('welcome.system_users') }}
            </span>
            <span class="text-4xl font-bold">
                {{ $userCount }}
            </span>
        </div>

        <!-- 3. KUTU: YEŞİL (Yapay Zeka Analiz Sayısı) -->
        <div
            class="flex flex-col items-center justify-center w-full py-8 px-4 rounded-xl bg-[#61BA65] hover:bg-[#4E9D51] text-white shadow-lg transition-transform duration-300 hover:-translate-y-1">
            <span class="text-sm font-medium text-green-100 mb-2 uppercase tracking-wider text-center">
                {{ __('welcome.ai_analysis') }}
            </span>
            <span class="text-4xl font-bold">
                {{ $analysisCount }}
            </span>
        </div>

    </div>
</body>
<!-- ... </html> ... -->

</html>
