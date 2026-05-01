<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - Pago cancelado</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #121212;
            --card-bg: #181818;
            --text-primary: #ffffff;
            --text-secondary: #b3b3b3;
            --border-color: #282828;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 52px 40px;
            max-width: 420px;
            width: 100%;
            text-align: center;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(100, 116, 139, 0.15);
            border: 2px solid rgba(100,116,139,0.3);
            color: #94a3b8;
            font-size: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
        }
        h1 { font-size: 28px; font-weight: 800; margin-bottom: 12px; }
        .subtitle { color: var(--text-secondary); font-size: 15px; line-height: 1.6; margin-bottom: 36px; }
        .btn-group { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 13px 24px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-primary { background: linear-gradient(135deg, #8e44ad, #d53f8c); color: #fff; }
        .btn-secondary { background: var(--border-color); color: var(--text-primary); }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <i class="fa-solid fa-xmark"></i>
        </div>
        <h1>Pago cancelado</h1>
        <p class="subtitle">
            No se realizo ningun cargo. Puedes volver a intentarlo cuando quieras, <?= $userName ?>.
        </p>
        <div class="btn-group">
            <a href="checkout.php" class="btn btn-primary">
                <i class="fa-solid fa-crown"></i> Ver Premium
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fa-solid fa-house"></i> Dashboard
            </a>
        </div>
    </div>
</body>
</html>
