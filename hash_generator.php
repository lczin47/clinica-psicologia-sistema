<?php
$password = '123456'; // A senha que queremos usar
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Senha Original: " . $password . "<br>";
echo "Hash Gerado (Copie ESTE VALOR): " . $hashed_password;
?>