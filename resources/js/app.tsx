
import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import { ChevronDown, ChevronRight } from 'lucide-react';
import axios from 'axios';





// console.log('=== REACT APP.TSX LOADED ===');
// console.log('React:', typeof React);
// console.log('createRoot:', typeof createRoot);


declare global {
  interface Window {
    customersData: Customer[];
    productsData: Product[];
    machineTypesData: MachineName[];
 
  }
}



interface Customer {
  id: number;
  name: string;

}


interface Product {
  id: number;
  name: string;

}


interface MachineName {
  id: number;
  name: string;

}

// Define interfaces for our data structures
interface JobCard {
  id: string;
  jobNumber: string;
  description: string;
  customerId: string;
  start: Date;
  end: Date;
  progress: number;
  color?: string;
}


interface JobcardEx {
  job_card_id : number;
  job_cards_customerId: number;
  job_cards_created_at: string;
  job_cards_updated_at: string;
  job_cards_stateId: number;
  job_cards_jobcardType: string | null;
  job_cards_outstanding: number;
  jobcarditem_id: number;
  jobcarditem_productId: number;
  jobcarditem_qnt: number;
  jobcarditem_unitId: number;
  jobcarditem_processId: number;
  jobcarditem_outstanding: number;
  jobcarditem_stateId: number;
  jobcarditem_other: string | null;
}


interface Machine {
  id: string;
  name: string;
  jobCards: JobCard[];
}

interface MachineEx {
  id: number;
  name: string;
  // other properties as needed
}

interface MachineResponse {
  machineries: MachineEx[];
}

interface GanttChartProps {
  machines: Machine[];
  timeUnit?: 'day' | 'week' | 'month';
  startDate?: Date;
  endDate?: Date;
  showWeekends?: boolean;
  onJobCardClick?: (machine: Machine, jobCard: JobCard) => void;
}

const GanttChart: React.FC<GanttChartProps> = ({
  machines,
  timeUnit = 'day',
  startDate: propStartDate,
  endDate: propEndDate,
  showWeekends = true,
  onJobCardClick
}) => {
  // State for tracking which machines are expanded
  const [expandedMachines, setExpandedMachines] = useState<{ [key: string]: boolean }>({});

  // Calculate the start and end dates for the chart
  const calculateDateRange = () => {
    let start = propStartDate;
    let end = propEndDate;

    if (!start || !end) {
      // If dates aren't provided, calculate from all job cards
      const allDates = machines.flatMap(machine => 
        machine.jobCards.flatMap(job => [job.start, job.end])
      );
      
      if (allDates.length > 0) {
        const minDate = new Date(Math.min(...allDates.map(d => d.getTime())));
        const maxDate = new Date(Math.max(...allDates.map(d => d.getTime())));

        // Add some padding
        if (!start) {
          start = new Date(minDate);
          start.setDate(start.getDate() - 3);
        }
        
        if (!end) {
          end = new Date(maxDate);
          end.setDate(end.getDate() + 3);
        }
      } else {
        // Fallback if no job cards
        const today = new Date();
        if (!start) {
          start = new Date(today);
          start.setDate(today.getDate() - 7);
        }
        if (!end) {
          end = new Date(today);
          end.setDate(today.getDate() + 14);
        }
      }
    }

    return { start, end };
  };

  const { start: chartStartDate, end: chartEndDate } = calculateDateRange();

  // Calculate position and width for a job card
  const calculateJobCardPosition = (jobCard: JobCard) => {
    const totalDays = Math.ceil((chartEndDate.getTime() - chartStartDate.getTime()) / (1000 * 60 * 60 * 24));
    const jobStartDays = Math.ceil((jobCard.start.getTime() - chartStartDate.getTime()) / (1000 * 60 * 60 * 24));
    const jobDuration = Math.ceil((jobCard.end.getTime() - jobCard.start.getTime()) / (1000 * 60 * 60 * 24)) + 1;
    
    const left = (jobStartDays / totalDays) * 100;
    const width = (jobDuration / totalDays) * 100;
    
    return { left: `${left}%`, width: `${width}%` };
  };

  // Generate time slots based on the time unit
  const generateTimeSlots = () => {
    const slots = [];
    const currentDate = new Date(chartStartDate);
    
    while (currentDate <= chartEndDate) {
      // Skip weekends if showWeekends is false
      const dayOfWeek = currentDate.getDay();
      const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
      
      if (showWeekends || !isWeekend) {
        slots.push(new Date(currentDate));
      }
      
      // Increment based on time unit
      switch (timeUnit) {
        case 'day':
          currentDate.setDate(currentDate.getDate() + 1);
          break;
        case 'week':
          currentDate.setDate(currentDate.getDate() + 7);
          break;
        case 'month':
          currentDate.setMonth(currentDate.getMonth() + 1);
          break;
        default:
          currentDate.setDate(currentDate.getDate() + 1);
      }
    }
    
    return slots;
  };

  const timeSlots = generateTimeSlots();

  // Format date for display
  const formatDate = (date: Date) => {
    switch (timeUnit) {
      case 'day':
        return `${date.getDate()}/${date.getMonth() + 1}`;
      case 'week':
        return `W${Math.ceil(date.getDate() / 7)} ${date.toLocaleString('default', { month: 'short' })}`;
      case 'month':
        return date.toLocaleString('default', { month: 'short', year: '2-digit' });
      default:
        return `${date.getDate()}/${date.getMonth() + 1}`;
    }
  };

  // Toggle machine expansion
  const toggleMachine = (machineId: string) => {
    setExpandedMachines(prev => ({
      ...prev,
      [machineId]: !prev[machineId]
    }));
  };



    const [machineName, ] = useState(window.machineTypesData || []);
    const [products, ] = useState(window.productsData || []);
    const [customers, ]   = useState(window.customersData || []);


    const getMachineName = (machineId) => {
    const machine = machineName.find(m => m.id === machineId);
    return machine ? machine.name : ` ${machineId}`;
          };


    // For products
    const getProductName = (productId) => {
    const product = products.find(p => p.id === productId);
    return product ? product.name : ` ${productId}`;
          };

    const getCustomerName = (customerId) => {
    const customer = customers.find(c => c.id === customerId);
    return customer ? customer.name : ` ${customerId}`;
};



  // Function to render job cards for a machine
  const renderJobCards = (machine: Machine) => {
    const isExpanded = expandedMachines[machine.id] !== false;
    
    return (
      <React.Fragment key={machine.id}>
        <div className="flex w-full border-b border-gray-200">
          {/* Machine name column */}
          <div 
            className="w-1/4 flex items-center p-2 border-r border-gray-200"
          >
            <span 
              className="mr-2 cursor-pointer"
              onClick={() => toggleMachine(machine.id)}
            >
              {isExpanded ? <ChevronDown size={16} /> : <ChevronRight size={16} />}
            </span>
            <span className="font-large truncate">{ getMachineName(machine.id)}</span> 
          </div>
          
          {/* Timeline column - empty for machine row */}
          <div className="w-3/4 relative h-10"></div>
        </div>
        
        {/* Render job cards if expanded */}
        {isExpanded && machine.jobCards.map(jobCard => {
          const { left, width } = calculateJobCardPosition(jobCard);
          
          return (
            <div key={jobCard.id} className="flex w-full border-b border-gray-200">
              {/* Job card details column */}
              <div className="w-1/4 flex items-center p-2 border-r border-gray-200 pl-10">
                <span className="truncate text-sm">
                  {getProductName(jobCard.description)}
                </span>
              </div>
              
              {/* Timeline column with job card */}
              <div className="w-3/4 relative h-10">
                <div 
                  className="absolute h-6 top-2 rounded-sm cursor-pointer"
                  style={{ 
                    left, 
                    width,
                    backgroundColor: jobCard.color || '#3b82f6',
                  }}
                  onClick={() => onJobCardClick && onJobCardClick(machine, jobCard)}
                >
                  {/* Progress indicator */}
                  <div 
                    className="absolute h-full rounded-sm bg-blue-800"
                    style={{ width: `${jobCard.progress}%`, opacity: 0.7 }}
                  />
                  
                  {/* Job number inside bar if there's enough space */}
                  {parseFloat(width.replace('%', '')) > 10 && (
                    <div className="px-2 text-xs text-white truncate h-full flex items-center">
                      {getCustomerName(jobCard.customerId )  }
                    </div>
                  )}
                </div>
              </div>
            </div>
          );
        })}
      </React.Fragment>
    );
  };

  // Initialize expanded machines
  useEffect(() => {
    const initialExpandedState: { [key: string]: boolean } = {};
    
    machines.forEach(machine => {
      // Default to expanded
      initialExpandedState[machine.id] = true;
    });
    
    setExpandedMachines(initialExpandedState);
  }, [machines]);

  return (
    <div className="w-full border border-gray-200 rounded-md overflow-auto bg-white">
      {/* Header */}
      <div className="flex w-full border-b border-gray-200 font-medium bg-gray-50">
        <div className="w-1/4 p-2 border-r border-gray-200">Machine / Job</div>
        <div className="w-3/4 flex">
          {timeSlots.map((slot, index) => (
            <div 
              key={index} 
              className="text-center border-r border-gray-200 flex-grow"
            >
              {formatDate(slot)}
            </div>
          ))}
        </div>
      </div>
      
      {/* Grid with time indicators */}
      <div className="relative">
        <div className="absolute top-0 left-1/4 right-0 h-full flex">
          {timeSlots.map((slot, index) => {
            const isWeekend = slot.getDay() === 0 || slot.getDay() === 6;
            return (
              <div 
                key={index}
                className={`border-r border-gray-200 flex-grow ${isWeekend ? 'bg-gray-100' : ''}`}
              />
            );
          })}
        </div>
        
        {/* Machines and Job Cards */}
        <div className="relative">
          {machines.map(machine => renderJobCards(machine))}
        </div>
      </div>
    </div>
  );
};
const JobScheduler = () => {
  const [machinex, setMachinex] = useState<MachineEx[]>([]);
  const [jobcardex, setJobcardex] = useState<JobcardEx[]>([]);
  const [machines, setMachines] = useState<Machine[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [apiError, setApiError] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [selectedJob, setSelectedJob] = useState<JobCard | null>(null);
  const [selectedMachine, setSelectedMachine] = useState<Machine | null>(null);
  const [jobNumber, setJobNumber] = useState<number | string>('');
  const [customerId, setCustomerId] = useState<number | string>('');
  const [productId, setProductId] = useState<number | string>('');
  const [machinevalue, setMachinevalue] = useState<number | string>('');
  const [startDates, setStartDates] = useState<number | string>('');
  const [endDates, setEndDates] = useState<number | string>('');


    // In your React component
  const [customers, ]   = useState(window.customersData || []);
  const [machineName, ] = useState(window.machineTypesData || []);
  const [products, ] = useState(window.productsData || []);
  


  // Extract data fetching functions so they can be reused
  const fetchGanttData = async () => {
    try {
      const response = await axios.get(`/LaravelCRUD/qryallocations/index?allocationlist=allocationlist`);

      if (!Array.isArray(response.data)) {
        setApiError('Unexpected API response format. Expected an array of machines.');
        return;
      }

      const transformedData: Machine[] = response.data.map((apiMachine: any) => {
        const machineId = apiMachine.id || apiMachine.machine_id;
        const machineName = apiMachine.name || apiMachine.machine_name;
        const rawJobCards = apiMachine.jobCards || apiMachine.jobs || apiMachine.allocations;

        const jobCardsForMachine = Array.isArray(rawJobCards)
          ? rawJobCards.map((apiJobCard: any) => ({
              id: apiJobCard.id,
              jobNumber: apiJobCard.jobNumber,
              description: apiJobCard.description,
              customerId: apiJobCard.customerId,
              start: new Date(apiJobCard.start),
              end: new Date(apiJobCard.end),
              progress: apiJobCard.progress,
              color: apiJobCard.color,
            }))
          : [];

        return {
          id: machineId  ,
          name: machineName,
          jobCards: jobCardsForMachine,
        };
      });

      setMachines(transformedData);
    } catch (err) {
      setApiError('Failed to load job cards');
      console.error(err);
    }
  };

  const fetchJobCards = async () => {
    try {
      const response = await axios.get('/LaravelCRUD/qryjobcards/index?allocation=allocation');
      
      if (response.data && response.data.machineries) {
        setJobcardex(response.data.machineries);
      } else if (Array.isArray(response.data)) {
        setJobcardex(response.data);
      } else {
        const possibleArrays = Object.entries(response.data)
          .filter(([_, value]) => Array.isArray(value))
          .map(([key, value]) => ({ key, length: (value as any[]).length }));
        
        if (possibleArrays.length > 0) {
          const arrayKey = possibleArrays[0].key;
          setJobcardex(response.data[arrayKey]);
        } else {
          setApiError('Invalid data structure received from API');
          setJobcardex([]);
        }
      }
    } catch (error) {
      console.error('Failed to load job cards:', error);
      setApiError('Failed to load job cards from API');
      setJobcardex([]);
    }
  };

  const fetchMachines = async () => {
    try {
      const response = await axios.get('/LaravelCRUD/qrymachine/index?machine=machineEx');
      
      if (response.data && response.data.machineries) {
        setMachinex(response.data.machineries);
      } else if (Array.isArray(response.data)) {
        setMachinex(response.data);
      } else {
        const possibleArrays = Object.entries(response.data)
          .filter(([_, value]) => Array.isArray(value))
          .map(([key, value]) => ({ key, length: (value as any[]).length }));
        
        if (possibleArrays.length > 0) {
          const arrayKey = possibleArrays[0].key;
          setMachinex(response.data[arrayKey]);
        } else {
          setApiError('Invalid data structure received from API');
          setMachinex([]);
        }
      }
    } catch (error) {
      console.error('Failed to load machines:', error);
      setApiError('Failed to load machines from API');
      setMachinex([]);
    }
  };




const getCustomerName = (customerId) => {
    const customer = customers.find(c => c.id === customerId);
    return customer ? customer.name : ` ${customerId}`;
};

// For machines
const getMachineName = (machineId) => {
    const machine = machineName.find(m => m.id === machineId);
    return machine ? machine.name : ` ${machineId}`;
};

// For products
const getProductName = (productId) => {
    const product = products.find(p => p.id === productId);
    return product ? product.name : ` ${productId}`;
};

  // Initial data loading
  useEffect(() => {
    const loadAllData = async () => {
      setLoading(true);
      try {
        await Promise.all([
          fetchGanttData(),
          fetchJobCards(),
          fetchMachines()
        ]);
      } catch (error) {
        console.error('Error loading data:', error);
      } finally {
        setLoading(false);
      }
    };

    loadAllData();
  }, []);

  // Handle input changes
  const handleStartDateChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setStartDates(event.target.value);
  };

  const handleEndDateChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setEndDates(event.target.value);
  };

  // FIXED: Updated handleAllocateJob function
  const handleAllocateJob = async () => {
    if (!jobNumber || !machinex || !customerId || !productId) {
      alert("Please fill in all required fields.");
      return;
    }

    const today = new Date().toISOString().slice(0, 10);

    const jobData = {
      job_number: jobNumber,
      machinevalue: machinevalue  ,
      start_date: startDates || today,
      end_date: endDates || today,
      customerId: customerId,
      productId: productId,
    };

    const dataString = encodeURIComponent(JSON.stringify(jobData));


    try {
      const response = await axios.get(`/LaravelCRUD/qryallocations/show?data=${dataString}`);
   
      
      // Clear form
      setJobNumber('');
      setCustomerId('');
      setProductId('');
      setMachinevalue('');
      setStartDates('');
      setEndDates('');
      
      // Refresh all data to show the new allocation
      await Promise.all([
        fetchGanttData(),    // This will refresh the Gantt chart
        fetchJobCards()      // This will refresh the job cards list
      ]);
      
      
      
    } catch (err) {
      console.error(err);
      alert("Failed to allocate job.");
    }
  };

  // Handle removing allocation
  const handleRemoveAllocation = async () => {
    if (!selectedJob || !selectedMachine) {
      alert("No job selected for removal.");
      return;
    }

    // Confirm before removing
    const confirmRemoval = window.confirm(`Are you sure you want to remove allocation for Job ${selectedJob.jobNumber} from ${selectedMachine.name}?`);
    if (!confirmRemoval) {
      return;
    }


    const today = new Date().toISOString().slice(0, 10);

     const removalData = {
        action: 'delete',
      job_number: selectedJob.jobNumber,
      machine_id: selectedMachine.id,
      id : selectedJob.id,


    };

    

    const dataString = encodeURIComponent(JSON.stringify(removalData));

    try {
      // Send removal request to your Laravel API
      const response = await axios.get(`/LaravelCRUD/qryallocations/destroy?data=${dataString}`);
      console.log("Allocation removed successfully!", response);
      
      // Clear selected job
      setSelectedJob(null);
      setSelectedMachine(null);
      
      // Refresh all data to show the updated allocations
      await Promise.all([
        fetchGanttData(),    // This will refresh the Gantt chart
        fetchJobCards()      // This will refresh the job cards list
      ]);
      
      
      
    } catch (err) {
      console.error(err);
      alert("Failed to remove allocation.");
    }
  };

  // Handle replacing allocation
  const handleReplaceAllocation = async () => {
    if (!selectedJob || !selectedMachine) {
      alert("No job selected for replacement.");
      return;
    }

    // Confirm before replacing
    const confirmReplacement = window.confirm(`Are you sure you want to replace allocation for Job ${selectedJob.jobNumber} from ${selectedMachine.name}?`);
    if (!confirmReplacement) {
      return;
    }

   

      if (!jobNumber || !machinex || !customerId || !productId) {
      alert("Please fill in all required fields.");
      return;
    }

    const today = new Date().toISOString().slice(0, 10);

    const replacementData = {
      action: 'replace',
      id : selectedJob.id,
      jobNumber: jobNumber,
      customerId: customerId,
      productId: productId,
    };

    const dataString = encodeURIComponent(JSON.stringify(replacementData));

    try {
      // Send replacement request to your Laravel API
      const response = await axios.get(`/LaravelCRUD/qryallocations/destroy?data=${dataString}`);
      console.log("Allocation replaced successfully!", response);
      
         // Clear form
      setJobNumber('');
      setCustomerId('');
      setProductId('');
      setMachinevalue('');
      setStartDates('');
      setEndDates('');
      
      // Refresh all data to show the new allocation
      await Promise.all([
        fetchGanttData(),    // This will refresh the Gantt chart
        fetchJobCards()      // This will refresh the job cards list
      ]);
      
   
      
    } catch (err) {
      console.error(err);
      alert("Failed to replace allocation.");
    }






  };

  const handleJobCardClick = (machine: Machine, jobCard: JobCard) => {
    setSelectedMachine(machine);
    setSelectedJob(jobCard);
  };

  const handleJobCardListClick = (job: JobcardEx) => {
    setJobNumber(job.jobcarditem_id);
    setCustomerId( job.job_cards_customerId)  ;       
    setProductId(job.jobcarditem_productId);
  };

  if (loading) {
    return <div className="p-4">Loading job schedule...</div>;
  }

  if (error) {
    return <div className="p-4 text-red-500">{error}</div>;
  }

  // Calculate date range for the chart
  const today = new Date();
  const startDate = new Date();
  startDate.setDate(today.getDate() - 3);
  
  const endDate = new Date();
  endDate.setDate(today.getDate() + 10);

  return (
    <div className="p-4 h-screen flex flex-col">
      <h2 className="text-2xl font-bold mb-4">Machine Job Schedule</h2>
      
      <div className="flex flex-1 gap-4 h-full overflow-hidden">
        <div className="w-3/5 flex flex-col">
          <div className="flex-1 overflow-auto">
            <GanttChart 
              machines={machines} 
              timeUnit="day" 
              startDate={startDate}
              endDate={endDate}
              showWeekends={true}
              onJobCardClick={handleJobCardClick}
            />
          </div>
        </div>
        
        <div className="w-2/5 flex flex-col gap-4 overflow-auto">
          {selectedJob && (
            <div className="p-4 border rounded-md bg-gray-50">
              <h3 className="font-medium mb-2">Job Card Details</h3>

              <p><strong>Product:</strong> {getProductName(selectedJob.description)}</p>
              <p><strong>Machine:</strong>  {getMachineName(selectedMachine?.name)}</p>
              <p><strong>Progress:</strong> {selectedJob.progress}%</p>
              
              {/* Action Buttons */}
              <div className="mt-4 flex gap-2">
                <button 
                  type="button" 
                  onClick={handleRemoveAllocation}
                  className="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                >
                  Remove
                </button>
                {/* <button 
                  type="button" 
                  onClick={handleReplaceAllocation}
                  className="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded"
                >
                  Replace
                </button> */}
              </div>
            </div>
          )}
          
          <div className="p-4 border rounded-md">
            <h3 className="font-medium mb-4">Allocate New Job</h3>
            
            <div className="grid grid-cols-1 gap-4">
              <div  style={{ display: 'none' }}>
                <label className="block text-sm font-medium text-gray-700">Job Number</label>
                <input 
                  type="text" 
                  value={jobNumber}
                  onChange={(e) => setJobNumber(e.target.value)} 
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" 
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Customer</label>
                <input 
                  type="text" 
                value={getCustomerName(customerId)}
                  onChange={(e) => setCustomerId(e.target.value)} 
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" 
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Product</label>
                <input 
                  type="text" 
                  value={getProductName(productId)}
                  onChange={(e) => setProductId(e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" 
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700">Machine</label>
                <select 
                  value={machinevalue}
                  onChange={(e) => setMachinevalue(e.target.value)}
                  className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border">
                  <option value="">Select Machine</option>
                  {loading ? (
                    <option value="" disabled>Loading machines...</option>
                  ) : error ? (
                    <option value="" disabled>Error: {error}</option>
                  ) : machinex.length > 0 ? (
                    machinex.map(machine => (
                      <option key={machine.id} value={machine.id}>
                         { getMachineName(machine.id)}
                      </option>
                    ))
                  ) : (
                    <option value="" disabled>No machines available</option>
                  )}
                </select>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700">Start Date</label>
                  <input 
                    type="date" 
                    id="startDate"
                    name="start_date"
                    value={startDates}
                    onChange={handleStartDateChange}    
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" 
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700">End Date</label>
                  <input 
                    type="date" 
                    id="endDate"
                    name="end_date"
                    value={endDates}
                    onChange={handleEndDateChange}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm p-2 border" 
                  />
                </div>
              </div>

              <div>
                <button 
                  type="button" 
                  onClick={handleAllocateJob}
                  className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                  Allocate Job
                </button>
              </div>
            </div>
          </div>
          
          <div className="p-4 border rounded-md">
            <h4 className="font-semibold mb-2">Extruding Job Cards</h4>
            <div className="space-y-2">
              {jobcardex.map((job) => (
                <div
                  key={job.job_card_id}
                  className="p-3 border rounded-md shadow-sm bg-white cursor-pointer hover:bg-gray-50 transition-colors"
                  onClick={() => handleJobCardListClick(job)}
                >
                  <div className="flex items-center justify-between gap-6 p-4 bg-white rounded-lg border border-gray-200 shadow-sm">
                    {/* Customer Information */}
                    <div className="flex flex-col">
      
                      <span className="text-md font-bold text-gray-900">
                        {getCustomerName(job.job_cards_customerId)}
                      </span>
                    </div>

                    {/* Product Information */}
                    <div className="flex flex-col">
          
                      <span className="text-md font-bold text-gray-900">
                        {getProductName(job.jobcarditem_productId)}
                      </span>
                    </div>

                    {/* Job Card ID */}
                    <div className="flex flex-col">
          
                      <span className="text-base font-semibold text-gray-700 font-mono">
                        {job.jobcarditem_id}
                      </span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default JobScheduler;

// Mount React
const container = document.getElementById('root');
if (container) {
    console.log('Root container found, mounting React...');
    const root = createRoot(container);
    root.render(<JobScheduler />);
    console.log('React render called');
} else {
    console.error('Root container not found!');
}