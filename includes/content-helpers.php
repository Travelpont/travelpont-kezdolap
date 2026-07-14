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
// $ids megadásával (Portál "kézi kiválasztás") csak a felsorolt ajánlatok
// jönnek, a megadott sorrendben – a lejárat-szűrő ekkor is érvényes, így a
// kitűzött, de közben lejárt ajánlat automatikusan kimarad.
function tpk_get_ajanlatok( $limit = 6, $ids = array() ) {
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

    if ( ! empty( $ids ) ) {
        $args['post__in'] = array_map( 'absint', (array) $ids );
        $args['orderby']  = 'post__in';
        unset( $args['order'] );
    }

    return new WP_Query( apply_filters( 'tpk_ajanlatok_query_args', $args ) );
}

// ── Legfelső szintű Úticélok (országok) ───────────────────────────────────────
// $ids megadásával (Portál "kézi kiválasztás") csak a felsorolt úticélok
// jönnek, a megadott sorrendben – ilyenkor a legfelső-szint megkötés sem él,
// tehát akár régió/város is kitűzhető.
function tpk_get_orszagok( $limit = 3, $ids = array() ) {
    if ( ! post_type_exists( 'uticel' ) ) return array();

    $args = array(
        'post_type'      => 'uticel',
        'post_status'    => 'publish',
        'post_parent'    => 0,
        'posts_per_page' => $limit,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    );

    if ( ! empty( $ids ) ) {
        $args['post__in'] = array_map( 'absint', (array) $ids );
        $args['orderby']  = 'post__in';
        unset( $args['post_parent'], $args['order'] );
    }

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
            'text'  => 'Mindig külön látod az utazás és a szállás árát – nincs elrejtett tétel.',
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

// ── Al-oldalak URL-jei – ha a megfelelő Oldal még nincs létrehozva, a nav       ──
// ── horgony-linkre esik vissza, hogy a plugin ne törjön el nélküle ────────────
// (A modul-sablonok külön-külön hívják őket, ezért kérésen belül statikusan
// gyorsítótárazottak – az oldal-feloldás csak egyszer fut le.)
function tpk_ajanlatok_url() {
    static $url = null;
    if ( null !== $url ) return $url;
    $slug = apply_filters( 'tpk_ajanlatok_oldal_slug', 'ajanlatok' );
    $page = get_page_by_path( $slug );
    $url  = $page ? get_permalink( $page ) : '#offers';
    return $url;
}

function tpk_uticelok_url() {
    static $url = null;
    if ( null !== $url ) return $url;
    $slug = apply_filters( 'tpk_uticelok_oldal_slug', 'uticelok' );
    $page = get_page_by_path( $slug );
    $url  = $page ? get_permalink( $page ) : '#destinations';
    return $url;
}

function tpk_utikalauz_url() {
    static $url = null;
    if ( null !== $url ) return $url;
    $page_for_posts = (int) get_option( 'page_for_posts' );
    if ( $page_for_posts ) {
        $url = get_permalink( $page_for_posts );
        return $url;
    }

    $slug = apply_filters( 'tpk_utikalauz_oldal_slug', 'utikalauz' );
    $page = get_page_by_path( $slug );
    $url  = $page ? get_permalink( $page ) : '#blog';
    return $url;
}

// ── Rólunk / Kapcsolat – nincs horgony-fallback rájuk, ha nincs oldal, nem      ──
// ── jelenik meg a nav-linkjük (lásd templates/front-page.php) ─────────────────
function tpk_rolunk_url() {
    $slug = apply_filters( 'tpk_rolunk_oldal_slug', 'rolunk' );
    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : '';
}

function tpk_kapcsolat_url() {
    $slug = apply_filters( 'tpk_kapcsolat_oldal_slug', 'kapcsolat' );
    $page = get_page_by_path( $slug );
    return $page ? get_permalink( $page ) : '';
}

// ── Márka-képek – a modul-konfigurációból (tpk_modulok, ill. amíg az nincs, ───
// ── a régi tpk_settings migrált értékeiből); ha nincs feltöltve kép, üres ─────
// ── string, a sablon a CSS-placeholderre esik vissza ──────────────────────────
function tpk_logo_url() {
    $id = (int) tpk_get_modulok()['chrome']['logo_kep_id'];
    return $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
}

function tpk_hero_kep_url() {
    $id = 0;
    foreach ( tpk_get_modulok()['modulok'] as $modul ) {
        if ( 'hero' === $modul['tipus'] ) {
            $id = (int) $modul['beallitasok']['hero_kep_id'];
            break;
        }
    }
    return $id ? wp_get_attachment_image_url( $id, 'large' ) : '';
}
