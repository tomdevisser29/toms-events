<?php
/**
 * Plugin Name: dev5 Events
 * Description: This plugin introduces a custom Event post type, enabling you to display the same event multiple times in a query for different dates. It achieves this by utilizing a hidden post type for managing event dates and requires Advanced Custom Fields (ACF) for setup.
 * Author: Tom de Visser
 * Version: 1.0.0
 *
 * @package dev5_events
 */

defined( 'ABSPATH' ) || die;

define( 'DEV5_EVENTS_VERSION', '1.0.0' );

define( 'DEV5_EVENTS_PLUGIN', __DIR__ );
define( 'DEV5_EVENTS_INCLUDES', DEV5_EVENTS_PLUGIN . '/includes' );

require_once DEV5_EVENTS_INCLUDES . '/bootstrap.php';
require_once DEV5_EVENTS_INCLUDES . '/event-date-sync.php';
