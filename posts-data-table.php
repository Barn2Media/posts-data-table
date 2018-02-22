<?php
/**
 * The main plugin file for Posts Table with Search & Sort.
 *
 * @wordpress-plugin
 * Plugin Name:       Posts Table with Search & Sort
 * Plugin URI:		  https://wordpress.org/plugins/posts-data-table/
 * Description:       Provides a shortcode to show a list of your posts in an instantly searchable & sortable table.
 * Version:           1.1.1
 * Author:            Barn2 Media
 * Author URI:        https://barn2.co.uk
 * Text Domain:       posts-data-table
 * Domain Path:       /languages
 *
 * Copyright:		  2016-2018 Barn2 Media Ltd
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Current version of this plugin
define( 'POSTS_DATA_TABLE_VERSION', '1.1.1' );

/**
 * The main plugin class.
 *
 * @package   Barn2\Posts_Table_Search_And_Sort
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class Posts_Data_Table_Plugin {

	private $shortcode;

	public static function bootstrap() {
		$self = new self();
		$self->load();
	}

	public function load() {
		$this->includes();

		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_plugin_settings_link' ) );
	}

	private function includes() {
		$includes = plugin_dir_path( __FILE__ ) . 'includes/';

		require_once $includes . 'class-posts-data-table-simple.php';
		require_once $includes . 'class-posts-data-table-shortcode.php';
	}

	public function init() {
		// Don't init plugin if Pro version exists
		if ( class_exists( 'Posts_Table_Pro_Plugin' ) ) {
			return;
		}

		// Load the text domain - should go on 'plugins_loaded' hook
		$this->load_textdomain();

		$this->shortcode = new Posts_Data_Table_Shortcode();

		// Register styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'posts-data-table', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function register_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : 'min.';

		wp_enqueue_style( 'jquery-data-tables', plugins_url( 'assets/css/datatables/datatables.min.css', __FILE__ ), array(), '1.10.15' );
		wp_enqueue_style( 'posts-data-table', plugins_url( "assets/css/posts-data-table.{$suffix}css", __FILE__ ), array( 'jquery-data-tables' ), POSTS_DATA_TABLE_VERSION );
	}

	public function register_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : 'min.';

		wp_enqueue_script( 'jquery-data-tables', plugins_url( "assets/js/datatables/datatables.{$suffix}js", __FILE__ ), array( 'jquery' ), '1.10.15', true );
		wp_enqueue_script( 'posts-data-table', plugins_url( "assets/js/posts-data-table.{$suffix}js", __FILE__ ), array( 'jquery-data-tables' ), POSTS_DATA_TABLE_VERSION, true );

		$locale				 = get_locale();
		$supported_locales	 = $this->get_supported_locales();

		// Add language file to script if locale is supported (English file is not added as this is the default language)
		if ( array_key_exists( $locale, $supported_locales ) ) {
			wp_localize_script( 'posts-data-table', 'posts_data_table', array(
				'langurl' => $supported_locales[$locale]
			) );
		}
	}

	public function add_plugin_settings_link( $links ) {
		$links[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://barn2.co.uk/wordpress-plugins/posts-table-pro/' ), __( 'Pro Version', 'posts-data-table' ) );
		return $links;
	}

	/**
	 * Returns an array of locales supported by the plugin.
	 * The array returned uses the locale as the array key mapped to the URL of the corresponding translation file.
	 *
	 * @return array The supported locales
	 */
	private function get_supported_locales() {
		$lang_file_base_url = plugins_url( 'languages/data-tables/', __FILE__ );

		return apply_filters( 'posts_data_table_supported_languages', array(
			'es_ES'	 => $lang_file_base_url . 'Spanish.json',
			'fr_FR'	 => $lang_file_base_url . 'French.json',
			'fr_BE'	 => $lang_file_base_url . 'French.json',
			'fr_CA'	 => $lang_file_base_url . 'French.json',
			'de_DE'	 => $lang_file_base_url . 'German.json',
			'de_CH'	 => $lang_file_base_url . 'German.json',
			'el'	 => $lang_file_base_url . 'Greek.json',
			'el_EL'	 => $lang_file_base_url . 'Greek.json',
			) );
	}

}
// end class

Posts_Data_Table_Plugin::bootstrap();