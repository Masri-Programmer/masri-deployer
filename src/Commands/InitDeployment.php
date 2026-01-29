<?php

namespace Masri\Deployer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InitDeployment extends Command
{
    protected $signature = 'deploy:init';
    protected $description = 'Generate custom deployment scripts (ship.sh and deploy.sh)';

    public function handle()
    {
        $this->info("🚀 Initializing Masri Deployment Scripts...");

        // 1. Ask Questions
        $remoteUser = $this->ask('Remote SSH User', 'masri');
        $remoteHost = $this->ask('Remote Host', 'gienah.uberspace.de');

        // Try to guess the app name from the folder
        $defaultApp = basename(base_path());
        $remotePath = $this->ask('Remote App Path', "/var/www/virtual/$remoteUser/$defaultApp");

        $ssrProcess = $this->ask('PM2 Process Name', "$defaultApp-ssr");

        // 2. Process Files
        $this->generateFile('ship.stub', 'ship.sh', [
            '{{REMOTE_USER}}' => $remoteUser,
            '{{REMOTE_HOST}}' => $remoteHost,
            '{{REMOTE_APP_PATH}}' => $remotePath,
            '{{SSR_PROCESS}}' => $ssrProcess,
        ]);

        $this->generateFile('deploy.stub', 'deploy.sh', [
            '{{REMOTE_APP_PATH}}' => $remotePath,
        ]);

        $this->info("✅ Scripts generated successfully!");
        $this->info("👉 Run './ship.sh' to deploy.");
    }

    protected function generateFile($stubName, $destinationName, $replacements)
    {
        $stubPath = __DIR__ . "/../Stubs/$stubName";
        $destinationPath = base_path($destinationName);

        if (File::exists($destinationPath)) {
            if (!$this->confirm("File $destinationName already exists. Overwrite?")) {
                return;
            }
        }

        $content = File::get($stubPath);
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);

        File::put($destinationPath, $content);

        // Make executable
        chmod($destinationPath, 0755);

        $this->line("Created: $destinationName");
    }
}