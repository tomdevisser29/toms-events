<?php
/**
 * Handles synchronization between Event posts and their associated Event Date posts.
 *
 * When dates are added, updated, or removed from an Event's repeater field,
 * this automatically creates, updates, or deletes corresponding Event Date posts.
 * Each Event Date post maintains a reference to its parent Event through post meta.
 *
 * @package toms
 */

/**
 * Sync event dates when an event is saved
 *
 * @param int|string $post_id The ID of the post being edited.
 * @return void
 */
function sync_event_dates( $post_id ): void {
	// Run standard save checks.
	if ( ! should_proceed_with_save( $post_id, 'event' ) ) {
		return;
	}

	// Get all dates from the repeater.
	$dates = get_field( 'field_event_dates', $post_id );

	// Get all existing event-date posts for this event.
	$existing_date_posts = get_linked_event_dates( $post_id );

	// If there are no dates in the repeater, delete all existing event-date posts.
	if ( empty( $dates ) ) {
		delete_event_date_posts( $existing_date_posts );
		return;
	}

	// Create or update event-date posts for each date in the repeater.
	foreach ( $dates as $date_row ) {
		$date                = (string) $date_row['date'];
		$existing_date_posts = create_or_update_event_date( $post_id, $date, $existing_date_posts );
	}

	// Delete any remaining event-date posts that weren't matched.
	delete_event_date_posts( $existing_date_posts );
}
add_action( 'acf/save_post', 'sync_event_dates', 20 );

/**
 * Delete all linked event-date posts when an event is deleted.
 *
 * @param int $post_id The id of the post being deleted.
 */
function delete_linked_event_dates( $post_id ) {
	if ( get_post_type( $post_id ) !== 'event' ) {
		return;
	}

	$linked_dates = get_linked_event_dates( $post_id );
	delete_event_date_posts( $linked_dates );
}
add_action( 'before_delete_post', 'delete_linked_event_dates' );

/**
 * Helper function to verify if we should proceed with a save_post action.
 *
 * @param int    $post_id The ID of the post being saved.
 * @param string $post_type The expected post type (optional).
 * @param bool   $check_ajax Whether to skip during AJAX calls.
 * @return bool|int Returns false if checks fail, post ID if checks pass.
 */
function should_proceed_with_save( $post_id, $post_type = null, $check_ajax = true ): bool|int {
	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	// If this is a revision, don't proceed.
	if ( wp_is_post_revision( $post_id ) ) {
		return false;
	}

	// Check if this is a quick edit/inline save.
	if ( $check_ajax && isset( $_POST['action'] ) && 'inline-save' === $_POST['action'] ) {
		// Verify quick edit nonce.
		if ( ! isset( $_POST['_inline_edit'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_inline_edit'] ), 'inlineeditnonce' ) ) {
			return false;
		}
	}

	// Verify user permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	// If a post type is specified, verify it.
	if ( null !== $post_type && get_post_type( $post_id ) !== $post_type ) {
		return false;
	}

	return $post_id;
}

/**
 * Save reference to parent event in event-date post
 *
 * @param int $post_id The id of the event date being updated.
 * @param int $parent_event_id The id of the parent event.
 * @return void
 */
function save_event_date_parent( $post_id, $parent_event_id ): void {
	update_post_meta( $post_id, 'parent_event', $parent_event_id );
}

/**
 * Get all event-date posts linked to a specific event
 *
 * @param int $event_id The event id of the parent event.
 * @return WP_Post[]|int[]
 */
function get_linked_event_dates( $event_id ): array {
	$args = array(
		'post_type'      => 'event-date',
		'posts_per_page' => -1,
		'meta_query'     => array(
			array(
				'key'   => 'parent_event',
				'value' => $event_id,
			),
		),
	);

	return get_posts( $args );
}

/**
 * Create or update event-date post for a specific date
 *
 * @param int|string      $event_id The ID of the event being edited.
 * @param string          $date The date of the event.
 * @param WP_Post[]|int[] $existing_date_posts The event-date posts that are connected to the event being edited.
 * @return WP_Post[]|int[] $existing_date_posts
 */
function create_or_update_event_date( int|string $event_id, string $date, array $existing_date_posts ) {
	// Check if we already have a post for this date.
	$matching_post = null;
	foreach ( $existing_date_posts as $key => $date_post ) {
		if ( get_field( 'field_event_date', $date_post->ID ) === $date ) {
			$matching_post = $date_post;

			// Remove from array so we know which ones to delete later.
			unset( $existing_date_posts[ $key ] );
			break;
		}
	}

	if ( ! $matching_post ) {
		// Create new event-date post.
		$post_data = array(
			'post_title'  => get_the_title( $event_id ) . ' - ' . $date,
			'post_type'   => 'event-date',
			'post_status' => 'publish',
		);

		$new_post_id = wp_insert_post( $post_data );

		// Set the date field.
		update_field( 'field_event_date', $date, $new_post_id );

		// Save reference to parent event.
		save_event_date_parent( $new_post_id, $event_id );
	}

	return $existing_date_posts;
}

/**
 * Delete specified event-date posts.
 *
 * @param WP_Post[]|int[] $posts The event-date posts to delete.
 */
function delete_event_date_posts( array $posts ) {
	foreach ( $posts as $date_post ) {
		wp_delete_post( $date_post->ID, true );
	}
}
