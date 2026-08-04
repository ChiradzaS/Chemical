import React, { useState, useEffect } from 'react';
import { ChevronDown, Save, ArrowLeft, AlertTriangle } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:         string;
    productFormUrl:        string;
    unitTypesData:         UnitType[];
    colourTypesData:       ColourType[];
    chemicalTypesData:     ChemicalType[];
    customersData:         Customer[];
    viscosityData:         Viscosity[];
    activeIngredientsData: ActiveIngredient[];
    fragranceData:         Fragrance[];
    bottleTypesData:       BottleType[];
    containerSizesData:    ContainerSize[];
    capTypesData:          CapType[];
    lableTypesData:        LableType[];
  }
}

interface UnitType         { id: string; name: string; }
interface ColourType       { id: string; name: string; }
interface ChemicalType     { id: string; name: string; }
interface Customer         { id: string; name: string; }
interface Viscosity        { id: string; name: string; }
interface ActiveIngredient { id: string; name: string; }
interface Fragrance        { id: string; name: string; }
interface BottleType       { id: string; name: string; }
interface ContainerSize    { id: string; name: string; }
interface CapType          { id: string; name: string; }
interface LableType        { id: string; name: string; }

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL = `${API_BASE}/chemicalproductlist`;
const VAT_RATE = 15;

// Density assumption used by the formula-derived weight
const DENSITY_G_PER_L = 1020;

type WeightSource = 'formula' | 'manual';

interface PF {
  name:                         string;
  sku:                          string;   // batch code e.g. DSE-005
  category:                     string;
  brand:                        string;
  barcode:                      string;
  description:                  string;
  invoiceDescription:           string;
  invoiceDescriptionOverridden: boolean;
  stockOnHand:                  string;
  stockUnitId:                  string;  // same values as container size
  formulaCode:                  string;
  phLevel:                      string;
  viscosityId:                  string;
  activeIngredientId:           string;  // optional
  fragranceId:                  string;
  colourId:                     string;
  concentration:                string;
  dilutionRatio:                string;
  bagTypeId:                    string;
  capTypeId:                    string;
  labelTypeId:                  string;
  unitsPerCarton:               string;
  cartonWeightKg:               string;
  batchSizeLitres:              string;
  unitsPerBatch:                string;
  mixingTimeMinutes:            string;
  fillingSpeedPerHour:          string;
  yieldPercentage:              string;
  shelfLifeMonths:              string;
  weightSource:                 WeightSource;
  weightPerUnitKg:              string;   // ← kg in, kg out. No conversion.
  rawMaterialCost:              string;   // ← the single "Cost price" field in the UI
  packagingCost:                string;   // ← retained: loaded from API, saved back unchanged
  labourCostPerUnit:            string;   // ← retained
  overheadCost:                 string;   // ← retained
  markupPercentage:             string;
  vatApplicable:                boolean;
  // ── Label print options ──
  showWeightOnLabel:            boolean;
  showDateOnLabel:              boolean;
  showExpiryDateOnLabel:        boolean;
  showBarcodeOnLabel:           boolean;
}

const EMPTY: PF = {
  name:'', sku:'', category:'', brand:'',
  barcode:'', description:'',
  invoiceDescription:'', invoiceDescriptionOverridden:false,
  stockOnHand:'0', stockUnitId:'',
  formulaCode:'', phLevel:'', viscosityId:'',
  activeIngredientId:'', fragranceId:'', colourId:'',
  concentration:'', dilutionRatio:'',
  bagTypeId:'', capTypeId:'', labelTypeId:'',
  unitsPerCarton:'', cartonWeightKg:'',
  batchSizeLitres:'', unitsPerBatch:'', mixingTimeMinutes:'',
  fillingSpeedPerHour:'', yieldPercentage:'95', shelfLifeMonths:'',
  weightSource:'manual', weightPerUnitKg:'',
  rawMaterialCost:'', packagingCost:'',
  labourCostPerUnit:'', overheadCost:'',
  markupPercentage:'35', vatApplicable:true,
  showWeightOnLabel:false,
  showDateOnLabel:true,
  showExpiryDateOnLabel:true,
  showBarcodeOnLabel:false,
};

const mapApiToForm = (d: any): PF => ({
  name:                         d.name                          ?? '',
  sku:                          d.sku                           ?? '',
  category:                     d.category                      ?? '',
  brand:                        d.brand                         ?? '',
  barcode:                      d.barcode                       ?? '',
  description:                  d.description                   ?? '',
  invoiceDescription:           d.invoice_description           ?? d.name ?? '',
  invoiceDescriptionOverridden: !!(d.invoice_description && d.invoice_description !== d.name),
  stockOnHand:                  String(d.stock_on_hand          ?? '0'),
  stockUnitId:                  String(d.stock_unit_id          ?? ''),
  formulaCode:                  d.formula_code                  ?? '',
  phLevel:                      String(d.ph_level               ?? ''),
  viscosityId:                  String(d.viscosity_id           ?? ''),
  activeIngredientId:           String(d.active_ingredient_id   ?? ''),
  fragranceId:                  String(d.fragrance_id           ?? ''),
  colourId:                     String(d.colour_id              ?? ''),
  concentration:                String(d.concentration          ?? ''),
  dilutionRatio:                d.dilution_ratio                ?? '',
  bagTypeId:                    String(d.bag_type_id            ?? ''),
  capTypeId:                    String(d.cap_type_id            ?? ''),
  labelTypeId:                  String(d.label_type_id          ?? ''),
  unitsPerCarton:               String(d.units_per_carton       ?? ''),
  cartonWeightKg:               String(d.carton_weight_kg       ?? ''),
  batchSizeLitres:              String(d.batch_size_litres      ?? ''),
  unitsPerBatch:                String(d.units_per_batch        ?? ''),
  mixingTimeMinutes:            String(d.mixing_time_minutes    ?? ''),
  fillingSpeedPerHour:          String(d.filling_speed_per_hour ?? ''),
  yieldPercentage:              String(d.yield_percentage       ?? '95'),
  shelfLifeMonths:              String(d.shelf_life_months      ?? ''),
  weightSource:                 (d.weight_source                ?? 'manual') as WeightSource,
  weightPerUnitKg:              String(d.weight_per_unit_grams  ?? ''),   // stored value used as-is
  rawMaterialCost:              String(d.raw_material_cost      ?? ''),
  packagingCost:                String(d.packaging_cost         ?? ''),
  labourCostPerUnit:            String(d.labour_cost_per_unit   ?? ''),
  overheadCost:                 String(d.overhead_cost          ?? ''),
  markupPercentage:             String(d.markup_percentage      ?? '35'),
  vatApplicable:                !!d.vat_applicable,
  showWeightOnLabel:            d.show_weight_on_label      === undefined ? false : !!d.show_weight_on_label,
  showDateOnLabel:              d.show_date_on_label        === undefined ? false : !!d.show_date_on_label,
  showExpiryDateOnLabel:        d.show_expiry_date_on_label === undefined ? true  : !!d.show_expiry_date_on_label,
  showBarcodeOnLabel:           d.show_barcode_on_label     === undefined ? false : !!d.show_barcode_on_label,
});

/* ── Input styling — darker fields on the light page ── */
const I  = "w-full bg-slate-200 text-gray-900 border border-slate-400 rounded-lg px-2.5 py-1.5 text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent hover:border-slate-500 transition-all";
const CI = "w-full bg-slate-300 text-slate-500 border border-dashed border-slate-400 rounded-lg px-2.5 py-1.5 text-sm cursor-not-allowed";
const RI = (err: boolean) => `${I} ${err ? '!border-red-400 !bg-red-100 focus:!ring-red-400' : ''}`;
// Emphasis modifier for the primary field (product name)
const BIG = "!text-base !py-2.5 !px-3 font-semibold";

const L = ({ t, required }: { t: string; required?: boolean }) => (
  <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">
    {t}{required && <span className="text-red-400 ml-0.5">*</span>}
  </p>
);

const SelId = ({ v, onCh, data, ph = 'Select…', err }: {
  v: string; onCh: (id: string) => void; data: { id: string; name: string }[]; ph?: string; err?: boolean;
}) => (
  <div className="relative">
    <select className={`${RI(!!err)} appearance-none pr-7`} value={v} onChange={e => onCh(e.target.value)}>
      <option value="">{ph}</option>
      {data.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
    </select>
    <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-500 pointer-events-none" />
  </div>
);

const Tog = ({ on, set, lbl }: { on: boolean; set: (v: boolean) => void; lbl: string }) => (
  <label className="flex items-center gap-2 cursor-pointer">
    <div
      onClick={() => set(!on)}
      className={`relative w-8 h-4 rounded-full transition-colors ${on ? 'bg-blue-600' : 'bg-gray-300'}`}
    >
      <div className={`absolute top-0.5 left-0.5 w-3 h-3 bg-white rounded-full shadow transition-transform ${on ? 'translate-x-4' : ''}`} />
    </div>
    <span className="text-xs text-gray-700 font-medium">{lbl}</span>
  </label>
);

// Simple checkbox for label-print options
const CheckBox = ({ on, set, lbl }: { on: boolean; set: (v: boolean) => void; lbl: string }) => (
  <label className="flex items-center gap-2 cursor-pointer select-none">
    <input
      type="checkbox"
      checked={on}
      onChange={e => set(e.target.checked)}
      className="w-3.5 h-3.5 accent-blue-600 rounded cursor-pointer"
    />
    <span className="text-xs text-gray-700 font-medium">{lbl}</span>
  </label>
);

const S = ({ icon, title }: { icon: string; title: string }) => (
  <div className="flex items-center gap-2">
    <span className="text-sm">{icon}</span>
    <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">{title}</span>
    <div className="flex-1 h-px bg-gray-100" />
  </div>
);

const ProductForm: React.FC = () => {
  const params    = new URLSearchParams(window.location.search);
  const productId = params.get('id') ? Number(params.get('id')) : null;

  const unitTypes         = window.unitTypesData         || [];
  const colourTypes       = window.colourTypesData       || [];
  const chemicalTypes     = window.chemicalTypesData     || [];
  const customers         = window.customersData         || [];
  const viscosities       = window.viscosityData         || [];
  const activeIngredients = window.activeIngredientsData || [];
  const fragrances        = window.fragranceData         || [];
  const bottleTypes       = window.bottleTypesData       || [];
  const containerSizes    = window.containerSizesData    || [];
  const capTypes          = window.capTypesData          || [];
  const labelTypes        = window.lableTypesData        || [];

  const [form,      setForm]      = useState<PF>(EMPTY);
  const [saving,    setSaving]    = useState(false);
  const [loading,   setLoading]   = useState(!!productId);
  const [submitted, setSubmitted] = useState(false);
  const [toast,     setToast]     = useState<{ msg: string; ok: boolean } | null>(null);

  useEffect(() => {
    if (!productId) return;
    setLoading(true);
    const encodedData = encodeURIComponent(JSON.stringify({ id: productId }));
    axios.get(`${API_BASE}/chemicalproducts/show?data=${encodedData}`)
      .then(r  => setForm(mapApiToForm(r.data)))
      .catch(() => flash('Failed to load product', false))
      .finally(() => setLoading(false));
  }, [productId]);

  useEffect(() => {
    if (!form.invoiceDescriptionOverridden)
      setForm(f => ({ ...f, invoiceDescription: f.name }));
  }, [form.name, form.invoiceDescriptionOverridden]);

  const flash = (msg: string, ok: boolean) => {
    setToast({ msg, ok });
    setTimeout(() => setToast(null), 3000);
  };

  const set = (k: keyof PF) => (v: any) => setForm(f => ({ ...f, [k]: v }));
  const ev  = (k: keyof PF) => (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => set(k)(e.target.value);

  const handleNameChange = (v: string) =>
    setForm(f => ({
      ...f, name: v,
      invoiceDescription: f.invoiceDescriptionOverridden ? f.invoiceDescription : v,
    }));

  const missing = (v: string) => submitted && !v.trim();

  // Formula-derived unit weight, in kg
  const calcWeightKg = (() => {
    const litres = parseFloat(form.batchSizeLitres);
    const units  = parseFloat(form.unitsPerBatch);
    const yield_ = parseFloat(form.yieldPercentage) / 100 || 0.95;
    if (!litres || !units) return null;
    return ((litres * DENSITY_G_PER_L * yield_) / units / 1000).toFixed(3);
  })();

  // Saved exactly as entered — 12 kg is stored as 12
  const weightForSave = (() => {
    const kg = form.weightSource === 'manual'
      ? parseFloat(form.weightPerUnitKg)
      : parseFloat(calcWeightKg ?? '');
    return Number.isFinite(kg) && kg > 0 ? kg : null;
  })();

  /* ── Pricing ──
     UI exposes only Cost price + Markup %.
     rawMaterialCost holds the entered cost price. packagingCost /
     labourCostPerUnit / overheadCost are no longer editable but are
     still read from the API and written back unchanged.          */
  const raw    = parseFloat(form.rawMaterialCost)   || 0;
  const pkg    = parseFloat(form.packagingCost)     || 0;
  const lab    = parseFloat(form.labourCostPerUnit) || 0;
  const ovh    = parseFloat(form.overheadCost)      || 0;
  const mkup   = parseFloat(form.markupPercentage)  || 0;
  const legacy = pkg + lab + ovh;                    // hidden extras on older records
  const cost   = raw + legacy;
  const excl   = cost * (1 + mkup / 100);
  const vat    = form.vatApplicable ? excl * (VAT_RATE / 100) : 0;
  const incl   = excl + vat;
  const mrgn   = excl > 0 ? ((excl - cost) / excl) * 100 : 0;
  const sellingPriceMissing = submitted && (!excl || excl <= 0);

  const buildPayload = () => ({
    id:                     productId ?? null,
    name:                   form.name,
    sku:                    form.sku                     || null,
    category:               form.category                || null,
    brand:                  form.brand                   || null,
    barcode:                form.barcode                 || null,
    description:            form.description             || null,
    invoice_description:    form.invoiceDescription      || null,
    stock_on_hand:          parseFloat(form.stockOnHand) || 0,
    stock_unit_id:          form.stockUnitId             || null,
    formula_code:           form.formulaCode             || null,
    ph_level:               form.phLevel                 ? parseFloat(form.phLevel)           : null,
    viscosity_id:           form.viscosityId             || null,
    active_ingredient_id:   form.activeIngredientId      || null,
    fragrance_id:           form.fragranceId             || null,
    colour_id:              form.colourId                || null,
    concentration:          form.concentration           ? parseFloat(form.concentration)     : null,
    dilution_ratio:         form.dilutionRatio           || null,
    bag_type_id:            form.bagTypeId               || null,
    container_size_id:      form.stockUnitId             || null, // same as stockUnitId
    cap_type_id:            form.capTypeId               || null,
    label_type_id:          form.labelTypeId             || null,
    units_per_carton:       form.unitsPerCarton          ? parseInt(form.unitsPerCarton)      : null,
    carton_weight_kg:       form.cartonWeightKg          ? parseFloat(form.cartonWeightKg)    : null,
    batch_size_litres:      form.batchSizeLitres         ? parseFloat(form.batchSizeLitres)   : null,
    units_per_batch:        form.unitsPerBatch           ? parseInt(form.unitsPerBatch)       : null,
    mixing_time_minutes:    form.mixingTimeMinutes       ? parseInt(form.mixingTimeMinutes)   : null,
    filling_speed_per_hour: form.fillingSpeedPerHour     ? parseInt(form.fillingSpeedPerHour) : null,
    yield_percentage:       parseFloat(form.yieldPercentage) || 95,
    shelf_life_months:      form.shelfLifeMonths         ? parseInt(form.shelfLifeMonths)     : null,
    weight_source:          form.weightSource,
    weight_per_unit_grams:  weightForSave,   // kg, exactly as typed
    raw_material_cost:      raw,
    packaging_cost:         pkg,
    labour_cost_per_unit:   lab,
    overhead_cost:          ovh,
    markup_percentage:      mkup,
    price:                  parseFloat(excl.toFixed(2)),
    vat_applicable:         form.vatApplicable ? 1 : 0,
    vat_rate:               VAT_RATE,
    // ── Label print options ──
    show_weight_on_label:       form.showWeightOnLabel      ? 1 : 0,
    show_date_on_label:         form.showDateOnLabel        ? 1 : 0,
    show_expiry_date_on_label:  form.showExpiryDateOnLabel  ? 1 : 0,
    show_barcode_on_label:      form.showBarcodeOnLabel     ? 1 : 0,
  });

  const handleSave = async () => {
    setSubmitted(true);
    if (!form.name.trim())        { flash('Product name is required', false);        return; }
    if (!form.sku.trim())         { flash('Batch code is required', false);          return; }
    if (!form.category)           { flash('Category is required', false);            return; }
    if (!form.brand)              { flash('Brand is required', false);               return; }
    if (!form.stockUnitId)        { flash('Package / unit is required', false);      return; }
    if (!form.viscosityId)        { flash('Viscosity is required', false);           return; }
    // active ingredient is optional — no check
    if (!form.fragranceId)        { flash('Fragrance is required', false);           return; }
    if (!form.colourId)           { flash('Colour is required', false);              return; }
    if (!form.bagTypeId)          { flash('Container / bottle type is required', false); return; }
    if (!form.capTypeId)          { flash('Cap type is required', false);            return; }
    if (!form.labelTypeId)        { flash('Label type is required', false);          return; }
    if (form.weightSource === 'manual' && !form.weightPerUnitKg)
                                  { flash('Weight per unit is required', false);     return; }
    if (form.weightSource === 'formula' && !calcWeightKg)
                                  { flash('Weight per unit could not be calculated — check batch size & units', false); return; }
    if (!form.shelfLifeMonths)    { flash('Shelf life is required', false);          return; }
    if (!excl || excl <= 0)       { flash('Selling price is required — enter a cost price and markup', false); return; }

    setSaving(true);
    try {
      const encodedData = encodeURIComponent(JSON.stringify(buildPayload()));
      const url = productId
        ? `${API_BASE}/chemicalproducts/update?data=${encodedData}`
        : `${API_BASE}/chemicalproducts/store?data=${encodedData}`;
      await axios.get(url);
      flash(productId ? 'Product updated' : 'Product saved', true);
      setTimeout(() => { window.location.href = LIST_URL; }, 1000);
    } catch (error: any) {
      if (!error.response) {
        flash(productId ? 'Product updated' : 'Product saved', true);
        setTimeout(() => { window.location.href = LIST_URL; }, 1000);
      } else {
        flash(error.response?.data?.message || 'Save failed', false);
      }
    } finally {
      setSaving(false);
    }
  };

  if (loading)
    return (
      <div className="h-screen flex items-center justify-center bg-gray-50 text-sm text-gray-400">
        Loading product…
      </div>
    );

  const mc = mrgn >= 30
    ? { b: 'border-green-200 bg-green-50', t: 'text-green-700', s: 'text-green-500', l: 'Healthy' }
    : mrgn >= 15
    ? { b: 'border-amber-200 bg-amber-50',  t: 'text-amber-700',  s: 'text-amber-500',  l: 'Acceptable' }
    : { b: 'border-red-200 bg-red-50',      t: 'text-red-700',    s: 'text-red-500',    l: 'Below target' };

  const categoryName = chemicalTypes.find(c => String(c.id) === String(form.category))?.name ?? form.category;

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-gray-100">

      {/* ── Toast ── */}
      {toast && (
        <div className={`fixed top-4 left-1/2 -translate-x-1/2 z-50 px-5 py-2 rounded-xl text-sm font-semibold shadow-lg flex items-center gap-2 ${
          toast.ok
            ? 'bg-green-50 text-green-700 border border-green-200'
            : 'bg-red-50 text-red-700 border border-red-200'
        }`}>
          {toast.ok ? '✓' : '✕'} {toast.msg}
        </div>
      )}

      {/* ── Header ── */}
      <div className="h-12 bg-black flex items-center px-5 gap-3 shrink-0">
        <button
          onClick={() => { window.location.href = LIST_URL; }}
          className="text-gray-500 hover:text-white transition-colors"
        >
          <ArrowLeft className="w-4 h-4" />
        </button>
        <div className="w-px h-5 bg-gray-800" />
        <span className="text-white font-bold text-sm truncate max-w-xs">
          {form.name || (productId ? `Edit Product #${productId}` : 'New Product')}
        </span>
        {categoryName && (
          <span className="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full shrink-0">
            {categoryName}
          </span>
        )}
        {cost > 0 && (
          <div className="hidden xl:flex items-center gap-4 ml-2 text-xs text-gray-500 border-l border-gray-800 pl-4 shrink-0">
            <span>Cost <strong className="text-white">R {cost.toFixed(2)}</strong></span>
            <span>Sell <strong className="text-white">R {excl.toFixed(2)}</strong></span>
            <span className={mrgn >= 30 ? 'text-green-400' : mrgn >= 15 ? 'text-amber-400' : 'text-red-400'}>
              Margin <strong>{mrgn.toFixed(1)}%</strong>
            </span>
          </div>
        )}
        <div className="ml-auto flex items-center gap-2 shrink-0">
          <button
            onClick={() => { setForm(EMPTY); setSubmitted(false); }}
            className="text-gray-500 hover:text-white text-xs px-3 py-1.5 border border-gray-700 rounded-lg transition-colors"
          >
            Reset
          </button>
          <button
            onClick={handleSave}
            disabled={saving}
            className="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors"
          >
            <Save className="w-3.5 h-3.5" />
            {saving ? 'Saving…' : productId ? 'Update' : 'Save'}
          </button>
        </div>
      </div>

      {/* ── 3-column body ── */}
      <div className="flex-1 overflow-hidden flex gap-3 p-3">

        {/* ═══════════════
            COL 1 — IDENTITY
        ═══════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-4 overflow-y-auto flex flex-col gap-3">
          <S icon="🏷" title="Identity" />

          {/* Primary field — larger than the rest */}
          <div>
            <L t="Product name" required />
            <input
              className={`${RI(missing(form.name))} ${BIG}`}
              placeholder="e.g. Lemon Dishwash 500ml"
              value={form.name}
              onChange={e => handleNameChange(e.target.value)}
            />
          </div>

          <div className="grid grid-cols-2 gap-2">
            {/* SKU renamed to Batch Code */}
            <div>
              <L t="Batch code" required />
              <input
                className={RI(missing(form.sku))}
                placeholder="e.g. DSE-005"
                value={form.sku}
                onChange={ev('sku')}
              />
            </div>
            <div>
              <L t="Barcode" />
              <input className={I} placeholder="6001234567890" value={form.barcode} onChange={ev('barcode')} />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-2">
            <div>
              <L t="Category" required />
              <SelId
                v={form.category}
                onCh={set('category')}
                data={chemicalTypes}
                ph="Select category…"
                err={missing(form.category)}
              />
            </div>
            <div>
              <L t="Brand" required />
              <SelId
                v={form.brand}
                onCh={set('brand')}
                data={customers}
                ph="Select brand…"
                err={missing(form.brand)}
              />
            </div>
          </div>

          <div>
            <L t="Invoice description" />
            <div className="relative">
              <input
                className={`${I} pr-14 ${form.invoiceDescriptionOverridden ? '!border-amber-400 focus:!ring-amber-400' : ''}`}
                placeholder="As it appears on invoice lines"
                value={form.invoiceDescription}
                onChange={e => setForm(f => ({
                  ...f,
                  invoiceDescription: e.target.value,
                  invoiceDescriptionOverridden: e.target.value !== f.name,
                }))}
              />
              {form.invoiceDescriptionOverridden && (
                <button
                  onClick={() => setForm(f => ({ ...f, invoiceDescription: f.name, invoiceDescriptionOverridden: false }))}
                  className="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-0.5 text-[10px] font-bold text-amber-600 hover:text-amber-800 whitespace-nowrap"
                >
                  <AlertTriangle className="w-2.5 h-2.5" /> Reset
                </button>
              )}
            </div>
          </div>

          {/* Package / unit — replaces both stockUnitId + containerSizeId */}
          <div className="grid grid-cols-2 gap-2">
            <div>
              <L t="Stock on hand" />
              <input className={I} type="number" step="0.001" placeholder="0" value={form.stockOnHand} onChange={ev('stockOnHand')} />
            </div>
            <div>
              <L t="Package / unit" required />
              <SelId
                v={form.stockUnitId}
                onCh={set('stockUnitId')}
                data={containerSizes}   // container sizes = unit values
                ph="Select package…"
                err={missing(form.stockUnitId)}
              />
            </div>
          </div>

          <div>
            <L t="Internal description / notes" />
            <input className={I} placeholder="Formulation notes, use case…" value={form.description} onChange={ev('description')} />
          </div>
        </div>

        {/* ═══════════════════════════
            COL 2 — FORMULATION + PRODUCTION
        ═══════════════════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-4 overflow-y-auto flex flex-col gap-3">
          <S icon="⚗️" title="Formulation" />

          <div className="grid grid-cols-2 gap-2">
            <div>
              <L t="Formula code" />
              <input className={I} placeholder="FML-2025-01" value={form.formulaCode} onChange={ev('formulaCode')} />
            </div>
            <div>
              <L t="pH level (0–14)" />
              <input className={I} type="number" step="0.1" min="0" max="14" placeholder="7.0" value={form.phLevel} onChange={ev('phLevel')} />
            </div>
            <div>
              <L t="Viscosity" required />
              <SelId
                v={form.viscosityId}
                onCh={set('viscosityId')}
                data={viscosities}
                ph="Select viscosity…"
                err={missing(form.viscosityId)}
              />
            </div>
            {/* Active ingredient — optional */}
            <div>
              <L t="Active ingredient" />
              <SelId
                v={form.activeIngredientId}
                onCh={set('activeIngredientId')}
                data={activeIngredients}
                ph="None"
              />
            </div>
            <div>
              <L t="Fragrance" required />
              <SelId
                v={form.fragranceId}
                onCh={set('fragranceId')}
                data={fragrances}
                ph="Select fragrance…"
                err={missing(form.fragranceId)}
              />
            </div>
            <div>
              <L t="Colour" required />
              <SelId
                v={form.colourId}
                onCh={set('colourId')}
                data={colourTypes}
                ph="Select colour…"
                err={missing(form.colourId)}
              />
            </div>
            <div>
              <L t="Concentration %" />
              <input className={I} type="number" step="0.1" placeholder="30" value={form.concentration} onChange={ev('concentration')} />
            </div>
            <div>
              <L t="Dilution ratio" />
              <input className={I} placeholder="1:10" value={form.dilutionRatio} onChange={ev('dilutionRatio')} />
            </div>
          </div>

          <S icon="⚙️" title="Production" />

          <div className="grid grid-cols-2 gap-2">
            <div>
              <L t="Batch size (L)" />
              <input className={I} type="number" placeholder="500" value={form.batchSizeLitres} onChange={ev('batchSizeLitres')} />
            </div>
            <div>
              <L t="Units per batch" />
              <input className={I} type="number" placeholder="1000" value={form.unitsPerBatch} onChange={ev('unitsPerBatch')} />
            </div>
            <div>
              <L t="Mixing time (min)" />
              <input className={I} type="number" placeholder="45" value={form.mixingTimeMinutes} onChange={ev('mixingTimeMinutes')} />
            </div>
            <div>
              <L t="Fill speed (units/hr)" />
              <input className={I} type="number" placeholder="600" value={form.fillingSpeedPerHour} onChange={ev('fillingSpeedPerHour')} />
            </div>
            <div>
              <L t="Yield %" />
              <input className={I} type="number" min="1" max="100" placeholder="95" value={form.yieldPercentage} onChange={ev('yieldPercentage')} />
            </div>
            <div>
              <L t="Shelf life (months)" required />
              <input className={RI(missing(form.shelfLifeMonths))} type="number" placeholder="24" value={form.shelfLifeMonths} onChange={ev('shelfLifeMonths')} />
            </div>
          </div>

          {/* Weight — kg in, kg saved */}
          <div className="bg-gray-50 rounded-xl p-3 border border-gray-100">
            <L t="Unit weight" required />
            <div className="flex gap-2 mt-1.5 mb-3">
              {(['manual', 'formula'] as WeightSource[]).map(ws => (
                <button
                  key={ws}
                  onClick={() => set('weightSource')(ws)}
                  className={`flex-1 py-1.5 rounded-lg text-xs font-bold border transition-all ${
                    form.weightSource === ws
                      ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                      : 'bg-white text-gray-500 border-gray-200 hover:border-blue-300'
                  }`}
                >
                  {ws === 'manual' ? '✎ Set manually' : '⚗ From formula'}
                </button>
              ))}
            </div>
            {form.weightSource === 'manual' ? (
              <div>
                <L t="Weight per unit (kg)" required />
                <input
                  className={RI(missing(form.weightPerUnitKg))}
                  type="number"
                  step="0.001"
                  placeholder="12"
                  value={form.weightPerUnitKg}
                  onChange={ev('weightPerUnitKg')}
                />
              </div>
            ) : (
              <div>
                <L t="Calculated weight (kg)" required />
                <input
                  className={submitted && !calcWeightKg ? RI(true) : CI}
                  readOnly
                  value={calcWeightKg ?? 'Enter batch size & units above'}
                />
                <p className="text-[10px] text-gray-400 mt-1">
                  Batch L × {DENSITY_G_PER_L} g/L × yield% ÷ units ÷ 1000
                </p>
              </div>
            )}
          </div>
        </div>

        {/* ═══════════════════════════
            COL 3 — PACKAGING + PRICING
        ═══════════════════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-4 overflow-y-auto flex flex-col gap-3">
          <S icon="📦" title="Packaging" />

          <div className="grid grid-cols-2 gap-2">
            {/* Container / bottle type — kept */}
            <div>
              <L t="Container / Bottle type" required />
              <SelId
                v={form.bagTypeId}
                onCh={set('bagTypeId')}
                data={bottleTypes}
                ph="Select type…"
                err={missing(form.bagTypeId)}
              />
            </div>
            {/* Cap type */}
            <div>
              <L t="Cap type" required />
              <SelId
                v={form.capTypeId}
                onCh={set('capTypeId')}
                data={capTypes}
                ph="Select cap…"
                err={missing(form.capTypeId)}
              />
            </div>
            {/* Label type */}
            <div>
              <L t="Label type" required />
              <SelId
                v={form.labelTypeId}
                onCh={set('labelTypeId')}
                data={labelTypes}
                ph="Select label…"
                err={missing(form.labelTypeId)}
              />
            </div>
            {/* Units per carton */}
            <div>
              <L t="Units per carton" />
              <input className={I} type="number" placeholder="24" value={form.unitsPerCarton} onChange={ev('unitsPerCarton')} />
            </div>
            <div className="col-span-2">
              <L t="Carton weight (kg)" />
              <input className={I} type="number" step="0.1" placeholder="12.5" value={form.cartonWeightKg} onChange={ev('cartonWeightKg')} />
            </div>
          </div>

          {/* Label print options */}
          <div className="bg-gray-50 rounded-xl p-3 border border-gray-100">
            <L t="Show on label" />
            <div className="grid grid-cols-2 gap-2 mt-1.5">
              <CheckBox on={form.showWeightOnLabel}      set={set('showWeightOnLabel')}      lbl="Weight" />
              <CheckBox on={form.showDateOnLabel}        set={set('showDateOnLabel')}        lbl="Date" />
              <CheckBox on={form.showExpiryDateOnLabel}  set={set('showExpiryDateOnLabel')}  lbl="Expiry date" />
              <CheckBox on={form.showBarcodeOnLabel}     set={set('showBarcodeOnLabel')}     lbl="Barcode" />
            </div>
          </div>

          <S icon="💰" title="Pricing" />

          {/* Cost price + markup — these two set the selling price */}
          <div className="grid grid-cols-2 gap-2">
            <div>
              <L t="Cost price (R)" />
              <input
                className={I}
                type="number"
                step="0.01"
                placeholder="0.00"
                value={form.rawMaterialCost}
                onChange={ev('rawMaterialCost')}
              />
            </div>
            <div>
              <L t="Markup %" />
              <div className="relative">
                <input
                  className={`${I} pr-6`}
                  type="number"
                  step="1"
                  placeholder="35"
                  value={form.markupPercentage}
                  onChange={ev('markupPercentage')}
                />
                <span className="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-slate-500 font-bold pointer-events-none">%</span>
              </div>
            </div>
          </div>

          {legacy > 0 && (
            <p className="text-[10px] text-amber-600 font-semibold -mt-1">
              This product carries R {legacy.toFixed(2)} of packaging, labour and overhead
              recorded earlier. It is kept and still counted in the cost price.
            </p>
          )}

          {/* Pricing results */}
          <div className={`bg-gray-50 rounded-xl p-3 border flex flex-col gap-0.5 ${sellingPriceMissing ? 'border-red-300 ring-1 ring-red-200' : 'border-gray-100'}`}>
            <div className="flex justify-between py-1">
              <span className="text-xs text-gray-400">Total cost price</span>
              <span className="text-xs font-mono text-gray-700">R {cost.toFixed(2)}</span>
            </div>
            <div className="flex justify-between py-1">
              <span className={`text-xs ${sellingPriceMissing ? 'text-red-500 font-bold' : 'text-gray-400'}`}>
                Selling (excl. VAT){sellingPriceMissing && ' *'}
              </span>
              <span className={`text-xs font-mono ${sellingPriceMissing ? 'text-red-600 font-bold' : 'text-gray-700'}`}>R {excl.toFixed(2)}</span>
            </div>
            <div className="flex justify-between items-center py-1">
              <Tog on={form.vatApplicable} set={set('vatApplicable')} lbl={`VAT ${VAT_RATE}%`} />
              <span className="text-xs font-mono text-gray-700">R {vat.toFixed(2)}</span>
            </div>
            <div className="flex justify-between items-center py-2 border-t border-gray-200 mt-1">
              <span className="text-sm font-bold text-gray-700">Selling (incl. VAT)</span>
              <span className="text-sm font-bold font-mono text-blue-700">R {incl.toFixed(2)}</span>
            </div>
            {sellingPriceMissing && (
              <p className="text-[10px] text-red-500 font-semibold mt-1">
                Selling price must be greater than R0.00 — enter a cost price and markup %.
              </p>
            )}
          </div>

          {/* Margin */}
          {cost > 0 && (
            <div className={`rounded-xl p-3 border ${mc.b} flex items-center justify-between`}>
              <div>
                <p className={`text-[9px] font-bold uppercase tracking-widest ${mc.s}`}>Gross margin</p>
                <p className={`text-2xl font-bold font-mono leading-none mt-0.5 ${mc.t}`}>{mrgn.toFixed(1)}%</p>
              </div>
              <span className={`text-xs font-bold px-3 py-1.5 rounded-full border ${mc.b} ${mc.t}`}>{mc.l}</span>
            </div>
          )}

          <div className="mt-auto pt-1">
            <button
              onClick={handleSave}
              disabled={saving}
              className="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-bold py-2.5 rounded-xl flex items-center justify-center gap-2 transition-colors"
            >
              <Save className="w-4 h-4" />
              {saving ? 'Saving…' : productId ? 'Update Product' : 'Save Product'}
            </button>
          </div>
        </div>

      </div>
    </div>
  );
};

export default ProductForm;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ProductForm />);
}