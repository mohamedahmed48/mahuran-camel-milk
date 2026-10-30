<?php
require_once 'config.php';
$q = $pdo->query("SELECT status, delivered_at, DATE(delivered_at), CURDATE() FROM orders WHERE status='delivered'");
print_r($q->fetchAll(PDO::FETCH_ASSOC));
