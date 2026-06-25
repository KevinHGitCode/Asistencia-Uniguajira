function A(){const e=document.getElementById("cal-heatmap");if(!e)return console.log("📏 getResponsiveDimensions: contenedor no encontrado"),{cellSize:20,gutter:2,containerWidth:0,containerHeight:0};const n=e.offsetWidth,o=e.offsetHeight;let s,a;n>=2e3?(s=35,a=3,console.log("📏 Modo: Pantalla muy grande")):n>=1500?(s=Math.min(Math.max(n/55,20),34),a=Math.max(s*.1,2.5),console.log("📏 Modo: Pantalla grande")):n>=940?(s=Math.min(Math.max(n/52,14),26),a=Math.max(s*.1,2),console.log("📏 Modo: Pantalla mediana (>= 940px)")):(s=Math.min(n/18,30),a=Math.max(s*.12,1),console.log("📏 Modo: Pantalla pequeña"));const h={cellSize:Math.floor(s),gutter:Math.floor(a),containerWidth:n,containerHeight:o};return console.log("📏 Dimensiones calculadas:",h),h}function D(){const e=document.getElementById("cal-heatmap");e&&(e.innerHTML="",e.removeAttribute("style"))}async function T(){return await(await fetch("/api/eventos-json")).json()}async function E(){const e=await fetch("/api/mis-eventos-json");if(!e.ok)return console.error("Error al traer eventos",e.status),[];const n=await e.json();return console.log("🔑 Auth:",n.auth_id),n.eventos}const _=()=>{const e=new Date,n=e.getMonth(),o=e.getFullYear();let s,a;return n>=0&&n<=5?(s=new Date(o,0,1),a=6):(s=new Date(o,6,1),a=6),{startDate:s,range:a}};function B(e,n){window.selectedCalendarDate=e;const o=document.getElementById("calendarModal");o.classList.remove("hidden"),o.classList.add("flex"),window.dispatchEvent(new CustomEvent("calendar-modal-opened",{detail:{date:e}}));const s=document.getElementById("calendarModalTitle"),a=document.getElementById("calendarModalBody"),h=document.getElementById("eventCount"),[v,w,y]=e.split("-").map(Number),g=new Date(v,w-1,y).toLocaleDateString("es-CO",{weekday:"long",year:"numeric",month:"long",day:"numeric"});if(s.textContent=`Eventos para el ${g}`,n.length>0){const p=document.querySelector('meta[name="user-id"]')?.content,r=document.querySelector('meta[name="user-role"]')?.content,d=r==="admin"||r==="superadmin",i=t=>{if(!t)return"No definido";const[u,f]=t.split(":").map(Number),M=u>=12?"PM":"AM";return`${u%12||12}:${String(f).padStart(2,"0")} ${M}`},l=t=>String(t??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#039;");a.innerHTML=n.map(t=>{const u=t.user_id&&p&&t.user_id.toString()===p.toString(),f=!!(t.can_view&&t.show_url),M=f?"a":"div",C=f?`href="${l(t.show_url)}"`:"",S=f?"cursor-pointer hover:shadow-xl hover:scale-[1.02] hover:border-[#62a9b6] transition-all duration-200":"cursor-default",x=[];t.campus_name&&x.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7.5-4.108 7.5-11.25a7.5 7.5 0 10-15 0C4.5 16.892 12 21 12 21z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Sede: ${l(t.campus_name)}
                    </span>
                `),t.dependency_name&&x.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#cc5e50] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        ${l(t.dependency_name)}
                    </span>
                `),t.area_name&&x.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        ${l(t.area_name)}
                    </span>
                `),d&&t.creator_name&&x.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium bg-[#e2a542] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Creado por: ${l(t.creator_name)}
                    </span>
                `);const z=x.length>0?`<div class="flex flex-wrap gap-2 mb-3">${x.join("")}</div>`:"";return`
                <${M} ${C} class="block p-4 mb-4 rounded-2xl border border-transparent bg-white dark:bg-zinc-800 shadow-lg ${S}">
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-start mb-2">
                        <div class="min-w-0">
                            <h3 class="font-bold text-lg leading-snug text-gray-900 dark:text-white">${l(t.title)}</h3>
                            ${u?`
                                <div class="mt-1 text-xs font-semibold px-2 py-1 w-fit rounded-md text-gray-900 bg-zinc-50 dark:bg-zinc-900 dark:text-white border border-gray-200 dark:border-zinc-800">
                                    Tu evento
                                </div>
                            `:""}
                            ${!u&&t.is_dependency_event?`
                                <div class="mt-1 text-xs font-semibold px-2 py-1 w-fit rounded-md bg-green-900 text-white">
                                    Tu dependencia
                                </div>
                            `:""}
                        </div>

                        ${f?`
                            <div class="shrink-0 flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#62a9b6]/10 text-gray-900 dark:text-white border border-[#62a9b6]/30">
                                <span class="text-xs font-medium">Ver detalles</span>
                                <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        `:""}
                    </div>

                    ${z}

                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        ${t.description?l(t.description):'<em class="text-gray-400">Sin descripción</em>'}
                    </p>

                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">🕗 ${i(t.start_time)}</div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">📌 ${l(t.location??"Ubicación no definida")}</div>
                </${M}>
            `}).join("")}else a.innerHTML='<p class="text-gray-600 dark:text-gray-400">No hay eventos registrados</p>';h.textContent=`${n.length} evento${n.length!==1?"s":""}`}function H(){const e=document.getElementById("calendarModal");e.classList.remove("flex"),e.classList.add("hidden")}window.closeModal=H;function I(){const e=new Date,n=new Date(e.getTime()-e.getTimezoneOffset()*6e4).toISOString().split("T")[0];return new Date(n)}let c=null,$=!1,k=!1,b=null;async function j(e=null){if(k){b=e,console.log("📅 Calendar already painting, skipping...");return}if(!document.getElementById("cal-heatmap")){console.log("📅 Calendar container not found");return}k=!0,console.log("📅 Starting calendar paint...");try{if(c){try{c.destroy()}catch(m){console.warn("Error destroying calendar:",m)}c=null}D();const o=e??document.documentElement.classList.contains("dark");console.log(`📅 Painting calendar: isDarkTheme=${o}`);const s=await T(),a=A(),h=await E();console.log(h);const v=h.map(m=>new Date(m.date)),w=_(),y=I();c=new CalHeatmap,await c.paint({domain:{type:"month",gutter:a.gutter,padding:[5,5,5,5],dynamicDimension:!0,sort:"asc",label:{position:"top"}},subDomain:{type:"xDay",width:a.cellSize,height:a.cellSize,gutter:a.gutter,radius:Math.max(a.cellSize*.1,2),label:"D",color:(m,g,p)=>{const r=new Date(m),d=v.some(i=>i.getFullYear()===r.getFullYear()&&i.getMonth()===r.getMonth()&&i.getDate()===r.getDate())||r.getTime()===y.getTime();return g>0||d||o?"white":"black"}},data:{source:s,type:"json",x:"date",y:"count"},date:{start:w.startDate,highlight:[...v,y],locale:"es",timezone:"America/Bogota"},range:w.range,theme:o?"dark":"light",animationDuration:1e3,itemSelector:"#cal-heatmap",scale:{color:{type:"threshold",domain:[1,3,5,10],range:o?["#03090f","#62a9b6","#cc5e50","#e2a542","#f2e9e4"]:["#03090f","#62a9b6","#cc5e50","#e2a542","#f2e9e4"]}}}),setTimeout(()=>{const g=document.getElementById("cal-heatmap").querySelectorAll("rect.highlight");console.log("🎯 Rectángulos highlight encontrados:",g.length);const p=y;p.setHours(0,0,0,0);let r=!1;g.forEach((d,i)=>{const l=d.__data__;if(!l||typeof l.t!="number")return;const t=new Date(l.t),u=new Date(t.getFullYear(),t.getMonth(),t.getDate());console.log(`Rect #${i} -> rectDate local: ${u.toISOString()}`),u.getTime()===p.getTime()&&(d.classList.add("today-highlight"),console.log("✅ Día actual encontrado y marcado en rect #"+i),r=!0)}),r||console.warn("⚠️ No se encontró la celda del día de hoy entre los highlights.")},150),c.on("click",async(m,g,p)=>{const r=new Date(g).toISOString().split("T")[0];try{const i=await(await fetch(`/api/events/${r}`)).json();B(r,i)}catch(d){console.error("Error al obtener eventos:",d)}}),$=!0,console.log("📅 Calendar painted successfully")}catch(o){console.error("📅 Error painting calendar:",o),D(),c=null,$=!1}finally{if(k=!1,b!==null){const o=b;b=null,setTimeout(()=>j(o),0)}}}function L(){if(c){try{c.destroy()}catch{}c=null}}export{L as destroyCalendar,j as paintCalendar};
