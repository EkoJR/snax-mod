<?php

class Snax_Mod_Core { 
	
	public function hook_activation_new_db_UNUSED() {
		global $wpdb;
		
		$table_name = '';
		$table_name = $wpdb->prefix . $this->table_name;
		// Create table only if needed.
		if ( $wpdb->get_var("SHOW TABLES LIKE $this->table_name") !== $this->table_name ) {
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $this->table_name (
				vote_id bigint(20) NOT NULL auto_increment,
				post_id bigint(20) NOT NULL,
				wp_post_id bigint(20) NOT NULL
				vote int(2) NOT NULL,
				author_id bigint(20) NOT NULL default '0',
				author_ip varchar(100) NOT NULL default '',
				author_host varchar(200) NOT NULL,
				date datetime NOT NULL default '0000-00-00 00:00:00',
				date_gmt datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY (vote_id),
				KEY post_id (post_id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			update_option( 'snax_mod_votes_table_version', '1.6.1' );
			
			$snax_table_name = $wpdb->prefix . 'snax_votes';
			$votes_db = $wpdb->get_results( "SELECT * FROM $snax_table_name ORDER BY vote_id DESC" );
			
			$votes_db_mod = $this->update_votes_db_sql_column( $votes_db );
			
			
			//$votes_db = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY vote_id DESC" );
			foreach( $votes_db_mod as $key => $vote_mod ) {
				try {
					$affected_rows = $wpdb->replace(
						$table_name,
						array(
							'vote_id'     => (int) $vote_mod['vote_id'],
							'post_id'     => (int) $vote_mod['post_id'],
							'wp_post_id'  => (int) $vote_mod['wp_post_id'],
							'vote'        => (int) $vote_mod['vote'],
							'author_id'   => (int) $vote_mod['author_id'],
							'author_ip'   => (string) $vote_mod['author_ip'],
							'author_host' => (string) $vote_mod['author_host'],
							'date'        => (string) $vote_mod['date'],
							'date_gmt'    => (string) $vote_mod['date_gmt'],
							
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
				} catch (Exception $e) {
					$a01 = $wpdb->query( $wpdb->prepare( "ALTER TABLE $table_name DROP COLUMN wp_post_id" ) );
					echo 'Caught exception: ',  $e->getMessage(), "\n";
					var_dump($e->getMessage());
				}
					
				if ( false === $affected_rows ) {
					$a01 = $wpdb->query( $wpdb->prepare( "ALTER TABLE $table_name DROP COLUMN wp_post_id" ) );
					return new WP_Error( 'snax_mod_insert_vote_failed', esc_html__( 'Could not insert new vote into the database!', 'snax_mod' ) );
				}
			}
		}
	}
	
	/**
	 * Update voting stats (upvotes, downvotes, total, score)
	 * \plugins\snax\includes\votes\functions.php
	 *
	 * @param int   $item_id            Item id.
	 * @param array $meta               Current meta value.
	 *
	 * @return bool
	 */
	/*private function snax_update_votes_metadata( $item_id = 0, $meta = array() ) {
		$post = get_post( $item_id );

		if ( empty( $meta ) ) {
			$meta = snax_generate_votes_metadata( $post );
		}

		if ( empty( $meta ) ) {
			return false;
		}

		update_post_meta( $post->ID, '_snax_upvote_count', $meta['upvotes'] );
		update_post_meta( $post->ID, '_snax_downvote_count', $meta['downvotes'] );
		update_post_meta( $post->ID, '_snax_vote_count', $meta['total'] );
		update_post_meta( $post->ID, '_snax_vote_score', $meta['score'] );

		return true;
	}*/
	
	
	
}
