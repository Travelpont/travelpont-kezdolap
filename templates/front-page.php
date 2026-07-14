<?php
/**
 * Travelpont Kezdőoldal – teljes egyedi sablon (template_include-dal betöltve)
 *
 * A jóváhagyott mockup (Travelpont - kezdőoldal mockup.html) alapján.
 * A szekciók MODULOK: a sorrendet, az aktív állapotot és a tartalmat a
 * `tpk_modulok` opció adja (a Travelpont Portálból szerkesztve), a szekciók
 * markupja a templates/modules/*.php sablonokban él.
 * Lásd: includes/modules.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$tpk_tagline = get_bloginfo( 'description' );
$tpk_title   = $tpk_tagline ? get_bloginfo( 'name' ) . ' – ' . $tpk_tagline : get_bloginfo( 'name' );
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $tpk_title ); ?></title>
<?php wp_head(); ?>
</head>
<body <?php body_class( 'tpk-managed-page tpk-kezdolap' ); ?>>
<?php wp_body_open(); ?>

<div class="tpk-page">

    <?php tpk_render_nav(); ?>

<?php
$tpk_config = tpk_get_modulok();
foreach ( $tpk_config['modulok'] as $tpk_modul ) {
    tpk_render_modul( $tpk_modul );
    echo "\n";
}
?>
    <?php tpk_render_footer(); ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
