<?php

namespace SmartWF\LaravelWebTerminal\Console\Commands;

use Illuminate\Console\Command;
use SmartWF\LaravelWebTerminal\Services\SecurityService;

class GenerateKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'web-terminal:generate-key 
                           {--show : Display the key instead of modifying files}
                           {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a secure access key for the web terminal';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $key = SecurityService::generateSecureKey();

        if ($this->option('show')) {
            $this->line('<comment>' . $key . '</comment>');
            return self::SUCCESS;
        }

        // Check if key already exists
        if (config('web-terminal.access_key') && !$this->option('force')) {
            $this->error('Web terminal key already exists.');
            $this->line('Use <info>--force</info> to overwrite the existing key.');
            return self::FAILURE;
        }

        // Update .env file
        if ($this->updateEnvironmentFile($key)) {
            $this->info('Web terminal key generated successfully.');
            $this->line('Access key: <comment>' . $key . '</comment>');
            $this->newLine();
            $this->line('Your web terminal will be available at:');
            $this->line('<info>' . url(config('web-terminal.route.prefix', 'web-terminal')) . '?key=' . $key . '</info>');
            return self::SUCCESS;
        }

        $this->error('Failed to update environment file.');
        $this->line('Generated key: <comment>' . $key . '</comment>');
        $this->line('Please add this to your .env file manually:');
        $this->line('<info>WEB_TERMINAL_KEY=' . $key . '</info>');
        return self::FAILURE;
    }

    /**
     * Update the environment file with the new key.
     */
    protected function updateEnvironmentFile(string $key): bool
    {
        $envPath = app()->environmentFilePath();

        if (!file_exists($envPath)) {
            return false;
        }

        $envContent = file_get_contents($envPath);

        // Check if WEB_TERMINAL_KEY already exists
        if (preg_match('/^WEB_TERMINAL_KEY=.*$/m', $envContent)) {
            // Update existing key
            $envContent = preg_replace(
                '/^WEB_TERMINAL_KEY=.*$/m',
                'WEB_TERMINAL_KEY=' . $key,
                $envContent
            );
        } else {
            // Add new key
            $envContent .= "\n# Web Terminal Configuration\n";
            $envContent .= "WEB_TERMINAL_KEY=" . $key . "\n";
        }

        return file_put_contents($envPath, $envContent) !== false;
    }
}