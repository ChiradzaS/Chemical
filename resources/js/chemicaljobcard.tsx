import React, { useState, useEffect, useRef } from 'react';
import { ChevronDown, Loader2, Package, FlaskConical, Calculator, Pencil, Check, X } from 'lucide-react';
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
  }
}

interface ChemicalProduct {
  id:                    number;
  name:                  string;
  category:              string | null;
  brand:                 string | null;
  sku:                   string | null;
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
  weight_per_unit_grams: number | null;
  price:                 number | null;
  vat_applicable:        number;
  concentration:         number | null;
  dilution_ratio:        string | null;
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

  // ── Batch / SKU editing ───────────────────────────────────────────────────
  // form.batchSku only ever holds a value the API has accepted.
  // batchDraft is what the user is typing; it is discarded if the call fails.
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
    weightPerUnit:      '',  // weight_per_unit_grams from product
    totalWeight:        '',  // qty × weightPerUnit — shown col 4 for reference only
    notes:              '',
    barcode:            '',
    batchSku:           '',  // product.sku — changed only through the API
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

  // ── Pre-fill from URL params (clone) ──────────────────────────────────────
  useEffect(() => {
    const urlParams = (window as any).urlParamsData || {};
    if (urlParams.customerId) setForm(f => ({ ...f, customerId: urlParams.customerId }));
    if (urlParams.productId) {
      const p = chemicalProducts.find(x => String(x.id) === String(urlParams.productId));
      if (p) handleProductSelect(p);
    }
  }, [chemicalProducts]);

  // ── Live total weight (for display only) ──────────────────────────────────
  useEffect(() => {
    const qty    = parseFloat(form.quantity)      || 0;
    const weight = parseFloat(form.weightPerUnit) || 0;
    const total  = qty * weight;
    setForm(f => ({ ...f, totalWeight: total > 0 ? total.toFixed(2) : '' }));
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
    setCalculated(false);      // new product → must calculate again
    setBatchEditable(false);   // lock the batch field again
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
    }));
  };

  // ── Batch code — open the field ───────────────────────────────────────────
  const openBatchEdit = () => {
    if (!form.productId) { alert('Select a product first'); return; }
    setBatchDraft(form.batchSku);
    setBatchEditable(true);
    setTimeout(() => batchRef.current?.select(), 0);
  };

  const cancelBatchEdit = () => {
    setBatchDraft('');
    setBatchEditable(false);
  };

  // ── Batch code — write it to the product, then set it locally ─────────────
  // GET /chemicalproducts/updatebatch?data={"id":..,"sku":".."}
  // The field is only updated and re-locked if the API returns 200.
  const saveBatchCode = async () => {
    const next = batchDraft.trim();

    if (!form.productId) { alert('Select a product first');      return; }
    if (!next)           { alert('Batch code cannot be blank');  return; }
    if (next === form.batchSku) { cancelBatchEdit(); return; }   // nothing changed

    setBatchSaving(true);
    try {
      const payload     = { id: form.productId, sku: next };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/chemicalproducts/updatebatch?data=${encodedData}`);

      const savedSku = response.data?.sku ?? next;

      // API accepted it — now update the page
      setForm(f => ({ ...f, batchSku: savedSku }));
      setSelectedProduct(p => (p ? { ...p, sku: savedSku } : p));

      // keep the in-memory product list in step so re-selecting shows the new code
      const local = chemicalProducts.find(p => String(p.id) === String(form.productId));
      if (local) local.sku = savedSku;

      setBatchEditable(false);
      setBatchDraft('');
    } catch (error: any) {
      const res    = error.response;
      const errors = res?.data?.errors;
      const msg    = errors ? Object.values(errors).flat().join('\n')
                            : res?.data?.message || 'Could not reach the server — batch code not changed.';
      alert(msg);
      // field stays open and form.batchSku stays on the last saved value
    } finally {
      setBatchSaving(false);
    }
  };

  // ── Calculate ─────────────────────────────────────────────────────────────
  const calculate = () => {
    if (!form.productId)  { alert('Please select a product');  return; }
    if (!form.customerId) { alert('Please select a customer'); return; }
    if (!form.quantity)   { alert('Please enter quantity');     return; }
    if (batchEditable)    { alert('Save or cancel the batch code first'); return; }

    if (!form.barcode) generateBarcode();

    const qty         = parseFloat(form.quantity)      || 0;
    const weight      = parseFloat(form.weightPerUnit) || 0;
    const totalWeight = qty * weight;

    setForm(f => ({ ...f, totalWeight: totalWeight.toFixed(2) }));

    // Process line quantity = exactly what the user entered (no multiplication)
    // Process line unit     = container size id
    setProcessLines(prev => prev.map(line => ({
      ...line,
      checked:   true,
      productId: form.productId,
      quantity:  qty.toString(),       // ← just the user's number
      unitId:    form.containerSizeId, // ← container size as unit
    })));

    setLoading(true);
    setTimeout(() => {
      setLoading(false);
      setCalculated(true);   // ← SAVE button appears from here
    }, 800);
  };

  const handleProcessChange = (idx: number, field: keyof ProcessLine, value: string | boolean) => {
    setProcessLines(prev => prev.map((line, i) => i === idx ? { ...line, [field]: value } : line));
  };

  // ── Submit ────────────────────────────────────────────────────────────────
  const handleSubmit = async () => {
    if (!form.customerId) { alert('Please select a customer'); return; }
    if (!form.productId)  { alert('Please select a product');  return; }
    if (!form.quantity)   { alert('Please enter quantity');     return; }

    setSaving(true);
    try {
      const payload = {
        jobCard: { ...form },   // batchSku travels with the job card
        items: processLines.filter(l => l.checked).map(l => ({
          processId:   l.processId,
          processName: l.processName,
          productId:   l.productId,
          quantity:    l.quantity,  // user's number saved to jobcarditem.quantity
          unitId:      l.unitId,    // containerSizeId saved to jobcarditem.unitId
        })),
      };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      await axios.get(`${API_BASE}/chemicaljobcards/store?data=${encodedData}`);
      alert('Job card saved successfully!');
      window.location.href = LIST_URL;
    } catch (error: any) {
      if (!error.response) {

        window.location.href = LIST_URL;
      } else {
        alert('Error saving job card. Please retry.');
        console.error(error);
      }
    } finally {
      setSaving(false);
    }
  };

  const sel = "appearance-none bg-white border-2 border-gray-300 rounded-lg px-4 py-2 pr-8 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200";
  const inp = "bg-white border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200";
  const ro  = "bg-gray-100 text-gray-900 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm cursor-not-allowed";

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        <div className="w-full p-6 bg-white rounded-lg shadow-lg">

          {/* ── Page header ── */}
          <div className="border-b border-gray-200 pb-4 mb-6">
            <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
              <FlaskConical className="text-blue-600" />
              Create Chemical Job Card
            </h1>
          </div>

          <div className="space-y-6">

            {/* ── Customer + Calculate ── */}
            <div className="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
              <div className="flex items-center gap-4">
                <label className="text-lg font-semibold text-gray-700">Customer:</label>
                <div className="relative">
                  <select
                    value={form.customerId}
                    onChange={e => { setForm(f => ({ ...f, customerId: e.target.value })); setCalculated(false); }}
                    className={`${sel} min-w-64`}
                  >
                    <option value="">---- Select Customer ----</option>
                    {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                  </select>
                  <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
                </div>
              </div>
              <button
                onClick={calculate}
                disabled={loading}
                className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium flex items-center gap-2 disabled:opacity-50"
              >
                {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Calculator className="w-4 h-4" />}
                Calculate
              </button>
            </div>

            {/* ── Product search ── */}
            <div className="flex items-center justify-between bg-blue-50 p-4 rounded-lg">
              <div className="flex items-center gap-4">
                <label className="text-lg font-semibold text-gray-700">Product:</label>
                <div className="relative">
                  <input
                    type="text"
                    value={productSearch}
                    onChange={e => { setProductSearch(e.target.value); setShowSuggestions(true); }}
                    onClick={() => {
                      setProductSearch('');
                      setForm(f => ({ ...f, productId: '' }));
                      setSelectedProduct(null);
                      setCalculated(false);
                      cancelBatchEdit();
                    }}
                    placeholder="---- Search Chemical Product ----"
                    className={`${sel} min-w-96`}
                  />
                  {showSuggestions && filteredProducts.length > 0 && (
                    <div className="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                      {filteredProducts.map(p => (
                        <div
                          key={p.id}
                          onMouseDown={() => handleProductSelect(p)}
                          className="px-4 py-2 hover:bg-blue-50 cursor-pointer"
                        >
                          {p.name}
                          {p.sku && <span className="text-gray-400 text-sm ml-2">#{p.sku}</span>}
                        </div>
                      ))}
                    </div>
                  )}
                  <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
                </div>
              </div>
              {form.productId && (
                <div className="text-sm text-green-600 font-medium">
                  ✓ Product Selected (ID: {form.productId})
                </div>
              )}
            </div>

            {/* ── Product details block ── */}
            <div className="border border-gray-200 rounded-lg overflow-hidden">

              {/* Dark header
                  Col 1 — Product name (disabled)
                  Col 2 — Package / container size (disabled)
                  Col 3 — Qty: no of units the user enters → also saved on process lines
                  Col 4 — Qnt per Unit: qty × weight (display only)
              */}
              <div className="bg-gray-800 text-white p-3">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">

                  <div>
                    <label className="block text-sm font-medium mb-2">Product Name</label>
                    <div className="relative">
                      <select
                        value={form.productId}
                        disabled
                        className="appearance-none bg-white text-gray-900 border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                      >
                        <option value="">---- Select Product ----</option>
                        {chemicalProducts.map(p => (
                          <option key={p.id} value={p.id}>{p.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium mb-2">Package</label>
                    <div className="relative">
                      <select
                        value={form.containerSizeId}
                        disabled
                        className="appearance-none bg-white text-gray-900 border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                      >
                        <option value="">-- package --</option>
                        {containerSizes.map(c => (
                          <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                  </div>

                  {/* Col 3 — user enters number of units, this goes on process lines */}
                  <div>
                    <label className="block text-sm font-medium mb-2">Qnt (no of units)</label>
                    <input
                      type="number"
                      value={form.quantity}
                      onChange={e => { setForm(f => ({ ...f, quantity: e.target.value })); setCalculated(false); }}
                      className="bg-white text-gray-900 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:bg-yellow-50"
                      placeholder="e.g. 500"
                    />
                  </div>

                  {/* Col 4 — qty × weight per unit, display only */}
                  <div>
                    <label className="block text-sm font-medium mb-2">Total Qnt per Job (g)</label>
                    <input
                      type="text"
                      value={form.totalWeight}
                      readOnly
                      className="bg-gray-100 text-gray-900 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm cursor-not-allowed"
                    />
                  </div>

                </div>
              </div>

              {/* White body — formulation readonly fields */}
              <div className="p-4">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Colour</label>
                    <input readOnly value={byId(colourTypes, form.colourId)} className={ro} />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Viscosity</label>
                    <input readOnly value={byId(viscosities, form.viscosityId)} className={ro} />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Active Ingredient</label>
                    <input readOnly value={byId(activeIngredients, form.activeIngredientId)} className={ro} />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Fragrance</label>
                    <input readOnly value={byId(fragrances, form.fragranceId)} className={ro} />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Container / Bottle Type</label>
                    <div className="relative">
                      <select
                        value={form.bottleTypeId}
                        disabled
                        className="appearance-none border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm bg-gray-100"
                      >
                        <option value="">--</option>
                        {bottleTypes.map(b => (
                          <option key={b.id} value={b.id}>{b.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Weight per Unit (g)</label>
                    <input readOnly value={form.weightPerUnit} className={ro} />
                  </div>

                  {/* ── Batch / SKU — writes to the product before it changes here ── */}
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Batch / SKU</label>
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
                    {batchEditable && (
                      <p className="text-xs text-gray-500 mt-1">Saves to the product record</p>
                    )}
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Notes / Instructions</label>
                    <input
                      type="text"
                      value={form.notes}
                      onChange={e => setForm(f => ({ ...f, notes: e.target.value }))}
                      className={inp}
                      placeholder="Safety notes, instructions…"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* ── Barcode ── */}
            {form.barcode && (
              <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div className="flex items-center gap-3">
                  <Package className="text-blue-600" />
                  <span className="font-medium text-blue-900">Barcode:</span>
                  <span className="font-mono text-lg text-blue-800">{form.barcode}</span>
                </div>
              </div>
            )}

            {/* ── Process lines ──
                quantity = exactly what the user typed (form.quantity)
                unit     = container size id
            */}
            <div className="space-y-4">
              <h3 className="text-lg font-semibold text-gray-900">Process Types</h3>
              {processLines.map((line, idx) => (
                <div key={line.processId} className="border border-gray-200 rounded-lg overflow-hidden">
                  <div className="bg-gray-800 text-white p-3">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">

                      <div className="flex items-center gap-3">
                        <input
                          type="checkbox"
                          checked={line.checked}
                          onChange={e => handleProcessChange(idx, 'checked', e.target.checked)}
                          className="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                        />
                        <span className="font-medium">{line.processName}</span>
                      </div>

                      <div>
                        <label className="block text-sm font-medium mb-1">Product</label>
                        <div className="relative">
                          <select
                            value={line.productId}
                            onChange={e => handleProcessChange(idx, 'productId', e.target.value)}
                            disabled={!line.checked}
                            className="appearance-none bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 pr-8 w-full text-sm disabled:bg-gray-100"
                          >
                            <option value="">-- select Product --</option>
                            {chemicalProducts.map(p => (
                              <option key={p.id} value={p.id}>{p.name}</option>
                            ))}
                          </select>
                          <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" />
                        </div>
                      </div>

                      {/* exactly what the user typed */}
                      <div>
                        <label className="block text-sm font-medium mb-1">Quantity</label>
                        <input
                          type="text"
                          readOnly
                          value={line.quantity}
                          className="bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 w-full text-sm cursor-not-allowed"
                        />
                      </div>

                      {/* container size */}
                      <div>
                        <label className="block text-sm font-medium mb-1">Unit</label>
                        <div className="relative">
                          <select
                            value={line.unitId}
                            disabled
                            className="appearance-none bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 pr-8 w-full text-sm disabled:bg-gray-100"
                          >
                            <option value="">-- select unit --</option>
                            {containerSizes.map(c => (
                              <option key={c.id} value={c.id}>{c.name}</option>
                            ))}
                          </select>
                          <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4" />
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              ))}
            </div>

            {/* ── Submit — only after Calculate ── */}
            {calculated && (
              <div className="flex justify-center pt-6">
                <button
                  onClick={handleSubmit}
                  disabled={saving}
                  className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium flex items-center gap-2 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all"
                >
                  {saving ? <Loader2 className="w-5 h-5 animate-spin" /> : null}
                  SAVE
                </button>
              </div>
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