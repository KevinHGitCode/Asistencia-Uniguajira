// import './calendar.js';
const cal = new CalHeatmap();
cal.paint({
    domain: {
        type: "month",
        gutter: 4,
        padding: [1, 1, 1, 1],
        dynamicDimension: true,
        sort: 'asc',
        label: {
        position: 'top'
        },
    },
    subDomain: {
        type: "day",
        width: 30,
        height: 30,
        gutter: 5,
        date: new Date()
    },
    range: 6,
    theme: 'dark'
});


