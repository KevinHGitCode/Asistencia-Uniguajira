function z(){const e=document.getElementById("cal-heatmap");if(!e)return console.log("📏 getResponsiveDimensions: contenedor no encontrado"),{cellSize:20,gutter:2,containerWidth:0,containerHeight:0};const n=e.offsetWidth,o=e.offsetHeight;let r,a;n>=2e3?(r=35,a=3,console.log("📏 Modo: Pantalla muy grande")):n>=1500?(r=Math.min(Math.max(n/55,20),34),a=Math.max(r*.1,2.5),console.log("📏 Modo: Pantalla grande")):n>=940?(r=Math.min(Math.max(n/52,14),26),a=Math.max(r*.1,2),console.log("📏 Modo: Pantalla mediana (>= 940px)")):(r=Math.min(n/18,30),a=Math.max(r*.12,1),console.log("📏 Modo: Pantalla pequeña"));const m={cellSize:Math.floor(r),gutter:Math.floor(a),containerWidth:n,containerHeight:o};return console.log("📏 Dimensiones calculadas:",m),m}function C(){const e=document.getElementById("cal-heatmap");e&&(e.innerHTML="",e.removeAttribute("style"))}async function A(){return await(await fetch("/api/eventos-json")).json()}async function I(){const e=await fetch("/api/mis-eventos-json");if(!e.ok)return console.error("Error al traer eventos",e.status),[];const n=await e.json();return console.log("🔑 Auth:",n.auth_id),n.eventos}const T=()=>{const e=new Date,n=e.getMonth(),o=e.getFullYear();let r,a;return n>=0&&n<=5?(r=new Date(o,0,1),a=6):(r=new Date(o,6,1),a=6),{startDate:r,range:a}};function H(e,n){window.selectedCalendarDate=e;const o=document.getElementById("calendarModal");o.classList.remove("hidden"),o.classList.add("flex"),window.dispatchEvent(new CustomEvent("calendar-modal-opened",{detail:{date:e}}));const r=document.getElementById("calendarModalTitle"),a=document.getElementById("calendarModalBody"),m=document.getElementById("eventCount");o.classList.remove("hidden"),o.classList.add("flex");const[x,w,p]=e.split("-").map(Number),d=new Date(x,w-1,p).toLocaleDateString("es-CO",{weekday:"long",year:"numeric",month:"long",day:"numeric"});if(r.textContent=`Eventos para el ${d}`,n.length>0){const u=document.querySelector('meta[name="user-dependency-id"]')?.content,s=document.querySelector('meta[name="user-id"]')?.content,l=document.querySelector('meta[name="user-role"]')?.content==="admin",f=t=>{if(!t)return"No definido";const[h,M]=t.split(":").map(Number),y=h>=12?"PM":"AM";return`${h%12||12}:${String(M).padStart(2,"0")} ${y}`};a.innerHTML=n.map(t=>{const h=t.user_id&&s&&t.user_id.toString()===s.toString(),M=t.dependency_id&&u&&t.dependency_id.toString()===u.toString(),y=l||h||M,D=y?"cursor-pointer hover:shadow-xl hover:scale-[1.02] transition-all duration-200":"cursor-default",E=y?`data-event-id="${t.id}"`:"",v=[];t.dependency_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                 bg-[#cc5e50] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        </svg>
                        ${t.dependency_name}
                    </span>
                `),t.area_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                 bg-[#62a9b6] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                        ${t.area_name}
                    </span>
                `),l&&t.creator_name&&v.push(`
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-xs font-medium
                                 bg-[#e2a542] text-white">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Creado por: ${t.creator_name}
                    </span>
                `);const $=v.length>0?`<div class="flex flex-wrap gap-2 mb-2">${v.join("")}</div>`:"";return`
                <div class="p-4 mb-4 rounded-2xl bg-white dark:bg-zinc-800 shadow-lg ${D}" ${E}>
                    
                    <!-- Encabezado: título y botón "Ver" -->
                    <div class="flex justify-between items-start mb-1">
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">${t.title}</h3>
                        ${y?`
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 
                                            text-gray-900 dark:text-white border border-gray-200 dark:border-zinc-800">
                                <span class="text-xs font-medium">Ver detalles</span>
                                <svg class="w-4 h-4 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" 
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" 
                                        clip-rule="evenodd"/>
                                </svg>
                            </div>
                        `:""}
                    </div>

                    <!-- "Tu evento" debajo del título -->
                    ${h?`
                        <div class="mb-2 text-xs font-semibold px-2 py-1 w-fit rounded-md 
                                  text-gray-900 bg-zinc-50 dark:bg-zinc-900
                                    dark:text-white border border-gray-200 dark:border-zinc-800">
                            Tu evento
                        </div>
                    `:""}

                    <!-- Badges: Dependencia, Área, Creador -->
                    ${$}

                    <!-- Descripción -->
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        ${t.description??'<em class="text-gray-400">Sin descripción</em>'}
                    </p>

                    <!-- Hora y ubicación -->
                    <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">🕗 ${f(t.start_time)}</div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">📌 ${t.location??"Ubicación no definida"}</div>
                </div>
            `}).join(""),a.querySelectorAll("[data-event-id]").forEach(t=>{t.addEventListener("click",()=>{const h=t.getAttribute("data-event-id");window.location.href=`/eventos/${h}`})})}else a.innerHTML='<p class="text-gray-600 dark:text-gray-400">No hay eventos registrados</p>';m.textContent=`${n.length} evento${n.length!==1?"s":""}`}function B(){const e=document.getElementById("calendarModal");e.classList.remove("flex"),e.classList.add("hidden")}window.closeModal=B;function _(){const e=new Date,n=new Date(e.getTime()-e.getTimezoneOffset()*6e4).toISOString().split("T")[0];return new Date(n)}let i=null,S=!1,k=!1;async function j(e=null){if(k){console.log("📅 Calendar already painting, skipping...");return}if(!document.getElementById("cal-heatmap")){console.log("📅 Calendar container not found");return}k=!0,console.log("📅 Starting calendar paint...");try{if(i){try{i.destroy()}catch(c){console.warn("Error destroying calendar:",c)}i=null}C();const o=e??document.documentElement.classList.contains("dark");console.log(`📅 Painting calendar: isDarkTheme=${o}`);const r=await A(),a=z(),m=await I();console.log(m);const x=m.map(c=>new Date(c.date)),w=T(),p=_();i=new CalHeatmap,await i.paint({domain:{type:"month",gutter:a.gutter,padding:[5,5,5,5],dynamicDimension:!0,sort:"asc",label:{position:"top"}},subDomain:{type:"xDay",width:a.cellSize,height:a.cellSize,gutter:a.gutter,radius:Math.max(a.cellSize*.1,2),label:"D",color:(c,d,u)=>{const s=new Date(c),g=x.some(l=>l.getFullYear()===s.getFullYear()&&l.getMonth()===s.getMonth()&&l.getDate()===s.getDate())||s.getTime()===p.getTime();return d>0||g||o?"white":"black"}},data:{source:r,type:"json",x:"date",y:"count"},date:{start:w.startDate,highlight:[...x,p],locale:"es",timezone:"America/Bogota"},range:w.range,theme:o?"dark":"light",animationDuration:1e3,itemSelector:"#cal-heatmap",scale:{color:{type:"threshold",domain:[1,3,5,10],range:o?["#03090f","#62a9b6","#cc5e50","#e2a542","#f2e9e4"]:["#03090f","#62a9b6","#cc5e50","#e2a542","#f2e9e4"]}}}),setTimeout(()=>{const d=document.getElementById("cal-heatmap").querySelectorAll("rect.highlight");console.log("🎯 Rectángulos highlight encontrados:",d.length);const u=p;u.setHours(0,0,0,0);let s=!1;d.forEach((g,l)=>{const f=g.__data__;if(!f||typeof f.t!="number")return;const b=new Date(f.t),t=new Date(b.getFullYear(),b.getMonth(),b.getDate());console.log(`Rect #${l} -> rectDate local: ${t.toISOString()}`),t.getTime()===u.getTime()&&(g.classList.add("today-highlight"),console.log("✅ Día actual encontrado y marcado en rect #"+l),s=!0)}),s||console.warn("⚠️ No se encontró la celda del día de hoy entre los highlights.")},150),i.on("click",async(c,d,u)=>{const s=new Date(d).toISOString().split("T")[0];try{const l=await(await fetch(`/api/events/${s}`)).json();H(s,l)}catch(g){console.error("Error al obtener eventos:",g)}}),S=!0,console.log("📅 Calendar painted successfully")}catch(o){console.error("📅 Error painting calendar:",o),C(),i=null,S=!1}finally{k=!1}}function L(){if(i){try{i.destroy()}catch{}i=null}}export{L as destroyCalendar,j as paintCalendar};
