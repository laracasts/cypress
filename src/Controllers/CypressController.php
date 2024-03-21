<?php

namespace Laracasts\Cypress\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

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
        $provider = $request->input('provider', 'users');
        $guard = $request->input('guard');
        $attributes = $request->input('attributes', []);

        if (empty($attributes)) {
            $user = $this->factoryBuilder(
                $this->userClassName($provider),
                $request->input('state', [])
            )->create();
        } else {
            $user = app($this->userClassName($provider))
                ->newQuery()
                ->where($attributes)
                ->first();

            if (!$user) {
                $user = $this->factoryBuilder(
                    $this->userClassName($provider),
                    $request->input('state', [])
                )->create($attributes);
            }
        }

        $user->load($request->input('load', []));

        return tap($user, function ($user) use ($guard) {
            auth($guard)->login($user);

            $user->setHidden([])->setVisible([]);
        });
    }

    public function currentUser(Request $request)
    {
        return auth($request->input('guard'))->user()?->setHidden([])->setVisible([]);
    }

    public function logout(Request $request)
    {
        auth($request->input('guard'))->logout();
    }

    public function factory(Request $request)
    {
        return $this->factoryBuilder(
            $request->input('model'),
            $request->input('state', [])
        )
            ->count(intval($request->input('count', 1)))
            ->create($request->input('attributes'))
            ->each(fn($model) => $model->setHidden([])->setVisible([]))
            ->load($request->input('load', []))
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

    protected function userClassName($provider)
    {
        return config("auth.providers.{$provider}.model");
    }

    protected function factoryBuilder($model, $states = [])
    {
        $factory = $model::factory();

        $states = Arr::wrap($states);

        foreach ($states as $state => $attributes) {
            if (is_int($state)) {
                $state = $attributes;
                $attributes = [];
            }

            $attributes = Arr::wrap($attributes);

            $factory = $factory->{$state}(...$attributes);
        }

        return $factory;
    }
}
