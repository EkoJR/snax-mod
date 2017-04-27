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
function snax_mod_render_voting_box( $post = null, $user_id = 0, $class = 'snax-voting-simple' ) {
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
						<strong><?php echo snax_mod_get_item_votes_amount_week( $post, true ) ?></strong><br>Last Week
					</div>
					<div class="snax-mod-voting-score-current">
						<strong><?php echo snax_mod_get_item_votes_amount_week( $post ) ?></strong><br>This Week
					</div>
				</div>
			</div>

			<?php
			if ( snax_show_item_upvote_link( $post ) ) :
				snax_render_upvote_link( $post, $user_id );
			endif;
			?>

			<?php
			// Removed Downvoting
			//if ( snax_show_item_downvote_link( $post ) ) :
				//snax_render_downvote_link( $post, $user_id );
			//endif;
			?>

			<div class="snax-voting-details">
				<p><?php printf( esc_html__( 'Total votes: %d', 'snax' ), (int) snax_get_vote_count( $post ) ); /*?></p>

				<p><?php printf( esc_html__( 'Upvotes: %d', 'snax' ), (int) snax_get_upvote_count( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Upvotes percentage: %f%%', 'snax' ), (float) snax_get_upvotes_percentage( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Downvotes: %d', 'snax' ), (int) snax_get_downvote_count( $post ) ); ?></p>

				<p><?php printf( esc_html__( 'Downvotes percentage: %f%%', 'snax' ), (float) snax_get_downvotes_percentage( $post ) ); */?></p>
			</div>
		</div>
		<?php
	}
}

function snax_mod_get_item_votes_amount( $item_id, $last_week = false ) {
	global $wp_query;
	global $wpdb;
	$wp_post_id = $wp_query->post->ID;
	if ( empty( $wp_post_id ) ) {
		$wp_post_url = wp_get_referer();
		$wp_post_id = (int) url_to_postid( $wp_post_url );
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

function snax_mod_get_item_votes_amount_week( $item, $last_week = false ) {
	global $wp_query;
	global $wpdb;
	// Get current/root post ID.
	$wp_post_id = $wp_query->post->ID;
	if ( empty( $wp_post_id ) ) {
		$wp_post_url = wp_get_referer();
		$wp_post_id = (int) url_to_postid( $wp_post_url );
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
		foreach ( $items_arr as $key => $item_ID ) {
			$item_totals[ $item_ID ] = snax_mod_get_item_votes_amount( $item_ID, $last_week );
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
