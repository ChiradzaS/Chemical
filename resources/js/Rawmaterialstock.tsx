import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';
import { FlaskConical, Search, Loader2, AlertTriangle, RefreshCw, ArrowUpDown } from 'lucide-react';

declare global {
  interface Window { laravelApiUrl?: string; }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

/* ── Types (match RawMaterialStockController::data) ────────────────────── */
type Status = 'out' | 'low' | 'in' | 'unlimited';

interface Row {
  id: number; code: string; name: string; type: string | null; uom: string;
  onHand: number; reorder: number; lowAt: number; status: Status;
}
interface Data { status: 'ok'; defaultLowQty: number; rows: Row[]; }

type View = 'all' | Status;
type SortKey = 'status' | 'name' | 'onHand' | 'reorder';

/* ── Helpers ───────────────────────────────────────────────────────────── */
const group = (s: string) => s.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
const qty = (n: number) => {
  const neg = n < 0;
  const a = Math.abs(n);
  if (Number.isInteger(a)) return `${neg ? '-' : ''}${group(String(a))}`;
  const [i, d] = a.toFixed(2).split('.');
  return `${neg ? '-' : ''}${group(i)}.${d}`;
};

const STATUS: Record<Status, { label: string; cls: string; rank: number }> = {
  out: { label: 'Out of stock', cls: 'bg-red-100 text-red-700',     rank: 0 },
  low: { label: 'Low',          cls: 'bg-amber-100 text-amber-800', rank: 1 },
  in:  { label: 'In stock',     cls: 'bg-green-100 text-green-800', rank: 2 },
  unlimited: { label: 'Unlimited', cls: 'bg-sky-100 text-sky-800',  rank: 3 },
};

const Kpi = ({ label, value, sub, tone }: { label: string; value: string; sub?: string; tone?: 'red' | 'amber' | 'green' }) => (
  <div className="bg-white border border-gray-200 rounded-xl px-4 py-3">
    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{label}</p>
    <p className={`text-lg font-bold font-mono mt-0.5 leading-tight ${
      tone === 'red' ? 'text-red-600' : tone === 'amber' ? 'text-amber-700' : tone === 'green' ? 'text-green-700' : 'text-gray-900'}`}>{value}</p>
    {sub && <p className="text-[11px] text-gray-500 mt-1">{sub}</p>}
  </div>
);

/* ── Page ──────────────────────────────────────────────────────────────── */
const RawMaterialStock: React.FC = () => {
  const [data, setData]       = useState<Data | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError]     = useState('');
  const [view, setView]       = useState<View>('all');
  const [search, setSearch]   = useState('');
  const [sort, setSort]       = useState<{ key: SortKey; dir: 1 | -1 }>({ key: 'status', dir: 1 });
  const [reloadKey, setReloadKey] = useState(0);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError('');

    axios.get(`${API_BASE}/reports/rawmaterialstock/data`)
      .then(r => {
        if (cancelled) return;
        // Session expired → Laravel answered with the login page instead of JSON
        if (typeof r.data === 'string') { window.location.reload(); return; }
        if (r.data?.status !== 'ok') throw new Error(r.data?.message || 'Could not load the report');
        setData(r.data as Data);
      })
      .catch(e => { if (!cancelled) setError(e?.message || 'Could not load the report'); })
      .finally(() => { if (!cancelled) setLoading(false); });

    return () => { cancelled = true; };
  }, [reloadKey]);

  const rows = data?.rows ?? [];

  const k = useMemo(() => ({
    materials: rows.length,
    in:        rows.filter(r => r.status === 'in').length,
    low:       rows.filter(r => r.status === 'low').length,
    out:       rows.filter(r => r.status === 'out').length,
  }), [rows]);

  const shown = useMemo(() => {
    const term = search.trim().toLowerCase();
    const list = rows.filter(r =>
      (view === 'all' || r.status === view) &&
      (!term || r.name.toLowerCase().includes(term) || r.code.toLowerCase().includes(term)
             || (r.type ?? '').toLowerCase().includes(term)),
    );

    const val = (r: Row): number | string =>
      sort.key === 'status' ? STATUS[r.status].rank * 1e9 + r.onHand   // out first, then low, then in — least stock first
      : sort.key === 'name' ? r.name.toLowerCase()
      : r[sort.key];

    return [...list].sort((a, b) => {
      const x = val(a), y = val(b);
      return (x < y ? -1 : x > y ? 1 : 0) * sort.dir;
    });
  }, [rows, view, search, sort]);

  const VIEWS: { id: View; label: string; count: number }[] = [
    { id: 'all', label: 'All',          count: k.materials },
    { id: 'in',  label: 'In stock',     count: k.in },
    { id: 'low', label: 'Low',          count: k.low },
    { id: 'out', label: 'Out of stock', count: k.out },
  ];

  const Th = ({ k: key, label, right }: { k: SortKey; label: string; right?: boolean }) => (
    <th
      onClick={() => setSort(s => ({ key, dir: s.key === key ? (s.dir === 1 ? -1 : 1) : (key === 'name' || key === 'status' ? 1 : -1) }))}
      className={`px-3 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 whitespace-nowrap ${right ? 'text-right' : 'text-left'}`}
    >
      <span className="inline-flex items-center gap-1">
        {label}<ArrowUpDown className={`w-3 h-3 ${sort.key === key ? 'text-blue-600' : 'text-gray-300'}`} />
      </span>
    </th>
  );

  return (
    <div className="min-h-screen bg-gray-100 text-gray-900">

      {/* ── Top bar ── */}
      <div className="bg-black text-white px-4 py-2.5 flex items-center gap-3 flex-wrap">
        <FlaskConical className="w-4 h-4 text-blue-400" />
        <span className="font-bold text-sm whitespace-nowrap">Reports / Raw Material Stock</span>

        <div className="flex items-center gap-1 bg-gray-900 rounded-lg p-1 flex-wrap">
          {VIEWS.map(v => (
            <button key={v.id} onClick={() => setView(v.id)}
              className={`px-3 py-1 rounded-md text-xs font-bold transition-colors ${
                view === v.id ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'}`}>
              {v.label} <span className="opacity-70">({v.count})</span>
            </button>
          ))}
        </div>

        <div className="relative">
          <Search className="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" />
          <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search material…"
            className="bg-gray-900 border border-gray-700 rounded-md pl-7 pr-2 py-1 text-xs text-white placeholder-gray-500 w-44" />
        </div>

        <button onClick={() => setReloadKey(n => n + 1)}
          className="ml-auto flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-700 text-gray-200 hover:text-white hover:border-gray-500">
          <RefreshCw className={`w-3.5 h-3.5 ${loading ? 'animate-spin' : ''}`} /> Refresh
        </button>
      </div>

      <div className="p-4 flex flex-col gap-4 max-w-[1200px] mx-auto" style={{ maxWidth: 1200, marginLeft: 'auto', marginRight: 'auto', width: '100%' }}>

        {error && (
          <div className="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <AlertTriangle className="w-4 h-4 mt-0.5 shrink-0" /><span>{error}</span>
          </div>
        )}

        {!data && loading && (
          <div className="flex items-center gap-2 text-sm text-gray-400 py-10 justify-center">
            <Loader2 className="w-4 h-4 animate-spin" /> Loading raw materials…
          </div>
        )}

        {data && (
          <div className={`flex flex-col gap-4 transition-opacity ${loading ? 'opacity-50' : ''}`}>

            {/* ── KPIs ── */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
              <Kpi label="Materials"     value={group(String(k.materials))} sub="active raw materials" />
              <Kpi label="In stock"      value={group(String(k.in))}  tone="green" />
              <Kpi label="Low"           value={group(String(k.low))} sub="at or below reorder level" tone={k.low ? 'amber' : undefined} />
              <Kpi label="Out of stock"  value={group(String(k.out))} tone={k.out ? 'red' : undefined} />
            </div>

            {/* ── Stock table ── */}
            <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
              <p className="px-4 pt-3 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Raw material on hand — out of stock first · click a heading to sort
              </p>
              {shown.length === 0 ? (
                <p className="px-4 py-8 text-sm text-gray-400 text-center">No materials match.</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-sm min-w-[520px]">
                    <thead>
                      <tr>
                        <Th k="name"      label="Material" />
                        <Th k="onHand"    label="On hand"       right />
                        <Th k="reorder"   label="Reorder level" right />
                        <Th k="status"    label="Status" />
                      </tr>
                    </thead>
                    <tbody>
                      {shown.map(r => (
                        <tr key={r.id} className="border-t border-gray-100 hover:bg-gray-50">
                          <td className="px-3 py-2">
                            <p className="font-medium text-gray-900">{r.name}</p>
                            <p className="text-[10px] font-mono text-gray-400">
                              {r.code}{r.type ? ` · ${r.type}` : ''}
                            </p>
                          </td>
                          {r.status === 'unlimited' ? (
                            <td className="px-3 py-2 text-right text-lg font-bold text-sky-700 whitespace-nowrap">∞</td>
                          ) : (
                            <td className={`px-3 py-2 text-right font-mono text-lg font-bold whitespace-nowrap ${r.onHand <= 0 ? 'text-red-600' : 'text-green-700'}`}>
                              {qty(r.onHand)} <span className="text-xs font-sans font-semibold">{r.uom}</span>
                            </td>
                          )}
                          <td className="px-3 py-2 text-right font-mono text-gray-500 whitespace-nowrap">
                            {r.status === 'unlimited'
                              ? <span className="text-gray-300">—</span>
                              : r.reorder > 0
                              ? <>{qty(r.reorder)} <span className="text-xs font-sans">{r.uom}</span></>
                              : <span className="text-[10px] font-sans text-gray-400">not set ({qty(r.lowAt)})</span>}
                          </td>
                          <td className="px-3 py-2">
                            <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap ${STATUS[r.status].cls}`}>
                              {STATUS[r.status].label}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            <p className="text-[11px] text-gray-400">
              Low = on hand at or below the material's reorder level. Materials with no reorder level use {data.defaultLowQty}.
              Water, and any material marked "allow negative", is unlimited and never runs out.
            </p>
          </div>
        )}
      </div>
    </div>
  );
};

export default RawMaterialStock;

const rootEl = document.getElementById('root');
if (rootEl) createRoot(rootEl).render(<RawMaterialStock />);