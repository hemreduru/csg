<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>@yield('title', config('app.name'))</title>

<meta name="description" content="{{ __('ui.meta.description') }}" />
<meta name="keywords" content="{{ __('ui.meta.keywords') }}" />

<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}" />
<meta property="og:type" content="website" />
<meta property="og:title" content="@yield('title', config('app.name'))" />
<meta property="og:description" content="{{ __('ui.meta.description') }}" />
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />

<link rel="canonical" href="{{ url()->current() }}" />
<link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.ico') }}" />

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

@stack('styles')

