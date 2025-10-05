<?php

namespace SynceraTech\LaravelWebTerminal\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class TerminalService
{
    protected array $blockedCommands;
    protected int $commandTimeout;
    protected int $maxCommandLength;

    public function __construct()
    {
        $this->blockedCommands = config('web-terminal.blocked_commands', []);
        $this->commandTimeout = config('web-terminal.command_timeout', 15);
        $this->maxCommandLength = config('web-terminal.max_command_length', 500);
    }

    /**
     * Execute a terminal command
     */
    public function executeCommand(string $command): string
    {
        // Validate command length
        if (strlen($command) > $this->maxCommandLength) {
            throw new \InvalidArgumentException('Command too long');
        }

        // Parse command
        $cmd = explode(' ', $command)[0];
        $arg = count(explode(' ', $command)) > 1 ? 
               implode(' ', array_slice(explode(' ', $command), 1)) : '';

        // Check for blocked commands
        if (in_array($cmd, $this->blockedCommands)) {
            throw new \SecurityException("Command '{$cmd}' is blocked for security reasons");
        }

        // Handle local commands
        if (method_exists($this, '_' . $cmd)) {
            $method = '_' . $cmd;
            return $this->$method($arg);
        }

        // Check if command exists
        if (!$this->commandExists($cmd)) {
            return "web-terminal: command not found: {$cmd}";
        }

        // Sanitize command
        $sanitizedCommand = $this->sanitizeCommand($command);
        if ($sanitizedCommand === false) {
            throw new \SecurityException('Command contains dangerous patterns');
        }

        // Execute with timeout
        $result = $this->executeWithTimeout($sanitizedCommand, $this->commandTimeout);
        
        // Store in history
        $this->addToHistory($command);
        
        return $result;
    }

    /**
     * Check if command exists
     */
    protected function commandExists(string $command): bool
    {
        $output = shell_exec('command -v ' . escapeshellarg($command));
        return !empty($output);
    }

    /**
     * Sanitize command to prevent injection
     */
    protected function sanitizeCommand(string $command): string|false
    {
        $dangerousPatterns = [
            '/[;&|`$()]/',           // Command chaining and substitution
            '/\s*>\s*\//',           // Redirect to root paths
            '/\s*<\s*\//',           // Input from root paths
            '/\$\{.*\}/',            // Variable substitution
            '/`.*`/',                // Command substitution
            '/\$\(.*\)/',            // Command substitution
            '/\\\\/',                // Escape sequences
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $command)) {
                return false;
            }
        }
        
        return $command;
    }

    /**
     * Execute command with timeout
     */
    protected function executeWithTimeout(string $command, int $timeout = 30): string
    {
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (is_resource($process)) {
            $startTime = time();
            $output = '';
            
            while (proc_get_status($process)['running'] && (time() - $startTime) < $timeout) {
                $output .= fgets($pipes[1], 1024);
                usleep(100000); // 0.1 second
            }
            
            // If still running, terminate
            if (proc_get_status($process)['running']) {
                proc_terminate($process);
                $output .= "\nProcess terminated due to timeout";
            }
            
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            
            return trim($output);
        }
        
        return 'Failed to execute command';
    }

    /**
     * Add command to history
     */
    protected function addToHistory(string $command): void
    {
        $history = Session::get('terminal_history', []);
        
        if (strlen($command) >= 2 && (empty($history) || end($history) !== $command)) {
            $history[] = $command;
            
            // Keep only last 100 commands
            if (count($history) > 100) {
                $history = array_slice($history, -100);
            }
            
            Session::put('terminal_history', $history);
        }
    }

    /**
     * Get current working directory
     */
    public function getCurrentPath(): string
    {
        return getcwd() ?: '/';
    }

    /**
     * Set working directory
     */
    public function setWorkingDirectory(string $path): bool
    {
        // Sanitize path
        $path = str_replace(['../', './'], '', $path);
        return chdir($path);
    }

    /**
     * Get available commands
     */
    public function getAvailableCommands(): array
    {
        $systemCommands = [];
        $binDirs = ['/usr/bin', '/bin', '/usr/local/bin'];
        
        foreach ($binDirs as $dir) {
            if (is_dir($dir)) {
                $commands = scandir($dir);
                $systemCommands = array_merge($systemCommands, $commands);
            }
        }
        
        $localCommands = ['cd', 'pwd', 'whoami', 'hostname', 'ls', 'clear', 'history'];
        
        return array_unique(array_merge($systemCommands, $localCommands));
    }

    /**
     * Get user information
     */
    public function getUserInfo(): array
    {
        return [
            'username' => $this->getUsername(),
            'hostname' => $this->getHostname(),
            'path' => $this->getCurrentPath()
        ];
    }

    /**
     * Get current username
     */
    public function getUsername(): string
    {
        return trim(shell_exec('whoami')) ?: 'unknown';
    }

    /**
     * Get hostname
     */
    public function getHostname(): string
    {
        return trim(shell_exec('hostname')) ?: 'localhost';
    }

    /**
     * Get system uptime
     */
    public function getSystemUptime(): string
    {
        return trim(shell_exec('uptime')) ?: 'Unknown';
    }

    // Local Commands Implementation

    /**
     * Change directory command
     */
    protected function _cd(string $path): string
    {
        if (empty($path)) {
            $path = getenv('HOME') ?: '/';
        }
        
        if ($this->setWorkingDirectory($path)) {
            return '';
        }
        
        return "cd: no such file or directory: {$path}";
    }

    /**
     * Present working directory command
     */
    protected function _pwd(): string
    {
        return $this->getCurrentPath();
    }

    /**
     * Who am I command
     */
    protected function _whoami(): string
    {
        return $this->getUsername();
    }

    /**
     * Hostname command
     */
    protected function _hostname(): string
    {
        return $this->getHostname();
    }

    /**
     * List directory command
     */
    protected function _ls(string $path = ''): string
    {
        if (empty($path)) {
            return shell_exec('ls -la') ?: '';
        }
        
        // Sanitize path
        $path = str_replace(['../', './'], '', $path);
        return shell_exec('ls -la ' . escapeshellarg($path)) ?: '';
    }

    /**
     * Clear command (handled by frontend)
     */
    protected function _clear(): string
    {
        return '';
    }

    /**
     * History command
     */
    protected function _history(string $arg = ''): string
    {
        $history = Session::get('terminal_history', []);
        $limit = !empty($arg) && is_numeric($arg) ? (int)$arg : count($history);
        
        $result = [];
        $startFrom = max(0, count($history) - $limit);
        
        for ($i = $startFrom; $i < count($history); $i++) {
            $result[] = ($i + 1) . '  ' . $history[$i];
        }
        
        return implode("\n", $result);
    }
}