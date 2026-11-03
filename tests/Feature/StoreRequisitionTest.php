<?php

use App\Models\Product;
use App\Models\RequisitionLine;
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
    // Mostrar la excepción original si falla la creación, en lugar de una respuesta HTTP 500.
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

test('rechaza cantidades inválidas sin guardar la requisición', function ($quantity) {
    // Preparar: los demás datos son válidos para aislar el error de quantity.
    $user = User::create([
        'name' => 'Usuario de validación',
        'email' => 'validacion@example.test',
        'password' => 'password-de-prueba',
    ]);

    $categoryId = DB::table('categories')->insertGetId(['name' => 'Oficina']);

    $product = Product::create([
        'category_id' => $categoryId,
        'code' => 'VALIDACION-001',
        'name' => 'Papel',
    ]);

    // Actuar: cada caso del dataset envía una cantidad diferente.
    $response = $this->actingAs($user, 'web')->postJson('/api/Requisitions', [
        'requisitionLines' => [
            ['product_id' => $product->id, 'quantity' => $quantity],
        ],
    ]);

    // Comprobar: la validación debe rechazar la petición antes de guardar datos.
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['requisitionLines.0.quantity']);

    $this->assertDatabaseCount('requisitions', 0);
    $this->assertDatabaseCount('requisition_lines', 0);
})->with([
    'cantidad nula' => [null],
    'texto no numérico' => ['abc'],
    'cero' => [0],
    'cantidad negativa' => [-1],
    'más de tres decimales' => ['1.2345'],
    // decimal(12, 3) admite nueve dígitos enteros; este valor ya tiene diez.
    'supera el límite de decimal(12, 3)' => ['1000000000'],
]);

test('rechaza líneas con productos repetidos', function () {
    // Preparar: las cantidades son válidas para aislar el error de productos repetidos.
    $user = User::create([
        'name' => 'Usuario de validación',
        'email' => 'validacion2@example.test',
        'password' => 'password-de-prueba',
    ]);

    $categoryId = DB::table('categories')->insertGetId(['name' => 'Oficina']);

    $product = Product::create([
        'category_id' => $categoryId,
        'code' => 'VALIDACION-002',
        'name' => 'Unidades de papa',
    ]);

    // Actuar: enviar el mismo producto en dos líneas de la petición.
    $response = $this->actingAs($user, 'web')->postJson('/api/Requisitions', [
        'requisitionLines' => [
            ['product_id' => $product->id, 'quantity' => 10],
            ['product_id' => $product->id, 'quantity' => 20],
        ],
    ]);

    // Comprobar: la validación debe rechazar la petición antes de guardar datos.
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['requisitionLines.0.product_id', 'requisitionLines.1.product_id']);

    $this->assertDatabaseCount('requisitions', 0);
    $this->assertDatabaseCount('requisition_lines', 0);
});

test('rechaza fallas en la inserción de líneas', function () {
    // Preparar: la petición debe superar la validación y entrar en la transacción.
    $this->withoutExceptionHandling();

    $user = User::create([
        'name' => 'Usuario de validación',
        'email' => 'validacion2@example.test',
        'password' => 'password-de-prueba',
    ]);

    $categoryId = DB::table('categories')->insertGetId(['name' => 'Oficina']);

    $product = Product::create([
        'category_id' => $categoryId,
        'code' => 'VALIDACION-002',
        'name' => 'Unidades de papa',
    ]);

    $secondProduct = Product::create([
        'category_id' => $categoryId,
        'code' => 'VALIDACION-003',
        'name' => 'Unidades de arroz',
    ]);

    $failure = new RuntimeException('Fallo simulado al insertar la segunda línea');
    $caughtException = null;
    $originalDispatcher = RequisitionLine::getEventDispatcher();

    // Aislar el listener sin eliminar los listeners originales de la aplicación.
    RequisitionLine::setEventDispatcher(clone $originalDispatcher);

    try {
        RequisitionLine::creating(function (RequisitionLine $line) use ($product, $secondProduct, $failure) {
            if ($line->product_id !== $secondProduct->id) {
                return;
            }

            // Antes del fallo ya existen la cabecera y la primera línea.
            $this->assertDatabaseCount('requisitions', 1);
            $this->assertDatabaseCount('requisition_lines', 1);
            $this->assertDatabaseHas('requisitions', ['id' => $line->requisition_id]);
            $this->assertDatabaseHas('requisition_lines', [
                'requisition_id' => $line->requisition_id,
                'product_id' => $product->id,
                'quantity' => 10,
            ]);

            throw $failure;
        });

        // Actuar: el fallo sucede durante createMany(), no en el FormRequest.
        $this->actingAs($user, 'web')->postJson('/api/Requisitions', [
            'requisitionLines' => [
                ['product_id' => $product->id, 'quantity' => 10],
                ['product_id' => $secondProduct->id, 'quantity' => 20],
            ],
        ]);
    } catch (RuntimeException $exception) {
        $caughtException = $exception;
    } finally {
        // Restaurar el dispatcher también si falla una aserción o la petición.
        RequisitionLine::setEventDispatcher($originalDispatcher);
    }

    // Comprobar el fallo exacto y el rollback de todo lo que ya estaba insertado.
    expect($caughtException)->toBe($failure);
    $this->assertDatabaseCount('requisitions', 0);
    $this->assertDatabaseCount('requisition_lines', 0);
});
