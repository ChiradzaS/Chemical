import React, { useState, useRef, useEffect } from 'react';
import { ChevronDown, Loader2, Package, Calculator } from 'lucide-react';
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
    urlParamsData: { customerId: string; productId: string }; // Add this
    
    
 
  }
}


// Types
interface Customer {
  id: number;
  name: string;
}

interface Product {
  id: number;
  name: string;
  unitPackId?: number;
  WeightPerProduct?: number;
  bagType?: number;
  thickness?: number;
  totalWidth?: number;
  product_length?: number;
  color?: number;
  materialTypeId?: number;
  product_Width?: number;
  workInProgressId?: number;
  avgWeightPerProduct?: number;
  gussetWidth?: number;
  image_path?: string;
}

interface Products {
  id: number;
  name: string;
  unitPackId?: number;
  WeightPerProduct?: number;
  bagType?: number;
  thickness?: number;
  totalWidth?: number;
  product_length?: number;
  color?: number;
  materialTypeId?: number;
  product_Width?: number;
  workInProgressId?: number;
  avgWeightPerProduct?: number;
  gussetWidth?: number;
  image_path?: string;
}

interface UnitType {
  id: number;
  name: string;
  value: number;
}

interface MaterialType {
  id: number;
  name: string;

}

interface BagType {
  id: number;
  name: string;
}

interface ColourType {
  id: number;
  name: string;
}

interface ProcessType {
  id: number;
  name: string;
}

interface ProcessData {
  [key: string]: {
    checked: boolean;
    productId: string;
    quantity: string;
    unitId: string;
    name: string;
  };
}

const JobCardCreator: React.FC = () => {


    
        const [machineName, ]       = useState(window.machineTypesData || []);
        const [products, ]          = useState(window.productsData || []);
        const [product, ]           = useState(window.product || []);
        const [customers, ]         = useState(window.customersData || []);
        const [processTypes, ]      = useState(window.processTypesData || []);
        const [colourTypes, ]       = useState(window.colourTypesData || []);
        const [bagTypes, ]          = useState(window.bagTypesData || []);
        const [unitTypes, ]         = useState(window.unitTypesData || []);
        const [materialTypes, ]     = useState(window.materialTypesData || []);
        

    

 

  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    customerId: '',
    productId: '',
    unitId: '',
    quantity: '',
    totalQuantity: '',
    weightPerProduct: '',
    avgWeightPerProduct: '',
    productWidth: '',
    gussetWidth: '',
    totalWidth: '',
    materialTypeId: '',
    bagType: '',
    color: '',
    thickness: '',
    productLength: '',
    testWeight: '',
    barcode: '',
    workInProgressId: '',
    orderId: '',
    startDate: '',
  });

  const [processData, setProcessData] = useState<ProcessData>({});

  // Initialize process data
  useEffect(() => {
    const initialProcessData: ProcessData = {};
    processTypes.forEach((process, index) => {
      const key = `${process.name}_${index + 1}`;
      initialProcessData[key] = {
        checked: false,
        productId: '',
        quantity: '',
        unitId: '',
        name: '',

      };
    });
    setProcessData(initialProcessData);
  }, [processTypes]);

  // Generate barcode
  const generateBarcode = () => {
    const uniqueId = Math.random().toString(36).substr(2, 22);
    const timestamp = Date.now().toString();
    const barcode = (uniqueId + timestamp).substr(7, 11);
    setFormData(prev => ({ ...prev, barcode }));
  };



  
useEffect(() => {
  const urlParams = window.urlParamsData || { customerId: '', productId: '' };

   
  
  if (urlParams.customerId) {
    handleInputChange('customerId', urlParams.customerId);
   
  }
  
  if (urlParams.productId) {
    // Find the product by ID to get its name
   const selectedProduct = products.find(p => p.id.toString() === urlParams.productId.toString());

     

    
    
    if (selectedProduct) {
      handleInputChange('productId', urlParams.productId);
      handleProductNameChange(selectedProduct.name);
      
    }
  }
}, []); // Empty dependency array - runs once on mount

// Define the API URL
const API_URL = '/LaravelCRUD/order_items/getProductbyid';


// Handle product change
const handleProductChange = async (productId: string) => {
  if (!productId || productId === '0') return;

  // Update the formData with the selected productId
  setFormData(prev => ({ ...prev, productId }));

  let productData = null;
try {
  
  const response = await axios.get(`/LaravelCRUD/qryplasticmaterials/getProductdetails?data=${productId}`);
  
  // The API response's data is an object with a `data` key that holds the array
  // So you need to access response.data.data
  const apiResponseData = response.data.data; 

  if (apiResponseData && apiResponseData.length > 0) {
    // Access the first object in the array
    productData = apiResponseData[0];
  } else {
    // Handle the case where the API returned an empty array
    productData = null;
  }

} catch (error) {
    console.error("Error fetching product data:", error);
    alert('Failed to load product data. Please try again.');
    return;
  }

  if (productData) {
    // Set the form data from the API response
    setFormData(prev => ({
      ...prev,
      unitId: productData.unitPackId?.toString() || '',
      weightPerProduct: productData.WeightPerProduct?.toString() || '',
      bagType: productData.bagType?.toString() || '',
      thickness: productData.thickness?.toString() || '',
      totalWidth: productData.totalWidth?.toString() || '',
      productLength: productData.product_length?.toString() || '',
      color: productData.color?.toString() || '',
      materialTypeId: productData.materialTypeId?.toString() || '',
      productWidth: productData.product_Width?.toString() || '',
      workInProgressId: productData.workInProgressId?.toString() || '',
      avgWeightPerProduct: productData.avgWeightPerProduct?.toString() || '',
      gussetWidth: productData.gussetWidth?.toString() || '',
      image_path : productData.image_path?.toString() || '',

    }));

    // Check if an image path exists for printing process
    if (productData.image_path) {
      setProcessData(prev => ({
        ...prev,
        'Printing_2': { ...prev['Printing_2'], checked: true }
      }));
      alert('Please NOTE!!! This jobcard will have a printing process');
    }

        setTimeout(() => {
  if (quantityInputRef.current) {
    quantityInputRef.current.focus();
    quantityInputRef.current.select(); // This will highlight all text
  }
}, 100);


  }
};

  // Calculate function
  const calculate = () => {
    if (!formData.quantity) {
      alert('Please insert the quantity');
      return;
    }

    if (!formData.customerId || formData.customerId === '0') {
      alert('Please select Customer');
      return;
    }

    if (!formData.productId || formData.productId === '0') {
      alert('Please select the product');
      return;
    }


    if (!formData.barcode) {
      generateBarcode();
    }

    setLoading(true);

    // Get unit value
    const selectedUnit = unitTypes.find(unit => unit.id.toString() === formData.unitId);
    const unitValue = selectedUnit?.value || 1 ;


    
    const quantity = parseFloat(formData.quantity);
    const totalQnt = unitValue * quantity;
    const weightPerProduct = parseFloat(formData.weightPerProduct) || 0;
    const width = parseFloat(formData.totalWidth) || 0;
    const micron = parseFloat(formData.thickness) || 0;
    const length = parseFloat(formData.productLength) || 0;

    // Calculations
    const qntPer1000 = totalQnt / 1000;
    const weightperQntIn1000 = qntPer1000 * weightPerProduct;
    const perc = 0.03 * weightperQntIn1000;
    const finalTotal = perc + weightperQntIn1000;
    const finalTotalz = quantity * weightPerProduct;
    const testingWeight = (micron * width) / 5600;
    const centerfold = weightPerProduct * quantity;

    // Update form data
    setFormData(prev => ({
      ...prev,
      totalQuantity: totalQnt.toString(),
      testWeight: testingWeight.toFixed(4),
      startDate: new Date().toISOString().split('T')[0],
    }));

    // Process calculations
    const bagTypeText = bagTypes.find(bt => bt.id.toString() === formData.bagType)?.name || '';
    const updatedProcessData = { ...processData };

    Object.keys(processData).forEach(key => {
      const [processName, processNumber] = key.split('_');
      
      if (bagTypeText.trim() === 'Rolls') {
        if (processName === 'Extruding') {
          updatedProcessData[key] = {
            ...updatedProcessData[key],
            checked: true,
            productId: formData.productId,
            quantity: finalTotalz.toString(),
            unitId: '52',
            name : 'Extruding',
          };
        }
      } else if (bagTypeText.trim() === 'Centre Fold') {
        if (processName === 'Extruding') {
          updatedProcessData[key] = {
            ...updatedProcessData[key],
            checked: true,
            productId: formData.productId,
            quantity: centerfold.toString(),
            unitId: '52',
            name : 'Extruding',
          };
        } else if (processName === 'Bagging') {
          updatedProcessData[key] = {
            ...updatedProcessData[key],
            checked: true,
            productId: formData.productId,
            quantity: formData.quantity,
            unitId: formData.unitId,
            name : 'Bagging',
          };
        }
      } else {
        if (processName === 'Bagging') {
          updatedProcessData[key] = {
            ...updatedProcessData[key],
            checked: true,
            productId: formData.productId,
            quantity: formData.quantity,
            unitId: formData.unitId,
            name : 'Bagging',
          };
        } else if (processName === 'Extruding'   ) {
          updatedProcessData[key] = {
            ...updatedProcessData[key],
            checked: true,
            productId: formData.workInProgressId,
            quantity: finalTotal.toString(),
            unitId: '52',
            name : 'Extruding',
          };
        } else if (processName === 'Packing') {
          updatedProcessData[key] = {
            ...updatedProcessData[key],
            productId: formData.productId,
          };
        }
      }
    });

    setProcessData(updatedProcessData);

    setTimeout(() => {
      setLoading(false);
    }, 1000);
  };


const [isProcessing, setIsProcessing] = useState(false);
const [notificationMessage, setNotificationMessage] = useState("");
  

const handleSubmit = async (e: React.MouseEvent) => {
  e.preventDefault();


  // Show processing notification
  const showProcessing = () => {
    // You can replace this with your preferred notification method
    // Examples: toast notification, modal, or state update
   // alert("Processing... Please wait while we recalculate your data.");
    // Or if using a state-based notification:

    setIsProcessing(true);
    setNotificationMessage("Processing... Please wait while we recalculate your data.");

  };
  
  // Hide processing notification
  const hideProcessing = () => {

    setIsProcessing(false);
     setNotificationMessage("");

  };

  try {
    // Start processing notification
    showProcessing();
    
    // Start both the calculation and the minimum 3-second timer
    const calculationPromise = (async () => {
      await calculate(); // Wait for calculate to complete if it's async
    })();
    
    const minDelayPromise = new Promise<void>((resolve) => {
      setTimeout(resolve, 3000); // 3 seconds minimum
    });
    
    // Wait for both calculation and minimum delay to complete
    await Promise.all([calculationPromise, minDelayPromise]);
    
    // Hide processing notification
    hideProcessing();

    // Build job card main data
    const jobCardData = {
      action: "create",
      qnt: formData.quantity,
      productId: formData.productId,
      unitId: formData.unitId,
      bagType: formData.bagType,
      customerId: formData.customerId,
      barcode: formData.barcode,
      orderId: formData.orderId,
      image_path: formData.imagePath,
      userId: formData.userId,
    };

    // Collect jobcard items from processData
    const items: any[] = [];
    Object.keys(processData).forEach((key) => {
      const p = processData[key];
      if (p.checked) {
        items.push({
          processId: p.name,
          productId: p.productId,
          qnt: p.quantity,
          unitId: p.unitId,
          bagType: p.bagType,
          barcode: p.barcode,
          name: p.name,
        });
      }
    });

    // Wrap everything
    const payload = {
      jobCard: jobCardData,
      items: items,
    };

    // Encode as query string
    const encodedData = encodeURIComponent(JSON.stringify(payload));

    // Save to server
    const response = await axios.get(
      `/LaravelCRUD/qryjobcards/store?data=${encodedData}`
    );

    //alert("Job card saved successfully!");
    
    // Redirect to job card list page
    window.location.href = '/LaravelCRUD/reactjoblist';
  } catch (error) {
    hideProcessing(); // Make sure to hide processing notification on error
    console.error(error);
    alert("Error saving job card! Please retry");
  }
};



  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleProcessChange = (key: string, field: string, value: string | boolean) => {
    setProcessData(prev => ({
      ...prev,
      [key]: { ...prev[key], [field]: value }
    }));
  };

    // Clear product input
  const clearProductInput = () => {
    setFormData(prev => ({
      ...prev,
      productName: '',
      productId: '0'
    }));
  };

   const quantityInputRef = useRef(null);

  // Handle product selection from datalist
  const handleProductNameChange = (productName) => {
    const selectedProduct = products.find(product => product.name === productName);
   
    
    setFormData(prev => ({
      ...prev,
      productName: productName,
      productId: selectedProduct ? selectedProduct.id.toString() : '0'
    }));

    // If a valid product is selected, call handleProductChange
    if (selectedProduct) {
  
      handleProductChange(selectedProduct.id.toString());
    }


  };

  return (
    
        <div className="min-h-screen bg-gray-50 p-6">
            <div className="max-w-7xl mx-auto">
                <div className="w-full p-6 bg-white rounded-lg shadow-lg">
          <div className="border-b border-gray-200 pb-4 mb-6">
            <h1 className="text-3xl font-bold text-gray-900 flex items-center gap-3">
              <Package className="text-blue-600" />
              Create Jobcards
            </h1>
          </div>

          <div className="space-y-6">
            {/* Customer Selection */}
            <div className="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
              <div className="flex items-center gap-4">
                <label className="text-lg font-semibold text-gray-700">Customer:</label>
                <div className="relative">
                  <select
                    value={formData.customerId}
                    onChange={(e) => handleInputChange('customerId', e.target.value)}
                    className="appearance-none bg-white border-2 border-gray-300 rounded-lg px-4 py-2 pr-8 min-w-64 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                  >
                    <option value="0">----Select Customer----</option>
                    {customers.map(customer => (
                      <option key={customer.id} value={customer.id}>{customer.name}</option>
                    ))}
                  </select>
                  <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                </div>
              </div>
              <button
                type="button"
                onClick={calculate}
                disabled={loading}
                className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2 disabled:opacity-50"
              >
                {loading ? <Loader2 className="w-4 h-4 animate-spin" /> : <Calculator className="w-4 h-4" />}
                Calculate
              </button>
            </div>

          {/* Product Selection */}
            <div className="flex items-center justify-between bg-blue-50 p-4 rounded-lg">
              <div className="flex items-center gap-4">
                <label className="text-lg font-semibold text-gray-700">Product:</label>
                <div className="relative">
                  <input
                    list="products"
                    value={formData.productName}
                    onChange={(e) => handleProductNameChange(e.target.value)}
                    onClick={clearProductInput}
                    placeholder="---- Search Product ----"
                    className="appearance-none bg-white border-2 border-gray-300 rounded-lg px-4 py-2 pr-8 min-w-96 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                  />
                  <datalist id="products">
                    {products.map(product => (
                      <option key={product.id} value={product.name} />
                    ))}
                  </datalist>
                  <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 pointer-events-none" />
                </div>
              </div>
              {formData.productId !== '0' && (
                <div className="text-sm text-green-600 font-medium">
                  ✓ Product Selected (ID: {formData.productId})
                </div>
              )}
            </div>

            {/* Product Details */}
            <div className="border border-gray-200 rounded-lg overflow-hidden">
              <div className="bg-gray-800 text-white p-3">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                    <label className="block text-sm font-medium mb-2">Product Name</label>
                    <div className="relative">
                      <select
                        value={formData.productId}
                        disabled
                        onChange={(e) => handleProductChange(e.target.value)}
                        className="appearance-none bg-white text-gray-900 border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                      >
                        <option value="0">----Select Product----</option>
                        {products.map(product => (
                          <option key={product.id} value={product.id}>{product.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium mb-2">Package</label>
                    <div className="relative">
                      <select
                        value={formData.unitId}
                        disabled
                        onChange={(e) => handleInputChange('unitId', e.target.value)}
                        className="appearance-none bg-white text-gray-900 border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                      >
                        <option value="">-- select unit --</option>
                        {unitTypes.map(unit => (
                          <option key={unit.id} value={unit.id}>{unit.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                  </div>

            {/* Quantity Input Section */}
          
          
                <div>
                  <label className="block text-sm font-medium mb-2">Qnt (no of bales)</label>
                  <input
                    ref={quantityInputRef}
                    type="number"
                    value={formData.quantity || ''}
                    onChange={(e) => handleInputChange('quantity', e.target.value)}
                    className="bg-white text-gray-900 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:bg-yellow-50"
                  />
                </div>

                  <div>
                    <label className="block text-sm font-medium mb-2">Qnt per Unit</label>
                    <input
                      type="text"
                      value={formData.totalQuantity}
                      readOnly
                      className="bg-gray-100 text-gray-900 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm cursor-not-allowed"
                    />
                  </div>
                </div>
              </div>

              <div className="p-4">
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Weight Per Product (kg's per 1000)</label>
                    <input
                      type="text"
                      readOnly
                      value={formData.weightPerProduct}
                      onChange={(e) => handleInputChange('weightPerProduct', e.target.value)}
                      className="border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                    />
                    <div className="mt-2">
                      <label className="block text-sm font-medium text-gray-700 mb-1">Avg Weight/Product</label>
                      <input
                        type="text"
                        readOnly
                        value={formData.avgWeightPerProduct}
                        onChange={(e) => handleInputChange('avgWeightPerProduct', e.target.value)}
                        className="border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Width (mm)</label>
                    <input
                      type="text"
                      readOnly
                      value={formData.productWidth}
                      onChange={(e) => handleInputChange('productWidth', e.target.value)}
                      className="border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Gusset Width (mm)</label>
                    <input
                      type="text"
                      readOnly
                      value={formData.gussetWidth}
                      onChange={(e) => handleInputChange('gussetWidth', e.target.value)}
                      className="border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                    />
                  </div>



                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Total Width (mm)</label>
                    <input
                      type="text"
                      readOnly
                      value={formData.totalWidth}
                      onChange={(e) => handleInputChange('totalWidth', e.target.value)}
                      className="border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Material Type</label>
                    <div className="relative">
                      <select
                        value={formData.materialTypeId}
                        dias
                        onChange={(e) => handleInputChange('materialTypeId', e.target.value)}
                        className="appearance-none border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                      >
                        <option value="">-- select material name --</option>
                        {materialTypes.map(material => (
                          <option key={material.id} value={material.id}>{material.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                    <div className="mt-2">
                      <label className="block text-sm font-medium text-gray-700 mb-1">Bag Type</label>
                      <div className="relative">
                        <select
                          value={formData.bagType}
                          disabled
                          onChange={(e) => handleInputChange('bagType', e.target.value)}
                          className="appearance-none border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                        >
                          <option value="">-- select bagType --</option>
                          {bagTypes.map(bagType => (
                            <option key={bagType.id} value={bagType.id}>{bagType.name}</option>
                          ))}
                        </select>
                        <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                      </div>
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Colour</label>
                    <div className="relative">
                      <select
                        value={formData.color}
                        disabled
                        onChange={(e) => handleInputChange('color', e.target.value)}
                        className="appearance-none border-2 border-gray-300 rounded px-3 py-2 pr-8 w-full shadow-sm"
                      >
                        <option value="">-- select color name --</option>
                        {colourTypes.map(color => (
                          <option key={color.id} value={color.id}>{color.name}</option>
                        ))}
                      </select>
                      <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                    </div>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Thickness (mic)</label>
                    <input
                      type="text"
                      value={formData.thickness}
                      readOnly
                      onChange={(e) => handleInputChange('thickness', e.target.value)}
                      className="border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">Testing Weight</label>
                    <input
                      type="text"
                     
                      value={formData.testWeight}
                      readOnly
                      className="bg-gray-100 border-2 border-gray-300 rounded px-3 py-2 w-full shadow-sm"
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* Barcode Display */}
            {formData.barcode && (
              <div className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div className="flex items-center gap-3">
                  <Package className="text-blue-600" />
                  <span className="font-medium text-blue-900">Barcode:</span>
                  <span className="font-mono text-lg text-blue-800">{formData.barcode}</span>
                </div>
              </div>
            )}

            {/* Process Types */}
            <div className="space-y-4">
              <h3 className="text-lg font-semibold text-gray-900">Process Types</h3>
              {processTypes.map((process, index) => {
                const key = `${process.name}_${index + 1}`;
                const processInfo = processData[key] || { checked: false, productId: '', quantity: '', unitId: '' };
                
                return (
                  <div key={process.id} className="border border-gray-200 rounded-lg overflow-hidden">
                    <div className="bg-gray-800 text-white p-3">
                      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                        <div className="flex items-center gap-3">
                          <input
                            type="checkbox"
                            readOnly
                            checked={processInfo.checked}
                            onChange={(e) => handleProcessChange(key, 'checked', e.target.checked)}
                            className="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                          />
                          <span className="font-medium">{process.name}</span>
                        </div>

                        <div>
                          <label className="block text-sm font-medium mb-1">Product</label>
                          <div className="relative">
                            <select
                              value={processInfo.productId}
                              readOnly
                              onChange={(e) => handleProcessChange(key, 'productId', e.target.value)}
                              disabled
                              className="appearance-none bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 pr-8 w-full text-sm disabled:bg-gray-100"
                            >
                              <option value="">-- select Product --</option>
                              {product.map(product => (
                                <option key={product.id} value={product.id}>{product.name}</option>
                              ))}
                            </select>
                            <ChevronDown className=" appearance-none absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                          </div>
                        </div>

                        <div>
                          <label className="block text-sm font-medium mb-1">Quantity</label>
                          <input
                            type="text"
                            value={processInfo.quantity}
                            readOnly
                            onChange={(e) => handleProcessChange(key, 'quantity', e.target.value)}
                            disabled={!processInfo.checked}
                            className="bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 w-full text-sm disabled:bg-gray-100"
                          />
                        </div>

                        <div>
                          <label className="block text-sm font-medium mb-1">Unit</label>
                          <div className="relative">
                            <select
                              value={processInfo.unitId}
                              readOnly
                              onChange={(e) => handleProcessChange(key, 'unitId', e.target.value)}
                              disabled
                              className="appearance-none bg-white text-gray-900 border border-gray-300 rounded px-3 py-2 pr-8 w-full text-sm disabled:bg-gray-100"
                            >
                              <option value="">-- select unit --</option>
                              {unitTypes.map(unit => (
                                <option key={unit.id} value={unit.id}>{unit.name}</option>
                              ))}
                            </select>
                            <ChevronDown className="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>

            {/* Submit Button */}
            <div className="flex justify-center pt-6">
              <button
                type="button"
                onClick={handleSubmit}
                disabled={loading}
                className="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2 disabled:opacity-50 shadow-lg hover:shadow-xl transform hover:scale-105"
              >
                {loading ? <Loader2 className="w-5 h-5 animate-spin" /> : null}
                SAVE
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Loading Spinner Overlay */}
      {loading && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white p-6 rounded-lg shadow-xl">
            <div className="flex items-center gap-3">
              <Loader2 className="w-6 h-6 animate-spin text-blue-600" />
              <span className="text-lg font-medium">Calculating...</span>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default JobCardCreator;

// Mount React
const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<JobCardCreator />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}