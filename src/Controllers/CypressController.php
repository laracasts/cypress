<?php

namespace Laracasts\Cypress\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class CypressController
{
    public function routes()
    {
        return collect(Route::getRoutes()->getRoutes())
            ->map(function (\Illuminate\Routing\Route $route) {
                return [
                    'name' => $route->getName(),
                    'domain' => $route->getDomain(),
                    'action' => $route->getActionName(),
                    'uri' => $route->uri(),
                    'method' => $route->methods(),
                ];
            })
            ->keyBy('name');
    }

    public function login(Request $request)
    {
        $attributes = $request->input('attributes', []);

        if (empty($attributes)) {
            $user = $this->factoryBuilder($this->userClassName())->create();
        } else {
            $user = app($this->userClassName())
                ->newQuery()
                ->where($attributes)
                ->first();

            if (!$user) {
                $user = $this->factoryBuilder($this->userClassName())->create(
                    $attributes
                );
            }
        }

        return tap($user, function ($user) {
            auth()->login($user);

            $user->setHidden([]);
        });
    }

    public function logout()
    {
        auth()->logout();
    }

    public function factory(Request $request)
    {
        return $this->factoryBuilder($request->input('model'))
            ->times(intval($request->input('times', 1)))
            ->create($request->input('attributes'))
            ->each->setHidden([])
            ->load($request->input('relations', []))
            ->pipe(function ($collection) {
                return $collection->count() > 1
                    ? $collection
                    : $collection->first();
            });
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
