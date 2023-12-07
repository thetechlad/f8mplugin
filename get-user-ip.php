<?php
/**
 * Plugin Name: F8MPlugin
 * Description: A WordPress plugin to automatically get and save the user's IP address on page load, and display it in the admin panel.
 * Version: 1.0
 * Author: Your Name
 */

// Activation hook to create the table
function create_ip_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'user_ips';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        ip_address varchar(45) NOT NULL,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Register activation hook
register_activation_hook(__FILE__, 'create_ip_table');

// Function to get the user's IP address
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Function to save user IP in the database
function save_user_ip() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_ips';
    $ip_address = get_user_ip();

    $wpdb->insert(
        $table_name,
        array(
            'ip_address' => $ip_address,
            'time'       => current_time('mysql'),
        )
    );
}

// Hook into the initialization process
function on_page_load() {
    save_user_ip();
}
add_action('init', 'on_page_load');

// Admin menu page
function user_ips_menu() {
    add_menu_page(
        'User IPs',
        'User IPs',
        'manage_options',
        'user_ips',
        'display_user_ips_page'
    );
}
<style>
        <?php
        // Enqueue the external CSS file from the assets directory
        echo file_get_contents(plugin_dir_path(__FILE__) . 'assets/css/style.css');
        ?>
    </style>
// Function to display the admin table
function display_user_ips_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'user_ips';
    $user_ips = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time DESC");

    echo '<div class="wrap">';
    echo '<h1>User IPs</h1>';
    echo '<table class="widefat">';
    echo '<thead><tr><th>ID</th><th>IP Address</th><th>Time</th></tr></thead>';
    echo '<tbody>';

    foreach ($user_ips as $user_ip) {
        echo '<tr>';
        echo '<td>' . esc_html($user_ip->id) . '</td>';
        echo '<td>' . esc_html($user_ip->ip_address) . '</td>';
        echo '<td>' . esc_html($user_ip->time) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';
}

// Register admin menu
add_action('admin_menu', 'user_ips_menu');
