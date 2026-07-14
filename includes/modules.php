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
            );
        case 'uticelok':
            return array(
                'eyebrow'          => 'Úticélok',
                'cim'              => 'Ahova idén mindenki készül',
                'darab'            => 3,
                'valogatas'        => 'auto',
                'kivalasztott_idk' => array(),
                'ures_szoveg'      => 'Jelenleg nincs feltöltött úticél.',
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
            );
        case 'utikalauz':
            return array(
                'eyebrow'     => 'Útikalauz',
                'cim'         => 'Olvasnivaló indulás előtt',
                'link_szoveg' => 'Az összes cikk →',
                'darab'       => 3,
            );
        case 'zaro_cta':
            return array(
                'cim'            => 'Kész az utad? Nézz körbe az ajánlatok között.',
                'alcim'          => 'Kövess minket, hogy elsőként lásd az új ajánlatokat.',
                'gomb_szoveg'    => 'Nézd meg az összes ajánlatot',
                'instagram_link' => '',
                'facebook_link'  => '',
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

// ── A teljes konfiguráció betöltése (mentett vagy migrált) ────────────────────
function tpk_get_modulok() {
    static $cache = null;
    if ( null !== $cache ) return $cache;

    $mentett = get_option( 'tpk_modulok' );
    if ( ! is_array( $mentett ) || empty( $mentett['modulok'] ) || ! is_array( $mentett['modulok'] ) ) {
        $cache = tpk_modulok_migralt_defaults();
        return $cache;
    }

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
        $beallitasok = array_merge(
            tpk_modul_alap( $tipus ),
            isset( $modul['beallitasok'] ) && is_array( $modul['beallitasok'] ) ? $modul['beallitasok'] : array()
        );

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

    $cache = $config;
    return $cache;
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
