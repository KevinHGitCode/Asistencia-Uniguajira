import Alpine from 'alpinejs'
window.Alpine = Alpine

// registrar módulos ANTES de arrancar Alpine
import './administration/dependencies/dependencies-manager'
import './administration/areas/areas-manager'
import './handle-sidebar'
import './copy-link-events'

// arrancar Alpine al final
Alpine.start()
