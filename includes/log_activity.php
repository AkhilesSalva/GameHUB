<?php
/**
 * Helper function to log admin activity
 * Include this file and call log_activity() to log admin actions
 */

function log_activity($koneksi, $action, $target_type = null, $target_id = null, $details = null) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    $user_id = (int)$_SESSION['user_id'];
    $action = mysqli_real_escape_string($koneksi, $action);
    $target_type = $target_type ? "'" . mysqli_real_escape_string($koneksi, $target_type) . "'" : 'NULL';
    $target_id = $target_id ? (int)$target_id : 'NULL';
    $details = $details ? "'" . mysqli_real_escape_string($koneksi, $details) . "'" : 'NULL';
    $ip_address = mysqli_real_escape_string($koneksi, $_SERVER['REMOTE_ADDR'] ?? '');
    
    $query = "INSERT INTO activity_log (user_id, action, target_type, target_id, details, ip_address) 
              VALUES ($user_id, '$action', $target_type, $target_id, $details, '$ip_address')";
    
    return mysqli_query($koneksi, $query);
}
