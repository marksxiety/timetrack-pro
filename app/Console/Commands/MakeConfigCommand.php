<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeConfigCommand extends Command
{
    protected $signature = 'make:config';

    protected $description = 'Create setup/config.json with blank default shift codes and default minimum overtime hours';

    public function handle()
    {
        $path = base_path('setup/config.json');

        if (file_exists($path)) {
            if (!$this->confirm('setup/config.json already exists. Overwrite it?')) {
                $this->info('Cancelled.');
                return self::SUCCESS;
            }
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $defaultConfig = [
            'default_shift_codes' => array_map(fn($day) => ['day' => $day, 'code' => ''], $days),
            'minimum_overtime_hours' => 1,
        ];

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode($defaultConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

        $this->info('setup/config.json created successfully.');
        $this->info('Update the file with your organization\'s default shift codes.');
    }
}
