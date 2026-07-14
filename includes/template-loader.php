<?php
/**
 * Travelpont Kezdőoldal – sablon-betöltés
 *
 * A plugin a `template_include` szűrővel a FŐOLDALON a téma sablonjait
 * megkerülve adja a TELJES HTML dokumentumot (`templates/front-page.php`),
 * a `wp_head()`/`wp_footer()` hívásokkal együtt – így témaváltás esetén is
 * működik (Twenty Twenty-Five / Hello Elementor / Kadence alatt is), és más
 * pluginek (SEO stb.) is rendben belekerülnek az oldalba.
 *
 * 2026-07-09 STRATÉGIAI FORDULAT: a plugin átvétele leszűkült KIZÁRÓLAG a
 * főoldalra. A többi oldalt (Rólunk / Kapcsolat / Ajánlatok–Úticélok lista,
 * Útikalauz index, egyedi Ajánlat / Úticél / bejegyzés) mostantól a TÉMA
 * (Kadence) rendereli, hogy a felhasználó vizuálisan, kód nélkül
 * szerkeszthesse a fejlécet / láblécet / oldalakat. Ez biztonságos: az
 * Ajánlatok/Úticélok pluginek a `the_content` szűrővel fűzik be a saját
 * dobozukat (téma-független), a lista-oldalak pedig shortcode-dal jelennek
 * meg. A korábbi `templates/page-wrapper.php` (nem-főoldali keret) ezzel
 * kikerült a használatból – a fájl megmarad, de már nem hívódik meg.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Melyik kéréseket kezeli (veszi át) a plugin a téma helyett? ───────────
// Immár KIZÁRÓLAG a főoldalt. Ezt a gate-et használja a fő plugin-fájl is a
// betűtípus / CSS / JS betöltéséhez, így a plugin stílusa NEM szennyezi a
// téma (Kadence) által rendezett többi oldalt.
function tpk_is_managed_request() {
    return is_front_page();
}

add_filter( 'template_include', function( $template ) {
    if ( is_front_page() ) {
        $custom = TPK_PATH . 'templates/front-page.php';
        return file_exists( $custom ) ? $custom : $template;
    }

    return $template;
}, 20 );

// ── Élő előnézet (?tpk_elonezet=TOKEN) védelme ────────────────────────────
// A vázlat-render sosem kerülhet az oldal-cache-be (különben a látogatók a
// vázlatot kapnák, ill. a szerkesztő a cache-elt élő oldalt a vázlat helyett),
// és a keresők sem indexelhetik.
add_action( 'template_redirect', function() {
    if ( ! isset( $_GET['tpk_elonezet'] ) || ! tpk_is_managed_request() ) return;
    nocache_headers();
    do_action( 'litespeed_control_set_nocache' ); // LiteSpeed Cache kizárás
} );

add_action( 'wp_head', function() {
    if ( ! isset( $_GET['tpk_elonezet'] ) || ! tpk_is_managed_request() ) return;
    echo '<meta name="robots" content="noindex,nofollow">' . "\n";
}, 1 );
