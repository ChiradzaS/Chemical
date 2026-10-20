import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';
import { ClipboardList, ChevronDown, ChevronRight, Loader2, AlertTriangle, RefreshCw } from 'lucide-react';

declare global {
  interface Window { laravelApiUrl?: string; }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';

/* ── Types (match OutstandingOrdersController::data) ───────────────────── */
type Status = 'ok' | 'partial' | 'none';

interface Line {
  id: number; product: string; formula: string | null;
  qty: number; canShip: number; value: number; status: Status;
}
interface Order {
  id: number; reference: string; customerId: number; customer: string;
  placed: string | null; due: string | null; age: number | null;
  overdue: boolean; daysLate: number | null;
  value: number; units: number; status: Status; lines: Line[];
}
interface Short {
  productId: number; product: string; formula: string | null;
  ordered: number; stock: number; short: number;
}
interface Data {
  status: 'ok'; today: string; overdueDays: number;
  orders: Order[]; shortfall: Short[];
}

type View = 'all' | 'overdue' | 'ship';

/* ── Helpers ───────────────────────────────────────────────────────────── */
const group = (s: string) => s.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
const money = (n: number) => {
  const [i, d] = Math.abs(n).toFixed(2).split('.');
  return `${n < 0 ? '-' : ''}R ${group(i)}.${d}`;
};
const qty = (n: number) => {
  if (Number.isInteger(n)) return group(String(n));
  const [i, d] = n.toFixed(2).split('.');
  return `${group(i)}.${d}`;
};
const niceDate = (s: string | null) =>
  s ? new Date(`${s}T00:00:00`).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

const STATUS: Record<Status, { label: string; cls: string }> = {
  ok:      { label: 'In stock',        cls: 'bg-green-100 text-green-800' },
  partial: { label: 'Partial',         cls: 'bg-amber-100 text-amber-800' },
  none:    { label: 'Need production', cls: 'bg-red-100 text-red-700' },
};

const Badge = ({ s }: { s: Status }) => (
  <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap ${STATUS[s].cls}`}>{STATUS[s].label}</span>
);

const Kpi = ({ label, value, tone }: { label: string; value: string; tone?: 'red' }) => (
  <div className="bg-white border border-gray-200 rounded-xl px-4 py-3">
    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{label}</p>
    <p className={`text-lg font-bold font-mono mt-0.5 leading-tight ${tone === 'red' ? 'text-red-600' : 'text-gray-900'}`}>{value}</p>
  </div>
);

const ageClass = (o: Order) =>
  o.overdue ? 'text-red-600 font-bold' : (o.age ?? 0) > 7 ? 'text-amber-700' : 'text-green-700';

/* ── Page ──────────────────────────────────────────────────────────────── */
const OutstandingOrders: React.FC = () => {
  const [data, setData]         = useState<Data | null>(null);
  const [loading, setLoading]   = useState(true);
  const [error, setError]       = useState('');
  const [view, setView]         = useState<View>('all');
  const [customerId, setCustomerId] = useState('');
  const [open, setOpen]         = useState<Record<number, boolean>>({});
  const [reloadKey, setReloadKey] = useState(0);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError('');

    axios.get(`${API_BASE}/reports/outstandingorders/data`)
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

  // Customers that actually have open orders
  const customers = useMemo(() => {
    const m = new Map<number, string>();
    data?.orders.forEach(o => m.set(o.customerId, o.customer));
    return [...m.entries()].sort((a, b) => a[1].localeCompare(b[1]));
  }, [data]);

  const forCustomer = useMemo(
    () => (data?.orders ?? []).filter(o => !customerId || String(o.customerId) === customerId),
    [data, customerId],
  );

  const shown = useMemo(() => forCustomer.filter(o =>
    view === 'overdue' ? o.overdue : view === 'ship' ? o.status === 'ok' : true,
  ), [forCustomer, view]);

  // KPIs and age buckets follow the customer filter, not the view tabs
  const k = useMemo(() => ({
    orders:  forCustomer.length,
    value:   forCustomer.reduce((s, o) => s + o.value, 0),
    units:   forCustomer.reduce((s, o) => s + o.units, 0),
    ship:    forCustomer.filter(o => o.status === 'ok').length,
    overdue: forCustomer.filter(o => o.overdue).length,
    fresh:   forCustomer.filter(o => (o.age ?? 0) <= 7).length,
    mid:     forCustomer.filter(o => (o.age ?? 0) > 7 && (o.age ?? 0) <= 14).length,
    old:     forCustomer.filter(o => (o.age ?? 0) > 14).length,
  }), [forCustomer]);

  const VIEWS: { id: View; label: string; count: number }[] = [
    { id: 'all',     label: 'All open',     count: k.orders },
    { id: 'overdue', label: 'Overdue',      count: k.overdue },
    { id: 'ship',    label: 'Can ship now', count: k.ship },
  ];

  return (
    <div className="min-h-screen bg-gray-100 text-gray-900">

      {/* ── Top bar ── */}
      <div className="bg-black text-white px-4 py-2.5 flex items-center gap-3 flex-wrap">
        <ClipboardList className="w-4 h-4 text-blue-400" />
        <span className="font-bold text-sm whitespace-nowrap">Reports / Outstanding Orders</span>

        <div className="flex items-center gap-1 bg-gray-900 rounded-lg p-1">
          {VIEWS.map(v => (
            <button key={v.id} onClick={() => setView(v.id)}
              className={`px-3 py-1 rounded-md text-xs font-bold transition-colors ${
                view === v.id ? 'bg-blue-600 text-white' : 'text-gray-400 hover:text-white'}`}>
              {v.label} <span className="opacity-70">({v.count})</span>
            </button>
          ))}
        </div>

        <div className="relative">
          <select value={customerId} onChange={e => setCustomerId(e.target.value)}
            className="appearance-none bg-gray-900 border border-gray-700 rounded-md pl-3 pr-7 py-1 text-xs text-white max-w-[200px]">
            <option value="">All customers</option>
            {customers.map(([id, name]) => <option key={id} value={String(id)}>{name}</option>)}
          </select>
          <ChevronDown className="w-3 h-3 absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
        </div>

        <button onClick={() => setReloadKey(n => n + 1)}
          className="ml-auto flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-700 text-gray-200 hover:text-white hover:border-gray-500">
          <RefreshCw className={`w-3.5 h-3.5 ${loading ? 'animate-spin' : ''}`} /> Refresh
        </button>
      </div>

      <div className="p-4 flex flex-col gap-4 max-w-[1400px] mx-auto" style={{ maxWidth: 1400, marginLeft: 'auto', marginRight: 'auto', width: '100%' }}>

        {error && (
          <div className="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <AlertTriangle className="w-4 h-4 mt-0.5 shrink-0" /><span>{error}</span>
          </div>
        )}

        {!data && loading && (
          <div className="flex items-center gap-2 text-sm text-gray-400 py-10 justify-center">
            <Loader2 className="w-4 h-4 animate-spin" /> Loading open orders…
          </div>
        )}

        {data && (
          <div className={`flex flex-col gap-4 transition-opacity ${loading ? 'opacity-50' : ''}`}>

            {/* ── KPIs ── */}
            <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3">
              <Kpi label="Open orders"       value={group(String(k.orders))} />
              <Kpi label="Outstanding value" value={money(k.value)} />
              <Kpi label="Units outstanding" value={qty(Math.round(k.units * 100) / 100)} />
              <Kpi label="Can ship now"      value={group(String(k.ship))} />
              <Kpi label={`Overdue`}         value={group(String(k.overdue))} tone={k.overdue ? 'red' : undefined} />
            </div>

            {/* ── Order age ── */}
            {k.orders > 0 && (
              <div className="bg-white border border-gray-200 rounded-xl p-4">
                <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Order age</p>
                <div className="flex h-7 rounded-md overflow-hidden text-[11px] font-bold text-white">
                  {[
                    { n: k.fresh, label: '0–7 days',  cls: 'bg-green-600' },
                    { n: k.mid,   label: '8–14',      cls: 'bg-amber-500' },
                    { n: k.old,   label: '15+',       cls: 'bg-red-600'   },
                  ].filter(b => b.n > 0).map(b => (
                    <div key={b.label} className={`${b.cls} flex items-center justify-center whitespace-nowrap px-2`}
                         style={{ width: `${(b.n / k.orders) * 100}%` }}>
                      {b.label} · {b.n}
                    </div>
                  ))}
                </div>
              </div>
            )}

            {/* ── Open orders ── */}
            <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
              <p className="px-4 pt-3 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Open orders — oldest first · click an order to see its lines
              </p>
              {shown.length === 0 ? (
                <p className="px-4 py-8 text-sm text-gray-400 text-center">
                  {k.orders === 0 ? 'No outstanding orders — everything is complete.' : 'No orders match this view.'}
                </p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-sm min-w-[680px]">
                    <thead>
                      <tr className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <th className="px-3 py-2 w-6"></th>
                        <th className="px-3 py-2 text-left">Customer</th>
                        <th className="px-3 py-2 text-left">Placed</th>
                        <th className="px-3 py-2 text-left">Due</th>
                        <th className="px-3 py-2 text-right">Age</th>
                        <th className="px-3 py-2 text-right">Lines</th>
                        <th className="px-3 py-2 text-right">Value</th>
                        <th className="px-3 py-2 text-left">Stock</th>
                      </tr>
                    </thead>
                    <tbody>
                      {shown.map(o => {
                        const isOpen = !!open[o.id];
                        return (
                          <React.Fragment key={o.id}>
                            <tr onClick={() => setOpen(s => ({ ...s, [o.id]: !s[o.id] }))}
                                className="border-t border-gray-100 hover:bg-gray-50 cursor-pointer">
                              <td className="px-3 py-2 text-gray-400">
                                {isOpen ? <ChevronDown className="w-3.5 h-3.5" /> : <ChevronRight className="w-3.5 h-3.5" />}
                              </td>
                              <td className="px-3 py-2 font-semibold text-gray-900">{o.customer}</td>
                              <td className="px-3 py-2 text-gray-500 whitespace-nowrap">{niceDate(o.placed)}</td>
                              <td className={`px-3 py-2 whitespace-nowrap ${o.daysLate ? 'text-red-600 font-semibold' : 'text-gray-500'}`}>
                                {niceDate(o.due)}{o.daysLate ? ` · ${o.daysLate} d late` : ''}
                              </td>
                              <td className={`px-3 py-2 text-right font-mono ${ageClass(o)}`}>{o.age ?? '—'} d</td>
                              <td className="px-3 py-2 text-right font-mono text-gray-600">{o.lines.length}</td>
                              <td className="px-3 py-2 text-right font-mono font-semibold">{money(o.value)}</td>
                              <td className="px-3 py-2"><Badge s={o.status} /></td>
                            </tr>
                            {isOpen && (
                              <tr className="bg-gray-50">
                                <td></td>
                                <td colSpan={7} className="px-3 pb-3 pt-1">
                                  <table className="w-full text-xs">
                                    <thead>
                                      <tr className="text-[10px] text-gray-400 uppercase">
                                        <th className="py-1 text-left font-bold">Product</th>
                                        <th className="py-1 text-right font-bold">Ordered</th>
                                        <th className="py-1 text-right font-bold">Can ship</th>
                                        <th className="py-1 text-right font-bold">Value</th>
                                        <th className="py-1 pl-3 text-left font-bold">Stock</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      {o.lines.map(l => (
                                        <tr key={l.id} className="border-t border-gray-200">
                                          <td className="py-1.5 text-gray-800">
                                            {l.product}
                                            {l.formula && <span className="ml-2 text-[10px] font-mono text-gray-400">{l.formula}</span>}
                                          </td>
                                          <td className="py-1.5 text-right font-mono">{qty(l.qty)}</td>
                                          <td className="py-1.5 text-right font-mono">{qty(l.canShip)}</td>
                                          <td className="py-1.5 text-right font-mono">{money(l.value)}</td>
                                          <td className="py-1.5 pl-3"><Badge s={l.status} /></td>
                                        </tr>
                                      ))}
                                    </tbody>
                                  </table>
                                </td>
                              </tr>
                            )}
                          </React.Fragment>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            {/* ── Production shortfall (all open orders) ── */}
            <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
              <p className="px-4 pt-3 pb-1 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                Production shortfall — what to make to clear all open orders
              </p>
              {data.shortfall.length === 0 ? (
                <p className="px-4 py-6 text-sm text-gray-400">Nothing outstanding.</p>
              ) : (
                <div className="overflow-x-auto">
                  <table className="w-full text-sm min-w-[520px]">
                    <thead>
                      <tr className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                        <th className="px-3 py-2 text-left">Product</th>
                        <th className="px-3 py-2 text-left">Formula</th>
                        <th className="px-3 py-2 text-right">Ordered</th>
                        <th className="px-3 py-2 text-right">In stock</th>
                        <th className="px-3 py-2 text-right">To make</th>
                      </tr>
                    </thead>
                    <tbody>
                      {data.shortfall.map(s => (
                        <tr key={s.productId} className="border-t border-gray-100">
                          <td className="px-3 py-2 text-gray-800 font-medium">{s.product}</td>
                          <td className="px-3 py-2 font-mono text-xs text-gray-500">{s.formula || '—'}</td>
                          <td className="px-3 py-2 text-right font-mono">{qty(s.ordered)}</td>
                          <td className="px-3 py-2 text-right font-mono">{qty(s.stock)}</td>
                          <td className={`px-3 py-2 text-right font-mono ${s.short > 0 ? 'text-red-600 font-bold' : 'text-green-700'}`}>
                            {s.short > 0 ? qty(s.short) : '—'}
                          </td>
                        </tr>
                      ))}
                    </tbody>
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

export default OutstandingOrders;

const rootEl = document.getElementById('root');
if (rootEl) createRoot(rootEl).render(<OutstandingOrders />);