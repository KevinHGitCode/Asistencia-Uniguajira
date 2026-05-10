import { useState, useEffect } from 'react';

/**
 * Hook que retorna `true` solo después del primer paint del componente.
 *
 * Recharts calcula las dimensiones del contenedor síncronamente en el
 * primer render; si el DOM aún no ha terminado de pintar (frecuente con
 * Livewire SPA), obtiene valores negativos y arroja el warning
 * "width(-1) and height(-1) should be greater than 0".
 *
 * Usando este hook se difiere el render de la gráfica hasta que el
 * layout del navegador ya esté resuelto.
 */
export function useMounted() {
  const [mounted, setMounted] = useState(false);
  useEffect(() => { setMounted(true); }, []);
  return mounted;
}
