import React, { useState, useEffect, useRef } from 'react';
import { ChevronDown, Save, ArrowLeft, AlertTriangle, CheckCircle, Trash2 } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:         string;
    customersData:         { id: string; name: string }[];
    chemicalProductsData:  { id: string; name: string }[];
    unitTypesData:         { id: string; name: string }[];
    processTypesData:      { id: string; name: string }[];
    stateTypesData:        { id: string; name: string }[];
    containerSizesData:    { id: string; name: string }[];
  }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL = `${API_BASE}/stock-adjustments`; // adjust to wherever "back" should go

// ── Types ─────────────────────────────────────────────────────────────────────

interface Product {
  name: string;
  soh: number;
}

interface Products {
  [key: string]: Product;
}

interface Adjustment {
  id: number;
  datetime: string;
  product: string;
  type: string;
  oldQty: number;
  newQty: number;
  change: number;
  comment: string;
  user: string;
}

type ToastType = 'error' | 'success' | 'warning';

interface ToastState {
  message: string;
  type: ToastType;
  visible: boolean;
  onConfirm?: () => void;
  onCancel?:  () => void;
}

const PRESET_COMMENTS = ['Stolen', 'Damaged', 'Lost', 'Expired', 'Returned to Supplier', 'Data Entry Error', 'Stock Count Correction'];

// ── Shared style primitives (matching ProductForm) ──────────────────────────

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

const typeLabels: { [key: string]: string } = {
  set:      'SET NEW QUANTITY',
  add:      'ADD',
  subtract: 'SUBTRACT',
};

// ── Component ─────────────────────────────────────────────────────────────────

const StockAdjustment: React.FC = () => {
  const [products,        setProducts]        = useState<Products>({});
  const [adjustments,     setAdjustments]     = useState<Adjustment[]>([]);
  const [loadingList,     setLoadingList]     = useState(true);

  const [selectedProduct, setSelectedProduct] = useState('');
  const [productSearch,   setProductSearch]   = useState('');
  const [showProductSuggestions,     setShowProductSuggestions]     = useState(false);
  const [filteredProductSuggestions, setFilteredProductSuggestions] = useState<{ id: string; name: string }[]>([]);
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

  // ── Load products ──────────────────────────────────────────────────────────
  // Names/ids come from the Blade-injected window global (same pattern as
  // ChemicalJobCardList / ProductForm). Stock-on-hand quantities are fetched
  // separately and merged in, since chemicalProductsData only carries id+name.
  useEffect(() => {
    const chemicalProducts = window.chemicalProductsData || [];

    const base: Products = {};
    chemicalProducts.forEach(p => {
      base[String(p.id)] = { name: p.name, soh: 0 };
    });
    setProducts(base);

    const fetchStockLevels = async () => {
      try {
        const response = await axios.get(`${API_BASE}/chemicalproducts/stocklist`);
        const data: any[] = response.data;
        setProducts(prev => {
          const updated = { ...prev };
          data.forEach((s: any) => {
            const key = String(s.productId);
            if (updated[key]) {
              updated[key] = { ...updated[key], soh: Number(s.qnt ?? 0) };
            } else {
              // Product wasn't in window.chemicalProductsData for some reason — add it anyway
              updated[key] = { name: s.name ?? '', soh: Number(s.qnt ?? 0) };
            }
          });
          return updated;
        });
      } catch (err: any) {
        showToast(`Could not load stock levels: ${err.message}`, 'error');
      }
    };
    fetchStockLevels();
  }, []);

  // ── Load history ───────────────────────────────────────────────────────────
  useEffect(() => {
    const fetchAdjustments = async () => {
      setLoadingList(true);
      try {
        const response = await axios.get(`${API_BASE}/stock_adjustment/`);
        const data = response.data;
        const mapped: Adjustment[] = data.map((a: any) => ({
          id:       a.id,
          datetime: a.created_at ?? a.datetime ?? '',
          product:  a.product_name ?? a.product ?? '',
          type:     a.adjustment_type ?? a.type ?? '',
          oldQty:   a.old_quantity ?? a.oldQty ?? 0,
          newQty:   a.new_quantity ?? a.newQty ?? 0,
          change:   a.change ?? 0,
          comment:  a.comment ?? '',
          user:     a.adjusted_by ?? a.user ?? '',
        }));
        setAdjustments(mapped);
      } catch (err: any) {
        console.error('History load error:', err.message);
      } finally {
        setLoadingList(false);
      }
    };
    fetchAdjustments();
  }, []);



  // ── SOH on product change ──────────────────────────────────────────────────
  useEffect(() => {
    if (selectedProduct && products[selectedProduct]) {
      setCurrentSOH(products[selectedProduct].soh);
    } else {
      setCurrentSOH(0);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedProduct, products]);

  // ── Preview ────────────────────────────────────────────────────────────────
  useEffect(() => {
    if (!adjustmentType) { setPreviewQty('-'); return; }
    let preview = 0;
    if      (adjustmentType === 'set')      preview = parseInt(newQty) || 0;
    else if (adjustmentType === 'add')      preview = currentSOH + (parseInt(adjustQty) || 0);
    else if (adjustmentType === 'subtract') preview = currentSOH - (parseInt(adjustQty) || 0);
    setPreviewQty(preview);
  }, [adjustmentType, adjustQty, newQty, currentSOH]);

  const previewStyle: React.CSSProperties = (() => {
    const preview = typeof previewQty === 'number' ? previewQty : parseInt(previewQty as string);
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

  const missing = (v: string) => submitted && !v.trim();

  // ── Submit ─────────────────────────────────────────────────────────────────
  const handleSave = async () => {
    setSubmitted(true);

    if (!selectedProduct) { showToast('Please select a product',          'warning'); return; }
    if (!adjustmentType)  { showToast('Please select an adjustment type', 'warning'); return; }
    if (!comment.trim())  { showToast('Please add a comment / reason',    'warning'); return; }

    const product = products[selectedProduct];
    const oldQty  = Number(product.soh);
    let finalQty  = 0;

    if (adjustmentType === 'set') {
      finalQty = parseInt(newQty);
      if (isNaN(finalQty) || finalQty < 0) { showToast('Please enter a valid new quantity', 'warning'); return; }
    } else if (adjustmentType === 'add') {
      const qty = parseInt(adjustQty);
      if (isNaN(qty) || qty < 0) { showToast('Please enter a valid adjustment quantity', 'warning'); return; }
      finalQty = oldQty + qty;
    } else if (adjustmentType === 'subtract') {
      const qty = parseInt(adjustQty);
      if (isNaN(qty) || qty < 0) { showToast('Please enter a valid adjustment quantity', 'warning'); return; }
      finalQty = oldQty - qty;
      if (finalQty < 0) {
        const ok = await showConfirm(`This will result in negative stock (${finalQty}). Continue?`);
        if (!ok) return;
      }
    }

    setSaving(true);
    try {
      const response = await axios.get(`${API_BASE}/stock_adjustments/`, {
        params: {
          product_id:      selectedProduct,
          product_name:    product.name,
          adjustment_type: adjustmentType,
          old_quantity:    oldQty,
          new_quantity:    finalQty,
          change:          finalQty - oldQty,
          comment,
        },
      });

      const saved = response.data;

      setProducts(prev => ({ ...prev, [selectedProduct]: { ...product, soh: finalQty } }));

      const adjustment: Adjustment = {
        id:       saved.id              ?? Date.now(),
        datetime: saved.created_at      ?? new Date().toISOString(),
        product:  saved.product_name    ?? product.name,
        type:     saved.adjustment_type ?? adjustmentType,
        oldQty:   saved.old_quantity    ?? oldQty,
        newQty:   saved.new_quantity    ?? finalQty,
        change:   saved.change          ?? (finalQty - oldQty),
        comment:  saved.comment         ?? comment,
        user:     saved.adjusted_by     ?? 'Current User',
      };

      setAdjustments(prev => [adjustment, ...prev]);
      setCurrentSOH(finalQty);
      showToast(`Stock adjusted: ${oldQty} → ${finalQty}`, 'success');

      setAdjustmentType(''); setAdjustQty(''); setNewQty(''); setComment(''); setPreviewQty('-'); setSubmitted(false);

    } catch (err: any) {
      const msg = err.response?.data?.message || err.message;
      showToast(`Save failed: ${msg}`, 'error');
    } finally {
      setSaving(false);
    }
  };



  // ── Delete ─────────────────────────────────────────────────────────────────
  const handleDelete = async (id: number) => {
    const ok = await showConfirm('Delete this adjustment? This cannot be undone.');
    if (!ok) return;

    try {
      await axios.delete(`${API_BASE}/stock_adjustments/${id}/`);
      setAdjustments(prev => prev.filter(adj => adj.id !== id));
      showToast('Adjustment deleted successfully', 'success');
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message;
      showToast(`Delete failed: ${msg}`, 'error');
    }
  };

  const productOptions = Object.entries(products).map(([id, p]) => ({ id, name: p.name }));

  const handleProductSearchChange = (value: string) => {
    setProductSearch(value);
    if (value.trim()) {
      setFilteredProductSuggestions(
        productOptions.filter(p => p.name.toLowerCase().includes(value.toLowerCase()))
      );
      setShowProductSuggestions(true);
    } else {
      setFilteredProductSuggestions([]);
      setShowProductSuggestions(false);
      setSelectedProduct('');
    }
  };

  const handleProductSelect = (p: { id: string; name: string }) => {
    setSelectedProduct(p.id);
    setProductSearch(p.name);
    setShowProductSuggestions(false);
  };

  const isConfirm   = !!toast?.onConfirm;
  const accent      = toast?.type === 'success' ? '#22c55e' : toast?.type === 'warning' ? '#f59e0b' : '#ef4444';
  const accentAlpha = toast?.type === 'success' ? 'rgba(34,197,94,0.3)'  : toast?.type === 'warning' ? 'rgba(245,158,11,0.3)'  : 'rgba(239,68,68,0.3)';
  const accentBg    = toast?.type === 'success' ? 'rgba(34,197,94,0.12)' : toast?.type === 'warning' ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)';
  const gradient    = toast?.type === 'success' ? 'linear-gradient(90deg,#22c55e,#16a34a)' : toast?.type === 'warning' ? 'linear-gradient(90deg,#f59e0b,#d97706)' : 'linear-gradient(90deg,#ef4444,#f97316)';

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-gray-100">

      <style>{`
        @keyframes toast-in  { 0% { opacity:0; transform:translate(-50%,-60%) scale(0.85); } 100% { opacity:1; transform:translate(-50%,-50%) scale(1); } }
        @keyframes toast-out { 0% { opacity:1; transform:translate(-50%,-50%) scale(1); } 100% { opacity:0; transform:translate(-50%,-40%) scale(0.9); } }
        .sa-toast-enter { animation: toast-in 0.25s cubic-bezier(0.34,1.56,0.64,1) forwards; }
        .sa-toast-exit  { animation: toast-out 0.35s ease-in forwards; }
        @keyframes toast-progress { 0% { width:100%; } 100% { width:0%; } }
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
        <span className="text-white font-bold text-base truncate max-w-xs">Stock Adjustment</span>

        {selectedProduct && (
          <span className="bg-blue-600 text-white text-sm font-bold px-3 py-1 rounded-full shrink-0">
            {products[selectedProduct]?.name}
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
            onClick={() => {
              setSelectedProduct(''); setProductSearch(''); setShowProductSuggestions(false);
              setAdjustmentType(''); setAdjustQty('');
              setNewQty(''); setComment(''); setPreviewQty('-'); setSubmitted(false);
            }}
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
          <S icon="📦" title="Product" />

          <div className="relative">
            <L t="Product name" required />
            <input
              type="text"
              className={RI(submitted && !selectedProduct)}
              value={productSearch}
              onChange={e => handleProductSearchChange(e.target.value)}
              onFocus={() => productSearch && setShowProductSuggestions(true)}
              placeholder="Search product…"
            />
            {showProductSuggestions && filteredProductSuggestions.length > 0 && (
              <div className="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-72 overflow-y-auto">
                {filteredProductSuggestions.map(p => (
                  <div
                    key={p.id}
                    onClick={() => handleProductSelect(p)}
                    className="px-4 py-2.5 text-base hover:bg-blue-50 cursor-pointer"
                  >
                    {p.name}
                  </div>
                ))}
              </div>
            )}
          </div>

          <div>
            <L t="Current stock on hand" />
            <input className={CI} readOnly value={currentSOH} />
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
                type="number" min="0" step="1"
                placeholder="0"
                value={newQty}
                onChange={e => setNewQty(e.target.value)}
              />
            </div>
          )}

          {(adjustmentType === 'add' || adjustmentType === 'subtract') && (
            <div>
              <L t="Adjust quantity" required />
              <input
                className={RI(submitted && !adjustQty)}
                type="number" min="0" step="1"
                placeholder="0"
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
                {previewQty}
              </div>
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
                    <th className="py-3 pr-4 font-bold">Product</th>
                    <th className="py-3 pr-4 font-bold">Type</th>
                    <th className="py-3 pr-4 font-bold text-right">Old</th>
                    <th className="py-3 pr-4 font-bold text-right">New</th>
                    <th className="py-3 pr-4 font-bold text-right">Change</th>
                    <th className="py-3 pr-4 font-bold">Comment</th>
                    <th className="py-3 pr-4 font-bold">By</th>
                    <th className="py-3 font-bold"></th>
                  </tr>
                </thead>
                <tbody>
                  {adjustments.slice(0, 30).map(adj => (
                    <tr key={adj.id} className="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                      <td className="py-3 pr-4 text-gray-500">{formatDate(adj.datetime)}</td>
                      <td className="py-3 pr-4 font-semibold text-gray-800">{adj.product}</td>
                      <td className="py-3 pr-4">
                        <span className={`inline-block px-2.5 py-1 rounded text-xs font-bold uppercase ${
                          adj.type === 'set' ? 'bg-blue-50 text-blue-700'
                          : adj.type === 'add' ? 'bg-green-50 text-green-700'
                          : 'bg-red-50 text-red-700'
                        }`}>
                          {typeLabels[adj.type] ?? adj.type}
                        </span>
                      </td>
                      <td className="py-3 pr-4 text-right text-gray-600">{adj.oldQty}</td>
                      <td className="py-3 pr-4 text-right text-gray-600">{adj.newQty}</td>
                      <td className={`py-3 pr-4 text-right font-bold ${adj.change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                        {adj.change > 0 ? '+' : ''}{adj.change}
                      </td>
                      <td className="py-3 pr-4 text-gray-500 max-w-[200px] truncate" title={adj.comment}>{adj.comment}</td>
                      <td className="py-3 pr-4 text-gray-500">{adj.user}</td>
                      <td className="py-3">
                        <button
                          onClick={() => handleDelete(adj.id)}
                          className="text-red-500 hover:text-red-700 transition-colors"
                          title="Delete"
                        >
                          <Trash2 className="w-4 h-4" />
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

export default StockAdjustment;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<StockAdjustment />);
}