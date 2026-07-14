<?php
/**
 * Travelpont Kezdőoldal – REST API
 *
 * Prefix: /wp-json/tpk/v1/
 *
 *   GET  /tpk/v1/kezdolap      – A teljes modul-konfiguráció (feloldott
 *                                kép-URL-ekkel és a kézzel kiválasztott
 *                                ajánlatok/úticélok cím+kép adataival)
 *   PUT  /tpk/v1/kezdolap      – A teljes konfiguráció mentése (mindig az
 *                                egész objektum – nincs részleges patch)
 *   POST /tpk/v1/kezdolap/kep  – Kép sideload URL-ből (hero / logó) →
 *                                attachment_id; az opcióba NEM ír, az ID-t
 *                                a Portál teszi a konfigurációba és menti
 *   GET  /tpk/v1/status        – Publikus ping (verzió, deploy-ellenőrzés)
 *
 * Auth: WordPress Application Password (Basic Auth) + publish_posts capability
 * (a Travelpont Portal Firebase Cloud Function proxyja hívja, sosem a böngésző
 * közvetlenül) – a tpu/tpa REST API-kkal azonos minta.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'rest_api_init', function() {

    register_rest_route( 'tpk/v1', '/kezdolap', array(
        array(
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'tpk_api_get',
            'permission_callback' => 'tpk_api_auth',
        ),
        array(
            'methods'             => WP_REST_Server::EDITABLE,
            'callback'            => 'tpk_api_save',
            'permission_callback' => 'tpk_api_auth',
        ),
    ) );

    register_rest_route( 'tpk/v1', '/kezdolap/elonezet', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'tpk_api_elonezet',
        'permission_callback' => 'tpk_api_auth',
    ) );

    register_rest_route( 'tpk/v1', '/kezdolap/kep', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'tpk_api_sideload_image',
        'permission_callback' => 'tpk_api_auth',
        'args'                => array(
            'url' => array( 'type' => 'string', 'required' => true ),
        ),
    ) );

    register_rest_route( 'tpk/v1', '/status', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'tpk_api_status',
        'permission_callback' => '__return_true',
    ) );
} );

// ── Auth ───────────────────────────────────────────────────────────────────────
function tpk_api_auth() {
    return current_user_can( 'publish_posts' );
}

// ── GET /tpk/v1/kezdolap ────────────────────────────────────────────────────────
function tpk_api_get() {
    return rest_ensure_response( tpk_api_format( tpk_get_modulok() ) );
}

// ── A konfiguráció API-válasz formátuma (feloldott képek + pin-adatok) ─────────
function tpk_api_format( $config ) {
    $logo_id = (int) $config['chrome']['logo_kep_id'];
    $config['chrome']['logo_url'] = $logo_id ? ( wp_get_attachment_image_url( $logo_id, 'medium' ) ?: '' ) : '';

    foreach ( $config['modulok'] as &$modul ) {
        if ( 'hero' === $modul['tipus'] ) {
            $kep_id = (int) $modul['beallitasok']['hero_kep_id'];
            $modul['beallitasok']['hero_kep_url'] = $kep_id ? ( wp_get_attachment_image_url( $kep_id, 'large' ) ?: '' ) : '';
        }
        if ( in_array( $modul['tipus'], array( 'ajanlatok', 'uticelok' ), true ) ) {
            $modul['beallitasok']['kivalasztott'] = tpk_api_pin_adatok(
                $modul['beallitasok']['kivalasztott_idk'],
                'ajanlatok' === $modul['tipus'] ? 'ajanlat' : 'uticel'
            );
        }
    }
    unset( $modul );

    return $config;
}

// A kézzel kiválasztott bejegyzések megjelenítési adatai a Portál-listához.
function tpk_api_pin_adatok( $ids, $post_type ) {
    $adatok = array();
    foreach ( (array) $ids as $id ) {
        $post = get_post( (int) $id );
        if ( ! $post || $post->post_type !== $post_type ) continue;
        $adatok[] = array(
            'id'            => (int) $id,
            'title'         => $post->post_title,
            'status'        => $post->post_status,
            'thumbnail_url' => get_post_thumbnail_id( $id ) ? ( wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'medium' ) ?: '' ) : '',
        );
    }
    return $adatok;
}

// ── PUT /tpk/v1/kezdolap – Mentés ───────────────────────────────────────────────
function tpk_api_save( WP_REST_Request $req ) {
    $input = $req->get_json_params();
    if ( ! is_array( $input ) ) {
        return new WP_Error( 'invalid_body', 'Hiányzó vagy érvénytelen JSON törzs', array( 'status' => 400 ) );
    }

    // Méret-őr: a főoldal-konfiguráció sosem lehet ekkora – hibás kliens jele.
    if ( strlen( wp_json_encode( $input ) ) > 200 * 1024 ) {
        return new WP_Error( 'too_large', 'A konfiguráció túl nagy (max. 200 KB)', array( 'status' => 400 ) );
    }

    $config = tpk_sanitize_modulok( $input );
    update_option( 'tpk_modulok', $config, true );

    // LiteSpeed oldal-cache: a főoldal automatikus ürítése mentés után, hogy a
    // látogatók azonnal az új változatot lássák. Ha a LiteSpeed Cache plugin
    // nincs telepítve, a do_action egyszerűen nem csinál semmit.
    do_action( 'litespeed_purge_url', home_url( '/' ) );

    return rest_ensure_response( tpk_api_format( $config ) );
}

// ── POST /tpk/v1/kezdolap/elonezet – Vázlat-előnézet (mentés NÉLKÜL) ───────────
// A beküldött configot szanitizálva 15 percre transientbe teszi, és visszaadja
// a token-alapú előnézeti URL-t. Az opciót nem érinti — a front-end a
// tpk_get_modulok()-ban ismeri fel a tokent (lásd modules.php), a cache/noindex
// védelem a template-loader.php-ban van.
function tpk_api_elonezet( WP_REST_Request $req ) {
    $input = $req->get_json_params();
    if ( ! is_array( $input ) ) {
        return new WP_Error( 'invalid_body', 'Hiányzó vagy érvénytelen JSON törzs', array( 'status' => 400 ) );
    }
    if ( strlen( wp_json_encode( $input ) ) > 200 * 1024 ) {
        return new WP_Error( 'too_large', 'A konfiguráció túl nagy (max. 200 KB)', array( 'status' => 400 ) );
    }

    $config = tpk_sanitize_modulok( $input );
    $token  = wp_generate_password( 20, false, false );
    set_transient( 'tpk_elonezet_' . $token, $config, 15 * MINUTE_IN_SECONDS );

    return rest_ensure_response( array(
        'url'         => add_query_arg( 'tpk_elonezet', $token, home_url( '/' ) ),
        'lejarat_perc' => 15,
    ) );
}

// ── A teljes konfiguráció szanitizálása ────────────────────────────────────────
// Elv: ismeretlen kulcs/típus eldobva, hiányzó singleton modul defaultból
// pótolva (tpk_get_modulok() amúgy is pótolná) – csonka konfiguráció sosem
// mentődik, a főoldal sosem törhet el egy hibás mentéstől.
function tpk_sanitize_modulok( $input ) {
    $config = array(
        'verzio'  => 1,
        'chrome'  => array(
            'logo_kep_id'    => absint( $input['chrome']['logo_kep_id'] ?? 0 ),
            'nav_cta_szoveg' => sanitize_text_field( $input['chrome']['nav_cta_szoveg'] ?? 'Ajánlatok böngészése' ),
        ),
        'stilus'  => tpk_sanitize_stilus( isset( $input['stilus'] ) && is_array( $input['stilus'] ) ? $input['stilus'] : array() ),
        'modulok' => array(),
    );

    $megvan = array();
    $lista  = isset( $input['modulok'] ) && is_array( $input['modulok'] ) ? $input['modulok'] : array();

    foreach ( $lista as $modul ) {
        if ( ! is_array( $modul ) || empty( $modul['tipus'] ) ) continue;
        $tipus = $modul['tipus'];
        if ( ! in_array( $tipus, tpk_modul_tipusok(), true ) ) continue;
        if ( 'szabad' !== $tipus && isset( $megvan[ $tipus ] ) ) continue;

        $id = isset( $modul['id'] ) ? preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $modul['id'] ) ) : '';
        if ( '' === $id || strlen( $id ) > 40 ) {
            $id = 'szabad' === $tipus ? 'szabad_' . substr( uniqid(), -6 ) : $tipus;
        }

        $config['modulok'][] = array(
            'tipus'       => $tipus,
            'id'          => $id,
            'aktiv'       => ! empty( $modul['aktiv'] ),
            'beallitasok' => tpk_sanitize_modul_beallitasok(
                $tipus,
                isset( $modul['beallitasok'] ) && is_array( $modul['beallitasok'] ) ? $modul['beallitasok'] : array()
            ),
        );
        $megvan[ $tipus ] = true;
    }

    // Hiányzó singleton pótlása a defaultból, hogy a mentett opció is teljes legyen
    foreach ( tpk_modulok_defaults()['modulok'] as $default_modul ) {
        if ( ! isset( $megvan[ $default_modul['tipus'] ] ) ) {
            $config['modulok'][] = $default_modul;
        }
    }

    return $config;
}

// ── "Miért mi?" ikon-érték validálása ──────────────────────────────────────────
// Három érvényes forma: régi CSS-ikon, beépített SVG (svg:nev — csak létező
// fájl), szabad emoji (emoji:… max 16 bájt). Minden más → default.
function tpk_sanitize_ikon( $ikon, $default ) {
    if ( in_array( $ikon, array( 'circle', 'square', 'loader', 'bag' ), true ) ) {
        return $ikon;
    }
    if ( preg_match( '/^svg:([a-z0-9-]{1,32})$/', $ikon, $m )
        && file_exists( TPK_PATH . 'assets/icons/miert-mi/' . $m[1] . '.svg' ) ) {
        return $ikon;
    }
    if ( 0 === strpos( $ikon, 'emoji:' ) ) {
        $emoji = sanitize_text_field( substr( $ikon, 6 ) );
        if ( '' !== $emoji && strlen( $emoji ) <= 16 ) {
            return 'emoji:' . $emoji;
        }
    }
    return $default;
}

// ── 🎨 Globális márka-paletta szanitizálása (csak hex vagy üres) ───────────────
function tpk_sanitize_stilus( $input ) {
    $ki = array();
    foreach ( tpk_stilus_alap() as $kulcs => $default ) {
        $ertek = (string) ( $input[ $kulcs ] ?? '' );
        $ki[ $kulcs ] = preg_match( '/^#[0-9a-fA-F]{6}$/', $ertek ) ? $ertek : '';
    }
    return $ki;
}

// ── 🎨 Megjelenés-beállítások szanitizálása (típusonkénti enum-whitelist) ──────
function tpk_sanitize_megjelenes( $tipus, $input, $alap ) {
    // Az 'egyeni' minden típusnál engedélyezett (a színt a hatter_szin adja)
    $hatterek = array(
        'hero'      => array( 'kek', 'feher', 'vilagos', 'egyeni' ),
        'ajanlatok' => array( 'feher', 'kek', 'vilagos', 'egyeni' ),
        'uticelok'  => array( 'sotet', 'feher', 'kek', 'egyeni' ),
        'miert_mi'  => array( 'feher', 'kek', 'vilagos', 'egyeni' ),
        'utikalauz' => array( 'vilagos', 'feher', 'kek', 'egyeni' ),
        'zaro_cta'  => array( 'sotet', 'bezs', 'egyeni' ),
    );
    $oszlopok = array(
        'ajanlatok' => array( 2, 3 ),
        'uticelok'  => array( 2, 3, 4 ),
        'utikalauz' => array( 2, 3 ),
    );

    $ki = array();
    foreach ( $alap as $kulcs => $default ) {
        $ertek = $input[ $kulcs ] ?? $default;
        switch ( $kulcs ) {
            case 'hatter':
                $ki[ $kulcs ] = in_array( $ertek, $hatterek[ $tipus ] ?? array(), true ) ? $ertek : $default;
                break;
            case 'hatter_szin':
                $ki[ $kulcs ] = preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $ertek ) ? (string) $ertek : '';
                break;
            case 'szoveg_szin':
                $ki[ $kulcs ] = in_array( $ertek, array( 'auto', 'sotet', 'vilagos' ), true ) ? $ertek : $default;
                break;
            case 'oszlopok':
                $ki[ $kulcs ] = in_array( (int) $ertek, $oszlopok[ $tipus ] ?? array(), true ) ? (int) $ertek : $default;
                break;
            case 'kep_arany':
            case 'kep_illesztes':
                $ki[ $kulcs ] = in_array( $ertek, array( 'kivagas', 'teljes' ), true ) ? $ertek : $default;
                break;
            case 'kep_oldal':
                $ki[ $kulcs ] = in_array( $ertek, array( 'jobb', 'bal' ), true ) ? $ertek : $default;
                break;
            case 'betumeret':
            case 'cim_meret':
                $ki[ $kulcs ] = in_array( $ertek, array( 'kisebb', 'normal', 'nagyobb' ), true ) ? $ertek : $default;
                break;
            case 'stilus':
                $ki[ $kulcs ] = in_array( $ertek, array( 'magazin', 'mozaik', 'kartya' ), true ) ? $ertek : $default;
                break;
            default:
                $ki[ $kulcs ] = $default;
        }
    }
    return $ki;
}

// ── Modulonkénti mező-szanitizálás (kulcs-szűrés a default-séma szerint) ───────
function tpk_sanitize_modul_beallitasok( $tipus, $input ) {
    $b = tpk_modul_alap( $tipus ); // a default kulcskészlet a séma

    // 🎨 Megjelenés minden olyan típusnál, ahol a séma tartalmazza
    if ( isset( $b['megjelenes'] ) ) {
        $b['megjelenes'] = tpk_sanitize_megjelenes(
            $tipus,
            isset( $input['megjelenes'] ) && is_array( $input['megjelenes'] ) ? $input['megjelenes'] : array(),
            $b['megjelenes']
        );
    }

    switch ( $tipus ) {
        case 'hero':
            $b['badge_szoveg'] = sanitize_text_field( $input['badge_szoveg'] ?? $b['badge_szoveg'] );
            $b['cim']          = wp_kses( (string) ( $input['cim'] ?? $b['cim'] ), array(
                'span'   => array( 'class' => true ),
                'br'     => array(),
                'strong' => array(),
                'em'     => array(),
            ) );
            $b['alcim']        = sanitize_textarea_field( $input['alcim'] ?? $b['alcim'] );
            $b['cta_szoveg']   = sanitize_text_field( $input['cta_szoveg'] ?? $b['cta_szoveg'] );
            $b['megjegyzes']   = sanitize_text_field( $input['megjegyzes'] ?? $b['megjegyzes'] );
            $b['hero_kep_id']  = absint( $input['hero_kep_id'] ?? 0 );
            break;

        case 'ajanlatok':
        case 'uticelok':
            $b['eyebrow']     = sanitize_text_field( $input['eyebrow'] ?? $b['eyebrow'] );
            $b['cim']         = sanitize_text_field( $input['cim'] ?? $b['cim'] );
            $b['ures_szoveg'] = sanitize_text_field( $input['ures_szoveg'] ?? $b['ures_szoveg'] );
            if ( 'ajanlatok' === $tipus ) {
                $b['link_szoveg'] = sanitize_text_field( $input['link_szoveg'] ?? $b['link_szoveg'] );
            }
            $b['darab']     = max( 1, min( 12, absint( $input['darab'] ?? $b['darab'] ) ) );
            $b['valogatas'] = in_array( $input['valogatas'] ?? '', array( 'auto', 'kezi' ), true ) ? $input['valogatas'] : 'auto';

            $post_type = 'ajanlatok' === $tipus ? 'ajanlat' : 'uticel';
            $idk       = array();
            foreach ( (array) ( $input['kivalasztott_idk'] ?? array() ) as $id ) {
                $id   = absint( $id );
                $post = $id ? get_post( $id ) : null;
                if ( $post && $post->post_type === $post_type && ! in_array( $id, $idk, true ) ) {
                    $idk[] = $id;
                }
            }
            $b['kivalasztott_idk'] = array_slice( $idk, 0, 12 );
            break;

        case 'miert_mi':
            $b['eyebrow'] = sanitize_text_field( $input['eyebrow'] ?? $b['eyebrow'] );
            $b['cim']     = sanitize_text_field( $input['cim'] ?? $b['cim'] );

            $pontok = array();
            for ( $i = 0; $i < 4; $i++ ) {
                $pont     = isset( $input['pontok'][ $i ] ) && is_array( $input['pontok'][ $i ] ) ? $input['pontok'][ $i ] : array();
                $pontok[] = array(
                    'title' => sanitize_text_field( $pont['title'] ?? $b['pontok'][ $i ]['title'] ),
                    'text'  => sanitize_text_field( $pont['text'] ?? $b['pontok'][ $i ]['text'] ),
                    'ikon'  => tpk_sanitize_ikon( (string) ( $pont['ikon'] ?? '' ), $b['pontok'][ $i ]['ikon'] ),
                );
            }
            $b['pontok'] = $pontok;
            break;

        case 'utikalauz':
            $b['eyebrow']     = sanitize_text_field( $input['eyebrow'] ?? $b['eyebrow'] );
            $b['cim']         = sanitize_text_field( $input['cim'] ?? $b['cim'] );
            $b['link_szoveg'] = sanitize_text_field( $input['link_szoveg'] ?? $b['link_szoveg'] );
            $b['darab']       = max( 1, min( 12, absint( $input['darab'] ?? $b['darab'] ) ) );
            break;

        case 'zaro_cta':
            $b['cim']            = sanitize_text_field( $input['cim'] ?? $b['cim'] );
            $b['alcim']          = sanitize_text_field( $input['alcim'] ?? $b['alcim'] );
            $b['gomb_szoveg']    = sanitize_text_field( $input['gomb_szoveg'] ?? $b['gomb_szoveg'] );
            $b['instagram_link'] = esc_url_raw( $input['instagram_link'] ?? '' );
            $b['facebook_link']  = esc_url_raw( $input['facebook_link'] ?? '' );
            break;

        case 'szabad':
            $b['hatter'] = in_array( $input['hatter'] ?? '', array( 'vilagos', 'sotet' ), true ) ? $input['hatter'] : 'vilagos';
            $tartalom    = (string) ( $input['tartalom_html'] ?? '' );
            if ( strlen( $tartalom ) > 64 * 1024 ) {
                $tartalom = substr( $tartalom, 0, 64 * 1024 );
            }
            $b['tartalom_html'] = wp_kses_post( $tartalom );
            break;
    }

    return $b;
}

// ── POST /tpk/v1/kezdolap/kep – Kép sideload URL-ből (hero / logó) ─────────────
// A tpu_download_and_sideload ~25 soros másolata tpk_ névvel – a pluginok
// önállósága a bevált konvenció (tpa/tpu ugyanígy duplikálja).
function tpk_download_and_sideload( $url ) {
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = download_url( $url, 30 );
    if ( is_wp_error( $tmp ) ) {
        return new WP_Error( 'download_failed', 'Kép letöltése sikertelen: ' . $tmp->get_error_message(), array( 'status' => 500 ) );
    }

    $file_name = basename( parse_url( $url, PHP_URL_PATH ) );
    if ( ! pathinfo( $file_name, PATHINFO_EXTENSION ) ) $file_name .= '.jpg';
    $file_name = sanitize_file_name( $file_name );

    $attachment_id = media_handle_sideload( array( 'name' => $file_name, 'tmp_name' => $tmp ), 0 );

    if ( file_exists( $tmp ) ) @unlink( $tmp );

    if ( is_wp_error( $attachment_id ) ) {
        return new WP_Error( 'sideload_failed', 'Importálás sikertelen: ' . $attachment_id->get_error_message(), array( 'status' => 500 ) );
    }

    return (int) $attachment_id;
}

function tpk_api_sideload_image( WP_REST_Request $req ) {
    $url = esc_url_raw( $req->get_param( 'url' ) );
    if ( ! $url ) {
        return new WP_Error( 'no_url', 'URL megadása kötelező', array( 'status' => 400 ) );
    }

    $attachment_id = tpk_download_and_sideload( $url );
    if ( is_wp_error( $attachment_id ) ) return $attachment_id;

    return rest_ensure_response( array(
        'attachment_id' => $attachment_id,
        'url'           => wp_get_attachment_image_url( $attachment_id, 'medium_large' ) ?: wp_get_attachment_url( $attachment_id ),
        'full_url'      => wp_get_attachment_url( $attachment_id ),
    ) );
}

// ── GET /tpk/v1/status – Státusz / ping ─────────────────────────────────────────
function tpk_api_status() {
    return rest_ensure_response( array(
        'plugin'         => 'Travelpont Kezdőoldal REST API',
        'version'        => TPK_VERSION,
        'endpoint'       => rest_url( 'tpk/v1/kezdolap' ),
        'modulok_option' => is_array( get_option( 'tpk_modulok' ) ),
    ) );
}
