<?php
require_once __DIR__ . '/../model/userModel.php';

class PaymentController
{
    public static function showCheckout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        $stripeSecretKey      = getenv('STRIPE_SECRET_KEY');
        $stripePublishableKey = getenv('STRIPE_PUBLISHABLE_KEY');

        $userId = (int) $_SESSION['user_id'];
        $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
        $error = null;

        if (User::esPremium($userId)) {
            header("Location: /mi-spotify/public/dashboard.php?msg=ya_premium");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                \Stripe\Stripe::setApiKey($stripeSecretKey);

                $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                         . '://' . $_SERVER['HTTP_HOST'] . '/mi-spotify/public';

                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'unit_amount' => 599,
                            'product_data' => [
                                'name' => 'MusicStream Premium',
                                'description' => 'Acceso ilimitado, sin anuncios y calidad de audio superior.',
                                'images' => [],
                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => $baseUrl . '/paymentSuccess.php?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => $baseUrl . '/paymentCancel.php',
                    'client_reference_id' => (string) $userId,
                ]);

                header("Location: " . $session->url);
                exit;
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $error = "Error al conectar con el sistema de pago: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }

        require_once __DIR__ . '/../views/checkoutView.php';
    }

    public static function showCancel()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
        require_once __DIR__ . '/../views/paymentCancelView.php';
    }

    public static function showSuccess()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id'])) {
            header("Location: /mi-spotify/public/iniciarSesion.php");
            exit;
        }

        require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

        $stripeSecretKey = getenv('STRIPE_SECRET_KEY');

        $sessionId = $_GET['session_id'] ?? null;
        $userId = (int) $_SESSION['user_id'];
        $success = false;
        $error = null;

        if ($sessionId) {
            try {
                \Stripe\Stripe::setApiKey($stripeSecretKey);
                $checkoutSession = \Stripe\Checkout\Session::retrieve($sessionId);

                if (
                    $checkoutSession->payment_status === 'paid'
                    && (string) $checkoutSession->client_reference_id === (string) $userId
                ) {
                    $paymentIntentId = is_string($checkoutSession->payment_intent)
                        ? $checkoutSession->payment_intent
                        : ($checkoutSession->payment_intent->id ?? $sessionId);

                    if (User::setPremium($userId, $paymentIntentId)) {
                        $_SESSION['is_premium'] = true;
                        $success = true;
                    } else {
                        $error = 'No se pudo actualizar tu cuenta a premium. Intentalo de nuevo.';
                    }
                } else {
                    $error = 'El pago no se ha podido verificar. Contacta con soporte si el cargo ya se realizo.';
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $error = 'Error al verificar el pago: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        } else {
            $error = 'Sesion de pago no valida.';
        }

        $userName = htmlspecialchars($_SESSION['user_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
        require_once __DIR__ . '/../views/paymentSuccessView.php';
    }
}