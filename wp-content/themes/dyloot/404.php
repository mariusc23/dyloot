<?php // vim: set ts=2 et sts=2 sw=2:
/**
 * The template for displaying 404 pages (Not Found).
 *
 */
get_header(); ?>
  <div id="container">
  <article id="content" role="main">
    <h1 class="entry-title">Not Found</h1>
    <div class="entry-content">
      <p>Apologies, but the page you requested could not be found. Perhaps searching will help.</p>
      <?php get_search_form(); ?>
    </div><!-- .entry-content -->
  </article><!-- #content -->
  </div><!-- #container -->
<?php get_footer(); ?>
