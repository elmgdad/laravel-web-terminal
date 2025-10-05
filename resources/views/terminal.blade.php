<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Web Terminal</title>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --background-color: {{ config('web-terminal.view.theme') === 'light' ? '#ffffff' : '#0d1117' }};
            --terminal-bg: {{ config('web-terminal.view.theme') === 'light' ? '#f6f8fa' : '#161b22' }};
            --text-color: {{ config('web-terminal.view.theme') === 'light' ? '#24292f' : '#c9d1d9' }};
            --primary-color: {{ config('web-terminal.view.theme') === 'light' ? '#0969da' : '#58a6ff' }};
            --success-color: {{ config('web-terminal.view.theme') === 'light' ? '#1f883d' : '#3fb950' }};
            --error-color: {{ config('web-terminal.view.theme') === 'light' ? '#d1242f' : '#f85149' }};
            --border-color: {{ config('web-terminal.view.theme') === 'light' ? '#d0d7de' : '#30363d' }};
            --font-family: {{ config('web-terminal.view.font_family', 'JetBrains Mono, Monaco, Consolas, monospace') }};
            --font-size: {{ config('web-terminal.view.font_size', '14px') }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--background-color);
            color: var(--text-color);
            height: 100vh;
            overflow: hidden;
        }

        .terminal-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .terminal-header {
            background: var(--terminal-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px 8px 0 0;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .terminal-title {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .terminal-info {
            font-size: 12px;
            opacity: 0.7;
        }

        .terminal-window {
            background: var(--terminal-bg);
            border: 1px solid var(--border-color);
            border-top: none;
            border-radius: 0 0 8px 8px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .terminal-content {
            flex: 1;
            padding: 16px;
            overflow-y: auto;
            font-size: var(--font-size);
            line-height: 1.4;
        }

        .terminal-line {
            display: block;
            margin-bottom: 4px;
        }

        .terminal-line.current {
            display: flex;
            align-items: center;
        }

        .terminal-prompt {
            color: var(--primary-color);
            margin-right: 8px;
            user-select: none;
        }

        .terminal-separator {
            color: var(--error-color);
            margin-right: 8px;
            user-select: none;
        }

        .terminal-input {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-color);
            font-family: inherit;
            font-size: inherit;
        }

        .terminal-output {
            white-space: pre-wrap;
            word-break: break-all;
        }

        .terminal-error {
            color: var(--error-color);
        }

        .terminal-success {
            color: var(--success-color);
        }

        .cursor {
            display: inline-block;
            background-color: var(--text-color);
            width: 2px;
            height: 1.2em;
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .status-bar {
            background: var(--terminal-bg);
            border-top: 1px solid var(--border-color);
            padding: 8px 16px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .loading {
            opacity: 0.5;
            pointer-events: none;
        }

        /* Scrollbar styling */
        .terminal-content::-webkit-scrollbar {
            width: 8px;
        }

        .terminal-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .terminal-content::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        .terminal-content::-webkit-scrollbar-thumb:hover {
            background: var(--text-color);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .terminal-container {
                padding: 10px;
            }
            
            .terminal-header {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }
            
            :root {
                --font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="terminal-container">
        <div class="terminal-header">
            <div class="terminal-title">
                <span>🖥️ Laravel Web Terminal</span>
            </div>
            <div class="terminal-info">
                <span id="user-info">{{ $userInfo['username'] }}@{{ $userInfo['hostname'] }}</span>
                <span> | </span>
                <span>Laravel {{ app()->version() }}</span>
            </div>
        </div>
        
        <div class="terminal-window">
            <div class="terminal-content" id="terminal-content">
                <div class="terminal-line">Welcome to Laravel Web Terminal</div>
                <div class="terminal-line">Type 'help' for available commands</div>
                <div class="terminal-line current">
                    <span class="terminal-prompt" id="current-path">{{ $initialPath }}</span>
                    <span class="terminal-separator">❯</span>
                    <input type="text" class="terminal-input" id="command-input" autocomplete="off" spellcheck="false">
                    <span class="cursor"></span>
                </div>
            </div>
            
            <div class="status-bar">
                <div>
                    <span id="status-text">Ready</span>
                </div>
                <div>
                    <span>Press Ctrl+C to interrupt</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const config = {
            executeUrl: '{{ route("web-terminal.execute") }}',
            historyUrl: '{{ route("web-terminal.history") }}',
            csrfToken: '{{ csrf_token() }}',
            commandsList: {!! $commandsList !!}
        };

        // Terminal state
        let currentPath = '{{ $initialPath }}';
        let commandHistory = [];
        let historyIndex = 0;
        let isExecuting = false;

        // DOM elements
        const terminalContent = document.getElementById('terminal-content');
        const commandInput = document.getElementById('command-input');
        const currentPathSpan = document.getElementById('current-path');
        const statusText = document.getElementById('status-text');

        // Initialize terminal
        document.addEventListener('DOMContentLoaded', function() {
            commandInput.focus();
            loadCommandHistory();
            
            // Handle command input
            commandInput.addEventListener('keydown', handleKeyDown);
            
            // Keep focus on input
            document.addEventListener('click', () => commandInput.focus());
        });

        function handleKeyDown(event) {
            if (isExecuting) return;

            switch(event.key) {
                case 'Enter':
                    executeCommand();
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    navigateHistory(-1);
                    break;
                case 'ArrowDown':
                    event.preventDefault();
                    navigateHistory(1);
                    break;
                case 'Tab':
                    event.preventDefault();
                    // TODO: Implement command completion
                    break;
                case 'c':
                    if (event.ctrlKey) {
                        event.preventDefault();
                        interruptCommand();
                    }
                    break;
            }
        }

        function executeCommand() {
            const command = commandInput.value.trim();
            if (!command) return;

            // Add to history
            addToHistory(command);
            
            // Display command in terminal
            displayCommand(command);
            
            // Clear input
            commandInput.value = '';
            
            // Handle local commands
            if (handleLocalCommand(command)) {
                return;
            }
            
            // Execute remote command
            executeRemoteCommand(command);
        }

        function executeRemoteCommand(command) {
            setExecuting(true);
            
            $.ajax({
                url: config.executeUrl,
                method: 'POST',
                data: {
                    command: command,
                    path: currentPath,
                    _token: config.csrfToken
                },
                success: function(response) {
                    if (response.success) {
                        displayOutput(response.result);
                        currentPath = response.path;
                        updateCurrentPath();
                    } else {
                        displayError(response.error || 'Command execution failed');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.error || 'Network error occurred';
                    displayError(errorMsg);
                },
                complete: function() {
                    setExecuting(false);
                    scrollToBottom();
                }
            });
        }

        function handleLocalCommand(command) {
            const cmd = command.toLowerCase().trim();
            
            switch(cmd) {
                case 'clear':
                    clearTerminal();
                    return true;
                case 'help':
                    displayHelp();
                    return true;
                default:
                    return false;
            }
        }

        function displayCommand(command) {
            const line = document.createElement('div');
            line.className = 'terminal-line';
            line.innerHTML = `<span class="terminal-prompt">${currentPath}</span><span class="terminal-separator">❯</span><span>${escapeHtml(command)}</span>`;
            
            // Insert before current input line
            const currentLine = document.querySelector('.terminal-line.current');
            terminalContent.insertBefore(line, currentLine);
        }

        function displayOutput(output) {
            if (!output) return;
            
            const line = document.createElement('div');
            line.className = 'terminal-line terminal-output';
            line.textContent = output;
            
            const currentLine = document.querySelector('.terminal-line.current');
            terminalContent.insertBefore(line, currentLine);
        }

        function displayError(error) {
            const line = document.createElement('div');
            line.className = 'terminal-line terminal-error';
            line.textContent = error;
            
            const currentLine = document.querySelector('.terminal-line.current');
            terminalContent.insertBefore(line, currentLine);
        }

        function displayHelp() {
            const helpText = `
Available commands:
  clear     - Clear the terminal screen
  help      - Show this help message
  history   - Show command history
  pwd       - Print working directory
  ls        - List directory contents
  cd        - Change directory
  whoami    - Display current user
  hostname  - Display hostname

Use arrow keys to navigate command history.
Press Ctrl+C to interrupt running commands.`;

            displayOutput(helpText);
        }

        function clearTerminal() {
            const lines = terminalContent.querySelectorAll('.terminal-line:not(.current)');
            lines.forEach(line => line.remove());
        }

        function addToHistory(command) {
            if (command && (!commandHistory.length || commandHistory[commandHistory.length - 1] !== command)) {
                commandHistory.push(command);
                if (commandHistory.length > 100) {
                    commandHistory.shift();
                }
            }
            historyIndex = commandHistory.length;
        }

        function navigateHistory(direction) {
            if (!commandHistory.length) return;

            historyIndex += direction;
            historyIndex = Math.max(0, Math.min(historyIndex, commandHistory.length));

            if (historyIndex < commandHistory.length) {
                commandInput.value = commandHistory[historyIndex];
            } else {
                commandInput.value = '';
            }
        }

        function loadCommandHistory() {
            $.get(config.historyUrl)
                .done(function(response) {
                    if (response.history) {
                        commandHistory = response.history;
                        historyIndex = commandHistory.length;
                    }
                });
        }

        function updateCurrentPath() {
            currentPathSpan.textContent = currentPath;
        }

        function setExecuting(executing) {
            isExecuting = executing;
            statusText.textContent = executing ? 'Executing...' : 'Ready';
            
            if (executing) {
                document.body.classList.add('loading');
            } else {
                document.body.classList.remove('loading');
            }
        }

        function interruptCommand() {
            if (isExecuting) {
                // In a real implementation, you might want to send an interrupt signal
                displayOutput('\n^C');
                setExecuting(false);
            }
        }

        function scrollToBottom() {
            terminalContent.scrollTop = terminalContent.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>