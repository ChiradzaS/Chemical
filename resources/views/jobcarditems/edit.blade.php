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


</head>
<script type="text/javascript">
    
    function display() {
       
        var val = Date.now()
        const uniqueId = Math.random().toString(36).substr(2, 22);

        var nId = ""+uniqueId+val;
        var valT = nId.toString().substr(7,13);
       
        //window.alert(" Barcode : "+valT);

        const myElement = document.getElementById("barcode");
        myElement.value = valT;
       
       
    // What to do
    } 

    function displayProductInfo() {
        var e = document.getElementById("productId");
        var eVal = document.getElementById("productId").value;
        var str = e.options[e.selectedIndex].text;

        eHtml = '<input type="hidden" name="productId" id="poductId" value="'+eVal+'">';
       // window.alert(" Product Info : " + eVal + " " +eHtml);
        //document.body.innerHTML += eHtml;
        document.getElementById("updateJobcarditems").submit();
    }

    
    function calculate() {
        
         var micron = document.getElementById("thickness").value;
         var width = document.getElementById("totalWidth").value;
         var process = document.getElementById("processId").value;
         var test = document.getElementById("test").value;
         var qnt = document.getElementById("qnt").value;
         //alert('qnqnqnnq'+qnt);
        

         

         if (process == 23){

            var testingweight =  micron * width / 5600;
           

            document.getElementById('test').value = testingweight;

         }
         else if(process != 23){

            alert("Testing weight is only for extruding process");

         }


         var valArray = { 
         @foreach($unittypes as $unittype)
         "{{ $unittype->name }}" : {{ $unittype->value }} , 
          @endforeach
             }



        var e = document.getElementById("unitId");
        var valueE = e.options[e.selectedIndex].value;
        var textE = e.options[e.selectedIndex].text;
        var valueN = -9;

        for (var key in valArray) {
     
            var rtnComp = textE.localeCompare(key);
      
            if (rtnComp == 0) {
              
              var unitDivide = 1000 / valArray[key];

              valueN = valArray[key];
              //alert(' Unit Val: ' + valueN); 
             // var weightPerProduct1000 = document.getElementById("WeightPerProduct").value;
              //alert('100000: ' + weightPerProduct1000); 
             // alert ('rrrrrrrrrrrrrr'+valueN );
            
            }


            
        }     

            var totalQnt = valueN * qnt
            var total = document.getElementById("Totalqnt").value = totalQnt;


        

         
        
    }
   

  </script>
<body>
<div class="container mt-2">
<div class="row">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<table class="table table-bordered">

<br>
<thead class="thead-dark">
<tr>
<th >Update Jobcard</th>



</tr>
</thead>
</table>
<br>
</div>
<div class="pull-right">
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif

<form id="updateJobcarditems" action="{{ route('jobcarditems.update',$jobcarditem[0]['id']) }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')


    <div class="form-group">

        <strong>Jobcarditem:</strong>
        <input type="text" name="id" value="{{$jobcarditem[0]['id']}}" >     

    <strong>Job Card id:</strong>
    <input type="text" name="jobCardId" value="{{$jobcarditem[0]['jobCardId']}}" >

    Material Type:
    <select name="" id=""  >
    @foreach( $materialtypes as $materialtype)
    <option value="{{  $materialtype->id }}"  @if($porduct[0]['materialTypeId']==$materialtype->id) selected @endif>{{ $materialtype->name }}</option>
    @endforeach
    </select>

    <br>
    
<strong>Length</strong>
<input type="text" id= "" value="{{$porduct[0]['product_length']}}" >



<strong>Thickness:</strong>
<input type="text"  id="thickness" value="{{$porduct[0]['thickness']}}" >
<br>

<strong>Width:</strong>
<input type="text" id="totalWidth" value="{{$porduct[0]['product_Width']}}" >

<strong>Gusset Width:</strong>
<input type="text" id="totalWidth" value="{{$porduct[0]['gussetWidth']}}" >
 
<strong>Total Width:</strong>
<input type="text" id="totalWidth" value="{{$porduct[0]['totalWidth']}}" >
 
   
   
    <br>
<strong>Product:</strong>
    <select  id="productId" name="productId" onclick="displayProductInfo()">
        @foreach ($porducts as $porduct)
        <option value="{{ $porduct->id }}"  @if($jobcarditem[0]['productId']==$porduct->id) selected @endif>{{ $porduct->name }}</option>
        @endforeach
    </select>

<strong>Job Card name:</strong>
<input type="text" name="name" value="{{ $jobcarditem[0]['name'] }}"  >



<strong>Bag Type:</strong>
<select  id="bagType" name="bagType">
@foreach($bagtypes as $bagtype)
<option value="{{ $bagtype->id }}" @if($jobcarditem[0]['bagType']==$bagtype->id) selected @endif>{{ $bagtype->name }}</option>
@endforeach
</select>
</div> 


<strong>Testing Weight (g):</strong>
<input type="text" name="test" id="test" readonly>
<button type="button"  onclick="calculate()"  padding right=5px  class="btn btn-dark">Calculate</button>&nbsp&nbsp&nbsp&nbsp

<br>
<strong>Process type:</strong>
<select  name="processId"   id="processId"  >
@foreach($processtypes as $processtype)
<option value="{{ $processtype->id }}"  @if($jobcarditem[0]['processId']==$processtype->id) selected @endif>{{ $processtype->name }}</option>
@endforeach
</select>





<strong>Unit type:</strong>
<select  name="unitId"  id="unitId" >
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}"  @if($jobcarditem[0]['unitId']==$unittype->id) selected @endif>{{ $unittype->name }}</option>
@endforeach
</select>




<strong>Quantity:</strong>
<input type="text" name="qnt" id='qnt' value="{{ $jobcarditem[0]['qnt'] }}" placeholder="Company name"><br>

<strong>Total Quantity:</strong>
<input type="text" name="Totalqnt" id='Totalqnt' ><br>


<strong>Barcode:</strong>
<input type="text" id="barcode" name="barcode" value="{{ $jobcarditem[0]['barcode'] }}" placeholder="Company name">
<button type="button" onclick="display()"  padding right=5px  class="btn btn-dark">Barcode</button>&nbsp&nbsp&nbsp&nbsp



<strong>Other:</strong>
<textarea name="other" id="other" value="{{$jobcarditem[0]['other']}}">{{$jobcarditem[0]['other']}}</textarea><br>

<br>
<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;
}


 
    h3{
        text-align: center;
    }



    
 </style>






<script>

var process = document.getElementById("processId").value;
var table = document.getElementById("list1");
//alert('llllll'+table  );

if (process == 23){

    table.disabled = true;

}

</script>


@php $tmpProcesstype = $processtypes[$jobcarditem[0]['processId']]; @endphp
 
 @if($tmpProcesstype->name == 'Extruding' )



 <h3>Roll Weight</h3>

<table   class="table table-striped" width="100%">
    <tr id="list1" >
        <th  scope="col">Roll weight </th>
        <th  scope="col"> {{$jobcarditem[0]['qnt']}} Kg</th>

        
    </tr>

    </table>
<br>


@else
<br>



<h3>Packaging List</h3>


<table   class="table table-striped" width="100%">


    <tr id="list1" >
     
        <th  scope="col"> Min Weight</th>
        <th  scope="col"> Avg Weight</th>
        <th  scope="col"> Max Weight</th>
        <th  scope="col"> Unit</th>
        
    </tr>

   
 
    
    @php $tmpUnittype = $unittypes[$unit]; @endphp
    @php $tmpUnitPack = $unittypes[$pack]; @endphp
   
    <tr>
     
        <td>{{ $minW}}</td>
        <td>{{ $avgW }}</td>
        <td>{{ $maxW }}</td>
        <td><strong>{{ $tmpUnittype->name }}</strong></td>
    </tr>
    <tr>

        <td>{{ $minpp }}</td>
        <td>{{ $avgpp}}</td>
        <td>{{ $avgpp }}</td>
        <td><strong>{{ $tmpUnitPack->name }}</strong></td >  
    </tr>
 
    </table>
   
    

    @endif



<button type="button" onclick="history.back()" class="btn btn-outline-info">Back</button>
<a class="btn btn-outline-info" href="{{ route('index.create', ['jobCardId' => $jobcarditem[0]['jobCardId'], 'other' => $jobcarditem[0]['other'], 'state' => $jobcarditem[0]['stateId'],'jobcarditemId' => $jobcarditem[0]['id'] ,'productId' => $jobcarditem[0]['productId']])  }}">Print</a>


</div>
</form>

</div>
</body>
</html>