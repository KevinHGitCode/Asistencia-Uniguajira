
const cal = new CalHeatmap();
// Detect theme from project (assuming you have a way to check, e.g., a class on body)
const isDarkTheme = document.documentElement.classList.contains('dark');


// función para obtener dimensiones responsivas
function getResponsiveSize() {
    const container = document.getElementById('cal-heatmap');
    if (!container) return { width: 20, height: 20, gutter: 2 };

    const containerWidth = container.offsetWidth;
    const baseSize = Math.floor(containerWidth / 40); // ajusta el divisor según densidad
    return {
        width: baseSize,
        height: baseSize,
        gutter: Math.floor(baseSize / 5),
    };
}

cal.paint({
    domain: {
        type: "month",
        gutter: 1,
        padding: [5, 5, 5, 5],
        dynamicDimension: true,
        sort: 'asc',
        label: {
            position: 'top'
        },
    },
    subDomain: {
        type: "xDay",
        width: 30,
        height: 30,
        gutter: 5,
        radius: 5,
        label: 'D',
        color: (t, v, backgroundColor) => {
            if (isDarkTheme) {
                return 'white';
            }
            return 'black';
        },
    },
    date: {
        start: new Date(),
        highlight: [
            new Date('2025-08-19'),
            new Date('2025-09-04'),
            new Date('2025-10-05'),
            new Date('2025-12-09'),
            new Date(new Date().toLocaleString('en-CO', { timeZone: 'America/Bogota' })) // TODO: hay que terminar de pulir esta fecha de hoy para que no haya conflicto de zonas horarias
        ],
        locale: 'es',
        timezone: 'America/Bogota'
        },    
        // range: 6,
    theme: isDarkTheme ? 'dark' : 'light',
    animationDuration: 2000
});

// Para luego hacer responsive
// resources/js/calendar.js
// document.addEventListener('DOMContentLoaded', function() {
//     const cal = new CalHeatmap();
    
//     // Detect theme from project
//     const isDarkTheme = document.documentElement.classList.contains('dark');
    
//     // Function to get responsive dimensions
//     function getResponsiveDimensions() {
//         const container = document.getElementById('cal-heatmap');
//         const containerWidth = container.offsetWidth;
//         const containerHeight = container.offsetHeight;
        
//         // Calculate responsive cell size based on container width
//         let cellSize = Math.min(Math.max(containerWidth / 45, 15), 35);
//         let gutter = Math.max(cellSize * 0.1, 2);
        
//         // Adjust for smaller screens
//         if (window.innerWidth < 768) {
//             cellSize = Math.min(containerWidth / 20, 25);
//             gutter = Math.max(cellSize * 0.1, 1);
//         }
        
//         return {
//             cellSize: Math.floor(cellSize),
//             gutter: Math.floor(gutter),
//             containerWidth,
//             containerHeight
//         };
//     }
    
//     // Function to paint/repaint calendar
//     function paintCalendar() {
//         const dimensions = getResponsiveDimensions();
        
//         cal.paint({
//             domain: {
//                 type: "month",
//                 gutter: dimensions.gutter,
//                 padding: [10, 10, 10, 10],
//                 dynamicDimension: true,
//                 sort: 'asc',
//                 label: {
//                     position: 'top',
//                     text: (timestamp) => {
//                         return new Intl.DateTimeFormat('es', { 
//                             month: 'long', 
//                             year: 'numeric' 
//                         }).format(timestamp);
//                     }
//                 }
//             },
//             subDomain: {
//                 type: "xDay",
//                 width: dimensions.cellSize,
//                 height: dimensions.cellSize,
//                 gutter: dimensions.gutter,
//                 radius: Math.max(dimensions.cellSize * 0.1, 2),
//                 label: 'D',
//                 color: (timestamp, value, backgroundColor) => {
//                     if (isDarkTheme) {
//                         return 'white';
//                     }
//                     return 'black';
//                 }
//             },
//             data: {
//                 source: [], // Aquí puedes agregar tus datos cuando implementes el backend
//                 type: 'json',
//                 x: 'date',
//                 y: 'count'
//             },
//             date: {
//                 start: new Date(),
//                 highlight: [
//                     new Date('2025-08-19'),
//                     new Date('2025-09-04'),
//                     new Date('2025-10-05'),
//                     new Date('2025-12-09'),
//                     new Date()
//                 ],
//                 locale: 'es',
//                 timezone: 'America/Bogota'
//             },
//             range: 6, // Mostrar 6 meses
//             theme: isDarkTheme ? 'dark' : 'light',
//             animationDuration: 1000,
//             itemSelector: '#cal-heatmap',
            
//             // Configuración responsiva adicional
//             scale: {
//                 color: {
//                     type: 'threshold',
//                     scheme: 'Blues',
//                     domain: [1, 3, 5, 10]
//                 }
//             }
//         });
//     }
    
//     // Initial paint
//     paintCalendar();
    
//     // Repaint on window resize with debounce
//     let resizeTimeout;
//     window.addEventListener('resize', function() {
//         clearTimeout(resizeTimeout);
//         resizeTimeout = setTimeout(() => {
//             // Destroy current instance and repaint
//             cal.destroy();
//             setTimeout(() => {
//                 paintCalendar();
//             }, 100);
//         }, 250);
//     });
    
//     // Repaint on zoom change (detect via resize event)
//     let lastZoom = window.devicePixelRatio;
//     setInterval(() => {
//         if (window.devicePixelRatio !== lastZoom) {
//             lastZoom = window.devicePixelRatio;
//             cal.destroy();
//             setTimeout(() => {
//                 paintCalendar();
//             }, 100);
//         }
//     }, 500);
    
//     // Handle theme changes
//     const observer = new MutationObserver(function(mutations) {
//         mutations.forEach(function(mutation) {
//             if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
//                 const newIsDarkTheme = document.documentElement.classList.contains('dark');
//                 if (newIsDarkTheme !== isDarkTheme) {
//                     cal.destroy();
//                     setTimeout(() => {
//                         paintCalendar();
//                     }, 100);
//                 }
//             }
//         });
//     });
    
//     observer.observe(document.documentElement, {
//         attributes: true,
//         attributeFilter: ['class']
//     });
// });

