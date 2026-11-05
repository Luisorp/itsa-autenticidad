$ErrorActionPreference = 'Stop'
$raiz = (Get-Location).Path
$carpeta = Join-Path $raiz 'docs\sprint-2'
New-Item -ItemType Directory -Path $carpeta -Force | Out-Null

function Xml([string]$texto) {
    [System.Security.SecurityElement]::Escape($texto)
}

function Guardar-Svg([string]$nombre, [string]$contenido) {
    $ruta = Join-Path $carpeta $nombre
    if (Test-Path -LiteralPath $ruta) {
        $existente = [System.IO.File]::ReadAllText($ruta, [System.Text.Encoding]::UTF8)
        if ($existente -ceq $contenido) { return }
    }
    [System.IO.File]::WriteAllText($ruta, $contenido, [System.Text.UTF8Encoding]::new($false))
}

function Convertir-SvgPng([string]$nombre) {
    $rutaSvg = Join-Path $carpeta $nombre
    $rutaPng = [System.IO.Path]::ChangeExtension($rutaSvg, '.png')
    if ((Test-Path -LiteralPath $rutaPng) -and
        (Get-Item -LiteralPath $rutaPng).Length -gt 1000 -and
        (Get-Item -LiteralPath $rutaPng).LastWriteTimeUtc -ge (Get-Item -LiteralPath $rutaSvg).LastWriteTimeUtc) {
        return $rutaPng
    }
    $xmlSvg = [System.IO.File]::ReadAllText($rutaSvg, [System.Text.Encoding]::UTF8)
    if ($xmlSvg -match 'width="(\d+)"\s+height="(\d+)"') {
        $ancho = [int]$Matches[1]
        $alto = [int]$Matches[2]
    } else {
        $ancho = 1100
        $alto = 800
    }
    $edge = 'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe'
    if (-not (Test-Path -LiteralPath $edge)) { throw 'No se encontró Microsoft Edge para convertir las figuras SVG.' }
    $uri = ([System.Uri]::new($rutaSvg)).AbsoluteUri
    $argumentos = @(
        '--headless=new', '--disable-gpu', '--hide-scrollbars', '--no-first-run',
        ('--window-size=' + $ancho + ',' + $alto),
        ('--screenshot=' + $rutaPng), $uri
    )
    $proceso = Start-Process -FilePath $edge -ArgumentList $argumentos -PassThru -Wait -WindowStyle Hidden
    if (-not (Test-Path -LiteralPath $rutaPng) -or (Get-Item -LiteralPath $rutaPng).Length -lt 1000) {
        throw ('No se pudo generar la imagen del diagrama: ' + $nombre + ' (Edge exit ' + $proceso.ExitCode + ').')
    }
    return $rutaPng
}

function Svg-CasoUso([string]$actor, [string[]]$casos) {
    $alto = 160 + ($casos.Count * 76)
    $centro = [int]($alto / 2)
    $p = [System.Collections.Generic.List[string]]::new()
    $p.Add('<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="' + $alto + '" viewBox="0 0 1000 ' + $alto + '">')
    $p.Add('<defs><style>text{font-family:Arial,sans-serif;fill:#111}.b{fill:#fff;stroke:#111;stroke-width:2}.a{fill:none;stroke:#111;stroke-width:2}</style></defs>')
    $p.Add('<rect x="205" y="25" width="770" height="' + ($alto - 50) + '" fill="#fff" stroke="#111" stroke-width="2"/>')
    $p.Add('<circle cx="95" cy="' + ($centro - 35) + '" r="19" class="a"/><path class="a" d="M95 ' + ($centro - 16) + 'v65 M59 ' + ($centro + 5) + 'h72 M95 ' + ($centro + 49) + 'l-28 38 M95 ' + ($centro + 49) + 'l28 38"/>')
    $p.Add('<text x="95" y="' + ($centro + 112) + '" text-anchor="middle" font-size="18">' + (Xml $actor) + '</text>')
    for ($i = 0; $i -lt $casos.Count; $i++) {
        $y = 82 + ($i * 76)
        $p.Add('<path class="a" d="M132 ' + ($centro + 5) + ' L285 ' + $y + '"/>')
        $p.Add('<ellipse class="b" cx="620" cy="' + $y + '" rx="280" ry="27"/>')
        $p.Add('<text x="620" y="' + ($y + 6) + '" text-anchor="middle" font-size="17">' + (Xml $casos[$i]) + '</text>')
    }
    $p.Add('</svg>')
    return ($p -join [Environment]::NewLine)
}

function Svg-Secuencia([string[]]$participantes, [object[]]$mensajes) {
    $alto = 150 + ($mensajes.Count * 45)
    $xs = @(120, 370, 620, 870)
    $p = [System.Collections.Generic.List[string]]::new()
    $p.Add('<svg xmlns="http://www.w3.org/2000/svg" width="1000" height="' + $alto + '" viewBox="0 0 1000 ' + $alto + '">')
    $p.Add('<defs><marker id="f" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.box{fill:#fff;stroke:#111;stroke-width:2}.line{stroke:#555;stroke-dasharray:6 5}.msg{stroke:#111;stroke-width:1.7;marker-end:url(#f)}.ret{stroke:#555;stroke-width:1.5;stroke-dasharray:5 4;marker-end:url(#f)}</style></defs>')
    for ($i = 0; $i -lt 4; $i++) {
        $p.Add('<rect class="box" x="' + ($xs[$i] - 92) + '" y="20" width="184" height="44" rx="2"/>')
        $p.Add('<text x="' + $xs[$i] + '" y="47" text-anchor="middle" font-size="15">' + (Xml $participantes[$i]) + '</text>')
        $p.Add('<path class="line" d="M' + $xs[$i] + ' 64V' + ($alto - 15) + '"/>')
    }
    for ($i = 0; $i -lt $mensajes.Count; $i++) {
        $m = $mensajes[$i]
        $y = 95 + ($i * 45)
        $clase = if ($m.retorno) { 'ret' } else { 'msg' }
        $p.Add('<path class="' + $clase + '" d="M' + $xs[$m.de] + ' ' + $y + 'H' + $xs[$m.a] + '"/>')
        $p.Add('<text x="' + [int](($xs[$m.de] + $xs[$m.a]) / 2) + '" y="' + ($y - 7) + '" text-anchor="middle" font-size="13">' + (Xml $m.texto) + '</text>')
    }
    $p.Add('</svg>')
    return ($p -join [Environment]::NewLine)
}

function Svg-Actividad([string[]]$pasos, [string]$decision, [string]$error) {
    $p = [System.Collections.Generic.List[string]]::new()
    $p.Add('<svg xmlns="http://www.w3.org/2000/svg" width="760" height="590" viewBox="0 0 760 590">')
    $p.Add('<defs><marker id="f" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10z" fill="#111"/></marker><style>text{font-family:Arial,sans-serif;fill:#111}.n{fill:#fff;stroke:#111;stroke-width:2}.l{fill:none;stroke:#111;stroke-width:2;marker-end:url(#f)}.s{fill:#111}.e{fill:#fff;stroke:#111;stroke-width:3}</style></defs>')
    $p.Add('<circle class="s" cx="350" cy="30" r="10"/><path class="l" d="M350 40V55"/>')
    for ($i = 0; $i -lt 3; $i++) {
        $y = 55 + ($i * 62)
        $p.Add('<rect class="n" x="195" y="' + $y + '" width="310" height="44" rx="16"/>')
        $p.Add('<text x="350" y="' + ($y + 27) + '" text-anchor="middle" font-size="15">' + (Xml $pasos[$i]) + '</text>')
        $p.Add('<path class="l" d="M350 ' + ($y + 44) + 'V' + ($y + 62) + '"/>')
    }
    $p.Add('<path class="n" d="M350 247L395 282L350 317L305 282Z"/>')
    $p.Add('<text x="350" y="279" text-anchor="middle" font-size="13">' + (Xml $decision) + '</text>')
    $p.Add('<text x="270" y="335" font-size="13">No</text><path class="l" d="M305 282H155V360"/>')
    $p.Add('<rect class="n" x="30" y="360" width="250" height="52" rx="14"/>')
    $p.Add('<text x="155" y="382" text-anchor="middle" font-size="14">' + (Xml $error) + '</text><text x="155" y="401" text-anchor="middle" font-size="13">Corregir y volver</text>')
    $p.Add('<circle class="e" cx="155" cy="455" r="12"/><circle class="s" cx="155" cy="455" r="6"/><path class="l" d="M155 412V443"/>')
    $p.Add('<text x="414" y="335" font-size="13">Sí</text><path class="l" d="M395 282H555V330"/>')
    $p.Add('<rect class="n" x="430" y="330" width="250" height="52" rx="14"/>')
    $p.Add('<text x="555" y="352" text-anchor="middle" font-size="14">' + (Xml $pasos[3]) + '</text><text x="555" y="371" text-anchor="middle" font-size="13">' + (Xml $pasos[4]) + '</text>')
    $p.Add('<path class="l" d="M555 382V435"/><circle class="e" cx="555" cy="455" r="12"/><circle class="s" cx="555" cy="455" r="6"/>')
    $p.Add('</svg>')
    return ($p -join [Environment]::NewLine)
}

function Svg-Mockup([string]$titulo, [string[]]$campos, [string[]]$columnas, [string[]]$acciones) {
    $p = [System.Collections.Generic.List[string]]::new()
    $p.Add('<svg xmlns="http://www.w3.org/2000/svg" width="1100" height="590" viewBox="0 0 1100 590">')
    $p.Add('<defs><style>text{font-family:Arial,sans-serif;fill:#111}.b{fill:#fff;stroke:#111;stroke-width:1.5}.muted{fill:#555}</style></defs>')
    $p.Add('<rect x="12" y="12" width="1076" height="566" fill="#fff" stroke="#111" stroke-width="2"/>')
    $p.Add('<text x="45" y="58" font-size="27" font-weight="700">' + (Xml $titulo) + '</text><line x1="40" y1="78" x2="1060" y2="78" stroke="#111"/>')
    $porFila = [Math]::Min(4, $campos.Count)
    $anchoCampo = [int]((1010 - (($porFila - 1) * 18)) / $porFila)
    for ($i = 0; $i -lt $campos.Count; $i++) {
        $col = $i % 4
        $fila = [int][Math]::Floor($i / 4)
        $xCampo = 45 + ($col * ($anchoCampo + 18))
        $yEtiqueta = 115 + ($fila * 68)
        $p.Add('<text x="' + $xCampo + '" y="' + $yEtiqueta + '" font-size="14">' + (Xml $campos[$i]) + '</text>')
        $p.Add('<rect class="b" x="' + $xCampo + '" y="' + ($yEtiqueta + 10) + '" width="' + $anchoCampo + '" height="36"/>')
    }
    $tablaY = if ($campos.Count -gt 4) { 330 } else { 250 }
    $botonY = if ($campos.Count -gt 4) { 265 } else { 184 }
    $etiquetas = @($acciones[0],$acciones[1],$acciones[2])
    for ($i = 0; $i -lt 3; $i++) {
        $bx = 45 + ($i * 160)
        $p.Add('<rect class="b" x="' + $bx + '" y="' + $botonY + '" width="145" height="38"/><text x="' + ($bx + 72) + '" y="' + ($botonY + 25) + '" text-anchor="middle" font-size="14">' + (Xml $etiquetas[$i]) + '</text>')
    }
    $p.Add('<rect class="b" x="45" y="' + $tablaY + '" width="1010" height="' + (520 - $tablaY) + '"/>')
    $colW = [int](1010 / $columnas.Count)
    for ($i = 0; $i -lt $columnas.Count; $i++) {
        $cx = 45 + ($i * $colW)
        if ($i -gt 0) { $p.Add('<line x1="' + $cx + '" y1="' + $tablaY + '" x2="' + $cx + '" y2="520" stroke="#777"/>') }
        $p.Add('<text x="' + ($cx + 10) + '" y="' + ($tablaY + 30) + '" font-size="13" font-weight="700">' + (Xml $columnas[$i]) + '</text>')
    }
    foreach ($yy in @(($tablaY + 48),($tablaY + 95),($tablaY + 142),($tablaY + 189))) {
        if ($yy -lt 520) { $p.Add('<line x1="45" y1="' + $yy + '" x2="1055" y2="' + $yy + '" stroke="#aaa"/>') }
    }
    $p.Add('</svg>')
    return ($p -join [Environment]::NewLine)
}

$casos = @{
    'hu05-caso-uso.svg' = @{ actor='Administrador / Gestor'; casos=@('Consultar y filtrar estudiantes','Registrar estudiante','Editar datos del estudiante','Importar estudiantes desde CSV','Activar o desactivar registro') }
    'hu06-caso-uso.svg' = @{ actor='Administrador / Gestor'; casos=@('Consultar directorio de tutores','Registrar tutor','Editar tutor','Asignar una o más carreras','Activar o desactivar tutor') }
    'hu07-caso-uso.svg' = @{ actor='Administrador / Gestor'; casos=@('Consultar proyectos','Registrar proyecto de titulación','Editar datos del proyecto','Relacionar carrera, estudiante y tutor','Adjuntar o reemplazar PDF') }
    'hu08-caso-uso.svg' = @{ actor='Persona autorizada'; casos=@('Buscar por título o estudiante','Filtrar proyectos','Consultar listado de resultados','Abrir PDF asociado') }
}
foreach ($nombre in $casos.Keys) {
    Guardar-Svg $nombre (Svg-CasoUso $casos[$nombre].actor $casos[$nombre].casos)
}

$actividades = @{
    'hu05-actividad.svg' = @{ pasos=@('Abrir padrón de estudiantes','Elegir registro, edición o importación','Ingresar datos o seleccionar CSV','Guardar estudiantes validados','Actualizar listado'); decision='¿Datos válidos?'; error='Mostrar errores del formulario o CSV' }
    'hu06-actividad.svg' = @{ pasos=@('Abrir directorio de tutores','Elegir registrar o editar tutor','Ingresar datos y seleccionar carreras','Guardar tutor y sus carreras','Actualizar directorio'); decision='¿Tutor y carreras válidos?'; error='Indicar qué datos deben corregirse' }
    'hu07-actividad.svg' = @{ pasos=@('Abrir formulario de proyecto','Ingresar modalidad y datos académicos','Seleccionar carrera, estudiante y tutor','Guardar proyecto y documento PDF','Actualizar catálogo'); decision='¿Datos y PDF válidos?'; error='Mostrar errores y conservar formulario' }
    'hu08-actividad.svg' = @{ pasos=@('Abrir catálogo de proyectos','Ingresar búsqueda y filtros','Consultar resultados permitidos','Mostrar listado y seleccionar PDF','Abrir documento asociado'); decision='¿Coincidencia y PDF disponible?'; error='Mostrar lista vacía o archivo ausente' }
}
foreach ($nombre in $actividades.Keys) {
    Guardar-Svg $nombre (Svg-Actividad $actividades[$nombre].pasos $actividades[$nombre].decision $actividades[$nombre].error)
}

$mockups = @{
    'hu05-mockup.svg' = @{ titulo='Estudiantes'; campos=@('Buscar por nombre o código','Carrera'); columnas=@('Código','Nombre','Carrera','Gestión','Estado','Acciones'); acciones=@('Nuevo estudiante','Importar CSV','Plantilla CSV') }
    'hu06-mockup.svg' = @{ titulo='Tutores'; campos=@('Buscar tutor','Carreras'); columnas=@('Código','Nombre','Carreras','Cuenta','Estado','Acciones'); acciones=@('Nuevo tutor','Editar','Guardar') }
    'hu07-mockup.svg' = @{ titulo='Proyecto'; campos=@('Título','Modalidad','Carrera','Estudiante','Tutor','Año'); columnas=@('Documento PDF','Nombre de archivo','Estado'); acciones=@('Seleccionar PDF','Guardar','Cancelar') }
    'hu08-mockup.svg' = @{ titulo='Proyectos'; campos=@('Título o estudiante','Carrera','Modalidad','Año'); columnas=@('Proyecto','Estudiante','Carrera','Modalidad','Año','PDF'); acciones=@('Buscar','Filtrar','Limpiar') }
}
foreach ($nombre in $mockups.Keys) {
    Guardar-Svg $nombre (Svg-Mockup $mockups[$nombre].titulo $mockups[$nombre].campos $mockups[$nombre].columnas $mockups[$nombre].acciones)
}

$salida = Join-Path $raiz 'docs\INFORME_SPRINT_2_REPOSITORIO_ACADEMICO_AJUSTADO.docx'

$historias = @(
    @{
        id='HU05'; base='hu05'; titulo='Registro e importación de estudiantes'
        historia='Como Gestor, necesito registrar e importar estudiantes para mantener organizada su información académica.'
        actor='Administrador / Gestor'
        casos=@('Consultar y filtrar estudiantes','Registrar estudiante','Editar datos del estudiante','Importar estudiantes desde CSV','Activar o desactivar registro')
        especificacion=@(
            @('Actores','Administrador y Gestor.'),
            @('Precondición','La persona inició sesión y tiene permiso de gestión académica. La carrera asociada debe existir.'),
            @('Postcondición','El registro queda creado o actualizado y aparece en el padrón. La cuenta de acceso es opcional; registrar al estudiante no crea una cuenta automáticamente.'),
            @('Flujo básico','1. Abrir Estudiantes. 2. Consultar o filtrar el padrón. 3. Elegir Nuevo estudiante o Importar CSV. 4. Ingresar datos o seleccionar el archivo. 5. El sistema valida y guarda. 6. El listado muestra la confirmación.'),
            @('Flujos alternativos','La importación utiliza la plantilla CSV. Se requieren código, nombre y carrera; correo y gestión de ingreso son opcionales.'),
            @('Excepciones','Datos incompletos, código repetido, formato incorrecto, carrera inexistente o encabezados faltantes: se muestran errores y no se confirma la importación.')
        )
        participantes=@('Gestor','Pantalla estudiantes','EstudianteController','Base de datos')
        mensajes=@(
            @{de=0;a=1;texto='Abrir padrón';retorno=$false},@{de=1;a=2;texto='Solicitar listado o formulario';retorno=$false},
            @{de=2;a=3;texto='Consultar estudiantes y carreras';retorno=$false},@{de=3;a=2;texto='Devolver registros';retorno=$true},
            @{de=0;a=1;texto='Enviar formulario o CSV';retorno=$false},@{de=1;a=2;texto='Validar datos y carrera';retorno=$false},
            @{de=2;a=3;texto='Crear o actualizar registros';retorno=$false},@{de=2;a=1;texto='Mostrar confirmación o errores';retorno=$true}
        )
        pasos=@('Abrir padrón de estudiantes','Elegir registro, edición o importación','Ingresar datos o seleccionar CSV','Guardar estudiantes validados','Actualizar listado')
        decision='¿Válido?'; error='Mostrar errores del formulario o CSV'
        mock='hu05-mockup.svg'; campos=@('Buscar por nombre o código','Carrera')
        columnas=@('Código','Nombre','Carrera','Gestión','Estado','Acciones'); acciones=@('Nuevo estudiante','Importar CSV','Plantilla CSV')
        captura='listado de estudiantes y área de importación CSV.'
    },
    @{
        id='HU06'; base='hu06'; titulo='Asignación de tutores a carreras'
        historia='Como Gestor, necesito indicar en qué carreras puede asesorar cada tutor para asignarlo a proyectos de su área.'
        actor='Administrador / Gestor'
        casos=@('Consultar directorio de tutores','Registrar tutor','Editar tutor','Asignar una o más carreras','Activar o desactivar tutor')
        especificacion=@(
            @('Actores','Administrador y Gestor.'),
            @('Precondición','La persona inició sesión y tiene permiso de gestión académica. Debe existir al menos una carrera para asociar.'),
            @('Postcondición','El tutor queda registrado o actualizado con una o más carreras. La cuenta de Gestor es opcional y no se crea automáticamente.'),
            @('Flujo básico','1. Abrir el directorio de tutores. 2. Elegir Nuevo tutor o editar un registro. 3. Ingresar nombre y datos opcionales. 4. Seleccionar una o más carreras. 5. El sistema valida y guarda tutor y asociaciones. 6. El directorio muestra los cambios.'),
            @('Flujos alternativos','Un tutor puede estar asociado a varias carreras. En el formulario de proyecto, el catálogo de tutores se limita a las carreras seleccionadas.'),
            @('Excepciones','Nombre ausente, correo inválido, código duplicado o ninguna carrera seleccionada: se muestran errores y no se guarda el cambio.')
        )
        participantes=@('Gestor','Pantalla tutores','DocenteController','BD / carrera_docente')
        mensajes=@(
            @{de=0;a=1;texto='Abrir directorio';retorno=$false},@{de=1;a=2;texto='Solicitar tutores y carreras';retorno=$false},
            @{de=2;a=3;texto='Consultar datos y asociaciones';retorno=$false},@{de=3;a=2;texto='Devolver registros';retorno=$true},
            @{de=0;a=1;texto='Enviar datos y carreras';retorno=$false},@{de=1;a=2;texto='Validar tutor y selección';retorno=$false},
            @{de=2;a=3;texto='Guardar tutor y sincronizar carreras';retorno=$false},@{de=2;a=1;texto='Mostrar confirmación o errores';retorno=$true}
        )
        pasos=@('Abrir directorio de tutores','Elegir registrar o editar tutor','Ingresar datos y seleccionar carreras','Guardar tutor y sus carreras','Actualizar directorio')
        decision='¿Válido?'; error='Indicar qué datos deben corregirse'
        mock='hu06-mockup.svg'; campos=@('Buscar tutor','Carreras')
        columnas=@('Código','Nombre','Carreras','Cuenta','Estado','Acciones'); acciones=@('Nuevo tutor','Editar','Guardar')
        captura='formulario de tutor con la selección de carreras asociadas.'
    },
    @{
        id='HU07'; base='hu07'; titulo='Registro y edición de proyectos'
        historia='Como Gestor, necesito registrar y editar proyectos de titulación, indicando su modalidad y relaciones académicas.'
        actor='Administrador / Gestor'
        casos=@('Consultar proyectos','Registrar proyecto de titulación','Editar datos del proyecto','Relacionar carrera, estudiante y tutor','Adjuntar o reemplazar PDF')
        especificacion=@(
            @('Actores','Administrador y Gestor; el Usuario no tiene permisos de escritura.'),
            @('Precondición','La persona inició sesión y tiene permiso para gestionar proyectos. Deben existir una carrera y un estudiante activos.'),
            @('Postcondición','El proyecto queda creado o actualizado con modalidad y relaciones válidas. Al registrar, se almacena el PDF y el texto extraído.'),
            @('Flujo básico','1. Abrir Proyectos y elegir Nuevo proyecto o Editar. 2. Ingresar título, modalidad, carrera y gestión. 3. Elegir estudiante y, opcionalmente, tutor de esa carrera. 4. Adjuntar el PDF al crear. 5. El sistema valida y almacena datos y documento. 6. El catálogo muestra confirmación.'),
            @('Flujos alternativos','En la edición se pueden actualizar los datos o reemplazar el PDF. Si cambia el documento, el análisis queda pendiente de repetición.'),
            @('Excepciones','Campos obligatorios inválidos, estudiante incompatible, tutor de otra carrera o PDF ausente/no válido: se muestran errores y no se confirma el registro.')
        )
        participantes=@('Gestor','Formulario proyecto','ProyectoController','BD / almacenamiento')
        mensajes=@(
            @{de=0;a=1;texto='Abrir formulario';retorno=$false},@{de=1;a=2;texto='Solicitar catálogos académicos';retorno=$false},
            @{de=2;a=3;texto='Consultar carreras, estudiantes y tutores';retorno=$false},@{de=3;a=2;texto='Devolver opciones';retorno=$true},
            @{de=0;a=1;texto='Enviar datos y PDF';retorno=$false},@{de=1;a=2;texto='Validar campos y compatibilidad';retorno=$false},
            @{de=2;a=3;texto='Guardar proyecto y documento';retorno=$false},@{de=2;a=3;texto='Almacenar PDF y extraer texto';retorno=$false},
            @{de=2;a=1;texto='Confirmar operación o errores';retorno=$true}
        )
        pasos=@('Abrir formulario de proyecto','Ingresar modalidad y datos académicos','Seleccionar carrera, estudiante y tutor','Guardar proyecto y documento PDF','Actualizar catálogo')
        decision='¿Válido?'; error='Mostrar errores y conservar formulario'
        mock='hu07-mockup.svg'; campos=@('Título','Modalidad','Carrera','Estudiante','Tutor','Año')
        columnas=@('Documento PDF','Nombre de archivo','Estado'); acciones=@('Seleccionar PDF','Guardar','Cancelar')
        captura='formulario de proyecto con sus relaciones y el campo de documento PDF.'
    },
    @{
        id='HU08'; base='hu08'; titulo='Búsqueda de proyectos y consulta de PDF'
        historia='Como persona autorizada, necesito buscar proyectos y abrir el PDF asociado para localizar y consultar su documentación.'
        actor='Persona autorizada'
        casos=@('Buscar por título o estudiante','Filtrar proyectos','Consultar listado de resultados','Abrir PDF asociado')
        especificacion=@(
            @('Actores','Administrador, Gestor y Usuario autenticado. El Usuario consulta solo proyectos vinculados a su registro.'),
            @('Precondición','La persona inició sesión. Para abrir el documento, debe estar autorizada y el PDF debe existir en el almacenamiento.'),
            @('Postcondición','Se muestra el listado que coincide con los criterios. Si el PDF existe y el acceso está permitido, se abre en el navegador.'),
            @('Flujo básico','1. Abrir Proyectos. 2. Buscar por título o nombre del estudiante. 3. Aplicar filtros de carrera, modalidad o gestión. 4. Revisar resultados. 5. Elegir Ver PDF para consultar el documento.'),
            @('Flujos alternativos','Los criterios se pueden combinar o limpiar. El Usuario solo ve proyectos vinculados a su propia cuenta.'),
            @('Excepciones','Sin coincidencias se muestra el estado vacío. Si el documento no está asociado o no existe, se indica que no está disponible o se devuelve recurso no encontrado.')
        )
        participantes=@('Usuario autorizado','Catálogo web','ProyectoController','BD / almacenamiento')
        mensajes=@(
            @{de=0;a=1;texto='Abrir catálogo y enviar filtros';retorno=$false},@{de=1;a=2;texto='Solicitar proyectos visibles';retorno=$false},
            @{de=2;a=3;texto='Consultar según filtros y permisos';retorno=$false},@{de=3;a=2;texto='Devolver resultados';retorno=$true},
            @{de=2;a=1;texto='Mostrar listado';retorno=$true},@{de=0;a=1;texto='Seleccionar Ver PDF';retorno=$false},
            @{de=1;a=2;texto='Comprobar permiso y existencia';retorno=$false},@{de=2;a=3;texto='Leer documento PDF';retorno=$false},
            @{de=3;a=2;texto='Enviar PDF o indicar que no existe';retorno=$true}
        )
        pasos=@('Abrir catálogo de proyectos','Ingresar búsqueda y filtros','Consultar resultados permitidos','Mostrar listado y seleccionar PDF','Abrir documento asociado')
        decision='¿Disponible?'; error='Mostrar lista vacía o archivo ausente'
        mock='hu08-mockup.svg'; campos=@('Título o estudiante','Carrera','Modalidad','Año')
        columnas=@('Proyecto','Estudiante','Carrera','Modalidad','Año','PDF'); acciones=@('Buscar','Filtrar','Limpiar')
        captura='catálogo de proyectos con filtros aplicados y enlace Ver PDF.'
    }
)

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Drawing
$wNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
$rNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships'
$relNs = 'http://schemas.openxmlformats.org/package/2006/relationships'
$ctNs = 'http://schemas.openxmlformats.org/package/2006/content-types'
$wpNs = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing'
$aNs = 'http://schemas.openxmlformats.org/drawingml/2006/main'
$picNs = 'http://schemas.openxmlformats.org/drawingml/2006/picture'
$script:wordXml = New-Object System.Xml.XmlDocument
$script:wordXml.PreserveWhitespace = $true
$script:wordRoot = $script:wordXml.CreateElement('w', 'document', $wNs)
$xmlnsW = $script:wordXml.CreateAttribute('xmlns', 'w', 'http://www.w3.org/2000/xmlns/'); $xmlnsW.Value = $wNs; [void]$script:wordRoot.Attributes.Append($xmlnsW)
$xmlnsR = $script:wordXml.CreateAttribute('xmlns', 'r', 'http://www.w3.org/2000/xmlns/'); $xmlnsR.Value = $rNs; [void]$script:wordRoot.Attributes.Append($xmlnsR)
[void]$script:wordXml.AppendChild($script:wordRoot)
$script:wordBody = $script:wordXml.CreateElement('w', 'body', $wNs); [void]$script:wordRoot.AppendChild($script:wordBody)
$script:figuraNumero = 27
$script:relNumero = 1

function New-WNode([System.Xml.XmlDocument]$xml, [System.Xml.XmlNode]$parent, [string]$prefix, [string]$local, [string]$ns) {
    $node = $xml.CreateElement($prefix, $local, $ns)
    [void]$parent.AppendChild($node)
    return ,$node
}
function Set-WAttr([System.Xml.XmlDocument]$xml, [System.Xml.XmlElement]$element, [string]$prefix, [string]$local, [string]$ns, [string]$value) {
    $attr = $xml.CreateAttribute($prefix, $local, $ns); $attr.Value = $value; [void]$element.Attributes.Append($attr)
}
function Set-Attr([System.Xml.XmlElement]$element, [string]$name, [string]$value) {
    $element.SetAttribute($name, $value)
}
function Add-WRun([System.Xml.XmlDocument]$xml, [System.Xml.XmlElement]$parent, [string]$text, [switch]$bold, [int]$size = 22) {
    $run = New-WNode $xml $parent 'w' 'r' $wNs
    if ($bold -or $size -ne 22) {
        $rp = New-WNode $xml $run 'w' 'rPr' $wNs
        if ($bold) { [void](New-WNode $xml $rp 'w' 'b' $wNs) }
        if ($size -ne 22) { $sz = New-WNode $xml $rp 'w' 'sz' $wNs; Set-WAttr $xml $sz 'w' 'val' $wNs ([string]$size) }
    }
    $t = New-WNode $xml $run 'w' 't' $wNs
    [void]$t.SetAttribute('space', 'http://www.w3.org/XML/1998/namespace', 'preserve')
    $t.InnerText = $text
}
function Add-WPara([string]$text, [string]$style = 'Normal', [switch]$pageBreak, [switch]$center, [switch]$bold, [System.Xml.XmlElement]$parent = $null) {
    if (-not $parent) { $parent = $script:wordBody }
    $p = New-WNode $script:wordXml $parent 'w' 'p' $wNs
    $pPr = New-WNode $script:wordXml $p 'w' 'pPr' $wNs
    $pStyle = New-WNode $script:wordXml $pPr 'w' 'pStyle' $wNs; Set-WAttr $script:wordXml $pStyle 'w' 'val' $wNs $style
    if ($pageBreak) { [void](New-WNode $script:wordXml $pPr 'w' 'pageBreakBefore' $wNs) }
    if ($center) { $jc = New-WNode $script:wordXml $pPr 'w' 'jc' $wNs; Set-WAttr $script:wordXml $jc 'w' 'val' $wNs 'center' }
    Add-WRun $script:wordXml $p $text -bold:$bold
}
function Add-WTable([string[]]$headers, [object[]]$rows) {
    $table = New-WNode $script:wordXml $script:wordBody 'w' 'tbl' $wNs
    $pr = New-WNode $script:wordXml $table 'w' 'tblPr' $wNs
    $width = New-WNode $script:wordXml $pr 'w' 'tblW' $wNs; Set-WAttr $script:wordXml $width 'w' 'w' $wNs '9360'; Set-WAttr $script:wordXml $width 'w' 'type' $wNs 'dxa'
    $borders = New-WNode $script:wordXml $pr 'w' 'tblBorders' $wNs
    foreach ($side in @('top','left','bottom','right','insideH','insideV')) { $b = New-WNode $script:wordXml $borders 'w' $side $wNs; Set-WAttr $script:wordXml $b 'w' 'val' $wNs 'single'; Set-WAttr $script:wordXml $b 'w' 'sz' $wNs '5'; Set-WAttr $script:wordXml $b 'w' 'color' $wNs 'B7C5BE' }
    $layout = New-WNode $script:wordXml $pr 'w' 'tblLayout' $wNs; Set-WAttr $script:wordXml $layout 'w' 'type' $wNs 'fixed'
    $margins = New-WNode $script:wordXml $pr 'w' 'tblCellMar' $wNs
    foreach ($side in @('top','start','bottom','end')) { $m = New-WNode $script:wordXml $margins 'w' $side $wNs; Set-WAttr $script:wordXml $m 'w' 'w' $wNs '90'; Set-WAttr $script:wordXml $m 'w' 'type' $wNs 'dxa' }
    $grid = New-WNode $script:wordXml $table 'w' 'tblGrid' $wNs
    $columnWidth = [int](9360 / $headers.Count)
    for ($c = 0; $c -lt $headers.Count; $c++) { $gc = New-WNode $script:wordXml $grid 'w' 'gridCol' $wNs; Set-WAttr $script:wordXml $gc 'w' 'w' $wNs ([string]$columnWidth) }
    $allRows = [System.Collections.Generic.List[object]]::new(); $allRows.Add([object]$headers); foreach ($row in $rows) { $allRows.Add([object]$row) }
    for ($ri = 0; $ri -lt $allRows.Count; $ri++) {
        $rowNode = New-WNode $script:wordXml $table 'w' 'tr' $wNs
        if ($ri -eq 0) { $trPr = New-WNode $script:wordXml $rowNode 'w' 'trPr' $wNs; [void](New-WNode $script:wordXml $trPr 'w' 'tblHeader' $wNs) }
        $cells = $allRows[$ri]
        for ($ci = 0; $ci -lt $headers.Count; $ci++) {
            $cell = New-WNode $script:wordXml $rowNode 'w' 'tc' $wNs
            $tcPr = New-WNode $script:wordXml $cell 'w' 'tcPr' $wNs; $tcw = New-WNode $script:wordXml $tcPr 'w' 'tcW' $wNs; Set-WAttr $script:wordXml $tcw 'w' 'w' $wNs ([string]$columnWidth); Set-WAttr $script:wordXml $tcw 'w' 'type' $wNs 'dxa'
            if ($ri -eq 0) { $shd = New-WNode $script:wordXml $tcPr 'w' 'shd' $wNs; Set-WAttr $script:wordXml $shd 'w' 'fill' $wNs 'E5EEE9' }
            $p = New-WNode $script:wordXml $cell 'w' 'p' $wNs
            Add-WRun $script:wordXml $p ([string]$cells[$ci]) -bold:($ri -eq 0) -size 18
        }
    }
    [void](New-WNode $script:wordXml $script:wordBody 'w' 'p' $wNs)
}
function Add-WFigure([string]$svgName, [string]$caption) {
    $pngPath = Convertir-SvgPng $svgName
    $script:figuraNumero++
    $script:relNumero++
    $relId = 'rId' + $script:relNumero
    $mediaName = 'figura-' + $script:figuraNumero + '.png'
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($script:archive, $pngPath, ('word/media/' + $mediaName), [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    $rel = $script:relsXml.CreateElement('Relationship', $relNs); $rel.SetAttribute('Id', $relId); $rel.SetAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image'); $rel.SetAttribute('Target', 'media/' + $mediaName); [void]$script:relsXml.DocumentElement.AppendChild($rel)
    $img = [System.Drawing.Image]::FromFile($pngPath)
    try { $pixelW = $img.Width; $pixelH = $img.Height } finally { $img.Dispose() }
    $cx = [double]$pixelW * 9525; $cy = [double]$pixelH * 9525
    $scale = [Math]::Min(5486400 / $cx, 4114800 / $cy); $cx = [string][int]($cx * $scale); $cy = [string][int]($cy * $scale)
    $p = New-WNode $script:wordXml $script:wordBody 'w' 'p' $wNs
    $pPr = New-WNode $script:wordXml $p 'w' 'pPr' $wNs; $jc = New-WNode $script:wordXml $pPr 'w' 'jc' $wNs; Set-WAttr $script:wordXml $jc 'w' 'val' $wNs 'center'
    $run = New-WNode $script:wordXml $p 'w' 'r' $wNs; $drawing = New-WNode $script:wordXml $run 'w' 'drawing' $wNs; $inline = New-WNode $script:wordXml $drawing 'wp' 'inline' $wpNs
    foreach ($pair in @(@('distT','0'),@('distB','0'),@('distL','0'),@('distR','0'))) { Set-Attr $inline $pair[0] $pair[1] }
    $extent = New-WNode $script:wordXml $inline 'wp' 'extent' $wpNs; Set-Attr $extent 'cx' $cx; Set-Attr $extent 'cy' $cy
    $docPr = New-WNode $script:wordXml $inline 'wp' 'docPr' $wpNs; Set-Attr $docPr 'id' ([string]$script:figuraNumero); Set-Attr $docPr 'name' $mediaName; Set-Attr $docPr 'descr' $caption
    $frame = New-WNode $script:wordXml $inline 'wp' 'cNvGraphicFramePr' $wpNs; $lock = New-WNode $script:wordXml $frame 'a' 'graphicFrameLocks' $aNs; Set-Attr $lock 'noChangeAspect' '1'
    $graphic = New-WNode $script:wordXml $inline 'a' 'graphic' $aNs; $data = New-WNode $script:wordXml $graphic 'a' 'graphicData' $aNs; Set-Attr $data 'uri' $picNs
    $pic = New-WNode $script:wordXml $data 'pic' 'pic' $picNs; $nv = New-WNode $script:wordXml $pic 'pic' 'nvPicPr' $picNs
    $cnv = New-WNode $script:wordXml $nv 'pic' 'cNvPr' $picNs; Set-Attr $cnv 'id' ([string]$script:figuraNumero); Set-Attr $cnv 'name' $mediaName; [void](New-WNode $script:wordXml $nv 'pic' 'cNvPicPr' $picNs)
    $fill = New-WNode $script:wordXml $pic 'pic' 'blipFill' $picNs; $blip = New-WNode $script:wordXml $fill 'a' 'blip' $aNs; Set-WAttr $script:wordXml $blip 'r' 'embed' $rNs $relId
    $stretch = New-WNode $script:wordXml $fill 'a' 'stretch' $aNs; [void](New-WNode $script:wordXml $stretch 'a' 'fillRect' $aNs)
    $sp = New-WNode $script:wordXml $pic 'pic' 'spPr' $picNs; $xfrm = New-WNode $script:wordXml $sp 'a' 'xfrm' $aNs
    $off = New-WNode $script:wordXml $xfrm 'a' 'off' $aNs; Set-Attr $off 'x' '0'; Set-Attr $off 'y' '0'
    $ext = New-WNode $script:wordXml $xfrm 'a' 'ext' $aNs; Set-Attr $ext 'cx' $cx; Set-Attr $ext 'cy' $cy
    $geom = New-WNode $script:wordXml $sp 'a' 'prstGeom' $aNs; Set-Attr $geom 'prst' 'rect'; [void](New-WNode $script:wordXml $geom 'a' 'avLst' $aNs)
    [void](Add-WPara $caption 'Caption' -center)
}
function Add-ZipText([System.IO.Compression.ZipArchive]$zip, [string]$name, [string]$text) {
    $entry = $zip.CreateEntry($name, [System.IO.Compression.CompressionLevel]::Optimal)
    $stream = $entry.Open(); $writer = [System.IO.StreamWriter]::new($stream, [System.Text.UTF8Encoding]::new($false))
    $writer.Write($text); $writer.Dispose(); $stream.Dispose()
}
function Xml-String([System.Xml.XmlDocument]$xml) {
    $settings = [System.Xml.XmlWriterSettings]::new(); $settings.Encoding = [System.Text.UTF8Encoding]::new($false); $settings.Indent = $false; $settings.OmitXmlDeclaration = $true
    $builder = [System.Text.StringBuilder]::new(); $writer = [System.Xml.XmlWriter]::Create($builder, $settings); $xml.Save($writer); $writer.Dispose()
    return $builder.ToString()
}
function New-WStyles {
    $x = New-Object System.Xml.XmlDocument
    $root = $x.CreateElement('w','styles',$wNs); $xmlnsStyle = $x.CreateAttribute('xmlns','w','http://www.w3.org/2000/xmlns/'); $xmlnsStyle.Value = $wNs; [void]$root.Attributes.Append($xmlnsStyle); [void]$x.AppendChild($root)
    $defaults = New-WNode $x $root 'w' 'docDefaults' $wNs; $rpd = New-WNode $x $defaults 'w' 'rPrDefault' $wNs; $rp = New-WNode $x $rpd 'w' 'rPr' $wNs
    $fonts = New-WNode $x $rp 'w' 'rFonts' $wNs; Set-WAttr $x $fonts 'w' 'ascii' $wNs 'Arial'; Set-WAttr $x $fonts 'w' 'hAnsi' $wNs 'Arial'
    $sz = New-WNode $x $rp 'w' 'sz' $wNs; Set-WAttr $x $sz 'w' 'val' $wNs '22'
    foreach ($st in @(@('Normal','Normal','22',$false),@('Title','Title','32',$true),@('Heading1','heading 1','30',$true),@('Heading2','heading 2','26',$true),@('Heading3','heading 3','22',$true),@('Caption','caption','18',$false))) {
        $s = New-WNode $x $root 'w' 'style' $wNs; Set-WAttr $x $s 'w' 'type' $wNs 'paragraph'; Set-WAttr $x $s 'w' 'styleId' $wNs $st[0]
        $n = New-WNode $x $s 'w' 'name' $wNs; Set-WAttr $x $n 'w' 'val' $wNs $st[1]
        if ($st[0] -ne 'Normal') { $based = New-WNode $x $s 'w' 'basedOn' $wNs; Set-WAttr $x $based 'w' 'val' $wNs 'Normal' }
        $ppr = $null
        if ($st[0] -like 'Heading*') { $ppr = New-WNode $x $s 'w' 'pPr' $wNs; [void](New-WNode $x $ppr 'w' 'keepNext' $wNs) }
        $rpr = New-WNode $x $s 'w' 'rPr' $wNs; $font = New-WNode $x $rpr 'w' 'rFonts' $wNs; Set-WAttr $x $font 'w' 'ascii' $wNs 'Arial'; Set-WAttr $x $font 'w' 'hAnsi' $wNs 'Arial'
        if ($st[3]) { [void](New-WNode $x $rpr 'w' 'b' $wNs) }
        if ($st[0] -eq 'Caption') { [void](New-WNode $x $rpr 'w' 'i' $wNs) }
        $size = New-WNode $x $rpr 'w' 'sz' $wNs; Set-WAttr $x $size 'w' 'val' $wNs $st[2]
    }
    return (Xml-String $x)
}

$contentTypes = New-Object System.Xml.XmlDocument
$ctRoot = $contentTypes.CreateElement('Types',$ctNs); $ctRoot.SetAttribute('xmlns',$ctNs); [void]$contentTypes.AppendChild($ctRoot)
foreach ($def in @(@('rels','application/vnd.openxmlformats-package.relationships+xml'),@('xml','application/xml'),@('png','image/png'))) { $d = $contentTypes.CreateElement('Default',$ctNs); $d.SetAttribute('Extension',$def[0]); $d.SetAttribute('ContentType',$def[1]); [void]$ctRoot.AppendChild($d) }
foreach ($ov in @(@('/word/document.xml','application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'),@('/word/styles.xml','application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml'))) { $o = $contentTypes.CreateElement('Override',$ctNs); $o.SetAttribute('PartName',$ov[0]); $o.SetAttribute('ContentType',$ov[1]); [void]$ctRoot.AppendChild($o) }
$pkgRels = New-Object System.Xml.XmlDocument; $pkgRoot = $pkgRels.CreateElement('Relationships',$relNs); $pkgRoot.SetAttribute('xmlns',$relNs); [void]$pkgRels.AppendChild($pkgRoot)
$pkgRel = $pkgRels.CreateElement('Relationship',$relNs); $pkgRel.SetAttribute('Id','rId1'); $pkgRel.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument'); $pkgRel.SetAttribute('Target','word/document.xml'); [void]$pkgRoot.AppendChild($pkgRel)
$script:relsXml = New-Object System.Xml.XmlDocument; $relsRoot = $script:relsXml.CreateElement('Relationships',$relNs); $relsRoot.SetAttribute('xmlns',$relNs); [void]$script:relsXml.AppendChild($relsRoot)
$stylesRel = $script:relsXml.CreateElement('Relationship',$relNs); $stylesRel.SetAttribute('Id','rId1'); $stylesRel.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles'); $stylesRel.SetAttribute('Target','styles.xml'); [void]$relsRoot.AppendChild($stylesRel)

if (Test-Path -LiteralPath $salida) { throw ('Se conserva el documento existente y no se sobrescribe: ' + $salida) }
$script:archive = [System.IO.Compression.ZipFile]::Open($salida, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    Add-WPara 'Sprint 2: Repositorio académico' 'Heading1'
    Add-WPara 'Objetivo del Sprint' 'Heading1'
    Add-WPara 'Construir el repositorio académico para organizar estudiantes, tutores y proyectos de titulación; facilitar la búsqueda de sus registros y permitir la consulta del documento PDF, respetando las relaciones académicas y los permisos de acceso.'
    Add-WPara 'Sprint Backlog' 'Heading1'
    Add-WPara 'Tabla 15. Sprint Backlog' 'Caption'
    Add-WTable @('Historia','Trabajo del sprint','Comprobación disponible') @(
        @('HU05','Registrar, editar, buscar e importar estudiantes mediante CSV.','AcademicRecordsTest: registro e importación.'),
        @('HU06','Registrar tutores y relacionarlos con una o más carreras para asignarlos a proyectos compatibles.','AcademicRecordsTest: relación tutor-carrera y compatibilidad.'),
        @('HU07','Registrar y editar proyectos con modalidad, carrera, estudiante, tutor opcional y documento PDF.','PdfEncodingTest y ProjectFilesTest.'),
        @('HU08','Buscar y filtrar proyectos, y abrir el PDF asociado cuando esté disponible.','ProjectFilesTest: filtros y consulta del PDF.')
    )
    $numeroTabla = 16
    foreach ($h in $historias) {
        Add-WPara ($h.id + ': ' + $h.titulo) 'Heading1' -pageBreak
        Add-WPara $h.historia
        Add-WPara 'Diagrama de caso de uso' 'Heading2'
        Guardar-Svg ($h.base + '-caso-uso.svg') (Svg-CasoUso $h.actor $h.casos)
        Add-WFigure ($h.base + '-caso-uso.svg') ('Figura ' + ($script:figuraNumero + 1) + '. Caso de uso de ' + $h.id + '. Fuente: elaboración propia.')
        Add-WPara 'Especificación del caso de uso' 'Heading2'
        Add-WPara ('Tabla ' + $numeroTabla + '. Especificación del caso de uso: ' + $h.titulo) 'Caption'
        Add-WTable @('Elemento','Descripción') $h.especificacion
        $numeroTabla++
        Add-WPara 'Diagrama de secuencia' 'Heading2'
        Guardar-Svg ($h.base + '-secuencia.svg') (Svg-Secuencia $h.participantes $h.mensajes)
        Add-WFigure ($h.base + '-secuencia.svg') ('Figura ' + ($script:figuraNumero + 1) + '. Secuencia de ' + $h.id + '. Fuente: elaboración propia.')
        Add-WPara 'Diagrama de actividades' 'Heading2'
        Guardar-Svg ($h.base + '-actividad.svg') (Svg-Actividad $h.pasos $h.decision $h.error)
        Add-WFigure ($h.base + '-actividad.svg') ('Figura ' + ($script:figuraNumero + 1) + '. Actividades de ' + $h.id + '. Fuente: elaboración propia.')
        Add-WPara 'Mockup' 'Heading2'
        $tituloMockup = switch ($h.id) { 'HU05' { 'Estudiantes' } 'HU06' { 'Tutores' } 'HU07' { 'Proyecto' } 'HU08' { 'Buscar proyectos' } }
        Guardar-Svg $h.mock (Svg-Mockup $tituloMockup $h.campos $h.columnas $h.acciones)
        Add-WFigure $h.mock ('Figura ' + ($script:figuraNumero + 1) + '. Wireframe monocromático de ' + $h.id + '. Fuente: elaboración propia.')
    }
    Add-WPara 'Diseño e implementaciones de interfaces' 'Heading1' -pageBreak
    foreach ($h in $historias) {
        Add-WPara ($h.id + ': ' + $h.titulo) 'Heading2'
        Add-WPara ('ESPACIO PARA CAPTURA REAL: ' + $h.captura) 'Heading3'
        Add-WPara 'Insertar la captura de la interfaz funcionando; ocultar correos, contraseñas y cualquier dato personal.'
    }
    Add-WPara 'Pruebas de Testing' 'Heading1' -pageBreak
    Add-WPara 'Las pruebas automatizadas se ejecutaron con PHPUnit y las pruebas Feature de Laravel, usando datos aislados y almacenamiento simulado. Se seleccionaron escenarios correspondientes a HU05–HU08; los resultados se limitan a esa ejecución.'
    Add-WPara 'Se verificaron el registro e importación de estudiantes (HU05), la relación tutor-carrera (HU06), la gestión de proyectos y documentos PDF (HU07), y la búsqueda y consulta de documentos (HU08). En la ejecución seleccionada, PHPUnit reportó 14 pruebas y 79 aserciones aprobadas, sin fallos; este resultado corresponde al conjunto ejecutado para el Sprint 2.'
    Add-WPara 'Herramientas utilizadas' 'Heading2'
    Add-WPara 'PHPUnit 12.5.32 — ejecución de las pruebas automatizadas.'
    Add-WPara 'Pruebas Feature de Laravel — simulación de solicitudes HTTP y verificación de respuestas, validaciones y permisos.'
    Add-WPara 'RefreshDatabase y SQLite en memoria — preparación y aislamiento de los datos de prueba.'
    Add-WPara 'Storage Fake y UploadedFile — simulación de carga, consulta y sustitución de documentos.'
    Add-WPara 'Aserciones de Laravel — comprobación de respuestas, permisos, registros y archivos.'
    Add-WPara 'Tipos de prueba aplicados' 'Heading2'
    Add-WPara 'Pruebas funcionales y de integración, principalmente de caja negra: verifican solicitudes, respuestas, validaciones y flujos observables. Algunas aserciones consultan la base de prueba para confirmar la persistencia.'
    Add-WPara 'Controles puntuales de acceso e integridad: comprueban permisos de gestión, compatibilidad de las relaciones académicas y disponibilidad de los PDF. No equivalen a una auditoría de penetración ni a una suite independiente de caja blanca.'
    Add-WPara 'Tabla 20. Resultados de las pruebas' 'Caption'
    Add-WTable @('Caso / HU','Comprobaciones y resultado esperado','Tipo de prueba','Resultado') @(
        @('TC-05 / HU05','Registro e importación CSV de estudiantes; no se crea una cuenta automáticamente.','Funcional e integración; validación de datos.','Aprobado'),
        @('TC-06 / HU06','Asociación tutor-carrera y rechazo de un tutor incompatible con la carrera del proyecto.','Funcional e integración; integridad de relaciones.','Aprobado'),
        @('TC-07 / HU07','Edición de proyecto, carga o reemplazo de PDF, extracción de texto y permisos de gestión.','Funcional e integración; permisos y persistencia.','Aprobado'),
        @('TC-08 / HU08','Filtros por título o estudiante, apertura de PDF y respuesta si el archivo falta.','Funcional e integración; acceso al documento.','Aprobado')
    )
    Add-WPara 'Tabla 21. Archivos utilizados para las pruebas' 'Caption'
    Add-WTable @('Archivo','Cobertura incluida') @(
        @('tests/Feature/AcademicRecordsTest.php','Registro e importación de estudiantes; permisos académicos; compatibilidad tutor-carrera.'),
        @('tests/Feature/PdfEncodingTest.php','Carga y reemplazo del PDF y conservación correcta del texto extraído.'),
        @('tests/Feature/ProjectFilesTest.php','Edición, permisos, búsqueda del catálogo, consulta del PDF y archivo no disponible.')
    )
    Add-WPara 'Evidencia' 'Heading2'
    Add-WPara 'ESPACIO PARA CAPTURA REAL: salida del terminal con la ejecución de las 14 pruebas seleccionadas para HU05–HU08.' 'Heading3'
    Add-WPara 'Insertar una captura de php artisan test --testdox; presentar solo los casos filtrados del Sprint 2.'
    Add-WPara 'Escenarios Cubiertos' 'Heading2'
    Add-WPara '14 pruebas ejecutadas y aprobadas, con 79 aserciones en 2,71 segundos (02/11/2026).'
    Add-WPara 'HU05: registro, edición e importación de estudiantes mediante CSV, con validación de permisos.'
    Add-WPara 'HU06: registro y asociación de tutores con carreras, y selección de tutores compatibles.'
    Add-WPara 'HU07: edición de proyectos, carga o reemplazo de PDF, extracción de texto y permisos de gestión.'
    Add-WPara 'HU08: filtros del catálogo, consulta del PDF y respuesta ante documentos no disponibles.'
    $sect = New-WNode $script:wordXml $script:wordBody 'w' 'sectPr' $wNs
    $pgSz = New-WNode $script:wordXml $sect 'w' 'pgSz' $wNs; Set-WAttr $script:wordXml $pgSz 'w' 'w' $wNs '12240'; Set-WAttr $script:wordXml $pgSz 'w' 'h' $wNs '15840'
    $pgMar = New-WNode $script:wordXml $sect 'w' 'pgMar' $wNs; foreach ($pair in @(@('top','1440'),@('right','1440'),@('bottom','1440'),@('left','1440'),@('header','720'),@('footer','720'),@('gutter','0'))) { Set-WAttr $script:wordXml $pgMar 'w' $pair[0] $wNs $pair[1] }
    Add-ZipText $script:archive '[Content_Types].xml' (Xml-String $contentTypes)
    Add-ZipText $script:archive '_rels/.rels' (Xml-String $pkgRels)
    Add-ZipText $script:archive 'word/document.xml' (Xml-String $script:wordXml)
    Add-ZipText $script:archive 'word/styles.xml' (New-WStyles)
    Add-ZipText $script:archive 'word/_rels/document.xml.rels' (Xml-String $script:relsXml)
}
finally { $script:archive.Dispose() }

$verifica = [System.IO.Compression.ZipFile]::OpenRead($salida)
try {
    foreach ($parte in @('word/document.xml','word/styles.xml','word/_rels/document.xml.rels','word/media/figura-43.png')) {
        if (-not $verifica.GetEntry($parte)) { throw ('Falta una parte del documento: ' + $parte) }
    }
    $reader = [System.IO.StreamReader]::new($verifica.GetEntry('word/document.xml').Open(), [System.Text.Encoding]::UTF8)
    [xml]$xmlVerificado = $reader.ReadToEnd(); $reader.Dispose()
    if ($xmlVerificado.OuterXml -notmatch '14 pruebas y 79 aserciones') { throw 'No se encontró el resultado de pruebas esperado.' }
    if ($xmlVerificado.OuterXml -notmatch 'HU08') { throw 'No se encontraron las cuatro historias del Sprint 2.' }
}
finally { $verifica.Dispose() }
Write-Output ('Documento Word creado y validado: ' + $salida)
Write-Output ('Diagramas y mockups: ' + $carpeta)
return

$doc = $word.Documents.Add()
$wdFormatDocx = 16
$azulClaro = 15395562
$normal = $doc.Styles.Item('Normal')
$normal.Font.Name = 'Arial'
$normal.Font.Size = 11
$normal.ParagraphFormat.SpaceAfter = 6
$doc.PageSetup.TopMargin = 70.85
$doc.PageSetup.BottomMargin = 70.85
$doc.PageSetup.LeftMargin = 70.85
$doc.PageSetup.RightMargin = 70.85

function Agregar-Parrafo([string]$texto, [string]$estilo = 'Normal', [switch]$NuevaPagina, [switch]$Centrado) {
    $inicio = $doc.Content.End - 1
    $r = $doc.Range($inicio, $inicio)
    $r.InsertAfter($texto)
    $fin = $doc.Content.End - 1
    $p = $doc.Range($inicio, $fin)
    $estiloId = switch ($estilo) {
        'Title' { -63 }
        'Heading 1' { -2 }
        'Heading 2' { -3 }
        'Heading 3' { -4 }
        'Caption' { -35 }
        default { -1 }
    }
    $p.Style = $estiloId
    if ($NuevaPagina) { $p.ParagraphFormat.PageBreakBefore = $true }
    if ($Centrado) { $p.ParagraphFormat.Alignment = 1 }
    $p.InsertParagraphAfter()
}

function Agregar-Tabla([string[]]$encabezados, [object[]]$filas) {
    $inicio = $doc.Content.End - 1
    $r = $doc.Range($inicio, $inicio)
    $tabla = $doc.Tables.Add($r, ($filas.Count + 1), $encabezados.Count)
    $tabla.Borders.Enable = 1
    $tabla.AllowAutoFit = $true
    $tabla.AutoFitBehavior(2)
    $tabla.Range.Font.Name = 'Arial'
    $tabla.Range.Font.Size = 9
    for ($c = 0; $c -lt $encabezados.Count; $c++) {
        $celda = $tabla.Cell(1, $c + 1)
        $celda.Range.Text = [string]$encabezados[$c]
        $celda.Range.Bold = $true
        $celda.Shading.BackgroundPatternColor = $azulClaro
    }
    for ($f = 0; $f -lt $filas.Count; $f++) {
        for ($c = 0; $c -lt $encabezados.Count; $c++) {
            $tabla.Cell($f + 2, $c + 1).Range.Text = [string]$filas[$f][$c]
        }
    }
    $tabla.Range.ParagraphFormat.SpaceAfter = 2
    $despues = $doc.Range($tabla.Range.End, $tabla.Range.End)
    $despues.InsertParagraphAfter()
}

function Agregar-Figura([string]$archivo, [string]$leyenda) {
    $ruta = Convertir-SvgPng $archivo
    $inicio = $doc.Content.End - 1
    $r = $doc.Range($inicio, $inicio)
    $imagen = $doc.InlineShapes.AddPicture($ruta, $false, $true, $r)
    $imagen.LockAspectRatio = -1
    $imagen.Width = 450
    $r2 = $doc.Range($doc.Content.End - 1, $doc.Content.End - 1)
    $r2.InsertParagraphAfter()
    Agregar-Parrafo $leyenda 'Caption' -Centrado
}

function Agregar-EspacioCaptura([string]$texto) {
    Agregar-Parrafo ('ESPACIO PARA CAPTURA REAL: ' + $texto) 'Heading 3'
    Agregar-Parrafo 'Insertar aquí la captura tomada del sistema; ocultar correos, contraseñas y datos personales antes de incorporarla.'
}

Agregar-Parrafo 'SPRINT 2: REPOSITORIO ACADÉMICO' 'Title' -Centrado
Agregar-Parrafo 'El Sprint 2 reúne las historias HU05 a HU08 del backlog: registro e importación de estudiantes, gestión de tutores y carreras de asesoría, registro y edición de proyectos, y búsqueda de proyectos con consulta de su PDF asociado.'
Agregar-Parrafo 'Objetivo del Sprint' 'Heading 1'
Agregar-Parrafo 'Construir el repositorio académico para organizar estudiantes, tutores y proyectos de titulación; facilitar la búsqueda de sus registros y permitir la consulta del documento PDF, respetando las relaciones académicas y los permisos de acceso.'
Agregar-Parrafo 'Sprint Backlog' 'Heading 1'
Agregar-Tabla @('Historia','Trabajo del sprint','Comprobación disponible') @(
    @('HU05','Registrar, editar, buscar e importar estudiantes mediante CSV.','AcademicRecordsTest: registro e importación.'),
    @('HU06','Registrar tutores y relacionarlos con una o más carreras para asignarlos a proyectos compatibles.','AcademicRecordsTest: relación tutor-carrera y compatibilidad.'),
    @('HU07','Registrar y editar proyectos con modalidad, carrera, estudiante, tutor opcional y documento PDF.','PdfEncodingTest y ProjectFilesTest.'),
    @('HU08','Buscar y filtrar proyectos, y abrir el PDF asociado cuando esté disponible.','ProjectFilesTest: filtros y consulta del PDF.')
)

$figura = 1
foreach ($h in $historias) {
    Agregar-Parrafo ($h.id + ': ' + $h.titulo) 'Heading 1' -NuevaPagina
    Agregar-Parrafo $h.historia
    Agregar-Parrafo 'Diagrama de caso de uso' 'Heading 2'
    Guardar-Svg ($h.base + '-caso-uso.svg') (Svg-CasoUso $h.actor $h.casos)
    Agregar-Figura ($h.base + '-caso-uso.svg') ('Figura ' + $figura + '. Caso de uso de ' + $h.id + '. Fuente: elaboración propia.')
    $figura++
    Agregar-Parrafo 'Especificación del caso de uso' 'Heading 2'
    Agregar-Tabla @('Elemento','Descripción') $h.especificacion
    Agregar-Parrafo 'Diagrama de secuencia' 'Heading 2'
    Guardar-Svg ($h.base + '-secuencia.svg') (Svg-Secuencia $h.participantes $h.mensajes)
    Agregar-Figura ($h.base + '-secuencia.svg') ('Figura ' + $figura + '. Secuencia de ' + $h.id + '. Fuente: elaboración propia.')
    $figura++
    Agregar-Parrafo 'Diagrama de actividades' 'Heading 2'
    Guardar-Svg ($h.base + '-actividad.svg') (Svg-Actividad $h.pasos $h.decision $h.error)
    Agregar-Figura ($h.base + '-actividad.svg') ('Figura ' + $figura + '. Actividades de ' + $h.id + '. Fuente: elaboración propia.')
    $figura++
    Agregar-Parrafo 'Mockup' 'Heading 2'
    $tituloMockup = $h.titulo
    $tituloMockup = $tituloMockup -replace '^[^:]+:\s*', ''
    Guardar-Svg $h.mock (Svg-Mockup $tituloMockup $h.campos $h.columnas $h.acciones)
    Agregar-Figura $h.mock ('Figura ' + $figura + '. Wireframe en blanco y negro de ' + $h.id + '. Fuente: elaboración propia.')
    $figura++
    Agregar-Parrafo 'Captura de la interfaz implementada' 'Heading 2'
    Agregar-EspacioCaptura $h.captura
}

Agregar-Parrafo 'Pruebas de testing' 'Heading 1' -NuevaPagina
Agregar-Parrafo 'Las pruebas automatizadas se ejecutaron con PHPUnit y las pruebas Feature de Laravel, usando datos aislados y almacenamiento simulado. Se seleccionaron los escenarios correspondientes a HU05–HU08; los resultados de esta sección se limitan a esa ejecución.'
Agregar-Parrafo 'Tipos de prueba aplicados' 'Heading 2'
Agregar-Parrafo 'Pruebas funcionales y de integración, con enfoque de caja negra: verifican solicitudes, respuestas, validaciones y flujos de las historias. Algunas aserciones comprueban el estado persistido en la base de prueba. También se realizaron controles puntuales de permisos e integridad; no equivalen a una auditoría de penetración ni a una suite independiente de caja blanca.'
Agregar-Parrafo 'Resultados de la ejecución' 'Heading 2'
Agregar-Tabla @('Caso / HU','Comprobación resumida','Tipo de prueba','Resultado') @(
    @('TC-05 / HU05','Registro e importación CSV de estudiantes; no se crea una cuenta automáticamente.','Funcional e integración; validación de datos.','Aprobado'),
    @('TC-06 / HU06','Asociación tutor-carrera y rechazo de un tutor incompatible con la carrera del proyecto.','Funcional e integración; integridad de relaciones.','Aprobado'),
    @('TC-07 / HU07','Edición de proyecto, carga o reemplazo de PDF, extracción de texto y permisos de gestión.','Funcional e integración; permisos y persistencia.','Aprobado'),
    @('TC-08 / HU08','Filtros por título o estudiante, apertura de PDF y respuesta si el archivo falta.','Funcional e integración; acceso al documento.','Aprobado')
)
Agregar-Parrafo 'En la ejecución seleccionada se aprobaron 14 pruebas y 79 aserciones, sin fallos, en 2,71 segundos (02/11/2026). La cifra corresponde al conjunto seleccionado para el Sprint 2; no representa el total del proyecto.'
Agregar-Parrafo 'Herramientas utilizadas' 'Heading 2'
Agregar-Parrafo '• PHPUnit 12.5.32 y Laravel Feature para ejecutar escenarios HTTP automatizados.'
Agregar-Parrafo '• RefreshDatabase y SQLite en memoria para aislar los datos de prueba.'
Agregar-Parrafo '• Storage Fake y UploadedFile para simular carga, consulta y sustitución de documentos.'
Agregar-Parrafo '• Aserciones de Laravel para verificar respuestas, permisos, registros y archivos.'
Agregar-Parrafo 'Archivos de prueba ejecutados' 'Heading 2'
Agregar-Tabla @('Archivo','Cobertura incluida') @(
    @('tests/Feature/AcademicRecordsTest.php','Registro e importación de estudiantes; permisos académicos; compatibilidad tutor-carrera.'),
    @('tests/Feature/PdfEncodingTest.php','Carga y reemplazo del PDF y conservación correcta del texto extraído.'),
    @('tests/Feature/ProjectFilesTest.php','Edición, permisos, búsqueda del catálogo, consulta del PDF y archivo no disponible.')
)
Agregar-Parrafo 'Evidencia de ejecución' 'Heading 2'
Agregar-EspacioCaptura 'salida del terminal con la ejecución de las 14 pruebas seleccionadas para HU05–HU08.'

try {
    $doc.SaveAs2($salida, $wdFormatDocx)
    $doc.Close($false)
    $word.Quit()
}
finally {
    if ($doc) { [Runtime.InteropServices.Marshal]::ReleaseComObject($doc) | Out-Null }
    if ($word) { [Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null }
}
Write-Output ('Documento creado: ' + $salida)
Write-Output ('Diagramas y mockups: ' + $carpeta)
