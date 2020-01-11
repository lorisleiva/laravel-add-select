<?php

namespace Lorisleiva\LaravelAddSelect;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\LaravelAddSelect\AddSubSelects;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        Builder::macro('withSelect', function ($keys) {

            // Guard: Ensure model uses the AddSubSelects trait.
            if (! in_array(AddSubSelects::class, class_uses_recursive($this->model))) {
                $classname = class_basename(get_class($this->model));
                throw Exception("Model [$classname] does not use the AddSubSelects trait.");
            }

            // Ensure query columns are initialised.
            if (is_null($this->query->columns)) {
                $this->query->select($this->query->from.'.*');
            }

            // Add a selectSub query for each key.
            foreach ((is_string($keys) ? func_get_args() : $keys) as $key) {
                if ($subQuery = $this->model->getSubSelectQuery($key)) {
                    $this->query->selectSub($subQuery->limit(1), $key);
                }
            }

            return $this;
        });
    }
}
