import { useState, useEffect } from 'react';

/**
 * Devuelve true cuando el documento tiene la clase 'dark'.
 * Se actualiza reactivamente cada vez que Flux / Alpine cambia el tema.
 */
export function useTheme() {
  const [isDark, setIsDark] = useState(
    () => document.documentElement.classList.contains('dark'),
  );

  useEffect(() => {
    const observer = new MutationObserver(() => {
      setIsDark(document.documentElement.classList.contains('dark'));
    });

    observer.observe(document.documentElement, {
      attributes:      true,
      attributeFilter: ['class'],
    });

    return () => observer.disconnect();
  }, []);

  return isDark;
}
