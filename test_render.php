<?php
require 'config.php';
$_GET['page']='products';
$_GET['subpage']='add';
$_SESSION['user_id']=1;
$_SESSION['role']='super_admin';
$_SESSION['branch_id']=null;
ob_start();
require 'index.php';
$out = ob_get_clean();
echo strlen($out) > 0 ? 'rendered' : 'empty';
echo "\n";
echo "OUT START\n" . substr($out, 0, 500) . "\nOUT END\n";
