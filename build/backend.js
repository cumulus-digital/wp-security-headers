(()=>{var e={629:()=>{(e=>{e.document.addEventListener("mouseover",(e=>{e.target.matches(".wpsf-group__row-index, .wpsf-group__row-index *")&&e.target.closest(".wpsf-group__row-index").setAttribute("title","Click and drag to reorder")})),e.document.addEventListener("mousedown",(e=>{e.target.matches(".wpsf-group__row-index, .wpsf-group__row-index *")&&e.target.closest(".wpsf-group__row").setAttribute("draggable",!0)}));let t=null;const r=new Image;r.src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7",document.body.appendChild(r);let n=null;e.document.addEventListener("dragstart",(e=>{e.target.matches(".wpsf-group__row")&&(t=e.target,e.dataTransfer.setDragImage(r,0,0),e.dataTransfer.dropEffect="move",e.dataTransfer.effectAllowed="move",n=e.target.closest(".wpsf-group"))})),e.document.addEventListener("dragend",(e=>{e.target.matches(".wpsf-group__row, .wpsf-group__rot *")&&e.target.closest(".wpsf-group__row").setAttribute("draggable",!1)})),e.document.addEventListener("dragover",(e=>{if(!n.contains(e.target))return;if(!e.target.matches(".wpsf-group tr, .wpsf-group tr *"))return;e.preventDefault();let r=Array.prototype.slice.call(e.target.closest(".wpsf-group").querySelector("tr").parentNode.children),a=e.target.closest(".wpsf-group__row");r.indexOf(a)>r.indexOf(t)?a.after(t):a.before(t)}))})(window.self)},892:()=>{let e=!1;window.cmlsSetUnsaved=t=>{e=t},window.addEventListener("change",(e=>{if(e.target.matches("input, textarea, select")){let t=e.target.defaultValue,r=e.target.value;if(e.target.matches("select")){const r=e.target.querySelectorAll("option");r&&r.forEach((e=>{e.defaultSelected&&(t=e.value)}))}e.target.matches('input[type="checkbox"]')&&(t=e.target.getAttribute("checked")?1:0,r=e.target.checked?1:0),r!==t&&cmlsSetUnsaved(!0)}})),window.addEventListener("beforeunload",(t=>{if(e)return t.preventDefault(),t.returnValue="string","string"}))},164:()=>{const e=document.querySelector(".wpsf-settings form"),t=document.querySelector("#csp_mode_enabled"),r="enabled"===t?.value,n=document.querySelector("#csp_mode_in-admin"),a=n?.checked;e.addEventListener("submit",(e=>{const o=[];if(r||"enabled"!==t?.value||o.push("Enabling a Content Security Policy may break your site!"),"enabled"===t?.value&&!a&&n.checked&&o.push("Enforcing a Content Security Policy in the WordPress admin area may lock you out! Reversing this action may require database access."),o.length&&(alert("WARNING!\n\n"+o.join("\n\n")+"\n\nYou will be asked to confirm after this dialog"),!confirm("Are you sure you want to save these changes?")))return e.preventDefault(),!1;window.cmlsSetUnsaved(!1)}))}},t={};function r(n){var a=t[n];if(void 0!==a)return a.exports;var o=t[n]={exports:{}};return e[n](o,o.exports,r),o.exports}r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";r(892),r(164);const e=window.jQuery;!function(e){const t=e('input[id^="csp_auto-nonce_directives-"]');e((()=>{e(document).on("keyup",'input[name$="[policy]"]',(function(){const e=this.id.match(/csp_policies\_([a-z\-]+)\-policy/);if(e?.length>1){const r=e[1],n=t.filter(`#csp_auto-nonce_directives-${r}`);if(n?.length){const e=[];this.value.includes("'none'")&&e.push("'none'"),this.value.includes("'unsafe-inline'")&&e.push("'unsafe-inline'"),e.length&&((e,t)=>{let r=e.parent().find("span.desc");r?.length||(r=e.after('<span class="desc"></span>')),r.html(`<strong class="error-message">Warning!</strong> ${t}`)})(n.parent(),`<a href="#csp_policies_${r}">Policy</a> contains\n\t\t\t\t\t\t\t\t${e.join(" and ")}`)}}})),setTimeout((()=>{e(".wpsf-button-submit").off("click").on("click",(function(){e('.wpsf-settings__content > form > p.submit > input[type="submit"]').trigger("click")}))}),500)}))}(r.n(e)()),r(629)})()})();