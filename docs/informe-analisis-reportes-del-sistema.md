# Informe: análisis de los reportes del sistema

**Instituto Tecnológico Sacaba**  
**Carrera:** Sistemas Informáticos  
**Asignatura:** Análisis y Diseño de Sistemas  
**Tarea:** Elaboración de informes de seguimiento — análisis de reportes que debe generar el proyecto  
**Sistema analizado:** Sistema de autenticidad de proyectos de titulación  
**Fecha:** 2 de noviembre de 2026

## Resumen

Este informe identifica los reportes y mecanismos de seguimiento necesarios para el Sistema de Autenticidad de Proyectos de Titulación. El análisis se realizó revisando las rutas, controladores, modelos y vistas existentes en el proyecto. Se encontró un panel de indicadores, un reporte PDF de análisis por proyecto, un listado de reportes generados, resultados de comparación interna y una pantalla para administrar respaldos SQL.

El principal reporte para apoyar la revisión académica es el PDF de análisis de un proyecto. El panel y el catálogo ayudan a monitorear el estado del repositorio. La comparación manual entre dos proyectos presenta resultados en pantalla, pero no genera actualmente un documento persistente de seguimiento. La versión vigente compara proyectos del repositorio institucional y no consulta servicios externos. El respaldo SQL es una función operativa de protección de datos, no un reporte académico.

El documento describe las funciones observadas sin inventar cantidades de proyectos, porcentajes institucionales ni resultados de uso. Los indicadores reales dependen de los datos existentes cuando se consulta el sistema.

## 1. Introducción

El sistema administra proyectos de titulación, sus documentos PDF y los datos académicos asociados. También permite comparar el contenido de los proyectos, revisar coincidencias textuales y descargar un reporte PDF. Para que administradores, gestores y usuarios comprendan el estado del repositorio y puedan dar seguimiento a los análisis, la información debe presentarse en reportes claros, actualizados y acordes con los permisos de cada rol.

Un reporte no solo muestra cifras: debe indicar a qué proyecto o periodo corresponde, cuándo se generó, qué método y alcance se utilizaron, y cuáles son las limitaciones de los resultados. En particular, un porcentaje de coincidencia textual es un indicador para revisión, no una determinación automática de plagio.

## 2. Objetivos

### Objetivo general

Analizar los reportes y mecanismos de seguimiento que utiliza o debería utilizar el sistema para apoyar la gestión del repositorio académico y la revisión de similitud textual de los proyectos.

### Objetivos específicos

- Identificar los reportes y vistas de seguimiento ya disponibles.
- Describir la información, utilidad y usuarios de cada salida.
- Revisar cómo se consultan, generan y conservan los resultados.
- Señalar diferencias que puedan afectar la interpretación o trazabilidad.
- Proponer una organización de reportes coherente con las funciones y permisos actuales.

## 3. Alcance y método de análisis

Se revisaron el panel principal, el catálogo de proyectos, la pantalla de resultados, el PDF de análisis, el listado de reportes, la comparación interna de proyectos y el módulo de respaldos. También se contrastaron estas pantallas con las rutas y la lógica de sus controladores. La integración con Crossref y OpenAlex no forma parte de la versión vigente.

Este es un análisis funcional del proyecto y de sus salidas. No se utilizaron datos reales de producción ni se ejecutó un monitoreo prolongado de actividad; por eso el informe no afirma cantidades de uso, proyectos procesados, disponibilidad de servicios externos ni porcentajes institucionales. Los valores que aparecen en las pantallas se calculan con los registros existentes al momento de la consulta.

## 4. Usuarios y necesidades de información

| Usuario | Necesidad de seguimiento |
|---|---|
| Administrador | Conocer el estado global del repositorio, revisar reportes institucionales y administrar respaldos. |
| Gestor | Consultar proyectos, revisar resultados de análisis y generar reportes de los proyectos que gestiona. |
| Usuario | Consultar los proyectos asociados a su cuenta y los reportes de sus propios proyectos, de acuerdo con las restricciones del sistema. |

Las vistas y rutas deben respetar el rol y la relación entre el usuario y el proyecto. Los reportes contienen nombres de estudiantes, títulos de trabajos y porcentajes; por tanto, no deben tratarse como información pública sin autorización.

## 5. Reportes y mecanismos de seguimiento observados

| Salida | Información principal | Disponibilidad observada | Utilidad |
|---|---|---|---|
| Panel de indicadores | Total de proyectos activos visibles, pendientes de análisis, analizados, promedio de similitud, rangos de similitud, proyectos recientes y carreras activas. | Disponible en el panel al iniciar sesión. Los valores se calculan al consultar la pantalla; no se observó una exportación del panel. | Permite una visión rápida del estado del repositorio y de la actividad de análisis. |
| Catálogo y estado de proyectos | Proyecto, estudiante, modalidad, carrera, gestión/año, estado del análisis y acceso al documento; ofrece búsqueda, filtros y ordenamiento. La vista muestra contadores de proyectos disponibles, pendientes de análisis y analizados. | Disponible en pantalla; no se observó una opción para descargar el catálogo como informe. El controlador también calcula archivados, pero el contador no aparece en la vista revisada. | Facilita localizar proyectos y detectar cuáles necesitan análisis o revisión. |
| Reporte PDF de análisis por proyecto | Título, modalidad, estudiante, carrera, año, tutor, fecha de generación, persona que lo genera y tabla de proyectos comparados con su porcentaje de similitud. | Se genera y descarga desde la ficha o los resultados del proyecto; se guarda una copia en el almacenamiento público de la aplicación y un registro asociado al proyecto. | Proporciona evidencia documental para revisar las coincidencias de un proyecto concreto. |
| Listado de reportes generados | Proyecto, persona que generó el reporte, fecha y disponibilidad/enlace al PDF. Presenta los registros en páginas de diez elementos. | Disponible en la opción «Reportes generados». Los usuarios de tipo Usuario ven reportes de sus proyectos; los roles institucionales consultan el listado general. | Permite localizar y volver a abrir un reporte PDF existente. |
| Resultados de comparación interna | Porcentajes de similitud de un proyecto respecto al repositorio comparable, ordenados de mayor a menor; filtros por porcentaje y opción de mostrar todos los resultados. | Disponible en pantalla y enlazado a la descarga del PDF del proyecto. | Ayuda a priorizar las coincidencias que deben ser revisadas por una persona. |
| Comparación manual de dos proyectos | Porcentaje global, explicación basada en términos y fragmentos de texto relacionados entre dos proyectos seleccionados. | Disponible para Administrador y Gestor como resultado en pantalla. El flujo revisado no genera ni archiva un PDF específico de esta comparación. | Sirve para examinar una pareja de proyectos de forma puntual. |
| Listado de respaldos SQL | Nombre del archivo de respaldo, tamaño y fecha; ofrece generar y descargar una copia. | Módulo disponible para el Administrador. La generación es manual. | Apoya la recuperación de registros de la base de datos; es un mecanismo de continuidad, no un informe de resultados académicos. |

## 6. Contenido y alcance del reporte PDF principal

El reporte PDF por proyecto es la salida documental central del sistema. Para que sea interpretable y útil para la revisión académica debe leerse junto con la información de alcance que incluye el propio reporte:

1. **Identificación:** proyecto, modalidad, estudiante, carrera, año, tutor, fecha y persona que genera el documento.
2. **Resultados:** proyectos comparados y porcentaje de coincidencia textual.
3. **Alcance textual:** el procesamiento busca secciones reconocidas del documento —resumen, introducción, desarrollo/propuesta y conclusiones— y omite preliminares, marco teórico o capítulo II, bibliografía y anexos, según los encabezados que logre reconocer en el PDF.
4. **Método:** la implementación compara secuencias textuales de cinco palabras consecutivas y conserva el orden y las cifras. Los porcentajes no representan similitud semántica.
5. **Interpretación:** una coincidencia es una señal para análisis humano. No demuestra por sí sola plagio ni reemplaza la evaluación del tribunal.
6. **Límites de extracción:** los PDF escaneados o con encabezados que el extractor no reconoce pueden no aportar texto analizable; el sistema no debe presentar esa ausencia como resultado de similitud igual a cero.

El documento PDF actualmente presenta datos del proyecto comparado y su porcentaje; no incluye los fragmentos coincidentes del análisis interno en la tabla del reporte. Esos fragmentos se muestran en ciertas vistas de comparación, por lo que podrían incorporarse en una versión futura del PDF si se requiere evidencia textual imprimible.

## 7. Seguimiento, frecuencia y conservación

- **Panel:** se recalcula al abrir la pantalla con los registros actuales; no es un informe periódico ni una exportación histórica.
- **Catálogo:** permite consultar el estado actual y filtrar proyectos por carrera, gestión/año, modalidad, estado y texto de búsqueda.
- **PDF por proyecto:** se genera cuando una persona autorizada solicita la descarga. El sistema conserva un archivo y un registro asociado al proyecto.
- **Historial mostrado:** el flujo de generación utiliza `updateOrCreate` buscando el reporte por proyecto. Por ello, la pantalla funciona como listado de reportes actualmente registrados, pero no conserva necesariamente cada versión histórica generada para un mismo proyecto.
- **Comparación entre proyectos:** se calcula cuando el usuario la solicita y sus resultados se presentan en pantalla; no se encontró un registro histórico propio de cada ejecución.
- **Respaldo:** se genera manualmente y el módulo muestra el archivo, tamaño y fecha. El SQL incluye las tablas de negocio que selecciona el controlador; los documentos PDF y otros archivos del almacenamiento deben copiarse aparte, tal como advierte la pantalla de respaldos.

## 8. Hallazgos del análisis

### 8.1 Diferencia entre los rangos de similitud

Las categorías no son uniformes en todas las vistas:

| Vista | Baja | Media | Alta |
|---|---:|---:|---:|
| Panel | Hasta 25 % | Más de 25 % y hasta 75 % | Más de 75 % |
| Resultados de proyecto, PDF y comparación entre proyectos | Menos de 40 % | Desde 40 % hasta menos de 70 % | Desde 70 % |

Un mismo porcentaje puede clasificarse de manera distinta según la pantalla. Se recomienda definir un único conjunto de límites y usarlo en el panel, resultados y PDF. El porcentaje debe seguir presentándose como orientativo, sin etiquetarlo como decisión automática de plagio.

### 8.2 Trazabilidad de las versiones PDF

El flujo de generación actualiza o crea un registro por proyecto. Esto permite consultar el PDF vigente, pero no constituye un historial completo de todas las versiones: una nueva generación puede sustituir los datos del registro anterior. Si la institución necesita auditoría, se recomienda guardar una entrada independiente por generación con fecha, usuario, versión del algoritmo y archivo correspondiente.

### 8.3 Resultados no persistentes

La comparación manual entre dos proyectos entrega resultados en pantalla, pero no se observó una forma propia de guardarlos como reporte histórico. Para reproducir o auditar esos análisis se recomienda registrar, como mínimo, fecha, usuario, proyectos consultados, versión del método y resultado obtenido.

### 8.4 Alcance de los contadores del catálogo

El listado de proyectos aplica filtros de visibilidad por usuario, mientras que los contadores del catálogo se calculan sobre los registros institucionales. Conviene confirmar que mostrar cifras globales a usuarios de tipo Usuario sea intencional; si no lo es, aplicar a los contadores el mismo alcance de visibilidad del listado.

### 8.5 Alcance de la comparación interna

La comparación se limita a los documentos de proyectos almacenados en el repositorio institucional. No realiza búsquedas en Internet ni consulta fuentes académicas externas. Por ello, los resultados describen coincidencias dentro de ese repositorio y no permiten afirmar que un proyecto no coincida con documentos que no estén registrados en el sistema.

### 8.6 Respaldo de datos y archivos

El respaldo SQL y los documentos PDF son componentes distintos. Un archivo SQL no basta para reconstruir el repositorio documental: también se deben proteger y recuperar los archivos almacenados. La pantalla del sistema informa esta separación. La restauración debe probarse con precaución porque el SQL generado contiene instrucciones que eliminan y vuelven a insertar registros en las tablas incluidas.

### 8.7 Seguimiento de tendencias

El panel presenta una fotografía de los datos actuales, no una serie histórica. En las pantallas revisadas no se observó selección de periodos ni conservación periódica de los indicadores para comparar la evolución mensual o por sprint. Por ello, el sistema permite conocer el estado actual, pero no sustenta por sí solo conclusiones sobre tendencias a lo largo del tiempo.

## 9. Propuesta de informes que debe ofrecer el proyecto

La siguiente propuesta prioriza salidas útiles y coherentes con las funciones existentes:

| Prioridad | Informe recomendado | Contenido mínimo | Estado respecto al sistema observado |
|---|---|---|---|
| Alta | Resumen de seguimiento del repositorio | Fecha de consulta, proyectos activos/archivados, pendientes/analizados, promedio de similitud, distribución por rangos y carrera/modalidad cuando el filtro esté disponible. | El panel ya presenta varios indicadores; conviene unificar rangos, conservar cortes por periodo y permitir exportar o imprimir el resumen si se necesita entregar evidencia. |
| Alta | Reporte individual de análisis de autenticidad | Identificación del proyecto, fecha, usuario, alcance del documento, método/versión, comparables, porcentajes, advertencia interpretativa y estado de fuentes, si aplica. | Existe PDF interno; se recomienda mantener criterios uniformes y mejorar la trazabilidad de versiones. |
| Media | Reporte de estado de proyectos | Filtros aplicados, fecha, totales y detalle de proyecto, carrera, gestión/año, modalidad, estudiante y estado de análisis. | El catálogo y sus contadores existen en pantalla; no se observó exportación formal. |
| Media | Historial auditable de análisis/reportes | Cada ejecución, usuario, fecha, proyecto o pareja consultada, método utilizado, archivo generado y estado de disponibilidad. | Existe un listado de PDF por proyecto, pero no conserva de forma completa las versiones ni las comparaciones manuales entre proyectos. |
| Media | Evidencia de comparación entre proyectos | Proyectos consultados, fecha, usuario, porcentaje, fragmentos coincidentes y versión del método aplicado. | La comparación interna muestra resultados en pantalla; conviene conservar cada ejecución si se requiere trazabilidad. |
| Operativa | Inventario de respaldos | Nombre, fecha, tamaño, responsable, tablas incluidas y confirmación de copia de archivos documentales. | El sistema enumera y descarga respaldos SQL; la copia de documentos debe controlarse aparte. |

## 10. Requisitos de calidad recomendados para los reportes

- **Exactitud:** calcular los indicadores con registros válidos y mostrar el periodo o filtro aplicado.
- **Actualidad:** indicar la fecha y hora de generación o de consulta.
- **Trazabilidad:** identificar proyecto, usuario generador y método/versión del algoritmo cuando corresponda.
- **Comprensión:** explicar qué significa el porcentaje y qué texto se comparó; distinguir «sin comparaciones» de «0 % de similitud».
- **Confidencialidad:** limitar vistas y descargas según rol y relación del usuario con el proyecto.
- **Disponibilidad:** advertir si el PDF no existe o si no hay documentos compatibles para comparar.
- **Consistencia:** usar los mismos rangos y nombres para las categorías de similitud en todas las salidas.
- **Integridad documental:** respaldar la base de datos y los PDF/documentos asociados; verificar la restauración antes de depender de una copia.

## 11. Conclusiones

El proyecto ya cuenta con salidas valiosas para el seguimiento: panel de indicadores, estado del catálogo, resultados de comparación y reporte PDF individual. El PDF es el medio más adecuado para documentar el análisis de autenticidad de un proyecto; el panel y el catálogo permiten monitorear el estado general y localizar trabajos pendientes.

El listado llamado «Reportes generados» permite acceder a documentos existentes, pero el modelo actual no debe interpretarse como una bitácora completa de todas las versiones. La comparación manual presenta resultados en pantalla sin producir un historial persistente propio. Los respaldos SQL apoyan la continuidad del sistema, pero no sustituyen la copia de los documentos PDF.

Para que los informes sean confiables y comparables, el proyecto debe mantener límites de similitud consistentes, aclarar que el porcentaje no determina plagio, registrar de forma trazable las ejecuciones y respetar la visibilidad por usuario. El análisis se basa en el comportamiento observado en el código y no atribuye cifras de actividad que no fueron suministradas.

## 12. Recomendaciones

1. Unificar los rangos de similitud del panel, resultados y PDF.
2. Diferenciar claramente ausencia de comparaciones, error de extracción y resultado de 0 %.
3. Si se requiere auditoría, conservar cada generación de reporte como una versión separada y registrar el método usado.
4. Ofrecer exportación del resumen de seguimiento y del estado filtrado de proyectos cuando la institución requiera entregarlos como evidencia.
5. Permitir guardar como evidencia las comparaciones entre proyectos, incluyendo fecha, documentos consultados y método aplicado.
6. Revisar que los contadores de proyectos respeten el mismo alcance de permisos que el catálogo.
7. Mantener una política de respaldo que incluya tanto la base de datos como documentos PDF y probar periódicamente la restauración.

## Anexo: resumen ejecutivo

**Reportes principales del proyecto:** (1) panel de seguimiento del repositorio; (2) reporte PDF individual de análisis de autenticidad; (3) consulta de estado de proyectos; (4) listado de reportes disponibles. **Salidas complementarias:** comparación entre proyectos, que se visualiza en pantalla, e inventario de respaldos, que cumple una función operativa. La comparación vigente utiliza documentos del repositorio institucional y no depende de servicios externos. El principal punto de consistencia detectado es que los rangos de similitud difieren entre el panel y el PDF/resultados. Las cifras deben interpretarse como coincidencia textual orientativa y revisarse por una persona.
