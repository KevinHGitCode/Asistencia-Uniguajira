import { createRoot } from 'react-dom/client';
import PDFFormatMapper from './PDFFormatMapper.jsx';

const container = document.getElementById('pdf-mapper-root');

if (container) {
    const props = {
        formatId: container.dataset.formatId,
        formatSlug: container.dataset.formatSlug,
        formatName: container.dataset.formatName,
        formatFile: container.dataset.formatFile,
        formatMapping: container.dataset.formatMapping ? JSON.parse(container.dataset.formatMapping) : null,
        saveUrl: container.dataset.saveUrl,
        pdfUrl: container.dataset.pdfUrl,
        csrfToken: container.dataset.csrfToken,
    };

    const root = createRoot(container);
    root.render(<PDFFormatMapper {...props} />);
}