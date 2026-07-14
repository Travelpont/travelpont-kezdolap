<?php
/**
 * Travelpont Kezdőoldal – admin beállítási felület
 *
 * A `templates/front-page.php` statikus szövegeit eddig csak kódszintű
 * `apply_filters()`-snippettel lehetett felülírni (lásd a docs "Hogyan
 * bővítsd?" szakaszát). Ez a modul ugyanezekre a hookokra iratkozik fel,
 * és a WP Admin "Kezdőlap" menüpontban szerkeszthető `tpk_settings`
 * opcióból tölti ki őket – így kódírás nélkül is módosítható a tartalom.
 * A kódszintű snippetek továbbra is működnek (pl. lekérdezés-hookok),
 * csak azonos prioritáson a később regisztrált filter felülírja a korábbit.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Alapértelmezett értékek (a korábbi apply_filters() defaultjaival egyeznek) ─
function tpk_settings_defaults() {
    return array(
        'nav_cta_szoveg'        => 'Ajánlatok böngészése',
        'hero_badge_szoveg'     => 'Kézzel válogatott utazási ajánlatok',
        'hero_cim'              => 'Fedezd fel a világot, <span class="tpk-accent-word">okosabban</span> foglalva.',
        'hero_alcim'            => 'A Travelpont naponta válogatja a legjobb repjegy- és szállás-kombinációkat azoknak, akik szeretnek utazni, de nem szeretnek órákat böngészni az árak után.',
        'hero_cta_szoveg'       => 'Nézd meg az ajánlatokat',
        'ajanlatok_ures_szoveg' => 'Jelenleg nincs feltöltött ajánlat – nézz vissza hamarosan!',
        'uticelok_ures_szoveg'  => 'Jelenleg nincs feltöltött úticél.',
        'zaro_cta_cim'          => 'Kész az utad? Nézz körbe az ajánlatok között.',
        'zaro_cta_alcim'        => 'Kövess minket, hogy elsőként lásd az új ajánlatokat.',
        'zaro_cta_gomb_szoveg'  => 'Nézd meg az összes ajánlatot',
        'instagram_link'        => '',
        'facebook_link'         => '',
        'logo_kep_id'           => 0,
        'hero_kep_id'           => 0,
        'miert_mi'              => array(
            array( 'title' => 'Valódi ember válogat', 'text' => 'Minden ajánlatot élő utazási szakértő néz át, mielőtt kikerül az oldalra.', 'ikon' => 'circle' ),
            array( 'title' => 'Átlátható árbontás', 'text' => 'Mindig külön látod a repjegy és a szállás árát – nincs elrejtett tétel.', 'ikon' => 'square' ),
            array( 'title' => 'Folyamatosan frissítve', 'text' => 'Naponta figyeljük a piacot, hogy a legjobb ajánlatok kerüljenek fel.', 'ikon' => 'loader' ),
            array( 'title' => 'Megbízható partnerek', 'text' => 'Csak ellenőrzött légitársaságokkal és szállásokkal dolgozunk együtt.', 'ikon' => 'bag' ),
        ),
    );
}

function tpk_get_settings() {
    $saved = get_option( 'tpk_settings' );
    if ( ! is_array( $saved ) ) return tpk_settings_defaults();
    return wp_parse_args( $saved, tpk_settings_defaults() );
}

// ── Regisztráció ───────────────────────────────────────────────────────────
add_action( 'admin_init', function() {
    register_setting( 'tpk_settings_group', 'tpk_settings', array(
        'type'              => 'array',
        'sanitize_callback' => 'tpk_sanitize_settings',
        'default'           => tpk_settings_defaults(),
    ) );
} );

add_action( 'admin_menu', function() {
    add_menu_page(
        'Kezdőlap beállításai',
        'Kezdőlap',
        'manage_options',
        'tpk-settings',
        'tpk_render_settings_page',
        'dashicons-admin-home'
    );
} );

// ── Média-feltöltő (Logó, Hero fotó) – csak a saját beállítási oldalon ────────
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( 'toplevel_page_tpk-settings' !== $hook ) return;
    wp_enqueue_media();
    wp_enqueue_script(
        'tpk-admin-media',
        TPK_URL . 'assets/js/admin-media.js',
        array( 'jquery' ), TPK_VERSION, true
    );
} );

function tpk_sanitize_settings( $input ) {
    $defaults    = tpk_settings_defaults();
    $megengedett = array( 'circle', 'square', 'loader', 'bag' );
    $out         = array();

    $out['nav_cta_szoveg']        = sanitize_text_field( $input['nav_cta_szoveg'] ?? $defaults['nav_cta_szoveg'] );
    $out['hero_badge_szoveg']     = sanitize_text_field( $input['hero_badge_szoveg'] ?? $defaults['hero_badge_szoveg'] );
    $out['hero_cim']              = wp_kses_post( $input['hero_cim'] ?? $defaults['hero_cim'] );
    $out['hero_alcim']            = sanitize_textarea_field( $input['hero_alcim'] ?? $defaults['hero_alcim'] );
    $out['hero_cta_szoveg']       = sanitize_text_field( $input['hero_cta_szoveg'] ?? $defaults['hero_cta_szoveg'] );
    $out['ajanlatok_ures_szoveg'] = sanitize_text_field( $input['ajanlatok_ures_szoveg'] ?? $defaults['ajanlatok_ures_szoveg'] );
    $out['uticelok_ures_szoveg']  = sanitize_text_field( $input['uticelok_ures_szoveg'] ?? $defaults['uticelok_ures_szoveg'] );
    $out['zaro_cta_cim']          = sanitize_text_field( $input['zaro_cta_cim'] ?? $defaults['zaro_cta_cim'] );
    $out['zaro_cta_alcim']        = sanitize_text_field( $input['zaro_cta_alcim'] ?? $defaults['zaro_cta_alcim'] );
    $out['zaro_cta_gomb_szoveg']  = sanitize_text_field( $input['zaro_cta_gomb_szoveg'] ?? $defaults['zaro_cta_gomb_szoveg'] );
    $out['instagram_link']        = esc_url_raw( $input['instagram_link'] ?? '' );
    $out['facebook_link']         = esc_url_raw( $input['facebook_link'] ?? '' );
    $out['logo_kep_id']           = absint( $input['logo_kep_id'] ?? 0 );
    $out['hero_kep_id']           = absint( $input['hero_kep_id'] ?? 0 );

    $out['miert_mi'] = array();
    for ( $i = 0; $i < 4; $i++ ) {
        $pont = $input['miert_mi'][ $i ] ?? array();
        $out['miert_mi'][] = array(
            'title' => sanitize_text_field( $pont['title'] ?? $defaults['miert_mi'][ $i ]['title'] ),
            'text'  => sanitize_text_field( $pont['text'] ?? $defaults['miert_mi'][ $i ]['text'] ),
            'ikon'  => in_array( $pont['ikon'] ?? '', $megengedett, true ) ? $pont['ikon'] : $defaults['miert_mi'][ $i ]['ikon'],
        );
    }

    return $out;
}

// ── Média-feltöltő mező kirajzolása (Logó / Hero fotó) ─────────────────────
function tpk_media_mezo( $field_key, $attachment_id ) {
    $url = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
    ?>
    <div class="tpk-media-field" data-field="<?php echo esc_attr( $field_key ); ?>">
        <div class="tpk-media-preview">
            <?php if ( $url ) : ?><img src="<?php echo esc_url( $url ); ?>" style="max-width:220px;height:auto;display:block;margin-bottom:10px;border-radius:8px;"><?php endif; ?>
        </div>
        <input type="hidden" class="tpk-media-id" name="tpk_settings[<?php echo esc_attr( $field_key ); ?>]" value="<?php echo esc_attr( $attachment_id ); ?>">
        <button type="button" class="button tpk-media-select">Kép kiválasztása</button>
        <button type="button" class="button tpk-media-remove" <?php echo $attachment_id ? '' : 'style="display:none;"'; ?>>Kép törlése</button>
    </div>
    <?php
}

// ── Admin oldal ────────────────────────────────────────────────────────────
function tpk_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $s = tpk_get_settings();
    ?>
    <div class="wrap">
        <h1>Kezdőlap beállításai</h1>
        <?php if ( is_array( get_option( 'tpk_modulok' ) ) ) : ?>
            <div class="notice notice-warning">
                <p><strong>A kezdőlapot mostantól a Travelpont Portálban szerkesztheted</strong> (Kezdőlap menüpont). Mivel a Portálból már történt mentés, az itteni mezők <strong>nem hatnak</strong> a főoldalra – ez az oldal csak vésztartalékként maradt meg.</p>
            </div>
        <?php endif; ?>
        <p>Itt szerkesztheted a kezdőlap statikus szövegeit kód nélkül. Az elrendezés/design módosításához továbbra is a plugin kódjához kell nyúlni.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'tpk_settings_group' ); ?>

            <h2 class="title">Márka-képek</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th>Logó</th>
                    <td>
                        <?php tpk_media_mezo( 'logo_kep_id', $s['logo_kep_id'] ); ?>
                        <p class="description">Teljes logó (ikon + "<?php bloginfo( 'name' ); ?>" felirat egy képben), átlátszó hátterű PNG. <strong>Fontos:</strong> a képet szorosan a látható rajzolatra vágva töltsd fel – ha a fájl vászna nagy üres/átlátszó margót tartalmaz a logó körül (pl. egy négyzetes exportnál gyakori), a nav-ban aránytalanul kicsinek/olvashatatlannak fog látszani, mert a böngésző a TELJES vásznat skálázza, az üres résszel együtt. Ha üresen hagyod, a jelvényes/repülős CSS-ikon jelenik meg.</p>
                    </td>
                </tr>
                <tr>
                    <th>Hero fotó</th>
                    <td>
                        <?php tpk_media_mezo( 'hero_kep_id', $s['hero_kep_id'] ); ?>
                        <p class="description">Ha üresen hagyod, a csíkos placeholder jelenik meg a főoldal hero szekciójában.</p>
                    </td>
                </tr>
            </table>

            <h2 class="title">Navigáció</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="tpk_nav_cta">Fejléc gomb szövege</label></th>
                    <td><input type="text" id="tpk_nav_cta" name="tpk_settings[nav_cta_szoveg]" value="<?php echo esc_attr( $s['nav_cta_szoveg'] ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2 class="title">Hero szekció</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="tpk_hero_badge">Kis jelvény-szöveg</label></th>
                    <td><input type="text" id="tpk_hero_badge" name="tpk_settings[hero_badge_szoveg]" value="<?php echo esc_attr( $s['hero_badge_szoveg'] ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="tpk_hero_cim">Főcím</label></th>
                    <td>
                        <textarea id="tpk_hero_cim" name="tpk_settings[hero_cim]" rows="2" class="large-text"><?php echo esc_textarea( $s['hero_cim'] ); ?></textarea>
                        <p class="description">Egyszerű HTML engedélyezett, pl. <code>&lt;span class="tpk-accent-word"&gt;kiemelt szó&lt;/span&gt;</code> a sárga kiemeléshez.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="tpk_hero_alcim">Alcím</label></th>
                    <td><textarea id="tpk_hero_alcim" name="tpk_settings[hero_alcim]" rows="3" class="large-text"><?php echo esc_textarea( $s['hero_alcim'] ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="tpk_hero_cta">Gomb szövege</label></th>
                    <td><input type="text" id="tpk_hero_cta" name="tpk_settings[hero_cta_szoveg]" value="<?php echo esc_attr( $s['hero_cta_szoveg'] ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2 class="title">„Miért mi?” – 4 pont</h2>
            <table class="form-table" role="presentation">
                <?php foreach ( $s['miert_mi'] as $i => $pont ) : ?>
                    <tr>
                        <th><?php echo esc_html( $i + 1 ); ?>. pont</th>
                        <td>
                            <p>
                                <label>Cím<br>
                                <input type="text" name="tpk_settings[miert_mi][<?php echo esc_attr( $i ); ?>][title]" value="<?php echo esc_attr( $pont['title'] ); ?>" class="regular-text"></label>
                            </p>
                            <p>
                                <label>Szöveg<br>
                                <textarea name="tpk_settings[miert_mi][<?php echo esc_attr( $i ); ?>][text]" rows="2" class="large-text"><?php echo esc_textarea( $pont['text'] ); ?></textarea></label>
                            </p>
                            <p>
                                <label>Ikon<br>
                                <select name="tpk_settings[miert_mi][<?php echo esc_attr( $i ); ?>][ikon]">
                                    <?php foreach ( array( 'circle', 'square', 'loader', 'bag' ) as $ikon ) : ?>
                                        <option value="<?php echo esc_attr( $ikon ); ?>" <?php selected( $pont['ikon'], $ikon ); ?>><?php echo esc_html( $ikon ); ?></option>
                                    <?php endforeach; ?>
                                </select></label>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <h2 class="title">Üres állapot szövegek</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="tpk_ajanlatok_ures">Ha nincs ajánlat</label></th>
                    <td><input type="text" id="tpk_ajanlatok_ures" name="tpk_settings[ajanlatok_ures_szoveg]" value="<?php echo esc_attr( $s['ajanlatok_ures_szoveg'] ); ?>" class="large-text"></td>
                </tr>
                <tr>
                    <th><label for="tpk_uticelok_ures">Ha nincs úticél</label></th>
                    <td><input type="text" id="tpk_uticelok_ures" name="tpk_settings[uticelok_ures_szoveg]" value="<?php echo esc_attr( $s['uticelok_ures_szoveg'] ); ?>" class="large-text"></td>
                </tr>
            </table>

            <h2 class="title">Záró CTA</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="tpk_zaro_cim">Cím</label></th>
                    <td><input type="text" id="tpk_zaro_cim" name="tpk_settings[zaro_cta_cim]" value="<?php echo esc_attr( $s['zaro_cta_cim'] ); ?>" class="large-text"></td>
                </tr>
                <tr>
                    <th><label for="tpk_zaro_alcim">Alcím</label></th>
                    <td><input type="text" id="tpk_zaro_alcim" name="tpk_settings[zaro_cta_alcim]" value="<?php echo esc_attr( $s['zaro_cta_alcim'] ); ?>" class="large-text"></td>
                </tr>
                <tr>
                    <th><label for="tpk_zaro_gomb">Gomb szövege</label></th>
                    <td><input type="text" id="tpk_zaro_gomb" name="tpk_settings[zaro_cta_gomb_szoveg]" value="<?php echo esc_attr( $s['zaro_cta_gomb_szoveg'] ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2 class="title">Közösségi linkek</h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="tpk_instagram">Instagram URL</label></th>
                    <td><input type="url" id="tpk_instagram" name="tpk_settings[instagram_link]" value="<?php echo esc_attr( $s['instagram_link'] ); ?>" class="regular-text" placeholder="https://instagram.com/travelpont"></td>
                </tr>
                <tr>
                    <th><label for="tpk_facebook">Facebook URL</label></th>
                    <td><input type="url" id="tpk_facebook" name="tpk_settings[facebook_link]" value="<?php echo esc_attr( $s['facebook_link'] ); ?>" class="regular-text" placeholder="https://facebook.com/travelpont"></td>
                </tr>
                <tr>
                    <th></th>
                    <td><p class="description">Ha üresen hagyod, az adott ikon nem jelenik meg a záró szekcióban.</p></td>
                </tr>
            </table>

            <?php submit_button( 'Mentés' ); ?>
        </form>
    </div>
    <?php
}

// ── FONTOS (2026-07-14, modul-rendszer): a korábbi tpk_* filter-feliratkozások
// innen KIVEZETVE. A sablonok mostantól közvetlenül a modul-konfigurációból
// (tpk_get_modulok) olvasnak – amíg a Portálból nem mentettek (nincs
// tpk_modulok opció), az itteni tpk_settings értékei migrált defaultként
// érvényesülnek, tehát ez az oldal átmenetileg még hat. Ha a filter-blokk
// visszakerülne, örökre felülírná a Portálból mentett értékeket!
