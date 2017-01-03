<?php

namespace Illuminate\Foundation\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\ParameterBag;

class CleanRequest
{
    /**
     * The types of cleaners to use for this request.
     *
     * @var array
     */
    protected $cleaners = [
        'trim' => true,
        'null' => true,
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$cleaners)
    {
        $this->setCleaners($cleaners);

        $this->clean($request);

        return $next($request);
    }

    /**
     * Set the list of cleaners to be used.
     *
     * @param  array  $cleaners
     * @return void
     */
    protected function setCleaners(array $cleaners)
    {
        if (empty($cleaners)) {
            return;
        }

        $cleaners = array_flip($cleaners);

        foreach ($this->cleaners as $cleaner => $value) {
            $this->cleaners[$cleaner] = array_key_exists($cleaner, $cleaners);
        }
    }

    /**
     * Clean the request's data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clean($request)
    {
        $this->cleanBag($request->query);

        $this->cleanBag($request->request);

        if ($request->isJson()) {
            $this->cleanBag($request->json());
        }
    }

    /**
     * Clean the data in the parameter bag.
     *
     * @param  \Symfony\Component\HttpFoundation\ParameterBag  $bag
     * @return void
     */
    protected function cleanBag(ParameterBag $bag)
    {
        $bag->replace($this->cleanArray($bag->all()));
    }

    /**
     * Clean the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function cleanValue($value)
    {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }

        if ($this->cleaners['trim'] && is_string($value)) {
            $value = trim($value);
        }

        if ($this->cleaners['null'] && $value === '') {
            $value = null;
        }

        return $value;
    }

    /**
     * Clean the data in the given array.
     *
     * @param  array  $data
     * @return array
     */
    protected function cleanArray(array $data)
    {
        return array_map(function ($value) {
            return $this->cleanValue($value);
        }, $data);
    }
}
