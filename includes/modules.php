<?php
/**
 * Travelpont Kezdőoldal – modul-rendszer
 *
 * A főoldal szekciói (hero, ajánlatok, úticélok, miért mi, útikalauz,
 * záró CTA) rendezhető/kapcsolható MODULOK: a `tpk_modulok` opció írja le
 * a sorrendet, az aktív állapotot és modulonként a beállításokat. A
 * `templates/front-page.php` egy loopban rendereli őket a
 * `templates/modules/*.php` sablonokból.
 *
 * Amíg a `tpk_modulok` opció nem létezik (a Portálból még nem mentettek),
 * az értékek a régi `tpk_settings`-ből + a kódbeli alapértékekből állnak
 * össze – így a kimenet bitre azonos a modulosítás előttivel, és a régi
 * WP-admin "Kezdőlap" oldal is működik az átmenet alatt.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Modul-típusonkénti alapbeállítások (= a korábbi hardcode-olt értékek) ─────
function tpk_modul_alap( $tipus ) {
    switch ( $tipus ) {
        case 'hero':
            return array(
                'badge_szoveg' => 'Kézzel válogatott utazási ajánlatok',
                'cim'          => 'Fedezd fel a világot, <span class="tpk-accent-word">okosabban</span> foglalva.',
                'alcim'        => 'A Travelpont naponta válogatja a legjobb repjegy- és szállás-kombinációkat azoknak, akik szeretnek utazni, de nem szeretnek órákat böngészni az árak után.',
                'cta_szoveg'   => 'Nézd meg az ajánlatokat',
                'megjegyzes'   => 'Ingyenes · nincs regisztráció',
                'hero_kep_id'  => 0,
                'megjelenes'   => array( 'kep_illesztes' => 'kivagas', 'hatter' => 'kek' ),
            );
        case 'ajanlatok':
            return array(
                'eyebrow'          => 'Kiemelt ajánlatok',
                'cim'              => 'Ez a hét legjobb dobása',
                'link_szoveg'      => 'Összes ajánlat →',
                'darab'            => 6,
                'valogatas'        => 'auto',
                'kivalasztott_idk' => array(),
                'ures_szoveg'      => 'Jelenleg nincs feltöltött ajánlat – nézz vissza hamarosan!',
                'megjelenes'       => array( 'kep_arany' => 'kivagas', 'oszlopok' => 3, 'hatter' => 'feher' ),
            );
        case 'uticelok':
            return array(
                'eyebrow'          => 'Úticélok',
                'cim'              => 'Ahova idén mindenki készül',
                'darab'            => 3,
                'valogatas'        => 'auto',
                'kivalasztott_idk' => array(),
                'ures_szoveg'      => 'Jelenleg nincs feltöltött úticél.',
                'megjelenes'       => array( 'stilus' => 'magazin', 'oszlopok' => 3, 'hatter' => 'sotet' ),
            );
        case 'miert_mi':
            return array(
                'eyebrow' => 'Miért mi?',
                'cim'     => 'Nem robotok válogatnak, hanem mi',
                'pontok'  => array(
                    array( 'title' => 'Valódi ember válogat', 'text' => 'Minden ajánlatot élő utazási szakértő néz át, mielőtt kikerül az oldalra.', 'ikon' => 'circle' ),
                    array( 'title' => 'Átlátható árbontás', 'text' => 'Mindig külön látod a repjegy és a szállás árát – nincs elrejtett tétel.', 'ikon' => 'square' ),
                    array( 'title' => 'Folyamatosan frissítve', 'text' => 'Naponta figyeljük a piacot, hogy a legjobb ajánlatok kerüljenek fel.', 'ikon' => 'loader' ),
                    array( 'title' => 'Megbízható partnerek', 'text' => 'Csak ellenőrzött légitársaságokkal és szállásokkal dolgozunk együtt.', 'ikon' => 'bag' ),
                ),
                'megjelenes' => array( 'hatter' => 'feher' ),
            );
        case 'utikalauz':
            return array(
                'eyebrow'     => 'Útikalauz',
                'cim'         => 'Olvasnivaló indulás előtt',
                'link_szoveg' => 'Az összes cikk →',
                'darab'       => 3,
                'megjelenes'  => array( 'kep_arany' => 'kivagas', 'oszlopok' => 3, 'hatter' => 'vilagos' ),
            );
        case 'zaro_cta':
            return array(
                'cim'            => 'Kész az utad? Nézz körbe az ajánlatok között.',
                'alcim'          => 'Kövess minket, hogy elsőként lásd az új ajánlatokat.',
                'gomb_szoveg'    => 'Nézd meg az összes ajánlatot',
                'instagram_link' => '',
                'facebook_link'  => '',
                'megjelenes'     => array( 'hatter' => 'sotet' ),
            );
        case 'szabad':
            return array(
                'hatter'        => 'vilagos',
                'tartalom_html' => '',
            );
    }
    return array();
}

// ── A rendezhető modul-típusok (a 'szabad' többpéldányos, a többi singleton) ──
function tpk_modul_tipusok() {
    return array( 'hero', 'ajanlatok', 'uticelok', 'miert_mi', 'utikalauz', 'zaro_cta', 'szabad' );
}

// ── Teljes alapértelmezett konfiguráció (mai sorrend, minden aktív) ───────────
function tpk_modulok_defaults() {
    $modulok = array();
    foreach ( array( 'hero', 'ajanlatok', 'uticelok', 'miert_mi', 'utikalauz', 'zaro_cta' ) as $tipus ) {
        $modulok[] = array(
            'tipus'       => $tipus,
            'id'          => $tipus,
            'aktiv'       => true,
            'beallitasok' => tpk_modul_alap( $tipus ),
        );
    }
    return array(
        'verzio'  => 1,
        'chrome'  => array(
            'logo_kep_id'    => 0,
            'nav_cta_szoveg' => 'Ajánlatok böngészése',
        ),
        'modulok' => $modulok,
    );
}

// ── Migrációs nézet: a defaults feltöltve a régi tpk_settings értékeivel ──────
// (Nem ír az adatbázisba – a tpk_modulok opciót először a Portál PUT-ja hozza
// létre. Addig a régi WP-admin oldal mentései is azonnal érvényesülnek.)
function tpk_modulok_migralt_defaults() {
    $config = tpk_modulok_defaults();
    if ( ! function_exists( 'tpk_get_settings' ) ) return $config;
    $s = tpk_get_settings();

    $config['chrome']['logo_kep_id']    = (int) $s['logo_kep_id'];
    $config['chrome']['nav_cta_szoveg'] = $s['nav_cta_szoveg'];

    $atemeles = array(
        'hero'      => array(
            'badge_szoveg' => $s['hero_badge_szoveg'],
            'cim'          => $s['hero_cim'],
            'alcim'        => $s['hero_alcim'],
            'cta_szoveg'   => $s['hero_cta_szoveg'],
            'hero_kep_id'  => (int) $s['hero_kep_id'],
        ),
        'ajanlatok' => array( 'ures_szoveg' => $s['ajanlatok_ures_szoveg'] ),
        'uticelok'  => array( 'ures_szoveg' => $s['uticelok_ures_szoveg'] ),
        'miert_mi'  => array( 'pontok' => $s['miert_mi'] ),
        'zaro_cta'  => array(
            'cim'            => $s['zaro_cta_cim'],
            'alcim'          => $s['zaro_cta_alcim'],
            'gomb_szoveg'    => $s['zaro_cta_gomb_szoveg'],
            'instagram_link' => $s['instagram_link'],
            'facebook_link'  => $s['facebook_link'],
        ),
    );

    foreach ( $config['modulok'] as &$modul ) {
        if ( isset( $atemeles[ $modul['tipus'] ] ) ) {
            $modul['beallitasok'] = array_merge( $modul['beallitasok'], $atemeles[ $modul['tipus'] ] );
        }
    }
    unset( $modul );

    return $config;
}

// ── A teljes konfiguráció betöltése (előnézet > mentett > migrált) ────────────
function tpk_get_modulok() {
    static $cache = null;
    if ( null !== $cache ) return $cache;

    $forras = null;

    // Élő előnézet: a Portál "👁️ Előnézet" gombja token-alapú vázlat-configot
    // tesz transientbe (tpk_api_elonezet) — érvényes tokennél AZT rendereljük,
    // az opcióhoz nem nyúlunk. A cache/noindex védelem a template-loader.php-ban.
    if ( isset( $_GET['tpk_elonezet'] ) ) {
        $token = preg_replace( '/[^A-Za-z0-9]/', '', (string) $_GET['tpk_elonezet'] );
        if ( $token ) {
            $vazlat = get_transient( 'tpk_elonezet_' . $token );
            if ( is_array( $vazlat ) && ! empty( $vazlat['modulok'] ) && is_array( $vazlat['modulok'] ) ) {
                $forras = $vazlat;
            }
        }
    }

    if ( null === $forras ) {
        $mentett = get_option( 'tpk_modulok' );
        if ( is_array( $mentett ) && ! empty( $mentett['modulok'] ) && is_array( $mentett['modulok'] ) ) {
            $forras = $mentett;
        }
    }

    if ( null === $forras ) {
        $cache = tpk_modulok_migralt_defaults();
        return $cache;
    }

    $cache = tpk_config_normalizalas( $forras );
    return $cache;
}

// ── Mentett/vázlat konfiguráció normalizálása (defaults-fésülés, pótlás) ──────
function tpk_config_normalizalas( $mentett ) {
    $defaults = tpk_modulok_defaults();
    $config   = array(
        'verzio'  => isset( $mentett['verzio'] ) ? (int) $mentett['verzio'] : 1,
        'chrome'  => array_merge( $defaults['chrome'], isset( $mentett['chrome'] ) && is_array( $mentett['chrome'] ) ? $mentett['chrome'] : array() ),
        'modulok' => array(),
    );

    $ervenyes_tipusok = tpk_modul_tipusok();
    $megvan           = array();

    foreach ( $mentett['modulok'] as $modul ) {
        if ( ! is_array( $modul ) || empty( $modul['tipus'] ) ) continue;
        $tipus = $modul['tipus'];
        if ( ! in_array( $tipus, $ervenyes_tipusok, true ) ) continue;
        if ( 'szabad' !== $tipus && isset( $megvan[ $tipus ] ) ) continue; // duplikált singleton: az első nyer

        // Modulonkénti mély default-fésülés: régi mentés + új mező sosem ad üres mezőt
        $alap      = tpk_modul_alap( $tipus );
        $mentett_b = isset( $modul['beallitasok'] ) && is_array( $modul['beallitasok'] ) ? $modul['beallitasok'] : array();
        $beallitasok = array_merge( $alap, $mentett_b );

        // A 'megjelenes' AL-TÖMB külön fésülést kap (az array_merge sekély —
        // megjelenes nélküli régi mentésnél is teljes kulcskészlet kell)
        if ( isset( $alap['megjelenes'] ) ) {
            $beallitasok['megjelenes'] = array_merge(
                $alap['megjelenes'],
                isset( $mentett_b['megjelenes'] ) && is_array( $mentett_b['megjelenes'] ) ? $mentett_b['megjelenes'] : array()
            );
        }

        $config['modulok'][] = array(
            'tipus'       => $tipus,
            'id'          => ! empty( $modul['id'] ) ? (string) $modul['id'] : $tipus,
            'aktiv'       => ! empty( $modul['aktiv'] ),
            'beallitasok' => $beallitasok,
        );
        $megvan[ $tipus ] = true;
    }

    // Hiányzó singleton modul pótlása a defaultból (a főoldal sosem csonkulhat
    // egy hibás mentés miatt) – a lista végére kerül, alapértelmezetten aktívan
    foreach ( $defaults['modulok'] as $default_modul ) {
        if ( ! isset( $megvan[ $default_modul['tipus'] ] ) ) {
            $config['modulok'][] = $default_modul;
        }
    }

    return $config;
}

// ── 🎨 Megjelenés-érték kiolvasása (defaulttal) ────────────────────────────────
function tpk_megj( $tpk_b, $kulcs, $alap = '' ) {
    return isset( $tpk_b['megjelenes'][ $kulcs ] ) && '' !== $tpk_b['megjelenes'][ $kulcs ]
        ? $tpk_b['megjelenes'][ $kulcs ]
        : $alap;
}

// ── 🎨 Módosító CSS-osztályok a szekció-tagre ─────────────────────────────────
// KRITIKUS: alapértelmezett értékeknél ÜRES stringet ad — így a default
// konfiguráció kimenete bitre azonos marad a módosítók bevezetése előttivel.
function tpk_megj_osztalyok( $tipus, $tpk_b ) {
    $alap = tpk_modul_alap( $tipus );
    if ( empty( $alap['megjelenes'] ) ) return '';

    $osztalyok = array();
    foreach ( $alap['megjelenes'] as $kulcs => $default ) {
        $ertek = tpk_megj( $tpk_b, $kulcs, $default );
        if ( (string) $ertek === (string) $default ) continue;

        switch ( $kulcs ) {
            case 'hatter':
                $osztalyok[] = 'tpk-hatter-' . $ertek;
                break;
            case 'oszlopok':
                $osztalyok[] = 'tpk-oszlop-' . (int) $ertek;
                break;
            case 'kep_arany':
            case 'kep_illesztes':
                if ( 'teljes' === $ertek ) $osztalyok[] = 'tpk-kep-teljes';
                break;
            case 'stilus':
                $osztalyok[] = 'tpk-dstilus-' . $ertek;
                break;
        }
    }

    if ( ! $osztalyok ) return '';
    return ' ' . implode( ' ', array_map( 'sanitize_html_class', $osztalyok ) );
}

// ── Az aktív szabad szekciók összefűzött tartalma (enqueue-döntésekhez) ───────
function tpk_szabad_tartalom_osszes() {
    $ossz = '';
    foreach ( tpk_get_modulok()['modulok'] as $modul ) {
        if ( 'szabad' === $modul['tipus'] && ! empty( $modul['aktiv'] ) ) {
            $ossz .= (string) $modul['beallitasok']['tartalom_html'];
        }
    }
    return $ossz;
}

// ── Egy modul kirajzolása (típus→sablon whitelist, sosem fatal) ───────────────
function tpk_render_modul( $tpk_modul ) {
    if ( ! is_array( $tpk_modul ) || empty( $tpk_modul['aktiv'] ) ) return;

    $sablonok = array(
        'hero'      => 'hero.php',
        'ajanlatok' => 'ajanlatok.php',
        'uticelok'  => 'uticelok.php',
        'miert_mi'  => 'miert-mi.php',
        'utikalauz' => 'utikalauz.php',
        'zaro_cta'  => 'zaro-cta.php',
        'szabad'    => 'szabad.php',
    );

    $tipus = isset( $tpk_modul['tipus'] ) ? $tpk_modul['tipus'] : '';
    if ( ! isset( $sablonok[ $tipus ] ) ) return;

    $sablon = TPK_PATH . 'templates/modules/' . $sablonok[ $tipus ];
    if ( ! file_exists( $sablon ) ) return;

    $tpk_b = $tpk_modul['beallitasok'];
    include $sablon;
}
