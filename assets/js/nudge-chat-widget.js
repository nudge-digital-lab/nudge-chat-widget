(function(){
	var cfg = window.NudgeChatWidgetData || {};
	var WHATSAPP = cfg.whatsapp || '';
	var BOT_NAME = cfg.botName || 'Nudge';
	var SITE_LABEL = cfg.siteLabel || '';

	if (!WHATSAPP) return;

	var FLOW = [
		{bot:"¡Hola! 👋 Soy el asistente de "+BOT_NAME+". Estamos terminando la web, pero tu proyecto puede arrancar hoy. ¿Cómo es tu nombre?", input:{key:"nombre", placeholder:"Tu nombre"}},
		{bot:function(d){return "Genial, "+d.nombre+" 🚀 ¿Qué necesitás?";}, chips:{key:"necesidad", options:["Crear mi tienda online","Rediseñar mi tienda","Migrar a Tiendanube","Otra cosa"]}},
		{bot:"¿En qué punto estás hoy?", chips:{key:"situacion", options:["Ya tengo tienda online","Tengo marca / redes","Arranco de cero"]}},
		{bot:"¿Para cuándo lo querrías?", chips:{key:"plazo", options:["Lo antes posible","Este mes","En 1 a 3 meses","Estoy explorando"]}},
		{bot:"Perfecto. Dejame tu WhatsApp o email para coordinar 👇", input:{key:"contacto", placeholder:"WhatsApp o email"}},
		{bot:"¿Querés contarnos algo más? (opcional)", input:{key:"extra", placeholder:"Escribí o tocá enviar", optional:true}}
	];

	var body=document.getElementById('ncw-body'), input=document.getElementById('ncw-input'), panel=document.getElementById('ncw-panel');
	var data={}, step=0;
	function scroll(){ body.scrollTop=body.scrollHeight; }
	function botText(s){ return typeof s==='function'?s(data):s; }
	function addMsg(t,who){ var m=document.createElement('div'); m.className='ncw-msg '+who; m.textContent=t; body.appendChild(m); scroll(); }
	function typing(cb){ var t=document.createElement('div'); t.className='ncw-typing'; t.innerHTML='<i></i><i></i><i></i>'; body.appendChild(t); scroll(); setTimeout(function(){t.remove();cb();},650); }
	function clearInput(){ input.innerHTML=''; }
	function renderStep(){ if(step>=FLOW.length) return finish(); var s=FLOW[step]; typing(function(){ addMsg(botText(s.bot),'bot'); clearInput(); if(s.input) renderInput(s.input); else if(s.chips) renderChips(s.chips); }); }
	function advance(key,value){ data[key]=value; if(value) addMsg(value,'user'); step++; clearInput(); renderStep(); }
	function renderInput(cfgField){ var form=document.createElement('div'); form.className='ncw-form'; var f=document.createElement('input'); f.type='text'; f.placeholder=cfgField.placeholder; f.autocomplete='off'; var b=document.createElement('button'); b.setAttribute('aria-label','Enviar'); b.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 11l18-8-8 18-2.3-7.5L3 11z"/></svg>'; function submit(){ var v=f.value.trim(); if(!v&&!cfgField.optional){f.focus();return;} advance(cfgField.key,v||'—'); } b.addEventListener('click',submit); f.addEventListener('keydown',function(e){if(e.key==='Enter')submit();}); form.appendChild(f); form.appendChild(b); input.appendChild(form); setTimeout(function(){f.focus();},60); }
	function renderChips(cfgField){ var w=document.createElement('div'); w.className='ncw-chips'; cfgField.options.forEach(function(opt){ var c=document.createElement('button'); c.className='ncw-chip'; c.textContent=opt; c.addEventListener('click',function(){advance(cfgField.key,opt);}); w.appendChild(c); }); input.appendChild(w); }
	function finish(){ typing(function(){ addMsg("¡Listo, "+data.nombre+"! Tocá el botón y nos llega tu consulta con todos los datos. Te respondemos enseguida 🙌",'bot'); var msg="¡Hola "+BOT_NAME+"! Soy "+data.nombre+".\n\n• Necesito: "+data.necesidad+"\n• Situación: "+data.situacion+"\n• Plazo: "+data.plazo+"\n• Contacto: "+data.contacto+(data.extra&&data.extra!=='—'?"\n• Nota: "+data.extra:"")+(SITE_LABEL?"\n\n(Enviado desde "+SITE_LABEL+")":""); var link="https://wa.me/"+WHATSAPP+"?text="+encodeURIComponent(msg); clearInput(); var send=document.createElement('a'); send.className='ncw-send'; send.href=link; send.target='_blank'; send.rel='noopener'; send.innerHTML='<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2Zm5.3 14c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1-1.4-1-2.6 0-1.2.6-1.8.9-2 .2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.1.1.3 0 .5l-.4.5c-.2.2-.3.4-.1.7.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.4 2.4 1.5.2.1.4.1.6-.1l.8-1c.2-.2.4-.2.6-.1l1.9 1c.2.1.4.2.4.3.1.1.1.6 0 1.2Z"/></svg> Enviar por WhatsApp'; input.appendChild(send); var again=document.createElement('button'); again.className='ncw-restart'; again.textContent='Empezar de nuevo'; again.addEventListener('click',reset); input.appendChild(again); }); }
	function reset(){ data={}; step=0; body.innerHTML=''; clearInput(); renderStep(); }
	var started=false;
	function openPanel(){ panel.hidden=false; document.getElementById('ncw-open').style.display='none'; if(!started){started=true;renderStep();} }
	function closePanel(){ panel.hidden=true; document.getElementById('ncw-open').style.display=''; }
	document.getElementById('ncw-open').addEventListener('click',openPanel);
	document.getElementById('ncw-close').addEventListener('click',closePanel);
})();
