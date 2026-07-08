<?php
/**
 * Travelpont Kezdőoldal – tartalom-lekérő segédfüggvények
 *
 * A `templates/front-page.php` innen kéri le a valós adatokat (Ajánlatok,
 * Úticélok, Blog cikkek). A statikus "Miért mi?" szöveg is itt van, EGY
 * helyen – a Travelpont Ajánlatok/Úticélok fields.php-jának szellemében,
 * hookkal felülírhatóan (bár ez nem CPT-mező, csak szöveg-tömb).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Kiemelt ajánlatok (max. 6, a lejártak nélkül) ─────────────────────────────
function tpk_get_ajanlatok( $limit = 6 ) {
    if ( ! post_type_exists( 'ajanlat' ) ) return array();

    $args = array(
        'post_type'      => 'ajanlat',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => array(
            'relation' => 'OR',
            array( 'key' => 'tpa_ervenyes', 'value' => current_time( 'Y-m-d' ), 'compare' => '>=', 'type' => 'DATE' ),
            array( 'key' => 'tpa_ervenyes', 'compare' => 'NOT EXISTS' ),
            array( 'key' => 'tpa_ervenyes', 'value' => '', 'compare' => '=' ),
        ),
    );

    return new WP_Query( apply_filters( 'tpk_ajanlatok_query_args', $args ) );
}

// ── Legfelső szintű Úticélok (országok) ───────────────────────────────────────
function tpk_get_orszagok( $limit = 3 ) {
    if ( ! post_type_exists( 'uticel' ) ) return array();

    $args = array(
        'post_type'      => 'uticel',
        'post_status'    => 'publish',
        'post_parent'    => 0,
        'posts_per_page' => $limit,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    );

    return new WP_Query( apply_filters( 'tpk_orszagok_query_args', $args ) );
}

// ── Legutóbbi Blog cikkek (natív bejegyzések, amíg nincs önálló Blog CPT) ─────
function tpk_get_cikkek( $limit = 3 ) {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    return new WP_Query( apply_filters( 'tpk_cikkek_query_args', $args ) );
}

// ── "Miért mi?" – statikus, 4 pontos rács ─────────────────────────────────────
function tpk_miert_mi_pontok() {
    $pontok = array(
        array(
            'title' => 'Valódi ember válogat',
            'text'  => 'Minden ajánlatot élő utazási szakértő néz át, mielőtt kikerül az oldalra.',
            'ikon'  => 'circle',
        ),
        array(
            'title' => 'Átlátható árbontás',
            'text'  => 'Mindig külön látod a repjegy és a szállás árát – nincs elrejtett tétel.',
            'ikon'  => 'square',
        ),
        array(
            'title' => 'Folyamatosan frissítve',
            'text'  => 'Naponta figyeljük a piacot, hogy a legjobb ajánlatok kerüljenek fel.',
            'ikon'  => 'loader',
        ),
        array(
            'title' => 'Megbízható partnerek',
            'text'  => 'Csak ellenőrzött légitársaságokkal és szállásokkal dolgozunk együtt.',
            'ikon'  => 'bag',
        ),
    );

    return apply_filters( 'tpk_miert_mi_pontok', $pontok );
}

// ── Ajánlat-kártya "címke" – az első ajánlat-kategória neve, ha van ───────────
function tpk_ajanlat_cimke( $post_id ) {
    if ( ! taxonomy_exists( 'ajanlat_kategoria' ) ) return '';
    $terms = get_the_terms( $post_id, 'ajanlat_kategoria' );
    if ( ! $terms || is_wp_error( $terms ) ) return '';
    return current( $terms )->name;
}
