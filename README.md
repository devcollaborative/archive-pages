Archive Pages
===

Add settings page to choose a page to render at the top of an archive page.

Allows you to use the archive page template for a post type, but render block editor content and set the page title.

## Installation

Clone or download this repository and add it to the `wp-content/plugins` directory.

## How to Use
Go to **Settings > Archive Pages** and choose a page to set as the archive page for your custom post type.

Once an archive page is selected, `get_the_archive_title()` will return the title from the selected page, and `get_the_archive_description()` will return the post content from the selected page.

If you want to directly access the selected archive page for a post type you can do so like this:

```
// Replace "my-cpt" with your custom post type slug
get_option('archive_page_my-cpt');
```
