<?php
$app = require __DIR__.'/bootstrap.php';
$qa = sys_get_temp_dir().DIRECTORY_SEPARATOR.'sistema-autenticidad-actividad2-20261025-entrega';
if (file_exists($qa.'/qa.sqlite')) throw new RuntimeException('La base nueva ya existe; no se sobrescribirá.');
touch($qa.'/qa.sqlite');
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
$career = App\Models\Carrera::create(['nombre'=>'Sistemas Informáticos', 'codigo'=>'SIS', 'activo'=>true]);
foreach ([
    ['Admin QA','admin.qa@example.com','administrador'],
    ['Estudiante QA','estudiante.qa@example.com','usuario'],
] as [$name,$email,$role]) {
    $user=App\Models\User::create(['name'=>$name,'email'=>$email,'password'=>Illuminate\Support\Facades\Hash::make('Prueba-QA2026!'),'rol'=>$role,'activo'=>true,'carrera_id'=>$career->id]);
    $user->forceFill(['email_verified_at'=>now()])->save();
}
$student=App\Models\Estudiante::create(['nombre'=>'Ana Pérez QA','codigo'=>'QA-EST-01','email'=>'ana.qa@example.com','carrera_id'=>$career->id,'user_id'=>2,'activo'=>true,'gestion_ingreso'=>2026]);
$teacher=App\Models\Docente::create(['nombre'=>'Carlos Flores QA','codigo'=>'QA-DOC-01','email'=>'carlos.qa@example.com','especialidad'=>'Sistemas','activo'=>true]);
$teacher->carreras()->attach($career);
foreach ([
 ['Proyecto de prueba inicial','Resumen inicial para pruebas funcionales.'],
 ['Segundo registro académico','Segundo proyecto aislado para comprobar filtros.'],
] as [$title,$summary]) {
    $project=App\Models\ProyectoTitulacion::create(['titulo'=>$title,'resumen'=>$summary,'modalidad'=>'proyecto_grado','carrera_id'=>$career->id,'estudiante_id'=>$student->id,'tutor_id'=>$teacher->id,'anio'=>2026,'estado'=>'pendiente_analisis','activo'=>true]);
    $pdf=Barryvdh\DomPDF\Facade\Pdf::loadHTML('<meta charset="utf-8"><h1>'.htmlspecialchars($title).'</h1><p>Contenido ficticio para pruebas funcionales.</p>')->output();
    $path='documentos/qa-inicial-'.$project->id.'.pdf';
    Illuminate\Support\Facades\Storage::disk('public')->put($path,$pdf);
    App\Models\Documento::create(['proyecto_id'=>$project->id,'nombre_archivo'=>basename($path),'ruta_archivo'=>$path,'tipo_archivo'=>'pdf','tamano_archivo'=>strlen($pdf),'hash_archivo'=>hash('sha256',$pdf),'contenido_extraido'=>'Contenido ficticio para pruebas funcionales.']);
}
$pdfPath=$qa.'/carga-prueba.pdf';
file_put_contents($pdfPath,Barryvdh\DomPDF\Facade\Pdf::loadHTML('<meta charset="utf-8"><h1>Documento de prueba</h1><p>Texto controlado para validar la carga del documento.</p>')->output());
echo "Escenario de pruebas aislado creado en $qa\n";
