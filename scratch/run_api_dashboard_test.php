<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$logged_in = true;
$current_role = 'super_admin';
require_once 'config.php';
require_once 'functions.php';

include 'scratch/api_dashboard_test.php';
