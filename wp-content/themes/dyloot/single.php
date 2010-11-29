<?php // vim: set ts=2 et sts=2 sw=2:
/**
 * The Template for displaying all single posts.
 */

get_header(); ?>

  <div id="container">
  <article id="content" role="main">
  <?php if (have_posts() ) while (have_posts() ) : the_post(); ?>
    <div id="nav-above" class="navigation">
      <div class="nav-previous"><?php previous_post_link('%link', '<span class="meta-nav">' . _x('&larr;', 'Previous post link') . '</span> %title'); ?></div>
      <div class="nav-next"><?php next_post_link('%link', '%title <span class="meta-nav">' . _x('&rarr;', 'Next post link') . '</span>'); ?></div>
				</div><!-- #nav-above -->

				<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>

					<div class="entry-meta">
						<?php dyloot_posted_on(); ?>
					</div><!-- .entry-meta -->

					<div class="entry-content">
						<?php the_content(); ?>
						<?php wp_link_pages(array('before' => '<div class="page-link">Pages:', 'after' => '</div>' ) ); ?>
					</div><!-- .entry-content -->

					<div class="entry-utility">
						<?php dyloot_posted_in(); ?>
						<?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
					</div><!-- .entry-utility -->
				</div><!-- #post-## -->

				<div id="nav-below" class="navigation">
					<div class="nav-previous"><?php previous_post_link('%link', '<span class="meta-nav">' . _x('&larr;', 'Previous post link') . '</span> %title'); ?></div>
					<div class="nav-next"><?php next_post_link('%link', '%title <span class="meta-nav">' . _x('&rarr;', 'Next post link') . '</span>'); ?></div>
				</div><!-- #nav-below -->

				<?php comments_template('', true); ?>

  <?php endwhile; // end of the loop. ?>
  </article><!-- #content -->
  </div><!-- #container -->

<?php get_footer(); ?>
