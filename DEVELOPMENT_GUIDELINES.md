# Development Guidelines

## Code Standards

### Comments

- **All code comments must be written in English**
- Use clear, descriptive comments
- Comment complex logic and business rules
- Keep comments up-to-date with code changes


### File Naming

- Use kebab-case for file names
- Use PascalCase for component names
- Use camelCase for variables and functions

### Git Flow & Commit Messages

#### Branch Strategy

- Use **Git Flow** workflow
- `main` - production code
- `develop` - development branch
- `feature/*` - feature branches
- `hotfix/*` - hotfix branches
- `release/*` - release branches

#### Commit Message Format

Use prefixes based on branch type:

- **Feature branches**: `[DEV] description`

  ```
  [DEV] Add user authentication system
  [DEV] Implement product search functionality
  [DEV] Update UI components for mobile responsiveness
  ```

- **Hotfix branches**: `[HOTFIX] description`

  ```
  [HOTFIX] Fix critical security vulnerability
  [HOTFIX] Resolve API endpoint crash
  [HOTFIX] Fix login button not working
  ```

- **Release branches**: `[UPD] description`
  ```
  [UPD] Release version 1.2.0
  [UPD] Update dependencies and security patches
  [UPD] Prepare release candidate for production
  ```

#### Git Flow Commands

```bash
# Start new feature
git flow feature start feature-name

# Finish feature
git flow feature finish feature-name

# Start hotfix
git flow hotfix start hotfix-name

# Finish hotfix
git flow hotfix finish hotfix-name

# Start release
git flow release start release-name

# Finish release
git flow release finish release-name
```


**Remember**: All code comments must be in English for better maintainability and team
collaboration.
