<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Wpcpo_Backend' ) ) {
	class Wpcpo_Backend {
		private $field = [];
		private $field_id = '';
		protected static $instance = null;
		protected static $settings = [];

		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function __construct() {
			self::$settings = (array) get_option( 'wpcpo_settings', [] );

			add_action( 'init', [ $this, 'init' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
			add_action( 'admin_init', [ $this, 'register_settings' ] );
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );
			add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );
			add_filter( 'plugin_row_meta', [ $this, 'row_meta' ], 10, 2 );

			add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
			add_action( 'save_post_wpc_product_option', [ $this, 'save_product_options' ] );
			add_filter( 'manage_edit-wpc_product_option_columns', [ $this, 'custom_column' ] );
			add_action( 'manage_wpc_product_option_posts_custom_column', [ $this, 'custom_column_value' ], 10, 2 );

			// AJAX
			add_action( 'wp_ajax_wpcpo_search_term', [ $this, 'ajax_search_term' ] );
			add_action( 'wp_ajax_wpcpo_add_field', [ $this, 'ajax_add_field' ] );
			add_action( 'wp_ajax_wpcpo_add_option', [ $this, 'ajax_add_option' ] );

			// Single product
			add_filter( 'woocommerce_product_data_tabs', [ $this, 'product_data_tabs' ] );
			add_action( 'woocommerce_product_data_panels', [ $this, 'product_data_panels' ] );
			add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_options' ] );

			// WPC Smart Messages
			add_filter( 'wpcsm_locations', [ $this, 'wpcsm_locations' ] );
		}

		public static function get_settings() {
			return apply_filters( 'wpcpo_get_settings', self::$settings );
		}

		public static function get_setting( $name, $default = false ) {
			if ( ! empty( self::$settings ) && isset( self::$settings[ $name ] ) ) {
				$setting = self::$settings[ $name ];
			} else {
				$setting = get_option( 'wpcpo_' . $name, $default );
			}

			return apply_filters( 'wpcpo_get_setting', $setting, $name, $default );
		}

		public function init() {
			$this->register_postype();
		}

		public function custom_column( $columns ) {
			return [
				'cb'      => $columns['cb'],
				'title'   => esc_html__( 'Title', 'wpc-product-options' ),
				'desc'    => esc_html__( 'Description', 'wpc-product-options' ),
				'summary' => esc_html__( 'Summary', 'wpc-product-options' ),
				'date'    => esc_html__( 'Date', 'wpc-product-options' )
			];
		}

		public function custom_column_value( $column, $post_id ) {
			if ( $column == 'desc' ) {
				echo get_the_excerpt( $post_id );
			}

			if ( $column == 'summary' ) {
				$fields    = get_post_meta( $post_id, 'wpcpo-fields', true );
				$apply_for = get_post_meta( $post_id, 'wpcpo-apply-for', true );
				$apply     = (array) get_post_meta( $post_id, 'wpcpo-apply', true );

				echo '<div>' . esc_html__( 'Fields', 'wpc-product-options' ) . ': ' . ( ! empty( $fields ) && is_array( $fields ) ? count( $fields ) : '0' ) . '</div>';
				echo '<div>' . esc_html__( 'Apply', 'wpc-product-options' ) . ': ' . ( in_array( $apply_for, [
						'all',
						'none'
					] ) ? $apply_for : $apply_for . ' - ' . ( ! empty( $apply ) ? implode( ', ', $apply ) : '' ) ) . '</div>';
			}
		}

		public function product_data_tabs( $tabs ) {
			$tabs['wpcpo'] = [
				'label'  => esc_html__( 'Product Options', 'wpc-product-options' ),
				'target' => 'wpcpo_settings'
			];

			return $tabs;
		}

		public function product_data_panels() {
			include_once WPCPO_DIR . 'includes/templates/panel.php';
		}

		public function add_meta_box() {
			add_meta_box( 'wpcpo_configuration', esc_html__( 'Configuration', 'wpc-product-options' ), [
				$this,
				'configuration_meta'
			], 'wpc_product_option', 'advanced', 'low' );

			add_meta_box( 'wpcpo_fields', esc_html__( 'Fields', 'wpc-product-options' ), [
				$this,
				'fields_meta'
			], 'wpc_product_option', 'advanced', 'low' );
		}

		public function save_product_options( $post_id ) {
			if ( isset( $_POST['wpcpo-fields'] ) ) {
				update_post_meta( $post_id, 'wpcpo-fields', $this->sanitize_array( $this->validate( $_POST['wpcpo-fields'] ) ) );
			}

			if ( isset( $_POST['wpcpo-apply-for'] ) ) {
				update_post_meta( $post_id, 'wpcpo-apply-for', sanitize_text_field( $_POST['wpcpo-apply-for'] ) );
			}

			if ( isset( $_POST['wpcpo-apply'] ) ) {
				update_post_meta( $post_id, 'wpcpo-apply', $this->sanitize_array( $_POST['wpcpo-apply'] ) );
			}

			if ( isset( $_POST['wpcpo-display'] ) ) {
				update_post_meta( $post_id, 'wpcpo-display', sanitize_text_field( $_POST['wpcpo-display'] ) );
			}
		}

		private function sanitize_array( $arr ) {
			foreach ( (array) $arr as $k => $v ) {
				if ( is_array( $v ) ) {
					$arr[ $k ] = self::sanitize_array( $v );
				} else {
					$arr[ $k ] = sanitize_post_field( 'post_content', $v, 0, 'display' );
				}
			}

			return $arr;
		}

		private function validate( $fields ) {
			foreach ( $fields as $key => $field ) {
				if ( isset( $field['custom_price'] ) ) {
					$field['custom_price'] = preg_replace( '/[^0-9\+\-\*\/\(\)\.pqlvws]/', '', $field['custom_price'] );
				} elseif ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
					foreach ( $field['options'] as $k => $option ) {
						if ( isset( $option['custom_price'] ) ) {
							$option['custom_price'] = preg_replace( '/[^0-9\+\-\*\/\(\)\.pqlvws]/', '', $option['custom_price'] );
							$field['options'][ $k ] = $option;
						}
					}
				}

				$fields[ $key ] = $field;
			}

			return $fields;
		}

		public function configuration_meta() {
			include_once WPCPO_DIR . 'includes/templates/configuration.php';
		}

		public function fields_meta() {
			$fields = get_post_meta( get_the_ID(), 'wpcpo-fields', true );
			$fields = ( is_array( $fields ) ) ? $fields : [];
			include_once WPCPO_DIR . 'includes/templates/fields.php';
		}

		public function enqueue_scripts() {
			if ( ( 'wpc_product_option' !== get_current_screen()->id ) && ( 'product' !== get_current_screen()->id ) ) {
				return;
			}

			wp_enqueue_media();
			wp_enqueue_style( 'hint', WPCPO_URI . 'assets/css/hint.css' );

			// wpcdpk
			wp_enqueue_style( 'wpcdpk', WPCPO_URI . 'assets/libs/wpcdpk/css/datepicker.css' );
			wp_enqueue_script( 'wpcdpk', WPCPO_URI . 'assets/libs/wpcdpk/js/datepicker.js', [ 'jquery' ], WPCPO_VERSION, true );

			wp_enqueue_style( 'wpcpo-backend', WPCPO_URI . 'assets/css/backend.css', [ 'woocommerce_admin_styles' ], WPCPO_VERSION );
			wp_enqueue_script( 'wpcpo-backend', WPCPO_URI . 'assets/js/backend.js', [
				'jquery',
				'wc-enhanced-select',
				'jquery-ui-sortable',
				'selectWoo'
			], WPCPO_VERSION, true );
		}

		function register_settings() {
			// settings
			register_setting( 'wpcpo_settings', 'wpcpo_settings' );
		}

		function admin_menu() {
			add_submenu_page( 'wpclever', esc_html__( 'WPC Product Options', 'wpc-product-options' ), esc_html__( 'Product Options', 'wpc-product-options' ), 'manage_options', 'wpclever-wpcpo', [
				$this,
				'admin_menu_content'
			] );
		}

		function admin_menu_content() {
			$active_tab = sanitize_key( $_GET['tab'] ?? 'settings' );
			?>
            <div class="wpclever_settings_page wrap">
                <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Product Options', 'wpc-product-options' ) . ' ' . esc_html( WPCPO_VERSION ) . ' ' . ( defined( 'WPCPO_PREMIUM' ) ? '<span class="premium" style="display: none">' . esc_html__( 'Premium', 'wpc-product-options' ) . '</span>' : '' ); ?></h1>
                <div class="wpclever_settings_page_desc about-text">
                    <p>
						<?php printf( /* translators: stars */ esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wpc-product-options' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                        <br/>
                        <a href="<?php echo esc_url( WPCPO_REVIEWS ); ?>" target="_blank"><?php esc_html_e( 'Reviews', 'wpc-product-options' ); ?></a> |
                        <a href="<?php echo esc_url( WPCPO_CHANGELOG ); ?>" target="_blank"><?php esc_html_e( 'Changelog', 'wpc-product-options' ); ?></a> |
                        <a href="<?php echo esc_url( WPCPO_DISCUSSION ); ?>" target="_blank"><?php esc_html_e( 'Discussion', 'wpc-product-options' ); ?></a>
                    </p>
                </div>
				<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php esc_html_e( 'Settings updated.', 'wpc-product-options' ); ?></p>
                    </div>
				<?php } ?>
                <div class="wpclever_settings_page_nav">
                    <h2 class="nav-tab-wrapper">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-wpcpo&tab=settings' ) ); ?>" class="<?php echo esc_attr( $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>">
							<?php esc_html_e( 'Settings', 'wpc-product-options' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpc_product_option' ) ); ?>" class="nav-tab">
							<?php esc_html_e( 'Product Options', 'wpc-product-options' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-wpcpo&tab=premium' ) ); ?>" class="<?php echo esc_attr( $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab' ); ?>" style="color: #c9356e">
							<?php esc_html_e( 'Premium Version', 'wpc-product-options' ); ?>
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-kit' ) ); ?>" class="nav-tab">
							<?php esc_html_e( 'Essential Kit', 'wpc-product-options' ); ?>
                        </a>
                    </h2>
                </div>
                <div class="wpclever_settings_page_content">
					<?php if ( $active_tab === 'settings' ) {
						$position = self::get_setting( 'position', 'above_atc' );
						?>
                        <form method="post" action="options.php">
                            <table class="form-table wpcpo-table">
                                <tr class="heading">
                                    <th colspan="2">
										<?php esc_html_e( 'General', 'wpc-product-options' ); ?>
                                    </th>
                                </tr>
                                <tr>
                                    <th><?php esc_html_e( 'Position', 'wpc-product-options' ); ?></th>
                                    <td>
                                        <select name="wpcpo_settings[position]">
                                            <option value="above_atc" <?php selected( $position, 'above_atc' ); ?>><?php esc_html_e( 'Above the add to cart button', 'wpc-product-options' ); ?></option>
                                            <option value="below_atc" <?php selected( $position, 'below_atc' ); ?>><?php esc_html_e( 'Under the add to cart button', 'wpc-product-options' ); ?></option>
                                            <option value="none" <?php selected( $position, 'none' ); ?>><?php esc_html_e( 'None (Hide it)', 'wpc-product-options' ); ?></option>
                                        </select>
                                        <span class="description"><?php esc_html_e( 'Choose the position to show product options on a single product page. You can also place the shortcode [wpcpo] inside the add-to-cart form.', 'wpc-product-options' ); ?></span>
                                    </td>
                                </tr>
                                <tr class="submit">
                                    <th colspan="2">
										<?php settings_fields( 'wpcpo_settings' ); ?><?php submit_button(); ?>
                                    </th>
                                </tr>
                            </table>
                        </form>
					<?php } elseif ( $active_tab === 'premium' ) { ?>
                        <div class="wpclever_settings_page_content_text">
                            <p>Get the Premium Version just $29!
                                <a href="https://wpclever.net/downloads/product-options/?utm_source=pro&utm_medium=wpcpo&utm_campaign=wporg" target="_blank">https://wpclever.net/downloads/product-options/</a>
                            </p>
                            <p><strong>Extra features for Premium Version:</strong></p>
                            <ul style="margin-bottom: 0">
                                <li>- Users can define their own price formula.</li>
                                <li>- Get the lifetime update & premium support.</li>
                            </ul>
                        </div>
					<?php } ?>
                </div><!-- /.wpclever_settings_page_content -->
                <div class="wpclever_settings_page_suggestion">
                    <div class="wpclever_settings_page_suggestion_label">
                        <span class="dashicons dashicons-yes-alt"></span> Suggestion
                    </div>
                    <div class="wpclever_settings_page_suggestion_content">
                        <div>
                            To display custom engaging real-time messages on any wished positions, please install
                            <a href="https://wordpress.org/plugins/wpc-smart-messages/" target="_blank">WPC Smart Messages</a> plugin. It's free!
                        </div>
                        <div>
                            Wanna save your precious time working on variations? Try our brand-new free plugin
                            <a href="https://wordpress.org/plugins/wpc-variation-bulk-editor/" target="_blank">WPC Variation Bulk Editor</a> and
                            <a href="https://wordpress.org/plugins/wpc-variation-duplicator/" target="_blank">WPC Variation Duplicator</a>.
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}

		function action_links( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( WPCPO_FILE );
			}

			if ( $plugin === $file ) {
				$settings             = '<a href="' . esc_url( admin_url( 'admin.php?page=wpclever-wpcpo&tab=settings' ) ) . '">' . esc_html__( 'Settings', 'wpc-product-options' ) . '</a>';
				$options              = '<a href="' . esc_url( admin_url( 'edit.php?post_type=wpc_product_option' ) ) . '">' . esc_html__( 'Product Options', 'wpc-product-options' ) . '</a>';
				$links['wpc-premium'] = '<a href="' . esc_url( admin_url( 'admin.php?page=wpclever-wpcpo&tab=premium' ) ) . '" style="color: #c9356e">' . esc_html__( 'Premium Version', 'wpc-product-options' ) . '</a>';
				array_unshift( $links, $settings, $options );
			}

			return (array) $links;
		}

		function row_meta( $links, $file ) {
			static $plugin;

			if ( ! isset( $plugin ) ) {
				$plugin = plugin_basename( WPCPO_FILE );
			}

			if ( $plugin === $file ) {
				$row_meta = [
					'support' => '<a href="' . esc_url( WPCPO_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'wpc-product-options' ) . '</a>',
				];

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		function ajax_search_term() {
			$return = [];

			$args = [
				'taxonomy'   => sanitize_text_field( $_REQUEST['taxonomy'] ),
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => false,
				'fields'     => 'all',
				'name__like' => sanitize_text_field( $_REQUEST['q'] ),
			];

			$terms = get_terms( $args );

			if ( is_array( $terms ) && count( $terms ) ) {
				foreach ( $terms as $term ) {
					$return[] = [ $term->slug, $term->name ];
				}
			}

			wp_send_json( $return );
		}

		private function get_field( $type, $key = '', $data = [], $active = false ) {
			$this->field   = $data;
			$text_fields   = [ 'number', 'text', 'password', 'hidden', 'textarea', 'email' ];
			$select_fields = [ 'radio', 'image-radio', 'select', 'checkbox', 'image-checkbox' ];

			if ( in_array( $type, $text_fields ) ) {
				$file_type = WPCPO_DIR . 'includes/templates/fields/text.php';
			} elseif ( in_array( $type, $select_fields ) ) {
				$file_type = WPCPO_DIR . 'includes/templates/fields/select.php';
			} else {
				$file_type = WPCPO_DIR . 'includes/templates/fields/' . $type . '.php';
			}

			$file_display   = WPCPO_DIR . 'includes/templates/display.php';
			$file_field     = WPCPO_DIR . 'includes/templates/field.php';
			$this->field_id = $field_id = ! empty( $key ) ? $key : self::generate_key();

			if ( file_exists( $file_type ) ) {
				include $file_field;
			}
		}

		private function get_options( $type = null ) {
			$options = $this->get_field_value( 'options', [] );
			include WPCPO_DIR . 'includes/templates/fields/options.php';
		}

		private function get_option( $option = [], $type = null ) {
			$option_id = self::generate_key();
			include WPCPO_DIR . 'includes/templates/fields/option.php';
		}

		private function get_field_value( $key, $default = '' ) {
			if ( isset( $this->field[ $key ] ) ) {
				return $this->field[ $key ];
			}

			return $default;
		}

		private function get_option_value( $option, $key, $default = '' ) {
			if ( isset( $option[ $key ] ) ) {
				return $option[ $key ];
			}

			return $default;
		}

		public function ajax_add_field() {
			$type = sanitize_text_field( $_POST['type'] ?? 'text' );
			$this->get_field( $type, '', [], true );
			wp_die();
		}

		public function ajax_add_option() {
			$this->field_id = sanitize_text_field( $_POST['field_id'] );
			$type           = sanitize_text_field( $_POST['type'] );
			$this->get_option( [], $type );
			wp_die();
		}

		public function register_postype() {
			$labels = [
				'name'          => _x( 'Product Options', 'Post Type General Name', 'wpc-product-options' ),
				'singular_name' => _x( 'Product Option', 'Post Type Singular Name', 'wpc-product-options' ),
				'add_new_item'  => esc_html__( 'Add New Product Option', 'wpc-product-options' ),
				'add_new'       => esc_html__( 'Add New', 'wpc-product-options' ),
				'edit_item'     => esc_html__( 'Edit Product Option', 'wpc-product-options' ),
				'update_item'   => esc_html__( 'Update Product Option', 'wpc-product-options' ),
				'search_items'  => esc_html__( 'Search Product Option', 'wpc-product-options' ),
			];

			$args = [
				'label'               => esc_html__( 'Product Option', 'wpc-product-options' ),
				'labels'              => $labels,
				'supports'            => [ 'title', 'excerpt' ],
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 28,
				'menu_icon'           => 'dashicons-feedback',
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'capability_type'     => 'post',
				'show_in_rest'        => false,
			];

			register_post_type( 'wpc_product_option', $args );
		}

		public static function generate_key() {
			$key         = '';
			$key_str     = apply_filters( 'wpcpo_key_characters', 'abcdefghijklmnopqrstuvwxyz0123456789' );
			$key_str_len = strlen( $key_str );

			for ( $i = 0; $i < apply_filters( 'wpcpo_key_length', 4 ); $i ++ ) {
				$key .= $key_str[ random_int( 0, $key_str_len - 1 ) ];
			}

			if ( is_numeric( $key ) ) {
				$key = self::generate_key();
			}

			return apply_filters( 'wpcpo_generate_key', 'wpcpo-' . $key );
		}

		public static function upload_filetypes( $types = null ) {
			$filetypes = '';

			if ( ! empty( $types ) ) {
				if ( is_array( $types ) ) {
					$filetypes = implode( ', ', array_map( 'Wpcpo_Backend::filetype_exts', $types ) );
				} elseif ( is_string( $types ) ) {
					$types_arr = array_unique( explode( ',', $types ) );
					$filetypes = implode( ', ', array_map( 'Wpcpo_Backend::filetype_exts', $types_arr ) );
				}
			} else {
				$misc_exts        = [
					// Images.
					'jpg',
					'jpeg',
					'png',
					'gif',
					'webp',
					// Video.
					'mov',
					'avi',
					'mpg',
					'3gp',
					'3g2',
					// "audio".
					'midi',
					'mid',
					// Miscellaneous.
					'pdf',
					'doc',
					'ppt',
					'odt',
					'pptx',
					'docx',
					'pps',
					'ppsx',
					'xls',
					'xlsx',
					'key',
				];
				$audio_exts       = wp_get_audio_extensions();
				$video_exts       = wp_get_video_extensions();
				$upload_filetypes = array_unique( array_merge( $misc_exts, $audio_exts, $video_exts ) );
				$filetypes        = implode( ', ', array_map( 'Wpcpo_Backend::filetype_exts', $upload_filetypes ) );
			}

			return apply_filters( 'wpcpo_upload_filetypes', $filetypes );
		}

		public static function filetype_exts( $filetype ) {
			return '.' . trim( strtolower( str_replace( '.', '', str_replace( ',', '', $filetype ) ) ) );
		}

		function wpcsm_locations( $locations ) {
			$locations['WPC Product Options'] = [
				'wpcpo_wrap_before'    => esc_html__( 'Before wrapper', 'wpc-product-options' ),
				'wpcpo_wrap_after'     => esc_html__( 'After wrapper', 'wpc-product-options' ),
				'wpcpo_options_before' => esc_html__( 'Before options', 'wpc-product-options' ),
				'wpcpo_options_after'  => esc_html__( 'After options', 'wpc-product-options' ),
			];

			return $locations;
		}

		public static function write_log( $log ) {
			if ( true === WP_DEBUG ) {
				if ( is_array( $log ) || is_object( $log ) ) {
					error_log( print_r( $log, true ) );
				} else {
					error_log( $log );
				}
			}
		}
	}
}

return Wpcpo_Backend::instance();
