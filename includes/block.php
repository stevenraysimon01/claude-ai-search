<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register the Gutenberg block and enqueue its assets
 */
add_action( 'init', function () {
    // Editor script (block JS)
    wp_register_script(
        'claude-ai-search-block',
        CLAUDE_AI_SEARCH_URL . 'block/block.js',
        [ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ],
        CLAUDE_AI_SEARCH_VERSION,
        true
    );

    // Frontend + editor style
    wp_register_style(
        'claude-ai-search-style',
        CLAUDE_AI_SEARCH_URL . 'block/style.css',
        [],
        CLAUDE_AI_SEARCH_VERSION
    );

    // Frontend script (handles the search UI on published pages)
    wp_register_script(
        'claude-ai-search-frontend',
        CLAUDE_AI_SEARCH_URL . 'block/frontend.js',
        [],
        CLAUDE_AI_SEARCH_VERSION,
        true
    );

    // Pass REST URL to frontend JS
    wp_localize_script( 'claude-ai-search-frontend', 'ClaudeAISearch', [
        'restUrl' => esc_url_raw( rest_url( 'claude-ai-search/v1/ask' ) ),
        'nonce'   => wp_create_nonce( 'wp_rest' ),
    ] );

    register_block_type( 'claude-ai-search/search-block', [
        'editor_script'   => 'claude-ai-search-block',
        'style'           => 'claude-ai-search-style',
        'script'          => 'claude-ai-search-frontend',
        'render_callback' => 'claude_ai_search_render_block',
        'attributes'      => [
            'placeholder' => [
                'type'    => 'string',
                'default' => 'Ask a question about our content...',
            ],
            'buttonText' => [
                'type'    => 'string',
                'default' => 'Search',
            ],
            'showSources' => [
                'type'    => 'boolean',
                'default' => true,
            ],
        ],
    ] );
} );

/**
 * Server-side render callback — outputs the HTML shell.
 * The frontend.js script hydrates it with interactive behaviour.
 */
function claude_ai_search_render_block( $attributes ) {
    $placeholder   = esc_attr( $attributes['placeholder'] ?? 'Ask a question about our content...' );
    $button_text   = esc_html( $attributes['buttonText'] ?? 'Search' );
    $show_sources  = ! empty( $attributes['showSources'] );
    $sources_attr  = $show_sources ? 'true' : 'false';

    ob_start();
    ?>
    <div
        class="claude-ai-search-widget"
        data-show-sources="<?php echo $sources_attr; ?>"
    >
        <div class="cas-input-row">
            <input
                type="text"
                class="cas-input"
                placeholder="<?php echo $placeholder; ?>"
                aria-label="Search"
            />
            <button class="cas-button" type="button"><?php echo $button_text; ?></button>
        </div>
        <div class="cas-answer" aria-live="polite" hidden></div>
        <div class="cas-sources" hidden></div>
    </div>
    <?php
    return ob_get_clean();
}
