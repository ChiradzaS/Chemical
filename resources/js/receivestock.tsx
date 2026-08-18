import React, { useState, useEffect, useCallback, useRef } from 'react';
import {
  PackagePlus, Loader2, Plus, ChevronDown, X, Check, Trash2, History,
  TrendingUp, TrendingDown, Truck, Search,
} from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:    string;
    ChemicalSupplier: { id: number | string; name: string; code?: string }[];
  }
}

interface MaterialOption {
  id:        number;
  code:      string;
  name:      string;
  uom:       string;
  is_active: boolean | number;
}

interface SupplierOption {
  id:   number;
  name: string;
  code?: string;
}

interface ReceiptLine {
  raw_material_id: number | '';
  qty:             string;   // text so it types cleanly
  unit_cost:       string;
}

interface ReceiptRow {
  id:              number;
  received_date:   string;
  reference:       string;
  supplier_id:     number;
  supplier_name:   string;
  raw_material_id: number;
  material_code:   string;
  material_name:   string;
  uom:             string;
  qty:             number | string;
  unit_cost:       number | string;
  notes?:          string;
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

const BLANK_LINE: ReceiptLine = { raw_material_id: '', qty: '', unit_cost: '' };

const truthy = (v: any) => v === true || Number(v) === 1;

// quantities carry 2 decimals — chemicals get issued in fractions of a kg
const num = (v: any) => {
  const n = Number(String(v ?? '').trim());
  return Number.isFinite(n) ? n : 0;
};
const qty2 = (v: any) => num(v).toFixed(2);

const money = (v: any) =>
  v === null || v === undefined || v === '' || !Number.isFinite(Number(v))
    ? null
    : Number(v);

const rands = (v: any) => {
  const n = money(v);
  return n === null ? '—' : `R ${n.toFixed(2)}`;
};

// sanitise rather than reject, so no keystroke ever gets trapped
const cleanCost = (raw: string) =>
  raw
    .replace(/[^\d.]/g, '')
    .replace(/(\..*)\./g, '$1')
    .replace(/^(\d*\.\d{2})\d+$/, '$1');

// same sanitiser as cost — quantities take 2 decimals
const cleanQty = (raw: string) => cleanCost(raw);

const today = () => new Date().toISOString().slice(0, 10);

const label = (o: MaterialOption) => `${o.code} — ${o.name}`;

// ── shared classes ──────────────────────────────────────────────────────────
const inp = "bg-white border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none";
const bad = "bg-white border border-red-400 rounded-lg px-3 py-2 w-full text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100 focus:outline-none";
const sel = `${inp} appearance-none pr-8`;
const lbl = "block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5";
const req = <span className="text-red-500 ml-0.5">*</span>;

// ════════════════════════════════════════════════════════════════════════════
// Type-ahead material picker
// ════════════════════════════════════════════════════════════════════════════
const MaterialPicker: React.FC<{
  value:   number | '';
  options: MaterialOption[];
  invalid?: boolean;
  onPick:  (id: number | '') => void;
}> = ({ value, options, invalid, onPick }) => {
  const selected = options.find(o => o.id === Number(value)) || null;

  const [query, setQuery] = useState(selected ? label(selected) : '');
  const [open,  setOpen]  = useState(false);
  const [hi,    setHi]    = useState(0);
  const boxRef = useRef<HTMLDivElement>(null);

  // the box shows the picked material until the user starts typing again
  useEffect(() => {
    setQuery(selected ? label(selected) : '');
  }, [selected?.id]);

  // clicking anywhere else closes the list and restores what was picked
  useEffect(() => {
    const away = (e: MouseEvent) => {
      if (boxRef.current && !boxRef.current.contains(e.target as Node)) {
        setOpen(false);
        setQuery(selected ? label(selected) : '');
      }
    };
    document.addEventListener('mousedown', away);
    return () => document.removeEventListener('mousedown', away);
  }, [selected?.id]);

  const typed = query.trim().toLowerCase();
  const showAll = !open || typed === '' || (selected && query === label(selected));
  const matches = showAll
    ? options
    : options.filter(o =>
        o.code.toLowerCase().includes(typed) || o.name.toLowerCase().includes(typed));

  const pick = (o: MaterialOption) => {
    onPick(o.id);
    setQuery(label(o));
    setOpen(false);
  };

  const keys = (e: React.KeyboardEvent) => {
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setOpen(true);
      setHi(h => Math.min(h + 1, matches.length - 1));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setHi(h => Math.max(h - 1, 0));
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (open && matches[hi]) pick(matches[hi]);
    } else if (e.key === 'Escape') {
      setOpen(false);
      setQuery(selected ? label(selected) : '');
    }
  };

  return (
    <div className="relative" ref={boxRef}>
      <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
      <input
        type="text"
        value={query}
        onChange={e => { setQuery(e.target.value); setOpen(true); setHi(0); }}
        onFocus={() => { setOpen(true); setHi(0); }}
        onKeyDown={keys}
        placeholder="Type a code or name"
        className={`${invalid ? bad : inp} pl-9 pr-8`}
      />
      {selected && (
        <button
          onClick={() => { onPick(''); setQuery(''); setOpen(true); }}
          className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-600"
          title="Clear"
        >
          <X size={14} />
        </button>
      )}

      {open && (
        <div className="absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-xl max-h-60 overflow-y-auto">
          {matches.length === 0 ? (
            <p className="px-3 py-2.5 text-sm text-slate-400">No material matches that</p>
          ) : (
            matches.map((o, idx) => (
              <button
                key={o.id}
                onMouseDown={e => e.preventDefault()}   // keep focus so the click lands
                onClick={() => pick(o)}
                onMouseEnter={() => setHi(idx)}
                className={`w-full text-left px-3 py-2 text-sm flex items-center gap-2 ${
                  idx === hi ? 'bg-sky-50' : 'hover:bg-slate-50'
                } ${o.id === Number(value) ? 'font-semibold text-sky-700' : 'text-slate-700'}`}
              >
                <span className="font-mono text-[11px] text-slate-500 w-20 shrink-0">{o.code}</span>
                <span className="truncate">{o.name}</span>
                <span className="ml-auto text-[11px] text-slate-400">{o.uom}</span>
              </button>
            ))
          )}
        </div>
      )}
    </div>
  );
};

// ════════════════════════════════════════════════════════════════════════════
// Quick add supplier
// ════════════════════════════════════════════════════════════════════════════
const AddSupplierModal: React.FC<{
  onClose: () => void;
  onSaved: (supplier: SupplierOption) => void;
  onError: (msg: string) => void;
}> = ({ onClose, onSaved, onError }) => {
  const [s, setS] = useState({ code: '', name: '', contact_person: '', phone: '', email: '' });
  const [saving, setSaving] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const set = (k: keyof typeof s, v: string) => {
    setS(prev => ({ ...prev, [k]: v }));
    setErrors(e => { const { [k]: _d, ...rest } = e; return rest; });
  };

  const save = async () => {
    const e: Record<string, string> = {};
    if (!s.code.trim()) e.code = 'Enter a code';
    if (!s.name.trim()) e.name = 'Enter a name';
    setErrors(e);
    if (Object.keys(e).length) return;

    setSaving(true);
    try {
      const body = {
        id: null,
        code: s.code.trim(),
        name: s.name.trim(),
        contact_person: s.contact_person.trim(),
        phone: s.phone.trim(),
        email: s.email.trim(),
        is_active: 1,
      };
      const encodedData = encodeURIComponent(JSON.stringify(body));
      const response    = await axios.get(`${API_BASE}/suppliers/save?data=${encodedData}`);

      if (response.data?.status === 'error') throw new Error(response.data.message);

      onSaved({
        id:   Number(response.data.id),
        name: response.data.name,
        code: response.data.code,
      });
    } catch (error: any) {
      onError(error.response?.data?.message || error.message || 'Could not save that supplier.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/50 p-4 overflow-y-auto">
      <div className="bg-white rounded-xl shadow-2xl w-full max-w-lg mt-16">
        <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
          <Truck size={18} className="text-sky-500" />
          <h3 className="font-semibold text-slate-800">Add a supplier</h3>
          <button onClick={onClose} className="ml-auto text-slate-400 hover:text-red-600">
            <X size={18} />
          </button>
        </div>

        <div className="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className={lbl}>Code{req}</label>
            <input
              type="text"
              value={s.code}
              autoFocus
              onChange={e => set('code', e.target.value.toUpperCase())}
              placeholder="SUP-001"
              className={errors.code ? bad : inp}
            />
            {errors.code && <p className="text-xs text-red-600 mt-1">{errors.code}</p>}
          </div>

          <div>
            <label className={lbl}>Supplier name{req}</label>
            <input
              type="text"
              value={s.name}
              onChange={e => set('name', e.target.value)}
              placeholder="Protea Chemicals"
              className={errors.name ? bad : inp}
            />
            {errors.name && <p className="text-xs text-red-600 mt-1">{errors.name}</p>}
          </div>

          <div>
            <label className={lbl}>Contact person</label>
            <input type="text" value={s.contact_person} onChange={e => set('contact_person', e.target.value)} className={inp} />
          </div>

          <div>
            <label className={lbl}>Phone</label>
            <input type="text" value={s.phone} onChange={e => set('phone', e.target.value)} placeholder="011 555 0100" className={inp} />
          </div>

          <div className="md:col-span-2">
            <label className={lbl}>Email</label>
            <input type="text" inputMode="email" value={s.email} onChange={e => set('email', e.target.value)} placeholder="orders@supplier.co.za" className={inp} />
            <p className="text-xs text-slate-400 mt-1">
              Added to this page now; it joins the global list on the next page load
            </p>
          </div>
        </div>

        <div className="flex items-center justify-end gap-3 px-5 py-4 border-t border-slate-100">
          <button onClick={onClose} className="text-slate-500 hover:text-slate-800 text-sm font-semibold px-3 py-2">
            Cancel
          </button>
          <button
            onClick={save}
            disabled={saving}
            className="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm shadow disabled:opacity-50"
          >
            {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Check className="w-4 h-4" strokeWidth={3} />}
            {saving ? 'Saving…' : 'Add supplier'}
          </button>
        </div>
      </div>
    </div>
  );
};

// ════════════════════════════════════════════════════════════════════════════
// Supply history for one material
// ════════════════════════════════════════════════════════════════════════════
const HistoryModal: React.FC<{
  material: { id: number; code: string; name: string; uom: string };
  onClose: () => void;
}> = ({ material, onClose }) => {
  const [rows, setRows] = useState<ReceiptRow[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    (async () => {
      setLoading(true);
      try {
        const payload     = { raw_material_id: material.id, limit: 50 };
        const encodedData = encodeURIComponent(JSON.stringify(payload));
        const response    = await axios.get(`${API_BASE}/stock-receipts/history?data=${encodedData}`);
        setRows(Array.isArray(response.data) ? response.data : []);
      } catch (error) {
        console.error('Error fetching supply history:', error);
        setRows([]);
      } finally {
        setLoading(false);
      }
    })();
  }, [material.id]);

  // newest first from the endpoint; compare the top two prices
  const latest = money(rows[0]?.unit_cost);
  const prev   = money(rows[1]?.unit_cost);
  const delta  = latest !== null && prev !== null && prev !== 0 ? ((latest - prev) / prev) * 100 : null;

  const totalQty   = rows.reduce((sum, r) => sum + num(r.qty), 0);
  const totalValue = rows.reduce((sum, r) => sum + num(r.qty) * (money(r.unit_cost) ?? 0), 0);

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center bg-slate-900/50 p-4 overflow-y-auto">
      <div className="bg-white rounded-xl shadow-2xl w-full max-w-3xl mt-12 mb-12">
        <div className="flex items-center gap-2 px-5 py-4 border-b border-slate-100">
          <History size={18} className="text-sky-500" />
          <div>
            <h3 className="font-semibold text-slate-800">Supply history</h3>
            <p className="text-xs text-slate-400">
              <span className="font-mono">{material.code}</span> — {material.name}
            </p>
          </div>
          <button onClick={onClose} className="ml-auto text-slate-400 hover:text-red-600">
            <X size={18} />
          </button>
        </div>

        {loading ? (
          <div className="text-center py-16 text-slate-400">Loading history…</div>
        ) : rows.length === 0 ? (
          <div className="text-center py-16">
            <History size={40} className="mx-auto text-slate-300 mb-3" />
            <p className="text-slate-600 font-medium">Nothing received yet</p>
            <p className="text-slate-400 text-sm mt-1">This is the first delivery of this material</p>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
              <div className="px-5 py-3">
                <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Last price</p>
                <p className="text-lg font-bold text-slate-800 tabular-nums flex items-center gap-2">
                  {rands(latest)}
                  {delta !== null && Math.abs(delta) >= 0.5 && (
                    <span className={`text-xs font-semibold flex items-center gap-0.5 ${delta > 0 ? 'text-red-600' : 'text-emerald-600'}`}>
                      {delta > 0 ? <TrendingUp size={13} /> : <TrendingDown size={13} />}
                      {Math.abs(delta).toFixed(1)}%
                    </span>
                  )}
                </p>
              </div>
              <div className="px-5 py-3">
                <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Total received</p>
                <p className="text-lg font-bold text-slate-800 tabular-nums">{totalQty.toFixed(2)} {material.uom}</p>
              </div>
              <div className="px-5 py-3">
                <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Total spent</p>
                <p className="text-lg font-bold text-slate-800 tabular-nums">{rands(totalValue)}</p>
              </div>
            </div>

            <div className="overflow-x-auto max-h-[55vh] overflow-y-auto">
              <table className="w-full text-sm">
                <thead className="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide sticky top-0">
                  <tr>
                    <th className="text-left px-5 py-2.5 font-semibold">Date</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Supplier</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Reference</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Qty</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Price/kg</th>
                    <th className="text-right px-5 py-2.5 font-semibold">Line total</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {rows.map((r, i) => {
                    const qty  = num(r.qty);
                    const cost = money(r.unit_cost) ?? 0;
                    return (
                      <tr key={r.id ?? i} className="hover:bg-slate-50">
                        <td className="px-5 py-2.5 text-slate-600 whitespace-nowrap">{r.received_date}</td>
                        <td className="px-3 py-2.5 font-medium text-slate-800">{r.supplier_name}</td>
                        <td className="px-3 py-2.5 text-slate-500 text-xs">{r.reference || '—'}</td>
                        <td className="px-3 py-2.5 text-right tabular-nums text-slate-800">{qty.toFixed(2)} {r.uom || material.uom}</td>
                        <td className="px-3 py-2.5 text-right tabular-nums text-slate-600">{rands(cost)}</td>
                        <td className="px-5 py-2.5 text-right tabular-nums font-semibold text-slate-800">{rands(qty * cost)}</td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </>
        )}
      </div>
    </div>
  );
};

// ════════════════════════════════════════════════════════════════════════════
// Receive stock
// ════════════════════════════════════════════════════════════════════════════
const ReceiveStock: React.FC = () => {
  // ── Blade-injected global ─────────────────────────────────────────────────
  const [suppliers, setSuppliers] = useState<SupplierOption[]>(
    () => (window.ChemicalSupplier || []).map(s => ({
      id: Number(s.id), name: s.name, code: s.code,
    })),
  );

  const [options,  setOptions]  = useState<MaterialOption[]>([]);
  const [receipts, setReceipts] = useState<ReceiptRow[]>([]);
  const [loading,  setLoading]  = useState(true);
  const [saving,   setSaving]   = useState(false);
  const [toast,    setToast]    = useState<{ msg: string; ok: boolean } | null>(null);

  const [showAddSupplier, setShowAddSupplier] = useState(false);
  const [historyFor, setHistoryFor] = useState<{ id: number; code: string; name: string; uom: string } | null>(null);

  const [supplierId,   setSupplierId]   = useState<number | ''>('');
  const [reference,    setReference]    = useState('');
  const [receivedDate, setReceivedDate] = useState(today());
  const [notes,        setNotes]        = useState('');
  const [lines,        setLines]        = useState<ReceiptLine[]>([{ ...BLANK_LINE }]);
  const [errors,       setErrors]       = useState<Record<string, string>>({});

  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(null), 3200);
    return () => clearTimeout(t);
  }, [toast]);

  // ── Fetch ─────────────────────────────────────────────────────────────────
  const fetchMaterials = useCallback(async () => {
    try {
      const payload     = { search: '', include_inactive: 0 };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/raw-materials/list?data=${encodedData}`);
      const data        = response.data;
      setOptions(Array.isArray(data) ? data.filter((m: MaterialOption) => truthy(m.is_active)) : []);
    } catch (error) {
      console.error('Error fetching raw materials:', error);
      setOptions([]);
    }
  }, []);

  const fetchReceipts = useCallback(async () => {
    setLoading(true);
    try {
      const payload     = { search: '', limit: 100 };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/stock-receipts/list?data=${encodedData}`);
      const data        = response.data;
      setReceipts(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Error fetching receipts:', error);
      setReceipts([]);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => { fetchMaterials(); fetchReceipts(); }, [fetchMaterials, fetchReceipts]);

  // ── Line helpers ──────────────────────────────────────────────────────────
  const clearError = (key: string) =>
    setErrors(e => { const { [key]: _drop, ...rest } = e; return rest; });

  const setLine = (i: number, key: keyof ReceiptLine, value: any) => {
    setLines(ls => ls.map((l, idx) => (idx === i ? { ...l, [key]: value } : l)));
    clearError(`line_${i}`);
  };

  const addLine  = () => setLines(ls => [...ls, { ...BLANK_LINE }]);
  const dropLine = (i: number) => setLines(ls => (ls.length === 1 ? [{ ...BLANK_LINE }] : ls.filter((_, idx) => idx !== i)));

  // a material already on this delivery drops out of the other rows' lists
  const availableFor = (i: number) => {
    const taken = new Set(
      lines.filter((l, idx) => idx !== i && l.raw_material_id !== '').map(l => Number(l.raw_material_id)),
    );
    return options.filter(o => !taken.has(o.id));
  };

  const lineTotal  = (l: ReceiptLine) => num(l.qty) * (money(l.unit_cost) ?? 0);
  const grandTotal = lines.reduce((sum, l) => sum + lineTotal(l), 0);

  const filledLines = lines.filter(l => l.raw_material_id !== '' || l.qty !== '' || l.unit_cost !== '');

  const resetForm = () => {
    setSupplierId('');
    setReference('');
    setReceivedDate(today());
    setNotes('');
    setLines([{ ...BLANK_LINE }]);
    setErrors({});
  };

  const validate = () => {
    const e: Record<string, string> = {};
    if (supplierId === '')  e.supplier      = 'Choose a supplier';
    if (!receivedDate)      e.received_date = 'Choose a date';
    if (filledLines.length === 0) e.lines    = 'Add at least one material';

    lines.forEach((l, i) => {
      const touched = l.raw_material_id !== '' || l.qty !== '' || l.unit_cost !== '';
      if (!touched) return;
      if (l.raw_material_id === '')            e[`line_${i}`] = 'Choose a material';
      else if (num(l.qty) <= 0)                e[`line_${i}`] = 'Enter a quantity above 0';
      else if ((money(l.unit_cost) ?? 0) <= 0) e[`line_${i}`] = 'Enter the price you paid';
    });

    return e;
  };

  const canSave = supplierId !== '' && !!receivedDate && filledLines.length > 0;

  // ── Save ──────────────────────────────────────────────────────────────────
  const save = async () => {
    const e = validate();
    setErrors(e);
    if (Object.keys(e).length) {
      setToast({ msg: 'Check the highlighted fields', ok: false });
      return;
    }

    setSaving(true);
    try {
      const body = {
        supplier_id:   Number(supplierId),
        reference:     reference.trim(),
        received_date: receivedDate,
        notes:         notes.trim(),
        lines: lines
          .filter(l => l.raw_material_id !== '')
          .map(l => ({
            raw_material_id: Number(l.raw_material_id),
            qty:             Number(num(l.qty).toFixed(2)),
            unit_cost:       money(l.unit_cost),
          })),
      };
      const encodedData = encodeURIComponent(JSON.stringify(body));
      const response    = await axios.get(`${API_BASE}/stock-receipts/save?data=${encodedData}`);

      if (response.data?.status === 'error') throw new Error(response.data.message);

      const count = body.lines.length;
      setToast({ msg: `${count} ${count === 1 ? 'material' : 'materials'} received into stock`, ok: true });
      resetForm();
      fetchReceipts();
    } catch (error: any) {
      setToast({ msg: error.response?.data?.message || error.message || 'Could not save this delivery.', ok: false });
      console.error(error);
    } finally {
      setSaving(false);
    }
  };

  // the global was rendered at page load, so a new supplier is added to state
  // here rather than refetched
  const onSupplierAdded = (s: SupplierOption) => {
    setShowAddSupplier(false);
    setSuppliers(list => [...list, s].sort((a, b) => a.name.localeCompare(b.name)));
    setSupplierId(s.id);
    clearError('supplier');
    setToast({ msg: `${s.name} added`, ok: true });
  };

  return (
    <div className="min-h-screen bg-slate-100 p-6">
      <div className="w-full space-y-6">

        {/* ── Page header ── */}
        <div className="bg-gradient-to-r from-slate-700 to-slate-800 rounded-xl p-6 flex items-center justify-between shadow-lg">
          <div className="flex items-center gap-3">
            <PackagePlus className="text-sky-400 w-7 h-7" />
            <div>
              <h1 className="text-2xl font-bold text-white">Receive Stock</h1>
              <p className="text-slate-400 text-sm">Book raw materials in against a delivery</p>
            </div>
          </div>
          <span className="bg-sky-500 text-white px-4 py-2 rounded-lg font-semibold text-sm shadow">
            {receipts.length} recent lines
          </span>
        </div>

        {/* ── Delivery ── */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
          <div className="flex items-center gap-2 mb-4">
            <Plus size={18} className="text-sky-500" />
            <h2 className="font-semibold text-slate-800">New delivery</h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

            <div className="lg:col-span-2">
              <label className={lbl}>Supplier{req}</label>
              <div className="flex gap-2">
                <div className="relative flex-1">
                  <select
                    value={supplierId}
                    onChange={e => { setSupplierId(e.target.value === '' ? '' : Number(e.target.value)); clearError('supplier'); }}
                    className={errors.supplier ? `${bad} appearance-none pr-8` : sel}
                  >
                    <option value="">Select supplier</option>
                    {suppliers.map(s => (
                      <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
                </div>
                <button
                  onClick={() => setShowAddSupplier(true)}
                  title="Add a supplier"
                  className="flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 rounded-lg font-semibold text-sm transition-colors shrink-0"
                >
                  <Plus size={16} /> New
                </button>
              </div>
              {errors.supplier
                ? <p className="text-xs text-red-600 mt-1">{errors.supplier}</p>
                : <p className="text-xs text-slate-400 mt-1">Where the stock came from</p>}
            </div>

            <div>
              <label className={lbl}>Date received{req}</label>
              <input
                type="date"
                value={receivedDate}
                max={today()}
                onChange={e => { setReceivedDate(e.target.value); clearError('received_date'); }}
                className={errors.received_date ? bad : inp}
              />
              {errors.received_date && <p className="text-xs text-red-600 mt-1">{errors.received_date}</p>}
            </div>

            <div>
              <label className={lbl}>Delivery note / invoice</label>
              <input
                type="text"
                value={reference}
                onChange={e => setReference(e.target.value)}
                placeholder="DN-48213"
                className={inp}
              />
              <p className="text-xs text-slate-400 mt-1">Their document number</p>
            </div>

            <div className="lg:col-span-2">
              <label className={lbl}>Notes</label>
              <input
                type="text"
                value={notes}
                onChange={e => setNotes(e.target.value)}
                placeholder="Short delivery, damaged drum, who signed"
                className={inp}
              />
            </div>
          </div>

          {/* ── Lines ── */}
          <div className="mt-6 pt-5 border-t border-slate-100">
            <div className="flex items-center justify-between mb-3">
              <div>
                <h3 className="font-semibold text-slate-800 text-sm">What came in</h3>
                <p className="text-xs text-slate-400 mt-0.5">
                  Start typing a code or name, then arrow down and press enter
                </p>
              </div>
              <button
                onClick={addLine}
                disabled={lines.length >= options.length}
                className="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 rounded-lg font-semibold text-xs transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
              >
                <Plus size={14} /> Add line
              </button>
            </div>

            <div className="space-y-2">
              {lines.map((l, i) => {
                const lineBad = !!errors[`line_${i}`];
                const opt     = options.find(o => o.id === Number(l.raw_material_id));

                return (
                  <div key={i}>
                    <div className="grid grid-cols-1 md:grid-cols-12 gap-2 items-center">

                      <div className="md:col-span-5">
                        <MaterialPicker
                          value={l.raw_material_id}
                          options={availableFor(i)}
                          invalid={lineBad && l.raw_material_id === ''}
                          onPick={id => setLine(i, 'raw_material_id', id)}
                        />
                      </div>

                      <div className="md:col-span-2 relative">
                        <input
                          type="text"
                          inputMode="decimal"
                          value={l.qty}
                          onChange={e => setLine(i, 'qty', cleanQty(e.target.value))}
                          placeholder="0.00"
                          className={`${lineBad && l.raw_material_id !== '' && num(l.qty) <= 0 ? bad : inp} pr-10 text-right`}
                        />
                        {opt && (
                          <span className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none">
                            {opt.uom}
                          </span>
                        )}
                      </div>

                      <div className="md:col-span-2 relative">
                        <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">R</span>
                        <input
                          type="text"
                          inputMode="decimal"
                          value={l.unit_cost}
                          onChange={e => setLine(i, 'unit_cost', cleanCost(e.target.value))}
                          placeholder="0.00"
                          className={`${lineBad && num(l.qty) > 0 ? bad : inp} pl-7 text-right`}
                        />
                      </div>

                      <div className="md:col-span-2 text-right tabular-nums text-sm font-semibold text-slate-800 px-2">
                        {lineTotal(l) > 0 ? rands(lineTotal(l)) : <span className="text-slate-300">R 0.00</span>}
                      </div>

                      <div className="md:col-span-1 flex justify-end">
                        <button
                          onClick={() => dropLine(i)}
                          className="text-slate-400 hover:text-red-600 p-2"
                          title="Remove this line"
                        >
                          <Trash2 size={15} />
                        </button>
                      </div>
                    </div>

                    {lineBad && <p className="text-xs text-red-600 mt-1">{errors[`line_${i}`]}</p>}
                  </div>
                );
              })}
            </div>

            {errors.lines && <p className="text-xs text-red-600 mt-2">{errors.lines}</p>}
          </div>

          <div className="flex items-center justify-between gap-4 flex-wrap mt-5 pt-4 border-t border-slate-100">
            <div>
              <p className="text-[11px] uppercase tracking-wide text-slate-400 font-semibold">Delivery total</p>
              <p className="text-xl font-bold text-slate-800 tabular-nums">{rands(grandTotal)}</p>
            </div>

            <div className="flex items-center gap-3">
              {!canSave && (
                <span className="text-xs text-slate-400">Supplier, date and one line are required</span>
              )}
              <button
                onClick={resetForm}
                className="text-slate-500 hover:text-slate-800 text-sm font-semibold px-3 py-2"
              >
                Clear
              </button>
              <button
                onClick={save}
                disabled={saving || !canSave}
                className="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Check className="w-4 h-4" strokeWidth={3} />}
                {saving ? 'Receiving…' : 'Receive into stock'}
              </button>
            </div>
          </div>
        </div>

        {/* ── Recent receipts ── */}
        {loading ? (
          <div className="text-center py-16 text-slate-400">Loading deliveries…</div>
        ) : receipts.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-xl shadow-sm border border-slate-200">
            <PackagePlus size={44} className="mx-auto text-slate-300 mb-4" />
            <p className="text-slate-600 font-medium">Nothing received yet</p>
            <p className="text-slate-400 text-sm mt-1">Book your first delivery above</p>
          </div>
        ) : (
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead className="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide">
                  <tr>
                    <th className="text-left px-4 py-2.5 font-semibold">Date</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Supplier</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Reference</th>
                    <th className="text-left px-3 py-2.5 font-semibold">Material</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Qty</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Price/kg</th>
                    <th className="text-right px-3 py-2.5 font-semibold">Line total</th>
                    <th className="w-36 px-3 py-2.5"></th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                  {receipts.map((r, i) => {
                    const qty  = num(r.qty);
                    const cost = money(r.unit_cost) ?? 0;
                    return (
                      <tr key={r.id ?? i} className="hover:bg-slate-50">
                        <td className="px-4 py-3 text-slate-600 whitespace-nowrap">{r.received_date}</td>
                        <td className="px-3 py-3 font-medium text-slate-800">{r.supplier_name}</td>
                        <td className="px-3 py-3 text-slate-500 text-xs">{r.reference || '—'}</td>
                        <td className="px-3 py-3 text-slate-800">
                          <span className="font-mono text-[11px] text-slate-500 mr-2">{r.material_code}</span>
                          {r.material_name}
                        </td>
                        <td className="px-3 py-3 text-right tabular-nums font-semibold text-slate-900">
                          {qty.toFixed(2)} {r.uom}
                        </td>
                        <td className="px-3 py-3 text-right tabular-nums text-slate-600">{rands(cost)}</td>
                        <td className="px-3 py-3 text-right tabular-nums text-slate-800">{rands(qty * cost)}</td>
                        <td className="px-3 py-3 text-right whitespace-nowrap">
                          <button
                            onClick={() => setHistoryFor({
                              id:   Number(r.raw_material_id),
                              code: r.material_code,
                              name: r.material_name,
                              uom:  r.uom,
                            })}
                            className="inline-flex items-center gap-1 text-sky-600 hover:text-sky-800 font-semibold text-xs px-2 py-1"
                          >
                            <History size={13} /> Supply history
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

      {showAddSupplier && (
        <AddSupplierModal
          onClose={() => setShowAddSupplier(false)}
          onSaved={onSupplierAdded}
          onError={msg => setToast({ msg, ok: false })}
        />
      )}

      {historyFor && (
        <HistoryModal material={historyFor} onClose={() => setHistoryFor(null)} />
      )}

      {/* ── Toast ── */}
      {toast && (
        <div
          className={`fixed bottom-6 right-6 z-[60] px-5 py-3 rounded-lg shadow-xl text-white font-semibold text-sm ${
            toast.ok ? 'bg-emerald-600' : 'bg-red-600'
          }`}
        >
          {toast.msg}
        </div>
      )}
    </div>
  );
};

export default ReceiveStock;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ReceiveStock />);
}