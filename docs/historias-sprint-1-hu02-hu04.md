# Historias de usuario del Sprint 1: HU02–HU04

Este documento completa las tres historias pendientes del Sprint 1. La descripción se contrastó con las rutas, controladores, vistas y pruebas que existen en el repositorio. Se documentan las funciones presentes; no se atribuyen al sistema operaciones que no están implementadas.

## Resumen

| Historia | Necesidad | Prioridad | Sprint |
|---|---|---:|---:|
| HU02 | Como Administrador, necesito gestionar cuentas y roles para distribuir responsabilidades. | Alta | 1 |
| HU03 | Como Administrador, necesito mantener las carreras para organizar el repositorio. | Alta | 1 |
| HU04 | Como persona autenticada, necesito actualizar mi perfil y contraseña. | Media | 1 |

## HU02 — Gestión de cuentas y roles

**Actor:** Administrador.  
**Propósito:** crear, consultar, editar y activar o desactivar las cuentas que pueden acceder al sistema.

**Precondición:** el Administrador inició sesión y tiene permiso para administrar usuarios.

**Flujo principal:**

1. El Administrador abre la sección **Usuarios** y consulta las cuentas agrupadas por rol.
2. Puede cambiar el filtro entre Administrador, Gestor y Usuario; la lista se muestra paginada.
3. Para crear una cuenta, ingresa nombre, correo, contraseña y confirmación, rol y, opcionalmente, carrera.
4. El sistema valida los datos, guarda la cuenta y muestra una confirmación.
5. Para modificarla, el Administrador actualiza nombre, correo, rol o carrera.
6. También puede activar o desactivar una cuenta existente.

**Alternativas y reglas:**

- Si el correo ya está registrado, el rol no es válido, falta un campo obligatorio o la contraseña no cumple la regla configurada/no coincide con su confirmación, el sistema muestra errores y no guarda el formulario.
- En la creación, la carrera es opcional; si se asigna, debe existir y estar activa.
- No se permite que el Administrador desactive su propia cuenta.
- La interfaz no ofrece eliminación de cuentas. Desactivar conserva el registro.

**Postcondición:** la cuenta queda creada o actualizada, o su estado cambia; los datos académicos relacionados se conservan.

**Criterios de aceptación:**

1. Solo un Administrador puede acceder a las rutas de gestión de usuarios.
2. Una cuenta nueva válida se almacena con su contraseña protegida y el rol seleccionado.
3. El sistema impide correos duplicados y muestra los errores de validación.
4. Se puede editar una cuenta y cambiar su rol o estado sin borrar su registro.
5. El Administrador no puede desactivar la cuenta con la que inició sesión.

**Observación del código:** al crear se exige que la carrera asignada esté activa; al editar, la validación solo exige que exista. Por tanto, hoy puede conservarse o seleccionarse una carrera inactiva en la edición.

## HU03 — Gestión de carreras

**Actor:** Administrador.  
**Propósito:** mantener el catálogo de carreras utilizado para clasificar usuarios y proyectos.

**Precondición:** el Administrador inició sesión y tiene permiso para gestionar carreras.

**Flujo principal:**

1. El Administrador abre **Carreras** y consulta el catálogo ordenado alfabéticamente.
2. Puede registrar una carrera con nombre obligatorio y código opcional.
3. Puede editar el nombre o el código de una carrera existente.
4. Puede activar o desactivar la carrera desde la lista.

**Alternativas y reglas:**

- El nombre debe ser único y tener como máximo 255 caracteres.
- El código es opcional, único y admite hasta 20 caracteres.
- Si el nombre o código ya pertenece a otra carrera, el sistema informa el error y conserva los datos existentes.
- No se ofrece eliminación de carreras. Al desactivarlas se conservan sus datos históricos y relaciones.

**Postcondición:** el catálogo refleja el alta, modificación o cambio de estado solicitado, sin borrar registros históricos.

**Criterios de aceptación:**

1. Solo un Administrador puede crear, editar o cambiar el estado de una carrera.
2. Se permite registrar una carrera sin código.
3. Se rechazan nombres y códigos duplicados.
4. Una carrera desactivada permanece almacenada y puede volver a activarse.

## HU04 — Actualización del perfil y contraseña

**Actor:** persona autenticada (Administrador, Gestor o Usuario).  
**Propósito:** permitir que cada persona mantenga sus datos de acceso y cambie su contraseña desde **Mi perfil**.

**Precondición:** la persona inició sesión.

**Flujo principal:**

1. La persona abre **Mi perfil**.
2. Puede actualizar su nombre y correo de acceso; el sistema valida el formato y que el correo no esté usado por otra cuenta.
3. Para cambiar la contraseña, introduce la contraseña actual, la nueva y su confirmación.
4. El sistema valida la contraseña actual y la coincidencia de la nueva contraseña, guarda el cambio y confirma el resultado.

**Alternativas y reglas:**

- Si el correo está duplicado o tiene un formato no válido, el sistema no actualiza los datos.
- Si la contraseña actual es incorrecta o la nueva no coincide con su confirmación, se informa el error y no se cambia la contraseña.
- Cambiar el correo no comprueba que la persona tenga acceso a esa bandeja ni envía una verificación funcional. El proyecto no tiene habilitados el envío real de correos ni la recuperación por correo.
- Esta historia no permite cambiar el rol de la cuenta ni eliminarla.

**Postcondición:** se guardan los datos válidos del perfil o se actualiza la contraseña tras verificar la contraseña actual.

**Criterios de aceptación:**

1. Cualquier persona autenticada puede abrir su perfil, pero no el perfil de otra persona mediante esta función.
2. Se pueden guardar nombre y correo válidos, siempre que el correo no esté duplicado.
3. La contraseña solo cambia si la contraseña actual es correcta y la confirmación coincide.
4. El sistema muestra un mensaje de confirmación o errores junto al formulario correspondiente.

## Evidencia en el repositorio

- **HU02 y HU03:** `app/Http/Controllers/UsuarioController.php`, `app/Http/Controllers/CarreraController.php`, vistas `resources/views/usuarios/` y `resources/views/carreras/`; acceso restringido en `routes/web.php`.
- **HU04:** `app/Http/Controllers/ProfileController.php`, `app/Http/Controllers/Auth/PasswordController.php`, `app/Http/Requests/ProfileUpdateRequest.php` y vistas `resources/views/profile/`.
- Hay cobertura automatizada relacionada en `tests/Feature/PresentationReadinessTest.php` y `tests/Feature/ProfileTest.php`. La existencia de esos casos no significa que se hayan ejecutado en esta revisión.

