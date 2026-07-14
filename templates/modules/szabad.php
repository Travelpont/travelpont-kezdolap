<?php
/**
 * Kezdőlap-modul: SZABAD SZEKCIÓ
 * Tartalma tpu-formátumú HTML (a Portál vászon-szerkesztőjéből) – a
 * travelpont-uticelok dekorátor-lánca (tpu_render_tartalom) rendereli a
 * widgeteket (kép, kiemelés, GYIK, videó, térkép, CTA, ajánlat/úticél
 * kártya). Ha a tpu plugin nincs aktiválva, a nyers HTML jelenik meg
 * wp_kses_post-tal szűrve (a widget-helyjelzők ilyenkor üresen eltűnnek,
 * az oldal nem törik).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_tartalom = (string) $tpk_b['tartalom_html'];
if ( '' === trim( $tpk_tartalom ) ) return;
?>
    <!-- SZABAD SZEKCIÓ -->
    <section class="tpk-section tpk-szabad<?php echo 'sotet' === $tpk_b['hatter'] ? ' tpk-szabad--sotet' : ''; ?>">
        <div class="tpk-szabad-tartalom">
            <?php
            if ( function_exists( 'tpu_render_tartalom' ) ) {
                echo tpu_render_tartalom( $tpk_tartalom ); // a lánc elemei escape-elnek
            } else {
                echo wp_kses_post( $tpk_tartalom );
            }
            ?>
        </div>
    </section>
