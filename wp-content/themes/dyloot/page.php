<?php // vim: set ts=2 et sts=2 sw=2:
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 */

get_header(); ?>

  <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
  <div id="container">
  <article id="content" role="main">
		<div id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
      <?php the_content(); ?>
      <?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
		</div><!-- #page-## -->
  </article><!-- #content -->
  </div><!-- #container -->
  <?php endwhile; ?>

<?php get_footer(); ?>
