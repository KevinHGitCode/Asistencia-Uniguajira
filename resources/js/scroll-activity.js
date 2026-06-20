/**
 * Actividad de scroll.
 *
 * Añade la clase `.is-scrolling` al elemento que se está desplazando para que
 * su scrollbar (definido en resources/css/scrollbars.css) solo resalte mientras
 * hay movimiento, y se desvanezca al detenerse.
 *
 * Funciona con el scroll de la página y con cualquier contenedor interno
 * (modales, tablas, listas de los selectores, etc.).
 */
const idleTimers = new WeakMap();
const IDLE_MS = 650;

function flagScrolling(event) {
    let el = event.target;

    // El scroll de la página llega con target = document → usamos el <html>.
    if (el === document || el === window || !el || el.nodeType !== 1) {
        el = document.scrollingElement || document.documentElement;
    }
    if (!el || !el.classList) return;

    el.classList.add('is-scrolling');

    const prev = idleTimers.get(el);
    if (prev) clearTimeout(prev);
    idleTimers.set(el, setTimeout(() => el.classList.remove('is-scrolling'), IDLE_MS));
}

// `capture: true` porque el evento scroll no burbujea: así detectamos el
// desplazamiento de cualquier contenedor, no solo el de la página.
document.addEventListener('scroll', flagScrolling, { capture: true, passive: true });
