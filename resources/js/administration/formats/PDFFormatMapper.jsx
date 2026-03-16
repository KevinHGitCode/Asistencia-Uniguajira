import { useState, useRef, useEffect, useCallback } from "react";

const HEADER_FIELDS = [
  { id: "dependency", label: "Dependencia" },
  { id: "area", label: "Ãrea" },
  { id: "title", label: "TÃ­tulo" },
  { id: "date_day", label: "DÃ­a" },
  { id: "date_month", label: "Mes" },
  { id: "date_year", label: "AÃ±o" },
  { id: "responsible", label: "Responsable" },
];

const COLUMN_FIELDS = [
  { id: "number", label: "NÂ°", defaults: { w: 12, align: "C", fontSize: 12, limit: 5 } },
  { id: "name", label: "Nombre", defaults: { w: 55, align: "L", limit: 30, fontSize: 12 } },
  { id: "identification", label: "IdentificaciÃ³n", defaults: { w: 25, align: "C", limit: 15, fontSize: 12 } },
  { id: "role", label: "Cargo", defaults: { w: 28, align: "L", limit: 13, fontSize: 12 }, canBeCheckbox: true },
  { id: "program", label: "Programa", defaults: { w: 35, align: "L", limit: 25, fontSize: 10 } },
  { id: "email", label: "Correo", defaults: { w: 34, align: "L", limit: 30, fontSize: 12 } },
  { id: "phone", label: "TelÃ©fono", defaults: { w: 25, align: "L", limit: 15, fontSize: 12 } },
  { id: "city", label: "Ciudad", defaults: { w: 28, align: "L", limit: 18, fontSize: 12 } },
  { id: "neighborhood", label: "Barrio", defaults: { w: 28, align: "L", limit: 18, fontSize: 12 } },
  { id: "address", label: "Direccion", defaults: { w: 40, align: "L", limit: 30, fontSize: 11 } },
  { id: "time", label: "Hora", defaults: { w: 20, align: "C", fontSize: 12, limit: 10 } },
];

const CHECKBOX_FIELDS = [
  { id: "gender", label: "Sexo", options: ["Femenino", "Masculino"] },
  {
    id: "priority_group", label: "Grupo Priorizado",
    options: ["IndÃ­gena", "Afrodescendiente", "Discapacitado", "VÃ­ctima de Conflicto Armado", "Comunidad LGTBQ+", "Habitante de Frontera"],
  },
  {
    id: "role_checkbox", label: "Cargo (casillas)", configId: "role",
    options: ["Estudiante", "Docente", "Administrativo", "Graduado", "Comunidad Externa"],
  },
];

const COLORS = {
  header: { bg: "rgba(34,197,94,0.85)", bgSelected: "#16a34a", badge: "#22c55e" },
  column: { bg: "rgba(59,130,246,0.75)", bgSelected: "#2563eb", badge: "#3b82f6" },
  checkbox: { bg: "rgba(245,158,11,0.8)", bgSelected: "#d97706", badge: "#f59e0b" },
};

// Estimate how many characters fit in a given width (mm) at a given font size
function estimateLimit(wMm, fontSize) {
  const avgCharWidthMm = fontSize * 0.17;
  return Math.max(3, Math.floor(wMm / avgCharWidthMm));
}

function isEditableTarget(target) {
  if (!target) return false;
  const tag = target.tagName?.toLowerCase();
  return tag === "input" || tag === "textarea" || tag === "select" || target.isContentEditable;
}

export default function PDFFormatMapper({ formatId, formatSlug, formatName, formatFile, formatMapping, saveUrl, pdfUrl, csrfToken } = {}) {
  const canvasRef = useRef(null);
  const containerRef = useRef(null);
  const [pdfReady, setPdfReady] = useState(false);
  const [pdfLoading, setPdfLoading] = useState(false);
  const [pageInfo, setPageInfo] = useState({ wMm: 297, hMm: 210, wPx: 800, hPx: 566 });
  const [scale, setScale] = useState(1.2);
  const [slug, setSlug] = useState(formatSlug || "");
  const [placedHeaders, setPlacedHeaders] = useState({});
  const [placedColumns, setPlacedColumns] = useState({});
  const [placedCheckboxes, setPlacedCheckboxes] = useState({});
  const [selectedField, setSelectedField] = useState(null);
  const [interaction, setInteraction] = useState(null);
  const [showExport, setShowExport] = useState(false);
  const [tableConfig, setTableConfig] = useState({ startY: 60, rowHeight: 8, maxRows: 16 });
  const [dateFormat, setDateFormat] = useState({ day: "d", month: "m", year: "Y" });
  const [timeFormat, setTimeFormat] = useState("h:i A");
  const [showGuides, setShowGuides] = useState(true);
  const [fileName, setFileName] = useState(formatFile || "");
  const [saving, setSaving] = useState(false);
  const [showPanel, setShowPanel] = useState(true);

  useEffect(() => {
    if (window.pdfjsLib) { setPdfReady(true); return; }
    const s = document.createElement("script");
    s.src = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js";
    s.onload = () => {
      window.pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
      setPdfReady(true);
    };
    document.head.appendChild(s);
  }, []);

  useEffect(() => {
    if (!pdfUrl || !pdfReady) return;
    const loadPdf = async () => {
      setPdfLoading(true);
      try {
        const res = await fetch(pdfUrl);
        const buf = await res.arrayBuffer();
        const doc = await window.pdfjsLib.getDocument({ data: buf }).promise;
        const page = await doc.getPage(1);
        const vp = page.getViewport({ scale });
        setPageInfo({ wMm: (vp.width / scale) * 25.4 / 72, hMm: (vp.height / scale) * 25.4 / 72, wPx: vp.width, hPx: vp.height });
        const canvas = canvasRef.current;
        canvas.width = vp.width;
        canvas.height = vp.height;
        await page.render({ canvasContext: canvas.getContext("2d"), viewport: vp }).promise;
      } catch (err) { console.error("Error loading PDF:", err); }
      setPdfLoading(false);
    };
    loadPdf();
  }, [pdfReady, pdfUrl, scale]);

  useEffect(() => {
    if (!formatMapping || !pdfReady || !fileName) return;
    if (typeof formatMapping !== 'object' || Object.keys(formatMapping).length === 0) return;
    const cfg = formatMapping;
    if (cfg.startY || cfg.rowHeight || cfg.maxRows) setTableConfig({ startY: cfg.startY || 60, rowHeight: cfg.rowHeight || 8, maxRows: cfg.maxRows || 16 });
    if (cfg.date_format && typeof cfg.date_format === 'object') setDateFormat(cfg.date_format);
    if (cfg.time_format) setTimeFormat(cfg.time_format);
    const timer = setTimeout(() => {
      const m2p = (mm, axis) => Math.round(mm * (axis === "x" ? pageInfo.wPx / pageInfo.wMm : pageInfo.hPx / pageInfo.hMm));
      if (cfg.header) {
        const h = {};
        Object.entries(cfg.header).forEach(([id, pos]) => {
          if (HEADER_FIELDS.find(f => f.id === id)) {
              const wMm = pos.w || 40;
              h[id] = { xPx: m2p(pos.x, "x"), yPx: m2p(pos.y, "y"), fontSize: pos.fontSize || 12, wMm: wMm, limit: pos.limit || estimateLimit(wMm, pos.fontSize || 12), align: pos.align || "L" };
          }
        });
        setPlacedHeaders(h);
      }
      if (cfg.columns) {
        const cols = {}, cbs = {};
        Object.entries(cfg.columns).forEach(([id, col]) => {
          const isCb = !col.hasOwnProperty('x') && !col.hasOwnProperty('w');
          if (isCb) {
            let cbId = id;
            const cbField = CHECKBOX_FIELDS.find(f => f.configId === id || f.id === id);
            if (cbField) cbId = cbField.id;
            const opts = {};
            Object.entries(col).forEach(([optKey, optPos]) => {
              if (typeof optPos === 'object' && optPos.x !== undefined) opts[optKey] = { xPx: m2p(optPos.x, "x"), yPx: m2p((cfg.startY || 60) + (optPos.y_offset || 0), "y"), wMm: 10 };
            });
            if (Object.keys(opts).length > 0) cbs[cbId] = opts;
          } else if (COLUMN_FIELDS.find(f => f.id === id)) {
            cols[id] = { xPx: m2p(col.x, "x"), yPx: m2p(cfg.startY || 60, "y"), w: col.w || 30, h: col.h || 7, align: col.align || "L", limit: col.limit || 25, fontSize: col.fontSize || 12 };
          }
        });
        setPlacedColumns(cols);
        setPlacedCheckboxes(cbs);
      }
    }, 600);
    return () => clearTimeout(timer);
  }, [formatMapping, pdfReady, fileName, pageInfo.wPx]);

  const pxToMm = useCallback((px, axis) => Math.round(px * (axis === "x" ? pageInfo.wMm / pageInfo.wPx : pageInfo.hMm / pageInfo.hPx) * 100) / 100, [pageInfo]);
  const mmToPx = useCallback((mm, axis) => Math.round(mm * (axis === "x" ? pageInfo.wPx / pageInfo.wMm : pageInfo.hPx / pageInfo.hMm)), [pageInfo]);

  const handlePdfUpload = async (e) => {
    const file = e.target.files[0];
    if (!file || !pdfReady) return;
    setFileName(file.name);
    setPdfLoading(true);
    const buf = await file.arrayBuffer();
    const doc = await window.pdfjsLib.getDocument({ data: buf }).promise;
    const page = await doc.getPage(1);
    const vp = page.getViewport({ scale });
    setPageInfo({ wMm: (vp.width / scale) * 25.4 / 72, hMm: (vp.height / scale) * 25.4 / 72, wPx: vp.width, hPx: vp.height });
    const canvas = canvasRef.current;
    canvas.width = vp.width;
    canvas.height = vp.height;
    await page.render({ canvasContext: canvas.getContext("2d"), viewport: vp }).promise;
    setPdfLoading(false);
  };

  const handleMouseMove = useCallback((e) => {
    if (!interaction || !containerRef.current) return;
    const rect = containerRef.current.getBoundingClientRect();
    if (interaction.type === "drag") {
      const x = Math.max(0, Math.min(e.clientX - rect.left - interaction.offsetX, pageInfo.wPx - 20));
      const y = Math.max(0, Math.min(e.clientY - rect.top - interaction.offsetY, pageInfo.hPx - 10));
      if (interaction.fieldType === "header") setPlacedHeaders(p => ({ ...p, [interaction.id]: { ...p[interaction.id], xPx: x, yPx: y } }));
      else if (interaction.fieldType === "column") setPlacedColumns(p => ({ ...p, [interaction.id]: { ...p[interaction.id], xPx: x, yPx: y } }));
      else if (interaction.fieldType === "checkbox") {
        setPlacedCheckboxes(p => {
          const field = { ...p[interaction.id] };
          field[interaction.optionKey] = { ...(field[interaction.optionKey] || {}), xPx: x, yPx: y, wMm: field[interaction.optionKey]?.wMm || 10 };
          return { ...p, [interaction.id]: field };
        });
      }
    } else if (interaction.type === "resize") {
      const x = e.clientX - rect.left;
      if (interaction.fieldType === "column") {
        const col = placedColumns[interaction.id];
        if (col) {
          const newWMm = Math.round(pxToMm(Math.max(20, x - col.xPx), "x") * 10) / 10;
          const newLimit = estimateLimit(newWMm, col.fontSize || 12);
          setPlacedColumns(p => ({ ...p, [interaction.id]: { ...p[interaction.id], w: newWMm, limit: newLimit } }));
        }
      } else if (interaction.fieldType === "header") {
        const h = placedHeaders[interaction.id];
        if (h) {
            const newWMm = Math.round(pxToMm(Math.max(20, x - h.xPx), "x") * 10) / 10;
            const newLimit = estimateLimit(newWMm, h.fontSize || 12);
            setPlacedHeaders(p => ({ ...p, [interaction.id]: { ...p[interaction.id], wMm: newWMm, limit: newLimit } }));
        }
      } else if (interaction.fieldType === "checkbox") {
        const opts = placedCheckboxes[interaction.id];
        const opt = opts?.[interaction.optionKey];
        if (opt) {
            const newWMm = Math.round(pxToMm(Math.max(10, x - opt.xPx), "x") * 10) / 10;
            const newLimit = estimateLimit(newWMm, 12);
            setPlacedCheckboxes(p => {
              const field = { ...p[interaction.id] };
              field[interaction.optionKey] = { ...field[interaction.optionKey], wMm: newWMm, limit: newLimit };
              return { ...p, [interaction.id]: field };
          });
        }
      }
    }
  }, [interaction, pageInfo, placedColumns, placedHeaders, placedCheckboxes, pxToMm]);

  const handleMouseUp = useCallback(() => setInteraction(null), []);

  useEffect(() => {
    if (interaction) {
      window.addEventListener("mousemove", handleMouseMove);
      window.addEventListener("mouseup", handleMouseUp);
      return () => { window.removeEventListener("mousemove", handleMouseMove); window.removeEventListener("mouseup", handleMouseUp); };
    }
  }, [interaction, handleMouseMove, handleMouseUp]);

  const moveSelectedField = useCallback((dx, dy) => {
    if (!selectedField) return;
    const clampX = (x) => Math.max(0, Math.min(x, pageInfo.wPx - 20));
    const clampY = (y) => Math.max(0, Math.min(y, pageInfo.hPx - 10));

    if (selectedField.type === "header") {
      setPlacedHeaders(p => {
        const cur = p[selectedField.id];
        if (!cur) return p;
        return { ...p, [selectedField.id]: { ...cur, xPx: clampX(cur.xPx + dx), yPx: clampY(cur.yPx + dy) } };
      });
    } else if (selectedField.type === "column") {
      setPlacedColumns(p => {
        const cur = p[selectedField.id];
        if (!cur) return p;
        return { ...p, [selectedField.id]: { ...cur, xPx: clampX(cur.xPx + dx), yPx: clampY(cur.yPx + dy) } };
      });
    } else if (selectedField.type === "checkbox") {
      setPlacedCheckboxes(p => {
        const field = p[selectedField.id];
        const optKey = selectedField.optionKey;
        if (!field || !optKey || !field[optKey]) return p;
        const cur = field[optKey];
        return { ...p, [selectedField.id]: { ...field, [optKey]: { ...cur, xPx: clampX(cur.xPx + dx), yPx: clampY(cur.yPx + dy) } } };
      });
    }
  }, [selectedField, pageInfo.wPx, pageInfo.hPx]);

  useEffect(() => {
    if (!selectedField) return;
    const onKeyDown = (e) => {
      if (isEditableTarget(e.target)) return;
      let dx = 0;
      let dy = 0;
      if (e.key === "ArrowUp") dy = -1;
      else if (e.key === "ArrowDown") dy = 1;
      else if (e.key === "ArrowLeft") dx = -1;
      else if (e.key === "ArrowRight") dx = 1;
      else return;

      e.preventDefault();
      const step = e.shiftKey ? 5 : 1;
      moveSelectedField(dx * step, dy * step);
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [selectedField, moveSelectedField]);

  const addHeaderField = (id) => {
      if (!placedHeaders[id]) setPlacedHeaders(p => ({
          ...p, [id]: { xPx: 100, yPx: 40, fontSize: 12, wMm: 40, limit: estimateLimit(40, 12), align: "L" }
      }));
  };
  const addColumnField = (id) => {
    if (placedColumns[id]) return;
    const def = COLUMN_FIELDS.find(f => f.id === id)?.defaults || {};
    setPlacedColumns(p => ({ ...p, [id]: { xPx: 100, yPx: mmToPx(tableConfig.startY, "y"), w: def.w || 30, h: 7, align: def.align || "L", limit: def.limit || 25, fontSize: def.fontSize || 12 } }));
  };
  const addCheckboxOption = (fieldId, optionKey) => {
      setPlacedCheckboxes(p => {
        const field = { ...p[fieldId] } || {};
        if (field[optionKey]) return p;
        field[optionKey] = { xPx: 200, yPx: mmToPx(tableConfig.startY, "y"), wMm: 10, align: 'C', limit: estimateLimit(10, 12) };
        return { ...p, [fieldId]: field };
      });
  };
  const removeField = (id, type, optionKey) => {
    if (type === "header") setPlacedHeaders(p => { const n = { ...p }; delete n[id]; return n; });
    else if (type === "column") setPlacedColumns(p => { const n = { ...p }; delete n[id]; return n; });
    else if (type === "checkbox") {
      setPlacedCheckboxes(p => {
        const field = { ...p[id] };
        delete field[optionKey];
        if (!Object.keys(field).length) { const n = { ...p }; delete n[id]; return n; }
        return { ...p, [id]: field };
      });
    }
    if (selectedField?.id === id) setSelectedField(null);
  };
  const updateFieldProp = (id, type, prop, value) => {
    if (type === "header") setPlacedHeaders(p => ({ ...p, [id]: { ...p[id], [prop]: value } }));
    else if (type === "column") {
      setPlacedColumns(p => {
        const updated = { ...p[id], [prop]: value };
        // Sync limit when width changes
        if (prop === "w") updated.limit = estimateLimit(value, updated.fontSize || 12);
        // Sync limit when fontSize changes
        if (prop === "fontSize") updated.limit = estimateLimit(updated.w || 30, value);
        return { ...p, [id]: updated };
      });
    }
  };

  // Scroll wheel: change fontSize and scale visually
  const handleWheel = useCallback((e, id, type, optionKey) => {
    e.preventDefault();
    e.stopPropagation();
    const delta = e.deltaY < 0 ? 1 : -1;
    if (type === "header") {
      setPlacedHeaders(p => {
          const cur = p[id]?.fontSize || 12;
          const newSize = Math.max(6, Math.min(24, cur + delta));
          const newLimit = estimateLimit(p[id]?.wMm || 40, newSize);
          return { ...p, [id]: { ...p[id], fontSize: newSize, limit: newLimit } };
      });
    } else if (type === "column") {
      setPlacedColumns(p => {
        const cur = p[id]?.fontSize || 12;
        const newSize = Math.max(6, Math.min(24, cur + delta));
        const newLimit = estimateLimit(p[id]?.w || 30, newSize);
        return { ...p, [id]: { ...p[id], fontSize: newSize, limit: newLimit } };
      });
    } else if (type === "checkbox" && optionKey) {
      // Checkboxes: scroll changes the visual size of the X marker
      setPlacedCheckboxes(p => {
        const field = { ...p[id] };
        const cur = field[optionKey]?.wMm || 10;
        field[optionKey] = { ...field[optionKey], wMm: Math.max(4, Math.min(30, cur + delta)) };
        return { ...p, [id]: field };
      });
    }
  }, []);

  const generateConfig = () => {
    const header = {};
    Object.entries(placedHeaders).forEach(([id, f]) => {
        header[id] = { x: pxToMm(f.xPx, "x"), y: pxToMm(f.yPx, "y") };
        if (f.fontSize && f.fontSize !== 12) header[id].fontSize = f.fontSize;
        if (f.wMm) header[id].w = f.wMm;
        if (f.limit) header[id].limit = f.limit;
        if (f.align && f.align !== "L") header[id].align = f.align;
    });
    const columns = {};
    Object.entries(placedColumns).forEach(([id, f]) => {
      columns[id] = { x: pxToMm(f.xPx, "x"), w: f.w, align: f.align };
      if (f.h && f.h !== 7.8) columns[id].h = f.h;
      if (f.limit) columns[id].limit = f.limit;
      if (f.fontSize && f.fontSize !== 12) columns[id].fontSize = f.fontSize;
    });
    Object.entries(placedCheckboxes).forEach(([fieldId, options]) => {
      const configKey = CHECKBOX_FIELDS.find(f => f.id === fieldId)?.configId || fieldId;
      columns[configKey] = {};
    Object.entries(options).forEach(([optKey, pos]) => {
          const yOff = pxToMm(pos.yPx - mmToPx(tableConfig.startY, "y"), "y");
          columns[configKey][optKey] = { x: pxToMm(pos.xPx, "x") };
          if (Math.abs(yOff) > 0.5) columns[configKey][optKey].y_offset = yOff;
          if (pos.align && pos.align !== 'C') columns[configKey][optKey].align = pos.align;
      });
    });
    return { file: fileName || "FORMATO.pdf", startY: tableConfig.startY, rowHeight: tableConfig.rowHeight, maxRows: tableConfig.maxRows, header, columns, date_format: dateFormat, time_format: timeFormat };
  };

  const handleSave = async () => {
    const cfg = generateConfig();
    setSaving(true);
    try {
      const res = await fetch(saveUrl, { method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken }, body: JSON.stringify({ mapping: cfg, slug }) });
      const data = await res.json();
      if (data.success) {
          window.location.href = "/administracion/formats";
      } else {
          alert("Error: " + (data.message || "No se pudo guardar"));
      }
    } catch (err) { alert("Error: " + err.message); }
    setSaving(false);
  };

  const configToPhp = (cfg) => {
    const pad = n => " ".repeat(n);
    const renderVal = (v, depth) => {
      if (typeof v === "number") return `${v}`;
      if (typeof v === "string") return `'${v}'`;
      if (typeof v === "object" && !Array.isArray(v)) {
        const entries = Object.entries(v);
        if (entries.length <= 4 && entries.every(([, val]) => typeof val !== "object"))
          return `[${entries.map(([k, val]) => `'${k}' => ${renderVal(val, depth)}`).join(", ")}]`;
        return `[\n${entries.map(([k, val]) => `${pad(depth + 4)}'${k}'${" ".repeat(Math.max(1, 18 - k.length))}=> ${renderVal(val, depth + 4)},`).join("\n")}\n${pad(depth)}]`;
      }
      return `${v}`;
    };
    return `'${slug || "nuevo"}' => ${renderVal(cfg, 4)},`;
  };

  const exportedConfig = showExport ? generateConfig() : null;
  const gridMinorX = mmToPx(5, "x");
  const gridMinorY = mmToPx(5, "y");
  const gridMajorX = mmToPx(10, "x");
  const gridMajorY = mmToPx(10, "y");
  const gridMinorStyle = {
    position: "absolute",
    inset: 0,
    pointerEvents: "none",
    backgroundImage: "linear-gradient(to right, rgba(59,130,246,0.18) 1px, transparent 1px), linear-gradient(to bottom, rgba(59,130,246,0.18) 1px, transparent 1px)",
    backgroundSize: `${gridMinorX}px ${gridMinorY}px`,
  };
  const gridMajorStyle = {
    position: "absolute",
    inset: 0,
    pointerEvents: "none",
    backgroundImage: "linear-gradient(to right, rgba(59,130,246,0.35) 1px, transparent 1px), linear-gradient(to bottom, rgba(59,130,246,0.35) 1px, transparent 1px)",
    backgroundSize: `${gridMajorX}px ${gridMajorY}px`,
  };
  const centerLineStyleX = {
    position: "absolute",
    top: 0,
    bottom: 0,
    left: Math.round(pageInfo.wPx / 2),
    width: 1,
    background: "rgba(239,68,68,0.6)",
    pointerEvents: "none",
  };
  const centerLineStyleY = {
    position: "absolute",
    left: 0,
    right: 0,
    top: Math.round(pageInfo.hPx / 2),
    height: 1,
    background: "rgba(239,68,68,0.6)",
    pointerEvents: "none",
  };
  const coordBadge = { fontSize: 7, opacity: 0.8, marginLeft: 3, fontFamily: "monospace", background: "rgba(0,0,0,0.3)", padding: "0 3px", borderRadius: 2 };
  const propBadge = { fontSize: 7, padding: "0 3px", borderRadius: 2, background: "rgba(0,0,0,0.35)", fontFamily: "monospace", color: "#fff" };
  const fieldBase = { position: "absolute", borderRadius: 4, cursor: "grab", userSelect: "none", display: "flex", alignItems: "center", transition: "box-shadow 0.12s" };
  const resizeHandleStyle = { position: "absolute", right: -4, top: 0, bottom: 0, width: 8, cursor: "ew-resize", borderRadius: "0 4px 4px 0", background: "rgba(255,255,255,0.1)", display: "flex", alignItems: "center", justifyContent: "center" };

  const ResizeHandle = ({ onMouseDown: onMd, visible }) => (
    <div onMouseDown={onMd} style={{ ...resizeHandleStyle, background: visible ? "rgba(255,255,255,0.3)" : "rgba(255,255,255,0.1)" }}
      onMouseEnter={e => e.currentTarget.style.background = "rgba(255,255,255,0.5)"}
      onMouseLeave={e => e.currentTarget.style.background = visible ? "rgba(255,255,255,0.3)" : "rgba(255,255,255,0.1)"}>
      <div style={{ width: 2, height: "60%", borderRadius: 1, background: "rgba(255,255,255,0.5)" }} />
    </div>
  );

  return (
    <div style={{ display: "flex", flexDirection: "column", height: "100%", fontFamily: "'Segoe UI', sans-serif", background: "#0f172a", color: "#e2e8f0" }}>
      {/* Top Bar */}
      <div style={{ display: "flex", alignItems: "center", gap: 8, padding: "7px 12px", background: "#1e293b", borderBottom: "1px solid #334155", flexShrink: 0, flexWrap: "wrap" }}>
        <svg width="18" height="18" fill="none" stroke="#f59e0b" strokeWidth="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        <span style={{ fontWeight: 700, fontSize: 13, color: "#f59e0b" }}>PDF Mapper</span>
        <div style={{ width: 1, height: 18, background: "#475569" }} />
        <span style={{ fontSize: 11, color: "#94a3b8" }}>Slug:</span>
        <input value={slug} onChange={e => setSlug(e.target.value)} style={{ padding: "2px 7px", borderRadius: 4, border: "1px solid #475569", background: "#0f172a", color: "#e2e8f0", fontSize: 11, width: 120, fontFamily: "monospace" }} />
        <div style={{ flex: 1 }} />
        <Btn onClick={() => setShowPanel(!showPanel)} bg="#475569">{showPanel ? "Ocultar Panel" : "Panel"}</Btn>
        <label style={{ padding: "3px 10px", borderRadius: 5, background: "#6366f1", color: "#fff", fontWeight: 600, fontSize: 11, cursor: "pointer" }}>
          {pdfLoading ? "..." : "Cambiar PDF"}<input type="file" accept=".pdf" onChange={handlePdfUpload} style={{ display: "none" }} disabled={!pdfReady} />
        </label>
        <Btn onClick={() => setShowExport(!showExport)} bg="#475569">{showExport ? "Ocultar PHP" : "Ver PHP"}</Btn>
        <Btn onClick={handleSave} bg="#22c55e" disabled={saving}>{saving ? "Guardando..." : "ðŸ’¾ Guardar"}</Btn>
      </div>

      <div style={{ display: "flex", flex: 1, overflow: "hidden" }}>
        {/* Side Panel */}
        {showPanel && (
          <div style={{ width: 230, background: "#1e293b", borderRight: "1px solid #334155", overflowY: "auto", flexShrink: 0, padding: 10, fontSize: 11 }}>
            <Section title="Tabla">
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 4 }}>
                {[["startY", "Inicio Y"], ["rowHeight", "Alt.Fila"], ["maxRows", "MÃ¡x"]].map(([k, l]) => (
                  <MiniInput key={k} label={l} type="number" step="0.5" value={tableConfig[k]} onChange={e => setTableConfig(p => ({ ...p, [k]: parseFloat(e.target.value) || 0 }))} />
                ))}
              </div>
              <label style={{ display: "flex", alignItems: "center", gap: 5, marginTop: 5, fontSize: 10, color: "#94a3b8", cursor: "pointer" }}>
                <input type="checkbox" checked={showGuides} onChange={e => setShowGuides(e.target.checked)} /> GuÃ­as (filas + rejilla)
              </label>
            </Section>
            <Section title="Fecha / Hora">
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr 1fr", gap: 4 }}>
                {[["day", "DÃ­a"], ["month", "Mes"], ["year", "AÃ±o"]].map(([k, l]) => (
                  <MiniInput key={k} label={l} value={dateFormat[k]} onChange={e => setDateFormat(p => ({ ...p, [k]: e.target.value }))} mono />
                ))}
              </div>
              <MiniInput label="Hora" value={timeFormat} onChange={e => setTimeFormat(e.target.value)} mono style={{ marginTop: 3 }} />
            </Section>
            <Section title="Encabezado" color={COLORS.header.badge}>
              <div style={{ display: "flex", flexWrap: "wrap", gap: 3 }}>
                {HEADER_FIELDS.map(f => <FieldBtn key={f.id} label={f.label} placed={!!placedHeaders[f.id]} color={COLORS.header.badge} onClick={() => addHeaderField(f.id)} />)}
              </div>
            </Section>
            <Section title="Columnas" color={COLORS.column.badge}>
              <div style={{ display: "flex", flexWrap: "wrap", gap: 3 }}>
                {COLUMN_FIELDS.map(f => <FieldBtn key={f.id} label={f.label} placed={!!placedColumns[f.id]} color={COLORS.column.badge} onClick={() => addColumnField(f.id)} />)}
              </div>
            </Section>
            <Section title="Casillas" color={COLORS.checkbox.badge}>
              {CHECKBOX_FIELDS.map(field => (
                <div key={field.id} style={{ marginBottom: 6 }}>
                  <div style={{ fontSize: 10, fontWeight: 600, color: "#cbd5e1", marginBottom: 2 }}>{field.label}</div>
                  <div style={{ display: "flex", flexWrap: "wrap", gap: 2 }}>
                    {field.options.map(opt => <FieldBtn key={opt} label={opt.length > 14 ? opt.substring(0, 14) + "â€¦" : opt} placed={!!placedCheckboxes[field.id]?.[opt]} color={COLORS.checkbox.badge} onClick={() => addCheckboxOption(field.id, opt)} small />)}
                  </div>
                </div>
              ))}
            </Section>

            {selectedField && (
              <div style={{ background: "#0f172a", borderRadius: 7, padding: 8, border: "1px solid #f59e0b", marginTop: 6 }}>
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 6 }}>
                  <span style={{ fontSize: 11, fontWeight: 700, color: "#f59e0b" }}>{selectedField.label}</span>
                  <button onClick={() => removeField(selectedField.id, selectedField.type, selectedField.optionKey)}
                    style={{ fontSize: 9, padding: "1px 5px", borderRadius: 3, background: "#ef4444", color: "#fff", border: "none", cursor: "pointer" }}>Quitar</button>
                </div>
                {selectedField.type === "column" && (
                  <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 3 }}>
                    {[["w", "Ancho(mm)"], ["h", "Alto(mm)"], ["fontSize", "Letra"], ["limit", "LÃ­mite"]].map(([k, l]) => (
                      <MiniInput key={k} label={l} type="number" step={k === "fontSize" ? 1 : 0.5} value={placedColumns[selectedField.id]?.[k] || ""} onChange={e => updateFieldProp(selectedField.id, "column", k, parseFloat(e.target.value) || 0)} />
                    ))}
                    <div style={{ gridColumn: "span 2" }}>
                      <label style={{ fontSize: 9, color: "#64748b" }}>AlineaciÃ³n</label>
                      <select value={placedColumns[selectedField.id]?.align || "L"} onChange={e => updateFieldProp(selectedField.id, "column", "align", e.target.value)}
                        style={{ width: "100%", padding: "2px 4px", borderRadius: 3, border: "1px solid #475569", background: "#1e293b", color: "#e2e8f0", fontSize: 10 }}>
                        <option value="L">Izquierda</option><option value="C">Centro</option><option value="R">Derecha</option>
                      </select>
                    </div>
                  </div>
                )}
                {selectedField.type === "header" && (
                  <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 3 }}>
                      <MiniInput label="Letra" type="number" value={placedHeaders[selectedField.id]?.fontSize || 12}
                          onChange={e => updateFieldProp(selectedField.id, "header", "fontSize", parseInt(e.target.value) || 12)} />
                      <MiniInput label="Ancho(mm)" type="number" step="0.5" value={placedHeaders[selectedField.id]?.wMm || 40}
                          onChange={e => {
                              const w = parseFloat(e.target.value) || 40;
                              const fs = placedHeaders[selectedField.id]?.fontSize || 12;
                              setPlacedHeaders(p => ({ ...p, [selectedField.id]: { ...p[selectedField.id], wMm: w, limit: estimateLimit(w, fs) } }));
                          }} />
                      <MiniInput label="LÃ­mite" type="number" value={placedHeaders[selectedField.id]?.limit || ''}
                          onChange={e => updateFieldProp(selectedField.id, "header", "limit", parseInt(e.target.value) || 0)} />
                      <div style={{ gridColumn: "span 2" }}>
                        <label style={{ fontSize: 9, color: "#64748b" }}>AlineaciÃ³n</label>
                        <select value={placedHeaders[selectedField.id]?.align || "L"} onChange={e => updateFieldProp(selectedField.id, "header", "align", e.target.value)}
                          style={{ width: "100%", padding: "2px 4px", borderRadius: 3, border: "1px solid #475569", background: "#1e293b", color: "#e2e8f0", fontSize: 10 }}>
                          <option value="L">Izquierda</option>
                          <option value="C">Centro</option>
                          <option value="R">Derecha</option>
                        </select>
                      </div>
                  </div>
                )}
                {selectedField.type === "checkbox" && (
                  <div>
                    <label style={{ fontSize: 9, color: "#64748b" }}>AlineaciÃ³n</label>
                    <select 
                      value={placedCheckboxes[selectedField.id]?.[selectedField.optionKey]?.align || "C"}
                      onChange={e => {
                        setPlacedCheckboxes(p => {
                          const field = { ...p[selectedField.id] };
                          field[selectedField.optionKey] = { ...field[selectedField.optionKey], align: e.target.value };
                          return { ...p, [selectedField.id]: field };
                        });
                      }}
                      style={{ width: "100%", padding: "2px 4px", borderRadius: 3, border: "1px solid #475569", background: "#1e293b", color: "#e2e8f0", fontSize: 10 }}>
                      <option value="L">Izquierda</option>
                      <option value="C">Centro</option>
                      <option value="R">Derecha</option>
                    </select>
                  </div>
                )}
              </div>
            )}

            <div style={{ marginTop: 10, padding: 6, borderRadius: 5, background: "#0f172a", border: "1px solid #334155", fontSize: 9, color: "#64748b", lineHeight: 1.5 }}>
              <strong style={{ color: "#94a3b8" }}>Controles:</strong><br/>
              â€¢ Arrastrar = mover campo<br/>
              â€¢ Flechas = mover seleccionado<br/>
              â€¢ Shift + flechas = movimiento rÃ¡pido<br/>
              â€¢ Borde derecho = ancho (y lÃ­mite)<br/>
              â€¢ Scroll (rueda) = tamaÃ±o letra<br/>
              â€¢ Click = ver propiedades
            </div>
          </div>
        )}

        {/* PDF Canvas */}
        <div style={{ flex: 1, overflow: "auto", background: "#0f172a", display: "flex", justifyContent: "center", alignItems: "flex-start", padding: 14 }} onClick={() => setSelectedField(null)}>
          <div ref={containerRef} style={{ position: "relative", width: pageInfo.wPx, height: pageInfo.hPx, flexShrink: 0 }}>
            <canvas ref={canvasRef} style={{ position: "absolute", top: 0, left: 0, borderRadius: 3, boxShadow: "0 4px 20px rgba(0,0,0,0.5)" }} />

            {/* ===== GRID GUIDES ===== */}
            {showGuides && fileName && (
              <>
                <div style={gridMinorStyle} />
                <div style={gridMajorStyle} />
                <div style={centerLineStyleX} />
                <div style={centerLineStyleY} />
              </>
            )}

            {/* ===== ROW GUIDES with labels ===== */}
            {showGuides && fileName && Array.from({ length: tableConfig.maxRows + 1 }, (_, i) => {
              const yPx = mmToPx(tableConfig.startY + i * tableConfig.rowHeight, "y");
              const isFirst = i === 0;
              const isLast = i === tableConfig.maxRows;
              return (
                <div key={`g-${i}`}>
                  {/* Horizontal line */}
                  <div style={{ position: "absolute", left: 0, top: yPx, width: "100%", height: 1, background: isFirst ? "#ef4444" : isLast ? "#f59e0b" : "#3b82f6", opacity: isFirst ? 0.95 : isLast ? 0.85 : 0.3, pointerEvents: "none" }} />

                  {/* Row number label on the right */}
                  {i < tableConfig.maxRows && (
                    <span style={{ position: "absolute", right: 4, top: yPx + 2, fontSize: 7, color: "#3b82f6", opacity: 0.6, fontFamily: "monospace", pointerEvents: "none" }}>
                      fila {i + 1}
                    </span>
                  )}

                  {/* startY label */}
                  {isFirst && (
                    <span style={{ position: "absolute", left: 4, top: yPx - 13, fontSize: 8, color: "#ef4444", fontWeight: 700, pointerEvents: "none" }}>
                      â–¼ startY: {tableConfig.startY}mm
                    </span>
                  )}

                  {/* Row height indicator between first two rows */}
                  {i === 1 && (
                    <div style={{ position: "absolute", left: 6, top: mmToPx(tableConfig.startY, "y"), height: mmToPx(tableConfig.rowHeight, "y"), display: "flex", alignItems: "center", pointerEvents: "none" }}>
                      <div style={{ width: 1, height: "100%", background: "#22c55e", opacity: 0.6 }} />
                      <span style={{ fontSize: 7, color: "#22c55e", marginLeft: 3, fontFamily: "monospace", whiteSpace: "nowrap" }}>â†• {tableConfig.rowHeight}mm</span>
                    </div>
                  )}

                  {/* Last row label */}
                  {isLast && (
                    <span style={{ position: "absolute", left: 4, top: yPx + 3, fontSize: 8, color: "#f59e0b", fontWeight: 700, pointerEvents: "none" }}>
                      â–² mÃ¡x: {tableConfig.maxRows} filas
                    </span>
                  )}
                </div>
              );
            })}

            {/* ===== HEADER FIELDS ===== */}
            {Object.entries(placedHeaders).map(([id, f]) => {
              const label = HEADER_FIELDS.find(h => h.id === id)?.label || id;
              const isSel = selectedField?.id === id && selectedField?.type === "header";
              const wPx = f.wMm ? mmToPx(f.wMm, "x") : undefined;
              return (
                <div key={`h-${id}`}
                  onMouseDown={e => { e.preventDefault(); e.stopPropagation(); const r = e.currentTarget.getBoundingClientRect(); setInteraction({ type: "drag", id, fieldType: "header", offsetX: e.clientX - r.left, offsetY: e.clientY - r.top }); }}
                  onClick={e => { e.stopPropagation(); setSelectedField({ id, type: "header", label, xPx: f.xPx, yPx: f.yPx }); }}
                  onWheel={e => handleWheel(e, id, "header")}
                  style={{ ...fieldBase, left: f.xPx, top: f.yPx, background: isSel ? COLORS.header.bgSelected : COLORS.header.bg, color: "#fff",
                    border: isSel ? "2px solid #fff" : "1px solid rgba(255,255,255,0.3)", boxShadow: isSel ? "0 0 12px rgba(34,197,94,0.5)" : "0 2px 6px rgba(0,0,0,0.3)",
                    padding: "2px 12px 2px 6px", fontSize: Math.min(11, f.fontSize || 12), fontWeight: 700, whiteSpace: "nowrap", minWidth: 36, width: wPx || "auto" }}>
                  {label}
                  <span style={coordBadge}>{pxToMm(f.xPx, "x")},{pxToMm(f.yPx, "y")}</span>
                  <span style={{ ...propBadge, marginLeft: 2 }}>{f.fontSize || 12}px</span>
                  <span style={{ ...propBadge, marginLeft: 2 }}>w:{f.wMm || 40}</span>
                  <span style={{ ...propBadge, marginLeft: 2 }}>lÃ­m:{f.limit || '-'}</span>
                  <span style={{ ...propBadge, marginLeft: 2 }}>{f.align || 'L'}</span>
                  <ResizeHandle visible={isSel} onMouseDown={e => { e.preventDefault(); e.stopPropagation(); setInteraction({ type: "resize", id, fieldType: "header" }); }} />
                </div>
              );
            })}

            {/* ===== COLUMN FIELDS ===== */}
            {Object.entries(placedColumns).map(([id, f]) => {
              const label = COLUMN_FIELDS.find(c => c.id === id)?.label || id;
              const isSel = selectedField?.id === id && selectedField?.type === "column";
              const wPx = mmToPx(f.w || 30, "x");
              const hPx = Math.max(14, mmToPx(f.h || 7, "y"));
              return (
                <div key={`c-${id}`}
                  onMouseDown={e => { e.preventDefault(); e.stopPropagation(); const r = e.currentTarget.getBoundingClientRect(); setInteraction({ type: "drag", id, fieldType: "column", offsetX: e.clientX - r.left, offsetY: e.clientY - r.top }); }}
                  onClick={e => { e.stopPropagation(); setSelectedField({ id, type: "column", label, xPx: f.xPx, yPx: f.yPx }); }}
                  onWheel={e => handleWheel(e, id, "column")}
                  style={{ ...fieldBase, left: f.xPx, top: f.yPx, width: wPx, height: hPx,
                    background: isSel ? COLORS.column.bgSelected : COLORS.column.bg, color: "#fff",
                    border: isSel ? "2px solid #fff" : "1px solid rgba(255,255,255,0.25)",
                    boxShadow: isSel ? "0 0 12px rgba(59,130,246,0.5)" : "0 2px 6px rgba(0,0,0,0.3)",
                    padding: "0 10px 0 4px", fontSize: Math.min(10, f.fontSize || 12), fontWeight: 600, overflow: "visible" }}>
                  <div style={{ display: "flex", alignItems: "center", gap: 2, width: "100%", overflow: "hidden" }}>
                    <span style={{ overflow: "hidden", textOverflow: "ellipsis", whiteSpace: "nowrap", flex: 1 }}>{label}</span>
                  </div>
                  <div style={{ position: "absolute", bottom: -13, left: 0, display: "flex", gap: 2, pointerEvents: "none" }}>
                    <span style={propBadge}>x:{pxToMm(f.xPx, "x")}</span>
                    <span style={propBadge}>w:{f.w}</span>
                    <span style={propBadge}>{f.fontSize || 12}px</span>
                    <span style={propBadge}>lÃ­m:{f.limit}</span>
                    <span style={propBadge}>{f.align}</span>
                  </div>
                  <ResizeHandle visible={isSel} onMouseDown={e => { e.preventDefault(); e.stopPropagation(); setInteraction({ type: "resize", id, fieldType: "column" }); }} />
                </div>
              );
            })}

            {/* ===== CHECKBOX FIELDS ===== */}
            {Object.entries(placedCheckboxes).map(([fieldId, options]) =>
              Object.entries(options).map(([optKey, pos]) => {
                const isSel = selectedField?.id === fieldId && selectedField?.optionKey === optKey;
                const wPx = pos.wMm ? mmToPx(pos.wMm, "x") : 40;
                return (
                  <div key={`cb-${fieldId}-${optKey}`}
                    onMouseDown={e => { e.preventDefault(); e.stopPropagation(); const r = e.currentTarget.getBoundingClientRect(); setInteraction({ type: "drag", id: fieldId, fieldType: "checkbox", optionKey: optKey, offsetX: e.clientX - r.left, offsetY: e.clientY - r.top }); }}
                    onClick={e => { e.stopPropagation(); setSelectedField({ id: fieldId, type: "checkbox", optionKey: optKey, label: optKey, xPx: pos.xPx, yPx: pos.yPx }); }}
                    onWheel={e => handleWheel(e, fieldId, "checkbox", optKey)}
                    style={{ ...fieldBase, left: pos.xPx, top: pos.yPx, width: wPx, padding: "1px 10px 1px 5px", fontSize: 8, fontWeight: 600,
                      background: isSel ? COLORS.checkbox.bgSelected : COLORS.checkbox.bg, color: "#fff",
                      border: isSel ? "2px solid #fff" : "1px solid rgba(255,255,255,0.3)", whiteSpace: "nowrap", overflow: "hidden" }}>
                    <span style={{ overflow: "hidden", textOverflow: "ellipsis", flex: 1 }}>âœ• {optKey.length > 12 ? optKey.substring(0, 12) + "â€¦" : optKey}</span>
                    <span style={coordBadge}>{pxToMm(pos.xPx, "x")}</span>
                    <span style={propBadge}>w:{pos.wMm || 10}</span>
                    <span style={propBadge}>lÃ­m:{pos.limit || '-'}</span>
                    <span style={propBadge}>{pos.align || 'C'}</span>
                    <ResizeHandle visible={isSel} onMouseDown={e => { e.preventDefault(); e.stopPropagation(); setInteraction({ type: "resize", id: fieldId, fieldType: "checkbox", optionKey: optKey }); }} />
                  </div>
                );
              })
            )}

            {!fileName && !pdfUrl && (
              <div style={{ position: "absolute", inset: 0, display: "flex", flexDirection: "column", alignItems: "center", justifyContent: "center", background: "#1e293b", borderRadius: 6, border: "2px dashed #475569" }}>
                <svg width="36" height="36" fill="none" stroke="#475569" strokeWidth="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                <p style={{ marginTop: 8, color: "#64748b", fontSize: 12 }}>Sube un PDF para comenzar</p>
              </div>
            )}
          </div>
        </div>
      </div>

      {showExport && exportedConfig && (
        <div style={{ background: "#1e293b", borderTop: "1px solid #334155", padding: 10, maxHeight: "30vh", overflowY: "auto" }}>
          <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 6 }}>
            <span style={{ fontSize: 12, fontWeight: 700, color: "#f59e0b" }}>PHP Config</span>
            <Btn onClick={() => navigator.clipboard.writeText(configToPhp(exportedConfig))} bg="#6366f1">Copiar</Btn>
          </div>
          <pre style={{ background: "#0f172a", padding: 10, borderRadius: 5, fontSize: 10, color: "#a5f3fc", overflow: "auto", fontFamily: "'Fira Code', monospace", lineHeight: 1.5, whiteSpace: "pre-wrap" }}>
            {configToPhp(exportedConfig)}
          </pre>
        </div>
      )}
    </div>
  );
}

function Section({ title, color, children }) {
  return (
    <div style={{ marginBottom: 10 }}>
      <h3 style={{ fontSize: 9, fontWeight: 700, textTransform: "uppercase", color: "#94a3b8", marginBottom: 5, letterSpacing: 0.7, display: "flex", alignItems: "center", gap: 4 }}>
        {color && <span style={{ display: "inline-block", width: 6, height: 6, borderRadius: 2, background: color }} />}{title}
      </h3>
      {children}
    </div>
  );
}

function MiniInput({ label, mono, style: extraStyle, ...props }) {
  return (
    <div style={extraStyle}>
      <label style={{ fontSize: 9, color: "#64748b", display: "block" }}>{label}</label>
      <input {...props} style={{ width: "100%", padding: "2px 4px", borderRadius: 3, border: "1px solid #475569", background: "#0f172a", color: "#e2e8f0", fontSize: 10, fontFamily: mono ? "monospace" : "inherit" }} />
    </div>
  );
}

function FieldBtn({ label, placed, color, onClick, small }) {
  return (
    <button onClick={onClick} disabled={placed}
      style={{ padding: small ? "1px 4px" : "2px 6px", borderRadius: 3, fontSize: small ? 8 : 10, fontWeight: 600, border: "none",
        cursor: placed ? "default" : "pointer", background: placed ? "#334155" : color, color: placed ? "#64748b" : "#fff", opacity: placed ? 0.5 : 1 }}>
      {placed ? "âœ“" : "+"} {label}
    </button>
  );
}

function Btn({ children, bg, ...props }) {
  return <button {...props} style={{ padding: "3px 10px", borderRadius: 5, background: bg || "#475569", color: "#fff", fontWeight: 600, fontSize: 11, border: "none", cursor: props.disabled ? "wait" : "pointer", opacity: props.disabled ? 0.6 : 1 }}>{children}</button>;
}
