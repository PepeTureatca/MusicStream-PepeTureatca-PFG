<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: iniciarSesion.php");
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/model/userModel.php';

// --- Claves Stripe ---------------------------------------------------------
$stripeSecretKey      = getenv('STRIPE_SECRET_KEY')      ?: 'sk_test_51TQPBnE82ZzgUmDQBzWM8dkAiazdF7iKrkAVp1wfjPKzdfn9cEcWGTsNAL5LeWWpKGRMXnYpVL7Q3Kbo3G5SsBZQ007NGMGQ6n';
$stripePublishableKey = getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_51TQPBnE82ZzgUmDQxe5BJevNee9IoJsoSjnFYz3QcD9gbGfZqcdmbdZSADx2Pqlqsu6ULZFjlEbfGGHgjayQyG3S00irtGizhI';
// ---------------------------------------------------------------------------

$userId   = (int) $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$error    = null;

// Si ya es premium, redirigir al dashboard
if (User::esPremium($userId)) {
    header("Location: dashboard.php?msg=ya_premium");
    exit;
}

// Crear sesion de Stripe Checkout al hacer POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                 . '://' . $_SERVER['HTTP_HOST'] . '/mi-spotify/public';

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => 599, // 5.99 EUR en centimos
                    'product_data' => [
                        'name'        => 'MusicStream Premium',
                        'description' => 'Acceso ilimitado, sin anuncios y calidad de audio superior.',
                        'images'      => [],
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'          => 'payment',
            'success_url'   => $baseUrl . '/paymentSuccess.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'    => $baseUrl . '/paymentCancel.php',
            'client_reference_id' => (string) $userId,
        ]);

        header("Location: " . $session->url);
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        $error = "Error al conectar con el sistema de pago: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MusicStream Premium</title>
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

