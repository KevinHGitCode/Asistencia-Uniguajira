export function buildStatisticsQuery(filters = {}, eventIds = null) {
  const params = new URLSearchParams();

  if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
  if (filters.dateTo) params.append('dateTo', filters.dateTo);
  if (filters.allCampuses) params.append('allCampuses', '1');
  if (filters.onlyOwnEvents) params.append('onlyOwnEvents', '1');

  (filters.campusIds ?? []).forEach(id => params.append('campusIds[]', id));
  (filters.dependencyIds ?? []).forEach(id => params.append('dependencyIds[]', id));

  if (Array.isArray(eventIds)) {
    eventIds.forEach(id => params.append('eventIds[]', id));
  }

  return params.toString();
}
