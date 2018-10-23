<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.nativerank.com
 * @since      1.0.0
 *
 * @package    Nr_Bpi
 * @subpackage Nr_Bpi/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nr_Bpi
 * @subpackage Nr_Bpi/admin
 * @author     Sahil Khanna <sahil.khanna@nativerank.com>
 */
class Nr_Bpi_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nr_Bpi_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nr_Bpi_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


        global $page_hook_suffix_providers;
        if( $hook != $page_hook_suffix_providers )
        {
            return;
        }
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nr-bpi-admin.css', array(), $this->version, 'all' );



	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nr_Bpi_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nr_Bpi_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


        global $page_hook_suffix_providers;

        if( $hook != $page_hook_suffix_providers )
        {
            return;
        }
       wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/nr-bpi-admin.js', array( 'plugin-js' ), $this->version, false );
        wp_enqueue_script( $this->plugin_name, 'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0-beta/js/materialize.min.js', '', $this->version, true );



	}


    public function add_plugin_admin_menu() {

        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        global $page_hook_suffix_providers;

        $page_hook_suffix_providers = add_submenu_page( 'edit.php?post_type=nr_provider', 'Import Providers | BioTE', 'Import Providers', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'));
        add_submenu_page(
            null            // -> Set to null - will hide menu link
            , 'CSV IMPORTER API'    // -> Page Title
            , 'CSV IMPORTER API'    // -> Title that would otherwise appear in the menu
            , 'administrator' // -> Capability level
            , 'csv_importer_api'   // -> Still accessible via admin.php?page=menu_handle
            , array($this, 'display_plugin_csv_importer_api_page') // -> To render the page
        );
    }






    public function add_import_button($views)
    {

            $views['my-button'] = '<a id="update-from-provider" class="uk-button uk-button-primary uk-padding-small" href="edit.php?post_type=nr_provider&page=nr-bpi" title="Update from Provider">Import From CSV</a>';
            return $views;

    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */

    public function add_action_links( $links ) {
        /*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
        );
        return array_merge(  $settings_link, $links );

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */


    public function display_plugin_setup_page() {
        include_once( 'partials/nr-bpi-admin-display.php' );
    }

    public function display_plugin_csv_importer_api_page() {
        include_once( 'partials/nr-bpi-admin-csv-importer-api.php' );
    }


}
