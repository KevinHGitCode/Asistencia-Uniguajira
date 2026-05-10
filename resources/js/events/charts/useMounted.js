import { useState, useEffect } from 'react';

/**
 * Hook que retorna `true` solo después del primer paint del componente.
 * Evita el warning de Recharts "width(-1) and height(-1)" al diferir
 * el render hasta que el layout del DOM esté resuelto.
 */
export function useMounted() {
  const [mounted, setMounted] = useState(false);
  useEffect(() => { setMounted(true); }, []);
  return mounted;
}
