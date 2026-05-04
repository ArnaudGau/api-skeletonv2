<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CreateApiCommand extends Command
{
    protected $signature = 'create-api {name : Model/controller name, for example User or BlogPost} {--force : Overwrite existing generated files}';

    protected $description = 'Create CRUD API files for a model.';

    public function handle(): int
    {
        $studly = Str::studly($this->argument('name'));
        $camel = Str::camel($studly);
        $table = Str::snake(Str::pluralStudly($studly));
        $routePrefix = Str::kebab(Str::pluralStudly($studly));

        $files = [
            app_path("Models/{$studly}.php") => $this->stub('model', compact('studly')),
            app_path("Http/Controllers/{$studly}Controller.php") => $this->stub('controller', compact('studly')),
            app_path("Queries/{$studly}Query.php") => $this->stub('query', compact('studly', 'camel')),
            app_path("Http/Resources/{$studly}Resource.php") => $this->stub('resource', compact('studly')),
            app_path("Http/Requests/{$studly}/StoreRequest.php") => $this->stub('request', [
                'studly' => $studly,
                'requestClass' => 'StoreRequest',
            ]),
            app_path("Http/Requests/{$studly}/UpdateRequest.php") => $this->stub('request', [
                'studly' => $studly,
                'requestClass' => 'UpdateRequest',
            ]),
            app_path("Policies/{$studly}Policy.php") => $this->stub('policy', compact('studly')),
            $this->migrationPath($table) => $this->stub('migration', compact('table')),
            base_path("routes/api/{$routePrefix}.php") => $this->stub('route', compact('studly')),
        ];

        foreach ($files as $path => $contents) {
            $this->writeFile($path, $contents);
        }

        $this->ensureApiRouteRegistered($routePrefix);
        $this->info("API CRUD generated for {$studly}.");

        return self::SUCCESS;
    }

    private function writeFile(string $path, string $contents): void
    {
        if (File::exists($path) && ! $this->option('force')) {
            $this->warn("Skipped existing file: {$path}");

            return;
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $contents);

        $this->line("Created: {$path}");
    }

    private function stub(string $name, array $replacements = []): string
    {
        $stub = File::get(base_path("stubs/api-crud/{$name}.stub"));

        foreach ($replacements as $key => $value) {
            $stub = str_replace('{{ ' . $key . ' }}', $value, $stub);
        }

        return $stub;
    }

    private function ensureApiRouteRegistered(string $routePrefix): void
    {
        $path = base_path('routes/api.php');
        $registration = "Route::prefix('{$routePrefix}')->group(base_path('routes/api/{$routePrefix}.php'));";

        if (! File::exists($path)) {
            File::put($path, "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n{$registration}\n");
            $this->line("Created: {$path}");

            return;
        }

        $contents = File::get($path);

        if (str_contains($contents, $registration)) {
            $this->warn("Skipped existing route registration: {$routePrefix}");

            return;
        }

        File::append($path, "\n{$registration}\n");
        $this->line("Updated: {$path}");
    }

    private function migrationPath(string $table): string
    {
        $existing = File::glob(database_path("migrations/*_create_{$table}_table.php"));

        return $existing[0] ?? database_path('migrations/' . $this->migrationFilename($table));
    }

    private function migrationFilename(string $table): string
    {
        return now()->format('Y_m_d_His') . "_create_{$table}_table.php";
    }
}
