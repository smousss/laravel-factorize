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
use Illuminate\Http\Client\RequestException;

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

        foreach (Arr::wrap($model) as $key => $model) {
            if ($key > 0) {
                $this->newLine();
            }

            $modelInstance = (new $model);

            $model_code = $this->getSourceCodeForModel($modelInstance);

            $model_schema = implode('; ', $this->getSchemaForModel($modelInstance));

            $this->line("GPT-4 is generating tokens for your {$model} factory…");

            try {
                $response = Http::withToken(config('factorize.secret_key'))
                    ->timeout(300)
                    ->retry(3)
                    ->withHeaders(['Accept' => 'application/json'])
                    ->post(
                        config('factorize.debug', false)
                            ? 'https://smousss.test/api/factorize'
                            : 'https://smousss.com/api/factorize',
                        compact('model_code', 'model_schema')
                    )
                    ->throw()
                    ->json();

                $baseModelName = Str::after($model, 'App\\Models\\');

                File::put(base_path($path = "database/factories/{$baseModelName}Factory.php"), trim(trim($response['data'], '`ph')) . PHP_EOL);

                $this->info("Your factory has been created at $path! 🎉 (Tokens: {$response['meta']['consumed_tokens']})");
            } catch (RequestException $e) {
                $this->error($e->response->json()['message']);
            }
        }

        return self::SUCCESS;
    }

    public function generateFactory(string $model)
    {
    }

    protected function getSourceCodeForModel(Model $model) : string
    {
        $file = (new ReflectionClass($model::class))->getFileName();

        return str(File::get($file))
            ->replace(["\t", "\n"], [' ', ' '])
            ->replaceMatches('/ {2,}/', ' ')
            ->replaceMatches('/\/\*[\s\S]*?\*\/|\/\/.*/', '');
    }

    protected function getSchemaForModel(Model $model)
    {
        return DB::getDoctrineConnection()->getDatabasePlatform()->getCreateTableSQL(
            DB::getDoctrineSchemaManager()->introspectTable($model->getTable())
        );
    }
}
