<?php
/**
 * Admin settings page.
 *
 * @package StarterPlugin
 */

declare( strict_types=1 );

namespace StarterPlugin\Admin;

use StarterPlugin\Helpers\Assets;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the plugin settings page under Settings → Starter Plugin.
 */
class SettingsPage {

	/**
	 * Option group name used by the Settings API.
	 *
	 * @var string
	 */
	private const OPTION_GROUP = 'starter_plugin_settings';

	/**
	 * Option name stored in wp_options.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'starter_plugin_options';

	/**
	 * Settings page hook suffix, set after add_options_page().
	 *
	 * @var string
	 */
	private string $hook_suffix = '';

	/**
	 * Register hooks with WordPress.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_filter(
			'plugin_action_links_' . plugin_basename( STARTER_PLUGIN_FILE ),
			[ $this, 'add_settings_link' ]
		);
	}

	/**
	 * Add the settings page to the Settings menu.
	 *
	 * @return void
	 */
	public function add_menu_page(): void {
		$this->hook_suffix = (string) add_options_page(
			__( 'Starter Plugin Settings', 'starter-plugin' ),
			__( 'Starter Plugin', 'starter-plugin' ),
			'manage_options',
			'starter-plugin',
			[ $this, 'render_page' ]
		);

		if ( '' !== $this->hook_suffix ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		}
	}

	/**
	 * Enqueue admin assets only on this settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( $hook_suffix !== $this->hook_suffix ) {
			return;
		}

		Assets::enqueue_script( 'admin' );
		Assets::enqueue_style( 'admin' );
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [ $this, 'sanitize_options' ],
				'default'           => self::defaults(),
			]
		);

		add_settings_section(
			'starter_plugin_general',
			__( 'General Settings', 'starter-plugin' ),
			[ $this, 'render_section_description' ],
			'starter-plugin'
		);

		add_settings_field(
			'starter_plugin_example_text',
			__( 'Example Text', 'starter-plugin' ),
			[ $this, 'render_text_field' ],
			'starter-plugin',
			'starter_plugin_general',
			[ 'label_for' => 'starter_plugin_example_text' ]
		);

		add_settings_field(
			'starter_plugin_example_checkbox',
			__( 'Example Checkbox', 'starter-plugin' ),
			[ $this, 'render_checkbox_field' ],
			'starter-plugin',
			'starter_plugin_general',
			[ 'label_for' => 'starter_plugin_example_checkbox' ]
		);
	}

	/**
	 * Default option values.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return [
			'example_text'     => '',
			'example_checkbox' => 0,
		];
	}

	/**
	 * Sanitize the submitted options.
	 *
	 * @param mixed $input Raw form input.
	 *
	 * @return array<string, mixed>
	 */
	public function sanitize_options( mixed $input ): array {
		$input    = is_array( $input ) ? $input : [];
		$defaults = self::defaults();

		return [
			'example_text'     => sanitize_text_field(
				wp_unslash( $input['example_text'] ?? $defaults['example_text'] )
			),
			'example_checkbox' => absint( $input['example_checkbox'] ?? $defaults['example_checkbox'] ),
		];
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap starter-plugin-settings">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( 'starter-plugin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the section description.
	 *
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . esc_html__( 'Configure the plugin settings below.', 'starter-plugin' ) . '</p>';
	}

	/**
	 * Render the example text field.
	 *
	 * @param array<string, string> $args Field arguments from add_settings_field().
	 *
	 * @return void
	 */
	public function render_text_field( array $args ): void {
		$options = get_option( self::OPTION_NAME, self::defaults() );
		$value   = $options['example_text'] ?? '';
		?>
		<input
			type="text"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[example_text]' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
		/>
		<p class="description">
			<?php esc_html_e( 'An example text field demonstrating sanitize_text_field() and esc_attr().', 'starter-plugin' ); ?>
		</p>
		<?php
	}

	/**
	 * Render the example checkbox field.
	 *
	 * @param array<string, string> $args Field arguments from add_settings_field().
	 *
	 * @return void
	 */
	public function render_checkbox_field( array $args ): void {
		$options = get_option( self::OPTION_NAME, self::defaults() );
		$value   = $options['example_checkbox'] ?? 0;
		?>
		<input
			type="checkbox"
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			name="<?php echo esc_attr( self::OPTION_NAME . '[example_checkbox]' ); ?>"
			value="1"
			<?php checked( 1, $value ); ?>
		/>
		<label for="<?php echo esc_attr( $args['label_for'] ); ?>">
			<?php esc_html_e( 'Enable this example feature.', 'starter-plugin' ); ?>
		</label>
		<?php
	}

	/**
	 * Add a "Settings" link to the Plugins page.
	 *
	 * @param array<int, string> $links Existing plugin action links.
	 *
	 * @return array<int, string>
	 */
	public function add_settings_link( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=starter-plugin' ) ),
			esc_html__( 'Settings', 'starter-plugin' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}
}
