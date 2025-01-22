<?php
/**
 * Plugin Name: toms Events
 * Description: This plugin introduces a custom Event post type, enabling you to display the same event multiple times in a query for different dates. It achieves this by utilizing a hidden post type for managing event dates and requires Advanced Custom Fields (ACF) for setup.
 * Author: Tom de Visser
 * Version: 1.0.0
 *
 * @package toms
 */

defined( 'ABSPATH' ) || die;

define( 'TOMS_EVENTS_VERSION', '1.0.0' );

define( 'TOMS_EVENTS_PLUGIN', __DIR__ );
define( 'TOMS_EVENTS_INCLUDES', TOMS_EVENTS_PLUGIN . '/includes' );

require_once TOMS_EVENTS_INCLUDES . '/bootstrap.php';
require_once TOMS_EVENTS_INCLUDES . '/event-date-sync.php';
