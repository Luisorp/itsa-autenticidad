<?php
$app = require __DIR__.'/bootstrap-qa.php';
if (file_exists($qa.'/qa.sqlite')) { throw new RuntimeException('Ya existe el escenario; no se sobrescribe.'); }
touch($qa.'/qa.sqlite');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
$carrera = App\Models\Carrera::create(['nombre'=>'Sistemas Informáticos','codigo'=>'SIS','activo'=>true]);
foreach ([
 ['Administrador de prueba','admin.qa@example.com','administrador',true],
 ['Gestor de prueba','gestor.qa@example.com','gestor',true],
 ['Usuario de prueba','usuario.qa@example.com','usuario',true],
 ['Cuenta inactiva','inactivo.qa@example.com','usuario',false]
] as [$name,$email,$rol,$activo]) {
 $u = App\Models\User::create(['name'=>$name,'email'=>$email,'password'=>Illuminate\Support\Facades\Hash::make('Prueba-QA2026!'),'rol'=>$rol,'activo'=>$activo,'carrera_id'=>$carrera->id]);
 $u->forceFill(['email_verified_at'=>now()])->save();
}
$e = App\Models\Estudiante::create(['nombre'=>'Ana Pérez (prueba)','codigo'=>'QA-001','email'=>'ana.qa@example.com','carrera_id'=>$carrera->id,'user_id'=>3,'activo'=>true,'gestion_ingreso'=>2026]);
$d = App\Models\Docente::create(['nombre'=>'Carlos Flores (prueba)','codigo'=>'QA-D01','email'=>'carlos.qa@example.com','especialidad'=>'Desarrollo de software','activo'=>true]);
$d->carreras()->attach($carrera);
$text = "INTRODUCCIÓN\nEl sistema registra documentos académicos y organiza los proyectos de titulación mediante una plataforma institucional. Los usuarios autorizados consultan resultados y verifican coincidencias textuales para apoyar la evaluación de trabajos.\nCONCLUSIONES\nLa solución permite conservar documentos y obtener reportes verificables para facilitar el seguimiento académico.";
$contents = [$text, $text, "Documento sin encabezados reconocibles con contenido académico de prueba.", $text];
$titles = ['Registro académico A','Registro académico B','Documento sin secciones','Proyecto de otra modalidad'];
foreach ($contents as $i=>$content) {
 $p = App\Models\ProyectoTitulacion::create(['titulo'=>$titles[$i],'modalidad'=>$i===3?'trabajo_dirigido_externo':'proyecto_grado','carrera_id'=>1,'estudiante_id'=>$e->id,'tutor_id'=>$d->id,'anio'=>2026,'estado'=>'pendiente_analisis','activo'=>true]);
 $pdf = Barryvdh\DomPDF\Facade\Pdf::loadHTML('<meta charset="utf-8"><h1>'.htmlspecialchars($titles[$i]).'</h1><p>'.nl2br(htmlspecialchars($content)).'</p>')->output();
 $path = 'documentos/qa-'.($i+1).'.pdf';
 Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdf);
 App\Models\Documento::create(['proyecto_id'=>$p->id,'nombre_archivo'=>basename($path),'ruta_archivo'=>$path,'tipo_archivo'=>'pdf','tamano_archivo'=>strlen($pdf),'hash_archivo'=>hash('sha256',$pdf),'contenido_extraido'=>$content]);
}
file_put_contents($qa.'/carga-prueba.pdf', Barryvdh\DomPDF\Facade\Pdf::loadHTML('<meta charset="utf-8"><h1>INTRODUCCIÓN</h1><p>Esta plataforma organiza documentos académicos y conserva registros de proyectos institucionales para apoyar la evaluación responsable de similitud textual.</p><h1>CONCLUSIONES</h1><p>El registro facilita la trazabilidad de los documentos mediante consultas y reportes.</p>')->output());
echo "Escenario creado en $qa\n";
