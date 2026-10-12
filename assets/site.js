(function(){
  /* Mobile menu */
  var bar=document.querySelector('.bar'), btn=document.querySelector('.menu-btn');
  if(btn){
    btn.addEventListener('click',function(){var o=bar.classList.toggle('open'); btn.setAttribute('aria-expanded',o); btn.textContent=o?'Lukk':'Meny';});
    document.addEventListener('keydown',function(e){ if(e.key==='Escape' && bar.classList.contains('open')){bar.classList.remove('open'); btn.setAttribute('aria-expanded','false'); btn.textContent='Meny'; btn.focus();} });
  }

  /* Lightbox */
  var lb=document.getElementById('lb'), lbImg, lbCap, lbClose, lbBack=null;
  if(lb){
    lbImg=lb.querySelector('img'); lbCap=lb.querySelector('figcaption'); lbClose=lb.querySelector('.close');
    lbClose.addEventListener('click',closeLb);
    lb.addEventListener('click',function(e){ if(e.target===lb) closeLb(); });
    lb.addEventListener('keydown',function(e){
      if(e.key==='Escape') closeLb();
      if(e.key==='Tab'){ e.preventDefault(); lbClose.focus(); } // only one control: keep focus inside
    });
  }
  function openLb(src,alt,cap,from){ lbImg.src=src; lbImg.alt=alt; lbCap.textContent=cap; lb.hidden=false; document.body.style.overflow='hidden'; lbBack=from; lbClose.focus(); }
  function closeLb(){ lb.hidden=true; document.body.style.overflow=''; if(lbBack) lbBack.focus(); }

  /* Gallery: one large image + the four others beside it */
  document.querySelectorAll('[data-gallery]').forEach(function(root){
    var items=JSON.parse(root.querySelector('script[type="application/json"]').textContent);
    var frame=root.querySelector('.frame'), four=root.querySelector('.four'),
        h=root.querySelector('.cap h3'), p=root.querySelector('.cap .d'), cnt=root.querySelector('.count'),
        live=root.querySelector('.live'), cur=0, n=items.length;

    function render(i, user){
      var it=items[i]; cur=i;
      frame.classList.toggle('fit',!!it.fit);
      var html='';
      if(it.video){ html='<video src="'+it.video+'" poster="'+it.poster+'" controls playsinline preload="none"'+(user?' autoplay muted':'')+' aria-label="Film: '+it.t+'"></video>'; }
      else if(it.ph){ html='<div class="ph"><span>Film</span><b>'+it.ph+'</b><span>Legges inn fra YouTube eller Vimeo</span></div>'; }
      else { html='<img src="'+it.src+'" srcset="'+it.srcset+'" sizes="(max-width:700px) 100vw, 60vw" alt="'+it.alt+'" width="'+it.w+'" height="'+it.h+'"><button class="zoom" type="button"><span class="sr-only">Vis «'+it.t+'» i stort format</span></button>'; }
      html+='<button class="navbtn prev" type="button" aria-label="Forrige bilde">‹</button><button class="navbtn next" type="button" aria-label="Neste bilde">›</button>';
      frame.innerHTML=html;
      var z=frame.querySelector('.zoom'); if(z) z.addEventListener('click',function(){ openLb(it.src,it.alt,it.t+(it.d?' – '+it.d:''),z); });
      frame.querySelector('.prev').addEventListener('click',function(){ render((cur-1+n)%n,true); frame.querySelector('.prev').focus(); });
      frame.querySelector('.next').addEventListener('click',function(){ render((cur+1)%n,true); frame.querySelector('.next').focus(); });
      h.textContent=it.t; p.textContent=it.d||''; cnt.textContent='Bilde '+(i+1)+' av '+n;
      four.innerHTML='';
      items.forEach(function(x,j){
        if(j===i) return;
        var li=document.createElement('li'), b=document.createElement('button');
        b.type='button';
        b.innerHTML=x.ph ? '<span class="ph">'+x.ph+'</span><span class="tag">FILM</span>'
          : '<img src="'+(x.thumb||x.poster)+'" alt="" loading="lazy">'+(x.video?'<span class="tag">FILM</span>':'');
        b.setAttribute('aria-label','Vis '+(x.video||x.ph?'film':'bilde')+': '+x.t);
        b.addEventListener('click',function(){ render(j,true); var first=frame.querySelector('.zoom,video,.prev'); if(first) first.focus(); });
        li.appendChild(b); four.appendChild(li);
      });
      if(user && live) live.textContent='Viser '+it.t+', bilde '+(i+1)+' av '+n;
    }
    render(0,false);
    root.addEventListener('keydown',function(e){
      if(e.target.closest('video')) return;
      if(e.key==='ArrowRight'){ render((cur+1)%n,true); }
      if(e.key==='ArrowLeft'){ render((cur-1+n)%n,true); }
    });
  });

  /* Contact form (demo – nothing is sent) */
  var form=document.getElementById('kontaktskjema');
  if(form){
    form.addEventListener('submit',function(e){
      e.preventDefault(); var ok=true, first=null;
      form.querySelectorAll('[required]').forEach(function(f){
        var err=document.getElementById(f.id+'-feil'), bad=!f.value.trim() || (f.type==='email' && !/^\S+@\S+\.\S+$/.test(f.value));
        f.setAttribute('aria-invalid',bad); err.hidden=!bad; if(bad){ ok=false; if(!first) first=f; }
      });
      var st=document.getElementById('skjema-status');
      if(!ok){ st.textContent='Fyll ut feltene som er merket.'; first.focus(); return; }
      st.textContent='Takk! Dette er en prøveversjon, så meldingen er ikke sendt. På den ferdige siden går den rett til Paul.';
    });
  }
})();
