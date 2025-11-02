@extends('layouts.auth')

@section('content')
    <div class="p-4 bg-white rounded-lg shadow-lg">

        <h2 class="text-lg font-bold uppercase tracking-wide text-center mb-4">
            <i class="fal fa-lock-alt mr-2"></i>{{ __('Login') }}
        </h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="my-2">
                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                <div class="col-md-6">
                    <input id="email" type="email"
                           class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500 @error('email') border border-red-500 @enderror"
                           name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                    <span class="text-sm text-red-500" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="my-2">
                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                <div class="col-md-6">
                    <input id="password" type="password"
                           class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500 @error('password') border border-red-500 @enderror"
                           name="password" required autocomplete="current-password">

                    @error('password')
                    <span class="text-sm text-red-500" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>

            <div class="flex items-center my-2">
                <input class="mr-2" type="checkbox" name="remember"
                       id="remember" {{ old('remember') ? 'checked' : '' }}>

                <label class="form-check-label" for="remember">
                    {{ __('Remember Me') }}
                </label>
            </div>

            <div class="">
                <button type="submit"
                        class="px-4 py-2 block bg-blue-500 rounded w-full text-center text-white transition-colors duration-150 hover:bg-blue-600">
                    <i class="fal fa-key mr-2"></i> {{ __('Login') }}
                </button>

                @if(Route::has('password.request'))
                    <a class="text-gray-500 text-center w-full block mt-4 italic transition-colors hover:text-gray-600"
                       href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>
        </form>
    </div>
@endsection
