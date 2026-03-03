<?php
echo "admin123 hash: " . password_hash('admin123', PASSWORD_DEFAULT) . "\n";
echo "user123 hash: " . password_hash('user123', PASSWORD_DEFAULT) . "\n";
?>
