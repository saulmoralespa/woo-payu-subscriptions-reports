<?php
/*
Plugin Name: Woo payU subscriptions reports
Description: Generates excel reports of payU latam subscriptions
Version: 1.0.0
Author: Saul Morales Pacheco
Author URI: https://saulmoralespa.com
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: woo-payu-subscriptions-reports
Domain Path: /languages/
WC tested up to: 3.5
WC requires at least: 2.6
*/

if (!defined( 'ABSPATH' )) exit;

if(!defined('WOO_PAYU_SUBSCRIPTIONS_REPORTS_VERSION')){
    define('WOO_PAYU_SUBSCRIPTIONS_REPORTS_VERSION', '1.0.0');
}

if(!defined('WOO_PAYU_SUBSCRIPTIONS_REPORTS_NAME')){
    define('WOO_PAYU_SUBSCRIPTIONS_REPORTS_NAME', 'Woo payU subscriptions reports');
}

add_action('plugins_loaded','woo_payu_subscriptions_reports_init',0);


function woo_payu_subscriptions_reports_init(){

    load_plugin_textdomain('woo-payu-subscriptions-reports', FALSE, dirname(plugin_basename(__FILE__)) . '/languages');

    if (!requeriments_woo_payu_subscriptions_reports()){
        return;
    }

    woo_payu_subscriptions_reports()->run_woo_payu_reports();

    if(get_option('woo_payu_subscriptions_reports_redirect', false)){
        delete_option('woo_payu_subscriptions_reports_redirect');
        wp_redirect(admin_url('admin.php?page=config-woopayusubscriptionsreports'));
    }
}

function woo_payu_subscriptions_reports_notices( $notice ) {
    ?>
    <div class="error notice">
        <p><?php echo esc_html( $notice ); ?></p>
    </div>
    <?php
}

function requeriments_woo_payu_subscriptions_reports(){

    if ( version_compare( '5.6.0', PHP_VERSION, '>' ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    woo_payu_subscriptions_reports_notices( __('Woo payU subscriptions reports: Requiere la versión de php 5.6 o superior', 'woo-payu-subscriptions-reports') );
                }
            );
        }
        return false;
    }

    if ( !in_array(
        'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    woo_payu_subscriptions_reports_notices( __('Woo payU subscriptions reports: Woocommerce must be installed and active', 'woo-payu-subscriptions-reports') );
                }
            );
        }
        return false;
    }

    if ( !in_array(
        'subscription-payu-latam/subscription-payu-latam.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
        true
    ) ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            add_action(
                'admin_notices',
                function() {
                    woo_payu_subscriptions_reports_notices( __('Woo payU subscriptions reports: Subscription Payu Latam must be installed and active', 'woo-payu-subscriptions-reports') );
                }
            );
        }
        return false;
    }


    if (!class_exists('WC_Subscriptions')){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            $subs = __( 'Woo payU subscriptions reports: Woocommerce Subscriptions must be installed and active, ', 'woo-payu-subscriptions-reports' ) . sprintf(__('%s', 'woo-payu-subscriptions-reports' ), '<a href="https://wordpress.org/plugins/subscription-payu-latam/#%C2%BF%20what%20else%20should%20i%20keep%20in%20mind%2C%20that%20you%20have%20not%20told%20me%20%3F">' . __('check the documentation for help', 'woo-payu-subscriptions-reports') . '</a>' );

            add_action(
                'admin_notices',
                function() use($subs) {
                    woo_payu_subscriptions_reports_notices($subs);
                }
            );

        }
        return false;
    }

    return true;
}


function woo_payu_subscriptions_reports(){

    static $plugin;
    if(!isset($plugin)){
        require_once ("includes/class-woo-payu-subscriptions-reports-plugin.php");
        $plugin = new Woo_Payu_Subscriptions_Reports_Plugin(__FILE__, WOO_PAYU_SUBSCRIPTIONS_REPORTS_VERSION, WOO_PAYU_SUBSCRIPTIONS_REPORTS_NAME);
    }

    return $plugin;
}

function woo_payu_subscriptions_reports_activate(){
    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/payu-suscriptions-reports/';
    if(!is_dir($dir)){
        woo_payu_subscriptions_reports()->createDirUploads($dir);
    }

    add_option('woo_payu_subscriptions_reports_redirect', true);
}


register_activation_hook(__FILE__,'woo_payu_subscriptions_reports_activate');