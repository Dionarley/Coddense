<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coddense</title>
    @php
        $manifestPath = public_path('build/.vite/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        }
    @endphp
    @if(isset($cssFile))
        <link rel="stylesheet" href="/build/{{ $cssFile }}">
    @endif
</head>
<body>
    @inertia
    @if(isset($jsFile))
        <script src="/build/{{ $jsFile }}"></script>
    @endif
</body>
</html>
