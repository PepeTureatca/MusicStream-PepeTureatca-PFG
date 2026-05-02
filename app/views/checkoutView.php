<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream Premium</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <a href="dashboard.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Volver al dashboard
    </a>

    <div class="checkout-card">
        <div class="logo-container">
            <div class="logo-icon"><i class="fa-solid fa-music"></i></div>
            <span class="logo-text">MusicStream</span>
        </div>

        <div class="premium-badge">
            <i class="fa-solid fa-crown"></i> PREMIUM
        </div>

        <h1>Hazte <span>Premium</span></h1>
        <p class="subtitle">Un pago unico para disfrutar de toda la musica sin anuncios y con saltos ilimitados.</p>

        <?php if ($error): ?>
            <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="price-box">
            <div class="price-amount"><sup>&euro;</sup>5<sup style="font-size:20px">,99</sup></div>
            <div class="price-label">pago unico &mdash; acceso premium de por vida</div>
        </div>

        <section class="plans-comparison" aria-label="Comparativa de planes">
            <h2>Compara Free vs Premium</h2>
            <div class="plans-grid">
                <table class="plan-table free-plan">
                    <caption>Plan Free</caption>
                    <tbody>
                        <tr>
                            <th>Anuncios</th>
                            <td><i class="fa-solid fa-circle-check negative"></i> Si</td>
                        </tr>
                        <tr>
                            <th>Saltos de canciones</th>
                            <td><i class="fa-solid fa-circle-check negative"></i> Limitados</td>
                        </tr>
                        <tr>
                            <th>Calidad de audio</th>
                            <td><i class="fa-solid fa-circle-check negative"></i> Estandar</td>
                        </tr>
                        <tr>
                            <th>Descarga offline</th>
                            <td><i class="fa-solid fa-circle-xmark negative"></i> No</td>
                        </tr>
                    </tbody>
                </table>

                <table class="plan-table premium-plan">
                    <caption>Plan Premium</caption>
                    <tbody>
                        <tr>
                            <th>Anuncios</th>
                            <td><i class="fa-solid fa-circle-check positive"></i> Sin anuncios</td>
                        </tr>
                        <tr>
                            <th>Saltos de canciones</th>
                            <td><i class="fa-solid fa-circle-check positive"></i> Ilimitados</td>
                        </tr>
                        <tr>
                            <th>Calidad de audio</th>
                            <td><i class="fa-solid fa-circle-check positive"></i> Alta calidad</td>
                        </tr>
                        <tr>
                            <th>Descarga offline</th>
                            <td><i class="fa-solid fa-circle-check positive"></i> Si</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <form method="POST" action="checkout.php">
            <button type="submit" class="btn-checkout">
                <i class="fa-brands fa-stripe-s"></i>
                Pagar con tarjeta &mdash; &euro;5,99
            </button>
        </form>

        <p class="secure-note">
            <i class="fa-solid fa-lock"></i>
            Pago seguro procesado por Stripe. No almacenamos datos de tu tarjeta.
        </p>
    </div>
</body>
</html>
