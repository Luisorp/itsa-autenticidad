$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$outputPath = Join-Path $PSScriptRoot 'INFORME_PRUEBAS_SPRINT_1_HU01-HU04.docx'
if (Test-Path -LiteralPath $outputPath) {
    throw "Se evita sobrescribir el documento existente: $outputPath"
}

$script:wNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
$script:xmlNs = 'http://www.w3.org/XML/1998/namespace'

function New-WElement([System.Xml.XmlDocument]$Document, [System.Xml.XmlNode]$Parent, [string]$Name) {
    $element = $Document.CreateElement('w', $Name, $script:wNs)
    [void]$Parent.AppendChild($element)
    return ,$element
}

function Set-WAttribute([System.Xml.XmlDocument]$Document, [System.Xml.XmlElement]$Element, [string]$Name, [string]$Value) {
    $attribute = $Document.CreateAttribute('w', $Name, $script:wNs)
    $attribute.Value = $Value
    [void]$Element.Attributes.Append($attribute)
}

function Add-Paragraph(
    [System.Xml.XmlDocument]$Document,
    [System.Xml.XmlElement]$Body,
    [string]$Text,
    [string]$Style = 'Normal',
    [switch]$Bold,
    [switch]$Italic
) {
    $paragraph = New-WElement $Document $Body 'p'
    if ($Style) {
        $properties = New-WElement $Document $paragraph 'pPr'
        $paragraphStyle = New-WElement $Document $properties 'pStyle'
        Set-WAttribute $Document $paragraphStyle 'val' $Style
    }
    $run = New-WElement $Document $paragraph 'r'
    if ($Bold -or $Italic) {
        $runProperties = New-WElement $Document $run 'rPr'
        if ($Bold) { [void](New-WElement $Document $runProperties 'b') }
        if ($Italic) { [void](New-WElement $Document $runProperties 'i') }
    }
    $textElement = New-WElement $Document $run 't'
    [void]$textElement.SetAttribute('space', $script:xmlNs, 'preserve')
    $textElement.InnerText = $Text
}

function Add-Table(
    [System.Xml.XmlDocument]$Document,
    [System.Xml.XmlElement]$Body,
    [string[][]]$Rows,
    [int[]]$Widths
) {
    if ($Rows.Count -eq 0) { return }
    $columnCount = $Widths.Count
    $table = New-WElement $Document $Body 'tbl'
    $tableProperties = New-WElement $Document $table 'tblPr'
    $tableWidth = New-WElement $Document $tableProperties 'tblW'
    Set-WAttribute $Document $tableWidth 'w' ([string](($Widths | Measure-Object -Sum).Sum))
    Set-WAttribute $Document $tableWidth 'type' 'dxa'
    $layout = New-WElement $Document $tableProperties 'tblLayout'
    Set-WAttribute $Document $layout 'type' 'fixed'
    $borders = New-WElement $Document $tableProperties 'tblBorders'
    foreach ($side in @('top', 'left', 'bottom', 'right', 'insideH', 'insideV')) {
        $border = New-WElement $Document $borders $side
        Set-WAttribute $Document $border 'val' 'single'
        Set-WAttribute $Document $border 'sz' '5'
        Set-WAttribute $Document $border 'space' '0'
        Set-WAttribute $Document $border 'color' 'CBD5D0'
    }
    $margins = New-WElement $Document $tableProperties 'tblCellMar'
    foreach ($side in @('top', 'start', 'bottom', 'end')) {
        $margin = New-WElement $Document $margins $side
        Set-WAttribute $Document $margin 'w' '90'
        Set-WAttribute $Document $margin 'type' 'dxa'
    }
    $grid = New-WElement $Document $table 'tblGrid'
    foreach ($width in $Widths) {
        $gridColumn = New-WElement $Document $grid 'gridCol'
        Set-WAttribute $Document $gridColumn 'w' ([string]$width)
    }
    for ($rowIndex = 0; $rowIndex -lt $Rows.Count; $rowIndex++) {
        $row = New-WElement $Document $table 'tr'
        if ($rowIndex -eq 0) {
            $rowProperties = New-WElement $Document $row 'trPr'
            [void](New-WElement $Document $rowProperties 'tblHeader')
        }
        for ($column = 0; $column -lt $columnCount; $column++) {
            $cell = New-WElement $Document $row 'tc'
            $cellProperties = New-WElement $Document $cell 'tcPr'
            $cellWidth = New-WElement $Document $cellProperties 'tcW'
            Set-WAttribute $Document $cellWidth 'w' ([string]$Widths[$column])
            Set-WAttribute $Document $cellWidth 'type' 'dxa'
            $shade = New-WElement $Document $cellProperties 'shd'
            if ($rowIndex -eq 0) {
                Set-WAttribute $Document $shade 'fill' '174C3A'
            } elseif (($rowIndex % 2) -eq 0) {
                Set-WAttribute $Document $shade 'fill' 'F2F6F3'
            } else {
                Set-WAttribute $Document $shade 'fill' 'FFFFFF'
            }
            $paragraph = New-WElement $Document $cell 'p'
            $paragraphProperties = New-WElement $Document $paragraph 'pPr'
            $paragraphStyle = New-WElement $Document $paragraphProperties 'pStyle'
            Set-WAttribute $Document $paragraphStyle 'val' 'TableText'
            $run = New-WElement $Document $paragraph 'r'
            if ($rowIndex -eq 0) {
                $runProperties = New-WElement $Document $run 'rPr'
                [void](New-WElement $Document $runProperties 'b')
                $fontColor = New-WElement $Document $runProperties 'color'
                Set-WAttribute $Document $fontColor 'val' 'FFFFFF'
            }
            $textElement = New-WElement $Document $run 't'
            [void]$textElement.SetAttribute('space', $script:xmlNs, 'preserve')
            if ($column -lt $Rows[$rowIndex].Count) {
                $textElement.InnerText = [string]$Rows[$rowIndex][$column]
            }
        }
    }
}

$document = New-Object System.Xml.XmlDocument
$document.PreserveWhitespace = $true
$root = $document.CreateElement('w', 'document', $script:wNs)
$root.SetAttribute('xmlns:w', $script:wNs)
[void]$document.AppendChild($root)
$body = New-WElement $document $root 'body'

Add-Paragraph $document $body 'PRUEBAS DEL SPRINT 1' 'Title'
Add-Paragraph $document $body 'Verificación de las Historias de Usuario HU01–HU04' 'Subtitle'
Add-Paragraph $document $body 'Sistema de gestión y análisis de proyectos de titulación' 'Subtitle'
Add-Paragraph $document $body 'Fecha de ejecución verificada: 02 de noviembre de 2026' 'Subtitle'

Add-Paragraph $document $body '1. Propósito y alcance' 'Heading1'
Add-Paragraph $document $body 'Este informe resume las comprobaciones automatizadas relacionadas con las cuatro historias del Sprint 1: acceso al sistema (HU01), gestión de cuentas y roles (HU02), gestión de carreras (HU03) y actualización del perfil y la contraseña (HU04). Se revisaron las pruebas Feature de Laravel y se ejecutaron los archivos indicados en la sección de evidencia.'

Add-Paragraph $document $body '2. Resultado general de la ejecución' 'Heading1'
$summaryRows = [System.Collections.Generic.List[string[]]]::new()
$summaryRows.Add([string[]]@('Indicador', 'Resultado'))
$summaryRows.Add([string[]]@('Comando', 'PHPUnit mediante php artisan test --testdox'))
$summaryRows.Add([string[]]@('Entorno observado', 'PHP 8.3.30 · PHPUnit 12.5.32 · SQLite en memoria'))
$summaryRows.Add([string[]]@('Archivos de prueba ejecutados', '4 archivos Feature seleccionados; la ejecución incluye casos adicionales fuera del alcance del Sprint 1.'))
$summaryRows.Add([string[]]@('Resultado global', '19 pruebas aprobadas · 193 aserciones · 0 fallos'))
$summaryRows.Add([string[]]@('Estado del proceso', 'Correcto (código de salida 0)'))
Add-Table $document $body $summaryRows.ToArray() @(2450, 6910)
Add-Paragraph $document $body 'El resultado de 19 pruebas corresponde a los cuatro archivos ejecutados como conjunto, no a 19 casos exclusivos de HU01–HU04. En PresentationReadinessTest también se ejecutan comprobaciones de respaldo y generación de PDF, que no se contabilizan como pruebas de estas historias.' 'SmallNote' -Italic

Add-Paragraph $document $body '3. Tipos de prueba aplicados' 'Heading1'
Add-Paragraph $document $body 'Pruebas funcionales y de integración: se verifican solicitudes, respuestas, redirecciones, validaciones y persistencia de datos. Son pruebas Feature de Laravel y están principalmente orientadas al comportamiento observable (enfoque de caja negra); algunas también consultan la base de datos de prueba para confirmar el resultado.'
Add-Paragraph $document $body 'Controles puntuales de seguridad: se comprueba el rechazo de credenciales incorrectas, la restricción de rutas según el rol, la protección frente a la desactivación del administrador actual y la exigencia de la contraseña vigente para cambiarla. Estos controles no equivalen a una auditoría o prueba de penetración completa.'
Add-Paragraph $document $body 'Caja blanca: no se encontraron pruebas unitarias o de cobertura específicas para la lógica de HU01–HU04 en esta ejecución. Por tanto, no se reporta caja blanca como realizada. PHPUnit es la herramienta que ejecuta pruebas; no es por sí misma un tipo de prueba.'

Add-Paragraph $document $body '4. Casos de prueba y resultados por HU' 'Heading1'
$caseRows = [System.Collections.Generic.List[string[]]]::new()
$caseRows.Add([string[]]@('Caso / HU', 'Comprobaciones y resultado esperado', 'Tipo de prueba', 'Resultado'))
$caseRows.Add([string[]]@('TC-01 / HU01 — Inicio y cierre de sesión', 'La pantalla /login responde correctamente. Credenciales válidas autentican y redirigen al panel; una contraseña incorrecta no autentica; cerrar sesión finaliza la autenticación.', 'Funcional/de integración (Feature), enfoque de caja negra. Rechazo de credenciales como control puntual de autenticación.', 'Aprobado'))
$caseRows.Add([string[]]@('TC-02 / HU02 — Cuentas y roles', 'Se verifican las rutas permitidas o denegadas para Administrador, Gestor y Usuario. Se crea, actualiza y desactiva una cuenta; se rechaza la autodesactivación del administrador y se conserva el proyecto relacionado.', 'Funcional/de integración. Control de acceso por roles y protección de cuenta administrativa.', 'Aprobado'))
$caseRows.Add([string[]]@('TC-03 / HU03 — Gestión de carreras', 'El administrador crea y actualiza una carrera y cambia su estado a inactiva. Se comprueba que el proyecto relacionado se conserve y que los roles sin permiso no accedan a la administración.', 'Funcional/de integración. Autorización por rol y comprobación de integridad de datos.', 'Aprobado'))
$caseRows.Add([string[]]@('TC-04 / HU04 — Perfil y contraseña', 'Se muestra y actualiza el perfil. Se rechaza un correo ya registrado. Se permite cambiar la contraseña con la contraseña actual correcta y confirmación coincidente; se rechazan los datos incorrectos.', 'Funcional/de integración y validación. Comprobaciones puntuales de control de contraseña.', 'Aprobado'))
Add-Table $document $body $caseRows.ToArray() @(1450, 3740, 3300, 870)
Add-Paragraph $document $body 'HU02 y HU03 comparten el método automatizado test_career_and_account_management_preserves_historical_records, que valida ambas gestiones en un mismo escenario. Los resultados se asignan a cada HU según las aserciones correspondientes.' 'SmallNote' -Italic

Add-Paragraph $document $body '5. Archivos y escenarios que sustentan la tabla' 'Heading1'
$sourceRows = [System.Collections.Generic.List[string[]]]::new()
$sourceRows.Add([string[]]@('Archivo', 'Escenarios utilizados'))
$sourceRows.Add([string[]]@('tests/Feature/Auth/AuthenticationTest.php', 'Pantalla de acceso, autenticación válida, contraseña inválida y cierre de sesión (HU01).'))
$sourceRows.Add([string[]]@('tests/Feature/PresentationReadinessTest.php', 'Permisos de rutas para los tres roles; administración de cuentas y carreras; conservación del proyecto relacionado (HU02 y HU03).'))
$sourceRows.Add([string[]]@('tests/Feature/ProfileTest.php', 'Vista y actualización del perfil, correo duplicado y validaciones relacionadas (HU04).'))
$sourceRows.Add([string[]]@('tests/Feature/Auth/PasswordUpdateTest.php', 'Cambio de contraseña, confirmación coincidente y validación de contraseña actual (HU04).'))
Add-Table $document $body $sourceRows.ToArray() @(3950, 5410)

Add-Paragraph $document $body '6. Evidencia reproducible' 'Heading1'
Add-Paragraph $document $body 'Desde la raíz del proyecto, la ejecución realizada fue:'
Add-Paragraph $document $body '& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --testdox tests/Feature/Auth/AuthenticationTest.php tests/Feature/PresentationReadinessTest.php tests/Feature/ProfileTest.php tests/Feature/Auth/PasswordUpdateTest.php' 'CodeText'
Add-Paragraph $document $body 'Salida final observada:' 'Normal' -Bold
Add-Paragraph $document $body 'OK (19 tests, 193 assertions)' 'CodeText'
Add-Paragraph $document $body 'En la terminal puede capturarse la salida de PHPUnit como evidencia visual. El archivo phpunit.xml configura el entorno de pruebas con SQLite en memoria, por lo que estos tests no usan la base de datos de producción.'

Add-Paragraph $document $body '7. Conclusión' 'Heading1'
Add-Paragraph $document $body 'Las comprobaciones automatizadas asociadas a HU01–HU04 pasaron en la ejecución verificada. La evidencia respalda funcionalidad, integración y controles de acceso/validación específicos. No demuestra cobertura completa de caja blanca ni una evaluación integral de seguridad; esas afirmaciones no se incluyen en este informe.'

$section = New-WElement $document $body 'sectPr'
$pageSize = New-WElement $document $section 'pgSz'
Set-WAttribute $document $pageSize 'w' '11906'
Set-WAttribute $document $pageSize 'h' '16838'
$pageMargins = New-WElement $document $section 'pgMar'
Set-WAttribute $document $pageMargins 'top' '850'
Set-WAttribute $document $pageMargins 'right' '850'
Set-WAttribute $document $pageMargins 'bottom' '850'
Set-WAttribute $document $pageMargins 'left' '850'

$styles = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="20"/><w:color w:val="26332D"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault></w:docDefaults>
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="20"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:pPr><w:jc w:val="center"/><w:spacing w:before="700" w:after="220"/></w:pPr><w:rPr><w:b/><w:color w:val="174C3A"/><w:sz w:val="34"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:pPr><w:jc w:val="center"/><w:spacing w:after="100"/></w:pPr><w:rPr><w:color w:val="42594E"/><w:sz w:val="21"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="260" w:after="120"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:b/><w:color w:val="174C3A"/><w:sz w:val="25"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="TableText"><w:name w:val="Table Text"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:after="30" w:line="230" w:lineRule="auto"/></w:pPr><w:rPr><w:sz w:val="16"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="SmallNote"><w:name w:val="Small Note"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:before="80" w:after="100"/></w:pPr><w:rPr><w:i/><w:color w:val="53635B"/><w:sz w:val="18"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="CodeText"><w:name w:val="Code Text"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:before="60" w:after="100"/><w:ind w:left="180" w:right="180"/></w:pPr><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:color w:val="174C3A"/><w:sz w:val="16"/></w:rPr></w:style>
</w:styles>
'@

$contentTypes = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/><Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/></Types>
'@
$packageRelationships = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>
'@
$documentRelationships = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>
'@

$writerSettings = New-Object System.Xml.XmlWriterSettings
$writerSettings.Encoding = New-Object System.Text.UTF8Encoding($false)
$stringBuilder = New-Object System.Text.StringBuilder
$xmlWriter = [System.Xml.XmlWriter]::Create($stringBuilder, $writerSettings)
$document.Save($xmlWriter)
$xmlWriter.Dispose()
$documentXml = $stringBuilder.ToString() -replace 'encoding="utf-16"', 'encoding="UTF-8"'

$archive = [System.IO.Compression.ZipFile]::Open($outputPath, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $entries = @{
        '[Content_Types].xml' = $contentTypes
        '_rels/.rels' = $packageRelationships
        'word/document.xml' = $documentXml
        'word/styles.xml' = $styles
        'word/_rels/document.xml.rels' = $documentRelationships
    }
    foreach ($entryName in $entries.Keys) {
        $entry = $archive.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)
        $stream = $entry.Open()
        $writer = New-Object System.IO.StreamWriter($stream, (New-Object System.Text.UTF8Encoding($false)))
        $writer.Write([string]$entries[$entryName])
        $writer.Dispose()
        $stream.Dispose()
    }
}
finally {
    $archive.Dispose()
}

$check = [System.IO.Compression.ZipFile]::OpenRead($outputPath)
try {
    foreach ($required in @('word/document.xml', 'word/styles.xml', 'word/_rels/document.xml.rels')) {
        if (-not $check.GetEntry($required)) { throw "Falta una parte DOCX requerida: $required" }
    }
    $reader = New-Object System.IO.StreamReader($check.GetEntry('word/document.xml').Open(), [System.Text.Encoding]::UTF8)
    [xml]$validatedDocument = $reader.ReadToEnd()
    $reader.Dispose()
    if ($validatedDocument.OuterXml -notmatch '19 pruebas aprobadas') { throw 'No se encontró el resultado de ejecución esperado en el DOCX.' }
}
finally {
    $check.Dispose()
}

Get-Item -LiteralPath $outputPath | Select-Object FullName, Length
Write-Output 'Documento Word creado y validado como paquete OpenXML.'
