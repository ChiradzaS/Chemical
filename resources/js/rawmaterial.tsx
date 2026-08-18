import React, { useState, useEffect, useRef } from 'react';
import { ChevronDown, Save, ArrowLeft, AlertTriangle, CheckCircle, Undo2 } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl: string;
  }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL = `${API_BASE}/raw-materials`;

// ── Types ─────────────────────────────────────────────────────────────────────

interface Material {
  id:             number;
  code:           string;
  name:           string;
  uom:            string;
  soh:            number;
  allow_negative: boolean;
}

interface Adjustment {
  id:       number;
  datetime: string;
  code:     string;
  material: string;
  uom:      string;
  oldQty:   number;
  newQty:   number;
  change:   number;
  comment:  string;
  user:     string;
}

type ToastType = 'error' | 'success' | 'warning';

interface ToastState {
  message: string;
  type: ToastType;
  visible: boolean;
  onConfirm?: () => void;
  onCancel?:  () => void;
}

const PRESET_COMMENTS = [
  'Stock Count Correction', 'Spillage', 'Damaged', 'Expired',
  'Contaminated', 'Returned to Supplier', 'Data Entry Error',
];

const truthy = (v: any) => v === true || Number(v) === 1;

// quantities carry 2 decimals — chemicals get issued in fractions of a kg
const num = (v: any) => {
  const n = Number(String(v ?? '').trim());
  return Number.isFinite(n) ? n : 0;
};
const round2 = (n: number) => Math.round(n * 100) / 100;

// ── Shared style primitives ─────────────────────────────────────────────────

const I  = "w-full bg-white text-gray-800 border border-gray-200 rounded-lg px-3.5 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all";
const CI = "w-full bg-gray-50 text-gray-400 border border-dashed border-gray-300 rounded-lg px-3.5 py-2.5 text-base cursor-not-allowed";
const RI = (err: boolean) => `${I} ${err ? '!border-red-400 focus:!ring-red-400' : ''}`;

const L = ({ t, required }: { t: string; required?: boolean }) => (
  <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">
    {t}{required && <span className="text-red-400 ml-0.5">*</span>}
  </p>
);

const SelId = ({ v, onCh, data, ph = 'Select…', err }: {
  v: string; onCh: (id: string) => void; data: { id: string; name: string }[]; ph?: string; err?: boolean;
}) => (
  <div className="relative">
    <select className={`${RI(!!err)} appearance-none pr-9`} value={v} onChange={e => onCh(e.target.value)}>
      <option value="">{ph}</option>
      {data.map(item => <option key={item.id} value={item.id}>{item.name}</option>)}
    </select>
    <ChevronDown className="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
  </div>
);

const S = ({ icon, title }: { icon: string; title: string }) => (
  <div className="flex items-center gap-2.5">
    <span className="text-base">{icon}</span>
    <span className="text-xs font-black text-gray-400 uppercase tracking-widest whitespace-nowrap">{title}</span>
    <div className="flex-1 h-px bg-gray-100" />
  </div>
);

// ── Component ─────────────────────────────────────────────────────────────────

const RawMaterialAdjustment: React.FC = () => {
  const [materials,   setMaterials]   = useState<Material[]>([]);
  const [adjustments, setAdjustments] = useState<Adjustment[]>([]);
  const [loadingList, setLoadingList] = useState(true);

  const [selectedId,      setSelectedId]      = useState<number | ''>('');
  const [materialSearch,  setMaterialSearch]  = useState('');
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [currentSOH,      setCurrentSOH]      = useState(0);
  const [adjustmentType,  setAdjustmentType]  = useState('');
  const [adjustQty,       setAdjustQty]       = useState('');
  const [newQty,          setNewQty]          = useState('');
  const [previewQty,      setPreviewQty]      = useState<string | number>('-');
  const [comment,         setComment]         = useState('');
  const [saving,          setSaving]          = useState(false);
  const [submitted,       setSubmitted]       = useState(false);

  const [toast, setToast] = useState<ToastState | null>(null);
  const toastTimerRef     = useRef<ReturnType<typeof setTimeout> | null>(null);

  const selected = materials.find(m => m.id === Number(selectedId)) || null;

  const showToast = (message: string, type: ToastType = 'error') => {
    if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
    setToast({ message, type, visible: true });
    toastTimerRef.current = setTimeout(() => {
      setToast(prev => prev ? { ...prev, visible: false } : null);
      setTimeout(() => setToast(null), 400);
    }, 2500);
  };

  const showConfirm = (message: string): Promise<boolean> => {
    if (toastTimerRef.current) clearTimeout(toastTimerRef.current);
    return new Promise(resolve => {
      setToast({
        message,
        type: 'warning',
        visible: true,
        onConfirm: () => { setToast(null); resolve(true); },
        onCancel:  () => { setToast(null); resolve(false); },
      });
    });
  };

  // ── Load materials ─────────────────────────────────────────────────────────
  // Raw materials carry their balance on the row itself, so one call gets both
  // the list and the stock on hand.
  useEffect(() => {
    const fetchMaterials = async () => {
      try {
        const payload     = { search: '', include_inactive: 0 };
        const encodedData = encodeURIComponent(JSON.stringify(payload));
        const response    = await axios.get(`${API_BASE}/raw-materials/list?data=${encodedData}`);
        const data: any[] = Array.isArray(response.data) ? response.data : [];

        setMaterials(
          data
            .filter(m => truthy(m.is_active))
            .map(m => ({
              id:             Number(m.id),
              code:           m.code,
              name:           m.name,
              uom:            m.uom,
              soh:            num(m.stock_on_hand),
              allow_negative: truthy(m.allow_negative),
            })),
        );
      } catch (err: any) {
        showToast(`Could not load materials: ${err.message}`, 'error');
      }
    };
    fetchMaterials();
  }, []);

  // ── Load history ───────────────────────────────────────────────────────────
  const fetchAdjustments = async () => {
    setLoadingList(true);
    try {
      const payload     = { limit: 50 };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/raw-material-adjustments/list?data=${encodedData}`);
      const data: any[] = Array.isArray(response.data) ? response.data : [];

      setAdjustments(data.map(a => ({
        id:       a.id,
        datetime: a.trans_date ?? a.created_at ?? '',
        code:     a.material_code ?? '',
        material: a.material_name ?? '',
        uom:      a.uom ?? '',
        oldQty:   num(a.old_quantity),
        newQty:   num(a.balance_after),
        change:   num(a.change),
        comment:  a.notes ?? '',
        user:     a.user_name ?? '',
      })));
    } catch (err: any) {
      console.error('History load error:', err.message);
    } finally {
      setLoadingList(false);
    }
  };

  useEffect(() => { fetchAdjustments(); }, []);

  // ── SOH on material change ─────────────────────────────────────────────────
  useEffect(() => {
    setCurrentSOH(selected ? selected.soh : 0);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedId, materials]);

  // ── Preview ────────────────────────────────────────────────────────────────
  useEffect(() => {
    if (!adjustmentType) { setPreviewQty('-'); return; }
    let preview = 0;
    if      (adjustmentType === 'set')      preview = round2(num(newQty));
    else if (adjustmentType === 'add')      preview = round2(currentSOH + num(adjustQty));
    else if (adjustmentType === 'subtract') preview = round2(currentSOH - num(adjustQty));
    setPreviewQty(preview);
  }, [adjustmentType, adjustQty, newQty, currentSOH]);

  const previewStyle: React.CSSProperties = (() => {
    const preview = typeof previewQty === 'number' ? previewQty : num(previewQty);
    if (isNaN(preview))       return {};
    if (preview < 0)          return { background: '#fef2f2', color: '#b91c1c', borderColor: '#fca5a5' };
    if (preview > currentSOH) return { background: '#f0fdf4', color: '#15803d', borderColor: '#86efac' };
    if (preview < currentSOH) return { background: '#fff7ed', color: '#c2410c', borderColor: '#fed7aa' };
    return { background: '#eff6ff', color: '#1d4ed8', borderColor: '#bfdbfe' };
  })();

  const formatDate = (raw: string): string => {
    if (!raw) return '—';
    const d = new Date(raw);
    if (isNaN(d.getTime())) return raw;
    return d.toLocaleDateString('en-ZA', { year: 'numeric', month: '2-digit', day: '2-digit' });
  };

  // ── Submit ─────────────────────────────────────────────────────────────────
  const handleSave = async () => {
    setSubmitted(true);

    if (!selected)       { showToast('Please select a material',          'warning'); return; }
    if (!adjustmentType) { showToast('Please select an adjustment type',  'warning'); return; }
    if (!comment.trim()) { showToast('Please add a comment / reason',     'warning'); return; }

    const oldQty = selected.soh;
    let finalQty = 0;

    if (adjustmentType === 'set') {
      if (newQty.trim() === '') { showToast('Please enter a valid new quantity', 'warning'); return; }
      finalQty = round2(num(newQty));
      if (finalQty < 0) { showToast('Please enter a valid new quantity', 'warning'); return; }
    } else {
      const qty = round2(num(adjustQty));
      if (qty <= 0) { showToast('Please enter a valid adjustment quantity', 'warning'); return; }
      finalQty = round2(adjustmentType === 'add' ? oldQty + qty : oldQty - qty);
    }

    if (finalQty === oldQty) { showToast('That leaves the stock unchanged', 'warning'); return; }

    // the material's own flag decides whether negative stock is even allowed
    if (finalQty < 0) {
      if (!selected.allow_negative) {
        showToast(`${selected.name} cannot go negative — only ${oldQty} ${selected.uom} on hand`, 'error');
        return;
      }
      const ok = await showConfirm(`This will result in negative stock (${finalQty}). Continue?`);
      if (!ok) return;
    }

    setSaving(true);
    try {
      const payload = {
        raw_material_id: selected.id,
        adjustment_type: adjustmentType,
        old_quantity:    oldQty,
        new_quantity:    finalQty,
        change:          round2(finalQty - oldQty),
        comment:         comment.trim(),
      };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/raw-material-adjustments/save?data=${encodedData}`);

      if (response.data?.status === 'error') throw new Error(response.data.message);

      setMaterials(prev => prev.map(m => (m.id === selected.id ? { ...m, soh: finalQty } : m)));
      setCurrentSOH(finalQty);
      showToast(`${selected.code}: ${oldQty} → ${finalQty} ${selected.uom}`, 'success');

      fetchAdjustments();
      setAdjustmentType(''); setAdjustQty(''); setNewQty(''); setComment(''); setPreviewQty('-'); setSubmitted(false);
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message;
      showToast(`Save failed: ${msg}`, 'error');
    } finally {
      setSaving(false);
    }
  };

  // ── Reverse ────────────────────────────────────────────────────────────────
  // The ledger is never edited: a mistake is undone by posting the opposite
  // movement, so the stock balance and the paper trail stay in step.
  const handleReverse = async (adj: Adjustment) => {
    const ok = await showConfirm(`Reverse this adjustment? ${adj.change > 0 ? '+' : ''}${adj.change} will be posted back.`);
    if (!ok) return;

    try {
      const encodedData = encodeURIComponent(JSON.stringify({ id: adj.id }));
      const response    = await axios.get(`${API_BASE}/raw-material-adjustments/reverse?data=${encodedData}`);

      if (response.data?.status === 'error') throw new Error(response.data.message);

      const balance = num(response.data.balance_after);
      setMaterials(prev => prev.map(m => (m.name === adj.material ? { ...m, soh: balance } : m)));
      if (selected && selected.name === adj.material) setCurrentSOH(balance);

      showToast('Adjustment reversed', 'success');
      fetchAdjustments();
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message;
      showToast(`Reverse failed: ${msg}`, 'error');
    }
  };

  // ── Material search ────────────────────────────────────────────────────────
  const term = materialSearch.trim().toLowerCase();
  const suggestions = term === ''
    ? []
    : materials.filter(m =>
        m.code.toLowerCase().includes(term) || m.name.toLowerCase().includes(term));

  const handleSearchChange = (value: string) => {
    setMaterialSearch(value);
    setShowSuggestions(value.trim() !== '');
    if (value.trim() === '') setSelectedId('');
  };

  const handleSelect = (m: Material) => {
    setSelectedId(m.id);
    setMaterialSearch(`${m.code} — ${m.name}`);
    setShowSuggestions(false);
  };

  const resetAll = () => {
    setSelectedId(''); setMaterialSearch(''); setShowSuggestions(false);
    setAdjustmentType(''); setAdjustQty('');
    setNewQty(''); setComment(''); setPreviewQty('-'); setSubmitted(false);
  };

  const isConfirm   = !!toast?.onConfirm;
  const accent      = toast?.type === 'success' ? '#22c55e' : toast?.type === 'warning' ? '#f59e0b' : '#ef4444';
  const accentAlpha = toast?.type === 'success' ? 'rgba(34,197,94,0.3)'  : toast?.type === 'warning' ? 'rgba(245,158,11,0.3)'  : 'rgba(239,68,68,0.3)';
  const accentBg    = toast?.type === 'success' ? 'rgba(34,197,94,0.12)' : toast?.type === 'warning' ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)';

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-gray-100">

      <style>{`
        @keyframes toast-in  { 0% { opacity:0; transform:translate(-50%,-60%) scale(0.85); } 100% { opacity:1; transform:translate(-50%,-50%) scale(1); } }
        @keyframes toast-out { 0% { opacity:1; transform:translate(-50%,-50%) scale(1); } 100% { opacity:0; transform:translate(-50%,-40%) scale(0.9); } }
        .sa-toast-enter { animation: toast-in 0.25s cubic-bezier(0.34,1.56,0.64,1) forwards; }
        .sa-toast-exit  { animation: toast-out 0.35s ease-in forwards; }
        .sa-confirm-btn { transition: filter 0.15s, transform 0.1s; cursor: pointer; border: none; border-radius: 8px; padding: 12px 32px; font-size: 15px; font-weight: 700; }
        .sa-confirm-btn:hover  { filter: brightness(1.12); transform: translateY(-1px); }
        .sa-confirm-btn:active { transform: translateY(0); }
      `}</style>

      {/* ── Toast ── */}
      {toast && !isConfirm && (
        <div className={`fixed top-5 left-1/2 -translate-x-1/2 z-50 px-6 py-3 rounded-xl text-base font-semibold shadow-lg flex items-center gap-2.5 ${
          toast.type === 'success'
            ? 'bg-green-50 text-green-700 border border-green-200'
            : toast.type === 'warning'
            ? 'bg-amber-50 text-amber-700 border border-amber-200'
            : 'bg-red-50 text-red-700 border border-red-200'
        }`}>
          {toast.type === 'success' ? '✓' : toast.type === 'warning' ? '!' : '✕'} {toast.message}
        </div>
      )}

      {/* ── Header ── */}
      <div className="h-16 bg-black flex items-center px-6 gap-4 shrink-0">
        <button
          onClick={() => { window.location.href = LIST_URL; }}
          className="text-gray-500 hover:text-white transition-colors"
        >
          <ArrowLeft className="w-5 h-5" />
        </button>
        <div className="w-px h-6 bg-gray-800" />
        <span className="text-white font-bold text-base truncate max-w-xs">Raw Material Adjustment</span>

        {selected && (
          <span className="bg-blue-600 text-white text-sm font-bold px-3 py-1 rounded-full shrink-0">
            {selected.code}
          </span>
        )}

        {adjustmentType && (
          <div className="hidden xl:flex items-center gap-5 ml-2 text-sm text-gray-500 border-l border-gray-800 pl-5 shrink-0">
            <span>Current <strong className="text-white">{currentSOH}</strong></span>
            <span>New <strong className="text-white">{previewQty}</strong></span>
            <span className={
              typeof previewQty === 'number' && previewQty < 0 ? 'text-red-400'
                : typeof previewQty === 'number' && previewQty > currentSOH ? 'text-green-400'
                : typeof previewQty === 'number' && previewQty < currentSOH ? 'text-amber-400'
                : 'text-blue-400'
            }>
              Change <strong>{typeof previewQty === 'number' ? (previewQty - currentSOH > 0 ? '+' : '') + (previewQty - currentSOH) : '-'}</strong>
            </span>
          </div>
        )}

        <div className="ml-auto flex items-center gap-3 shrink-0">
          <button
            onClick={resetAll}
            className="text-gray-500 hover:text-white text-sm px-4 py-2 border border-gray-700 rounded-lg transition-colors"
          >
            Reset
          </button>
          <button
            onClick={handleSave}
            disabled={saving}
            className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg text-sm font-bold transition-colors"
          >
            <Save className="w-4 h-4" />
            {saving ? 'Saving…' : 'Add Adjustment'}
          </button>
        </div>
      </div>

      {/* ── Body: 2-column, form + history ── */}
      <div className="flex-1 overflow-hidden flex gap-4 p-4">

        {/* ═══════════════
            COL 1 — ADJUSTMENT FORM
        ═══════════════ */}
        <div className="w-full max-w-md bg-white rounded-xl border border-gray-200 shadow-sm p-5 overflow-y-auto flex flex-col gap-4">
          <S icon="🧪" title="Material" />

          <div className="relative">
            <L t="Material code or name" required />
            <input
              type="text"
              className={RI(submitted && !selected)}
              value={materialSearch}
              onChange={e => handleSearchChange(e.target.value)}
              onFocus={() => materialSearch && setShowSuggestions(true)}
              placeholder="Search material…"
            />
            {showSuggestions && suggestions.length > 0 && (
              <div className="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-72 overflow-y-auto">
                {suggestions.map(m => (
                  <div
                    key={m.id}
                    onClick={() => handleSelect(m)}
                    className="px-4 py-2.5 text-base hover:bg-blue-50 cursor-pointer flex items-center gap-2"
                  >
                    <span className="font-mono text-xs text-gray-400 w-20 shrink-0">{m.code}</span>
                    <span className="truncate">{m.name}</span>
                    <span className="ml-auto text-xs text-gray-400">{m.soh.toFixed(2)} {m.uom}</span>
                  </div>
                ))}
              </div>
            )}
          </div>

          <div>
            <L t="Current stock on hand" />
            <input className={CI} readOnly value={selected ? `${currentSOH.toFixed(2)} ${selected.uom}` : currentSOH.toFixed(2)} />
          </div>

          <S icon="🔧" title="Adjustment" />

          <div>
            <L t="Adjustment type" required />
            <SelId
              v={adjustmentType}
              onCh={setAdjustmentType}
              data={[
                { id: 'set',      name: 'Set New Quantity' },
                { id: 'add',      name: 'Add' },
                { id: 'subtract', name: 'Subtract' },
              ]}
              ph="Select type…"
              err={submitted && !adjustmentType}
            />
          </div>

          {adjustmentType === 'set' && (
            <div>
              <L t="Set quantity" required />
              <input
                className={RI(submitted && !newQty)}
                type="number" min="0" step="0.01"
                placeholder="0.00"
                value={newQty}
                onChange={e => setNewQty(e.target.value)}
              />
              <p className="text-xs text-gray-400 mt-1">Counted quantity — the ledger records the difference</p>
            </div>
          )}

          {(adjustmentType === 'add' || adjustmentType === 'subtract') && (
            <div>
              <L t="Adjust quantity" required />
              <input
                className={RI(submitted && !adjustQty)}
                type="number" min="0" step="0.01"
                placeholder="0.00"
                value={adjustQty}
                onChange={e => setAdjustQty(e.target.value)}
              />
            </div>
          )}

          {adjustmentType && (
            <div className="bg-gray-50 rounded-xl p-4 border border-gray-100">
              <L t="Resulting quantity" />
              <div
                className="w-full rounded-lg px-3.5 py-3 text-base font-bold text-center border-2 mt-1"
                style={previewStyle}
              >
                {typeof previewQty === 'number' ? previewQty.toFixed(2) : previewQty}{selected ? ` ${selected.uom}` : ''}
              </div>
              {typeof previewQty === 'number' && previewQty < 0 && selected && !selected.allow_negative && (
                <p className="text-xs text-red-600 mt-2 text-center font-semibold">
                  This material is not allowed to go negative
                </p>
              )}
            </div>
          )}

          <S icon="📝" title="Reason" />

          <div>
            <L t="Comment / reason" required />
            <div className="flex flex-wrap gap-2 mb-2.5">
              {PRESET_COMMENTS.map(preset => (
                <button
                  key={preset}
                  type="button"
                  onClick={() => setComment(preset)}
                  className={`px-3 py-1.5 text-xs font-semibold rounded-full border transition-colors ${
                    comment === preset
                      ? 'bg-blue-600 text-white border-blue-600'
                      : 'bg-gray-50 text-gray-500 border-gray-200 hover:border-blue-300'
                  }`}
                >
                  {preset}
                </button>
              ))}
            </div>
            <textarea
              className={`${RI(submitted && !comment.trim())} min-h-[88px] resize-y`}
              placeholder="Enter reason for adjustment or select from presets above…"
              value={comment}
              onChange={e => setComment(e.target.value)}
            />
          </div>

          <div className="pt-1">
            <button
              onClick={handleSave}
              disabled={saving}
              className="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-base font-bold py-3 rounded-xl flex items-center justify-center gap-2 transition-colors"
            >
              <Save className="w-5 h-5" />
              {saving ? 'Saving…' : 'Add Adjustment'}
            </button>
          </div>
        </div>

        {/* ═══════════════════════════
            COL 2 — HISTORY
        ═══════════════════════════ */}
        <div className="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm p-5 overflow-y-auto flex flex-col gap-4">
          <S icon="🕒" title="Recent Adjustments" />

          {loadingList ? (
            <div className="flex-1 flex items-center justify-center text-base text-gray-400">Loading…</div>
          ) : adjustments.length === 0 ? (
            <div className="flex-1 flex items-center justify-center text-base text-gray-400">No stock adjustments yet</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="text-left text-gray-400 uppercase text-xs tracking-wider border-b border-gray-100">
                    <th className="py-3 pr-4 font-bold">Date</th>
                    <th className="py-3 pr-4 font-bold">Material</th>
                    <th className="py-3 pr-4 font-bold">Type</th>
                    <th className="py-3 pr-4 font-bold text-right">Old</th>
                    <th className="py-3 pr-4 font-bold text-right">New</th>
                    <th className="py-3 pr-4 font-bold text-right">Change</th>
                    <th className="py-3 pr-4 font-bold">Reason</th>
                    <th className="py-3 pr-4 font-bold">By</th>
                    <th className="py-3 font-bold"></th>
                  </tr>
                </thead>
                <tbody>
                  {adjustments.slice(0, 30).map(adj => (
                    <tr key={adj.id} className="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                      <td className="py-3 pr-4 text-gray-500 whitespace-nowrap">{formatDate(adj.datetime)}</td>
                      <td className="py-3 pr-4 font-semibold text-gray-800">
                        <span className="font-mono text-xs text-gray-400 mr-2">{adj.code}</span>
                        {adj.material}
                      </td>
                      <td className="py-3 pr-4">
                        <span className={`inline-block px-2.5 py-1 rounded text-xs font-bold uppercase ${
                          adj.change >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                        }`}>
                          {adj.change >= 0 ? 'Increase' : 'Decrease'}
                        </span>
                      </td>
                      <td className="py-3 pr-4 text-right text-gray-600">{adj.oldQty.toFixed(2)}</td>
                      <td className="py-3 pr-4 text-right text-gray-600">{adj.newQty.toFixed(2)}</td>
                      <td className={`py-3 pr-4 text-right font-bold ${adj.change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                        {adj.change > 0 ? '+' : ''}{adj.change.toFixed(2)}
                      </td>
                      <td className="py-3 pr-4 text-gray-500 max-w-[200px] truncate" title={adj.comment}>{adj.comment}</td>
                      <td className="py-3 pr-4 text-gray-500">{adj.user || '—'}</td>
                      <td className="py-3">
                        <button
                          onClick={() => handleReverse(adj)}
                          className="text-amber-600 hover:text-amber-800 transition-colors"
                          title="Reverse this adjustment"
                        >
                          <Undo2 className="w-4 h-4" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

      </div>

      {/* ── Confirm modal ── */}
      {toast && isConfirm && (
        <div
          className={toast.visible ? 'sa-toast-enter' : 'sa-toast-exit'}
          style={{ position: 'fixed', top: '50%', left: '50%', transform: 'translate(-50%,-50%)', zIndex: 9999 }}
        >
          <div style={{ background: 'linear-gradient(135deg,#1a1a2e 0%,#16213e 100%)', border: `1px solid ${accentAlpha}`, borderRadius: '16px', padding: '32px 40px', display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '18px', boxShadow: '0 32px 80px rgba(0,0,0,0.6)', minWidth: '340px', textAlign: 'center' }}>
            <div style={{ width: '64px', height: '64px', borderRadius: '50%', background: accentBg, border: `2px solid ${accentAlpha}`, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              {toast.type === 'success' ? <CheckCircle size={30} color={accent} /> : <AlertTriangle size={30} color={accent} />}
            </div>
            <div style={{ fontSize: '18px', fontWeight: 800, color: '#fff', letterSpacing: '-0.01em', lineHeight: 1.4 }}>
              {toast.message}
            </div>
            <div style={{ display: 'flex', gap: '14px' }}>
              <button className="sa-confirm-btn" onClick={toast.onCancel} style={{ background: 'rgba(255,255,255,0.08)', color: '#9ca3af', border: '1px solid rgba(255,255,255,0.15)' }}>
                Cancel
              </button>
              <button className="sa-confirm-btn" onClick={toast.onConfirm} style={{ background: 'linear-gradient(135deg,#ef4444,#dc2626)', color: '#fff' }}>
                Confirm
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default RawMaterialAdjustment;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<RawMaterialAdjustment />);
}