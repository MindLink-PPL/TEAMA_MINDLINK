<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'MindLink')</title>
  @stack('head')
</head>
<body>
  <aside class="side">
    <div class="brand"><div class="dot">M</div><div><b>MindLink</b><small>Support platform</small></div></div>

    <a class="nav {{ request()->routeIs('student.appointments') ? 'on' : '' }}" href="{{ route('student.appointments') }}">
      <span class="dot">C</span>Counselling
    </a>
    {{-- your teammates add Dashboard, Learn, Wellbeing, Notifications links here --}}

    <div class="who">
      <b>{{ auth()->user()->name }}</b><br><small>Secure account</small>
    </div>
  </aside>

  <main>
    @yield('content')
  </main>

  <dialog id="dlg">
    <h2 id="dlgT"></h2><p class="sub" id="dlgP" style="margin-top:8px"></p>
    <div class="acts"><button class="btn o s" id="dlgNo">Go back</button><button class="btn s" id="dlgYes">Confirm</button></div>
  </dialog>
  <div id="toast" role="status"></div>
</body>
</html>