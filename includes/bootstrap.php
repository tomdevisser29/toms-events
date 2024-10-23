<?php
/**
 * Bootstraps the plugin to set it up for success.
 *
 * @package dev5
 */

/**
 * Show an admin notice if ACF is not installed and activated.
 *
 * @return void
 */
function dev5_events_needs_acf(): void {
	?>
	<div class="notice notice-warning">
		<p><?php esc_html_e( 'The dev5 Events plug-in needs the plug-in Advanced Custom Fields to be activated.', 'dev5' ); ?></p>
	</div>
	<?php
}

if ( ! class_exists( 'acf' ) ) {
	add_action( 'admin_notices', 'dev5_events_needs_acf', 10, 0 );
}

/**
 * Register the Event and Event Date post types.
 *
 * @return void
 */
function dev5_events_post_types(): void {
	register_post_type(
		'event',
		array(
			'label'               => __( 'Event', 'dev5' ),
			'description'         => __( 'The Event custom post type includes structured data and utilizes a hidden post type to display multiple event dates within a single event overview.', 'dev5' ),
			'public'              => true,
			'hierarchical'        => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'rest_base'           => 'event',
			'rest_namespace'      => 'dev5',
			'menu_position'       => null,
			'menu_icon'           => 'dashicons-calendar',
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'custom-fields', 'revisions', 'excerpt', 'autosave' ),
			'has_archive'         => true,
			'can_export'          => true,
			'delete_with_user'    => false,
		)
	);

	register_post_type(
		'event-date',
		array(
			'label'               => __( 'Event Date', 'dev5' ),
			'description'         => __( 'The Event Date custom post type is a hidden post type generated automatically when site editors add dates to an Event post. On the frontend, these dates are linked to their corresponding event.', 'dev5' ),
			'public'              => false,
			'hierarchical'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'rest_base'           => 'event-date',
			'rest_namespace'      => 'dev5',
			'menu_position'       => null,
			'menu_icon'           => 'dashicons-calendar',
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'custom-fields' ),
			'has_archive'         => false,
			'can_export'          => true,
			'delete_with_user'    => false,
		)
	);
}
add_action( 'init', 'dev5_events_post_types' );

/**
 * Create the local field groups for the Event and Event Date post types.
 *
 * @return void
 */
function dev5_events_add_local_field_groups(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_event',
			'title'                 => __( 'Event', 'dev5' ),
			'fields'                => array(
				array(
					'key'          => 'field_event_dates',
					'name'         => 'event_dates',
					'label'        => __( 'Dates', 'dev5' ),
					'type'         => 'repeater',
					'button_label' => __( 'Add new date', 'dev5' ),
					'sub_fields'   => array(
						array(
							'key'           => 'field_event_dates_date',
							'name'          => 'date',
							'label'         => __( 'Date', 'dev5' ),
							'type'          => 'date_picker',
							'return_format' => 'Ymd',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'event',
					),
				),
			),
			'position'              => 'acf_after_title',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_event_date',
			'title'                 => __( 'Event Date', 'dev5' ),
			'fields'                => array(
				array(
					'key'           => 'field_event_date',
					'name'          => 'event_date',
					'label'         => __( 'Date', 'dev5' ),
					'type'          => 'date_picker',
					'return_format' => 'Ymd',
					'readonly'      => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'event-date',
					),
				),
			),
			'position'              => 'acf_after_title',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'dev5_events_add_local_field_groups' );
