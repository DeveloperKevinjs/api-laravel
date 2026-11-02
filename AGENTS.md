# Guía del proyecto

## Aprendizaje y alcance

- Este ERP de compras es un proyecto de aprendizaje. Al desarrollar funcionalidades, prioriza una implementación parcial con ejemplos acotados y comentarios `TODO` concretos para que el usuario complete la lógica; no resuelvas todo automáticamente salvo petición explícita.
- Antes de implementar cualquier cambio, analiza el contexto necesario y presenta un plan breve con los archivos que modificarás, qué implementarás tú y qué partes quedarán como ejercicios `TODO` para el usuario. Espera su aprobación explícita antes de implementar. No completes automáticamente los `TODO` reservados para su aprendizaje.
- Respeta las convenciones Laravel y los patrones existentes; evita sobreingeniería, nuevas tecnologías y refactorizaciones no solicitadas. Modifica únicamente archivos necesarios para la tarea.
- Investiga solo archivos relevantes y reutiliza lo ya leído, salvo que haya cambios o dudas concretas. No inspecciones dependencias o generados (`vendor/`, `node_modules/`, `public/build/`, `storage/framework/`) para entender lógica propia.
- Distingue implementación, flujo parcial y funcionamiento no verificado. No deduzcas funcionalidad por existir un archivo, método o ruta; señala evidencias y límites de la verificación.
- Propón pruebas enfocadas cuando aporten valor al ejercicio o al comportamiento desarrollado, explicando qué comprueban.

## Arquitectura y convenciones verificadas

- Laravel 13, PHP requerido `^8.3`, Sanctum 4 y Pest 4; presentación con Blade, Vite 8 y Tailwind CSS 4. `README.md` es genérico: prioriza manifiestos, lockfiles y configuración ejecutable.
- Flujo principal: rutas → controladores → FormRequests → modelos Eloquent; la lógica reside principalmente en controladores. Productos añade scopes, `ProductResource` y `ProductPolicy`. Conserva los atributos existentes `#[Scope]`, `#[Fillable]` y `#[Hidden]`.
- `bootstrap/app.php` conecta rutas web/API, activa Sanctum stateful y respuestas de excepción JSON para `api/*`. `AuthController` autentica por sesión/cookies, no emite tokens Bearer: respeta sesión y CSRF. Login: `/login` y `/api/login`; logout: `/logout`.
- Los recursos API requieren `auth:sanctum`. Requisiciones usa **`/api/Requisitions`**, con mayúscula. La ruta web pública `/supplier/{supplier:document_number}` devuelve JSON, no la vista de proveedor (`routes/api.php`, `routes/web.php`).
- Relaciones: Category → Products; User → Requisitions → RequisitionLines → Product. Supplier no declara relaciones. Las FK restringen borrar categorías utilizadas y productos referenciados; proveedores usa `SoftDeletes`, productos no. Consulta modelos y migraciones antes de alterar estos flujos.

## Módulos: estado actual, no garantía de ejecución

- **Proveedores:** CRUD API conectado con validación y borrado lógico (`SupplierController`, sus Requests y modelo); no implica autorización por roles ni ausencia de incidencias.
- **Productos:** CRUD, filtros y paginación conectados, con partes incompletas. Sus Requests de escritura autorizan mediante Policy aunque los Gates del controlador estén comentados. La Policy permite todo el CRUD a usuarios autenticados; `role` no supone permisos diferenciados. `status` existe, pero los Requests de escritura no lo incluyen.
- **Categorías:** modelo y migración disponibles; controlador vacío, Request sin reglas y con autorización denegada, sin rutas de gestión. El alta de productos exige una categoría existente.
- **Requisiciones:** `apiResource` declara operaciones que no están implementadas; el controlador solo tiene `store`. Intenta crear cabecera y líneas en una transacción, con `requisitionLines` (`product_id`, `quantity`). Ambos modelos carecen de configuración de asignación masiva: no des por funcional la creación.
- La interfaz del ERP es parcial; los comentarios de práctica y esqueletos no son funcionalidades terminadas. Revalida este estado si la tarea cambia esos módulos.

## Comandos, pruebas y datos

- Desde la raíz: `composer test` limpia configuración cacheada y ejecuta pruebas. Para enfocar: `php artisan test tests/Feature/ExampleTest.php` o `php artisan test --filter="nombre"`; limpia antes configuración cacheada con `php artisan config:clear` si existe.
- Formato sin editar: `php vendor/bin/pint --test ruta/al/archivo.php`. Frontend: `npm run dev` / `npm run build`; no hay scripts frontend de pruebas, lint o typecheck. `.npmrc` desactiva scripts de instalación.
- `composer dev` inicia servidor, consumidor de colas y Vite; el consumidor puede procesar trabajos reales. **No uses `composer setup` como verificación**: instala dependencias, genera `APP_KEY` y ejecuta `migrate --force`. No ejecutes migraciones ni seeders sin autorización.
- `phpunit.xml` configura SQLite `:memory:`, sesión/cache en arrays y cola síncrona. `tests/Pest.php` tiene `RefreshDatabase` comentado: las pruebas con BD deben preparar el esquema. Solo hay pruebas de ejemplo; un resultado verde no valida el ERP.
- `DatabaseSeeder` solo prepara proveedores. No asumas usuarios, credenciales, categorías o productos existentes. No leas secretos de `.env` ni deduzcas la conexión efectiva por existir `database/database.sqlite`.
