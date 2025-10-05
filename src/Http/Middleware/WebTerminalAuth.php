<?php

namespace SmartWF\LaravelWebTerminal\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SmartWF\LaravelWebTerminal\Services\SecurityService;

class WebTerminalAuth
{
    protected SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if access key is required
        if (config('web-terminal.require_key', true)) {
            if (!$request->has('key')) {
                $this->securityService->logSecurityEvent('Access attempt without key');
                return response('Access Denied - Key Required', 403);
            }

            if (!$this->securityService->validateAccessKey($request->get('key'))) {
                $this->securityService->logSecurityEvent('Access attempt with invalid key');
                return response('Access Denied - Invalid Key', 403);
            }
        }

        // Validate request security
        $errors = $this->securityService->validateRequest($request);
        if (!empty($errors)) {
            return response(implode(', ', $errors), 403);
        }

        return $next($request);
    }
}