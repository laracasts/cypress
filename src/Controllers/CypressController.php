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
        if($request->attributes){
            // find user based on attributes
            /**@var Builder $query */
            $query = app($this->userClassName())
                ->query();
            collect($request->attributes)->each(function ($attribute, $key) use($query){
                $query->where($key, '=', $attribute);
            });
            $user = $query->first();
        }

        if(!isset($user)){
            $user = factory($this->userClassName())
                ->create($request->input('attributes', []));
        }

        auth()->login($user);
        return $user;
    }

    public function logout()
    {
        auth()->logout();
    }

    public function factory(Request $request)
    {
        return factory($request->input('model'))
            ->times($request->input('times'))
            ->create($request->input('attributes'));
    }

    public function artisan(Request $request)
    {
        Artisan::call($request->input('command'), $request->input('parameters', []));
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

        if (! Str::contains($code, 'return')) {
            $code = 'return ' . $code;
        }

        return response()->json([
            'result' => eval($code)
        ]);
    }

    protected function userClassName()
    {
        return config('auth.providers.users.model');
    }
}
