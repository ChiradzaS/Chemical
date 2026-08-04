import React, { useState, useEffect } from 'react';
import { Trash2, Package, MapPin, User, Search, Truck, Copy } from 'lucide-react';
import { createRoot } from 'react-dom/client';

declare global {
  interface Window {
    customersData: any[];
    product: any[];
    driverTypesData: any[];
    unitTypesData: any[];
    processTypesData: any[];
    vehicleTypesData: any[];
  }
}

interface DeliveryItem {
  tb_deliveries_id: number;
  tb_deliveries_created_at: string;
  tb_deliveries_customerId: number;
  tb_deliveries_addressId: string;
  tb_deliveries_driver: number;
  tb_deliveries_vehicleReg: number;
  tb_deliveries_invoiceNo: string | null;
  tb_deliveries_reference: string | null;
  tb_delivery_items_processId: number;
  tb_delivery_items_productId: number;
  tb_delivery_items_qnt: number;
  tb_delivery_items_unitId: number;
}

interface GroupedDelivery {
  id: number;
  customerId: number;
  customerName: string;
  address: string;
  date: string;
  driver: string;
  vehicleReg: number;
  invoiceNo: string | null;
  reference: string | null;
  items: {
    productId: number;
    productName: string;
    processName: string;
    quantity: number;
    unitName: string;
  }[];
}

function DeliveryList() {
  const [customers] = useState(window.customersData || []);
  const [products] = useState(window.product || []);
  const [drivers] = useState(window.driverTypesData || []);
  const [unitTypes] = useState(window.unitTypesData || []);
  const [processTypes] = useState(window.processTypesData || []);
  const [vehicleTypesData] = useState(window.vehicleTypesData || []);

  const [deliveries, setDeliveries] = useState<DeliveryItem[]>([]);
  const [deleting, setDeleting] = useState<number | null>(null);
  const [cloning, setCloning] = useState<number | null>(null);
  const [searchCustomer, setSearchCustomer] = useState('');
  const [searchProduct, setSearchProduct] = useState('');
  const [dateFrom, setDateFrom] = useState('');
  const [dateTo, setDateTo] = useState('');
  const [showCustomerSuggestions, setShowCustomerSuggestions] = useState(false);
  const [showProductSuggestions, setShowProductSuggestions] = useState(false);

  const fetchDeliveries = async () => {
    try {
      const response = await fetch(`/LaravelCRUD/qrydeliveries/index?list=query`);
      const apiResponseData = await response.json();

      if (Array.isArray(apiResponseData)) {
        setDeliveries(apiResponseData);
      } else if (apiResponseData && typeof apiResponseData === "object") {
        setDeliveries([apiResponseData]);
      } else {
        setDeliveries([]);
      }
    } catch (error) {
      console.error("Error fetching deliveries:", error);
      alert("Failed to load deliveries. Please try again.");
    }
  };

  useEffect(() => {
    fetchDeliveries();
  }, []);

  const groupedDeliveries: GroupedDelivery[] = Object.values(
    deliveries.reduce((acc, item) => {
      const deliveryId = item.tb_deliveries_id;
      
      if (!acc[deliveryId]) {
        const customer = customers.find(c => c.id === item.tb_deliveries_customerId);
        const driver = drivers.find(d => d.id === item.tb_deliveries_driver);
        const vehicles = vehicleTypesData.find(v => v.id === item.tb_deliveries_vehicleReg);
        
        acc[deliveryId] = {
          id: deliveryId,
          customerId: item.tb_deliveries_customerId,
          customerName: customer?.name || 'Unknown Customer',
          address: item.tb_deliveries_addressId,
          date: item.tb_deliveries_created_at,
          driver: vehicles?.name || 'Private',
          vehicleReg:driver?.name || 'Unknown Driver',
          invoiceNo: item.tb_deliveries_invoiceNo,
          reference: item.tb_deliveries_reference,
          items: []
        };
      }
      
      const product = products.find(p => p.id === item.tb_delivery_items_productId);
      const unit = unitTypes.find(u => u.id === item.tb_delivery_items_unitId);
      const process = processTypes.find(p => p.id === item.tb_delivery_items_processId);
      
      acc[deliveryId].items.push({
        productId: item.tb_delivery_items_productId,
        productName: product?.name || 'Unknown Product',
        processName: process?.name || 'N/A',
        quantity: item.tb_delivery_items_quantity,
        unitName: unit?.name || 'units'
      });
      
      return acc;
    }, {} as Record<number, GroupedDelivery>)
  );

  const handleDeleteDelivery = async (deliveryId: number) => {
    if (!confirm('Are you sure you want to delete this delivery?')) {
      return;
    }

    setDeleting(deliveryId);
    
    try {
      const response = await fetch(`/LaravelCRUD/qrydeliveries/delete?data=${deliveryId}`);

      if (response.ok) {
        await fetchDeliveries();
        alert('Delivery deleted successfully');
      } else {
        alert('Failed to delete delivery. Please try again.');
      }
    } catch (error) {
      console.error('Error deleting delivery:', error);
      alert('Error deleting delivery. Please try again.');
    } finally {
      setDeleting(null);
    }
  };

  const handlePrintDelivery = async (deliveryId: number) => {
    const url = `/LaravelCRUD/delivery?id=${deliveryId}`;
    window.open(url, '_blank');
  };

 const handleCloneDelivery = async (delivery: GroupedDelivery) => {
 

    setCloning(delivery.id);

    try {
      // Prepare the data to send
      const cloneData = {
        customerId: delivery.customerId,
        addressId: delivery.address,
        driver: delivery.vehicleReg,
        vehicleReg: delivery.driver,
        invoiceNo: delivery.invoiceNo,
        reference: delivery.reference,
        items: delivery.items.map(item => ({
          productId: item.productId,
          productName: item.productName,
          processName: item.processName,
          quantity: item.quantity,
          unitName: item.unitName
        }))
      };

      console.log('Delivery details::::::::::::::::..>>>>>>>>>>:', cloneData);

      const params = new URLSearchParams({

        customerId: cloneData.customerId.toString(),
        addressId:  cloneData.addressId,
        driver:     cloneData.driver.toString(),
        vehicleReg: cloneData.vehicleReg.toString(),
        invoiceNo:  cloneData.invoiceNo || '',
        reference:  cloneData.reference || '',
        items:      JSON.stringify(cloneData.items)

      });

      const url = `/LaravelCRUD/reactdeliveries?${params.toString()}`;

     window.location.href = url;


    } catch (error) {
      console.error('Error cloning delivery:', error);
      alert('Error cloning delivery. Please try again.');
    } finally {
      setCloning(null);
    }
  };

  const filteredDeliveries = groupedDeliveries.filter(delivery => {
    const matchesCustomer = searchCustomer.trim() === '' || 
      delivery.customerName.toLowerCase().includes(searchCustomer.toLowerCase().trim()); 
    
    const matchesProduct = searchProduct.trim() === '' || 
      delivery.items.some(item => 
        item.productName.toLowerCase().includes(searchProduct.toLowerCase().trim()) ||
        item.productId.toString().includes(searchProduct.trim())
      );
    
    const matchesDateFrom = dateFrom === '' || 
      new Date(delivery.date) >= new Date(dateFrom);
    
    const matchesDateTo = dateTo === '' || 
      new Date(delivery.date) <= new Date(dateTo);
    
    return matchesCustomer && matchesProduct && matchesDateFrom && matchesDateTo;
  });

  const customerSuggestions = customers.filter(customer =>
    (customer.name.toLowerCase().includes(searchCustomer.toLowerCase().trim()) ||
    customer.id.toString().includes(searchCustomer.trim())) &&
    searchCustomer.trim() !== ''
  );

  const productSuggestions = products.filter(product =>
    (product.name.toLowerCase().includes(searchProduct.toLowerCase().trim()) ||
    product.id.toString().includes(searchProduct.trim())) &&
    searchProduct.trim() !== ''
  );

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-6xl mx-auto">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900 mb-2">Delivery List</h1>
          <p className="text-gray-600">Manage your scheduled deliveries</p>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="flex items-center gap-2 mb-4">
            <Search size={20} className="text-gray-500" />
            <h2 className="text-lg font-semibold text-gray-900">Search Filters</h2>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div className="relative">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Customer
              </label>
              <input
                type="text"
                value={searchCustomer}
                onChange={(e) => {
                  setSearchCustomer(e.target.value);
                  setShowCustomerSuggestions(true);
                }}
                onFocus={() => setShowCustomerSuggestions(true)}
                onBlur={() => setTimeout(() => setShowCustomerSuggestions(false), 300)}
                placeholder="Type to search customers..."
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none"
              />
              {showCustomerSuggestions && customerSuggestions.length > 0 && (
                <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                  {customerSuggestions.map((customer) => (
                    <button
                      key={customer.id}
                      type="button"
                      onMouseDown={(e) => {
                        e.preventDefault();
                        setSearchCustomer(customer.name);
                        setShowCustomerSuggestions(false);
                      }}
                      className="w-full text-left px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                    >
                      <span className="font-medium">{customer.name}</span>
                      <span className="text-gray-500 text-sm ml-2">#{customer.id}</span>
                    </button>
                  ))}
                </div>
              )}
            </div>

            <div className="relative">
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Product
              </label>
              <input
                type="text"
                value={searchProduct}
                onChange={(e) => {
                  setSearchProduct(e.target.value);
                  setShowProductSuggestions(true);
                }}
                onFocus={() => setShowProductSuggestions(true)}
                onBlur={() => setTimeout(() => setShowProductSuggestions(false), 300)}
                placeholder="Type to search products..."
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none"
              />
              {showProductSuggestions && productSuggestions.length > 0 && (
                <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                  {productSuggestions.map((product) => (
                    <button
                      key={product.id}
                      type="button"
                      onMouseDown={(e) => {
                        e.preventDefault();
                        setSearchProduct(product.name);
                        setShowProductSuggestions(false);
                      }}
                      className="w-full text-left px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                    >
                      <span className="font-medium">{product.name}</span>
                      <span className="text-gray-500 text-sm ml-2">#{product.id}</span>
                    </button>
                  ))}
                </div>
              )}
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Date From
              </label>
              <input
                type="date"
                value={dateFrom}
                onChange={(e) => setDateFrom(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Date To
              </label>
              <input
                type="date"
                value={dateTo}
                onChange={(e) => setDateTo(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <div className="mt-4 text-sm text-gray-600">
            Showing {filteredDeliveries.length} delivery{filteredDeliveries.length !== 1 ? 's' : ''}
          </div>
        </div>

        <div className="space-y-6">
          {filteredDeliveries
            .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
            .map((delivery) => (
            <div 
              key={delivery.id} 
              className="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow"
            >
              <div className="bg-gradient-to-r from-slate-700 to-slate-800 text-white p-5">
                <div className="flex justify-between items-start">
                  <div className="flex-1">
                    <div className="flex items-center gap-3 mb-2">
                      <div className="bg-slate-600 px-3 py-1 rounded-full text-sm font-semibold">
                        #{delivery.id}
                      </div>
                      <User size={20} />
                      <h2 className="text-xl font-semibold">{delivery.customerName}</h2>
                    </div>
                    <div className="flex items-start gap-2 text-slate-300 mb-2">
                      <MapPin size={18} className="mt-1 flex-shrink-0" />
                      <p className="text-sm">{delivery.address}</p>
                    </div>
                    <div className="flex items-center gap-4 text-sm text-slate-300">
                      <div className="flex items-center gap-2">
                        <Truck size={16} />
                        <span>{delivery.driver}</span>
                      </div>
                      <div>
                        Driver: {delivery.vehicleReg}
                      </div>
                      <div>
                        Date: {new Date(delivery.date).toLocaleDateString()}
                      </div>
                    </div>
                    {delivery.reference && (
                      <div className="mt-2 text-sm text-slate-400">
                        Reference: {delivery.reference}
                      </div>
                    )}
                  </div>
                  
                  <button
                    onClick={() => handleCloneDelivery(delivery)}
                    disabled={cloning === delivery.id}
                    className="ml-4 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-md"
                    title="Clone delivery"
                  >
                    <Copy size={18} />
                    {cloning === delivery.id ? 'Cloning...' : 'Clone'}
                  </button>

                  <button
                    onClick={() => handlePrintDelivery(delivery.id)}
                    className="ml-4 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-md"
                    title="Print delivery"
                  >
                    Print
                  </button>

                  <button
                    onClick={() => handleDeleteDelivery(delivery.id)}
                    disabled={deleting === delivery.id}
                    className="ml-4 bg-red-600 hover:bg-red-700 disabled:bg-red-300 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-md"
                    title="Delete delivery"
                  >
                    <Trash2 size={18} />
                    {deleting === delivery.id ? 'Deleting...' : 'Delete'}
                  </button>
                </div>
              </div>

              <div className="p-5">
                <div className="flex items-center gap-2 mb-4 text-gray-700">
                  <Package size={18} />
                  <h3 className="font-semibold text-lg">Delivery Items ({delivery.items.length})</h3>
                </div>
                <div className="space-y-2">
                  {delivery.items.map((item, index) => (
                    <div 
                      key={index}
                      className="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors"
                    >
                      <div className="flex-1">
                        <p className="font-semibold text-gray-900 text-lg">
                          {item.productName} - {item.unitName}
                        </p>
                      </div>
                      <div className="flex items-center gap-4">
                        <div className="text-right">
                          <p className="text-sm text-gray-500">Quantity</p>
                          <p className="font-bold text-gray-900 text-lg">{item.quantity}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          ))}

          {filteredDeliveries.length === 0 && (
            <div className="text-center py-12 bg-white rounded-lg shadow-md">
              <Package size={48} className="mx-auto text-gray-400 mb-4" />
              <p className="text-gray-600 text-lg">No deliveries found</p>
              <p className="text-gray-500 text-sm mt-2">Try adjusting your search filters</p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

export default DeliveryList;

// Mount React
const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<DeliveryList />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}