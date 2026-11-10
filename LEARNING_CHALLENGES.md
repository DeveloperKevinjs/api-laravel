# Category Writes — desafíos de aprendizaje

Estado: funcionalidad parcial. E1, E2, B1 y B2 están verificados; E3 y B3 siguen pendientes. No se debe integrar la funcionalidad hasta resolver los dos desafíos restantes y volver a verificarla.

Para reproducir los casos, usa únicamente las pruebas de `tests/Feature/CategoryTest.php` (SQLite en memoria, usuario autenticado mediante sesión) o una base descartable. No pruebes eliminaciones con datos reales. La API emplea sesión y CSRF, no tokens Bearer emitidos por el login.

## Ejercicios de implementación

### E1 — fácil · Completado

- **Objetivo:** validar la unicidad del nombre al actualizar una categoría.
- **Archivo y ubicación:** `app/Http/Requests/UpdateCategoryRequest.php`, regla `name` marcada para E1.
- **Comportamiento esperado:** se acepta conservar el propio nombre o usar uno nuevo; se responde `422` si ya pertenece a otra categoría. `name` puede omitirse en una actualización parcial.
- **Criterio de terminado:** las pruebas de nombre propio y nombre duplicado cumplen lo esperado, sin bloquear actualizaciones válidas.
- **Pista:** la validación de creación ya exige unicidad; en una actualización también hay que considerar la categoría recibida por el binding.

### E2 — intermedio · Completado

- **Objetivo:** persistir una actualización de categoría mediante Eloquent.
- **Archivo y ubicación:** `app/Http/Controllers/CategoryController.php`, método `update`, marcado para E2.
- **Comportamiento esperado:** solo se guardan los atributos validados; la respuesta contiene los valores actualizados. Se permiten actualizaciones parciales mediante `PUT`.
- **Criterio de terminado:** una actualización válida cambia la fila esperada y el JSON refleja los cambios, sin modificar atributos omitidos.
- **Pista:** observa el patrón de actualización de los otros recursos y la respuesta actual de `CategoryResource`.

### E3 — avanzado · Pendiente

- **Objetivo:** habilitar la eliminación segura de categorías.
- **Archivo y ubicación:** `app/Http/Controllers/CategoryController.php`, método `destroy`, marcado para E3.
- **Comportamiento esperado:** una categoría sin productos se elimina con `204`; si está asociada a productos se conserva y se responde `409`. El flujo todavía no cumple ambos casos.
- **Criterio de terminado:** ambas pruebas de eliminación pasan, sin borrar productos ni categorías utilizadas.
- **Pista:** el modelo ya tiene una relación con productos y la base de datos restringe su eliminación cuando hay referencias.

## Bugs intencionales

### B1 — fácil · Completado

- **Síntoma:** `PATCH /api/categories/{id}` no permite una actualización válida, mientras que la petición equivalente con `PUT` sí alcanza el flujo de actualización.
- **Reproducción:** crea una categoría de prueba; inicia sesión con un usuario de prueba; envía `PATCH` con un nombre nuevo y compara la respuesta con `PUT` para esa categoría.
- **Resultado esperado:** ambos verbos admiten la actualización válida con respuesta `200`.
- **Condiciones:** categoría y usuario aislados; no son necesarios productos.

### B2 — medio · Completado

- **Síntoma:** una actualización parcial por `PUT` que envía solo `status` devuelve `422`.
- **Reproducción:** crea una categoría de prueba, autentícate y envía `PUT /api/categories/{id}` con `{"status": false}` sin incluir `name`.
- **Resultado esperado:** respuesta `200`; el estado cambia y el nombre se conserva.
- **Condiciones:** categoría aislada, sin productos; la API acepta `PUT` parcial.

### B3 — difícil · Pendiente

- **Síntoma:** una categoría con un producto asociado rechaza una actualización válida de nombre y estado con `409`.
- **Reproducción:** crea una categoría y un producto asociado en la base aislada; autentícate y envía `PUT /api/categories/{id}` con un nombre nuevo y `status: false`.
- **Resultado esperado:** respuesta `200`; la categoría se actualiza y el producto conserva su asociación.
- **Condiciones:** nombre nuevo no utilizado, usuario autenticado y producto de prueba asociado. No es una operación de eliminación ni depende de E3.

Los bugs se prepararon en código de OpenCode, fuera de los ejercicios. No se documentan aquí sus causas, ubicaciones precisas ni soluciones.
