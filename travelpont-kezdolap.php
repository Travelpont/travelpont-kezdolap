<?php
/**
 * Plugin Name: Travelpont Kezdőoldal
 * Plugin URI:  https://travelpont.hu
 * Description: A Travelpont kezdőoldalának teljes, jóváhagyott mockup alapján épített sablonja – ACF-mentes, önálló plugin, a Travelpont Ajánlatok / Úticélok pluginek mintájára.
 * Version:     1.4.0
 * Author:      travelpont.hu
 * Text Domain: travelpont-kezdolap
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
define( 'TPK_VERSION', $tpk_plugin_data['Version'] );
define( 'TPK_PATH', plugin_dir_path( __FILE__ ) );
define( 'TPK_URL',  plugin_dir_url( __FILE__ ) );

// ── Modulok betöltése ─────────────────────────────────────────────────────────
require_once TPK_PATH . 'includes/content-helpers.php';
require_once TPK_PATH . 'includes/chrome.php';
require_once TPK_PATH . 'includes/template-loader.php';
require_once TPK_PATH . 'includes/settings.php';

// ── Betűtípusok + stílus – minden, a plugin által kezelt oldalon ──────────────
// (lásd tpk_is_managed_request() az includes/template-loader.php-ban)
add_action( 'wp_head', function() {
    if ( ! tpk_is_managed_request() ) return;
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );

add_action( 'wp_enqueue_scripts', function() {
    if ( ! tpk_is_managed_request() ) return;

    wp_enqueue_style(
        'tpk-google-fonts',
        'https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap',
        array(), null
    );
    wp_enqueue_style(
        'travelpont-kezdolap',
        TPK_URL . 'assets/css/frontend.css',
        array( 'tpk-google-fonts' ), TPK_VERSION
    );
    wp_enqueue_script(
        'travelpont-kezdolap',
        TPK_URL . 'assets/js/frontend.js',
        array(), TPK_VERSION, true
    );
} );
