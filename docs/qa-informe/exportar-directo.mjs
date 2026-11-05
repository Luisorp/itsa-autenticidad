import fs from 'node:fs';
import path from 'node:path';
const dir=path.resolve('docs/qa-informe');
const tabs=await(await fetch('http://127.0.0.1:9231/json')).json();
const ws=new WebSocket(tabs.find(t=>t.type==='page').webSocketDebuggerUrl);
await new Promise(r=>ws.addEventListener('open',r,{once:true}));
let id=0;const pending=new Map();
ws.addEventListener('message',ev=>{const m=JSON.parse(ev.data);if(m.id){const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(Error(m.error.message)):p.resolve(m.result);}});
const cmd=(method,params={})=>new Promise((resolve,reject)=>{const n=++id;pending.set(n,{resolve,reject});ws.send(JSON.stringify({id:n,method,params}));});
async function ev(expression){const r=await cmd('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw Error(r.exceptionDetails.text);return r.result.value;}
await cmd('Page.enable');
await cmd('Emulation.setDeviceMetricsOverride',{width:850,height:1100,deviceScaleFactor:1,mobile:false});
await cmd('Page.navigate',{url:'file:///'+path.join(dir,'informe.html').replaceAll('\\','/')});
await new Promise(r=>setTimeout(r,1800));
await ev('document.fonts.ready.then(()=>true)');
const images=await ev('Array.from(document.images).every(i=>i.complete&&i.naturalWidth>0)');
if(!images)throw Error('Faltan imágenes');
const footer='<div style="font-size:8px;width:100%;padding:0 48px;color:#64776c;display:flex;justify-content:space-between"><span>Sistema de autenticidad · Pruebas funcionales</span><span>Página <span class="pageNumber"></span> de <span class="totalPages"></span></span></div>';
const pdf=await cmd('Page.printToPDF',{printBackground:true,preferCSSPageSize:true,displayHeaderFooter:true,headerTemplate:'<div></div>',footerTemplate:footer});
fs.writeFileSync(path.join(dir,'Sistema_de_autenticidad_Pruebas_funcionales.pdf'),Buffer.from(pdf.data,'base64'));
const tree=await ev('(()=>{function t(n){if(n.nodeType===3)return {tag:"#text",text:n.textContent};if(n.nodeType!==1)return null;return {tag:n.tagName.toLowerCase(),cls:n.className,src:n.getAttribute("src"),width:n.getAttribute("width"),height:n.getAttribute("height"),children:Array.from(n.childNodes).map(t).filter(Boolean)};}return Array.from(document.querySelectorAll("body>div")).map(t);})()');
const previews=['.cover','.page:nth-child(5)','.page:nth-child(17)'];
for(let i=0;i<previews.length;i++){
 await ev('document.querySelector('+JSON.stringify(previews[i])+').scrollIntoView()');
 const shot=await cmd('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
 fs.writeFileSync(path.join(dir,'vista-'+i+'.png'),Buffer.from(shot.data,'base64'));
}
ws.close();
const pack=path.join(dir,'word-package');
for(const d of ['','_rels','word','word/_rels','word/media','docProps'])fs.mkdirSync(path.join(pack,d),{recursive:true});
const esc=s=>String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
const ns='http://schemas.openxmlformats.org/wordprocessingml/2006/main';
const pstyle=(style,extra='')=>'<w:pPr><w:pStyle w:val="'+style+'"/>'+extra+'</w:pPr>';
function textOf(n){if(n.tag==='#text')return n.text;if(n.tag==='br')return '\n';return(n.children||[]).map(textOf).join('');}
function runs(s,bold=false,color=''){return String(s).split('\n').map((l,i)=>(i?'<w:r><w:br/></w:r>':'')+'<w:r>'+((bold||color)?'<w:rPr>'+(bold?'<w:b/>':'')+(color?'<w:color w:val="'+color+'"/>':'')+'</w:rPr>':'')+'<w:t xml:space="preserve">'+esc(l)+'</w:t></w:r>').join('');}
function p(s,style='Normal',extra=''){return'<w:p>'+pstyle(style,extra)+runs(s)+'</w:p>';}
let imageId=0;const rels=[];
function picture(n){
 const rid='rImage'+(++imageId);const name='image'+imageId+'.png';
 fs.copyFileSync(n.src,path.join(pack,'word/media',name));rels.push('<Relationship Id="'+rid+'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'+name+'"/>');
 const cx=Number(n.width||640)*9525,cy=Number(n.height||436)*9525;
 return '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="20" w:after="30"/></w:pPr><w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0"><wp:extent cx="'+cx+'" cy="'+cy+'"/><wp:docPr id="'+imageId+'" name="Evidencia '+imageId+'"/><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="'+imageId+'" name="'+name+'"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="'+rid+'"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="'+cx+'" cy="'+cy+'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>';
}
function table(n){
 const trs=[];function collect(x){if(x.tag==='tr')trs.push(x);else for(const c of x.children||[])collect(c);}collect(n);
 const cols=trs[0].children.filter(c=>c.tag==='td'||c.tag==='th').length;
 const widths=cols===2?[2300,7700]:cols===4?[1250,5000,1650,2100]:Array(cols).fill(Math.floor(10000/cols));
 let s='<w:tbl><w:tblPr><w:tblW w:w="10000" w:type="dxa"/><w:tblLayout w:type="fixed"/><w:tblBorders>'+['top','left','bottom','right','insideH','insideV'].map(x=>'<w:'+x+' w:val="single" w:sz="4" w:color="D3E1D9"/>').join('')+'</w:tblBorders><w:tblCellMar><w:top w:w="65" w:type="dxa"/><w:bottom w:w="65" w:type="dxa"/><w:left w:w="100" w:type="dxa"/><w:right w:w="100" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>'+widths.map(w=>'<w:gridCol w:w="'+w+'"/>').join('')+'</w:tblGrid>';
 for(const tr of trs){s+='<w:tr><w:trPr><w:cantSplit/></w:trPr>';let j=0;for(const c of tr.children.filter(c=>c.tag==='td'||c.tag==='th')){
 const head=c.tag==='th',matrix=n.cls==='matrix',color=head?(matrix?'FFFFFF':'14573E'):c.cls==='pass'?'087642':'20382F';
 s+='<w:tc><w:tcPr><w:tcW w:w="'+widths[j++]+'" w:type="dxa"/><w:shd w:fill="'+(head?(matrix?'06452F':'EDF5EF'):'FFFFFF')+'"/></w:tcPr><w:p>'+pstyle('Cell')+runs(textOf(c),head||c.cls==='pass',color)+'</w:p></w:tc>';
 }s+='</w:tr>';}return s+'</w:tbl>'+p('','Cell');
}
function render(n,cover=false){
 if(n.tag==='#text')return n.text.trim()?p(n.text):'';
 if(n.tag==='img')return picture(n);
 if(n.tag==='table')return table(n);
 if(n.tag==='h1')return p(textOf(n),cover?'Title':'Heading1');
 if(n.tag==='h2')return p(textOf(n),cover?'Subtitle':'Heading2');
 if(n.tag==='p'){
 const imgs=(n.children||[]).filter(c=>c.tag==='img');if(imgs.length)return imgs.map(picture).join('');
 const style=({'kicker':'Kicker','caption':'Caption','lead':'Lead','covermeta':'Meta','evidence-label':'Kicker'})[n.cls]||'Normal';
 return p(textOf(n),style);
 }
 if(n.tag==='pre')return p(textOf(n),'Code');
 return (n.children||[]).map(c=>render(c,cover)).join('');
}
let body='';
for(let i=0;i<tree.length;i++){if(i)body+='<w:p><w:r><w:br w:type="page"/></w:r></w:p>';body+=render(tree[i],i===0);}
body+='<w:sectPr><w:headerReference w:type="default" r:id="rHeader"/><w:footerReference w:type="default" r:id="rFooter"/><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="820" w:right="907" w:bottom="850" w:left="907" w:header="360" w:footer="360"/></w:sectPr>';
const xmlHead='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
fs.writeFileSync(path.join(pack,'word/document.xml'),xmlHead+'<w:document xmlns:w="'+ns+'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"><w:body>'+body+'</w:body></w:document>');
function style(id,size,color,bold=false,after=90,keep=false){return'<w:style w:type="paragraph" w:styleId="'+id+'"><w:name w:val="'+id+'"/><w:pPr><w:spacing w:after="'+after+'" w:line="240" w:lineRule="auto"/>'+(keep?'<w:keepNext/>':'')+'</w:pPr><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="'+size+'"/><w:color w:val="'+color+'"/>'+(bold?'<w:b/>':'')+'</w:rPr></w:style>';}
fs.writeFileSync(path.join(pack,'word/styles.xml'),xmlHead+'<w:styles xmlns:w="'+ns+'">'+style('Normal',20,'20382F')+style('Cell',18,'20382F',false,0)+style('Title',76,'06452F',true,220)+style('Subtitle',40,'386351',false,150)+style('Heading1',36,'06452F',true,160,true)+style('Heading2',26,'126447',true,80,true)+style('Kicker',16,'16805D',true,80,true)+style('Caption',16,'64776C',false,70)+style('Lead',25,'506B5E',false,200)+style('Meta',20,'506B5E')+style('Code',19,'20382F')+'</w:styles>');
fs.writeFileSync(path.join(pack,'word/header1.xml'),xmlHead+'<w:hdr xmlns:w="'+ns+'">'+p('SISTEMA DE AUTENTICIDAD | PRUEBAS FUNCIONALES','Caption')+'</w:hdr>');
fs.writeFileSync(path.join(pack,'word/footer1.xml'),xmlHead+'<w:ftr xmlns:w="'+ns+'"><w:p>'+pstyle('Caption','<w:jc w:val="right"/>')+runs('16/10/2026 · Página ')+'<w:fldSimple w:instr="PAGE"/>'+runs(' de ')+'<w:fldSimple w:instr="NUMPAGES"/></w:p></w:ftr>');
rels.push('<Relationship Id="rStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>','<Relationship Id="rHeader" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>','<Relationship Id="rFooter" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>');
fs.writeFileSync(path.join(pack,'word/_rels/document.xml.rels'),xmlHead+'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'+rels.join('')+'</Relationships>');
fs.writeFileSync(path.join(pack,'_rels/.rels'),xmlHead+'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rDoc" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/><Relationship Id="rCore" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/></Relationships>');
fs.writeFileSync(path.join(pack,'docProps/core.xml'),xmlHead+'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/"><dc:title>Sistema de autenticidad - Pruebas funcionales e implantación</dc:title><dc:creator>Enrique</dc:creator><dc:description>18 casos ejecutados con evidencias visuales</dc:description></cp:coreProperties>');
const types=[['/word/document.xml','document.main'],['/word/styles.xml','styles'],['/word/header1.xml','header'],['/word/footer1.xml','footer']].map(([part,type])=>'<Override PartName="'+part+'" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.'+type+'+xml"/>').join('');
fs.writeFileSync(path.join(pack,'[Content_Types].xml'),xmlHead+'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/>'+types+'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/></Types>');
console.log('PDF generado; paquete Word preparado con '+imageId+' imágenes incrustadas.');
