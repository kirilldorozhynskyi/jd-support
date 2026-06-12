<?php

namespace JdSupport\Services;

use JdSupport\Core\Config\ConfigManager;
use JdSupport\Core\Container\Container;

/**
 * Content Lock Service
 *
 * @package JdSupport\Services
 */
class ContentLockService
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var ConfigManager
	 */
	private $config;

	/**
	 * @var array|null
	 */
	private $blockedPrimitiveCaps = null;

	/**
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
	}

	/**
	 * Deny direct post edit and delete permissions for locked post types.
	 */
	public function blockPostMetaCapabilities(array $caps, string $cap, int $userId, array $args): array
	{
		if (!$this->isLocked() || !in_array($cap, ['edit_post', 'delete_post'], true)) {
			return $caps;
		}

		$postId = isset($args[0]) ? (int) $args[0] : 0;
		if ($postId <= 0) {
			return $caps;
		}

		$post = get_post($postId);
		if (!$post || !$this->isLockedPostType($post->post_type)) {
			return $caps;
		}

		return ['do_not_allow'];
	}

	/**
	 * Deny post type primitive capabilities used by editors, publishers, and uploaders.
	 */
	public function blockContentCapabilities(array $allcaps, array $caps, array $args, $user): array
	{
		if (!$this->isLocked()) {
			return $allcaps;
		}

		$requestedCap = isset($args[0]) && is_string($args[0]) ? $args[0] : '';
		$capsToCheck = array_unique(array_filter(array_merge([$requestedCap], $caps)));

		foreach ($capsToCheck as $cap) {
			if (!is_string($cap) || !$this->isBlockedPrimitiveCap($cap)) {
				continue;
			}

			$allcaps[$cap] = false;
		}

		return $allcaps;
	}

	/**
	 * Remove edit actions from post list tables.
	 */
	public function removePostRowActions(array $actions, $post): array
	{
		if (!$this->isLocked() || !$post || !$this->isLockedPostType($post->post_type)) {
			return $actions;
		}

		unset($actions['edit'], $actions['inline hide-if-no-js'], $actions['trash'], $actions['delete']);

		return $actions;
	}

	/**
	 * Remove content creation shortcuts from the admin bar.
	 */
	public function removeAdminBarContentActions($wpAdminBar): void
	{
		if (!$this->isLocked()) {
			return;
		}

		$wpAdminBar->remove_node('new-content');
	}

	/**
	 * Stop users who manually open post editing URLs.
	 */
	public function blockEditorScreens(): void
	{
		if (!$this->isLocked() || !is_admin()) {
			return;
		}

		global $pagenow;

		if ($pagenow !== 'post.php' && $pagenow !== 'post-new.php') {
			return;
		}

		$postType = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : 'post';
		if (isset($_GET['post'])) {
			$post = get_post((int) $_GET['post']);
			$postType = $post ? $post->post_type : $postType;
		}

		if (!$this->isLockedPostType($postType)) {
			return;
		}

		wp_die(
			esc_html__('Content editing is locked by justDev Support to prevent site changes.', 'jd_support'),
			esc_html__('Content editing locked', 'jd_support'),
			['response' => 403],
		);
	}

	/**
	 * Show an admin notice while the lock is active.
	 */
	public function showAdminNotice(): void
	{
		if (!$this->isLocked() || !is_admin()) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__('Content editing is locked by justDev Support. Disable the Content Edit Lock option to allow changes.', 'jd_support');
		echo '</p></div>';
	}

	/**
	 * Block REST API mutations for locked WordPress content endpoints.
	 */
	public function blockRestContentMutations($result, $server, $request)
	{
		if (!$this->isLocked()) {
			return $result;
		}

		$method = strtoupper($request->get_method());
		if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
			return $result;
		}

		$route = $request->get_route();
		if (!$this->isLockedRestRoute($route)) {
			return $result;
		}

		return new \WP_Error(
			'jd_support_content_locked',
			__('Content editing is locked by justDev Support to prevent site changes.', 'jd_support'),
			['status' => 403],
		);
	}

	/**
	 * Check if content locking is enabled.
	 */
	private function isLocked(): bool
	{
		return $this->config->isEnabled('content_lock');
	}

	/**
	 * Check if the post type should be protected.
	 */
	private function isLockedPostType(string $postType): bool
	{
		return in_array($postType, $this->getLockedPostTypes(), true);
	}

	/**
	 * Get post types protected by the lock.
	 */
	private function getLockedPostTypes(): array
	{
		$postTypes = [];
		$postTypeObjects = get_post_types([], 'objects');
		if (!is_array($postTypeObjects)) {
			return [];
		}

		foreach ($postTypeObjects as $postType => $postTypeObject) {
			if (!$postTypeObject->public && !$postTypeObject->show_ui && !$postTypeObject->show_in_rest) {
				continue;
			}

			$postTypes[] = $postType;
		}

		return (array) apply_filters('jd_support/content_lock_post_types', $postTypes);
	}

	/**
	 * Check if a primitive capability should be denied while locked.
	 */
	private function isBlockedPrimitiveCap(string $cap): bool
	{
		if ($this->blockedPrimitiveCaps === null) {
			$this->blockedPrimitiveCaps = $this->getBlockedPrimitiveCaps();
		}

		return in_array($cap, $this->blockedPrimitiveCaps, true);
	}

	/**
	 * Collect editable capabilities from locked post types.
	 */
	private function getBlockedPrimitiveCaps(): array
	{
		$capNames = [
			'create_posts',
			'delete_others_posts',
			'delete_posts',
			'delete_private_posts',
			'delete_published_posts',
			'edit_others_posts',
			'edit_posts',
			'edit_private_posts',
			'edit_published_posts',
			'publish_posts',
		];
		$blockedCaps = [];

		foreach ($this->getLockedPostTypes() as $postType) {
			$postTypeObject = get_post_type_object($postType);
			if (!$postTypeObject || !isset($postTypeObject->cap)) {
				continue;
			}

			foreach ($capNames as $capName) {
				if (!empty($postTypeObject->cap->{$capName})) {
					$blockedCaps[] = $postTypeObject->cap->{$capName};
				}
			}
		}

		$blockedCaps[] = 'upload_files';

		return array_values(array_unique((array) apply_filters('jd_support/content_lock_blocked_caps', $blockedCaps)));
	}

	/**
	 * Determine if a REST route targets locked content.
	 */
	private function isLockedRestRoute(string $route): bool
	{
		foreach ($this->getLockedPostTypes() as $postType) {
			$postTypeObject = get_post_type_object($postType);
			if (!$postTypeObject || !$postTypeObject->show_in_rest || empty($postTypeObject->rest_base)) {
				continue;
			}

			if (preg_match('#^/wp/v2/' . preg_quote($postTypeObject->rest_base, '#') . '(/|$)#', $route)) {
				return true;
			}
		}

		return false;
	}
}
