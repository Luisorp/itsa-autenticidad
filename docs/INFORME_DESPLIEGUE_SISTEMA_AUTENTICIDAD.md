# Informe de despliegue y verificación de acceso

## Sistema de autenticidad de proyectos de titulación

**Instituto Tecnológico Sacaba**  
**Carrera:** Sistemas Informáticos  
**Estudiante:** Cazorla Paredez Luis Enrique  
**Gestión:** 2026 — Cuarto bimestre  
**Fecha de elaboración:** 30 de octubre de 2026

> **Resultado:** el servicio se desplegó en Render y está **Live** en la URL pública indicada abajo. La página y la ruta de salud responden desde Internet. En la comprobación visual se detectó que el navegador no aplica correctamente los estilos; se documenta la causa técnica observada y la corrección necesaria, que se aplicará en una actualización posterior. Por ello, este informe acredita la publicación y las pruebas HTTP realizadas, no una validación completa de todas las funciones del sistema.

| Dato de entrega | Estado |
| --- | --- |
| URL pública | https://itsa-autenticidad.onrender.com |
| Repositorio GitHub | https://github.com/Luisorp/itsa-autenticidad |
| Rama desplegada | main |
| Servicio web | Render, plan Free, contenedor Docker |
| Base de datos | MySQL 8.4 en Aiven, plan Free |
| Estado de verificación | URL y ruta /up responden HTTP 200; el despliegue está Live. Se detectó un problema de estilos HTTPS en la interfaz |

## 1. Introducción

En el entorno local, la aplicación se ejecutaba en localhost, por lo que solo era accesible desde la computadora del desarrollador. El propósito de esta actividad es preparar una publicación accesible por Internet usando servicios gratuitos, sin comprar un dominio ni contratar inicialmente un servidor de pago.

La aplicación está desarrollada con Laravel y PHP. Para el despliegue se eligió una imagen Docker que incorpora PHP y Apache, construye los recursos de frontend con Node y Vite, y conecta Laravel con una base de datos MySQL administrada por Aiven. Render aloja la aplicación y entrega una dirección pública bajo su dominio onrender.com.

El trabajo se realizó para una demostración académica. El plan gratuito no debe considerarse una plataforma de producción con disponibilidad garantizada.

## 2. Componentes y configuración del despliegue

| Componente | Configuración aplicada |
| --- | --- |
| Código fuente | Repositorio GitHub Luisorp/itsa-autenticidad, rama main |
| Imagen de aplicación | Dockerfile del repositorio |
| Entorno PHP | PHP 8.3 con Apache y extensiones requeridas por Laravel |
| Recursos frontend | Node 22 y Vite durante la construcción de la imagen |
| Puerto del contenedor | Apache escucha en el puerto 10000 |
| Aplicación | Laravel sirve desde el directorio public |
| Base de datos | Aiven MySQL 8.4, base defaultdb |
| Seguridad de conexión | Certificado CA de Aiven y variable MYSQL_ATTR_SSL_CA |
| Supervisión HTTP | Ruta de salud /up |
| Plan seleccionado | Render Free: 0.1 CPU y 512 MB RAM, según la pantalla de creación |
| Persistencia de archivos | No habilitada; el plan Free de Render tiene sistema de archivos efímero |

El Dockerfile ejecuta `php artisan migrate --force` al arrancar el contenedor y luego inicia Apache. Render muestra el servicio Live y la ruta `/up` responde HTTP 200; esto es consistente con que el comando de inicio avanzó hasta Apache. No se adjunta una consulta directa a las tablas de Aiven, así que el alcance de la verificación de base de datos se limita a la disponibilidad observada del servicio.

## 3. Procedimiento realizado

### 3.1 Preparación del código

1. Se revisó que el repositorio estuviera en la rama main y sincronizado con origin/main.
2. Se prepararon Dockerfile y .dockerignore para construir la aplicación en Render sin subir .env, vendor, node_modules ni archivos de runtime.
3. El Dockerfile construye los recursos de frontend con npm ci y npm run build, instala dependencias de producción de Composer, configura Apache para servir public y exponer el puerto 10000.
4. Se subieron los cambios de preparación del despliegue a GitHub. En el historial local revisado aparecen los commits:
   - f2ed659 — Prepara despliegue en Render.
   - 4a380ec — Actualiza gestión de docentes.
   - cbd4767 — Agrega certificado CA de Aiven.
5. Se mantuvieron fuera del repositorio las credenciales locales. El archivo .env no debe incluir credenciales reales ni claves de producción.

### 3.2 Creación de MySQL en Aiven

1. Se creó un servicio Aiven for MySQL y se esperó a que su estado cambiara de Building a Running.
2. Se eligió el plan Free. La pantalla de Aiven indicaba MySQL 8.4, una instancia, 1 GB de RAM y 1 GB de almacenamiento.
3. En Connection information se consultaron el host, puerto, nombre de la base de datos, usuario y contraseña. Esos valores se usan en Render y no se incluyen en este documento.
4. Se descargó el certificado CA proporcionado por Aiven como ca.pem.
5. El certificado se copió al repositorio como docker/aiven-ca.pem y se registró el commit cbd4767. El certificado CA no es la contraseña de la base de datos; las credenciales siguen siendo secretas.
6. La configuración Laravel para MySQL lee MYSQL_ATTR_SSL_CA y se indicó en Render la ruta /var/www/html/docker/aiven-ca.pem.
7. No se importó una copia de la base local. En el servidor se espera crear el esquema ejecutando las migraciones de Laravel; no se deben cargar datos personales o académicos reales para la demostración.

### 3.3 Creación del servicio web en Render

1. En el panel se inició la creación de un Web Service, no una base Postgres, porque el proyecto utiliza MySQL y la base ya se creó en Aiven.
2. Se conectó la cuenta de GitHub y se concedió acceso únicamente al repositorio Luisorp/itsa-autenticidad.
3. Se seleccionó ese repositorio, la rama main y el lenguaje Docker, detectado por Render.
4. Se dejó Root Directory vacío porque el Dockerfile se encuentra en la raíz del repositorio.
5. La pantalla inicialmente tenía seleccionado un plan de 7 USD mensuales. Se cambió manualmente al plan Free de 0 USD mensuales antes de continuar.
6. Se configuró Health Check Path como /up. Las opciones Docker Command y Pre-Deploy Command se dejaron vacías; el comando de inicio del Dockerfile aplica las migraciones y arranca Apache.
7. Se agregaron manualmente variables de entorno en Render. No se usó Add from .env.

| Variable configurada | Valor guardado en Render |
| --- | --- |
| APP_ENV | production |
| APP_DEBUG | false |
| APP_KEY | Clave generada con php artisan key:generate --show; no se reproduce en el informe |
| DB_CONNECTION | mysql |
| DB_HOST | Host proporcionado por Aiven; omitido por seguridad |
| DB_PORT | Puerto proporcionado por Aiven |
| DB_DATABASE | defaultdb |
| DB_USERNAME | Usuario proporcionado por Aiven |
| DB_PASSWORD | Contraseña secreta; omitida |
| MYSQL_ATTR_SSL_CA | /var/www/html/docker/aiven-ca.pem |
| APP_URL | No estaba configurada como URL HTTPS durante la comprobación visual |

8. Se inició el despliegue desde Render. El panel confirmó **Deploy succeeded | Live**, con duración aproximada de 1 minuto y 53 segundos, desde el commit `cbd4767`. Render asignó la URL https://itsa-autenticidad.onrender.com.
9. Se abrió la URL pública y la ruta `/up` desde Internet; ambas respondieron con código HTTP 200. La vista de acceso mostró el contenido, pero sin el formato esperado.
10. Al revisar el HTML servido, se observó que los enlaces del CSS y JavaScript comenzaban con `http://` aunque la página pública usa `https://`. El navegador puede bloquear esos recursos como contenido mixto. La configuración recomendada es establecer `APP_URL=https://itsa-autenticidad.onrender.com` en Render y confiar en el proxy HTTPS de Render mediante `$middleware->trustProxies(at: '*');` en `bootstrap/app.php`. Esta corrección de código no forma parte del commit desplegado en la captura; se dejó para una actualización posterior.

## 4. Problemas encontrados y soluciones aplicadas

| Problema o riesgo | Solución aplicada |
| --- | --- |
| Render mostró primero una pantalla para crear Postgres. | Se continuó con New Web Service. Se conservó MySQL en Aiven para coincidir con la conexión del proyecto. |
| Render preseleccionó un plan de 7 USD al mes. | Se seleccionó el plan Free de 0 USD antes de crear el servicio. |
| GitHub solicitó instalar y autorizar la integración de Render. | Se limitó el acceso al repositorio del proyecto en lugar de dar acceso a todos los repositorios. |
| La conexión remota MySQL requiere parámetros de Aiven y TLS/CA. | Se descargó el certificado CA, se guardó en docker/aiven-ca.pem y se configuró la variable MYSQL_ATTR_SSL_CA. |
| El archivo .env contiene secretos locales y no debe publicarse. | Se dejaron fuera las credenciales de GitHub y se escribieron directamente en Environment Variables de Render. |
| La primera APP_KEY generada quedó expuesta en el intercambio de configuración. | Se descartó esa clave y se generó una nueva. La clave que se usa debe permanecer solo en Render y no incluirse en capturas o documentos. |
| La vista pública se mostraba sin estilos. | La inspección del HTML encontró que los recursos CSS/JS se generan con `http://`. La causa probable es que Laravel no reconoce el protocolo HTTPS recibido mediante el proxy inverso. Se identificó como corrección establecer APP_URL con HTTPS y configurar TrustProxies en `bootstrap/app.php`; esta actualización se difirió para realizarla posteriormente. |
| Los PDF guardados en el almacenamiento local no tienen persistencia en Render Free. | Se documenta como limitación: usar archivos de prueba solamente. Para conservarlos se requiere almacenamiento persistente compatible, por ejemplo un disco de pago u object storage, más la configuración correspondiente. |

## 5. Evidencias de configuración y capturas

Durante el procedimiento se compartieron capturas de Aiven, Render y GitHub. Para la entrega académica, conviene incluir las capturas originales en este apartado o como anexos; las imágenes del chat no se han incorporado automáticamente en este archivo Word.

| N.º | Captura que se debe insertar | Qué debe mostrar | Protección de datos |
| --- | --- | --- | --- |
| 1 | Aiven — Overview del servicio MySQL | Nombre del servicio y estado Running | Ocultar o recortar Service URI, contraseña y detalles que no sean necesarios |
| 2 | Aiven — Connection information | Que existen los datos de conexión y la opción CA Certificate | Ocultar URI completa, contraseña y cualquier campo revelado |
| 3 | GitHub — historial del repositorio | Commits de preparación Docker y del certificado CA | No mostrar tokens ni configuraciones personales |
| 4 | Render — configuración del Web Service | Repositorio, rama main, Docker y plan Free seleccionado | No mostrar variables con valores |
| 5 | Render — Environment Variables | Nombres de variables configuradas | Ocultar o difuminar todos los valores, en particular APP_KEY, DB_PASSWORD y DB_HOST |
| 6 | Render — Events/Logs del despliegue final | Resultado del build, migraciones y estado Live | Ocultar secretos que pudieran aparecer accidentalmente en logs |
| 7 | Navegador — aplicación pública | La URL pública y `/up` responden; la vista de acceso fue mostrada sin estilos durante esta verificación | No mostrar información académica real; repetir la captura después de corregir el protocolo de los recursos |

> No se deben adjuntar capturas que muestren APP_KEY, DB_PASSWORD, URL de conexión completa, tokens, códigos de recuperación o datos personales reales.

## 6. Verificación y resultado

| Comprobación | Resultado verificable al elaborar este informe |
| --- | --- |
| Servicio MySQL de Aiven | El estudiante informó que cambió a Running |
| Certificado CA en el repositorio | Confirmado en el repositorio local y en el commit cbd4767 |
| Rama main sincronizada con origin/main | Confirmado en la revisión local |
| Plan Free de Render | Seleccionado en el formulario de creación; la captura muestra 0 USD por mes |
| Health Check Path /up | Ingresado en el formulario |
| Deploy Web Service | Render muestra Deploy succeeded y estado Live; duración aproximada 1m53s |
| Build Docker | Render muestra la exportación y publicación de la imagen terminadas; despliegue asociado al commit cbd4767 |
| Migraciones MySQL | El contenedor pasó al estado Live y `/up` responde; el Dockerfile ejecuta `migrate --force` antes de iniciar Apache. No se hizo consulta directa a las tablas |
| URL pública | https://itsa-autenticidad.onrender.com respondió HTTP 200 desde una solicitud externa |
| Ruta de salud | `/up` respondió HTTP 200 |
| Presentación visual | La página carga, pero se observó sin estilos; los links CSS/JS se generan con `http://` y se recomendó confiar en el proxy HTTPS |
| Inicio de sesión y flujo principal | No se registró una prueba funcional de autenticación en esta verificación |

### Criterio para declarar la entrega verificada

La publicación quedó confirmada: Render indica Live y la URL pública y `/up` respondieron HTTP 200. La verificación visual también detectó una incidencia con la generación de enlaces HTTPS de CSS/JavaScript. La causa y la corrección recomendada están documentadas en este informe; no se afirma que dicha corrección ya se haya desplegado ni que se hayan probado todos los flujos funcionales.

## 7. Respuestas a las preguntas de la actividad

### ¿Cuál es la diferencia entre ejecutar Laravel en localhost y ejecutarlo en un hosting?

En localhost, el servidor se ejecuta en la computadora del desarrollador y normalmente solo esa computadora puede acceder a él mediante una dirección local. Depende de que el equipo esté encendido, del entorno instalado y de la red local. En un hosting, el servidor se ejecuta en infraestructura accesible por Internet, tiene una URL pública, recibe conexiones externas y debe configurarse para producción. También intervienen las reglas de red, HTTPS, recursos contratados, variables de entorno, base de datos remota, permisos del sistema de archivos y procesos de despliegue.

En esta actividad, Render ejecuta el contenedor Docker y Aiven proporciona MySQL en otra plataforma. Las credenciales se configuran en Render para que el contenedor conecte con Aiven por la red usando el certificado CA.

### ¿Por qué no se debe subir .env con credenciales reales a GitHub?

Ese archivo puede contener contraseñas, claves de cifrado, tokens y direcciones de servicios. Si se publica, una persona no autorizada podría acceder a la base de datos, suplantar servicios o comprometer datos. El repositorio debe contener .env.example con nombres de variables y valores de ejemplo, nunca secretos válidos. En este despliegue los valores reales se escribieron en el panel de Render y APP_DEBUG se dejó en false.

### ¿Qué función cumple APP_KEY?

APP_KEY es la clave privada de la aplicación que Laravel utiliza para sus funciones de cifrado y protección de datos firmados, como valores cifrados y cookies cifradas. Se genera con el comando de Laravel y debe guardarse como secreto en el entorno de ejecución. No se debe publicar ni regenerar después de que la aplicación almacene datos cifrados, porque los valores creados con la clave anterior podrían dejar de ser legibles.

### ¿Qué problemas pueden aparecer en el despliegue aunque el sistema funcione localmente?

- Diferencias de versiones de PHP, extensiones y paquetes; por eso la imagen especifica PHP 8.3 y construye los recursos con Node 22.
- Variables ausentes o incorrectas, especialmente APP_KEY, APP_URL y parámetros de conexión a MySQL.
- Errores de red, puerto, usuario, contraseña o certificado CA al conectar con Aiven.
- Migraciones incompatibles o fallidas al crear el esquema remoto.
- Diferencias entre Windows local y Linux en nombres de archivos, mayúsculas/minúsculas y permisos.
- Recursos de frontend no compilados, rutas mal configuradas o falta del manifiesto de Vite.
- Límites de CPU, memoria y tiempo del plan Free.
- Archivos PDF que desaparecen si se guardan en el sistema efímero del plan gratuito.
- Tiempo de arranque inicial después del periodo sin tráfico y posibles reinicios del servicio.
- Una prueba local satisfactoria no demuestra por sí sola que la instancia pública esté operativa. En esta publicación, la página y `/up` respondieron HTTP 200, pero la pantalla de acceso mostró un problema de recursos HTTPS que fue diagnosticado.

### ¿Qué limitaciones tiene el hosting gratuito seleccionado?

Render Free ofrece recursos pequeños: la pantalla de creación mostró 0.1 CPU y 512 MB de RAM. El servicio se suspende temporalmente tras 15 minutos sin tráfico y el primer acceso posterior puede tardar alrededor de un minuto. El sistema de archivos es efímero; los cambios locales, incluidos PDF subidos a la aplicación, se pierden en reinicios, redeploys o suspensión. No incluye disco persistente para el plan Free y no debe usarse como disponibilidad garantizada de producción.

Aiven MySQL Free ofrece una sola instancia con 1 CPU, 1 GB de RAM y 1 GB de almacenamiento, sin alta disponibilidad ni SLA de pago. Aiven puede apagar servicios gratuitos que permanezcan sin uso continuado y notifica antes de hacerlo. Ambos servicios son apropiados para aprendizaje, demostraciones o prototipos pequeños, no para datos institucionales reales ni cargas de trabajo críticas.

### ¿Qué cambios son necesarios para migrar a un hosting de pago?

1. Elegir una instancia Render de pago con CPU y memoria acordes al uso, y configurar dominio propio y HTTPS si se requiere.
2. Contratar una base MySQL con almacenamiento, copias de seguridad y disponibilidad apropiados; actualizar variables de entorno sin cambiar el código de conexión si se conserva MySQL.
3. Migrar el esquema con las migraciones de Laravel y, si corresponde, exportar/importar los datos con una copia de seguridad validada.
4. Trasladar los PDF a un almacenamiento persistente: disco persistente compatible con el hosting o almacenamiento de objetos S3-compatible. No basta con mover la base, porque los archivos guardados en storage son independientes de las filas de la base de datos.
5. Configurar copias de seguridad, monitoreo, alertas, dominio, TLS, límites de recursos y plan de recuperación.
6. Mantener APP_KEY y demás secretos fuera de GitHub; revisar APP_DEBUG=false, permisos, logs y una política de actualización/rotación del CA de la base.
7. Probar login, permisos, búsquedas, carga/descarga de PDF, comparación y reportes con una copia de prueba antes de cambiar a los usuarios.

La aplicación no necesariamente requiere reescritura para pasar a pago: Docker y las variables de entorno permiten cambiar recursos y servicios. La migración de archivos, datos y configuración operativa sí debe planificarse y verificarse.

## 8. Limitaciones y uso responsable de la demostración

La instancia gratuita es de demostración. El repositorio de GitHub no contiene la base de datos ni los proyectos académicos locales. Aiven se creó para esta instalación; el estado Live y la respuesta de `/up` son compatibles con el arranque previsto, aunque no se adjuntó una consulta directa de las tablas. Los documentos PDF existentes tampoco se copiaron. Los PDF nuevos guardados localmente en el contenedor pueden perderse por la naturaleza efímera del almacenamiento de Render Free.

No se deben cargar documentos institucionales, datos de estudiantes ni credenciales reales de usuarios mientras no exista una solución de almacenamiento persistente, política de respaldo y autorización institucional para publicar el sistema.

## 9. Defensa breve del trabajo realizado

“Publiqué el sistema desde GitHub en Render mediante Docker y conecté Laravel con MySQL de Aiven usando TLS. El despliegue quedó Live en https://itsa-autenticidad.onrender.com. Comprobé que la página y la ruta de salud responden por Internet. Durante la revisión visual detecté que los estilos no se aplicaban porque las direcciones de los recursos CSS y JavaScript se generan como HTTP mientras la página usa HTTPS. Documenté como solución configurar la URL segura de la aplicación y confiar en el proxy de Render. La instancia gratuita sirve para una demostración académica, pero sus recursos y almacenamiento no persistente no son adecuados para datos institucionales reales. Distingo entre lo que ya comprobé y la corrección visual que se aplicará en una actualización posterior.”

## 10. Fuentes oficiales consultadas

1. Render, Free instances and limitations: https://render.com/docs/free
2. Render, Web Services: https://render.com/docs/web-services
3. Render, Docker deployments: https://render.com/docs/docker
4. Render, Environment Variables and Secrets: https://render.com/docs/configure-environment-variables
5. Laravel 13, Deployment: https://laravel.com/docs/13.x/deployment
6. Laravel 13, Encryption: https://laravel.com/docs/13.x/encryption
7. Aiven for MySQL, Free tier: https://aiven.io/docs/products/mysql/concepts/mysql-free-tier
8. Aiven, TLS/SSL certificates: https://aiven.io/docs/platform/concepts/tls-ssl-certificates
