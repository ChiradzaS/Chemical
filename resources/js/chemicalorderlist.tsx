import React, { useState, useEffect } from 'react';
import {
  Check, Loader2, Plus, Search, ChevronDown, ChevronRight, ShoppingCart, CircleCheck, X, Trash2,
} from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:        string;
    customersData:        { id: string; name: string }[];
    chemicalProductsData: { id: string; name: string; sku?: string | null }[];
    containerSizesData:   { id: string; name: string }[];
    usersData:            { id: string; name: string }[];
  }
}

interface OrderItemRow {
  id:            number;
  ordersId:      number;
  productId:     string;
  unitId:        string;
  quantity:      number;
  price:         number;
  totalPrice:    number;
  stateId:       number | null;
  dueDate:       string | null;
  DateComplete:  string | null;
  reference:     string | null;
  other:         string | null;
  job_card_id:   number | null;
  manufactured:  number | null;
  openningQNT:   number | null;
}

interface OrderRow {
  id:          number;
  reference:   string | null;
  date:        string | null;
  customerId:  string;
  totalValue:  number;
  stateId:     string | number;
  datePlaced:  string | null;
  dueDate:     string | null;
  other:       string | null;
  orderBy:     string | null;
  created_at:  string;
  items:       OrderItemRow[];
}

const API_BASE   = window.laravelApiUrl || 'http://localhost/Chemical';
const CREATE_URL = `${API_BASE}/chemicalorder`;

const STATE_COMPLETE = 2;

const isComplete = (state: any) => Number(state) === STATE_COMPLETE;

// ── Lookup helper — same shape as the job card list ─────────────────────────
const byId = (list: { id: string; name: string }[], id: any) =>
  list.find(x => String(x.id) === String(id))?.name ?? '—';

const money = (n: any) => `R ${Number(n ?? 0).toFixed(2)}`;

const OrderList: React.FC = () => {
  // ── Blade-injected globals ────────────────────────────────────────────────
  const customers        = window.customersData        || [];
  const chemicalProducts = window.chemicalProductsData || [];
  const containerSizes   = window.containerSizesData   || [];
  const users            = window.usersData            || [];

  const [orders,  setOrders]  = useState<OrderRow[]>([]);
  const [loading, setLoading] = useState(true);
  const [busy,    setBusy]    = useState<string | null>(null);   // 'order-12' | 'item-44'
  const [open,    setOpen]    = useState<Record<number, boolean>>({});

  // ── Filters ───────────────────────────────────────────────────────────────
  const [customerId,    setCustomerId]    = useState('');   // select, same as the create page
  const [productId,     setProductId]     = useState('');   // set by picking a suggestion
  const [productSearch, setProductSearch] = useState('');
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [searchRef,     setSearchRef]     = useState('');
  const [statusFilter,  setStatusFilter]  = useState('all');
  const [dateFrom,      setDateFrom]      = useState('');
  const [dateTo,        setDateTo]        = useState('');

  const productSuggestions = chemicalProducts.filter(p =>
    p.name.toLowerCase().includes(productSearch.toLowerCase())
  );

  const pickProduct = (p: { id: string; name: string }) => {
    setProductId(p.id);
    setProductSearch(p.name);
    setShowSuggestions(false);
  };

  const clearProduct = () => {
    setProductId('');
    setProductSearch('');
    setShowSuggestions(false);
  };

  // ── Fetch ─────────────────────────────────────────────────────────────────
  const fetchOrders = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`${API_BASE}/orders/index`);
      const data     = response.data;
      setOrders(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Error fetching orders:', error);
      setOrders([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchOrders(); }, []);

  // ── Swap one order in place with whatever the API returned ────────────────
  const replaceOrder = (updated: OrderRow) => {
    setOrders(prev => prev.map(o => (o.id === updated.id ? updated : o)));
  };

  // ── Tick one item ─────────────────────────────────────────────────────────
  const toggleItem = async (item: OrderItemRow) => {
    const complete = !isComplete(item.stateId);
    setBusy(`item-${item.id}`);
    try {
      const payload     = { id: item.id, complete };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/orders/completeitem?data=${encodedData}`);
      if (response.data?.order) replaceOrder(response.data.order);
    } catch (error: any) {
      alert(error.response?.data?.message || 'Could not update that item.');
      console.error(error);
    } finally {
      setBusy(null);
    }
  };

  // ── Tick the whole order → every line follows ─────────────────────────────
  const toggleOrder = async (order: OrderRow) => {
    const complete = !isComplete(order.stateId);
    if (complete && !confirm(`Mark all ${order.items.length} item(s) on this order as complete?`)) return;

    setBusy(`order-${order.id}`);
    try {
      const payload     = { id: order.id, complete };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${API_BASE}/orders/completeorder?data=${encodedData}`);
      if (response.data?.order) replaceOrder(response.data.order);
    } catch (error: any) {
      alert(error.response?.data?.message || 'Could not update that order.');
      console.error(error);
    } finally {
      setBusy(null);
    }
  };

  // ── Can this order still be deleted? ──────────────────────────────────────
  // Nothing completed, nothing planned into a job card, nothing manufactured,
  // and no line amended since it was placed. Same checks run server-side.
  const deleteBlockers = (order: OrderRow): string[] => {
    const reasons: string[] = [];

    if (isComplete(order.stateId))                              reasons.push('order is complete');
    if (order.items.some(i => isComplete(i.stateId)))           reasons.push('items already ticked off');
    if (order.items.some(i => Number(i.job_card_id) > 0))       reasons.push('linked to a job card');
    if (order.items.some(i => Number(i.manufactured) > 0))      reasons.push('production started');
    if (order.items.some(i => i.openningQNT != null
                              && Number(i.quantity) !== Number(i.openningQNT)))
                                                                reasons.push('quantities changed');
    return reasons;
  };

  // ── Delete ────────────────────────────────────────────────────────────────
  const handleDelete = async (order: OrderRow) => {
    if (!confirm(`Delete order #${order.id} and its ${order.items.length} item(s)? This cannot be undone.`)) return;

    setBusy(`delete-${order.id}`);
    try {
      const encodedData = encodeURIComponent(JSON.stringify({ id: order.id }));
      const response    = await axios.get(`${API_BASE}/orders/destroy?data=${encodedData}`);

      if (response.data?.message !== 'deleted') {
        alert(response.data?.message || 'The order was not deleted.');
        return;
      }
      setOrders(prev => prev.filter(o => o.id !== order.id));
    } catch (error: any) {
      alert(error.response?.data?.message || 'Could not delete that order.');
      console.error(error);
    } finally {
      setBusy(null);
    }
  };

  // ── Filter ────────────────────────────────────────────────────────────────
  const filtered = orders.filter(o => {
    const done = isComplete(o.stateId);

    const matchCustomer = !customerId || String(o.customerId) === String(customerId);
    const matchProduct  = !productId  || o.items.some(i => String(i.productId) === String(productId));
    const matchRef      = !searchRef.trim() || (o.reference ?? '').toLowerCase().includes(searchRef.toLowerCase());
    const matchStatus   = statusFilter === 'all' || (statusFilter === 'complete' ? done : !done);
    const matchFrom     = !dateFrom || new Date(o.created_at) >= new Date(dateFrom);
    const matchTo       = !dateTo   || new Date(o.created_at) <= new Date(dateTo);

    return matchCustomer && matchProduct && matchRef && matchStatus && matchFrom && matchTo;
  });

  const doneCount = (o: OrderRow) => o.items.filter(i => isComplete(i.stateId)).length;

  const inp = "bg-white border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none";
  const sel = `${inp} appearance-none pr-8`;
  const lbl = "block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5";

  return (
    <div className="min-h-screen bg-slate-100 p-6">
      <div className="w-full space-y-6">

        {/* ── Page header ── */}
        <div className="bg-[#0f172a] rounded-xl p-6 flex items-center justify-between shadow-lg">
          <div className="flex items-center gap-3">
            <ShoppingCart className="text-sky-400 w-7 h-7" />
            <div>
              <h1 className="text-2xl font-bold text-white">Orders</h1>
              <p className="text-slate-400 text-sm">Tick items off as they are completed</p>
            </div>
          </div>
          <button
            onClick={() => { window.location.href = CREATE_URL; }}
            className="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-5 py-2.5 rounded-lg font-semibold transition-colors shadow"
          >
            <Plus size={18} /> New Order
          </button>
        </div>

        {/* ── Filters ── */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
          <div className="flex items-center gap-2 mb-4">
            <Search size={18} className="text-slate-400" />
            <h2 className="font-semibold text-slate-800">Search</h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">

            {/* Customer — dropdown, same as the create page */}
            <div>
              <label className={lbl}>Customer</label>
              <div className="relative">
                <select value={customerId} onChange={e => setCustomerId(e.target.value)} className={sel}>
                  <option value="">All customers</option>
                  {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>

            {/* Product — type-ahead, matches orders containing that product */}
            <div className="relative">
              <label className={lbl}>Product</label>
              <input
                type="text"
                value={productSearch}
                onChange={e => {
                  setProductSearch(e.target.value);
                  setShowSuggestions(true);
                  if (!e.target.value.trim()) setProductId('');
                }}
                onFocus={() => setShowSuggestions(true)}
                onBlur={() => setTimeout(() => setShowSuggestions(false), 150)}
                placeholder="Search product…"
                className={`${inp} ${productId ? 'pr-8' : ''}`}
              />
              {productId && (
                <button
                  onClick={clearProduct}
                  title="Clear product filter"
                  className="absolute right-2 top-[30px] text-slate-400 hover:text-red-600"
                >
                  <X size={15} />
                </button>
              )}
              {showSuggestions && productSuggestions.length > 0 && (
                <div className="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                  {productSuggestions.map(p => (
                    <div
                      key={p.id}
                      onMouseDown={() => pickProduct(p)}
                      className="px-3 py-2 text-sm hover:bg-sky-50 cursor-pointer flex justify-between"
                    >
                      <span>{p.name}</span>
                      {p.sku && <span className="text-slate-400 text-xs">#{p.sku}</span>}
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div>
              <label className={lbl}>Reference</label>
              <input type="text" value={searchRef} onChange={e => setSearchRef(e.target.value)} placeholder="Search reference…" className={inp} />
            </div>

            <div>
              <label className={lbl}>Status</label>
              <div className="relative">
                <select value={statusFilter} onChange={e => setStatusFilter(e.target.value)} className={sel}>
                  <option value="all">All</option>
                  <option value="pending">Pending</option>
                  <option value="complete">Complete</option>
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>

            <div>
              <label className={lbl}>Date from</label>
              <input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} className={inp} />
            </div>

            <div>
              <label className={lbl}>Date to</label>
              <input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} className={inp} />
            </div>
          </div>

          <p className="mt-3 text-sm text-slate-500">
            Showing {filtered.length} order{filtered.length !== 1 ? 's' : ''}
            {productId && <> containing <strong className="text-slate-700">{byId(chemicalProducts, productId)}</strong></>}
          </p>
        </div>

        {/* ── Orders ── */}
        {loading ? (
          <div className="text-center py-16 text-slate-400">Loading orders…</div>
        ) : filtered.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-xl shadow-sm border border-slate-200">
            <ShoppingCart size={44} className="mx-auto text-slate-300 mb-4" />
            <p className="text-slate-600 font-medium">No orders found</p>
            <p className="text-slate-400 text-sm mt-1">Try adjusting your filters</p>
          </div>
        ) : (
          <div className="space-y-4">
            {filtered.map(order => {
              const done         = isComplete(order.stateId);
              const expanded     = open[order.id] ?? true;
              const busyOrder    = busy === `order-${order.id}`;
              const customerName = byId(customers, order.customerId);
              const placedBy     = order.orderBy ? byId(users, order.orderBy) : null;
              const blockers     = deleteBlockers(order);
              const busyDelete   = busy === `delete-${order.id}`;

              return (
                <div
                  key={order.id}
                  className={`bg-white rounded-xl shadow-sm border overflow-hidden transition-colors ${
                    done ? 'border-emerald-300' : 'border-slate-200'
                  }`}
                >
                  {/* Order header */}
                  <div className={`p-5 ${done ? 'bg-emerald-900' : 'bg-[#0f172a]'} text-white transition-colors`}>
                    <div className="flex items-start justify-between gap-4">

                      <button
                        onClick={() => setOpen(o => ({ ...o, [order.id]: !expanded }))}
                        className="flex items-start gap-3 text-left flex-1"
                      >
                        {expanded
                          ? <ChevronDown className="w-5 h-5 mt-1 text-slate-400 shrink-0" />
                          : <ChevronRight className="w-5 h-5 mt-1 text-slate-400 shrink-0" />}
                        <div>
                          <div className="flex items-center gap-3 flex-wrap mb-1.5">
                            <span className="bg-slate-700 px-2.5 py-0.5 rounded-full text-xs font-semibold">#{order.id}</span>
                            {order.reference && (
                              <span className="bg-sky-600 text-xs font-bold px-2.5 py-0.5 rounded-full">{order.reference}</span>
                            )}
                            <h3 className="text-lg font-semibold">{customerName}</h3>
                            <span className={`text-xs font-bold px-2.5 py-0.5 rounded-full ${
                              done ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-950'
                            }`}>
                              {done ? 'COMPLETE' : 'PENDING'}
                            </span>
                          </div>
                          <div className="flex items-center gap-5 text-sm text-slate-300 flex-wrap">
                            <span>Placed: {order.datePlaced ?? '—'}</span>
                            <span>Due: {order.dueDate ?? '—'}</span>
                            {placedBy && placedBy !== '—' && <span>By: {placedBy}</span>}
                            <span>Value: <strong className="text-white">{money(order.totalValue)}</strong></span>
                            <span>
                              Items: <strong className="text-white">{doneCount(order)}/{order.items.length}</strong> done
                            </span>
                          </div>
                        </div>
                      </button>

                      {/* Tick the whole order */}
                      <button
                        onClick={() => toggleOrder(order)}
                        disabled={busyOrder}
                        title={done ? 'Reopen this order' : 'Mark order and all items complete'}
                        className={`shrink-0 flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm transition-colors disabled:opacity-50 ${
                          done
                            ? 'bg-emerald-500 hover:bg-emerald-600 text-white'
                            : 'bg-white/10 hover:bg-emerald-500 text-white border border-white/20'
                        }`}
                      >
                        {busyOrder
                          ? <Loader2 className="w-4 h-4 animate-spin" />
                          : <CircleCheck className="w-4 h-4" />}
                        {done ? 'Completed' : 'Complete all'}
                      </button>

                      {/* Delete — only while the order is untouched */}
                      {blockers.length === 0 && (
                        <button
                          onClick={() => handleDelete(order)}
                          disabled={busyDelete}
                          title="Delete this order"
                          className="shrink-0 flex items-center gap-2 px-4 py-2 rounded-lg font-semibold text-sm transition-colors disabled:opacity-50 bg-white/10 hover:bg-red-600 text-white border border-white/20"
                        >
                          {busyDelete
                            ? <Loader2 className="w-4 h-4 animate-spin" />
                            : <Trash2 className="w-4 h-4" />}
                          Delete
                        </button>
                      )}
                    </div>

                    {order.other && (
                      <p className="mt-2 text-slate-400 text-xs italic">{order.other}</p>
                    )}
                    {blockers.length > 0 && (
                      <p className="mt-2 text-slate-500 text-xs">
                        Locked — {blockers.join(', ')}
                      </p>
                    )}
                  </div>

                  {/* Items */}
                  {expanded && (
                    <div className="overflow-x-auto">
                      <table className="w-full text-sm">
                        <thead className="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide">
                          <tr>
                            <th className="w-14 px-4 py-2.5"></th>
                            <th className="text-left px-3 py-2.5 font-semibold">Product</th>
                            <th className="text-left px-3 py-2.5 font-semibold">Unit</th>
                            <th className="text-right px-3 py-2.5 font-semibold">Qty</th>
                            <th className="text-right px-3 py-2.5 font-semibold">Price</th>
                            <th className="text-right px-3 py-2.5 font-semibold">Total</th>
                            <th className="text-left px-3 py-2.5 font-semibold">Due</th>
                            <th className="text-left px-3 py-2.5 font-semibold">Completed</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                          {order.items.map(item => {
                            const itemDone  = isComplete(item.stateId);
                            const busyItem  = busy === `item-${item.id}`;
                            const prodName  = byId(chemicalProducts, item.productId);
                            const unitName  = byId(containerSizes,   item.unitId);
                            const highlight = productId && String(item.productId) === String(productId);

                            return (
                              <tr
                                key={item.id}
                                className={
                                  itemDone   ? 'bg-emerald-50/60'
                                  : highlight ? 'bg-sky-50'
                                  : 'hover:bg-slate-50'
                                }
                              >
                                <td className="px-4 py-3">
                                  <button
                                    onClick={() => toggleItem(item)}
                                    disabled={busyItem}
                                    title={itemDone ? 'Mark as not done' : 'Mark as complete'}
                                    className={`w-7 h-7 rounded-full flex items-center justify-center border-2 transition-colors disabled:opacity-50 ${
                                      itemDone
                                        ? 'bg-emerald-500 border-emerald-500 text-white hover:bg-emerald-600'
                                        : 'border-slate-300 text-transparent hover:border-emerald-400 hover:text-emerald-400'
                                    }`}
                                  >
                                    {busyItem
                                      ? <Loader2 className="w-4 h-4 animate-spin text-slate-400" />
                                      : <Check className="w-4 h-4" strokeWidth={3} />}
                                  </button>
                                </td>
                                <td className={`px-3 py-3 font-medium ${itemDone ? 'text-slate-500 line-through' : 'text-slate-800'}`}>
                                  {prodName}
                                </td>
                                <td className="px-3 py-3 text-slate-600">{unitName}</td>
                                <td className="px-3 py-3 text-right text-slate-800">{Number(item.quantity)}</td>
                                <td className="px-3 py-3 text-right text-slate-600">{money(item.price)}</td>
                                <td className="px-3 py-3 text-right font-semibold text-slate-900">{money(item.totalPrice)}</td>
                                <td className="px-3 py-3 text-slate-500">{item.dueDate ?? '—'}</td>
                                <td className="px-3 py-3">
                                  {item.DateComplete
                                    ? <span className="text-emerald-600 font-medium">{item.DateComplete}</span>
                                    : <span className="text-slate-400">—</span>}
                                </td>
                              </tr>
                            );
                          })}

                          {order.items.length === 0 && (
                            <tr>
                              <td colSpan={8} className="px-4 py-6 text-center text-slate-400">
                                This order has no items
                              </td>
                            </tr>
                          )}
                        </tbody>
                      </table>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
};

export default OrderList;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<OrderList />);
}