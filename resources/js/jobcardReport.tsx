import React, { useState, useEffect } from 'react';
import { Search, Calendar, Package, Recycle, AlertCircle, Loader2, RefreshCw, Download } from 'lucide-react';
import { createRoot } from 'react-dom/client';
import { ChevronDown, ChevronRight } from 'lucide-react';
import axios from 'axios';


const PlasticMaterialsSearch = () => {
  const [searchTerm, setSearchTerm] = useState('');
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [selectedMaterial, setSelectedMaterial] = useState(null);
  const [plasticMaterials, setPlasticMaterials] = useState([]);
  const [loading, setLoading] = useState(false);
  const [apiError, setApiError] = useState(null);
  const [fetchingCategories, setFetchingCategories] = useState(new Set());
  const [fetchingAll, setFetchingAll] = useState(false);
  const [materialCategories, setMaterialCategories] = useState([]);

  // Fetch all materials to create categories
  const fetchMaterials = async (setMaterialData, setApiError) => {
    try {
      const response = await fetch(`/LaravelCRUD/qryplasticmaterials/index`);
      
      if (!response.ok) {
        throw new Error(`Server error: ${response.status} - ${response.statusText}`);
      }
      
      const data = await response.json();
      
      if (!Array.isArray(data)) {
        throw new Error('Invalid materials data received from API');
      }

      console.log('Received all materials:', data);

      // Create individual category for each material
      const materialCategories = data.map(material => {
        return generateCategoryProperties(material);
      });

      console.log('Generated categories (one per material):', materialCategories);

      setMaterialData(materialCategories);
      setApiError(null);
    } catch (error) {
      console.error('Failed to load materials:', error);
      setApiError('Failed to load materials from API');
      setMaterialData([]);
    }
  };

  // Helper function to get color based on material characteristics
  const getColorForMaterial = (materialName, description) => {
    const desc = description?.toLowerCase() || '';
    
if (desc.includes('ac')) return 'bg-gray-400'; // silver-like
if (desc.includes('smokey')) return 'bg-amber-300'; // light brown
if (desc.includes('virgin')) return 'bg-green-600'; ; // transparent-like
if (desc.includes('r/c') || desc.includes('recycled')) return 'bg-green-600'; // green
if (desc.includes('black')) return 'bg-neutral-900'; // dark black
if (desc.includes('white')) return 'bg-green-600'; ; // grey
if (desc.includes('yellow')) return 'bg-yellow-400'; // yellow
if (desc.includes('red') || desc.includes('fed')) return 'bg-red-700'; // blood red
if (desc.includes('grey')) return 'bg-gray-300'; // light grey
if (desc.includes('beige')) return 'bg-amber-100'; // beige
if (desc.includes('light')) return 'bg-yellow-100'; // light yellow


    return 'bg-gray-600';
  };

  // Helper function to generate category properties for each individual material
  const generateCategoryProperties = (material) => {
    return {
      id: material.id.toString(),
      name: material.name,
      type: material.name,
      color: getColorForMaterial(material.name, material.description),
      endpoint: material.id,
      data: [material],
      description: material.description,
      value: material.value,
      level: material.level,
      parentKey: material.parentKey,
      groupType: material.groupType,
      topValue: material.topValue,
      childType: material.childType,
      label: material.label,
      created_at: material.created_at,
      updated_at: material.updated_at
    };
  };

  // Use useEffect to call fetchMaterials when the component mounts
  useEffect(() => {
    fetchMaterials(setMaterialCategories, setApiError);
  }, []);

  // Handle date changes
  const handleFromDateChange = (e) => {
    setFromDate(e.target.value);
  };

  const handleToDateChange = (e) => {
    setToDate(e.target.value);
  };

  // Fetch materials for a specific category with production data
  const fetchCategoryMaterials = async (category) => {
    setFetchingCategories(prev => new Set([...prev, category.id]));
    setApiError(null);

    try {
      // Make the second API call to get production data
      const response = await fetch(`/LaravelCRUD/qryplasticmaterials/index?category=${category.endpoint}&from_date=${fromDate}&to_date=${toDate}`);
      
      if (!response.ok) {
        throw new Error(`Server error: ${response.status} - ${response.statusText}`);
      }
      
      const data = await response.json();
      //console.log('Production data response:', data);
      
      // Extract the production data from your API response format
      const productionData = {
        from_date: data.from_date,
        to_date: data.to_date,
        production_sum: data.production_sum || 0,
        bags_sum: data.bags_sum || 0,
        production_sum_production: data.production_sum_production || 0,
        bags_sum_production: data.bags_sum_production || 0,
        types: data.types || []
      };

      // Create material object with production data
      const materialWithProduction = {
        ...category.data[0], // Original material data
        category: category.type,
        categoryId: category.id,
        fetchedAt: new Date().toISOString(),
        // Production data from second API
        jobcardProductionKgs: productionData.production_sum,
        jobcardBags: productionData.bags_sum,
        //productionKgs: productionData.production_sum_production,
        //productionBags: productionData.bags_sum_production,
        productionData: productionData,
        types: productionData.types
      };

      setPlasticMaterials(prev => {
        // Remove existing materials from this category
        const filtered = prev.filter(m => m.categoryId !== category.id);
        // Add new material with production data
        return [...filtered, materialWithProduction];
      });
      
    } catch (error) {
      console.error(`Failed to load ${category.name}:`, error);
      setApiError(`Failed to load ${category.name}: ${error.message}`);
    } finally {
      setFetchingCategories(prev => {
        const newSet = new Set(prev);
        newSet.delete(category.id);
        return newSet;
      });
    }
  };

  // Fetch all categories one by one
  const fetchAllMaterials = async () => {
    setFetchingAll(true);
    setApiError(null);
    setPlasticMaterials([]); // Clear existing data
    
    for (const category of materialCategories) {
      try {
        await fetchCategoryMaterials(category);
        // Add a small delay between requests to avoid overwhelming the server
        await new Promise(resolve => setTimeout(resolve, 800));
      } catch (error) {
        console.error(`Failed to fetch ${category.name}:`, error);
      }
    }
    
    setFetchingAll(false);
  };

  // Filter materials based on search term and date range
  const filteredMaterials = plasticMaterials.filter(material => {
    const matchesSearch = material.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                          material.type?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                          material.category?.toLowerCase().includes(searchTerm.toLowerCase());
    
    let matchesDate = true;
    if (fromDate && toDate && material.fetchedAt) {
      const materialDate = new Date(material.fetchedAt);
      matchesDate = materialDate >= new Date(fromDate) && materialDate <= new Date(toDate);
    }
    
    return matchesSearch && matchesDate;
  });

  // Get appropriate icon based on material type
  const getMaterialIcon = (material) => {
    return material.isVirgin ? <Package className="w-6 h-6" /> : <Recycle className="w-6 h-6" />;
  };

  // Get appropriate color class
  const getMaterialColor = (material) => {
    if (material.color) return material.color;
    
    const category = materialCategories.find(cat => cat.id === material.categoryId);
    return category ? category.color : 'bg-gray-400';
  };

  // Get text color based on background
  const getTextColor = (material) => {
    const lightColors = ['bg-gray-100', 'bg-amber-200', 'bg-yellow-400', 'bg-gray-200', 'bg-gray-300'];
    const materialColor = getMaterialColor(material);
    return lightColors.includes(materialColor) ? 'text-gray-800' : 'text-white';
  };

  // Get materials count per category
  const getCategoryCount = (categoryId) => {
    return plasticMaterials.filter(m => m.categoryId === categoryId).length;
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
      <div className="max-w-full mx-auto h-screen flex flex-col">
        {/* Header */}
        <div className="text-center mb-4">
          <h1 className="text-3xl font-bold text-gray-800 mb-2">
            Plastic Materials Usage as per Production
          </h1>
          <p className="text-gray-600">
            Fetch and explore different categories of plastic materials
          </p>
        </div>

        {/* Controls */}
        <div className="bg-white rounded-xl shadow-lg p-4 mb-4">
          <div className="flex flex-col lg:flex-row gap-4 items-center">
            {/* Date Range */}
            <div className="flex flex-col sm:flex-row gap-4 items-center">
              <div className="flex items-center gap-2">
                <Calendar className="w-5 h-5 text-gray-500" />
                <label className="text-gray-700 font-medium">From:</label>
                <input
                  type="date"
                  value={fromDate}
                  onChange={handleFromDateChange}
                  className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
              <div className="flex items-center gap-2">
                <label className="text-gray-700 font-medium">To:</label>
                <input
                  type="date"
                  value={toDate}
                  onChange={handleToDateChange}
                  className="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>

            {/* Search Bar */}
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                type="text"
                placeholder="Search plastic materials..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            {/* Fetch All Button */}
            <button
              onClick={fetchAllMaterials}
              disabled={fetchingAll}
              className="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {fetchingAll ? <Loader2 className="w-4 h-4 animate-spin" /> : <Download className="w-4 h-4" />}
              {fetchingAll ? 'Fetching All...' : 'Fetch All Categories'}
            </button>
          </div>
        </div>

        {/* API Error Message */}
        {apiError && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div className="flex items-center gap-2">
              <AlertCircle className="w-5 h-5 text-red-500" />
              <p className="text-red-700">{apiError}</p>
            </div>
          </div>
        )}

        {/* Material Categories */}
        <div className="bg-white rounded-xl shadow-lg p-6 mb-4">
          <h2 className="text-xl font-semibold text-gray-800 mb-4">Material Categories</h2>
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            {materialCategories.map((category) => (
              <div
                key={category.id}
                className="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div className="flex items-center justify-between mb-3">
                  <div className="flex items-center gap-3">
                    <div className={`${category.color} w-4 h-4 rounded-full`}></div>
                    <h3 className="font-semibold text-gray-800">{category.name}</h3>
                  </div>
                  <span className="text-sm text-gray-500">
                    {getCategoryCount(category.id)} items
                  </span>
                </div>
                <button
                  onClick={() => fetchCategoryMaterials(category)}
                  disabled={fetchingCategories.has(category.id) || fetchingAll}
                  className="w-full flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {fetchingCategories.has(category.id) ? (
                    <>
                      <Loader2 className="w-4 h-4 animate-spin" />
                      Fetching...
                    </>
                  ) : (
                    <>
                      <RefreshCw className="w-4 h-4" />
                      Fetch Data
                    </>
                  )}
                </button>
              </div>
            ))}
          </div>
        </div>

        {/* Main Content Area */}
        <div className="flex-1 flex flex-col">
          {/* Material Cards */}
          {filteredMaterials.length > 0 && (
            <div className="mb-4">
              <h3 className="text-xl font-semibold text-gray-800 mb-3">
                Materials ({filteredMaterials.length})
              </h3>
              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                {filteredMaterials.map((material) => (
                  <div
                    key={`${material.categoryId}-${material.id}`}
                    className={`${getMaterialColor(material)} ${getTextColor(material)} rounded-xl p-3 shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 cursor-pointer`}
                    // onClick={() => setSelectedMaterial(material)}
                  >
                    <div className="flex flex-col items-center text-center">
                      <div className="mb-2 opacity-90">
                        {getMaterialIcon(material)}
                      </div>
                      <h1 className="text-lg font-semibold mb-1">
                        {material.name}
                      </h1>

                      <div className="text-m space-y-2">
                        <div className="bg-white bg-opacity-20 rounded p-6">
                          <p className="text-m opacity-75 mb-1">Jobcard Totals</p>
                          <p className="text-l opacity-75 mb-1">Extruding: {material.jobcardProductionKgs || 0} kg</p>
                          <p className="text-l opacity-75 mb-1">Bagging: {material.jobcardBags || 0}</p>
                        </div>
                        {/* <div className="bg-white bg-opacity-20 rounded p-2">
                          <p className="text-xs opacity-75 mb-1">Production Totals</p>
                          <p>Prod: {material.productionKgs || 0} kg</p>
                          <p>Bags: {material.productionBags || 0}</p>
                        </div> */}
                      </div>
                      {/* <button className="bg-white bg-opacity-20 hover:bg-opacity-30 px-2 py-1 rounded text-xs font-medium transition-colors mt-2">
                        View Details
                      </button> */}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Description and Details Panel */}
          <div className="flex-1 bg-white rounded-xl shadow-lg p-6">
            {selectedMaterial ? (
              <div className="h-full">
                <div className="flex items-center justify-between mb-4">
                  <h2 className="text-2xl font-bold text-gray-800">
                    {selectedMaterial.name} Details
                  </h2>
                  <button
                    onClick={() => setSelectedMaterial(null)}
                    className="text-gray-500 hover:text-gray-700 text-xl font-bold px-2"
                  >
                    ×
                  </button>
                </div>
                
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <div>
                    <h3 className="text-lg font-semibold mb-3">Material Information</h3>
                    <p className="text-gray-700 mb-2"><strong>Category:</strong> {selectedMaterial.category}</p>
                    <p className="text-gray-700 mb-2"><strong>Type:</strong> {selectedMaterial.type}</p>
                    <p className="text-gray-600 mb-4">{selectedMaterial.description}</p>
                    <div className="flex items-center gap-2 mb-2">
                      <span className="text-sm text-gray-500">Status:</span>
                      {selectedMaterial.isVirgin ? (
                        <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">Virgin Material</span>
                      ) : (
                        <span className="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Recycled Material</span>
                      )}
                    </div>
                    <p className="text-xs text-gray-500">
                      Fetched: {new Date(selectedMaterial.fetchedAt).toLocaleString()}
                    </p>
                    {selectedMaterial.productionData && (
                      <div className="mt-3 text-xs text-gray-500">
                        <p>Date Range: {selectedMaterial.productionData.from_date} to {selectedMaterial.productionData.to_date}</p>
                      </div>
                    )}
                  </div>
                  
                  <div>
                    <h3 className="text-lg font-semibold mb-3">Production Data</h3>
                    <div className="grid grid-cols-1 gap-4">
                      <div className="bg-blue-50 p-4 rounded-lg">
                        <h4 className="font-medium text-gray-700 mb-2">Job Cards Summary</h4>
                        <div className="grid grid-cols-2 gap-4">
                          <div>
                            <p className="text-sm text-gray-600">Production (Kg)</p>
                            <p className="text-lg font-semibold text-blue-600">
                              {selectedMaterial.jobcardProductionKgs || 0}
                            </p>
                          </div>
                          <div>
                            <p className="text-sm text-gray-600">Bags</p>
                            <p className="text-lg font-semibold text-blue-600">
                              {selectedMaterial.jobcardBags || 0}
                            </p>
                          </div>
                        </div>
                      </div>
                      <div className="bg-green-50 p-4 rounded-lg">
                        <h4 className="font-medium text-gray-700 mb-2">Production Summary</h4>
                        <div className="grid grid-cols-2 gap-4">
                          <div>
                            <p className="text-sm text-gray-600">Production (Kg)</p>
                            <p className="text-lg font-semibold text-green-600">
                              {selectedMaterial.productionKgs || 0}
                            </p>
                          </div>
                          <div>
                            <p className="text-sm text-gray-600">Bags</p>
                            <p className="text-lg font-semibold text-green-600">
                              {selectedMaterial.productionBags || 0}
                            </p>
                          </div>
                        </div>
                      </div>
                      
                      {/* Types from API response */}
                      {selectedMaterial.types && selectedMaterial.types.length > 0 && (
                        <div className="bg-gray-50 p-4 rounded-lg">
                          <h4 className="font-medium text-gray-700 mb-2">Types</h4>
                          <div className="flex flex-wrap gap-2">
                            {selectedMaterial.types.map((type, index) => (
                              <span key={index} className="bg-gray-200 text-gray-700 px-2 py-1 rounded text-sm">
                                {type}
                              </span>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>
                  </div>
                </div>
              </div>
            ) : (
              <div className="h-full flex items-center justify-center">
                <div className="text-gray-400 text-center">
                  <Package className="w-16 h-16 mx-auto mb-4 opacity-50" />
                  <h3 className="text-xl font-semibold mb-2">Select a Material</h3>
                  <p>Click on any material card above to view detailed information</p>
                  {filteredMaterials.length === 0 && (
                    <p className="mt-4 text-gray-500">
                      Use the category buttons above to fetch material data
                    </p>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};

export default PlasticMaterialsSearch;


// Mount React
const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<PlasticMaterialsSearch />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}