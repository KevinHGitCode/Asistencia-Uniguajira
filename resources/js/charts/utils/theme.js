// utils/theme.js
export function isDarkMode() {
  return document.documentElement.classList.contains('dark');
}

export function getCommonOptions() {
  const dark = isDarkMode();
  return {
    tooltip: {
      trigger: 'item',
      backgroundColor: dark ? 'rgba(50,50,50,0.7)' : 'rgba(255,255,255,0.9)',
      textStyle: { color: dark ? '#fff' : '#333' },
      borderColor: dark ? '#555' : '#ddd'
    },
    textStyle: { color: dark ? '#fff' : '#333' },
    legend: { textStyle: { color: dark ? '#fff' : '#333' } }
  };
}

export function getEnhancedOptions() {
  const dark = isDarkMode();
  return {
    ...getCommonOptions(),
    toolbox: {
      feature: {
        saveAsImage: { title: 'Descargar', backgroundColor: dark ? '#1f2937' : '#fff' },
        restore: { title: 'Restaurar' },
        dataView: { title: 'Ver Datos', readOnly: false }
      },
      iconStyle: { borderColor: dark ? '#fff' : '#333' }
    },
    backgroundColor: dark ? '#1f2937' : '#ffffff'
  };
}
