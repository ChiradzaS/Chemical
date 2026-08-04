import React, { useState, useEffect } from 'react';
import { FlaskConical, User, MapPin, Package, Minus, Save, ChevronDown, RotateCcw } from 'lucide-react';
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
  price:               number | null;
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

// item.vatApplicable is the product's own setting; vatOn is the document-level
// switch. VAT is charged only when both agree.
const calcLine = (item: LineItem, vatOn: boolean): LineItem => {
  const qty      = parseFloat(item.quantity) || 0;
  const subtotal = qty * item.unitPrice;
  const charges  = vatOn && item.vatApplicable;
  const vatAmt   = charges ? subtotal * (item.vatRate / 100) : 0;
  return { ...item, subtotal, vatAmount: vatAmt, total: subtotal + vatAmt };
};

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
  const [vatEnabled,    setVatEnabled]    = useState(false);
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

  // flipping the switch re-prices every line already on the note
  useEffect(() => {
    setLines(prev => prev.map(l => calcLine(l, vatEnabled)));
  }, [vatEnabled]);

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
      unitPrice:     p.price ?? 0,
      vatApplicable: !!p.vat_applicable,
      vatRate:       p.vat_rate ?? VAT_DEFAULT,
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
          unitPrice:     l.unitPrice,
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
  const inp = "w-full bg-white text-gray-800 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500";
  const sel = `${inp} appearance-none`;

  return (
    <div className="h-screen overflow-hidden flex flex-col bg-gray-100">

      {/* ── Toast ── */}
      {toast && (
        <div className={`fixed top-4 left-1/2 -translate-x-1/2 z-50 px-5 py-2.5 rounded-xl text-sm font-semibold shadow-xl flex items-center gap-2 ${
          toast.ok ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'
        }`}>
          {toast.ok ? '✓' : '✕'} {toast.msg}
        </div>
      )}

      {/* ── Top bar ── */}
      <div className="h-12 bg-gray-900 flex items-center px-5 gap-3 shrink-0">
        <FlaskConical className="w-4 h-4 text-blue-400" />
        <span className="text-white font-bold text-sm">Chemical Delivery / Invoice</span>
        <span className="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">CHEM</span>

        {/* VAT switch — off means nothing on this document carries VAT */}
        <button
          onClick={() => setVatEnabled(v => !v)}
          title={vatEnabled ? 'VAT is being charged — click to turn off' : 'No VAT on this document — click to turn on'}
          className={`flex items-center gap-2 px-3 py-1 rounded-lg text-xs font-bold transition-colors ${
            vatEnabled
              ? 'bg-green-600/20 text-green-300 border border-green-600/40'
              : 'bg-gray-800 text-gray-400 border border-gray-700'
          }`}
        >
          <span className={`w-8 h-4 rounded-full relative transition-colors ${vatEnabled ? 'bg-green-500' : 'bg-gray-600'}`}>
            <span className={`absolute top-0.5 w-3 h-3 bg-white rounded-full transition-all ${vatEnabled ? 'left-4' : 'left-0.5'}`} />
          </span>
          {vatEnabled ? 'VAT 15%' : 'No VAT'}
        </button>

        {/* Doc type toggle */}
        <div className="ml-4 flex items-center gap-1 bg-gray-800 rounded-lg p-1">
          {(['both', 'invoice'] as const).map(t => (
            <button
              key={t}
              onClick={() => setDocType(t)}
              className={`px-3 py-1 rounded-md text-xs font-bold transition-all ${
                docType === t
                  ? 'bg-blue-600 text-white shadow'
                  : 'text-gray-400 hover:text-white'
              }`}
            >
              {t === 'both' ? '📄 Invoice + Delivery Note' : '🧾 Invoice Only'}
            </button>
          ))}
        </div>

        <div className="ml-auto flex gap-2 shrink-0">
          <button
            onClick={() => { window.location.href = LIST_URL; }}
            className="text-gray-400 hover:text-white text-xs px-3 py-1.5 border border-gray-700 rounded-lg transition-colors"
          >
            Back to list
          </button>
          <button
            onClick={handleSave}
            disabled={saving}
            className="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors"
          >
            <Save className="w-3.5 h-3.5" />
            {saving ? 'Saving…' : docType === 'both' ? 'Generate Invoice + Delivery Note' : 'Generate Invoice'}
          </button>
        </div>
      </div>

      {/* ── Body ── */}
      <div className="flex-1 overflow-hidden flex gap-3 p-3 min-h-0">

        {/* ── Left — header fields ── */}
        <div className="w-72 shrink-0 flex flex-col gap-3 overflow-y-auto">

          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-col gap-3">
            <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Document</p>

            <div>
              <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                <User className="inline w-3 h-3 mr-1" />Customer *
              </p>
              <div className="relative">
                <select className={`${sel} pr-7`} value={customerId} onChange={e => setCustomerId(e.target.value)}>
                  <option value="">-- Select --</option>
                  {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
                <ChevronDown className="absolute right-2 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" />
              </div>
            </div>

            {/* Details pulled from the customer record */}
            {customer && (
              <div className="bg-gray-50 border border-gray-100 rounded-lg px-3 py-2.5 text-xs text-gray-600 flex flex-col gap-1">
                {clean(customer.legalName) && clean(customer.legalName) !== clean(customer.name) && (
                  <p className="font-semibold text-gray-800">{customer.legalName}</p>
                )}
                {contactName  && <p><span className="text-gray-400">Attn</span> {contactName}</p>}
                {contactPhone && <p><span className="text-gray-400">Tel</span> {contactPhone}</p>}
                {clean(customer.emailAddress)  && <p className="truncate">{customer.emailAddress}</p>}
                {clean(customer.vatNo)         && <p><span className="text-gray-400">VAT</span> {customer.vatNo}</p>}
                {clean(customer.accountNumber) && <p><span className="text-gray-400">Acc</span> {customer.accountNumber}</p>}
                {!contactName && !contactPhone && !clean(customer.vatNo) && (
                  <p className="text-gray-400 italic">No contact details on file</p>
                )}
              </div>
            )}

            <div>
              <div className="flex items-center justify-between mb-1">
                <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">
                  <MapPin className="inline w-3 h-3 mr-1" />Delivery Address *
                </p>
                {customer && (
                  <button
                    onClick={resetAddress}
                    title="Reset to the customer's address on file"
                    className="text-[10px] text-blue-600 hover:text-blue-800 flex items-center gap-1"
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
                <p className="text-[10px] text-amber-600 mt-1">
                  This customer has no address saved — type one for this delivery.
                </p>
              )}
            </div>

            <div>
              <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Reference / PO No.</p>
              <input className={inp} placeholder="e.g. PO-2025-001" value={reference} onChange={e => setReference(e.target.value)} />
            </div>

            <div>
              <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Notes</p>
              <textarea
                className={`${inp} resize-none`}
                rows={3}
                placeholder="Special instructions…"
                value={notes}
                onChange={e => setNotes(e.target.value)}
              />
            </div>
          </div>

          {/* Totals card */}
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex flex-col gap-3 mt-auto shrink-0">
            <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Totals</p>
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-500">Excl. VAT</span>
              <span className="font-mono text-base font-semibold text-gray-800">{fmt(totExcl)}</span>
            </div>
            <div className="flex justify-between items-center">
              <span className="text-sm text-gray-500">VAT</span>
              <span className="font-mono text-base font-semibold text-gray-800">{fmt(totVat)}</span>
            </div>
            <div className="flex justify-between items-center border-t border-gray-200 pt-3 mt-1">
              <span className="text-base font-bold text-gray-700">Total incl. VAT</span>
              <span className="font-mono text-xl font-bold text-blue-700">{fmt(totIncl)}</span>
            </div>
          </div>
        </div>

        {/* ── Right — product search + line items ── */}
        <div className="flex-1 flex flex-col gap-3 min-h-0 min-w-0">

          {/* Product search bar */}
          <div className="bg-white rounded-xl border border-gray-200 shadow-sm p-3 flex items-center gap-3 shrink-0">
            <Package className="w-4 h-4 text-blue-600 shrink-0" />
            <div className="flex-1 relative">
              <input
                type="text"
                className={inp}
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
                <div className="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-56 overflow-y-auto">
                  {filtered.length === 0 ? (
                    <div className="px-4 py-3 text-sm text-gray-400">No products match “{productSearch}”</div>
                  ) : (
                    filtered.map(p => {
                      const onList = lines.some(l => l.productId === p.id);
                      return (
                        <div
                          key={p.id}
                          onMouseDown={() => handleAddProduct(p)}
                          className="px-4 py-2 hover:bg-blue-50 cursor-pointer text-sm flex justify-between items-center"
                        >
                          <span className="flex items-center gap-2">
                            {p.name}
                            {onList && (
                              <span className="bg-blue-100 text-blue-700 text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                                on list
                              </span>
                            )}
                          </span>
                          <span className="text-xs text-gray-400 font-mono">{containerName(p)} · {fmt(p.price ?? 0)}</span>
                        </div>
                      );
                    })
                  )}
                </div>
              )}
            </div>
          </div>

          {/* Line items table */}
          <div className="flex-1 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col min-h-0">
            <div className="overflow-y-auto flex-1 min-h-0">
              <table className="w-full text-sm border-collapse">
                <thead className="sticky top-0 bg-gray-900 text-white z-10">
                  <tr>
                    <th className="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider">#</th>
                    <th className="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider">Product</th>
                    <th className="px-3 py-3 text-left text-[11px] font-bold uppercase tracking-wider">Pack</th>
                    <th className="px-3 py-3 text-center text-[11px] font-bold uppercase tracking-wider w-20">Qty</th>
                    <th className="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider w-28">Unit Price</th>
                    <th className="px-3 py-3 text-center text-[11px] font-bold uppercase tracking-wider w-16">VAT</th>
                    <th className="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider w-24">Excl.</th>
                    <th className="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider w-24">VAT Amt</th>
                    <th className="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider w-28">Total</th>
                    <th className="px-3 py-3 text-center text-[11px] font-bold uppercase tracking-wider w-12"></th>
                  </tr>
                </thead>
                <tbody>
                  {lines.length === 0 ? (
                    <tr>
                      <td colSpan={10} className="px-4 py-16 text-center text-gray-400 text-sm">
                        <FlaskConical className="w-10 h-10 mx-auto mb-3 text-gray-300" />
                        No products added yet — search above and click Add
                      </td>
                    </tr>
                  ) : (
                    lines.map((line, idx) => (
                      <tr key={line.rowId} className={`border-b border-gray-100 ${idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'}`}>
                        <td className="px-3 py-2 text-gray-400 text-xs">{idx + 1}</td>
                        <td className="px-3 py-2">
                          <p className="font-semibold text-gray-900">{line.productName}</p>
                          {line.sku && <p className="text-[11px] text-gray-400">{line.sku}</p>}
                        </td>
                        <td className="px-3 py-2 text-gray-600 text-xs">{line.containerName}</td>
                        <td className="px-3 py-2">
                          <input
                            type="number"
                            min="0"
                            step="1"
                            className="w-full border border-gray-200 rounded px-2 py-1 text-center text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value={line.quantity}
                            onChange={e => handleQtyChange(line.rowId, e.target.value)}
                          />
                        </td>
                        <td className="px-3 py-2">
                          <input
                            type="number"
                            min="0"
                            step="0.01"
                            className="w-full border border-gray-200 rounded px-2 py-1 text-right text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            value={line.unitPrice}
                            onChange={e => handlePriceChange(line.rowId, e.target.value)}
                          />
                        </td>
                        <td className="px-3 py-2 text-center">
                          {vatEnabled && line.vatApplicable ? (
                            <span className="bg-green-100 text-green-700 text-[10px] font-bold px-2 py-0.5 rounded-full">{line.vatRate}%</span>
                          ) : (
                            <span className="bg-gray-100 text-gray-400 text-[10px] font-bold px-2 py-0.5 rounded-full">
                              {vatEnabled ? 'No VAT' : 'Off'}
                            </span>
                          )}
                        </td>
                        <td className="px-3 py-2 text-right text-gray-700 font-mono text-xs">{fmt(line.subtotal)}</td>
                        <td className="px-3 py-2 text-right text-gray-500 font-mono text-xs">{fmt(line.vatAmount)}</td>
                        <td className="px-3 py-2 text-right font-bold text-gray-900 font-mono text-sm">{fmt(line.total)}</td>
                        <td className="px-3 py-2 text-center">
                          <button
                            onClick={() => handleRemove(line.rowId)}
                            className="text-red-400 hover:text-red-600 hover:bg-red-50 p-1 rounded transition-colors"
                          >
                            <Minus className="w-4 h-4" />
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
              <div className="border-t border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-end gap-10 shrink-0">
                <div className="text-sm text-gray-500">
                  Excl. VAT <span className="font-mono font-bold text-gray-800 ml-2 text-base">{fmt(totExcl)}</span>
                </div>
                <div className="text-sm text-gray-500">
                  VAT <span className="font-mono font-bold text-gray-800 ml-2 text-base">{fmt(totVat)}</span>
                </div>
                <div className="text-base font-bold text-gray-700">
                  Total incl. VAT <span className="font-mono text-blue-700 ml-2 text-xl">{fmt(totIncl)}</span>
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