import React, { useState, useEffect } from 'react';
import { Trash2, FlaskConical, User, Search, FileText, MapPin, ChevronDown, ChevronUp } from 'lucide-react';
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

const API_BASE  =  'http://localhost/Chemical';
const STORE_URL = `${API_BASE}/chemicaldeliveries/create`;
const fmt       = (n: number) => `R ${Number(n).toFixed(2)}`;

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

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-gray-100">

      {/* ── Top bar ── */}
      <div className="h-12 bg-gray-900 flex items-center px-5 gap-3 shrink-0">
        <FlaskConical className="w-4 h-4 text-blue-400" />
        <span className="text-white font-bold text-sm">Chemical Invoices &amp; Deliveries</span>
        <span className="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">CHEM</span>
        <div className="ml-auto">
          <button
            onClick={() => { window.location.href = STORE_URL; }}
            className="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors"
          >
            + New Invoice / Delivery
          </button>
        </div>
      </div>

      {/* ── Filters bar ── */}
      <div className="bg-white border-b border-gray-200 px-5 py-2.5 shrink-0">
        <div className="flex items-center gap-3 flex-wrap">
          <Search className="w-4 h-4 text-gray-400 shrink-0" />

          <div className="relative">
            <input type="text" placeholder="Customer…" value={searchCust}
              onChange={e => { setSearchCust(e.target.value); setShowCustSug(true); }}
              onFocus={() => setShowCustSug(true)}
              onBlur={() => setTimeout(() => setShowCustSug(false), 200)}
              className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40"
            />
            {showCustSug && custSuggestions.length > 0 && (
              <div className="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                {custSuggestions.map(c => (
                  <div key={c.id} onMouseDown={() => { setSearchCust(c.name); setShowCustSug(false); }}
                    className="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{c.name}</div>
                ))}
              </div>
            )}
          </div>

          <div className="relative">
            <input type="text" placeholder="Product…" value={searchProd}
              onChange={e => { setSearchProd(e.target.value); setShowProdSug(true); }}
              onFocus={() => setShowProdSug(true)}
              onBlur={() => setTimeout(() => setShowProdSug(false), 200)}
              className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40"
            />
            {showProdSug && prodSuggestions.length > 0 && (
              <div className="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                {prodSuggestions.map(p => (
                  <div key={p.id} onMouseDown={() => { setSearchProd(p.name); setShowProdSug(false); }}
                    className="px-3 py-2 hover:bg-blue-50 cursor-pointer text-sm">{p.name}</div>
                ))}
              </div>
            )}
          </div>

          <input type="date" value={dateFrom} onChange={e => setDateFrom(e.target.value)}
            className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <span className="text-gray-400 text-xs">–</span>
          <input type="date" value={dateTo} onChange={e => setDateTo(e.target.value)}
            className="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />

          {(searchCust || searchProd || dateFrom || dateTo) && (
            <button onClick={() => { setSearchCust(''); setSearchProd(''); setDateFrom(''); setDateTo(''); }}
              className="text-xs text-gray-400 hover:text-gray-700 underline">Clear</button>
          )}
          <span className="ml-auto text-xs text-gray-400">{filtered.length} record{filtered.length !== 1 ? 's' : ''}</span>
        </div>
      </div>

      {/* ── List ── */}
      <div className="flex-1 overflow-y-auto p-3 space-y-2">

        {filtered.length === 0 && (
          <div className="text-center py-16 bg-white rounded-xl border border-gray-200">
            <FlaskConical className="w-10 h-10 mx-auto text-gray-300 mb-3" />
            <p className="text-gray-500 text-sm">No records found</p>
          </div>
        )}

        {filtered.map(doc => {
          const isCollapsed = collapsed.has(doc.invoiceId);
          const totIncl     = Number(doc.totalExcl) + Number(doc.totalVat);

          return (
            <div key={doc.invoiceId} className="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">

              {/* ── Header row ── */}
              <div className="flex items-center gap-3 px-4 py-3 bg-gray-900 text-white">

                {/* Doc badges */}
                <div className="flex gap-1.5 shrink-0">
                  <span className="text-[11px] font-bold bg-gray-700 text-gray-200 px-2.5 py-0.5 rounded-full whitespace-nowrap">
                    INV #{doc.invoiceId}
                  </span>
                  {doc.hasDelivery && (
                    <span className="text-[11px] font-bold bg-blue-600 text-white px-2.5 py-0.5 rounded-full whitespace-nowrap">
                      DN #{doc.deliveryId}
                    </span>
                  )}
                </div>

                {/* Customer */}
                <div className="flex items-center gap-2 flex-1 min-w-0">
                  <User className="w-3.5 h-3.5 text-gray-400 shrink-0" />
                  <span className="font-semibold text-sm truncate">{doc.customerName}</span>
                </div>

                {/* Meta row — ref / address / date */}
                <div className="hidden md:flex items-center gap-4 shrink-0 text-xs text-gray-400">
                  {doc.reference && (
                    <span className="flex items-center gap-1">
                      <FileText className="w-3 h-3" />{doc.reference}
                    </span>
                  )}
                  {doc.address && (
                    <span className="flex items-center gap-1 max-w-[180px] truncate">
                      <MapPin className="w-3 h-3 shrink-0" />{doc.address}
                    </span>
                  )}
                  <span>{new Date(doc.date).toLocaleDateString()}</span>
                </div>

                {/* Totals */}
                <div className="flex items-baseline gap-1.5 shrink-0 text-right">
                  <span className="text-xs text-gray-500 hidden lg:inline">
                    {fmt(doc.totalExcl)} + {fmt(doc.totalVat)} VAT =
                  </span>
                  <span className="font-mono font-bold text-base text-blue-400 whitespace-nowrap">
                    {fmt(totIncl)}
                  </span>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-1.5 shrink-0">
                  <button onClick={() => handlePrintInvoice(doc.invoiceId)} title="Print Invoice"
                    className="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                    🧾 Invoice
                  </button>
                  {doc.hasDelivery && doc.deliveryId && (
                    <button onClick={() => handlePrintDelivery(doc.deliveryId!)} title="Print Delivery Note"
                      className="bg-blue-700 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                      📦 DN
                    </button>
                  )}
                  <button onClick={() => handleDelete(doc)} disabled={deleting === doc.invoiceId}
                    title="Delete"
                    className="bg-red-700 hover:bg-red-600 disabled:opacity-50 text-white px-2.5 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1">
                    {deleting === doc.invoiceId
                      ? <span className="text-xs">…</span>
                      : <><Trash2 className="w-3.5 h-3.5" /><span className="hidden sm:inline">Del</span></>
                    }
                  </button>
                  <button onClick={() => toggleCollapse(doc.invoiceId)}
                    className="text-gray-400 hover:text-white p-1.5 rounded-lg transition-colors">
                    {isCollapsed ? <ChevronDown className="w-4 h-4" /> : <ChevronUp className="w-4 h-4" />}
                  </button>
                </div>
              </div>

              {/* ── Items table — visible by default ── */}
              {!isCollapsed && (
                <div className="px-4 py-3">
                  {doc.notes && (
                    <p className="text-xs text-gray-400 italic mb-2">{doc.notes}</p>
                  )}
                  <table className="w-full text-sm border-collapse">
                    <thead>
                      <tr className="text-[11px] uppercase tracking-wider text-gray-400 border-b border-gray-100">
                        <th className="text-left pb-1.5 pr-4 font-semibold">Product</th>
                        <th className="text-left pb-1.5 pr-4 font-semibold">Pack / Container</th>
                        <th className="text-center pb-1.5 pr-4 font-semibold w-16">Qty</th>
                        <th className="text-right pb-1.5 pr-4 font-semibold w-28">Unit Price</th>
                        <th className="text-center pb-1.5 pr-4 font-semibold w-14">VAT</th>
                        <th className="text-right pb-1.5 pr-4 font-semibold w-24">VAT Amt</th>
                        <th className="text-right pb-1.5 font-semibold w-28">Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      {doc.items.map((item, idx) => (
                        <tr key={idx} className={`border-b border-gray-50 ${idx % 2 === 1 ? 'bg-gray-50' : ''}`}>
                          <td className="py-2 pr-4 font-semibold text-gray-900">{item.productName}</td>
                          <td className="py-2 pr-4 text-gray-500 text-sm">{item.containerName}</td>
                          <td className="py-2 pr-4 text-center font-bold text-gray-800">{item.quantity}</td>
                          <td className="py-2 pr-4 text-right font-mono text-gray-700">{fmt(item.unitPrice)}</td>
                          <td className="py-2 pr-4 text-center">
                            {item.vatApplicable
                              ? <span className="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Yes</span>
                              : <span className="bg-gray-100 text-gray-400 text-[10px] font-bold px-2 py-0.5 rounded-full">No</span>
                            }
                          </td>
                          <td className="py-2 pr-4 text-right font-mono text-gray-500">{fmt(item.vatAmount)}</td>
                          <td className="py-2 text-right font-mono font-bold text-gray-900">{fmt(item.total)}</td>
                        </tr>
                      ))}
                    </tbody>

                  </table>
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