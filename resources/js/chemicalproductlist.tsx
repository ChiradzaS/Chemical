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
  price:                 number | null;   // excl. VAT
  price_incl_vat:            number | null;   // incl. VAT
  vat_applicable:        number;
  is_active:             number;
  created_at:            string;
}

const TH = 'px-4 py-3 text-xs font-bold text-gray-400 uppercase tracking-wider whitespace-nowrap';
const inputCls = 'w-full px-2.5 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:outline-none';

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

  const handleEdit = (id: number) => { window.location.href = `${FORM_URL}?id=${id}`; };
  const handleNew  = () => { window.location.href = FORM_URL; };

  // ── Filter ──────────────────────────────────────────────────────────────
  const term = searchName.trim().toLowerCase();
  const filtered = products.filter(p =>
    !term || p.name.toLowerCase().includes(term)
  ).sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());

  return (
    <div className="min-h-screen bg-gray-50 p-4">
      <div className="max-w-7xl mx-auto">

        {/* ── Header ── */}
        <div className="mb-4 flex items-center justify-between gap-3">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Chemical Products</h1>
            <p className="text-sm text-gray-600">Manage your chemical product catalogue</p>
          </div>
          <button
            onClick={handleNew}
            className="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-colors shadow"
          >
            <Plus size={16} /> New Product
          </button>
        </div>

        {/* ── Filters ── */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-3 mb-4">
          <div className="max-w-md">
            <label className="block text-xs font-medium text-gray-600 mb-1">Product name</label>
            <div className="relative">
              <Search size={16} className="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" />
              <input type="text" value={searchName} onChange={e => setSearchName(e.target.value)}
                placeholder="Search product name…" className={`${inputCls} pl-8 py-2 text-base`} />
            </div>
          </div>
          <div className="mt-2 text-xs text-gray-500">
            Showing {filtered.length} product{filtered.length !== 1 ? 's' : ''}
          </div>
        </div>

        {/* ── List ── */}
        {loading ? (
          <div className="text-center py-16 text-gray-400">Loading products…</div>
        ) : filtered.length === 0 ? (
          <div className="text-center py-16 bg-white rounded-lg shadow-sm border border-gray-200">
            <Package size={48} className="mx-auto text-gray-400 mb-4" />
            <p className="text-gray-600 text-lg">No products found</p>
            <p className="text-gray-500 text-sm mt-1">Try adjusting your search filters</p>
          </div>
        ) : (
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div className="overflow-x-auto">
              <table className="w-full text-base min-w-[1000px]">
                <thead className="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th className={`${TH} text-left`}>Product</th>
                    <th className={`${TH} text-right`}>Stock on hand</th>
                    <th className={`${TH} text-left`}>Colour</th>
                    <th className={`${TH} text-left`}>Viscosity</th>
                    <th className={`${TH} text-left`}>Fragrance</th>
                    <th className={`${TH} text-left`}>Container</th>
                    <th className={`${TH} text-right`}>Unit weight</th>
                    <th className={`${TH} text-right`}>Price (incl. VAT)</th>
                    <th className={TH}></th>
                  </tr>
                </thead>
                <tbody>
                  {filtered.map(product => {
                    const colourName   = byId(colourTypes, product.colour_id);
                    const colourSwatch = getColourSwatch(colourName);

                    return (
                      <tr key={product.id} className="border-t border-gray-100 hover:bg-blue-50/40">
                        {/* Product details: name + batch only */}
                        <td className="px-4 py-3.5">
                          <p className="font-semibold text-gray-900 text-base leading-tight">{product.name}</p>
                          <p className="text-xs text-gray-500 font-mono mt-0.5">
                            BATCH: {product.sku || '—'}
                          </p>
                        </td>
                        <td className="px-4 py-3.5 text-right whitespace-nowrap">
                          <span className="text-lg font-bold text-gray-800">{product.stock_on_hand ?? 0}</span>
                          <span className="text-sm text-gray-500 ml-1">{(() => {
                            const u = byId(unitTypes, product.stock_unit_id);
                            return u === '—' ? '' : u.replace(/\s*[\/\\]\s*/g, ' ');
                          })()}</span>
                        </td>
                        <td className="px-4 py-3.5 whitespace-nowrap">
                          <span className="flex items-center gap-1.5 text-gray-700">
                            {colourSwatch && (
                              <span className="inline-block w-4 h-4 rounded-full border border-gray-300 shrink-0"
                                style={{ backgroundColor: colourSwatch }} />
                            )}
                            {colourName}
                          </span>
                        </td>
                        <td className="px-4 py-3.5 text-gray-700 whitespace-nowrap">{byId(viscosities, product.viscosity_id)}</td>
                        <td className="px-4 py-3.5 text-gray-700 whitespace-nowrap">{byId(fragrances, product.fragrance_id)}</td>
                        <td className="px-4 py-3.5 text-gray-700 whitespace-nowrap">
                          {[byId(bagTypes, product.bag_type_id), byId(containerSizes, product.container_size_id)]
                            .filter(v => v !== '—')
                            .map(v => v.replace(/\s*[\/\\]\s*/g, ' '))
                            .join(' ')}
                        </td>
                        <td className="px-4 py-3.5 text-right text-gray-700 whitespace-nowrap">
                          {product.weight_per_unit_grams ? `${product.weight_per_unit_grams} kg` : '—'}
                        </td>
                        <td className="px-4 py-3.5 text-right text-lg font-bold text-blue-700 whitespace-nowrap">
                          {product.price_incl_vat != null ? `R ${Number(product.price_incl_vat).toFixed(2)}` : '—'}
                        </td>
                        <td className="px-4 py-3.5 text-right">
                          <button
                            onClick={() => handleEdit(product.id)}
                            className="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-1.5 rounded-md text-sm font-semibold transition-colors"
                          >
                            <Edit size={15} /> Edit
                          </button>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
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