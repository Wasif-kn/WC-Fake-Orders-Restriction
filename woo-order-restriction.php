<?php
/**
 * Plugin Name: WC Fake Orders Restriction
 * Plugin URI: http://example.com/plugin-name-uri/
 * Description: This plugin restricts orders based on email, phone number, or IP address in WooCommerce.
 * Version: 1.0.0
 * Author: Wasif Khan
 * Author URI: http://example.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wc_fake_restriction
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to create database table during plugin activation
function activate_wc_fake_restriction()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'wc_fake_restriction';

    // Check if the table already exists in the database
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        return; // Table already exists, no need to create a new table
    }

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        restriction_type text NOT NULL,
        restriction_value varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Register plugin activation hook
register_activation_hook(__FILE__, 'activate_wc_fake_restriction');


// Function to insert values into the database table
function insert_values_into_wc_fake_restriction($time, $restriction_type, $value)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'wc_fake_restriction';

    // Prepare data to be inserted into the table
    $data = array(
        'time' => $time,
        'restriction_type' => $restriction_type,
        'restriction_value' => $value
    );

    // Insert data into the table
    $wpdb->insert($table_name, $data);
}

// Function to retrieve data from the database table
function get_wc_fake_restriction_data()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'wc_fake_restriction';

    // Query to fetch all data from the table
    $query = "SELECT * FROM $table_name";

    // Get results from the database
    $results = $wpdb->get_results($query, ARRAY_A);

    return $results;
}



// Function to register a menu page in WordPress admin
function register_wc_fake_restriction_page()
{
    add_menu_page(
        'WC Fake Orders Restriction',
        'Restrictions',
        'manage_options',
        'wc_fake_restriction_page',
        'render_wc_fake_restriction_page',
        'dashicons-welcome-widgets-menus',
        90
    );
}

// Add action to create the menu page
add_action('admin_menu', 'register_wc_fake_restriction_page');

// Function to render the plugin's settings page in WordPress admin
function render_wc_fake_restriction_page()
{
    // Handle form submission to insert restrictions into the database
    if (isset($_POST['restriction_value'])) {
        insert_values_into_wc_fake_restriction(time(), $_POST['restriction_type'], $_POST['restriction_value']);
    }
    ?>

    <!-- HTML form for adding restrictions -->
    <div class="restriction_form">
        <form method="post">
            <p><label>Restriction Type</label><select name="restriction_type">
                    <option>Email</option>
                    <option>Phone Number</option>
                    <option>IP Address</option>
                </select></p>
            <p><label>Value</label>
                <input type="text" name="restriction_value">
            </p>
            <p><input type="submit" value="Add Restriction" class="btn"></p>
        </form>
    </div>

    <!-- CSS styles for the form and table -->
    <style>
        /* Style form elements within the .restriction_form div */
        .restriction_form form {
            width: 300px;
            margin: 20px auto;
            background-color: #f7f7f7;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .restriction_form label {
            display: block;
            margin-bottom: 8px;
        }

        .restriction_form select,
        .restriction_form input[type="text"],
        .restriction_form input[type="submit"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .restriction_form input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .restriction_form input[type="submit"]:hover {
            background-color: #0056b3;
        }


        /* Style the table */
        #restrictionTable {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        /* Style the table header */
        #restrictionTable th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 8px;
        }

        /* Style alternating rows */
        #restrictionTable tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Style table cells */
        #restrictionTable td {
            border: 1px solid #dddddd;
            padding: 8px;
        }

        /* Hover effect on rows */
        #restrictionTable tr:hover {
            background-color: #e2e2e2;
        }
    </style>

    <!-- Display the list of restrictions in a table -->
    <div class="wrap">
        <table id="restrictionTable">
            <thead>
                <th>Id</th>
                <th>Type</th>
                <th>Value</th>
            </thead>
            <?php
            // Fetch and display restriction data in the table
            $data = get_wc_fake_restriction_data();
            foreach ($data as $key => $value) {
                $id = $value['id'];
                $type = $value['restriction_type'];
                $r_value = $value['restriction_value'];
                ?>
                <tr>
                    <td>
                        <?php esc_html_e($id, 'wc_fake_restriction'); ?>
                    </td>
                    <td>
                        <?php esc_html_e($type, 'wc_fake_restriction'); ?>
                    </td>
                    <td>
                        <?php esc_html_e($r_value, 'wc_fake_restriction'); ?>
                    </td>
                </tr>
                <?php
            } ?>
        </table>
    </div>

    <?php
}

// Function to store values of email, phone, or IP restrictions data to use
$data_compare = get_wc_fake_restriction_data();

$email_items = array();
$phone_items = array();
$ip_items = array();

foreach ($data_compare as $key => $value) {
    $id = $value['id'];
    $type = $value['restriction_type'];
    $r_value = $value['restriction_value'];

    if ($type == "Email") {
        $email_items[] = $r_value;
    } else if ($type == "Phone Number") {
        $phone_items[] = $r_value;
    } else if ($type == "IP Address") {
        $ip_items[] = $r_value;
    }
}

// Hook into WooCommerce checkout process to prevent orders based on restrictions
add_action('woocommerce_checkout_process', 'prevent_order_with_certain_email');

function prevent_order_with_certain_email()
{
    global $email_items;
    global $phone_items;
    global $ip_items;

    $restricted_phones = $phone_items;
    $restricted_emails = $email_items;
    $restricted_ips = $ip_items;

    $billing_email = $_POST['billing_email'];
    $billing_phone = $_POST['billing_phone'];
    $user_ip = $_SERVER['REMOTE_ADDR'];

    if (in_array($billing_email, $restricted_emails)) {
        wc_add_notice(__('Sorry, we are unable to process orders with this email address. Please contact us for assistance.'), 'error');
        return false;
    } else if (in_array($billing_phone, $restricted_phones)) {
        wc_add_notice(__('Sorry, we are unable to process orders with this phone number. Please contact us for assistance.'), 'error');
        return false;
    } else if (in_array($user_ip, $restricted_ips)) {
        wc_add_notice(__('Sorry, your IP address is restricted from placing orders.'), 'error');
        return false;
    }
}
