function j(){const e=document.getElementById("cal-heatmap");if(!e)return console.log("📏 getResponsiveDimensions: contenedor no encontrado"),{cellSize:20,gutter:2,containerWidth:0,containerHeight:0};const n=e.offsetWidth,r=e.offsetHeight;let t,o;n>=2e3?(t=35,o=3,console.log("📏 Modo: Pantalla muy grande")):n>=1500?(t=Math.min(Math.max(n/55,20),34),o=Math.max(t*.1,2.5),console.log("📏 Modo: Pantalla grande")):n>=940?(t=Math.min(Math.max(n/52,14),26),o=Math.max(t*.1,2),console.log("📏 Modo: Pantalla mediana (>= 940px)")):(t=Math.min(n/18,30),o=Math.max(t*.12,1),console.log("📏 Modo: Pantalla pequeña"));const l={cellSize:Math.floor(t),gutter:Math.floor(o),containerWidth:n,containerHeight:r};return console.log("📏 Dimensiones calculadas:",l),l}function z(){const e=document.getElementById("cal-heatmap");e&&(e.innerHTML="",e.removeAttribute("style"))}function _(e=null){const n=new URLSearchParams;return e?.year&&e?.semester&&(n.set("year",e.year),n.set("semester",e.semester)),n}async function P(e=null){const n=_(e);n.set("include_navigation","1");const t=await(await fetch(`/api/eventos-json?${n.toString()}`)).json();return Array.isArray(t)?{events:t,period:$(),navigation:null}:t}async function L(e=null){const r=_(e).toString(),t=await fetch(`/api/mis-eventos-json${r?`?${r}`:""}`);if(!t.ok)return console.error("Error al traer eventos",t.status),[];const o=await t.json();return console.log("🔑 Auth:",o.auth_id),o.eventos}const $=()=>{const e=new Date,n=e.getMonth(),r=e.getFullYear();let t,o;n>=0&&n<=5?(t=new Date(r,0,1),o=6):(t=new Date(r,6,1),o=6);const l=n>=0&&n<=5?1:2;return{year:r,semester:l,label:`Semestre ${l===1?"I":"II"} ${r}`,start:t.toISOString().split("T")[0],startDate:t,range:o}};function O(e,n){window.selectedCalendarDate=e;const r=document.getElementById("calendarModal");r.classList.remove("hidden"),r.classList.add("flex"),window.dispatchEvent(new CustomEvent("calendar-modal-opened",{detail:{date:e}}));const t=document.getElementById("calendarModalTitle"),o=document.getElementById("calendarModalBody"),l=document.getElementById("eventCount"),[c,b,M]=e.split("-").map(Number),w=new Date(c,b-1,M).toLocaleDateString("es-CO",{weekday:"long",year:"numeric",month:"long",day:"numeric"});if(t.textContent=`Eventos para el ${w}`,n.length>0){const d=document.querySelector('meta[name="user-id"]')?.content,g=document.querySelector('meta[name="user-role"]')?.content,y=g==="admin"||g==="superadmin",i=a=>{if(!a)return"No definido";const[h,m]=a.split(":").map(Number),p=h>=12?"PM":"AM";return`${h%12||12}:${String(m).padStart(2,"0")} ${p}`},s=a=>String(a??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;");o.innerHTML=n.map(a=>{const h=a.user_id&&d&&a.user_id.toString()===d.toString(),m=!!(a.can_view&&a.show_url),p=m?"a":"div",A=m?`href="${s(a.show_url)}"`:"",B=m?"cursor-pointer hover:shadow-xl hover:scale-[1.02] hover:border-[#62a9b6] transition-all duration-200":"cursor-default",v=[];a.campus_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.108 7.5-11.25a7.5 7.5 0 10-15 0C4.5 16.892 12 21 12 21z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Sede: ${s(a.campus_name)}
                    </span>
                `),a.dependency_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#cc5e50] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        ${s(a.dependency_name)}
                    </span>
                `),a.area_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        ${s(a.area_name)}
                    </span>
                `),y&&a.creator_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#e2a542] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Creado por: ${s(a.creator_name)}
                    </span>
                `);const H=v.length>0?`<div class="flex flex-wrap gap-2 mb-3">${v.join("")}</div>`:"";return`
                <${p} ${A} class="block p-4 mb-4 rounded-2xl border border-transparent bg-white dark:bg-zinc-800 shadow-lg ${B}">
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-start mb-2">
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg leading-snug text-gray-900 dark:text-white">${s(a.title)}</h3>
                            ${h?`
                                <div class="mt-1 text-xs font-semibold px-2 py-1 w-fit rounded-md text-gray-900 bg-zinc-50 dark:bg-zinc-900 dark:text-white border border-gray-200 dark:border-zinc-800">
                                    Tu evento
                                </div>
                            `:""}
                            ${!h&&a.is_dependency_event?`
                                <div class="mt-1 text-xs font-semibold px-2 py-1 w-fit rounded-md bg-green-900 text-white">
                                    Tu dependencia
                                </div>
                            `:""}
                        </div>

                        ${m?`
                            <div class="shrink-0 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#62a9b6]/10 text-gray-900 dark:text-white border border-[#62a9b6]/30">
                                <span class="text-xs font-medium">Ver detalles</span>
                                <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        `:""}
                    </div>

                    ${H}

                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        ${a.description?s(a.description):'<em class="text-gray-400">Sin descripción</em>'}
                    </p>

                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">🕗 ${i(a.start_time)}</div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">📌 ${s(a.location??"Ubicación no definida")}</div>
                </${p}>
            `}).join("")}else o.innerHTML='<p class="text-gray-600 dark:text-gray-400">No hay eventos registrados</p>';l.textContent=`${n.length} evento${n.length!==1?"s":""}`}function N(){const e=document.getElementById("calendarModal");e.classList.remove("flex"),e.classList.add("hidden")}window.closeModal=N;function q(){const e=new Date,n=new Date(e.getTime()-e.getTimezoneOffset()*6e4).toISOString().split("T")[0];return new Date(n)}let u=null,E=!1,C=!1,k=null,S=null,f=null,D=null,T=!1;function R(e){const[n,r,t]=e.split("-").map(Number);return new Date(n,r-1,t)}function F(){const e=$();return{year:e.year,semester:e.semester}}function V(){T||(document.querySelectorAll("[data-calendar-nav]").forEach(e=>{e.addEventListener("click",()=>{const n=e.dataset.calendarNav,r=D?.[n];r?.has_events&&I(null,{year:r.year,semester:r.semester})})}),T=!0)}function Y(e,n){const r=document.querySelector("[data-calendar-period-label]");r&&e?.label&&(r.textContent=e.label),document.querySelectorAll("[data-calendar-nav]").forEach(t=>{const o=t.dataset.calendarNav,l=n?.[o],c=!l?.has_events;t.disabled=c,t.title=l?.label??(o==="previous"?"Semestre anterior":"Semestre siguiente"),t.setAttribute("aria-disabled",c?"true":"false")})}async function I(e=null,n=null){if(C){k=e,S=n,console.log("📅 Calendar already painting, skipping...");return}if(!document.getElementById("cal-heatmap")){console.log("📅 Calendar container not found");return}C=!0,console.log("📅 Starting calendar paint...");try{if(u){try{u.destroy()}catch(d){console.warn("Error destroying calendar:",d)}u=null}z();const t=e??document.documentElement.classList.contains("dark");console.log(`📅 Painting calendar: isDarkTheme=${t}`),V(),n?f=n:f??=F();const o=await P(f),l=o.events??[];f={year:o.period?.year??f.year,semester:o.period?.semester??f.semester},D=o.navigation,Y(o.period,D);const c=j(),b=await L(f);console.log(b);const M=b.map(d=>new Date(d.date)),x=o.period??$(),w=q();u=new CalHeatmap,await u.paint({domain:{type:"month",gutter:c.gutter,padding:[5,5,5,5],dynamicDimension:!0,sort:"asc",label:{position:"top"}},subDomain:{type:"xDay",width:c.cellSize,height:c.cellSize,gutter:c.gutter,radius:Math.max(c.cellSize*.1,2),label:"D",color:(d,g,y)=>{const i=new Date(d),s=M.some(a=>a.getFullYear()===i.getFullYear()&&a.getMonth()===i.getMonth()&&a.getDate()===i.getDate())||i.getTime()===w.getTime();return g>0||s||t?"white":"black"}},data:{source:l,type:"json",x:"date",y:"count"},date:{start:x.start?R(x.start):x.startDate,highlight:[...M,w],locale:"es",timezone:"America/Bogota"},range:x.range,theme:t?"dark":"light",animationDuration:1e3,itemSelector:"#cal-heatmap",scale:{color:{type:"threshold",domain:[1,3,5,10],range:t?["#03090f","#62a9b6","#cc5e50","#e2a542","#f2e9e4"]:["#03090f","#62a9b6","#cc5e50","#e2a542","#f2e9e4"]}}}),setTimeout(()=>{const g=document.getElementById("cal-heatmap").querySelectorAll("rect.highlight");console.log("🎯 Rectángulos highlight encontrados:",g.length);const y=w;y.setHours(0,0,0,0);let i=!1;g.forEach((s,a)=>{const h=s.__data__;if(!h||typeof h.t!="number")return;const m=new Date(h.t),p=new Date(m.getFullYear(),m.getMonth(),m.getDate());console.log(`Rect #${a} -> rectDate local: ${p.toISOString()}`),p.getTime()===y.getTime()&&(s.classList.add("today-highlight"),console.log("✅ Día actual encontrado y marcado en rect #"+a),i=!0)}),i||console.warn("⚠️ No se encontró la celda del día de hoy entre los highlights.")},150),u.on("click",async(d,g,y)=>{const i=new Date(g).toISOString().split("T")[0];try{const a=await(await fetch(`/api/events/${i}`)).json();O(i,a)}catch(s){console.error("Error al obtener eventos:",s)}}),E=!0,console.log("📅 Calendar painted successfully")}catch(t){console.error("📅 Error painting calendar:",t),z(),u=null,E=!1}finally{if(C=!1,k!==null||S!==null){const t=k,o=S;k=null,S=null,setTimeout(()=>I(t,o),0)}}}function W(){if(u){try{u.destroy()}catch{}u=null}}export{W as destroyCalendar,I as paintCalendar};
