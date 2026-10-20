import React, { useState, useEffect } from 'react';
import { FlaskConical, User, MapPin, Package, Trash2, Save, ChevronDown, RotateCcw, ArrowLeft, FileText, Receipt, Hash, StickyNote, Phone, Mail } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:        string;
    customersData:        Customer[];
    chemicalProductsData: ChemProduct[];
    containerSizesData:   { id: string; name: string }[];
  }
}

interface Customer {
  id:                    string;
  name:                  string;
  legalName?:            string | null;
  vatNo?:                string | null;
  accountNumber?:        string | null;
  emailAddress?:         string | null;
  phoneNumber?:          string | null;
  mobileNumber?:         string | null;
  contactPerson?:        string | null;
  contactPersonLastName?: string | null;
  // street address (delivery)
  sAAttentionTo?:        string | null;
  sAAttentionLine1?:     string | null;
  sAAttentionLine2?:     string | null;
  sAAttentionLine3?:     string | null;
  sAAttentionLine4?:     string | null;
  sACity?:               string | null;
  sARegion?:             string | null;
  sAPostalCode?:         string | null;
  sACountry?:            string | null;
  // postal address (fallback)
  pOAttentionTo?:        string | null;
  pOAddressLine1?:       string | null;
  pOAddressLine2?:       string | null;
  pOAddressLine3?:       string | null;
  pOAddressLine4?:       string | null;
  pOCity?:               string | null;
  pORegion?:             string | null;
  pOPostalCode?:         string | null;
  pOCountry?:            string | null;
}

interface ChemProduct {
  id:                  number;
  name:                string;
  sku:                 string | null;
  price_incl_vat:          number | null;
  vat_applicable:      number;
  vat_rate:            number | null;
  container_size_id:   number | null;
  stock_unit_id:       number | null;
}

interface LineItem {
  rowId:         number;
  productId:     number;
  productName:   string;
  sku:           string;
  containerId:   number | null;
  containerName: string;
  quantity:      string;
  unitPrice:     number;
  vatApplicable: boolean;
  vatRate:       number;
  subtotal:      number;
  vatAmount:     number;
  total:         number;
}

const API_BASE    = window.laravelApiUrl || 'http://localhost/Chemical';
const LIST_URL    = `${API_BASE}/chemicaldeliverylist`;
const VAT_DEFAULT = 15;

// ── helpers ──────────────────────────────────────────────────────────────────
const fmt = (n: number) => `R ${n.toFixed(2)}`;

const clean = (v: any) => String(v ?? '').trim();

// Street address is what goes on a delivery note; postal is the fallback for
// customers who only ever had the postal block filled in.
const buildAddress = (c: Customer): string => {
  const street = [
    c.sAAttentionTo, c.sAAttentionLine1, c.sAAttentionLine2,
    c.sAAttentionLine3, c.sAAttentionLine4,
    [clean(c.sACity), clean(c.sARegion)].filter(Boolean).join(', '),
    c.sAPostalCode, c.sACountry,
  ].map(clean).filter(Boolean);

  if (street.length) return street.join('\n');

  const postal = [
    c.pOAttentionTo, c.pOAddressLine1, c.pOAddressLine2,
    c.pOAddressLine3, c.pOAddressLine4,
    [clean(c.pOCity), clean(c.pORegion)].filter(Boolean).join(', '),
    c.pOPostalCode, c.pOCountry,
  ].map(clean).filter(Boolean);

  return postal.join('\n');
};

// VAT always applies to every line.
// unitPrice is VAT-INCLUSIVE, so VAT is taken out of the price, not added on top.
const calcLine = (item: LineItem, vatOn: boolean): LineItem => {
  const qty      = parseFloat(item.quantity) || 0;
  const total    = qty * item.unitPrice;                       // incl. VAT
  const charges  = vatOn && item.vatApplicable;
  const subtotal = charges ? total / (1 + item.vatRate / 100) : total;   // excl. VAT
  const vatAmt   = total - subtotal;
  return { ...item, subtotal, vatAmount: vatAmt, total };
};

// The excl. figure per unit, for what gets stored against the invoice line
const unitExcl = (l: LineItem) =>
  l.vatApplicable ? l.unitPrice / (1 + l.vatRate / 100) : l.unitPrice;

// ─────────────────────────────────────────────────────────────────────────────
const ChemicalDeliveryForm: React.FC = () => {
  const customers      = window.customersData        || [];
  const allProducts    = window.chemicalProductsData || [];
  const containerSizes = window.containerSizesData   || [];

  // ── form state ────────────────────────────────────────────────────────────
  const [customerId,    setCustomerId]    = useState(customers[0]?.id ?? '');
  const [address,       setAddress]       = useState('');
  const [reference,     setReference]     = useState('');
  const [notes,         setNotes]         = useState('');
  const [docType,       setDocType]       = useState<'both' | 'invoice'>('invoice');
  const vatEnabled = true;   // VAT always applies — there is no switch
  const [lines,         setLines]         = useState<LineItem[]>([]);
  const [nextId,        setNextId]        = useState(1);
  const [productSearch, setProductSearch] = useState('');
  const [saving,        setSaving]        = useState(false);
  const [toast,         setToast]         = useState<{ msg: string; ok: boolean } | null>(null);

  const customer = customers.find(c => String(c.id) === String(customerId)) ?? null;

  const flash = (msg: string, ok: boolean) => {
    setToast({ msg, ok });
    setTimeout(() => setToast(null), 3500);
  };

  // ── pull the customer's details in on selection ───────────────────────────
  // Address is a starting point, not a lock — a one-off delivery to a
  // different site is normal, so the field stays editable and can be reset.
  useEffect(() => {
    if (!customer) { setAddress(''); return; }
    setAddress(buildAddress(customer));
  }, [customerId]);

  const resetAddress = () => {
    if (customer) setAddress(buildAddress(customer));
  };

  const contactName = customer
    ? [clean(customer.contactPerson), clean(customer.contactPersonLastName)].filter(Boolean).join(' ')
    : '';
  const contactPhone = customer ? (clean(customer.phoneNumber) || clean(customer.mobileNumber)) : '';

  // ── filtered product list ─────────────────────────────────────────────────
  const filtered = allProducts.filter(p =>
    p.name.toLowerCase().includes(productSearch.toLowerCase())
  );

  const containerName = (p: ChemProduct) => {
    const cid = p.container_size_id ?? p.stock_unit_id;
    return containerSizes.find(c => String(c.id) === String(cid))?.name ?? '—';
  };

  // ── add product line — clicking a suggestion adds it straight away ────────
  const handleAddProduct = (p: ChemProduct) => {
    // already on the list? bump the quantity instead of duplicating the row
    const existing = lines.find(l => l.productId === p.id);
    if (existing) {
      const qty = (parseFloat(existing.quantity) || 0) + 1;
      setLines(prev => prev.map(l =>
        l.rowId === existing.rowId ? calcLine({ ...l, quantity: String(qty) }, vatEnabled) : l
      ));
      setProductSearch('');
      flash(`${p.name} — qty ${qty}`, true);
      return;
    }

    const cid = p.container_size_id ?? p.stock_unit_id ?? null;

    const newLine: LineItem = calcLine({
      rowId:         nextId,
      productId:     p.id,
      productName:   p.name,
      sku:           p.sku ?? '',
      containerId:   cid,
      containerName: containerName(p),
      quantity:      '1',
      unitPrice:     Number(p.price_incl_vat ?? 0),
      vatApplicable: true,                                    // every product carries VAT — the document switch decides
      vatRate:       Number(p.vat_rate) > 0 ? Number(p.vat_rate) : VAT_DEFAULT,
      subtotal:      0,
      vatAmount:     0,
      total:         0,
    }, vatEnabled);
    setLines(prev => [...prev, newLine]);
    setNextId(n => n + 1);
    setProductSearch('');
  };

  const handleRemove = (rowId: number) =>
    setLines(prev => prev.filter(l => l.rowId !== rowId));

  const handleQtyChange = (rowId: number, val: string) =>
    setLines(prev => prev.map(l => l.rowId === rowId ? calcLine({ ...l, quantity: val }, vatEnabled) : l));

  const handlePriceChange = (rowId: number, val: string) =>
    setLines(prev => prev.map(l => l.rowId === rowId ? calcLine({ ...l, unitPrice: parseFloat(val) || 0 }, vatEnabled) : l));

  // ── totals ────────────────────────────────────────────────────────────────
  const totExcl = lines.reduce((s, l) => s + l.subtotal,  0);
  const totVat  = lines.reduce((s, l) => s + l.vatAmount, 0);
  const totIncl = lines.reduce((s, l) => s + l.total,     0);

  // ── save ──────────────────────────────────────────────────────────────────
  const handleSave = async () => {
    if (!customerId)        { flash('Please select a customer',        false); return; }
    if (!address.trim())    { flash('A delivery address is required',  false); return; }
    if (lines.length === 0) { flash('Add at least one product',        false); return; }
    if (lines.some(l => !parseFloat(l.quantity) || l.quantity === '0'))
                            { flash('All lines need a quantity > 0',   false); return; }

    setSaving(true);
    try {
      const payload = {
        customerId,
        customerName:  customer?.legalName || customer?.name || '',
        customerVatNo: clean(customer?.vatNo),
        address,
        reference,
        notes,
        docType,
        vatEnabled: vatEnabled ? 1 : 0,
        items: lines.map(l => ({
          productId:     l.productId,
          productName:   l.productName,
          sku:           l.sku,
          containerId:   l.containerId,
          quantity:      parseFloat(l.quantity),
          unitPrice:     Math.round(unitExcl(l) * 10000) / 10000,   // excl. VAT, as before
          unitPriceIncl: l.unitPrice,                                  // what was entered
          vatApplicable: (vatEnabled && l.vatApplicable) ? 1 : 0,
          vatRate:       vatEnabled && l.vatApplicable ? l.vatRate : 0,
          subtotal:      l.subtotal,
          vatAmount:     l.vatAmount,
          total:         l.total,
        })),
        totExcl,
        totVat,
        totIncl,
      };

      const encoded = encodeURIComponent(JSON.stringify(payload));
      await axios.get(`${API_BASE}/chemicaldeliveries/store?data=${encoded}`);
      flash('Delivery note saved!', true);
      setTimeout(() => { window.location.href = LIST_URL; }, 1200);
    } catch (err: any) {
      if (!err.response) {
        flash('Saved!', true);
        setTimeout(() => { window.location.href = LIST_URL; }, 1200);
      } else {
        flash(err.response?.data?.message ?? 'Save failed', false);
      }
    } finally {
      setSaving(false);
    }
  };

  // ─────────────────────────────────────────────────────────────────────────
  const inp = "w-full bg-white text-slate-800 border border-blue-100 rounded-lg px-3 py-2 text-sm placeholder-slate-400 focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition";
  const sel = `${inp} appearance-none`;
  const lbl = "flex items-center gap-1.5 text-[11px] font-bold text-blue-900/60 uppercase tracking-wider mb-1.5";

  const itemCount = lines.reduce((s, l) => s + (parseFloat(l.quantity) || 0), 0);

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-slate-100" style={{ background: '#eef2f7' }}>

      {/* ── Toast ── */}
      {toast && (
        <div className={`fixed top-4 left-1/2 -translate-x-1/2 z-50 px-5 py-2.5 rounded-xl text-sm font-semibold shadow-xl flex items-center gap-2 ${
          toast.ok ? 'bg-blue-600 text-white' : 'bg-red-600 text-white'
        }`}>
          {toast.ok ? '✓' : '✕'} {toast.msg}
        </div>
      )}

      {/* ── Top bar ── */}
      <div className="bg-blue-700 shadow-md shrink-0" style={{ background: '#1d4ed8' }}>
        <div className="h-14 flex items-center px-5 gap-3">
          <div className="w-9 h-9 rounded-lg bg-white/15 flex items-center justify-center">
            <FlaskConical className="w-5 h-5 text-white" />
          </div>
          <div className="leading-tight">
            <p className="text-white font-bold text-base">New Invoice</p>
            <p className="text-blue-200 text-[11px]">Chemical delivery · prices include VAT 15%</p>
          </div>

          {/* Doc type toggle */}
          <div className="ml-6 flex items-center gap-1 rounded-lg p-1" style={{ background: '#1e3a8a' }}>
            {(['invoice', 'both'] as const).map(t => (
              <button
                key={t}
                onClick={() => setDocType(t)}
                className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-bold transition-all ${
                  docType === t ? 'bg-white text-blue-700 shadow' : 'text-blue-100 hover:text-white'
                }`}
              >
                {t === 'both' ? <FileText className="w-3.5 h-3.5" /> : <Receipt className="w-3.5 h-3.5" />}
                {t === 'both' ? 'Invoice + Delivery Note' : 'Invoice Only'}
              </button>
            ))}
          </div>

          <div className="ml-auto flex gap-2 shrink-0">
            <button
              onClick={() => { window.location.href = LIST_URL; }}
              className="flex items-center gap-1.5 text-blue-100 hover:text-white hover:bg-white/10 text-xs font-semibold px-3 py-2 rounded-lg transition-colors"
            >
              <ArrowLeft className="w-3.5 h-3.5" /> Back to list
            </button>
            <button
              onClick={handleSave}
              disabled={saving}
              className="flex items-center gap-1.5 bg-white text-blue-700 hover:bg-blue-50 disabled:opacity-50 px-4 py-2 rounded-lg text-xs font-bold shadow transition-colors"
            >
              <Save className="w-4 h-4" />
              {saving ? 'Saving…' : docType === 'both' ? 'Generate Invoice + Delivery Note' : 'Generate Invoice'}
            </button>
          </div>
        </div>
      </div>

      {/* ── Body ── */}
      <div className="flex-1 overflow-hidden flex gap-4 p-4 min-h-0">

        {/* ── Left — customer & document ── */}
        <div className="w-80 shrink-0 flex flex-col gap-4 overflow-y-auto pr-1">

          <div className="bg-white rounded-2xl shadow-sm ring-1 ring-blue-100 overflow-hidden shrink-0">
            <div className="px-4 py-2.5 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
              <User className="w-4 h-4 text-blue-600" />
              <p className="text-xs font-bold text-blue-900 uppercase tracking-wider">Customer</p>
            </div>

            <div className="p-4 flex flex-col gap-4">
              <div>
                <label className={lbl}>Customer *</label>
                <div className="relative">
                  <select className={`${sel} pr-8 font-semibold`} value={customerId} onChange={e => setCustomerId(e.target.value)}>
                    <option value="">-- Select --</option>
                    {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                  </select>
                  <ChevronDown className="absolute right-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-400 pointer-events-none" />
                </div>
              </div>

              {/* Details pulled from the customer record */}
              {customer && (
                <div className="rounded-xl bg-blue-50 border border-blue-100 px-3.5 py-3 text-xs text-slate-600 flex flex-col gap-1.5">
                  {clean(customer.legalName) && clean(customer.legalName) !== clean(customer.name) && (
                    <p className="font-bold text-slate-800 text-sm">{customer.legalName}</p>
                  )}
                  {contactName  && <p className="flex items-center gap-2"><User  className="w-3.5 h-3.5 text-blue-400" />{contactName}</p>}
                  {contactPhone && <p className="flex items-center gap-2"><Phone className="w-3.5 h-3.5 text-blue-400" />{contactPhone}</p>}
                  {clean(customer.emailAddress) && (
                    <p className="flex items-center gap-2 truncate"><Mail className="w-3.5 h-3.5 text-blue-400 shrink-0" />{customer.emailAddress}</p>
                  )}
                  {(clean(customer.vatNo) || clean(customer.accountNumber)) && (
                    <div className="flex flex-wrap gap-1.5 pt-1">
                      {clean(customer.vatNo) && (
                        <span className="bg-blue-100 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-full">VAT {customer.vatNo}</span>
                      )}
                      {clean(customer.accountNumber) && (
                        <span className="bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded-full">ACC {customer.accountNumber}</span>
                      )}
                    </div>
                  )}
                  {!contactName && !contactPhone && !clean(customer.vatNo) && (
                    <p className="text-slate-400 italic">No contact details on file</p>
                  )}
                </div>
              )}

              <div>
                <div className="flex items-center justify-between">
                  <label className={lbl}><MapPin className="w-3.5 h-3.5" />Delivery address *</label>
                  {customer && (
                    <button
                      onClick={resetAddress}
                      title="Reset to the customer's address on file"
                      className="text-[11px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 mb-1.5"
                    >
                      <RotateCcw className="w-3 h-3" /> Reset
                    </button>
                  )}
                </div>
                <textarea
                  className={`${inp} resize-none`}
                  rows={5}
                  placeholder={customer ? 'No address on file — enter one' : 'Select a customer first'}
                  value={address}
                  onChange={e => setAddress(e.target.value)}
                />
                {customer && !address.trim() && (
                  <p className="text-[11px] text-amber-600 mt-1">
                    This customer has no address saved — type one for this delivery.
                  </p>
                )}
              </div>
            </div>
          </div>

          <div className="bg-white rounded-2xl shadow-sm ring-1 ring-blue-100 overflow-hidden shrink-0">
            <div className="px-4 py-2.5 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
              <FileText className="w-4 h-4 text-blue-600" />
              <p className="text-xs font-bold text-blue-900 uppercase tracking-wider">Document</p>
            </div>
            <div className="p-4 flex flex-col gap-4">
              <div>
                <label className={lbl}><Hash className="w-3.5 h-3.5" />Reference / PO No.</label>
                <input className={inp} placeholder="e.g. PO-2025-001" value={reference} onChange={e => setReference(e.target.value)} />
              </div>
              <div>
                <label className={lbl}><StickyNote className="w-3.5 h-3.5" />Notes</label>
                <textarea
                  className={`${inp} resize-none`}
                  rows={3}
                  placeholder="Special instructions…"
                  value={notes}
                  onChange={e => setNotes(e.target.value)}
                />
              </div>
            </div>
          </div>

          {/* Totals card */}
          <div className="rounded-2xl shadow-lg bg-blue-800 text-white p-5 flex flex-col gap-2.5 mt-auto shrink-0" style={{ background: '#1e40af' }}>
            <p className="text-[11px] font-bold text-blue-200 uppercase tracking-widest">Invoice total</p>
            <div className="flex justify-between items-center text-sm">
              <span className="text-blue-200">Excl. VAT</span>
              <span className="font-mono font-semibold">{fmt(totExcl)}</span>
            </div>
            <div className="flex justify-between items-center text-sm">
              <span className="text-blue-200">VAT 15%</span>
              <span className="font-mono font-semibold">{fmt(totVat)}</span>
            </div>
            <div className="border-t border-white/20 pt-3 mt-1 flex justify-between items-end">
              <span className="text-sm font-semibold text-blue-100">Total incl. VAT</span>
              <span className="font-mono text-2xl font-bold">{fmt(totIncl)}</span>
            </div>
          </div>
        </div>

        {/* ── Right — product search + line items ── */}
        <div className="flex-1 flex flex-col gap-4 min-h-0 min-w-0">

          {/* Product search bar */}
          <div className="bg-white rounded-2xl shadow-sm ring-1 ring-blue-100 p-3 shrink-0">
            <div className="relative">
              <input
                type="text"
                className="w-full bg-blue-50/50 border border-blue-100 rounded-xl px-4 py-3 text-base text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition"
                placeholder="Search chemical products — click one to add it…"
                value={productSearch}
                onChange={e => setProductSearch(e.target.value)}
                onKeyDown={e => {
                  // Enter adds the only remaining match
                  if (e.key === 'Enter' && filtered.length === 1) handleAddProduct(filtered[0]);
                  if (e.key === 'Escape') setProductSearch('');
                }}
              />
              {productSearch && (
                <div className="absolute z-20 w-full mt-2 bg-white ring-1 ring-blue-100 rounded-xl shadow-xl max-h-72 overflow-y-auto py-1">
                  {filtered.length === 0 ? (
                    <div className="px-4 py-3 text-sm text-slate-400">No products match “{productSearch}”</div>
                  ) : (
                    filtered.map(p => {
                      const onList = lines.some(l => l.productId === p.id);
                      return (
                        <div
                          key={p.id}
                          onMouseDown={() => handleAddProduct(p)}
                          className="px-4 py-2.5 hover:bg-blue-50 cursor-pointer text-sm flex justify-between items-center gap-3"
                        >
                          <span className="flex items-center gap-2 min-w-0">
                            <Package className="w-4 h-4 text-blue-400 shrink-0" />
                            <span className="font-medium text-slate-800 truncate">{p.name}</span>
                            {onList && (
                              <span className="bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0">
                                on list
                              </span>
                            )}
                          </span>
                          <span className="text-xs text-slate-500 font-mono shrink-0">
                            {containerName(p)} · <span className="text-blue-700 font-bold">{fmt(Number(p.price_incl_vat ?? 0))}</span>
                          </span>
                        </div>
                      );
                    })
                  )}
                </div>
              )}
            </div>
          </div>

          {/* Line items table */}
          <div className="flex-1 bg-white rounded-2xl shadow-sm ring-1 ring-blue-100 overflow-hidden flex flex-col min-h-0">
            <div className="px-4 py-2.5 border-b border-blue-100 flex items-center justify-between shrink-0">
              <p className="text-xs font-bold text-blue-900 uppercase tracking-wider">Items</p>
              {lines.length > 0 && (
                <p className="text-xs text-slate-500">
                  {lines.length} product{lines.length !== 1 ? 's' : ''} · {itemCount} unit{itemCount !== 1 ? 's' : ''}
                </p>
              )}
            </div>

            <div className="overflow-y-auto flex-1 min-h-0">
              <table className="w-full text-sm border-collapse">
                <thead className="sticky top-0 bg-blue-700 text-white z-10" style={{ background: '#1d4ed8' }}>
                  <tr>
                    <th className="px-3 py-3 text-left   text-[11px] font-bold uppercase tracking-wider w-10">#</th>
                    <th className="px-3 py-3 text-left   text-[11px] font-bold uppercase tracking-wider">Product</th>
                    <th className="px-3 py-3 text-left   text-[11px] font-bold uppercase tracking-wider">Pack</th>
                    <th className="px-3 py-3 text-center text-[11px] font-bold uppercase tracking-wider w-24">Qty</th>
                    <th className="px-3 py-3 text-right  text-[11px] font-bold uppercase tracking-wider w-32">Unit price (incl.)</th>
                    <th className="px-3 py-3 text-right  text-[11px] font-bold uppercase tracking-wider w-28">Excl.</th>
                    <th className="px-3 py-3 text-right  text-[11px] font-bold uppercase tracking-wider w-28">VAT</th>
                    <th className="px-3 py-3 text-right  text-[11px] font-bold uppercase tracking-wider w-32">Total</th>
                    <th className="px-3 py-3 w-12"></th>
                  </tr>
                </thead>
                <tbody>
                  {lines.length === 0 ? (
                    <tr>
                      <td colSpan={9} className="px-4 py-20 text-center">
                        <div className="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 flex items-center justify-center">
                          <FlaskConical className="w-8 h-8 text-blue-400" />
                        </div>
                        <p className="text-slate-700 font-semibold">No products added yet</p>
                        <p className="text-slate-400 text-sm mt-1">Search above and click a product to add it</p>
                      </td>
                    </tr>
                  ) : (
                    lines.map((line, idx) => (
                      <tr key={line.rowId} className={`border-b border-blue-50 hover:bg-blue-50/60 transition-colors ${idx % 2 === 0 ? 'bg-white' : 'bg-slate-50/60'}`}>
                        <td className="px-3 py-2.5">
                          <span className="w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold flex items-center justify-center">{idx + 1}</span>
                        </td>
                        <td className="px-3 py-2.5">
                          <p className="font-semibold text-slate-900">{line.productName}</p>
                          {line.sku && <p className="text-[11px] text-slate-400 font-mono">BATCH: {line.sku}</p>}
                        </td>
                        <td className="px-3 py-2.5">
                          <span className="bg-slate-100 text-slate-600 text-xs font-medium px-2 py-0.5 rounded-md">{line.containerName}</span>
                        </td>
                        <td className="px-3 py-2.5">
                          <input
                            type="number"
                            min="0"
                            step="1"
                            className="w-full border border-blue-100 rounded-lg px-2 py-1.5 text-center text-sm font-semibold focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            value={line.quantity}
                            onChange={e => handleQtyChange(line.rowId, e.target.value)}
                          />
                        </td>
                        <td className="px-3 py-2.5">
                          <input
                            type="number"
                            min="0"
                            step="0.01"
                            className="w-full border border-blue-100 rounded-lg px-2 py-1.5 text-right text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            value={line.unitPrice}
                            onChange={e => handlePriceChange(line.rowId, e.target.value)}
                          />
                        </td>
                        <td className="px-3 py-2.5 text-right text-slate-600 font-mono text-xs">{fmt(line.subtotal)}</td>
                        <td className="px-3 py-2.5 text-right text-slate-500 font-mono text-xs">{fmt(line.vatAmount)}</td>
                        <td className="px-3 py-2.5 text-right font-bold text-blue-800 font-mono">{fmt(line.total)}</td>
                        <td className="px-3 py-2.5 text-center">
                          <button
                            onClick={() => handleRemove(line.rowId)}
                            title="Remove line"
                            className="text-slate-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-colors"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            {/* Footer totals row */}
            {lines.length > 0 && (
              <div className="border-t border-blue-100 bg-blue-50 px-6 py-3.5 flex items-center justify-end gap-8 shrink-0">
                <div className="text-sm text-slate-500">
                  Excl. VAT <span className="font-mono font-bold text-slate-800 ml-2">{fmt(totExcl)}</span>
                </div>
                <div className="text-sm text-slate-500">
                  VAT <span className="font-mono font-bold text-slate-800 ml-2">{fmt(totVat)}</span>
                </div>
                <div className="bg-blue-700 text-white rounded-lg px-4 py-2 text-sm font-semibold" style={{ background: '#1d4ed8' }}>
                  Total <span className="font-mono text-lg font-bold ml-2">{fmt(totIncl)}</span>
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default ChemicalDeliveryForm;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ChemicalDeliveryForm />);
}