# Claude AI Search — WordPress Plugin

A Gutenberg block that lets visitors ask questions and get answers powered by Claude AI — grounded **only** in your site's own posts and pages.

---

## Installation (Local by Flywheel)

1. **Copy the plugin folder** into your Local site's plugins directory:
   ```
   ~/Local Sites/<your-site>/app/public/wp-content/plugins/claude-ai-search/
   ```

2. **Activate the plugin** in WordPress Admin → Plugins → "Claude AI Search" → Activate.

3. **Add your API key** in WordPress Admin → Settings → Claude AI Search.
   - Get a key at https://console.anthropic.com/
   - The key is stored in your WordPress database (wp_options), never exposed to the browser.

4. **Add the block** to any page or post:
   - Open the Gutenberg editor
   - Click the ➕ block inserter
   - Search for "Claude AI Search"
   - Drop it onto your page and publish

---

## Settings

Found at **Admin → Settings → Claude AI Search**:

| Setting | Description |
|---|---|
| **API Key** | Your Anthropic Claude API key |
| **Content types** | Which post types Claude searches (posts, pages, custom types) |
| **Max posts** | How many posts to pass as context (default: 8) |
| **System prompt** | Customise Claude's persona and instructions |

---

## How it works

1. Visitor types a question and hits Search
2. WordPress searches your published content (keyword match)
3. Top matching posts/pages are passed as context to Claude
4. Claude answers **only** from that context
5. Answer + source links are shown to the visitor

---

## Customising the prompt

The default system prompt is:

> You are a helpful assistant for {site_name}. Answer the user's question using ONLY the articles provided below. If the answer is not covered in the articles, say: "I don't have information about that on this site." Keep answers concise and helpful. Always mention which article(s) your answer is based on, with a link if available.

You can change this in Settings. Use `{site_name}` as a placeholder.

---

## File structure

```
claude-ai-search/
├── claude-ai-search.php      ← Main plugin file
├── includes/
│   ├── settings.php          ← Admin settings page + API key storage
│   ├── rest-api.php          ← REST endpoint: searches posts, calls Claude
│   └── block.php             ← Registers Gutenberg block + render callback
├── block/
│   ├── block.js              ← Editor-side block JS (Inspector Controls)
│   ├── frontend.js           ← Frontend search interaction
│   └── style.css             ← Styles for editor + frontend
└── README.md
```

---

## Requirements

- WordPress 6.0+
- PHP 7.4+
- An Anthropic API key (https://console.anthropic.com/)
