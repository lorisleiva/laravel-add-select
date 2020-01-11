<?php

namespace Lorisleiva\LaravelAddSelect;

use Illuminate\Support\Str;

trait AddSubSelects
{
    public static function bootAddSubSelects()
    {
        if (property_exists(static::class, 'withSelect')) {
            $withSelect = (new static)->withSelect;
            static::addGlobalScope(function ($query) use ($withSelect) {
                $query->withSelect($withSelect);
            });
        }
    }

    public function loadSelect($keys)
    {
        foreach ((is_string($keys) ? func_get_args() : $keys) as $key) {
            if ($query = $this->getSubSelectQuery($key)) {
                $this->attributes[$key] = $this->getValueFromSubSelectQuery($key, $query);
            }
        }

        return $this;
    }

    public function getAttribute($key)
    {
        if (! array_key_exists($key, $this->attributes) &&
            ! $this->hasGetMutator($key) &&
            $query = $this->getSubSelectQuery($key)) {
            return $this->attributes[$key] = $this->getValueFromSubSelectQuery($key, $query);
        }

        return parent::getAttribute($key);
    }

    public function getSubSelectQuery($key)
    {
        if (method_exists($this, $method = 'add'.Str::studly($key).'Select')) {
            return call_user_func([$this, $method]);
        }
    }

    protected function getValueFromSubSelectQuery($key, $query)
    {
        return $this->query()
            ->getQuery()
            ->where($this->getKeyName(), $this->getKey())
            ->selectSub($query->limit(1), $key)
            ->limit(1)
            ->first()
            ->{$key};
    }
}
