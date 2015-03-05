<?php
/*
Plugin Name: Print-O-Matic
Text Domain: printomat
Domain Path: /language
Plugin URI: http://plugins.twinpictures.de/plugins/print-o-matic/
Description: Shortcode that adds a printer icon, allowing the user to print the post or a specified HTML element in the post.
Version: 1.6.0
Author: twinpictures
Author URI: http://twinpictuers.de
License: GPL2
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
	var $version = '1.6.0';

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
		'do_not_print' => '',
		'printicon' => 'true',
		'printstlye' => 'pom-default',
		'use_theme_css' => '',
		'custom_page_css' => '',
		'custom_css' => '',
		'html_top' => '',
		'html_bottom' => '',
		'script_check' => '',
		'fix_clone' => '',
		'pause_time' => '',
	);
	
	var $add_print_script = array();
	
	
	/**
	 * PHP5 constructor
	 */
	function __construct() {
		// set option values
		$this->_set_options();
		
		// load text domain for translations
		load_plugin_textdomain( 'printomat', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/language/' );

		// set uninstall hook
		if ( function_exists( 'register_deactivation_hook' ) )
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ));
		
		//load the script and style if not viewing the dashboard
		add_action('wp_enqueue_scripts', array( $this, 'printMaticInit' ) );
		
		// add actions
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_head', array( $this, 'printomat_style' ) );
		add_shortcode('print-me', array($this, 'shortcode'));
		add_action( 'wp_footer', array($this, 'printer_scripts') );
		
		// Add shortcode support for widgets  
		add_filter('widget_text', 'do_shortcode');
	}
	
	//global javascript vars
	function printomat_style(){
		if( !empty( $this->options['custom_page_css'] ) ){
			echo "\n<style>\n";
			echo $this->options['custom_page_css'];
			echo "\n</style>\n";
		}
	}
	
	/**
	 * Callback init
	 */
	function printMaticInit() {
		//script
		wp_register_script('printomatic-js', plugins_url('/printomat.js', __FILE__), array('jquery'), '1.6.0');
		if( empty($this->options['script_check']) ){
			wp_enqueue_script('printomatic-js');
		}
		
		wp_register_script('jquery-clone-fix', plugins_url('/jquery.fix.clone.js', __FILE__), array('jquery'), '1.1');
		if( empty($this->options['script_check']) && !empty($this->options['fix_clone']) ){
			wp_enqueue_script('jquery-clone-fix');
		}
		
		//css
		wp_register_style( 'printomatic-css', plugins_url('/css/style.css', __FILE__) , array (), '1.2' );
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
		
		if( !empty($this->options['script_check']) ){
			wp_enqueue_script('printomatic-js');
			if(!empty($this->options['fix_clone'])){
				wp_enqueue_script('jquery-clone-fix');
			}
		}
		
		extract( shortcode_atts(array(
			'id' => 'id'.$ran,
			'class' => '',
			'tag' => 'div',
			'target' => $options['print_target'],
			'do_not_print' => $options['do_not_print'],
			'printicon' => $options['printicon'],
			'printstlye' => $options['printstlye'],
			'html_top' => $options['html_top'],
			'html_bottom' => $options['html_bottom'],
			'pause_before_print' => $options['pause_time'],
			'title' => '',
			'alt' => ''
		), $atts));
		
		//if no printstyle, force-set to default
		if( empty( $printstlye ) ){
			$printstlye = 'pom-default';
		}
		
		//swap target placeholders out for the real deal
		$target = str_replace('%ID%', get_the_ID(), $target);
		
		if( empty( $options['use_theme_css'] ) ){
			$pom_site_css = '';
		}else{
			$pom_site_css = get_stylesheet_uri();
		}
		if( empty( $options['custom_css'] ) ){
			$pom_custom_css = '';
		}
		else{
			$pom_custom_css = $options['custom_css'];
		}
		if( empty( $html_top ) ){
			$pom_html_top = '';
		}
		else{
			$pom_html_top = $html_top;
		}
		if( empty( $html_bottom ) ){
			$pom_html_bottom = '';
		}
		else{
			$pom_html_bottom = $html_bottom;
		}
		if( empty( $do_not_print ) ){
			$pom_do_not_print = '';
		}
		else{
			$pom_do_not_print = $do_not_print;
		}
		
		$this->add_print_script[$id] = array(
							'pom_site_css' => $pom_site_css,
							'pom_custom_css' => $pom_custom_css,
							'pom_html_top' => $pom_html_top,
							'pom_html_bottom' => $pom_html_bottom,
							'pom_do_not_print' => $pom_do_not_print,
							'pom_pause_time' => $pause_before_print
						);
		
		if($printicon == "false"){
			$printicon = 0;
		}
		if( empty($alt) ){
			if( empty($title) ){
				$alt_tag = '';
			}
			else{
				$alt_tag = "alt='".$title."' title='".$title."'";
			}
		}
		else{
			$alt_tag = "alt='".$alt."' title='".$alt."'";
		}
		if($printicon && $title){
			$output = "<div class='printomatic ".$printstlye." ".$class."' id='".$id."' ".$alt_tag." data-print_target='".$target."'></div> <div class='printomatictext' id='".$id."' ".$alt_tag.">".$title."</div><div style='clear: both;'></div>";
		}
		else if($printicon){
			$output = "<".$tag." class='printomatic ".$printstlye." ".$class."'' id='".$id."' ".$alt_tag." data-print_target='".$target."'></".$tag.">";
		}
		else if($title){
			$output = "<".$tag." class='printomatictext ".$class."'' id='".$id."' ".$alt_tag." data-print_target='".$target."'>".$title."</".$tag.">";
		}
		
		return  $output;
	}
	
	function printer_scripts() { 
		if ( empty( $this->add_print_script ) ){
			return;
		}
		
		?>
		<script language="javascript" type="text/javascript">
			var print_data = <?php echo json_encode( $this->add_print_script ); ?>;
		</script>
		<?php
	}
	
	/**
	 * Admin options page
	 */
	function options_page() {
		$like_it_arr = array(
						__('really tied the room together', 'printomat'),
						__('made you feel all warm and fuzzy on the inside', 'printomat'),
						__('restored your faith in humanity... even if only for a fleeting second', 'printomat'),
						__('rocked your world', 'provided a positive vision of future living', 'printomat'),
						__('inspired you to commit a random act of kindness', 'printomat'),
						__('encouraged more regular flossing of the teeth', 'printomat'),
						__('helped organize your life in the small ways that matter', 'printomat'),
						__('saved your minutes--if not tens of minutes--writing your own solution', 'printomat'),
						__('brightened your day... or darkened if if you are trying to sleep in', 'printomat'),
						__('caused you to dance a little jig of joy and joyousness', 'printomat'),
						__('inspired you to tweet a little @twinpictues social love', 'printomat'),
						__('tasted great, while also being less filling', 'printomat'),
						__('caused you to shout: "everybody spread love, give me some mo!"', 'printomat'),
						__('helped you keep the funk alive', 'printomat'),
						__('<a href="http://www.youtube.com/watch?v=dvQ28F5fOdU" target="_blank">soften hands while you do dishes</a>', 'printomat'),
						__('helped that little old lady <a href="http://www.youtube.com/watch?v=Ug75diEyiA0" target="_blank">find the beef</a>', 'printomat')
					);
		$rand_key = array_rand($like_it_arr);
		$like_it = $like_it_arr[$rand_key];
	?>
		<div class="wrap">
			<div class="icon32" id="icon-options-custom" style="background:url( <?php echo plugins_url( 'css/print-icon.png', __FILE__ ) ?> ) no-repeat 50% 50%"><br></div>
			<h2>Print-O-Matic</h2>
		</div>
		
		<div class="postbox-container metabox-holder meta-box-sortables" style="width: 69%">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ) ?>"><br/></div>
					<h3 class="handle"><?php _e( 'Print-O-Matic Settings', 'printomat' ) ?></h3>
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
									<th><?php _e( 'Default Target Attribute' , 'printomat'  ) ?></th>
									<td><label><input type="text" id="<?php echo $this->options_name ?>[print_target]" name="<?php echo $this->options_name ?>[print_target]" value="<?php echo $options['print_target']; ?>" />
										<br /><span class="description"><?php printf(__('Print target. See %sTarget Attribute%s in the documentation for more info.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#target" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Use Print Icon', 'printomat' ) ?></th>
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
									<th><?php _e( 'Printer Icon', 'printomat') ?></th>
									<td>
										<?php
											$si_array = array(
												__('Default', 'printomat') => 'pom-default',
												__('Small', 'printomat') => 'pom-small',
												__('Small Black', 'printomat') => 'pom-small-black',
												__('Small Grey', 'printomat') => 'pom-small-grey',
												__('Small White', 'printomat') => 'pom-small-white'
											);
											$icon_array = array(
												'pom-default' => 'print-icon.png',
												'pom-small' => 'print-icon-small.png',
												'pom-small-black' => 'print-icon-small-black.png',
												'pom-small-grey' => 'print-icon-small-grey.png',
												'pom-small-white' => 'print-icon-small-white.png'
											);
											foreach( $si_array as $key => $value){
												$selected = '';
												if($options['printstlye'] == $value){
													$selected = 'checked';
												}
												?>
												<label><input type="radio" name="<?php echo $this->options_name ?>[printstlye]" value="<?php echo $value; ?>" <?php echo $selected; ?>> &nbsp;<?php echo $key; ?>
												<img src="<?php echo plugins_url( 'css/'.$icon_array[$value], __FILE__ ) ?>"/>
												</label><br/>
												<?php
											}
										?>
										<span class="description"><?php printf(__('If using a printer icon, which printer icon should be used? See %sPrintstlye Attribute%s in the documentation for more info.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#printstlye" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Custom Style', 'printomat' ) ?></th>
									<td><label><textarea id="<?php echo $this->options_name ?>[custom_page_css]" name="<?php echo $this->options_name ?>[custom_page_css]" style="width: 100%; height: 150px;"><?php echo $options['custom_page_css']; ?></textarea>
										<br /><span class="description"><?php printf(__('Custom <strong>display page</strong> CSS Style for for <em>Ultimate Flexibility</em>. Here are some helpful %scustom CSS samples%s', 'printomat' ), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#cssexamples" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Use Theme CSS For Print Page', 'printomat' ) ?></th>
									<td><label><input type="checkbox" id="<?php echo $this->options_name ?>[use_theme_css]" name="<?php echo $this->options_name ?>[use_theme_css]" value="1"  <?php echo checked( $options['use_theme_css'], 1 ); ?> /> <?php _e('Yes, Use Theme CSS', 'printomat'); ?>
										<br /><span class="description"><?php _e('Use the CSS style of the active theme for print page.', 'printomat'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Custom Print Page Style', 'printomat' ) ?></th>
									<td><label><textarea id="<?php echo $this->options_name ?>[custom_css]" name="<?php echo $this->options_name ?>[custom_css]" style="width: 100%; height: 150px;"><?php echo $options['custom_css']; ?></textarea>
										<br /><span class="description"><?php _e( 'Custom <strong>print page</strong> CSS style for <em>Ultimate Flexibility</em>', 'printomat' ) ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Do Not Print Elements', 'printomat' ) ?></th>
									<td><label><input type="text" id="<?php echo $this->options_name ?>[do_not_print]" name="<?php echo $this->options_name ?>[do_not_print]" value="<?php echo $options['do_not_print']; ?>" />
										<br /><span class="description"><?php printf(__('Content elements to exclude from the print page. See %sDo Not Print Attribute%s in the documentation for more info.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#do-no-print" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Print Page Top HTML', 'printomat' ) ?></th>
									<td><label><textarea id="<?php echo $this->options_name ?>[html_top]" name="<?php echo $this->options_name ?>[html_top]" style="width: 100%; height: 150px;"><?php echo $options['html_top']; ?></textarea>
										<br /><span class="description"><?php printf(__('HTML to be inserted at the top of the print page. See %sHTML Top Attribute%s in the documentation for more info.', 'printomat' ), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#html-top" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Print Page Bottom HTML', 'printomat' ) ?></th>
									<td><label><textarea id="<?php echo $this->options_name ?>[html_bottom]" name="<?php echo $this->options_name ?>[html_bottom]" style="width: 100%; height: 150px;"><?php echo $options['html_bottom']; ?></textarea>
										<br /><span class="description"><?php printf(__('HTML to be inserted at the bottom of the print page. See %sHTML Bottom Attribute%s in the documentation for more info.', 'printomat' ), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/#html-bottom" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								<tr>
									<th><?php _e( 'Shortcode Loads Scripts', 'printomat' ) ?></th>
									<td><label><input type="checkbox" id="<?php echo $this->options_name ?>[script_check]" name="<?php echo $this->options_name ?>[script_check]" value="1"  <?php echo checked( $options['script_check'], 1 ); ?> /> <?php _e('Only load scripts with shortcode.', 'printomat'); ?>
										<br /><span class="description"><?php _e('Only load  Print-O-Matic scripts if [print-me] shortcode is used.', 'printomat'); ?></span></label>
									</td>
								</tr>
								
								<tr>
									<th><?php _e( 'Activate jQuery fix.clone', 'printomat' ) ?></th>
									<td><label><input type="checkbox" id="<?php echo $this->options_name ?>[fix_clone]" name="<?php echo $this->options_name ?>[fix_clone]" value="1"  <?php echo checked( $options['fix_clone'], 1 ); ?> /> <?php _e('Activate if textbox content is not printing.', 'printomat'); ?>
										<br /><span class="description"><?php printf(__('Addresses known bug with textboxes and the jQuery clone function. %sjquery.fix.clone on github.com%s', 'printomat'), '<a href="http://github.com/spencertipping/jquery.fix.clone/" target="_blank">', '</a>'); ?></span></label>
									</td>
								</tr>
								
								<tr>
									<th><?php _e( 'Pause Before Print', 'printomat' ) ?></th>
									<td><label><input type="text" id="<?php echo $this->options_name ?>[pause_time]" name="<?php echo $this->options_name ?>[pause_time]" value="<?php echo $options['pause_time']; ?>" />
										<br /><span class="description"><?php _e('Amount of time in milliseconds to pause and let the page fully load before triggering the print dialogue box', 'printomat'); ?></span></label>
									</td>
								</tr>
								
								</table>
							</fieldset>
							
							<p class="submit">
								<input class="button-primary" type="submit" style="float:right" value="<?php _e( 'Save Changes' ) ?>" />
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
					<h3 class="handle"><?php _e( 'About' ) ?></h3>
					<div class="inside">
						<h4><img src="<?php echo plugins_url( 'css/print-icon-small.png', __FILE__ ) ?>" /> Print-O-Matic Version <?php echo $this->version; ?></h4>
						<p><?php _e( 'Shortcode that adds a printer icon, allowing the user to print the post or a specified HTML element in the post.', 'printomat') ?></p>
						<ul>
							<li><?php printf( __( '%sDetailed documentation%s, complete with working demonstrations of all shortcode attributes, is available for your instructional enjoyment.', 'printomat'), '<a href="http://plugins.twinpictures.de/plugins/print-o-matic/documentation/" target="_blank">', '</a>'); ?></li>
							<li><?php printf( __( 'Free, Open Source %sSupport%s', 'printomat'), '<a href="http://wordpress.org/support/plugin/print-o-matic" target="_blank">', '</a>'); ?></li>
							<li><?php printf( __('If Print-O-Matic %s, please consider %sreviewing it at WordPress.org%s to better help others make informed plugin choices.', 'printomat'), $like_it, '<a href="http://wordpress.org/support/view/plugin-reviews/print-o-matic" target="_blank">', '</a>' ) ?></li>
							<li><a href="http://wordpress.org/extend/plugins/print-o-matic/" target="_blank">WordPress.org</a> | <a href="http://plugins.twinpictures.de/plugins/print-o-matic/" target="_blank">Twinpictues Plugin Oven</a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		
		<div class="postbox-container side metabox-holder meta-box-sortables" style="width:29%;">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ) ?>"><br/></div>
					<h3 class="handle"><?php _e( 'Level Up!' ) ?></h3>
					<div class="inside">
						<p><?php printf(__( '%sPrint-Pro-Matic%s is our premium plugin that offers a few additional attributes and features for <i>ultimate</i> flexibility.', 'print-o-mat' ), '<a href="http://plugins.twinpictures.de/premium-plugins/print-pro-matic/?utm_source=print-o-matic&utm_medium=plugin-settings-page&utm_content=print-pro-matic&utm_campaign=print-pro-level-up">', '</a>'); ?></p>		
						<p style="padding: 5px; border: 1px dashed #cccc66; background: #EEE;"><strong>Special Offer:</strong> <a href="http://plugins.twinpictures.de/premium-plugins/print-pro-matic/?utm_source=print-o-matic&utm_medium=plugin-settings-page&utm_content=print-pro-matic&utm_campaign=print-pro-may-the-forth">Update to Print-Pro-Matic</a> with discount code: <strong>MAYTHEFORTH</strong> on or before May 4th, 2015 and get a 15% discount. Why? Because Star Wars, that's why.</p>
						<h4><?php _e('Reasons To Go Pro', 'printomat'); ?></h4>
						<ol>
							<li><?php _e('You are an advanced user and have advanced needs.', 'printomat'); ?></li>
							<li><?php _e('Print-O-Matic was just what I needed. Here, have some money.', 'printomat'); ?></li>
							<li>Special Offer: May the forth be with you.</li>
						</ol>
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