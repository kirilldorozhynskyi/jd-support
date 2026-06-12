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
	 * Constructor
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
	}

	/**
	 * Stop saves before WordPress writes post data.
	 */
	public function blockPostSaveData(array $data, array $postarr, array $unsanitizedPostarr = [], bool $update = false): array
	{
		if (!$this->isLocked()) {
			return $data;
		}

		$postType = isset($data['post_type']) ? (string) $data['post_type'] : '';
		if ($postType === '' && isset($postarr['post_type'])) {
			$postType = (string) $postarr['post_type'];
		}

		if (!$this->isLockedPostType($postType)) {
			return $data;
		}

		if (!$update && isset($data['post_status']) && $data['post_status'] === 'auto-draft') {
			return $data;
		}

		$this->denyContentChange();

		return $data;
	}

	/**
	 * Stop direct post updates before the database write.
	 */
	public function blockPostUpdate(int $postId, array $data): void
	{
		if (!$this->isLocked()) {
			return;
		}

		$postType = isset($data['post_type']) ? (string) $data['post_type'] : '';
		if ($postType === '') {
			$post = get_post($postId);
			$postType = $post ? $post->post_type : '';
		}

		if ($this->isLockedPostType($postType)) {
			$this->denyContentChange();
		}
	}

	/**
	 * Stop moving locked content to the trash.
	 */
	public function blockPostTrash($trash, \WP_Post $post, string $previousStatus = '')
	{
		if ($this->isLocked() && $this->isLockedPostType($post->post_type)) {
			return false;
		}

		return $trash;
	}

	/**
	 * Stop permanently deleting locked content.
	 */
	public function blockPostDelete($check, \WP_Post $post, bool $forceDelete)
	{
		if ($this->isLocked() && $this->isLockedPostType($post->post_type)) {
			return false;
		}

		return $check;
	}

	/**
	 * Stop media uploads while the lock is active.
	 */
	public function blockUpload(array $file): array
	{
		if ($this->isLocked()) {
			$file['error'] = __('Content editing is locked by justDev Support. Uploads are disabled.', 'jd_support');
		}

		return $file;
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
		echo esc_html__('Content editing is locked by justDev Support. You can view content normally, but saving changes is disabled.', 'jd_support');
		echo '</p></div>';
	}

	/**
	 * Keep the editor visible while disabling save/update controls.
	 */
	public function renderEditorReadOnlyState(): void
	{
		if (!$this->isLocked() || !$this->isLockedEditorScreen()) {
			return;
		}

		$message = __('Content editing is locked by justDev Support. You can view content normally, but saving changes is disabled.', 'jd_support');
		$selector = implode(
			',',
			[
				'#publish',
				'#save-post',
				'#delete-action a',
				'.editor-post-publish-button',
				'.editor-post-save-draft',
				'.editor-post-trash',
				'.editor-post-switch-to-draft',
			],
		);
		?>
		<style>
			<?php echo esc_html($selector); ?> {
				cursor: not-allowed !important;
				opacity: 0.55 !important;
			}
		</style>
		<script>
			(function () {
				var message = <?php echo wp_json_encode($message); ?>;
				var selector = <?php echo wp_json_encode($selector); ?>;

				function showNotice() {
					try {
						if (!window.wp || !wp.data || !wp.data.dispatch) {
							return;
						}

						var notices = wp.data.dispatch('core/notices');
						if (!notices || !notices.createWarningNotice) {
							return;
						}

						notices.createWarningNotice(message, {
							id: 'jd-support-content-lock',
							isDismissible: false
						});
					} catch (error) {
						return;
					}
				}

				function disableControls() {
					document.querySelectorAll(selector).forEach(function (button) {
						button.setAttribute('aria-disabled', 'true');
						button.setAttribute('title', message);
						button.disabled = true;
					});
				}

				document.addEventListener('submit', function (event) {
					if (event.target && event.target.id === 'post') {
						event.preventDefault();
						window.alert(message);
					}
				}, true);

				document.addEventListener('click', function (event) {
					var target = event.target && event.target.closest ? event.target.closest(selector) : null;
					if (!target) {
						return;
					}

					event.preventDefault();
					event.stopPropagation();
					window.alert(message);
				}, true);

				disableControls();
				showNotice();

				if (window.MutationObserver) {
					new MutationObserver(disableControls).observe(document.body, {
						childList: true,
						subtree: true
					});
				}
			})();
		</script>
		<?php
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
			__('Content editing is locked by justDev Support. Changes were not saved.', 'jd_support'),
			['status' => 423],
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

	/**
	 * Check if the current admin screen is a locked post editor.
	 */
	private function isLockedEditorScreen(): bool
	{
		if (!is_admin() || !function_exists('get_current_screen')) {
			return false;
		}

		$screen = get_current_screen();
		if (!$screen || !in_array($screen->base, ['post', 'post-new'], true)) {
			return false;
		}

		$postType = isset($screen->post_type) ? (string) $screen->post_type : 'post';

		return $this->isLockedPostType($postType);
	}

	/**
	 * Stop the current request with a consistent lock message.
	 */
	private function denyContentChange(): void
	{
		wp_die(
			esc_html__('Content editing is locked by justDev Support. Changes were not saved.', 'jd_support'),
			esc_html__('Content editing locked', 'jd_support'),
			['response' => 423],
		);
	}
}
