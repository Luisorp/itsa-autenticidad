<?php
$app=require __DIR__.'/bootstrap.php';
$qa=sys_get_temp_dir().DIRECTORY_SEPARATOR.'sistema-autenticidad-actividad2-20261025-entrega';
$project=App\Models\ProyectoTitulacion::where('titulo','Proyecto funcional QA 2026')->with('documento')->first();
$checks=[
 'database_is_temporary_sqlite'=>config('database.default')==='sqlite' && config('database.connections.sqlite.database')===$qa.'/qa.sqlite',
 'successful_project_exists'=>$project!==null,
 'uploaded_pdf_record_exists'=>$project?->documento!==null,
 'uploaded_pdf_file_exists'=>$project?->documento && Illuminate\Support\Facades\Storage::disk('public')->exists($project->documento->ruta_archivo),
 'pdf_text_extracted'=>str_contains(mb_strtolower($project?->documento?->contenido_extraido ?? ''),'texto controlado'),
 'missing_title_did_not_create_project'=>!App\Models\ProyectoTitulacion::where('titulo','')->exists(),
 'invalid_file_did_not_create_project'=>!App\Models\ProyectoTitulacion::where('titulo','Registro con archivo inválido QA')->exists(),
 'edit_was_persisted'=>App\Models\ProyectoTitulacion::where('id',1)->where('titulo','Proyecto editado QA 2026')->exists(),
];
foreach($checks as $name=>$passed) echo $name.': '.($passed?'OK':'FALLO').PHP_EOL;
file_put_contents(__DIR__.'/verificacion-datos.json',json_encode(['verifiedAt'=>now()->toIso8601String(),'checks'=>$checks],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
if(in_array(false,$checks,true)) exit(1);
