<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the /wp-json/claude-ai-search/v1/ask endpoint
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'claude-ai-search/v1', '/ask', [
        'methods'             => 'POST',
        'callback'            => 'claude_ai_search_handle_request',
        'permission_callback' => '__return_true',
        'args'                => [
            'query' => [
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function ( $v ) {
                    return is_string( $v ) && strlen( trim( $v ) ) >= 2;
                },
            ],
        ],
    ] );
} );

/**
 * Main handler: search posts → build prompt → call Claude → return answer
 */
function claude_ai_search_handle_request( WP_REST_Request $request ) {
    $query = trim( $request->get_param( 'query' ) );

    // --- 1. Check API key ---
    $api_key = get_option( 'claude_ai_search_api_key', '' );
    if ( empty( $api_key ) ) {
        return new WP_Error( 'no_api_key', 'Claude API key is not configured.', [ 'status' => 500 ] );
    }

    // --- 2. Search WordPress content ---
    $post_types  = get_option( 'claude_ai_search_post_types', [ 'post', 'page' ] );
    $max_results = (int) get_option( 'claude_ai_search_max_results', 8 );

    $posts = get_posts( [
        's'              => $query,
        'post_type'      => (array) $post_types,
        'post_status'    => 'publish',
        'posts_per_page' => $max_results,
    ] );

    // If keyword search returns nothing, fall back to recent content
    if ( empty( $posts ) ) {
        $posts = get_posts( [
            'post_type'      => (array) $post_types,
            'post_status'    => 'publish',
            'posts_per_page' => min( $max_results, 5 ),
        ] );
    }

    // --- 3. Build context from posts ---
    $sources = [];
    $context = '';

    foreach ( $posts as $post ) {
        $title   = get_the_title( $post );
        $url     = get_permalink( $post );
        $content = wp_strip_all_tags( $post->post_content );
        // Trim to ~800 chars per post to stay within token limits
        $content = mb_substr( $content, 0, 800 );
        if ( strlen( $content ) === 800 ) $content .= '...';

        $context .= "---\nTitle: {$title}\nURL: {$url}\nContent: {$content}\n\n";
        $sources[] = [ 'title' => $title, 'url' => $url ];
    }

    if ( empty( $context ) ) {
        return rest_ensure_response( [
            'answer'  => "I couldn't find any published content on this site to answer your question.",
            'sources' => [],
        ] );
    }

    // --- 4. Build prompt ---
    $raw_system = get_option( 'claude_ai_search_system_prompt', claude_ai_search_default_prompt() );
    $site_name  = get_bloginfo( 'name' );
    $system     = str_replace( '{site_name}', $site_name, $raw_system );

    $user_message = "Here are articles from the site:\n\n{$context}\n\nUser question: {$query}";

    // --- 5. Call Claude API ---
    $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
        'timeout' => 30,
        'headers' => [
            'x-api-key'         => $api_key,
            'anthropic-version' => '2023-06-01',
            'Content-Type'      => 'application/json',
        ],
        'body' => wp_json_encode( [
            'model'      => 'claude-sonnet-4-20250514',
            'max_tokens' => 1000,
            'system'     => $system,
            'messages'   => [
                [ 'role' => 'user', 'content' => $user_message ],
            ],
        ] ),
    ] );

    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'api_error', 'Failed to reach Claude API: ' . $response->get_error_message(), [ 'status' => 502 ] );
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $body['content'][0]['text'] ) ) {
        return new WP_Error( 'api_bad_response', 'Unexpected response from Claude API.', [ 'status' => 502 ] );
    }

    return rest_ensure_response( [
        'answer'  => $body['content'][0]['text'],
        'sources' => $sources,
    ] );
}
