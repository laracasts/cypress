<?php

namespace Laracasts\Cypress\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Laracasts\Cypress\CypressServiceProvider;
use Laracasts\Cypress\Tests\Support\TestUser;
use Orchestra\Testbench\TestCase;

class CypressControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CypressServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->withFactories(__DIR__ . '/database/factories');

        config(['auth.providers.users.model' => TestUser::class]);
    }

    /** @test */
    function it_fetches_a_collection_of_named_routes()
    {
        Route::get('foo')->name('home');

        $response = $this->post(route('cypress.routes'));

        $response->assertJsonFragment([
            'uri' => 'foo',
            'name' => 'home',
            'method' => ['GET', 'HEAD'],
            'action' => 'Closure',
            'domain' => null,
        ]);

        $this->assertArrayHasKey('home', $response->json());
    }

    /** @test */
    public function it_logs_a_new_user_in()
    {
        $this->post(route('cypress.login'));

        $this->assertTrue(auth()->check());
    }

    /** @test */
    public function it_makes_all_logged_in_user_attributes_visible()
    {
        $response = $this->post(route('cypress.login'));

        $this->assertTrue(auth()->check());

        // The TestUser model sets the "plan" field to hidden. But
        // when we fetch it with Cypress, it should be visible.
        $this->assertEquals('monthly', $response->json()['plan']);
    }

    /** @test */
    public function it_logs_a_new_user_in_with_the_given_attributes()
    {
        $this->post(route('cypress.login'), [
            'attributes' => ['name' => 'Frank'],
        ]);

        $this->assertDatabaseHas('users', ['name' => 'Frank']);
    }

    /** @test */
    public function it_logs_an_existing_user_in_with_the_given_attribute()
    {
        factory(TestUser::class)->create([
            'name' => 'Joe',
        ]);

        $frank = factory(TestUser::class)->create([
            'name' => 'Frank',
            'plan' => 'monthly',
        ]);

        $response = $this->post(route('cypress.login'), [
            'attributes' => ['name' => 'Frank', 'plan' => 'monthly'],
        ]);

        $this->assertEquals(2, TestUser::count());
        $this->assertEquals($frank->id, $response->json()['id']);
    }

    /** @test */
    public function it_logs_a_user_out()
    {
        $this->post(route('cypress.login'));

        $this->post(route('cypress.logout'));

        $this->assertFalse(auth()->check());
    }

    /** @test */
    public function it_generates_an_eloquent_model_using_a_factory()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'attributes' => [
                'name' => 'John Doe',
            ],
        ]);

        $this->assertDatabaseHas('users', ['name' => 'John Doe']);
        $this->assertEquals('John Doe', $response->json()['name']);
    }

    /** @test */
    public function it_generates_an_eloquent_model_and_loads_the_requested_relations()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'attributes' => [
                'name' => 'John Doe',
            ],
            'relations' => ['profile']
        ]);


        $this->assertEquals('USA', $response->json()['profile']['location']);
    }

    /** @test */
    public function it_generates_a_collection_of_eloquent_model_using_a_factory()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'times' => 2,
            'attributes' => [
                'name' => 'John Doe',
            ],
            'relations' => ['profile']
        ]);

        $this->assertEquals(2, TestUser::whereName('John Doe')->count());
        $this->assertCount(2, $response->json());
    }

    /** @test */
    function it_makes_model_attributes_visible()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'attributes' => [
                'name' => 'John Doe',
            ],
        ]);

        // The TestUser model sets the "plan" field to hidden. But
        // when we fetch it with Cypress, it should be visible.
        $this->assertEquals('monthly', $response->json()['plan']);
    }

    /** @test */
    function it_makes_collection_model_attributes_visible()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'times' => 2,
            'attributes' => [
                'name' => 'John Doe',
            ],
        ]);

        // The TestUser model sets the "plan" field to hidden. But
        // when we fetch it with Cypress, it should be visible.
        $this->assertEquals('monthly', $response->json()[0]['plan']);
        $this->assertEquals('monthly', $response->json()[1]['plan']);
    }

    /** @test */
    public function it_runs_an_artisan_command()
    {
        $called = false;

        Artisan::command('testing', function () use (&$called) {
            $called = true;
        });

        $this->post(route('cypress.artisan'), [
            'command' => 'testing',
        ]);

        $this->assertTrue($called);
    }

    /** @test */
    public function it_runs_single_line_arbitrary_php()
    {
        $response = $this->post(route('cypress.run-php'), [
            'command' => '2 + 3',
        ]);

        $this->assertEquals(5, $response->json()['result']);
    }

    /** @test */
    public function it_runs_multi_line_arbitrary_php()
    {
        $response = $this->post(route('cypress.run-php'), [
            'command' => '$a = 2; $b = 3; return $a + $b;',
        ]);

        $this->assertEquals(5, $response->json()['result']);
    }
}
