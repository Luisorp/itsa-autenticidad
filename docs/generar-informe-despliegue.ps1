$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$markdownPath = Join-Path $PSScriptRoot 'INFORME_DESPLIEGUE_SISTEMA_AUTENTICIDAD.md'
$outputPath = Join-Path $PSScriptRoot 'INFORME_DESPLIEGUE_RENDER_CORREGIDO.docx'
if (-not (Test-Path -LiteralPath $markdownPath)) { throw "No se encuentra el informe fuente: $markdownPath" }
if (Test-Path -LiteralPath $outputPath) { throw "El archivo ya existe; se evita sobrescribirlo: $outputPath" }

$script:wNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
$script:rNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships'
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
    [switch]$PageBreak
) {
    $paragraph = New-WElement $Document $Body 'p'
    if ($Style -or $PageBreak) {
        $pPr = New-WElement $Document $paragraph 'pPr'
        if ($Style) {
            $pStyle = New-WElement $Document $pPr 'pStyle'
            Set-WAttribute $Document $pStyle 'val' $Style
        }
        if ($PageBreak) {
            $run = New-WElement $Document $paragraph 'r'
            $break = New-WElement $Document $run 'br'
            Set-WAttribute $Document $break 'type' 'page'
            return
        }
    }
    $run = New-WElement $Document $paragraph 'r'
    $textElement = New-WElement $Document $run 't'
    [void]$textElement.SetAttribute('space', $script:xmlNs, 'preserve')
    $textElement.InnerText = $Text
}

function Add-Table(
    [System.Xml.XmlDocument]$Document,
    [System.Xml.XmlElement]$Body,
    [string[][]]$Rows
) {
    if ($Rows.Count -eq 0) { return }
    $columnCount = $Rows[0].Count
    $table = New-WElement $Document $Body 'tbl'
    $properties = New-WElement $Document $table 'tblPr'
    $width = New-WElement $Document $properties 'tblW'
    Set-WAttribute $Document $width 'w' '9360'
    Set-WAttribute $Document $width 'type' 'dxa'
    $layout = New-WElement $Document $properties 'tblLayout'
    Set-WAttribute $Document $layout 'type' 'fixed'
    $borders = New-WElement $Document $properties 'tblBorders'
    foreach ($side in @('top', 'left', 'bottom', 'right', 'insideH', 'insideV')) {
        $border = New-WElement $Document $borders $side
        Set-WAttribute $Document $border 'val' 'single'
        Set-WAttribute $Document $border 'sz' '4'
        Set-WAttribute $Document $border 'space' '0'
        Set-WAttribute $Document $border 'color' 'CBD5D0'
    }
    $margins = New-WElement $Document $properties 'tblCellMar'
    foreach ($side in @('top', 'start', 'bottom', 'end')) {
        $margin = New-WElement $Document $margins $side
        Set-WAttribute $Document $margin 'w' '90'
        Set-WAttribute $Document $margin 'type' 'dxa'
    }
    $grid = New-WElement $Document $table 'tblGrid'
    $cellWidth = [int](9360 / [Math]::Max(1, $columnCount))
    for ($column = 0; $column -lt $columnCount; $column++) {
        $gridColumn = New-WElement $Document $grid 'gridCol'
        Set-WAttribute $Document $gridColumn 'w' ([string]$cellWidth)
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
            $cellWidthElement = New-WElement $Document $cellProperties 'tcW'
            Set-WAttribute $Document $cellWidthElement 'w' ([string]$cellWidth)
            Set-WAttribute $Document $cellWidthElement 'type' 'dxa'
            $shade = New-WElement $Document $cellProperties 'shd'
            if ($rowIndex -eq 0) {
                Set-WAttribute $Document $shade 'fill' '174C3A'
            } elseif (($rowIndex % 2) -eq 0) {
                Set-WAttribute $Document $shade 'fill' 'F2F6F3'
            } else {
                Set-WAttribute $Document $shade 'fill' 'FFFFFF'
            }
            $paragraph = New-WElement $Document $cell 'p'
            $pPr = New-WElement $Document $paragraph 'pPr'
            $pStyle = New-WElement $Document $pPr 'pStyle'
            Set-WAttribute $Document $pStyle 'val' 'TableText'
            $run = New-WElement $Document $paragraph 'r'
            if ($rowIndex -eq 0) {
                $runProperties = New-WElement $Document $run 'rPr'
                [void](New-WElement $Document $runProperties 'b')
                $color = New-WElement $Document $runProperties 'color'
                Set-WAttribute $Document $color 'val' 'FFFFFF'
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
$root.SetAttribute('xmlns:r', $script:rNs)
[void]$document.AppendChild($root)
$body = New-WElement $document $root 'body'

$lines = Get-Content -LiteralPath $markdownPath -Encoding UTF8
$tableRows = New-Object 'System.Collections.Generic.List[System.String[]]'
$cover = $true
$pageBreakAdded = $false
$insideQuote = $false
$index = 0
while ($index -lt $lines.Count) {
    $line = [string]$lines[$index]
    if ([string]::IsNullOrWhiteSpace($line) -or $line.Trim() -eq '---') { $index++; continue }

    if ($line.TrimStart().StartsWith('|')) {
        $tableRows.Clear()
        while ($index -lt $lines.Count -and ([string]$lines[$index]).TrimStart().StartsWith('|')) {
            $tableLine = ([string]$lines[$index]).Trim()
            $cells = @($tableLine.Trim('|').Split('|') | ForEach-Object { $_.Trim() })
            $separator = $true
            foreach ($cellText in $cells) {
                if ($cellText -notmatch '^:?-{3,}:?$') { $separator = $false; break }
            }
            if (-not $separator) {
                $rowValues = [string[]]$cells
                $tableRows.Add($rowValues)
            }
            $index++
        }
        if ($tableRows.Count -gt 0) {
            $rowsArray = [string[][]]$tableRows.ToArray()
            Add-Table $document $body $rowsArray
        }
        continue
    }

    if ($line -match '^#\s+(.+)$') {
        Add-Paragraph $document $body $Matches[1] 'Title'
        $cover = $false
        $index++
        continue
    }
    if ($line -match '^##\s+(.+)$') {
        if (-not $pageBreakAdded -and $Matches[1] -match '^1\.') {
            Add-Paragraph $document $body '' '' -PageBreak
            $pageBreakAdded = $true
        }
        $style = if ($Matches[1] -match '^\d+\.') { 'Heading1' } else { 'Heading2' }
        Add-Paragraph $document $body $Matches[1] $style
        $cover = $false
        $index++
        continue
    }
    if ($line -match '^###\s+(.+)$') {
        Add-Paragraph $document $body $Matches[1] 'Heading2'
        $index++
        continue
    }
    if ($line -match '^>\s?(.*)$') {
        Add-Paragraph $document $body $Matches[1] 'Quote'
        $index++
        continue
    }
    if ($line -match '^\s*\d+\.\s+(.+)$') {
        Add-Paragraph $document $body $line.Trim() 'ListParagraph'
        $index++
        continue
    }
    if ($line -match '^\s*-\s+(.+)$') {
        Add-Paragraph $document $body ('•  ' + $Matches[1]) 'ListParagraph'
        $index++
        continue
    }
    $text = $line -replace '\*\*', ''
    $style = if ($cover) { 'Subtitle' } elseif ($text.StartsWith('**')) { 'Normal' } else { 'Normal' }
    Add-Paragraph $document $body $text $style
    $index++
}

$section = New-WElement $document $body 'sectPr'
$pageSize = New-WElement $document $section 'pgSz'
Set-WAttribute $document $pageSize 'w' '11906'
Set-WAttribute $document $pageSize 'h' '16838'
$pageMargins = New-WElement $document $section 'pgMar'
Set-WAttribute $document $pageMargins 'top' '1134'
Set-WAttribute $document $pageMargins 'right' '1134'
Set-WAttribute $document $pageMargins 'bottom' '1134'
Set-WAttribute $document $pageMargins 'left' '1134'
Set-WAttribute $document $pageMargins 'header' '567'
Set-WAttribute $document $pageMargins 'footer' '567'
Set-WAttribute $document $pageMargins 'gutter' '0'

$styles = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="20"/><w:color w:val="26332D"/></w:rPr></w:rPrDefault>
    <w:pPrDefault><w:pPr><w:spacing w:after="140" w:line="276" w:lineRule="auto"/></w:pPr></w:pPrDefault>
  </w:docDefaults>
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Aptos" w:hAnsi="Aptos"/><w:sz w:val="20"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:pPr><w:jc w:val="center"/><w:spacing w:before="2400" w:after="360"/></w:pPr><w:rPr><w:b/><w:color w:val="174C3A"/><w:sz w:val="36"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:pPr><w:jc w:val="center"/><w:spacing w:after="180"/></w:pPr><w:rPr><w:color w:val="42594E"/><w:sz w:val="22"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="300" w:after="160"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:b/><w:color w:val="174C3A"/><w:sz w:val="28"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="220" w:after="100"/><w:outlineLvl w:val="1"/></w:pPr><w:rPr><w:b/><w:color w:val="28624B"/><w:sz w:val="23"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="Quote"><w:name w:val="Quote"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="360" w:right="360"/><w:spacing w:before="180" w:after="180"/></w:pPr><w:rPr><w:i/><w:color w:val="42594E"/></w:rPr></w:style>
  <w:style w:type="paragraph" w:styleId="ListParagraph"><w:name w:val="List Paragraph"/><w:basedOn w:val="Normal"/><w:pPr><w:ind w:left="360" w:hanging="180"/><w:spacing w:after="80"/></w:pPr></w:style>
  <w:style w:type="paragraph" w:styleId="TableText"><w:name w:val="Table Text"/><w:basedOn w:val="Normal"/><w:pPr><w:spacing w:after="40" w:line="240" w:lineRule="auto"/></w:pPr><w:rPr><w:sz w:val="18"/></w:rPr></w:style>
</w:styles>
'@

$contentTypes = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>
'@

$packageRelationships = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
'@

$documentRelationships = @'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
'@

$documentSettings = New-Object System.Xml.XmlWriterSettings
$documentSettings.Encoding = New-Object System.Text.UTF8Encoding($false)
$documentSettings.Indent = $false
$stringBuilder = New-Object System.Text.StringBuilder
$xmlWriter = [System.Xml.XmlWriter]::Create($stringBuilder, $documentSettings)
$document.Save($xmlWriter)
$xmlWriter.Dispose()
# XmlWriter over a StringBuilder emits a UTF-16 declaration. The package writer
# stores the string as UTF-8, so align the XML declaration with the actual bytes.
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
    $documentEntry = $check.GetEntry('word/document.xml')
    $reader = New-Object System.IO.StreamReader($documentEntry.Open(), [System.Text.Encoding]::UTF8)
    [xml]$validation = $reader.ReadToEnd()
    $reader.Dispose()
    if ($validation.OuterXml -notmatch 'itsa-autenticidad\.onrender\.com') { throw 'El DOCX no incluye la URL pública confirmada.' }
}
finally {
    $check.Dispose()
}

Get-Item -LiteralPath $outputPath | Select-Object FullName, Length
Write-Output 'DOCX creado y validado como paquete OpenXML con la URL pública y el resultado observado.'
