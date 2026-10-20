import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';
import { Factory, Search, Loader2, AlertTriangle, TrendingUp, TrendingDown, ArrowUpDown } from 'lucide-react';

declare global {
  interface Window { laravelApiUrl?: string; }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

/* ── Types (match ProductionReportController::data) ────────────────────── */
interface Totals { runs: number; products: number; units: number; }
interface Row { id: number; name: string; runs: number; units: number; lastMade: string | null; }
interface Data {
  status: 'ok';
  period: { from: string; to: string };
  prior:  { from: string; to: string };
  totals: Totals;
  priorTotals: Totals;
  products: Row[];
}

type Preset = 'today' | 'yesterday' | 'week' | 'month' | 'lastMonth' | 'custom';
type SortKey = 'name' | 'runs' | 'units' | 'lastMade';

const PRESETS: { id: Preset; label: string }[] = [
  { id: 'today',     label: 'Today' },
  { id: 'yesterday', label: 'Yesterday' },
  { id: 'week',      label: 'This week' },
  { id: 'month',     label: 'This month' },
  { id: 'lastMonth', label: 'Last month' },
  { id: 'custom',    label: 'Custom' },
];

/* ── Helpers ───────────────────────────────────────────────────────────── */
const ymd = (d: Date) =>
  `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

const rangeFor = (p: Exclude<Preset, 'custom'>): [string, string] => {
  const now = new Date();
  const y = now.getFullYear(), m = now.getMonth(), d = now.getDate();
  if (p === 'yesterday') { const t = new Date(y, m, d - 1); return [ymd(t), ymd(t)]; }
  if (p === 'week') {
    const monday = new Date(y, m, d - ((now.getDay() + 6) % 7));   // week starts on Monday
    return [ymd(monday), ymd(now)];
  }
  if (p === 'month')     return [ymd(new Date(y, m, 1)), ymd(now)];
  if (p === 'lastMonth') return [ymd(new Date(y, m - 1, 1)), ymd(new Date(y, m, 0))];
  return [ymd(now), ymd(now)];
};

const group = (s: string) => s.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
const qty = (n: number) => {
  if (Number.isInteger(n)) return group(String(n));
  const [i, d] = n.toFixed(2).split('.');
  return `${group(i)}.${d}`;
};
const niceDate = (s: string | null) =>
  s ? new Date(`${s}T00:00:00`).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

const Delta = ({ cur, prev }: { cur: number; prev: number }) => {
  if (!prev) return <span className="text-[11px] text-gray-400">Nothing in prior period</span>;
  const pct = ((cur - prev) / Math.abs(prev)) * 100;
  const up  = pct >= 0;
  return (
    <span className={`text-[11px] font-semibold inline-flex items-center gap-1 ${up ? 'text-green-700' : 'text-red-600'}`}>
      {up ? <TrendingUp className="w-3 h-3" /> : <TrendingDown className="w-3 h-3" />}
      {up ? '+' : ''}{pct.toFixed(1)}% vs prior
    </span>
  );
};

const Kpi = ({ label, value, sub }: { label: string; value: string; sub?: React.ReactNode }) => (
  <div className="bg-white border border-gray-200 rounded-xl px-4 py-3">
    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{label}</p>
    <p className="text-lg font-bold font-mono text-gray-900 mt-0.5 leading-tight">{value}</p>
    {sub && <div className="mt-1">{sub}</div>}
  </div>
);

/* ── Page ──────────────────────────────────────────────────────────────── */
const ProductionReport: React.FC = () => {
  const [preset, setPreset]    = useState<Preset>('today');
  const [[from, to], setRange] = useState<[string, string]>(rangeFor('today'));
  const [data, setData]        = useState<Data | null>(null);
  const [loading, setLoading]  = useState(true);
  const [error, setError]      = useState('');
  const [search, setSearch]    = useState('');
  const [sort, setSort]        = useState<{ key: SortKey; dir: 1 | -1 }>({ key: 'units', dir: -1 });

  const pickPreset = (p: Preset) => {
    setPreset(p);
    if (p !== 'custom') setRange(rangeFor(p));
  };

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError('');

    const enc = encodeURIComponent(JSON.stringify({ from, to }));
    axios.get(`${API_BASE}/reports/production/data?data=${enc}`)
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
  }, [from, to]);

  const t  = data?.totals;
  const pt = data?.priorTotals;
  const maxUnits = Math.max(1, ...(data?.products ?? []).map(p => p.units));

  const shown = useMemo(() => {
    const term = search.trim().toLowerCase();
    const list = (data?.products ?? []).filter(p => !term || p.name.toLowerCase().includes(term));
    const val = (r: Row): number | string =>
      sort.key === 'name' ? r.name.toLowerCase() : sort.key === 'lastMade' ? (r.lastMade ?? '') : r[sort.key];
    return [...list].sort((a, b) => {
      const x = val(a), y = val(b);
      return (x < y ? -1 : x > y ? 1 : 0) * sort.dir;
    });
  }, [data, search, sort]);

  const Th = ({ k, label, right }: { k: SortKey; label: string; right?: boolean }) => (
    <th
      onClick={() => setSort(s => ({ key: k, dir: s.key === k ? (s.dir === 1 ? -1 : 1) : (k === 'name' ? 1 : -1) }))}
      className={`px-3 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 whitespace-nowrap ${right ? 'text-right' : 'text-left'}`}
    >
      <span className="inline-flex items-center gap-1">
        {label}<ArrowUpDown className={`w-3 h-3 ${sort.key === k ? 'text-blue-600' : 'text-gray-300'}`} />
      </span>
    </th>
  );

  return (
    <div className="min-h-screen bg-gray-100 text-gray-900">

      {/* ── Top bar ── */}
      <div className="bg-black text-white px-4 py-2.5 flex items-center gap-3 flex-wrap">
        <Factory className="w-4 h-4 text-blue-400" />
        <span className="font-bold text-sm whitespace-nowrap">Reports / Production</span>

        <div className="flex items-center gap-1 bg-gray-900 rounded-lg p-1 flex-wrap">
          {PRESETS.map(p => (
            <button key={p.id} onClick={() => pickPreset(p.id)}
              className={`px-3 py-1 rounded-md text-xs font-bold transition-colors ${
                preset === p.id ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'}`}>
              {p.label}
            </button>
          ))}
        </div>

        {preset === 'custom' && (
          <div className="flex items-center gap-1.5 text-xs">
            <input type="date" value={from} max={to}
              onChange={e => e.target.value && setRange([e.target.value, to])}
              className="bg-gray-900 border border-gray-700 rounded-md px-2 py-1 text-white [color-scheme:dark]" />
            <span className="text-gray-500">to</span>
            <input type="date" value={to} min={from}
              onChange={e => e.target.value && setRange([from, e.target.value])}
              className="bg-gray-900 border border-gray-700 rounded-md px-2 py-1 text-white [color-scheme:dark]" />
          </div>
        )}

        <div className="relative">
          <Search className="w-3.5 h-3.5 absolute left-2 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none" />
          <input value={search} onChange={e => setSearch(e.target.value)} placeholder="Search product…"
            className="bg-gray-900 border border-gray-700 rounded-md pl-7 pr-2 py-1 text-xs text-white placeholder-gray-500 w-44" />
        </div>

        {loading && <Loader2 className="w-4 h-4 animate-spin text-gray-400 ml-auto" />}
      </div>

      <div className="p-4 flex flex-col gap-4 max-w-[1200px] mx-auto" style={{ maxWidth: 1200, marginLeft: 'auto', marginRight: 'auto', width: '100%' }}>

        <p className="text-sm text-gray-600">
          {niceDate(from)}{from !== to && <> – {niceDate(to)}</>}
          {data && <span className="text-gray-400"> · compared with {niceDate(data.prior.from)}{data.prior.from !== data.prior.to && <> – {niceDate(data.prior.to)}</>}</span>}
        </p>

        {error && (
          <div className="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <AlertTriangle className="w-4 h-4 mt-0.5 shrink-0" /><span>{error}</span>
          </div>
        )}

        {data && t && pt && (
          <div className={`flex flex-col gap-4 transition-opacity ${loading ? 'opacity-50' : ''}`}>

            {/* ── KPIs ── */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <Kpi label="Units produced"  value={qty(t.units)}           sub={<Delta cur={t.units} prev={pt.units} />} />
              <Kpi label="Production runs" value={group(String(t.runs))} sub={<Delta cur={t.runs} prev={pt.runs} />} />
              <Kpi label="Products made"   value={group(String(t.products))} />
            </div>

            {/* ── By product ── */}
            <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
              <p className="px-4 pt-3 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Production by product · click a heading to sort
              </p>
              {shown.length === 0 ? (
                <p className="px-4 py-8 text-sm text-gray-400 text-center">
                  {data.products.length === 0 ? 'Nothing was produced in this period.' : 'No products match.'}
                </p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-sm min-w-[460px]">
                    <thead>
                      <tr>
                        <Th k="name"     label="Product" />
                        <Th k="runs"     label="Runs"       right />
                        <Th k="units"    label="Units"      right />
                        <Th k="lastMade" label="Last made"  right />
                      </tr>
                    </thead>
                    <tbody>
                      {shown.map(p => (
                        <tr key={p.id} className="border-t border-gray-100 hover:bg-gray-50">
                          <td className="px-3 py-2">
                            <p className="font-medium text-gray-900">{p.name}</p>
                            <div className="h-1.5 bg-green-100 rounded-full mt-1 max-w-[260px]" style={{ maxWidth: 260 }}>
                              <div className="h-1.5 bg-green-600 rounded-full" style={{ width: `${(p.units / maxUnits) * 100}%` }} />
                            </div>
                          </td>
                          <td className="px-3 py-2 text-right font-mono text-gray-600">{p.runs}</td>
                          <td className="px-3 py-2 text-right font-mono text-lg font-bold text-green-700">{qty(p.units)}</td>
                          <td className="px-3 py-2 text-right text-xs text-gray-500 whitespace-nowrap">{niceDate(p.lastMade)}</td>
                        </tr>
                      ))}
                    </tbody>
                    {shown.length > 1 && (
                      <tfoot>
                        <tr className="border-t-2 border-gray-200 bg-gray-50 font-bold">
                          <td className="px-3 py-2 text-[10px] text-gray-500 uppercase tracking-wider">Total</td>
                          <td className="px-3 py-2 text-right font-mono">{t.runs}</td>
                          <td className="px-3 py-2 text-right font-mono text-green-700">{qty(t.units)}</td>
                          <td></td>
                        </tr>
                      </tfoot>
                    )}
                  </table>
                </div>
              )}
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ProductionReport;

const rootEl = document.getElementById('root');
if (rootEl) createRoot(rootEl).render(<ProductionReport />);