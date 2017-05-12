<?php

/**
 * Check whether user has already voted for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return mixed            User vote if voted, false othrwise.
 */
//unset($snax_user_voted);
/*function snax_mod_user_voted( $item_id, $user_id ) {
	return 0 !== eko_get_user_vote( $item_id, $user_id );
}*/

/**
 * Return user votes for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
//unset($snax_get_user_vote);
/*function snax_mod_get_user_vote( $item_id, $user_id ) {
	// Guest voting disabled.
	if ( 0 === $user_id && ! snax_guest_voting_is_enabled() ) {
		return 0;
	}

	// Guest voting enabled.
	if ( 0 === $user_id && snax_guest_voting_is_enabled() ) {
		// Read cookie setn by client.
		$vote_cookie = filter_input( INPUT_POST, 'snax_user_voted', FILTER_SANITIZE_STRING );

		// If not sent, read cookie from server.
		if ( ! $vote_cookie ) {
			$vote_cookie = filter_input( INPUT_COOKIE, 'snax_vote_item_' . $item_id, FILTER_SANITIZE_STRING );
		}

		switch ( $vote_cookie ) {
			case 'upvote':
				return snax_get_upvote_value();
			case 'downvote':
				return snax_get_downvote_value();
			default:
				return 0;
		}
	}

	// User logged in.
	global $wpdb;
	// 'wp_snax_votes'
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$vote2 = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT *
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d
			ORDER BY vote_id DESC
			LIMIT 1",
			$item_id,
			$user_id
		)
	);
	$vote = $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT vote
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d
			ORDER BY vote_id DESC
			LIMIT 1",
			$item_id,
			$user_id
		)
	);

	return (int) $vote;
}*/

/*function eko_get_votes_db( $arg = array() ) {
	$default_args = array(
		'select' => 'SELECT vote_id ',
		'from'   => 'FROM wp_snax_votes ',
		'where'  => '',
		'order'  => 'ORDER BY vote_id DESC',
		'limit'  => '',
	);
	
	foreach( $default_arg as $key => $value ) {
		if ( empty( $arg[ $key ] ) ) {
			$arg[ $key ] = $value;
		}
	}
	
	$vote1 = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT vote
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d
			ORDER BY vote_id DESC
			LIMIT 1",
			$item_id,
			$user_id
		)
	);
	$vote2 = $wpdb->get_results(
		$wpdb->prepare(
			"
			SELECT *
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d
			ORDER BY vote_id DESC
			LIMIT 1",
			$item_id,
			$user_id
		)
	);
}*/

/**
 * True if user voted for an item this week
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_mod_user_voted_this_item_week( $item_id, $user_id, $wp_post_id ) {
	// Guest voting disabled.
	if ( 0 === $user_id && ! snax_guest_voting_is_enabled() ) {
		return false;
	}

	// IF Guest voting enabled.
	if ( 0 === $user_id && snax_guest_voting_is_enabled() ) {
		// Read cookie sent by client.
		$vote_cookie = filter_input( INPUT_POST, 'snax_user_voted', FILTER_SANITIZE_STRING );

		// If not sent, read cookie from server.
		if ( ! $vote_cookie ) {
			$vote_cookie = filter_input( INPUT_COOKIE, 'snax_vote_item_' . $item_id, FILTER_SANITIZE_STRING );
		}

		switch ( $vote_cookie ) {
			case 'upvote':
				return 1;
			//case 'downvote':
				//return -1;
			default:
				return false;
		}
	}

	// User logged in.
	global $wpdb;
	// 'wp_snax_votes'
	$votes_table_name = $wpdb->prefix . 'snax_votes';//snax_get_votes_table_name();

	$prep = $wpdb->prepare(
			"SELECT vote
			FROM $votes_table_name
			WHERE post_id = %d AND author_id = %d AND wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)
			ORDER BY vote_id DESC
			LIMIT 1",
			$item_id,
			$user_id,
			$wp_post_id
		);
//	$prep = $wpdb->prepare(
//			"SELECT vote
//			FROM $votes_table_name
//			WHERE author_id = %d AND wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)
//			ORDER BY vote_id DESC
//			LIMIT 1",
//			$user_id,
//			$wp_post_id
//		);
	$vote = $wpdb->get_var( $prep );

	if ( is_null( $vote ) ) {
		return false;
	}
	return (boolean) $vote;
}

/**
 * True if user voted for an item this week
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
function snax_mod_user_voted_this_list_week( $item_id, $user_id, $wp_post_id ) {
	// Guest voting disabled.
	if ( 0 === $user_id && ! snax_guest_voting_is_enabled() ) {
		return false;
	}

	// IF Guest voting enabled.
	if ( 0 === $user_id && snax_guest_voting_is_enabled() ) {
		// Read cookie sent by client.
		$vote_cookie = filter_input( INPUT_POST, 'snax_user_voted', FILTER_SANITIZE_STRING );

		// If not sent, read cookie from server.
		if ( ! $vote_cookie ) {
			$vote_cookie = filter_input( INPUT_COOKIE, 'snax_vote_item_' . $item_id, FILTER_SANITIZE_STRING );
		}

		switch ( $vote_cookie ) {
			case 'upvote':
				return 1;
			//case 'downvote':
				//return -1;
			default:
				return false;
		}
	}

	// User logged in.
	global $wpdb;
	// 'wp_snax_votes'
	$votes_table_name = $wpdb->prefix . 'snax_votes';//snax_get_votes_table_name();

//	$prep = $wpdb->prepare(
//			"SELECT vote
//			FROM $votes_table_name
//			WHERE post_id = %d AND author_id = %d AND wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)
//			ORDER BY vote_id DESC
//			LIMIT 1",
//			$item_id,
//			$user_id,
//			$wp_post_id
//		);
	$prep = $wpdb->prepare(
			"SELECT vote " .
			"FROM $votes_table_name " .
			"WHERE author_id = %d AND wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK) " .
			"ORDER BY vote_id DESC " .
			"LIMIT 1",
			$user_id,
			$wp_post_id
		);
	$vote = $wpdb->get_var( $prep );

	if ( is_null( $vote ) ) {
		return false;
	}
	return (boolean) $vote;
}
/**
 * Remove user vote
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool|WP_Error
 */
function snax_mod_remove_vote( $item_id, $user_id ) {
	if ( ! snax_user_voted( $item_id, $user_id ) ) {
		return new WP_Error( 'snax_has_not_voted', __( 'User has not voted for this item!', 'snax' ) );
	}

	global $wpdb;
	$votes_table_name = $wpdb->prefix . snax_get_votes_table_name();

	$vote = snax_get_user_vote( $item_id, $user_id );

	$updated_rows = $wpdb->query(
		$wpdb->prepare(
			"
            DELETE FROM $votes_table_name
		    WHERE post_id = %d AND author_id = %d AND vote = %d
		 	ORDER BY date DESC LIMIT 1
			",
			$item_id,
			$user_id,
			$vote
		)
	);

	if ( false === $updated_rows ) {
		return new WP_Error( 'snax_remove_vote_failed', __( 'User vote could not be removed!', 'snax' ) );
	}

	snax_update_votes_metadata( $item_id );

	return true;
}

/**
 * Render upvote/downvote box
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 * @param string      $class                CSS class.
 */
function snax_mod_render_voting_box( $post = null, $wp_post_id = 0, $user_id = 0, $class = 'snax-voting-simple' ) {
	if ( snax_show_item_voting_box( $post ) ) {
		$post = get_post( $post );

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$final_class = array(
			'snax-voting',
		);
		$final_class = array_merge( $final_class, explode( ' ', $class ) );
		?>
		<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $final_class ) ); ?>">
			<?php
			$snax_class = array(
				'snax-voting-score'
			);

			$snax_voting_score = snax_get_voting_score( $post );
			if ( 0 === $snax_voting_score ) {
				$snax_class[] = 'snax-voting-score-0';
			}
			?>

			<?php
			if ( snax_show_item_upvote_link( $post ) ) :
				snax_mod_render_upvote_link( $post, $wp_post_id, $user_id );
			endif;
			?>

			<?php
			// Removed Downvoting
			//if ( snax_show_item_downvote_link( $post ) ) :
				//snax_render_downvote_link( $post, $user_id );
			//endif;
			?>

			<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
				<div>
					<?php
					printf( wp_kses_post( _n( '<strong>%d</strong><br>vote', '<strong>%d</strong><br>votes', (int) $snax_voting_score, 'snax' ) ), (int) $snax_voting_score );
					?>
				</div>
			</div>
			<div class="snax-voting-details">
				<p><?php printf( esc_html__( 'Total votes: %d', 'snax' ), (int) snax_get_vote_count( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Upvotes: %d', 'snax' ), (int) snax_get_upvote_count( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Upvotes percentage: %f%%', 'snax' ), (float) snax_get_upvotes_percentage( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Downvotes: %d', 'snax' ), (int) snax_get_downvote_count( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Downvotes percentage: %f%%', 'snax' ), (float) snax_get_downvotes_percentage( $post ) ); ?></p>
			</div>
		</div>
		<?php
	}
}

/**
 * Render upvote/downvote weekly  box
 *snax_mod_render_upvote_link_weeks
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 * @param string      $class                CSS class.
 */
function snax_mod_render_voting_box_weeks( $post = null, $wp_post_id = 0, $user_id = 0, $class = 'snax-voting-simple' ) {
	if ( snax_show_item_voting_box( $post ) ) {
		$post = get_post( $post );

		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		$final_class = array(
			'snax-voting',
		);
		$final_class = array_merge( $final_class, explode( ' ', $class ) );
		?>
		<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $final_class ) ); ?>">
			<?php
			$snax_class = array(
				'snax-voting-score'
			);

			$snax_voting_score = snax_get_voting_score( $post );
			if ( 0 === $snax_voting_score ) {
				$snax_class[] = 'snax-voting-score-0';
			}
			?>

			<div class="<?php echo implode( ' ', array_map( 'sanitize_html_class', $snax_class ) ); ?>">
				<div style="margin-bottom: 9px;">
					<?php
					printf( wp_kses_post( _n( '<strong>%d</strong> vote', '<strong>%d</strong> votes', (int) $snax_voting_score, 'snax' ) ), (int) $snax_voting_score );
					?>
				</div>
				<div style="margin-bottom: 9px;">
					<div class="snax-mod-voting-score-last">
						<strong><?php echo snax_mod_get_item_votes_amount_week( $post, $wp_post_id, true ) ?></strong><br>Last Week
					</div>
					<div class="snax-mod-voting-score-current">
						<strong><?php echo snax_mod_get_item_votes_amount_week( $post, $wp_post_id ) ?></strong><br>This Week
					</div>
				</div>
			</div>

			<?php
			// TODO - Add post ID to Data Attr
			if ( snax_show_item_upvote_link( $post ) ) :
				//snax_render_upvote_link( $post, $user_id );
				snax_mod_render_upvote_link( $post, $wp_post_id, $user_id );
			endif;
			?>

			<div class="snax-voting-details">
				<p><?php printf( esc_html__( 'Total votes: %d', 'snax' ), (int) snax_get_vote_count( $post ) ); ?></p>
			</div>
		</div>
		<?php
	}
}

function snax_mod_get_item_votes_amount( $item_id, $wp_post_id = 0, $last_week = false ) {
	global $wp_query;
	global $wpdb;
	if ( empty( $wp_post_id ) ) {
		$wp_post_id = $wp_query->post->ID;
		if ( empty( $wp_post_id ) ) {
			$wp_post_url = wp_get_referer();
			$wp_post_id = (int) url_to_postid( $wp_post_url );
		}
	}
		
	
	// Database table name.
	$table_name = $wpdb->prefix . 'snax_votes';

	$prep = '';
	if ( $last_week ) { // Prior Week Total Amount.
		$prep = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d AND wp_post_id = %d AND date < DATE_SUB(NOW(), INTERVAL 1 WEEK)", $item_id, $wp_post_id );
		//$prep = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d AND wp_post_id = %d AND date BETWEEN DATE_SUB(now(), INTERVAL 2 WEEK) AND DATE_SUB(now(), INTERVAL 1 WEEK)", $item_id, $wp_post_id );
	} else { // Total Amount.
		$prep = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d AND wp_post_id = %d", $item_id, $wp_post_id );
	}

	// Array/Rows of votes.
	$votes = $wpdb->get_results( $prep );

	//$votes = snax_mod_get_item_votes_week( $item_id, $wp_post_id, $last_week );

	// Get amount.
	$vote_count = count( $votes );

	// Return amount.
	return $vote_count;
}

function snax_mod_get_item_votes_amount_week( $item, $wp_post_id = 0, $last_week = false ) {
	global $wp_query;
	global $wpdb;
	// Get current/root post ID.
	if ( empty( $wp_post_id ) ) {
		$wp_post_id = $wp_query->post->ID;
		if ( empty( $wp_post_id ) ) {
			$wp_post_url = wp_get_referer();
			$wp_post_id = (int) url_to_postid( $wp_post_url );
		}
	}
		

	$item_id = $item->ID;

	// Database table name.
	$table_name = $wpdb->prefix . 'snax_votes';

	$prep = '';
	if ( $last_week ) { // Prior Week.
		$prep = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d AND wp_post_id = %d AND date BETWEEN DATE_SUB(now(), INTERVAL 2 WEEK) AND DATE_SUB(now(), INTERVAL 1 WEEK)", $item_id, $wp_post_id );
	} else { // Current Week.
		$prep = $wpdb->prepare( "SELECT * FROM $table_name WHERE post_id = %d AND wp_post_id = %d AND date > DATE_SUB(NOW(), INTERVAL 1 WEEK)", $item_id, $wp_post_id );
	}

	// Array/Rows of votes.
	$votes = $wpdb->get_results( $prep );

	// Get amount.
	$vote_count = count( $votes );

	// Return amount.
	return $vote_count;
}

// UNUSED
function snax_mod_get_item_votes_rank( $item_id, $wp_post_id, $last_week = false ) {
	
	
	snax_mod_get_item_votes_week( $item_id, $wp_post_id, $last_week = false );
	snax_mod_get_item_votes_week( $item_id, $wp_post_id, $last_week = true );
	//get amount.
	
	//return amount
}
/**
 * Return item description
 *
 * @return string
 */
function snax_mod_item_description() {
	$content = '';
	$length = 144;
	if ( is_singular( snax_get_item_post_type() ) ) {
		$content = '%%SNAX_ITEM_DESCRIPTION%%';
	} else {
		$content = get_the_content();
		$content = snax_strip_embed_url_from_embed_content( $content );
	}
	//$encoding = mb_internal_encoding();
	//$content = mb_substr( $content, 0, $length, $encoding );
	$content = substr( $content, 0, $length );
	
	if ( ' ' !== substr( $content, -1, 1 ) ) {
		$content = substr( $content, 0, strrpos( $content, ' ' ) );
	}
	$content .= '...';
	$content = wpautop( wp_kses_post( $content ) );
	echo $content;
}

/**
 * Output admin links for item
 *
 * @param array $args See {@link snax_get_item_admin_links()}.
 */
function snax_mod_render_item_action_links( $args = array() ) {
	$links = snax_mod_item_action_links( $args );

	echo filter_var( $links );
}

/**
 * Return admin links for item
 *
 * @param array $args This function supports these arguments (
 *  - before: Before the links
 *  - after: After the links
 *  - sep: Links separator
 *  - links: item admin links array
 * ).
 *
 * @return string
 */
function snax_mod_item_action_links( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'before' => '<div class="snax-actions snax-mod-actions"><a href="#" class="snax-actions-toggle snax-mod-actions-toggle">'. esc_html__( 'More', 'snax' ) .'</a><ul class="snax-action-links"><li>',
		'after'  => '</li></ul></div>',
		'sep'    => '</li><li>',
		'links'  => array(),
	) );

	$args = apply_filters( 'snax_item_action_links_args', $args );

	if ( empty( $args['links'] ) ) {
		$args['links'] = apply_filters( 'snax_item_admin_links', array(
			'edit'      => snax_item_edit_link(),
			'delete'    => snax_item_delete_link(),
			'report'    => snax_item_report_link(),
			'featured'  => snax_item_featured_link(),
		) );
	}

	// Prepare output.
	$out   = '';
	$links = implode( $args['sep'], array_filter( $args['links'] ) );

	if ( strlen( $links ) ) {
		$out = $args['before'] . $links . $args['after'];
	}

	return apply_filters( 'snax_get_item_admin_links', $out, $args );
}

function snax_mod_render_rank() {
	$html = '';
	$divs = array ();
	$position = array(
		'current' => intval( snax_mod_get_item_rank_position( null, false ) ),
		'last'    => intval( snax_mod_get_item_rank_position( null, true ) ),
	);
	
	foreach ( $position as $key => $pos_value ) {

		if ( snax_show_item_position() ) {
			$divs[ $key ] = '<div class="snax-mod-item-rank-position-' . $key . '"><div>';
			$suffix_arr = array( 'th', 'st','nd','rd','th','th','th','th','th','th' );

			if ( 'last' === $key ) {
				$divs['last'] .= '<u>Last</u>: ';
			}
			
			
			if ( ( ( $pos_value % 100 ) >= 11 ) && ( ( $pos_value % 100 ) <= 13 ) ) {
				$divs[ $key ]  .= $pos_value . 'th';
			} else if ( 0 !== $pos_value ) {
				$divs[ $key ]  .= $pos_value . $suffix_arr[ $pos_value % 10 ];
			} else if ( 'last' === $key ){
				$divs[ $key ]  .= 'NA';
			} else {
				$divs[ $key ]  .= 'ERROR in snax_mod_render_rank()';
			}
			

			$divs[ $key ]  .= '</div></div>';
		}
	}

	
	//snax_mod_status_img();
	$divs['status'] = '<div class="snax-mod-item-rank-status">';
	if ( 0 === $position['last'] ) {
		$divs['status'] .= 'NEW';
	} elseif ( $position['current'] < $position['last'] ) {
		$divs['status'] .= 'UP';
	} elseif ( $position['current'] > $position['last'] ) {
		$divs['status'] .= 'DOWN';
	} else {
		$divs['status'] .= 'SAME';
	}
	$divs['status'] .= '</div>';
	
	//$html .= '<div class="snax-mod-item-rank-position">';
	
	
	//$html .= '</div>';
	$html .= $divs['current'];
	$html .= $divs['status'];
	$html .= $divs['last'];
	
	$html = wpautop( wp_kses_post( $html ) );
	return $html;
}



function snax_mod_get_item_rank_position( $item = null, $last_week = false ) {
	$item    = get_post( $item );
	// TODO - Change Assigned Page/Post ( Post_Parent ) to allow multiple IDs.
	// This allows single items to be place on multiple pages. This isn't part
	//   of the current scope, but is a possible future project.
	$post_id = $item->post_parent;

	
	//global $wp_query;
	//$wp_post_id = $wp_query->post->ID;
	//if ( empty( $wp_post_id ) ) {
	//	$wp_post_url = wp_get_referer();
	//	$wp_post_id = (int) url_to_postid( $wp_post_url );
	//}
	
	// Shouldn't happen.
	$position = - 1;
	$items_arr = array();
	
	if ( true === $last_week ) { 
		$items_arr = snax_mod_get_items_ids( $post_id );
		$item_totals = array();
		foreach ( $items_arr as $key => $item_id ) {
			$item_totals[ $item_id ] = snax_mod_get_item_votes_amount( $item_id, $post_id, $last_week );
		}
		
		// If item has votes (last week) then search list position.
		if ( 0 !== $item_totals[ $item->ID ] ) {
			rsort( $item_totals, SORT_NUMERIC );
			$position = array_search( $item->ID, array_keys( $item_totals ) );
		}
		
		$position = $position + 1;
		
	} else { // Current Week
		// Snax function uses WP_Query instead to grab total amount from post->meta_data
		$items_arr = snax_mod_get_items_ids( $post_id );
		$found_index = array_search( $item->ID, $items_arr );

		if ( false !== $found_index ) {
			// Array keys start from 0. DUH!!!
			$position = $found_index + 1; 
		}
	}

	return $position;
}

function snax_mod_get_items_ids( $post_id = 0 ) {
	$post = get_post( $post_id );

	$args = array();
	$args = wp_parse_args( $args, array(
		'post_parent' => $post->ID,
	) );

	$query_args = snax_get_items_query_args( $args );

	$items = get_posts( $query_args );
	
	//$items = snax_get_items( $post_id );

	$ids = wp_list_pluck( $items, 'ID' );

	return $ids;
}

/**
 * Returns default items query args
 *
 * @param array $args Optional.
 *
 * @return array
 */
function snax_mod_get_items_query_args( $args = array() ) {
	$defaults = array(
		'post_type'      => snax_get_item_post_type(),
		'post_parent'    => 0,
		'orderby'        => array(
			'meta_value_num' => 'DESC',
			'menu_order'     => 'ASC',
			'post_date'      => 'ASC',
		),
		'meta_key'       => '_snax_vote_score',
		'posts_per_page' => - 1,
	);

	$args = wp_parse_args( $args, $defaults );

	return apply_filters( 'snax_item_query_args', $args );
}

// This gets the vote/item WP_Post object.
// Votes are assigned by using the post parent, which restricts the use of
//     assigning multiple posts a vote can appear on. 
/**
 * Return all items assigned to post
 *
 * @param int|WP_Post $post_id          Optional. Post ID or WP_Post object. Default global $post.
 * @param array       $args             Extra WP_Query arguments.
 *
 * @return array
 */
function snax_mod_get_items( $post_id = 0, $args = array() ) {
	$post = get_post( $post_id );

	$args = wp_parse_args( $args, array(
		// TODO Change to carry multiple values
		'post_parent' => $post->ID,
	) );

	/* ***** snax_get_items_query_args( $args ) ***** */
	//$query_args = snax_get_items_query_args( $args );
	$defaults = array(
		'post_type'      => snax_get_item_post_type(),
		'post_parent'    => 0,
		'orderby'        => array(
			'meta_value_num' => 'DESC',
			'menu_order'     => 'ASC',
			'post_date'      => 'ASC',
		),
		'meta_key'       => '_snax_vote_score',
		'posts_per_page' => - 1,
	);

	$args = wp_parse_args( $args, $defaults );

	$query_args = apply_filters( 'snax_item_query_args', $args );
	/* ********************************************** */


	$items = get_posts( $query_args );

	return $items;
}

/**
 * Set up items query
 *
 * @param string $parent_format         Format of item parent (image | embed | gallery | list).
 * @param string $origin                Origin type ( all | contribution | post ).
 * @param array  $args                  WP Query extra args.
 *
 * @return WP_Query
 */
function snax_mod_get_items_query( $args = array() ) {
	global $wp_rewrite;
	
	$default_args = array(
		'post_type'      => snax_get_item_post_type(),
		'post_parent'    => 0,
		'orderby'        => array(
			
			'meta_value_num' => 'DESC',
			'menu_order'     => 'ASC',
			'post_date'      => 'ASC',
		),
		'meta_key'       => '_snax_vote_score',
		'posts_per_page' => snax_get_items_per_page(),
		'paged'          => snax_get_paged(),
		'max_num_pages'  => false,
		//'meta_query'     => array(
		//	'relation'   => 'AND',
		//	array(
		//		'key'     => '_snax_parent_format',
		//		'value'   => $parent_format,
		//		'compare' => '=',
		//	),
		//	array(
		//		'key'     => '_snax_origin',
		//		'value'   => 'all',//$origin,
		//		'compare' => '=',
		//	),
		//),
	);


	// We get author items, not items assigned to a particular post.
	//unset( $r['post_parent'] );

	$r = wp_parse_args( $args, $default_args );

	// Make query.
	$query = new WP_Query( $r );

	// Limited the number of pages shown.
	if ( ! empty( $r['max_num_pages'] ) ) {
		$query->max_num_pages = $r['max_num_pages'];
	}

	// If no limit to posts per page, set it to the current post_count.
	if ( - 1 === $r['posts_per_page'] ) {
		$r['posts_per_page'] = $query->post_count;
	}

	// Add pagination values to query object.
	$query->posts_per_page = $r['posts_per_page'];
	$query->paged          = $r['paged'];

	// Only add pagination if query returned results.
	if ( ( (int) $query->post_count || (int) $query->found_posts ) && (int) $query->posts_per_page ) {

		// Limit the number of topics shown based on maximum allowed pages.
		if ( ( ! empty( $r['max_num_pages'] ) ) && $query->found_posts > $query->max_num_pages * $query->post_count ) {
			$query->found_posts = $query->max_num_pages * $query->post_count;
		}

		$base = add_query_arg( 'paged', '%#%' );

		$base = apply_filters( 'snax_items_pagination_base', $base, $r );

		// Pagination settings with filter.
		$pagination = apply_filters( 'snax_items_pagination', array(
			'base'      => $base,
			'format'    => '',
			'total'     => $r['posts_per_page'] === $query->found_posts ? 1 : ceil( (int) $query->found_posts / (int) $r['posts_per_page'] ),
			'current'   => (int) $query->paged,
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
			'mid_size'  => 1,
		) );

		// Add pagination to query object.
		$query->pagination_links = paginate_links( $pagination );

		// Remove first page from pagination.
		$query->pagination_links = str_replace( $wp_rewrite->pagination_base . "/1/'", "'", $query->pagination_links );
	}

	snax()->items_query = $query;

	return $query;
}

/**
 * Render HTML formatted link to upvote action
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 */
function snax_mod_render_upvote_link( $post = null, $wp_post_id = 0, $user_id = 0 ) {
	$link = snax_mod_get_upvote_link( $post, $wp_post_id, $user_id );

	echo wp_kses( $link, array(
		'a' => array(
			'href'                      => array(),
			'class'                     => array(),
			'title'                     => array(),
			'data-snax-mod-is-type'     => array(),
			'data-snax-mod-wp-post-id'  => array(),
			'data-snax-item-id'         => array(),
			'data-snax-author-id'       => array(),
			'data-snax-nonce'           => array(),
		),
	) );
}

/**
 * Return HTML formatted link to upvote action
 *
 * @param int|WP_Post $post                 Optional. Post ID or WP_Post object. Default is global `$post`.
 * @param int         $user_id              Options. User ID. Default is current user id.
 *
 * @return string
 */
function snax_mod_get_upvote_link( $post = null, $wp_post_id = 0, $user_id = 0 ) {
	$post = get_post( $post );

	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$classes = array(
		'snax-voting-upvote'
	);

	if ( snax_user_upvoted( $post->ID, $user_id ) ) {
		$classes[] = 'snax-user-voted';
	}

	$user = get_user_by( 'id', $user_id );

	// User with this id doesn't exist.
	if ( 0 !== $user_id && false === $user ) {
		return '';
	}

	// User exists.
	if ( $user ) {
		// Is logged-in but has no access.
		if ( $user->exists() && ! user_can( $user_id, 'snax_vote_items', $post->ID ) ) {
			return '';
		}

		// Is logged-out?
		if ( ! $user->exists() ) {
			$classes[] = 'snax-login-required';
		}
	} elseif ( snax_guest_voting_is_enabled() ) {
		// Guest can vote.
		$classes[] = 'snax-guest-voting';
	} else {
		// User not logged in.
		$classes[] = 'snax-login-required';
	}

	global $wp_query;
	$is_type = '';
	$is_type = (string) filter_input( INPUT_POST, 'snax_mod_is_type', FILTER_SANITIZE_STRING );
	
	if ( empty( $is_type ) && $wp_query->is_singular ) {
		$is_type = 'singular';
	} elseif ( $wp_query->is_archive ) {
		$is_type = 'archive';
	} elseif ( $wp_query->is_category || $wp_query->is_tag ) {
		$is_type = 'category';
	} elseif ( $wp_query->is_home ) {
		$is_type = 'home';
	}
	//global $post;
	//echo var_dump($post);data-snax-mod-is
	$link = sprintf(
		'<a href="#" class="' . implode( ' ', array_map( 'sanitize_html_class', $classes ) ) . '" title="%s" data-snax-mod-wp-post-id="%d" data-snax-item-id="%d" data-snax-author-id="%d" data-snax-nonce="%s" data-snax-mod-is-type="%s">%s</a>',
		__( 'Upvote', 'snax' ),
		$wp_post_id,
		$post->ID,
		$user_id,
		wp_create_nonce( 'snax-vote-item' ),
		$is_type,
		__( 'Upvote', 'snax' )
	);

	return $link;
}
