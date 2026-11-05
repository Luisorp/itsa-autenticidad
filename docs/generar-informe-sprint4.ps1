$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Drawing

$root = (Get-Location).Path
$assetDir = Join-Path $root 'docs\sprint-4'
$output = Join-Path $root 'docs\INFORME_SPRINT_4_REPORTES_RESPALDOS_FINAL.docx'
New-Item -ItemType Directory -Path $assetDir -Force | Out-Null
if (Test-Path -LiteralPath $output) { throw "Se conserva el documento existente y no se sobrescribe: $output" }

$useCase10 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="850" viewBox="0 0 1500 850">
<defs>
  <marker id="arr" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker>
  <style>text{font-family:Arial,sans-serif;fill:#111}.actor{fill:none;stroke:#111;stroke-width:3}.use{fill:#fff;stroke:#111;stroke-width:2.5}.assoc{fill:none;stroke:#111;stroke-width:2}.inc{fill:none;stroke:#111;stroke-width:2;stroke-dasharray:8 6;marker-end:url(#arr)}</style>
</defs>
<rect x="350" y="45" width="980" height="760" fill="#fff" stroke="#111" stroke-width="2.5"/>
<!-- Actores y permisos reales del sistema. -->
<g aria-label="Administrador"><circle class="actor" cx="65" cy="135" r="16"/><path class="actor" d="M65 151v48M40 168h50M65 199l-22 36M65 199l22 36"/><text x="65" y="263" text-anchor="middle" font-size="19">Administrador</text></g>
<g aria-label="Gestor"><circle class="actor" cx="65" cy="375" r="16"/><path class="actor" d="M65 391v48M40 408h50M65 439l-22 36M65 439l22 36"/><text x="65" y="503" text-anchor="middle" font-size="19">Gestor</text></g>
<g aria-label="Usuario"><circle class="actor" cx="65" cy="615" r="16"/><path class="actor" d="M65 631v48M40 648h50M65 679l-22 36M65 679l22 36"/><text x="65" y="743" text-anchor="middle" font-size="19">Usuario</text></g>
<ellipse class="use" cx="555" cy="270" rx="175" ry="58"/><text x="555" y="264" text-anchor="middle" font-size="24"><tspan x="555">Generar reporte</tspan><tspan x="555" dy="29">PDF</tspan></text>
<ellipse class="use" cx="555" cy="570" rx="175" ry="58"/><text x="555" y="564" text-anchor="middle" font-size="24"><tspan x="555">Consultar reportes</tspan><tspan x="555" dy="29">PDF</tspan></text>
<ellipse class="use" cx="1040" cy="145" rx="220" ry="48"/><text x="1040" y="139" text-anchor="middle" font-size="21"><tspan x="1040">Validar acceso y</tspan><tspan x="1040" dy="25">documento del proyecto</tspan></text>
<ellipse class="use" cx="1040" cy="270" rx="220" ry="48"/><text x="1040" y="264" text-anchor="middle" font-size="21"><tspan x="1040">Obtener resultados</tspan><tspan x="1040" dy="25">de comparación</tspan></text>
<ellipse class="use" cx="1040" cy="395" rx="220" ry="48"/><text x="1040" y="389" text-anchor="middle" font-size="21"><tspan x="1040">Crear y guardar</tspan><tspan x="1040" dy="25">el reporte PDF</tspan></text>
<ellipse class="use" cx="1040" cy="540" rx="220" ry="48"/><text x="1040" y="534" text-anchor="middle" font-size="21"><tspan x="1040">Mostrar historial</tspan><tspan x="1040" dy="25">según permisos</tspan></text>
<ellipse class="use" cx="1040" cy="680" rx="220" ry="48"/><text x="1040" y="674" text-anchor="middle" font-size="21"><tspan x="1040">Abrir reporte</tspan><tspan x="1040" dy="25">PDF disponible</tspan></text>
<path class="assoc" d="M90 168L380 250M90 180L380 530M90 408L380 290M90 420L380 550M90 648L380 610"/>
<path class="inc" d="M730 250L820 145"/><text x="758" y="191" font-size="18">«include»</text>
<path class="inc" d="M730 270H820"/><text x="747" y="258" font-size="18">«include»</text>
<path class="inc" d="M730 290L820 395"/><text x="758" y="352" font-size="18">«include»</text>
<path class="inc" d="M730 550L820 540"/><text x="747" y="532" font-size="18">«include»</text>
<path class="inc" d="M730 590L820 680"/><text x="758" y="647" font-size="18">«include»</text>
<rect x="790" y="748" width="490" height="40" rx="8" fill="#fff" stroke="#777"/>
<text x="1035" y="765" text-anchor="middle" font-size="16"><tspan x="1035">Administrador y Gestor: generan y consultan reportes.</tspan><tspan x="1035" dy="18">Usuario: solo consulta reportes de sus propios proyectos.</tspan></text>
</svg>
'@

$sequence10 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="1380" viewBox="0 0 1500 1380">
<defs><marker id="arr" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.box{fill:#fff;stroke:#111;stroke-width:2}.life{stroke:#666;stroke-width:1.5;stroke-dasharray:7 6}.msg{stroke:#111;stroke-width:2;marker-end:url(#arr)}.ret{stroke:#444;stroke-width:1.7;stroke-dasharray:7 5;marker-end:url(#arr)}.frame{fill:none;stroke:#111;stroke-width:2}</style></defs>
<g font-size="18"><rect class="box" x="20" y="25" width="190" height="58"/><text x="115" y="61" text-anchor="middle">Persona autorizada</text><rect class="box" x="310" y="25" width="190" height="58"/><text x="405" y="61" text-anchor="middle">Navegador</text><rect class="box" x="610" y="25" width="210" height="58"/><text x="715" y="61" text-anchor="middle">Laravel</text><rect class="box" x="930" y="25" width="190" height="58"/><text x="1025" y="61" text-anchor="middle">Base de datos</text><rect class="box" x="1230" y="25" width="240" height="58"/><text x="1350" y="61" text-anchor="middle">PDF y almacenamiento</text></g>
<path class="life" d="M115 83V1340M405 83V1340M715 83V1340M1025 83V1340M1350 83V1340"/>
<g font-size="16">
<path class="msg" d="M115 135H405"/><text x="260" y="125" text-anchor="middle">Solicitar reporte del proyecto</text>
<path class="msg" d="M405 195H715"/><text x="560" y="185" text-anchor="middle">GET /proyectos/{id}/reporte</text>
<path class="msg" d="M715 255H1025"/><text x="870" y="245" text-anchor="middle">Validar usuario, acceso, PDF y comparaciones</text>
<path class="ret" d="M1025 315H715"/><text x="870" y="305" text-anchor="middle">Datos del proyecto y resultados</text>
<path class="msg" d="M715 375H1350"/><text x="1032" y="365" text-anchor="middle">Generar PDF y guardarlo</text>
<path class="ret" d="M1350 435H715"/><text x="1032" y="425" text-anchor="middle">Archivo PDF y ruta</text>
<path class="msg" d="M715 495H1025"/><text x="870" y="485" text-anchor="middle">Registrar o actualizar reporte</text>
<path class="ret" d="M1025 555H715"/><text x="870" y="545" text-anchor="middle">Registro confirmado</text>
<path class="ret" d="M715 615H405"/><text x="560" y="605" text-anchor="middle">Responder con el PDF</text>
<path class="ret" d="M405 675H115"/><text x="260" y="665" text-anchor="middle">Descargar reporte</text>
<rect class="frame" x="285" y="715" width="1170" height="585"/><text x="300" y="743" font-size="17" font-weight="700">Consulta posterior del historial</text>
<path class="msg" d="M115 775H405"/><text x="260" y="765" text-anchor="middle">Abrir reportes</text>
<path class="msg" d="M405 825H715"/><text x="560" y="815" text-anchor="middle">GET /reportes</text>
<path class="msg" d="M715 875H1025"/><text x="870" y="865" text-anchor="middle">Filtrar por permisos y consultar registros</text>
<path class="ret" d="M1025 925H715"/><text x="870" y="915" text-anchor="middle">Lista disponible</text>
<path class="ret" d="M715 975H405"/><text x="560" y="965" text-anchor="middle">Mostrar historial</text>
<path class="msg" d="M115 1025H405"/><text x="260" y="1015" text-anchor="middle">Seleccionar Ver PDF</text>
<path class="msg" d="M405 1075H715"/><text x="560" y="1065" text-anchor="middle">GET /reportes/{id}/archivo</text>
<path class="msg" d="M715 1120H1350"/><text x="1032" y="1110" text-anchor="middle">Validar acceso y solicitar archivo existente</text>
<path class="ret" d="M1350 1170H715"/><text x="1032" y="1160" text-anchor="middle">Devolver contenido PDF</text>
<path class="ret" d="M715 1220H405"/><text x="560" y="1210" text-anchor="middle">Transmitir archivo PDF</text>
<path class="ret" d="M405 1270H115"/><text x="260" y="1260" text-anchor="middle">Abrir reporte</text>
</g></svg>
'@

$activity10 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="1000" viewBox="0 0 1500 1000">
<defs><marker id="arr" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.n{fill:#fff;stroke:#111;stroke-width:2}.l{fill:none;stroke:#111;stroke-width:2;marker-end:url(#arr)}.start{fill:#111}.end{fill:#fff;stroke:#111;stroke-width:3}</style></defs>
<circle class="start" cx="750" cy="35" r="13"/><path class="l" d="M750 48V75"/><rect class="n" x="545" y="75" width="410" height="62" rx="16"/><text x="750" y="114" text-anchor="middle" font-size="19">Abrir proyecto o historial de reportes</text><path class="l" d="M750 137V170"/>
<path class="n" d="M750 170L850 225L750 280L650 225Z"/><text x="750" y="220" text-anchor="middle" font-size="17"><tspan x="750">¿Generar o</tspan><tspan x="750" dy="21">consultar?</tspan></text>
<path class="l" d="M650 225H360V285"/><text x="500" y="215" font-size="16">Generar</text><path class="l" d="M850 225H1140V285"/><text x="1000" y="215" font-size="16">Consultar</text>
<rect class="n" x="150" y="285" width="420" height="65" rx="16"/><text x="360" y="324" text-anchor="middle" font-size="18">Validar acceso y existencia del PDF</text><path class="l" d="M360 350V390"/>
<path class="n" d="M360 390L445 440L360 490L275 440Z"/><text x="360" y="435" text-anchor="middle" font-size="16"><tspan x="360">¿Acceso válido</tspan><tspan x="360" dy="19">y PDF disponible?</tspan></text><path class="l" d="M445 440H520V525"/><text x="460" y="430" font-size="15">Sí</text><path class="l" d="M275 440H185V525"/><text x="220" y="430" font-size="15">No</text>
<rect class="n" x="370" y="525" width="300" height="65" rx="16"/><text x="520" y="564" text-anchor="middle" font-size="18">Generar y guardar reporte PDF</text><path class="l" d="M520 590V635"/><rect class="n" x="370" y="635" width="300" height="60" rx="16"/><text x="520" y="672" text-anchor="middle" font-size="18">Entregar PDF al usuario</text>
<rect class="n" x="35" y="525" width="300" height="60" rx="16"/><text x="185" y="549" text-anchor="middle" font-size="17"><tspan x="185">Mostrar acceso o</tspan><tspan x="185" dy="20">documento no disponible</tspan></text>
<rect class="n" x="930" y="285" width="420" height="65" rx="16"/><text x="1140" y="324" text-anchor="middle" font-size="18">Listar reportes permitidos</text><path class="l" d="M1140 350V390"/><rect class="n" x="930" y="390" width="420" height="65" rx="16"/><text x="1140" y="429" text-anchor="middle" font-size="18">Seleccionar reporte PDF</text><path class="l" d="M1140 455V495"/>
<path class="n" d="M1140 495L1225 545L1140 595L1055 545Z"/><text x="1140" y="540" text-anchor="middle" font-size="16"><tspan x="1140">¿Existe y tiene</tspan><tspan x="1140" dy="19">permiso?</tspan></text><path class="l" d="M1225 545H1320V635"/><text x="1260" y="535" font-size="15">Sí</text><path class="l" d="M1055 545H960V635"/><text x="1000" y="535" font-size="15">No</text>
<rect class="n" x="1180" y="635" width="280" height="60" rx="16"/><text x="1320" y="672" text-anchor="middle" font-size="18">Abrir el archivo PDF</text><rect class="n" x="820" y="635" width="280" height="60" rx="16"/><text x="960" y="672" text-anchor="middle" font-size="18">Informar archivo no disponible</text>
<path class="l" d="M520 695V790H390"/><path class="l" d="M185 585V790H330"/><path class="n" d="M360 760L390 790L360 820L330 790Z"/><path class="l" d="M360 820V885"/><circle class="end" cx="360" cy="915" r="15"/><circle class="start" cx="360" cy="915" r="7"/>
<path class="l" d="M1320 695V790H1170"/><path class="l" d="M960 695V790H1110"/><path class="n" d="M1140 760L1170 790L1140 820L1110 790Z"/><path class="l" d="M1140 820V885"/><circle class="end" cx="1140" cy="915" r="15"/><circle class="start" cx="1140" cy="915" r="7"/>
</svg>
'@

$useCase11 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="850" viewBox="0 0 1400 850">
<defs><marker id="arr" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.actor{fill:none;stroke:#111;stroke-width:3}.use{fill:#fff;stroke:#111;stroke-width:2.5}.assoc{stroke:#111;stroke-width:2}.inc{fill:none;stroke:#111;stroke-width:2;stroke-dasharray:8 6;marker-end:url(#arr)}</style></defs>
<rect x="245" y="45" width="1085" height="760" fill="#fff" stroke="#111" stroke-width="2.5"/>
<circle class="actor" cx="105" cy="420" r="21"/><path class="actor" d="M105 441v74M65 467h80M105 515l-32 54M105 515l32 54"/><text x="105" y="600" text-anchor="middle" font-size="22">Administrador</text>
<ellipse class="use" cx="480" cy="230" rx="180" ry="55"/><text x="480" y="224" text-anchor="middle" font-size="23"><tspan x="480">Generar respaldo</tspan><tspan x="480" dy="28">SQL</tspan></text>
<ellipse class="use" cx="480" cy="460" rx="180" ry="55"/><text x="480" y="454" text-anchor="middle" font-size="23"><tspan x="480">Consultar respaldos</tspan><tspan x="480" dy="28">existentes</tspan></text>
<ellipse class="use" cx="480" cy="690" rx="180" ry="55"/><text x="480" y="684" text-anchor="middle" font-size="23"><tspan x="480">Descargar respaldo</tspan><tspan x="480" dy="28">SQL</tspan></text>
<ellipse class="use" cx="1010" cy="130" rx="230" ry="48"/><text x="1010" y="124" text-anchor="middle" font-size="21"><tspan x="1010">Validar permisos</tspan><tspan x="1010" dy="25">de administrador</tspan></text>
<ellipse class="use" cx="1010" cy="270" rx="230" ry="48"/><text x="1010" y="264" text-anchor="middle" font-size="21"><tspan x="1010">Exportar datos de nueve tablas</tspan><tspan x="1010" dy="25">y generar archivo SQL</tspan></text>
<ellipse class="use" cx="1010" cy="400" rx="230" ry="48"/><text x="1010" y="394" text-anchor="middle" font-size="21"><tspan x="1010">Guardar archivo de respaldo</tspan><tspan x="1010" dy="25">en almacenamiento privado</tspan></text>
<ellipse class="use" cx="1010" cy="540" rx="230" ry="48"/><text x="1010" y="534" text-anchor="middle" font-size="21"><tspan x="1010">Listar respaldos</tspan><tspan x="1010" dy="25">disponibles</tspan></text>
<ellipse class="use" cx="1010" cy="690" rx="230" ry="48"/><text x="1010" y="684" text-anchor="middle" font-size="21"><tspan x="1010">Comprobar existencia</tspan><tspan x="1010" dy="25">y transmitir archivo SQL</tspan></text>
<path class="assoc" d="M145 467L300 230M145 467L300 460M145 467L300 690"/>
<path class="inc" d="M660 215L780 130"/><text x="685" y="165" font-size="17">«include»</text><path class="inc" d="M660 230H780"/><text x="690" y="220" font-size="17">«include»</text><path class="inc" d="M660 245L780 400"/><text x="690" y="335" font-size="17">«include»</text>
<path class="inc" d="M660 460L780 540"/><text x="690" y="500" font-size="17">«include»</text><path class="inc" d="M660 690H780"/><text x="690" y="680" font-size="17">«include»</text>
</svg>
'@

$sequence11 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="1120" viewBox="0 0 1500 1120">
<defs><marker id="arr" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.box{fill:#fff;stroke:#111;stroke-width:2}.life{stroke:#666;stroke-width:1.5;stroke-dasharray:7 6}.msg{stroke:#111;stroke-width:2;marker-end:url(#arr)}.ret{stroke:#444;stroke-width:1.7;stroke-dasharray:7 5;marker-end:url(#arr)}</style></defs>
<g font-size="18"><rect class="box" x="20" y="25" width="190" height="58"/><text x="115" y="61" text-anchor="middle">Administrador</text><rect class="box" x="310" y="25" width="190" height="58"/><text x="405" y="61" text-anchor="middle">Navegador</text><rect class="box" x="610" y="25" width="210" height="58"/><text x="715" y="61" text-anchor="middle">Laravel</text><rect class="box" x="930" y="25" width="190" height="58"/><text x="1025" y="61" text-anchor="middle">Base de datos</text><rect class="box" x="1230" y="25" width="240" height="58"/><text x="1350" y="61" text-anchor="middle">Almacenamiento privado</text></g>
<path class="life" d="M115 83V1090M405 83V1090M715 83V1090M1025 83V1090M1350 83V1090"/>
<g font-size="16">
<path class="msg" d="M115 135H405"/><text x="260" y="125" text-anchor="middle">Abrir respaldos</text>
<path class="msg" d="M405 195H715"/><text x="560" y="185" text-anchor="middle">GET /respaldos</text>
<path class="msg" d="M715 255H1350"/><text x="1032" y="245" text-anchor="middle">Listar archivos existentes</text>
<path class="ret" d="M1350 315H715"/><text x="1032" y="305" text-anchor="middle">Nombres, tamaño y fecha</text>
<path class="ret" d="M715 375H405"/><text x="560" y="365" text-anchor="middle">Mostrar listado</text>
<path class="msg" d="M115 435H405"/><text x="260" y="425" text-anchor="middle">Solicitar nuevo respaldo</text>
<path class="msg" d="M405 495H715"/><text x="560" y="485" text-anchor="middle">POST /respaldos/generar</text>
<path class="msg" d="M715 555H1025"/><text x="870" y="545" text-anchor="middle">Consultar las nueve tablas de datos</text>
<path class="ret" d="M1025 615H715"/><text x="870" y="605" text-anchor="middle">Registros y relaciones</text>
<path class="msg" d="M715 675H1350"/><text x="1032" y="665" text-anchor="middle">Construir y guardar archivo .sql</text>
<path class="ret" d="M1350 735H715"/><text x="1032" y="725" text-anchor="middle">Archivo guardado</text>
<path class="ret" d="M715 795H405"/><text x="560" y="785" text-anchor="middle">Redirigir y actualizar listado</text>
<path class="msg" d="M115 855H405"/><text x="260" y="845" text-anchor="middle">Elegir Descargar</text>
<path class="msg" d="M405 915H715"/><text x="560" y="905" text-anchor="middle">GET /respaldos/{archivo}/descargar</text>
<path class="msg" d="M715 975H1350"/><text x="1032" y="965" text-anchor="middle">Validar existencia y solicitar archivo .sql</text>
<path class="ret" d="M1350 1010H715"/><text x="1032" y="1000" text-anchor="middle">Devolver contenido del respaldo</text>
<path class="ret" d="M715 1045H405"/><text x="560" y="1035" text-anchor="middle">Transmitir archivo descargable</text>
<path class="ret" d="M405 1080H115"/><text x="260" y="1070" text-anchor="middle">Guardar copia SQL</text>
</g></svg>
'@

$activity11 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1500" height="1000" viewBox="0 0 1500 1000">
<defs><marker id="arr" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.n{fill:#fff;stroke:#111;stroke-width:2}.l{fill:none;stroke:#111;stroke-width:2;marker-end:url(#arr)}.start{fill:#111}.end{fill:#fff;stroke:#111;stroke-width:3}</style></defs>
<circle class="start" cx="750" cy="35" r="13"/><path class="l" d="M750 48V75"/><rect class="n" x="545" y="75" width="410" height="62" rx="16"/><text x="750" y="114" text-anchor="middle" font-size="19">Ingresar a la sección de respaldos</text><path class="l" d="M750 137V170"/><path class="n" d="M750 170L850 225L750 280L650 225Z"/><text x="750" y="220" text-anchor="middle" font-size="17"><tspan x="750">¿Es</tspan><tspan x="750" dy="21">administrador?</tspan></text>
<path class="l" d="M650 225H280V300"/><text x="465" y="215" font-size="16">No</text><path class="l" d="M850 225H1120V300"/><text x="990" y="215" font-size="16">Sí</text>
<rect class="n" x="110" y="300" width="340" height="65" rx="16"/><text x="280" y="339" text-anchor="middle" font-size="18">Rechazar acceso</text><path class="l" d="M280 365V925"/><circle class="end" cx="280" cy="945" r="13"/><circle class="start" cx="280" cy="945" r="6"/>
<rect class="n" x="900" y="300" width="440" height="65" rx="16"/><text x="1120" y="339" text-anchor="middle" font-size="18">Mostrar respaldos existentes</text><path class="l" d="M1120 365V405"/><path class="n" d="M1120 405L1210 455L1120 505L1030 455Z"/><text x="1120" y="450" text-anchor="middle" font-size="17"><tspan x="1120">¿Generar o</tspan><tspan x="1120" dy="20">descargar?</tspan></text>
<path class="l" d="M1030 455H580V530"/><text x="830" y="445" font-size="16">Generar</text><path class="l" d="M1210 455H1350V530"/><text x="1260" y="445" font-size="16">Descargar</text>
<rect class="n" x="360" y="530" width="440" height="65" rx="16"/><text x="580" y="569" text-anchor="middle" font-size="18">Leer las nueve tablas del sistema</text><path class="l" d="M580 595V630"/><rect class="n" x="360" y="630" width="440" height="65" rx="16"/><text x="580" y="669" text-anchor="middle" font-size="18">Crear archivo de respaldo SQL</text><path class="l" d="M580 695V730"/><rect class="n" x="360" y="730" width="440" height="65" rx="16"/><text x="580" y="769" text-anchor="middle" font-size="18">Guardar y actualizar el listado</text><path class="l" d="M580 795V925"/><circle class="end" cx="580" cy="945" r="13"/><circle class="start" cx="580" cy="945" r="6"/>
<rect class="n" x="1200" y="530" width="300" height="65" rx="16"/><text x="1350" y="569" text-anchor="middle" font-size="18">Seleccionar archivo</text><path class="l" d="M1350 595V635"/><path class="n" d="M1350 635L1420 680L1350 725L1280 680Z"/><text x="1350" y="685" text-anchor="middle" font-size="16">¿Existe?</text><path class="l" d="M1420 680H1460V740H1350V760"/><text x="1435" y="670" font-size="15">Sí</text><path class="l" d="M1280 680H980V760"/><text x="1160" y="670" font-size="15">No</text>
<rect class="n" x="1200" y="760" width="300" height="65" rx="16"/><text x="1350" y="799" text-anchor="middle" font-size="18">Descargar archivo SQL</text><path class="l" d="M1350 825V925"/><circle class="end" cx="1350" cy="945" r="13"/><circle class="start" cx="1350" cy="945" r="6"/>
<rect class="n" x="830" y="760" width="300" height="65" rx="16"/><text x="980" y="799" text-anchor="middle" font-size="18">Informar archivo inexistente</text><path class="l" d="M980 825V925"/><circle class="end" cx="980" cy="945" r="13"/><circle class="start" cx="980" cy="945" r="6"/>
</svg>
'@

$mockup10 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1100" height="720" viewBox="0 0 1100 720">
<style>text{font-family:Arial,sans-serif;fill:#111}.line{fill:#fff;stroke:#111;stroke-width:2}.thin{stroke:#111;stroke-width:1.5}.button{fill:#fff;stroke:#111;stroke-width:2}</style>
<rect x="25" y="25" width="1050" height="670" class="line"/><text x="65" y="85" font-size="30" font-weight="700">Proyecto de titulación</text><line x1="60" y1="110" x2="1040" y2="110" class="thin"/>
<text x="65" y="160" font-size="20">Análisis de similitud completado</text><rect x="700" y="130" width="335" height="54" rx="4" class="button"/><text x="867" y="165" text-anchor="middle" font-size="20">Generar reporte PDF</text>
<text x="65" y="245" font-size="22" font-weight="700">Reportes generados</text>
<rect x="60" y="270" width="980" height="300" class="line"/><rect x="60" y="270" width="980" height="60" fill="#eee" stroke="#111" stroke-width="1.5"/><line x1="430" y1="270" x2="430" y2="570" class="thin"/><line x1="620" y1="270" x2="620" y2="570" class="thin"/><line x1="800" y1="270" x2="800" y2="570" class="thin"/><text x="80" y="308" font-size="18" font-weight="700">Proyecto</text><text x="450" y="308" font-size="18" font-weight="700">Generado por</text><text x="640" y="308" font-size="18" font-weight="700">Fecha</text><text x="820" y="308" font-size="18" font-weight="700">Archivo</text>
<line x1="60" y1="410" x2="1040" y2="410" class="thin"/><line x1="60" y1="490" x2="1040" y2="490" class="thin"/><text x="80" y="375" font-size="17">Proyecto de titulación</text><text x="450" y="375" font-size="17">Administrador</text><text x="640" y="375" font-size="17">dd/mm/aaaa</text><text x="820" y="375" font-size="17" text-decoration="underline">Ver PDF</text><text x="80" y="455" font-size="17">Proyecto académico</text><text x="450" y="455" font-size="17">Gestor</text><text x="640" y="455" font-size="17">dd/mm/aaaa</text><text x="820" y="455" font-size="17" text-decoration="underline">Ver PDF</text>
<text x="65" y="630" font-size="16">Página 1</text><text x="985" y="630" font-size="16">Siguiente</text>
</svg>
'@

$mockup11 = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1100" height="720" viewBox="0 0 1100 720">
<style>text{font-family:Arial,sans-serif;fill:#111}.line{fill:#fff;stroke:#111;stroke-width:2}.thin{stroke:#111;stroke-width:1.5}.button{fill:#fff;stroke:#111;stroke-width:2}</style>
<rect x="25" y="25" width="1050" height="670" class="line"/><text x="65" y="85" font-size="28" font-weight="700">Respaldos de la base de datos</text><line x1="60" y1="110" x2="1040" y2="110" class="thin"/>
<rect x="60" y="145" width="310" height="54" rx="4" class="button"/><text x="215" y="180" text-anchor="middle" font-size="20">Generar nuevo respaldo</text>
<rect x="60" y="245" width="980" height="330" class="line"/><rect x="60" y="245" width="980" height="60" fill="#eee" stroke="#111" stroke-width="1.5"/><line x1="475" y1="245" x2="475" y2="575" class="thin"/><line x1="650" y1="245" x2="650" y2="575" class="thin"/><line x1="820" y1="245" x2="820" y2="575" class="thin"/><text x="80" y="283" font-size="18" font-weight="700">Archivo</text><text x="495" y="283" font-size="18" font-weight="700">Tamaño (KB)</text><text x="670" y="283" font-size="18" font-weight="700">Fecha</text><text x="840" y="283" font-size="18" font-weight="700">Acción</text>
<line x1="60" y1="395" x2="1040" y2="395" class="thin"/><line x1="60" y1="485" x2="1040" y2="485" class="thin"/><text x="80" y="355" font-size="16">respaldo_aaaa-mm-dd.sql</text><text x="495" y="355" font-size="16">12.4</text><text x="670" y="355" font-size="16">dd/mm/aaaa</text><text x="840" y="355" font-size="16" text-decoration="underline">Descargar</text><text x="80" y="445" font-size="16">respaldo_aaaa-mm-dd.sql</text><text x="495" y="445" font-size="16">10.8</text><text x="670" y="445" font-size="16">dd/mm/aaaa</text><text x="840" y="445" font-size="16" text-decoration="underline">Descargar</text>
<text x="65" y="630" font-size="16">Página 1</text><text x="985" y="630" font-size="16">Siguiente</text>
</svg>
'@

$figures = @(
    @{ name='hu10-caso-uso.svg'; svg=$useCase10; caption='Figura 48. Caso de uso para generar y consultar reportes PDF. Fuente: elaboración propia.' },
    @{ name='hu10-secuencia.svg'; svg=$sequence10; caption='Figura 49. Secuencia de generación y consulta de reportes PDF. Fuente: elaboración propia.' },
    @{ name='hu10-actividad.svg'; svg=$activity10; caption='Figura 50. Actividades para generación y consulta de reportes PDF. Fuente: elaboración propia.' },
    @{ name='hu10-mockup.svg'; svg=$mockup10; caption='Figura 51. Mockup monocromático de reportes PDF. Fuente: elaboración propia.' },
    @{ name='hu11-caso-uso.svg'; svg=$useCase11; caption='Figura 52. Caso de uso para gestionar respaldos SQL. Fuente: elaboración propia.' },
    @{ name='hu11-secuencia.svg'; svg=$sequence11; caption='Figura 53. Secuencia de generación y descarga de respaldo SQL. Fuente: elaboración propia.' },
    @{ name='hu11-actividad.svg'; svg=$activity11; caption='Figura 54. Actividades para gestionar respaldos SQL. Fuente: elaboración propia.' },
    @{ name='hu11-mockup.svg'; svg=$mockup11; caption='Figura 55. Mockup monocromático de respaldos. Fuente: elaboración propia.' }
)
function Save-Figure([string]$name,[string]$svg) {
    $svgPath=Join-Path $assetDir $name
    [IO.File]::WriteAllText($svgPath,$svg,[Text.UTF8Encoding]::new($false))
    $pngPath=[IO.Path]::ChangeExtension($svgPath,'.png')
    if($svg -match 'width="(\d+)"\s+height="(\d+)"'){$width=[int]$Matches[1];$height=[int]$Matches[2]}else{throw "Tamaño SVG ausente: $name"}
    $edge='C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
    if(-not(Test-Path -LiteralPath $edge)){$edge='C:\Program Files\Microsoft\Edge\Application\msedge.exe'}
    if(-not(Test-Path -LiteralPath $edge)){throw 'No se encontró Microsoft Edge para convertir las figuras.'}
    $args=@('--headless=new','--disable-gpu','--hide-scrollbars','--no-first-run',('--window-size='+$width+','+$height),('--screenshot='+$pngPath),([Uri]::new($svgPath).AbsoluteUri))
    $proc=Start-Process -FilePath $edge -ArgumentList $args -PassThru -Wait -WindowStyle Hidden
    if(-not(Test-Path -LiteralPath $pngPath)-or(Get-Item -LiteralPath $pngPath).Length -lt 1000){throw "No se pudo renderizar $name (Edge $($proc.ExitCode))."}
    return $pngPath
}
$pngs=@();foreach($f in $figures){$pngs+=,(Save-Figure $f.name $f.svg)}

$wNs='http://schemas.openxmlformats.org/wordprocessingml/2006/main'
$rNs='http://schemas.openxmlformats.org/officeDocument/2006/relationships'
$relNs='http://schemas.openxmlformats.org/package/2006/relationships'
$ctNs='http://schemas.openxmlformats.org/package/2006/content-types'
$wpNs='http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing'
$aNs='http://schemas.openxmlformats.org/drawingml/2006/main'
$picNs='http://schemas.openxmlformats.org/drawingml/2006/picture'
$doc=New-Object System.Xml.XmlDocument;$doc.PreserveWhitespace=$true
$document=$doc.CreateElement('w','document',$wNs);$nsW=$doc.CreateAttribute('xmlns','w','http://www.w3.org/2000/xmlns/');$nsW.Value=$wNs;[void]$document.Attributes.Append($nsW);$nsR=$doc.CreateAttribute('xmlns','r','http://www.w3.org/2000/xmlns/');$nsR.Value=$rNs;[void]$document.Attributes.Append($nsR);[void]$doc.AppendChild($document)
$body=$doc.CreateElement('w','body',$wNs);[void]$document.AppendChild($body)
function Add-WNode([System.Xml.XmlDocument]$x,[System.Xml.XmlElement]$parent,[string]$prefix,[string]$name,[string]$ns){$e=$x.CreateElement($prefix,$name,$ns);[void]$parent.AppendChild($e);return $e}
function Set-WAttr([System.Xml.XmlDocument]$x,[System.Xml.XmlElement]$el,[string]$name,[string]$ns,[string]$value){$a=$x.CreateAttribute('w',$name,$ns);$a.Value=$value;[void]$el.Attributes.Append($a)}
function Add-TextRun([System.Xml.XmlElement]$parent,[string]$text,[switch]$bold,[int]$size=22){$run=Add-WNode $script:doc $parent 'w' 'r' $wNs;if($bold -or $size -ne 22){$rp=Add-WNode $script:doc $run 'w' 'rPr' $wNs;if($bold){[void](Add-WNode $script:doc $rp 'w' 'b' $wNs)};if($size -ne 22){$sz=Add-WNode $script:doc $rp 'w' 'sz' $wNs;Set-WAttr $script:doc $sz 'val' $wNs ([string]$size)}};$t=Add-WNode $script:doc $run 'w' 't' $wNs;$space=$script:doc.CreateAttribute('xml','space','http://www.w3.org/XML/1998/namespace');$space.Value='preserve';[void]$t.Attributes.Append($space);$t.InnerText=$text}
function Add-Paragraph([string]$text,[string]$style='Normal',[switch]$pageBreak,[switch]$center){$p=Add-WNode $script:doc $script:body 'w' 'p' $wNs;$pr=Add-WNode $script:doc $p 'w' 'pPr' $wNs;$ps=Add-WNode $script:doc $pr 'w' 'pStyle' $wNs;Set-WAttr $script:doc $ps 'val' $wNs $style;if($pageBreak){[void](Add-WNode $script:doc $pr 'w' 'pageBreakBefore' $wNs)};if($center){$jc=Add-WNode $script:doc $pr 'w' 'jc' $wNs;Set-WAttr $script:doc $jc 'val' $wNs 'center'};if($text){Add-TextRun $p $text}}
function Add-Table([string[]]$headers,[System.Collections.IList]$rows,[int[]]$widths){$tableWidth=9360;if($widths.Count -ne $headers.Count -or (($widths|Measure-Object -Sum).Sum -ne $tableWidth)){throw 'Anchos de tabla inválidos.'};$tbl=Add-WNode $script:doc $script:body 'w' 'tbl' $wNs;$pr=Add-WNode $script:doc $tbl 'w' 'tblPr' $wNs;$tw=Add-WNode $script:doc $pr 'w' 'tblW' $wNs;Set-WAttr $script:doc $tw 'w' $wNs ([string]$tableWidth);Set-WAttr $script:doc $tw 'type' $wNs 'dxa';$layout=Add-WNode $script:doc $pr 'w' 'tblLayout' $wNs;Set-WAttr $script:doc $layout 'type' $wNs 'fixed';$borders=Add-WNode $script:doc $pr 'w' 'tblBorders' $wNs;foreach($side in @('top','left','bottom','right','insideH','insideV')){$b=Add-WNode $script:doc $borders 'w' $side $wNs;Set-WAttr $script:doc $b 'val' $wNs 'single';Set-WAttr $script:doc $b 'sz' $wNs '5';Set-WAttr $script:doc $b 'color' $wNs 'AAB8B0'};$grid=Add-WNode $script:doc $tbl 'w' 'tblGrid' $wNs;foreach($width in $widths){$col=Add-WNode $script:doc $grid 'w' 'gridCol' $wNs;Set-WAttr $script:doc $col 'w' $wNs ([string]$width)};$allRows=[System.Collections.ArrayList]::new();[void]$allRows.Add([object[]]$headers);foreach($row in $rows){if($row.Count -ne $headers.Count){throw "Cantidad de columnas incorrecta: $row"};[void]$allRows.Add($row)};for($ri=0;$ri -lt $allRows.Count;$ri++){$tr=Add-WNode $script:doc $tbl 'w' 'tr' $wNs;if($ri -eq 0){$trpr=Add-WNode $script:doc $tr 'w' 'trPr' $wNs;[void](Add-WNode $script:doc $trpr 'w' 'tblHeader' $wNs)};for($ci=0;$ci -lt $headers.Count;$ci++){$tc=Add-WNode $script:doc $tr 'w' 'tc' $wNs;$tcpr=Add-WNode $script:doc $tc 'w' 'tcPr' $wNs;$tcw=Add-WNode $script:doc $tcpr 'w' 'tcW' $wNs;Set-WAttr $script:doc $tcw 'w' $wNs ([string]$widths[$ci]);Set-WAttr $script:doc $tcw 'type' $wNs 'dxa';if($ri -eq 0){$shd=Add-WNode $script:doc $tcpr 'w' 'shd' $wNs;Set-WAttr $script:doc $shd 'fill' $wNs 'E5EEE9'};$p=Add-WNode $script:doc $tc 'w' 'p' $wNs;Add-TextRun $p ([string]$allRows[$ri][$ci]) -bold:($ri -eq 0) -size 19}};Add-Paragraph ''}

$relationships=New-Object System.Xml.XmlDocument;$relsRoot=$relationships.CreateElement('Relationships',$relNs);$relsRoot.SetAttribute('xmlns',$relNs);[void]$relationships.AppendChild($relsRoot)
function PlainAttr([System.Xml.XmlElement]$e,[string]$name,[string]$value){$e.SetAttribute($name,$value)}
function Add-Figure([string]$pngPath,[string]$caption){$script:figureNumber++;$rid='rId'+($script:figureNumber+1);$media='image'+$script:figureNumber+'.png';[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($script:zip,$pngPath,('word/media/'+$media),[System.IO.Compression.CompressionLevel]::Optimal)|Out-Null;$rel=$script:relationships.CreateElement('Relationship',$relNs);$rel.SetAttribute('Id',$rid);$rel.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');$rel.SetAttribute('Target','media/'+$media);[void]$script:relationships.DocumentElement.AppendChild($rel);$img=[System.Drawing.Image]::FromFile($pngPath);try{$w0=$img.Width*9525.0;$h0=$img.Height*9525.0}finally{$img.Dispose()};$scale=[Math]::Min(5486400/$w0,4114800/$h0);$cx=[string][int]($w0*$scale);$cy=[string][int]($h0*$scale);$p=Add-WNode $script:doc $script:body 'w' 'p' $wNs;$ppr=Add-WNode $script:doc $p 'w' 'pPr' $wNs;$jc=Add-WNode $script:doc $ppr 'w' 'jc' $wNs;Set-WAttr $script:doc $jc 'val' $wNs 'center';$run=Add-WNode $script:doc $p 'w' 'r' $wNs;$draw=Add-WNode $script:doc $run 'w' 'drawing' $wNs;$inline=Add-WNode $script:doc $draw 'wp' 'inline' $wpNs;foreach($attr in @(@('distT','0'),@('distB','0'),@('distL','0'),@('distR','0'))){PlainAttr $inline $attr[0] $attr[1]};$extent=Add-WNode $script:doc $inline 'wp' 'extent' $wpNs;PlainAttr $extent 'cx' $cx;PlainAttr $extent 'cy' $cy;$dp=Add-WNode $script:doc $inline 'wp' 'docPr' $wpNs;PlainAttr $dp 'id' ([string]$script:figureNumber);PlainAttr $dp 'name' $media;PlainAttr $dp 'descr' $caption;$frame=Add-WNode $script:doc $inline 'wp' 'cNvGraphicFramePr' $wpNs;$lock=Add-WNode $script:doc $frame 'a' 'graphicFrameLocks' $aNs;PlainAttr $lock 'noChangeAspect' '1';$graphic=Add-WNode $script:doc $inline 'a' 'graphic' $aNs;$gd=Add-WNode $script:doc $graphic 'a' 'graphicData' $aNs;PlainAttr $gd 'uri' $picNs;$pic=Add-WNode $script:doc $gd 'pic' 'pic' $picNs;$nv=Add-WNode $script:doc $pic 'pic' 'nvPicPr' $picNs;$cnv=Add-WNode $script:doc $nv 'pic' 'cNvPr' $picNs;PlainAttr $cnv 'id' ([string]$script:figureNumber);PlainAttr $cnv 'name' $media;[void](Add-WNode $script:doc $nv 'pic' 'cNvPicPr' $picNs);$fill=Add-WNode $script:doc $pic 'pic' 'blipFill' $picNs;$blip=Add-WNode $script:doc $fill 'a' 'blip' $aNs;$emb=$script:doc.CreateAttribute('r','embed',$rNs);$emb.Value=$rid;[void]$blip.Attributes.Append($emb);$stretch=Add-WNode $script:doc $fill 'a' 'stretch' $aNs;[void](Add-WNode $script:doc $stretch 'a' 'fillRect' $aNs);$sp=Add-WNode $script:doc $pic 'pic' 'spPr' $picNs;$xf=Add-WNode $script:doc $sp 'a' 'xfrm' $aNs;$off=Add-WNode $script:doc $xf 'a' 'off' $aNs;PlainAttr $off 'x' '0';PlainAttr $off 'y' '0';$ext=Add-WNode $script:doc $xf 'a' 'ext' $aNs;PlainAttr $ext 'cx' $cx;PlainAttr $ext 'cy' $cy;$geom=Add-WNode $script:doc $sp 'a' 'prstGeom' $aNs;PlainAttr $geom 'prst' 'rect';[void](Add-WNode $script:doc $geom 'a' 'avLst' $aNs);Add-Paragraph $caption 'Caption' -center}
function Xml-String([System.Xml.XmlDocument]$x){$settings=[System.Xml.XmlWriterSettings]::new();$settings.Encoding=[Text.UTF8Encoding]::new($false);$settings.OmitXmlDeclaration=$true;$sb=[Text.StringBuilder]::new();$writer=[Xml.XmlWriter]::Create($sb,$settings);$x.Save($writer);$writer.Dispose();return $sb.ToString()}
function Zip-Text([System.IO.Compression.ZipArchive]$zip,[string]$name,[string]$value){$entry=$zip.CreateEntry($name,[System.IO.Compression.CompressionLevel]::Optimal);$stream=$entry.Open();$writer=[IO.StreamWriter]::new($stream,[Text.UTF8Encoding]::new($false));$writer.Write($value);$writer.Dispose();$stream.Dispose()}

$script:figureNumber=47
$script:zip=[System.IO.Compression.ZipFile]::Open($output,[System.IO.Compression.ZipArchiveMode]::Create)
try {
    Add-Paragraph 'Sprint 4: Reportes PDF y respaldos' 'Heading1'
    Add-Paragraph 'Objetivo del Sprint' 'Heading2'
    Add-Paragraph 'Consolidar las salidas del sistema mediante la generación y consulta de reportes PDF, y permitir al administrador exportar los datos institucionales en un respaldo SQL descargable. Verificar los permisos, archivos generados y recuperación de los registros respaldados.'
    Add-Paragraph 'Sprint Backlog' 'Heading2'
    Add-Paragraph 'Tabla 26. Sprint Backlog' 'Caption'
    $backlog=[System.Collections.ArrayList]::new()
    [void]$backlog.Add([object[]]@('HU10','Generar, registrar y consultar reportes PDF con resultados del análisis, respetando el acceso del usuario a los proyectos.','Pruebas de generación, persistencia, descarga y consulta según permisos.'))
    [void]$backlog.Add([object[]]@('HU11','Exportar los registros de nueve tablas en un archivo SQL para conservar una copia de datos recuperable.','Generación, descarga e importación de la copia en una base SQLite separada.'))
    Add-Table @('Historia','Trabajo del sprint','Comprobación disponible') $backlog @(900,4200,4260)
    Add-Paragraph 'La planificación del Sprint 4 contempla tres semanas. El respaldo SQL contiene datos y relaciones; los archivos PDF se conservan mediante una copia aparte de storage/app/public.'

    Add-Paragraph 'HU10: Generación y consulta de reportes PDF' 'Heading1' -pageBreak
    Add-Paragraph 'Como persona autorizada, necesito obtener y consultar reportes PDF.'
    Add-Paragraph 'Diagrama de caso de uso' 'Heading2';Add-Figure $pngs[0] $figures[0].caption
    Add-Paragraph 'Especificación del caso de uso' 'Heading2';Add-Paragraph 'Tabla 27. Especificación del caso de uso: reportes PDF' 'Caption'
    $spec10=[System.Collections.ArrayList]::new()
    [void]$spec10.Add([object[]]@('Actores','Persona autenticada con permiso para consultar el proyecto. El Administrador y el Gestor consultan el ámbito institucional; el usuario regular, solo sus proyectos.'))
    [void]$spec10.Add([object[]]@('Precondición','La persona inició sesión. El proyecto existe y cuenta con un documento PDF registrado para generar el reporte.'))
    [void]$spec10.Add([object[]]@('Postcondición','Se genera y descarga un reporte PDF, se guarda su archivo y se registra el proyecto, la persona que lo generó y la ruta. El reporte puede consultarse después desde el historial si el archivo está disponible.'))
    [void]$spec10.Add([object[]]@('Flujo básico','1. La persona abre un proyecto y solicita el reporte. 2. El sistema verifica el permiso y la existencia del PDF. 3. Consulta las comparaciones de la misma modalidad. 4. Genera y guarda el PDF. 5. Registra o actualiza el reporte y entrega el archivo.'))
    [void]$spec10.Add([object[]]@('Flujo alternativo: consultar','La persona abre el historial. El sistema muestra reportes de acuerdo con su rol y permite abrir el PDF disponible.'))
    [void]$spec10.Add([object[]]@('Excepciones','Sin documento: vuelve al catálogo con un mensaje. Sin permiso: acceso prohibido. Archivo ausente: informa que no se encuentra disponible.'))
    Add-Table @('Elemento','Descripción') $spec10 @(1900,7460)
    Add-Paragraph 'Diagrama de secuencia' 'Heading2';Add-Figure $pngs[1] $figures[1].caption
    Add-Paragraph 'Diagrama de actividades' 'Heading2';Add-Figure $pngs[2] $figures[2].caption
    Add-Paragraph 'Mockup' 'Heading2';Add-Figure $pngs[3] $figures[3].caption

    Add-Paragraph 'HU11: Exportación de respaldos SQL' 'Heading1' -pageBreak
    Add-Paragraph 'Como Administrador, necesito exportar datos para conservar una copia recuperable.'
    Add-Paragraph 'Diagrama de caso de uso' 'Heading2';Add-Figure $pngs[4] $figures[4].caption
    Add-Paragraph 'Especificación del caso de uso' 'Heading2';Add-Paragraph 'Tabla 28. Especificación del caso de uso: respaldos SQL' 'Caption'
    $spec11=[System.Collections.ArrayList]::new()
    [void]$spec11.Add([object[]]@('Actor','Administrador autenticado. Las rutas de consulta, generación y descarga están protegidas para este rol.'))
    [void]$spec11.Add([object[]]@('Precondición','El administrador inició sesión y la base de datos del sistema está disponible.'))
    [void]$spec11.Add([object[]]@('Postcondición','Se crea un archivo .sql con datos de nueve tablas del sistema, se almacena en el directorio privado de respaldos y aparece en el listado para su descarga.'))
    [void]$spec11.Add([object[]]@('Flujo básico','1. El administrador abre Respaldos. 2. Solicita generar una copia. 3. El sistema lee carreras, cuentas, estudiantes, docentes, relaciones, proyectos, documentos, comparaciones y reportes. 4. Construye el SQL y lo guarda. 5. Actualiza el listado y permite descargar el archivo.'))
    [void]$spec11.Add([object[]]@('Flujo alternativo: descargar','El administrador selecciona un respaldo existente. El sistema comprueba que el archivo exista y lo descarga.'))
    [void]$spec11.Add([object[]]@('Excepciones','Si el archivo no existe, se devuelve no encontrado. Si falla la lectura o escritura, no se confirma la generación y se debe reintentar.'))
    [void]$spec11.Add([object[]]@('Límite del respaldo','El archivo SQL contiene registros, no los archivos PDF binarios. Para una copia completa también se debe conservar storage/app/public. La aplicación no ofrece una acción propia para restaurar; la recuperación se realiza con una herramienta de base de datos.'))
    Add-Table @('Elemento','Descripción') $spec11 @(1900,7460)
    Add-Paragraph 'Diagrama de secuencia' 'Heading2';Add-Figure $pngs[5] $figures[5].caption
    Add-Paragraph 'Diagrama de actividades' 'Heading2';Add-Figure $pngs[6] $figures[6].caption
    Add-Paragraph 'Mockup' 'Heading2';Add-Figure $pngs[7] $figures[7].caption

    Add-Paragraph 'Diseño e implementación de interfaces' 'Heading1' -pageBreak
    Add-Paragraph 'HU10: Generar y consultar reportes' 'Heading2'
    Add-Paragraph 'ESPACIO PARA CAPTURA REAL: catálogo o resultados del proyecto con la opción para generar el reporte y respuesta PDF.' 'Heading3'
    Add-Paragraph 'Insertar captura del sistema funcionando; ocultar nombres, correos y datos personales.'
    Add-Paragraph 'ESPACIO PARA CAPTURA REAL: historial de reportes y apertura de un PDF guardado.' 'Heading3'
    Add-Paragraph 'Insertar captura del listado de reportes con datos de prueba.'
    Add-Paragraph 'HU11: Generar y descargar respaldos' 'Heading2'
    Add-Paragraph 'ESPACIO PARA CAPTURA REAL: pantalla de respaldos con archivo SQL generado y opción de descarga.' 'Heading3'
    Add-Paragraph 'Insertar captura del sistema funcionando; no mostrar el contenido sensible del respaldo.'

    Add-Paragraph 'Pruebas de testing' 'Heading1' -pageBreak
    Add-Paragraph 'Se ejecutaron cinco pruebas automatizadas de Laravel relacionadas con HU10 y HU11. Verificaron la generación y persistencia del PDF, la consulta por usuario y permisos, y la creación y descarga de un respaldo SQL cuya importación se comprobó en una base SQLite separada.'
    Add-Paragraph 'Resultado de la ejecución seleccionada: 5 pruebas aprobadas y 47 aserciones, sin fallos, en 1,87 segundos (02/11/2026). Los archivos PDF se simularon en almacenamiento de prueba; la comprobación del respaldo sí importó el SQL y comparó los registros de nueve tablas.'
    Add-Paragraph 'Herramientas utilizadas' 'Heading2'
    Add-Paragraph 'PHPUnit 12.5.32 y Laravel Testing Framework — pruebas automatizadas de rutas, respuestas, permisos y persistencia.'
    Add-Paragraph 'SQLite en memoria — restauración de prueba aislada del respaldo SQL.'
    Add-Paragraph 'Storage Fake — comprobación de archivos PDF sin alterar el almacenamiento real.'
    Add-Paragraph 'Tipos de prueba aplicados' 'Heading2'
    Add-Paragraph 'Pruebas funcionales y de integración, con enfoque de caja negra: se verifican las solicitudes y respuestas observables para generación, consulta, descarga y permisos. La importación en una base separada comprueba la recuperabilidad de los datos exportados.'
    Add-Paragraph 'Los controles de acceso se comprobaron en las rutas cubiertas. Esta ejecución no constituye una auditoría de seguridad ni una prueba de penetración; tampoco se presentó una suite independiente de caja blanca.'
    Add-Paragraph 'Tabla 29. Resultados de las pruebas' 'Caption'
    $testRows=[System.Collections.ArrayList]::new()
    [void]$testRows.Add([object[]]@('TC-10A / HU10','Generar PDF, guardarlo y registrar proyecto, ruta y persona generadora.','Feature / integración','Aprobado'))
    [void]$testRows.Add([object[]]@('TC-10B / HU10','Consultar y abrir reportes; restringir al usuario regular a los proyectos propios.','Feature / permisos','Aprobado'))
    [void]$testRows.Add([object[]]@('TC-11 / HU11','Generar y descargar SQL; importarlo en otra SQLite y comparar nueve tablas y relaciones.','Feature / recuperación de datos','Aprobado'))
    Add-Table @('Caso / HU','Comprobación resumida','Tipo de prueba','Resultado') $testRows @(1500,3900,2550,1410)
    Add-Paragraph 'Tabla 30. Archivos de prueba relacionados' 'Caption'
    $filesRows=[System.Collections.ArrayList]::new()
    [void]$filesRows.Add([object[]]@('tests/Feature/ProjectFilesTest.php','Generación, acceso, permisos y apertura de reportes PDF.'))
    [void]$filesRows.Add([object[]]@('tests/Feature/PresentationReadinessTest.php','Generación y descarga del respaldo; importación y comprobación de datos en SQLite aislado.'))
    Add-Table @('Archivo','Escenarios ejecutados') $filesRows @(3400,5960)
    Add-Paragraph 'Evidencia' 'Heading2'
    Add-Paragraph 'ESPACIO PARA CAPTURA REAL: terminal con las cinco pruebas seleccionadas aprobadas.' 'Heading3'
    Add-Paragraph 'Comando ejecutado: php artisan test --testdox --filter="backup_download_and_restore|upload_of_a_real_pdf_and_reports|authenticated_user_can_view_a_generated_report|user_only_sees_and_opens_reports|pdf_report_generation_remains_available".'
    Add-Paragraph 'Escenarios cubiertos' 'Heading2'
    Add-Paragraph 'HU10: el PDF se genera, se guarda y queda relacionado con su proyecto; los reportes se consultan según permisos y propiedad.'
    Add-Paragraph 'HU11: el administrador genera y descarga un .sql; la copia se importa en una base separada y se comparan cantidades de registros y relaciones.'
    Add-Paragraph 'Límite: el respaldo SQL no incluye binarios PDF y la restauración no se ejecuta desde el sistema; se requiere copiar los archivos y usar una herramienta de base de datos.'

    $sect=Add-WNode $doc $body 'w' 'sectPr' $wNs;$pgSz=Add-WNode $doc $sect 'w' 'pgSz' $wNs;Set-WAttr $doc $pgSz 'w' $wNs '12240';Set-WAttr $doc $pgSz 'h' $wNs '15840';$pgMar=Add-WNode $doc $sect 'w' 'pgMar' $wNs;foreach($pair in @(@('top','1080'),@('right','1440'),@('bottom','1080'),@('left','1440'),@('header','720'),@('footer','720'),@('gutter','0'))){Set-WAttr $doc $pgMar $pair[0] $wNs $pair[1]}

    $ct="<?xml version='1.0' encoding='UTF-8' standalone='yes'?><Types xmlns='$ctNs'><Default Extension='rels' ContentType='application/vnd.openxmlformats-package.relationships+xml'/><Default Extension='xml' ContentType='application/xml'/><Default Extension='png' ContentType='image/png'/><Override PartName='/word/document.xml' ContentType='application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'/><Override PartName='/word/styles.xml' ContentType='application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml'/></Types>"
    $rootRels="<?xml version='1.0' encoding='UTF-8' standalone='yes'?><Relationships xmlns='$relNs'><Relationship Id='rId1' Type='http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument' Target='word/document.xml'/></Relationships>"
    $styles="<w:styles xmlns:w='$wNs'><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii='Arial' w:hAnsi='Arial'/><w:sz w:val='22'/></w:rPr></w:rPrDefault></w:docDefaults><w:style w:type='paragraph' w:styleId='Normal'><w:name w:val='Normal'/><w:rPr><w:rFonts w:ascii='Arial' w:hAnsi='Arial'/><w:sz w:val='22'/></w:rPr></w:style><w:style w:type='paragraph' w:styleId='Heading1'><w:name w:val='heading 1'/><w:basedOn w:val='Normal'/><w:pPr><w:keepNext/></w:pPr><w:rPr><w:b/><w:color w:val='20382F'/><w:sz w:val='30'/></w:rPr></w:style><w:style w:type='paragraph' w:styleId='Heading2'><w:name w:val='heading 2'/><w:basedOn w:val='Normal'/><w:pPr><w:keepNext/></w:pPr><w:rPr><w:b/><w:color w:val='20382F'/><w:sz w:val='26'/></w:rPr></w:style><w:style w:type='paragraph' w:styleId='Heading3'><w:name w:val='heading 3'/><w:basedOn w:val='Normal'/><w:pPr><w:keepNext/></w:pPr><w:rPr><w:b/><w:sz w:val='22'/></w:rPr></w:style><w:style w:type='paragraph' w:styleId='Caption'><w:name w:val='caption'/><w:basedOn w:val='Normal'/><w:rPr><w:i/><w:sz w:val='18'/></w:rPr></w:style></w:styles>"
    Zip-Text $script:zip '[Content_Types].xml' $ct
    Zip-Text $script:zip '_rels/.rels' $rootRels
    Zip-Text $script:zip 'word/document.xml' (Xml-String $doc)
    Zip-Text $script:zip 'word/styles.xml' $styles
    Zip-Text $script:zip 'word/_rels/document.xml.rels' (Xml-String $relationships)
}
finally { $script:zip.Dispose() }

$check=[System.IO.Compression.ZipFile]::OpenRead($output)
try {
    $reader=[IO.StreamReader]::new($check.GetEntry('word/document.xml').Open(),[Text.Encoding]::UTF8)
    try{[xml]$verified=$reader.ReadToEnd()}finally{$reader.Dispose()}
    $allText=($verified.SelectNodes('//*[local-name()="t"]')|ForEach-Object{$_.InnerText}) -join ' '
    foreach($required in @('HU10','HU11','47 aserciones','Tabla 30')){if($allText -notmatch [regex]::Escape($required)){throw "Falta texto esperado en el Word: $required"}}
    if(($check.Entries|Where-Object{$_.FullName -like 'word/media/*'}).Count -ne 8){throw 'El documento no contiene las ocho figuras previstas.'}
    if($verified.SelectNodes('//*[local-name()="tbl"]').Count -ne 5){throw 'El documento no contiene las cinco tablas previstas.'}
}
finally{$check.Dispose()}
Write-Output "Documento creado y validado: $output"
Write-Output "Figuras SVG/PNG: $assetDir"
