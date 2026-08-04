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

<title>Add job_cards Form - Laravel 8 CRUD</title>

</head>
<script>





    


   
    

    function calculate() {

    

    // alert('mmm');

        var e = document.getElementById("unitId");
       
        var valueE = e.options[e.selectedIndex].value;
        var textE = e.options[e.selectedIndex].text;
        var valueN = -9;

       
      

        var lengths = document.getElementById("product_length").value;
        var widths = document.getElementById("totalWidth").value;
        var microns = document.getElementById("thickness").value;
        let bagtyp = 'roll';

        // var rollName = Math.floor(widths)+'mm'+' x '+Math.floor(microns)+'mic'+;
        // alert(''+rollName);

        




      


        for (var key in valArray) {
     
            var rtnComp = textE.localeCompare(key);
      
            if (rtnComp == 0) {
              
              var unitDivide = 1000 / valArray[key];

              valueN = valArray[key];
              //alert(' Unit Val: ' + valArray); 
              var weightPerProduct1000 = document.getElementById("WeightPerProduct").value;
              //alert('100000: ' + weightPerProduct1000); 
            
            }
        }

   
        var length = document.getElementById("product_length").value;
       
        var micron = document.getElementById("thickness").value;
       
        var width = document.getElementById("totalWidth").value;

   

        var weightPerProduct1000 = document.getElementById("WeightPerProduct").value;

       // alert('kkkk'+weightPerProduct1000);

    
        
        //var product = document.getElementById("productId").value;
        var unit = document.getElementById("unitId").value;
        var qnt = document.getElementById("qnt").value;
        //var process = document.getElementById("processid").checked = true;
        //alert('hhhh'+valueN);
        var weightperQntIn1000 = -9;
        //alert(' Q: ' + qnt);
       
            var totalQnt = valueN * qnt;
           // alert(' Total : '+  totalQnt);
            
            
            //var total = document.getElementById('totalqnt').value = totalQnt ;

            var qntPer1000 = totalQnt / 1000;
            //alert('Avg'+  qntPer1000 );
            
            //--------------------------------------------------------------------------------

            weightperQntIn1000 = qntPer1000 * weightPerProduct1000;
           // alert('Total Quantity' + weightperQntIn1000 );
            var  perc = 0.03 *  weightperQntIn1000 ;//percent increase

            var finalTotal = perc +  weightperQntIn1000 ;
        
            var finalTotalz = qnt *  weightPerProduct1000 ;
            //alert('Avg'+weightperQntIn1000  );
         
            //--------------------------------------------------------------------------------

            var testingweight =  micron * width / 5600;
            //testing weight;

           //----------------------------------------------------------------------------------
            var constVar = 5.325;
            var WeightPerRoll = ((width/10 * length/10 * micron/1000)/constVar) / 1000;

           //----------------------------------------------------------------------------------          
            var centerfold = weightPerProduct1000 * qnt;
            var  perc = 0.03 *  centerfold;
            var centerfoldcalc = perc + centerfold;
            //--------------------------------------------------------------------------------------------
            
           

            var bagtype = document.getElementById("bagType").value;
            var bagtype = document.getElementById("bagType").value;
            var comboBoxBagType = document.getElementById("bagType");
            var bagTypeValue = comboBoxBagType.value;
            var bagTypeText = comboBoxBagType.options[comboBoxBagType.selectedIndex].text;
            
            

            if(bagTypeText.trim() == 'Rolls') {
              
                   document.getElementById('extruding').value = finalTotalz;
                   document.getElementById('bagging').value = 0;
                   
                }
              

          
            else if(bagTypeText.trim() == 'Centre Fold') {

                   document.getElementById('bagging').value = centerfoldcalc ;
                   document.getElementById('extruding').value = qnt ;
      
                }  

            
            else  {
              document.getElementById('bagging').value = qnt ;
              document.getElementById('extruding').value = finalTotal ;

            }  
        
          

            
        }
       
       

      

       
              

  
            
            
          
         

             
           

    

</script>
<body>
<div>
<br>
</div>
<div class="container mt-2">
<div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h3>Update Job Cards</h3>
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


<button class="btn btn-outline-info" name="myButton"  id="clone" value="clone" >Clone</button>

<strong>Customer:</strong>
<select  id="customerId" name="customerId"  class="js-example-basic-single">
<option  >-- select customer --</option>
@foreach($customers as $customer)
<option value="{{ $customer->id }}"  @if($job_card[0]['customerId']==$customer->id) selected @endif >{{ $customer->name }}</option>
@endforeach
</select>
</div>

<script>
    window.onload = function() {
      calculate();
    };
</script>

<script type="text/javascript">

function my_code(){
//alert(" Alert inside my_code function");
}

window.onload=calculate();
</script>


<strong>Product:</strong>

<select  id="productId" name="productId"   >
@foreach($porducts as $porduct)
<option value="{{ $porduct->id }}"   @if($job_card[0]['productId']==$porduct->id) selected @endif>{{ $porduct->name }}</option>
@endforeach
</select>
</div>



<strong>Length</strong>
<input type="text" id="product_length" name="product_length" value="{{ $product[0]['product_length'] }}" >
<br>
<strong>Width:</strong>
<input type="text"  id="product_Width" name="product_Width" value="{{$product[0]['product_Width']}}" >



<strong>Thickness:</strong>
<input type="text"  id="thickness" name="thickness"   value="{{$product[0]['thickness']}}" >

<strong>Gusset Width:</strong>
<input type="text"  id="gussetWidth" name="gussetWidth"  value="{{$product[0]['gussetWidth']}}">

<strong>Total Width:</strong>
<input type="text" id="totalWidth" name="totalWidth" value="{{$product[0]['totalWidth']}}">

 
Material Type:
<select name="" id=""  >
@foreach( $materialtypes as $materialtype)
<option value="{{  $materialtype->id }}"  @if($product[0]['materialTypeId']==$materialtype->id) selected @endif>{{ $materialtype->name }}</option>
@endforeach
</select>
</div>


Colour:
<select name="color"  id="porducts"   placeholder="-- Select Product --">
@foreach($colourtypes as $colourtype)
<option value="{{ $colourtype->id }}" @if($product[0]['color']==$colourtype->id) selected @endif>{{ $colourtype->name }}</option>
@endforeach
</select>
</div>
<br> 

<strong>Bag Type:</strong>
<select  name="bagType"   id="bagType">
@foreach($bagtypes as $bagtype)
<option value="{{ $bagtype->id }}" @if($product[0]['bagType']==$bagtype->id) selected @endif>{{ $bagtype->name }}</option>
@endforeach
</select>
</div> 

<strong>Unit</strong>
<select name="unitId" id="unitId" >
@foreach($unittypes as $unittype)
<option value="{{ $unittype->id }}" @if($product[0]['unitTypeId']==$unittype->id) selected @endif>{{ $unittype->name }}</option>
@endforeach
</select>
</div>
</div>




&nbsp&nbsp<strong>Quantity:</strong>
<input type="text" id="qnt" name="qnt" onchange="calculate()" value="{{$job_card[0]['qnt']}}"  >

<strong>WeightPerProduct</strong>
<input type="text"  name="WeightPerProduct"  id="WeightPerProduct"   value="{{$product[0]['WeightPerProduct']}}"   >
<strong>Extruding</strong>
<input type="text"  name="extruding"  id="extruding"   >
<strong>Bagging </strong>
<input type="text"  name="bagging"  id="bagging"    ><br>
<button type="button" onclick="calculate()"  padding right=5px  class="btn btn-dark"> Calculate </button>&nbsp&nbsp&nbsp&nbsp
<strong>Enter Start Date:</strong>
<input type="date" name="startDate"   value="{{$job_card[0]['startDate']}}"  ><br>
<br>

<input type="hidden" name="customerId"     value="{{$job_card[0]['customerId']}}"  ><br>
<input type="hidden" name="id"    value="{{$job_card[0]['id']}} " ><br>


<!-- <button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Save</button> -->
</form>
@foreach($jobcarditems as $jobcarditem)
@php
    $tmpProcesstype = isset($processtypes[$jobcarditem['processId']]) ? $processtypes[$jobcarditem['processId']] : '   ';
@endphp

@endforeach


<button class="btn btn-outline-info"  onclick="javascript:window.history.back();">Go Back</button>
<button type="submit" class="btn btn-outline-info"  >Submit</button>
</div>
</form>

<style>
    hr {
  height:5px;
  border-width:0;
  background-color:#00A4BD;

}
</style>
<hr>
<h3>Job Card items</h3>

<table class="table table-striped" >
    <tr>
    <th  scope="col"> Id</th>
    <th  scope="col"> Date/Time </th>
     <th  scope="col"> Product </th>
    <th  scope="col"> Process</th>
    <th  scope="col"> Qnt</th>
    <th  scope="col"> Unit Id</th>
    <th  scope="col"> Barcode</th>
    <th  scope="col"> Outstanding</th>
    <th  scope="col"> State</th>
    <th  scope="col" width="300px"> Action</th>
    
    </tr>


@foreach($jobcarditems as $jobcarditem)
@php $tmpProduct = $porducts[$jobcarditem['productId']]; @endphp
@php $tmpUnittype = $unittypes[$jobcarditem['unitId']]; @endphp
@php $tmpProcesstype = $processtypes[$jobcarditem['processId']]; @endphp
@php $tmpstatus = $statustypes[$jobcarditem['stateId']]; @endphp

<tr>
<td><strong>{{$jobcarditem['id']}}</strong></td>
<td><strong>{{$jobcarditem['created_at']}}</strong></td>
<td><strong>{{$tmpProduct->name }}</strong></td>
<td><strong>{{$tmpProcesstype->name }}</strong></td>
<td><strong>{{$jobcarditem['outstanding']}}</strong></td>
<td><strong>{{$tmpUnittype->name}}</strong></td>
<td><strong>{{$jobcarditem['barcode']}}</strong></td>
<td><strong>{{$jobcarditem['qnt']}}</strong></td>
<td><strong>{{$tmpstatus->name}}</strong></td>

<td>
    <div class="btn-group" role="group">
        <form action="{{ route('actionjobitems.actionupdate',['item' => $jobcarditem['id']]) }}" method="GET" >
            <button type="submit" class="btn btn-outline-info">View</button>
        </form>
        &nbsp;
        &nbsp;
        <form action="{{ route('actionjobitems.actiondelete',['item' => $jobcarditem['id']]) }}" method="GET" >
            <button type="submit" class="btn btn-outline-danger"  onclick="return confirm('Are you sure you want to delete')">Delete</button>
        </form>
    </div>
</td>
</tr>

@endforeach

</body>
</html>