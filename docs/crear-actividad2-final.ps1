$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$docs = $PSScriptRoot
$source = Join-Path $docs '04_Actividad_2_Comparacion_modulo_2.docx'
$output = Join-Path $docs 'Actividad_2_Comparacion_Automatizacion_Entrega_Final.docx'
$assets = Join-Path $docs 'actividad2-assets'
if (-not (Test-Path -LiteralPath $source)) { throw "Source document not found: $source" }
if (Test-Path -LiteralPath $output) { throw "Output already exists; refusing to overwrite: $output" }
New-Item -ItemType Directory -Path $assets -Force | Out-Null

function New-Diagram([string]$Path, [string]$Title, [string[]]$Labels, [string[]]$Details, [string[]]$Colors, [bool]$Arrows) {
    $bmp = [Drawing.Bitmap]::new(1500, 360)
    $g = [Drawing.Graphics]::FromImage($bmp)
    $g.SmoothingMode = [Drawing.Drawing2D.SmoothingMode]::AntiAlias
    $g.Clear([Drawing.Color]::FromArgb(247, 250, 248))
    $titleFont = [Drawing.Font]::new('Arial', 28, [Drawing.FontStyle]::Bold)
    $labelFont = [Drawing.Font]::new('Arial', 19, [Drawing.FontStyle]::Bold)
    $detailFont = [Drawing.Font]::new('Arial', 15, [Drawing.FontStyle]::Regular)
    $darkBrush = [Drawing.SolidBrush]::new([Drawing.Color]::FromArgb(28, 57, 43))
    $g.DrawString($Title, $titleFont, $darkBrush, 45, 28)
    $count = $Labels.Count
    $gap = 24
    $left = 42
    $top = 105
    $cardWidth = [int]((1416 - (($count - 1) * $gap)) / $count)
    $cardHeight = 208
    $boxBrushes = @()
    $pens = @()
    for ($i = 0; $i -lt $count; $i++) {
        $x = $left + ($i * ($cardWidth + $gap))
        $color = [Drawing.ColorTranslator]::FromHtml($Colors[$i])
        $boxBrush = [Drawing.SolidBrush]::new([Drawing.Color]::FromArgb(255, $color.R, $color.G, $color.B))
        $pen = [Drawing.Pen]::new([Drawing.Color]::FromArgb([Math]::Max(0,$color.R-45), [Math]::Max(0,$color.G-45), [Math]::Max(0,$color.B-45)), 3)
        $g.FillRectangle($boxBrush, $x, $top, $cardWidth, $cardHeight)
        $g.DrawRectangle($pen, $x, $top, $cardWidth, $cardHeight)
        $labelRect = [Drawing.RectangleF]::new(($x + 12), ($top + 42), ($cardWidth - 24), 42)
        $fmt = [Drawing.StringFormat]::new(); $fmt.Alignment = [Drawing.StringAlignment]::Center; $fmt.Trimming = [Drawing.StringTrimming]::EllipsisCharacter
        $g.DrawString($Labels[$i], $labelFont, $darkBrush, $labelRect, $fmt)
        $detailRect = [Drawing.RectangleF]::new(($x + 14), ($top + 103), ($cardWidth - 28), 72)
        $detailText = $Details[$i] -replace '\|', "`n"
        $g.DrawString($detailText, $detailFont, $darkBrush, $detailRect, $fmt)
        $boxBrush.Dispose(); $pen.Dispose(); $fmt.Dispose()
        if ($Arrows -and $i -lt ($count - 1)) {
            $arrowPen = [Drawing.Pen]::new([Drawing.Color]::FromArgb(82, 117, 101), 5)
            $arrowPen.EndCap = [Drawing.Drawing2D.LineCap]::ArrowAnchor
            $g.DrawLine($arrowPen, ($x + $cardWidth + 3), ($top + 104), ($x + $cardWidth + $gap - 4), ($top + 104))
            $arrowPen.Dispose()
        }
    }
    $bmp.Save($Path, [Drawing.Imaging.ImageFormat]::Png)
    $darkBrush.Dispose(); $titleFont.Dispose(); $labelFont.Dispose(); $detailFont.Dispose(); $g.Dispose(); $bmp.Dispose()
}

$pngs = @(
    @{ Name='flujo-pruebas.png'; Title='Flujo propuesto de automatizacion'; Labels=@('Requisitos','Playwright','Aserciones','Evidencias'); Details=@('RF04 / RF05 / RF06','Acciones en navegador','Esperado vs. observado','Reporte - captura - traza'); Colors=@('#DCEDE3','#DCEAF8','#FFF0D5','#F0E5F6'); Arrows=$true },
    @{ Name='herramientas.png'; Title='Herramientas consideradas - comparacion documental'; Labels=@('Playwright','Selenium WebDriver','Cypress'); Details=@('Seleccionada para la actividad|Acciones - aserciones - trazas','Alternativa WebDriver|Varios lenguajes disponibles','Alternativa interactiva|Comandos y reintentos'); Colors=@('#E0EFE7','#E2EAF6','#FFF0E6'); Arrows=$false },
    @{ Name='cobertura.png'; Title='Cobertura funcional propuesta - ocho casos pendientes'; Labels=@('Registro M2-01/02','Edicion M2-03','Consulta M2-04/05','Documentos y acceso'); Details=@('Valido y campos requeridos','Actualizar proyecto','Busqueda y detalle','M2-06 PDF / M2-07 formato / M2-08 permisos'); Colors=@('#E0EFE7','#DCEAF8','#FFF0D5','#F0E5F6'); Arrows=$false }
)

foreach ($item in $pngs) {
    $path = Join-Path $assets $item.Name
    New-Diagram -Path $path -Title $item.Title -Labels $item.Labels -Details $item.Details -Colors $item.Colors -Arrows $item.Arrows
}

function Add-Element($Document, $Parent, [string]$Prefix, [string]$LocalName, [string]$Namespace) {
    $element = $Document.CreateElement($Prefix, $LocalName, $Namespace)
    [void]$Parent.AppendChild($element)
    return $element
}
function Set-AttributeNS($Document, $Element, [string]$Prefix, [string]$LocalName, [string]$Namespace, [string]$Value) {
    $attribute = $Document.CreateAttribute($Prefix, $LocalName, $Namespace)
    $attribute.Value = $Value
    [void]$Element.Attributes.Append($attribute)
}

[IO.File]::Copy($source, $output, $false)
$zip = [IO.Compression.ZipFile]::Open($output, [IO.Compression.ZipArchiveMode]::Update)
try {
    $docEntry = $zip.GetEntry('word/document.xml')
    $reader = [IO.StreamReader]::new($docEntry.Open(), [Text.Encoding]::UTF8)
    $rawDocument = $reader.ReadToEnd(); $reader.Dispose(); $docEntry.Delete()
    $rawDocument = [regex]::Replace($rawDocument,'Archivo ejecutable ficticio con extensi.n \.exe','Archivo de texto inocuo con extension .txt')
    [xml]$xml = $rawDocument
    $xml.PreserveWhitespace = $true
    $wNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
    $rNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships'
    $wpNs = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing'
    $aNs = 'http://schemas.openxmlformats.org/drawingml/2006/main'
    $picNs = 'http://schemas.openxmlformats.org/drawingml/2006/picture'
    $ns = [Xml.XmlNamespaceManager]::new($xml.NameTable); $ns.AddNamespace('w',$wNs)
    $body = $xml.SelectSingleNode('//w:body',$ns)
    $section = $body.SelectSingleNode('./w:sectPr',$ns)
    $paragraphs = [Collections.Generic.List[Xml.XmlNode]]::new()

    $breakP = $xml.CreateElement('w','p',$wNs); $breakRun = Add-Element $xml $breakP 'w' 'r' $wNs; $break = Add-Element $xml $breakRun 'w' 'br' $wNs; Set-AttributeNS $xml $break 'w' 'type' $wNs 'page'; $paragraphs.Add($breakP)
    $heading = $xml.CreateElement('w','p',$wNs); $hp = Add-Element $xml $heading 'w' 'pPr' $wNs; $ps = Add-Element $xml $hp 'w' 'pStyle' $wNs; Set-AttributeNS $xml $ps 'w' 'val' $wNs 'Heading1'; $hr=Add-Element $xml $heading 'w' 'r' $wNs; $ht=Add-Element $xml $hr 'w' 't' $wNs; $ht.InnerText='Apoyo visual de la actividad'; $paragraphs.Add($heading)
    $note = $xml.CreateElement('w','p',$wNs); $nr=Add-Element $xml $note 'w' 'r' $wNs; $nt=Add-Element $xml $nr 'w' 't' $wNs; $nt.InnerText='Diagramas ilustrativos. No son capturas ni resultados de pruebas ejecutadas.'; $paragraphs.Add($note)

    $relsEntry = $zip.GetEntry('word/_rels/document.xml.rels')
    $rr = [IO.StreamReader]::new($relsEntry.Open(),[Text.Encoding]::UTF8); [xml]$rels=$rr.ReadToEnd();$rr.Dispose();$relsEntry.Delete()
    $relRoot=$rels.DocumentElement; $relNs='http://schemas.openxmlformats.org/package/2006/relationships'
    $typesEntry=$zip.GetEntry('[Content_Types].xml');$tr=[IO.StreamReader]::new($typesEntry.Open(),[Text.Encoding]::UTF8);[xml]$types=$tr.ReadToEnd();$tr.Dispose();$typesEntry.Delete()
    $ctNs='http://schemas.openxmlformats.org/package/2006/content-types'
    if (-not $types.DocumentElement.SelectSingleNode("*[local-name()='Default' and @Extension='png']")) { $d=$types.CreateElement('Default',$ctNs);$d.SetAttribute('Extension','png');$d.SetAttribute('ContentType','image/png');[void]$types.DocumentElement.AppendChild($d) }

    $figures=@(
      @{ File='flujo-pruebas.png'; Caption='Figura 1. Flujo propuesto: requisitos, automatizacion, aserciones y evidencias.'; Rel='rId11' },
      @{ File='herramientas.png'; Caption='Figura 2. Herramientas comparadas documentalmente para la actividad.'; Rel='rId12' },
      @{ File='cobertura.png'; Caption='Figura 3. Cobertura funcional propuesta para los ocho casos.'; Rel='rId13' }
    )
    $imgNo=1
    foreach ($fig in $figures) {
        $mediaPath=Join-Path $assets $fig.File
        [IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip,$mediaPath,"word/media/$($fig.File)",[IO.Compression.CompressionLevel]::Optimal) | Out-Null
        $rel=$rels.CreateElement('Relationship',$relNs);$rel.SetAttribute('Id',$fig.Rel);$rel.SetAttribute('Type','http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');$rel.SetAttribute('Target',"media/$($fig.File)");[void]$relRoot.AppendChild($rel)

        $p=$xml.CreateElement('w','p',$wNs);$pPr=Add-Element $xml $p 'w' 'pPr' $wNs;$jc=Add-Element $xml $pPr 'w' 'jc' $wNs;Set-AttributeNS $xml $jc 'w' 'val' $wNs 'center'
        $run=Add-Element $xml $p 'w' 'r' $wNs;$drawing=Add-Element $xml $run 'w' 'drawing' $wNs;$inline=Add-Element $xml $drawing 'wp' 'inline' $wpNs
        foreach($pair in @(@('distT','0'),@('distB','0'),@('distL','0'),@('distR','0'))){Set-AttributeNS $xml $inline '' $pair[0] '' $pair[1]}
        $cx='5394960';$cy='1348740';$extent=Add-Element $xml $inline 'wp' 'extent' $wpNs;Set-AttributeNS $xml $extent '' 'cx' '' $cx;Set-AttributeNS $xml $extent '' 'cy' '' $cy
        $docPr=Add-Element $xml $inline 'wp' 'docPr' $wpNs;Set-AttributeNS $xml $docPr '' 'id' '' ([string](20+$imgNo));Set-AttributeNS $xml $docPr '' 'name' '' $fig.File;Set-AttributeNS $xml $docPr '' 'descr' '' $fig.Caption
        $frame=Add-Element $xml $inline 'wp' 'cNvGraphicFramePr' $wpNs;$locks=Add-Element $xml $frame 'a' 'graphicFrameLocks' $aNs;Set-AttributeNS $xml $locks 'a' 'noChangeAspect' $aNs '1'
        $graphic=Add-Element $xml $inline 'a' 'graphic' $aNs;$graphicData=Add-Element $xml $graphic 'a' 'graphicData' $aNs;Set-AttributeNS $xml $graphicData '' 'uri' '' $picNs
        $pic=Add-Element $xml $graphicData 'pic' 'pic' $picNs;$nv=Add-Element $xml $pic 'pic' 'nvPicPr' $picNs;$cnv=Add-Element $xml $nv 'pic' 'cNvPr' $picNs;Set-AttributeNS $xml $cnv '' 'id' '' ([string]$imgNo);Set-AttributeNS $xml $cnv '' 'name' '' $fig.File;$cnvp=Add-Element $xml $nv 'pic' 'cNvPicPr' $picNs
        $blipFill=Add-Element $xml $pic 'pic' 'blipFill' $picNs;$blip=Add-Element $xml $blipFill 'a' 'blip' $aNs;Set-AttributeNS $xml $blip 'r' 'embed' $rNs $fig.Rel;$stretch=Add-Element $xml $blipFill 'a' 'stretch' $aNs;[void](Add-Element $xml $stretch 'a' 'fillRect' $aNs)
        $sp=Add-Element $xml $pic 'pic' 'spPr' $picNs;$xfrm=Add-Element $xml $sp 'a' 'xfrm' $aNs;$off=Add-Element $xml $xfrm 'a' 'off' $aNs;Set-AttributeNS $xml $off '' 'x' '' '0';Set-AttributeNS $xml $off '' 'y' '' '0';$ext=Add-Element $xml $xfrm 'a' 'ext' $aNs;Set-AttributeNS $xml $ext '' 'cx' '' $cx;Set-AttributeNS $xml $ext '' 'cy' '' $cy;$geom=Add-Element $xml $sp 'a' 'prstGeom' $aNs;Set-AttributeNS $xml $geom '' 'prst' '' 'rect';[void](Add-Element $xml $geom 'a' 'avLst' $aNs)
        $paragraphs.Add($p)
        $cap=$xml.CreateElement('w','p',$wNs);$cp=Add-Element $xml $cap 'w' 'pPr' $wNs;$cps=Add-Element $xml $cp 'w' 'pStyle' $wNs;Set-AttributeNS $xml $cps 'w' 'val' $wNs 'Caption';$cr=Add-Element $xml $cap 'w' 'r' $wNs;$ct=Add-Element $xml $cr 'w' 't' $wNs;$ct.InnerText=$fig.Caption;$paragraphs.Add($cap)
        $imgNo++
    }

    foreach($p in $paragraphs){[void]$body.InsertBefore($p,$section)}
    $settings=[Xml.XmlWriterSettings]::new();$settings.Encoding=[Text.UTF8Encoding]::new($false);$settings.Indent=$false
    $entry=$zip.CreateEntry('word/document.xml',[IO.Compression.CompressionLevel]::Optimal);$stream=$entry.Open();$writer=[Xml.XmlWriter]::Create($stream,$settings);$xml.Save($writer);$writer.Dispose();$stream.Dispose()
    $settings2=[Xml.XmlWriterSettings]::new();$settings2.Encoding=[Text.UTF8Encoding]::new($false);$stream=$zip.CreateEntry('word/_rels/document.xml.rels').Open();$writer=[Xml.XmlWriter]::Create($stream,$settings2);$rels.Save($writer);$writer.Dispose();$stream.Dispose()
    $settings3=[Xml.XmlWriterSettings]::new();$settings3.Encoding=[Text.UTF8Encoding]::new($false);$stream=$zip.CreateEntry('[Content_Types].xml').Open();$writer=[Xml.XmlWriter]::Create($stream,$settings3);$types.Save($writer);$writer.Dispose();$stream.Dispose()
}
finally { $zip.Dispose() }

# Validate that the final package is a readable DOCX with all three embedded diagrams.
$check=[IO.Compression.ZipFile]::OpenRead($output)
try {
    $required=@('word/document.xml','word/media/flujo-pruebas.png','word/media/herramientas.png','word/media/cobertura.png','word/_rels/document.xml.rels')
    foreach($name in $required){if(-not $check.GetEntry($name)){throw "Falta una parte requerida del DOCX: $name"}}
    $e=$check.GetEntry('word/document.xml');$sr=[IO.StreamReader]::new($e.Open(),[Text.Encoding]::UTF8);[xml]$verify=$sr.ReadToEnd();$sr.Dispose()
    $verifiedXml = $verify.OuterXml
    if($verifiedXml -notmatch 'No observado|Sin ejecutar'){throw 'Pending execution status was not found in the final document.'}
    if($verifiedXml -match 'ejecutable ficticio.*\.exe'){throw 'The unsupported-file case still references an .exe file.'}
}
finally {$check.Dispose()}
Get-Item -LiteralPath $output | Select-Object FullName,Length
Write-Output 'DOCX created and checked: 3 diagrams embedded; original preserved.'
