import { useEffect } from 'react';

export const CAMPUS_CHANGED_EVENT = 'statistics:campus-changed';

export function useCampusRefresh(callback) {
  useEffect(() => {
    window.addEventListener(CAMPUS_CHANGED_EVENT, callback);

    return () => window.removeEventListener(CAMPUS_CHANGED_EVENT, callback);
  }, [callback]);
}
