# justDev Support Plugin - Modern Architecture

## Обзор

Этот плагин был полностью переработан с использованием современных принципов разработки и архитектурных паттернов.

## Новая архитектура

### Структура проекта

```
src/
├── Core/                    # Ядро плагина
│   ├── Plugin.php          # Главный класс плагина
│   ├── Container/          # Dependency Injection
│   │   └── Container.php
│   ├── Config/             # Управление конфигурацией
│   │   └── ConfigManager.php
│   └── Hooks/              # Управление хуками
│       └── HookManager.php
├── Services/               # Бизнес-логика
│   ├── SecurityService.php
│   ├── AdminService.php
│   ├── CacheService.php
│   ├── SvgService.php
│   └── VersionService.php
├── Admin/                  # Административная часть
│   ├── AdminManager.php
│   └── Controllers/
│       ├── SettingsController.php
│       └── AssetsController.php
├── Public/                 # Публичная часть
│   ├── PublicManager.php
│   └── Controllers/
│       └── AssetsController.php
└── Views/                  # Представления
    └── settings-page.php
```

### Ключевые улучшения

#### 1. Dependency Injection Container
- Централизованное управление зависимостями
- Автоматическое создание экземпляров сервисов
- Легкое тестирование и расширяемость

#### 2. Service Layer Pattern
- Разделение бизнес-логики на отдельные сервисы
- Каждый сервис отвечает за конкретную функциональность
- Легкое переиспользование кода

#### 3. MVC Architecture
- Контроллеры для обработки запросов
- Представления для отображения данных
- Модели (сервисы) для бизнес-логики

#### 4. Configuration Management
- Централизованное управление настройками
- Типизированные методы для работы с конфигурацией
- Автоматическая валидация настроек

#### 5. Hook Management
- Структурированное управление WordPress хуками
- Автоматическая регистрация всех хуков
- Легкое добавление новых хуков

## Установка и использование

### Требования
- PHP 7.4+
- WordPress 5.0+

### Установка
1. Скопируйте файлы в директорию `wp-content/mu-plugins/`
2. Активируйте плагин через админ-панель
3. Настройте опции в разделе "j|D Support"

### Разработка

#### Добавление нового сервиса
```php
// 1. Создайте сервис в src/Services/
class NewService
{
    public function __construct(Container $container)
    {
        $this->config = $container->get('config');
    }
    
    public function doSomething()
    {
        // Ваша логика
    }
}

// 2. Зарегистрируйте в Container.php
$this->register('new', NewService::class);

// 3. Используйте в HookManager.php
$this->addAction('hook_name', [$this->container->get('new'), 'doSomething']);
```

#### Добавление нового контроллера
```php
// 1. Создайте контроллер в src/Admin/Controllers/
class NewController
{
    public function registerHooks()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
    }
}

// 2. Добавьте в AdminManager.php
$this->newController = new NewController($this->container);
$this->newController->registerHooks();
```

## Тестирование

### Запуск тестов
```bash
composer test
```

### Проверка кода
```bash
composer phpcs    # Проверка стиля кода
composer phpstan  # Статический анализ
composer fix      # Автоисправление стиля
```

## Миграция со старой версии

### Основные изменения
1. **Файловая структура**: Полностью переработана
2. **Namespace**: Используется PSR-4 автозагрузка
3. **Dependency Injection**: Внедрен контейнер зависимостей
4. **Service Layer**: Бизнес-логика разделена на сервисы
5. **Configuration**: Централизованное управление настройками

### Совместимость
- Все существующие настройки сохраняются
- API хуков остается совместимым
- Функциональность не изменилась

## Преимущества новой архитектуры

### 1. Поддерживаемость
- Четкое разделение ответственности
- Легкое понимание структуры кода
- Простое добавление новых функций

### 2. Тестируемость
- Каждый компонент можно тестировать отдельно
- Dependency Injection упрощает мокирование
- Автоматизированные тесты

### 3. Расширяемость
- Легкое добавление новых сервисов
- Модульная архитектура
- Переиспользование компонентов

### 4. Производительность
- Автозагрузка только нужных классов
- Оптимизированный контейнер зависимостей
- Кеширование конфигурации

## Лицензия

GPL-2.0+

## Автор

Kyrylo Dorozhynskyi | justDev 
