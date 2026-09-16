# Переход проектов на кеш jD Support 2.2

## Что перенесено

Кеш данных из Hinohara: Object Cache → transients → callback, TTL по умолчанию 300 секунд. Старые ключи сохранены: объектный кеш использует исходные key/group,
transient — `{group}_{key}`. По умолчанию сбрасываются группы `theme` и `inertia`, включая записи срока действия. В development/staging/local чтение и запись
кеша обходятся. Сначала проверяется `WP_ENV`, если его нет — `wp_get_environment_type()`; production сохраняет кеширование.

Автоматический сброс: `save_post`, `deleted_post`, `wp_update_nav_menu`, `acf/save_post` с приоритетом 20, `added_option`, `updated_option`, `deleted_option`.
Запись/удаление transients не вызывает сброс. Очистка остаётся доступной даже на staging для удаления старого кеша.

**Граница:** это кеш данных, не CDN, PHP OPcache, серверный кеш страниц или кеш браузера. Как в старом helper Hinohara, очистка вызывает общий
`wp_cache_flush()`: затрагивает весь Object Cache, в том числе persistent transients. В таблице options удаляются только transients выбранных групп. При общей
Redis-базе нескольких сайтов учитывайте область очистки вашего drop-in. API рассчитан на обычные transients текущего сайта; network/site transients не
удаляются.

## Подключение существующего проекта

1. Сделайте резервную копию базы и исходного `classes/Cache.php`.
2. Обновите jd-support до 2.2 (Composer/package metadata: 2.2.0). MU-loader должен загрузить плагин до инициализации темы. API доступен после загрузки файла
   плагина.
3. Скопируйте `stubs/JDEVCache.php` в `classes/Cache.php` темы. В образце namespace — `JDEV`; для другого проекта сохраните его существующий
   namespace/автозагрузку. Это образец для проверки и адаптации, не заменяйте им дополнительные методы проекта вслепую.
4. Существующие вызовы `JDEV\Cache::getCached(...)` и `JDEV\Cache::clear(...)` менять не нужно. При наличии плагина они делегируются `JdSupport\Cache`; без него
   используется автономная реализация.
5. В ThemeSetup оберните **только регистрации старых cache hooks** проверкой:

```php
if (!class_exists(\JdSupport\Cache::class)) {
	add_action('save_post', [self::class, 'clearCache']);
	add_action('deleted_post', [self::class, 'clearCache']);
	add_action('wp_update_nav_menu', [self::class, 'clearCache']);
	add_action('acf/save_post', [self::class, 'clearCache'], 20);
	add_action('added_option', [self::class, 'clearOptionCache']);
	add_action('updated_option', [self::class, 'clearOptionCache']);
	add_action('deleted_option', [self::class, 'clearOptionCache']);
}
```

Оставьте fallback-методы темы:

```php
public static function clearCache(): void
{
    \JDEV\Cache::clear();
}

public static function clearOptionCache(string $option): void
{
    if (strpos($option, '_transient_') === 0 || strpos($option, '_site_transient_') === 0) {
        return;
    }
    self::clearCache();
}
```

Выполняйте проверку при инициализации темы после загрузки плагинов, не до MU-loader. Не оставляйте старый `update_option` hook: он вызывается до сохранения
значения. Не регистрируйте обе реализации одновременно — получите лишние сбросы. При программном `update_field()` вне обычного ACF-сохранения вызовите сброс
явно после записи.

6. При выкладке один раз выполните `wp eval '\JdSupport\Cache::clear();'`. Проверьте адрес/окружение WP-CLI перед запуском: Object Cache очищается полностью.
7. Проверьте главную, контактную форму, квартиру, переводы и сохранение ACF/options. Build для PHP-интеграции не требуется. Плагин не изменяет `WP_CACHE` и
   серверную конфигурацию; ранние запреты кеша страниц для staging остаются в конфигурации проекта.

## Два направления и новые проекты

```php
$data = \JdSupport\Cache::getCached(
	'my_data_sk',
	function () {
		return load_project_data();
	},
	300,
	'theme',
);

// Проект → плагин:
\JDEV\Cache::clear(); // Через адаптер.
// Или без зависимости на класс проекта:
do_action('jd_support/cache/clear'); // Все зарегистрированные группы.
do_action('jd_support/cache/clear', 'theme'); // Только DB-transients этой группы.

// Плагин → проект: событие после очистки для дополнительных хранилищ.
add_action('jd_support/cache/cleared', function (array $groups) {
	// Очистите собственное хранилище, если оно есть.
});
```

Plugin clear инвалидирует значения, записанные через адаптер темы, и наоборот — это одно хранилище. Не нужно создавать alias класса `JDEV\Cache` из плагина или
вызывать старую тему из плагина. Повторный вход в clear во время уведомления заблокирован, чтобы интеграции не образовали рекурсию. Передавайте в clear только
внутренние доверенные имена групп, не пользовательский HTTP-параметр.

Для дополнительных групп зарегистрируйте постоянный список (до сохранений):

```php
add_filter('jd_support/cache/groups', function (array $groups) {
	$groups[] = 'my_project';
	return $groups;
});
```

Одинаковые key/group должны означать одни данные. Добавляйте язык, ID поста, страницу пагинации и другие влияющие параметры в ключ. Не кешируйте персональные
данные без корректного разделения пользователей. Callback с null не кешируется. false сохраняется в Object Cache, но является cache miss в стандартном transient
API после потери Object Cache — используйте массив-обёртку, если это существенно.

## Удаление браузерного кеша

В 2.2 удалены UI/регистрация `jd_cache` и генератор Expires/.htaccess. При первом посещении админки пользователем с `manage_options` плагин удалит только блок
`# BEGIN Cache Rules` … `# END Cache Rules` из `ABSPATH . '.htaccess'`. Остальные правила остаются без изменений. Новые файлы .htaccess не создаются.

Перед изменением сохраняется исходный текст в option `jd_support_legacy_htaccess_backup_22` (autoload выключен). Файла с доступной по HTTP резервной копией не
создаётся. Успешная очистка помечается `jd_support_htaccess_cache_removed_22`. Если файл недоступен для записи или существующая копия отличается, появляется
предупреждение; удалите только старый маркированный блок вручную. Копию при необходимости получите через WP-CLI и восстановите вручную после сверки других
правил; автоматического отката нет.

Старое значение `jd_cache` остаётся в базе, но больше не используется. Кеш, уже сохранённый браузерами, этим не очищается. Для изменившихся ассетов нужны новые
version/hash URL. Если .htaccess расположен вне ABSPATH, проверьте его отдельно при выкладке.

## Проверки

```sh
php tests/cache-regression.php production
php tests/cache-regression.php production persistent
php tests/cache-regression.php production legacy
php tests/cache-regression.php development
php tests/cache-regression.php staging
```

Это изолированный regression harness с заменами WordPress storage/hooks, а не тест на реальном Redis. После установки отдельно проверьте реальные
WordPress/ACF-сохранения и используемый Object Cache drop-in. Для отката проекта верните исходный helper/hooks из Git; формат ключей остаётся совместимым.
