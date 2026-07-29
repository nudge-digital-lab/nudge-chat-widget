(function(){
	var cfg = window.NudgeChatWidgetData || {};
	var WHATSAPP = cfg.whatsapp || '';
	var BOT_NAME = cfg.botName || 'Nudge';
	var SITE_LABEL = cfg.siteLabel || '';
	var FLOW = cfg.flow || [];
	var FINAL_MESSAGE = cfg.finalMessage || "¡Listo, {nombre}! Tocá el botón para enviarnos tu consulta — solemos responder en pocos minutos ⏱️";

	if (!WHATSAPP || !FLOW.length) return;

	var body=document.getElementById('ncw-body'), input=document.getElementById('ncw-input'), panel=document.getElementById('ncw-panel');
	var data={}, step=0;

	function fillTokens(text){
		return String(text || '')
			.replace(/\{bot_name\}/g, BOT_NAME)
			.replace(/\{nombre\}/g, data.nombre || '');
	}
	function scroll(){ body.scrollTop=body.scrollHeight; }
	function addMsg(t,who){ var m=document.createElement('div'); m.className='ncw-msg '+who; m.textContent=t; body.appendChild(m); scroll(); }
	function typing(cb){ var t=document.createElement('div'); t.className='ncw-typing'; t.innerHTML='<i></i><i></i><i></i>'; body.appendChild(t); scroll(); setTimeout(function(){t.remove();cb();},650); }
	function clearInput(){ input.innerHTML=''; }
	function renderStep(){ if(step>=FLOW.length) return finish(); var s=FLOW[step]; typing(function(){ addMsg(fillTokens(s.bot),'bot'); clearInput(); if(s.type==='chips') renderChips(s); else renderInput(s); }); }
	function advance(key,value){ data[key]=value; if(value) addMsg(value,'user'); step++; clearInput(); renderStep(); }
	function renderInput(s){ var form=document.createElement('div'); form.className='ncw-form'; var f=document.createElement('input'); f.type='text'; f.placeholder=s.placeholder||''; f.autocomplete='off'; var b=document.createElement('button'); b.setAttribute('aria-label','Enviar'); b.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 11l18-8-8 18-2.3-7.5L3 11z"/></svg>'; function submit(){ var v=f.value.trim(); if(!v&&!s.optional){f.focus();return;} advance(s.key,v||'—'); } b.addEventListener('click',submit); f.addEventListener('keydown',function(e){if(e.key==='Enter')submit();}); form.appendChild(f); form.appendChild(b); input.appendChild(form); setTimeout(function(){f.focus();},60); }
	function renderChips(s){ var w=document.createElement('div'); w.className='ncw-chips'; (s.options||[]).forEach(function(opt){ var c=document.createElement('button'); c.className='ncw-chip'; c.textContent=opt; c.addEventListener('click',function(){advance(s.key,opt);}); w.appendChild(c); }); input.appendChild(w); }
	function finish(){
		window.dispatchEvent(new CustomEvent('nudgeChatWidgetLead', { detail: { data: Object.assign({}, data), whatsapp: WHATSAPP, botName: BOT_NAME, siteLabel: SITE_LABEL } }));
		typing(function(){ addMsg(fillTokens(FINAL_MESSAGE),'bot'); var msg="¡Hola "+BOT_NAME+"! Soy "+data.nombre+".\n\n• Necesito: "+data.necesidad+"\n• Situación: "+data.situacion+"\n• Plazo: "+data.plazo+"\n• Contacto: "+data.contacto+(data.extra&&data.extra!=='—'?"\n• Nota: "+data.extra:"")+(SITE_LABEL?"\n\n(Enviado desde "+SITE_LABEL+")":""); var link="https://wa.me/"+WHATSAPP+"?text="+encodeURIComponent(msg); clearInput(); var send=document.createElement('a'); send.className='ncw-send'; send.href=link; send.target='_blank'; send.rel='noopener'; send.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2Zm5.3 14c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1-1.4-1-2.6 0-1.2.6-1.8.9-2 .2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5c-.2.2-.3.4-.1.7.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.2.1.4.1.6-.1l.8-1c.2-.2.4-.2.6-.1l1.9 1c.2.1.4.2.4.3.1.1.1.6 0 1.2Z"/></svg> Enviar por WhatsApp'; input.appendChild(send); var again=document.createElement('button'); again.className='ncw-restart'; again.textContent='Empezar de nuevo'; again.addEventListener('click',reset); input.appendChild(again); }); }
	function reset(){ data={}; step=0; body.innerHTML=''; clearInput(); renderStep(); }
	var started=false;
	function openPanel(){ panel.hidden=false; document.getElementById('ncw-open').style.display='none'; if(!started){started=true;renderStep();} }
	function closePanel(){ panel.hidden=true; document.getElementById('ncw-open').style.display=''; }
	document.getElementById('ncw-open').addEventListener('click',openPanel);
	document.getElementById('ncw-close').addEventListener('click',closePanel);
})();
