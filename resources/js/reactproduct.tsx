import React, { useState, useEffect } from 'react';
import { ChevronDown, Save, ArrowLeft, AlertTriangle, Loader2 } from 'lucide-react';
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
interface ContainerSize    { id: string; name: string; value?: string | number | null; }
interface CapType          { id: string; name: string; }
interface LableType        { id: string; name: string; }

// Formulas come from the formula builder
interface FormulaOption {
  id:                number;
  code:              string;
  name:              string;
  density_kg_per_l?: number | string | null;
}

// Raw materials, used to resolve ingredient names on the formula panel
interface MaterialRow {
  id:           number;
  code:         string;
  name:         string;
  uom:          string;
  cost_per_kg?: number | string | null;
}

// One ingredient row as returned by formulas/show
interface FormulaItem {
  id:              number;
  raw_material_id: number;
  percentage:      number | string;
  uom:             string;
  is_balance:      number | boolean;
  sequence?:       number;
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL = `${API_BASE}/chemicalproductlist`;
const VAT_RATE = 15;

// Fallback only — the real figure comes off the selected formula
const DEFAULT_DENSITY_KG_PER_L = 1;

// Formulas are written against a 1000 kg batch; used to turn % into kg
const FORMULA_BASE_QTY = 1000;

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

const numOrNull = (v: any): number | null => {
  if (v === null || v === undefined || v === '') return null;
  const n = Number(v);
  return Number.isFinite(n) ? n : null;
};

/* Container size → litres.
   The `value` column is authoritative when present (0.500 means half a litre).
   Otherwise the name is parsed, since sizes are often only labelled: "500ml",
   "5 L", "0.750". A bare number is read as litres, matching the value column. */
const containerLitres = (c?: ContainerSize): number | null => {
  if (!c) return null;

  const fromValue = numOrNull(c.value);
  if (fromValue !== null && fromValue > 0) return fromValue;

  const raw = String(c.name ?? '').trim().toLowerCase();
  const match = raw.match(/([\d.,]+)\s*(ml|l|lt|ltr|litre|liter)?/);
  if (!match) return null;

  const qty = Number(match[1].replace(',', '.'));
  if (!Number.isFinite(qty) || qty <= 0) return null;

  // ml is the only unit that isn't already litres
  return match[2] === 'ml' ? qty / 1000 : qty;
};

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

  // ── Formulas + the selected formula's ingredients ──
  const [formulas,     setFormulas]     = useState<FormulaOption[]>([]);
  const [materials,    setMaterials]    = useState<MaterialRow[]>([]);
  const [items,        setItems]        = useState<FormulaItem[]>([]);
  const [itemsLoading, setItemsLoading] = useState(false);
  const [itemsError,   setItemsError]   = useState<string | null>(null);
  const [baseQty,      setBaseQty]      = useState<number>(FORMULA_BASE_QTY);
  const [priceInput,   setPriceInput]   = useState('');
  const [priceFocused, setPriceFocused] = useState(false);
  const [priceInclInput,   setPriceInclInput]   = useState('');
  const [priceInclFocused, setPriceInclFocused] = useState(false);

  useEffect(() => {
    // materials resolve ingredient ids to names for the panel below
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

  useEffect(() => {
    if (!productId) return;
    setLoading(true);
    const encodedData = encodeURIComponent(JSON.stringify({ id: productId }));
    axios.get(`${API_BASE}/chemicalproducts/show?data=${encodedData}`)
      .then(r  => setForm(mapApiToForm(r.data)))
      .catch(() => flash('Failed to load product', false))
      .finally(() => setLoading(false));
  }, [productId]);

  const pickedFormula = formulas.find(f => f.code === form.formulaCode);

  // Fetch the ingredients whenever the resolved formula changes. Keyed on the
  // id rather than the object so a re-fetch of the list doesn't re-trigger it.
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
        // a formula written against a different base must not be read as 1000
        const base = numOrNull(r.data?.formula?.base_batch_qty);
        setBaseQty(base && base > 0 ? base : FORMULA_BASE_QTY);
      })
      .catch(() => { if (!cancelled) { setItems([]); setItemsError('Could not load the ingredients'); } })
      .finally(() => { if (!cancelled) setItemsLoading(false); });

    // a fast second pick must not have its response overwrite the newer one
    return () => { cancelled = true; };
  }, [pickedFormulaId]);

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

  const materialOf = (id: number) => materials.find(m => Number(m.id) === Number(id));

  /* ── Recipe cost ──
     Each ingredient's kg for the batch × its cost per kg. A material with no
     price on file contributes nothing and is counted as unpriced, so a partial
     total is never presented as if it were complete. */
  const priceOf = (id: number) => {
    const c = numOrNull(materialOf(id)?.cost_per_kg);
    return c !== null && c > 0 ? c : null;
  };

  const lineKg   = (pct: number) => baseQty * pct / 100;
  const lineCost = (it: FormulaItem) => {
    const rate = priceOf(it.raw_material_id);
    return rate === null ? null : lineKg(numOrNull(it.percentage) ?? 0) * rate;
  };

  const unpricedCount = items.filter(it => priceOf(it.raw_material_id) === null).length;
  const batchCost     = items.reduce((s, it) => s + (lineCost(it) ?? 0), 0);
  const costPerKg     = baseQty > 0 ? batchCost / baseQty : 0;
  // Density comes off the formula. Falls back to water when unset.
  const density = numOrNull(pickedFormula?.density_kg_per_l) ?? DEFAULT_DENSITY_KG_PER_L;

  // The chosen package, in litres
  const pickedContainer = containerSizes.find(c => String(c.id) === String(form.stockUnitId));
  const unitLitres      = containerLitres(pickedContainer);

  // Unit weight = what the container holds × how heavy that liquid is.
  // kg/L × L gives kg directly — no /1000.
  const calcWeightKg = unitLitres === null
    ? null
    : (unitLitres * density).toFixed(3);

  // Whatever sits in the input is what gets saved — in container mode the
  // effect above keeps it equal to the calculation
  const weightForSave = (() => {
    const kg = parseFloat(form.weightPerUnitKg);
    return Number.isFinite(kg) && kg > 0 ? kg : null;
  })();

  // Picking a formula switches to container-derived weight, but only the first
  // time — an operator who then chooses manual must not be flipped back
  const [weightModeTouched, setWeightModeTouched] = useState(false);

  useEffect(() => {
    if (pickedFormula && !weightModeTouched && form.weightSource !== 'formula') {
      setForm(f => ({ ...f, weightSource: 'formula' }));
    }
  }, [pickedFormula, weightModeTouched, form.weightSource]);

  // Clearing the formula removes the container option, so the mode must not be
  // left pointing at a button that is no longer on screen
  useEffect(() => {
    if (!pickedFormula && form.weightSource === 'formula') {
      setForm(f => ({ ...f, weightSource: 'manual', weightPerUnitKg: '' }));
    }
  }, [pickedFormula, form.weightSource]);

  // In container mode the input mirrors the calculation, so what gets saved is
  // exactly what the operator can see in the box
  useEffect(() => {
    if (form.weightSource !== 'formula') return;
    const next = calcWeightKg ?? '';
    if (next !== form.weightPerUnitKg) {
      setForm(f => ({ ...f, weightPerUnitKg: next }));
    }
  }, [form.weightSource, calcWeightKg, form.weightPerUnitKg]);

  // What one unit of finished product costs to make
  const unitWeightKg   = numOrNull(form.weightPerUnitKg);
  const recipeCostUnit = unitWeightKg && unitWeightKg > 0 && costPerKg > 0
    ? unitWeightKg * costPerKg
    : null;

  // The same ingredient, scaled to one unit rather than the whole batch:
  // its share of the unit's weight × its price
  const lineCostPerUnit = (it: FormulaItem) => {
    const rate = priceOf(it.raw_material_id);
    if (rate === null || !unitWeightKg || unitWeightKg <= 0) return null;
    return unitWeightKg * ((numOrNull(it.percentage) ?? 0) / 100) * rate;
  };

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

  /* Selling price and markup are two views of one number. Markup is what gets
     stored, so typing a price back-solves the markup rather than adding a
     second saved field that could drift out of step with the first. */
  const markupFromExcl = (price: number) => {
    if (!Number.isFinite(price) || cost <= 0) return;
    setForm(f => ({ ...f, markupPercentage: String(Math.round((price / cost - 1) * 10000) / 100) }));
  };

  const setSellingExcl = (v: string) => {
    setPriceInput(v);
    markupFromExcl(parseFloat(v));
  };

  // Incl. VAT is stripped back to excl. before the markup is worked out, so
  // both boxes end up driving the same stored figure
  const setSellingIncl = (v: string) => {
    setPriceInclInput(v);
    const incVat = parseFloat(v);
    if (!Number.isFinite(incVat)) return;
    markupFromExcl(form.vatApplicable ? incVat / (1 + VAT_RATE / 100) : incVat);
  };

  // The price boxes follow the markup unless the operator is typing in one
  useEffect(() => {
    if (priceFocused) return;
    const next = excl > 0 ? excl.toFixed(2) : '';
    setPriceInput(prev => (prev === next ? prev : next));
  }, [excl, priceFocused]);

  useEffect(() => {
    if (priceInclFocused) return;
    const next = incl > 0 ? incl.toFixed(2) : '';
    setPriceInclInput(prev => (prev === next ? prev : next));
  }, [incl, priceInclFocused]);

  // Switching to manual clears the field so the operator types their own
  // figure rather than editing a derived one they may not notice is derived.
  const setWeightSource = (ws: WeightSource) => {
    setWeightModeTouched(true);
    setForm(f => ({
      ...f,
      weightSource: ws,
      weightPerUnitKg: ws === 'manual' ? '' : f.weightPerUnitKg,
    }));
  };

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
    if (!form.weightPerUnitKg)
                                  { flash(form.weightSource === 'manual'
                                      ? 'Weight per unit is required'
                                      : 'Weight per unit could not be calculated — the package size is not readable in litres', false); return; }
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
              {form.stockUnitId && (
                <p className="text-[10px] text-gray-400 mt-0.5">
                  {unitLitres !== null
                    ? `${unitLitres} L · ${(unitLitres * density).toFixed(3)} kg`
                    : 'Not readable as litres'}
                </p>
              )}
            </div>
          </div>

          <div>
            <L t="Internal description / notes" />
            <input className={I} placeholder="Formulation notes, use case…" value={form.description} onChange={ev('description')} />
          </div>

          {/* ── Selected formula: density, then the ingredients ── */}
          <S icon="🧪" title="Formula makeup" />

          {!pickedFormula ? (
            <p className="text-xs text-gray-400">
              Pick a formula in the Formulation column to see its ingredients.
            </p>
          ) : (
            <div className="bg-gray-50 rounded-xl border border-gray-100 overflow-hidden">
              {/* Density sits above the list — it governs every kg/L figure below */}
              <div className="flex items-center justify-between px-3 py-2.5 bg-white border-b border-gray-100">
                <div>
                  <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Density</p>
                  <p className="text-lg font-bold font-mono text-gray-800 leading-none mt-0.5">
                    {density.toFixed(4)} <span className="text-xs font-sans font-normal text-gray-400">kg/L</span>
                  </p>
                </div>
                <div className="text-right">
                  <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Formula</p>
                  <p className="text-xs font-semibold text-gray-700 mt-0.5">{pickedFormula.name}</p>
                  <p className="text-[10px] font-mono text-gray-400">
                    {pickedFormula.code}
                  </p>
                </div>
              </div>

              {numOrNull(pickedFormula.density_kg_per_l) === null && (
                <p className="px-3 py-1.5 text-[10px] text-amber-700 bg-amber-50 border-b border-amber-100 font-semibold">
                  No density recorded on this formula — using 1.0000 kg/L
                </p>
              )}

              {itemsLoading ? (
                <div className="flex items-center gap-2 px-3 py-4 text-xs text-gray-400">
                  <Loader2 className="w-3.5 h-3.5 animate-spin" /> Loading ingredients…
                </div>
              ) : itemsError ? (
                <p className="px-3 py-4 text-xs text-red-600 font-semibold">{itemsError}</p>
              ) : items.length === 0 ? null : (
                <table className="w-full text-xs">
                  <thead className="text-gray-400 uppercase text-[9px] tracking-wider">
                    <tr>
                      <th className="text-left px-3 py-1.5 font-bold">Ingredient</th>
                      <th className="text-right px-2 py-1.5 font-bold w-14">%</th>
                      {!!unitWeightKg && unitWeightKg > 0 && (
                        <th className="text-right px-3 py-1.5 font-bold w-20">Cost</th>
                      )}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100">
                    {items.map(it => {
                      const m   = materialOf(it.raw_material_id);
                      const pct = numOrNull(it.percentage) ?? 0;
                      const bal = Number(it.is_balance) === 1;

                      return (
                        <tr key={it.id} className="bg-white">
                          <td className="px-3 py-1.5 text-gray-700">
                            {m?.name ?? `#${it.raw_material_id}`}
                            {bal && (
                              <span className="ml-1.5 text-[9px] font-bold text-blue-600">BAL</span>
                            )}
                          </td>
                          <td className="px-2 py-1.5 text-right font-mono text-gray-600">
                            {pct.toFixed(2)}
                          </td>
                          {!!unitWeightKg && unitWeightKg > 0 && (
                            <td className="px-3 py-1.5 text-right font-mono font-semibold text-gray-800">
                              {lineCostPerUnit(it) === null
                                ? <span className="text-amber-600 font-sans font-medium text-[10px]">no price</span>
                                : lineCostPerUnit(it)!.toFixed(4)}
                            </td>
                          )}
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              )}

              {/* A total missing even one ingredient understates the cost, so
                  nothing is shown until every ingredient carries a price */}
              {items.length > 0 && unpricedCount > 0 && (
                <div className="border-t border-gray-200 bg-white px-3 py-2.5">
                  <p className="text-[10px] text-amber-700 bg-amber-50 border border-amber-100 rounded px-2 py-1.5 font-semibold">
                    {unpricedCount} ingredient{unpricedCount > 1 ? 's have' : ' has'} no cost on
                    file — add {unpricedCount > 1 ? 'their prices' : 'its price'} to see the recipe cost
                  </p>
                </div>
              )}

              {items.length > 0 && unpricedCount === 0 && batchCost > 0 && (
                <div className="border-t border-gray-200 bg-white px-3 py-2.5">
                  <div className="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                      <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Cost per kg
                      </p>
                      <p className="text-sm font-bold font-mono text-gray-800 leading-none mt-0.5">
                        R {costPerKg.toFixed(4)}
                      </p>
                    </div>
                    <div className="text-right">
                      <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        Cost per unit
                      </p>
                      <p className="text-lg font-bold font-mono text-blue-700 leading-none mt-0.5">
                        {recipeCostUnit === null ? '—' : `R ${recipeCostUnit.toFixed(2)}`}
                      </p>
                    </div>
                  </div>

                  {recipeCostUnit !== null && (
                    <button
                      onClick={() => set('rawMaterialCost')(recipeCostUnit.toFixed(2))}
                      className="w-full mt-2 text-[10px] font-bold text-blue-600 hover:text-blue-800 border border-blue-200 hover:border-blue-400 rounded py-1.5 transition-colors"
                    >
                      Use as cost price
                    </button>
                  )}
                </div>
              )}
            </div>
          )}
        </div>

        {/* ═══════════════════════════
            COL 2 — FORMULATION + PRODUCTION
        ═══════════════════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-4 overflow-y-auto flex flex-col gap-3">
          <S icon="⚗️" title="Formulation" />

          {/* Formulas — list of formula names */}
          <div>
            <L t="Formulas" />
            <div className="relative">
              <select
                className={`${I} appearance-none pr-7`}
                value={form.formulaCode}
                onChange={ev('formulaCode')}
              >
                <option value="">Select formula…</option>
                {formulas.filter(f => f.code).map(f => (
                  <option key={f.id} value={f.code}>{f.name}</option>
                ))}
              </select>
              <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-500 pointer-events-none" />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-2">
            {pickedFormula && (
              <div>
                <L t="Formula code" />
                <input className={CI} readOnly value={form.formulaCode} />
              </div>
            )}
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
              {(['manual', 'formula'] as WeightSource[])
                .filter(ws => ws === 'manual' || !!pickedFormula)
                .map(ws => (
                <button
                  key={ws}
                  onClick={() => setWeightSource(ws)}
                  className={`flex-1 py-1.5 rounded-lg text-xs font-bold border transition-all ${
                    form.weightSource === ws
                      ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                      : 'bg-white text-gray-500 border-gray-200 hover:border-blue-300'
                  }`}
                >
                  {ws === 'manual' ? '✎ Set manually' : '⚗ From container'}
                </button>
              ))}
            </div>
            <div>
              <L t="Weight per unit (kg)" required />
              <input
                className={
                  form.weightSource === 'manual'
                    ? RI(missing(form.weightPerUnitKg))
                    : (submitted && !form.weightPerUnitKg ? RI(true) : CI)
                }
                type="number"
                step="0.001"
                placeholder={form.weightSource === 'manual' ? '12' : 'Select a package / unit'}
                readOnly={form.weightSource !== 'manual'}
                value={form.weightPerUnitKg}
                onChange={ev('weightPerUnitKg')}
              />
              {form.weightSource === 'manual' ? (
                <p className="text-[10px] text-gray-400 mt-1">Typed in, saved as entered</p>
              ) : (
                <>
                  <p className="text-[10px] text-gray-400 mt-1">
                    {unitLitres !== null
                      ? `${unitLitres} L × ${density.toFixed(4)} kg/L`
                      : 'Package size could not be read as litres'}
                  </p>
                  {unitLitres !== null && !pickedFormula && (
                    <p className="text-[10px] text-amber-600 font-semibold mt-0.5">
                      No formula picked — using 1.0000 kg/L
                    </p>
                  )}
                </>
              )}
            </div>
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
            <div>
              <L t="Selling excl. VAT (R)" />
              <input
                className={I}
                type="number"
                step="0.01"
                placeholder="0.00"
                value={priceInput}
                disabled={cost <= 0}
                onFocus={() => setPriceFocused(true)}
                onBlur={() => setPriceFocused(false)}
                onChange={e => setSellingExcl(e.target.value)}
              />
            </div>
            <div>
              <L t="Selling incl. VAT (R)" />
              <input
                className={I}
                type="number"
                step="0.01"
                placeholder="0.00"
                value={priceInclInput}
                disabled={cost <= 0}
                onFocus={() => setPriceInclFocused(true)}
                onBlur={() => setPriceInclFocused(false)}
                onChange={e => setSellingIncl(e.target.value)}
              />
            </div>
            <p className="col-span-2 text-[10px] text-gray-400 -mt-1">
              {cost > 0
                ? 'Set any one of markup, excl. or incl. — the others follow'
                : 'Enter a cost price first'}
            </p>
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