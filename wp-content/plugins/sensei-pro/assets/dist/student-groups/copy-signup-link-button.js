(()=>{"use strict";var e,n={2545:(e,n,r)=>{const t=window.wp.element,o=window.wp.i18n,i=window.wp.domReady;var a=r.n(i);const s=window.wp.compose,c=window.wp.notices,l=window.wp.data,p=window.wp.editor,u=window.React;var d;function w(){return w=Object.assign?Object.assign.bind():function(e){for(var n=1;n<arguments.length;n++){var r=arguments[n];for(var t in r)Object.prototype.hasOwnProperty.call(r,t)&&(e[t]=r[t])}return e},w.apply(this,arguments)}function v(e){return u.createElement("svg",w({width:24,height:24,viewBox:"0 0 24 24",fill:"none",xmlns:"http://www.w3.org/2000/svg"},e),d||(d=u.createElement("path",{d:"M20.2 8v11c0 .7-.6 1.2-1.2 1.2H6v1.5h13c1.5 0 2.7-1.2 2.7-2.8V8h-1.5zM18 16.4V4.6c0-.9-.7-1.6-1.6-1.6H4.6C3.7 3 3 3.7 3 4.6v11.8c0 .9.7 1.6 1.6 1.6h11.8c.9 0 1.6-.7 1.6-1.6zm-13.5 0V4.6c0-.1.1-.1.1-.1h11.8c.1 0 .1.1.1.1v11.8c0 .1-.1.1-.1.1H4.6l-.1-.1z",fill:"currentColor"})))}const f=e=>{let{signupLink:n,editPageLink:r}=e;const{createNotice:i}=(0,l.useDispatch)(c.store),a=(0,s.useCopyToClipboard)(n,(()=>{i("info",(0,o.__)("Page link copied to the clipboard.","sensei-pro"),{isDismissible:!0,type:"snackbar",actions:[{url:r,label:(0,o.__)("Edit page","sensei-pro"),variant:"primary"}]})}));return(0,t.createElement)("button",{className:"sensei-group-copy-signup-link-button",type:"button",ref:a},(0,o.__)("Copy invite link","sensei-pro"),(0,t.createElement)(v,{className:"sensei-group-copy-signup-link-button__icon"}))};a()((()=>{Array.from(document.getElementsByClassName("sensei-group-copy-signup-link-container")).forEach((e=>{(0,t.render)((0,t.createElement)(f,e?.dataset),e)}));const e=document.createElement("div");document.querySelector("#wpfooter").insertAdjacentElement("afterbegin",e),(0,t.render)((0,t.createElement)(p.EditorSnackbars,null),e)}))}},r={};function t(e){var o=r[e];if(void 0!==o)return o.exports;var i=r[e]={exports:{}};return n[e](i,i.exports,t),i.exports}t.m=n,e=[],t.O=(n,r,o,i)=>{if(!r){var a=1/0;for(p=0;p<e.length;p++){for(var[r,o,i]=e[p],s=!0,c=0;c<r.length;c++)(!1&i||a>=i)&&Object.keys(t.O).every((e=>t.O[e](r[c])))?r.splice(c--,1):(s=!1,i<a&&(a=i));if(s){e.splice(p--,1);var l=o();void 0!==l&&(n=l)}}return n}i=i||0;for(var p=e.length;p>0&&e[p-1][2]>i;p--)e[p]=e[p-1];e[p]=[r,o,i]},t.n=e=>{var n=e&&e.__esModule?()=>e.default:()=>e;return t.d(n,{a:n}),n},t.d=(e,n)=>{for(var r in n)t.o(n,r)&&!t.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:n[r]})},t.o=(e,n)=>Object.prototype.hasOwnProperty.call(e,n),(()=>{var e={3874:0,7958:0};t.O.j=n=>0===e[n];var n=(n,r)=>{var o,i,[a,s,c]=r,l=0;if(a.some((n=>0!==e[n]))){for(o in s)t.o(s,o)&&(t.m[o]=s[o]);if(c)var p=c(t)}for(n&&n(r);l<a.length;l++)i=a[l],t.o(e,i)&&e[i]&&e[i][0](),e[i]=0;return t.O(p)},r=globalThis.webpackChunksensei_pro=globalThis.webpackChunksensei_pro||[];r.forEach(n.bind(null,0)),r.push=n.bind(null,r.push.bind(r))})();var o=t.O(void 0,[7958],(()=>t(2545)));o=t.O(o)})();