$ErrorActionPreference='Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Drawing
$docs=Join-Path $PSScriptRoot '..'
$source=Join-Path $docs '04_Actividad_2_Comparacion_modulo_2.docx'
$output=Join-Path $docs 'Actividad_2_Sistema_de_Autenticidad_Pruebas_Ejecutadas_2026-10-25.docx'
$results=Get-Content (Join-Path $PSScriptRoot 'resultados.json') -Raw -Encoding UTF8 | ConvertFrom-Json
$checks=Get-Content (Join-Path $PSScriptRoot 'verificacion-datos.json') -Raw -Encoding UTF8 | ConvertFrom-Json
function Fix-Utf8([string]$value){if($value.Contains([char]195) -or $value.Contains([char]194)){return [Text.Encoding]::UTF8.GetString([Text.Encoding]::GetEncoding(28591).GetBytes($value))};return $value}
if($results.results.Count -ne 6 -or @($results.results | Where-Object status -ne 'APROBADO').Count -ne 0){throw 'Se esperaba exactamente seis resultados aprobados.'}
if(@($checks.checks.PSObject.Properties | Where-Object Value -ne $true).Count -ne 0){throw 'La verificación de datos aislados tiene fallos.'}
if(Test-Path -LiteralPath $output){throw "No se sobrescribe una entrega previa: $output"}
[IO.File]::Copy($source,$output,$false)
$zip=[IO.Compression.ZipFile]::Open($output,[IO.Compression.ZipArchiveMode]::Update)
try {
  $w='http://schemas.openxmlformats.org/wordprocessingml/2006/main'
  $relNs='http://schemas.openxmlformats.org/officeDocument/2006/relationships'
  $wp='http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing'
  $a='http://schemas.openxmlformats.org/drawingml/2006/main'
  $pic='http://schemas.openxmlformats.org/drawingml/2006/picture'
  $pkgRel='http://schemas.openxmlformats.org/package/2006/relationships'
  $ct='http://schemas.openxmlformats.org/package/2006/content-types'
  $entry=$zip.GetEntry('word/document.xml');$reader=[IO.StreamReader]::new($entry.Open(),[Text.Encoding]::UTF8);[xml]$doc=$reader.ReadToEnd();$reader.Dispose();$entry.Delete()
  $doc.PreserveWhitespace=$true
  $n=[Xml.XmlNamespaceManager]::new($doc.NameTable);$n.AddNamespace('w',$w)
  $body=$doc.SelectSingleNode('/w:document/w:body',$n)
  function Get-Text($node){(($node.SelectNodes('.//w:t',$script:n)|ForEach-Object InnerText)-join '')}
  function Replace-Paragraph($needle,$replacement){
    $needle=Fix-Utf8 $needle;$replacement=Fix-Utf8 $replacement
    $p=$script:body.SelectNodes('./w:p',$script:n)|Where-Object {(Get-Text $_).StartsWith($needle)}|Select-Object -First 1
    if(-not $p){throw "No se localizó el párrafo: $needle"}
    $pp=$p.SelectSingleNode('./w:pPr',$script:n);foreach($child in @($p.ChildNodes)){if($child -ne $pp){[void]$p.RemoveChild($child)}}
    $run=$script:doc.CreateElement('w','r',$script:w);$text=$script:doc.CreateElement('w','t',$script:w);$text.InnerText=$replacement;[void]$run.AppendChild($text);[void]$p.AppendChild($run)
  }
  function Replace-All-Paragraphs($needle,$replacement){
    $needle=Fix-Utf8 $needle;$replacement=Fix-Utf8 $replacement
    foreach($p in @($script:body.SelectNodes('./w:p',$script:n)|Where-Object {(Get-Text $_).StartsWith($needle)})){
      $pp=$p.SelectSingleNode('./w:pPr',$script:n);foreach($child in @($p.ChildNodes)){if($child -ne $pp){[void]$p.RemoveChild($child)}}
      $run=$script:doc.CreateElement('w','r',$script:w);$text=$script:doc.CreateElement('w','t',$script:w);$text.InnerText=$replacement;[void]$run.AppendChild($text);[void]$p.AppendChild($run)
    }
  }
  function Replace-Cell($row,$index,$value){
    $value=Fix-Utf8 $value
    $cell=$row.SelectNodes('./w:tc',$script:n)[$index];$p=$cell.SelectSingleNode('./w:p',$script:n)
    foreach($extra in @($cell.SelectNodes('./w:p',$script:n))){if($extra -ne $p){[void]$cell.RemoveChild($extra)}}
    $pp=$p.SelectSingleNode('./w:pPr',$script:n);foreach($child in @($p.ChildNodes)){if($child -ne $pp){[void]$p.RemoveChild($child)}}
    $run=$script:doc.CreateElement('w','r',$script:w);$text=$script:doc.CreateElement('w','t',$script:w);$text.InnerText=$value;[void]$run.AppendChild($text);[void]$p.AppendChild($run)
  }
  Replace-Paragraph 'La comparación de herramientas es documental' 'La comparación de herramientas es documental, no un benchmark. Se ejecutaron seis casos funcionales sobre los formularios y rutas reales del proyecto, en la versión del repositorio 72b2451. Los casos seleccionados cubren alta con PDF, validación de campos, edición, búsqueda, rechazo de archivo y permisos.'
  Replace-Paragraph 'El archivo Word describe requisitos' 'La ejecución se realizó el 25 de octubre de 2026 contra el código local de este proyecto (commit 72b2451), mediante Microsoft Edge headless controlado con Playwright. Se usó una base SQLite temporal y datos ficticios; la configuración MySQL del archivo .env no se cambió ni se utilizó.'
  Replace-Paragraph 'Roles de referencia' 'Se probaron los roles Administrador y Estudiante. La cuenta Administrador pudo completar operaciones de gestión y la cuenta Estudiante recibió HTTP 403 al abrir el formulario restringido. El rol Docente no forma parte de estos seis casos.'
  Replace-Paragraph 'Preparación y ejecución' 'Entorno y procedimiento ejecutado'
  Replace-Paragraph '1. Trabajar sobre una copia local' '1. Se ejecutó el código local identificado por el commit 72b2451, con datos ficticios en una base SQLite independiente almacenada en la carpeta temporal del sistema. No se conectó a MySQL ni se modificó .env.'
  Replace-Paragraph '2. Comprobar la versión' '2. Se inspeccionaron las rutas de proyectos, los formularios Blade y las reglas de validación presentes en el código antes de automatizar las interacciones.'
  Replace-Paragraph '3. Preparar un estudiante' '3. Se crearon en la base temporal una carrera, una estudiante, un docente y dos proyectos de referencia. También se generó un PDF de prueba con texto conocido.'
  Replace-Paragraph '4. Preparar cuentas activas' '4. Se utilizaron cuentas ficticias de Administrador y Estudiante. Las credenciales no se incluyen en este documento.'
  Replace-Paragraph '5. Configurar settings.json' '5. Se adaptaron los selectores de Playwright a los nombres y controles que existen en los formularios actuales; no se modificó el HTML de la aplicación.'
  Replace-Paragraph '6. Instalar las dependencias' '6. Playwright se instaló fuera del proyecto, en una carpeta temporal, y se ejecutó con Microsoft Edge headless. No se alteraron package.json ni package-lock.json.'
  Replace-Paragraph '7. Iniciar la aplicación' '7. Se inició Laravel en http://127.0.0.1:8775 con un bootstrap de pruebas que fuerza y verifica explícitamente la conexión SQLite temporal.'
  Replace-Paragraph '8. Ejecutar npx playwright' '8. Se automatizaron seis casos con aserciones sobre la interfaz y respuestas HTTP. En la primera pasada se corrigió un selector ambiguo del script y se aisló el contexto de sesión del estudiante; luego se repitieron los seis casos en una base temporal limpia.'
  Replace-Paragraph '9. Registrar los resultados' '9. Se conservaron los resultados JSON y seis capturas de pantalla del navegador. Una verificación independiente confirmó el archivo PDF, su registro y el texto extraído, así como la ausencia de registros inválidos.'
  Replace-Paragraph '10. Corregir los defectos' '10. En la ejecución final, los seis casos aprobaron. No se modificó código de la aplicación; la corrección se limitó al script de automatización.'
  Replace-All-Paragraphs 'Estados: Aprobado' 'Estados utilizados: Aprobado = resultado observado conforme al esperado; Fallido = incumplimiento observado. Los seis casos de esta entrega se ejecutaron y tienen captura asociada.'
  Replace-All-Paragraphs 'Diseño de casos basado en el documento.' 'Resultados de la ejecución real del sistema en entorno local aislado. Cada caso incluye su captura individual.'
  Replace-Paragraph 'Para cada identificador:' 'Cada caso incluye fecha de ejecución, resultado esperado y observado, estado y captura real. Las cuentas y datos fueron ficticios. El PDF de prueba se almacenó dentro del directorio temporal aislado.'
  Replace-Paragraph 'El paquete contiene scripts base' 'Los archivos de ejecución y verificación que acompañan esta entrega están en docs/actividad2-ejecucion. La comparación entre Playwright, Selenium y Cypress es documental; solo Playwright se ejecutó en esta actividad.'
  Replace-Paragraph 'Cuando haya ejecución:' 'Resultado de la ejecución final: 6 aprobados, 0 fallidos, 0 bloqueados y 0 pendientes. Porcentaje de aprobación: 6 / (6 + 0) x 100 = 100 %. Esta tasa describe únicamente los seis casos ejecutados y no representa cobertura del código.'
  Replace-Paragraph 'La comparación documental justifica' 'La actividad compara documentalmente Playwright, Selenium WebDriver y Cypress, y ejecuta Playwright en seis flujos del sistema. Los seis casos aprobaron en un entorno local aislado. La evidencia demuestra esos comportamientos concretos, pero no permite afirmar ausencia total de defectos ni medir rendimiento o cobertura del código.'

  Replace-Paragraph 'La consigna permite utilizar cualquier herramienta' 'La actividad compara documentalmente Playwright, Selenium WebDriver y Cypress. Los casos practicos de esta entrega se ejecutaron con Playwright; no se presentan mediciones comparativas de rendimiento.'
  $tables=$doc.SelectNodes('//w:tbl',$n);$caseRowsA=@($tables[1].SelectNodes('./w:tr',$n));$caseRowsB=@($tables[2].SelectNodes('./w:tr',$n));
  $cases=@(
    @('M2-01 Registro y PDF (RF04/RF05)','Título, carrera, estudiante, año y PDF válidos; enviar formulario de registro.','El proyecto se registra, aparece en el catálogo y el PDF queda asociado.','APROBADO. Registro y mensaje de éxito observados; PDF asociado y texto extraído confirmado. Captura M2-01.'),
    @('M2-02 Título obligatorio (RF04)','Enviar el formulario dejando vacío el título y completando los demás datos válidos.','El servidor rechaza el envío y no crea un proyecto.','APROBADO. El campo título se marcó inválido; no se creó registro. Captura M2-02.'),
    @('M2-03 Editar proyecto (RF04)','Cambiar el título del proyecto de referencia y guardar.','El nuevo título persiste y se muestra confirmación.','APROBADO. Título actualizado y confirmación observada en el catálogo. Captura M2-03.'),
    @('M2-04 Buscar por título (RF06)','Consultar el catálogo usando el título exacto del proyecto recién creado.','Aparece el proyecto que coincide y no los registros ajenos a la búsqueda.','APROBADO. El resultado coincidente apareció y el segundo proyecto fue excluido. Captura M2-04.'),
    @('M2-05 Rechazar archivo no PDF (RF05)','Intentar registrar un proyecto adjuntando un archivo inocuo .txt.','El servidor rechaza el archivo y no crea el proyecto.','APROBADO. Documento marcado inválido; no se creó registro. Captura M2-05.'),
    @('M2-06 Restringir registro a estudiante (RNF03)','Iniciar sesión como Estudiante y solicitar GET /proyectos/create.','El servidor niega el acceso al formulario restringido.','APROBADO. Respuesta HTTP 403 recibida. Captura M2-06.')
  )
  for($i=0;$i -lt 4;$i++){for($c=0;$c -lt 4;$c++){Replace-Cell $caseRowsA[$i+1] $c $cases[$i][$c]}}
  for($i=4;$i -lt 6;$i++){for($c=0;$c -lt 4;$c++){Replace-Cell $caseRowsB[$i-3] $c $cases[$i][$c]}}
  foreach($idx in @(4,3)){[void]$caseRowsB[$idx].ParentNode.RemoveChild($caseRowsB[$idx])}
  $summary=@('Casos diseñados','6 seleccionados para esta ejecución','Casos ejecutados en el sistema','6','Casos aprobados','6','Casos fallidos','0','Casos restantes','0','Porcentaje de aprobación','100 % (6 de 6)');
  $summaryRows=$tables[3].SelectNodes('./w:tr',$n)
  for($i=1;$i -lt $summaryRows.Count;$i++){Replace-Cell $summaryRows[$i] 1 $summary[($i-1)*2+1]}

  $relsEntry=$zip.GetEntry('word/_rels/document.xml.rels');$rr=[IO.StreamReader]::new($relsEntry.Open());[xml]$rels=$rr.ReadToEnd();$rr.Dispose();$relsEntry.Delete();$relsRoot=$rels.DocumentElement
  $rids=@($relsRoot.ChildNodes | ForEach-Object { if($_.Id -match '^rId(\d+)$'){[int]$Matches[1]} });$nextRid=($rids|Measure-Object -Maximum).Maximum+1
  $typesEntry=$zip.GetEntry('[Content_Types].xml');$tr=[IO.StreamReader]::new($typesEntry.Open());[xml]$types=$tr.ReadToEnd();$tr.Dispose();$typesEntry.Delete()
  if(-not $types.DocumentElement.SelectSingleNode("*[local-name()='Default' and @Extension='png']")){$d=$types.CreateElement('Default',$ct);$d.SetAttribute('Extension','png');$d.SetAttribute('ContentType','image/png');[void]$types.DocumentElement.AppendChild($d)}
  function Add-TextParagraph([string]$text,[bool]$pageBreak=$false){
    $text=Fix-Utf8 $text
    $p=$doc.CreateElement('w','p',$w);if($pageBreak){$r=$doc.CreateElement('w','r',$w);$br=$doc.CreateElement('w','br',$w);$br.SetAttribute('type',$w,'page');[void]$r.AppendChild($br);[void]$p.AppendChild($r)}
    $r=$doc.CreateElement('w','r',$w);$t=$doc.CreateElement('w','t',$w);$t.InnerText=$text;[void]$r.AppendChild($t);[void]$p.AppendChild($r);[void]$body.InsertBefore($p,$body.SelectSingleNode('./w:sectPr',$n));return $p
  }
  $null=Add-TextParagraph 'Evidencias de ejecución · capturas reales de Microsoft Edge' $true
  $null=Add-TextParagraph 'Las capturas siguientes corresponden a los seis casos que se ejecutaron el 25 de octubre de 2026. Se muestran directamente desde el navegador de prueba; no son ilustraciones ni maquetas.'
  for($i=0;$i -lt 6;$i++){
    if($i -gt 0 -and $i % 2 -eq 0){$null=Add-TextParagraph '' $true}
    $result=$results.results[$i];$id=[string]$result.id;$imageName=$id+'.png';$imagePath=Join-Path $PSScriptRoot ('evidencias/'+$imageName);$rid='rId'+$nextRid;$nextRid++
    [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip,$imagePath,"word/media/$imageName",[IO.Compression.CompressionLevel]::Optimal)|Out-Null
    $rel=$rels.CreateElement('Relationship',$pkgRel);$rel.SetAttribute('Id',$rid);$rel.SetAttribute('Type',$relNs+'/image');$rel.SetAttribute('Target',"media/$imageName");[void]$relsRoot.AppendChild($rel)
    $captionText=$id+' - '+$result.title+' - APROBADO. '+$result.detail;$caption=Add-TextParagraph $captionText
    $p=$doc.CreateElement('w','p',$w);$pPr=$doc.CreateElement('w','pPr',$w);$jc=$doc.CreateElement('w','jc',$w);[void]$jc.SetAttribute('val',$w,'center');[void]$pPr.AppendChild($jc);[void]$p.AppendChild($pPr)
    $r=$doc.CreateElement('w','r',$w);$drawing=$doc.CreateElement('w','drawing',$w);$inline=$doc.CreateElement('wp','inline',$wp);foreach($att in @('distT','distB','distL','distR')){$inline.SetAttribute($att,'0')}
    $img=[System.Drawing.Image]::FromFile($imagePath);$cx=4800000;$cy=[long]($cx*$img.Height/$img.Width);$img.Dispose()
    $extent=$doc.CreateElement('wp','extent',$wp);$extent.SetAttribute('cx',[string]$cx);$extent.SetAttribute('cy',[string]$cy);[void]$inline.AppendChild($extent)
    $docPr=$doc.CreateElement('wp','docPr',$wp);$docPr.SetAttribute('id',[string](100+$i));$docPr.SetAttribute('name',$imageName);[void]$inline.AppendChild($docPr)
    $frame=$doc.CreateElement('wp','cNvGraphicFramePr',$wp);$locks=$doc.CreateElement('a','graphicFrameLocks',$a);$locks.SetAttribute('noChangeAspect','1');[void]$frame.AppendChild($locks);[void]$inline.AppendChild($frame)
    $graphic=$doc.CreateElement('a','graphic',$a);$gd=$doc.CreateElement('a','graphicData',$a);$gd.SetAttribute('uri',$pic);$picture=$doc.CreateElement('pic','pic',$pic)
    $nv=$doc.CreateElement('pic','nvPicPr',$pic);$cnv=$doc.CreateElement('pic','cNvPr',$pic);$cnv.SetAttribute('id',[string](100+$i));$cnv.SetAttribute('name',$imageName);[void]$nv.AppendChild($cnv);[void]$nv.AppendChild($doc.CreateElement('pic','cNvPicPr',$pic));[void]$picture.AppendChild($nv)
    $fill=$doc.CreateElement('pic','blipFill',$pic);$blip=$doc.CreateElement('a','blip',$a);$blip.SetAttribute('embed',$relNs,$rid);[void]$fill.AppendChild($blip);$stretch=$doc.CreateElement('a','stretch',$a);[void]$stretch.AppendChild($doc.CreateElement('a','fillRect',$a));[void]$fill.AppendChild($stretch);[void]$picture.AppendChild($fill)
    $sp=$doc.CreateElement('pic','spPr',$pic);$xfrm=$doc.CreateElement('a','xfrm',$a);$off=$doc.CreateElement('a','off',$a);$off.SetAttribute('x','0');$off.SetAttribute('y','0');[void]$xfrm.AppendChild($off);$ext=$doc.CreateElement('a','ext',$a);$ext.SetAttribute('cx',[string]$cx);$ext.SetAttribute('cy',[string]$cy);[void]$xfrm.AppendChild($ext);[void]$sp.AppendChild($xfrm);$geom=$doc.CreateElement('a','prstGeom',$a);$geom.SetAttribute('prst','rect');[void]$geom.AppendChild($doc.CreateElement('a','avLst',$a));[void]$sp.AppendChild($geom);[void]$picture.AppendChild($sp)
    [void]$gd.AppendChild($picture);[void]$graphic.AppendChild($gd);[void]$inline.AppendChild($graphic);[void]$drawing.AppendChild($inline);[void]$r.AppendChild($drawing);[void]$p.AppendChild($r);[void]$body.InsertBefore($p,$body.SelectSingleNode('./w:sectPr',$n))
  }
  function Save-ZipXml($zip,$name,$xml){$settings=[Xml.XmlWriterSettings]::new();$settings.Encoding=[Text.UTF8Encoding]::new($false);$e=$zip.CreateEntry($name,[IO.Compression.CompressionLevel]::Optimal);$s=$e.Open();$writer=[Xml.XmlWriter]::Create($s,$settings);$xml.Save($writer);$writer.Dispose();$s.Dispose()}
  Save-ZipXml $zip 'word/document.xml' $doc;Save-ZipXml $zip 'word/_rels/document.xml.rels' $rels;Save-ZipXml $zip '[Content_Types].xml' $types
}
finally{$zip.Dispose()}
$check=[IO.Compression.ZipFile]::OpenRead($output)
try{
  $required=@('word/document.xml','word/_rels/document.xml.rels')+@(1..6|ForEach-Object { 'word/media/M2-0' + $_ + '.png' })
  foreach($name in $required){if(-not $check.GetEntry($name)){throw 'Falta contenido requerido en el Word.'}}
  $e=$check.GetEntry('word/document.xml');$r=[IO.StreamReader]::new($e.Open());[xml]$verify=$r.ReadToEnd();$r.Dispose();$all=$verify.OuterXml
  if($all -match 'Sin ejecutar|No observado|Evidencia: pendiente|8 sin ejecutar'){throw 'El documento conserva un caso pendiente.'}
  if($all -notmatch '6 / \(6 \+ 0\)'){throw 'Falta el cálculo de aprobación.'}
}
finally{$check.Dispose()}
Get-Item $output | Select-Object FullName,Length
