<?php
require __DIR__.'/bootstrap-qa.php';
$u=App\Models\User::where('email','lucia.qa@example.com')->get();
$e=App\Models\Estudiante::where('codigo','QA-002')->firstOrFail();
$p=App\Models\ProyectoTitulacion::where('titulo','Plataforma documental de prueba')->with('documento')->firstOrFail();
$checks=[
 'usuario_creado_una_vez'=>$u->count()===1,
 'usuario_creado_gestor'=>$u->first()->rol==='gestor',
 'confirmacion_invalida_no_creo_cuenta'=>!App\Models\User::where('email','confirmacion.qa@example.com')->exists(),
 'correo_vacio_no_creo_cuenta'=>!App\Models\User::where('name','Registro incompleto')->exists(),
 'estudiante_sin_cuenta'=>$e->user_id===null,
 'proyecto_pendiente'=>$p->estado==='pendiente_analisis',
 'archivo_pdf_existe'=>Illuminate\Support\Facades\Storage::disk('public')->exists($p->documento->ruta_archivo),
 'texto_pdf_extraido'=>str_contains($p->documento->contenido_extraido,'plataforma'),
 'un_reporte_registrado'=>App\Models\Reporte::where('proyecto_id',1)->exists(),
 'base_aislada'=>config('database.connections.sqlite.database')===$qa.'/qa.sqlite',
];
file_put_contents(__DIR__.'/control-datos.json',json_encode($checks,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
foreach($checks as $k=>$v) echo "$k: ".($v?'OK':'FALLO')."\n";
if(in_array(false,$checks,true))exit(1);
