<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after. Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?>
	</div><!-- #main -->

	<div id="footer" role="contentinfo">
		<div id="colophon">

<?php
	/* A sidebar in the footer? Yep. You can can customize
	 * your footer with four columns of widgets.
	 */
	get_sidebar( 'footer' );
?>

<div id="site-info"><a href="http://www.datenschutz-elearning.com/"><img src="http://datenschutz-elearning.com/wp-content/uploads/2013/05/daschug-logo-text-schwarz2-41x7.jpg" alt="" border="0"></a></div><!--<a href="<?php echo home_url( '/' ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
                                         <?php bloginfo( 'name' ); ?>
                                 </a>--><!-- #site-info -->

		  <div id="site-generator">  
				<a title="Impressum auf www.daschug.de" href="http://daschug.de/ueber-uns/impressum" target="_blank"><span style="font-size: 12px; color: #000000;">IMPRESSUM</span></a>
				<a title="Datenschutzerklärung auf www.daschug.de" href="http://daschug.de/ueber-uns/datenschutzerklarung" target="_blank"><span style="font-size: 12px; color: #000000;">DATENSCHUTZERKLÄRUNG</span></a>
				<a title="ÜBER daschug" href="http://www.datenschutz-elearning.com/top-navigation/uber-uns/"><span style="font-size: 12px; color: #000000;">Über uns</span></a>
	    </div><!-- #site-generator -->

		</div><!-- #colophon -->
	</div><!-- #footer -->

</div><!-- #wrapper -->

<?php
	/* Always have wp_footer() just before the closing </body>
	 * tag of your theme, or you will break many plugins, which
	 * generally use this hook to reference JavaScript files.
	 */

	wp_footer();
?>
</body>
</html>
