<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://jonahgeluk.com/
 * @since      1.0.0
 *
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nf_Cleanup
 * @subpackage Nf_Cleanup/admin
 * @author     Jonah Geluk <jonah@retailenclicks.nl>
 */
class Nf_Cleanup_Admin {

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


	private $parser;

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nf_Cleanup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nf_Cleanup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        if(isset($_GET['page']) && strstr($_GET['page'], $this->plugin_name))
        {
            wp_enqueue_style( $this->plugin_name."-reset", plugin_dir_url( __FILE__ ) . 'css/reset.css', array(), $this->version, 'all' );

            wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nf-cleanup-admin.css', array(), $this->version, 'all' );
            wp_enqueue_style( $this->plugin_name."-material-framework", plugin_dir_url( __FILE__ ) . 'css/material.min.css', array(), $this->version, 'all' );
            wp_enqueue_style( $this->plugin_name."-material-icons", 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), $this->version, 'all' );
            wp_dequeue_style('pagination-style');
        }


	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Nf_Cleanup_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Nf_Cleanup_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
        if(isset($_GET['page']) && strstr($_GET['page'], $this->plugin_name))
        {
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/nf-cleanup-admin.js', array('jquery'), $this->version, false);
            wp_enqueue_script($this->plugin_name . "-material-framework", plugin_dir_url(__FILE__) . 'js/material.min.js', array('jquery'), $this->version, false);
            wp_localize_script($this->plugin_name, 'NF_CLEANUP', array(
                'security' => wp_create_nonce('nfc-retrieve-fields')
            ));
        }
    }


	/**
	 * Register admin menu for plugin
	 * @since   1.0.0
	 */

	public function add_plugin_admin_menu()
    {
        add_menu_page("Ninja Forms Cleanup", "NF Cleanup", 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page'), '../wp-content/plugins/'.$this->plugin_name.'/img/icon.png');
        //add new subitem
        add_submenu_page($this->plugin_name, "Add new handler", "Add new handler", 'manage_options', $this->plugin_name.'-addnewhandler', array($this, 'display_plugin_add_new_handler'));
        add_submenu_page($this->plugin_name, "Run Handlers", "Run Handlers", 'manage_options', $this->plugin_name.'-runhandlers', array($this, 'display_plugin_run_handlers'));

    }

    /**
	 * Add settings link to pluginpage
	 * @since   1.0.0
	 */

    public function add_action_links($links)
    {

        $settings_link = array(
          '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">'.__('Settings', $this->plugin_name).'</a>'
        );

        return array_merge($links, $settings_link);
    }

    /**
	 * Include admin display
	 * @since   1.0.0
	 */


    public function display_plugin_setup_page()
    {
        include_once 'partials/nf-cleanup-admin-display.php';
    }

    /**
     * Include run handlers page
     * @since   1.0.0
     */

    public function display_plugin_run_handlers()
    {
        include_once '../wp-content/plugins/'.$this->plugin_name.'/includes/class/parser.class.php';
        include_once '../wp-content/plugins/'.$this->plugin_name.'/includes/class/handler.class.php';
        include_once 'partials/pages/nf-cleanup-run-handlers-display.php';
    }

    /**
     * Include handler display
     * @since   1.0.0
     */
    public function display_plugin_add_new_handler()
    {
        include_once '../wp-content/plugins/'.$this->plugin_name.'/includes/class/parser.class.php';
        include_once '../wp-content/plugins/'.$this->plugin_name.'/includes/class/handler.class.php';

        include_once 'partials/pages/nf-cleanup-add-new-handle-display.php';
    }
    /**
	 * validate settings form submission
	 * @since   1.0.0
	 */

    public function validate($input)
    {
        $valid = array();

        $valid['auto_cleanup'] = (isset($input['auto_cleanup']) && !empty($input['auto_cleanup'])) ? 1 : 0;

        return $valid;
    }

    /**
	 * Register setting
	 * @since   1.0.0
	 */

    public function options_update()
    {
        register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
    }

}

include_once 'partials/pages/nf-cleanup-add-new-handle-functions.php';
