<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the Settings admin page under Settings > Claude AI Search
 */
add_action( 'admin_menu', function () {
    add_options_page(
        'Claude AI Search Settings',
        'Claude AI Search',
        'manage_options',
        'claude-ai-search',
        'claude_ai_search_settings_page'
    );
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
    register_setting( 'claude_ai_search_settings', 'claude_ai_search_api_key', [
        'sanitize_callback' => 'sanitize_text_field',
    ] );
    register_setting( 'claude_ai_search_settings', 'claude_ai_search_post_types', [
        'sanitize_callback' => 'claude_ai_search_sanitize_post_types',
    ] );
    register_setting( 'claude_ai_search_settings', 'claude_ai_search_max_results', [
        'sanitize_callback' => 'absint',
        'default'           => 8,
    ] );
    register_setting( 'claude_ai_search_settings', 'claude_ai_search_system_prompt', [
        'sanitize_callback' => 'sanitize_textarea_field',
    ] );
} );

function claude_ai_search_sanitize_post_types( $value ) {
    if ( ! is_array( $value ) ) return [ 'post', 'page' ];
    return array_map( 'sanitize_key', $value );
}

/**
 * Render the settings page
 */
function claude_ai_search_settings_page() {
    $api_key       = get_option( 'claude_ai_search_api_key', '' );
    $post_types    = get_option( 'claude_ai_search_post_types', [ 'post', 'page' ] );
    $max_results   = get_option( 'claude_ai_search_max_results', 8 );
    $system_prompt = get_option( 'claude_ai_search_system_prompt', claude_ai_search_default_prompt() );
    $all_types     = get_post_types( [ 'public' => true ], 'objects' );
    ?>
    <div class="wrap">
        <h1>🔍 Claude AI Search Settings</h1>
        <p style="color:#666;">Configure your Claude API key and search behaviour. Then add the <strong>Claude AI Search</strong> block to any page or post.</p>

        <?php if ( isset( $_GET['settings-updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><strong>Settings saved!</strong></p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'claude_ai_search_settings' ); ?>

            <table class="form-table" role="presentation">

                <!-- API Key -->
                <tr>
                    <th scope="row"><label for="claude_api_key">Claude API Key</label></th>
                    <td>
                        <input
                            type="password"
                            id="claude_api_key"
                            name="claude_ai_search_api_key"
                            value="<?php echo esc_attr( $api_key ); ?>"
                            class="regular-text"
                            placeholder="sk-ant-..."
                            autocomplete="off"
                        />
                        <p class="description">
                            Get your key at <a href="https://console.anthropic.com/" target="_blank">console.anthropic.com</a>.
                            Stored securely in your WordPress database.
                        </p>
                        <?php if ( $api_key ) : ?>
                            <p class="description" style="color:green;">✅ API key is set.</p>
                        <?php else : ?>
                            <p class="description" style="color:red;">⚠️ No API key set — the search block will not work.</p>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Post Types -->
                <tr>
                    <th scope="row">Search these content types</th>
                    <td>
                        <?php foreach ( $all_types as $type ) : ?>
                            <label style="display:block;margin-bottom:4px;">
                                <input
                                    type="checkbox"
                                    name="claude_ai_search_post_types[]"
                                    value="<?php echo esc_attr( $type->name ); ?>"
                                    <?php checked( in_array( $type->name, (array) $post_types ) ); ?>
                                />
                                <?php echo esc_html( $type->label ); ?>
                                <span style="color:#999;font-size:12px;">(<?php echo esc_html( $type->name ); ?>)</span>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">Claude will only answer from these content types.</p>
                    </td>
                </tr>

                <!-- Max Results -->
                <tr>
                    <th scope="row"><label for="claude_max_results">Max posts to retrieve</label></th>
                    <td>
                        <input
                            type="number"
                            id="claude_max_results"
                            name="claude_ai_search_max_results"
                            value="<?php echo esc_attr( $max_results ); ?>"
                            min="1"
                            max="20"
                            class="small-text"
                        />
                        <p class="description">How many posts/pages to pass as context to Claude (1–20). More = better answers, slightly higher API cost.</p>
                    </td>
                </tr>

                <!-- System Prompt -->
                <tr>
                    <th scope="row"><label for="claude_system_prompt">System Prompt</label></th>
                    <td>
                        <textarea
                            id="claude_system_prompt"
                            name="claude_ai_search_system_prompt"
                            rows="7"
                            class="large-text"
                        ><?php echo esc_textarea( $system_prompt ); ?></textarea>
                        <p class="description">
                            Customise how Claude responds. Use <code>{site_name}</code> as a placeholder for your site name.
                            The retrieved articles are appended automatically after this prompt.
                        </p>
                    </td>
                </tr>

            </table>

            <?php submit_button( 'Save Settings' ); ?>
        </form>

        <hr>
        <h2>How to use</h2>
        <ol>
            <li>Enter your Claude API key above and save.</li>
            <li>Edit any page or post in the Gutenberg editor.</li>
            <li>Add the <strong>"Claude AI Search"</strong> block (search for it in the block inserter).</li>
            <li>Publish — your visitors can now ask questions and Claude will answer from your content.</li>
        </ol>
    </div>
    <?php
}

function claude_ai_search_default_prompt() {
    return 'You are a helpful assistant for {site_name}. Answer the user\'s question using ONLY the articles provided below. ' .
           'If the answer is not covered in the articles, say: "I don\'t have information about that on this site." ' .
           'Keep answers concise and helpful. Always mention which article(s) your answer is based on, with a link if available.';
}
