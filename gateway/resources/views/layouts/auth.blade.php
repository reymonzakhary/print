<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CEC Prindustry') }}</title>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="/" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>

<body>
<div id="app"
     class="flex items-center justify-center h-screen text-blue-900 bg-gradient-to-br from-green-400 via-blue-500 to-indigo-500">
    <div class="fixed top-0 flex w-screen text-white">

        <nav class="flex items-center justify-between w-screen h-12 px-2 md:w-screen">
            <div class="flex items-center">

                <img src="images/Prindustry_logo_wit.png" alt="Prindustry Logo" id="prindustry-logo"
                     class="h-8 mr-1 max-w-none"/>
                <span class="mt-2 text-sm uppercase">Manager</span>
            </div>

            <ul class="flex justify-around w-44">
                <!-- Authentication Links -->
                @guest
                    <li>
                        <a class="{{ (Request::is('login'))  ? 'font-bold' : 'font-normal' }}"
                           href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if(Route::has('register'))
                        <li>
                            <a class="{{ (Request::is('register'))  ? 'font-bold' : 'font-normal' }}"
                               href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }}
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </nav>
    </div>
    @yield('content')
</div>
</body>

</html>
