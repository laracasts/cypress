<?php

namespace Laracasts\Cypress\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CypressController
{
    public function login(Request $request)
    {
        if ($attributes = $request->input('attributes')) {
            $query = app($this->userClassName())->query();

            foreach ($attributes as $name => $value) {
                $query->where($name, $value);
            }

            $user = $query->first();
        }

        if (!isset($user)) {
            $user = $this->factoryBuilder($this->userClassName())->create(
                $request->input('attributes', [])
            );
        }

        auth()->login($user);

        return $user->setHidden([]);
    }

    public function logout()
    {
        auth()->logout();
    }

    public function factory(Request $request)
    {
        $times = intval($request->input('times', 1));

        $collection = $this->factoryBuilder($request->input('model'))
            ->times($times)
            ->create($request->input('attributes'))
            ->each(function ($model) {
                $model->setHidden([]);
            });

        return $collection->count() === 1 ? $collection->first() : $collection;
    }

    public function artisan(Request $request)
    {
        Artisan::call(
            $request->input('command'),
            $request->input('parameters', [])
        );
    }

    public function csrfToken()
    {
        return response()->json(csrf_token());
    }

    public function runPhp(Request $request)
    {
        $code = $request->input('command');

        if ($code[-1] !== ';') {
            $code .= ';';
        }

        if (!Str::contains($code, 'return')) {
            $code = 'return ' . $code;
        }

        return response()->json([
            'result' => eval($code),
        ]);
    }

    protected function userClassName()
    {
        return config('auth.providers.users.model');
    }

    protected function factoryBuilder($model)
    {
        // Should we use legacy factories?
        if (class_exists('Illuminate\Database\Eloquent\Factory')) {
            return factory($model);
        }

        return (new $model())->factory();
    }
}
