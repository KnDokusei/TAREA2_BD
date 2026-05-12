<?php
// Utilidad temporal: genera hash BCrypt para una contraseña
// Uso: http://localhost/ct_usm/gen_hash.php?pw=tucontraseña
// ELIMINAR este archivo después de usarlo en producción

$pw = $_GET['pw'] ?? 'password';
$hash = password_hash($pw, PASSWORD_BCRYPT);
echo "<pre>";
echo "Contraseña: " . htmlspecialchars($pw) . "\n";
echo "Hash:       " . $hash . "\n\n";
echo "Verificación: " . (password_verify($pw, $hash) ? 'OK ✓' : 'FALLO ✗') . "\n";

// Verificar el hash incluido en el SQL
$hash_sql = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "\nHash del SQL verifica 'password': " . (password_verify('password', $hash_sql) ? 'OK ✓' : 'FALLO ✗') . "\n";
echo "</pre>";
