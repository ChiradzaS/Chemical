import React, { useState, useEffect } from 'react';
import { Truck, User, MapPin, Package, FileText, Calculator, Plus, Minus } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';




declare global {

  interface Window {

  
    urlParamsDataDelivery: { 

    customerId: string; 
    addressId: string;
    driver: string;
    vehicleReg: string;
    invoiceNo: string;
    reference: string;
    items: Array<{

        productId: number;
        productName: string;
        processName: string;
        quantity: number;
        unitName: string;

    }>;

};
    
    
 
  }

}

const DeliveryInvoiceForm = () => {



  // Mock data


  // const mockProducts = [
  //   { id: 1, name: 'Product A', unitName: 'Box' },
  //   { id: 2, name: 'Product B', unitName: 'Pallet' },
  //   { id: 3, name: 'Product C', unitName: 'Case' },
  //   { id: 4, name: 'Product D', unitName: 'Box' },
  //   { id: 5, name: 'Product E', unitName: 'Unit' },
  //   { id: 6, name: 'Apple Juice', unitName: 'Bottle' },
  //   { id: 7, name: 'Orange Juice', unitName: 'Bottle' },
  //   { id: 8, name: 'Water Bottles', unitName: 'Pack' },
  //   { id: 9, name: 'Coffee Beans', unitName: 'Bag' },
  //   { id: 10, name: 'Tea Bags', unitName: 'Box' }
  // ];

  // const mockVehicles = [
  //   { id: 1, name: 'VAN-001-GP' },
  //   { id: 2, name: 'TRUCK-002-GP' },
  //   { id: 3, name: 'VAN-003-GP' }
  // ];

  // const mockDrivers = [
  //   { id: 1, name: 'John Smith' },
  //   { id: 2, name: 'Sarah Johnson' },
  //   { id: 3, name: 'Michael Brown' }
  // ];

  // const mockCustomers = [
  //   { id: 1, name: 'ABC Corporation' },
  //   { id: 2, name: 'XYZ Enterprises' },
  //   { id: 3, name: 'Global Trading Co.' }
  // ];



  const [mockProducts, ] =      useState(window.productsData || []);
  const [mockVehicles, ] =      useState(window.vehicleTypesData || []);
  const [mockDrivers, ]  =      useState(window.driverTypesData || []);
  const [mockCustomers, ]  =    useState(window.customersData || []);
  const [unitTypes] =           useState(window.unitTypesData || []);

  const [formData, setFormData] = useState({
    customerId: mockCustomers[0].id,
    vehicleReg: '',
    driver: '',
    address: '13 smith street, Germiston',
    generateInvoice: false
  });

  const [selectedProduct, setSelectedProduct] = useState('');
  const [productSearch, setProductSearch] = useState('');
  const [orderItems, setOrderItems] = useState([]);
  const [nextId, setNextId] = useState(1);

  const [totals, setTotals] = useState({
    subtotal: 0,
    totalVat: 0,
    totalDiscount: 0,
    grandTotal: 0
  });

  const calculateItemTotal = (item) => {
    const quantity = parseFloat(item.quantity) || 0;
    const price = parseFloat(item.price) || 0;
    const discount = parseFloat(item.discount) || 0;
    const vat = parseFloat(item.vat) || 0;

    const subtotal = quantity * price;
    const discountValue = (discount / 100) * subtotal;
    const afterDiscount = subtotal - discountValue;
    const vatValue = (vat / 100) * afterDiscount;
    const total = afterDiscount + vatValue;

    return {
      discountValue,
      vatValue,
      total
    };
  };

  const filteredProducts = mockProducts.filter(product => 
    product.name.toLowerCase().includes(productSearch.toLowerCase())
  );

  const handleAddProduct = () => {
    if (!selectedProduct) {
      alert('Please select a product');
      return;
    }

      // Helper function to get unit name by ID
  const getUnitNameById = (unitId) => {
    const unit = unitTypes.find(u => u.id === parseInt(unitId));
    return unit ? unit.name : 'N/Aa';
  };

    const product = mockProducts.find(p => p.id === parseInt(selectedProduct));
    if (!product) return;



       

    const newItem = {
      id: nextId,
      reference: `ORD-${String(nextId).padStart(3, '0')}`,
      productName: product.name,
      unitName: product.unitName,
      unit: getUnitNameById(product.unitName),
      productId: product.id,
      quantity: '',
      price: '',
      discount: 0,
      vat: 0,
      discountValue: 0,
      vatValue: 0,
      total: 0,
      selected: false
    };

     //console.log('Das i add items ,,,,,,,,,,,,,,,,,,........>:', product);

    setOrderItems([...orderItems, newItem]);
    setNextId(nextId + 1);
    setSelectedProduct('');
    setProductSearch('');
  };

  const handleRemoveProduct = (id) => {
    setOrderItems(prev => prev.filter(item => item.id !== id));
  };

  const handleItemChange = (id, field, value) => {
    setOrderItems(prev => prev.map(item => {
      if (item.id === id) {
        const updated = { ...item, [field]: value };
        const calculated = calculateItemTotal(updated);
        return { ...updated, ...calculated };
      }
      return item;
    }));
  };


useEffect(() => {
  const urlParams = window.urlParamsDataDelivery;

   //console.log('Error generating deliveryQQQQQQQQQQQQQQQQQQOOOOOOOOOOO:', urlParams);
  
  if (urlParams && urlParams.customerId) {

    const getDriverIdByName = (driverName) => {
      const driver = mockDrivers.find(d => d.name === driverName);
      return driver ? driver.id : '';
    };

    const getVehicleIdByName = (vehicleName) => {
      const vehicle = mockVehicles.find(v => v.name === vehicleName);
      return vehicle ? vehicle.id : '';
    };
   
    setFormData(prev => ({
      ...prev,
      customerId:    urlParams.customerId || prev.customerId,
      driver:        getDriverIdByName(urlParams.driver ) || '',
      vehicleReg:    getVehicleIdByName(urlParams.vehicleReg) || '',
      address:       urlParams.addressId || prev.address
    }));
    
    // Fill in all items
    if (urlParams.items && Array.isArray(urlParams.items) && urlParams.items.length > 0) {
      const newOrderItems = urlParams.items.map((item, index) => {
        // Find the product by ID to get full details
        const selectedProduct = mockProducts.find(p => p.id.toString() === item.productId.toString());
        
        if (selectedProduct) {
          // Helper function to get unit name by ID
          const getUnitNameBy = (unitId) => {
            const unit = unitTypes.find(u => u.id === parseInt(unitId));
            return unit ? unit.name : 'N/A';
          };

          return {
            id: index + 1,
            reference: urlParams.reference || `ORD-${String(index + 1).padStart(3, '0')}`,
            productName: item.productName,
            unitName: item.unitName,
            unit: getUnitNameBy(selectedProduct.unitName),
            productId: item.productId,
            quantity: item.quantity,
            price: '',
            discount: 0,
            vat: 0,
            discountValue: 0,
            vatValue: 0,
            total: 0,
            selected: false
          };
        }
        return null;
      }).filter(item => item !== null); // Remove any null items where product wasn't found
      
      setOrderItems(newOrderItems);
      setNextId(newOrderItems.length + 1);
    }
  }
}, [mockProducts, unitTypes]); // Add dependencies

  const handleDeliveryList = () => {
  // Your function logic here
      const url = `/LaravelCRUD/reactdeliverylist`;
      window.location.href = url;

};

 const handleGenerateDelivery = async () => {
    if (!formData.vehicleReg) {
      alert('Please select a vehicle');
      return;
    }
    if (!formData.driver) {
      alert('Please select a driver');
      return;
    }
    if (!formData.address) {
      alert('Please enter a delivery address');
      return;
    }


    const selectedItems = orderItems.filter(item => item.quantity > 0 );

        // Collect delivery items
    const items = selectedItems.map(item => ({
      productId: item.productId,
      productName: item.productName,
      unitName: item.unitName, 
      quantity: item.quantity,
      reference: item.reference
    }));

    // Prepare delivery details
    const deliveryDetails = {
      customerId: formData.customerId,
      vehicleId: formData.vehicleReg,
      driverId: formData.driver,
      address: formData.address
    };

    // Wrap everything
    const payload = {
      delivery: deliveryDetails,
      items: items
    };






       try {
      // Encode as query string
      const encodedData = encodeURIComponent(JSON.stringify(payload));

      // Save to server
      const response = await axios.get(

        `/LaravelCRUD/qrydeliveries/index?data=${encodedData}`
      );


   




      if (response) {


      
        //alert('Delivery note generated successfully!');

        const url = `/LaravelCRUD/reactdeliverylist`;
        window.location.href = url;

      } else {

        alert('Failed to generate delivery note');

      }
    } catch (error) {

      console.error('Error generating delivery:', error);
      alert('Error generating delivery note');
      
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-100 p-8">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <div className="mb-10">
          <h1 className="text-5xl font-bold text-gray-800 mb-3 flex items-center gap-4">
            <FileText className="w-12 h-12 text-blue-600" />
            Create Delivery / Invoice
          </h1>
          <div className="h-1.5 w-40 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full"></div>
        </div>

{/* Top Action Buttons */}
        <div className="bg-white rounded-2xl shadow-lg p-5 mb-7 border border-gray-200">
          <div className="flex items-center justify-between">
            <button 
              onClick={handleDeliveryList}
              className="bg-gray-800 hover:bg-gray-700 text-white px-7 py-3 rounded-xl transition-colors font-semibold text-base"
            >
              Delivery List
            </button>
          </div>
        </div>
        {/* Add Product Section */}
        <div className="bg-white rounded-2xl shadow-lg p-5 mb-7 border border-gray-200">
          <div className="flex items-center gap-5">
            <label className="block text-gray-700 text-base font-semibold flex items-center gap-2 whitespace-nowrap">
              <Package className="w-5 h-5 text-blue-600" />
              Add Product:
            </label>
            <div className="flex-1 relative">
              <input
                type="text"
                className="w-full bg-white text-gray-800 text-base border border-gray-300 rounded-xl px-5 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Search products..."
                value={productSearch}
                onChange={(e) => {
                  setProductSearch(e.target.value);
                  setSelectedProduct('');
                }}
              />
              {productSearch && filteredProducts.length > 0 && (
                <div className="absolute z-10 w-full bg-white border border-gray-300 rounded-xl mt-1 max-h-72 overflow-y-auto shadow-lg">
                  {filteredProducts.map(product => (
                    <div
                      key={product.id}
                      className="px-5 py-3 text-base hover:bg-blue-50 cursor-pointer"
                      onClick={() => {
                        setSelectedProduct(product.id.toString());
                        setProductSearch(product.name);
                      }}
                    >
                      {product.name} 
                    </div>
                  ))}
                </div>
              )}
            </div>
            <button
              onClick={handleAddProduct}
              className="bg-blue-600 hover:bg-blue-700 text-white px-7 py-3 rounded-xl transition-colors font-semibold text-base flex items-center gap-2"
            >
              <Plus className="w-6 h-6" />
              Add to List
            </button>
          </div>
        </div>

        {/* Top Form Section */}
        <div className="bg-white rounded-2xl shadow-lg p-7 mb-7 border border-gray-200">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            {/* Customer */}
            <div>
              <label className="block text-gray-700 text-base font-semibold mb-2 flex items-center gap-2">
                <User className="w-5 h-5 text-blue-600" />
                CUSTOMER
              </label>
              <select
                className="w-full bg-white text-gray-800 text-base border border-gray-300 rounded-xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={formData.customerId}
                onChange={(e) => setFormData({ ...formData, customerId: e.target.value })}
              >
                {mockCustomers.map(customer => (
                  <option key={customer.id} value={customer.id}>{customer.name}</option>
                ))}
              </select>
            </div>

            {/* Vehicle */}
            <div>
              <label className="block text-gray-700 text-base font-semibold mb-2 flex items-center gap-2">
                <Truck className="w-5 h-5 text-blue-600" />
                VEHICLE
              </label>
              <select
                className="w-full bg-white text-gray-800 text-base border border-gray-300 rounded-xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={formData.vehicleReg}
                onChange={(e) => setFormData({ ...formData, vehicleReg: e.target.value })}
              >
                <option value="">-- Select Vehicle --</option>
                {mockVehicles.map(vehicle => (
                  <option key={vehicle.id} value={vehicle.id}>{vehicle.name}</option>
                ))}
              </select>
            </div>

            {/* Driver */}
            <div>
              <label className="block text-gray-700 text-base font-semibold mb-2 flex items-center gap-2">
                <User className="w-5 h-5 text-blue-600" />
                DRIVER
              </label>
              <select
                className="w-full bg-white text-gray-800 text-base border border-gray-300 rounded-xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={formData.driver}
                onChange={(e) => setFormData({ ...formData, driver: e.target.value })}
              >
                <option value="">-- Select Driver --</option>
                {mockDrivers.map(driver => (
                  <option key={driver.id} value={driver.id}>{driver.name}</option>
                ))}
              </select>
            </div>

            {/* Address */}
            <div>
              <label className="block text-gray-700 text-base font-semibold mb-2 flex items-center gap-2">
                <MapPin className="w-5 h-5 text-blue-600" />
                ADDRESS
              </label>
              <input
                type="text"
                className="w-full bg-white text-gray-800 text-base border border-gray-300 rounded-xl px-5 py-3.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                value={formData.address}
                onChange={(e) => setFormData({ ...formData, address: e.target.value })}
              />
            </div>
          </div>
        </div>

        {/* Order Items Table */}
        <div className="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-200">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-black text-white">
                <tr>
                  <th className="px-5 py-5 text-center text-base font-bold">ID</th>
                  <th className="px-5 py-5 text-center text-base font-bold">REFERENCE</th>
                  <th className="px-5 py-5 text-center text-base font-bold">PRODUCT</th>
                  <th className="px-5 py-5 text-center text-base font-bold">PACK</th>
                  <th className="px-5 py-5 text-center text-base font-bold">QUANTITY</th>
                  <th className="px-5 py-5 text-center text-base font-bold">ACTION</th>
                </tr>
              </thead>
              <tbody>
                {orderItems.length === 0 ? (
                  <tr>
                    <td colSpan="6" className="px-5 py-10 text-center text-gray-500 text-base">
                      No products added. Please add products using the form above.
                    </td>
                  </tr>
                ) : (
                  orderItems.map((item, index) => (
                    <tr key={item.id} className={`border-t border-gray-200 ${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}`}>
                      <td className="px-5 py-4 text-center text-gray-800 text-base">{item.id}</td>
                      <td className="px-5 py-4 text-center text-gray-800 text-base">{item.reference}</td>
                      <td className="px-5 py-4 text-center text-gray-800 text-base">{item.productName}</td>
                      <td className="px-5 py-4 text-center text-gray-800 text-base">{item.unit}</td>
                      <td className="px-5 py-4">
                        <input
                          type="number"
                          className="w-full bg-white text-gray-800 text-base border border-gray-300 rounded-lg px-4 py-2.5 text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
                          value={item.quantity}
                          onChange={(e) => handleItemChange(item.id, 'quantity', e.target.value)}
                        />
                      </td>
                      <td className="px-5 py-4 text-center">
                        <button
                          onClick={() => handleRemoveProduct(item.id)}
                          className="bg-red-500 hover:bg-red-600 text-white p-2.5 rounded-lg transition-colors"
                          title="Remove item"
                        >
                          <Minus className="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>

          {/* Action Button */}
          <div className="bg-black p-5">
            <div className="flex justify-end">
              <button
                onClick={handleGenerateDelivery}
                className="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold text-lg px-10 py-4 rounded-xl transition-all transform hover:scale-105 flex items-center gap-3 shadow-lg"
              >
                <Package className="w-6 h-6" />
                Generate Delivery Note
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DeliveryInvoiceForm;



const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<DeliveryInvoiceForm />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}