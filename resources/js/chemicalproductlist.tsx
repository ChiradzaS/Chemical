import React, { useState, useEffect } from 'react';
import { Package, Search, Edit, Plus } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl:         string;
    unitTypesData:         { id: string; name: string }[];
    colourTypesData:       { id: string; name: string }[];
    viscosityData:         { id: string; name: string }[];
    activeIngredientsData: { id: string; name: string }[];
    fragranceData:         { id: string; name: string }[];
    bagTypesData:          { id: string; name: string }[];
    containerSizesData:    { id: string; name: string }[];
    materialTypesData:     { id: string; name: string }[];
    capTypesData:          { id: string; name: string }[];
    lableTypesData:        { id: string; name: string }[];
  }
}

const API_BASE = window.laravelApiUrl || 'http://localhost/Chemical';
const FORM_URL = `${API_BASE}/productchemicals`;

// ── Lookup helper ─────────────────────────────────────────────────────────────
const byId = (list: { id: string; name: string }[], id: any) =>
  list.find(x => String(x.id) === String(id))?.name ?? '—';

// ── Colour name → swatch hex ────────────────────────────────────────────────
// Keyed by lowercase colour name (matches the `colour` settings group added
// earlier: clear, white, red, blue, green, yellow, orange, pink, purple,
// black). Anything not in this map renders no swatch at all.
const COLOUR_SWATCHES: Record<string, string> = {
  clear:  '#f3f4f6',
  white:  '#ffffff',
  red:    '#ef4444',
  blue:   '#3b82f6',
  green:  '#22c55e',
  yellow: '#eab308',
  orange: '#f97316',
  pink:   '#ec4899',
  purple: '#a855f7',
  black:  '#111827',
};

const getColourSwatch = (name: string): string | null =>
  COLOUR_SWATCHES[name.trim().toLowerCase()] ?? null;

interface Product {
  id:                    number;
  name:                  string;
  sku:                   string | null;
  category:              string | null;
  brand:                 string | null;
  barcode:               string | null;
  stock_on_hand:         number;
  stock_unit_id:         string | null;
  colour_id:             string | null;
  viscosity_id:          string | null;
  active_ingredient_id:  string | null;
  fragrance_id:          string | null;
  bag_type_id:           string | null;
  container_size_id:     string | null;
  weight_per_unit_grams: number | null;
  price:                 number | null;
  vat_applicable:        number;
  is_active:             number;
  created_at:            string;
}

function ChemicalProductList() {
  const unitTypes      = window.unitTypesData      || [];
  const colourTypes    = window.colourTypesData    || [];
  const viscosities    = window.viscosityData      || [];
  const fragrances     = window.fragranceData      || [];
  const bagTypes       = window.bagTypesData       || [];
  const containerSizes = window.containerSizesData || [];

  const [products,       setProducts]       = useState<Product[]>([]);
  const [loading,        setLoading]        = useState(true);
  const [searchName,     setSearchName]     = useState('');
  const [searchCategory, setSearchCategory] = useState('');
  const [searchBrand,    setSearchBrand]    = useState('');
  const [dateFrom,       setDateFrom]       = useState('');
  const [dateTo,         setDateTo]         = useState('');

  // ── Fetch ───────────────────────────────────────────────────────────────
  const fetchProducts = async () => {
    setLoading(true);
    try {
      const response = await axios.get(`${API_BASE}/chemicalproducts/index`);
      const data     = response.data;
      setProducts(Array.isArray(data) ? data : []);
    } catch (error) {
      console.error('Error fetching products:', error);
      setProducts([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchProducts(); }, []);

  // ── Edit → form page with id ─────────────────────────────────────────────
  const handleEdit = (id: number) => {
    window.location.href = `${FORM_URL}?id=${id}`;
  };

  // ── New → form page no id ────────────────────────────────────────────────
  const handleNew = () => {
    window.location.href = FORM_URL;
  };

  // ── Filter ──────────────────────────────────────────────────────────────
  const filtered = products.filter(p => {
    const matchName     = !searchName.trim()     || p.name.toLowerCase().includes(searchName.toLowerCase());
    const matchCategory = !searchCategory.trim() || (p.category ?? '').toLowerCase().includes(searchCategory.toLowerCase());
    const matchBrand    = !searchBrand.trim()    || (p.brand ?? '').toLowerCase().includes(searchBrand.toLowerCase());
    const matchFrom     = !dateFrom || new Date(p.created_at) >= new Date(dateFrom);
    const matchTo       = !dateTo   || new Date(p.created_at) <= new Date(dateTo);
    return matchName && matchCategory && matchBrand && matchFrom && matchTo;
  }).sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-6xl mx-auto">

        {/* ── Header ── */}
        <div className="mb-8 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-1">Chemical Products</h1>
            <p className="text-gray-600">Manage your chemical product catalogue</p>
          </div>
          <button
            onClick={handleNew}
            className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition-colors shadow"
          >
            <Plus size={18} />
            New Product
          </button>
        </div>

        {/* ── Filters ── */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="flex items-center gap-2 mb-4">
            <Search size={20} className="text-gray-500" />
            <h2 className="text-lg font-semibold text-gray-900">Search Filters</h2>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Product name</label>
              <input
                type="text"
                value={searchName}
                onChange={e => setSearchName(e.target.value)}
                placeholder="Search name…"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
              <input
                type="text"
                value={searchCategory}
                onChange={e => setSearchCategory(e.target.value)}
                placeholder="Search category…"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Brand</label>
              <input
                type="text"
                value={searchBrand}
                onChange={e => setSearchBrand(e.target.value)}
                placeholder="Search brand…"
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Date from</label>
              <input
                type="date"
                value={dateFrom}
                onChange={e => setDateFrom(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Date to</label>
              <input
                type="date"
                value={dateTo}
                onChange={e => setDateTo(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none"
              />
            </div>
          </div>
          <div className="mt-3 text-sm text-gray-500">
            Showing {filtered.length} product{filtered.length !== 1 ? 's' : ''}
          </div>
        </div>

        {/* ── List ── */}
        {loading ? (
          <div className="text-center py-16 text-gray-400">Loading products…</div>
        ) : (
          <div className="space-y-4">
            {filtered.map(product => {
              const colourName   = byId(colourTypes, product.colour_id);
              const colourSwatch = getColourSwatch(colourName);

              return (
              <div
                key={product.id}
                className="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow"
              >
                {/* Card header */}
                <div className="bg-gradient-to-r from-slate-700 to-slate-800 text-white p-5">
                  <div className="flex justify-between items-start">
                    <div className="flex-1">
                      <div className="flex items-center gap-3 mb-2">
                        <div className="bg-slate-600 px-3 py-1 rounded-full text-sm font-semibold">
                          #{product.id}
                        </div>
                        {product.category && (
                          <span className="bg-blue-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                            {product.category}
                          </span>
                        )}
                        {product.brand && (
                          <span className="bg-slate-500 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">
                            {product.brand}
                          </span>
                        )}
                        <h2 className="text-xl font-semibold">{product.name}</h2>
                      </div>
                      <div className="flex items-center gap-6 text-sm text-slate-300">
                        {product.sku     && <span>BATCH: <strong className="text-white">{product.sku}</strong></span>}
                        {product.barcode && <span>Barcode: <strong className="text-white">{product.barcode}</strong></span>}
                        <span>Added: {new Date(product.created_at).toLocaleDateString()}</span>
                        <span className={`font-bold ${product.is_active ? 'text-green-400' : 'text-red-400'}`}>
                          {product.is_active ? '● Active' : '● Inactive'}
                        </span>
                      </div>
                    </div>

                    <div className="flex items-center gap-2 ml-4 shrink-0">
                      <button
                        onClick={() => handleEdit(product.id)}
                        className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-md"
                      >
                        <Edit size={16} /> Edit
                      </button>
                    </div>
                  </div>
                </div>

                {/* Card body */}
                <div className="p-5 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                  {/* no border — stroke removed */}
                  <div className="bg-gray-50 rounded-lg p-3">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Stock on hand</p>
                    <p className="text-lg font-bold text-gray-800">
                      {product.stock_on_hand ?? 0}
                      <span className="text-sm font-normal text-gray-500 ml-1">
                        {byId(unitTypes, product.stock_unit_id)}
                      </span>
                    </p>
                  </div>

                  <div className="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Colour</p>
                    <p className="text-sm font-semibold text-gray-800 flex items-center gap-2">
                      {colourSwatch && (
                        <span
                          className="inline-block w-3.5 h-3.5 rounded-full border border-gray-300 shrink-0"
                          style={{ backgroundColor: colourSwatch }}
                        />
                      )}
                      {colourName}
                    </p>
                  </div>

                  <div className="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Viscosity</p>
                    <p className="text-sm font-semibold text-gray-800">{byId(viscosities, product.viscosity_id)}</p>
                  </div>

                  {/* price moved into the old active-ingredient slot */}
                  <div className="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Selling price</p>
                    <p className="text-lg font-bold text-blue-700">
                      {product.price != null ? `R ${Number(product.price).toFixed(2)}` : '—'}
                      {!!product.vat_applicable && (
                        <span className="text-xs font-normal text-gray-400 ml-1">excl. VAT</span>
                      )}
                    </p>
                  </div>

                  <div className="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Fragrance</p>
                    <p className="text-sm font-semibold text-gray-800">{byId(fragrances, product.fragrance_id)}</p>
                  </div>

                  {/* no border — stroke removed */}
                  <div className="bg-gray-50 rounded-lg p-3">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Container</p>
                    <p className="text-sm font-semibold text-gray-800">
                      {byId(bagTypes, product.bag_type_id)}  {byId(containerSizes, product.container_size_id)}
                    </p>
                  </div>

                  <div className="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Unit weight</p>
                    <p className="text-sm font-semibold text-gray-800">
                      {product.weight_per_unit_grams ? `${product.weight_per_unit_grams} kg` : '—'}
                    </p>
                  </div>

                </div>
              </div>
              );
            })}

            {filtered.length === 0 && (
              <div className="text-center py-16 bg-white rounded-lg shadow-md">
                <Package size={48} className="mx-auto text-gray-400 mb-4" />
                <p className="text-gray-600 text-lg">No products found</p>
                <p className="text-gray-500 text-sm mt-1">Try adjusting your search filters</p>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}

export default ChemicalProductList;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<ChemicalProductList />);
}