<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>
<body>
    <x-print-header :title="$title" :patient="$patient" />

    <main>
        {{ $slot }}
    </main>
</body>
</html>
