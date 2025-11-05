import { renderProgramAttendancesBar } from './programAttendancesBar.js';
import { renderProgramParticipantsPie } from './programParticipantsPie.js';
import { renderEventsOverTime } from './eventsOverTime.js';
import { renderAttendancesOverTime } from './attendancesOverTime.js';

window.createGeneralCharts = () => {
  const charts = {
    programAttendancesBar: null,
    programParticipantsPie: null,
    eventsOverTime: null,
    attendancesOverTime: null
  };

  renderProgramAttendancesBar(charts);
  renderProgramParticipantsPie(charts);
  renderEventsOverTime(charts);
  renderAttendancesOverTime(charts);
};

window.createGeneralCharts = createGeneralCharts;

console.log('Cargado charts/index.js');