$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Drawing

$raiz = (Get-Location).Path
$carpeta = Join-Path $raiz 'docs\sprint-3'
$salida = Join-Path $raiz 'docs\INFORME_SPRINT_3_COMPARACION_FINAL_VERIFICADO.docx'
New-Item -ItemType Directory -Path $carpeta -Force | Out-Null

function Escape-Xml([string]$value) { [System.Security.SecurityElement]::Escape($value) }
function Guardar-Figura([string]$nombre, [string]$svg) {
    $svgPath = Join-Path $carpeta $nombre
    [System.IO.File]::WriteAllText($svgPath, $svg, [System.Text.UTF8Encoding]::new($false))
    $pngPath = [System.IO.Path]::ChangeExtension($svgPath, '.png')
    if ($svg -match 'width="(\d+)"\s+height="(\d+)"') { $width = [int]$Matches[1]; $height = [int]$Matches[2] } else { $width = 1600; $height = 1100 }
    $edge = 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
    if (-not (Test-Path -LiteralPath $edge)) { throw 'No se encontró Microsoft Edge para convertir las figuras.' }
    $args = @('--headless=new','--disable-gpu','--hide-scrollbars','--no-first-run',('--window-size='+$width+','+$height),('--screenshot='+$pngPath),([System.Uri]::new($svgPath).AbsoluteUri))
    $proc = Start-Process -FilePath $edge -ArgumentList $args -PassThru -Wait -WindowStyle Hidden
    if (-not (Test-Path -LiteralPath $pngPath) -or (Get-Item -LiteralPath $pngPath).Length -lt 1000) { throw "No se pudo renderizar $nombre (Edge $($proc.ExitCode))." }
    return $pngPath
}

$useCaseSvg = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1560" height="1220" viewBox="0 0 1560 1220">
<defs><marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.use{fill:#fff;stroke:#111;stroke-width:2.4}.assoc{fill:none;stroke:#111;stroke-width:2}.inc{fill:none;stroke:#333;stroke-width:1.7;stroke-dasharray:7 6;marker-end:url(#arrow)}.actor{fill:none;stroke:#111;stroke-width:3}</style></defs>
<rect x="225" y="28" width="1080" height="1165" fill="#fff" stroke="#111" stroke-width="2.5"/>
<!-- Administrador / Gestor -->
<circle class="actor" cx="92" cy="558" r="20"/><path class="actor" d="M92 578v70M52 602h80M92 648l-32 48M92 648l32 48"/><text x="92" y="724" text-anchor="middle" font-size="19">Administrador / Gestor</text>
<!-- API actors -->
<circle class="actor" cx="1450" cy="870" r="18"/><path class="actor" d="M1450 888v58M1418 909h64M1450 946l-25 40M1450 946l25 40"/><text x="1450" y="1005" text-anchor="middle" font-size="18">Crossref</text><text x="1450" y="1028" text-anchor="middle" font-size="16">API</text>
<circle class="actor" cx="1450" cy="1070" r="18"/><path class="actor" d="M1450 1088v58M1418 1109h64M1450 1146l-25 40M1450 1146l25 40"/><text x="1450" y="1200" text-anchor="middle" font-size="18">OpenAlex API</text>
<!-- Main use cases -->
<ellipse class="use" cx="510" cy="125" rx="235" ry="43"/><text x="510" y="120" text-anchor="middle" font-size="17"><tspan x="510">Seleccionar proyecto activo</tspan><tspan x="510" dy="21">con PDF y texto disponible</tspan></text>
<ellipse class="use" cx="510" cy="300" rx="235" ry="47"/><text x="510" y="294" text-anchor="middle" font-size="17"><tspan x="510">Comparar proyecto con el repositorio</tspan><tspan x="510" dy="22">de su carrera y modalidad</tspan></text>
<ellipse class="use" cx="510" cy="475" rx="235" ry="47"/><text x="510" y="469" text-anchor="middle" font-size="17"><tspan x="510">Comparar dos proyectos</tspan><tspan x="510" dy="22">de la misma modalidad</tspan></text>
<ellipse class="use" cx="510" cy="650" rx="235" ry="47"/><text x="510" y="644" text-anchor="middle" font-size="17"><tspan x="510">Comparar proyecto con</tspan><tspan x="510" dy="22">publicaciones académicas externas</tspan></text>
<ellipse class="use" cx="510" cy="850" rx="235" ry="47"/><text x="510" y="844" text-anchor="middle" font-size="17"><tspan x="510">Consultar porcentajes</tspan><tspan x="510" dy="22">ordenados por similitud</tspan></text>
<ellipse class="use" cx="510" cy="1040" rx="235" ry="47"/><text x="510" y="1034" text-anchor="middle" font-size="17"><tspan x="510">Revisar fragmentos</tspan><tspan x="510" dy="22">semejantes detectados</tspan></text>
<!-- Included use cases -->
<ellipse class="use" cx="1030" cy="125" rx="230" ry="43"/><text x="1030" y="120" text-anchor="middle" font-size="16"><tspan x="1030">Validar proyecto activo</tspan><tspan x="1030" dy="21">y texto analizable</tspan></text>
<ellipse class="use" cx="1030" cy="300" rx="230" ry="47"/><text x="1030" y="294" text-anchor="middle" font-size="16"><tspan x="1030">Filtrar candidatos internos</tspan><tspan x="1030" dy="22">misma carrera y modalidad</tspan></text>
<ellipse class="use" cx="1030" cy="475" rx="230" ry="47"/><text x="1030" y="469" text-anchor="middle" font-size="16"><tspan x="1030">Validar modalidad y texto</tspan><tspan x="1030" dy="22">de ambos proyectos</tspan></text>
<ellipse class="use" cx="1030" cy="650" rx="230" ry="47"/><text x="1030" y="644" text-anchor="middle" font-size="16"><tspan x="1030">Analizar secciones comparables</tspan><tspan x="1030" dy="22">y calcular similitud textual</tspan></text>
<ellipse class="use" cx="1030" cy="850" rx="230" ry="47"/><text x="1030" y="844" text-anchor="middle" font-size="16"><tspan x="1030">Buscar por título en Crossref</tspan><tspan x="1030" dy="22">y comparar metadatos</tspan></text>
<ellipse class="use" cx="1030" cy="1040" rx="230" ry="47"/><text x="1030" y="1034" text-anchor="middle" font-size="16"><tspan x="1030">Buscar por título en OpenAlex</tspan><tspan x="1030" dy="22">y comparar metadatos</tspan></text>
<!-- Actor associations -->
<path class="assoc" d="M132 602L275 125M132 602L275 300M132 602L275 475M132 602L275 650M132 602L275 850M132 602L275 1040"/>
<path class="assoc" d="M1418 909L1260 850M1418 1109L1260 1040"/>
<!-- Include relations, kept dashed and labeled -->
<path class="inc" d="M745 300H785V125H800"/><text x="755" y="220" font-size="14">«include»</text>
<path class="inc" d="M745 300H800"/><text x="750" y="288" font-size="14">«include»</text>
<path class="inc" d="M745 300H780V650H800"/><text x="752" y="535" font-size="14">«include»</text>
<path class="inc" d="M745 475H785V125H800"/><text x="755" y="390" font-size="14">«include»</text>
<path class="inc" d="M745 475H800"/><text x="750" y="463" font-size="14">«include»</text>
<path class="inc" d="M745 475H780V650H800"/><text x="752" y="610" font-size="14">«include»</text>
<path class="inc" d="M745 650H785V125H800"/><text x="755" y="720" font-size="14">«include»</text>
<path class="inc" d="M745 650H780V850H800"/><text x="752" y="810" font-size="14">«include»</text>
<path class="inc" d="M745 650H780V1040H800"/><text x="752" y="1010" font-size="14">«include»</text>
<path class="inc" d="M510 650V850"/><text x="520" y="755" font-size="14">«include»</text>
<path class="inc" d="M510 475V1040"/><text x="520" y="990" font-size="14">«include»</text>
<path class="inc" d="M510 650V1040"/><text x="520" y="1008" font-size="14">«include»</text>
</svg>
'@

$useCaseSvg = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1200" viewBox="0 0 1600 1200">
<defs><marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.use{fill:#fff;stroke:#111;stroke-width:2.4}.assoc{fill:none;stroke:#111;stroke-width:2}.inc{fill:none;stroke:#333;stroke-width:1.7;stroke-dasharray:7 6;marker-end:url(#arrow)}.actor{fill:none;stroke:#111;stroke-width:3}</style></defs>
<rect x="235" y="28" width="1090" height="1140" fill="#fff" stroke="#111" stroke-width="2.5"/>
<circle class="actor" cx="105" cy="510" r="20"/><path class="actor" d="M105 530v70M65 552h80M105 600l-32 48M105 600l32 48"/><text x="105" y="680" text-anchor="middle" font-size="17">Administrador / Gestor</text>
<circle class="actor" cx="1490" cy="865" r="18"/><path class="actor" d="M1490 883v58M1458 904h64M1490 941l-25 40M1490 941l25 40"/><text x="1490" y="1000" text-anchor="middle" font-size="17">Crossref API</text>
<circle class="actor" cx="1490" cy="1035" r="18"/><path class="actor" d="M1490 1053v58M1458 1074h64M1490 1111l-25 40M1490 1111l25 40"/><text x="1490" y="1170" text-anchor="middle" font-size="17">OpenAlex API</text>
<ellipse class="use" cx="480" cy="245" rx="200" ry="54"/><text x="480" y="238" text-anchor="middle" font-size="17"><tspan x="480">Comparar proyecto con</tspan><tspan x="480" dy="22">el repositorio institucional</tspan></text>
<ellipse class="use" cx="480" cy="565" rx="200" ry="54"/><text x="480" y="558" text-anchor="middle" font-size="17"><tspan x="480">Comparar dos proyectos</tspan><tspan x="480" dy="22">de modalidad compatible</tspan></text>
<ellipse class="use" cx="480" cy="885" rx="200" ry="54"/><text x="480" y="878" text-anchor="middle" font-size="17"><tspan x="480">Buscar similitudes en</tspan><tspan x="480" dy="22">publicaciones externas</tspan></text>
<ellipse class="use" cx="1060" cy="145" rx="220" ry="43"/><text x="1060" y="140" text-anchor="middle" font-size="15"><tspan x="1060">Validar proyecto activo y PDF</tspan><tspan x="1060" dy="20">con texto analizable</tspan></text>
<ellipse class="use" cx="1060" cy="245" rx="220" ry="43"/><text x="1060" y="240" text-anchor="middle" font-size="15"><tspan x="1060">Filtrar proyectos de la misma</tspan><tspan x="1060" dy="20">carrera y modalidad</tspan></text>
<ellipse class="use" cx="1060" cy="345" rx="220" ry="43"/><text x="1060" y="340" text-anchor="middle" font-size="15"><tspan x="1060">Comparar secciones válidas</tspan><tspan x="1060" dy="20">y calcular el porcentaje</tspan></text>
<ellipse class="use" cx="1060" cy="465" rx="220" ry="43"/><text x="1060" y="460" text-anchor="middle" font-size="15"><tspan x="1060">Validar texto y modalidad</tspan><tspan x="1060" dy="20">de ambos proyectos</tspan></text>
<ellipse class="use" cx="1060" cy="565" rx="220" ry="43"/><text x="1060" y="560" text-anchor="middle" font-size="15"><tspan x="1060">Extraer secciones y calcular</tspan><tspan x="1060" dy="20">la similitud textual</tspan></text>
<ellipse class="use" cx="1060" cy="665" rx="220" ry="43"/><text x="1060" y="660" text-anchor="middle" font-size="15"><tspan x="1060">Mostrar porcentaje y revisar</tspan><tspan x="1060" dy="20">fragmentos coincidentes</tspan></text>
<ellipse class="use" cx="1060" cy="785" rx="220" ry="43"/><text x="1060" y="780" text-anchor="middle" font-size="15"><tspan x="1060">Validar proyecto y texto</tspan><tspan x="1060" dy="20">para la consulta externa</tspan></text>
<ellipse class="use" cx="1060" cy="885" rx="220" ry="43"/><text x="1060" y="890" text-anchor="middle" font-size="15">Buscar publicaciones en Crossref</text>
<ellipse class="use" cx="1060" cy="985" rx="220" ry="43"/><text x="1060" y="990" text-anchor="middle" font-size="15">Buscar publicaciones en OpenAlex</text>
<ellipse class="use" cx="1060" cy="1085" rx="220" ry="43"/><text x="1060" y="1080" text-anchor="middle" font-size="15"><tspan x="1060">Comparar título y resumen disponible;</tspan><tspan x="1060" dy="20">ordenar resultados por similitud</tspan></text>
<path class="assoc" d="M145 555L280 245M145 565H280M145 575L280 885"/><path class="assoc" d="M1458 904L1280 885M1458 1074L1280 985"/>
<path class="inc" d="M680 230L840 145"/><text x="735" y="178" font-size="13">«include»</text><path class="inc" d="M680 245H840"/><text x="720" y="237" font-size="13">«include»</text><path class="inc" d="M680 260L840 345"/><text x="735" y="310" font-size="13">«include»</text>
<path class="inc" d="M680 550L840 465"/><text x="735" y="505" font-size="13">«include»</text><path class="inc" d="M680 565H840"/><text x="720" y="557" font-size="13">«include»</text><path class="inc" d="M680 580L840 665"/><text x="735" y="630" font-size="13">«include»</text>
<path class="inc" d="M680 870L840 785"/><text x="735" y="825" font-size="13">«include»</text><path class="inc" d="M680 885H840"/><text x="720" y="877" font-size="13">«include»</text><path class="inc" d="M680 900L840 985"/><text x="735" y="950" font-size="13">«include»</text><path class="inc" d="M680 900L840 1085"/><text x="740" y="1010" font-size="13">«include»</text>
</svg>
'@

$sequenceSvg = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1320" viewBox="0 0 1600 1320">
<defs><marker id="arrow" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.box{fill:#fff;stroke:#111;stroke-width:2}.life{stroke:#666;stroke-dasharray:7 6;stroke-width:1.7}.msg{stroke:#111;stroke-width:2;marker-end:url(#arrow)}.ret{stroke:#444;stroke-width:1.7;stroke-dasharray:7 5;marker-end:url(#arrow)}.frame{fill:none;stroke:#111;stroke-width:2}</style></defs>
<g font-size="17"><rect class="box" x="25" y="20" width="190" height="55"/><text x="120" y="54" text-anchor="middle">Administrador / Gestor</text><rect class="box" x="275" y="20" width="190" height="55"/><text x="370" y="54" text-anchor="middle">Vista de análisis</text><rect class="box" x="525" y="20" width="190" height="55"/><text x="620" y="54" text-anchor="middle">Controladores</text><rect class="box" x="775" y="20" width="210" height="55"/><text x="880" y="54" text-anchor="middle">Servicios de análisis</text><rect class="box" x="1050" y="20" width="190" height="55"/><text x="1145" y="54" text-anchor="middle">Base de datos</text><rect class="box" x="1300" y="20" width="270" height="55"/><text x="1435" y="54" text-anchor="middle">Crossref / OpenAlex</text></g>
<path class="life" d="M120 75V1290M370 75V1290M620 75V1290M880 75V1290M1145 75V1290M1435 75V1290"/>
<g font-size="15">
<path class="msg" d="M120 125H370"/><text x="245" y="116" text-anchor="middle">Abrir análisis</text>
<path class="msg" d="M370 185H620"/><text x="495" y="176" text-anchor="middle">Solicitar proyectos analizables</text>
<path class="msg" d="M620 245H1145"/><text x="882" y="236" text-anchor="middle">Consultar proyectos activos y texto extraído</text>
<path class="ret" d="M1145 305H620"/><text x="882" y="296" text-anchor="middle">Devolver datos académicos</text>
<path class="ret" d="M620 365H370"/><text x="495" y="356" text-anchor="middle">Mostrar opciones de comparación</text>
<path class="msg" d="M120 425H370"/><text x="245" y="416" text-anchor="middle">Elegir modo y proyecto(s)</text>
<path class="msg" d="M370 485H620"/><text x="495" y="476" text-anchor="middle">Enviar selección</text>
<path class="msg" d="M620 545H1145"/><text x="882" y="536" text-anchor="middle">Validar estado, modalidad y documentos</text>
<path class="ret" d="M1145 605H620"/><text x="882" y="596" text-anchor="middle">Devolver texto y relaciones</text>
<path class="msg" d="M620 665H880"/><text x="750" y="656" text-anchor="middle">Extraer secciones permitidas</text>
<rect class="frame" x="575" y="705" width="990" height="165"/><text x="590" y="730" font-size="16" font-weight="700">alt [Repositorio institucional]</text>
<path class="msg" d="M880 755H1145"/><text x="1012" y="746" text-anchor="middle">Buscar misma carrera y modalidad</text>
<path class="ret" d="M1145 805H880"/><text x="1012" y="796" text-anchor="middle">Devolver candidatos compatibles</text>
<path class="msg" d="M880 845H1145"/><text x="1012" y="836" text-anchor="middle">Guardar porcentajes calculados</text>
<rect class="frame" x="575" y="885" width="990" height="125"/><text x="590" y="910" font-size="16" font-weight="700">alt [Dos proyectos]</text>
<path class="msg" d="M620 945H880"/><text x="750" y="936" text-anchor="middle">Comparar textos y detectar fragmentos</text>
<path class="ret" d="M880 985H620"/><text x="750" y="976" text-anchor="middle">Devolver porcentaje y coincidencias</text>
<rect class="frame" x="575" y="1025" width="990" height="190"/><text x="590" y="1050" font-size="16" font-weight="700">alt [Crossref y OpenAlex]</text>
<path class="msg" d="M880 1085H1435"/><text x="1157" y="1076" text-anchor="middle">Buscar publicaciones por título</text>
<path class="ret" d="M1435 1135H880"/><text x="1157" y="1126" text-anchor="middle">Devolver título, DOI y resumen disponible</text>
<path class="msg" d="M880 1185H620"/><text x="750" y="1176" text-anchor="middle">Deduplicar y calcular porcentajes / fragmentos</text>
<path class="ret" d="M620 1240H370"/><text x="495" y="1231" text-anchor="middle">Mostrar resultados y fuente no disponible si aplica</text>
<path class="ret" d="M370 1280H120"/><text x="245" y="1271" text-anchor="middle">Revisar puntajes y coincidencias</text>
</g></svg>
'@

$activitySvg = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1440" height="1020" viewBox="0 0 1440 1020">
<defs><marker id="a" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.n{fill:#fff;stroke:#111;stroke-width:2}.l{fill:none;stroke:#111;stroke-width:2;marker-end:url(#a)}.dash{fill:none;stroke:#444;stroke-width:1.7;stroke-dasharray:7 6;marker-end:url(#a)}.start{fill:#111}.end{fill:#fff;stroke:#111;stroke-width:3}</style></defs>
<circle class="start" cx="720" cy="35" r="12"/><path class="l" d="M720 47V80"/><rect class="n" x="530" y="80" width="380" height="56" rx="18"/><text x="720" y="115" text-anchor="middle" font-size="18">Abrir la función de comparación</text><path class="l" d="M720 136V168"/>
<path class="n" d="M720 168L820 224L720 280L620 224Z"/><text x="720" y="219" text-anchor="middle" font-size="16"><tspan x="720">¿Qué se</tspan><tspan x="720" dy="20">compara?</tspan></text>
<path class="l" d="M620 224H215V285"/><text x="400" y="213" font-size="15">Repositorio</text><path class="l" d="M720 280V285"/><text x="733" y="273" font-size="15">Dos proyectos</text><path class="l" d="M820 224H1225V285"/><text x="1030" y="213" font-size="15">Fuentes externas</text>
<!-- Repository branch -->
<rect class="n" x="35" y="285" width="360" height="68" rx="16"/><text x="215" y="313" text-anchor="middle" font-size="16"><tspan x="215">Seleccionar proyecto activo</tspan><tspan x="215" dy="21">con texto disponible</tspan></text><path class="l" d="M215 353V390"/>
<rect class="n" x="35" y="390" width="360" height="68" rx="16"/><text x="215" y="418" text-anchor="middle" font-size="16"><tspan x="215">Buscar proyectos activos</tspan><tspan x="215" dy="21">misma carrera y modalidad</tspan></text><path class="l" d="M215 458V500"/>
<path class="n" d="M215 500L275 540L215 580L155 540Z"/><text x="215" y="545" text-anchor="middle" font-size="14">¿Hay candidatos?</text><path class="l" d="M275 540H330V610"/><text x="285" y="530" font-size="14">Sí</text>
<rect class="n" x="35" y="610" width="360" height="70" rx="16"/><text x="215" y="639" text-anchor="middle" font-size="16"><tspan x="215">Calcular coincidencia textual</tspan><tspan x="215" dy="21">y guardar comparaciones</tspan></text><path class="l" d="M215 680V735"/>
<rect class="n" x="35" y="735" width="360" height="62" rx="16"/><text x="215" y="773" text-anchor="middle" font-size="16">Ordenar resultados por similitud</text><path class="l" d="M155 540H65V610"/><text x="75" y="530" font-size="14">No</text>
<rect class="n" x="35" y="820" width="360" height="62" rx="16"/><text x="215" y="858" text-anchor="middle" font-size="15">Informar que no hay proyectos comparables</text>
<!-- Pair branch -->
<rect class="n" x="540" y="285" width="360" height="68" rx="16"/><text x="720" y="313" text-anchor="middle" font-size="16"><tspan x="720">Seleccionar proyecto A</tspan><tspan x="720" dy="21">y proyecto B</tspan></text><path class="l" d="M720 353V390"/>
<path class="n" d="M720 390L790 438L720 486L650 438Z"/><text x="720" y="433" text-anchor="middle" font-size="14"><tspan x="720">¿Activos, texto</tspan><tspan x="720" dy="18">y modalidad igual?</tspan></text><path class="l" d="M790 438H860V520"/><text x="805" y="428" font-size="14">Sí</text>
<rect class="n" x="540" y="520" width="360" height="70" rx="16"/><text x="720" y="549" text-anchor="middle" font-size="16"><tspan x="720">Extraer secciones comparables</tspan><tspan x="720" dy="21">y calcular similitud</tspan></text><path class="l" d="M720 590V635"/>
<rect class="n" x="540" y="635" width="360" height="68" rx="16"/><text x="720" y="663" text-anchor="middle" font-size="16"><tspan x="720">Identificar y mostrar fragmentos</tspan><tspan x="720" dy="21">coincidentes si existen</tspan></text><path class="l" d="M720 703V735"/>
<rect class="n" x="540" y="735" width="360" height="62" rx="16"/><text x="720" y="773" text-anchor="middle" font-size="16">Presentar porcentaje de similitud</text><path class="l" d="M650 438H575V520"/><text x="584" y="428" font-size="14">No</text>
<rect class="n" x="540" y="820" width="360" height="62" rx="16"/><text x="720" y="858" text-anchor="middle" font-size="15">Mostrar validación y volver al formulario</text>
<!-- External branch -->
<rect class="n" x="1045" y="285" width="360" height="68" rx="16"/><text x="1225" y="313" text-anchor="middle" font-size="16"><tspan x="1225">Seleccionar proyecto activo</tspan><tspan x="1225" dy="21">con texto analizable</tspan></text><path class="l" d="M1225 353V390"/>
<rect class="n" x="1045" y="390" width="360" height="75" rx="16"/><text x="1225" y="419" text-anchor="middle" font-size="16"><tspan x="1225">Buscar por título en</tspan><tspan x="1225" dy="21">Crossref y OpenAlex</tspan></text><path class="l" d="M1225 465V505"/>
<rect class="n" x="1045" y="505" width="360" height="75" rx="16"/><text x="1225" y="534" text-anchor="middle" font-size="16"><tspan x="1225">Comparar con título y resumen</tspan><tspan x="1225" dy="21">si está disponible</tspan></text><path class="l" d="M1225 580V625"/>
<path class="n" d="M1225 625L1295 673L1225 721L1155 673Z"/><text x="1225" y="668" text-anchor="middle" font-size="14"><tspan x="1225">¿Responde cada</tspan><tspan x="1225" dy="18">fuente?</tspan></text><path class="l" d="M1295 673H1365V735"/><text x="1310" y="663" font-size="14">Sí / parcial</text>
<rect class="n" x="1045" y="735" width="360" height="62" rx="16"/><text x="1225" y="773" text-anchor="middle" font-size="16">Ordenar resultados por similitud</text><path class="l" d="M1155 673H1080V735"/><text x="1085" y="663" font-size="14">No</text>
<rect class="n" x="1045" y="820" width="360" height="62" rx="16"/><text x="1225" y="858" text-anchor="middle" font-size="15">Indicar fuente no disponible / sin resultados</text>
<circle class="end" cx="720" cy="1000" r="14"/><circle class="start" cx="720" cy="1000" r="7"/><path class="l" d="M395 766C430 930 520 1000 702 1000M900 766C860 930 820 1000 738 1000M1405 766C1110 945 930 1000 738 1000"/>
</svg>
'@

$activitySvg = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1440" height="1140" viewBox="0 0 1440 1140">
<defs><marker id="a" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.n{fill:#fff;stroke:#111;stroke-width:2}.l{fill:none;stroke:#111;stroke-width:2;marker-end:url(#a)}.start{fill:#111}.end{fill:#fff;stroke:#111;stroke-width:3}</style></defs>
<circle class="start" cx="720" cy="35" r="12"/><path class="l" d="M720 47V75"/><rect class="n" x="530" y="75" width="380" height="55" rx="18"/><text x="720" y="109" text-anchor="middle" font-size="18">Abrir la función de comparación</text><path class="l" d="M720 130V160"/><path class="n" d="M720 160L820 215L720 270L620 215Z"/><text x="720" y="210" text-anchor="middle" font-size="16"><tspan x="720">¿Qué desea</tspan><tspan x="720" dy="20">comparar?</tspan></text>
<path class="l" d="M620 215H215V275"/><text x="405" y="204" font-size="15">Repositorio</text><path class="l" d="M720 270V275"/><text x="735" y="263" font-size="15">Dos proyectos</text><path class="l" d="M820 215H1225V275"/><text x="1030" y="204" font-size="15">Publicaciones externas</text>
<!-- Rama: repositorio institucional -->
<rect class="n" x="35" y="275" width="360" height="65" rx="16"/><text x="215" y="302" text-anchor="middle" font-size="16"><tspan x="215">Seleccionar proyecto activo</tspan><tspan x="215" dy="21">con texto analizable</tspan></text><path class="l" d="M215 340V375"/><rect class="n" x="35" y="375" width="360" height="65" rx="16"/><text x="215" y="402" text-anchor="middle" font-size="16"><tspan x="215">Buscar proyectos de la misma</tspan><tspan x="215" dy="21">carrera y modalidad</tspan></text><path class="l" d="M215 440V460"/><path class="n" d="M215 460L275 500L215 540L155 500Z"/><text x="215" y="505" text-anchor="middle" font-size="14">¿Hay candidatos?</text><path class="l" d="M275 500H330V570"/><text x="291" y="490" font-size="14">Sí</text><path class="l" d="M155 500H15V570H35"/><text x="74" y="490" font-size="14">No</text><rect class="n" x="35" y="570" width="360" height="65" rx="16"/><text x="215" y="597" text-anchor="middle" font-size="16"><tspan x="215">Calcular y guardar porcentajes</tspan><tspan x="215" dy="21">de similitud</tspan></text><path class="l" d="M215 635V675"/><rect class="n" x="35" y="675" width="360" height="60" rx="16"/><text x="215" y="712" text-anchor="middle" font-size="16">Ordenar resultados por similitud</text><rect class="n" x="35" y="570" width="360" height="65" rx="16"/><text x="215" y="597" text-anchor="middle" font-size="15"><tspan x="215">Informar que no hay proyectos</tspan><tspan x="215" dy="21">comparables</tspan></text>
<!-- Rama: comparación directa -->
<rect class="n" x="540" y="275" width="360" height="65" rx="16"/><text x="720" y="302" text-anchor="middle" font-size="16"><tspan x="720">Seleccionar proyecto A</tspan><tspan x="720" dy="21">y proyecto B</tspan></text><path class="l" d="M720 340V370"/><path class="n" d="M720 370L790 415L720 460L650 415Z"/><text x="720" y="410" text-anchor="middle" font-size="13"><tspan x="720">¿Activos, con texto</tspan><tspan x="720" dy="17">y misma modalidad?</tspan></text><path class="l" d="M790 415H860V485"/><text x="805" y="405" font-size="14">Sí</text><path class="l" d="M650 415H575V485"/><text x="586" y="405" font-size="14">No</text><rect class="n" x="540" y="485" width="360" height="65" rx="16"/><text x="720" y="512" text-anchor="middle" font-size="16"><tspan x="720">Extraer secciones y calcular</tspan><tspan x="720" dy="21">la similitud textual</tspan></text><path class="l" d="M720 550V585"/><rect class="n" x="540" y="585" width="360" height="65" rx="16"/><text x="720" y="612" text-anchor="middle" font-size="16"><tspan x="720">Identificar los fragmentos</tspan><tspan x="720" dy="21">coincidentes</tspan></text><path class="l" d="M720 650V685"/><rect class="n" x="540" y="685" width="360" height="60" rx="16"/><text x="720" y="722" text-anchor="middle" font-size="16">Mostrar porcentaje y coincidencias</text><rect class="n" x="540" y="485" width="360" height="65" rx="16"/><text x="720" y="512" text-anchor="middle" font-size="15"><tspan x="720">Mostrar validación y regresar</tspan><tspan x="720" dy="21">al formulario</tspan></text>
<!-- Rama: publicaciones externas -->
<rect class="n" x="1045" y="275" width="360" height="65" rx="16"/><text x="1225" y="302" text-anchor="middle" font-size="16"><tspan x="1225">Seleccionar proyecto activo</tspan><tspan x="1225" dy="21">con texto analizable</tspan></text><path class="l" d="M1225 340V375"/><rect class="n" x="1045" y="375" width="360" height="65" rx="16"/><text x="1225" y="402" text-anchor="middle" font-size="16"><tspan x="1225">Consultar publicaciones en</tspan><tspan x="1225" dy="21">Crossref y OpenAlex</tspan></text><path class="l" d="M1225 440V475"/><rect class="n" x="1045" y="475" width="360" height="65" rx="16"/><text x="1225" y="502" text-anchor="middle" font-size="16"><tspan x="1225">Comparar título y resumen</tspan><tspan x="1225" dy="21">cuando esté disponible</tspan></text><path class="l" d="M1225 540V560"/><path class="n" d="M1225 560L1295 605L1225 650L1155 605Z"/><text x="1225" y="600" text-anchor="middle" font-size="14"><tspan x="1225">¿Hay resultados</tspan><tspan x="1225" dy="17">disponibles?</tspan></text><path class="l" d="M1295 605H1365V675"/><text x="1310" y="595" font-size="14">Sí / parcial</text><path class="l" d="M1155 605H1080V775"/><text x="1087" y="595" font-size="14">No</text><rect class="n" x="1045" y="675" width="360" height="65" rx="16"/><text x="1225" y="702" text-anchor="middle" font-size="15"><tspan x="1225">Ordenar resultados e indicar</tspan><tspan x="1225" dy="21">si alguna fuente no responde</tspan></text><rect class="n" x="1045" y="775" width="360" height="60" rx="16"/><text x="1225" y="812" text-anchor="middle" font-size="15">Informar que no hay resultados</text>
<circle class="end" cx="720" cy="1050" r="14"/><circle class="start" cx="720" cy="1050" r="7"/><path class="l" d="M395 705C455 900 570 1010 702 1050M395 605C450 820 580 980 702 1050M900 715C850 890 800 1000 738 1050M900 517C950 790 850 970 738 1050M1405 707C1210 900 930 1000 738 1050M1405 805C1200 960 920 1015 738 1050"/>
</svg>
'@
$activitySvg = $activitySvg.Replace('M155 500H15V570H35','M155 500H15V775H35')
$activitySvg = $activitySvg.Replace('<rect class="n" x="35" y="570" width="360" height="65" rx="16"/><text x="215" y="597" text-anchor="middle" font-size="15"><tspan x="215">Informar que no hay proyectos</tspan><tspan x="215" dy="21">comparables</tspan></text>','<rect class="n" x="35" y="775" width="360" height="60" rx="16"/><text x="215" y="812" text-anchor="middle" font-size="15">Informar que no hay proyectos comparables</text>')
$activitySvg = $activitySvg.Replace('M650 415H575V485','M650 415H515V785H540')
$activitySvg = $activitySvg.Replace('<rect class="n" x="540" y="485" width="360" height="65" rx="16"/><text x="720" y="512" text-anchor="middle" font-size="15"><tspan x="720">Mostrar validación y regresar</tspan><tspan x="720" dy="21">al formulario</tspan></text>','<rect class="n" x="540" y="775" width="360" height="60" rx="16"/><text x="720" y="800" text-anchor="middle" font-size="15"><tspan x="720">Mostrar validación</tspan><tspan x="720" dy="19">y volver al formulario</tspan></text>')
$activitySvg = $activitySvg.Replace('¿Activos, con texto</tspan><tspan x="720" dy="17">y misma modalidad?','¿Activos, con texto</tspan><tspan x="720" dy="17">y modalidad compatible?')
$activitySvg = $activitySvg.Replace('M395 605C450 820 580 980 702 1050','M395 805C450 900 580 1000 702 1050')
$activitySvg = $activitySvg.Replace('M900 517C950 790 850 970 738 1050','M900 807C950 900 850 1000 738 1050')
$activitySvg = $activitySvg.Replace('M1155 605H1080V775','M1155 605H1025V805H1045')

$mockupSvg = @'
<svg xmlns="http://www.w3.org/2000/svg" width="1100" height="650" viewBox="0 0 1100 650">
<defs><style>text{font-family:Arial,sans-serif;fill:#111}.b{fill:#fff;stroke:#111;stroke-width:1.6}</style></defs><rect x="12" y="12" width="1076" height="626" fill="#fff" stroke="#111" stroke-width="2"/>
<text x="42" y="58" font-size="26" font-weight="700">Comparación</text><line x1="40" y1="78" x2="1060" y2="78" stroke="#111"/>
<text x="45" y="115" font-size="16" font-weight="700">Tipo</text>
<rect class="b" x="45" y="130" width="240" height="48"/><text x="165" y="160" text-anchor="middle" font-size="15">Repositorio interno</text>
<rect class="b" x="300" y="130" width="240" height="48"/><text x="420" y="160" text-anchor="middle" font-size="15">Dos proyectos</text>
<rect class="b" x="555" y="130" width="280" height="48"/><text x="695" y="160" text-anchor="middle" font-size="15">Crossref y OpenAlex</text>
<text x="45" y="218" font-size="15">Proyecto a comparar</text><rect class="b" x="45" y="228" width="490" height="42"/><text x="58" y="254" font-size="14">Seleccionar proyecto</text>
<text x="555" y="218" font-size="15">Segundo proyecto</text><rect class="b" x="555" y="228" width="490" height="42"/><text x="568" y="254" font-size="14">Seleccionar cuando corresponda</text>
<rect class="b" x="45" y="292" width="150" height="42"/><text x="120" y="319" text-anchor="middle" font-size="15">Comparar</text>
<line x1="40" y1="356" x2="1060" y2="356" stroke="#888"/><text x="45" y="391" font-size="18" font-weight="700">Resultados</text>
<rect class="b" x="45" y="408" width="1010" height="170"/><line x1="45" y1="450" x2="1055" y2="450" stroke="#777"/><line x1="430" y1="408" x2="430" y2="578" stroke="#777"/><line x1="700" y1="408" x2="700" y2="578" stroke="#777"/>
<text x="58" y="436" font-size="15" font-weight="700">Proyecto o publicación</text><text x="448" y="436" font-size="15" font-weight="700">Fuente</text><text x="720" y="436" font-size="15" font-weight="700">Similitud</text>
<line x1="45" y1="492" x2="1055" y2="492" stroke="#aaa"/><line x1="45" y1="535" x2="1055" y2="535" stroke="#aaa"/>
<text x="45" y="610" font-size="14">Ver coincidencias</text>
</svg>
'@

$useCaseSvg = $useCaseSvg.Replace('<text x="105" y="680" text-anchor="middle" font-size="17">Administrador / Gestor</text>','<text x="105" y="665" text-anchor="middle" font-size="20"><tspan x="105">Administrador</tspan><tspan x="105" dy="22">/ Gestor</tspan></text>')
$useCaseSvg = $useCaseSvg.Replace('font-size="17">Crossref API','font-size="18">Crossref API').Replace('font-size="17">OpenAlex API','font-size="18">OpenAlex API')
$useCaseSvg = $useCaseSvg.Replace('font-size="17"','font-size="28"').Replace('font-size="15"','font-size="24"').Replace('font-size="13"','font-size="20"')
$useCaseSvg = [regex]::Replace($useCaseSvg, '(?m)^<circle class="actor" cx="1490"[^>]*>.*?</text>\r?\n?', '')
$useCaseSvg = $useCaseSvg.Replace('<text x="1060" y="1080" text-anchor="middle" font-size="24"><tspan x="1060">Comparar título y resumen disponible;</tspan><tspan x="1060" dy="20">ordenar resultados por similitud</tspan></text>','<text x="1060" y="1075" text-anchor="middle" font-size="24"><tspan x="1060">Comparar título y resumen</tspan><tspan x="1060" dy="24">disponible; ordenar resultados</tspan></text>')

$useCaseSvg = $useCaseSvg.Replace('width="1600" height="1200" viewBox="0 0 1600 1200"','width="1370" height="1200" viewBox="0 0 1370 1200"')
$useCaseSvg = $useCaseSvg.Replace('<circle class="actor" cx="1490" cy="865" r="18"/><path class="actor" d="M1490 883v58M1458 904h64M1490 941l-25 40M1490 941l25 40"/><text x="1490" y="1000" text-anchor="middle" font-size="17">Crossref API</text>','')
$useCaseSvg = $useCaseSvg.Replace('<circle class="actor" cx="1490" cy="1035" r="18"/><path class="actor" d="M1490 1053v58M1458 1074h64M1490 1111l-25 40M1490 1111l25 40"/><text x="1490" y="1170" text-anchor="middle" font-size="17">OpenAlex API</text>','')
$useCaseSvg = $useCaseSvg.Replace('<path class="assoc" d="M1458 904L1280 885M1458 1074L1280 985"/>','')
$useCaseSvg = $useCaseSvg.Replace('<ellipse class="use" cx="1060" cy="885" rx="220" ry="43"/><text x="1060" y="890" text-anchor="middle" font-size="24">Buscar publicaciones en Crossref</text>','<ellipse class="use" cx="1060" cy="885" rx="220" ry="43"/><text x="1060" y="878" text-anchor="middle" font-size="24"><tspan x="1060">Consultar publicaciones</tspan><tspan x="1060" dy="28">en fuentes externas</tspan></text>')
$useCaseSvg = $useCaseSvg.Replace('<ellipse class="use" cx="1060" cy="985" rx="220" ry="43"/><text x="1060" y="990" text-anchor="middle" font-size="24">Buscar publicaciones en OpenAlex</text>','')
$useCaseSvg = $useCaseSvg.Replace('<ellipse class="use" cx="1060" cy="1085" rx="220" ry="43"/><text x="1060" y="1075" text-anchor="middle" font-size="24"><tspan x="1060">Comparar título y resumen</tspan><tspan x="1060" dy="24">disponible; ordenar resultados</tspan></text>','<ellipse class="use" cx="1060" cy="985" rx="220" ry="43"/><text x="1060" y="975" text-anchor="middle" font-size="24"><tspan x="1060">Comparar título y resumen</tspan><tspan x="1060" dy="24">disponible; ordenar resultados</tspan></text>')
$useCaseSvg = $useCaseSvg.Replace('<path class="inc" d="M680 900L840 1085"/><text x="740" y="1010" font-size="20">«include»</text>','')

$activitySvg = $activitySvg.Replace('<circle class="end" cx="720" cy="1050" r="14"/><circle class="start" cx="720" cy="1050" r="7"/><path class="l" d="M395 705C455 900 570 1010 702 1050M395 805C450 900 580 1000 702 1050M900 715C850 890 800 1000 738 1050M900 807C950 900 850 1000 738 1050M1405 707C1210 900 930 1000 738 1050M1405 805C1200 960 920 1015 738 1050"/>','<path class="l" d="M395 735H15V900H190M215 835V875M720 745H920V900H750M900 805H960V900H750M1225 740H1430V900H1250M1225 835V875"/><path class="n" d="M215 875L240 900L215 925L190 900Z M720 875L745 900L720 925L695 900Z M1225 875L1250 900L1225 925L1200 900Z"/><path class="l" d="M215 925Q360 1030 702 1050M720 925V1036M1225 925Q1080 1030 738 1050"/><circle class="end" cx="720" cy="1050" r="14"/><circle class="start" cx="720" cy="1050" r="7"/>')
$activitySvg = $activitySvg.Replace('<path class="l" d="M395 735H15V900H190M215 835V875M720 745H920V900H750M900 805H960V900H750M1225 740H1430V900H1250M1225 835V875"/>','<path class="l" d="M395 735H15V900H190"/><path class="l" d="M215 835V875"/><path class="l" d="M720 745H920V900H750"/><path class="l" d="M900 805H960V900H750"/><path class="l" d="M1225 740H1430V900H1250"/><path class="l" d="M1225 835V875"/>')
$activitySvg = $activitySvg.Replace('M395 735H15V900H190','M215 735H430V900H240')
$activitySvg = $activitySvg.Replace('<path class="l" d="M215 925Q360 1030 702 1050M720 925V1036M1225 925Q1080 1030 738 1050"/>','<path class="l" d="M215 925Q360 1030 702 1050"/><path class="l" d="M720 925V1036"/><path class="l" d="M1225 925Q1080 1030 738 1050"/>')

$useCaseSvg = Get-Content -Raw -Encoding UTF8 (Join-Path $carpeta 'hu09-caso-uso.svg')
$sequenceSvg = Get-Content -Raw -Encoding UTF8 (Join-Path $carpeta 'hu09-secuencia.svg')
$activitySvg = Get-Content -Raw -Encoding UTF8 (Join-Path $carpeta 'hu09-actividad.svg')

$figuras = @(
    @{ Nombre='hu09-caso-uso.svg'; Svg=$useCaseSvg; Leyenda='Figura 44. Caso de uso de comparación interna con actores Administrador y Gestor. Fuente: elaboración propia.' },
    @{ Nombre='hu09-secuencia.svg'; Svg=$sequenceSvg; Leyenda='Figura 45. Secuencia de comparación con el repositorio interno y entre dos proyectos. Fuente: elaboración propia.' },
    @{ Nombre='hu09-actividad.svg'; Svg=$activitySvg; Leyenda='Figura 46. Actividades de comparación con el repositorio interno y entre dos proyectos. Fuente: elaboración propia.' },
    @{ Nombre='hu09-mockup.svg'; Svg=$mockupSvg; Leyenda='Figura 47. Mockup monocromático de la función de comparación. Fuente: elaboración propia.' }
)
$pngs = @(); foreach($figura in $figuras){$pngs += ,(Guardar-Figura $figura.Nombre $figura.Svg)}

$wNs='http://schemas.openxmlformats.org/wordprocessingml/2006/main';$rNs='http://schemas.openxmlformats.org/officeDocument/2006/relationships';$relNs='http://schemas.openxmlformats.org/package/2006/relationships';$ctNs='http://schemas.openxmlformats.org/package/2006/content-types';$wpNs='http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing';$aNs='http://schemas.openxmlformats.org/drawingml/2006/main';$picNs='http://schemas.openxmlformats.org/drawingml/2006/picture'
$script:docXml=New-Object System.Xml.XmlDocument;$script:docXml.PreserveWhitespace=$true
$root=$script:docXml.CreateElement('w','document',$wNs);foreach($pair in @(@('w',$wNs),@('r',$rNs))){$attr=$script:docXml.CreateAttribute('xmlns',$pair[0],'http://www.w3.org/2000/xmlns/');$attr.Value=$pair[1];[void]$root.Attributes.Append($attr)};[void]$script:docXml.AppendChild($root);$script:body=$script:docXml.CreateElement('w','body',$wNs);[void]$root.AppendChild($script:body)
$script:rels=New-Object System.Xml.XmlDocument;$relsRoot=$script:rels.CreateElement('Relationships',$relNs);$relsRoot.SetAttribute('xmlns',$relNs);[void]$script:rels.AppendChild($relsRoot);$styleRel=$script:rels.CreateElement('Relationship',$relNs);$styleRel.SetAttribute('Id','rId1');$styleRel.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles');$styleRel.SetAttribute('Target','styles.xml');[void]$relsRoot.AppendChild($styleRel)
$script:relNum=1;$script:figNum=43
function Node([System.Xml.XmlDocument]$xml,[System.Xml.XmlNode]$parent,[string]$prefix,[string]$name,[string]$ns){$node=$xml.CreateElement($prefix,$name,$ns);[void]$parent.AppendChild($node);return ,$node}
function WAttr([System.Xml.XmlDocument]$xml,[System.Xml.XmlElement]$el,[string]$prefix,[string]$name,[string]$ns,[string]$value){$a=$xml.CreateAttribute($prefix,$name,$ns);$a.Value=$value;[void]$el.Attributes.Append($a)}
function PlainAttr([System.Xml.XmlElement]$el,[string]$name,[string]$value){$el.SetAttribute($name,$value)}
function Add-Run([System.Xml.XmlElement]$parent,[string]$text,[switch]$bold,[int]$size=22){$run=Node $script:docXml $parent 'w' 'r' $wNs;if($bold -or $size -ne 22){$rp=Node $script:docXml $run 'w' 'rPr' $wNs;if($bold){[void](Node $script:docXml $rp 'w' 'b' $wNs)};if($size -ne 22){$sz=Node $script:docXml $rp 'w' 'sz' $wNs;WAttr $script:docXml $sz 'w' 'val' $wNs ([string]$size)}};$t=Node $script:docXml $run 'w' 't' $wNs;[void]$t.SetAttribute('space','http://www.w3.org/XML/1998/namespace','preserve');$t.InnerText=$text}
function Add-Para([string]$text,[string]$style='Normal',[switch]$pageBreak,[switch]$center){$p=Node $script:docXml $script:body 'w' 'p' $wNs;$pr=Node $script:docXml $p 'w' 'pPr' $wNs;$ps=Node $script:docXml $pr 'w' 'pStyle' $wNs;WAttr $script:docXml $ps 'w' 'val' $wNs $style;if($pageBreak){[void](Node $script:docXml $pr 'w' 'pageBreakBefore' $wNs)};if($center){$jc=Node $script:docXml $pr 'w' 'jc' $wNs;WAttr $script:docXml $jc 'w' 'val' $wNs 'center'};Add-Run $p $text}
function Add-Table([string[]]$headers,[object[]]$rows){$tbl=Node $script:docXml $script:body 'w' 'tbl' $wNs;$pr=Node $script:docXml $tbl 'w' 'tblPr' $wNs;$tw=Node $script:docXml $pr 'w' 'tblW' $wNs;WAttr $script:docXml $tw 'w' 'w' $wNs '9360';WAttr $script:docXml $tw 'w' 'type' $wNs 'dxa';$borders=Node $script:docXml $pr 'w' 'tblBorders' $wNs;foreach($side in @('top','left','bottom','right','insideH','insideV')){$b=Node $script:docXml $borders 'w' $side $wNs;WAttr $script:docXml $b 'w' 'val' $wNs 'single';WAttr $script:docXml $b 'w' 'sz' $wNs '5';WAttr $script:docXml $b 'w' 'color' $wNs 'B7C5BE'};$layout=Node $script:docXml $pr 'w' 'tblLayout' $wNs;WAttr $script:docXml $layout 'w' 'type' $wNs 'fixed';$mar=Node $script:docXml $pr 'w' 'tblCellMar' $wNs;foreach($side in @('top','start','bottom','end')){$m=Node $script:docXml $mar 'w' $side $wNs;WAttr $script:docXml $m 'w' 'w' $wNs '90';WAttr $script:docXml $m 'w' 'type' $wNs 'dxa'};$grid=Node $script:docXml $tbl 'w' 'tblGrid' $wNs;$cw=[int](9360/$headers.Count);for($i=0;$i -lt $headers.Count;$i++){$col=Node $script:docXml $grid 'w' 'gridCol' $wNs;WAttr $script:docXml $col 'w' 'w' $wNs ([string]$cw)};$allRows=[System.Collections.Generic.List[object]]::new();$allRows.Add([object]$headers);foreach($row in $rows){$allRows.Add([object]$row)};for($ri=0;$ri -lt $allRows.Count;$ri++){$tr=Node $script:docXml $tbl 'w' 'tr' $wNs;if($ri -eq 0){$trpr=Node $script:docXml $tr 'w' 'trPr' $wNs;[void](Node $script:docXml $trpr 'w' 'tblHeader' $wNs)};for($ci=0;$ci -lt $headers.Count;$ci++){$tc=Node $script:docXml $tr 'w' 'tc' $wNs;$tcpr=Node $script:docXml $tc 'w' 'tcPr' $wNs;$tcw=Node $script:docXml $tcpr 'w' 'tcW' $wNs;WAttr $script:docXml $tcw 'w' 'w' $wNs ([string]$cw);WAttr $script:docXml $tcw 'w' 'type' $wNs 'dxa';if($ri -eq 0){$sh=Node $script:docXml $tcpr 'w' 'shd' $wNs;WAttr $script:docXml $sh 'w' 'fill' $wNs 'E5EEE9'};$p=Node $script:docXml $tc 'w' 'p' $wNs;Add-Run $p ([string]$allRows[$ri][$ci]) -bold:($ri -eq 0) -size 18}};[void](Node $script:docXml $script:body 'w' 'p' $wNs)}
function Add-Table([string[]]$headers,[object[]]$rows,[int[]]$widths) {
    $tableWidth = 9360
    if (-not $widths -or $widths.Count -ne $headers.Count) {
        $baseWidth = [int][Math]::Floor($tableWidth / $headers.Count)
        $widths = @(); for ($i = 0; $i -lt $headers.Count; $i++) { $widths += $baseWidth }
        $widths[$widths.Count - 1] += $tableWidth - (($widths | Measure-Object -Sum).Sum)
    }
    if (($widths | Measure-Object -Sum).Sum -ne $tableWidth) { throw 'El ancho de columnas de la tabla debe sumar 9360.' }
    $tbl=Node $script:docXml $script:body 'w' 'tbl' $wNs
    $pr=Node $script:docXml $tbl 'w' 'tblPr' $wNs
    $tw=Node $script:docXml $pr 'w' 'tblW' $wNs; WAttr $script:docXml $tw 'w' 'w' $wNs ([string]$tableWidth); WAttr $script:docXml $tw 'w' 'type' $wNs 'dxa'
    $borders=Node $script:docXml $pr 'w' 'tblBorders' $wNs
    foreach($side in @('top','left','bottom','right','insideH','insideV')){$b=Node $script:docXml $borders 'w' $side $wNs;WAttr $script:docXml $b 'w' 'val' $wNs 'single';WAttr $script:docXml $b 'w' 'sz' $wNs '5';WAttr $script:docXml $b 'w' 'color' $wNs 'B7C5BE'}
    $layout=Node $script:docXml $pr 'w' 'tblLayout' $wNs;WAttr $script:docXml $layout 'w' 'type' $wNs 'fixed'
    $mar=Node $script:docXml $pr 'w' 'tblCellMar' $wNs
    foreach($side in @('top','start','bottom','end')){$m=Node $script:docXml $mar 'w' $side $wNs;WAttr $script:docXml $m 'w' 'w' $wNs '90';WAttr $script:docXml $m 'w' 'type' $wNs 'dxa'}
    $grid=Node $script:docXml $tbl 'w' 'tblGrid' $wNs
    for($i=0;$i -lt $headers.Count;$i++){$col=Node $script:docXml $grid 'w' 'gridCol' $wNs;WAttr $script:docXml $col 'w' 'w' $wNs ([string]$widths[$i])}
    $allRows=[System.Collections.Generic.List[object]]::new();$allRows.Add([object]$headers);foreach($row in $rows){$allRows.Add([object]$row)}
    for($ri=0;$ri -lt $allRows.Count;$ri++) {
        $tr=Node $script:docXml $tbl 'w' 'tr' $wNs
        if($ri -eq 0){$trpr=Node $script:docXml $tr 'w' 'trPr' $wNs;[void](Node $script:docXml $trpr 'w' 'tblHeader' $wNs)}
        for($ci=0;$ci -lt $headers.Count;$ci++) {
            $tc=Node $script:docXml $tr 'w' 'tc' $wNs;$tcpr=Node $script:docXml $tc 'w' 'tcPr' $wNs;$tcw=Node $script:docXml $tcpr 'w' 'tcW' $wNs
            WAttr $script:docXml $tcw 'w' 'w' $wNs ([string]$widths[$ci]);WAttr $script:docXml $tcw 'w' 'type' $wNs 'dxa'
            if($ri -eq 0){$sh=Node $script:docXml $tcpr 'w' 'shd' $wNs;WAttr $script:docXml $sh 'w' 'fill' $wNs 'E5EEE9'}
            $p=Node $script:docXml $tc 'w' 'p' $wNs;Add-Run $p ([string]$allRows[$ri][$ci]) -bold:($ri -eq 0) -size 18
        }
    }
    [void](Node $script:docXml $script:body 'w' 'p' $wNs)
}
function Add-Figure([string]$pngPath,[string]$caption){$script:figNum++;$script:relNum++;$rid='rId'+$script:relNum;$media='figura-'+$script:figNum+'.png';[System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($script:zip,$pngPath,('word/media/'+$media),[System.IO.Compression.CompressionLevel]::Optimal)|Out-Null;$rel=$script:rels.CreateElement('Relationship',$relNs);$rel.SetAttribute('Id',$rid);$rel.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');$rel.SetAttribute('Target','media/'+$media);[void]$script:rels.DocumentElement.AppendChild($rel);$img=[System.Drawing.Image]::FromFile($pngPath);try{$cx0=$img.Width*9525.0;$cy0=$img.Height*9525.0}finally{$img.Dispose()};$scale=[Math]::Min(5486400/$cx0,4114800/$cy0);$cx=[string][int]($cx0*$scale);$cy=[string][int]($cy0*$scale);$p=Node $script:docXml $script:body 'w' 'p' $wNs;$pr=Node $script:docXml $p 'w' 'pPr' $wNs;$jc=Node $script:docXml $pr 'w' 'jc' $wNs;WAttr $script:docXml $jc 'w' 'val' $wNs 'center';$run=Node $script:docXml $p 'w' 'r' $wNs;$drawing=Node $script:docXml $run 'w' 'drawing' $wNs;$in=Node $script:docXml $drawing 'wp' 'inline' $wpNs;foreach($a in @(@('distT','0'),@('distB','0'),@('distL','0'),@('distR','0'))){PlainAttr $in $a[0] $a[1]};$ex=Node $script:docXml $in 'wp' 'extent' $wpNs;PlainAttr $ex 'cx' $cx;PlainAttr $ex 'cy' $cy;$dp=Node $script:docXml $in 'wp' 'docPr' $wpNs;PlainAttr $dp 'id' ([string]$script:figNum);PlainAttr $dp 'name' $media;PlainAttr $dp 'descr' $caption;$frame=Node $script:docXml $in 'wp' 'cNvGraphicFramePr' $wpNs;$lock=Node $script:docXml $frame 'a' 'graphicFrameLocks' $aNs;PlainAttr $lock 'noChangeAspect' '1';$graphic=Node $script:docXml $in 'a' 'graphic' $aNs;$gd=Node $script:docXml $graphic 'a' 'graphicData' $aNs;PlainAttr $gd 'uri' $picNs;$pic=Node $script:docXml $gd 'pic' 'pic' $picNs;$nv=Node $script:docXml $pic 'pic' 'nvPicPr' $picNs;$cnv=Node $script:docXml $nv 'pic' 'cNvPr' $picNs;PlainAttr $cnv 'id' ([string]$script:figNum);PlainAttr $cnv 'name' $media;[void](Node $script:docXml $nv 'pic' 'cNvPicPr' $picNs);$fill=Node $script:docXml $pic 'pic' 'blipFill' $picNs;$blip=Node $script:docXml $fill 'a' 'blip' $aNs;WAttr $script:docXml $blip 'r' 'embed' $rNs $rid;$stretch=Node $script:docXml $fill 'a' 'stretch' $aNs;[void](Node $script:docXml $stretch 'a' 'fillRect' $aNs);$sp=Node $script:docXml $pic 'pic' 'spPr' $picNs;$xf=Node $script:docXml $sp 'a' 'xfrm' $aNs;$off=Node $script:docXml $xf 'a' 'off' $aNs;PlainAttr $off 'x' '0';PlainAttr $off 'y' '0';$ext=Node $script:docXml $xf 'a' 'ext' $aNs;PlainAttr $ext 'cx' $cx;PlainAttr $ext 'cy' $cy;$geom=Node $script:docXml $sp 'a' 'prstGeom' $aNs;PlainAttr $geom 'prst' 'rect';[void](Node $script:docXml $geom 'a' 'avLst' $aNs);Add-Para $caption 'Caption' -center}
function XmlText([System.Xml.XmlDocument]$xml){$settings=[System.Xml.XmlWriterSettings]::new();$settings.Encoding=[System.Text.UTF8Encoding]::new($false);$settings.OmitXmlDeclaration=$true;$settings.Indent=$false;$sb=[System.Text.StringBuilder]::new();$writer=[System.Xml.XmlWriter]::Create($sb,$settings);$xml.Save($writer);$writer.Dispose();return $sb.ToString()}
function ZipText([System.IO.Compression.ZipArchive]$zip,[string]$name,[string]$text){$entry=$zip.CreateEntry($name,[System.IO.Compression.CompressionLevel]::Optimal);$stream=$entry.Open();$writer=[System.IO.StreamWriter]::new($stream,[System.Text.UTF8Encoding]::new($false));$writer.Write($text);$writer.Dispose();$stream.Dispose()}
function StylesXml {$x=New-Object System.Xml.XmlDocument;$root=$x.CreateElement('w','styles',$wNs);$xmlns=$x.CreateAttribute('xmlns','w','http://www.w3.org/2000/xmlns/');$xmlns.Value=$wNs;[void]$root.Attributes.Append($xmlns);[void]$x.AppendChild($root);$defaults=Node $x $root 'w' 'docDefaults' $wNs;$rpd=Node $x $defaults 'w' 'rPrDefault' $wNs;$rp=Node $x $rpd 'w' 'rPr' $wNs;$font=Node $x $rp 'w' 'rFonts' $wNs;WAttr $x $font 'w' 'ascii' $wNs 'Arial';WAttr $x $font 'w' 'hAnsi' $wNs 'Arial';$sz=Node $x $rp 'w' 'sz' $wNs;WAttr $x $sz 'w' 'val' $wNs '22';foreach($st in @(@('Normal','Normal','22',$false),@('Heading1','heading 1','30',$true),@('Heading2','heading 2','26',$true),@('Heading3','heading 3','22',$true),@('Caption','caption','18',$false))){$s=Node $x $root 'w' 'style' $wNs;WAttr $x $s 'w' 'type' $wNs 'paragraph';WAttr $x $s 'w' 'styleId' $wNs $st[0];$n=Node $x $s 'w' 'name' $wNs;WAttr $x $n 'w' 'val' $wNs $st[1];if($st[0] -ne 'Normal'){$b=Node $x $s 'w' 'basedOn' $wNs;WAttr $x $b 'w' 'val' $wNs 'Normal'};if($st[0] -like 'Heading*'){$pp=Node $x $s 'w' 'pPr' $wNs;[void](Node $x $pp 'w' 'keepNext' $wNs)};$rr=Node $x $s 'w' 'rPr' $wNs;$ff=Node $x $rr 'w' 'rFonts' $wNs;WAttr $x $ff 'w' 'ascii' $wNs 'Arial';WAttr $x $ff 'w' 'hAnsi' $wNs 'Arial';if($st[3]){[void](Node $x $rr 'w' 'b' $wNs)};if($st[0] -eq 'Caption'){[void](Node $x $rr 'w' 'i' $wNs)};$z=Node $x $rr 'w' 'sz' $wNs;WAttr $x $z 'w' 'val' $wNs $st[2]};return (XmlText $x)}

if (Test-Path -LiteralPath $salida) { throw "Se conserva el documento existente y no se sobrescribe: $salida" }
$script:zip=[System.IO.Compression.ZipFile]::Open($salida,[System.IO.Compression.ZipArchiveMode]::Create)
try {
    Add-Para 'Sprint 3: Comparación de similitud' 'Heading1'
    Add-Para 'Objetivo del Sprint' 'Heading2'
    Add-Para 'Implementar la comparación de proyectos del instituto entre sí y con publicaciones académicas externas, presentando porcentajes y fragmentos semejantes para apoyar la revisión de posibles coincidencias. El resultado es orientativo y no constituye por sí solo una determinación de plagio.'
    Add-Para 'Sprint Backlog' 'Heading2'
    Add-Para 'Tabla 22. Sprint Backlog' 'Caption'
    $backlogRows = @()
    $backlogRows += ,@('HU09','Comparar con el repositorio institucional, entre dos proyectos y con publicaciones de Crossref y OpenAlex; presentar puntajes y coincidencias.','SimilitudServiceTest, SeccionesDocumentoServiceTest, ProjectFilesTest y CrossrefSearchTest.','15*')
    Add-Table -headers @('Historia','Trabajo técnico','Comprobación disponible','Días') -rows $backlogRows -widths @(900,3250,4320,890)
    Add-Para '*Estimación de 15 días hábiles para el sprint planificado en tres semanas.'
    Add-Para 'HU09: Comparación de proyectos y publicaciones' 'Heading1' -pageBreak
    Add-Para 'Como Gestor, necesito comparar un proyecto con el repositorio del instituto, con otro proyecto o con publicaciones de Crossref y OpenAlex para identificar similitudes.'
    Add-Para 'Diagrama de Caso de Uso' 'Heading2'
    Add-Figure $pngs[0] $figuras[0].Leyenda
    Add-Para 'Especificación del caso de uso' 'Heading2'
    Add-Para 'Tabla 23. Especificación del caso de uso: comparación de similitud' 'Caption'
    Add-Table @('Elemento','Descripción') @(
        @('Actores','Administrador y Gestor con permisos para realizar comparaciones.'),
        @('Precondición','La persona inició sesión y tiene permiso de análisis. El proyecto está activo y tiene un PDF con texto extraído y secciones reconocidas. Para comparar dos proyectos, ambos deben estar activos, con texto analizable y ser de la misma modalidad.'),
        @('Postcondición','La aplicación presenta el porcentaje de similitud y los resultados asociados. La comparación con el repositorio queda registrada; los resultados se ordenan de mayor a menor. Cuando una fuente externa falla, se informa sin ocultar resultados válidos de la otra.'),
        @('Flujo básico: repositorio','1. Abrir el análisis del proyecto. 2. El sistema valida que esté activo y tenga contenido analizable. 3. Extrae resumen, introducción, desarrollo/propuesta y conclusiones. 4. Selecciona proyectos activos de la misma carrera y modalidad con texto disponible. 5. Calcula y guarda la similitud. 6. Presenta los porcentajes ordenados.'),
        @('Flujo alternativo: dos proyectos','El Gestor selecciona proyecto A y proyecto B. El sistema valida que sean distintos, estén activos, tengan texto analizable y compartan modalidad. Calcula el porcentaje y muestra los fragmentos semejantes que superan los criterios de coincidencia.'),
        @('Flujo alternativo: fuentes externas','El Gestor selecciona un proyecto para consultar publicaciones. Se busca por título en Crossref y OpenAlex; se comparan título y resumen cuando existe, o solo título si el resumen no está disponible. Se eliminan duplicados y se muestran los resultados ordenados con su fuente y metadatos.'),
        @('Criterio de análisis','Se comparan secuencias textuales ordenadas de cinco palabras. El análisis se limita a las secciones reconocidas; excluye portada, datos del tutor y marco teórico. El porcentaje representa coincidencia textual, no una conclusión automática de plagio.'),
        @('Excepciones','Sin texto o secciones reconocidas: se solicita revisar el PDF. Modalidad distinta en comparación de dos proyectos: se rechaza la operación. Sin proyectos compatibles o publicaciones: se informa que no hay resultados. Si Crossref u OpenAlex no responde, se indica qué fuente falló y se conservan los resultados disponibles.')
    )
    Add-Para 'Diagrama de secuencia' 'Heading2'
    Add-Figure $pngs[1] $figuras[1].Leyenda
    Add-Para 'Diagrama de Actividades' 'Heading2'
    Add-Figure $pngs[2] $figuras[2].Leyenda
    Add-Para 'Diseño de Mockup' 'Heading2'
    Add-Figure $pngs[3] $figuras[3].Leyenda
    Add-Para 'Diseño e implementaciones de interfaces' 'Heading1' -pageBreak
    Add-Para 'Comparación con repositorio institucional' 'Heading2'
    Add-Para 'ESPACIO PARA CAPTURA REAL: acción de analizar desde el catálogo de proyectos y resultados internos ordenados por similitud.' 'Heading3'
    Add-Para 'Insertar captura del sistema funcionando; ocultar datos personales.'
    Add-Para 'Comparación de dos proyectos' 'Heading2'
    Add-Para 'ESPACIO PARA CAPTURA REAL: selección de proyecto A y B, porcentaje y fragmentos coincidentes.' 'Heading3'
    Add-Para 'Insertar captura del sistema funcionando; ocultar datos personales.'
    Add-Para 'Comparación con Crossref y OpenAlex' 'Heading2'
    Add-Para 'ESPACIO PARA CAPTURA REAL: resultados externos con título de publicación, fuente, porcentaje y estado de conexión.' 'Heading3'
    Add-Para 'Insertar captura del sistema funcionando; ocultar datos personales.'
    Add-Para 'Pruebas de Testing' 'Heading1' -pageBreak
    Add-Para 'La comparación se verificó mediante pruebas unitarias de los servicios de extracción y similitud, y pruebas Feature de Laravel para las rutas, permisos y presentación de resultados. Las respuestas de Crossref y OpenAlex se simularon durante las pruebas para obtener resultados reproducibles; esto no mide la disponibilidad en vivo de las APIs.'
    Add-Para 'Se probaron las tres rutas de HU09: comparación contra el repositorio, comparación manual de dos proyectos y consulta de publicaciones externas. La ejecución seleccionada reportó 24 pruebas y 169 aserciones aprobadas, sin fallos.'
    Add-Para 'Herramientas utilizadas' 'Heading2'
    Add-Para 'PHPUnit 12.5.32 — ejecución de pruebas unitarias y Feature.'
    Add-Para 'Laravel Testing Framework — simulación de solicitudes, permisos y respuestas HTTP.'
    Add-Para 'SQLite en memoria y RefreshDatabase — aislamiento de los datos de prueba.'
    Add-Para 'Laravel HTTP Fake — simulación controlada de respuestas de Crossref y OpenAlex.'
    Add-Para 'Aserciones de Laravel/PHPUnit — verificación de puntajes, fragmentos, filtros y resultados.'
    Add-Para 'Tipos de prueba aplicados' 'Heading2'
    Add-Para 'Pruebas unitarias: verifican extracción de secciones, secuencias de cinco palabras, cálculo porcentual, selección de fragmentos y descarte de coincidencias insuficientes.'
    Add-Para 'Pruebas funcionales y de integración (enfoque de caja negra): verifican autenticación, selección de proyectos, filtros, comparación y resultados visibles. También se comprobaron permisos y compatibilidad de datos como controles puntuales; no se realizó una auditoría de penetración.'
    Add-Para 'Tabla 24. Resultados de las pruebas' 'Caption'
    Add-Table @('Caso / HU','Comprobaciones y resultado esperado','Tipo de prueba','Resultado') @(
        @('TC-09A / HU09 — Repositorio','Solo se comparan proyectos activos compatibles con texto. Se analizan secciones válidas, se excluyen contenido común no comparable y se muestran resultados ordenados.','Unitarias y Feature; funcional/integración.','Aprobado'),
        @('TC-09B / HU09 — Dos proyectos','Se valida la modalidad y el contenido de ambos documentos; se calcula el porcentaje y se muestran fragmentos coincidentes sin duplicarlos.','Unitarias y Feature; validación.','Aprobado'),
        @('TC-09C / HU09 — Fuentes externas','Se consultan Crossref y OpenAlex con respuestas simuladas, se normalizan publicaciones, comparan título/resumen y se informa la disponibilidad de las fuentes.','Feature e integración con HTTP Fake.','Aprobado')
    )
    Add-Para 'Tabla 25. Archivos utilizados para las pruebas' 'Caption'
    Add-Table @('Archivo','Escenarios utilizados') @(
        @('tests/Unit/SimilitudServiceTest.php','Cálculo del porcentaje, coincidencias por secuencias de cinco palabras, fragmentos y descarte de falsos positivos.'),
        @('tests/Unit/SeccionesDocumentoServiceTest.php','Extracción de secciones permitidas y exclusión del marco teórico y otras partes no comparables.'),
        @('tests/Feature/ProjectFilesTest.php','Comparación del repositorio y entre proyectos, selección compatible, resultados, fragmentos y permisos.'),
        @('tests/Feature/CrossrefSearchTest.php','Consulta autenticada y comparación de proyecto con publicaciones simuladas de Crossref y OpenAlex.')
    )
    Add-Para 'Evidencia' 'Heading2'
    Add-Para 'ESPACIO PARA CAPTURA REAL: salida del terminal con las 24 pruebas seleccionadas aprobadas.' 'Heading3'
    Add-Para 'Insertar captura de php artisan test --testdox con los casos del Sprint 3.'
    Add-Para 'Escenarios Cubiertos' 'Heading2'
    Add-Para '24 pruebas ejecutadas y aprobadas, con 169 aserciones en 2,24 segundos (02/11/2026).'
    Add-Para 'Repositorio: proyectos activos de la misma carrera y modalidad, con texto disponible; resultados ordenados.'
    Add-Para 'Comparación directa: dos proyectos con modalidad compatible, porcentaje y fragmentos semejantes.'
    Add-Para 'Fuentes externas: consulta de Crossref/OpenAlex, metadatos y resumen cuando está disponible, deduplicación y manejo de fuente no disponible.'
    Add-Para 'Alcance: coincidencia textual orientativa; no prueba por sí sola plagio ni busca en todo Internet.'
    $sect=Node $script:docXml $script:body 'w' 'sectPr' $wNs;$sz=Node $script:docXml $sect 'w' 'pgSz' $wNs;WAttr $script:docXml $sz 'w' 'w' $wNs '12240';WAttr $script:docXml $sz 'w' 'h' $wNs '15840';$mar=Node $script:docXml $sect 'w' 'pgMar' $wNs;foreach($item in @(@('top','1440'),@('right','1440'),@('bottom','1440'),@('left','1440'),@('header','720'),@('footer','720'),@('gutter','0'))){WAttr $script:docXml $mar 'w' $item[0] $wNs $item[1]}
    $types=New-Object System.Xml.XmlDocument;$typesRoot=$types.CreateElement('Types',$ctNs);$typesRoot.SetAttribute('xmlns',$ctNs);[void]$types.AppendChild($typesRoot);foreach($item in @(@('rels','application/vnd.openxmlformats-package.relationships+xml'),@('xml','application/xml'),@('png','image/png'))){$n=$types.CreateElement('Default',$ctNs);$n.SetAttribute('Extension',$item[0]);$n.SetAttribute('ContentType',$item[1]);[void]$typesRoot.AppendChild($n)};foreach($item in @(@('/word/document.xml','application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'),@('/word/styles.xml','application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml'))){$n=$types.CreateElement('Override',$ctNs);$n.SetAttribute('PartName',$item[0]);$n.SetAttribute('ContentType',$item[1]);[void]$typesRoot.AppendChild($n)}
    $pkg=New-Object System.Xml.XmlDocument;$pr=$pkg.CreateElement('Relationships',$relNs);$pr.SetAttribute('xmlns',$relNs);[void]$pkg.AppendChild($pr);$r=$pkg.CreateElement('Relationship',$relNs);$r.SetAttribute('Id','rId1');$r.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument');$r.SetAttribute('Target','word/document.xml');[void]$pr.AppendChild($r)
    ZipText $script:zip '[Content_Types].xml' (XmlText $types);ZipText $script:zip '_rels/.rels' (XmlText $pkg);ZipText $script:zip 'word/document.xml' (XmlText $script:docXml);ZipText $script:zip 'word/styles.xml' (StylesXml);ZipText $script:zip 'word/_rels/document.xml.rels' (XmlText $script:rels)
}
finally { $script:zip.Dispose() }

$check=[System.IO.Compression.ZipFile]::OpenRead($salida)
try{$docEntry=$check.GetEntry('word/document.xml');$reader=[System.IO.StreamReader]::new($docEntry.Open(),[System.Text.Encoding]::UTF8);try{[xml]$verified=$reader.ReadToEnd()}finally{$reader.Dispose()};if(-not $check.GetEntry('word/media/figura-47.png')){throw 'Falta el mockup integrado en el documento.'};$allText=($verified.SelectNodes('//*[local-name()="t"]')|ForEach-Object{$_.InnerText}) -join ' ';foreach($required in @('HU09','Crossref','OpenAlex','24 pruebas y 169 aserciones','Tabla 25')){if($allText -notmatch [regex]::Escape($required)){throw "No se encontró el texto requerido: $required"}}}finally{$check.Dispose()}
Write-Output "Documento creado: $salida"
Write-Output "Figuras SVG/PNG: $carpeta"
