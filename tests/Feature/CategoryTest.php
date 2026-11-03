<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Conexión exclusiva en memoria: no ejecuta migraciones ni usa la base de desarrollo.
    config([
        'database.default' => 'category_testing',
        'database.connections.category_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        'session.driver' => 'array',
        'cache.default' => 'array',
    ]);

    $schema = Schema::connection('category_testing');

    // Esquema necesario para crear usuarios y categorías, basado en sus migraciones.
    $schema->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->string('role', 30)->default('user');
        $table->timestamps();
    });

    $schema->create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name', 100)->unique();
        $table->boolean('status')->default(true);
        $table->timestamps();
    });
});

afterEach(function () {
    DB::purge('category_testing');
});

test('un usuario autenticado crea una categoría con estado activo por defecto', function () {
    // Preparar: la autenticación de sesión permite acceder a la ruta protegida.
    $user = User::create([
        'name' => 'Usuario de categorías',
        'email' => 'categorias@example.test',
        'password' => 'password-de-prueba',
    ]);

    // Actuar: omitir status para comprobar el valor predeterminado de la base de datos.
    $response = $this->actingAs($user, 'web')->postJson('/api/categories', [
        'name' => 'Oficina',
    ]);

    // Comprobar: el Resource envuelve los atributos en data y devuelve un booleano.
    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'status']])
        ->assertJsonPath('data.name', 'Oficina')
        ->assertJsonPath('data.status', true);

    $this->assertDatabaseCount('categories', 1);
    $this->assertDatabaseHas('categories', [
        'id' => $response->json('data.id'),
        'name' => 'Oficina',
        'status' => true,
    ]);
});

test('un visitante no puede crear categorías', function () {
    $this->postJson('/api/categories', ['name' => 'Oficina'])
        ->assertUnauthorized();

    $this->assertDatabaseCount('categories', 0);
});

test('rechaza nombres de categoría duplicados sin guardar otro registro', function () {
    $user = User::create([
        'name' => 'Usuario de categorías',
        'email' => 'categorias@example.test',
        'password' => 'password-de-prueba',
    ]);

    $category = Category::create(['name' => 'Oficina']);

    $this->actingAs($user, 'web')->postJson('/api/categories', [
        'name' => 'Oficina',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    $this->assertDatabaseCount('categories', 1);
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Oficina',
        'status' => true,
    ]);
});

test('rechaza una categoría sin nombre', function () {
    $user = User::create([
        'name' => 'Usuario de categorías',
        'email' => 'categorias@example.test',
        'password' => 'password-de-prueba',
    ]);

    // Omitir name permite comprobar required sin introducir otros datos inválidos.
    $this->actingAs($user, 'web')->postJson('/api/categories', [
        'status' => true,
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['name'])
        ->assertJsonMissingValidationErrors(['status']);

    $this->assertDatabaseCount('categories', 0);
});

test('rechaza un nombre de categoría de más de 100 caracteres', function () {
    $user = User::create([
        'name' => 'Usuario de categorías',
        'email' => 'categorias@example.test',
        'password' => 'password-de-prueba',
    ]);

    // Un carácter por encima del límite aísla la regla max:100.
    $this->actingAs($user, 'web')->postJson('/api/categories', [
        'name' => str_repeat('a', 101),
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['name']);

    $this->assertDatabaseCount('categories', 0);
});

test('rechaza un estado de categoría inválido', function () {
    $user = User::create([
        'name' => 'Usuario de categorías',
        'email' => 'categorias@example.test',
        'password' => 'password-de-prueba',
    ]);

    // El nombre es válido; el texto no es un valor admitido por la regla boolean.
    $this->actingAs($user, 'web')->postJson('/api/categories', [
        'name' => 'Oficina',
        'status' => 'activo',
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['status'])
        ->assertJsonMissingValidationErrors(['name']);

    $this->assertDatabaseCount('categories', 0);
});
