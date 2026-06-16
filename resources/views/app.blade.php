<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ماي واي - منصة التوصيل الأولى. اطلب من متاجرك المفضلة واحصل على توصيل سريع وآمن.">
    <title>{{ config('app.name', 'ماي واي') }}</title>
    {{-- Critical inline CSS: paint background immediately while main CSS loads --}}
    <style>body{margin:0;background:#f9fafb;font-family:'IBM Plex Sans Arabic',system-ui,sans-serif}[data-page]{min-height:100vh}</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet"></noscript>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
