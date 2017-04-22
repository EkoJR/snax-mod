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
function eko_user_voted( $item_id, $user_id ) {
	return 0 !== eko_get_user_vote( $item_id, $user_id );
}

/**
 * Return user votes for an item
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
//unset($snax_get_user_vote);
function eko_get_user_vote( $item_id, $user_id ) {
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
}

function eko_get_votes_db( $arg = array() ) {
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
}

/**
 * True if user voted for an item this week
 *
 * @param int $item_id Item id.
 * @param int $user_id User id.
 *
 * @return bool
 */
function eko_user_voted_this_list_week( $item_id, $user_id, $wp_post_id ) {
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
