import CalHeatmap from 'cal-heatmap';
import 'cal-heatmap/cal-heatmap.css';

console.log("Calendar script loaded");

import CalHeatmap from 'cal-heatmap';
import 'cal-heatmap/cal-heatmap.css';

document.addEventListener('DOMContentLoaded', () => {
    if (!document.querySelector('#event-calendar')) return;

    const cal = new CalHeatmap();
    cal.paint({
        itemSelector: "#event-calendar", // <- siempre cadena
        domain: "month",
        subDomain: "day",
        data: "/api/event-calendar",
        start: new Date(new Date().setMonth(new Date().getMonth() - 5)),
        cellSize: 20,
        range: 6,
        legend: [1, 2, 4, 6],
    });
});

