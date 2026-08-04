
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->


<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>

<!--------------------------------------------------------------------------------------------------------------------------->









<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
 
<br>
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success! </strong><strong>{{ $message }}</strong>
</div>
@endif


@if ($message = Session::get('error'))
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Failed!</strong> {{ $message }}
</div>
@endif


<script>
  function closeNotification() {
    var alertElement = document.querySelector('.alert');
    if (alertElement) {
        alertElement.style.display = 'none';

    }

    
}


</script>


<style>

@media print {
    .no-print {
        display: none !important;
    }
}

  
    .alert.alert-success.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }

    .alert.alert-danger.alert-dismissible {
        padding: 31px;
        font-size: 18px;
    }
</style>
<div>
<form action="{{ route('productions.index', ['prntReport' => 'PRODUCTION_LIST'])}}" method="GET"  >
<table class="table table-striped no-print" >
<td width="70px" >
    <div class="input-group mb-3 none">
  <div class="input-group-prepend none">
    <span class="input-group-text" id="basic-addon1">Date</span>
  </div>
  <input type="date" name="fromDate"  id="fromDate" value="{{$fromDate}}" class="form-control none">
</div>
</td>

<td width="400px">
    <div class="input-group mb-3 none">



    <select name="machineryId" id="machineryId" style="width:200px;" class="form-control none" placeholder="-- Select Process --">
    <!-- Placeholder option -->
    <option value="" {{ $machineryId == '' ? 'selected' : '' }}>--All Machines--</option>
    <!-- Loop through the processtypes -->
    @foreach($machinetypes as $machinetype)
        <option value="{{$machinetype->id}}" @if($machineryId ==$machinetype->id) selected @endif>
        {{$machinetype->name  }}
        </option>
        @endforeach
</select>

    
</div>
</td>

<td width="400px">
    <div class="input-group mb-3 none">



    <select name="shiftId" id="shiftId"  style="width:200px;" class="form-control none" placeholder="-- Select Process --">
    <!-- Placeholder option -->
    <option value="" {{ $shiftId == '' ? 'selected' : '' }}>--All Shifts--</option>
    <!-- Loop through the processtypes -->
    @foreach($shifttypes as $shifttype)
        <option value="{{  $shifttype->id }}" @if($shiftId == $shifttype->id) selected @endif>
        {{ $shifttype->name }}
        </option>
        @endforeach
</select>
    

</div>
</td>
<td width="400px">
    <div class="input-group mb-3 none">

  <select name="processId" id="processId" style="width:200px;" class="form-control none" placeholder="-- Select Process --">
    <!-- Placeholder option -->
    <option value="" {{ $processId == '' ? 'selected' : '' }}>--All Processes--</option>
    <!-- Loop through the processtypes -->
    @foreach($processtypes as $processtype)
        <option value="{{ $processtype->id }}" @if($processId == $processtype->id) selected @endif>
            {{ $processtype->name }}
        </option>
    @endforeach
</select>
</div>
</td>
</td>


    
<td width="100px"><button type="submit" value="query" name="action" class="form-control  btn-dark none" >Search</button></td>
<td width="100px"><button type="button" onclick=print() name="action" class="form-control  btn-dark none" >Print</button></td>
    <p id="demo"></p>
</form>
 
</div>


  
    @if(isset($productions) && !empty($productions))
        @foreach($productions as $machineryId => $machine)


   
        @php $tmpMachinetype = $machinetypes[$machineryId ]; @endphp
      
   


            <?php

                    $totalQuantity = 0;
                    $totalWeight = 0;
                    $totalBales = 0;

                    foreach ($machine['productions'] as $production) {
                        foreach ($production['items'] as $item) {
                            $totalBales += $item['quantity'];
                            $weightPerBale = $item['weight_per_bale'] ?? 0; // If weight_per_bale is null, use 0
                            $totalQuantity += $item['quantity'] * $weightPerBale;
                        }
                    }
            ?>
            <div class="mb-3">

                <table class="table table-bordered">
                <thead class="table-dark">
                <tr>
                <th style="margin-right: 30px;" > {{ $tmpMachinetype->name }}  <h3>

                </h3></th>

                @foreach($machine['productions'] as $production)

                    @php $tmpuser = $user[ $production['details']['userId'] ]?? null; @endphp
                    @php $tmpShifttype = $shifttypes[$production['details']['shiftId']]?? null ; @endphp
                    @php $tmpProcesstype = $processtypes[$production['details']['processId']] ?? null ; @endphp

                

                @if( $tmpProcesstype->name == 'Extruding' )
                                      
                                        
                                       
                <th  style="text-align: right;">  {{$totalBales}} kgs </th>
                                    
                @else
            
                <th style="text-align: right;" >     {{$totalBales}} bales    &nbsp;&nbsp;&nbsp;  {{  $totalQuantity }} kgs  </th>
            
                
                @endif


         
                </tr>
                </thead>
                </table>
                <table class="table table-bordered">
                    <thead >
                        <tr>
             
                        <th class="no-print">Date Time</th>
                            <th>Operator</th>
                            <th>Process</th>
                            <th class="no-print"></th>
                            <th>Shift</th>
                            <th>Products</th>
                        </tr>
                    </thead>
                    <tbody>
   
                            <tr>
                            <td class="no-print">{{   \Carbon\Carbon::parse($production['details']['created_at'])->format('d D  ') }}</td>
                                 <td>
                                    <strong>  <?php echo e(is_object($tmpuser) ? $tmpuser->name : ''); ?></strong></td>
                                <td>{{ $tmpProcesstype->name }}</td>
                      
                                <td class="no-print" ><td><?php echo e(isset($tmpShifttype) ? $tmpShifttype->name : 'N/A'); ?></td></td>
                        
                                <td>
                                    <ul> <?php $totalItemsPerProduction = 0; ?>
                                        @foreach($production['items'] as $item)
                                        @php $tmpProduct = $porducts[$item['productId']]; @endphp
                                        @php $tmpUnittype = $unittypes[$item['unitId']] ?? '';  @endphp
                                        @php $tmpWeight = $weights[$item['weightState']] ?? '';  @endphp
                                        <li>

                                        <span  class="no-print">
                                           
                                        <!-- {{ \Carbon\Carbon::parse($item['created_at'])->format(' H:i') }} -->
   
                                           </span>


                                           <span style="margin-right: 30px;" class="no-print">
                                           
                                           <a href="{{ route('productions.index', ['delete' => $item['item_id']]) }}">Delete</a>
   
   
                                           </span>
                                           <span style="margin-right: 30px;" class="no-print">
                                           
                                           {{ $item['created_at'] }}
   
   
                                           </span>
   


                                 

                                        <span style="margin-right: 30px;">{{ $tmpProduct->name }} / {{ $tmpUnittype->name }}</span>


                                        @if( $tmpProcesstype->name == 'Extruding' )
                                      
                                        
                                       
                                        <span style="margin-right: 30px; font-weight: bold; ">  {{ $item['quantity'] }} kgs</span>
                                     
                                    
                                        @else


                                        <span style="margin-right: 30px;            ">  {{ $item['quantity'] }} </span>
                                  
                                 
                                    
                                      
                                        @endif

                              
                                        <!-- <span style="margin-right: 30px;" class="no-print">Job: {{ $item['jobcarditemId'] }}</span> -->
                                     
                                      
                                        <!-- <?php $totalItemsPerProduction += $item['quantity']; ?>  -->
              


                                    
                                      

                                        @if( $item['weight'] == null  || $item['weight'] == '0.00'   )
                                        <span style="margin-right: 10px;"></span>

                                        @else

                                        <!-- <span style="margin-right: 10px;"><strong>{{ $item['weight'] }} kg </strong></span> -->

                                        @if( $tmpProcesstype->name == 'Mixing' )

                                        <span style="margin-right: 10px;"></span>
                                        @else

                                        <span style="margin-right: 10px;"></span>
                                        @endif

                                  
                                    
                                        @endif

                                        </li>

                                            
                                        @endforeach
                                  
                                       
                                    </ul>

                                </td>

                                <td  style="width: 100px;"  >

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                 
                </table>
            </div>
        @endforeach
    @else
      
       
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>No production data available for today !</strong> {{ $message }}
</div>

    @endif
