<?php
require_once 'config.php';
require_once 'functions.php';

try {
    $where_o = getBranchContextQuery('o');
    echo "where_o: $where_o\n";
    $pdo->query("SELECT COUNT(*) FROM orders o WHERE $where_o AND status='pending'");
    echo "Query OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
