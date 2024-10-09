<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="Your Name or Company Name">
    <link rel="shortcut icon" href="{{ asset('frontend/favicon.png') }}">
    <meta name="keywords" content="@yield('meta_keywords', 'Vaccination, Vaccine Registration, Health, Wellness')">
    <meta name="description" content="@yield('meta_description', 'Register for vaccination easily and quickly. Choose a vaccination center and get scheduled for your dose.')">
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="@yield('og_title', 'Vaccination Registration - Your Health Partner')" />
    <meta property="og:description" content="@yield('meta_description', 'Register for vaccination easily and quickly. Choose a vaccination center and get scheduled for your dose.')" />
    <meta property="og:image" content="@yield('og_image', asset('frontend/img/meta-img.png'))" />

    <!-- Bootstrap CSS -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"></noscript>

    <!-- Preload Font Awesome CSS -->
    <link rel="preload" href="https://unpkg.com/@fortawesome/fontawesome-free@6.0.0-beta2/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://unpkg.com/@fortawesome/fontawesome-free@6.0.0-beta2/css/all.min.css"></noscript>

    <link href="/frontend/css/style.css" rel="stylesheet">
    <title>@yield('title', 'Vaccination Registration')</title>
    @yield('styles')

    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;400;700&family=Roboto:wght@400;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;400;700&family=Roboto:wght@400;700&display=swap"></noscript>

    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff">
</head>

<style>
    body {
        width: 100% !important;
        font-family: 'Roboto', 'Noto Sans Bengali', sans-serif;
    }
    .container-co-section {
             margin-bottom: 100px;
     }
</style>
<body>

<!-- Start Message Section -->
@include('frontend.inc.message')
<!-- End Message Section -->

<!-- Start Main Content Section -->
@yield('content')
<!-- End Main Content Section -->

<!-- Start Footer Section -->
@include('frontend.inc.footer')
<!-- End Footer Section -->

<!-- JavaScript Files -->
<script src="{{ asset('frontend/js/bootstrap.bundle.min.js') }}"></script>
@yield('scripts')

</body>
</html>
