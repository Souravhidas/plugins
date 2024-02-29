<?php
/*
Plugin Name: Post Importer
Description: Allows users to import posts from a CSV file within the WordPress admin panel.
Version: 1.0
Author: sourav
*/
if(!defined('ABSPATH')){
    echo"This is My  Plugin ";
    exit;
}
// Register Custom Post Type
function register_custom_post_type() {
    register_post_type('imported_post', array(
        'labels' => array(
            'name' => __('Imported Posts'),
            'singular_name' => __('Imported Post')
        ),
        'public' => true,
        // Add more arguments as needed
    ));
}
add_action('init', 'register_custom_post_type');

// Add Admin Menu Item
function add_importer_menu_item() {
    add_submenu_page(
        'tools.php', 
        'Post Importer', 
        'Post Importer', 
        'manage_options', 
        'imported_post', 
        'display_importer_page' 
    );
}
add_action('admin_menu', 'add_importer_menu_item', 99);

// Display Importer Page
function display_importer_page() {
    ?>
    <div class="wrap">
        <h1>Post Importer</h1>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="csv_file" />
            <input type="submit" name="submit" class="button button-primary" value="Upload" />
        </form>
        <?php handle_form_submission(); ?>
    </div>
    <?php
}

// Handle Form Submission
function handle_form_submission() {
    if (isset($_POST['submit'])) {
        if (isset($_FILES['csv_file']) && ($_FILES['csv_file']['error'] == UPLOAD_ERR_OK)) {
            $csv_file = $_FILES['csv_file']['tmp_name'];
            $csv_file_name=$_FILES['csv_file']['name'];
            register_custom_post_type($csv_file_name);
            $csv_data = array_map('str_getcsv', file($csv_file));
            
            // Count total CSV rows
            $total_count = count($csv_data);
            
            // Get remaining count
            $remaining_count =$total_count;
            
            foreach ($csv_data as $row) {
                print_r($row);
                // Process each row and create a new post
                $post_data = array(
                    'post_title' => $row[0], // Assuming first column is post title
                    'post_content' => $row[1], // Assuming second column is post content
                    'post_type' => 'imported_post',
             
                );
                wp_insert_post($post_data);
                $remaining_count--; // Decrement the remaining count
            }

         
            
            // Display total and remaining count
            echo '<div class="notice notice-success"><p>Posts imported successfully.</p></div>';
            echo '<div class="notice notice-info"><p>Total CSV data: ' . $total_count . '</p></div>';
            echo '<div class="notice notice-info"><p>Remaining data: ' . $remaining_count . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Error uploading file.</p></div>';
        }
    }
}
?>
