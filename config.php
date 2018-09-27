<?php

$config = new TileServer_Configuration();
$baseUrls = array( 't0.server.com', 't1.server.com' );
$availableFormats = array( 'png', 'jpg', 'jpeg', 'gif', 'webp', 'pbf', 'hybrid' );
$isHttps = false;

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
    $isHttps = true;
}

if (isset($_SERVER['HTTPS'])) {
    $isHttps = true;
}

if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
    $isHttps = true;
}

$config
    ->setBaseUrls($baseUrls)
    ->setProtocol($isHttps ? 'https' : 'http')
    ->setTemplate('template.php')
    ->setServerTitle('Maps hosted with TileServer-php v2.0')
    ->setDataRoot('')
    ->setAvailableFormats($availableFormats);