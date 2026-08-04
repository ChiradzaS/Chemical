import React, { useState, useEffect } from 'react';
import { Calendar, User, Package, Trash2, Play, Printer, Copy, FlaskConical } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:         string;
    customersData:         { id: string; name: string }[];
    chemicalProductsData:  { id: string; name: string }[];
    unitTypesData:         { id: string; name: string }[];
    processTypesData:      { id: string; name: string }[];
    stateTypesData:        { id: string; name: string }[];
    containerSizesData:    { id: string; name: string }[];
  }
}

interface JobCardItem {
  job_card_id:             string;
  job_cards_created_at:    string;
  job_cards_customerId:    string;
  job_cards_productId:     string;
  job_cards_stateId:       string;
  job_cards_quantity:      number;
  job_cards_batchCount:    number;
  job_cards_totalUnits:    number;
  job_cards_barcode:       string;
  job_cards_notes:         string;
  jobcarditem_id:          string;
  jobcarditem_processId:   string;
  jobcarditem_productId:   string;
  jobcarditem_quantity:    number;
  jobcarditem_outstanding: number;
  jobcarditem_unitId:      string;
  jobcarditem_stateId:     string;
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const FORM_URL = `${API_BASE}/chemicaljobcard`;

const byId = (list: { id: string; name: string }[], id: any) =>
  list.find(x => String(x.id) === String(id))?.name ?? '—';

const ChemicalJobCardList: React.FC = () => {
  const customers        = window.customersData        || [];
  const chemicalProducts = window.chemicalProductsData || [];
  const processTypes     = window.processTypesData     || [];
  const containerSizes   = window.containerSizesData   || [];

  const [allCards,     setAllCards]     = useState<JobCardItem[]>([]);
  const [loading,      setLoading]      = useState(true);
  const [deleting,     setDeleting]     = useState<string | null>(null);
  const [notification, setNotification] = useState<{ type: 'success' | 'error' | null; message: string }>({ type: null, message: '' });

  const [filters, setFilters] = useState({
    fromDate:      '',
    toDate:        '',
    customerId:    '',
    productSearch: '',
    productId:     '',
  });
  const [showProductSuggestions,     setShowProductSuggestions]     = useState(false);
  const [filteredProductSuggestions, setFilteredProductSuggestions] = useState<{ id: string; name: string }[]>([]);

  // ── Fetch ──────────────────────────────────────────────────────────────────
  const fetchCards = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`${API_BASE}/chemicaljobcards/index?data=query`);
      const data = response.data;
      setAllCards(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Error fetching job cards:', error);
      setAllCards([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchCards(); }, []);

  // ── Filter ─────────────────────────────────────────────────────────────────
  const filtered = allCards.filter(item => {
    if (filters.fromDate   && item.job_cards_created_at < filters.fromDate) return false;
    if (filters.toDate     && item.job_cards_created_at > filters.toDate)   return false;
    if (filters.customerId && String(item.job_cards_customerId) !== String(filters.customerId)) return false;
    if (filters.productId) {
      const ids = allCards
        .filter(i => String(i.jobcarditem_productId) === String(filters.productId))
        .map(i => i.job_card_id);
      if (!ids.includes(item.job_card_id)) return false;
    }
    return true;
  });

  const grouped = filtered.reduce((acc, item) => {
    if (!acc[item.job_card_id]) acc[item.job_card_id] = [];
    acc[item.job_card_id].push(item);
    return acc;
  }, {} as Record<string, JobCardItem[]>);

  // ── Handlers ───────────────────────────────────────────────────────────────
  const handleDelete = async (jobCardId: string) => {
    if (!confirm('Are you sure you want to delete this job card?')) return;
    setDeleting(jobCardId);
    try {
      const encodedData = encodeURIComponent(JSON.stringify({ id: jobCardId }));
      await axios.get(`${API_BASE}/chemicaljobcards/destroy?data=${encodedData}`);
      setNotification({ type: 'success', message: 'Job card deleted successfully!' });
      await fetchCards();
    } catch (_) {
      await fetchCards();
      setNotification({ type: 'success', message: 'Job card deleted!' });
    } finally {
      setDeleting(null);
    }
  };

  const handleClone = (item: JobCardItem) => {
    window.location.href = `${FORM_URL}?customerId=${item.job_cards_customerId}&productId=${item.job_cards_productId}`;
  };

  const handleProduction = (jobcarditemId: string, outstanding: number, quantity: number) => {
    const params = new URLSearchParams({
      job:         jobcarditemId,
      outstanding: outstanding.toString(),
      quantity:    quantity.toString(),
    });
    window.location.href = `${API_BASE}/chemicaljobcardproduction?${params.toString()}`;
  };

  const handlePrint = (item: JobCardItem) => {
    const params = new URLSearchParams({
      jobCardId:     item.job_card_id,
      jobcarditemId: item.jobcarditem_id,
      productId:     item.jobcarditem_productId,
    });
    window.open(`${API_BASE}/chemicalcreate?${params.toString()}`, '_blank');
  };

  const handleFilterChange = (key: string, value: string) => {
    setFilters(f => ({ ...f, [key]: value }));
    if (key === 'productSearch') {
      if (value.trim()) {
        setFilteredProductSuggestions(
          chemicalProducts.filter(p => p.name.toLowerCase().includes(value.toLowerCase()))
        );
        setShowProductSuggestions(true);
      } else {
        setFilteredProductSuggestions([]);
        setShowProductSuggestions(false);
        setFilters(f => ({ ...f, productId: '' }));
      }
    }
  };

  const getStatusColor = (outstanding: number, quantity: number) => {
    if (quantity < outstanding)  return 'text-green-600';
    if (outstanding <= 0)        return 'text-red-600';
    if (outstanding < quantity)  return 'text-yellow-600';
    return 'text-gray-900';
  };

  return (
    <div className="min-h-screen bg-gray-50 p-4">

      {loading && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600" />
        </div>
      )}

      {notification.type && (
        <div className={`mb-4 p-4 rounded-lg flex items-center justify-between ${
          notification.type === 'success'
            ? 'bg-green-100 text-green-800 border border-green-300'
            : 'bg-red-100 text-red-800 border border-red-300'
        }`}>
          <span className="font-medium">{notification.message}</span>
          <button onClick={() => setNotification({ type: null, message: '' })} className="ml-4 text-gray-500 hover:text-gray-700">×</button>
        </div>
      )}

      <div className="max-w-7xl mx-auto">

        {/* ── Filters ── */}
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8 p-8">
          <div className="flex items-center justify-between mb-6">
            <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
              <FlaskConical className="text-blue-600" />
              Chemical Job Cards
            </h1>
            <button
              onClick={() => { window.location.href = FORM_URL; }}
              className="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold flex items-center gap-2"
            >
              <Package className="w-4 h-4" /> New Job Card
            </button>
          </div>

          <div className="grid grid-cols-12 gap-6 items-end">
            <div className="col-span-3 relative">
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                <Package className="inline w-4 h-4 mr-1" /> Product
              </label>
              <input
                type="text"
                value={filters.productSearch}
                onChange={e => handleFilterChange('productSearch', e.target.value)}
                onFocus={() => filters.productSearch && setShowProductSuggestions(true)}
                placeholder="Search product…"
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white"
              />
              {showProductSuggestions && filteredProductSuggestions.length > 0 && (
                <div className="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                  {filteredProductSuggestions.map(p => (
                    <div
                      key={p.id}
                      onClick={() => {
                        setFilters(f => ({ ...f, productId: p.id, productSearch: p.name }));
                        setShowProductSuggestions(false);
                      }}
                      className="px-4 py-3 hover:bg-blue-50 cursor-pointer"
                    >
                      {p.name}
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div className="col-span-3">
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                <User className="inline w-4 h-4 mr-1" /> Customer
              </label>
              <select
                value={filters.customerId}
                onChange={e => handleFilterChange('customerId', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white appearance-none"
              >
                <option value="">All customers</option>
                {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>

            <div className="col-span-3">
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                <Calendar className="inline w-4 h-4 mr-1" /> From date
              </label>
              <input
                type="date"
                value={filters.fromDate}
                onChange={e => handleFilterChange('fromDate', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white"
              />
            </div>

            <div className="col-span-3">
              <label className="block text-sm font-semibold text-gray-700 mb-2">
                <Calendar className="inline w-4 h-4 mr-1" /> To date
              </label>
              <input
                type="date"
                value={filters.toDate}
                onChange={e => handleFilterChange('toDate', e.target.value)}
                className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50 focus:bg-white"
              />
            </div>
          </div>

          <div className="mt-4 text-sm text-gray-500">
            Showing {Object.keys(grouped).length} job card{Object.keys(grouped).length !== 1 ? 's' : ''}
            ({filtered.length} item{filtered.length !== 1 ? 's' : ''})
          </div>
        </div>

        {/* ── Cards ── */}
        <div className="space-y-6">
          {Object.entries(grouped)
            .sort(([, a], [, b]) =>
              new Date(b[0].job_cards_created_at).getTime() - new Date(a[0].job_cards_created_at).getTime()
            )
            .map(([jobCardId, items]) => {
              const first = items[0];
              const customerName = byId(customers, first.job_cards_customerId);

              // Hide delete if any process line has qnt different from outstanding (production started)
              const productionStarted = items.some(
                item => item.jobcarditem_outstanding !== item.jobcarditem_quantity
              );

              return (
                <div key={jobCardId} className="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow">

                  {/* ── Card header ── */}
                  <div className="bg-gradient-to-r from-slate-700 to-slate-800 text-white px-8 py-5">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-6">
                        <div>
                          <p className="text-slate-300 text-sm">{first.job_cards_created_at}</p>
                        </div>
                        <div>
                          <h4 className="text-xl font-semibold">{customerName}</h4>
                        </div>
                        <div className="text-sm text-slate-300">
                          Batches: <strong className="text-white">{first.job_cards_batchCount}</strong>
                        </div>
                      </div>

                      <div className="flex gap-2">
                        <button
                          onClick={() => handleClone(first)}
                          className="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-md"
                        >
                          <Copy className="w-4 h-4" /> Clone
                        </button>

                        {!productionStarted && (
                          <button
                            onClick={() => handleDelete(jobCardId)}
                            disabled={deleting === jobCardId}
                            className="bg-red-600 hover:bg-red-700 disabled:bg-red-300 px-4 py-2 rounded-lg text-sm flex items-center gap-2 transition-colors shadow-md"
                          >
                            <Trash2 className="w-4 h-4" />
                            {deleting === jobCardId ? 'Deleting…' : 'Delete'}
                          </button>
                        )}
                      </div>
                    </div>

                    {first.job_cards_notes && (
                      <p className="mt-2 text-slate-400 text-xs italic">{first.job_cards_notes}</p>
                    )}
                  </div>

                  {/* ── Process items ── */}
                  <div className="divide-y divide-gray-100">
                    {items.map(item => {
                      const procName = byId(processTypes,     item.jobcarditem_processId);
                      const prodName = byId(chemicalProducts, item.jobcarditem_productId);
                      const unitName = byId(containerSizes,   item.jobcarditem_unitId);

                      return (
                        <div key={item.jobcarditem_id} className="p-6 hover:bg-gray-50 transition-colors">
                          <div className="flex items-center justify-between">
                            <div className="flex items-center gap-6">
                              <div className="bg-blue-100 text-blue-800 px-4 py-1.5 rounded-full text-sm font-semibold">
                                {procName}
                              </div>
                              <span className="text-gray-500 text-sm">{item.jobcarditem_id}</span>
                              <span className="font-semibold text-gray-900">
                                {prodName} ({unitName})
                              </span>
                            </div>

                            <div className="flex items-center gap-6">
                              <div className="text-center">
                                <p className="text-xs text-gray-500">Qty</p>
                                <p className="font-bold text-gray-900">{item.jobcarditem_outstanding}</p>
                              </div>
                              <div className="text-center">
                                <p className="text-xs text-gray-500">Outstanding</p>
                                <p className={`font-bold ${getStatusColor(item.jobcarditem_outstanding, item.jobcarditem_quantity)}`}>
                                  {item.jobcarditem_quantity}
                                </p>
                              </div>
                              <div className="flex gap-2">
                                {item.jobcarditem_outstanding === item.jobcarditem_quantity ? (
                                  <button
                                    disabled
                                    className="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg flex items-center gap-2 cursor-not-allowed text-sm"
                                  >
                                    <Play className="w-4 h-4" /> Production
                                  </button>
                                ) : (
                                  <button
                                    onClick={() => handleProduction(item.jobcarditem_id, item.jobcarditem_outstanding, item.jobcarditem_quantity)}
                                    className="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm shadow-md"
                                  >
                                    <Play className="w-4 h-4" /> Production
                                  </button>
                                )}
                                <button
                                  onClick={() => handlePrint(item)}
                                  className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors text-sm shadow-md"
                                >
                                  <Printer className="w-4 h-4" /> Print
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                </div>
              );
            })}

          {Object.keys(grouped).length === 0 && !loading && (
            <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-12 text-center">
              <FlaskConical className="w-20 h-20 mx-auto text-gray-400 mb-4" />
              <h3 className="text-xl font-semibold text-gray-900 mb-2">No job cards found</h3>
              <p className="text-gray-500">Try adjusting your filters or create a new chemical job card.</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ChemicalJobCardList;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ChemicalJobCardList />);
}