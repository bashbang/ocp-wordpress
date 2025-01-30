<?php
/*
Plugin Name: OCP-Wordpress MU Loader
Description: A custom WP-MU plugin loader for the BC Gov't OCP-WP. This currently activates the W3-Total-Cache and Rapunzel plugins.
Author: Chris Mullin
Version: 1.0
*/

// Filter S3 Uploads params.
// See: https://github.com/humanmade/S3-Uploads?tab=readme-ov-file#custom-endpoints
add_filter( 's3_uploads_s3_client_params', function ( $params ) {
  $params['endpoint'] = getenv('OBJECT_STORE_URL');
  $params['use_path_style_endpoint'] = true;
  $params['debug'] = false;
  return $params;
} );

// Load the WordPress plugin API functions if not already loaded.
if (defined('ABSPATH') && !function_exists('is_plugin_active')) {
  require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Programmatically activate plugins if not already active.
function ensure_plugins_active() {
  $plugins_to_activate = [
    'rapunzel/rapunzel.php',
    's3-uploads/s3-uploads.php'
  ];

  foreach ( $plugins_to_activate as $plugin ) {
    if ( ! is_plugin_active( $plugin ) ) {
      activate_plugin( $plugin );
    }
  }
}

ensure_plugins_active();