<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <!-- CSRF Token -->
   <meta name="csrf-token" content="{{ csrf_token() }}">

   <title>{{ config('app.name', 'Laravel') }}</title>


   <!-- Fonts -->
   <link rel="dns-prefetch" href="//fonts.gstatic.com">
   <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

   <!-- Styles -->
   <link href="{{ mix('css/app.css') }}" rel="stylesheet">
   <link href="{{ mix('css/animations.css') }}" rel="stylesheet">
   <link href="{{ mix('css/tooltip.css') }}" rel="stylesheet">
   <!-- Scripts -->
   <script src="{{ mix('js/app.js') }}" defer></script>

   @inertiaHead

</head>

<body>
   @inertia
</body>

</html>