<?php

require __DIR__.'/classes/RNCryptor/autoload.php';

$password = "0123456789abcdef";
$base64Encrypted = "AwEsQZZxAUW XPL2Zk6A1bCsnVZi96DA1lFhmvkN 11voPkX2K2uPiPxlzsSI1I0rLx/ VoYNvzmlKBVlk0AFNhvATdWcu8N4dJyl35mfSVLjFzgiwq6SSPfecJ5otLd9AAbIhkmlweRygobvh4GaDaaj5X8zJOqD53ar0nyb456g3j66uSPs17fO6ecdnd3ol6KGyy15kL/dvUyRT qX4kT";
$cryptor = new \RNCryptor\Decryptor();
$plaintext = $cryptor->decrypt($base64Encrypted, $password);

echo "Plaintext: $plaintext\n";
echo "\n";
var_dump($plaintext);
echo "\n";
var_dump($base64Encrypted);

/*
require __DIR__.'/../autoload.php';

$password = "myPassword";
$base64Encrypted = "AgGXutvFqW9RqQuokYLjehbfM7F+8OO/2sD8g3auA+oNCQFoarRmc59qcKJve7FHyH9MkyJWZ4Cj6CegDU+UbtpXKR0ND6UlfwaZncRUNkw53jy09cgUkHRJI0gCfOsS4rXmRdiaqUt+ukkkaYfAJJk/o3HBvqK/OI4qttyo+kdiLbiAop5QQwWReG2LMQ08v9TAiiOQgFWhd1dc+qFEN7Cv";

$cryptor = new \RNCryptor\Decryptor();
$plaintext = $cryptor->decrypt($base64Encrypted, $password);

echo "Base64 Encrypted:\n$base64Encrypted\n\n";
echo "Plaintext:\n$plaintext\n\n";*/
?>