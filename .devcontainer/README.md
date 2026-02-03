# Roundcube Development Container

This guide explains how to set up and use the Roundcube development environment using VS Code Dev Containers.

## System Requirements

### All Platforms

- **Docker Desktop** (or Docker Engine + Docker Compose on Linux)
  - Windows/macOS: [Download Docker Desktop](https://www.docker.com/products/docker-desktop/)
  - Linux: Install Docker Engine and Docker Compose plugin
- **Visual Studio Code** with the **Dev Containers extension**
  - Extension ID: `ms-vscode-remote.remote-containers`
- **Git** for cloning the repository
- **4GB+ RAM** available for Docker (8GB recommended)
- **10GB+ free disk space** for images and volumes

### Windows-Specific

- **Windows 10/11** with WSL 2 enabled
- Docker Desktop configured to use WSL 2 backend (recommended for performance)
- For best performance, clone the repository inside WSL 2 filesystem (`/home/...`) rather than Windows filesystem (`/mnt/c/...`)

### macOS-Specific

- **macOS 10.15 (Catalina)** or later
- Apple Silicon (M1/M2/M3) and Intel Macs both supported
- Docker Desktop automatically handles architecture differences

### Linux-Specific

- Docker Engine 20.10+ with Docker Compose v2
- User must be in the `docker` group: `sudo usermod -aG docker $USER`

## Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/roundcube/roundcubemail.git
cd roundcubemail
```

### 2. Open in VS Code

**Option A: From Command Line**
```bash
code .
```

**Option B: From VS Code**
1. Open VS Code
2. File → Open Folder → Select the `roundcubemail` directory

### 3. Reopen in Container

When you open the folder, VS Code should detect the `.devcontainer` configuration and prompt:

> "Folder contains a Dev Container configuration file. Reopen folder to develop in a container?"

Click **"Reopen in Container"**.

**If the prompt doesn't appear:**
1. Press `F1` (or `Cmd+Shift+P` / `Ctrl+Shift+P`)
2. Type "Dev Containers: Reopen in Container"
3. Press Enter

### 4. Wait for Setup

The first time you open the container:
1. Docker builds the PHP development image (may take several minutes)
2. Docker pulls MariaDB, Dovecot, and Mailhog images
3. The `post-create.sh` script runs automatically to:
   - Wait for MariaDB to be ready
   - Initialize the database schema
   - Install Composer dependencies
   - Install npm dependencies
   - Download JavaScript libraries (jQuery, TinyMCE, CodeMirror)
   - Create the development configuration file

Subsequent opens are much faster as images and volumes are cached.

### 5. Start the Development Server

Press `Ctrl+Shift+B` (or `Cmd+Shift+B` on macOS) to run the default build task.

You'll be prompted for a port number (default: 8081). VS Code will automatically forward the port and show a notification with a link to open the webmail interface.

**Test Credentials:**
- Username: `testuser`
- Password: `testpass`

## Services and Ports

| Service    | Internal Host | Forwarded Port | Description                    |
|------------|---------------|----------------|--------------------------------|
| Roundcube  | localhost     | (user-chosen)  | PHP development server         |
| MariaDB    | db:3306       | 3306           | Database server                |
| IMAP       | mail:143      | 143            | Dovecot IMAP server            |
| Mailhog UI | mailhog:8025  | 8025           | Email testing interface        |
| SMTP       | mailhog:1025  | -              | Mailhog SMTP (internal only)   |

**Mailhog** captures all outgoing emails for testing. Access the web UI at http://localhost:8025 to view sent messages.

## VS Code Tasks

Access tasks via `Terminal → Run Task...` or `Ctrl+Shift+P` → "Tasks: Run Task"

| Task                          | Description                                      |
|-------------------------------|--------------------------------------------------|
| **Start Roundcube Dev Server** | Start PHP built-in server (default build task)  |
| **Run PHPUnit Tests**         | Run the full test suite                          |
| **PHP CS Fixer (check)**      | Check code style without making changes          |
| **PHP CS Fixer (fix)**        | Automatically fix code style issues              |
| **PHPStan Analyse**           | Run static analysis                              |
| **ESLint Check**              | Check JavaScript code style                      |
| **Initialize Database**       | Smart initialization (detects current state)     |
| **Reset Database**            | Drop and recreate database (destroys all data)   |
| **Check Database Status**     | Show database state without making changes       |

## Command Line Scripts

These commands are available in the container terminal:

### Database Management

```bash
# Check database state (initialized, partial, or empty)
.devcontainer/post-create.sh --db-status

# Initialize database if needed (smart detection)
.devcontainer/post-create.sh --db-only

# Force reset database (drops all tables, reinitializes)
.devcontainer/post-create.sh --db-reset

# Show help
.devcontainer/post-create.sh --help
```

### Running Tests

```bash
# Run all unit tests
vendor/bin/phpunit -c tests/phpunit.xml --fail-on-warning

# Run a specific test file
vendor/bin/phpunit -c tests/phpunit.xml tests/Framework/SomeTest.php

# Run tests matching a pattern
vendor/bin/phpunit -c tests/phpunit.xml --filter "TestClassName"
```

### Code Quality

```bash
# PHP code style - check only
vendor/bin/php-cs-fixer fix --dry-run --diff

# PHP code style - apply fixes
vendor/bin/php-cs-fixer fix

# PHP static analysis
vendor/bin/phpstan analyse -v

# JavaScript linting
npx eslint --ext .js .

# JavaScript linting with auto-fix
npx eslint --fix .
```

### Dependency Management

```bash
# Update PHP dependencies
composer install --prefer-dist

# Update JavaScript dependencies
npm install --include=dev --omit=optional

# Reinstall JavaScript libraries (jQuery, TinyMCE, etc.)
./bin/install-jsdeps.sh
```

### Database Access

```bash
# Connect to MariaDB
mariadb -h db -u roundcube -proundcube roundcube

# Run a SQL file
mariadb -h db -u roundcube -proundcube roundcube < path/to/file.sql
```

## Troubleshooting

### Container Won't Start

1. Ensure Docker Desktop is running
2. Check Docker has enough resources (Settings → Resources)
3. Try rebuilding: `F1` → "Dev Containers: Rebuild Container"

### Database Connection Issues

If MariaDB isn't ready when the setup script runs:

```bash
# Check database status
.devcontainer/post-create.sh --db-status

# Manually initialize if needed
.devcontainer/post-create.sh --db-only
```

### "Database is in partial/inconsistent state"

This can happen if initialization was interrupted. Reset the database:

```bash
.devcontainer/post-create.sh --db-reset
```

### Port Already in Use

If port 8081 (or your chosen port) is in use:
- Choose a different port when prompted
- Or stop the process using that port

### Slow Performance on Windows

Clone the repository inside WSL 2 for better I/O performance:

```bash
# In WSL terminal
cd ~
git clone https://github.com/roundcube/roundcubemail.git
code roundcubemail
```

### Extensions Not Working

If PHP extensions (like Intelephense) aren't working:
1. Wait for container to fully initialize
2. Try reloading the window: `F1` → "Developer: Reload Window"

## Container Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Docker Network: roundcube-dev            │
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐  │
│  │     app     │  │     db      │  │        mail         │  │
│  │  (PHP 8.4)  │  │ (MariaDB 11)│  │     (Dovecot)       │  │
│  │             │  │             │  │                     │  │
│  │  Composer   │  │  roundcube  │  │  testuser:testpass  │  │
│  │  Node.js    │  │  database   │  │  IMAP on :143       │  │
│  │  Xdebug     │  │             │  │                     │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
│         │                                                   │
│         │         ┌─────────────────────┐                   │
│         │         │      mailhog        │                   │
│         └────────▶│   SMTP on :1025     │                   │
│                   │   Web UI on :8025   │                   │
│                   └─────────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

## PHP Extensions

The container includes all required and recommended PHP extensions for Roundcube:

### Required Extensions
| Extension | Purpose                                    |
|-----------|--------------------------------------------|
| gd        | Image processing                           |
| intl      | Internationalization support               |
| ldap      | LDAP directory access                      |
| pdo_mysql | MySQL/MariaDB database driver              |
| pdo_pgsql | PostgreSQL database driver                 |
| pdo_sqlite| SQLite database driver                     |
| zip       | ZIP archive handling                       |
| opcache   | PHP bytecode caching                       |

### Optional Extensions (Performance Recommended)
| Extension | Purpose                                    |
|-----------|--------------------------------------------|
| exif      | EXIF metadata reading from images          |
| imagick   | Advanced image manipulation (ImageMagick)  |
| enchant   | Spell checking support                     |
| xdebug    | Debugging and code coverage                |

### Additional Libraries
| Library    | Source                                    | Purpose                    |
|------------|-------------------------------------------|----------------------------|
| Net_LDAP3  | kolab/net_ldap3 (Composer)                | Enhanced LDAP support      |

Net_LDAP3 is installed via Composer from the official Kolab package for best LDAP compatibility.

## VS Code Extensions

The following extensions are automatically installed in the container:

| Extension                     | Purpose                          |
|-------------------------------|----------------------------------|
| Intelephense                  | PHP intelligence                 |
| PHP Debug                     | Xdebug integration               |
| PHP CS Fixer                  | Code formatting                  |
| ESLint                        | JavaScript linting               |
| Prettier                      | Code formatting                  |

## Configuration Files

| File                          | Purpose                          |
|-------------------------------|----------------------------------|
| `.devcontainer/devcontainer.json` | VS Code Dev Container config |
| `.devcontainer/docker-compose.yml` | Service definitions         |
| `.devcontainer/Dockerfile`    | PHP development image            |
| `.devcontainer/Dockerfile.dovecot` | IMAP test server image      |
| `.devcontainer/post-create.sh` | Setup automation script         |
| `config/config.inc.php`       | Roundcube configuration (generated) |

## Stopping and Removing

### Stop the Container

Close VS Code or use: `F1` → "Remote: Close Remote Connection"

### Remove Container and Volumes

To completely reset (removes all data including database):

```bash
# From outside the container
cd .devcontainer
docker compose down -v
```

Then reopen in VS Code to rebuild.
