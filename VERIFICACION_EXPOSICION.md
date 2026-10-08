# Comprobación de Sello ITSa — 8 de octubre de 2026

Las funciones principales verificadas funcionan en esta instalación. Este informe documenta el alcance de la revisión para la exposición del 9 de octubre; no constituye una garantía de ausencia de errores en todos los escenarios posibles.

## Resultados

- Suite completa: **78 pruebas aprobadas, 493 aserciones**. Las pruebas utilizan SQLite en memoria y almacenamiento simulado; no la base principal.
- Chrome: **25 verificaciones aprobadas**, sin excepciones de JavaScript. Se comprobó acceso, salida, navegación, menús Bootstrap, selectores académicos, selección de proyectos, comparación, carga de PDF y pantallas móviles.
- Pruebas del navegador ejecutadas con usuarios ficticios y SQLite temporal, documentos y sesiones separados de los reales.
- Con datos actuales, dashboard, proyectos, estudiantes, docentes, carreras, usuarios, respaldos, análisis, Crossref, reportes y perfil devolvieron HTTP 200 en una consulta interna autenticada sin persistir sesiones.
- Se comprobó la autorización de administrador, gestor y usuario, además del acceso de invitados.
- Un PDF generado realmente se subió, se extrajo su texto, se visualizó, se analizó y permitió generar reportes. La comparación de documentos iguales dio 100%.
- El respaldo incluyó las nueve tablas del negocio y se restauró correctamente en otra base SQLite en memoria, con relaciones verificadas. No se ejecutó una restauración sobre MySQL real.
- Crossref y OpenAlex respondieron a consultas reales con diez resultados cada uno. Requieren Internet y disponibilidad de los proveedores.
- `npm run build`, `php artisan optimize:clear`, `php artisan view:cache` y `php artisan route:list --except-vendor` terminaron correctamente. Se registraron 67 rutas.
- Verificación de sintaxis PHP y `git diff --check` sin errores. Composer y migraciones comprobados durante la revisión.

## Correcciones de esta comprobación

1. **Reportes:** el nombre del PDF incluye el identificador del proyecto; proyectos con el mismo título ya no comparten el archivo de reporte.
2. **Respaldos:** se añadieron estudiantes, docentes y la relación carrera/docente al SQL; se ordenaron las tablas para restaurar relaciones y se corrigió el escape de valores. Se aclaró en pantalla que los PDF necesitan una copia aparte.
3. **Login:** se ajustó la prioridad del CSS para conservar el espacio entre el icono y el correo.
4. Se añadieron pruebas de pantallas, permisos, respaldo/restauración, carga y extracción de PDF, reportes con títulos iguales y administración de carreras y usuarios.

Se conservaron las correcciones previas del extractor PDF y del análisis de similitud. No se cambiaron nombres de rutas, roles ni formularios durante esta comprobación.

## Integridad de datos

Las huellas de las nueve tablas se compararon antes y después y permanecieron iguales: 5 usuarios, 9 carreras, 50 estudiantes, 46 docentes, 46 relaciones carrera/docente, 5 proyectos, 5 documentos, 9 comparaciones y 0 reportes reales.

Las huellas de `.env` y de los seis PDF existentes permanecieron iguales. Los cinco documentos registrados tienen archivo disponible; el PDF adicional existente tampoco se eliminó. No se alteró la base principal ni se hicieron commits o pushes. Los archivos de `public/build` se generaron con Vite.

## Límites y precauciones para la demostración

- El PHP predeterminado de consola es 8.2.12. El proyecto requiere PHP 8.3; los comandos de esta revisión usaron `C:/laragon/bin/php/php-8.3.33-Win32-vs16-x64/php.exe`. Asegurar que Laragon utilice una versión compatible.
- La base de esta instalación es MySQL. Mantener MySQL y el servidor web de Laragon encendidos.
- `MAIL_MAILER=log`: la recuperación de contraseña escribe el correo en el registro y **no envía un mensaje real**. No se modificó `.env`.
- El respaldo SQL exporta datos, no documentos. Para una copia completa hay que conservar también `storage/app/public`. La restauración presupone el mismo esquema migrado.
- El análisis compara coincidencias textuales, no demuestra plagio. Depende del texto extraíble y de los encabezados reconocidos; un PDF escaneado sin texto requiere OCR, que no forma parte de esta revisión. Mostrar también los fragmentos y las exclusiones, no solamente el porcentaje.
- Al editar carrera o modalidad de un proyecto, volver a ejecutar el análisis antes de presentar sus resultados: la edición de metadatos sin reemplazar el PDF no invalida automáticamente todas las comparaciones existentes.
- La desactivación de una cuenta impide nuevos inicios de sesión; la revocación inmediata de una sesión que ya estaba abierta queda pendiente de endurecimiento. Evitar presentar ese flujo como revocación instantánea.
- No se probaron restauraciones destructivas, cargas simultáneas, recuperación ante cortes de energía ni envío de correo real.

## Secuencia recomendada para exponer

1. Iniciar Laragon con MySQL y PHP compatible, abrir el sistema y comprobar el acceso.
2. Mostrar dashboard y catálogos académicos; abrir un PDF existente.
3. Comparar dos proyectos de la misma carrera y modalidad, explicar las exclusiones y mostrar coincidencias.
4. Generar un reporte y demostrar el respaldo SQL, explicando que los PDF se copian por separado.
5. Mostrar Crossref/OpenAlex si hay Internet. Mantener documentos locales preparados para continuar la demostración sin depender del proveedor externo.

No es necesario volver a instalar dependencias ni modificar la clave para exponer: los recursos de producción ya están compilados.
