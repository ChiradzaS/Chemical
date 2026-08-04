import React, { useState, useEffect } from 'react';
import { Loader2, Building2, MapPin, Receipt, CheckCircle2, XCircle } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

declare global {
  interface Window {
    laravelApiUrl: string;
  }
}

interface CompanyInfoData {
  id?: number;
  name: string;
  trading_name: string;
  vat_number: string;
  reg_number: string;
  tel_number: string;
  email: string;
  web_address: string;
  suburb: string;
  shop_no: string;
  physical_address: string;
  city: string;
  country: string;
  receipt_comment: string;
}

interface Toast {
  id: number;
  type: 'success' | 'error';
  message: string;
}

const emptyCompanyInfo: CompanyInfoData = {
  name: '',
  trading_name: '',
  vat_number: '',
  reg_number: '',
  tel_number: '',
  email: '',
  web_address: '',
  suburb: '',
  shop_no: '',
  physical_address: '',
  city: '',
  country: '',
  receipt_comment: '',
};

// Coalesce any null values from the API (nullable DB columns) to empty strings,
// so inputs never flip from controlled to uncontrolled.
const sanitizeCompanyData = (data: Partial<CompanyInfoData>): CompanyInfoData => ({
  ...emptyCompanyInfo,
  ...data,
  trading_name:     data.trading_name     ?? '',
  shop_no:          data.shop_no          ?? '',
  receipt_comment:  data.receipt_comment  ?? '',
  vat_number:       data.vat_number       ?? '',
  reg_number:       data.reg_number       ?? '',
  tel_number:       data.tel_number       ?? '',
  email:            data.email            ?? '',
  web_address:      data.web_address      ?? '',
  suburb:           data.suburb           ?? '',
  physical_address: data.physical_address ?? '',
  city:             data.city             ?? '',
  country:          data.country          ?? '',
});

// Required fields — everything except trading_name, shop_no, web_address, receipt_comment
const REQUIRED_FIELDS: { field: keyof CompanyInfoData; label: string }[] = [
  { field: 'name',             label: 'Company Name' },
  { field: 'vat_number',       label: 'VAT Number' },
  { field: 'reg_number',       label: 'Registration Number' },
  { field: 'tel_number',       label: 'Tel / Cell Number' },
  { field: 'email',            label: 'Email Address' },
  { field: 'physical_address', label: 'Physical Address' },
  { field: 'suburb',           label: 'Suburb' },
  { field: 'city',             label: 'City' },
  { field: 'country',          label: 'Country' },
];

const API_BASE  = window.laravelApiUrl || 'http://localhost/Chemical';
const FETCH_URL = `${API_BASE}/qrycompanyinfo/fetch`;
const SAVE_URL  = `${API_BASE}/qrycompanyinfo/save`;

const CompanySettingsForm: React.FC = () => {
  const [form,             setForm]             = useState<CompanyInfoData>(emptyCompanyInfo);
  const [loading,          setLoading]          = useState(false);
  const [saving,           setSaving]           = useState(false);
  const [isFirstTimeSetup, setIsFirstTimeSetup] = useState(false);
  const [toasts,           setToasts]           = useState<Toast[]>([]);

  // ── Toast helpers ─────────────────────────────────────────────────────────
  const showToast = (type: Toast['type'], message: string) => {
    const id = Date.now();
    setToasts(prev => [...prev, { id, type, message }]);
    setTimeout(() => {
      setToasts(prev => prev.filter(t => t.id !== id));
    }, 4000);
  };

  const dismissToast = (id: number) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  };

  // ── Fetch company info on mount ──────────────────────────────────────────
  useEffect(() => {
    fetchCompanyInfo();
  }, []);

  const fetchCompanyInfo = async () => {
    setLoading(true);
    try {
      const response = await axios.get(FETCH_URL);
      const apiResponseData = response.data.data;

      if (apiResponseData) {
        setForm(sanitizeCompanyData(apiResponseData));
        setIsFirstTimeSetup(false);
      } else {
        setForm(emptyCompanyInfo);
        setIsFirstTimeSetup(true);
      }
    } catch (error) {
      console.error('Error fetching company info:', error);
      showToast('error', 'Failed to load company info. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field: keyof CompanyInfoData, value: string) => {
    setForm(f => ({ ...f, [field]: value }));
  };

  // ── Validation ────────────────────────────────────────────────────────────
  const validateForm = (): string | null => {
    for (const { field, label } of REQUIRED_FIELDS) {
      const value = form[field];
      if (value === null || value === undefined || String(value).trim() === '') {
        return `${label} is required`;
      }
    }
    return null;
  };

  // ── Submit ────────────────────────────────────────────────────────────────
  const handleSubmit = async () => {
    const validationError = validateForm();
    if (validationError) {
      showToast('error', validationError);
      return;
    }

    setSaving(true);
    try {
      const encodedData = encodeURIComponent(JSON.stringify(form));
      const response = await axios.get(`${SAVE_URL}?data=${encodedData}`);

      const apiResponseData = response.data.data;
      if (apiResponseData) {
        setForm(sanitizeCompanyData(apiResponseData));
        setIsFirstTimeSetup(false);
      }

      showToast('success', 'Company info saved successfully!');
    } catch (error: any) {
      console.error(error);
      if (error.response?.data?.message) {
        showToast('error', error.response.data.message);
      } else {
        showToast('error', 'Error saving company info. Please retry.');
      }
    } finally {
      setSaving(false);
    }
  };

  const inp = "bg-white border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200";
  const req = <span className="text-red-500">*</span>;
  const opt = <span className="text-gray-400 font-normal">(optional)</span>;

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        <div className="w-full p-6 bg-white rounded-lg shadow-lg">

          {/* ── Page header ── */}
          <div className="border-b border-gray-200 pb-4 mb-6">
            <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
              <Building2 className="text-blue-600" />
              Company Info
            </h1>
            {isFirstTimeSetup && (
              <p className="mt-2 text-sm text-amber-600 font-medium">
                No company info found — fill this in to set it up for the first time.
              </p>
            )}
          </div>

          <div className="space-y-6">

            {/* ── Company details ── */}
            <div className="border border-gray-200 rounded-lg overflow-hidden">
              <div className="bg-gray-800 text-white p-3">
                <h3 className="text-lg font-semibold">Company Details</h3>
              </div>
              <div className="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Company Name {req}</label>
                  <input type="text" value={form.name} onChange={e => handleChange('name', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Trading Name {opt}</label>
                  <input type="text" value={form.trading_name} onChange={e => handleChange('trading_name', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">VAT Number {req}</label>
                  <input type="text" value={form.vat_number} onChange={e => handleChange('vat_number', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Registration Number {req}</label>
                  <input type="text" value={form.reg_number} onChange={e => handleChange('reg_number', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Tel / Cell Number {req}</label>
                  <input type="text" value={form.tel_number} onChange={e => handleChange('tel_number', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Email Address {req}</label>
                  <input type="email" value={form.email} onChange={e => handleChange('email', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Web Address {opt}</label>
                  <input type="text" value={form.web_address} onChange={e => handleChange('web_address', e.target.value)} className={inp} />
                </div>
              </div>
            </div>

            {/* ── Address ── */}
            <div className="border border-gray-200 rounded-lg overflow-hidden">
              <div className="bg-gray-800 text-white p-3">
                <h3 className="text-lg font-semibold flex items-center gap-2">
                  <MapPin className="w-4 h-4" /> Address
                </h3>
              </div>
              <div className="p-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Physical Address {req}</label>
                  <input type="text" value={form.physical_address} onChange={e => handleChange('physical_address', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Suburb {req}</label>
                  <input type="text" value={form.suburb} onChange={e => handleChange('suburb', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Shop No. {opt}</label>
                  <input type="text" value={form.shop_no} onChange={e => handleChange('shop_no', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">City {req}</label>
                  <input type="text" value={form.city} onChange={e => handleChange('city', e.target.value)} className={inp} />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">Country {req}</label>
                  <input type="text" value={form.country} onChange={e => handleChange('country', e.target.value)} className={inp} />
                </div>
              </div>
            </div>

            {/* ── Receipt comment ── */}
            <div className="border border-gray-200 rounded-lg overflow-hidden">
              <div className="bg-gray-800 text-white p-3">
                <h3 className="text-lg font-semibold flex items-center gap-2">
                  <Receipt className="w-4 h-4" /> Receipt Comment
                </h3>
              </div>
              <div className="p-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Optional message to highlight on printed receipts {opt}
                </label>
                <textarea
                  rows={3}
                  value={form.receipt_comment}
                  onChange={e => handleChange('receipt_comment', e.target.value)}
                  placeholder="e.g. Thank you for your business! Goods sold are not returnable."
                  className={inp}
                />
              </div>
            </div>

            {/* ── Submit ── */}
            <div className="flex justify-center pt-6">
              <button
                onClick={handleSubmit}
                disabled={saving}
                className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium flex items-center gap-2 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all"
              >
                {saving ? <Loader2 className="w-5 h-5 animate-spin" /> : null}
                SAVE
              </button>
            </div>

          </div>
        </div>
      </div>

      {loading && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg shadow-xl">
            <div className="flex items-center gap-3">
              <Loader2 className="w-6 h-6 animate-spin text-blue-600" />
              <span className="text-lg font-medium">Loading...</span>
            </div>
          </div>
        </div>
      )}

      {/* ── Toast container ── */}
      <div className="fixed top-6 right-6 z-[100] flex flex-col gap-3">
        {toasts.map(toast => (
          <div
            key={toast.id}
            onClick={() => dismissToast(toast.id)}
            className={`flex items-center gap-3 px-5 py-3 rounded-lg shadow-lg cursor-pointer min-w-72 max-w-96 animate-[fadeIn_0.2s_ease-out] ${
              toast.type === 'success'
                ? 'bg-green-50 border border-green-200 text-green-800'
                : 'bg-red-50 border border-red-200 text-red-800'
            }`}
          >
            {toast.type === 'success'
              ? <CheckCircle2 className="w-5 h-5 flex-shrink-0" />
              : <XCircle className="w-5 h-5 flex-shrink-0" />}
            <span className="text-sm font-medium">{toast.message}</span>
          </div>
        ))}
      </div>
    </div>
  );
};

export default CompanySettingsForm;

const container = document.getElementById('root');
if (container) {
  const root = createRoot(container);
  root.render(<CompanySettingsForm />);
}