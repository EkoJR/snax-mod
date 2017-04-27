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
	/**
	 * Summary.
	 *
	 * @since 1.6.1
	 * @access private
	 * @var null $instance Description.
	 */
	private static $instance = null;

	/**
	 * Summary.
	 *
	 * @since 1.6.1
	 * @access public
	 * @var string $plugin_dir Description.
	 */
	public $plugin_dir;

	/**
	 * Summary.
	 *
	 * @since 1.6.1
	 * @access public
	 * @var string $plugin_url Description.
	 */
	public $plugin_url;

	/**
	 * Summary.
	 *
	 * @since 1.6.1
	 * @access public
	 * @var string $table_name Description.
	 */
	public $table_name;

	/**
	 * Summary.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access public
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 *
	 * @return object self::$instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access private
	 */
	private function __construct() {
		$this->plugin_dir = WP_PLUGIN_DIR . '/snax-mod/';
		$this->plugin_url = WP_PLUGIN_URL . '/snax-mod/';
		$this->table_name = 'snax_mod_votes';

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'snax/snax.php' ) ) {
			return new WP_Error( 'snax_mod_construct_failed', esc_html__( 'Snax plugin must be activated.', 'snax-mod' ) );
		} else {
			$this->_requires();

			$this->_add_hooks();

			/*
			// Early Hook
			add_action( 'plugins_loaded', array( $this, 'hook_action_plugins_loaded' ) );

			// Multilingual Support
			add_action( 'load_textdomain', array( $this, 'hook_action_load_textdomain' ) );

			// Plugin Init Hook
			add_action( 'init', array( $this, 'hook_action_init' ) );

			// After WordPress is fully loaded
			add_action( 'wp_loaded', array( $this, 'hook_action_wp_loaded' ) );

			// WordPress Footer
			add_action( 'wp_footer', array( $this, 'hook_action_wp_footer' ) );
			*/
			if ( is_admin() ) {
				/*
				// Plugin Admin Init Hook
				add_action( 'admin_init', array( $this, 'hook_action_admin_init' ) );
				*/
				$file = $this->plugin_dir . 'snax-mod.php';
				register_activation_hook( $file, array( $this, 'hook_activation' ) );
				register_deactivation_hook( $file, array( $this, 'hook_deactivation' ) );
			}
		}
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access private
	 * @return void
	 */
	private function __clone() {
		// Cloning instances of the class is forbidden.
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
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'snax_mod_core' ), '1.0' );
	}

	/**
	 * Required Files.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access private
	 */
	private function _requires() {
		// Functions.
		require_once( $this->plugin_dir . 'includes\functions.php' );
	}

	/**
	 * Add Extension Hooks for Snax Mod/Extension.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access private
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 */
	private function _add_hooks() {
		// AJAX Hook for Voting on an Item
		// '\plugins\snax\includes\core\hooks.php' && '\plugins\snax\includes\votes\ajax.php'.
		add_action( 'wp_ajax_snax_vote_item', array( $this, 'hook_ajax_vote_item' ) );

		// Hook to replace vote that was added.
		// snax_insert_vote( $vote_arr ) IN 'wp-content\plugins\snax\includes\votes\functions.php'.
		add_action( 'snax_vote_added', array( $this, 'hook_action_reinsert_vote' ) );
	}

	/**
	 * Plugin Activation.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access public
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 * @global object $wpdb WP Db Query.
	 *
	 * @return boolean If successful.
	 */
	public function hook_activation() {
		/* ***** VOTES ***** */
		global $wpdb;

		$table_name      = $wpdb->prefix . 'snax_votes';
		$charset_collate = $wpdb->get_charset_collate();
		$column = $wpdb->get_row( "SELECT wp_post_id FROM $table_name" );

		if ( ! isset( $column ) ) {
			// Change to --> wp_cache_get( int|string $key, string $group = '', bool $force = false, bool $found = null )
			$votes_db = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY vote_id DESC" );

			$votes_db_mod = $this->update_votes_db_sql_column( $votes_db );

			$r01 = $wpdb->query( "ALTER TABLE $table_name ADD wp_post_id bigint(20) NOT NULL" );

			foreach ( $votes_db_mod as $key => $vote_mod ) {
				$affected_rows = $wpdb->replace(
					$table_name,
					array(
						'vote_id'     => (int) intval( $vote_mod->vote_id ),
						'post_id'     => (int) intval( $vote_mod->post_id ),
						'vote'        => (int) intval( $vote_mod->vote ),
						'author_id'   => (int) intval( $vote_mod->author_id ),
						'author_ip'   => (string) $vote_mod->author_ip,
						'author_host' => (string) $vote_mod->author_host,
						'date'        => (string) $vote_mod->date,
						'date_gmt'    => (string) $vote_mod->date_gmt,
						'wp_post_id'  => (int) intval( $vote_mod->wp_post_id ),
					),
					array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
					)
				);

				// If any fail, remove column and send error.
				if ( false === $affected_rows ) {
					$r01 = $wpdb->query( $wpdb->prepare( "ALTER TABLE $table_name DROP COLUMN wp_post_id" ) );
					$e01 = new WP_Error( 'snax_mod_insert_vote_failed', esc_html__( 'Could not insert new vote into the database!', 'snax_mod' ) );

					return false;
				}
			}
		}// End if().
		return true;
	}

	/**
	 * Plugin Deactivation.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access public
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 * @global object $wpdb WP Db Query.
	 */
	public function hook_deactivation() {
		global $wpdb;

		$table_name      = $wpdb->prefix . snax_get_votes_table_name();
		$charset_collate = $wpdb->get_charset_collate();
		$column = $wpdb->get_row( 'SELECT wp_post_id FROM ' . $table_name );

		if ( isset( $column ) ) {
			$wpdb->query( $wpdb->prepare( "ALTER TABLE $table_name DROP COLUMN wp_post_id" ) );
		}
	}

	/**
	 * Update wp_post_id in Votes Database Table.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access private
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 *
	 * @param array $votes_db SQL Snax Votes Database.
	 * @return array Modified Votes Db Table.
	 */
	private function update_votes_db_sql_column( $votes_db ) {
		$vote_defaults = array(
			'vote_id'     => 0,
			'post_id'     => 0,
			'vote'        => 1,
			'author_id'   => 0,
			'author_ip'   => '',
			'author_host' => '',
			'date'        => '',
			'date_gmt'    => '',
			'wp_post_id'  => 0,
		);

		$votes_db_mod = array();
		foreach ( $votes_db as $db_key => $vote ) {
			$votes_db_mod[ $db_key ] = (object) $vote_defaults;
			foreach ( $vote_defaults as $vote_key => $default_value ) {
				if ( 'wp_post_id' === $vote_key ) {
					$votes_db_mod[ $db_key ]->$vote_key = (string) $this->get_item_parent_id( $vote->post_id );
				} elseif ( isset( $vote->$vote_key ) ) {
					$votes_db_mod[ $db_key ]->$vote_key = $vote->$vote_key;
				} else {
					//$votes_db_mod[ $db_key ]->$vote_key = $default_value;
				}
			}
		}

		return $votes_db_mod;
	}

	/**
	 * Get Item's Displayed paged.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access private
	 *
	 * @see WP_Query class
	 * @link URL
	 *
	 * @param int $item_id Items index number.
	 * @return int Post/Page (Parent) ID.
	 */
	private function get_item_parent_id( $item_id ) {
		$default_args = array(
			'p'         => $item_id,
			'post_type' => apply_filters( 'snax_item_post_type', 'snax_item' ),
		);

		$items = new WP_Query( $default_args );

		$parent_id = $items->post->post_parent;
		return $parent_id;
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

	/**
	 * AJAX Vote on Item.
	 *
	 * Description.
	 *
	 * @since 1.6.1
	 * @access public
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 * @global object $_POST Through filters.
	 *
	 * @return void
	 */
	public function hook_ajax_vote_item() {
		check_ajax_referer( 'snax-vote-item', 'security' );

		// Sanitize item id.
		$a01 = INPUT_POST;
		$a02 = FILTER_SANITIZE_NUMBER_INT;
		
		$item_id = (int) filter_input( INPUT_POST, 'snax_item_id', FILTER_SANITIZE_NUMBER_INT ); // Removes all illegal characters from a number.

		// WHAT ARE THE OTHER $_POST'S?
		// TODO - Add post ID.
		$wp_post_url = wp_get_referer();
		$wp_post_id = (int) url_to_postid( $wp_post_url );

		if ( 0 === $item_id ) {
			snax_ajax_response_error( 'Item id not set!' );
			exit;
		} elseif ( empty( $wp_post_id ) ) {
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

		// Eko - Removed Downvoting as a valid vote.
		if ( ! in_array( $type, array( 'upvote' ), true ) ) {
			snax_ajax_response_error( 'Vote type is not allowed!' );
			exit;
		}

		// Check creds.
		if ( $guest_voting_disabled && ! user_can( $author_id, 'snax_vote_items', $item_id ) ) {
			snax_ajax_response_error( sprintf( 'Author %d is not allowed to vote for this item.', $author_id ) );
			exit;
		}

		// Update current vote.
		// TODO - Add check if voted this week.
		if ( snax_mod_user_voted_this_list_week( $item_id, $author_id, $wp_post_id ) ) {
			// User already upvoted and clicked upvote again, wants to remove vote.
			if ( snax_user_upvoted( $item_id, $author_id ) && 'upvote' === $type ) {
				// TODO/FIXME - This is removing past votes rather than recent ones.
				$voted = snax_mod_remove_vote( $item_id, $author_id );
			} 
			/* REMOVED OLD CODE - Since Downvoting is no longer relevant.
			elseif ( snax_user_downvoted( $item_id, $author_id ) && 'downvote' === $type ) {
				// ELSEIF User already downvoted and clicked downvote again, 
				// wants to remove vote.
				// NOT ALLOWED.
				snax_ajax_response_error( 'Vote type is not allowed!' );
				exit;
				/*
				 * OLD
				 * User decided to vote opposite.
				 * $voted = snax_remove_vote( $item_id, $author_id );
				 *//*
			}
			else {
				// NOT ALLOWED.
				snax_ajax_response_error( 'Vote type is not allowed!' );
				exit;
				/*
				 * OLD
				 * User decides to vote opposite (up/down)
				 * $voted = snax_toggle_vote( $item_id, $author_id );
				 *//*
				
			}
			*/
		} else { // New vote.
			$new_vote = array(
				'post_id'   => $item_id,
				'author_id' => $author_id,
			);

			if ( 'upvote' === $type ) {
				// CHANGE?
				$voted = snax_upvote_item( $new_vote );
			}/* else {
				$voted = snax_downvote_item( $new_vote );
			}*/
		}

		// On snax_upvote_item() fail.
		if ( is_wp_error( $voted ) ) {
			snax_ajax_response_error( sprintf( 'Failed to vote for item with id %d', $item_id ), array(
				'error_code'    => esc_html( $voted->get_error_code() ),
				'error_message' => esc_html( $voted->get_error_message() ),
			) );
			exit;
		}

		ob_start();
		snax_mod_render_voting_box( $item_id, $author_id );
		$html = ob_get_clean();

		snax_ajax_response_success( 'Vote added successfully.', array(
			'html' => $html,
		) );
		exit;
	}

	/**
	 * Hook Re-insert Vote.
	 *
	 * Main function for replacing votes in the database.
	 *
	 * @since 1.6.1
	 * @access public
	 *
	 * @see Function/method/class relied on
	 * @link URL
	 * @global object $wpdb WP Db Query.
	 *
	 * @param array $vote_arr AJAX array that was passed earlier.
	 * @return type Description.
	 */
	public function hook_action_reinsert_vote( $vote_arr ) {
		global $wpdb;
		$defaults = array(
			'post_id'   => get_the_ID(),
			'author_id' => get_current_user_id(),
			'vote'      => 1,
		);
		$vote_arr = wp_parse_args( $vote_arr, $defaults );

		$table_name = $wpdb->prefix . snax_get_votes_table_name();

		// Grab Post ID Server-side.
		$wp_post_url = wp_get_referer();
		$wp_post_id = 0;
		if ( ! empty( $wp_post_url ) ) {
			$wp_post_id = (int) url_to_postid( $wp_post_url );
		}

		$post_date  = current_time( 'mysql' );
		$ip_address = snax_get_ip_address();
		$host = gethostbyaddr( $ip_address );

		$unprep = "SELECT * FROM $table_name ORDER BY vote_id DESC LIMIT 1";
		$last_row = $wpdb->get_row( $unprep );
		// Replace(modifies or adds new) || Update(error if not set).
		$affected_rows = $wpdb->replace(
			$table_name,
			array(
				'vote_id'     => intval( $last_row->vote_id ),
				'post_id'     => $vote_arr['post_id'],
				'vote'        => $vote_arr['vote'],
				'author_id'   => $vote_arr['author_id'],
				'author_ip'   => $ip_address ? $ip_address : '',
				'author_host' => $host ? $host : '',
				'date'        => $post_date,
				'date_gmt'    => get_gmt_from_date( $post_date ),
				'wp_post_id'  => $wp_post_id,
			),
			array(
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
			)
		);

		if ( false === $affected_rows ) {
			return new WP_Error( 'snax_insert_vote_failed', esc_html__( 'Could not insert new vote into the database!', 'snax' ) );
		}

		// snax_update_votes_metadata( $vote_arr['post_id'] );

		return true;
	}

	public function post_rank( $wp_post_id, $last_week = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'snax_votes';
		
		if ( $last_week ) {
			$prep = $wpdb->prepare( 'SELECT vote FROM %d WHERE wp_post_id = %d AND date > DATE_SUB(INTERVAL 1 WEEK, INTERVAL 2 WEEK)', $wp_post_id );
		} else {
			$prep = $wpdb->prepare( 'SELECT vote FROM %d WHERE wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)', $wp_post_id );
		}
		
		$results = $wpdb->get_results( $prep );
		
	}

	public function item_rank( $item_id, $wp_post_id, $last_week = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'snax_votes';
		
		if ( $last_week ) {
			$prep = $wpdb->prepare( 'SELECT vote FROM %d WHERE post_id = %d AND wp_post_id = %d AND date > DATE_SUB(INTERVAL 1 WEEK, INTERVAL 2 WEEK)', $item_id, $wp_post_id );
		} else { // Current Week
			$prep = $wpdb->prepare( 'SELECT vote FROM %d WHERE post_id = %d AND wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)', $item_id, $wp_post_id );
		}
		
		$results = $wpdb->get_results( $prep );
	}
	
	
}
