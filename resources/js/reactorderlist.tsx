import React, { useState, useEffect } from 'react';
import { Search, Plus, Truck, Eye, Trash2, FileText, X, Package, Calendar, User } from 'lucide-react';
import { createRoot } from 'react-dom/client';

const OrdersListPage = () => {
  const [filters, setFilters] = useState({
    customerId: '',
    fromDate: '',
    toDate: ''
  });

  const [showAddItemModal, setShowAddItemModal] = useState(false);
  const [showDeliveryModal, setShowDeliveryModal] = useState(false);
  const [selectedOrder, setSelectedOrder] = useState(null);
  const [selectedOrderItem, setSelectedOrderItem] = useState(null);
  const [statusMessage, setStatusMessage] = useState({ type: '', message: '' });
  const [productSearchTerm, setProductSearchTerm] = useState('');

  const [newItem, setNewItem] = useState({
    productId: '',
    unitId: '',
    price: '',
    quantity: '',
    totalPrice: '',
    reference: '',
    dueDate: ''
  });

  const [deliveryData, setDeliveryData] = useState({
    quantity: '',
    deliveryDate: '',
    notes: ''
  });

  // Mock data
  // const customers = [
  //   { id: 1, name: 'Acme Corporation' },
  //   { id: 2, name: 'Global Industries Ltd' },
  //   { id: 3, name: 'Tech Solutions Inc' }
  // ];

  // const products = [
  //   { id: 1, name: 'Product A - Premium Widget', unitPackId: 1, actualSellingPrice: 150.00 },
  //   { id: 2, name: 'Product B - Standard Component', unitPackId: 2, actualSellingPrice: 75.50 },
  //   { id: 3, name: 'Product C - Deluxe Assembly', unitPackId: 3, actualSellingPrice: 250.00 }
  // ];

  // const unitTypes = [
  //   { id: 1, name: 'Pieces' },
  //   { id: 2, name: 'Boxes' },
  //   { id: 3, name: 'Cartons' }
  // ];

    const [customers, ] =  useState(window.customersData || []);
  
    const [products, ] =   useState(window.productsData || []);
  
    const [unitTypes, ] =  useState(window.unitTypesData || []);
  

  const orders = [
    {
      id: 1,
      customerId: 1,
      reference: 'ORD-001',
      createdAt: '2025-01-15',
      items: [
        { id: 101, productId: 1, unitId: 1, quantity: 100, openingQty: 100, manufactured: 50, delivered: 30, orderBy: 1 },
        { id: 102, productId: 2, unitId: 2, quantity: 50, openingQty: 50, manufactured: 0, delivered: 0, orderBy: 0 }
      ]
    },
    {
      id: 2,
      customerId: 2,
      reference: 'ORD-002',
      createdAt: '2025-01-20',
      items: [
        { id: 201, productId: 3, unitId: 3, quantity: 75, openingQty: 75, manufactured: 75, delivered: 25, orderBy: 1 }
      ]
    }
  ];

  const filteredProducts = products.filter(product =>
    product.name.toLowerCase().includes(productSearchTerm.toLowerCase())
  );

  useEffect(() => {
    if (newItem.price && newItem.quantity) {
      const total = parseFloat(newItem.price) * parseFloat(newItem.quantity);
      setNewItem(prev => ({ ...prev, totalPrice: total.toFixed(2) }));
    }
  }, [newItem.price, newItem.quantity]);

  const handleProductChange = (productId) => {
    const selectedProduct = products.find(p => p.id === parseInt(productId));
    if (selectedProduct) {
      setNewItem(prev => ({
        ...prev,
        productId: productId,
        unitId: selectedProduct.unitPackId.toString(),
        price: selectedProduct.actualSellingPrice.toString()
      }));
    }
  };

  const handleAddItem = () => {
    setStatusMessage({ type: 'success', message: 'Item added successfully!' });
    setShowAddItemModal(false);
    setNewItem({
      productId: '',
      unitId: '',
      price: '',
      quantity: '',
      totalPrice: '',
      reference: '',
      dueDate: ''
    });
    setTimeout(() => setStatusMessage({ type: '', message: '' }), 3000);
  };

  const handleDelivery = () => {
    setStatusMessage({ type: 'success', message: 'Delivery recorded successfully!' });
    setShowDeliveryModal(false);
    setDeliveryData({ quantity: '', deliveryDate: '', notes: '' });
    setTimeout(() => setStatusMessage({ type: '', message: '' }), 3000);
  };

  const getCustomerName = (id) => customers.find(c => c.id === id)?.name || 'N/A';
  const getProductName = (id) => products.find(p => p.id === id)?.name || 'N/A';
  const getUnitName = (id) => unitTypes.find(u => u.id === id)?.name || 'N/A';

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-cyan-50 py-8 px-4">
      <div className="container mx-auto max-w-7xl">
        {/* Header */}
        <div className="mb-6">
          <h1 className="text-4xl font-bold text-gray-800 mb-2">Orders List</h1>
        </div>

        {/* Status Messages */}
        {statusMessage.message && (
          <div className={`mb-6 p-4 rounded-lg shadow-lg ${
            statusMessage.type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : 'bg-red-100 border-l-4 border-red-500 text-red-700'
          }`}>
            <button onClick={() => setStatusMessage({ type: '', message: '' })} className="float-right">
              <X size={20} />
            </button>
            <strong>{statusMessage.type === 'success' ? 'Success!' : 'Error!'}</strong> {statusMessage.message}
          </div>
        )}

        {/* Filters */}
        <div className="bg-white rounded-xl shadow-lg p-6 mb-8">
          <div className="grid md:grid-cols-4 gap-4">
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">Customer</label>
              <select
                value={filters.customerId}
                onChange={(e) => setFilters(prev => ({ ...prev, customerId: e.target.value }))}
                className="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
              >
                <option value="">--All Customers--</option>
                {customers.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select>
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
              <input
                type="date"
                value={filters.fromDate}
                onChange={(e) => setFilters(prev => ({ ...prev, fromDate: e.target.value }))}
                className="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
              />
            </div>
            <div>
              <label className="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
              <input
                type="date"
                value={filters.toDate}
                onChange={(e) => setFilters(prev => ({ ...prev, toDate: e.target.value }))}
                className="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
              />
            </div>
            <div className="flex items-end">
              <button className="w-full bg-cyan-500 hover:bg-cyan-600 text-white font-bold py-2 px-4 rounded-lg transition-all">
                <Search className="inline mr-2" size={18} />
                Search
              </button>
            </div>
          </div>
        </div>

        {/* Orders List */}
        <div className="space-y-6">
          {orders.map(order => (
            <div key={order.id} className="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-shadow duration-300 overflow-hidden">
              {/* Order Header */}
              <div className="bg-gradient-to-r from-teal-700 to-teal-600 text-white p-6">
                <div className="flex flex-wrap justify-between items-center gap-4">
                  <div className="flex items-center space-x-4">
                    <User size={24} />
                    <div>
                      <h3 className="text-2xl font-bold">{getCustomerName(order.customerId)}</h3>
                      <p className="text-teal-100">Order #{order.reference}</p>
                    </div>
                  </div>
                  <div className="flex items-center space-x-2">
                    <Calendar size={18} />
                    <span className="text-lg">{order.createdAt}</span>
                  </div>
                  <div className="flex flex-wrap gap-2">
                    <button
                      onClick={() => { setSelectedOrder(order); setShowAddItemModal(true); }}
                      className="bg-white text-teal-700 hover:bg-teal-50 px-4 py-2 rounded-lg font-semibold transition-all flex items-center"
                    >
                      <Plus size={18} className="mr-1" />
                      Add Items
                    </button>
                    <button className="bg-white text-teal-700 hover:bg-teal-50 px-4 py-2 rounded-lg font-semibold transition-all flex items-center">
                      <Eye size={18} className="mr-1" />
                      View
                    </button>
                    <button className="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg font-semibold transition-all flex items-center">
                      <Trash2 size={18} className="mr-1" />
                      Delete
                    </button>
                  </div>
                </div>
              </div>

              {/* Order Items */}
              <div className="p-6">
                <div className="space-y-4">
                  {order.items.map(item => {
                    const delivered = item.openingQty - item.quantity;
                    return (
                      <div key={item.id} className="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                        <div className="grid md:grid-cols-5 gap-4 items-center">
                          <div className="md:col-span-2">
                            <div className="flex items-center">
                              <Package className="text-purple-600 mr-2" size={20} />
                              <div>
                                <p className="font-bold text-gray-800">{getProductName(item.productId)}</p>
                                <p className="text-sm text-gray-600">{getUnitName(item.unitId)}</p>
                              </div>
                            </div>
                            {item.orderBy === 1 && (
                              <span className="inline-block mt-2 bg-blue-500 text-white text-xs px-2 py-1 rounded">Online</span>
                            )}
                          </div>

                          <div className="md:col-span-2">
                            <div className="grid grid-cols-3 gap-2 text-center">
                              <div>
                                <p className="text-xs text-gray-600">Quantity</p>
                                <p className={`font-bold ${item.openingQty < 0 ? 'text-red-600' : 'text-gray-800'}`}>
                                  {item.openingQty}
                                </p>
                              </div>
                              <div>
                                <p className="text-xs text-gray-600">Delivered</p>
                                <p className={`font-bold ${delivered < 0 ? 'text-red-600' : 'text-green-600'}`}>
                                  {delivered}
                                </p>
                              </div>
                              <div>
                                <p className="text-xs text-gray-600">Manufactured</p>
                                <p className={`font-bold ${item.manufactured < 0 ? 'text-red-600' : 'text-blue-600'}`}>
                                  {item.manufactured}
                                </p>
                              </div>
                            </div>
                          </div>

                          <div className="flex flex-wrap gap-2 justify-end">
                            <button className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition-all">
                              Update
                            </button>
                            <button
                              onClick={() => { setSelectedOrderItem(item); setShowDeliveryModal(true); }}
                              className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-all flex items-center"
                            >
                              <Truck size={14} className="mr-1" />
                              Delivery
                            </button>
                            {item.manufactured ? (
                              <button className="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-sm transition-all">
                                <FileText size={14} className="inline mr-1" />
                                View Jobcard
                              </button>
                            ) : (
                              <button className="bg-cyan-500 hover:bg-cyan-600 text-white px-3 py-1 rounded text-sm transition-all">
                                <FileText size={14} className="inline mr-1" />
                                Create Jobcard
                              </button>
                            )}
                            <button className="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-all">
                              Delete
                            </button>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Add Item Modal */}
        {showAddItemModal && (
          <div className="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
              <div className="bg-gradient-to-r from-purple-600 to-purple-500 text-white p-6 flex justify-between items-center">
                <div className="flex items-center">
                  <Plus size={28} className="mr-3" />
                  <h2 className="text-2xl font-bold">Add Order Item</h2>
                </div>
                <button onClick={() => setShowAddItemModal(false)} className="hover:bg-purple-700 rounded-full p-2 transition-colors">
                  <X size={24} />
                </button>
              </div>

              <div className="p-6">
                <div className="mb-4">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Product</label>
                  <input
                    type="text"
                    placeholder="Search product..."
                    value={productSearchTerm}
                    onChange={(e) => setProductSearchTerm(e.target.value)}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                  />
                  {productSearchTerm && (
                    <select
                      value={newItem.productId}
                      onChange={(e) => handleProductChange(e.target.value)}
                      className="w-full mt-2 px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                      size={4}
                    >
                      <option value="">-- select product --</option>
                      {filteredProducts.map(p => (
                        <option key={p.id} value={p.id}>{p.name}</option>
                      ))}
                    </select>
                  )}
                </div>

                <div className="mb-4">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Unit Type</label>
                  <select
                    value={newItem.unitId}
                    onChange={(e) => setNewItem(prev => ({ ...prev, unitId: e.target.value }))}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                  >
                    <option value="">-- select unit type --</option>
                    {unitTypes.map(u => <option key={u.id} value={u.id}>{u.name}</option>)}
                  </select>
                </div>

                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                    <input
                      type="number"
                      value={newItem.price}
                      onChange={(e) => setNewItem(prev => ({ ...prev, price: e.target.value }))}
                      className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                      placeholder="0.00"
                      step="0.01"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                    <input
                      type="number"
                      value={newItem.quantity}
                      onChange={(e) => setNewItem(prev => ({ ...prev, quantity: e.target.value }))}
                      className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                      placeholder="0"
                    />
                  </div>
                </div>

                <div className="mb-4">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Total Cost</label>
                  <input
                    type="number"
                    value={newItem.totalPrice}
                    readOnly
                    className="w-full px-4 py-3 border-2 border-green-200 rounded-lg bg-green-50 font-bold text-green-700 text-lg"
                  />
                </div>

                <div className="mb-4">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Reference</label>
                  <input
                    type="text"
                    value={newItem.reference}
                    onChange={(e) => setNewItem(prev => ({ ...prev, reference: e.target.value }))}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                  />
                </div>

                <div className="mb-6">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Due Date</label>
                  <input
                    type="date"
                    value={newItem.dueDate}
                    onChange={(e) => setNewItem(prev => ({ ...prev, dueDate: e.target.value }))}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                  />
                </div>

                <button
                  onClick={handleAddItem}
                  className="w-full bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white font-bold py-4 px-6 rounded-lg shadow-lg transition-all flex items-center justify-center"
                >
                  <Plus size={20} className="mr-2" />
                  Add Item
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Delivery Modal */}
        {showDeliveryModal && (
          <div className="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-2xl shadow-2xl max-w-lg w-full">
              <div className="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-6 flex justify-between items-center">
                <div className="flex items-center">
                  <Truck size={28} className="mr-3" />
                  <h2 className="text-2xl font-bold">Record Delivery</h2>
                </div>
                <button onClick={() => setShowDeliveryModal(false)} className="hover:bg-blue-700 rounded-full p-2 transition-colors">
                  <X size={24} />
                </button>
              </div>

              <div className="p-6">
                {selectedOrderItem && (
                  <div className="bg-gray-50 rounded-lg p-4 mb-6">
                    <p className="text-sm text-gray-600 mb-2">Order Item Details</p>
                    <p className="font-bold text-gray-800">{getProductName(selectedOrderItem.productId)}</p>
                    <p className="text-sm text-gray-600">{getUnitName(selectedOrderItem.unitId)}</p>
                    <p className="text-sm text-gray-600 mt-2">Available Quantity: <span className="font-bold">{selectedOrderItem.quantity}</span></p>
                  </div>
                )}

                <div className="mb-4">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Delivery Quantity</label>
                  <input
                    type="number"
                    value={deliveryData.quantity}
                    onChange={(e) => setDeliveryData(prev => ({ ...prev, quantity: e.target.value }))}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter quantity"
                  />
                </div>

                <div className="mb-4">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Delivery Date</label>
                  <input
                    type="date"
                    value={deliveryData.deliveryDate}
                    onChange={(e) => setDeliveryData(prev => ({ ...prev, deliveryDate: e.target.value }))}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div className="mb-6">
                  <label className="block text-sm font-semibold text-gray-700 mb-2">Notes (Optional)</label>
                  <textarea
                    value={deliveryData.notes}
                    onChange={(e) => setDeliveryData(prev => ({ ...prev, notes: e.target.value }))}
                    className="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    rows="3"
                    placeholder="Add any delivery notes..."
                  />
                </div>

                <button
                  onClick={handleDelivery}
                  className="w-full bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-bold py-4 px-6 rounded-lg shadow-lg transition-all flex items-center justify-center"
                >
                  <Truck size={20} className="mr-2" />
                  Save Delivery
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default OrdersListPage;




// Mount React
const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<OrdersListPage />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}