<?php
/**
 * Plugin Name:       Changecrab
 * Description:       Changecrab embed code management
 * Version:           1.0.0
 * Author:            
 * Author URI:        
 * Text Domain:       changecrab
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * GitHub Plugin URI: 
 */


/*
 * Plugin constants
 */
if(!defined('CHANGECRAB_PLUGIN_VERSION'))
	define('CHANGECRAB_PLUGIN_VERSION', '1.1.0');
if(!defined('CHANGECRAB_URL'))
	define('CHANGECRAB_URL', plugin_dir_url( __FILE__ ));
if(!defined('CHANGECRAB_PATH'))
	define('CHANGECRAB_PATH', plugin_dir_path( __FILE__ ));

/*
 * Main class
 */
/**
 * Class CHANGECRAB
 *
 * This class creates the option page and add the web app script
 */
class CHANGECRAB
{

	/**
	 * The security nonce
	 *
	 * @var string
	 */
	private $_nonce = 'CHANGECRAB_admin';

	/**
	 * The option name
	 *
	 * @var string
	 */
	private $option_name = 'CHANGECRAB_data';

	/**
	 * CHANGECRAB constructor.
	 *
	 * The main plugin actions registered for WordPress
	 */
	public function __construct()
	{

		add_action('wp_footer',                 array($this,'addFooterCode'));

		// Admin page calls
		add_action('admin_menu',                array($this,'addAdminMenu'));
		add_action('wp_ajax_store_admin_data',  array($this,'storeAdminData'));
		add_action('admin_enqueue_scripts',     array($this,'addAdminScripts'));
	}

	/**
	 * Returns the saved options data as an array
	 *
	 * @return array
	 */
	private function getData()
	{
		return get_option($this->option_name, array());
	}

	/**
	 * Callback for the Ajax request
	 *
	 * Updates the options data
	 *
	 * @return void
	 */
	public function storeAdminData()
	{

		if (wp_verify_nonce($_POST['security'], $this->_nonce ) === false)
			die('Invalid Request! Reload your page please.');

		$data = $this->getData();

		foreach ($_POST as $field=>$value) {

			if (substr($field, 0, strlen('CHANGECRAB_')) !== "CHANGECRAB_")
				continue;

			if (empty($value))
				unset($data[$field]);

			// We remove the CHANGECRAB_ prefix to clean things up
			//$field = substr($field, 11);
			$field = str_replace('CHANGECRAB_', '', $field);

			$data[$field] = sanitize_text_field($value); // Sanitize the input before doing anything else.

		}

		update_option($this->option_name, $data);

		$user_id = sanitize_text_field($_POST['CHANGECRAB_userid']);

		if(ctype_alnum($user_id)) { // Check the string is alphanumeric
            $url = "https://changecrab.com/validate/" . $user_id; // Send a query to the validator to ensure it's a valid user ID.
            $response = wp_remote_get(esc_url_raw($url));
            $api_response = wp_remote_retrieve_body($response);

            echo $api_response;
        } else {
		    echo json_encode(array("success" => false));
        }

		die();

	}

	/**
	 * Adds Admin Scripts for the Ajax call
	 */
	public function addAdminScripts()
	{

		wp_enqueue_style('changecrab-admin', CHANGECRAB_URL. 'assets/css/admin.css', false, 1.0);

		wp_enqueue_script('changecrab-admin', CHANGECRAB_URL. 'assets/js/admin.js', array(), 1.0);

		$admin_options = array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'_nonce'   => wp_create_nonce( $this->_nonce ),
		);

		wp_localize_script('changecrab-admin', 'changecrab_exchanger', $admin_options);

	}

	/**
	 * Adds the CHANGECRAB label to the WordPress Admin Sidebar Menu
	 */
	public function addAdminMenu()
	{
		add_menu_page(
			__( 'Changecrab', 'CHANGECRAB' ),
			__( 'Changecrab', 'CHANGECRAB' ),
			'manage_options',
			'changecrab',
			array($this, 'adminLayout'),
			'dashicons-testimonial'
		);
	}

	/**
	 * Outputs the Admin Dashboard layout containing the form with all its options
	 *
	 * @return void
	 */
	public function adminLayout()
	{

		$data = $this->getData();

$menuLocations = get_nav_menu_locations(); // Get our nav locations (set in our theme, usually functions.php)
   // This returns an array of menu locations ([LOCATION_NAME] = MENU_ID);
$menuItems = array();

foreach ($menuLocations as $k => $menuID) {

	$primaryNav = wp_get_nav_menu_items($menuID);
	foreach ($primaryNav as $idx => $i) {
		$menuItems[$i->ID] = $i->title;
	}
}

//var_dump($primaryNav);
//foreach ($primaryNav as $idx => $i) {
//	/
//}
//echo '<pre>' . print_r($primaryNav, true) . '</pre>';
		?>

		<div class="crabwrap wrap">

						<div class="changecrabtop"><img src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/img/crablogo.png'; ?>"></h1></div>



			<form id="changecrab-admin-form" class="crabbg">

				<div class="form-group inside">

					<?php
					/*
					 * --------------------------
					 * API Settings
					 * --------------------------
					 */
					?>

                    <p class="lead"><span class="dashicons dashicons-sos"></span> Quickly embed your Changelog into your menu navigations using our Wordpress plugin. Simply insert your Project ID (provided inside of ChangeCrab) and then select which menu item you'd like to have the widget show on. Don't have a ChangeCrab account? <a target="_blank" href="https://changecrab.com?ref=wordpress">Create one free</a>




                    </p>
					<table class="form-table">
						<tbody>
							<tr>
								<td scope="row">
									<label><?php _e( 'Project ID', 'CHANGECRAB' ); ?></label>
								</td>
								<td>
									<input name="CHANGECRAB_userid"
										   id="CHANGECRAB_userid"
										   class="regular-text"
										   type="text"
										   value="<?php echo (isset($data['userid'])) ? $data['userid'] : ''; ?>"/>
								</td>
							</tr>
							<tr>
								<td scope="row">
									<label><?php _e( 'Selector', 'CHANGECRAB' ); ?></label>
								</td>
								<td>
									<select name="CHANGECRAB_selector" id="CHANGECRAB_selector" class="regular-text">
									<?php foreach ($menuItems as $k => $v) { ?>
										<option<?php (((isset($data['selector']) && !strcmp($data['selector'], '#menu-item-' . $k))) ? ' SELECTED' : ''); ?> value="#menu-item-<?php echo $k ?>"><?php echo $v ?></option>
									<?php } ?>
									</select>
									
								</td>
							</tr>
						</tbody>
					</table>
					
					<div id="notices"></div>

				</div>

			

				<div class="changefooter">

					<button class=" changebutton" id="CHANGECRAB-admin-save" type="submit">
						<?php _e( 'Save Settings', 'CHANGECRAB' ); ?>
					</button>
				</div>

			</form>

		</div>

		<?php

	}

	/**
	 * Add the web app code to the page's footer
	 *
	 * This contains the widget markup used by the web app and the widget API call on the frontend
	 * We use the options saved from the admin page
	 *
	 * @param $force boolean
	 *
	 * @return void
	 */
	public function addFooterCode($force = false)
	{

		$data = $this->getData();

		// Only if the survey id is selected and saved
		if(empty($data) || !isset($data['userid']) || !isset($data['selector']))
			return;

            wp_enqueue_script( "changecrab", "https://changecrab.com/embed/embed.js", array(), false, true );

            $projectID = strip_tags($data['userid']);
            $selector = strip_tags($data['selector']);

            wp_add_inline_script( "changecrab", 'changecrab_config = {"projectid": "'.$projectID.'", "selector": "'.$selector.'", "emptycolor": \'rgb(208 208 208)\', \'activecolor\': \'rgb(232 23 71)\'};  ','before' )
		?>

		<?php

	}

}

/*
 * Starts our plugin class, easy!
 */
new CHANGECRAB();
