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
      <p>
        Apologies, but the page you requested could not be found. These are the only pages on our site:
        <ul>
          <li><a href="<?php echo home_url( '/' ); ?>">Home page</a></li>
          <li><a href="/portfolio">Portfolio</a></li>
          <li><a href="/about">About</a></li>
          <li><a href="/contact">Contact Us</a></li>
        </ul>
      </p>
    </div><!-- .entry-content -->
  </article><!-- #content -->
  </div><!-- #container -->
<?php get_footer(); ?>
