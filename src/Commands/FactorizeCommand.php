<?php

namespace Smousss\Laravel\Factorize\Commands;

use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

class FactorizeCommand extends Command
{
    protected $signature = 'smousss:factorize {model?*}';

    protected $description = 'Create a Laravel factory for a given model';

    public function handle() : int
    {
        if (empty(config('factorize.secret_key'))) {
            $this->error('Please generate a secret key on smousss.com and add it to your .env file as SMOUSSS_SECRET_KEY.');

            return self::FAILURE;
        }

        if (! ($model = $this->argument('model'))) {
            $model = $this->ask("Name of your model (e.g. App\Models\Post)", 'App\Models\Post');
        }

        foreach (Arr::wrap($model) as $key => $value) {
            if ($key > 0) {
                $this->newLine();
            }

            $this->generateFactory($value);
        }

        return self::SUCCESS;
    }

    public function generateFactory(string $model) : void
    {
        $modelInstance = (new $model);

        $model_code = $this->getSourceCodeForModel($modelInstance);

        $model_schema = implode('; ', $this->getSchemaForModel($modelInstance));

        $this->line("GPT-4 is generating tokens for your {$model} factory…");

        $response = Http::withToken(config('factorize.secret_key'))
            ->timeout(300)
            ->retry(3)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(config('factorize.debug', false)
                ? 'https://smousss.test/api/factorize'
                : 'https://smousss.com/api/factorize', compact('model_code', 'model_schema'))
            ->throw()
            ->json();

        $baseModelName = Str::after($model, 'App\\Models\\');

        File::put(base_path($path = "database/factories/{$baseModelName}Factory.php"), trim(trim($response['data'], '`ph')) . PHP_EOL);

        $this->info("Your factory has been created at $path! 🎉 (Tokens: {$response['meta']['consumed_tokens']})");
    }

    protected function getSourceCodeForModel(Model $model) : string
    {
        $sourceCode = File::get((new ReflectionClass($model::class))->getFileName());

        $sourceCode = str_replace("\t", '', $sourceCode);
        $sourceCode = str_replace("\n", ' ', $sourceCode);

        return preg_replace('/ {2,}/', ' ', $sourceCode);
    }

    protected function getSchemaForModel(Model $model)
    {
        return DB::getDoctrineConnection()->getDatabasePlatform()->getCreateTableSQL(
            DB::getDoctrineSchemaManager()->introspectTable($model->getTable())
        );
    }
}
