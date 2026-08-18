import React, { useState, useEffect, useCallback } from 'react';
import {
  FlaskConical, Loader2, Plus, Trash2, ChevronDown, Check, Package, Lock, X,
} from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:     string;
    chemicalTypesData: { id: string; name: string }[];
  }
}

interface FormulaHeader {
  id:               number | null;
  code:             string;
  name:             string;
  chemical_type:    string;
  base_batch_qty:   number;
  density_kg_per_l: string;   // text so "1." types cleanly mid-entry
  status:           string;
  notes:            string;
}

interface Material {
  id:            number;
  code:          string;
  name:          string;
  material_type: string | null;
  uom:           string;
  stock_on_hand: number;
  is_active?:    number | boolean;
}

interface Line {
  key:             string;
  raw_material_id: number | null;
  material_type:   string;
  percentage:      number;
  quantity:        number;
  uom:             string;
  entry_mode:      'percent' | 'quantity';
  is_balance:      boolean;
  instruction:     string;
}

interface ProductRow { id: number; name: string; sku?: string | null }

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

// Batch size is fixed — every formula is written against a 1000 kg batch
const BATCH_QTY = 1000;
const NEW_OPTION = '__new__';

const BLANK_HEADER: FormulaHeader = {
  id: null,
  code: '',
  name: '',
  chemical_type: '',
  base_batch_qty: BATCH_QTY,
  density_kg_per_l: '',   // no default — it has to be measured, not assumed
  status: 'active',
  notes: '',
};

const newKey = () => `l${Date.now()}${Math.random().toString(36).slice(2, 7)}`;

const newLine = (): Line => ({
  key: newKey(),
  raw_material_id: null,
  material_type: '',
  percentage: 0,
  quantity: 0,
  uom: 'kg',
  entry_mode: 'percent',
  is_balance: false,
  instruction: '',
});

const num = (v: any) => Number(v ?? 0) || 0;

// percentages keep 4 places: 0.01 kg of a 1000 kg batch is 0.001%
const r4 = (n: number) => Math.round(n * 10000) / 10000;

// quantities keep 2, matching raw_materials.stock_on_hand
const r2 = (n: number) => Math.round(n * 100) / 100;

const active = (m: Material) => m.is_active === undefined || Number(m.is_active) === 1;

// "1.0200" from the database → "1.02" for the input box.
// A formula saved before density was recorded comes back blank, not 1 — a
// wrong density silently mis-converts every litre figure downstream
const toDensityInput = (v: any) => {
  const n = Number(v);
  return Number.isFinite(n) && n > 0 ? String(Math.round(n * 10000) / 10000) : '';
};

// a row only counts once a material has been picked
const isFilled = (l: Line) => l.raw_material_id !== null;

const apiGet = async (route: string, payload: Record<string, unknown> = {}) => {
  const encodedData = encodeURIComponent(JSON.stringify(payload));
  const response = await axios.get(`${API_BASE}/${route}?data=${encodedData}`);
  if (response.data?.status === 'error') throw new Error(response.data.message);
  return response.data;
};

const FormulaBuilder: React.FC = () => {
  const chemicalTypes = window.chemicalTypesData || [];

  const [formulas,  setFormulas]  = useState<FormulaHeader[]>([]);
  const [materials, setMaterials] = useState<Material[]>([]);
  const [products,  setProducts]  = useState<ProductRow[]>([]);

  const [header, setHeader] = useState<FormulaHeader>(BLANK_HEADER);
  const [lines,  setLines]  = useState<Line[]>([newLine()]);

  const [loading, setLoading] = useState(true);
  const [saving,  setSaving]  = useState(false);
  const [toast,   setToast]   = useState<{ msg: string; ok: boolean } | null>(null);

  // name prompt — opened by "Add new formula"
  const [prompt, setPrompt] = useState<{ name: string; code: string } | null>(null);

  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(null), 3200);
    return () => clearTimeout(t);
  }, [toast]);

  // ── Initial load ─────────────────────────────────────────────────────────
  const loadLookups = useCallback(async () => {
    setLoading(true);
    try {
      const [f, m] = await Promise.all([
        axios.get(`${API_BASE}/formulas/list`),
        axios.get(`${API_BASE}/formulas/materials`),
      ]);
      setFormulas(Array.isArray(f.data) ? f.data : []);
      setMaterials(Array.isArray(m.data) ? m.data : []);
    } catch (error) {
      console.error('Error loading formula lookups:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { loadLookups(); }, [loadLookups]);

  // ── Dropdown → load a formula, or open the new-name prompt ───────────────
  const onSelectChange = (value: string) => {
    if (value === NEW_OPTION) {
      setPrompt({ name: '', code: '' });
      return;
    }
    if (!value) return;
    selectFormula(value);
  };

  const selectFormula = async (id: string) => {
    try {
      const data = await apiGet('formulas/show', { id: Number(id) });

      setHeader({
        id:               data.formula.id,
        code:             data.formula.code ?? '',
        name:             data.formula.name ?? '',
        chemical_type:    data.formula.chemical_type ?? '',
        base_batch_qty:   BATCH_QTY,
        density_kg_per_l: toDensityInput(data.formula.density_kg_per_l),
        status:           data.formula.status ?? 'active',
        notes:            data.formula.notes ?? '',
      });

      const loaded: Line[] = (data.items ?? []).map((i: any) => ({
        key:             `l${i.id}`,
        raw_material_id: Number(i.raw_material_id),
        material_type:   i.material_type ?? '',
        percentage:      r4(num(i.percentage)),
        quantity:        r2(BATCH_QTY * num(i.percentage) / 100),
        uom:             i.uom ?? 'kg',
        entry_mode:      i.entry_mode === 'quantity' ? 'quantity' : 'percent',
        is_balance:      Number(i.is_balance) === 1,
        instruction:     i.instruction ?? '',
      }));

      // always leave one empty row ready so new ingredients can be added
      setLines(loaded.length ? [...loaded, newLine()] : [newLine()]);
      setProducts(Array.isArray(data.products) ? data.products : []);
    } catch (error: any) {
      setToast({ msg: error.message || 'Could not load that formula', ok: false });
    }
  };

  // ── Confirm the new-formula prompt ───────────────────────────────────────
  const confirmPrompt = () => {
    if (!prompt) return;

    const name = prompt.name.trim();
    const code = prompt.code.trim().toUpperCase();

    if (!name) { setToast({ msg: 'Enter a formula name', ok: false }); return; }
    if (!code) { setToast({ msg: 'Enter a formula code', ok: false }); return; }

    if (formulas.some(f => (f.code ?? '').toUpperCase() === code)) {
      setToast({ msg: `Code ${code} is already used`, ok: false });
      return;
    }

    setHeader({ ...BLANK_HEADER, name, code });
    setLines([newLine()]);
    setProducts([]);
    setPrompt(null);
  };

  // ── Density ──────────────────────────────────────────────────────────────
  // sanitise rather than reject, so a stored value the pattern dislikes can
  // never leave the field unable to accept a keystroke
  const setDensity = (raw: string) => {
    const cleaned = raw
      .replace(/[^\d.]/g, '')
      .replace(/(\..*)\./g, '$1')
      .replace(/^(\d*\.\d{4})\d+$/, '$1');
    setHeader(h => ({ ...h, density_kg_per_l: cleaned }));
  };

  const densityValue   = Number(header.density_kg_per_l);
  const densityOk      = Number.isFinite(densityValue) && densityValue > 0;
  const batchLitres    = densityOk ? r2(BATCH_QTY / densityValue) : null;

  // ── Line editing ─────────────────────────────────────────────────────────
  const patch = (key: string, changes: Partial<Line>) =>
    setLines(ls => ls.map(l => (l.key === key ? { ...l, ...changes } : l)));

  const setPercent = (key: string, v: string) => {
    const pct = parseFloat(v) || 0;
    patch(key, { percentage: r4(pct), quantity: r2(BATCH_QTY * pct / 100), entry_mode: 'percent' });
  };

  const setQuantity = (key: string, v: string) => {
    const qty = r2(parseFloat(v) || 0);
    patch(key, { quantity: qty, percentage: r4(qty / BATCH_QTY * 100), entry_mode: 'quantity' });
  };

  const setMaterial = (key: string, id: string) => {
    const m = materials.find(x => String(x.id) === id);

    // clearing the material also clears the figures and any balance flag,
    // so an abandoned row can never contribute to the total
    if (!m) {
      patch(key, {
        raw_material_id: null,
        material_type: '',
        uom: 'kg',
        percentage: 0,
        quantity: 0,
        is_balance: false,
      });
      return;
    }

    patch(key, {
      raw_material_id: m.id,
      uom: m.uom ?? 'kg',
      material_type: m.material_type ?? '',
    });
  };

  const toggleBalance = (key: string) =>
    setLines(ls => ls.map(l => ({
      ...l,
      is_balance: l.key === key ? isFilled(l) && !l.is_balance : false,
    })));

  const addLine    = () => setLines(ls => [...ls, newLine()]);
  const removeLine = (key: string) =>
    setLines(ls => (ls.length === 1 ? [newLine()] : ls.filter(l => l.key !== key)));

  // ── Ingredient options: no material twice in one formula ─────────────────
  const usedIds = new Set(lines.map(l => l.raw_material_id).filter(Boolean) as number[]);

  const optionsFor = (l: Line) =>
    materials.filter(m => m.id === l.raw_material_id || (!usedIds.has(m.id) && active(m)));

  const available = materials.filter(m => !usedIds.has(m.id) && active(m));

  // ── Totals — only rows with a material selected are counted ──────────────
  const filled     = lines.filter(isFilled);
  const hasBalance = filled.some(l => l.is_balance);
  const typedTotal = r4(filled.filter(l => !l.is_balance).reduce((s, l) => s + l.percentage, 0));
  const balancePct = hasBalance ? r4(Math.max(0, 100 - typedTotal)) : 0;
  const grandTotal = r4(typedTotal + balancePct);
  const balanced   = hasBalance ? typedTotal <= 100.0001 : Math.abs(typedTotal - 100) <= 0.01;

  const canSave = !!header.name.trim()
               && !!header.code.trim()
               && !!header.chemical_type
               && densityOk
               && filled.length > 0
               && balanced;

  // ── Save ─────────────────────────────────────────────────────────────────
  const save = async () => {
    if (!header.chemical_type) {
      setToast({ msg: 'Choose a chemical type first', ok: false });
      return;
    }
    if (!densityOk) {
      setToast({ msg: 'Enter the density in kg/L', ok: false });
      return;
    }
    if (filled.length === 0) {
      setToast({ msg: 'Add at least one ingredient', ok: false });
      return;
    }

    setSaving(true);
    try {
      const data = await apiGet('formulas/save', {
        ...header,
        base_batch_qty:   BATCH_QTY,
        density_kg_per_l: densityValue,
        status: header.status || 'active',
        // rows with no material selected are never sent
        items: filled.map((l, i) => ({
          raw_material_id: l.raw_material_id,
          material_type:   l.material_type,
          percentage:      l.is_balance ? balancePct : r4(l.percentage),
          quantity:        l.is_balance ? r2(BATCH_QTY * balancePct / 100) : r2(l.quantity),
          uom:             l.uom,
          entry_mode:      l.entry_mode,
          is_balance:      l.is_balance ? 1 : 0,
          sequence:        i + 1,
          instruction:     l.instruction,
        })),
      });

      setHeader(h => ({ ...h, id: data.id }));
      setToast({ msg: 'Formula saved', ok: true });
      loadLookups();
    } catch (error: any) {
      setToast({ msg: error.message || 'Could not save the formula', ok: false });
    } finally {
      setSaving(false);
    }
  };

  // ── Styles ───────────────────────────────────────────────────────────────
  const inp = "bg-white border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none";
  const bad = "bg-white border border-red-400 rounded-lg px-3 py-2 w-full text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100 focus:outline-none";
  const sel = `${inp} appearance-none pr-8`;
  const lbl = "block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5";
  const cell = "bg-white border border-slate-300 rounded-lg px-2 py-1.5 w-full text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none";
  const driven = "bg-sky-50 border-sky-400 font-semibold text-sky-900";
  const req = <span className="text-red-500 ml-0.5">*</span>;

  if (loading) {
    return <div className="min-h-screen bg-slate-100 p-6 text-center text-slate-400">Loading…</div>;
  }

  const unsavedNew  = header.id === null && !!header.name;
  const typeMissing = !!header.name && !header.chemical_type;

  return (
    <div className="min-h-screen bg-slate-100 p-6">
      <div className="w-full space-y-6">

        {/* ── Header ── */}
        <div className="bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg">
          <div className="flex items-center gap-3">
            <FlaskConical className="text-sky-400 w-7 h-7" />
            <div>
              <h1 className="text-2xl font-bold text-white">
                {header.name || 'Formulas'}
              </h1>
              <p className="text-slate-400 text-sm">
                {header.code ? `${header.code} · ` : ''}Built against a {BATCH_QTY} kg batch
                {batchLitres !== null && ` · about ${batchLitres.toFixed(2)} L`}
              </p>
            </div>
          </div>
          {unsavedNew && (
            <span className="bg-amber-400 text-amber-950 px-3 py-1.5 rounded-lg font-bold text-xs">
              NOT SAVED YET
            </span>
          )}
        </div>

        {/* ── Formula details ── */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
          <h2 className="font-semibold text-slate-800 mb-4">Formula details</h2>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

            <div className="lg:col-span-2">
              <label className={lbl}>Formula</label>
              <div className="relative">
                <select
                  value={header.id ?? ''}
                  onChange={e => onSelectChange(e.target.value)}
                  className={sel}
                >
                  <option value={NEW_OPTION}>+ Add new formula</option>
                  {header.id === null && header.name && (
                    <option value="">{header.name} (unsaved)</option>
                  )}
                  {formulas.map(f => (
                    <option key={f.id} value={f.id!}>{f.name}</option>
                  ))}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>

            <div>
              <label className={lbl}>Code{req}</label>
              <input
                type="text"
                value={header.code}
                onChange={e => setHeader(h => ({ ...h, code: e.target.value.toUpperCase() }))}
                placeholder="LIQSOAP-01"
                className={inp}
              />
            </div>

            <div>
              <label className={lbl}>Chemical type{req}</label>
              <div className="relative">
                <select
                  value={header.chemical_type}
                  onChange={e => setHeader(h => ({ ...h, chemical_type: e.target.value }))}
                  className={typeMissing ? `${bad} appearance-none pr-8` : sel}
                >
                  <option value="">Select type</option>
                  {chemicalTypes.map(t => <option key={t.id} value={t.name}>{t.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
              {typeMissing && <p className="text-xs text-red-600 mt-1">Required before saving</p>}
            </div>

            <div>
              <label className={lbl}>Batch size</label>
              <div className="relative">
                <input
                  type="text"
                  value={`${BATCH_QTY} kg`}
                  disabled
                  className={`${inp} bg-slate-100 text-slate-500 pr-8`}
                />
                <Lock className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-3.5 h-3.5" />
              </div>
            </div>

            {/* Density — what converts this formula's kg into litres downstream */}
            <div>
              <label className={lbl}>Density (kg/L){req}</label>
              <input
                type="text"
                inputMode="decimal"
                value={header.density_kg_per_l}
                onChange={e => setDensity(e.target.value)}
                placeholder="1.02"
                className={densityOk ? inp : bad}
              />
              <p className="text-xs text-slate-400 mt-1">
                {densityOk
                  ? `${BATCH_QTY} kg ≈ ${batchLitres!.toFixed(2)} L`
                  : header.density_kg_per_l.trim() === ''
                    ? 'Measure it — no default'
                    : 'Must be greater than 0'}
              </p>
            </div>
          </div>

          {/* Products using this formula */}
          <div className="mt-4 pt-4 border-t border-slate-100">
            <div className="flex items-center gap-2 mb-2">
              <Package size={15} className="text-slate-400" />
              <span className="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                Products using this formula
              </span>
            </div>
            {products.length === 0 ? (
              <p className="text-sm text-slate-400">
                {header.id ? 'No products linked to this formula yet' : 'Save the formula, then link products to it'}
              </p>
            ) : (
              <div className="flex flex-wrap gap-2">
                {products.map(p => (
                  <span
                    key={p.id}
                    className="text-xs bg-slate-100 border border-slate-200 text-slate-700 px-3 py-1.5 rounded-lg"
                  >
                    {p.name}
                    {p.sku && <span className="text-slate-400 ml-2">#{p.sku}</span>}
                  </span>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* ── Ingredients ── */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
          <div className="flex items-center justify-between mb-4">
            <h2 className="font-semibold text-slate-800">
              Ingredients
              <span className="ml-2 text-xs font-normal text-slate-400">
                {filled.length} saved{lines.length > filled.length ? `, ${lines.length - filled.length} empty` : ''}
              </span>
            </h2>
            <div className="flex items-center gap-3">
              {available.length === 0 && (
                <span className="text-xs text-slate-400">Every material is already used</span>
              )}
              <button
                onClick={addLine}
                disabled={available.length === 0}
                className="flex items-center gap-1.5 text-sky-600 hover:text-sky-800 font-semibold text-sm disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <Plus size={16} /> Add row
              </button>
            </div>
          </div>

          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead className="text-slate-500 uppercase text-[11px] tracking-wide">
                <tr>
                  <th className="w-8 py-2"></th>
                  <th className="text-left px-2 py-2 font-semibold">Ingredient</th>
                  <th className="text-right px-2 py-2 font-semibold w-32">%</th>
                  <th className="text-right px-2 py-2 font-semibold w-36">kg</th>
                  <th className="text-center px-2 py-2 font-semibold w-20">Balance</th>
                  <th className="w-10"></th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {lines.map((l, i) => {
                  const picked = isFilled(l);
                  const pct = l.is_balance ? balancePct : l.percentage;
                  const qty = l.is_balance ? r2(BATCH_QTY * balancePct / 100) : l.quantity;
                  const locked = l.is_balance || !picked;

                  return (
                    <tr key={l.key} className={picked ? 'hover:bg-slate-50' : 'bg-slate-50/60'}>
                      <td className="py-2 text-xs text-slate-400 text-center">{i + 1}</td>

                      <td className="px-2 py-2">
                        <select
                          value={l.raw_material_id ?? ''}
                          onChange={e => setMaterial(l.key, e.target.value)}
                          className={cell}
                        >
                          <option value="">Select ingredient</option>
                          {optionsFor(l).map(m => (
                            <option key={m.id} value={m.id}>
                              {m.name}{active(m) ? '' : ' (inactive)'} — {num(m.stock_on_hand).toFixed(2)} {m.uom} on hand
                            </option>
                          ))}
                        </select>
                      </td>

                      <td className="px-2 py-2">
                        <input
                          type="number"
                          step="0.001"
                          min="0"
                          max="100"
                          value={picked ? pct : ''}
                          disabled={locked}
                          placeholder="—"
                          onChange={e => setPercent(l.key, e.target.value)}
                          className={`${cell} text-right ${locked ? 'bg-slate-100 text-slate-400' : l.entry_mode === 'percent' ? driven : ''}`}
                        />
                      </td>

                      <td className="px-2 py-2">
                        <input
                          type="number"
                          step="0.01"
                          min="0"
                          value={picked ? qty : ''}
                          disabled={locked}
                          placeholder="—"
                          onChange={e => setQuantity(l.key, e.target.value)}
                          className={`${cell} text-right ${locked ? 'bg-slate-100 text-slate-400' : l.entry_mode === 'quantity' ? driven : ''}`}
                        />
                      </td>

                      <td className="px-2 py-2 text-center">
                        <input
                          type="checkbox"
                          checked={l.is_balance}
                          disabled={!picked}
                          onChange={() => toggleBalance(l.key)}
                          title="Fill with whatever is left of 100%"
                          className="w-4 h-4 accent-sky-500 disabled:opacity-40"
                        />
                      </td>

                      <td className="px-2 py-2 text-right">
                        <button
                          onClick={() => removeLine(l.key)}
                          className="text-slate-300 hover:text-red-600"
                        >
                          <Trash2 size={15} />
                        </button>
                      </td>
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>

          {/* Total */}
          <div className="flex items-center gap-3 flex-wrap mt-4 pt-4 border-t border-slate-200">
            <span className="text-xs text-slate-400">
              Rows without an ingredient are ignored
            </span>
            <div className="ml-auto flex items-center gap-3">
              <span className="text-sm text-slate-500">Total</span>
              <span
                className={`text-sm font-bold px-3 py-1.5 rounded-lg ${
                  balanced ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'
                }`}
              >
                {grandTotal.toFixed(3)}%
                {!balanced && ` — ${(100 - typedTotal).toFixed(3)} short`}
              </span>
            </div>
          </div>
        </div>

        {/* ── Actions ── */}
        <div className="flex items-center gap-3 flex-wrap">
          <div className="relative">
            <select
              value={header.status}
              onChange={e => setHeader(h => ({ ...h, status: e.target.value }))}
              className={`${sel} w-40`}
            >
              <option value="active">Active</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
            <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
          </div>

          {!canSave && (
            <span className="text-xs text-slate-400">
              {!header.name
                ? 'Pick a formula or add a new one to begin'
                : !header.chemical_type
                  ? 'Choose a chemical type'
                  : !header.code.trim()
                    ? 'Enter a formula code'
                    : !densityOk
                      ? 'Enter a density greater than 0'
                      : filled.length === 0
                        ? 'Select at least one ingredient'
                        : 'Ingredients must total 100%'}
            </span>
          )}

          <button
            onClick={save}
            disabled={saving || !canSave}
            className="ml-auto flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-6 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Check className="w-4 h-4" strokeWidth={3} />}
            {saving ? 'Saving…' : header.id ? 'Save changes' : 'Create formula'}
          </button>
        </div>
      </div>

      {/* ── New formula prompt ── */}
      {prompt && (
        <div className="fixed inset-0 bg-slate-900/50 flex items-center justify-center p-6 z-50">
          <div className="bg-white rounded-xl shadow-2xl w-full max-w-md p-5">
            <div className="flex items-center gap-2 mb-4">
              <Plus size={18} className="text-sky-500" />
              <h3 className="font-semibold text-slate-800">New formula</h3>
              <button
                onClick={() => setPrompt(null)}
                className="ml-auto text-slate-400 hover:text-slate-700"
              >
                <X size={18} />
              </button>
            </div>

            <div className="space-y-3">
              <div>
                <label className={lbl}>Name</label>
                <input
                  type="text"
                  autoFocus
                  value={prompt.name}
                  onChange={e => setPrompt(p => p && ({ ...p, name: e.target.value }))}
                  onKeyDown={e => e.key === 'Enter' && confirmPrompt()}
                  placeholder="Lemon liquid soap base"
                  className={inp}
                />
              </div>

              <div>
                <label className={lbl}>Code</label>
                <input
                  type="text"
                  value={prompt.code}
                  onChange={e => setPrompt(p => p && ({ ...p, code: e.target.value.toUpperCase() }))}
                  onKeyDown={e => e.key === 'Enter' && confirmPrompt()}
                  placeholder="LIQSOAP-01"
                  className={inp}
                />
              </div>
            </div>

            <div className="flex gap-3 mt-5">
              <button
                onClick={() => setPrompt(null)}
                className="flex-1 border border-slate-300 rounded-lg py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50"
              >
                Cancel
              </button>
              <button
                onClick={confirmPrompt}
                className="flex-1 bg-sky-500 hover:bg-sky-600 text-white rounded-lg py-2.5 text-sm font-semibold"
              >
                Start formula
              </button>
            </div>
          </div>
        </div>
      )}

      {toast && (
        <div
          className={`fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-xl text-white font-semibold text-sm z-50 ${
            toast.ok ? 'bg-emerald-600' : 'bg-red-600'
          }`}
        >
          {toast.msg}
        </div>
      )}
    </div>
  );
};

export default FormulaBuilder;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<FormulaBuilder />);
}