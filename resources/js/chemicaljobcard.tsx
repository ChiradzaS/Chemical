import React, { useState, useEffect, useRef } from 'react';
import {
  ChevronDown, Loader2, Package, FlaskConical, Calculator, Pencil, Check, X,
  AlertTriangle,
} from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:         string;
    customersData:         { id: number; name: string }[];
    chemicalProductsData:  ChemicalProduct[];
    unitTypesData:         { id: number; name: string; value?: number }[];
    colourTypesData:       { id: number; name: string }[];
    viscosityData:         { id: number; name: string }[];
    activeIngredientsData: { id: number; name: string }[];
    fragranceData:         { id: number; name: string }[];
    bottleTypesData:       { id: number; name: string }[];
    containerSizesData:    { id: number; name: string; value?: number }[];
    processTypesData:      { id: number; name: string }[];
    chemicalTypesData:     { id: number; name: string }[];
    Swal?:                 any;
  }
}

interface ChemicalProduct {
  id:                    number;
  name:                  string;
  category:              string | null;
  brand:                 string | null;
  sku:                   string | null;
  formula_code:          string | null;
  stock_unit_id:         number | null;
  colour_id:             number | null;
  viscosity_id:          number | null;
  active_ingredient_id:  number | null;
  fragrance_id:          number | null;
  bag_type_id:           number | null;
  container_size_id:     number | null;
  batch_size_litres:     number | null;
  units_per_batch:       number | null;
  yield_percentage:      number | null;
  weight_per_unit_grams: number | null;   // stores kg despite the column name
  price:                 number | null;
  vat_applicable:        number;
  concentration:         number | null;
  dilution_ratio:        string | null;
}

interface FormulaOption {
  id:                number;
  code:              string;
  name:              string;
  density_kg_per_l?: number | string | null;
  base_batch_qty?:   number | string | null;
}

interface MaterialRow {
  id:             number;
  code:           string;
  name:           string;
  uom:            string;
  stock_on_hand:  number | string | null;
  cost_per_kg?:   number | string | null;
  allow_negative?: number | boolean;
}

interface FormulaItem {
  id:              number;
  raw_material_id: number;
  percentage:      number | string;
  uom:             string;
  is_balance:      number | boolean;
  sequence?:       number;
}

interface ProcessLine {
  processId:   string;
  processName: string;
  productId:   string;
  quantity:    string;  // exactly what the user typed
  unitId:      string;  // container size id
  checked:     boolean;
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL = `${API_BASE}/chemicaljobcardlist`;

const byId = (list: { id: number; name: string }[], id: any) =>
  list.find(x => String(x.id) === String(id))?.name ?? '';

/* SweetAlert when the library is on the page, plain dialogs when it isn't —
   a missing CDN script should not stop an operator saving a job card. */
const alertOk = (title: string, text?: string) =>
  window.Swal
    ? window.Swal.fire({ icon: 'success', title, text, confirmButtonColor: '#4f46e5' })
    : Promise.resolve(alert(text ? `${title}\n\n${text}` : title));

const alertWarn = (title: string, text?: string) =>
  window.Swal
    ? window.Swal.fire({ icon: 'warning', title, text, confirmButtonColor: '#4f46e5' })
    : Promise.resolve(alert(text ? `${title}\n\n${text}` : title));

const alertError = (title: string, text?: string) =>
  window.Swal
    ? window.Swal.fire({ icon: 'error', title, text, confirmButtonColor: '#4f46e5' })
    : Promise.resolve(alert(text ? `${title}\n\n${text}` : title));

const confirmAction = async (title: string, html: string, confirmText: string) => {
  if (!window.Swal) return confirm(`${title}\n\n${html.replace(/<[^>]+>/g, ' ')}`);
  const res = await window.Swal.fire({
    icon: 'warning',
    title,
    html,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: 'Go back',
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    reverseButtons: true,
  });
  return !!res.isConfirmed;
};

const numOrNull = (v: any): number | null => {
  if (v === null || v === undefined || v === '') return null;
  const n = Number(v);
  return Number.isFinite(n) ? n : null;
};

const ChemicalJobCardCreator: React.FC = () => {
  const customers         = window.customersData         || [];
  const chemicalProducts  = window.chemicalProductsData  || [];
  const colourTypes       = window.colourTypesData       || [];
  const viscosities       = window.viscosityData         || [];
  const activeIngredients = window.activeIngredientsData || [];
  const fragrances        = window.fragranceData         || [];
  const bottleTypes       = window.bottleTypesData       || [];
  const containerSizes    = window.containerSizesData    || [];
  const processTypes      = window.processTypesData      || [];

  const [loading,         setLoading]         = useState(false);
  const [saving,          setSaving]          = useState(false);
  const [productSearch,   setProductSearch]   = useState('');
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [selectedProduct, setSelectedProduct] = useState<ChemicalProduct | null>(null);
  const [processLines,    setProcessLines]    = useState<ProcessLine[]>([]);

  // SAVE only appears once the job has been calculated
  const [calculated,      setCalculated]      = useState(false);

  // ── Formula + material data for the right-hand panel ──
  const [formulas,     setFormulas]     = useState<FormulaOption[]>([]);
  const [materials,    setMaterials]    = useState<MaterialRow[]>([]);
  const [items,        setItems]        = useState<FormulaItem[]>([]);
  const [itemsLoading, setItemsLoading] = useState(false);
  const [itemsError,   setItemsError]   = useState<string | null>(null);

  // ── Batch / SKU editing ───────────────────────────────────────────────────
  const [batchEditable,   setBatchEditable]   = useState(false);
  const [batchDraft,      setBatchDraft]      = useState('');
  const [batchSaving,     setBatchSaving]     = useState(false);
  const batchRef = useRef<HTMLInputElement>(null);

  const [form, setForm] = useState({
    customerId:         '',
    productId:          '',
    quantity:           '',  // no of units — user types this, also saved on process lines
    containerSizeId:    '',  // package — auto-filled from product
    colourId:           '',
    viscosityId:        '',
    activeIngredientId: '',
    fragranceId:        '',
    bottleTypeId:       '',
    weightPerUnit:      '',  // kg per unit, from the product
    totalWeight:        '',  // qty × weightPerUnit — the batch size in kg
    notes:              '',
    barcode:            '',
    batchSku:           '',
    formulaCode:        '',
  });

  // ── Init process lines ────────────────────────────────────────────────────
  useEffect(() => {
    setProcessLines(
      processTypes.map(pt => ({
        processId:   String(pt.id),
        processName: pt.name,
        productId:   '',
        quantity:    '',
        unitId:      '',
        checked:     false,
      }))
    );
  }, []);

  // ── Formula + raw material lookups ────────────────────────────────────────
  useEffect(() => {
    Promise.all([
      axios.get(`${API_BASE}/formulas/list`),
      axios.get(`${API_BASE}/formulas/materials`),
    ])
      .then(([f, m]) => {
        setFormulas(Array.isArray(f.data) ? f.data : []);
        setMaterials(Array.isArray(m.data) ? m.data : []);
      })
      .catch(() => { setFormulas([]); setMaterials([]); });
  }, []);

  const pickedFormula   = formulas.find(f => f.code === form.formulaCode);
  const pickedFormulaId = pickedFormula?.id ?? null;

  useEffect(() => {
    if (!pickedFormulaId) { setItems([]); setItemsError(null); return; }

    let cancelled = false;
    setItemsLoading(true);
    setItemsError(null);

    const encodedData = encodeURIComponent(JSON.stringify({ id: pickedFormulaId }));
    axios.get(`${API_BASE}/formulas/show?data=${encodedData}`)
      .then(r => {
        if (cancelled) return;
        if (r.data?.status === 'error') throw new Error(r.data.message);
        setItems(Array.isArray(r.data?.items) ? r.data.items : []);
      })
      .catch(() => { if (!cancelled) { setItems([]); setItemsError('Could not load the formula'); } })
      .finally(() => { if (!cancelled) setItemsLoading(false); });

    // a fast second pick must not have its response overwrite the newer one
    return () => { cancelled = true; };
  }, [pickedFormulaId]);

  // ── Pre-fill from URL params (clone) ──────────────────────────────────────
  useEffect(() => {
    const urlParams = (window as any).urlParamsData || {};
    if (urlParams.customerId) setForm(f => ({ ...f, customerId: urlParams.customerId }));
    if (urlParams.productId) {
      const p = chemicalProducts.find(x => String(x.id) === String(urlParams.productId));
      if (p) handleProductSelect(p);
    }
  }, [chemicalProducts]);

  // ── Live batch weight ─────────────────────────────────────────────────────
  useEffect(() => {
    const qty    = parseFloat(form.quantity)      || 0;
    const weight = parseFloat(form.weightPerUnit) || 0;
    const total  = qty * weight;
    setForm(f => ({ ...f, totalWeight: total > 0 ? total.toFixed(3) : '' }));
  }, [form.quantity, form.weightPerUnit]);

  const generateBarcode = () => {
    const b = (Math.random().toString(36).substr(2, 9) + Date.now()).substr(0, 11);
    setForm(f => ({ ...f, barcode: b }));
  };

  const filteredProducts = chemicalProducts.filter(p =>
    p.name.toLowerCase().includes(productSearch.toLowerCase())
  );

  const handleProductSelect = (product: ChemicalProduct) => {
    setSelectedProduct(product);
    setProductSearch(product.name);
    setShowSuggestions(false);
    setCalculated(false);
    setBatchEditable(false);
    setBatchDraft('');
    setForm(f => ({
      ...f,
      productId:          String(product.id),
      containerSizeId:    String(product.container_size_id     ?? ''),
      colourId:           String(product.colour_id             ?? ''),
      viscosityId:        String(product.viscosity_id          ?? ''),
      activeIngredientId: String(product.active_ingredient_id  ?? ''),
      fragranceId:        String(product.fragrance_id          ?? ''),
      bottleTypeId:       String(product.bag_type_id           ?? ''),
      weightPerUnit:      String(product.weight_per_unit_grams ?? ''),
      batchSku:           String(product.sku                   ?? ''),
      formulaCode:        String(product.formula_code          ?? ''),
    }));
  };

  // ── Batch code — open the field ───────────────────────────────────────────
  const openBatchEdit = () => {
    if (!form.productId) { alertWarn('Select a product first'); return; }
    setBatchDraft(form.batchSku);
    setBatchEditable(true);
    setTimeout(() => batchRef.current?.select(), 0);
  };

  const cancelBatchEdit = () => {
    setBatchDraft('');
    setBatchEditable(false);
  };

  const saveBatchCode = async () => {
    const next = batchDraft.trim();

    if (!form.productId) { alertWarn('Select a product first');                                  return; }
    if (!next)           { alertWarn('Batch code cannot be blank', 'Enter a code or cancel.'); return; }
    if (next === form.batchSku) { cancelBatchEdit(); return; }

    setBatchSaving(true);
    try {
      const payload     = { id: form.productId, sku: next };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/chemicalproducts/updatebatch?data=${encodedData}`);

      const savedSku = response.data?.sku ?? next;

      setForm(f => ({ ...f, batchSku: savedSku }));
      setSelectedProduct(p => (p ? { ...p, sku: savedSku } : p));

      const local = chemicalProducts.find(p => String(p.id) === String(form.productId));
      if (local) local.sku = savedSku;

      setBatchEditable(false);
      setBatchDraft('');
    } catch (error: any) {
      const res    = error.response;
      const errors = res?.data?.errors;
      const msg    = errors ? Object.values(errors).flat().join('\n')
                            : res?.data?.message || 'Could not reach the server — batch code not changed.';
      alertError('Batch code not changed', msg);
    } finally {
      setBatchSaving(false);
    }
  };

  /* ── Material requirement ──
     Batch kg = units ordered × kg per unit. Each ingredient's issue quantity is
     its share of that, so the figures move with the order rather than with the
     formula's own 1000 kg reference batch. */
  const batchKg = parseFloat(form.totalWeight) || 0;

  const materialOf = (id: number) => materials.find(m => Number(m.id) === Number(id));

  const requiredKg = (it: FormulaItem) => batchKg * (numOrNull(it.percentage) ?? 0) / 100;

  const stockOf = (id: number) => numOrNull(materialOf(id)?.stock_on_hand) ?? 0;

  const shortfall = (it: FormulaItem) => {
    const need = requiredKg(it);
    const have = stockOf(it.raw_material_id);
    return need > have ? need - have : 0;
  };

  const shortLines = batchKg > 0 ? items.filter(it => shortfall(it) > 0) : [];

  const totalRequired = items.reduce((s, it) => s + requiredKg(it), 0);

  // ── Calculate ─────────────────────────────────────────────────────────────
  const calculate = () => {
    if (!form.productId)  { alertWarn('Select a product',  'Pick the product this job will make.'); return; }
    if (!form.customerId) { alertWarn('Select a customer', 'A job card has to belong to a customer.'); return; }
    if (!form.quantity)   { alertWarn('Enter a quantity',  'How many units is this job for?'); return; }
    if (batchEditable)    { alertWarn('Finish the batch code', 'Save or cancel the batch code before calculating.'); return; }

    if (!form.barcode) generateBarcode();

    const qty         = parseFloat(form.quantity)      || 0;
    const weight      = parseFloat(form.weightPerUnit) || 0;
    const totalWeight = qty * weight;

    setForm(f => ({ ...f, totalWeight: totalWeight.toFixed(3) }));

    setProcessLines(prev => prev.map(line => ({
      ...line,
      checked:   true,
      productId: form.productId,
      quantity:  qty.toString(),
      unitId:    form.containerSizeId,
    })));

    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      setCalculated(true);
    }, 800);
  };

  const handleProcessChange = (idx: number, field: keyof ProcessLine, value: string | boolean) => {
    setProcessLines(prev => prev.map((line, i) => i === idx ? { ...line, [field]: value } : line));
  };

  // ── Submit ────────────────────────────────────────────────────────────────
  const handleSubmit = async () => {
    if (!form.customerId) { alertWarn('Select a customer', 'A job card has to belong to a customer.'); return; }
    if (!form.productId)  { alertWarn('Select a product',  'Pick the product this job will make.');    return; }
    if (!form.quantity)   { alertWarn('Enter a quantity',  'How many units is this job for?');         return; }

    if (shortLines.length > 0) {
      const rows = shortLines
        .map(it => `<tr>
            <td style="text-align:left;padding:2px 8px">${materialOf(it.raw_material_id)?.name ?? `#${it.raw_material_id}`}</td>
            <td style="text-align:right;padding:2px 8px;font-weight:700">${shortfall(it).toFixed(3)} kg</td>
          </tr>`)
        .join('');

      const ok = await confirmAction(
        'Stock is short',
        `<p style="margin-bottom:8px">These materials do not have enough on hand:</p>
         <table style="margin:0 auto;font-size:14px">${rows}</table>`,
        'Save anyway',
      );
      if (!ok) return;
    }

    setSaving(true);
    try {
      /* Material lines are resolved here and saved with the job, not left as a
         pointer to the formula — editing the formula later must not rewrite
         what this batch was issued. */
      const payload = {
        jobCard: { ...form, formulaId: pickedFormulaId, batchKg },
        items: processLines.filter(l => l.checked).map(l => ({
          processId:   l.processId,
          processName: l.processName,
          productId:   l.productId,
          quantity:    l.quantity,
          unitId:      l.unitId,
        })),
        materials: items.map(it => ({
          rawMaterialId: it.raw_material_id,
          percentage:    numOrNull(it.percentage) ?? 0,
          requiredKg:    Number(requiredKg(it).toFixed(4)),
          uom:           it.uom ?? 'kg',
        })),
      };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      await axios.get(`${API_BASE}/chemicaljobcards/store?data=${encodedData}`);
      await alertOk('Job card saved', `${form.quantity} units of ${selectedProduct?.name ?? 'product'}.`);
      window.location.href = LIST_URL;
    } catch (error: any) {
      if (!error.response) {
        window.location.href = LIST_URL;
      } else {
        alertError('Job card not saved', error.response?.data?.message || 'The server rejected the request. Check the details and try again.');
        console.error(error);
      }
    } finally {
      setSaving(false);
    }
  };

  const sel = "appearance-none bg-white border-2 border-gray-300 rounded-lg px-3 py-2 pr-8 w-full shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200";
  const inp = "bg-white border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200";
  const ro  = "bg-gray-100 text-gray-900 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm cursor-not-allowed";
  const lbl = "block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1";

  const density = numOrNull(pickedFormula?.density_kg_per_l);

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-slate-200">

      {/* ── Top bar ── */}
      <div className="h-12 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 flex items-center px-5 gap-3 shrink-0 shadow-lg">
        <FlaskConical className="text-cyan-400 w-4 h-4" />
        <span className="text-white font-bold text-sm tracking-tight">Create Chemical Job Card</span>
        {selectedProduct && (
          <span className="bg-cyan-500/20 text-cyan-200 border border-cyan-500/40 text-xs font-semibold px-2.5 py-0.5 rounded-full">
            {selectedProduct.name}
          </span>
        )}
        {batchKg > 0 && (
          <span className="hidden lg:inline-flex items-center gap-1.5 text-xs text-slate-400 border-l border-slate-700 pl-3 ml-1">
            Batch
            <strong className="text-white font-mono text-sm">{batchKg.toFixed(2)} kg</strong>
          </span>
        )}
        {shortLines.length > 0 && (
          <span className="ml-auto flex items-center gap-1.5 bg-red-500/20 text-red-200 border border-red-500/40 text-xs font-bold px-3 py-1 rounded-full">
            <AlertTriangle className="w-3 h-3" />
            {shortLines.length} short
          </span>
        )}
      </div>

      {/* ── Two panels ── */}
      <div className="flex-1 overflow-hidden flex gap-3 p-3">

        {/* ═══════════════════════════
            PANEL 1 — THE JOB
        ═══════════════════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <div className="bg-indigo-50 border-b-2 border-indigo-500 px-4 py-2.5 flex items-center gap-2 shrink-0">
            <Package className="w-4 h-4 text-indigo-600" />
            <span className="text-xs font-black text-indigo-900 uppercase tracking-widest">The job</span>
          </div>

          <div className="p-4 overflow-y-auto flex flex-col gap-4 flex-1">

          {/* Customer sits alone — every job belongs to one, and it is the
              first decision made */}
          <div>
            <label className={lbl}>Customer</label>
            <div className="relative">
              <select
                value={form.customerId}
                onChange={e => { setForm(f => ({ ...f, customerId: e.target.value })); setCalculated(false); }}
                className={`${sel} text-base py-2.5`}
              >
                <option value="">---- Select Customer ----</option>
                {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
              <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className={lbl}>Product</label>
              <div className="relative">
                <input
                  type="text"
                  value={productSearch}
                  onChange={e => { setProductSearch(e.target.value); setShowSuggestions(true); }}
                  onClick={() => {
                    setProductSearch('');
                    setForm(f => ({ ...f, productId: '', formulaCode: '' }));
                    setSelectedProduct(null);
                    setCalculated(false);
                    cancelBatchEdit();
                  }}
                  placeholder="---- Search Product ----"
                  className={inp}
                />
                {showSuggestions && filteredProducts.length > 0 && (
                  <div className="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    {filteredProducts.map(p => (
                      <div
                        key={p.id}
                        onMouseDown={() => handleProductSelect(p)}
                        className="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm"
                      >
                        {p.name}
                        {p.sku && <span className="text-gray-400 text-xs ml-2">#{p.sku}</span>}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>

            <div>
              <label className={lbl}>Package</label>
              <div className="relative">
                <select value={form.containerSizeId} disabled className={`${sel} bg-gray-100`}>
                  <option value="">-- package --</option>
                  {containerSizes.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>

            <div>
              <label className={lbl}>Qnt (no of units)</label>
              <input
                type="number"
                value={form.quantity}
                onChange={e => { setForm(f => ({ ...f, quantity: e.target.value })); setCalculated(false); }}
                className={`${inp} focus:bg-yellow-50`}
                placeholder="e.g. 500"
              />
            </div>
            <div>
              <label className={lbl}>Weight per unit (kg)</label>
              <input readOnly value={form.weightPerUnit} className={ro} />
            </div>
            <div>
              <label className={lbl}>Batch weight (kg)</label>
              <input
                readOnly
                value={form.totalWeight}
                className="bg-indigo-50 text-indigo-900 border-2 border-indigo-300 rounded px-3 py-2 w-full shadow-sm cursor-not-allowed font-bold font-mono"
              />
            </div>
          </div>

          <div className="flex items-center gap-2 mt-1">
            <span className="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Specification</span>
            <div className="flex-1 h-px bg-indigo-100" />
          </div>

          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className={lbl}>Colour</label>
              <input readOnly value={byId(colourTypes, form.colourId)} className={ro} />
            </div>
            <div>
              <label className={lbl}>Viscosity</label>
              <input readOnly value={byId(viscosities, form.viscosityId)} className={ro} />
            </div>
            <div>
              <label className={lbl}>Active ingredient</label>
              <input readOnly value={byId(activeIngredients, form.activeIngredientId)} className={ro} />
            </div>
            <div>
              <label className={lbl}>Fragrance</label>
              <input readOnly value={byId(fragrances, form.fragranceId)} className={ro} />
            </div>
            <div>
              <label className={lbl}>Container / bottle</label>
              <input readOnly value={byId(bottleTypes, form.bottleTypeId)} className={ro} />
            </div>

            {/* Formula code — filled from the product, editable so a job can
                still be raised when the product's code is stale or missing */}
            <div>
              <label className={lbl}>Formula code</label>
              <input
                type="text"
                value={form.formulaCode}
                onChange={e => {
                  setForm(f => ({ ...f, formulaCode: e.target.value.toUpperCase() }));
                  setCalculated(false);
                }}
                placeholder="LIQSOAP-01"
                className={`${inp} font-mono ${
                  form.formulaCode && !pickedFormula ? '!border-red-400 !bg-red-50' : ''
                }`}
              />
              {form.formulaCode && !pickedFormula && !itemsLoading && (
                <p className="text-[10px] text-red-600 font-semibold mt-0.5">No formula with this code</p>
              )}
              {pickedFormula && (
                <p className="text-[10px] text-teal-600 font-semibold mt-0.5">{pickedFormula.name}</p>
              )}
            </div>

            {/* Batch / SKU — writes to the product before it changes here */}
            <div>
              <label className={lbl}>Batch / SKU</label>
              <div className="relative">
                <input
                  ref={batchRef}
                  type="text"
                  value={batchEditable ? batchDraft : form.batchSku}
                  readOnly={!batchEditable}
                  disabled={batchSaving}
                  onChange={e => setBatchDraft(e.target.value)}
                  onKeyDown={e => {
                    if (e.key === 'Enter')  saveBatchCode();
                    if (e.key === 'Escape') cancelBatchEdit();
                  }}
                  placeholder="Batch code"
                  className={`${batchEditable ? inp : ro} ${batchEditable ? 'pr-16' : 'pr-11'} font-mono`}
                />

                {batchEditable ? (
                  <div className="absolute right-1.5 top-1/2 -translate-y-1/2 flex items-center gap-0.5">
                    <button
                      type="button"
                      onClick={saveBatchCode}
                      disabled={batchSaving}
                      title="Update batch code on the product"
                      className="p-1.5 rounded text-green-600 hover:bg-green-50 disabled:opacity-50"
                    >
                      {batchSaving
                        ? <Loader2 className="w-4 h-4 animate-spin" />
                        : <Check className="w-4 h-4" />}
                    </button>
                    <button
                      type="button"
                      onClick={cancelBatchEdit}
                      disabled={batchSaving}
                      title="Cancel"
                      className="p-1.5 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 disabled:opacity-50"
                    >
                      <X className="w-4 h-4" />
                    </button>
                  </div>
                ) : (
                  <button
                    type="button"
                    onClick={openBatchEdit}
                    title="Change batch code"
                    className="absolute right-1.5 top-1/2 -translate-y-1/2 p-1.5 rounded text-gray-500 hover:text-blue-600 hover:bg-blue-50"
                  >
                    <Pencil className="w-4 h-4" />
                  </button>
                )}
              </div>
            </div>
          </div>

          <div>
            <label className={lbl}>Notes / instructions</label>
            <input
              type="text"
              value={form.notes}
              onChange={e => setForm(f => ({ ...f, notes: e.target.value }))}
              className={inp}
              placeholder="Safety notes, instructions…"
            />
          </div>

          {form.barcode && (
            <div className="bg-blue-50 p-3 rounded-lg border border-blue-200 flex items-center gap-3">
              <Package className="text-blue-600 w-4 h-4" />
              <span className="text-xs font-medium text-blue-900">Barcode</span>
              <span className="font-mono text-sm text-blue-800">{form.barcode}</span>
            </div>
          )}

          {/* ── Process lines ── */}
          <div className="flex items-center gap-2 mt-1">
            <span className="text-[10px] font-black text-indigo-500 uppercase tracking-widest">Process types</span>
            <div className="flex-1 h-px bg-indigo-100" />
            <button
              onClick={calculate}
              disabled={loading}
              className="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold disabled:opacity-50 shrink-0 shadow-sm"
            >
              {loading ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Calculator className="w-3.5 h-3.5" />}
              Calculate
            </button>
          </div>

          <div className="space-y-2">
            {processLines.map((line, idx) => (
              <div
                key={line.processId}
                className={`flex items-center gap-3 border rounded-lg px-3 py-2 transition-colors ${
                  line.checked
                    ? 'bg-indigo-50 border-indigo-200'
                    : 'bg-slate-50 border-slate-200'
                }`}
              >
                <input
                  type="checkbox"
                  checked={line.checked}
                  onChange={e => handleProcessChange(idx, 'checked', e.target.checked)}
                  className="w-4 h-4 text-blue-600 rounded"
                />
                <span className={`text-sm font-medium flex-1 ${line.checked ? 'text-indigo-900' : 'text-slate-500'}`}>
                  {line.processName}
                </span>
                <span className="text-xs font-mono text-slate-500">
                  {line.quantity || '—'} {byId(containerSizes, line.unitId)}
                </span>
              </div>
            ))}
          </div>

          {/* SAVE only appears once the job has been calculated */}
          {calculated && (
            <button
              onClick={handleSubmit}
              disabled={saving}
              className="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-bold py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors mt-1 shadow-md"
            >
              {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
              SAVE JOB CARD
            </button>
          )}
          </div>
        </div>

        {/* ═══════════════════════════
            PANEL 2 — THE MATERIALS
        ═══════════════════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
          <div className="bg-teal-50 border-b-2 border-teal-500 px-4 py-2.5 flex items-center gap-2 shrink-0">
            <FlaskConical className="w-4 h-4 text-teal-600" />
            <span className="text-xs font-black text-teal-900 uppercase tracking-widest">Materials required</span>
            {items.length > 0 && (
              <span className="ml-auto bg-teal-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                {items.length}
              </span>
            )}
          </div>

          <div className="p-4 overflow-y-auto flex flex-col gap-4 flex-1">

          {!form.productId ? (
            <p className="text-xs text-gray-400">Select a product to see what it needs.</p>
          ) : !form.formulaCode ? (
            <div className="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5">
              <p className="text-xs text-amber-800 font-semibold">
                This product has no formula linked — no materials can be worked out.
              </p>
            </div>
          ) : !pickedFormula && !itemsLoading ? (
            <div className="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5">
              <p className="text-xs text-amber-800 font-semibold">
                Formula {form.formulaCode} was not found in the formula list.
              </p>
            </div>
          ) : (
            <>
              {/* Formula header */}
              <div className="bg-gradient-to-r from-teal-50 to-cyan-50 rounded-lg border border-teal-200 px-3 py-2.5 flex items-center justify-between">
                <div>
                  <p className="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Formula</p>
                  <p className="text-sm font-semibold text-slate-800">{pickedFormula?.name}</p>
                  <p className="text-[10px] font-mono text-teal-500">{form.formulaCode}</p>
                </div>
                <div className="text-right">
                  <p className="text-[10px] font-bold text-teal-600 uppercase tracking-wider">Batch</p>
                  <p className="text-xl font-bold font-mono text-teal-900 leading-none mt-0.5">
                    {batchKg > 0 ? batchKg.toFixed(2) : '—'}
                    <span className="text-xs font-sans font-normal text-gray-400 ml-1">kg</span>
                  </p>
                  {density !== null && density > 0 && batchKg > 0 && (
                    <p className="text-[10px] text-teal-600 mt-0.5">
                      ≈ {(batchKg / density).toFixed(1)} L at {density.toFixed(4)} kg/L
                    </p>
                  )}
                </div>
              </div>

              {batchKg <= 0 && (
                <p className="text-xs text-gray-400">
                  Enter a quantity to see how much of each material to issue.
                </p>
              )}

              {itemsLoading ? (
                <div className="flex items-center gap-2 text-xs text-gray-400 py-4">
                  <Loader2 className="w-3.5 h-3.5 animate-spin" /> Loading the formula…
                </div>
              ) : itemsError ? (
                <p className="text-xs text-red-600 font-semibold py-4">{itemsError}</p>
              ) : items.length === 0 ? null : (
                <div className="border border-gray-200 rounded-lg overflow-hidden">
                  <table className="w-full text-sm">
                    <thead className="bg-teal-50 text-teal-700 uppercase text-[10px] tracking-wider">
                      <tr>
                        <th className="text-left px-3 py-2.5 font-bold">Material</th>
                        <th className="text-right px-2 py-2.5 font-bold w-16">%</th>
                        <th className="text-right px-2 py-2.5 font-bold w-24">Issue kg</th>
                        <th className="text-right px-3 py-2.5 font-bold w-24">On hand</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {items.map(it => {
                        const m     = materialOf(it.raw_material_id);
                        const pct   = numOrNull(it.percentage) ?? 0;
                        const need  = requiredKg(it);
                        const have  = stockOf(it.raw_material_id);
                        const short = batchKg > 0 && need > have;

                        return (
                          <tr key={it.id} className={short ? 'bg-red-50' : 'bg-white'}>
                            <td className="px-3 py-2.5 text-slate-800 font-medium">
                              {m?.name ?? `#${it.raw_material_id}`}
                              {Number(it.is_balance) === 1 && (
                                <span className="ml-1.5 text-[10px] font-bold text-blue-600 align-middle">BAL</span>
                              )}
                            </td>
                            <td className="px-2 py-2.5 text-right font-mono text-slate-500">
                              {pct.toFixed(2)}
                            </td>
                            <td className="px-2 py-2.5 text-right font-mono font-bold text-base text-teal-800">
                              {batchKg > 0 ? need.toFixed(3) : '—'}
                            </td>
                            <td className={`px-3 py-2.5 text-right font-mono ${short ? 'text-red-700 font-bold' : 'text-slate-500'}`}>
                              <span className="inline-flex items-center gap-1 justify-end">
                                {short && <AlertTriangle className="w-3.5 h-3.5 text-red-500" />}
                                {have.toFixed(2)}
                              </span>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                    {batchKg > 0 && (
                      <tfoot>
                        <tr className="bg-teal-50 border-t-2 border-teal-200">
                          <td className="px-3 py-2.5 text-[10px] font-bold text-teal-700 uppercase" colSpan={2}>
                            Total to issue
                          </td>
                          <td className="px-2 py-2.5 text-right font-mono font-bold text-base text-teal-900">
                            {totalRequired.toFixed(3)}
                          </td>
                          <td />
                        </tr>
                      </tfoot>
                    )}
                  </table>
                </div>
              )}

              {/* Shortfall warning — the thing worth catching before the mixer runs */}
              {shortLines.length > 0 && (
                <div className="bg-red-50 border border-red-200 rounded-lg px-3 py-2.5">
                  <p className="text-xs font-bold text-red-800 flex items-center gap-1.5 mb-1.5">
                    <AlertTriangle className="w-3.5 h-3.5" />
                    Not enough stock for {shortLines.length} material{shortLines.length > 1 ? 's' : ''}
                  </p>
                  <ul className="space-y-0.5">
                    {shortLines.map(it => (
                      <li key={it.id} className="text-xs text-red-700 flex justify-between">
                        <span>{materialOf(it.raw_material_id)?.name ?? `#${it.raw_material_id}`}</span>
                        <span className="font-mono font-semibold">
                          short {shortfall(it).toFixed(3)} kg
                        </span>
                      </li>
                    ))}
                  </ul>
                </div>
              )}

              {batchKg > 0 && shortLines.length === 0 && items.length > 0 && (
                <div className="bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex items-center gap-2">
                  <Check className="w-3.5 h-3.5 text-green-600" />
                  <span className="text-xs font-semibold text-green-800">
                    Enough stock on hand for this batch
                  </span>
                </div>
              )}
            </>
          )}
          </div>
        </div>

      </div>

      {loading && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg shadow-xl">
            <div className="flex items-center gap-3">
              <Loader2 className="w-6 h-6 animate-spin text-blue-600" />
              <span className="text-lg font-medium">Calculating...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ChemicalJobCardCreator;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ChemicalJobCardCreator />);
}