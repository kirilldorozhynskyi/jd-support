<?php

namespace JdSupport\Services;

/**
 * Gravity Forms Service
 *
 * @package JdSupport\Services
 */
class GravityFormsService
{
	/**
	 * Fix Gravity Forms database options
	 */
	public function fixGravityFormsOptions(): void
	{
		global $wpdb;

		$new_value = '';

		// Check if gform_pending_installation option exists and rename it
		$option_exists = $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(*) FROM $wpdb->options WHERE option_name = %s",
			'gform_pending_installation'
		));

		if ($option_exists) {
			$wpdb->update(
				$wpdb->options,
				['option_name' => 'rg_gforms_hideLicense'],
				['option_name' => 'gform_pending_installation']
			);
		}

		// Update rg_gforms_message option value
		$wpdb->update(
			$wpdb->options,
			['option_value' => $new_value],
			['option_name' => 'rg_gforms_message']
		);
	}

	/**
	 * Ensure Editors can view Gravity Forms entries (menu + entries list).
	 *
	 * This is intentionally narrow: it does not grant form editing permissions.
	 */
	public function ensureEditorCanViewEntries(): void
	{
		if (!function_exists('get_role')) {
			return;
		}

		$role = get_role('editor');
		if (!$role) {
			return;
		}

		$caps = apply_filters('jd_support/gravityforms_editor_caps', [
			'gravityforms_view_entries',
		]);

		if (!is_array($caps)) {
			return;
		}

		foreach ($caps as $cap) {
			if (!is_string($cap) || $cap === '') {
				continue;
			}
			$role->add_cap($cap);
		}
	}
}
