<?php // vim: set ts=2 et sts=2 sw=2:
/**
 * The template for displaying the footer.
 */
?>
  </div><!-- #main -->

  <div id="footer" role="contentinfo">
    <div id="site-info">
      <a href="<?php echo home_url( '/' ) ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
        &copy;<?php echo date('Y'); ?> Paul and Marius Craciunoiu. All rights reserved.
      </a>
    </div><!-- #site-info -->
	</div><!-- #footer -->

</div><!-- #wrapper -->

<?php wp_footer(); ?>
</body>
</html>
