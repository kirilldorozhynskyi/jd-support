# justDev Support Plugin - Modern Architecture

## Overview

This plugin has been completely redesigned using modern development principles and architectural patterns.

## New Architecture

### Project Structure

```
src/
├── Core/                    # Plugin core
│   ├── Plugin.php          # Main plugin class
│   ├── Container/          # Dependency Injection
│   │   └── Container.php
│   ├── Config/             # Configuration management
│   │   └── ConfigManager.php
│   └── Hooks/              # Hook management
│       └── HookManager.php
├── Services/               # Business logic
│   ├── SecurityService.php
│   ├── AdminService.php
│   ├── CacheService.php
│   ├── SvgService.php
│   └── VersionService.php
├── Admin/                  # Administrative part
│   ├── AdminManager.php
│   └── Controllers/
│       ├── SettingsController.php
│       └── AssetsController.php
├── Public/                 # Public part
│   ├── PublicManager.php
│   └── Controllers/
│       └── AssetsController.php
└── Views/                  # Views
    └── settings-page.php
```

### Key Improvements

#### 1. Dependency Injection Container
- Centralized dependency management
- Automatic service instance creation
- Easy testing and extensibility

#### 2. Service Layer Pattern
- Separation of business logic into separate services
- Each service is responsible for specific functionality
- Easy code reuse

#### 3. MVC Architecture
- Controllers for request handling
- Views for data display
- Models (services) for business logic

#### 4. Configuration Management
- Centralized settings management
- Typed methods for configuration work
- Automatic settings validation

#### 5. Hook Management
- Structured WordPress hook management
- Automatic registration of all hooks
- Easy addition of new hooks

## Installation and Usage

### Requirements
- PHP 7.4+
- WordPress 5.0+

### Installation
1. Copy files to `wp-content/mu-plugins/` directory
2. Activate plugin through admin panel
3. Configure options in "j|D Support" section

### Development

#### Adding a new service
```php
// 1. Create service in src/Services/
class NewService
{
    public function __construct(Container $container)
    {
        $this->config = $container->get('config');
    }
    
    public function doSomething()
    {
        // Your logic
    }
}

// 2. Register in Container.php
$this->register('new', NewService::class);

// 3. Use in HookManager.php
$this->addAction('hook_name', [$this->container->get('new'), 'doSomething']);
```

#### Adding a new controller
```php
// 1. Create controller in src/Admin/Controllers/
class NewController
{
    public function registerHooks()
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
    }
}

// 2. Add to AdminManager.php
$this->newController = new NewController($this->container);
$this->newController->registerHooks();
```

## Testing

### Running tests
```bash
composer test
```

### Code checking
```bash
composer phpcs    # Code style check
composer phpstan  # Static analysis
composer fix      # Auto-fix style
```

## Migration from old version

### Main changes
1. **File structure**: Completely redesigned
2. **Namespace**: PSR-4 autoloading used
3. **Dependency Injection**: Dependency container implemented
4. **Service Layer**: Business logic separated into services
5. **Configuration**: Centralized settings management

### Compatibility
- All existing settings are preserved
- Hook API remains compatible
- Functionality unchanged

## Advantages of new architecture

### 1. Maintainability
- Clear separation of responsibilities
- Easy understanding of code structure
- Simple addition of new features

### 2. Testability
- Each component can be tested separately
- Dependency Injection simplifies mocking
- Automated tests

### 3. Extensibility
- Easy addition of new services
- Modular architecture
- Component reuse

### 4. Performance
- Loading only necessary classes
- Optimized dependency container
- Configuration caching

## License

GPL-2.0+

## Author

Kyrylo Dorozhynskyi | justDev 
