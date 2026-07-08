<?php
/**
 * Travelpont Kezdőoldal – sablon-betöltés
 *
 * SZÁNDÉKOS ELTÉRÉS a Travelpont Ajánlatok/Úticélok mintától: azok a
 * the_content szűrővel fűznek dobozt a MEGLÉVŐ téma-elrendezés köré (ez
 * teszi őket téma-függetlenné). A kezdőlap mockupja viszont saját navigációt
 * és footert is hoz – ezt nem lehet egy téma nav/footer köré illeszteni
 * anélkül, hogy duplikálódna. Ezért itt a `template_include` szűrővel a
 * plugin adja a TELJES HTML dokumentumot, a téma header.php/footer.php-ját
 * megkerülve – a főoldalon a `templates/front-page.php`-t, MINDEN MÁS
 * kezelt oldalon (Oldalak, egyedi Ajánlat/Úticél/bejegyzés, a bejegyzés-
 * index) a `templates/page-wrapper.php`-t, hogy a teljes site egységes,
 * brandelt fejlécet/láblécet kapjon, ne csak a főoldal.
 *
 * Ez attól még ugyanúgy téma-független marad: nem nyúl a téma fájljaihoz,
 * témaváltás esetén is működik (Twenty Twenty-Five vagy Hello Elementor
 * alatt is), és a wp_head()/wp_footer() hívásokkal minden más plugin
 * (SEO, statisztika stb.) továbbra is rendben belekerül az oldalba.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Melyik kéréseket adja a plugin a téma helyett? ────────────────────────
function tpk_is_managed_request() {
    return is_front_page()
        || is_page()
        || is_home()
        || is_singular( array( 'post', 'ajanlat', 'uticel' ) );
}

add_filter( 'template_include', function( $template ) {
    if ( is_front_page() ) {
        $custom = TPK_PATH . 'templates/front-page.php';
        return file_exists( $custom ) ? $custom : $template;
    }

    if ( tpk_is_managed_request() ) {
        $custom = TPK_PATH . 'templates/page-wrapper.php';
        return file_exists( $custom ) ? $custom : $template;
    }

    return $template;
}, 20 );
