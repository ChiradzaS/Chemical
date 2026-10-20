import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';
import {
  BarChart3, ChevronDown, TrendingUp, TrendingDown,
  Loader2, AlertTriangle, ArrowUpDown,
} from 'lucide-react';

declare global {
  interface Window {
    laravelApiUrl?: string;
    customersData?: { id: number | string; name: string }[];
  }
}

const API_BASE = window.laravelApiUrl ;

/* ── Types (match SalesReportController::data) ─────────────────────────── */
interface Money {
  units: number; excl: number; vat: number; incl: number;
  cost: number; profit: number; margin: number | null;
}
interface Totals extends Money { invoices: number; uncosted_lines: number; }
interface Row extends Money { id: number | string; name: string; invoices?: number; }
interface Point { key: string; label: string; excl: number; incl: number; }
interface Report {
  status: 'ok';
  period: { from: string; to: string; days: number };
  prior:  { from: string; to: string };
  totals: Totals;
  priorTotals: Totals;
  trend: { granularity: 'hour' | 'day' | 'month'; points: Point[] };
  customers: Row[];
  products: Row[];
}

type Preset = 'today' | 'month' | 'lastMonth' | '6m' | 'year' | 'custom';

const PRESETS: { id: Preset; label: string }[] = [
  { id: 'today',     label: 'Today' },
  { id: 'month',     label: 'This month' },
  { id: 'lastMonth', label: 'Last month' },
  { id: '6m',        label: 'Last 6 months' },
  { id: 'year',      label: 'This year' },
  { id: 'custom',    label: 'Custom' },
];

/* ── Helpers ───────────────────────────────────────────────────────────── */
const ymd = (d: Date) =>
  `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;

const rangeFor = (p: Exclude<Preset, 'custom'>): [string, string] => {
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();
  if (p === 'today')     return [ymd(now), ymd(now)];
  if (p === 'lastMonth') return [ymd(new Date(y, m - 1, 1)), ymd(new Date(y, m, 0))];
  if (p === '6m')        return [ymd(new Date(y, m - 5, 1)), ymd(now)];
  if (p === 'year')      return [ymd(new Date(y, 0, 1)), ymd(now)];
  return [ymd(new Date(y, m, 1)), ymd(now)];
};

// "1 284 650" — space as the thousands separator
const group = (s: string) => s.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

const money = (n: number) => {
  const [i, d] = Math.abs(n).toFixed(2).split('.');
  return `${n < 0 ? '-' : ''}R ${group(i)}.${d}`;
};

const qty = (n: number) =>
  Number.isInteger(n) ? group(String(n)) : (() => { const [i, d] = n.toFixed(2).split('.'); return `${group(i)}.${d}`; })();

const niceDate = (s: string) =>
  new Date(`${s}T00:00:00`).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

/* ── Small pieces ──────────────────────────────────────────────────────── */
const Delta = ({ cur, prev }: { cur: number; prev: number }) => {
  if (!prev) return <span className="text-[11px] text-gray-400">No prior sales</span>;
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
  <div className="bg-white border border-gray-200 rounded-xl px-4 py-3 break-inside-avoid">
    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{label}</p>
    <p className="text-lg font-bold font-mono text-gray-900 mt-0.5 leading-tight">{value}</p>
    {sub && <div className="mt-1">{sub}</div>}
  </div>
);

const Chart = ({ points }: { points: Point[] }) => {
  const max   = Math.max(1, ...points.map(p => p.incl));
  const every = Math.max(1, Math.ceil(points.length / 12));   // at most ~12 labels
  const total = points.reduce((s, p) => s + p.incl, 0);

  if (total === 0)
    return <div className="h-44 flex items-center justify-center text-sm text-gray-400">No sales in this period</div>;

  return (
    <div className="flex items-stretch gap-1 h-44">
      {points.map((p, i) => (
        <div key={p.key} className="flex-1 min-w-0 flex flex-col group">
          <div className="flex-1 flex items-end">
            <div
              className="w-full rounded-t bg-blue-300 group-hover:bg-blue-600 transition-colors print:bg-gray-500"
              style={{ height: `${(p.incl / max) * 100}%`, minHeight: p.incl > 0 ? 2 : 0 }}
              title={`${p.label}: ${money(p.incl)} incl. VAT`}
            />
          </div>
          <span className="text-[10px] text-gray-500 mt-1 h-3 text-center whitespace-nowrap overflow-visible">
            {i % every === 0 ? p.label : ''}
          </span>
        </div>
      ))}
    </div>
  );
};

type SortKey = 'name' | 'invoices' | 'units' | 'incl' | 'profit';

const RankTable = ({ title, rows, showInvoices }: { title: string; rows: Row[]; showInvoices?: boolean }) => {
  const [sort, setSort] = useState<{ key: SortKey; dir: 1 | -1 }>({ key: 'incl', dir: -1 });

  const sorted = useMemo(() => {
    const val = (r: Row): number | string =>
      sort.key === 'name' ? r.name.toLowerCase() : (r[sort.key] ?? -Infinity) as number;
    return [...rows].sort((a, b) => (val(a) < val(b) ? -1 : val(a) > val(b) ? 1 : 0) * sort.dir);
  }, [rows, sort]);

  const Th = ({ k, label, right }: { k: SortKey; label: string; right?: boolean }) => (
    <th
      onClick={() => setSort(s => ({ key: k, dir: s.key === k ? (s.dir === 1 ? -1 : 1) : (k === 'name' ? 1 : -1) }))}
      className={`px-3 py-2 text-[10px] font-bold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-700 ${right ? 'text-right' : 'text-left'}`}
    >
      <span className="inline-flex items-center gap-1">
        {label}
        <ArrowUpDown className={`w-3 h-3 print:hidden ${sort.key === k ? 'text-blue-600' : 'text-gray-300'}`} />
      </span>
    </th>
  );

  return (
    <div className="bg-white border border-gray-200 rounded-xl overflow-hidden break-inside-avoid">
      <p className="px-4 pt-3 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">{title}</p>
      {rows.length === 0 ? (
        <p className="px-4 py-6 text-sm text-gray-400">No sales in this period</p>
      ) : (
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr>
                <Th k="name" label={showInvoices ? 'Customer' : 'Product'} />
                {showInvoices && <Th k="invoices" label="Invoices" right />}
                <Th k="units"  label="Units"     right />
                <Th k="incl"   label="Incl. VAT" right />
                <Th k="profit" label="Profit"    right />
              </tr>
            </thead>
            <tbody>
              {sorted.map(r => (
                <tr key={String(r.id)} className="border-t border-gray-100 hover:bg-gray-50">
                  <td className="px-3 py-2 text-gray-800 font-medium">{r.name}</td>
                  {showInvoices && <td className="px-3 py-2 text-right font-mono text-gray-600">{r.invoices}</td>}
                  <td className="px-3 py-2 text-right font-mono text-gray-600">{qty(r.units)}</td>
                  <td className="px-3 py-2 text-right font-mono font-semibold text-gray-900">{money(r.incl)}</td>
                  <td className="px-3 py-2 text-right font-mono text-gray-600">{money(r.profit)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
};

/* ── Page ──────────────────────────────────────────────────────────────── */
const SalesReport: React.FC = () => {
  const customers = window.customersData || [];

  const [preset, setPreset]         = useState<Preset>('today');   // opens on today's sales
  const [[from, to], setRange]      = useState<[string, string]>(rangeFor('today'));
  const [customerId, setCustomerId] = useState('');
  const [data, setData]             = useState<Report | null>(null);
  const [loading, setLoading]       = useState(true);
  const [error, setError]           = useState('');

  const filterJson = encodeURIComponent(JSON.stringify({ from, to, customerId: customerId || null }));

  const pickPreset = (p: Preset) => {
    setPreset(p);
    if (p !== 'custom') setRange(rangeFor(p));
  };

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError('');

    axios.get(`${API_BASE}/reports/sales/data?data=${filterJson}`)
      .then(r => {
        if (cancelled) return;
        // Session expired → Laravel answered with the login page instead of JSON
        if (typeof r.data === 'string') { window.location.reload(); return; }
        if (r.data?.status !== 'ok') throw new Error(r.data?.message || 'Could not load the report');
        setData(r.data as Report);
      })
      .catch(e => { if (!cancelled) setError(e?.message || 'Could not load the report'); })
      .finally(() => { if (!cancelled) setLoading(false); });

    return () => { cancelled = true; };
  }, [filterJson]);

  const t  = data?.totals;
  const pt = data?.priorTotals;
  const customerName = customers.find(c => String(c.id) === customerId)?.name;

  return (
    <div className="min-h-screen bg-gray-100 print:bg-white text-gray-900">

      {/* ── Top bar ── */}
      <div className="bg-black text-white px-4 py-2.5 flex items-center gap-3 flex-wrap print:hidden">
        <BarChart3 className="w-4 h-4 text-blue-400" />
        <span className="font-bold text-sm whitespace-nowrap">Reports / Sales Report</span>

        <div className="flex items-center gap-1 bg-gray-900 rounded-lg p-1 flex-wrap">
          {PRESETS.map(p => (
            <button
              key={p.id}
              onClick={() => pickPreset(p.id)}
              className={`px-3 py-1 rounded-md text-xs font-bold transition-colors ${
                preset === p.id ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'
              }`}
            >
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
          <select
            value={customerId}
            onChange={e => setCustomerId(e.target.value)}
            className="appearance-none bg-gray-900 border border-gray-700 rounded-md pl-3 pr-7 py-1 text-xs text-white max-w-[200px]"
          >
            <option value="">All customers</option>
            {customers.map(c => <option key={c.id} value={String(c.id)}>{c.name}</option>)}
          </select>
          <ChevronDown className="w-3 h-3 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
        </div>

      </div>

      <div className="p-4 flex flex-col gap-4 max-w-[1400px] mx-auto print:p-0" style={{ maxWidth: 1400, marginLeft: 'auto', marginRight: 'auto', width: '100%' }}>

        {/* Period line — also the heading on the printout */}
        <div className="flex items-baseline justify-between flex-wrap gap-2">
          <div>
            <h1 className="hidden print:block text-xl font-bold">Buzz Chem — Sales Report</h1>
            <p className="text-sm text-gray-600">
              {niceDate(from)} – {niceDate(to)}
              {customerName && <> · <span className="font-semibold text-gray-800">{customerName}</span></>}
              {data && <span className="text-gray-400"> · compared with {niceDate(data.prior.from)} – {niceDate(data.prior.to)}</span>}
            </p>
          </div>
          {loading && <span className="text-xs text-gray-400 inline-flex items-center gap-1.5 print:hidden"><Loader2 className="w-3.5 h-3.5 animate-spin" /> Loading…</span>}
        </div>

        {error && (
          <div className="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <AlertTriangle className="w-4 h-4 mt-0.5 shrink-0" />
            <span>{error}</span>
          </div>
        )}

        {data && t && pt && (
          <div className={`flex flex-col gap-4 transition-opacity ${loading ? 'opacity-50' : ''}`}>

            {/* ── KPIs ── */}
            <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
              <Kpi label="Sales incl. VAT" value={money(t.incl)} sub={<Delta cur={t.incl} prev={pt.incl} />} />
              <Kpi label="Excl. VAT"       value={money(t.excl)} />
              <Kpi label="VAT"             value={money(t.vat)} />
              <Kpi label="Gross margin"
                   value={t.margin === null ? '—' : `${t.margin.toFixed(1)}%`}
                   sub={<span className="text-[11px] text-gray-500">{money(t.profit)} profit</span>} />
              <Kpi label="Invoices"   value={group(String(t.invoices))} sub={<Delta cur={t.invoices} prev={pt.invoices} />} />
              <Kpi label="Units sold" value={qty(t.units)}              sub={<Delta cur={t.units} prev={pt.units} />} />
            </div>

            {t.uncosted_lines > 0 && (
              <p className="text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                {t.uncosted_lines} invoice line{t.uncosted_lines > 1 ? 's are' : ' is'} for products with no cost price,
                so profit and margin are overstated. Add cost prices on the product form to fix this.
              </p>
            )}

            {/* ── Trend ── */}
            <div className="bg-white border border-gray-200 rounded-xl p-4 break-inside-avoid">
              <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">
                {data.trend.granularity === 'hour' ? 'Sales by hour' : `Sales per ${data.trend.granularity}`} (incl. VAT)
              </p>
              <Chart points={data.trend.points} />
            </div>

            {/* ── Rankings ── */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <RankTable title="Top customers" rows={data.customers} showInvoices />
              <RankTable title="Top products"  rows={data.products} />
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default SalesReport;

const rootEl = document.getElementById('root');
if (rootEl) createRoot(rootEl).render(<SalesReport />);