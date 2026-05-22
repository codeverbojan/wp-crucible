=== Starter Plugin ===
Contributors:      yourname
Tags:              starter, boilerplate, scaffold, developer, toolkit
Requires at least: 6.4
Tested up to:      6.8.1
Requires PHP:      8.1
Stable tag:        1.0.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

A starter plugin scaffolded by WP Crucible. Replace this with your plugin description.

== Description ==

Starter Plugin provides a modern, well-structured foundation for building WordPress plugins that follow current coding standards and pass WordPress.org Plugin Check validation.

**Features:**

* PSR-4 autoloading with WordPress coding standards
* Example settings page using the WordPress Settings API
* Webpack build pipeline via `@wordpress/scripts`
* PHPUnit tests with Brain\Monkey for WordPress function mocking
* PHPStan static analysis at level 6
* PHPCS with WordPress coding standards

This plugin is a boilerplate — clone it, run the rename script, and start building.

== Installation ==

1. Upload the `starter-plugin` directory to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Settings → Starter Plugin** to configure.

== Frequently Asked Questions ==

= How do I rename this plugin for my own project? =

Run `bin/rename.sh` and follow the prompts. It updates all file names, namespaces, prefixes, and text domains.

= What PHP version is required? =

PHP 8.1 or later. This aligns with PHPUnit 10 requirements and modern WordPress recommendations.

= Does this plugin send data to external services? =

No. This boilerplate does not contact any third-party services by default.

== Screenshots ==

1. Example settings page under Settings → Starter Plugin.

<!-- Uncomment and complete this section if your plugin contacts external servers.
     Required by wp.org Guideline 7: Proper Use of Third-Party Services.

== Third-Party Services ==

This plugin connects to [Service Name](https://example.com) for [purpose].

- Service Terms of Use: https://example.com/terms
- Service Privacy Policy: https://example.com/privacy

No data is sent to any third party by default.
-->

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
