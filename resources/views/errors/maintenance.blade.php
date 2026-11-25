<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trang này đang bảo trì | An Tâm Pharmacy</title>
    @php
        try {
            $interFont = Vite::asset('resources/fonts/InterVariable.woff2');
        } catch (\Illuminate\Foundation\ViteException) {
            $interFont = null;
        }
    @endphp
    <style>
        @if ($interFont)
            @font-face {
                font-family: Inter;
                font-weight: 100 900;
                font-display: swap;
                src: url('{{ $interFont }}') format('woff2');
            }
        @endif

        body {
            display: grid;
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            place-items: center;
            background: #f5f3ee;
            color: #1d2420;
            font-family: Inter, system-ui, sans-serif;
        }

        main {
            box-sizing: border-box;
            width: min(100% - 32px, 540px);
            padding: clamp(28px, 7vw, 60px);
            border: 1px solid #dad6cd;
            border-radius: 20px;
            background: #fff;
            text-align: center;
        }

        h1 { margin: 0; font-size: clamp(26px, 5vw, 36px); line-height: 1.2; }
        a {
            display: inline-flex;
            box-sizing: border-box;
            min-height: 44px;
            margin-top: 28px;
            align-items: center;
            justify-content: center;
            padding: 10px 24px;
            border-radius: 999px;
            background: #2e5442;
            color: #fff;
            font-weight: 600;
            text-decoration: none;
        }

        a:hover { background: #1d2420; }
        a:focus-visible { outline: 2px solid #2e5442; outline-offset: 4px; }
    </style>
</head>
<body>
    <main aria-labelledby="maintenance-title">
        <h1 id="maintenance-title">Trang này đang bảo trì</h1>
        <a href="{{ route('home') }}">Về trang chủ</a>
    </main>
</body>
</html>
