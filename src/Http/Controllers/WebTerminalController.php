<?php

namespace SynceraTech\LaravelWebTerminal\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Routing\Controller;
use SynceraTech\LaravelWebTerminal\Services\TerminalService;
use SynceraTech\LaravelWebTerminal\Services\SecurityService;

class WebTerminalController extends Controller
{
    protected TerminalService $terminalService;
    protected SecurityService $securityService;

    public function __construct(TerminalService $terminalService, SecurityService $securityService)
    {
        $this->terminalService = $terminalService;
        $this->securityService = $securityService;
    }

    /**
     * Display the terminal interface
     */
    public function index(): View
    {
        $initialPath = $this->terminalService->getCurrentPath();
        $commandsList = $this->terminalService->getAvailableCommands();
        
        return view('web-terminal::terminal', [
            'initialPath' => $initialPath,
            'commandsList' => json_encode($commandsList),
            'userInfo' => $this->terminalService->getUserInfo()
        ]);
    }

    /**
     * Execute terminal command via AJAX
     */
    public function execute(Request $request): JsonResponse
    {
        $request->validate([
            'command' => 'required|string|max:' . config('web-terminal.max_command_length', 500),
            'path' => 'nullable|string|max:4096'
        ]);

        try {
            // Set working directory if provided
            if ($request->has('path')) {
                $this->terminalService->setWorkingDirectory($request->input('path'));
            }

            $command = $request->input('command');
            
            // Log the command execution attempt
            $this->securityService->logSecurityEvent("Command executed: {$command}");

            // Execute the command
            $result = $this->terminalService->executeCommand($command);
            $currentPath = $this->terminalService->getCurrentPath();

            return response()->json([
                'success' => true,
                'result' => $result,
                'path' => $currentPath
            ]);

        } catch (\Exception $e) {
            $this->securityService->logSecurityEvent("Command execution error: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Command execution failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get current system information
     */
    public function info(): JsonResponse
    {
        return response()->json([
            'user' => $this->terminalService->getUsername(),
            'hostname' => $this->terminalService->getHostname(),
            'path' => $this->terminalService->getCurrentPath(),
            'uptime' => $this->terminalService->getSystemUptime(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version()
        ]);
    }

    /**
     * Get command history
     */
    public function history(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $history = session('terminal_history', []);
        
        return response()->json([
            'history' => array_slice($history, -$limit)
        ]);
    }

    /**
     * Clear command history
     */
    public function clearHistory(): JsonResponse
    {
        session()->forget('terminal_history');
        
        return response()->json([
            'success' => true,
            'message' => 'History cleared'
        ]);
    }
}