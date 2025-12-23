<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify App</title>
    @vite(['resources/js/app.jsx', 'resources/css/app.css'])  <!-- Vite directive to load the JSX and CSS -->
</head>
<body>
<div id="app" data-shop="{{ request()->get('shop')}}" data-host="{{ request()->get('host')}}" data-api-key="{{ config('services.shopify')['api_key']}}"></div>
</body>
</html>
