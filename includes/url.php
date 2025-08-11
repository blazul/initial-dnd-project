<?php
// includes/url.php

function url_origin(array $s, bool $use_forwarded_host = false): string
{
    $ssl      = (!empty($s['HTTPS']) && $s['HTTPS'] === 'on');
    $sp       = strtolower($s['SERVER_PROTOCOL']);
    $protocol = substr($sp, 0, strpos($sp, '/')) . ($ssl ? 's' : '');
    $port     = $s['SERVER_PORT'];
    $port     = ((!$ssl && $port === '80') || ($ssl && $port === '443')) ? '' : ':' . $port;
    $host     = $use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])
        ? $s['HTTP_X_FORWARDED_HOST']
        : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);

    if (!isset($host)) {
        $host = $s['SERVER_NAME'] . $port;
    }
    return $protocol . '://' . $host;
}

function full_url(array $s, bool $use_forwarded_host = false): string
{
    return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
}
