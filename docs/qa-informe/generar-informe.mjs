import fs from 'node:fs';
import path from 'node:path';
const dir=path.resolve('docs/qa-informe');
const run=JSON.parse(fs.readFileSync(path.join(dir,'resultados.json'),'utf8'));
const meta=JSON.parse(fs.readFileSync(path.join(dir,'casos.json'),'utf8'));
const checks=JSON.parse(fs.readFileSync(path.join(dir,'control-datos.json'),'utf8'));
if(Object.values(checks).some(v=>!v))throw Error('Control de datos pendiente');
const esc=s=>String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
const img=(name,width=640,height=436)=>'<img src="'+esc(path.join(dir,name).replaceAll('\\','/'))+'" width="'+width+'" height="'+height+'">';
const rows=arr=>'<table>'+arr.map(([k,v])=>'<tr><th>'+esc(k)+'</th><td>'+v+'</td></tr>').join('')+'</table>';
const page=(title,body,kicker='SISTEMA DE AUTENTICIDAD')=>'<div class="page"><p class="kicker">'+kicker+'</p><h1>'+title+'</h1>'+body+'</div>';
const total=run.results.length, passed=run.results.filter(r=>r.status==='APROBADO').length;
let pages=[];
pages.push('<div class="cover"><p class="kicker">PROYECTO DE TITULACIÓN · CONTROL DE CALIDAD</p><h1>Sistema de<br>autenticidad</h1><h2>Pruebas funcionales<br>e implantación local</h2><p class="lead">18 casos de prueba documentados<br>con datos controlados y evidencias visuales.</p>'+img('evidencias/TC-002.png',640,436)+'<p class="caption">Pantalla real de acceso del sistema en el entorno de pruebas.</p><p class="covermeta">Responsable: Enrique<br>16 de octubre de 2026 · Documento 1.0<br>Referencia de código: 72b2451</p></div>');
pages.push(page('01 · Propósito y alcance',
'<p>Este informe verifica funciones del Sistema de autenticidad mediante casos reproducibles. Sigue el formato del ejemplo proporcionado: título, prioridad, precondición, descripción, datos, pasos y resultado esperado. Se incorporan resultado obtenido, estado y evidencia para distinguir lo diseñado de lo ejecutado.</p>'+
'<p>Se documentan <b>18 casos</b>, superando el mínimo de 10 solicitado. El alcance comprende acceso, usuarios, permisos, estudiantes, carga de PDF, comparación, reportes, respaldos y adaptación móvil.</p>'+
rows([
['Versión revisada','72b2451 · Mejora comparación de proyectos, respaldos y navegación.'],
['Tecnologías observadas','PHP 8.3.30; Laravel; Blade; CSS personalizado; Bootstrap; JavaScript; Vite; Smalot PDFParser y DomPDF.'],
['Base de la instalación','MySQL, confirmado en la configuración local.'],
['Base de esta ejecución','SQLite temporal, con cuentas y documentos ficticios. No demuestra por sí sola compatibilidad completa con MySQL.'],
['Navegador','Microsoft Edge automatizado; escritorio 1320 × 900 y móvil 390 × 844.'],
['Dirección de pruebas','http://127.0.0.1:8765 · servidor exclusivo del informe.'],
['Resultado registrado','<b>'+passed+' aprobados de '+total+' casos ejecutados.</b> Alcance limitado a estos escenarios.']
])+
'<h2>Criterio de aprobación</h2><p>Un caso se aprueba cuando las comprobaciones automatizadas confirman el resultado esperado. La captura documenta el estado visible; la verificación de campos, archivos o registros complementa la imagen. Las pruebas negativas se aprueban cuando el sistema rechaza correctamente una entrada inválida.</p>'+
'<h2>Tratamiento de las evidencias</h2><p>Las capturas proceden de la aplicación real con datos sintéticos. Para usar la denominación solicitada, el rótulo de marca se sustituyó visualmente por “Sistema de autenticidad” solo en el navegador de pruebas. Los mensajes de validación y resultados se conservaron.</p>'+
'<p>Los formularios se operaron mediante automatización. TC-014 envía una combinación incompatible directamente al servidor. Los selectores de autocompletado no se consideran exhaustivamente probados. No se editaron los archivos funcionales del proyecto.</p>'));
pages.push(page('02 · Preparación de datos',
'<p>El conjunto de datos pertenece al entorno de ensayo. Los correos de example.com y la contraseña siguiente son ficticios y no corresponden a cuentas de producción.</p>'+
rows([
['Administrador','Administrador de prueba · admin.qa@example.com · activo.'],
['Gestor','Gestor de prueba · gestor.qa@example.com · activo.'],
['Usuario','Usuario de prueba · usuario.qa@example.com · activo.'],
['Cuenta inactiva','inactivo.qa@example.com · inactiva.'],
['Contraseña de ensayo','Prueba-QA2026!'],
['Carrera','Sistemas Informáticos · código SIS.'],
['Estudiante / tutor','Ana Pérez (prueba), QA-001; Carlos Flores (prueba), QA-D01.'],
['Proyecto A / B','Registro académico A, id 1; Registro académico B, id 2. Ambos proyecto_grado y con contenido idéntico.'],
['Proyectos negativos','Documento sin secciones, id 3; Proyecto de otra modalidad, id 4, trabajo_dirigido_externo.'],
['Archivo de carga','carga-prueba.pdf, generado con texto extraíble y encabezados INTRODUCCIÓN y CONCLUSIONES.']
])+
'<h2>Texto comparable de A y B</h2><div class="note"><b>INTRODUCCIÓN</b><p>El sistema registra documentos académicos y organiza los proyectos de titulación mediante una plataforma institucional. Los usuarios autorizados consultan resultados y verifican coincidencias textuales para apoyar la evaluación de trabajos.</p><b>CONCLUSIONES</b><p>La solución permite conservar documentos y obtener reportes verificables para facilitar el seguimiento académico.</p></div>'+
'<h2>Reglas verificadas en el código</h2><p>Las cuentas se crean desde administración; no hay registro público. Los roles actuales son administrador, gestor y usuario. Proyectos admite PDF hasta 30 720 KB. La comparación manual exige la misma modalidad y contenido analizable.</p>'+
'<p>El porcentaje visible compara secuencias de cinco palabras; TF-IDF aporta vocabulario compartido como información secundaria. La similitud no determina por sí sola la existencia de plagio.</p>'));
pages.push(page('03 · Matriz de casos',
'<table class="matrix"><tr><th>ID</th><th>Caso funcional</th><th>Prioridad</th><th>Resultado</th></tr>'+
run.results.map((r,i)=>'<tr><td>'+r.code+'</td><td>'+esc(r.title)+'</td><td>'+meta[i][1]+'</td><td class="'+(r.status==='APROBADO'?'pass':'fail')+'">'+r.status+'</td></tr>').join('')+'</table>'+
'<p class="caption">Cada caso dispone de ficha y evidencia numeradas. Prioridades: crítica, alta o media, de acuerdo con el impacto funcional.</p>'+
'<h2>Lectura de resultados</h2><p>Los errores de validación que aparecen en inglés se transcriben literalmente. El rechazo funcional puede ser correcto aunque la localización al español quede como mejora de usabilidad.</p>'));
run.results.forEach((r,i)=>{
 const [module,priority,pre,data,description,steps,expected,route]=meta[i];
 let obtained=r.detail;
 if(r.code==='TC-012')obtained='Proyecto guardado como pendiente_analisis; PDF existente y texto extraído confirmado en la base temporal.';
 if(r.code==='TC-013')obtained='100% de similitud y tres tarjetas de coincidencias verificadas. Detalle adicional en el anexo.';
 if(r.code==='TC-011')obtained+=' Control de datos: user_id nulo; no se creó una cuenta.';
 const table=rows([
 ['Título',esc(r.title)+' · <b>'+r.code+'</b>'],
 ['Módulo / prioridad',esc(module)+' · '+esc(priority)],
 ['Precondición',esc(pre)],
 ['Descripción y datos',esc(description)+'<br><span class="muted">'+esc(data)+'</span>'],
 ['Pasos',steps.map((s,n)=>(n+1)+'. '+esc(s)).join('<br>')],
 ['Resultado esperado',esc(expected)],
 ['Resultado obtenido',esc(obtained)],
 ['Estado','<b class="'+(r.status==='APROBADO'?'pass':'fail')+'">'+r.status+'</b> · Ejecución automatizada · 16/10/2026']
 ]);
 pages.push(page(r.code+' · '+esc(r.title),table+
 '<p class="evidence-label">EVIDENCIA '+String(i+1).padStart(2,'0')+' · '+esc(route)+'</p>'+
 '<p class="picture">'+img(r.evidence,r.code==='TC-018'?190:640,r.code==='TC-018'?411:436)+'</p>'+
 '<p class="caption">'+(r.code==='TC-004'?'El bloqueo se verificó con la propiedad de validez del campo; el globo nativo puede no aparecer en la captura.':r.code==='TC-012'?'El control de datos confirma archivo y texto extraído; esta imagen muestra su registro.':r.code==='TC-016'?'La imagen acredita el registro; la respuesta descargada se comprobó como PDF.':'Captura real del entorno controlado con datos ficticios.')+'</p>','CASOS DE PRUEBA · '+module.toUpperCase()));
});
pages.push(page('04 · Evidencia de la comparación',
'<p>TC-013 comparó los proyectos A y B con contenido idéntico. La interfaz mostró 100% y tres fragmentos coincidentes. Esta imagen complementa el resultado global.</p>'+
img('evidencias/TC-013-detalle.png',640,436)+
'<p class="caption">Detalle real de las tarjetas de coincidencia para los documentos de ensayo.</p>'+
'<h2>Interpretación responsable</h2><p>La igualdad de los textos es conocida de antemano, por eso se espera 100%. Este caso valida ese comportamiento específico y no mide precisión sobre documentos reales, paráfrasis o textos escaneados.</p>'+
'<p>El primer intento del guion buscaba una etiqueta con mayúsculas/minúsculas específicas. Se corrigió el selector de la prueba y se comprobó la existencia de tarjetas HTML. No fue necesario cambiar el sistema.</p>'));
pages.push(page('05 · Implantación local',
'<p>La implantación prepara una computadora para ejecutar el código, conectar MySQL y conservar los documentos. Esta sección describe el procedimiento; no registra una reinstalación de la base principal durante este informe.</p>'+
rows([
 ['Entorno','Windows y Laragon; PHP 8.3 compatible con composer.json; Composer; Node.js y npm compatibles con package.json.'],
 ['Servidor y base','Servidor web y MySQL activos. La raíz web debe apuntar a public.'],
 ['Código','Usar la versión acordada; conservar composer.lock y package-lock.json.'],
 ['Configuración','Preparar .env con DB_CONNECTION=mysql, conexión local correcta y APP_URL. En un traslado, conservar la APP_KEY existente.'],
 ['Documentos','Copiar storage/app/public conservando rutas y recrear public/storage si falta.'],
 ['Dependencias','composer install<br>npm ci'],
 ['Base de datos','Respaldar antes de migrar. En una base vacía, ejecutar migraciones. Para trasladar registros, importar una copia MySQL compatible y revisar migraciones pendientes.'],
 ['Recursos y caché','npm run build<br>php artisan optimize:clear'],
 ['Verificación','Comprobar manifest.json, inicio de sesión, permisos, carga y apertura de PDF, comparación, reportes y respaldo.']
])+
'<h2>Flujo de funcionamiento</h2><table class="flow"><tr><td>Navegador<br><b>Formularios y resultados</b></td><td>Laravel / PHP<br><b>Validación y análisis</b></td><td>MySQL + archivos<br><b>Registros y PDF</b></td></tr></table>'+
'<p>Vite genera los recursos de la interfaz. MySQL conserva registros y relaciones; los PDF se almacenan como archivos. Crossref y OpenAlex requieren Internet para las consultas externas.</p>'));
pages.push(page('06 · Traslado y aceptación',
'<h2>Traslado a otra computadora</h2><p>El código se obtiene del repositorio. MySQL se traslada mediante una exportación e importación compatibles: copiar database.sqlite no traslada MySQL. Los PDF se copian por separado conservando sus rutas. La configuración del destino debe adaptarse sin divulgar credenciales.</p>'+
'<h2>Respaldo y recuperación</h2><p>El respaldo del sistema exporta registros de nueve tablas del negocio. Requiere un destino con el mismo esquema. Incluye instrucciones que reemplazan datos; su restauración debe ensayarse primero en una base separada.</p>'+
rows([
 ['Copia completa','Exportación MySQL, storage/app/public, configuración protegida y referencia del commit.'],
 ['Antes del cambio','Detener escrituras, comprobar las copias y registrar conteos por tabla y archivos.'],
 ['Después del traslado','Comprobar relaciones, permisos, apertura documental, resultados y descargas.'],
 ['Aceptación','Casos críticos aprobados en el destino y ausencia de incidencias que impidan el flujo de trabajo.'],
 ['Reversión','Recuperar conjuntamente código, base y documentos anteriores desde respaldos verificados.']
])+
'<h2>Comandos de comprobación</h2><pre>php artisan migrate:status\nphp artisan route:list --except-vendor\nnpm run build\nphp artisan storage:link</pre>'+
'<p>El último comando solo se necesita si falta el enlace de almacenamiento. No regenerar la clave de una instalación trasladada ni vaciar la base para resolver errores de configuración.</p>'+
'<h2>Pendientes institucionales</h2><p>Verificación específica sobre MySQL; restauración de respaldos en otra base; SMTP y entrega real de correos; consultas externas en vivo; concurrencia, carga y recuperación ante cortes. Estos puntos no se presentan como aprobados aquí.</p>'));
pages.push(page('07 · Conclusiones y trazabilidad',
'<p>Se ejecutaron '+total+' casos funcionales sobre la versión 72b2451 y se registraron '+passed+' aprobados. Las evidencias cubren aceptación de datos válidos, rechazo de entradas inválidas, permisos, carga documental y operaciones de salida.</p>'+
'<h2>Observaciones</h2><p><b>Idioma:</b> algunos errores de credenciales y formularios se muestran en inglés. Conviene unificar las traducciones para usuarios hispanohablantes.</p>'+
'<p><b>Motor de datos:</b> se utilizó SQLite aislado. La configuración de uso es MySQL; la compatibilidad y el respaldo en ese motor requieren comprobación propia.</p>'+
'<p><b>Documentos:</b> los archivos de prueba tienen texto extraíble. No se evaluó OCR ni PDF escaneados. El respaldo SQL no incluye los archivos binarios.</p>'+
'<p><b>Alcance:</b> estos casos no sustituyen una auditoría de seguridad, pruebas de carga ni una evaluación estadística del algoritmo.</p>'+
'<h2>Fuentes internas</h2><p>Rutas web y autenticación; controladores de usuarios, estudiantes, análisis, proyectos y respaldos; servicios de similitud, secciones y extracción PDF; vistas de formularios; composer.json, package.json, phpunit.xml y pruebas existentes.</p>'+
'<h2>Material entregado</h2>'+rows([
 ['Editable','Sistema_de_autenticidad_Pruebas_funcionales.docx'],
 ['Lectura','Sistema_de_autenticidad_Pruebas_funcionales.pdf'],
 ['Evidencias','18 capturas numeradas y una captura complementaria de comparación.'],
 ['Registro','resultados.json y control-datos.json.'],
 ['Reproducibilidad','Guion de pruebas y preparación del entorno aislado en docs/qa-informe.']
])+
'<p class="caption">No se incluyen datos personales reales ni claves de la instalación. Las cifras de informes anteriores no se reutilizan como resultados de esta ejecución.</p>'));
const style=[
'@page { size:21cm 29.7cm; margin:1.45cm 1.6cm 1.5cm 1.6cm; }',
'body {font-family:Calibri,Arial,sans-serif;color:#20382f;font-size:10pt;line-height:1.16}',
'.page {page-break-before:always;} .cover {padding-top:18pt}',
'.kicker {font-size:9pt;color:#16805d;letter-spacing:1pt;font-weight:bold;margin:0 0 10pt}',
'h1 {font-size:22pt;line-height:1.1;color:#06452f;margin:0 0 12pt;}',
'.cover h1 {font-size:42pt;margin-top:18pt;margin-bottom:12pt}',
'.cover h2 {font-size:23pt;color:#386351;margin-bottom:14pt}',
'h2 {font-size:13pt;color:#126447;margin:13pt 0 6pt} p {margin:6pt 0}',
'.lead {font-size:13pt;color:#506b5e;line-height:1.3;margin-bottom:20pt}',
'.covermeta {font-size:11pt;border-top:2pt solid #a7d887;padding-top:12pt;margin-top:15pt}',
'table {border-collapse:collapse;width:100%;table-layout:fixed;margin:8pt 0}',
'th,td {border:0.6pt solid #d3e1d9;padding:5pt 7pt;text-align:left;vertical-align:top;font-size:9.5pt}',
'th {width:24%;background:#edf5ef;color:#14573e;font-weight:bold} td {background:#fff}',
'.pass {color:#087642;font-weight:bold}.fail {color:#a12929;font-weight:bold}',
'.muted,.caption {color:#64776c}.caption {font-size:8pt;margin:5pt 0}',
'.evidence-label {font-size:8pt;font-weight:bold;color:#14724e;margin:10pt 0 5pt}',
'.picture {text-align:center;margin:0}',
'.matrix td,.matrix th {font-size:9pt;padding:6pt}.matrix th {background:#06452f;color:#fff}',
'.matrix th:nth-child(1) {width:13%}.matrix th:nth-child(2) {width:50%}.matrix th:nth-child(3) {width:17%}.matrix th:nth-child(4) {width:20%}',
'.note {background:#edf5ef;border-left:3pt solid #20835a;padding:10pt}',
'.flow td {text-align:center;background:#edf5ef;padding:10pt 3pt}',
'pre {font-family:Consolas,monospace;font-size:10pt;background:#edf5ef;padding:12pt}',
'img {border:0;}'
].join('\n');
fs.writeFileSync(path.join(dir,'informe.html'),'\ufeff<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Sistema de autenticidad — Pruebas funcionales</title><style>'+style+'</style></head><body>'+pages.join('\n')+'</body></html>');
console.log('HTML generado: '+pages.length+' páginas previstas.');
