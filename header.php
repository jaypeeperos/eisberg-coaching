<?php
$is_front = is_front_page();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site-shell">
<header class="site-header starry-background">
    <?php if (has_custom_logo()) : the_custom_logo(); else : ?><a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>"><span class="site-brand-text">Eisberg</span></a><?php endif; ?>
    <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu" aria-label="Menü öffnen">
        <span></span><span></span><span></span>
    </button>
    <nav class="primary-menu" id="primary-menu" aria-label="Hauptnavigation">
        <?php if (has_nav_menu('primary')) : wp_nav_menu(array('theme_location' => 'primary', 'container' => false, 'items_wrap' => '%3$s')); else : ?>
            <a href="<?php echo esc_url(home_url('/')); ?>">Produkt</a>
            <a href="<?php echo esc_url(eisberg_page_url('preise')); ?>">Preise</a>
            <a href="<?php echo esc_url(eisberg_page_url('fuer-schulleitungen')); ?>">Für Schulleitungen</a>
            <a href="<?php echo esc_url(eisberg_page_url('faq')); ?>">FAQ</a>
        <?php endif; ?>
        <?php eisberg_region_switcher(); ?>
        <a class="menu-cta eisberg-entdecken-btn" href="#" onclick="document.querySelector('.header-modal button').click(); return false;" data-target="#premium-modal-78bc7c2">Eisberg entdecken</a>
    </nav>
</header>