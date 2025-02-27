<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TaskFilter
{
    protected array $filters;

    public function __construct(Request $request)
    {
        $this->filters = $request->only(['name', 'description', 'completed_at']);
    }

    public function apply(Builder $query): Builder
    {
        return $query->where(function ($q) {
            foreach ($this->filters as $key => $value) {
                if (empty($value)) {
                    continue;
                }

                switch ($key) {
                    case 'name':
                    case 'description':
                        $q->orWhere($key, 'like', "%{$value}%");
                        break;
                    case 'completed_at':
                        $q->orWhereDate($key, $value);
                        break;
                }
            }
        });
    }
}