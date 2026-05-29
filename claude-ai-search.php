<?php
/**
 * Plugin Name: Claude AI Search
 * Description: AI-powered search for your WordPress posts and pages using Claude API. Add the block anywhere via Gutenberg.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CLAUDE_AI_SEARCH_VERSION', '1.0.0' );
define( 'CLAUDE_AI_SEARCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLAUDE_AI_SEARCH_URL', plugin_dir_url( __FILE__ ) );

// Load sub-modules
require_once CLAUDE_AI_SEARCH_PATH . 'includes/settings.php';
require_once CLAUDE_AI_SEARCH_PATH . 'includes/rest-api.php';
require_once CLAUDE_AI_SEARCH_PATH . 'includes/block.php';
