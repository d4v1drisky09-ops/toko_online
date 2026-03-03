<?php
$hash_admin = password_hash('admin123', PASSWORD_DEFAULT);
$hash_user = password_hash('user123', PASSWORD_DEFAULT);

echo "Admin hash: " . $hash_admin . "\n";
echo "User hash: " . $hash_user . "\n";
echo "Test admin123: " . (password_verify('admin123', $hash_admin) ? 'OK' : 'FAIL') . "\n";
echo "Test user123: " . (password_verify('user123', $hash_user) ? 'OK' : 'FAIL') . "\n";

try {
    $conn = new PDO("mysql:host=localhost;dbname=toko_online", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("UPDATE users SET password = '$hash_admin' WHERE email = 'admin@toko.com'");
    echo "Admin password updated!\n";
    
    $conn->exec("UPDATE users SET password = '$hash_user' WHERE email = 'user@toko.com'");
    echo "User password updated!\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
