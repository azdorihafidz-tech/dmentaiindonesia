<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f97316">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-950 antialiased">
    <main class="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-20">
        <p class="mb-5 text-sm font-bold uppercase tracking-[0.24em] text-orange-600">Dimsum &amp; Gyoza</p>
        <h1 class="max-w-3xl text-5xl font-black tracking-tight sm:text-7xl">Halo, ini D'Mentai Indonesia.</h1>
        <p class="mt-6 max-w-xl text-lg leading-8 text-stone-600">Website resmi D'Mentai sedang disiapkan. Informasi menu dan outlet akan hadir di sini.</p>
        <a class="mt-9 inline-flex w-fit rounded-full bg-orange-500 px-6 py-3 font-semibold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-4 focus:ring-orange-200" href="{{ url('/admin') }}">Masuk admin</a>
    </main>
</body>
</html>