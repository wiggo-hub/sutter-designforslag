(function(){
  /* Mobile menu */
  var bar=document.querySelector('.bar'), btn=document.querySelector('.menu-btn');
  if(btn){
    btn.addEventListener('click',function(){var o=bar.classList.toggle('open'); btn.setAttribute('aria-expanded',o); btn.textContent=o?'Lukk':'Meny';});
    document.addEventListener('keydown',function(e){ if(e.key==='Escape' && bar.classList.contains('open')){bar.classList.remove('open'); btn.setAttribute('aria-expanded','false'); btn.textContent='Meny'; btn.focus();} });
  }

  /* Lightbox: the picture in max size, no text */
  var lb=document.getElementById('lb');
  var list=[], cur=0, back=null;
  if(lb){
    var media=lb.querySelector('.lb-media'), close=lb.querySelector('.close'), prev=lb.querySelector('.lb-prev'), next=lb.querySelector('.lb-next');
    function show(i){
      cur=(i+list.length)%list.length; var it=list[cur];
      media.innerHTML = it.video
        ? '<video src="'+it.video+'" poster="'+it.poster+'" controls autoplay playsinline aria-label="'+it.alt+'"></video>'
        : '<img src="'+it.src+'" alt="'+it.alt+'">';
      var many=list.length>1; prev.hidden=!many; next.hidden=!many;
    }
    function open(items,i,from){ list=items; back=from; show(i); lb.hidden=false; document.body.style.overflow='hidden'; close.focus(); }
    function shut(){ media.innerHTML=''; lb.hidden=true; document.body.style.overflow=''; if(back) back.focus(); }
    close.addEventListener('click',shut);
    /* swipe left/right on phones and tablets */
    var x0=null;
    lb.addEventListener('touchstart',function(e){ x0=e.touches[0].clientX; },{passive:true});
    lb.addEventListener('touchend',function(e){ if(x0===null||list.length<2) return; var dx=e.changedTouches[0].clientX-x0; if(Math.abs(dx)>50) show(cur+(dx<0?1:-1)); x0=null; });
    prev.addEventListener('click',function(){show(cur-1)});
    next.addEventListener('click',function(){show(cur+1)});
    lb.addEventListener('click',function(e){ if(e.target===lb||e.target===media) shut(); });
    lb.addEventListener('keydown',function(e){
      if(e.key==='Escape') shut();
      if(e.key==='ArrowRight' && list.length>1) show(cur+1);
      if(e.key==='ArrowLeft' && list.length>1) show(cur-1);
      if(e.key==='Tab'){ // keep focus inside the dialog
        var f=[].slice.call(lb.querySelectorAll('button:not([hidden]),video')); var i=f.indexOf(document.activeElement);
        e.preventDefault(); f[(i+(e.shiftKey?-1:1)+f.length)%f.length].focus();
      }
    });
    document.querySelectorAll('[data-squares]').forEach(function(root){
      var items=JSON.parse(root.querySelector('script[type="application/json"]').textContent);
      root.querySelectorAll('button.sq').forEach(function(b){ b.addEventListener('click',function(){ open(items,+b.dataset.i,b); }); });
    });
  }

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
