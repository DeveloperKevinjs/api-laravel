<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    // Conexión exclusiva en memoria: no usa la base de desarrollo ni migraciones.
    config([
        'database.default' => 'requisition_store_testing',
        'database.connections.requisition_store_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        'session.driver' => 'array',
        'cache.default' => 'array',
    ]);

    $schema = Schema::connection('requisition_store_testing');

    // Solo el esquema necesario para este ejemplo, basado en las migraciones.
    $schema->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    $schema->create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name', 100)->unique();
        $table->boolean('status')->default(true);
        $table->timestamps();
    });

    $schema->create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->restrictOnDelete();
        $table->string('code', 50)->unique();
        $table->string('name', 150);
        $table->text('description')->nullable();
        $table->decimal('price', 12, 2)->default(0);
        $table->boolean('status')->default(true);
        $table->timestamps();
    });

    $schema->create('requisitions', function (Blueprint $table) {
        $table->id();
        $table->string('number', 50)->unique();
        $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
        $table->string('status')->default('DRAFT');
        $table->text('notes')->nullable();
        $table->timestamps();
    });

    $schema->create('requisition_lines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('requisition_id')->constrained('requisitions')->cascadeOnDelete();
        $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
        $table->decimal('quantity', 12, 3);
        $table->timestamps();
    });
});

afterEach(function () {
    DB::purge('requisition_store_testing');
});

test('un usuario autenticado crea una requisición con dos líneas', function () {
    // Mostrar la excepción original si todavía falta completar el TODO del modelo.
    $this->withoutExceptionHandling();

    $user = User::create([
        'name' => 'Usuario de prueba',
        'email' => 'requisiciones@example.test',
        'password' => 'password-de-prueba',
    ]);

    $categoryId = DB::table('categories')->insertGetId(['name' => 'Oficina']);

    $firstProduct = Product::create([
        'category_id' => $categoryId,
        'code' => 'PRUEBA-001',
        'name' => 'Papel',
    ]);

    $secondProduct = Product::create([
        'category_id' => $categoryId,
        'code' => 'PRUEBA-002',
        'name' => 'Lápices',
    ]);

    // Autenticación de sesión; no se generan tokens Bearer.
    $response = $this->actingAs($user, 'web')->postJson('/api/Requisitions', [
        'notes' => 'Material para oficina',
        'requisitionLines' => [
            ['product_id' => $firstProduct->id, 'quantity' => 2.5],
            ['product_id' => $secondProduct->id, 'quantity' => 3],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('user_id', $user->id)
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonCount(2, 'requisition_lines')
        ->assertJsonPath('requisition_lines.0.product.id', $firstProduct->id)
        ->assertJsonPath('requisition_lines.1.product.id', $secondProduct->id);

    expect($response->json('number'))->toStartWith('REQ-');

    $this->assertDatabaseCount('requisitions', 1);
    $this->assertDatabaseCount('requisition_lines', 2);

    $this->assertDatabaseHas('requisitions', [
        'id' => $response->json('id'),
        'number' => $response->json('number'),
        'user_id' => $user->id,
        'notes' => 'Material para oficina',
        'status' => 'DRAFT',
    ]);

    $this->assertDatabaseHas('requisition_lines', [
        'requisition_id' => $response->json('id'),
        'product_id' => $firstProduct->id,
        'quantity' => 2.5,
    ]);

    $this->assertDatabaseHas('requisition_lines', [
        'requisition_id' => $response->json('id'),
        'product_id' => $secondProduct->id,
        'quantity' => 3,
    ]);
});

// TODO (aprendizaje): Añadir pruebas de cantidades inválidas, incluida la precisión
// y el límite de decimal(12, 3), comprobando respuesta 422 y ausencia de registros.
// TODO (aprendizaje): Añadir una prueba de productos repetidos que compruebe
// respuesta 422 y ausencia de cabecera y líneas.
// TODO (aprendizaje): Simular un fallo al guardar una línea y comprobar que la
// transacción revierte la cabecera y todas las líneas, incluso las ya insertadas.
