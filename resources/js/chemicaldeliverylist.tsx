import React, { useState, useEffect } from 'react';
import { Trash2, FlaskConical, User, Search, FileText, MapPin, ChevronDown, ChevronUp, Plus, Receipt, Truck, Calendar, X, Package } from 'lucide-react';
import { createRoot } from 'react-dom/client';

declare global {
  interface Window {
    laravelApiUrl:        string;
    customersData:        { id: string | number; name: string }[];
    chemicalProductsData: { id: number; name: string; sku: string | null }[];
    containerSizesData:   { id: string | number; name: string }[];
  }
}

interface RawRow {
  invoice_id:         number;
  invoice_reference:  string | null;
  invoice_customerId: number;
  invoice_totalValue: number;
  invoice_totalVat:   number;
  invoice_totIncl:    number;
  invoice_notes:      string | null;
  invoice_created_at: string;
  delivery_id:        number | null;
  delivery_address:   string | null;
  item_productId:     number;
  item_containerId:   number | null;
  item_quantity:      number;
  item_unitPrice:     number;
  item_vatApplicable: number;
  item_vatAmount:     number;
  item_total:         number;
}

interface DocItem {
  productId:     number;
  productName:   string;
  containerName: string;
  quantity:      number;
  unitPrice:     number;
  vatApplicable: boolean;
  vatAmount:     number;
  total:         number;
}

interface GroupedDoc {
  invoiceId:    number;
  reference:    string | null;
  customerId:   number;
  customerName: string;
  address:      string | null;
  date:         string;
  totalExcl:    number;
  totalVat:     number;
  totalIncl:    number;
  notes:        string | null;
  hasDelivery:  boolean;
  deliveryId:   number | null;
  items:        DocItem[];
}

const API_BASE  = window.laravelApiUrl || 'http://localhost/Chemical';
const STORE_URL = `${API_BASE}/chemicaldeliveries/create`;
const fmt       = (n: number) => `R ${Number(n).toFixed(2)}`;
const VAT_RATE  = 15;

// Invoice lines are stored excl. VAT — show them the way the create page does:
// unit price incl. VAT, then the line's excl. / VAT / incl. amounts.
const r2 = (n: number) => Math.round(n * 100) / 100;
const lineFigures = (i: DocItem) => {
  const rate      = i.vatApplicable ? VAT_RATE / 100 : 0;
  const unitIncl  = r2(Number(i.unitPrice) * (1 + rate));
  const totalIncl = r2(Number(i.quantity) * unitIncl);
  const excl      = r2(totalIncl / (1 + rate));
  return { unitIncl, excl, vat: r2(totalIncl - excl), totalIncl };
};

function ChemicalDeliveryList() {
  const customers      = window.customersData        || [];
  const products       = window.chemicalProductsData || [];
  const containerSizes = window.containerSizesData   || [];

  const [rows,        setRows]        = useState<RawRow[]>([]);
  const [deleting,    setDeleting]    = useState<number | null>(null);
  const [collapsed,   setCollapsed]   = useState<Set<number>>(new Set());
  const [searchCust,  setSearchCust]  = useState('');
  const [searchProd,  setSearchProd]  = useState('');
  const [dateFrom,    setDateFrom]    = useState('');
  const [dateTo,      setDateTo]      = useState('');
  const [showCustSug, setShowCustSug] = useState(false);
  const [showProdSug, setShowProdSug] = useState(false);

  const fetchDocs = async () => {
    try {
      const res  = await fetch(`${API_BASE}/chemicaldeliveries/index?list=1`);
      const data = await res.json();
      setRows(Array.isArray(data) ? data : []);
    } catch (err) {
      console.error('Fetch error:', err);
      setRows([]);
    }
  };

  useEffect(() => { fetchDocs(); }, []);

  const customerName = (id: number) =>
    customers.find(c => String(c.id) === String(id))?.name ?? 'Unknown';

  const productName = (id: number) =>
    products.find(p => p.id === id)?.name ?? 'Unknown Product';

  const containerName = (id: number | null) =>
    id == null ? '—' : (containerSizes.find(c => String(c.id) === String(id))?.name ?? '—');

  // default open — only track collapsed ones
  const toggleCollapse = (id: number) =>
    setCollapsed(prev => {
      const next = new Set(prev);
      next.has(id) ? next.delete(id) : next.add(id);
      return next;
    });

  const grouped: GroupedDoc[] = Object.values(
    rows.reduce((acc, row) => {
      const key = row.invoice_id;
      if (!acc[key]) {
        acc[key] = {
          invoiceId:    row.invoice_id,
          reference:    row.invoice_reference,
          customerId:   row.invoice_customerId,
          customerName: customerName(row.invoice_customerId),
          address:      row.delivery_address,
          date:         row.invoice_created_at,
          totalExcl:    row.invoice_totalValue,
          totalVat:     row.invoice_totalVat,
          totalIncl:    row.invoice_totIncl,
          notes:        row.invoice_notes,
          hasDelivery:  row.delivery_id !== null,
          deliveryId:   row.delivery_id,
          items:        [],
        };
      }
      acc[key].items.push({
        productId:     row.item_productId,
        productName:   productName(row.item_productId),
        containerName: containerName(row.item_containerId),
        quantity:      row.item_quantity,
        unitPrice:     row.item_unitPrice,
        vatApplicable: !!row.item_vatApplicable,
        vatAmount:     row.item_vatAmount,
        total:         row.item_total,
      });
      return acc;
    }, {} as Record<number, GroupedDoc>)
  );

  const handleDelete = async (doc: GroupedDoc) => {
    if (!confirm('Delete this invoice' + (doc.hasDelivery ? ' and delivery note' : '') + '?')) return;
    setDeleting(doc.invoiceId);
    try {
      const encoded = encodeURIComponent(JSON.stringify({ invoiceId: doc.invoiceId, deliveryId: doc.deliveryId }));
      await fetch(`${API_BASE}/chemicaldeliveries/destroy?data=${encoded}`);
      await fetchDocs();
    } catch {
      alert('Delete failed.');
    } finally {
      setDeleting(null);
    }
  };

  const handlePrintInvoice  = (id: number) => window.open(`${API_BASE}/print/invoice?id=${id}`,  '_blank');
  const handlePrintDelivery = (id: number) => window.open(`${API_BASE}/print/delivery?id=${id}`, '_blank');

  const filtered = grouped
    .filter(d => {
      const matchCust = !searchCust.trim() || d.customerName.toLowerCase().includes(searchCust.toLowerCase());
      const matchProd = !searchProd.trim() || d.items.some(i => i.productName.toLowerCase().includes(searchProd.toLowerCase()));
      const matchFrom = !dateFrom || new Date(d.date) >= new Date(dateFrom);
      const matchTo   = !dateTo   || new Date(d.date) <= new Date(dateTo);
      return matchCust && matchProd && matchFrom && matchTo;
    })
    .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());

  const custSuggestions = customers.filter(c => searchCust.trim() && c.name.toLowerCase().includes(searchCust.toLowerCase()));
  const prodSuggestions = products.filter(p  => searchProd.trim() && p.name.toLowerCase().includes(searchProd.toLowerCase()));

  // ── summary of what's showing ─────────────────────────────────────────────
  const sumExcl  = filtered.reduce((s, d) => s + Number(d.totalExcl), 0);
  const sumVat   = filtered.reduce((s, d) => s + Number(d.totalVat),  0);
  const sumIncl  = sumExcl + sumVat;
  const dnCount  = filtered.filter(d => d.hasDelivery).length;
  const anyFilter = !!(searchCust || searchProd || dateFrom || dateTo);

  const fInp = "bg-white border border-blue-100 rounded-lg px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition";

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-slate-100" style={{ background: '#eef2f7' }}>

      {/* ── Top bar ── */}
      <div className="bg-blue-700 shadow-md shrink-0" style={{ background: '#1d4ed8' }}>
        <div className="h-14 flex items-center px-5 gap-3">
          <div className="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
            <FlaskConical className="w-5 h-5 text-white" />
          </div>
          <div className="leading-tight">
            <p className="text-white font-bold text-base">Invoices &amp; Deliveries</p>
            <p className="text-blue-200 text-[11px]">Chemical products · VAT 15% included</p>
          </div>
          <div className="ml-auto">
            <button
              onClick={() => { window.location.href = STORE_URL; }}
              className="flex items-center gap-1.5 bg-white text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-lg text-xs font-bold shadow transition-colors"
            >
              <Plus className="w-4 h-4" /> New Invoice
            </button>
          </div>
        </div>
      </div>

      {/* ── Filters + summary ── */}
      <div className="px-4 pt-4 shrink-0 flex flex-col gap-3">
        <div className="bg-white rounded-2xl shadow-sm ring-1 ring-blue-100 px-4 py-3 flex items-center gap-3 flex-wrap">
          <Search className="w-4 h-4 text-blue-500 shrink-0" />

          <div className="relative">
            <User className="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input type="text" placeholder="Customer…" value={searchCust}
              onChange={e => { setSearchCust(e.target.value); setShowCustSug(true); }}
              onFocus={() => setShowCustSug(true)}
              onBlur={() => setTimeout(() => setShowCustSug(false), 200)}
              className={`${fInp} pl-8 w-48`}
            />
            {showCustSug && custSuggestions.length > 0 && (
              <div className="absolute z-20 w-full mt-1 bg-white ring-1 ring-blue-100 rounded-xl shadow-xl max-h-48 overflow-y-auto py-1">
                {custSuggestions.map(c => (
                  <div key={c.id} onMouseDown={() => { setSearchCust(c.name); setShowCustSug(false); }}
                    className="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{c.name}</div>
                ))}
              </div>
            )}
          </div>

          <div className="relative">
            <Package className="w-3.5 h-3.5 absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
            <input type="text" placeholder="Product…" value={searchProd}
              onChange={e => { setSearchProd(e.target.value); setShowProdSug(true); }}
              onFocus={() => setShowProdSug(true)}
              onBlur={() => setTimeout(() => setShowProdSug(false), 200)}
              className={`${fInp} pl-8 w-48`}
            />
            {showProdSug && prodSuggestions.length > 0 && (
              <div className="absolute z-20 w-full mt-1 bg-white ring-1 ring-blue-100 rounded-xl shadow-xl max-h-48 overflow-y-auto py-1">
                {prodSuggestions.map(p => (
                  <div key={p.id} onMouseDown={() => { setSearchProd(p.name); setShowProdSug(false); }}
                    className="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{p.name}</div>
                ))}
              </div>
            )}
          </div>

          <div className="flex items-center gap-2">
            <Calendar className="w-4 h-4 text-slate-400" />
            <input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)} className={fInp} />
            <span className="text-slate-400 text-xs">to</span>
            <input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)} className={fInp} />
          </div>

          {anyFilter && (
            <button onClick={() => { setSearchCust(''); setSearchProd(''); setDateFrom(''); setDateTo(''); }}
              className="flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800 px-2 py-1 rounded-md hover:bg-blue-50">
              <X className="w-3.5 h-3.5" /> Clear
            </button>
          )}
        </div>

        {/* Summary tiles */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div className="bg-white rounded-2xl ring-1 ring-blue-100 shadow-sm px-4 py-3">
            <p className="text-[11px] font-bold text-blue-900/60 uppercase tracking-wider">Invoices</p>
            <p className="text-2xl font-bold text-slate-900 mt-0.5">{filtered.length}</p>
          </div>
          <div className="bg-white rounded-2xl ring-1 ring-blue-100 shadow-sm px-4 py-3">
            <p className="text-[11px] font-bold text-blue-900/60 uppercase tracking-wider">With delivery note</p>
            <p className="text-2xl font-bold text-slate-900 mt-0.5">{dnCount}</p>
          </div>
          <div className="bg-white rounded-2xl ring-1 ring-blue-100 shadow-sm px-4 py-3">
            <p className="text-[11px] font-bold text-blue-900/60 uppercase tracking-wider">VAT</p>
            <p className="text-2xl font-bold font-mono text-slate-900 mt-0.5">{fmt(sumVat)}</p>
          </div>
          <div className="rounded-2xl shadow-md bg-blue-800 text-white px-4 py-3" style={{ background: '#1e40af' }}>
            <p className="text-[11px] font-bold text-blue-200 uppercase tracking-wider">Total incl. VAT</p>
            <p className="text-2xl font-bold font-mono mt-0.5">{fmt(sumIncl)}</p>
          </div>
        </div>
      </div>

      {/* ── List ── */}
      <div className="flex-1 overflow-y-auto p-4 space-y-3">

        {filtered.length === 0 && (
          <div className="text-center py-20 bg-white rounded-2xl ring-1 ring-blue-100 shadow-sm">
            <div className="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 flex items-center justify-center">
              <FlaskConical className="w-8 h-8 text-blue-400" />
            </div>
            <p className="text-slate-700 font-semibold">No invoices found</p>
            <p className="text-slate-400 text-sm mt-1">{anyFilter ? 'Try clearing the filters' : 'Create one with New Invoice'}</p>
          </div>
        )}

        {filtered.map(doc => {
          const isCollapsed = collapsed.has(doc.invoiceId);
          const totIncl     = Number(doc.totalExcl) + Number(doc.totalVat);

          return (
            <div key={doc.invoiceId} className="bg-white rounded-2xl ring-1 ring-blue-100 overflow-hidden shadow-sm hover:shadow-md transition-shadow">

              {/* ── Header row ── */}
              <div className="flex items-center gap-4 px-4 py-3 border-l-4 border-blue-600 bg-blue-50" style={{ background: '#eff6ff', borderLeftColor: '#2563eb' }}>

                <button onClick={() => toggleCollapse(doc.invoiceId)}
                  title={isCollapsed ? 'Show items' : 'Hide items'}
                  className="text-blue-500 hover:text-blue-800 hover:bg-blue-100 p-1 rounded-lg transition-colors shrink-0">
                  {isCollapsed ? <ChevronDown className="w-5 h-5" /> : <ChevronUp className="w-5 h-5" />}
                </button>

                {/* Customer + meta */}
                <div className="flex-1 min-w-0">
                  <div className="flex items-center gap-2 flex-wrap">
                    <span className="font-bold text-slate-900 text-base truncate">{doc.customerName}</span>
                    <span className="text-[10px] font-bold bg-blue-600 text-white px-2 py-0.5 rounded-full whitespace-nowrap" style={{ background: '#2563eb' }}>
                      INV #{doc.invoiceId}
                    </span>
                    {doc.hasDelivery && (
                      <span className="text-[10px] font-bold bg-sky-100 text-sky-800 px-2 py-0.5 rounded-full whitespace-nowrap">
                        DN #{doc.deliveryId}
                      </span>
                    )}
                  </div>
                  <div className="flex items-center gap-4 mt-1 text-xs text-slate-500 flex-wrap">
                    <span className="flex items-center gap-1">
                      <Calendar className="w-3 h-3 text-blue-400" />{new Date(doc.date).toLocaleDateString()}
                    </span>
                    {doc.reference && (
                      <span className="flex items-center gap-1">
                        <FileText className="w-3 h-3 text-blue-400" />{doc.reference}
                      </span>
                    )}
                    {doc.address && (
                      <span className="flex items-center gap-1 max-w-[260px] truncate">
                        <MapPin className="w-3 h-3 text-blue-400 shrink-0" />
                        <span className="truncate">{doc.address.replace(/\s*\n\s*/g, ', ')}</span>
                      </span>
                    )}
                  </div>
                </div>

                {/* Totals */}
                <div className="text-right shrink-0">
                  <p className="font-mono font-bold text-lg text-blue-800 whitespace-nowrap">{fmt(totIncl)}</p>
                  <p className="text-[11px] text-slate-400 whitespace-nowrap">incl. {fmt(doc.totalVat)} VAT</p>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-1.5 shrink-0">
                  <button onClick={() => handlePrintInvoice(doc.invoiceId)} title="Print invoice"
                    className="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-semibold shadow-sm transition-colors">
                    <Receipt className="w-3.5 h-3.5" /> Invoice
                  </button>
                  {doc.hasDelivery && doc.deliveryId && (
                    <button onClick={() => handlePrintDelivery(doc.deliveryId!)} title="Print delivery note"
                      className="flex items-center gap-1.5 bg-white ring-1 ring-blue-200 text-blue-700 hover:bg-blue-50 px-3 py-2 rounded-lg text-xs font-semibold transition-colors">
                      <Truck className="w-3.5 h-3.5" /> Delivery note
                    </button>
                  )}
                  <button onClick={() => handleDelete(doc)} disabled={deleting === doc.invoiceId}
                    title="Delete"
                    className="text-slate-400 hover:text-red-600 hover:bg-red-50 disabled:opacity-50 p-2 rounded-lg transition-colors">
                    {deleting === doc.invoiceId
                      ? <span className="text-xs">…</span>
                      : <Trash2 className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              {/* ── Items table — visible by default ── */}
              {!isCollapsed && (
                <div className="px-4 pb-3 pt-2">
                  {doc.notes && (
                    <p className="text-xs text-slate-500 italic bg-amber-50 border border-amber-100 rounded-lg px-3 py-1.5 mb-2">{doc.notes}</p>
                  )}
                  <div className="rounded-xl overflow-hidden ring-1 ring-blue-100">
                    <table className="w-full text-sm border-collapse">
                      <thead className="bg-blue-700 text-white" style={{ background: '#1d4ed8' }}>
                        <tr className="text-[11px] uppercase tracking-wider">
                          <th className="text-left   px-3 py-2 font-bold">Product</th>
                          <th className="text-left   px-3 py-2 font-bold">Pack</th>
                          <th className="text-center px-3 py-2 font-bold w-16">Qty</th>
                          <th className="text-right  px-3 py-2 font-bold w-36">Unit price (incl.)</th>
                          <th className="text-right  px-3 py-2 font-bold w-28">Excl.</th>
                          <th className="text-right  px-3 py-2 font-bold w-28">VAT</th>
                          <th className="text-right  px-3 py-2 font-bold w-32">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        {doc.items.map((item, idx) => { const f = lineFigures(item); return (
                          <tr key={idx} className={`border-t border-blue-50 hover:bg-blue-50/60 ${idx % 2 === 1 ? 'bg-slate-50/70' : 'bg-white'}`}>
                            <td className="px-3 py-2 font-semibold text-slate-900">{item.productName}</td>
                            <td className="px-3 py-2">
                              <span className="bg-slate-100 text-slate-600 text-xs font-medium px-2 py-0.5 rounded-md">{item.containerName}</span>
                            </td>
                            <td className="px-3 py-2 text-center font-bold text-slate-800">{item.quantity}</td>
                            <td className="px-3 py-2 text-right font-mono text-slate-700">{fmt(f.unitIncl)}</td>
                            <td className="px-3 py-2 text-right font-mono text-slate-500">{fmt(f.excl)}</td>
                            <td className="px-3 py-2 text-right font-mono text-slate-500">{fmt(f.vat)}</td>
                            <td className="px-3 py-2 text-right font-mono font-bold text-blue-800">{fmt(f.totalIncl)}</td>
                          </tr>
                        ); })}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}
            </div>
          );
        })}
      </div>
    </div>
  );
}

export default ChemicalDeliveryList;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ChemicalDeliveryList />);
}