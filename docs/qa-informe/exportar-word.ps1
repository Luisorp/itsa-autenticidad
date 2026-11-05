$ErrorActionPreference = 'Stop'
$folder = 'C:\laragon\www\itsa-autenticidad\docs\qa-informe'
$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
$document = $null
try {
    $document = $word.Documents.Open((Join-Path $folder 'informe.html'), $false, $false)
    $document.PageSetup.PaperSize = 7
    $document.PageSetup.TopMargin = $word.CentimetersToPoints(1.45)
    $document.PageSetup.BottomMargin = $word.CentimetersToPoints(1.5)
    $document.PageSetup.LeftMargin = $word.CentimetersToPoints(1.6)
    $document.PageSetup.RightMargin = $word.CentimetersToPoints(1.6)
    $document.PageSetup.HeaderDistance = $word.CentimetersToPoints(0.65)
    $document.PageSetup.FooterDistance = $word.CentimetersToPoints(0.65)
    foreach ($table in $document.Tables) {
        $table.Rows.AllowBreakAcrossPages = 0
        $table.Range.ParagraphFormat.SpaceAfter = 2
        $table.Range.ParagraphFormat.SpaceBefore = 0
    }
    foreach ($shape in $document.InlineShapes) {
        if ($shape.Width -gt 485) { $shape.LockAspectRatio = -1; $shape.Width = 480 }
    }
    foreach ($section in $document.Sections) {
        $header = $section.Headers.Item(1).Range
        $header.Text = 'SISTEMA DE AUTENTICIDAD  |  PRUEBAS FUNCIONALES'
        $header.Font.Name = 'Calibri'
        $header.Font.Size = 8
        $header.Font.Color = 5473798
        $footer = $section.Footers.Item(1).Range
        $footer.Text = 'Control de calidad | 16/10/2026                                         Pagina '
        $footer.Font.Name = 'Calibri'
        $footer.Font.Size = 8
        $footer.Collapse(0)
        $null = $document.Fields.Add($footer, 33)
    }
    $document.Repaginate()
    $docx = Join-Path $folder 'Sistema_de_autenticidad_Pruebas_funcionales.docx'
    $pdf = Join-Path $folder 'Sistema_de_autenticidad_Pruebas_funcionales.pdf'
    $document.SaveAs2($docx, 16)
    $document.ExportAsFixedFormat($pdf, 17)
    $pages = $document.ComputeStatistics(2)
    "PAGES=$pages IMAGES=$($document.InlineShapes.Count)"
    for ($i=1; $i -le $pages; $i++) {
        $start = $document.GoTo(1,1,$i).Start
        if ($i -lt $pages) { $end = $document.GoTo(1,1,($i+1)).Start } else { $end=$document.Content.End }
        $text = $document.Range($start,$end).Text -replace '[\r\n\x07\x0c]+', ' | '
        ('PAGE '+$i+': ') + $text.Substring(0,[Math]::Min(150,$text.Length))
    }
    Get-Item -LiteralPath $docx,$pdf | Select-Object Name,Length
} finally {
    if ($document) { $document.Close(0) }
    $word.Quit()
    [Runtime.InteropServices.Marshal]::ReleaseComObject($word) | Out-Null
}
