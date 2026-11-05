$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$phpCommand = Get-Command php.exe -ErrorAction SilentlyContinue

if ($phpCommand) {
    $php = $phpCommand.Source
} else {
    $phpCandidates = Get-ChildItem 'C:\laragon\bin\php' -Directory -ErrorAction SilentlyContinue |
        Sort-Object Name -Descending |
        ForEach-Object { Join-Path $_.FullName 'php.exe' } |
        Where-Object { Test-Path $_ }
    $php = $phpCandidates | Select-Object -First 1
}

if (-not $php) {
    throw 'No se encontró php.exe. Verifica que Laragon esté instalado o agrega PHP al PATH.'
}

$testNames = @(
    'test_project_search_only_returns_active_compatible_projects_with_text',
    'test_project_catalog_can_filter_by_partial_title_or_student_name',
    'test_recognized_sections_are_used_for_manual_and_repository_comparisons',
    'test_results_show_top_ten_and_allow_all_with_filters',
    'test_shared_tutor_and_theory_do_not_inflate_manual_or_repository_scores',
    'test_unrecognized_sections_are_rejected_instead_of_using_full_text',
    'test_la_consulta_requiere_autenticacion',
    'test_busca_una_publicacion_por_titulo',
    'test_la_busqueda_muestra_el_selector_y_exige_un_proyecto',
    'test_busca_y_normaliza_una_publicacion_en_openalex',
    'test_compara_un_proyecto_con_resultados_de_ambas_fuentes',
    'test_encuentra_fragmentos_textuales_semejantes',
    'test_descarta_fragmentos_sin_relacion_suficiente',
    'test_explica_los_terminos_que_aportan_al_porcentaje_global',
    'test_omite_totales_y_rotulos_del_ejemplo_del_sprint',
    'test_cifras_y_negaciones_no_desaparecen_de_la_comparacion',
    'test_el_mismo_vocabulario_en_otro_orden_no_da_una_coincidencia_alta',
    'test_una_copia_real_de_redaccion_se_conserva_como_evidencia',
    'test_el_porcentaje_es_simetrico_y_no_cruza_filas_excluidas',
    'test_no_muestra_repetido_el_mismo_par_de_fragmentos',
    'test_extrae_solo_las_secciones_autorizadas',
    'test_indica_cuando_no_hay_secciones_autorizadas',
    'test_excluye_capitulo_dos_completo_con_encabezados_reales',
    'test_reconoce_nombres_alternativos_del_marco_teorico'
)

$filter = '(' + ($testNames -join '|') + ')'
Push-Location $projectRoot
try {
    & $php artisan test --testdox "--filter=$filter" `
        tests/Feature/ProjectFilesTest.php `
        tests/Feature/CrossrefSearchTest.php `
        tests/Unit/SimilitudServiceTest.php `
        tests/Unit/SeccionesDocumentoServiceTest.php
    exit $LASTEXITCODE
} finally {
    Pop-Location
}
