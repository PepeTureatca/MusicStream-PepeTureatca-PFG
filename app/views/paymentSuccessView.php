<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream - <?php echo $success ? 'Pago completado' : 'Error en el pago'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/paymentSuccess.css">
</head>
<body>
    <div class="card">
        <?php if ($success): ?>
            <div class="icon-circle success"><i class="fa-solid fa-crown"></i></div>
            <div class="badge"><i class="fa-solid fa-crown"></i> PREMIUM ACTIVADO</div>
            <h1>Bienvenido, <?php echo $userName; ?>!</h1>
            <p class="subtitle">
                Tu cuenta premium ya esta activa. Disfruta de musica sin anuncios y con calidad alta.
            </p>
            <div class="btn-row">
                <a href="dashboard.php" class="btn btn-primary"><i class="fa-solid fa-house"></i> Ir al dashboard</a>
            </div>
        <?php else: ?>
            <div class="icon-circle error"><i class="fa-solid fa-circle-xmark"></i></div>
            <h1>Error en el pago</h1>
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            <p class="subtitle">
                No pudimos confirmar tu pago. Puedes reintentarlo o volver al dashboard.
            </p>
            <div class="btn-row">
                <a href="checkout.php" class="btn btn-primary"><i class="fa-solid fa-rotate-left"></i> Reintentar</a>
                <a href="dashboard.php" class="btn btn-secondary"><i class="fa-solid fa-house"></i> Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
