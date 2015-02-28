<?php
error_reporting(E_ALL);
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct($page_type)
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, $page_type.'_page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_menu_page(
            'Settings Admin', 
            'SMSF Integration Settings', 
            '', 
            'smsf-api-settings', 
            array( $this, '' )
        );

		add_submenu_page(
            'smsf-api-settings',
            '', 
            'Box Settings', 
            'manage_options', 
            'box-api-settings', 
            array( $this, 'create_box_page' )
        );
    }
    public function create_tabs(){

        $tabs = array( 'xero' => 'XERO', 'box' => 'BOX', 'class_super' => 'Class Super' );
        echo '<div id="icon-themes" class="icon32"><br></div>';
        echo '<h2 class="nav-tab-wrapper">';
        foreach( $tabs as $tab => $name ){
            $class = ( $tab == $current ) ? ' nav-tab-active' : '';
            echo "<a class='nav-tab$class' href='?page=theme-settings&tab=$tab'>$name</a>";

        }
        echo '</h2>';
    }

	public function create_box_page()
    {
        // Set class property
        $this->options = get_option( 'box-api-options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?> 
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'box-api-options-group' );   
                do_settings_sections( 'box-api-settings' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

	public function box_page_init()
    {        
        register_setting(
            'box-api-options-group', // Option group
            'box-api-options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'BOX API Settings', // secret
            array( $this, 'print_section_info' ), // Callback
            'box-api-settings' // Page
        );  

        add_settings_field(
            'client_id', // ID
            'Client id', // secret 
            array( $this, 'client_id_callback' ), // Callback
            'box-api-settings', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'client_secret', 
            'Client secret', 
            array( $this, 'client_secret_callback' ), 
            'box-api-settings', 
            'setting_section_id'
        );

        add_settings_field(
            'api_key', // ID
            'Api key', // title 
            array( $this, 'api_key_callback' ), // Callback
            'box-api-settings', // Page
            'setting_section_id' // Section           
        );
    
    }
    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();

		//For box fields
		if( isset( $input['client_id'] ) )
            $new_input['client_id'] = sanitize_text_field( $input['client_id'] );

        if( isset( $input['client_secret'] ) )
            $new_input['client_secret'] = sanitize_text_field( $input['client_secret'] );

        if( isset( $input['api_key'] ) )
            $new_input['api_key'] = sanitize_text_field( $input['api_key'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print '';
    }

    public function client_id_callback()
    {
        printf(
            '<input type="text" id="client_id" name="box-api-options[client_id]" value="%s" />',
            isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function client_secret_callback()
    {
        printf(
            '<input type="text" id="client_secret" name="box-api-options[client_secret]" value="%s" />',
            isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret']) : ''
        );
    }
	/** 
     * Get the settings option array and print one of its values
     */
    public function api_key_callback()
    {
        printf(
            '<input type="text" id="api_key" name="box-api-options[api_key]" value="%s" />',
            isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key']) : ''
        );
    }
}

if( is_admin() ){

	if(in_array('box-api-settings', $_REQUEST) || in_array('box-api-options-group', $_REQUEST))
		$my_settings_page = new MySettingsPage('box');
	else
		$my_settings_page = new MySettingsPage('box');


}