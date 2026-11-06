# Guía del proyecto

## Aprendizaje y alcance

- Este ERP de compras es un proyecto de aprendizaje. Para cada funcionalidad solicitada, implementa la mayor parte siguiendo los patrones existentes y reserva **exactamente 3 ejercicios de implementación real** para el usuario: lógica Laravel, Eloquent, controladores, validaciones, relaciones o consultas; nunca tests. No completes esos ejercicios automáticamente.
- Documenta cada ejercicio con objetivo, comportamiento esperado, archivo y ubicación del `TODO`, y pistas conceptuales sin solución.
- Prepara **exactamente 3 bugs intencionales por funcionalidad**: fácil, intermedio y difícil. Tras aprobación explícita, desarrolla la funcionalidad, sus 3 ejercicios y sus 3 bugs en una rama normal `feature/*` dentro de la misma carpeta del proyecto; no desarrolles una segunda versión en `main`. Si la funcionalidad ya está implementada y el usuario solicita introducir los bugs posteriormente, propón una rama `practice/*` basada en el estado existente; no crees una rama por bug. Los bugs deben ser reproducibles y seguros, sin afectar autenticación, permisos, integridad de datos reales ni operaciones destructivas; verifica con datos aislados. Si no puedes aislarlos, detente y consulta.
- Documenta cada bug con síntomas observables, pasos para reproducirlo, resultado esperado y dificultad. No reveles la causa ni su ubicación exacta antes del intento del usuario.
- No completes ni reveles las soluciones de los ejercicios o bugs sin autorización explícita del usuario; guía con pistas progresivas sin anticipar causas ni correcciones exactas.
- Trabaja con ramas Git normales en la misma carpeta del proyecto: no crees ni utilices Worktrees. Antes de implementar, comprueba el estado de `main` y acuerda una base estable sin perder cambios pendientes. Presenta un plan breve con archivos, trabajo propio, los 3 ejercicios y el aislamiento de los 3 bugs; espera aprobación explícita antes de crear o cambiar a una rama descriptiva `feature/*` (o reutiliza una rama apropiada). Desarrolla únicamente en esa rama; no modifiques `main` salvo mantenimiento expresamente aprobado.
- Antes de cualquier cambio de rama, revisa `git status`, incluidos cambios staged, unstaged y untracked, y comprueba los commits pendientes de integrar; si existe riesgo de perder o mezclar trabajo, detente y consulta. No hagas commits, cambios de rama, cherry-picks, reverts, merges ni push sin autorización explícita. Nunca uses `reset --hard`, `git clean` ni `push --force`; no uses stash sin aprobación ni reescribas commits compartidos. Ante riesgo de perder o sobrescribir trabajo, detente y explícalo.
- Antes de integrar, comprueba que no queden TODOs ni bugs intencionales, revisa cambios y posibles conflictos frente a `main`, verifica manualmente y con pruebas pertinentes, y enseña a organizar commits descriptivos. El usuario realiza personalmente el merge; ante conflictos reales, guíalo sin resolverlos automáticamente.
- Respeta las convenciones Laravel y los patrones existentes; evita sobreingeniería, nuevas tecnologías y refactorizaciones no solicitadas. Modifica únicamente archivos necesarios para la tarea.
- Investiga solo archivos relevantes y reutiliza lo ya leído, salvo que haya cambios o dudas concretas. No inspecciones dependencias o generados (`vendor/`, `node_modules/`, `public/build/`, `storage/framework/`) para entender lógica propia.
- Distingue implementación, flujo parcial y funcionamiento no verificado. No deduzcas funcionalidad por existir un archivo, método o ruta; señala evidencias y límites de la verificación.
- Crea o ejecuta verificaciones enfocadas cuando aporten valor, explicando qué comprueban; no reserves escribir tests como ejercicio.

## Arquitectura y convenciones verificadas

- Laravel 13, PHP requerido `^8.3`, Sanctum 4 y Pest 4; presentación con Blade, Vite 8 y Tailwind CSS 4. `README.md` es genérico: prioriza manifiestos, lockfiles y configuración ejecutable.
- Flujo principal: rutas → controladores → FormRequests → modelos Eloquent; la lógica reside principalmente en controladores. Productos añade scopes, `ProductResource` y `ProductPolicy`. Conserva los atributos existentes `#[Scope]`, `#[Fillable]` y `#[Hidden]`.
- `bootstrap/app.php` conecta rutas web/API, activa Sanctum stateful y respuestas de excepción JSON para `api/*`. `AuthController` autentica por sesión/cookies, no emite tokens Bearer: respeta sesión y CSRF. Login: `/login` y `/api/login`; logout: `/logout`.
- Los recursos API requieren `auth:sanctum`. Requisiciones usa **`/api/Requisitions`**, con mayúscula. La ruta web pública `/supplier/{supplier:document_number}` devuelve JSON, no la vista de proveedor (`routes/api.php`, `routes/web.php`).
- Relaciones: Category → Products; User → Requisitions → RequisitionLines → Product. Supplier no declara relaciones. Las FK restringen borrar categorías utilizadas y productos referenciados; proveedores usa `SoftDeletes`, productos no. Consulta modelos y migraciones antes de alterar estos flujos.

## Módulos: estado actual, no garantía de ejecución

- **Proveedores:** CRUD API conectado con validación y borrado lógico (`SupplierController`, sus Requests y modelo); no implica autorización por roles ni ausencia de incidencias.
- **Productos:** CRUD, filtros y paginación conectados, con partes incompletas. Sus Requests de escritura autorizan mediante Policy; la Policy permite el CRUD a usuarios autenticados. `status` existe, pero los Requests de escritura no lo incluyen.
- **Categorías:** `main` incluye `POST /api/categories` con Request, Policy y Resource; `feature/category-reads` desarrolla `index` y `show` con ejercicios y bugs pendientes. Revalida la rama activa antes de dar por funcional la lectura. El alta de productos exige una categoría existente.
- **Requisiciones:** `apiResource` declara operaciones que no están implementadas; el controlador solo tiene `store`, con transacción para cabecera y líneas. Ambos modelos configuran asignación masiva para los campos usados; hay pruebas Feature de creación, validación y rollback en SQLite en memoria, no garantía de producción.
- La interfaz del ERP es parcial; los comentarios de práctica y esqueletos no son funcionalidades terminadas. Revalida este estado si la tarea cambia esos módulos.

## Comandos, pruebas y datos

- Desde la raíz: `composer test` limpia configuración cacheada y ejecuta pruebas. Para enfocar: `php artisan test tests/Feature/ExampleTest.php` o `php artisan test --filter="nombre"`; limpia antes configuración cacheada con `php artisan config:clear` si existe.
- Formato sin editar: `php vendor/bin/pint --test ruta/al/archivo.php`. Frontend: `npm run dev` / `npm run build`; no hay scripts frontend de pruebas, lint o typecheck. `.npmrc` desactiva scripts de instalación.
- `composer dev` inicia servidor, consumidor de colas y Vite; el consumidor puede procesar trabajos reales. **No uses `composer setup` como verificación**: instala dependencias, genera `APP_KEY` y ejecuta `migrate --force`. No ejecutes migraciones ni seeders sin autorización.
- `phpunit.xml` configura SQLite `:memory:`, sesión/cache en arrays y cola síncrona. `tests/Pest.php` tiene `RefreshDatabase` comentado: las pruebas con BD deben preparar el esquema. Solo hay pruebas de ejemplo; un resultado verde no valida el ERP.
- `DatabaseSeeder` solo prepara proveedores. No asumas usuarios, credenciales, categorías o productos existentes. No leas secretos de `.env` ni deduzcas la conexión efectiva por existir `database/database.sqlite`.
