import React, { useState, useEffect, useCallback } from 'react';
import {
  FlaskConical, Loader2, Plus, ChevronDown, Pencil, X, AlertTriangle, Check,
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
  stock_on_hand:  number;
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
  stock_on_hand: 0,
  reorder_level: 0,
  allow_negative: false,
  is_active: true,
  notes: '',
};

const truthy = (v: any) => v === true || Number(v) === 1;
const int    = (v: any) => Math.trunc(Number(v ?? 0)) || 0;

const RawMaterialList: React.FC = () => {
  // ── Blade-injected globals ────────────────────────────────────────────────
  const unitTypes     = window.ChemicalUnitType     || [];
  const chemicalTypes = window.ChemicalMaterialType || [];

  const [materials, setMaterials] = useState<RawMaterial[]>([]);
  const [loading,   setLoading]   = useState(true);
  const [saving,    setSaving]    = useState(false);
  const [busy,      setBusy]      = useState<number | null>(null);
  const [toast,     setToast]     = useState<{ msg: string; ok: boolean } | null>(null);

  const [form,   setForm]   = useState<RawMaterial>({ ...BLANK, uom: unitTypes[0]?.name ?? '' });
  const [errors, setErrors] = useState<Record<string, string>>({});

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
  const setField = (key: keyof RawMaterial, value: any) => {
    setForm(f => ({ ...f, [key]: value }));
    setErrors(e => { const { [key]: _drop, ...rest } = e; return rest; });
  };

  const resetForm = () => {
    setForm({ ...BLANK, uom: form.uom || unitTypes[0]?.name || '', material_type: form.material_type });
    setErrors({});
  };

  const validate = () => {
    const e: Record<string, string> = {};
    if (!form.code.trim())   e.code          = 'Enter a code';
    if (!form.name.trim())   e.name          = 'Enter a name';
    if (!form.material_type) e.material_type = 'Choose a type';
    if (!form.uom)           e.uom           = 'Choose a unit';
    return e;
  };

  // every required field filled — drives the save button
  const canSave = !!form.code.trim()
               && !!form.name.trim()
               && !!form.material_type
               && !!form.uom;

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
        stock_on_hand: int(form.stock_on_hand),
        reorder_level: int(form.reorder_level),
      };
      const payload     = editing ? { ...body, stock_on_hand: undefined } : body;
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
    setForm(m);
    setErrors({});
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const activeCount = materials.filter(m => truthy(m.is_active)).length;

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
              <label className={lbl}>Opening stock</label>
              <input
                type="number"
                step="1"
                min="0"
                value={form.stock_on_hand}
                disabled={editing}
                onChange={e => setField('stock_on_hand', parseInt(e.target.value, 10) || 0)}
                className={`${inp} disabled:bg-slate-100 disabled:text-slate-400`}
              />
              <p className="text-xs text-slate-400 mt-1">
                {editing ? 'Change via stock adjustment' : 'Whole units only'}
              </p>
            </div>

            <div>
              <label className={lbl}>Reorder level</label>
              <input
                type="number"
                step="1"
                min="0"
                value={form.reorder_level}
                onChange={e => setField('reorder_level', parseInt(e.target.value, 10) || 0)}
                className={inp}
              />
              <p className="text-xs text-slate-400 mt-1">0 means no warning</p>
            </div>

            <div className="lg:col-span-5">
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
                <span className="text-xs text-slate-400">Code, name, type and unit are required</span>
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
                    <th className="text-right px-3 py-2.5 font-semibold">On hand</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Reorder at</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Notes</th>
                    <th className="w-40 px-3 py-2.5"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {materials.map(m => {
                    const active  = truthy(m.is_active);
                    const stock   = int(m.stock_on_hand);
                    const reorder = int(m.reorder_level);
                    const low     = reorder > 0 && stock <= reorder;
                    const busyMe  = busy === m.id;

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
                        <td className={`px-3 py-3 text-right font-semibold ${low ? 'text-amber-700' : 'text-slate-900'}`}>
                          <span className="inline-flex items-center gap-1.5 justify-end">
                            {low && <AlertTriangle size={13} className="text-amber-500" />}
                            {stock} {m.uom}
                          </span>
                        </td>
                        <td className="px-3 py-3 text-right text-slate-500">
                          {reorder > 0 ? reorder : '—'}
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