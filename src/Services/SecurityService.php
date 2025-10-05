<?php

namespace SynceraTech\LaravelWebTerminal\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class SecurityService
{
    protected int $maxRequestsPerSession;
    protected int $rateLimitWindow;
    protected bool $enableLogging;

    public function __construct()
    {
        $this->maxRequestsPerSession = config('web-terminal.max_requests_per_session', 50);
        $this->rateLimitWindow = config('web-terminal.rate_limit_window', 600);
        $this->enableLogging = config('web-terminal.enable_logging', true);
    }

    /**
     * Check rate limiting for current session
     */
    public function checkRateLimit(): bool
    {
        $sessionId = Session::getId();
        $cacheKey = "terminal_requests_{$sessionId}";
        
        $requests = Cache::get($cacheKey, []);
        $currentTime = time();
        
        // Clean old requests
        $requests = array_filter($requests, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < $this->rateLimitWindow;
        });
        
        // Check if limit exceeded
        if (count($requests) >= $this->maxRequestsPerSession) {
            $this->logSecurityEvent("Rate limit exceeded for session: {$sessionId}");
            return false;
        }
        
        // Add current request
        $requests[] = $currentTime;
        Cache::put($cacheKey, $requests, $this->rateLimitWindow);
        
        return true;
    }

    /**
     * Check if IP is allowed (if whitelist is configured)
     */
    public function isIpAllowed(string $ip): bool
    {
        $allowedIps = config('web-terminal.allowed_ips', []);
        
        if (empty($allowedIps)) {
            return true; // No whitelist configured
        }
        
        foreach ($allowedIps as $allowedRange) {
            if (strpos($allowedRange, '/') !== false) {
                // CIDR notation
                list($subnet, $mask) = explode('/', $allowedRange);
                if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
                    return true;
                }
            } else {
                // Single IP
                if ($ip === $allowedRange) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Validate terminal access key
     */
    public function validateAccessKey(string $providedKey): bool
    {
        $validKey = config('web-terminal.access_key');
        
        if (empty($validKey)) {
            $this->logSecurityEvent("No access key configured");
            return false;
        }
        
        $isValid = hash_equals($validKey, $providedKey);
        
        if (!$isValid) {
            $this->logSecurityEvent("Invalid access key attempted");
        }
        
        return $isValid;
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $message): void
    {
        if (!$this->enableLogging) {
            return;
        }
        
        $request = request();
        $ip = $request ? $request->ip() : 'unknown';
        $userAgent = $request ? $request->userAgent() : 'unknown';
        
        $logMessage = "IP: {$ip} | UA: {$userAgent} | {$message}";
        
        // Log to Laravel log
        Log::channel('web-terminal')->info($logMessage);
    }

    /**
     * Generate secure access key
     */
    public static function generateSecureKey(): string
    {
        return bin2hex(random_bytes(32)); // 64 character key
    }

    /**
     * Check if shell_exec is available
     */
    public function isShellExecAvailable(): bool
    {
        return function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')));
    }

    /**
     * Validate request for security
     */
    public function validateRequest(Request $request): array
    {
        $errors = [];
        
        // Check if shell_exec is available
        if (!$this->isShellExecAvailable()) {
            $errors[] = 'Shell execution is disabled on this server';
        }
        
        // Check rate limiting
        if (!$this->checkRateLimit()) {
            $errors[] = 'Rate limit exceeded. Too many requests.';
        }
        
        // Check IP whitelist
        if (!$this->isIpAllowed($request->ip())) {
            $errors[] = 'Access denied from your IP address';
            $this->logSecurityEvent("Blocked IP access attempt from: " . $request->ip());
        }
        
        return $errors;
    }
}