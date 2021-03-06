<?php
/**
 * Creates a welcome screen with pre-flight check sequence
 *
 * @since 0.8.2
 */
namespace lasso_admin\menus;

class welcome {

	function __construct() {

		add_action( 'admin_init',   array( $this, 'redirect' ) );
		add_action( 'admin_menu',   array( $this, 'lasso_welcome' ) );
		add_action( 'network_admin_menu',   array( $this, 'lasso_welcome' ) );  // CHANGED Added hook.

		add_action( 'tgmpa_register',  array( $this,'required_plugins' ));

	}

	function redirect() {

		// Bail if no activation redirect
		if ( !get_transient( '_lasso_welcome_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_lasso_welcome_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( esc_url_raw( add_query_arg( array( 'page' => 'lasso-editor' ), admin_url( 'admin.php' ) ) ) );

	}

	/**
	 * Add dashboard pages for welcome and welcome back
	 *
	 * @since 0.8
	 */
	function lasso_welcome() {

		// CHANGED Removed condition.
		add_menu_page( __( 'Editus', 'lasso' ), __( 'Editus', 'lasso' ), 'manage_options', 'lasso-editor', '', LASSO_URL.'/admin/assets/img/menu-icon.png' );
		add_submenu_page( 'lasso-editor', __( 'Welcome', 'lasso' ), __( 'Status', 'lasso' ), 'manage_options', 'lasso-editor', array( $this, 'welcome' ) );

	}

	/**
	 * Callback for the intial welcome screen for new users
	 *
	 * @since 0.8.2
	 */
	function welcome() {

		?>
		  <div class="wrap lasso--welcome">

		  		<?php self::header();?>

		  		<ul class="lasso--welcome__steps">

		  		<?php // CHANGED Added the is_network_admin condition. ?>
			  		<?php if ( is_network_admin() ) : ?>

			  			<li>
			  				<p><?php _e( 'We will check the current theme on every site in your network and give you a quick status feedback here. You can see the status by visiting the Editus menu on each site.', 'lasso' );?></p>
			  			</li>

			  		<?php else :

					$checks = self::lasso_preflight_check();

					if ( $checks && !defined( 'LASSO_AGENCY_MODE' ) ):

						foreach ( (array) $checks as $key => $check ) {

							echo $check;
						}

						else:

							// pre-flight is go for flight
						?>
						<li class="success">
							<h3><?php _e( 'You\'re Ready to Rock!', 'lasso' );?></h3>
							<?php if ( lasso_get_supported_theme_class() ) { ?>
							<p><?php _e( 'Your theme is automatically supported. No additional setup is needed.', 'lasso' );?></p>
							<?php } ?>
			  				<p><?php _e( 'Editus will place a small menu on the bottom of your site. While on a single post or page, click the "pen" icon to go into edit mode. Press escape to get out of edit mode.', 'lasso' );?></p>
			  			</li>
			  			<?php
			  			endif;
					endif; ?>

			  	</ul>
		  	</div>
	 	<?php
	}

	/**
	 * Universal header draw on both welcome and activation screens
	 *
	 * @since 0.8.2
	 */
	function header() {

?>

	  		<div class="lasso--welcome__top">

	  			<img style="width:125px;" src="<?php echo LASSO_URL.'/admin/assets/img/logo.png';?>">
	    		<h1><?php _e( 'Welcome to Editus', 'lasso' );?></h1>
	    		<p><?php _e( 'Version', 'lasso' );echo '<span> '.LASSO_VERSION.'</span>';?></p>

	    		<?php if ( !defined( 'LASSO_AGENCY_MODE' ) ): ?>

			    	<ul class="lasso--welcome__social">
			    		<li><a href="https://edituswp.com/help" target="_blank"><i class="dashicons dashicons-sos"></i> <?php _e( 'Help', 'lasso' );?></a></li>
			    		<li><a href="http://twitter.com/aesopinteractiv" target="_blank"><i class="dashicons dashicons-twitter"></i> <?php _e( 'Twitter', 'lasso' );?></a></li>
			    		<li><a href="http://facebook.com/aesopinteractive" target="_blank"><i class="dashicons dashicons-facebook"></i> <?php _e( 'Facebook', 'lasso' );?></a></li>
			    	</ul>

			    <?php endif; ?>

		    </div>

	 	<?php
	}

	/**
	 * Run a series of checks to inform the user about incompatibilities, missing option fields, missing license keys for updates and suggested addons
	 *
	 * @since 0.8.6
	 */
	function lasso_preflight_check() {

		$notices 		= array();

		$article_object = lasso_editor_get_option( 'article_class', 'lasso_editor' );

		$theme_name  	= wp_get_theme()->get('Name');
		$theme_class  	= lasso_get_supported_theme_class();

		$license   		= get_option( 'lasso_license_key' );
		$status   		= get_option( 'lasso_license_status' );

		// if the required CSS class has not been saved and we're not a supported theme
		if ( empty( $article_object ) && false == $theme_class ) {

			// we dont automatically support this theme so show them otherwise
			$notices[] = sprintf('<li class="error">
							<h3>'.__( 'Article CSS Class Needed!', 'lasso' ).'</h3>
							<p>'.__( 'Before using Editus,', 'lasso' ).' <a href="%s">'.__( 'enter and save', 'lasso' ).'</a> '.__( 'the CSS class of the container that holds your post and page content. You can <a href="https://dl.dropboxusercontent.com/u/5594632/lasso-media/doc-movies/using-inspector-lasso.gif" target="_blank">use a tool like inspector</a> in Chrome or Firefox to find this CSS class, or ', 'lasso' ).' <a href="mailto:editus@aesopinteractive.com">'.__( 'email us.', 'lasso' ).'</a> '.__( 'with a link to a public URL with the theme and we\'ll find it for you.', 'lasso' ).'</p>
							</li>', admin_url( 'admin.php?page=lasso-editor-settings' ) );
		}

		// WP REST API not active
		if ( !function_exists( 'json_get_url_prefix' ) &&  !function_exists( 'rest_url' )) {
			$notices[] = '<li class="info"><h3>WP REST API not Activated!</h3>
							<p>'.__( 'Just a heads up that the WP REST API isn\'t activated. This is required to list the posts and pages on the front-end.', 'lasso' ).'</p>
							</li>';
		}

		// aesop story engine isnt active
		if ( !class_exists( 'Aesop_Core' ) ) {
			$notices[] = sprintf( '<li class="info"><h3>Aesop Story Engine not Activated!</h3>
							<p>'.__( 'Just a heads up that ', 'lasso' ).'<a href="%s" target="_blank
							">'.__( 'Aesop Story Engine', 'lasso' ).'</a> '.__( 'isn\'t activated. It\'s not required to use Editus, but you won\'t get the cool drag and drop components without it activated. It\'s free!', 'lasso' ).'</p>
							</li>', admin_url('plugin-install.php?tab=search&s=aesop+story+engine') );
		}

		// we dont really get along with wp side comments because of the section ids that get applied dynamically. since we save our html, it'll get saved along with the post as HTML
		if ( class_exists( 'WP_Side_Comments' ) ) {
			$notices[] = '<li class="error"><h3>'.__( 'WP Side Comments Compatibility Warning!', 'lasso' ).'</h3>
							<p>'.__( 'Since Editus saves the HTML of a post, this may cause undesired issues. We\'re working to resolve incompatibilities faster than a jack rabbit in a hot greasy griddle in the middle of August.', 'lasso' ).'</p>
							</li>';
		}

		// if the license key isnt activated
		if ( empty( $license ) ) {
			$notices[] = '<li class="info"><h3>'.__( 'License Key Not Activated', 'lasso' ).'</h3>
							<p>'.__( 'Just a heads up, your license key isn\'t activated. Enter your license key into the License tab on the left in order to receive plugin update notifications.', 'lasso' ).'</p>
							</li>';
		}
		if ( !empty( $license ) && 'invalid' == $status ) {
			$notices[] = '<li class="error"><h3>'.__( 'License Key Invalid', 'lasso' ).'</h3>
							<p>'.__( 'The license key that you entered is ', 'lasso' ).'<strong>'.__( 'invalid', 'lasso' ).'</strong>'.__( '. It may have been entered incorreclty, or may have expired.', 'lasso' ).'</p>
							</li>';
		}

		return apply_filters( 'lasso_preflight_notices', $notices );

	}

	/**
	 * Register the required plugins for this theme.
	 *
	 *	@since 0.9.3
	 */
	function required_plugins() {

	    $plugins = array(

	        array(
	            'name'      => __('Aesop Story Engine','lasso'),
	            'slug'      => 'aesop-story-engine',
	            'required'  => false,
	        ),

	    );

	    $config = array(
	        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
	        'menu'         => 'lasso-install-plugins', // Menu slug.
	        'has_notices'  => true,                    // Show admin notices or not.
	        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
	        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
	        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
	        'message'      => '',                      // Message to output right before the plugins table.
	        'strings'      => array(
	            'page_title'                      => __( 'Install Required Plugins', 'lasso' ),
	            'menu_title'                      => __( 'Install Plugins', 'lasso' ),
	            'installing'                      => __( 'Installing Plugin: %s', 'lasso' ), // %s = plugin name.
	            'oops'                            => __( 'Something went wrong with the plugin API.', 'lasso' ),
	            'notice_can_install_required'     => _n_noop( 'This plugin requires the following plugin: %1$s.', 'This plugin requires the following plugins: %1$s.' ), // %1$s = plugin name(s).
	            'notice_can_install_recommended'  => _n_noop( 'This plugin recommends the following plugin: %1$s.', 'This plugin recommends the following plugins: %1$s.' ), // %1$s = plugin name(s).
	            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s).
	            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
	            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s).
	            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s).
	            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this plugin: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this plugin: %1$s.' ), // %1$s = plugin name(s).
	            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s).
	            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
	            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' ),
	            'return'                          => __( 'Return to Required Plugins Installer', 'lasso' ),
	            'plugin_activated'                => __( 'Plugin activated successfully.', 'lasso' ),
	            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'lasso' ), // %s = dashboard link.
	            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
	        )
	    );
		
		$plugins = apply_filters( 'lasso_required_plugins', $plugins );

	    tgmpa( $plugins, $config );

	}
}

