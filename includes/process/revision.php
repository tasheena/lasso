<?php

namespace lasso\process;


use lasso\internal_api\api_action;

class revision implements api_action {

	/**
	 * Array of revisions for a post
	 *
	 * @access protected
	 *
	 * @since 0.9.5
	 *
	 * @var array
	 */
	protected static $revisions = array();

	/**
	 * Get revisions for a post
	 *
	 * @since 0.9.5
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function get( $data ) {
		$args = array();
		if ( isset( $data[ 'limit' ] ) ) {
			$args[ 'posts_per_page' ] = $data[ 'limit' ];
		}else{
			$args[ 'posts_per_page' ] = 6; // we start at revision 0
		}

		$revisions = wp_get_post_revisions( $data[ 'postid' ], $args  );
		if ( is_array( $revisions )  && ! empty( $revisions )  ) {
			self::set_revisions( $data[ 'postid' ], $revisions );
		}

		return self::$revisions;
	}

	/**
	 * Set the revisions property
	 *
	 * @access protected
	 *
	 * @since 0.9.5
	 *
	 * @param int $id The post ID to get the revisions for
	 * @param obj $revisions The revisions for this post
	 */
	protected static function set_revisions( $id, $revisions ) {

		array_walk( $revisions, function ( $post, $i ) {
			self::$revisions[] = array(
				'post_content' => $post->post_content,
				'post_title' => $post->post_title,
				'modified_time' => mysql2date('g:i a', $post->post_modified),
				'modified_date' => mysql2date('F j, Y', $post->post_modified)
			);
		} );

	}

	/**
	 * The keys required for the actions of this class.
	 *
	 * @since     0.9.5
	 *
	 * @return array Array of keys to pull from $_POST per action and their sanitization callback
	 */
	public static function params(){
		$params[ 'process_revision_get' ] = array(
			'postid'    => 'absint',
			'limit'     => 'absint'
		);

		return $params;
	}

	/**
	 * Additional auth callbacks to check.
	 *
	 * @since     0.9.5
	 *
	 * @return array Array of additional functions to use to authorize action.
	 */
	public static function auth_callbacks() {
		$params[ 'process_revision_get' ] = array(
			'lasso_user_can'
		);

		return $params;

	}

}
