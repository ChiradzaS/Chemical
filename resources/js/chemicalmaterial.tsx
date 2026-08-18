import React, { useState, useEffect, useCallback } from 'react';
import {
  FlaskConical, Loader2, Plus, ChevronDown, Pencil, X, AlertTriangle, Check,
  History, ArrowDownCircle, ArrowUpCircle,
} from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:     string;
    ChemicalUnitType:     { id: string; name: string; value?: string }[];
    ChemicalMaterialType: { id: string; name: string }[];
  }
}

interface RawMaterial {
  id:             number | null;
  code:           string;
  name:           string;
  material_type:  string;
  uom:            string;
  cost_per_kg:    number | string | null;   // MySQL hands DECIMAL back as a string
  stock_on_hand:      number;
  prv_stock_on_hand?: number;   // balance before the last movement, like stocks.prvqnt
  reorder_level:  number;
  allow_negative: boolean | number;
  is_active:      boolean | number;
  notes:          string;
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

const BLANK: RawMaterial = {
  id: null,
  code: '',
  name: '',
  material_type: '',
  uom: '',
  cost_per_kg: null,
  stock_on_hand: 0,
  prv_stock_on_hand: 0,
  reorder_level: 0,
  allow_negative: false,
  is_active: true,
  notes: '',
};

const truthy = (v: any) => v === true || Number(v) === 1;

// quantities carry 2 decimals — chemicals get issued in fractions of a kg
const num = (v: any) => {
  const n = Number(String(v ?? '').trim());
  return Number.isFinite(n) ? n : 0;
};

// display helper — older rows may still hold NULL, so keep tolerating it here
const money = (v: any) =>
  v === null || v === undefined || v === '' || !Number.isFinite(Number(v))
    ? null
    : Number(v);

// "28.5000" from the database → "28.5" for a 2dp input box
const toCostInput = (v: any) => {
  const n = money(v);
  return n === null ? '' : String(Math.round(n * 100) / 100);
};

// cost is required on save: a real, positive amount
const costOf = (raw: string) => {
  const c = raw.trim();
  if (c === '' || c === '.') return null;
  const n = Number(c);
  return Number.isFinite(n) && n > 0 ? n : null;
};


// ════════════════════════════════════════════════════════════════════════════
// Movement history for one material
// ════════════════════════════════════════════════════════════════════════════

interface TransRow {
  id:            number;
  trans_date:    string;
  created_at?:   string;
  doc_type:      number | string;
  doc_no?:       string | null;
  supplier_name?: string | null;
  qty_in:        number | string;
  qty_out:       number | string;
  balance_after: number | string | null;
  unit_cost:     number | string | null;
  notes?:        string | null;
  user_name?:    string | null;
}

/* The same numbers stocks_trans uses, so one document reads the same on both
   ledgers. Anything unmapped still shows its raw number rather than a blank. */
const DOC_TYPES: Record<string, { label: string; tone: string }> = {
  '103': { label: 'Receipt',     tone: 'bg-emerald-50 text-emerald-700' },
  '104': { label: 'Return out',  tone: 'bg-orange-50 text-orange-700'   },
  '105': { label: 'Production',  tone: 'bg-sky-50 text-sky-700'         },
  '111': { label: 'Adjustment',  tone: 'bg-violet-50 text-violet-700'   },
  '112': { label: 'Write-off',   tone: 'bg-red-50 text-red-700'         },
};

const docLabel = (t: any) => DOC_TYPES[String(t)]?.label ?? `Type ${t}`;
const docTone  = (t: any) => DOC_TYPES[String(t)]?.tone  ?? 'bg-slate-100 text-slate-600';

const MaterialHistoryModal: React.FC<{
  material: RawMaterial;
  onClose: () => void;
}> = ({ material, onClose }) => {
  const [rows, setRows]       = useState<TransRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState<string | null>(null);
  const [filter, setFilter]   = useState<string>('');   // '' = every movement

  useEffect(() => {
    (async () => {
      setLoading(true);
      setError(null);
      try {
        const payload     = { raw_material_id: material.id, limit: 300 };
        const encodedData = encodeURIComponent(JSON.stringify(payload));
        const response    = await axios.get(`${API_BASE}/raw-materials/history?data=${encodedData}`);
        if (response.data?.status === 'error') throw new Error(response.data.message);
        setRows(Array.isArray(response.data) ? response.data : []);
      } catch (e: any) {
        console.error('Error fetching material history:', e);
        setError(e.message || 'Could not load the history');
        setRows([]);
      } finally {
        setLoading(false);
      }
    })();
  }, [material.id]);

  // escape closes, like every other popup on the system
  useEffect(() => {
    const key = (e: KeyboardEvent) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', key);
    return () => document.removeEventListener('keydown', key);
  }, [onClose]);

  const shown    = filter === '' ? rows : rows.filter(r => String(r.doc_type) === filter);
  const totalIn  = rows.reduce((s, r) => s + num(r.qty_in), 0);
  const totalOut = rows.reduce((s, r) => s + num(r.qty_out), 0);
  const spend    = rows.reduce((s, r) => s + num(r.qty_in) * (money(r.unit_cost) ?? 0), 0);

  // which doc types actually appear, so the filter never offers an empty view
  const present = Array.from(new Set(rows.map(r => String(r.doc_type))));

  return (
    <div className="fixed inset-0 z-50 bg-slate-900/60 flex items-start justify-center p-4 overflow-y-auto">
      <div className="bg-white rounded-xl shadow-2xl w-full max-w-6xl mt-8 mb-8">

        {/* ── Header ── */}
        <div className="flex items-center gap-3 px-5 py-4 border-b border-slate-100">
          <History size={20} className="text-sky-500" />
          <div>
            <h3 className="font-semibold text-slate-800">Movement history</h3>
            <p className="text-xs text-slate-400">
              <span className="font-mono">{material.code}</span> — {material.name}
            </p>
          </div>
          <button onClick={onClose} className="ml-auto text-slate-400 hover:text-red-600">
            <X size={20} />
          </button>
        </div>

        {/* ── Totals ── */}
        <div className="grid grid-cols-2 md:grid-cols-4 divide-x divide-slate-100 border-b border-slate-100">
          <div className="px-5 py-3">
            <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">On hand</p>
            <p className="text-lg font-bold text-slate-800 tabular-nums">
              {num(material.stock_on_hand).toFixed(2)} {material.uom}
            </p>
          </div>
          <div className="px-5 py-3">
            <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Total in</p>
            <p className="text-lg font-bold text-emerald-700 tabular-nums flex items-center gap-1.5">
              <ArrowDownCircle size={15} /> {totalIn.toFixed(2)}
            </p>
          </div>
          <div className="px-5 py-3">
            <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Total out</p>
            <p className="text-lg font-bold text-red-600 tabular-nums flex items-center gap-1.5">
              <ArrowUpCircle size={15} /> {totalOut.toFixed(2)}
            </p>
          </div>
          <div className="px-5 py-3">
            <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Total spent</p>
            <p className="text-lg font-bold text-slate-800 tabular-nums">R {spend.toFixed(2)}</p>
          </div>
        </div>

        {/* ── Filter ── */}
        {present.length > 1 && (
          <div className="flex items-center gap-2 flex-wrap px-5 py-2.5 border-b border-slate-100">
            <button
              onClick={() => setFilter('')}
              className={`px-3 py-1 rounded-full text-xs font-semibold border transition-colors ${
                filter === '' ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-400'
              }`}
            >
              All ({rows.length})
            </button>
            {present.map(t => (
              <button
                key={t}
                onClick={() => setFilter(t)}
                className={`px-3 py-1 rounded-full text-xs font-semibold border transition-colors ${
                  filter === t ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-500 border-slate-200 hover:border-slate-400'
                }`}
              >
                {docLabel(t)} ({rows.filter(r => String(r.doc_type) === t).length})
              </button>
            ))}
          </div>
        )}

        {/* ── Rows ── */}
        {loading ? (
          <div className="text-center py-20 text-slate-400">Loading history…</div>
        ) : error ? (
          <div className="text-center py-20">
            <AlertTriangle size={36} className="mx-auto text-amber-400 mb-3" />
            <p className="text-slate-600 font-medium">{error}</p>
          </div>
        ) : rows.length === 0 ? (
          <div className="text-center py-20">
            <History size={40} className="mx-auto text-slate-300 mb-3" />
            <p className="text-slate-600 font-medium">Nothing has moved yet</p>
            <p className="text-slate-400 text-sm mt-1">Receipts, production and adjustments all show here</p>
          </div>
        ) : (
          <div className="overflow-x-auto max-h-[60vh] overflow-y-auto">
            <table className="w-full text-sm">
              <thead className="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide sticky top-0">
                <tr>
                  <th className="text-left px-5 py-2.5 font-semibold">Date</th>
                  <th className="text-left px-3 py-2.5 font-semibold">Type</th>
                  <th className="text-left px-3 py-2.5 font-semibold">Reference</th>
                  <th className="text-left px-3 py-2.5 font-semibold">Supplier</th>
                  <th className="text-right px-3 py-2.5 font-semibold">In</th>
                  <th className="text-right px-3 py-2.5 font-semibold">Out</th>
                  <th className="text-right px-3 py-2.5 font-semibold">Balance</th>
                  <th className="text-right px-3 py-2.5 font-semibold">Cost/kg</th>
                  <th className="text-left px-3 py-2.5 font-semibold">Notes</th>
                  <th className="text-left px-5 py-2.5 font-semibold">By</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {shown.map(r => {
                  const qtyIn  = num(r.qty_in);
                  const qtyOut = num(r.qty_out);
                  const bal    = money(r.balance_after);
                  const cost   = money(r.unit_cost);

                  return (
                    <tr key={r.id} className="hover:bg-slate-50">
                      <td className="px-5 py-2.5 text-slate-600 whitespace-nowrap">{r.trans_date}</td>
                      <td className="px-3 py-2.5">
                        <span className={`inline-block px-2 py-0.5 rounded text-[11px] font-bold ${docTone(r.doc_type)}`}>
                          {docLabel(r.doc_type)}
                        </span>
                      </td>
                      <td className="px-3 py-2.5 text-slate-500 text-xs">{r.doc_no || '—'}</td>
                      <td className="px-3 py-2.5 text-slate-600">{r.supplier_name || '—'}</td>
                      <td className="px-3 py-2.5 text-right tabular-nums font-semibold text-emerald-700">
                        {qtyIn > 0 ? qtyIn.toFixed(2) : ''}
                      </td>
                      <td className="px-3 py-2.5 text-right tabular-nums font-semibold text-red-600">
                        {qtyOut > 0 ? qtyOut.toFixed(2) : ''}
                      </td>
                      <td className={`px-3 py-2.5 text-right tabular-nums font-bold ${bal !== null && bal < 0 ? 'text-red-600' : 'text-slate-900'}`}>
                        {bal === null ? '—' : bal.toFixed(2)}
                      </td>
                      <td className="px-3 py-2.5 text-right tabular-nums text-slate-500">
                        {cost === null ? '—' : `R ${cost.toFixed(2)}`}
                      </td>
                      <td className="px-3 py-2.5 text-slate-400 text-xs max-w-xs truncate" title={r.notes || ''}>
                        {r.notes || '—'}
                      </td>
                      <td className="px-5 py-2.5 text-slate-500 text-xs">{r.user_name || '—'}</td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
};

const RawMaterialList: React.FC = () => {
  // ── Blade-injected globals ────────────────────────────────────────────────
  const unitTypes     = window.ChemicalUnitType     || [];
  const chemicalTypes = window.ChemicalMaterialType || [];

  const [materials, setMaterials] = useState<RawMaterial[]>([]);
  const [loading,   setLoading]   = useState(true);
  const [saving,    setSaving]    = useState(false);
  const [busy,      setBusy]      = useState<number | null>(null);
  const [toast,     setToast]     = useState<{ msg: string; ok: boolean } | null>(null);
  const [historyFor, setHistoryFor] = useState<RawMaterial | null>(null);

  const [form,      setForm]      = useState<RawMaterial>({ ...BLANK, uom: unitTypes[0]?.name ?? '' });
  const [costInput, setCostInput] = useState('');   // kept as text so decimals type cleanly
  const [errors,    setErrors]    = useState<Record<string, string>>({});

  const editing = form.id !== null;

  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(null), 3200);
    return () => clearTimeout(t);
  }, [toast]);

  // ── Fetch ─────────────────────────────────────────────────────────────────
  const fetchMaterials = useCallback(async () => {
    setLoading(true);
    try {
      const payload     = { search: '', include_inactive: 1 };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/raw-materials/list?data=${encodedData}`);
      const data        = response.data;
      setMaterials(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Error fetching raw materials:', error);
      setMaterials([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchMaterials(); }, [fetchMaterials]);

  // ── Form helpers ──────────────────────────────────────────────────────────
  const clearError = (key: string) =>
    setErrors(e => { const { [key]: _drop, ...rest } = e; return rest; });

  const setField = (key: keyof RawMaterial, value: any) => {
    setForm(f => ({ ...f, [key]: value }));
    clearError(key as string);
  };

  // sanitise rather than reject — a rejecting guard can trap the field on a
  // value the regex doesn't like, and then no keystroke ever gets through
  const setCost = (raw: string) => {
    const cleaned = raw
      .replace(/[^\d.]/g, '')              // drop letters, R, spaces, commas
      .replace(/(\..*)\./g, '$1')          // keep only the first dot
      .replace(/^(\d*\.\d{2})\d+$/, '$1'); // clamp to two decimals
    setCostInput(cleaned);
    clearError('cost_per_kg');
  };

  const resetForm = () => {
    setForm({ ...BLANK, uom: form.uom || unitTypes[0]?.name || '', material_type: form.material_type });
    setCostInput('');
    setErrors({});
  };

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.code.trim())   e.code          = 'Enter a code';
    if (!form.name.trim())   e.name          = 'Enter a name';
    if (!form.material_type) e.material_type = 'Choose a type';
    if (!form.uom)           e.uom           = 'Choose a unit';

    const c = costInput.trim();
    if (c === '' || c === '.')      e.cost_per_kg = 'Enter a cost per kg';
    else if (costOf(c) === null)    e.cost_per_kg = 'Cost must be more than 0';

    return e;
  };

  // every required field filled — drives the save button
  const canSave = !!form.code.trim()
               && !!form.name.trim()
               && !!form.material_type
               && !!form.uom
               && costOf(costInput) !== null;

  // ── Save ──────────────────────────────────────────────────────────────────
  const save = async () => {
    const e = validate();
    setErrors(e);
    if (Object.keys(e).length) {
      setToast({ msg: 'Fill in every required field', ok: false });
      return;
    }

    setSaving(true);
    try {
      const body = {
        ...form,
        code:          form.code.trim(),
        name:          form.name.trim(),
        cost_per_kg:   costOf(costInput),
        stock_on_hand: num(form.stock_on_hand),
        reorder_level: num(form.reorder_level),
      };
      const payload = editing
        ? { ...body, stock_on_hand: undefined, prv_stock_on_hand: undefined }
        : { ...body, prv_stock_on_hand: undefined };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/raw-materials/save?data=${encodedData}`);

      if (response.data?.status === 'error') throw new Error(response.data.message);

      setToast({ msg: editing ? `${response.data.name} updated` : `${response.data.name} added`, ok: true });
      resetForm();
      fetchMaterials();
    } catch (error: any) {
      setToast({ msg: error.response?.data?.message || error.message || 'Could not save.', ok: false });
      console.error(error);
    } finally {
      setSaving(false);
    }
  };

  // ── Activate / deactivate ─────────────────────────────────────────────────
  const toggleActive = async (m: RawMaterial) => {
    setBusy(m.id);
    try {
      const encodedData = encodeURIComponent(JSON.stringify({ id: m.id }));
      const response    = await axios.get(`${API_BASE}/raw-materials/toggle?data=${encodedData}`);

      if (response.data?.status === 'error') throw new Error(response.data.message);

      setToast({
        msg: truthy(response.data.is_active) ? 'Material reactivated' : 'Material deactivated',
        ok: true,
      });
      fetchMaterials();
    } catch (error: any) {
      setToast({ msg: error.response?.data?.message || 'Could not update that material.', ok: false });
      console.error(error);
    } finally {
      setBusy(null);
    }
  };

  const startEdit = (m: RawMaterial) => {
    const cost = toCostInput(m.cost_per_kg);
    setForm(m);
    setCostInput(cost);
    // rows saved before cost was required arrive empty — flag it straight away
    setErrors(cost === '' ? { cost_per_kg: 'This material still needs a cost' } : {});
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const activeCount  = materials.filter(m => truthy(m.is_active)).length;
  const missingCosts = materials.filter(m => truthy(m.is_active) && money(m.cost_per_kg) === null).length;

  const inp = "bg-white border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none";
  const bad = "bg-white border border-red-400 rounded-lg px-3 py-2 w-full text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100 focus:outline-none";
  const sel = `${inp} appearance-none pr-8`;
  const lbl = "block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5";
  const req = <span className="text-red-500 ml-0.5">*</span>;

  return (
    <div className="min-h-screen bg-slate-100 p-6">
      <div className="w-full space-y-6">

        {/* ── Page header ── */}
        <div className="bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg">
          <div className="flex items-center gap-3">
            <FlaskConical className="text-sky-400 w-7 h-7" />
            <div>
              <h1 className="text-2xl font-bold text-white">Raw Materials</h1>
              <p className="text-slate-400 text-sm">Ingredients available to formulas</p>
            </div>
          </div>
          <span className="bg-sky-500 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow">
            {activeCount} active
          </span>
        </div>

        {/* ── Materials still missing a cost ── */}
        {!loading && missingCosts > 0 && (
          <div className="flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm">
            <AlertTriangle size={16} className="text-amber-500 shrink-0" />
            {missingCosts === 1
              ? '1 active material has no cost per kg. Edit it to add one.'
              : `${missingCosts} active materials have no cost per kg. Edit them to add one.`}
          </div>
        )}

        {/* ── Add / edit ── */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
          <div className="flex items-center gap-2 mb-4">
            {editing ? <Pencil size={18} className="text-sky-500" /> : <Plus size={18} className="text-sky-500" />}
            <h2 className="font-semibold text-slate-800">
              {editing ? `Edit ${form.name || 'material'}` : 'Add a raw material'}
            </h2>
            {editing && (
              <button
                onClick={resetForm}
                className="ml-auto flex items-center gap-1 text-slate-400 hover:text-red-600 text-sm"
              >
                <X size={15} /> Cancel
              </button>
            )}
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

            <div>
              <label className={lbl}>Code{req}</label>
              <input
                type="text"
                value={form.code}
                onChange={e => setField('code', e.target.value.toUpperCase())}
                placeholder="SLES-70"
                className={errors.code ? bad : inp}
              />
              {errors.code && <p className="text-xs text-red-600 mt-1">{errors.code}</p>}
            </div>

            <div className="lg:col-span-2">
              <label className={lbl}>Name{req}</label>
              <input
                type="text"
                value={form.name}
                onChange={e => setField('name', e.target.value)}
                placeholder="Sodium Laureth Sulphate 70%"
                className={errors.name ? bad : inp}
              />
              {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
            </div>

            <div>
              <label className={lbl}>Chemical type{req}</label>
              <div className="relative">
                <select
                  value={form.material_type}
                  onChange={e => setField('material_type', e.target.value)}
                  className={errors.material_type ? `${bad} appearance-none pr-8` : sel}
                >
                  <option value="">Select type</option>
                  {chemicalTypes.map(t => <option key={t.id} value={t.name}>{t.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
              {errors.material_type && <p className="text-xs text-red-600 mt-1">{errors.material_type}</p>}
            </div>

            <div>
              <label className={lbl}>Unit{req}</label>
              <div className="relative">
                <select
                  value={form.uom}
                  onChange={e => setField('uom', e.target.value)}
                  className={errors.uom ? `${bad} appearance-none pr-8` : sel}
                >
                  <option value="">Select</option>
                  {unitTypes.map(u => <option key={u.id} value={u.name}>{u.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
              {errors.uom && <p className="text-xs text-red-600 mt-1">{errors.uom}</p>}
            </div>

            <div>
              <label className={lbl}>Cost per kg{req}</label>
              <div className="relative">
                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">R</span>
                <input
                  type="text"
                  inputMode="decimal"
                  value={costInput}
                  onChange={e => setCost(e.target.value)}
                  onBlur={() => {
                    if (costInput.trim() === '' || costInput.trim() === '.') {
                      setErrors(err => ({ ...err, cost_per_kg: 'Enter a cost per kg' }));
                    }
                  }}
                  placeholder="0.00"
                  className={`${errors.cost_per_kg ? bad : inp} pl-7`}
                />
              </div>
              {errors.cost_per_kg
                ? <p className="text-xs text-red-600 mt-1">{errors.cost_per_kg}</p>
                : <p className="text-xs text-slate-400 mt-1">Used to cost formulas</p>}
            </div>

            <div>
              <label className={lbl}>Opening stock</label>
              <input
                type="number"
                step="0.01"
                min="0"
                value={form.stock_on_hand}
                disabled={editing}
                onChange={e => setField('stock_on_hand', num(e.target.value))}
                className={`${inp} disabled:bg-slate-100 disabled:text-slate-400`}
              />
              <p className="text-xs text-slate-400 mt-1">
                {editing ? 'Change via stock adjustment' : 'Two decimals'}
              </p>
            </div>

            <div>
              <label className={lbl}>Reorder level</label>
              <input
                type="number"
                step="0.01"
                min="0"
                value={form.reorder_level}
                onChange={e => setField('reorder_level', num(e.target.value))}
                className={inp}
              />
              <p className="text-xs text-slate-400 mt-1">0 means no warning</p>
            </div>

            <div className="lg:col-span-4">
              <label className={lbl}>Notes</label>
              <input
                type="text"
                value={form.notes}
                onChange={e => setField('notes', e.target.value)}
                placeholder="Supplier, handling, storage"
                className={inp}
              />
            </div>
          </div>

          <div className="flex items-center justify-between gap-4 flex-wrap mt-5 pt-4 border-t border-slate-100">
            <label className="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
              <input
                type="checkbox"
                checked={truthy(form.allow_negative)}
                onChange={e => setField('allow_negative', e.target.checked)}
                className="w-4 h-4 accent-sky-500"
              />
              Allow production when stock runs out
            </label>

            <div className="flex items-center gap-3">
              {!canSave && (
                <span className="text-xs text-slate-400">Code, name, type, unit and cost are required</span>
              )}
              <button
                onClick={save}
                disabled={saving || !canSave}
                className="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Check className="w-4 h-4" strokeWidth={3} />}
                {saving ? 'Saving…' : editing ? 'Save changes' : 'Add material'}
              </button>
            </div>
          </div>
        </div>

        {/* ── List ── */}
        {loading ? (
          <div className="text-center py-16 text-slate-400">Loading materials…</div>
        ) : materials.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-xl shadow-sm border border-slate-200">
            <FlaskConical size={44} className="mx-auto text-slate-300 mb-4" />
            <p className="text-slate-600 font-medium">No materials yet</p>
            <p className="text-slate-400 text-sm mt-1">Add your first ingredient above</p>
          </div>
        ) : (
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide">
                  <tr>
                    <th className="text-left px-4 py-2.5 font-semibold">Code</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Name</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Chemical type</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Prev qty</th>
                    <th className="text-right px-3 py-2.5 font-semibold">On hand</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Reorder at</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Cost/kg</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Notes</th>
                    <th className="w-56 px-3 py-2.5"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {materials.map(m => {
                    const active  = truthy(m.is_active);
                    const stock   = num(m.stock_on_hand);
                    const prev    = num(m.prv_stock_on_hand);
                    const moved   = Math.round((stock - prev) * 100) / 100;
                    const reorder = num(m.reorder_level);
                    const cost    = money(m.cost_per_kg);
                    const low     = reorder > 0 && stock <= reorder;
                    const busyMe  = busy === m.id;
                    const noCost  = cost === null || cost <= 0;

                    return (
                      <tr
                        key={m.id}
                        className={active ? (low ? 'bg-amber-50/60' : 'hover:bg-slate-50') : 'bg-slate-50 opacity-60'}
                      >
                        <td className="px-4 py-3 font-mono text-xs text-slate-800">{m.code}</td>
                        <td className="px-3 py-3 font-medium text-slate-800">
                          {m.name}
                          {!active && (
                            <span className="ml-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-300 text-slate-700">
                              INACTIVE
                            </span>
                          )}
                        </td>
                        <td className="px-3 py-3 text-slate-600">{m.material_type || '—'}</td>
                        {/* what the balance was before the last movement, and by how much it moved */}
                        <td className="px-3 py-3 text-right tabular-nums text-slate-400">
                          {prev.toFixed(2)}
                          {moved !== 0 && (
                            <span className={`ml-1.5 text-[10px] font-semibold ${moved > 0 ? 'text-emerald-600' : 'text-red-500'}`}>
                              {moved > 0 ? '+' : ''}{moved.toFixed(2)}
                            </span>
                          )}
                        </td>
                        <td className={`px-3 py-3 text-right font-semibold ${low ? 'text-amber-700' : 'text-slate-900'}`}>
                          <span className="inline-flex items-center gap-1.5 justify-end">
                            {low && <AlertTriangle size={13} className="text-amber-500" />}
                            {stock.toFixed(2)} {m.uom}
                          </span>
                        </td>
                        <td className="px-3 py-3 text-right text-slate-500">
                          {reorder > 0 ? reorder.toFixed(2) : '—'}
                        </td>
                        <td className="px-3 py-3 text-right tabular-nums">
                          {noCost
                            ? <span className="text-amber-700 font-semibold text-xs">Cost needed</span>
                            : <span className="text-slate-600">R {cost.toFixed(2)}</span>}
                        </td>
                        <td className="px-3 py-3 text-slate-400 text-xs max-w-xs truncate">{m.notes || '—'}</td>
                        <td className="px-3 py-3 text-right whitespace-nowrap">
                          <button
                            onClick={() => startEdit(m)}
                            className="text-sky-600 hover:text-sky-800 font-semibold text-xs px-2 py-1"
                          >
                            Edit
                          </button>
                          <button
                            onClick={() => setHistoryFor(m)}
                            className="inline-flex items-center gap-1 text-slate-600 hover:text-slate-900 font-semibold text-xs px-2 py-1"
                          >
                            <History size={12} /> History
                          </button>
                          <button
                            onClick={() => toggleActive(m)}
                            disabled={busyMe}
                            className="text-slate-500 hover:text-slate-800 font-semibold text-xs px-2 py-1 disabled:opacity-50"
                          >
                            {busyMe
                              ? <Loader2 className="w-3.5 h-3.5 animate-spin inline" />
                              : active ? 'Deactivate' : 'Activate'}
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>

      {historyFor && (
        <MaterialHistoryModal material={historyFor} onClose={() => setHistoryFor(null)} />
      )}

      {/* ── Toast ── */}
      {toast && (
        <div
          className={`fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-xl text-white font-semibold text-sm ${
            toast.ok ? 'bg-emerald-600' : 'bg-red-600'
          }`}
        >
          {toast.msg}
        </div>
      )}
    </div>
  );
};

export default RawMaterialList;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<RawMaterialList />);
}