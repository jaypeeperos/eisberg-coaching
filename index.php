<?php get_header(); ?>
<main class="section"><div class="narrow">
<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('entry-content'); ?>>
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
<?php else : ?>
    <h1><?php esc_html_e('Keine Inhalte gefunden.', 'eisberg'); ?></h1>
<?php endif; ?>
</div></main>
<?php get_footer(); ?>