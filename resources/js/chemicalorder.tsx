import React, { useState, useMemo } from 'react';
import { ChevronDown, Loader2, Plus, Trash2, ShoppingCart, ListPlus, Save } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:        string;
    customersData:        { id: number; name: string }[];
    chemicalProductsData: OrderProduct[];
    containerSizesData:   { id: number; name: string }[];
    usersData:            { id: number; name: string }[];
  }
}

interface OrderProduct {
  id:                number;
  name:              string;
  sku:               string | null;
  price:             number | null;
  container_size_id: number | null;
}

interface OrderItem {
  key:         string;   // client-side row id only
  productId:   string;
  productName: string;
  unitId:      string;
  quantity:    string;
  price:       string;
  dueDate:     string;
  other:       string;
}

const API_BASE  = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL  = `${API_BASE}/chemicalorderlist`;
const STORE_URL = `${API_BASE}/orders/store`;

const byId = (list: { id: number; name: string }[], id: any) =>
  list.find(x => String(x.id) === String(id))?.name ?? '';

const money = (n: number) => `R ${n.toFixed(2)}`;

const OrderCreate: React.FC = () => {
  const customers      = window.customersData        || [];
  const products       = window.chemicalProductsData || [];
  const containerSizes = window.containerSizesData   || [];
  const users          = window.usersData            || [];

  const [saving, setSaving] = useState(false);

  // ── Order header ──────────────────────────────────────────────────────────
  const [header, setHeader] = useState({
    customerId: '',
    reference:  '',
    date:       new Date().toISOString().split('T')[0],
    datePlaced: new Date().toISOString().split('T')[0],
    dueDate:    '',
    orderBy:    '',
    other:      '',
  });

  // ── Items ─────────────────────────────────────────────────────────────────
  const [items, setItems] = useState<OrderItem[]>([]);

  // ── Item entry row ────────────────────────────────────────────────────────
  const emptyLine = { productId: '', unitId: '', quantity: '', price: '', dueDate: '', other: '' };
  const [line, setLine] = useState(emptyLine);
  const [productSearch,   setProductSearch]   = useState('');
  const [showSuggestions, setShowSuggestions] = useState(false);

  const filteredProducts = products.filter(p =>
    p.name.toLowerCase().includes(productSearch.toLowerCase())
  );

  const pickProduct = (p: OrderProduct) => {
    setProductSearch(p.name);
    setShowSuggestions(false);
    setLine(l => ({
      ...l,
      productId: String(p.id),
      unitId:    String(p.container_size_id ?? ''),
      price:     p.price != null ? String(p.price) : '',
    }));
  };

  const addItem = () => {
    if (!line.productId)            { alert('Pick a product');            return; }
    if (!line.unitId)               { alert('This product has no container size set — fix it on the product first'); return; }
    if (!parseInt(line.quantity, 10) || parseInt(line.quantity, 10) < 1) {
      alert('Enter a whole-number quantity');
      return;
    }
    if (line.price === '')          { alert('Enter a price');             return; }

    const product = products.find(p => String(p.id) === line.productId);

    setItems(prev => [...prev, {
      key:         `${Date.now()}-${Math.random().toString(36).slice(2, 7)}`,
      productId:   line.productId,
      productName: product?.name ?? '',
      unitId:      line.unitId,
      quantity:    line.quantity,
      price:       line.price,
      dueDate:     line.dueDate || header.dueDate,
      other:       line.other,
    }]);

    setLine(emptyLine);
    setProductSearch('');
  };

  const removeItem = (key: string) => setItems(prev => prev.filter(i => i.key !== key));

  const lineTotal = (i: OrderItem) => (parseInt(i.quantity, 10) || 0) * (parseFloat(i.price) || 0);
  const grandTotal = useMemo(() => items.reduce((sum, i) => sum + lineTotal(i), 0), [items]);

  // ── Save ──────────────────────────────────────────────────────────────────
  const handleSave = async () => {
    if (!header.customerId) { alert('Select a customer');                 return; }
    if (!items.length)      { alert('Add at least one item');             return; }

    setSaving(true);
    try {
      const payload = {
        order: { ...header, totalValue: grandTotal.toFixed(2) },
        items: items.map(i => ({
          productId:   i.productId,
          unitId:      i.unitId,
          quantity:    parseInt(i.quantity, 10),
          price:       i.price,
          totalPrice:  lineTotal(i).toFixed(2),
          openningQNT: parseInt(i.quantity, 10),
          dueDate:     i.dueDate || null,
          reference:   header.reference || null,
          other:       i.other || null,
        })),
      };
      const encodedData = encodeURIComponent(JSON.stringify(payload));
      const response    = await axios.get(`${STORE_URL}?data=${encodedData}`);

      // A 200 alone proves nothing — a login redirect or a catch-all route
      // also returns 200, with HTML. Only an id means a row was written.
      if (!response.data?.id) {
        console.error('Unexpected store response:', response.data);
        alert('The server did not save the order. Check the console and the Network tab.');
        return;
      }

      alert(`Order #${response.data.id} saved successfully!`);
      window.location.href = LIST_URL;
    } catch (error: any) {
      const msg = error.response?.data?.message || 'Could not save the order. Please retry.';
      alert(msg);
      console.error(error);
    } finally {
      setSaving(false);
    }
  };

  const inp = "bg-white border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:border-sky-500 focus:ring-2 focus:ring-sky-100 focus:outline-none";
  const sel = `${inp} appearance-none pr-8`;
  const lbl = "block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5";

  const customerChosen = !!header.customerId;

  return (
    <div className="min-h-screen bg-slate-100 p-6">
      <div className="w-full space-y-6">

        {/* ── Page header ── */}
        <div className="bg-[#0f172a] rounded-xl p-6 flex items-center justify-between shadow-lg">
          <div className="flex items-center gap-3">
            <ShoppingCart className="text-sky-400 w-7 h-7" />
            <div>
              <h1 className="text-2xl font-bold text-white">New Order</h1>
              <p className="text-slate-400 text-sm">Capture the order, then add its items</p>
            </div>
          </div>
          <button
            onClick={() => { window.location.href = LIST_URL; }}
            className="text-slate-300 hover:text-white text-sm font-medium"
          >
            ← Back to orders
          </button>
        </div>

        {/* ── Step 1 — order header ── */}
        <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div className="bg-[#0f172a] px-5 py-3 flex items-center gap-2">
            <span className="bg-sky-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">1</span>
            <h2 className="text-white font-semibold">Order details</h2>
          </div>

          <div className="p-5 grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div className="lg:col-span-2">
              <label className={lbl}>Customer *</label>
              <div className="relative">
                <select
                  value={header.customerId}
                  onChange={e => setHeader(h => ({ ...h, customerId: e.target.value }))}
                  className={sel}
                >
                  <option value="">---- Select Customer ----</option>
                  {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>

            <div>
              <label className={lbl}>Reference</label>
              <input
                type="text"
                value={header.reference}
                onChange={e => setHeader(h => ({ ...h, reference: e.target.value }))}
                placeholder="PO number…"
                className={inp}
              />
            </div>

            <div>
              <label className={lbl}>Ordered by</label>
              <div className="relative">
                <select
                  value={header.orderBy}
                  onChange={e => setHeader(h => ({ ...h, orderBy: e.target.value }))}
                  className={sel}
                >
                  <option value="">--</option>
                  {users.map(u => <option key={u.id} value={u.id}>{u.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
              </div>
            </div>

            <div>
              <label className={lbl}>Order date</label>
              <input
                type="date"
                value={header.date}
                onChange={e => setHeader(h => ({ ...h, date: e.target.value }))}
                className={inp}
              />
            </div>

            <div>
              <label className={lbl}>Date placed</label>
              <input
                type="date"
                value={header.datePlaced}
                onChange={e => setHeader(h => ({ ...h, datePlaced: e.target.value }))}
                className={inp}
              />
            </div>

            <div>
              <label className={lbl}>Due date</label>
              <input
                type="date"
                value={header.dueDate}
                onChange={e => setHeader(h => ({ ...h, dueDate: e.target.value }))}
                className={inp}
              />
            </div>

            <div>
              <label className={lbl}>Notes</label>
              <input
                type="text"
                value={header.other}
                onChange={e => setHeader(h => ({ ...h, other: e.target.value }))}
                placeholder="Delivery notes…"
                className={inp}
              />
            </div>
          </div>
        </div>

        {/* ── Step 2 — items (only once a customer is picked) ── */}
        {!customerChosen ? (
          <div className="bg-white rounded-xl border border-dashed border-slate-300 p-10 text-center">
            <ListPlus size={40} className="mx-auto text-slate-300 mb-3" />
            <p className="text-slate-500 font-medium">Select a customer to start adding items</p>
          </div>
        ) : (
          <div className="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div className="bg-[#0f172a] px-5 py-3 flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="bg-sky-500 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center">2</span>
                <h2 className="text-white font-semibold">Order items</h2>
              </div>
              <span className="text-slate-400 text-sm">
                {items.length} line{items.length !== 1 ? 's' : ''}
              </span>
            </div>

            {/* Entry row */}
            <div className="p-5 bg-sky-50/60 border-b border-slate-200">
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 items-end">

                <div className="lg:col-span-2 relative">
                  <label className={lbl}>Product</label>
                  <input
                    type="text"
                    value={productSearch}
                    onChange={e => { setProductSearch(e.target.value); setShowSuggestions(true); }}
                    onFocus={() => setShowSuggestions(true)}
                    onBlur={() => setTimeout(() => setShowSuggestions(false), 150)}
                    placeholder="Search product…"
                    className={inp}
                  />
                  {showSuggestions && filteredProducts.length > 0 && (
                    <div className="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                      {filteredProducts.map(p => (
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
                  <label className={lbl}>Unit</label>
                  <div className="relative">
                    <select
                      value={line.unitId}
                      disabled
                      className={`${sel} bg-slate-100 text-slate-700 cursor-not-allowed`}
                    >
                      <option value="">-- unit --</option>
                      {containerSizes.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                    </select>
                    <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" />
                  </div>
                </div>

                <div>
                  <label className={lbl}>Quantity</label>
                  <input
                    type="number"
                    step="1"
                    min="1"
                    value={line.quantity}
                    onChange={e => setLine(l => ({ ...l, quantity: e.target.value.replace(/[^0-9]/g, '') }))}
                    onKeyDown={e => { if (e.key === '.' || e.key === ',') e.preventDefault(); }}
                    placeholder="0"
                    className={inp}
                  />
                </div>

                <div>
                  <label className={lbl}>Unit price</label>
                  <input
                    type="number"
                    step="0.01"
                    value={line.price}
                    onChange={e => setLine(l => ({ ...l, price: e.target.value }))}
                    placeholder="0.00"
                    className={inp}
                  />
                </div>

                <div>
                  <label className={lbl}>Line due date</label>
                  <input
                    type="date"
                    value={line.dueDate}
                    onChange={e => setLine(l => ({ ...l, dueDate: e.target.value }))}
                    className={inp}
                  />
                </div>
              </div>

              <div className="flex items-center justify-between mt-3">
                <p className="text-xs text-slate-500">
                  Line total:{' '}
                  <strong className="text-slate-700">
                    {money((parseInt(line.quantity, 10) || 0) * (parseFloat(line.price) || 0))}
                  </strong>
                </p>
                <button
                  onClick={addItem}
                  className="flex items-center gap-2 bg-sky-500 hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors shadow-sm"
                >
                  <Plus size={16} /> Add item
                </button>
              </div>
            </div>

            {/* Item table */}
            {items.length === 0 ? (
              <div className="p-8 text-center text-slate-400 text-sm">No items added yet</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead className="bg-slate-50 text-slate-500 uppercase text-[11px] tracking-wide">
                    <tr>
                      <th className="text-left px-5 py-2.5 font-semibold">Product</th>
                      <th className="text-left px-3 py-2.5 font-semibold">Unit</th>
                      <th className="text-right px-3 py-2.5 font-semibold">Qty</th>
                      <th className="text-right px-3 py-2.5 font-semibold">Price</th>
                      <th className="text-right px-3 py-2.5 font-semibold">Total</th>
                      <th className="text-left px-3 py-2.5 font-semibold">Due</th>
                      <th className="px-3 py-2.5"></th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {items.map(i => (
                      <tr key={i.key} className="hover:bg-slate-50">
                        <td className="px-5 py-3 font-medium text-slate-800">{i.productName}</td>
                        <td className="px-3 py-3 text-slate-600">{byId(containerSizes, i.unitId)}</td>
                        <td className="px-3 py-3 text-right text-slate-800">{i.quantity}</td>
                        <td className="px-3 py-3 text-right text-slate-600">{money(parseFloat(i.price) || 0)}</td>
                        <td className="px-3 py-3 text-right font-semibold text-slate-900">{money(lineTotal(i))}</td>
                        <td className="px-3 py-3 text-slate-500">{i.dueDate || '—'}</td>
                        <td className="px-3 py-3 text-right">
                          <button
                            onClick={() => removeItem(i.key)}
                            title="Remove line"
                            className="text-slate-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded transition-colors"
                          >
                            <Trash2 size={16} />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                  <tfoot>
                    <tr className="bg-slate-50 border-t-2 border-slate-200">
                      <td colSpan={4} className="px-5 py-3 text-right font-semibold text-slate-600 uppercase text-xs tracking-wide">
                        Order total
                      </td>
                      <td className="px-3 py-3 text-right text-lg font-bold text-sky-700">{money(grandTotal)}</td>
                      <td colSpan={2}></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            )}
          </div>
        )}

        {/* ── Save — only once there is something to save ── */}
        {customerChosen && items.length > 0 && (
          <div className="flex justify-center pb-6">
            <button
              onClick={handleSave}
              disabled={saving}
              className="bg-[#0f172a] hover:bg-slate-800 text-white px-8 py-3 rounded-xl font-semibold flex items-center gap-2 disabled:opacity-50 shadow-lg transition-colors"
            >
              {saving ? <Loader2 className="w-5 h-5 animate-spin" /> : <Save className="w-5 h-5" />}
              Save order ({money(grandTotal)})
            </button>
          </div>
        )}

      </div>
    </div>
  );
};

export default OrderCreate;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<OrderCreate />);
}