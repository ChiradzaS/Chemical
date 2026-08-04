import React, { useState, useEffect } from 'react';
import { Calendar, User, Package, Eye, Copy, Trash2, Play, Printer } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';



declare global {
  interface Window {

    customersData: Customer[];
    productData: Product[];
    product: Products[];
    unitTypesData: UnitType[];
    colourTypesData:  ColourType[];
    materialTypesData: MaterialType[];
    processTypesData: ProcessType[];
    bagTypesData: BagType[];
    stateTypesData :StatusType[];
 
  }
}

interface Customer {
  id: string;
  name: string;
}

interface Product {
  id: string;
  name: string;
}

interface UnitType {
  id: string;
  name: string;
}

interface StatusType {
  id: string;
  name: string;
}

interface ProcessType {
  id: string;
  name: string;
}

interface JobCardItem {
  job_card_id: string;
  job_cards_created_at: string;
  job_cards_customerId: string;
  job_cards_Id: string;
  job_cards_stateId: string;
  job_cards_outstanding: number;
  jobcarditem_id: string;
  jobcarditem_productId: string;
  jobcarditem_unitId: string;
  jobcarditem_processId: string;
  jobcarditem_outstanding: number;
  jobcarditem_qnt: number;
  jobcarditem_stateId: string;
  jobcarditem_other: string;
}

interface SearchFilters {
  fromDate: string;
  toDate: string;
  customerId: string;
  productId: string;
  productSearch: string;
}

const JobCardList: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const [allJobCards, setAllJobCards] = useState<JobCardItem[]>([]);
  const [notification, setNotification] = useState<{ type: 'success' | 'error' | null; message: string }>({
    type: null,
    message: ''
  });


const fetchJobCards = async () => {
  try {


        const response = await axios.get(
            `/LaravelCRUD/qryjobcards/index?data=query`
        );

    const apiResponseData = response.data;

    if (Array.isArray(apiResponseData)) {
      setAllJobCards(apiResponseData);
    } else if (apiResponseData && typeof apiResponseData === "object") {
      setAllJobCards([apiResponseData]);
    } else {
      setAllJobCards([]); // fallback
    }
  } catch (error) {
    console.error("Error fetching job cards:", error);
    alert("Failed to load job cards. Please try again.");
  }
};

useEffect(() => {
  fetchJobCards();
}, []);



  if (loading) {
    return <p>Loading job cards...</p>;
  }





  const [filteredJobCards, setFilteredJobCards] = useState<JobCardItem[]>(allJobCards);

          const [machineName, ]        = useState(window.machineTypesData || []);
          const [products, ]           = useState(window.product || []);
          const [product, ]            = useState(window.product || []);
          const [customers, ]          = useState(window.customersData || []);
          const [processTypes, ]       = useState(window.processTypesData || []);
          const [statusTypes, ]        = useState(window.stateTypesData || []);
          const [colourTypes, ]        = useState(window.colourTypesData || []);
          const [bagTypes, ]           = useState(window.bagTypesData || []);
          const [unitTypes, ]          = useState(window.unitTypesData || []);
          const [materialTypes, ]      = useState(window.materialTypesData || []);
 
  // const [customers] = useState<Customer[]>([
  //   { id: '1', name: 'ABC Manufacturing Co.' },
  //   { id: '2', name: 'XYZ Industries Ltd.' }
  // ]);

  // const [products] = useState<Product[]>([
  //   { id: '1', name: 'Steel Component A' },
  //   { id: '2', name: 'Aluminum Part B' }
  // ]);

  // const [unitTypes] = useState<UnitType[]>([
  //   { id: '1', name: 'Pieces' },
  //   { id: '2', name: 'Kilograms' }
  // ]);

  // const [statusTypes] = useState<StatusType[]>([
  //   { id: '1', name: 'In Progress' },
  //   { id: '2', name: 'Completed' }
  // ]);

  // const [processTypes] = useState<ProcessType[]>([
  //   { id: '1', name: 'Cutting' },
  //   { id: '2', name: 'Welding' }
  // ]);

  const [filters, setFilters] = useState<SearchFilters>({
    fromDate: '',
    toDate: '',
    customerId: '',
    productId: '',
    productSearch: ''
  });

  const [showProductSuggestions, setShowProductSuggestions] = useState(false);
  const [filteredProducts, setFilteredProducts] = useState<Product[]>([]);

  // Auto-filter effect
  useEffect(() => {
    let filtered = [...allJobCards];

    // Filter by date range
    if (filters.fromDate) {
      filtered = filtered.filter(item => item.job_cards_created_at >= filters.fromDate);
    }
    if (filters.toDate) {
      filtered = filtered.filter(item => item.job_cards_created_at <= filters.toDate);
    }

   // Filter by customer - convert both to string and compare
    if (filters.customerId) {
      filtered = filtered.filter(item => String(item.job_cards_customerId) === String(filters.customerId));
    }

   // Filter by product - search in job cards instead of job card items
    if (filters.productId) {
      // Get unique job card IDs that contain the selected product
      const jobCardIdsWithProduct = [...new Set(allJobCards
        .filter(item => String(item.jobcarditem_productId) === String(filters.productId))
        .map(item => item.job_card_id))];
      
      // Filter to include all items from job cards that contain the selected product
      filtered = filtered.filter(item => jobCardIdsWithProduct.includes(item.job_card_id));
    }

    // Filter by job card ID
    if (filters.jobCardId) {
      filtered = filtered.filter(item => 
        item.job_card_id.toLowerCase().includes(filters.jobCardId.toLowerCase())
      );
    }

    setFilteredJobCards(filtered);
  }, [filters, allJobCards]);

  const showSpinner = () => {
    setLoading(true);
    setTimeout(() => setLoading(false), 2000);
  };

  const closeNotification = () => {
    setNotification({ type: null, message: '' });
  };

  const handleFilterChange = (filterType: keyof SearchFilters, value: string) => {
    setFilters(prev => ({ ...prev, [filterType]: value }));
    
    // Handle product search suggestions
    if (filterType === 'productSearch') {
      if (value.trim()) {
        const filtered = products.filter(p => 
          p.name.toLowerCase().includes(value.toLowerCase())
        );
        setFilteredProducts(filtered);
        setShowProductSuggestions(true);
      } else {
        setFilteredProducts([]);
        setShowProductSuggestions(false);
        setFilters(prev => ({ ...prev, productId: '' }));
      }
    }
  };

  const handleProductSelect = (productId: string, productName: string) => {
    setFilters(prev => ({ 
      ...prev, 
      productId: productId,
      productSearch: productName 
    }));
    setShowProductSuggestions(false);
  };



  const handleClone = (jobCardId: string, customerId: string) => {




    // Method 1: URL Parameters
    const url = `/LaravelCRUD/reactjob?customerId=${customerId}&productId=${jobCardId}`;
    window.location.href = url;

    


  };

    const handlePrint = (jobCardItem: JobCardItem) => {

    const params = new URLSearchParams({

      jobCardId:     jobCardItem.job_card_id,
      other:         jobCardItem.jobcarditem_other,
      state:         jobCardItem.jobcarditem_stateId,
      jobcarditemId: jobCardItem.jobcarditem_id,
      productId:     jobCardItem.jobcarditem_productId
      
    });


   
    const url = `/Chemical/index/chemicalcreate?${params.toString()}`;

    window.open(url, '_blank');

  };


  




  const handleView = (jobCardId: string, productId: string) => {
    const params = new URLSearchParams({
      job: jobCardId,
      product: productId
    });
    
    const url = `/LaravelCRUD/actionjobs/actionview?${params.toString()}`;
       window.open(url, '_blank');
  };

const handleDelete = async (jobCardId: string) => {
  if (confirm("Are you sure you want to delete?")) {
    try {
      const response = await axios.get(
        `/LaravelCRUD/qryjobcards/reactdelete?data=${jobCardId}`
      );

      if (response.status === 200 || response.data.success) {
        setNotification({
          type: "success",
          message: "Job card deleted successfully!",
        });

        // 🔥 Refresh the list without page reload
        fetchJobCards();
      } else {
        setNotification({ type: "error", message: "Failed to delete job card." });
      }
    } catch (error) {
      console.error("Error deleting job card:", error);
      setNotification({
        type: "error",
        message: "Failed to delete job card. Please try again.",
      });
    }
  }
};

  const handleProduction = (jobCardItemId: string, outstanding: number, quantity: number) => {
    const params = new URLSearchParams({
      job: jobCardItemId,
      outstanding: outstanding.toString(),
      quantity: quantity.toString()
    });
    
    const url = `/LaravelCRUD/actionjobs/actionproduction?${params.toString()}`;
    window.location.href = url;
  };


  const getStatusColor = (quantity: number, outstanding: number): string => {
    if (quantity <= 0) return 'text-red-600';
    if (quantity < outstanding) return 'text-yellow-600';
    return 'text-gray-900';
  };

  const groupedJobCards = filteredJobCards.reduce((acc, item) => {
    if (!acc[item.job_card_id]) {
      acc[item.job_card_id] = [];
    }
    acc[item.job_card_id].push(item);
    return acc;
  }, {} as Record<string, JobCardItem[]>);

  return (
    <div className="min-h-screen bg-gray-50 p-4">
      {/* Loading Spinner */}
      {loading && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
        </div>
      )}

      {/* Notifications */}
      {notification.type && (
        <div className={`mb-4 p-4 rounded-lg flex items-center justify-between ${
          notification.type === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'
        }`}>
          <span className="font-medium">
            {notification.type === 'success' ? 'Success! ' : 'Error! '}
            {notification.message}
          </span>
          <button onClick={closeNotification} className="ml-4 text-gray-500 hover:text-gray-700">
            ×
          </button>
        </div>
      )}

      {/* Header */}
      <div className="max-w-7xl mx-auto">
        {/* Floating Search Filters */}
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 mb-8 backdrop-blur-sm bg-white/95">
          <div className="p-8">
            <h1 className="text-3xl font-bold text-gray-900 mb-8">Job Card Management</h1>
            
            {/* Search Filters - Single Row with Floating Style */}
            <div className="grid grid-cols-12 gap-6 items-end">
              <div className="col-span-3">
                <label className="block text-sm font-semibold text-gray-700 mb-3">
                  <Calendar className="inline w-4 h-4 mr-2" />
                  From Date
                </label>
                <input
                  type="date"
                  value={filters.fromDate}
                  onChange={(e) => handleFilterChange('fromDate', e.target.value)}
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition-all duration-200 bg-gray-50 focus:bg-white"
                />
              </div>

              <div className="col-span-3">
                <label className="block text-sm font-semibold text-gray-700 mb-3">
                  <Calendar className="inline w-4 h-4 mr-2" />
                  To Date
                </label>
                <input
                  type="date"
                  value={filters.toDate}
                  onChange={(e) => handleFilterChange('toDate', e.target.value)}
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition-all duration-200 bg-gray-50 focus:bg-white"
                />
              </div>

              <div className="col-span-3">
                <label className="block text-sm font-semibold text-gray-700 mb-3">
                  <User className="inline w-4 h-4 mr-2" />
                  Customer
                </label>
                <select
                  value={filters.customerId}
                  onChange={(e) => handleFilterChange('customerId', e.target.value)}
                  className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition-all duration-200 bg-gray-50 focus:bg-white appearance-none"
                >
                  <option value="">All Customers</option>
                  {customers.map((customer) => (
                    <option key={customer.id} value={customer.id}>
                      {customer.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="col-span-3">
                <label className="block text-sm font-semibold text-gray-700 mb-3">
                  <Package className="inline w-4 h-4 mr-2" />
                  Product
                </label>
                <div className="relative">
                  <input
                    type="text"
                    value={filters.productSearch}
                    onChange={(e) => handleFilterChange('productSearch', e.target.value)}
                    onFocus={() => filters.productSearch && setShowProductSuggestions(true)}
                    placeholder="Search product..."
                    className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm hover:shadow-md transition-all duration-200 bg-gray-50 focus:bg-white"
                  />
                  {showProductSuggestions && filteredProducts.length > 0 && (
                    <div className="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                      {filteredProducts.map((product) => (
                        <div
                          key={product.id}
                          onClick={() => handleProductSelect(product.id, product.name)}
                          className="px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors duration-150"
                        >
                          <span className="text-gray-900">{product.name}</span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              </div>

              

              
            </div>

            {/* Results Counter */}
            <div className="mt-6 text-sm text-gray-600">
              Showing {Object.keys(groupedJobCards).length} job card{Object.keys(groupedJobCards).length !== 1 ? 's' : ''} 
              ({filteredJobCards.length} item{filteredJobCards.length !== 1 ? 's' : ''})
            </div>
          </div>
        </div>
{/* Job Cards List */}
<div className="space-y-6">
  {Object.entries(groupedJobCards)
    .sort(([, itemsA], [, itemsB]) => {
      // Sort by creation date descending (most recent first)
      const dateA = new Date(itemsA[0].job_cards_created_at);
      const dateB = new Date(itemsB[0].job_cards_created_at);
      return dateB - dateA;
    })
    .map(([jobCardId, items]) => {
      const firstItem = items[0];
      const customer = customers.find(c => c.id === firstItem.job_cards_customerId);
      const status = statusTypes.find(s => s.id === firstItem.job_cards_stateId);
      const finalproduct = product.find(p => p.productType === '100');

      return (
        <div key={jobCardId} className="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300">
          {/* Job Card Header */}
          <div className="bg-gradient-to-r from-slate-700 to-slate-800 text-white px-8 py-5">
            <div className="flex items-center justify-between">
              <div className="flex items-center space-x-8">
                <div>
                  <h3 className="text-m font-bold"> {jobCardId}</h3>
                  <p className="text-slate-300 text-sm">{firstItem.job_cards_created_at}</p>
                </div>
                <div className="bg-slate-600 px-4 py-2 rounded-full">
                  <span className="text-sm font-medium">{status?.name}</span>
                </div>
                <div>
                  <h4 className="text-xl font-semibold">{customer?.name}</h4>
                </div>
              </div>
              
                    {/* Action Buttons */}
                    <div className="flex space-x-2">
                      <button
                        onClick={() => handleClone(firstItem.job_cards_productId, firstItem.job_cards_customerId)}
                        className="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm flex items-center transition-colors duration-200 shadow-md hover:shadow-lg"
                      >
                        <Copy className="w-4 h-4 mr-2" />
                        Clone
                      </button>

                      <button
                        onClick={() => handleDelete(jobCardId)}
                        className="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg text-sm flex items-center transition-colors duration-200 shadow-md hover:shadow-lg"
                      >
                        <Trash2 className="w-4 h-4 mr-2" />
                        Delete
                      </button>
                    </div>
                  </div>
                </div>

          {/* Job Card Items */}
          <div className="divide-y divide-gray-100">
            {items.map((item) => {
              const product = products.find(p => p.id === item.jobcarditem_productId);
              const unitType = unitTypes.find(u => u.id === item.jobcarditem_unitId);
              const processType = processTypes.find(pt => pt.id === item.jobcarditem_processId);

              return (
                <div key={item.jobcarditem_id} className="p-8 hover:bg-gray-50 transition-colors duration-200">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-8">
                      <div className="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-semibold">
                        {processType?.name}
                      </div>
                      <div>
                        <span className="font-medium">{item.jobcarditem_id}</span>
                      </div>
                      <div>
                        <span className="font-semibold text-gray-900 text-lg">
                          {product?.name} ({unitType?.name})
                        </span>
                      </div>
                    </div>

                    <div className="flex items-center space-x-6">
                      <div className="text-center">
                        <p className="text-sm text-gray-500 font-medium">Qty</p>
                        <p className="font-bold text-gray-900 text-lg">{item.jobcarditem_outstanding}</p>
                      </div>
                      <div className="text-center">
                        <p className="text-sm text-gray-500 font-medium">Outstanding</p>
                        <p className={`font-bold text-lg ${getStatusColor(item.jobcarditem_qnt, item.jobcarditem_outstanding)}`}>
                          {item.jobcarditem_qnt}
                        </p>
                      </div>
                      <div className="flex space-x-3">
                        {item.jobcarditem_outstanding === item.jobcarditem_qnt ? (
                          <button
                            disabled
                            className="bg-gray-300 text-gray-500 px-5 py-2 rounded-lg cursor-not-allowed flex items-center"
                          >
                            <Play className="w-4 h-4 mr-2" />
                            Production
                          </button>
                        ) : (
                          <button
                            onClick={() => handleProduction(item.jobcarditem_id)}
                            className="bg-orange-600 hover:bg-orange-700 text-white px-5 py-2 rounded-lg flex items-center transition-colors duration-200 shadow-md hover:shadow-lg"
                          >
                            <Play className="w-4 h-4 mr-2" />
                            Production
                          </button>
                        )}
                        <button
                          onClick={() => handlePrint(item)}
                          className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors duration-200 shadow-md hover:shadow-lg"
                        >
                          <Printer className="w-4 h-4 mr-2" />
                          Print
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
</div>

        {Object.keys(groupedJobCards).length === 0 && (
          <div className="bg-white rounded-xl shadow-lg border border-gray-100 p-12 text-center">
            <div className="text-gray-400 mb-6">
              <Package className="w-20 h-20 mx-auto" />
            </div>
            <h3 className="text-xl font-semibold text-gray-900 mb-3">Loading Jobcard List .......</h3>
            <p className="text-gray-500">Try adjusting your search filters or create a new job card.</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default JobCardList;

// Mount React
const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<JobCardList />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}