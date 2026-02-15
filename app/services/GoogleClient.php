<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class GoogleClientService
{
    public static function getClient()
    {
        $client = new Google_Client();
        $client->setClientId('201748311150-jpv9t1m88t1ec5suodh7emmikdvu62q4.apps.googleusercontent.com');
        $client->setClientSecret('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');
        $client->setRedirectUri('http://localhost:8080/mi-spotify/public/google-callback.php');
        $client->addScope('email');
        $client->addScope('profile');
        return $client;
    }
}
