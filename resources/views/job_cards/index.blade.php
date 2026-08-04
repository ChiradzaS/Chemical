<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{asset('style/style.css')}}" >
</head>
<script src="{{ asset('public/js/script.js') }}" ></script>
<body>
<div class=".container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<br>
<br>
@if ($message = Session::get('success'))
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" onclick="closeNotification()">
        <span aria-hidden="true">&times;</span>
    </button>
    <strong>Success! </strong>{{ $message }}
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
        .none {
        display: none;
    }

  }

  td {
            padding: 10px;
            box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3);
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


<table class="table table-bordered">
<thead class="thead-dark">
<tr>
<th >Job Card List</th>



</tr>
</thead>
</table>
</div>
<div class="pull-right mb-2">
</div>
</div>
</div>


<!-- <a class="btn btn-dark " href="{{ route('index.index',['prntReport' =>'ALL_JOBCARDS','id'=>'$job_card->id']) }}">Print Jobcard List</a> -->



<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});


</script>




<style>
    span.select2.select2-container.select2-container--classic{
        width: 70% !important;
    }
</style>

<form action="{{ route('job_cards.index', ['prntReport' => 'JOB_CARDS'])}}" method="GET">

<table class="table table-striped" >

<td width="250px">
    <div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text none" id="basic-addon1">From</span>
  </div>
  <input type="date" name="fromDate"   value="{{$fromDate}}" id="fromDate" class="form-control none">
</div>
</td>

<td width="250px">
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text none" id="basic-addon1" >To</span>
  </div>
  <input type="date" name="toDate"  id="toDate"  value="{{$toDate}}" class="form-control none">
</div>
</td>

<td >
    <div class="input-group mb-3 none">
  <div class="input-group-prepend none">
    
    <strong>Customer</strong>
  </div>&nbsp
<select name="customerId" id="customers"    class="js-example-basic-single none"  >
<option value="" {{ $customerId == '' ? 'selected' : '' }}>--All Customers--</option>
@foreach($customers as $customer)
<option value="{{$customer->id }}" @if($customerId == $customer->id ) selected @endif><strong>{{ $customer->name }}</strong></option>
@endforeach
</select>
</div>
</td>


<td >
    <div class="input-group mb-3 none">
  <div class="input-group-prepend none">
    
    <strong>Products</strong>
  </div>&nbsp
  <select name="productId" id="productId"  class="js-example-basic-single none" style="width:500px;"  placeholder="-- Select Product --">
  <option value="" {{ $productId == '' ? 'selected' : '' }}>--All Products--</option>
    @foreach($products as $product)
<option value="{{$product->id}}" @if($productId == $product->id ) selected @endif>{{ $product->name }}</option>
@endforeach
    </select>
</div>
</td>



<td>
<button class="btn btn-outline-success my-2 my-sm-0 none"  value="query" name="action"  type="submit">Search</button>
</td>


<td width="100px"><button type="button" onclick=print() name="action" class="btn btn-outline-success my-2 my-sm-0 none" >Print</button></td>

</form>
<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}

.inline-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}




 
    h3{
        text-align: center;
    }
 </style>
<br>


<table class="table table-bordered">

@php
    $lastJobcardId = null;
@endphp

@if(isset($orders) && !empty($orders))

@foreach ($job_cards as $job_card)


@php $tmpProduct =  $porducts[$job_card->jobcarditem_productId] ?? ''; @endphp
@php $tmpUnittype = $unittypes[$job_card->jobcarditem_unitId] ?? ''; @endphp
@php $tmpCustomer = $customers[$job_card->job_cards_customerId] ?? ''; @endphp
@php $tmpstatus = $statustypes[$job_card->job_cards_stateId] ?? ''; @endphp
@php $tmpProcesstype = $processtypes[$job_card->jobcarditem_processId] ?? '';  @endphp


    @if ($job_card->job_card_id != $lastJobcardId)
        @if (!is_null($lastJobcardId))
            </tbody>
        @endif



  

        
        <thead class="thead-dark">
        <tr style="color: white;">
            <td style="text-align: center; vertical-align: middle; background-color:#0d5757; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); border: none; "><strong> {{ $job_card->job_card_id }}</strong></td>
            <td style="text-align: center; vertical-align: middle; background-color:#0d5757; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); border: none;"><strong> {{ $job_card->job_cards_created_at }}</strong></td>
            <td colspan='3'style="text-align: center; vertical-align: middle; background-color:#0d5757; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); border: none;  font-size: 25px;"  ><strong>{{ $tmpCustomer->name ??''}}</strong></td>
            <td style="text-align: center; vertical-align: middle; background-color:#0d5757; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); border: none;" ><strong>{{ $tmpstatus->name }} </strong></td>
            <td colspan="2" style="text-align: center; vertical-align: middle; background-color:#0d5757; box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5); border: none;">&nbsp;
        &nbsp;

        <div class="btn-group" role="group">
        <form action="
"><a class="btn btn-light btn-sm none" onclick="clone({ jobCardId: '{{ $job_card->job_card_id }}', customerId: '{{$job_card->job_cards_customerId}}', outstanding: '{{$job_card->job_cards_outstanding}}', jobcardId: '{{$job_card->job_card_id}}' })">Clone&nbsp;&nbsp;</a>



<script>


function clone(data){

    var jobCardId = data.jobCardId;
    var customer = data.customerId;
    var outstanding = data.outstanding;
    var jobCardId = data.jobcardId;

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
//alert(jobCardId);
              
$.ajax({
url: "{{ route('clonejobcard') }}",
type: 'post',
data: {_token: CSRF_TOKEN, jobCardId:jobCardId},
dataType: 'json',
success: function(response){

 
  
if(response > 0 ){

    //alert(response);



    var url = `job_cards/create?value=${response}&value1=${customer}&value2=${outstanding}&value3=${jobCardId}`;

    
    window.location.href = url;

  

}





}


});

   
    }
</script>


</form>
<!-- 
        &nbsp;
        &nbsp;
        <form action="{{ route('actionjobs.actionupdate',['job' => $job_card->job_card_id ,'product' => $job_card->jobcarditem_productId]) }}" method="GET" >
            <button type="submit" class="btn btn-light btn-sm none">Update</button>
        </form> -->
  
        &nbsp;
        &nbsp;

        <form action="{{ route('actionjobs.actionview',['job' => $job_card->job_card_id,'product' =>  $job_card->jobcarditem_productId]) }}" method="GET" >
            <button type="submit"  onclick="showSpinner()" class="btn btn-light btn-sm none">&nbsp;View&nbsp;</button>
        </form>
  
        &nbsp;
        &nbsp;
        <form action="{{ route('actionjobs.actiondelete',['job' => $job_card->job_card_id ]) }}" method="GET" >
        <button type="submit" class="btn btn-light btn-sm none" onclick="return confirm('Are you sure you want to delete')">Delete</button>
        </form>


     
        </div>
      
 
  
      </td>
        </tr>
        </thead>
        <tbody>
        @php
            $lastJobcardId = $job_card->job_card_id;
        @endphp
    @endif

    <tr>    
        <td></td>

        <td></td>

        <td><strong>{{ $tmpProcesstype ->name}}</strong></td>
        <td>{{ $job_card->jobcarditem_id }}</td>
        <td>
    <div class="inline-content">
        <strong>{{ $tmpProduct->name }} ' ' {{ $tmpUnittype->name }}</strong>
        @if ($job_card->jobcarditem_outstanding === $job_card->jobcarditem_qnt)
            <button disabled class="btn btn-danger btn-sm"><strong>Production</strong></button>
        @else
            <form action="{{ route('actionjobs.actionproduction', ['job' => $job_card->jobcarditem_id, 'outstanding' => $job_card->jobcarditem_outstanding, 'quantity' => $job_card->jobcarditem_qnt ]) }}" method="GET">
                <button type="submit" onclick="showSpinner()" class="btn btn-danger btn-sm none"><strong>Production</strong></button>
            </form>
        @endif
    </div>
</td>
        <td>Openning Qnt: <strong>{{ $job_card->jobcarditem_outstanding}}</strong>  </td> 
        <td>
    Outstanding: 
    <strong style="color: 
    {{ $job_card->jobcarditem_qnt <= 0 ? 'red' : 
       ($job_card->jobcarditem_qnt < $job_card->jobcarditem_outstanding ? 'orange' : 'black') 
    }};">
    {{ $job_card->jobcarditem_qnt }}
</strong>

</td>


        <td class="none"><a  onclick="showSpinner()" href="{{ route('index.create', ['jobCardId' => $job_card->job_card_id, 'other' =>  $job_card->jobcarditem_other , 'state' => $job_card->jobcarditem_stateId,'jobcarditemId' => $job_card->jobcarditem_id ,'productId' => $job_card->jobcarditem_productId ])  }}"><strong>&nbspPrint&nbsp</strong></a></td> 

    </tr>
@endforeach

@if (!is_null($lastJobcardId))
    </tbody>
@endif



            </tbody>


            </tr>


              </tbody>


              @else
      
       
      <div class="alert alert-danger alert-dismissible">
          <button type="button" class="close" onclick="closeNotification()">
              <span aria-hidden="true">&times;</span>
          </button>
          <strong>No orders data available!</strong> {{ $message }}
      </div>
      
 @endif



            </table>


            <style>
        /* Spinner Container */
        .spinner-container {
            display: none; /* Hidden by default */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }

        /* Spinner Style */
        .spinner {
            width: 17.6px;
            height: 17.6px;
            border-radius: 17.6px;
            box-shadow: 44px 0px 0 0 rgba(0, 0, 0, 0.2), 35.6px 26px 0 0 rgba(0, 0, 0, 0.4), 13.64px 41.8px 0 0 rgba(0, 0, 0, 0.6), -13.64px 41.8px 0 0 rgba(0, 0, 0, 0.8), -35.6px 26px 0 0 #000000;
            animation: spinner-b87k6z 1.4s infinite linear;
        }

        @keyframes spinner-b87k6z {
            to {
                transform: rotate(360deg);
            }
        }


    </style>
       

<!-- Spinner Container -->
<div id="spinnerContainer" class="spinner-container">
    <div class="spinner"></div>
</div>

<script>
    function showSpinner() {
        // Show the spinner
        document.getElementById('spinnerContainer').style.display = 'block';

        // Simulate a task (e.g., API call or delay)
        setTimeout(function () {
            // Hide the spinner after the task is complete
            document.getElementById('spinnerContainer').style.display = 'none';
        }, 13000); // 3 seconds delay for demonstration
    }
</script>
</body>
</html>