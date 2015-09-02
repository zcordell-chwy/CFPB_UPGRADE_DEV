<?
$sessionParameter = (function_exists('sessionParm') ? sessionParm() : '');
header('Location: /app/error/error_id/404/url/' . urlencode(urlencode($_SERVER['REQUEST_URI'])) . $sessionParameter);
