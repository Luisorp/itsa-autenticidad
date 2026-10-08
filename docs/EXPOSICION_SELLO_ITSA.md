# Sello ITSa
## Guion de exposición: gestión y comparación de proyectos de titulación

**Duración prevista:** 12 a 15 minutos, incluidas pausas y una demostración breve.  
**Exposición:** 9 de octubre de 2026.  
**Enfoque:** problema, utilidad, funcionamiento y fundamento de la comparación.  
**Presenta:** completa aquí tu nombre y tu carrera.

### Cómo usar este documento

Lee en voz alta solamente los párrafos del **guion principal**. Las indicaciones entre corchetes sirven para orientarte; no se leen. El anexo es para estudiar y responder preguntas. Ensaya con cronómetro: los tiempos son una referencia, no una duración garantizada. Si necesitas reducir la exposición a 12 minutos, acorta el recorrido por los módulos y explica los ejemplos sin hacer todas las operaciones.

**Aclaración para tu defensa:** en la versión actual, TF-IDF se utiliza como apoyo para explicar el vocabulario compartido. La función de similitud coseno está implementada, pero no calcula el porcentaje principal mostrado por la comparación actual. Ese porcentaje procede de secuencias de cinco palabras y del coeficiente Dice. No presentes TF-IDF, coseno y Dice como un único cálculo ni como porcentajes intercambiables.

### Distribución del tiempo

| Bloque | Tiempo orientativo | Qué mostrar |
| --- | --- | --- |
| Presentación y problema | 0:00–1:45 | Nombre del sistema y dashboard |
| Objetivo y módulos | 1:45–3:40 | Proyectos y catálogos |
| Flujo de trabajo, roles y reportes | 3:40–5:30 | Un proyecto y su PDF |
| Demostración breve | 5:30–6:15 | Comparación ya preparada |
| Preparación del documento | 6:15–7:20 | Secciones analizadas y excluidas |
| Origen, TF-IDF y coseno | 7:20–10:50 | Un ejemplo sencillo |
| Comparación actual e interpretación | 10:50–13:15 | Porcentaje y fragmentos |
| Límites y cierre | 13:15–14:15 | Conclusión |

## Guion principal

### 1. Presentación y problema — 0:00 a 1:45

[Muestra el dashboard. Saluda y presenta tu nombre.]

Buenos días. Hoy presentaré Sello ITSa, un sistema web para gestionar proyectos de titulación y apoyar la revisión de su autenticidad mediante el análisis de similitud textual. Su finalidad es reunir los documentos académicos en un lugar organizado y ofrecer al docente información que le permita revisar posibles coincidencias entre proyectos.

El problema tiene dos partes. Primero, los proyectos necesitan conservarse junto con sus datos: quién los elaboró, a qué carrera pertenecen, quién fue su tutor y bajo qué modalidad se realizaron. Cuando esa información está dispersa en carpetas o archivos separados, localizar antecedentes y mantener un registro resulta más difícil.

La segunda parte es la revisión del contenido. Leer varios documentos completos para encontrar coincidencias requiere atención y tiempo. Además, no toda semejanza representa una copia: dos estudiantes pueden utilizar la misma metodología, mencionar al mismo tutor o describir conceptos comunes de su área.

Por eso, la propuesta combina organización documental y comparación de contenido. El sistema ayuda a localizar información relevante; la valoración académica sigue correspondiendo al docente o al tribunal.

### 2. Objetivo y módulos — 1:45 a 3:40

[Abre Proyectos y, brevemente, Estudiantes o Docentes.]

El objetivo de Sello ITSa es facilitar el registro, la consulta y la comparación de proyectos de titulación, manteniendo una relación clara entre el documento y su contexto académico.

El dashboard presenta una visión general del sistema. Desde allí se accede al módulo de proyectos, donde se consultan los trabajos registrados y se utilizan filtros para encontrar información por carrera, año, modalidad o estado.

Los catálogos de carreras, estudiantes y docentes permiten mantener los datos que acompañan a cada proyecto. Esto evita tratar el PDF como un archivo aislado: cada trabajo queda vinculado con su estudiante, su carrera y, cuando corresponde, su tutor.

También existen módulos de usuarios, análisis, reportes, respaldos y perfil. La administración de usuarios permite controlar quién accede al sistema. El análisis ayuda a revisar coincidencias. Los reportes permiten conservar o presentar los resultados. Los respaldos permiten exportar los registros para su conservación.

El sistema contempla distintas modalidades de titulación, como proyecto de grado, proyecto sociocomunitario productivo, emprendimiento productivo y trabajo dirigido externo. Esta clasificación es importante porque el contexto de un trabajo influye en qué documentos resulta razonable comparar.

La interfaz reúne estas funciones en un mismo entorno, para que el usuario pueda pasar del registro de un proyecto a la revisión de su documento y sus resultados.

### 3. Flujo de trabajo, roles y reportes — 3:40 a 5:30

[Abre un proyecto con su documento disponible.]

El flujo comienza con el registro del proyecto. Se ingresan el título, la modalidad, la carrera, el estudiante, el tutor si corresponde y el año. Después se adjunta el PDF. El sistema conserva el archivo y extrae su texto para preparar el análisis.

Una vez registrado, el documento puede consultarse desde el proyecto. El usuario autorizado puede ejecutar la comparación con otros trabajos del repositorio. En el análisis automático se consideran proyectos activos de la misma carrera y modalidad. En la comparación manual se eligen dos proyectos de la misma modalidad.

Los resultados permiten revisar un porcentaje y, en la comparación manual, los fragmentos que justifican las coincidencias. Esta segunda parte es fundamental: un número aislado ofrece menos información que observar los textos y entender por qué se relacionaron.

El acceso se organiza mediante tres roles del sistema: administrador, gestor y usuario. El administrador dispone de funciones de configuración y gestión; el gestor trabaja con los registros académicos y el análisis; el usuario tiene un acceso más limitado y consulta sus proyectos vinculados. Los estudiantes y docentes también existen como registros académicos, y no todos necesitan tener una cuenta de acceso.

Finalmente, se pueden generar reportes PDF y respaldos SQL. El reporte presenta los resultados guardados del proyecto. El respaldo conserva los registros; para proteger también los documentos, debe acompañarse de una copia de los archivos PDF.

### 4. Demostración breve — 5:30 a 6:15

[Selecciona dos proyectos preparados, ejecuta Comparar y señala el resultado y un par de fragmentos. No leas títulos largos ni todos los resultados.]

En esta pantalla selecciono dos proyectos y ejecuto la comparación. Aquí se muestra el contenido que se tomó en cuenta, el porcentaje obtenido y las coincidencias identificadas. Lo que interesa no es solamente si el porcentaje es alto o bajo, sino qué fragmentos lo explican y si esas coincidencias tienen relevancia académica.

[Si hay una demora, usa una captura preparada y continúa.]

### 5. Preparación del documento — 6:15 a 7:20

Antes de comparar, el sistema prepara el texto. Esto importa porque comparar todo el PDF sin distinguir sus partes puede producir resultados engañosos.

La versión actual busca secciones como resumen, introducción y planteamiento del problema, desarrollo o propuesta, y conclusiones y recomendaciones. Excluye preliminares, referencias, anexos y el marco teórico reconocido por sus encabezados. También excluye el capítulo dos, según la estructura documental adoptada en esta versión.

Se filtran datos del tutor, encabezados institucionales, rótulos de tablas y figuras y algunas filas de plantilla. Por ejemplo, compartir el nombre de un tutor no significa compartir el contenido de una propuesta. Del mismo modo, que dos tablas se llamen Sprint Backlog no demuestra que los proyectos sean iguales.

Estas reglas dependen de que el texto y los encabezados puedan reconocerse. Son una decisión de alcance del sistema y deben adaptarse si la institución utiliza otra estructura para sus documentos.

### 6. De dónde vienen TF-IDF y la similitud coseno — 7:20 a 8:10

La comparación de documentos tiene antecedentes en la recuperación de información: el área que estudia cómo representar y encontrar documentos relevantes en una colección.

En 1972, Karen Spärck Jones publicó un trabajo que fundamentó la idea de dar más valor a términos menos frecuentes en una colección. En 1975, Gerard Salton, Anita Wong y C. S. Yang publicaron un trabajo clásico sobre el modelo de espacio vectorial. Estos antecedentes ayudan a explicar la base de TF-IDF y de la comparación entre vectores.

No son técnicas inventadas para este proyecto. En Sello ITSa se implementaron y adaptaron a los documentos académicos. La aportación del sistema está en su integración con el repositorio, la selección del contenido y la presentación de resultados. [1, 2]

### 7. Cómo funciona TF-IDF — 8:10 a 9:40

TF-IDF asigna un peso a las palabras. Combina dos preguntas: ¿cuánto aparece una palabra dentro de este documento? y ¿en cuántos documentos de la colección aparece esa palabra?

TF significa frecuencia del término. En esta implementación, se cuenta cuántas veces aparece una palabra y se divide entre la cantidad de palabras que quedaron después del procesamiento. Si una palabra aparece diez veces entre cien palabras procesadas, su frecuencia es cero coma diez.

IDF significa frecuencia inversa de documento. Su función es reducir el peso relativo de palabras que aparecen en muchos documentos y aumentar el de términos que ayudan a distinguirlos. No se trata de si una palabra es rara en todo el idioma: se trata de su presencia en la colección que estamos comparando.

Por ejemplo, en una colección de proyectos informáticos, la palabra sistema puede aparecer en muchos trabajos. Un término más específico podría aparecer en pocos. TF-IDF combina la frecuencia dentro del proyecto con esa capacidad de distinguirlo dentro de la colección.

El resultado es una lista de palabras con pesos numéricos, que se representa como un vector. Así, el texto puede compararse matemáticamente. En el flujo manual actual, esa información permite mostrar vocabulario compartido como una explicación secundaria; no determina el porcentaje principal. [3, 4]

### 8. Cómo funciona la similitud coseno — 9:40 a 10:50

Cuando dos documentos se representan como vectores, la similitud coseno compara sus direcciones. Podemos imaginar cada vector como una flecha: si ambas apuntan en una dirección parecida, tienen un perfil de palabras parecido.

El cálculo relaciona las palabras compartidas y sus pesos, y divide ese valor entre las magnitudes de los dos vectores. Con los pesos no negativos de TF-IDF, el resultado se encuentra entre cero y uno; al multiplicarlo por cien se expresa como un porcentaje.

Un resultado cercano a uno indica una gran semejanza en el perfil de términos. Sin embargo, no significa que ese porcentaje de páginas esté copiado. Dos trabajos pueden compartir vocabulario porque estudian temas parecidos.

Además, en esta representación las palabras se cuentan sin conservar su orden. Esta limitación es una de las razones por las que la versión actual utiliza otra medida para el porcentaje principal. La función coseno está disponible en el servicio, pero no se ejecuta para calcular ese resultado en el flujo actual. [5]

### 9. Cómo se calcula la comparación actual — 10:50 a 12:25

[Señala la explicación del porcentaje en la pantalla.]

Para distinguir mejor entre compartir un tema y compartir redacción, el sistema compara secuencias de cinco palabras consecutivas, también llamadas grupos de cinco palabras o cinco-gramas. Estas secuencias conservan el orden, las cifras y las negaciones.

Por ejemplo, la frase el sistema registra los pagos genera una secuencia. Si otro documento contiene esa misma secuencia después de la normalización, existe una coincidencia textual. Compartir solamente las palabras sistema y pagos en distintas partes del documento no produce esa misma coincidencia.

El sistema cuenta las secuencias de cada texto y las que tienen en común, considerando también sus repeticiones. Luego aplica una medida conocida como coeficiente Dice: dos veces la cantidad compartida, dividida entre el total de secuencias de ambos documentos.

Como ejemplo, si el documento A tiene cien secuencias, el B tiene ochenta y comparten veinte, el cálculo es cuarenta dividido entre ciento ochenta. Multiplicado por cien, resulta aproximadamente veintidós coma veintidós por ciento.

Este número describe coincidencia de secuencias dentro del contenido analizable. No representa directamente páginas copiadas ni una probabilidad de plagio. Para ayudar a interpretarlo, la pantalla también muestra fragmentos que superan los criterios de coincidencia establecidos.

### 10. Interpretación, límites y cierre — 12:25 a 14:15

La diferencia principal es esta: TF-IDF ayuda a representar el vocabulario; el coseno puede medir semejanza entre esos vectores; y Dice sobre secuencias mide cuánto comparten los textos en grupos ordenados de palabras. En la versión actual, el tercer cálculo produce el porcentaje principal.

Esta elección busca reducir falsas coincidencias por vocabulario común. Por ejemplo, cincuenta horas y cien horas son datos distintos. Un rótulo de tabla o el nombre de una metodología no debería bastar para concluir que dos propuestas son iguales. Por eso se combinan la selección de secciones, la limpieza y la comparación de secuencias.

El resultado sigue necesitando revisión humana. Un porcentaje alto puede justificar una revisión más detallada, pero una coincidencia también puede proceder de una expresión habitual o de contenido legítimamente compartido. Un porcentaje bajo tampoco demuestra que todas las ideas sean originales: una paráfrasis puede cambiar la redacción conservando una idea ajena.

El alcance actual se centra en texto extraíble de PDF. No analiza imágenes ni determina por sí solo si una cita es correcta. Un documento escaneado sin texto necesita un procesamiento adicional. Asimismo, la búsqueda externa depende de las fuentes disponibles; no equivale a comparar contra todos los documentos de Internet.

Durante la revisión previa a esta exposición se aprobaron setenta y ocho pruebas y se verificaron los flujos principales en el navegador. Esto aporta evidencia de funcionamiento, aunque no sustituye la evaluación académica de la precisión con más documentos y casos conocidos.

Para cerrar, Sello ITSa ofrece una forma organizada de registrar proyectos y una herramienta para revisar coincidencias con evidencia visible. Su valor está en apoyar al docente: facilita la búsqueda, conserva el contexto del trabajo y permite discutir resultados concretos. Muchas gracias.

## Anexo de estudio — no se lee durante el guion

### A. Las tres ideas que debes recordar

| Técnica | Pregunta que responde | Situación actual en Sello ITSa |
| --- | --- | --- |
| TF-IDF | ¿Qué peso tiene cada palabra en un documento, considerando la colección? | Apoya la explicación del vocabulario compartido en la comparación manual. |
| Similitud coseno | ¿Qué tan parecidos son los perfiles de términos de dos vectores? | Función implementada; no calcula el porcentaje principal del flujo actual. |
| Dice sobre secuencias de cinco palabras | ¿Cuántas secuencias ordenadas comparten los dos textos analizables? | Determina el porcentaje principal actual y la puntuación de los fragmentos. |

**Frase para memorizar:** «TF-IDF representa el vocabulario, coseno compara vectores y la versión actual mide la coincidencia principal con Dice sobre secuencias de cinco palabras».

### B. Fórmulas explicadas sin código

**TF(t, d) = apariciones de t en d / total de palabras procesadas de d**

En esta implementación, la tokenización para TF-IDF normaliza mayúsculas y tildes, elimina palabras vacías y utiliza palabras alfabéticas de más de dos caracteres. La lista de palabras vacías incluye términos como «de», «la» y «el». No se usa esa misma eliminación de palabras para construir las secuencias principales: allí se conservan, entre otros elementos, las negaciones y las cifras.

**IDF(t) = ln((N + 1) / (df(t) + 1)) + 1**

N es el número de documentos de la colección utilizada; df es el número de documentos distintos que contienen la palabra. «ln» significa logaritmo natural. Los ajustes de uno corresponden a la variante suavizada implementada. No todas las variantes de TF-IDF usan exactamente esta fórmula.

**Peso TF-IDF(t, d) = TF(t, d) × IDF(t)**

Ejemplo didáctico: N = 10, df = 2 y TF = 0,10. Entonces IDF = ln(11/3) + 1 ≈ 2,30 y el peso ≈ 0,23. Una palabra presente en los diez documentos tiene IDF = 1 en esta variante: su peso no se vuelve cero. Si cambia la colección, puede cambiar el peso de un término aunque el PDF siga igual.

**Coseno(A, B) = (A · B) / (‖A‖ × ‖B‖)**

El producto punto suma las multiplicaciones de los pesos correspondientes. Las magnitudes representan el tamaño de los vectores. Ejemplo simplificado con tres términos: A = (1, 1, 0) y B = (1, 0, 1). El producto punto es 1 y cada magnitud es raíz de 2; el coseno es 1/2 = 0,5, equivalente a 50%. Estos vectores son ilustrativos, no resultados de un proyecto real. Si un vector no tiene contenido, el servicio devuelve cero para evitar dividir entre cero.

**Dice(A, B) = 2C / (SA + SB) × 100**

SA y SB son las cantidades totales de secuencias de A y B. C suma, para cada secuencia, el menor número de repeticiones entre ambos textos. Así, si una secuencia aparece tres veces en A y una vez en B, aporta una coincidencia compartida, no tres. El cálculo es simétrico: comparar A con B da el mismo porcentaje que comparar B con A. Si no hay secuencias, el servicio devuelve cero; eso no debe interpretarse como prueba de originalidad.

### C. Preguntas probables del tribunal

**¿Entonces el sistema detecta plagio?**  
Detecta coincidencias textuales y ofrece apoyo a la revisión. Determinar plagio requiere examinar contexto, atribución, citas y normas académicas. El sistema no toma esa decisión por sí solo.

**¿Por qué no basta TF-IDF con coseno?**  
Porque una representación de palabras aisladas puede dar semejanza a trabajos que comparten temática o metodología. No conserva el orden de la redacción. Por eso, el porcentaje principal actual compara secuencias ordenadas y se complementa con filtros de contenido.

**¿Inventaste estos algoritmos?**  
No. Se utilizan fundamentos conocidos de recuperación de información y comparación textual. El trabajo del proyecto consiste en implementarlos e integrarlos al contexto académico, organizar los documentos y explicar los resultados.

**¿Por qué cinco palabras?**  
Es una decisión de diseño para exigir más contexto que una palabra aislada y permitir localizar coincidencias dentro de párrafos. No es un valor universal ni una precisión demostrada experimentalmente. Debe validarse con documentos y casos etiquetados por docentes.

**¿Por qué excluyes el marco teórico y el capítulo dos?**  
Para concentrar el análisis en el contenido seleccionado y reducir coincidencias por definiciones o estructura común. Es una decisión de alcance según la estructura adoptada. No significa que copiar teoría sin atribución sea aceptable: esa revisión queda fuera de este cálculo y corresponde al evaluador. Si el capítulo dos contiene la propuesta original en otra plantilla, la regla tendría que adaptarse.

**¿Qué significa un resultado de 60%?**  
Que el coeficiente calculado sobre las secuencias comparables vale 60%. No que el 60% de las páginas esté copiado ni que exista un 60% de probabilidad de plagio. Hay que revisar los fragmentos y el contenido excluido.

**¿Los colores definen si se aprueba o se rechaza?**  
No. Son una orientación visual. No equivalen a umbrales académicos validados ni reemplazan una norma institucional o el criterio del tribunal.

**¿Por qué a veces hay porcentaje y pocos fragmentos?**  
El porcentaje cuenta secuencias del conjunto analizable. Los fragmentos visibles deben superar requisitos adicionales: al menos tres secuencias compartidas, 45% de coincidencia y otros filtros de extensión y contenido. Por eso no se muestra cada coincidencia breve; ese 45% no es un umbral de plagio.

**¿Puede detectar una paráfrasis?**  
El método principal busca coincidencia textual, no equivalencia semántica completa. Una reformulación profunda, traducción o sustitución por sinónimos puede pasar desapercibida. Esa es una limitación y una posible línea de mejora.

**¿Compara con todo Internet?**  
No. La comparación interna usa documentos del repositorio. Las funciones externas consultan fuentes como Crossref y OpenAlex, con los contenidos disponibles; no garantizan acceso al texto completo de todas las publicaciones.

**¿Cómo sabes que funciona?**  
Se verificaron rutas, permisos, carga y extracción de PDF, generación de reportes, respaldos y flujos en navegador. Las 78 pruebas aprobadas muestran funcionamiento de los casos probados. Para afirmar una tasa de precisión académica haría falta un conjunto de casos evaluados por expertos y medir falsos positivos y falsos negativos.

**¿Cuál es la siguiente mejora más importante?**  
Evaluar el análisis con documentos reales autorizados y casos etiquetados por docentes. Después, ajustar exclusiones y tamaño de secuencias con evidencia. OCR y detección de paráfrasis pueden plantearse como ampliaciones, no como capacidades actuales.

### D. Guía para ensayar esta noche

1. Ensaya el guion con cronómetro. Busca terminar entre 13 y 14 minutos para tener margen.
2. Prepara dos proyectos que ya hayas revisado y una captura del resultado. No hagas una carga larga de documentos durante la exposición.
3. Practica sin mirar estas cuatro explicaciones: problema, TF-IDF, coseno y porcentaje actual con Dice.
4. No memorices todas las fórmulas. Recuerda qué representa cada componente y utiliza el ejemplo de veinte secuencias compartidas.
5. Si la demostración falla o se demora, muestra la captura y continúa con la explicación; conserva el tiempo del bloque de comparación.
6. Antes de empezar, comprueba Laragon, MySQL, acceso al sistema y que el PDF elegido abra. Las consultas externas requieren Internet.

### E. Fuentes para sustentar la explicación

Las descripciones del funcionamiento de Sello ITSa se contrastaron con sus servicios, controladores y vistas actuales. Las fuentes siguientes sustentan los antecedentes y conceptos; los ejemplos numéricos son didácticos.

**[1]** Spärck Jones, K. (1972). *A statistical interpretation of term specificity and its application in retrieval*. Journal of Documentation, 28(1), 11–21. Copia de la reedición del artículo: [texto del trabajo](https://www.staff.city.ac.uk/~sbrp622/idfpapers/ksj_orig.pdf).

**[2]** Salton, G., Wong, A. y Yang, C. S. (1975). *A vector space model for automatic indexing*. Communications of the ACM, 18(11), 613–620. [Referencia del artículo](https://doi.org/10.1145/361219.361220).

**[3]** Manning, C. D., Raghavan, P. y Schütze, H. (2008). *Introduction to Information Retrieval*: [TF-IDF weighting](https://nlp.stanford.edu/IR-book/html/htmledition/tf-idf-weighting-1.html).

**[4]** Los mismos autores: [Inverse document frequency](https://nlp.stanford.edu/IR-book/html/htmledition/inverse-document-frequency-1.html). La fórmula suavizada del sistema es una variante de la ponderación IDF, no una copia literal de todas las fórmulas de este capítulo.

**[5]** Los mismos autores: [Dot products y similitud coseno](https://nlp.stanford.edu/IR-book/html/htmledition/dot-products-1.html).
