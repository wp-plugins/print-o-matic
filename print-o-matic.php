<?php
/*
Plugin Name: Print-O-Matic
Plugin URI: http://plugins.twinpictures.de
Description: Shortcode that adds a printer icon, allowing the user to print the post or a specified HTML element in the post.
Version: 1.2
Author: Twinpictures
Author URI: http://twinpictuers.de
License: GPL2
*/

/*  Copyright 2012 Twinpictures (www.twinpictures.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Class WP_Print_O_Matic
 * @package WP_Print_O_Matic
 * @category WordPress Plugins
 */
class WP_Print_O_Matic {
	/**
	 * Current version
	 * @var string
	 */
	var $version = '1.2';

	/**
	 * Used as prefix for options entry
	 * @var string
	 */
	var $domain = 'printomat';
	
	/**
	 * Name of the options
	 * @var string
	 */
	var $options_name = 'WP_Print_O_Matic_options';

	/**
	 * @var array
	 */
	var $options = array(
		'print_target' => 'article',
		'printicon' => true,
		'use_theme_css' => '',
		'custom_css' => ''
	);
	
	/**
	 * PHP4 constructor
	 */
	function WP_Print_O_Matic() {
		$this->__construct();
	}
	
	
	/**
	 * PHP5 constructor
	 */
	function __construct() {
		// set option values
		$this->_set_options();
		
		// load text domain for translations
		load_plugin_textdomain( 'printomat', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		// set uninstall hook
		if ( function_exists( 'register_deactivation_hook' ) )
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ));
		
		//load the script and style if not viwing the dashboard
		if (!is_admin()){
			add_action('init', array( $this, 'printMaticInit' ) );
		}
		
		// add actions
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_shortcode('print-me', array($this, 'shortcode'));
		
		// Add shortcode support for widgets  
		add_filter('widget_text', 'do_shortcode'); 
	}
	
	/**
	 * Callback init
	 */
	function printMaticInit() {
		//load up jQuery the Jedi way
		wp_enqueue_script('jquery');
		
		//script
		wp_register_script('printomatic-js', plugins_url('/printomat.js', __FILE__), array('jquery'), '1.1');
		wp_enqueue_script('printomatic-js');

		//css
		wp_register_style( 'printomatic-css', plugins_url('/css/style.css', __FILE__) , array (), '1.1' );
		wp_enqueue_style( 'printomatic-css' );
	}
	
	/**
	 * Callback admin_menu
	 */
	function admin_menu() {
		if ( function_exists( 'add_options_page' ) AND current_user_can( 'manage_options' ) ) {
			// add options page
			$page = add_options_page('Print-O-Matic Options', 'Print-O-Matic', 'manage_options', 'print-o-matic-options', array( $this, 'options_page' ));
		}
	}
	
	/**
	 * Callback admin_init
	 */
	function admin_init() {
		// register settings
		register_setting( $this->domain, $this->options_name );
	}
	
	/**
	 * Callback shortcode
	 */
	function shortcode($atts, $content = null){
		$ran = rand(1, 10000);
		$options = $this->options;
		extract(shortcode_atts(array(
			'id' => 'id'.$ran,
			'target' => $options['print_target'],
			'printicon' => $options['printicon'],
			'title' => ''
		), $atts));
		
		if($printicon){
			$output = "<div class='printomatic' id='".$id."' title='".$title."' ></div>";
		}
		else{
			$output = "<div class='printomatictext' id='".$id."' title='".$title."' >".$title."</div>";
		}
		
		$output .= "<input type='hidden' id='target-".$id."' value='".$target."' /><script>\n";
		
		if( empty( $options['use_theme_css'] ) ){
			$output .= "var site_css = '';\n";
		}
		else{
			$output .= "var site_css = '".get_stylesheet_uri()."';";
		}
		
		if( empty( $options['custom_css'] ) ){
			$output .= "var custom_css = '';\n";
		}
		else{
			$output .= "var custom_css = ".json_encode( $options['custom_css'] ).";";
		}
		
		$output .= "</script>\n";
		
		return  $output;
	}
	
	/**
	 * Admin options page
	 */
	function options_page() {
		//here is where I left off
		/** to do
		 * add default target option
		 * add toggle to add theme's css
		 * add textarea to add custom css
		 * figure out how to add css to new page via jQuery
		 **/
	?>
		<div class="wrap">
			<div class="icon32" id="icon-options-custom" style="background:url( <?php echo plugins_url( 'css/print-icon.png', __FILE__ ) ?> ) no-repeat 50% 50%"><br></div>
			<h2>Print-O-Matic</h2>
		</div>
		
		<div class="postbox-container metabox-holder meta-box-sortables" style="width: 69%">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ) ?>"><br/></div>
					<h3 class="hndle"><?php _e( 'Print-O-Matic Settings' ) ?></h3>
					<div class="inside">
						<form method="post" action="options.php">
							<?php
								settings_fields( $this->domain );
								$this->_set_options();
								$options = $this->options;
							?>
							<fieldset class="options">
								<table class="form-table">
								<tr>
									<th><?php _e( 'Default Target Attribute:' ) ?></th>
									<td><label><input type="text" id="<?php echo $this->options_name ?>[print_target]" name="<?php echo $this->options_name ?>[print_target]" value="<?php echo $options['print_target']; ?>" />
										<br /><span class="description"><?php printf(__('Print target. See %sTarget Attribute%s in the documentation for more info.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#target" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Use Print Icon:' ) ?></th>
									<td><label><select id="<?php echo $this->options_name ?>[printicon]" name="<?php echo $this->options_name ?>[printicon]">
										<?php
											$se_array = array(
												__('Yes', 'printomat') => true,
												__('No', 'printomat') => false
											);
											foreach( $se_array as $key => $value){
												$selected = '';
												if($options['printicon'] == $value){
													$selected = 'SELECTED';
												}
												echo '<option value="'.$value.'" '.$selected.'>'.$key.'</option>';
											}
										?>
										</select>
										<br /><span class="description"><?php printf(__('Use printer icon. See %sPrinticon Attribute%s in the documentation for more info.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#printicon" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>	
								<tr>
									<th><?php _e( 'Use Theme Style' ) ?>:</th>
									<td><label><input type="checkbox" id="<?php echo $this->options_name ?>[use_theme_css]" name="<?php echo $this->options_name ?>[use_theme_css]" value="1"  <?php echo checked( $options['use_theme_css'], 1 ); ?> /> <?php _e('Yes, Use Theme CSS', 'printomat'); ?>
										<br /><span class="description"><?php _e('Use theme style for print.', 'printomat'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Custom Style' ) ?>:</th>
									<td><label><textarea id="<?php echo $this->options_name ?>[custom_css]" name="<?php echo $this->options_name ?>[custom_css]" style="width: 100%; height: 150px;"><?php echo $options['custom_css']; ?></textarea>
										<br /><span class="description"><?php _e( 'Custom CSS Style for Ultimate Flexibility', 'printomat' ) ?></span></label>
									</td>
								</tr>
								</table>
							</fieldset>
							
							<p class="submit">
								<input class="button-primary" type="submit" value="<?php _e( 'Save Changes' ) ?>" />
							</p>
						</form>
					</div>
				</div>
			</div>
		</div>
		
		<div class="postbox-container side metabox-holder meta-box-sortables" style="width:29%;">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ) ?>"><br/></div>
					<h3 class="hndle"><?php _e( 'About' ) ?></h3>
					<div class="inside">
						<h4><img src="<?php echo plugins_url( 'css/print-icon.png', __FILE__ ) ?>" width="16" height="16"/> Print-O-Matic Version <?php echo $this->version; ?></h4>
						<p><?php _e( 'Shortcode that adds a printer icon, allowing the user to print the post or a specified HTML element in the post.', 'printomat') ?></p>
						<ul>
							<li><?php printf( __( '%sDetailed documentation%s, complete with working demonstrations of all shortcode attributes, is available for your instructional enjoyment.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/" target="_blank">', '</a>'); ?></li>
							<li><?php printf( __( '%sFree%s & %sPremimum%s Support', 'printomat'), '<a href="http://wordpress.org/support/plugin/print-o-matic" target="_blank">', '</a>', '<a href="http://plugins.twinpictures.de/products-page/support/print-o-matic-premium-support/" target="_blank">', '</a>'); ?></li>
							<li><?php printf( __('If you like this plugin, please consider %sreviewing it at WordPress.org%s to help others.', 'printomat'), '<a href="http://wordpress.org/support/view/plugin-reviews/print-o-matic" target="_blank">', '</a>' ) ?></li>
							<li><a href="http://wordpress.org/extend/plugins/print-o-matic/" target="_blank">WordPress.org</a> | <a href="http://plugins.twinpictures.de/plugins/print-o-matic/" target="_blank">Twinpictues Plugin Oven</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	<?php
	}

	/**
	 * Deactivation plugin method
	 */
	function deactivation() {
		delete_option( $this->options_name );
		unregister_setting( $this->domain, $this->options_name );
	}

	/**
	 * Set options from save values or defaults
	 */
	function _set_options() {
		// set options
		$saved_options = get_option( $this->options_name );

		// backwards compatible (old values)
		if ( empty( $saved_options ) ) {
			$saved_options = get_option( $this->domain . 'options' );
		}
		
		// set all options
		if ( ! empty( $saved_options ) ) {
			foreach ( $this->options AS $key => $option ) {
				$this->options[ $key ] = ( empty( $saved_options[ $key ] ) ) ? '' : $saved_options[ $key ];
			}
		}
	}
	
} // end class WP_Print_O_Matic


/**
 * Create instance
 */
$WP_Print_O_Matic = new WP_Print_O_Matic;

?>