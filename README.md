# Laravel Web Terminal

A secure, feature-rich web terminal emulator package for Laravel applications. Execute shell commands safely through a beautiful web interface with comprehensive security features.

![Laravel Web Terminal](https://img.shields.io/badge/Laravel-11.x%20%7C%2010.x%20%7C%209.x-red.svg)
![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)

## ✨ Features

### 🔒 Security First
- **Access Key Authentication** - Secure key-based access control
- **Command Blocking** - Comprehensive list of dangerous commands blocked by default
- **Input Sanitization** - Prevents command injection attacks
- **Rate Limiting** - Session-based request limiting
- **IP Whitelisting** - Optional IP address restrictions
- **Security Logging** - All security events logged with details

### 🎨 Modern Interface
- **Responsive Design** - Works on desktop and mobile devices
- **Dark/Light Themes** - Customizable appearance
- **Real-time Execution** - AJAX-powered command execution
- **Command History** - Navigate through previous commands
- **Auto-completion** - Tab completion for commands (coming soon)

### 🛠️ Developer Friendly
- **Laravel Integration** - Built specifically for Laravel
- **Artisan Commands** - Easy key generation and management
- **Configurable** - Extensive configuration options
- **Middleware Support** - Custom middleware integration
- **Event Logging** - Laravel logging integration

## 📋 Requirements

- PHP 8.0 or higher
- Laravel 9.x, 10.x, or 11.x
- `shell_exec` function enabled
- `proc_open` function enabled (recommended)

## 🚀 Installation

### 1. Install via Composer

```bash
composer require SynceraTech/laravel-web-terminal
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=web-terminal-config
```

### 3. Generate Access Key

```bash
php artisan web-terminal:generate-key
```

This will generate a secure access key and add it to your `.env` file.

### 4. Configure Environment

Add these variables to your `.env` file (automatically added by the generate-key command):

```env
# Web Terminal Configuration
WEB_TERMINAL_KEY=your_generated_secure_key_here
WEB_TERMINAL_REQUIRE_KEY=true
WEB_TERMINAL_MAX_REQUESTS=50
WEB_TERMINAL_RATE_WINDOW=600
WEB_TERMINAL_TIMEOUT=15
WEB_TERMINAL_MAX_LENGTH=500
WEB_TERMINAL_LOGGING=true
```

### 5. Optional: Publish Views

If you want to customize the terminal interface:

```bash
php artisan vendor:publish --tag=web-terminal-views
```

## 🔧 Configuration

The configuration file `config/web-terminal.php` provides extensive customization options:

### Security Settings

```php
'access_key' => env('WEB_TERMINAL_KEY', null),
'require_key' => env('WEB_TERMINAL_REQUIRE_KEY', true),
'max_requests_per_session' => env('WEB_TERMINAL_MAX_REQUESTS', 50),
'rate_limit_window' => env('WEB_TERMINAL_RATE_WINDOW', 600),
'command_timeout' => env('WEB_TERMINAL_TIMEOUT', 15),
'max_command_length' => env('WEB_TERMINAL_MAX_LENGTH', 500),
```

### IP Whitelisting

```php
'allowed_ips' => [
    '192.168.1.0/24',  // Local network
    '10.0.0.0/8',      // Private network
    '127.0.0.1'        // Localhost
],
```

### Blocked Commands

```php
'blocked_commands' => [
    'rm', 'rmdir', 'sudo', 'chmod', 'chown',
    'useradd', 'userdel', 'systemctl', 'reboot',
    // ... many more security-focused restrictions
],
```

### Theme Customization

```php
'view' => [
    'theme' => env('WEB_TERMINAL_THEME', 'dark'), // dark, light
    'font_family' => env('WEB_TERMINAL_FONT', 'JetBrains Mono, Monaco, Consolas, monospace'),
    'font_size' => env('WEB_TERMINAL_FONT_SIZE', '14px'),
],
```

## 🌐 Usage

### Accessing the Terminal

Once installed and configured, access your web terminal at:

```
https://yourdomain.com/web-terminal?key=YOUR_GENERATED_KEY
```

The route prefix can be customized in the configuration file.

### Available Commands

The terminal supports most standard Unix/Linux commands, except those blocked for security:

- **Navigation**: `cd`, `pwd`, `ls`
- **File Operations**: `cat`, `head`, `tail`, `find`
- **System Info**: `whoami`, `hostname`, `uptime`, `ps`
- **Text Processing**: `grep`, `sort`, `uniq`, `wc`
- **Network**: `ping` (limited), `netstat`

### Built-in Commands

- `clear` - Clear the terminal screen
- `help` - Show available commands
- `history` - Display command history

### Keyboard Shortcuts

- **↑/↓ Arrow Keys** - Navigate command history
- **Tab** - Auto-complete commands (coming soon)
- **Ctrl+C** - Interrupt running command
- **Enter** - Execute command

## 🔒 Security Considerations

### Production Deployment Checklist

- [ ] **Use HTTPS** - Always serve over encrypted connections
- [ ] **Strong Access Key** - Use the generated 64-character key
- [ ] **IP Restrictions** - Configure IP whitelist if possible
- [ ] **Regular Key Rotation** - Change access keys periodically
- [ ] **Monitor Logs** - Check `storage/logs/web-terminal.log` regularly
- [ ] **Limited User** - Run web server with minimal privileges
- [ ] **Firewall Rules** - Restrict network access appropriately

### Security Features

1. **Command Blocking**: 50+ dangerous commands blocked by default
2. **Input Sanitization**: Prevents command injection with pattern matching
3. **Rate Limiting**: Prevents abuse with configurable request limits
4. **Access Control**: Key-based authentication with optional IP whitelisting
5. **Audit Logging**: All commands and security events logged
6. **Timeout Protection**: Commands automatically terminated after timeout

### Recommended Server Configuration

```nginx
# Nginx configuration example
location /web-terminal {
    # IP whitelist (optional)
    allow 192.168.1.0/24;
    allow 10.0.0.0/8;
    deny all;
    
    # Security headers
    add_header X-Frame-Options DENY;
    add_header X-Content-Type-Options nosniff;
    add_header X-XSS-Protection "1; mode=block";
    
    # Pass to PHP
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 🎨 Customization

### Custom Commands

Add custom PHP commands to the terminal:

```php
// In your service provider
use SynceraTech\LaravelWebTerminal\Services\TerminalService;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        TerminalService::macro('customCommand', function($args) {
            return 'Custom command output: ' . implode(' ', $args);
        });
    }
}
```

### Custom Middleware

Add additional security middleware:

```php
// config/web-terminal.php
'route' => [
    'middleware' => ['web', 'web-terminal-auth', 'your-custom-middleware'],
],
```

### Theme Customization

Override CSS variables in your published views:

```css
:root {
    --background-color: #your-color;
    --terminal-bg: #your-terminal-bg;
    --text-color: #your-text-color;
    /* ... more customizations */
}
```

## 📊 Logging and Monitoring

### Log Files

- **Security Events**: `storage/logs/web-terminal.log`
- **Laravel Logs**: Standard Laravel logging integration

### Example Log Entries

```
[2025-10-05 12:00:00] IP: 192.168.1.100 | UA: Mozilla/5.0... | Command executed: ls -la
[2025-10-05 12:01:00] IP: 192.168.1.100 | UA: Mozilla/5.0... | Blocked command attempted: sudo su
[2025-10-05 12:02:00] IP: 10.0.0.50 | UA: Mozilla/5.0... | Rate limit exceeded
```

### Monitoring Commands

```bash
# Watch logs in real-time
tail -f storage/logs/web-terminal.log

# Check for security incidents
grep -i "blocked\|dangerous\|denied" storage/logs/web-terminal.log

# Monitor command usage
grep "Command executed" storage/logs/web-terminal.log | wc -l
```

## 🧪 Testing

Run the package tests:

```bash
composer test
```

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Clone your fork
3. Install dependencies: `composer install`
4. Run tests: `composer test`
5. Create a feature branch
6. Make your changes
7. Submit a pull request

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes and version history.

## 🐛 Security Issues

If you discover a security vulnerability, please send an email to [hi@synceratech.com](mailto:hi@synceratech.com) instead of using the issue tracker.

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Credits

- Original concept inspired by terminal.php
- Built with ❤️ for the Laravel community
- Icons and fonts from Google Fonts and various open-source projects

## 📞 Support

- **Documentation**: [GitHub Wiki](https://github.com/elmgdad/laravel-web-terminal/wiki)
- **Issues**: [GitHub Issues](https://github.com/elmgdad/laravel-web-terminal/issues)
- **Discussions**: [GitHub Discussions](https://github.com/elmgdad/laravel-web-terminal/discussions)
- **Email**: [hi@synceratech.com](mailto:hi@synceratech.com)

---

**⚠️ Important Security Note**: This package provides shell access through a web interface. Only use in controlled environments with proper security measures. Never deploy without authentication and proper access controls.