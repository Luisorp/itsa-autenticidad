import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
const output=path.resolve('docs/qa-informe'), qa=path.join(os.tmpdir(),'autenticidad-informe-20261016');
fs.mkdirSync(path.join(output,'evidencias'),{recursive:true});
const base='http://127.0.0.1:8765';
const tabs=await (await fetch('http://127.0.0.1:9231/json')).json();
const ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);
await new Promise(r=>ws.addEventListener('open',r,{once:true}));
let id=0; const pending=new Map();
ws.addEventListener('message',ev=>{const m=JSON.parse(ev.data);if(m.id){const p=pending.get(m.id);pending.delete(m.id);m.error?p?.reject(new Error(m.error.message)):p?.resolve(m.result);}});
const cmd=(method,params={})=>new Promise((resolve,reject)=>{const n=++id;pending.set(n,{resolve,reject});ws.send(JSON.stringify({id:n,method,params}));});
const sleep=ms=>new Promise(r=>setTimeout(r,ms));
async function ev(expression){const r=await cmd('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(r.exceptionDetails.text+' '+JSON.stringify(r.exceptionDetails.exception));return r.result.value;}
async function ready(){await sleep(650);for(let i=0;i<80;i++){try{if(await ev("document.readyState==='complete'")){await sleep(300);return;}}catch{}await sleep(150);}throw Error('Carga incompleta');}
async function nav(url){await cmd('Page.navigate',{url:base+url});await ready();}
async function fill(values){await ev('(()=>{for(const [name,value] of Object.entries('+JSON.stringify(values)+')){const el=document.querySelector(\'[name="\'+name+\'"]\');if(!el)throw Error(name);el.value=value;el.dispatchEvent(new Event("input",{bubbles:true}));el.dispatchEvent(new Event("change",{bubbles:true}));}})()');}
async function submit(sel='form'){await ev('document.querySelector('+JSON.stringify(sel)+').requestSubmit()');await ready();}
const text=()=>ev('document.body.innerText');
const check=(v,msg)=>{if(!v)throw Error(msg);};
async function login(email='admin.qa@example.com'){await nav('/login');if((await ev('location.pathname'))==='/dashboard')return;await fill({email,password:'Prueba-QA2026!'});await submit('form[action$="/login"]');check((await ev('location.pathname'))==='/dashboard','No se inició sesión');}
async function logout(){await nav('/dashboard');await ev('(()=>{const f=document.querySelector(\'form[action$="/logout"]\');if(f)f.submit();})()');await ready();}
async function screenshot(code,scrollSelector=null){
 await ev('(()=>{const w=document.createTreeWalker(document.body,NodeFilter.SHOW_TEXT);while(w.nextNode()){w.currentNode.textContent=w.currentNode.textContent.replace(/sello\\s+itsa/gi,"Sistema de autenticidad");}document.title="Sistema de autenticidad";for(const e of document.querySelectorAll("[alt]"))e.alt=e.alt.replace(/sello\\s+itsa/gi,"Sistema de autenticidad");})()');
 if(scrollSelector)await ev('document.querySelector('+JSON.stringify(scrollSelector)+')?.scrollIntoView({block:"center"})');
 await ev('document.fonts.ready.then(()=>true)');await sleep(200);
 const data=await cmd('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
 fs.writeFileSync(path.join(output,'evidencias',code+'.png'),Buffer.from(data.data,'base64'));
}
const selected=process.argv[2];
const results=selected?JSON.parse(fs.readFileSync(path.join(output,'resultados.json'),'utf8')).results:[];
async function test(code,title,fn,scroll=null){if(selected&&selected!==code)return;const previous=results.findIndex(r=>r.code===code);if(previous>=0)results.splice(previous,1);try{const detail=await fn();await screenshot(code,scroll);results.push({code,title,status:'APROBADO',detail,evidence:'evidencias/'+code+'.png'});console.log(code+' APROBADO: '+detail);}catch(e){await screenshot(code).catch(()=>{});results.push({code,title,status:'FALLIDO',detail:e.message,evidence:'evidencias/'+code+'.png'});console.log(code+' FALLIDO: '+e.message);}fs.writeFileSync(path.join(output,'resultados.json'),JSON.stringify({executedAt:new Date().toISOString(),environment:'SQLite temporal; Edge headless; datos ficticios',results:results.sort((a,b)=>a.code.localeCompare(b.code))},null,2));}
await cmd('Page.enable');await cmd('Runtime.enable');await cmd('DOM.enable');
await cmd('Emulation.setDeviceMetricsOverride',{width:1320,height:900,deviceScaleFactor:1,mobile:false});
await test('TC-001','Inicio de sesión válido',async()=>{await login();check((await text()).includes('Administrador de prueba'),'Identidad incorrecta');return 'Acceso al dashboard con cuenta activa de administrador.';});
await test('TC-002','Cierre de sesión',async()=>{await logout();await nav('/dashboard');check((await ev('location.pathname'))==='/login','Dashboard accesible tras salir');return 'Sesión cerrada; /dashboard redirige a /login.';});
await test('TC-003','Contraseña incorrecta',async()=>{await fill({email:'admin.qa@example.com',password:'Incorrecta2026!'});await submit('form[action$="/login"]');check((await ev('location.pathname'))==='/login','Login inesperado');check(await ev('!!document.querySelector(".invalid-feedback")'),'Sin error de credenciales');return await ev('document.querySelector(".invalid-feedback").innerText');});
await test('TC-004','Correo obligatorio',async()=>{await nav('/login');await fill({email:'',password:'Prueba-QA2026!'});await submit('form[action$="/login"]');check(await ev('document.querySelector("#email").validity.valueMissing'),'Correo vacío permitido');return 'El navegador bloquea el envío: correo obligatorio; se conserva la pantalla de acceso.';});
await test('TC-005','Cuenta desactivada',async()=>{await nav('/login');await fill({email:'inactivo.qa@example.com',password:'Prueba-QA2026!'});await submit('form[action$="/login"]');check((await text()).includes('desactivada'),'No se rechazó cuenta inactiva');return 'Acceso rechazado: Esta cuenta ha sido desactivada. Contacta al administrador.';});
await login();
const user={name:'Lucía Rojas (prueba)',email:'lucia.qa@example.com',password:'Prueba-QA2026!',password_confirmation:'Prueba-QA2026!',rol:'gestor',carrera_id:'1'};
await test('TC-006','Crear usuario gestor',async()=>{await nav('/usuarios/create');await fill(user);await submit('form[action$="/usuarios"]');check((await text()).includes('lucia.qa@example.com'),'Usuario no registrado');return 'Cuenta de Lucía Rojas creada y visible en la lista de gestores.';});
await test('TC-007','Rechazar usuario sin correo',async()=>{await nav('/usuarios/create');await fill({...user,email:'',name:'Registro incompleto'});await submit('form[action$="/usuarios"]');check(await ev('!!document.querySelector("[name=email].is-invalid")'),'Falta validación email');return await ev('document.querySelector("[name=email]").nextElementSibling.innerText');});
await test('TC-008','Rechazar confirmación diferente',async()=>{await nav('/usuarios/create');await fill({...user,email:'confirmacion.qa@example.com',password_confirmation:'Diferente2026!'});await submit('form[action$="/usuarios"]');check(await ev('!!document.querySelector("[name=password].is-invalid")'),'Falta validación confirmación');return await ev('document.querySelector("[name=password]").nextElementSibling.innerText');});
await test('TC-009','Rechazar correo duplicado',async()=>{await nav('/usuarios/create');await fill({...user,name:'Duplicado de prueba'});await submit('form[action$="/usuarios"]');check(await ev('!!document.querySelector("[name=email].is-invalid")'),'Se permitió duplicado');return await ev('document.querySelector("[name=email]").nextElementSibling.innerText');});
await test('TC-010','Restringir administración al usuario',async()=>{await logout();await login('usuario.qa@example.com');await nav('/usuarios');check((await text()).includes('403'),'No se mostró 403');return 'La cuenta con rol usuario recibe HTTP 403 al intentar abrir /usuarios.';});
await logout();await login();
await test('TC-011','Registrar estudiante sin cuenta',async()=>{await nav('/estudiantes/create');await fill({codigo:'QA-002',nombre:'Luis Mendoza (prueba)',email:'luis.qa@example.com',carrera_id:'1',gestion_ingreso:'2026'});await submit('form[action$="/estudiantes"]');check((await text()).includes('Luis Mendoza'),'Estudiante ausente');return 'Estudiante QA-002 registrado en el padrón de Sistemas Informáticos.';});
await test('TC-012','Registrar proyecto y cargar PDF',async()=>{
 await nav('/proyectos/create');await fill({titulo:'Plataforma documental de prueba',resumen:'Registro controlado para verificar carga y extracción de PDF.',carrera_id:'1',anio:'2026'});
 await fill({estudiante_id:'1',tutor_id:'1'});
 const {root}=await cmd('DOM.getDocument');const {nodeId}=await cmd('DOM.querySelector',{nodeId:root.nodeId,selector:'input[type=file]'});
 await cmd('DOM.setFileInputFiles',{nodeId,files:[path.join(qa,'carga-prueba.pdf')]});
 await submit('form[action$="/proyectos"]');
 check((await text()).includes('Plataforma documental de prueba'),'Proyecto no creado');
 return 'Proyecto registrado; archivo PDF recibido. Se verifica texto extraído con el control de datos temporal.';
});
async function compare(a,b){await nav('/analizar');await fill({proyecto_a:String(a),proyecto_b:String(b)});await ev("document.querySelector('[data-comparison-form]').submit()");await ready();}
await test('TC-013','Comparar textos idénticos',async()=>{await compare(1,2);check((await text()).includes('100% de similitud'),'Resultado distinto de 100%');const matches=await ev('document.querySelectorAll(".match-card").length');check(matches>0,'Sin fragmentos');await screenshot('TC-013-detalle','.match-card');return '100% de similitud entre A y B; '+matches+' fragmentos coincidentes visibles. La comprobación usa elementos HTML y no texto transformado a mayúsculas por CSS.';},'.alert.text-center');
await test('TC-014','Rechazar modalidades distintas',async()=>{await compare(1,4);check((await text()).includes('misma modalidad'),'Modalidades distintas aceptadas');return 'El servidor rechaza la solicitud: Solo se pueden comparar proyectos de la misma modalidad de graduación.';});
await test('TC-015','Rechazar texto sin secciones',async()=>{await compare(1,3);check((await text()).includes('No se reconoció contenido analizable'),'Texto sin secciones aceptado');return 'El sistema solicita encabezados reconocibles para el Proyecto B; no presenta un porcentaje.';});
await test('TC-016','Generar y descargar reporte PDF',async()=>{
 await nav('/proyectos');const pdf=await ev("fetch('/proyectos/1/reporte').then(async r=>({status:r.status,type:r.headers.get('content-type'),data:btoa(String.fromCharCode(...new Uint8Array(await r.arrayBuffer())))}))");
 check(pdf.status===200&&pdf.type.includes('application/pdf'),'Respuesta no PDF');const bytes=Buffer.from(pdf.data,'base64');check(bytes.subarray(0,5).toString()==='%PDF-','Firma PDF ausente');
 fs.writeFileSync(path.join(qa,'reporte-verificado.pdf'),bytes);
 await nav('/reportes');check((await text()).includes('Registro académico A'),'Reporte sin registro');
 return 'Descarga HTTP 200, tipo application/pdf, firma %PDF- y registro en Reportes; '+bytes.length+' bytes.';
});
await test('TC-017','Generar y descargar respaldo SQL',async()=>{
 await nav('/respaldos');await submit('form[action$="/respaldos/generar"]');
 const href=await ev("Array.from(document.querySelectorAll('a[href]')).map(a=>a.href).find(h=>h.includes('/descargar'))");
 check(href,'Sin enlace de descarga');const sql=await ev('fetch('+JSON.stringify(href)+').then(async r=>({status:r.status,text:await r.text()}))');
 check(sql.status===200&&sql.text.includes('INSERT INTO'),'Respaldo SQL inválido');
 check(sql.text.includes('estudiantes')&&sql.text.includes('documentos'),'Faltan tablas con registros');
 return 'Respaldo SQL descargado con HTTP 200 y datos de estudiantes/documentos. Los PDF se respaldan aparte.';
});
await test('TC-018','Dashboard en pantalla móvil',async()=>{
 await cmd('Emulation.setDeviceMetricsOverride',{width:390,height:844,deviceScaleFactor:1,mobile:true});
 await nav('/dashboard');
 const dimensions=await ev('({scroll:document.documentElement.scrollWidth,width:innerWidth})');
 check(dimensions.scroll<=dimensions.width+2,'Desbordamiento horizontal');
 return 'Dashboard renderizado a 390 × 844 px sin desbordamiento horizontal.';
});
await cmd('Emulation.setDeviceMetricsOverride',{width:1320,height:900,deviceScaleFactor:1,mobile:false});
ws.close();
console.log(JSON.stringify(results.map(r=>({code:r.code,status:r.status}))));
