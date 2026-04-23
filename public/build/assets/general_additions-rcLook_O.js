document.querySelectorAll("input.numeric-only").forEach(e=>{e.addEventListener("input",()=>{let t=e.value.replace(/\D/g,"");const l=e.getAttribute("maxlength");l&&(t=t.slice(0,l)),e.value=t})});
