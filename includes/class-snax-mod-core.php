<?php
/**
 * Snax Mod Core File
 *
 * @link https://github.com/EkoJr/
 *
 * @package Snax
 * @subpackage snax-mod.php
 * @since 1.6.1
 */

/**
 * Snax Mod Core
 *
 * Main Core Class
 *
 * @since 1.6.1
 */
class Snax_Mod_Core {

	private static $instance = null;

	public $plugin_dir;

	public $plugin_url;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	private function __construct() {
		// is_plugin_active( 'plugin-directory/plugin-file.php' )
		//$this->_set_vars();
		$this->plugin_dir = WP_PLUGIN_DIR . '/snax-mod/';
		$this->plugin_url = WP_PLUGIN_URL . '/snax-mod/';

		$this->_requires();

		$this->_add_hooks();

		// Early Hook
		//add_action( 'plugins_loaded', array( $this, 'hook_action_plugins_loaded' ) );

		// Multilingual Support
		//add_action( 'load_textdomain', array( $this, 'hook_action_load_textdomain' ) );

		// Plugin Init Hook
		//add_action( 'init', array( $this, 'hook_action_init' ) );

		// After WordPress is fully loaded
		//add_action( 'wp_loaded', array( $this, 'hook_action_wp_loaded' ) );

		// WordPress Footer
		//add_action( 'wp_footer', array( $this, 'hook_action_wp_footer' ) );

		if ( is_admin() ) {
			// Plugin Admin Init Hook
			//add_action( 'admin_init', array( $this, 'hook_action_admin_init' ) );

			$file = $this->plugin_dir . 'snax-mod.php';
			register_activation_hook( $file, array( 'Snax_Mod_Core', 'hook_activation' ) );
			register_deactivation_hook( $file, array( 'Snax_Mod_Core', 'hook_deactivation' ) );
		}
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected || private
	 */
	private function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'snax_mod_core' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	private function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'snax_mod_core' ), '1.0' );
	}

	private function _requires() {
		// Functions.
		require_once( $this->plugin_dir . 'includes\functions.php' );
	}

	public function hook_activation() {
		//$a01 = 'hi';
	}

	public function hook_deactivation() {
		//$a01 = 'hi';
	}

/*	public function hook_action_plugins_loaded() {
		$this->add_hooks();
	}*/

/*	public function hook_action_load_textdomain() {
		$this->add_hooks();
	}*/

/*	public function hook_action_init() {
		$this->add_hooks();
	}*/

/*	public function hook_action_admin_init() {
		$this->add_hooks();
	}*/

/*	public function hook_action_wp_loaded() {
		$this->add_hooks();
	}*/

/*	public function hook_action_wp_footer() {
		$this->add_hooks();
	}*/

	private function _add_hooks() {
		// snax_insert_vote( $vote_arr ) IN 'wp-content\plugins\snax\includes\votes\functions.php'
		add_action( 'snax_vote_added', array( $this, 'hook_action_insert_vote' ) );
		
		// \plugins\snax\includes\core\hooks.php
		// \plugins\snax\includes\votes\ajax.php
		add_action( 'wp_ajax_snax_vote_item', array($this, 'hook_ajax_vote_item' ) );
	}

	/**
	 * DEFAULT Insert Vote.
	 *
	 * Main function for adding votes to Posts/Pages.
	 *
	 * @since 1.6.1
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 *
	 * @param array $vote_arr Current vote being added.
	 * @return boolean False if unsuccessful.
	 */
	public function hook_action_insert_vote( $vote_arr ) {
		$defaults = array(
			'post_id'   => get_the_ID(),
			'author_id' => get_current_user_id(),
			'vote'      => 1,
		);

		$vote_arr = wp_parse_args( $vote_arr, $defaults );

		global $wpdb;
		$table_name = $wpdb->prefix . snax_get_votes_table_name();

		$post_date  = current_time( 'mysql' );
		$ip_address = snax_get_ip_address();
		$host = gethostbyaddr( $ip_address );

		$affected_rows = $wpdb->insert(
			$table_name,
			array(
				'post_id'     => $vote_arr['post_id'],
				'vote'        => $vote_arr['vote'],
				'author_id'   => $vote_arr['author_id'],
				'author_ip'   => $ip_address ? $ip_address : '',
				'author_host' => $host ? $host : '',
				'date'        => $post_date,
				'date_gmt'    => get_gmt_from_date( $post_date ),
			),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		if ( false === $affected_rows ) {
			return new WP_Error( 'snax_insert_vote_failed', esc_html__( 'Could not insert new vote into the database!', 'snax' ) );
		}

		snax_update_votes_metadata( $vote_arr['post_id'] );

		do_action( 'snax_vote_added', $vote_arr );

		return true;
	}

	function hook_ajax_vote_item() {
		check_ajax_referer( 'snax-vote-item', 'security' );

		// Sanitize item id.
		$item_id = (int) filter_input( INPUT_POST, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

		if ( 0 === $item_id ) {
			snax_ajax_response_error( 'Item id not set!' );
			exit;
		}

		$guest_voting_disabled = ! snax_guest_voting_is_enabled();

		// Sanitize author id.
		$author_id = (int) filter_input( INPUT_POST, 'snax_author_id', FILTER_SANITIZE_NUMBER_INT );

		if ( $guest_voting_disabled && 0 === $author_id ) {
			snax_ajax_response_error( 'Author id not set!' );
			exit;
		}

		// Sanitize type.
		$type = filter_input( INPUT_POST, 'snax_vote_type', FILTER_SANITIZE_STRING );

		if ( ! in_array( $type, array( 'upvote', 'downvote' ), true ) ) {
			snax_ajax_response_error( 'Vote type is not allowed!' );
			exit;
		}

		// Check creds.
		if ( $guest_voting_disabled && ! user_can( $author_id, 'snax_vote_items', $item_id ) ) {
			snax_ajax_response_error( sprintf( 'Author %d is not allowed to vote for this item.', $author_id ) );
			exit;
		}

		// Update current vote.
		if ( snax_user_voted( $item_id, $author_id ) ) {
			// User already upvoted and clicked upvote again, wants to remove vote.
			if ( snax_user_upvoted( $item_id, $author_id ) && 'upvote' === $type ) {
				$voted = snax_remove_vote( $item_id, $author_id );

				// User already downvoted and clicked downvote again, wants to remove vote.
			} else if ( snax_user_downvoted( $item_id, $author_id ) && 'downvote' === $type ) {
				$voted = snax_remove_vote( $item_id, $author_id );

				// User decided to vote opposite.
			} else {
				$voted = snax_toggle_vote( $item_id, $author_id );
			}

			// New vote.
		} else {
			$new_vote = array(
				'post_id'   => $item_id,
				'author_id' => $author_id,
			);

			if ( 'upvote' === $type ) {
				$voted = snax_upvote_item( $new_vote );
			} else {
				$voted = snax_downvote_item( $new_vote );
			}
		}

		if ( is_wp_error( $voted ) ) {
			snax_ajax_response_error( sprintf( 'Failed to vote for item with id %d', $item_id ), array(
				'error_code'    => esc_html( $voted->get_error_code() ),
				'error_message' => esc_html( $voted->get_error_message() ),
			) );
			exit;
		}

		ob_start();
		snax_render_voting_box( $item_id, $author_id );
		$html = ob_get_clean();

		snax_ajax_response_success( 'Vote added successfully.', array(
			'html' => $html,
		) );
		exit;
	}
}
