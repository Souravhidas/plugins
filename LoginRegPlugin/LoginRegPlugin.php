<?php
/*
Plugin Name: Register Login
Plugin URI: https://wordpress.org/plugins/
Description: login and register of your WordPress 
Version: 1.0
Author: sourav das
Author URI: http://souravhidas@gmail.com
Text Domain: login register
Domain Path: /languages
*/
if(!defined('ABSPATH')){
    echo"This is My  Plugin ";
    exit;
}

  class CustomRegister{
  function __construct(){
   add_action('init',array($this,'CustomRegisterWordpress'));
//    enquee the script in plugin 
  add_action('wp_enqueue_scripts',array($this,'load_assets'));

  // add  do short code

  add_shortcode( 'register_short_code', array($this,'register_shortcode') );

//   add footer script
  add_action('wp_footer',array($this,'wp_script'));
  
//   add rest api
 add_action('rest_api_init',array($this,'register_rest_api') );

//  add login short_code

add_shortcode( 'register_login_code', array($this,'loginShortCode'));

// add login action api code
add_action('rest_api_init',array($this,'register_rest_api_login'));
  }
  function CustomRegisterWordpress(){

  }
  function register_shortcode(){
  
    ?>
 
    <section class="container">
      <header>Registration Form</header>
      <form  action="#" class="form" id="register_form">
        <div class="input-box">
          <label>Full Name</label>
          <input type="text"  name="full_name" placeholder="Enter full name" required />
        </div>

        <div class="input-box">
          <label>Email Address</label>
          <input type="text" name="email" placeholder="Enter email address" required />
        </div>
        <div class="input-box">
          <label>Password</label>
          <input type="text" name="password" placeholder="Enter your Password" required />
        </div>
        
        <div class="input-box">
          <label>Confirm Password</label>
          <input type="text" name="confirm_password" placeholder="Enter email Confirm Password" required />
        </div>
        <button type="submit">Submit</button>
      </form>
 
</section>
    <?php
  
  }


function register_rest_api() {
    register_rest_route('customapi/v1', '/create_user', array(
        'methods' => 'POST',
        'callback' => array($this, 'create_user_endpoint'),
    ));
}

function create_user_endpoint($request) {
    $headers = $request->get_headers();
    $parameters = $request->get_params();
 $nonce = $headers['x_wp_nonce']['0'];
//  return $nonce;
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_REST_Response('User not registered - Nonce verification failed', 422);
    }

    // Use email as username for wp_create_user
    $user_id = wp_create_user($parameters['email'], $parameters['password'], $parameters['email']);

    if (is_wp_error($user_id)) {
        return new WP_Error('create_user_error', $user_id->get_error_message(), array('status' => 500));
    }

    // Retrieve user information
    $user = get_user_by('ID', $user_id);

    if ($user) {
        wp_send_json('User registered successfully', 200);
    } else {
        wp_send_json('Error retrieving user information', 500);
    }
}

    



// =================login section start==========================

function loginShortCode(){
    ?>
        <section class="container">
            <header>Login Form</header>
            <form action="#" method="post" class="form" id="login_form">
                <div class="input-box">
                    <label>Email Address</label>
                    <input type="text" name="email" placeholder="Enter email address" required  />
                </div>
                <div class="input-box">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your Password" required  />
                </div>
                <button type="submit">Submit</button>
            </form>
        </section>
    <?php
    }
    
    function register_rest_api_login() {
        register_rest_route('customapi/v1', '/login_user', array(
            'methods' => 'POST',
            'callback' => array($this, 'login_user_endpoint'),
        ));
    }
    
    function login_user_endpoint($request) {
        
        $parameters = $request->get_params();

    
        // Attempt authentication
        $user = wp_authenticate($parameters['email'], $parameters['password']);
    
        // Check if authentication failed
        if (is_wp_error($user)) {
            return new WP_REST_Response('invalid User Id And Password');

        } else {
            // Authentication successful
            wp_set_current_user($user->ID);
            return new WP_REST_Response('User logged in successfully', 200);
        }
    }
    

    function load_assets(){
      //wp_enqueue_style('customcss',plugin_dir_url( __FILE__ ).'/css/custom.css',array(),1,'all');
        wp_enqueue_script('jquery.js','https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js',array('jquery'),time(),true);
        // wp_register_script('custom.jsnew',plugin_dir_url( __FILE__ ).'js/custom.js',array(),1,1,1);
        wp_enqueue_script('custom.jsnew');
      }
    
      function wp_script() {
        ?>
        <script>
            jQuery(document).ready(function ($) {
                var nonce = '<?php echo wp_create_nonce('wp_rest');?>';
                $('#register_form').submit(function (event) {
                    event.preventDefault();
                    var form = $(this).serialize();
                    console.log(form);
                    console.log(nonce);
                    $.ajax({
                        method: 'POST',
                        url: '<?php echo esc_url_raw(get_rest_url(null, 'customapi/v1/create_user')); ?>',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', nonce);
                        },
                        data: form,
                        success: function (response) {
                               
                        },
                        error: function (error) {
                            console.log(error.responseText);
                        }
                    });
                });
            });
    
            //login from part
    
            jQuery(document).ready(function ($) {
        // var nonce = '<?php echo wp_create_nonce('wp_rest');?>';
        $('#login_form').submit(function (event) {
            event.preventDefault();
            var form = $(this).serialize();
            console.log(form);
            // console.log(nonce);
            $.ajax({
                method: 'POST',
                url: '<?php echo esc_url_raw(get_rest_url(null, 'customapi/v1/login_user')); ?>',
                // beforeSend: function (xhr) {
                //     xhr.setRequestHeader('X-WP-Nonce', nonce);
                // },
                data: form,
                success: function (response) {
                    console.log(response);
                    // Display success message
                    // $('#login-message').text('Login successful').css('color', 'green');
                 
                },
                error: function (error) {
                    // Display error message
    //                 $('#login-message').text('Login failed. ' + error.response
    // Text).css('color', 'red');
                }
            });
        });
    });
    
        </script>
        <?php
    }
    


  }

  new CustomRegister;
?>

