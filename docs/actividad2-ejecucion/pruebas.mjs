import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const { chromium } = require(path.join(os.tmpdir(), 'actividad2-playwright', 'node_modules', 'playwright'));
const base = 'http://127.0.0.1:8775';
const qa = path.join(os.tmpdir(), 'sistema-autenticidad-actividad2-20261025-entrega');
const evidence = path.resolve('docs/actividad2-ejecucion/evidencias');
fs.mkdirSync(evidence, { recursive: true });
const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe' });
const context = await browser.newContext({ viewport: { width: 1365, height: 900 } });
const page = await context.newPage();
const results = [];
async function test(id, title, action, capturePage=page) {
  let detail = '';
  try { detail = await action(); results.push({id,title,status:'APROBADO',detail,evidence:`evidencias/${id}.png`}); }
  catch (error) { results.push({id,title,status:'FALLIDO',detail:String(error?.message ?? error),evidence:`evidencias/${id}.png`}); }
  await capturePage.screenshot({ path: path.join(evidence, `${id}.png`), fullPage: false }).catch(() => {});
  console.log(`${id} ${results.at(-1).status}: ${results.at(-1).detail}`);
}
async function login(p=page, email='admin.qa@example.com') {
  await p.goto(`${base}/login`);
  await p.locator('[name=email]').fill(email);
  await p.locator('[name=password]').fill('Prueba-QA2026!');
  await p.locator('form[action$="/login"] button[type=submit]').click();
  await p.waitForURL('**/dashboard');
}
try {
  await login();
  await test('M2-01','Registrar proyecto y cargar PDF',async()=>{
    await page.goto(`${base}/proyectos/create`);
    await page.locator('[name=titulo]').fill('Proyecto funcional QA 2026');
    await page.locator('[name=resumen]').fill('Registro ficticio para verificar formulario y carga documental.');
    await page.locator('[name=modalidad][value=proyecto_grado]').check();
    await page.locator('[name=carrera_id]').selectOption('1');
    await page.locator('[name=estudiante_id]').evaluate(e=>e.value='1');
    await page.locator('[name=tutor_id]').evaluate(e=>e.value='1');
    await page.locator('[name=anio]').fill('2026');
    await page.locator('[name=documento]').setInputFiles(path.join(qa,'carga-prueba.pdf'));
    await page.locator('form[action$="/proyectos"] button[type=submit]').click();
    await page.waitForURL('**/proyectos');
    if (!(await page.getByText('Proyecto funcional QA 2026',{exact:true}).count())) throw Error('El nuevo proyecto no aparece en el catálogo.');
    if (!(await page.getByText('Proyecto registrado correctamente.').count())) throw Error('No apareció el mensaje de registro exitoso.');
    return 'El formulario creó el proyecto, mostró confirmación y devolvió al catálogo.';
  });
  await test('M2-02','Validar campo obligatorio del título',async()=>{
    await page.goto(`${base}/proyectos/create`);
    await page.locator('[name=resumen]').fill('Prueba de validación de campo requerido.');
    await page.locator('[name=modalidad][value=proyecto_grado]').check();
    await page.locator('[name=carrera_id]').selectOption('1');
    await page.locator('[name=estudiante_id]').evaluate(e=>e.value='1');
    await page.locator('[name=tutor_id]').evaluate(e=>e.value='1');
    await page.locator('[name=anio]').fill('2026');
    await page.locator('[name=documento]').setInputFiles(path.join(qa,'carga-prueba.pdf'));
    await page.locator('form[action$="/proyectos"] button[type=submit]').click();
    await page.waitForLoadState('networkidle');
    if (!(await page.locator('[name=titulo].is-invalid').count())) throw Error('No se marcó el título como inválido.');
    return 'El servidor rechazó el registro sin título y mostró el campo como inválido.';
  });
  await test('M2-03','Editar datos de un proyecto',async()=>{
    await page.goto(`${base}/proyectos/1/edit`);
    await page.locator('[name=titulo]').fill('Proyecto editado QA 2026');
    await page.locator('form[action$="/proyectos/1"] button[type=submit]').click();
    await page.waitForURL('**/proyectos');
    if (!(await page.getByText('Proyecto editado QA 2026',{exact:true}).count())) throw Error('No se ve el título actualizado.');
    if (!(await page.getByText('Proyecto actualizado correctamente.').count())) throw Error('No apareció confirmación de edición.');
    return 'La actualización guardó el nuevo título y mostró confirmación en el catálogo.';
  });
  await test('M2-04','Buscar por título del proyecto',async()=>{
    await page.goto(`${base}/proyectos?buscar=Proyecto%20funcional%20QA%202026`);
    if (!(await page.getByText('Proyecto funcional QA 2026',{exact:true}).count())) throw Error('No se encontró el proyecto buscado.');
    if (!(await page.getByText('Segundo registro académico',{exact:true}).count() === 0)) throw Error('El filtro también mostró un proyecto ajeno al término.');
    return 'La búsqueda por título mostró el registro coincidente y excluyó el otro proyecto.';
  });
  await test('M2-05','Rechazar archivo no PDF',async()=>{
    const invalid = path.join(qa,'archivo-invalido.txt'); fs.writeFileSync(invalid,'Contenido que no es PDF.');
    await page.goto(`${base}/proyectos/create`);
    await page.locator('[name=titulo]').fill('Registro con archivo inválido QA');
    await page.locator('[name=modalidad][value=proyecto_grado]').check();
    await page.locator('[name=carrera_id]').selectOption('1');
    await page.locator('[name=estudiante_id]').evaluate(e=>e.value='1');
    await page.locator('[name=tutor_id]').evaluate(e=>e.value='1');
    await page.locator('[name=anio]').fill('2026');
    await page.locator('[name=documento]').setInputFiles(invalid);
    await page.locator('form[action$="/proyectos"] button[type=submit]').click();
    await page.waitForLoadState('networkidle');
    if (!(await page.locator('[name=documento].is-invalid').count())) throw Error('No se rechazó el archivo .txt.');
    return 'El servidor rechazó la extensión no permitida y señaló el control Documento PDF.';
  });
  const studentContext = await browser.newContext({ viewport: { width: 1365, height: 900 } });
  const studentPage = await studentContext.newPage();
  await test('M2-06','Controlar acceso de estudiante',async()=>{
    await login(studentPage,'estudiante.qa@example.com');
    const response = await studentPage.goto(`${base}/proyectos/create`);
    if (response?.status() !== 403) throw Error(`Se esperaba HTTP 403; se recibió ${response?.status()}.`);
    return 'Una cuenta con rol estudiante recibió HTTP 403 al abrir el formulario restringido.';
  },studentPage);
  await studentContext.close();
} finally {
  await browser.close();
  const meta = {executedAt:new Date().toISOString(),environment:'Laravel local; Playwright con Microsoft Edge headless; base SQLite temporal; datos ficticios; MySQL/.env intactos',results};
  fs.writeFileSync(path.join(path.resolve('docs/actividad2-ejecucion'),'resultados.json'),JSON.stringify(meta,null,2),'utf8');
  console.log(`RESUMEN ${results.filter(x=>x.status==='APROBADO').length}/${results.length} aprobadas`);
}
if (results.some(x=>x.status==='FALLIDO')) process.exitCode=1;
