<?php

namespace Laracasts\Cypress\Tests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Laracasts\Cypress\CypressServiceProvider;
use Laracasts\Cypress\Tests\Support\TestUser;
use Orchestra\Testbench\TestCase;
use Spatie\LaravelRay\RayServiceProvider;

class CypressControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [CypressServiceProvider::class, RayServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

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
    public function it_fetches_the_currently_authenticated_user()
    {
        $this->post(route('cypress.login'), ['attributes' => ['email' => 'joe@example.com']]);

        $response = $this->post(route('cypress.current-user'));

        $this->assertNotNull($response->json());
        $this->assertEquals('joe@example.com', $response->json()['email']);
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
    public function it_logs_a_user_in()
    {
        $this->post(route('cypress.login'), [
            'attributes' => ['name' => 'Frank'],
            'state' => ['guest'],
        ]);

        $this->assertDatabaseHas('users', ['name' => 'Frank']);
    }

    /** @test */
    public function it_logs_an_existing_user_in()
    {
        TestUser::factory()->create(['name' => 'Joe']);

        $frank = TestUser::factory()->create([
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
    public function it_builds_a_model_factory()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'attributes' => [
                'name' => 'John Doe',
            ],
            'load' => ['profile'],
            'state' => ['guest'],
        ]);

        $this->assertDatabaseHas('users', ['name' => 'John Doe']);
        $this->assertEquals('John Doe', $response->json()['name']);
        $this->assertEquals('USA', $response->json()['profile']['location']);
        $this->assertEquals('guest', $response->json()['plan']);
    }

    /** @test */
    public function it_builds_a_model_factory_by_its_morph_name()
    {
        Relation::morphMap([
            'test_user' => TestUser::class,
        ]);

        $response = $this->post(route('cypress.factory'), [
            'model' => 'test_user',
            'attributes' => [
                'name' => 'John Doe',
            ],
            'load' => ['profile'],
            'state' => ['guest'],
        ]);

        $this->assertDatabaseHas('users', ['name' => 'John Doe']);
        $this->assertEquals('John Doe', $response->json()['name']);
        $this->assertEquals('USA', $response->json()['profile']['location']);
        $this->assertEquals('guest', $response->json()['plan']);
    }

    /** @test */
    public function it_accepts_arguments_to_model_factory_states()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'state' => ['guest' => 'forum'],
        ]);

        $this->assertEquals('forum', $response->json()['plan']);

        // When passing an array of arguments.
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'state' => ['guest' => ['forum']],
        ]);

        $this->assertEquals('forum', $response->json()['plan']);
    }

    /** @test */
    public function it_builds_a_collection_of_model_factories()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'count' => 2,
        ]);

        $this->assertCount(2, $response->json());
    }

    /** @test */
    public function it_makes_model_attributes_visible()
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
    public function it_makes_collection_model_attributes_visible()
    {
        $response = $this->post(route('cypress.factory'), [
            'model' => TestUser::class,
            'count' => 2,
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
