<?php // vim: set ts=2 et sts=2 sw=2:
/**
 * The Header for our theme.
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title('|', true, 'right');

	// Add the blog name.
	bloginfo('name');

	// Add a page number if necessary:
	if ($paged >= 2 || $page >= 2) {
		echo ' | ' . sprintf('Page %s', max( $paged, $page ));
    }
	?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo('stylesheet_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="wrapper">
	<div id="header">
    <div id="access" role="navigation">
      <?php /*  Allow screen readers / text browsers to skip the navigation menu and get right to the good stuff */ ?>
      <div class="skip-link screen-reader-text"><a href="#content" title="<?php esc_attr('Skip to content'); ?>">Skip to content</a></div>
      <?php /* Our navigation menu.  If one isn't filled out, wp_nav_menu falls back to wp_page_menu.  The menu assiged to the primary position is the one used.  If none is assigned, the menu with the lowest ID is used.  */ ?>
      <?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
      <?php if (!strpos($_SERVER['REQUEST_URI'], 'about') &&
                !strpos($_SERVER['REQUEST_URI'], 'contact') &&
                !strpos($_SERVER['REQUEST_URI'], 'portfolio')): ?>
        <a id="relationship" title="Hire us!" href="/contact?detailed">
            Are we right for each other?
        </a>
      <?php endif; ?>
		</div><!-- #access -->
	</div><!-- #header -->

	<div id="main">
