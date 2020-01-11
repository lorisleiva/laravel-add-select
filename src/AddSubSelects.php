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
        $keys = array_unique(is_string($keys) ? func_get_args() : $keys);

        // Prepare the wrapper query to attach subSelect queries.
        $wrapperQuery = $this->getSubSelectWrapperQuery();
        
        // Add a subSelect query for each key that has a subSelect method
        // And keep track of those loaded keys in a different array.
        $loadedKeys = [];
        foreach ($keys as $key) {
            if ($query = $this->getSubSelectQuery($key)) {
                $wrapperQuery->selectSub($query->limit(1), $key);
                $loadedKeys[] = $key;
            }
        }

        // Assign attributes based on the wrapped query results for each loaded key.
        $result = $wrapperQuery->first();
        foreach ($loadedKeys as $key) {
            $this->attributes[$key] = $result->{$key};
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

    protected function getSubSelectWrapperQuery()
    {
        return $this->query()
            ->getQuery()
            ->where($this->getKeyName(), $this->getKey())
            ->limit(1);
    }

    protected function getValueFromSubSelectQuery($key, $query)
    {
        return $this->getSubSelectWrapperQuery()
            ->selectSub($query->limit(1), $key)
            ->first()
            ->{$key};
    }
}
