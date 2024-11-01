<?php
/*
  Plugin Name: WP MAIL REST-API
  Description: Send wordpess emails through Zaiper and IFTT
  Author: Marketing Auftrag
  Version: 1.0.0
  Author URI: http://marketing-auftrag.ch
 */

class WP_Mail_REST_API
{
    /**
     * Start up
     */
    public function __construct()
    {
        //Show plugin pages in left admin menu
        add_action( 'admin_menu', array( $this, 'add_plugin_pages' ) );
        
        //Override wp mail function to send requests to Webhook URLs and to disable wp email
        add_filter( 'wp_mail', array( $this, 'override_wp_email' ) );
        
        //Run this, when plugin is activated
        register_activation_hook( __FILE__, array( $this, 'activate_this_plugin' ) );
        
        //Run this when plugin is deactivated
        register_deactivation_hook( __FILE__, array( $this, 'deactivate_this_plugin' ) );
        
        //Run, when plugin is deleted.
        register_uninstall_hook( __FILE__, 'wp_mail_rest_api_remove_this_plugin' );
    }
    
    /**
     * Perform these action while activing a plugin
     */
    
    public function activate_this_plugin()
    {
        //We need to create a table, where we could store the log
        $this->create_db_table_for_log();
    }
    
    /**
     * Perform these action while deactiving a plugin
     */
    
    public function deactivate_this_plugin()
    {
        //do anything at deactivation
    }
    
    /**
     *Create log table for storing shots log
     */
    
    public function create_db_table_for_log()
    {
        global $wpdb;
        
        //table name for log
	$table_name = $wpdb->prefix . 'wp_mail_rest_api_log';
	
        //charset encoding
	$charset_collate = $wpdb->get_charset_collate();

        //Write query
	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
		args_json text,
                response text,
                url varchar(255),
		status tinyint(1) DEFAULT 1 NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

        //execute query
        $wpdb->query($sql);
    }
    
    /**
     * Delete DB table
     * 
     * @param string $table_name without prefix
     */
    
    public static function delete_db_table( $table_name )
    {
        global $wpdb;
        
        //log table name
	$table_name = $wpdb->prefix . $table_name;

        //Write query
	$sql = "DROP TABLE IF EXISTS $table_name;";

        //Execute Query
        $wpdb->query($sql);
    }
    
    /**
     * Override wp_mail function, this will send email arguments to webhooks and it will disable wp emai
     * 
     * @param array $args
     */
    
    public function override_wp_email( $args )
    {    
        //get option values and send post request to webhook urls
        $target_urls_option_value = get_option('wp_mail_rest_api_target_urls');
        
        //if there are any target urls and they saved as array
        if( $target_urls_option_value && is_array($target_urls_option_value) )
        {
            //loop through each url
            foreach( $target_urls_option_value as $target_url ):
                
                //send curl request and save its response
                $this->post_args_data_to_url( $target_url, $args );
                
            endforeach;
        }
        
        //wp_mail function's argument will be returned after manipulation
        return $args;
    }
    
    /**
     * This method will take email arguments and send them to webhook URLs
     * @param atring $url
     * @param array $args
     */
    public function post_args_data_to_url($url, $args)
    {
        //include Html2Text
        include_once 'Html2Text.php';
        
        //if args is not array, then typecast into an array
        $args = ( is_array( $args ) )? $args : (array)$args;
        
        //Convert HTML to text to use text_message using html2text
        $html = new \Html2Text\Html2Text( $args['message'] );
        $args['message_text'] = $html->getText();
        
        //Separate From Name and Email
        if( isset( $args['headers'] ) )
        {
            //extract from name and email and assign them in left variables with list function
            list($args['from_name'], $args['from_email']) = $this->extract_from_name_email( $args['headers'] );
        }        

        $post_data = array(
            'body' => json_encode($args),
            'timeout' => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array('Accept: application/json', 'Content-Type: application/json'),
            'cookies' => array()
        );

        $request = wp_remote_post( $url, $post_data );

        //if there is a wp error
        if( is_wp_error( $request ) )
        {
            $request_status = 0; //request status
            $request_response_body = $request->get_error_message(); //get error message
        }
        //if there is no wp error
        else
        {
            //if response code is 200, assign 1 i.e true
            $request_status = (isset( $request, $request['response'], $request['response']['code'] ) && $request['response']['code'] == 200)? 1 : 0;
            $request_response_body = isset( $request['body'] )? sanitize_text_field( $request['body'] ) : ''; //respone returned by webhook url
        }
        
        
        ///INSERT LOG START////
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_mail_rest_api_log';
        
        //preparing data for insertion
        $data = array( 'url' => $url,  'args_json' => json_encode($args), 'response' => $request_response_body, 'status' => $request_status  );
        
        //format of data being inserted
        $format = array('%s', '%s', '%s', '%d');
        
        //execute insetion
        $wpdb->insert( $table_name, $data, $format );
        
        //return insert id
        //return $wpdb->insert_id;
        ///INSERT LOG END////
        
        //check if webhook was posted
        return $request_status;
    }

    /**
     * Add menu pages for this plugin settings
     */
    public function add_plugin_pages()
    {
        //Add sub page for adding webhook urls
        add_options_page(
            __( 'WP Mail REST-API Target Webhooks', __DIR__ ),
            __( 'WP Mail REST-API Target Webhooks', __DIR__ ),
            'manage_options',
            'wp-mail-rest-api',
            array( $this, 'create_webhooks_page' )
        ); 
        
        //Add sub page for log page
        add_options_page(
            __( 'WP Mail REST-API Webhooks Log', __DIR__ ),
            __('WP Mail REST-API Webhooks Log', __DIR__),
            'manage_options',
            'wp-mail-rest-api-log',
            array( $this, 'create_log_page' )
        ); 
    }
    
    
    /**
     * This method will handle output of webhooks page, from where webhooks could be added
     */
    public function create_webhooks_page()
    {
        //get option values
        $target_urls_option_value = get_option('wp_mail_rest_api_target_urls');
                
        //if target values is already an array, assign it, otherwise, just assign an empty array
        $target_urls_option_array = $target_urls_option_value && is_array( $target_urls_option_value ) ? $target_urls_option_value : [];

        //urls array, will be sent for output
        $urls_to_output = $target_urls_option_value;

        //if new_webhook_url is posted
        $new_webhook_url = isset( $_POST['new_webhook_url'] ) ? esc_url_raw( $_POST['new_webhook_url'] ) : false;
        
        //check if required variables are set, it is a valid url, it does not already exist
        if( current_user_can('manage_options') && $new_webhook_url && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'add_new_webhook_url' ) && wp_http_validate_url( $new_webhook_url ) && !in_array( $new_webhook_url, $target_urls_option_array ) )
        {
            //push new url to array for insertion in options
            $target_urls_option_array[] = $new_webhook_url;
            
            //if option is updated with updated array
            if( update_option('wp_mail_rest_api_target_urls', $target_urls_option_array) )
            {
                //also updated urls for output
                $urls_to_output = $target_urls_option_array;
                
                //unset url variable
                unset( $new_webhook_url );
                
                $success_message_addition = 'New URL has been added.';
            } else
            {
                $error_message_addition = 'New URL couldnt be added.';
            }
        } else if ( $new_webhook_url )
        {
            $error_message_addition = 'The input (' . esc_url($new_webhook_url) . ') is not a valid URL or it is duplicate. ';
        }
        
        //if delete_webhook_url_id is posted
        $delete_webhook_url_id = isset( $_POST['delete_webhook_url_id'] ) ? intval( $_POST['delete_webhook_url_id'] ) : false;
        
        //If current user is an admistrator and delete webhook is requested
        if( current_user_can('manage_options') && $delete_webhook_url_id !== false && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'delete_webhook_url' ) )
        {
            //unset url
            unset( $target_urls_option_array[$delete_webhook_url_id] );
            
            //update optiont to save deletion
            if( update_option('wp_mail_rest_api_target_urls', $target_urls_option_array) )
            {
                //urls for output
                $urls_to_output = $target_urls_option_array;
                $success_message_deletion = 'The URL has been deleted.';
            }
        
        //if url is not valid or something false in IF condition
        } else if ( $delete_webhook_url_id !== false )
        {
            $error_message_deletion = 'The URL couldnt be deleted.';
        }
        
        //if test_webhook_url_id is posted
        $test_webhook_url_id = isset( $_POST['test_webhook_url_id'])? intval( $_POST['test_webhook_url_id'] ) : false;
        
        //If current user is an admistrator and test webhook request is received
        if( current_user_can('manage_options') && $test_webhook_url_id !== false && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'test_webhook_request' ) )
        {
            //unset url
            $url_to_test = $target_urls_option_array[$test_webhook_url_id];
            
            //send curl request and save its response
            $request_status = $this->post_args_data_to_url( $url_to_test, [
                'to' => get_option('admin_email'),
                'subject' => 'Test Subject from WP Mail REST API Plugin',
                'message' => '<html><body>Test HTML Message from WP Mail REST API Plugin.</body></html>',
                'message_text' => 'Only text of the message from WP Mail REST API Plugin',
                'from_name' => 'WP Mail REST API Plugin',
                'from_email' => get_option('admin_email'),
                'attachments' => ''
            ] );
            
            if( $request_status )
            {
                $success_message_testing_webhook = 'A test request has been sent at ' . $url_to_test;
            } else
            {
                $error_message_testing_webhook = 'There was a problem, while sending request at ' . $url_to_test;
            }
            
        
        //if url is not valid or something false in IF condition
        } else if ( $test_webhook_url_id !== false )
        {
            $error_message_testing_webhook = 'The webhook couldnt tested.';
        }
        
        
        //Is SMTP disabled, default value will be 1, if option does not exist
        $disable_smtp_option = get_option( 'wp_mail_rest_api_disable_smtp', 1 );
        
        
        //If current user can manage options and test webhook request is received
        if( current_user_can('manage_options') && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'disable_stmp' ) )
        {
            //if disable_smtp_value checkox is unchecked
            $target_disable_smtp_value = !isset($_POST['disable_smtp_value'])? 0 : 1;
            
            //update option
            if( update_option('wp_mail_rest_api_disable_smtp', $target_disable_smtp_value ) )
            {
                $disable_smtp_option = $target_disable_smtp_value;
                
                $success_message_disable_smtp = 'Settings have been saved.';
            } else
            {
                $error_message_disable_smtp = 'Settings did not update.';
            }
        } 
        
        
        //start beffering
        ob_start();
        
        //include view
        include __DIR__.'/webhooks-page.php';
        
        //get contents
        $contents = ob_get_contents();
        
        ob_end_clean();
        
        //send contents for output to browser
        echo $contents;
    }
    
    /**
     * This will handle log page, resent webhook request and all listing of log etc
     */
    public function create_log_page()
    {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wp_mail_rest_api_log';
        
        ///START RESEND WEBHOOK REQUEST///
        // if webhook_log_id is received
        $webhook_log_id = isset($_POST['webhook_log_id'])? intval($_POST['webhook_log_id']) : false;
        //
        //check if required variables are set and check nonce as well
        if( current_user_can('manage_options') && $webhook_log_id !== false && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'resend_webhook_request' ) )
        {
            //prepare and fetch db row
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE `id` = %d", $webhook_log_id ) );
            
            if( $row )
            {
                //send for curl
                if( $this->post_args_data_to_url($row->url,  json_decode($row->args_json)) )
                {
                    $success_message_resend_webhook_request = 'Request has been resent.';
                } else
                {
                    $error_message_resend_webhook_request = 'There was a problem, while sending the request.';
                }
            }
            
        } else if(  $webhook_log_id !== false )
        {
            $error_message_resend_webhook_request = 'This action cant be completed at this moment.';
        }
        ///END RESEND WEBHOOK REQUEST///
        
        
        //Get 100 log requests
        $log_results = $wpdb->get_results("SELECT id, args_json, response, url, status, created_at  FROM $table_name ORDER BY created_at DESC LIMIT 0, 100");
        
        //start collecting buffer
        ob_start();
        
        //load view
        include __DIR__.'/log-page.php';
        $contents = ob_get_contents();
        
        ob_end_clean();
        
        echo $contents;
    }
    
    /**
     * This method woll receive headers as string or array and provide us an array of fron name and from email
     * 
     * @param array/string $headers
     * 
     * @return array
     */
    
    public function extract_from_name_email( $headers )
    {
        //initialize address_string
        $address_string = '';
        
        
        //if headers is an onject, convert it into an array
        if( is_object( $headers ) )
        {
            $headers = (array)$headers; //typecasting
        }
        
        //if From index found in header
        if( isset( $headers['From'] ) )
        {
            //decode any html entities
            $address_string = html_entity_decode($headers['From']);
        }
        //if passed headers are a string
        else if ( is_string( $headers ) )
        {
            //decode html entities
            $headers = html_entity_decode($headers);
            
            //use lower case to get good positioning points
            $headers_lower = strtolower( $headers );
            
            //position of from in lowered header string
            $lower_from_position = strpos($headers_lower, 'from:');
            
            //closing position of from at >
            $lower_closing_position = strpos($headers_lower, '>', $lower_from_position);
            
            //if lower from/closing options are not exactly false and closing is greater than start
            if( $lower_from_position !== FALSE && $lower_closing_position !== FALSE  && $lower_closing_position > $lower_from_position )
            {
                //get address string
                $address_string = substr($headers, $lower_from_position, $lower_closing_position - $lower_from_position + 1);
            }
        }
        
        //go into this, only, if address string is true
        if( $address_string )
        {
            //get start position from from address string
            $email_start_position = strpos($address_string, '<') + 1;

            //get ending position
            $email_end_position = strpos($address_string, '>', $email_start_position);

            //prepare from email
            $from_email = substr($address_string, $email_start_position, $email_end_position - $email_start_position );

            //prepare from name
            $from_name = substr( $address_string, 7, $email_start_position - 10 ); // substr( $address_string, 6, $email_start_position - 2 );

            //return data
            return [ $from_name, $from_email ];
        }
        //if address string is false
        else
        {
            //return empty data
            return ['', ''];
        }
        
    }
}

/**
 * Completely remove alll settings
 */
function wp_mail_rest_api_remove_this_plugin()
{
    //Delete log table
    WP_Mail_REST_API::delete_db_table( 'wp_mail_rest_api_log' );
    
    //Delete options, which store webhook URLs
    delete_option('wp_mail_rest_api_target_urls');

    //Delete option about disabling smtp plugin
    delete_option('wp_mail_rest_api_disable_smtp');
}

//Initialize the plugin
$wp_mail_rest_api = new WP_Mail_REST_API();

//Redefine wp_mail function, if wpmail smtp has been disabled.

if ( ! function_exists( 'wp_mail' ) && get_option( 'wp_mail_rest_api_disable_smtp', 1 ) == 1 ) 
{
    /**
     * Override wp_mail function
     * 
     * @param string $to
     * @param string $subject
     * @param string/text/html $message
     * @param array $headers
     * @param files attachments

     */
    function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() )
    {
        //These filter will be used to catch wp_mail arguments
        apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );
        
        return true;
    }
}