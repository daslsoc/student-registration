<!--
    resources/views/layouts/app.blade.php

    This layout file centralizes the common HTML <head>, Bootstrap links,
    and any shared navigation. All other pages extend this layout and
    inject their content into the 'content' section.
-->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Dhamma and Sinhala Language School Registration')</title>

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">

  <!-- Any additional CSS can go here -->
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">
        <img src="/images/logo.png" alt="logo" width="27" height="30" class="d-inline-block align-text-top">
        Dhamma and Sinhala Language School of Canberra
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
        <div class="navbar-nav">
          <a class="nav-link" aria-current="page" href="{{route('registration.form')}}">Register New Family</a>
          <a class="nav-link" aria-current="page" href="{{route('registration.retrieve')}}">Update Existing Family</a>
          <a class="nav-link" aria-current="page" href="{{route('guidelines')}}">Guidelines</a>
          @if (Auth::check())
          <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Admin
            </a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="{{route('admin.parent_student_list')}}">Parents & Child List</a>
              <a class="dropdown-item" href="{{route('admin.orientation_list')}}">Orientation List</a>
              <a class="dropdown-item" href="{{route('admin.show_import_csv')}}">Import CSV</a>
            </div>
          </div>
          @endif
        </div>
      </div>

      <!-- Right Side Of Navbar -->
      <div class="nav navbar-nav navbar-right">
        @if (Auth::guest())
        <div>
          <a class="nav-link" aria-current="page" href="{{ url('/login') }}">Login</a>
        </div>
        @else
        <div>
          <a class="nav-link" href="{{ url('/logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            Logout
          </a>

          <form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
        @endif
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <!-- Yield the 'content' section where child views insert their main HTML -->
    @yield('content')
  </div>

  <footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
    <div class="col-md-4 d-flex align-items-center">
      <span class="mx-3 mb-3 mb-md-0 text-body-secondary">Developed and supported by CodiPhi Solutions</span>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('custom.tracking.google_analytics_id') }}"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());

    gtag('config', '{{ config("custom.tracking.google_analytics_id") }}');
  </script>
</body>

</html>