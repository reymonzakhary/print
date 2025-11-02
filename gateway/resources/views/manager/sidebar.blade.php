<nav class="flex flex-col justify-center text-white">


   <a class="flex flex-col items-center my-2" href="{{ route('dashboard')}}">
      <i class="{{(Request::is('/'))?'fad':'fal'}} fa-tachometer-fast my-2 fa-3x"></i>
      Dashboard
   </a>

   <a class="flex flex-col items-center my-2" href="{{ url('tenants') }}">
      <i class="{{(Request::is('tenants'))?'fad':'fal'}} fa-users-crown my-2 fa-3x"></i>
      Tenants
   </a>

   <a class="flex flex-col items-center my-2" href="{{ url('tenants') }}">
      <i class="{{(Request::is('users'))?'fad':'fal'}} fa-users-crown my-2 fa-3x"></i>
      Users
   </a>

   <a class="flex flex-col items-center my-2" href="{{ url('tenants') }}">
      <i class="{{(Request::is('companies'))?'fad':'fal'}} fa-users-crown my-2 fa-3x"></i>
      Contracts
   </a>

   <a class="flex flex-col items-center my-2" href="{{ url('standardisation') }}">
      <i class="{{(Request::is('standardisation'))?'fad':'fal'}} fa-digging my-2 fa-3x"></i>
      Standardisation
   </a>
</nav>