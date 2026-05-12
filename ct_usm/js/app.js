// CT-USM — app.js

function showTab(n) {
    document.querySelectorAll('.tab-section').forEach(el => el.classList.add('d-none'));
    const tab = document.getElementById('tab' + n);
    if (tab) tab.classList.remove('d-none');
    document.querySelectorAll('#formTabs .nav-link').forEach((link, i) => {
        link.classList.toggle('active', i === n - 1);
        const badge = link.querySelector('.badge');
        if (badge) badge.className = 'badge me-1 ' + (i < n-1 ? 'bg-success' : i === n-1 ? 'bg-usm' : 'bg-secondary');
    });
}

function toggleEmpresa(chk) {
    document.getElementById('divEmpresaExistente').classList.toggle('d-none', !chk.checked);
    document.getElementById('divEmpresaNueva').classList.toggle('d-none', chk.checked);
}

let equipoData = [];

function agregarIntegrante() {
    const sel = document.getElementById('selectPersona');
    const rol = document.getElementById('inputRolEquipo').value.trim();
    const idP = sel.value;
    const opt = sel.options[sel.selectedIndex];
    if (!idP) { alert('Selecciona una persona.'); return; }
    if (!rol)  { alert('Ingresa el rol.'); return; }
    if (equipoData.some(e => e.id === idP)) { alert('Persona ya en el equipo.'); return; }
    equipoData.push({ id: idP, nombre: opt.dataset.nombre||'', tipo: opt.dataset.tipo||'', rol });
    renderEquipo();
    sel.value = '';
    document.getElementById('inputRolEquipo').value = '';
}

function quitarIntegrante(idx) { equipoData.splice(idx,1); renderEquipo(); }

function renderEquipo() {
    const tbody = document.getElementById('equipoBody');
    const vacio = document.getElementById('equipoVacio');
    if (!tbody) return;
    tbody.querySelectorAll('.fila-int').forEach(r=>r.remove());
    if (equipoData.length === 0) { vacio.style.display=''; return; }
    vacio.style.display='none';
    let prof=0, est=0;
    equipoData.forEach((p,i) => {
        if (p.tipo==='Profesor') prof++;
        if (p.tipo==='Estudiante') est++;
        const tr=document.createElement('tr'); tr.className='fila-int';
        tr.innerHTML=`<td class="fw-medium">${e(p.nombre)}</td><td><span class="badge bg-light text-dark border">${e(p.tipo)}</span></td><td>${e(p.rol)}</td><td><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="quitarIntegrante(${i})"><i class="bi bi-trash"></i></button><input type="hidden" name="equipo_personas[]" value="${e(p.id)}"><input type="hidden" name="equipo_roles[]" value="${e(p.rol)}"></td>`;
        tbody.appendChild(tr);
    });
    const alerta = document.getElementById('alertaEquipo');
    if (alerta) {
        const msgs=[];
        if(prof<3) msgs.push(`${prof}/3 profesores`);
        if(est<5)  msgs.push(`${est}/5 estudiantes`);
        if(msgs.length){ alerta.classList.remove('d-none'); document.getElementById('alertaEquipoTexto').textContent=msgs.join(' — '); }
        else alerta.classList.add('d-none');
    }
}

let cronoData = [];

function agregarEtapa() {
    const nombre  = document.getElementById('inputEtapaNombre').value.trim();
    const desc    = document.getElementById('inputEtapaDesc').value.trim();
    const semanas = parseInt(document.getElementById('inputEtapaSemanas').value)||4;
    if (!nombre) { alert('Ingresa el nombre de la etapa.'); return; }
    const total = cronoData.reduce((s,x)=>s+x.semanas,0);
    if (total+semanas>36) { alert(`Supera 36 semanas (actual: ${total}, nuevo: ${total+semanas}).`); return; }
    cronoData.push({nombre,desc,semanas});
    renderCronograma();
    document.getElementById('inputEtapaNombre').value='';
    document.getElementById('inputEtapaDesc').value='';
    document.getElementById('inputEtapaSemanas').value='4';
}

function quitarEtapa(idx) { cronoData.splice(idx,1); renderCronograma(); }

function renderCronograma() {
    const tbody = document.getElementById('cronogramaBody');
    const vacio = document.getElementById('cronoVacio');
    if (!tbody) return;
    const total = cronoData.reduce((s,x)=>s+x.semanas,0);
    const info  = document.getElementById('totalSemanas');
    if(info) info.innerHTML=`Total: <strong>${total}/36 semanas</strong> ${total>36?'<span class="text-danger">⚠ Excede el máximo</span>':'<span class="text-success">✓</span>'}`;
    tbody.querySelectorAll('.fila-et').forEach(r=>r.remove());
    if(cronoData.length===0){ vacio.style.display=''; return; }
    vacio.style.display='none';
    cronoData.forEach((et,i)=>{
        const tr=document.createElement('tr'); tr.className='fila-et';
        tr.innerHTML=`<td>${i+1}</td><td class="fw-medium">${e(et.nombre)}</td><td class="text-muted">${e(et.desc||'—')}</td><td>${et.semanas}</td><td><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2" onclick="quitarEtapa(${i})"><i class="bi bi-trash"></i></button><input type="hidden" name="etapa_nombre[]" value="${e(et.nombre)}"><input type="hidden" name="etapa_desc[]" value="${e(et.desc)}"><input type="hidden" name="etapa_semanas[]" value="${et.semanas}"></td>`;
        tbody.appendChild(tr);
    });
}

function e(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
