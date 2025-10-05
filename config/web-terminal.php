<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Web Terminal Access Key
    |--------------------------------------------------------------------------
    |
    | This key is required to access the web terminal. Generate a secure
    | random key for production use. You can use the artisan command:
    | php artisan web-terminal:generate-key
    |
    */
    'access_key' => env('WEB_TERMINAL_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Require Access Key
    |--------------------------------------------------------------------------
    |
    | Whether to require an access key for terminal access. For security,
    | this should always be true in production environments.
    |
    */
    'require_key' => env('WEB_TERMINAL_REQUIRE_KEY', true),

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security-related settings for the web terminal.
    |
    */
    'max_requests_per_session' => env('WEB_TERMINAL_MAX_REQUESTS', 50),
    'rate_limit_window' => env('WEB_TERMINAL_RATE_WINDOW', 600), // 10 minutes
    'command_timeout' => env('WEB_TERMINAL_TIMEOUT', 15), // seconds
    'max_command_length' => env('WEB_TERMINAL_MAX_LENGTH', 500),
    'enable_logging' => env('WEB_TERMINAL_LOGGING', true),

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | List of allowed IP addresses or CIDR ranges. Leave empty to allow all IPs.
    | Example: ['192.168.1.0/24', '10.0.0.0/8', '127.0.0.1']
    |
    */
    'allowed_ips' => env('WEB_TERMINAL_ALLOWED_IPS') ? 
        explode(',', env('WEB_TERMINAL_ALLOWED_IPS')) : [],

    /*
    |--------------------------------------------------------------------------
    | Blocked Commands
    |--------------------------------------------------------------------------
    |
    | List of commands that are blocked for security reasons.
    | These commands cannot be executed through the web terminal.
    |
    */
    'blocked_commands' => [
        // File Operations
        'rm', 'rmdir', 'unlink', 'delete',
        
        // Network Operations
        'wget', 'curl', 'nc', 'netcat', 'telnet',
        
        // Permissions
        'chmod', 'chown', 'chgrp',
        
        // User Management
        'sudo', 'su', 'passwd', 'shadow',
        'useradd', 'userdel', 'usermod', 'groupadd', 'groupdel',
        
        // System Services
        'systemctl', 'service', 'init', 'systemd',
        
        // Process Management
        'kill', 'killall', 'pkill', 'killproc',
        
        // Disk Operations
        'mount', 'umount', 'fdisk', 'mkfs', 'fsck',
        
        // Security
        'iptables', 'ufw', 'firewall-cmd',
        
        // Scheduling
        'crontab', 'at', 'nohup',
        
        // Remote Access
        'ssh', 'scp', 'rsync', 'ftp', 'sftp',
        
        // Dangerous Operations
        'dd', 'shred', 'wipe',
        
        // System Control
        'reboot', 'shutdown', 'halt', 'poweroff',
        
        // Text Editors (can be used to modify system files)
        'vi', 'vim', 'nano', 'emacs', 'edit',
        
        // Programming Languages (can execute arbitrary code)
        'python', 'python3', 'perl', 'ruby', 'node', 'php',
        
        // Compilers
        'gcc', 'g++', 'make', 'cmake',
        
        // Container Operations
        'docker', 'podman', 'kubectl',
        
        // Package Managers
        'apt', 'apt-get', 'yum', 'dnf', 'pacman', 'brew',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the routes for the web terminal.
    |
    */
    'route' => [
        'prefix' => env('WEB_TERMINAL_ROUTE_PREFIX', 'web-terminal'),
        'middleware' => ['web', 'web-terminal-auth'],
        'name' => 'web-terminal.',
    ],

    /*
    |--------------------------------------------------------------------------
    | View Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the view settings for the web terminal.
    |
    */
    'view' => [
        'theme' => env('WEB_TERMINAL_THEME', 'dark'), // dark, light
        'font_family' => env('WEB_TERMINAL_FONT', 'Monaco, Consolas, monospace'),
        'font_size' => env('WEB_TERMINAL_FONT_SIZE', '14px'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for security events.
    |
    */
    'logging' => [
        'channel' => env('WEB_TERMINAL_LOG_CHANNEL', 'web-terminal'),
        'level' => env('WEB_TERMINAL_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Commands
    |--------------------------------------------------------------------------
    |
    | Define custom commands that can be executed in the terminal.
    | These are PHP methods that will be called instead of shell commands.
    |
    */
    'custom_commands' => [
        // Example: 'hello' => \App\Console\Commands\HelloCommand::class,
    ],
];