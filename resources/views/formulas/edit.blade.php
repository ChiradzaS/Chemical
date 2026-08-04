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

<title>Create Fomula</title>




<style>
 

 .highlights {
 background-color: #B0C4DE;
}


     input.larger {
       width: 40px;
       height: 40px;
     }

     input.large {
       width: 200px;
       height: 40px;
     }

     /* The switch - the box around the slider */
.switch {
 position: relative;
 display: inline-block;
 width: 60px;
 height: 34px;
}

/* Hide default HTML checkbox */
.switch input {
 opacity: 0;
 width: 0;
 height: 0;
}

/* The slider */
.slider {
 position: absolute;
 cursor: pointer;
 top: 0;
 left: 0;
 right: 0;
 bottom: 0;
 background-color: grey;
 -webkit-transition: .4s;
 transition: .4s;
}

.slider:before {
 position: absolute;
 content: "";
 height: 26px;
 width: 26px;
 left: 4px;
 bottom: 4px;
 background-color: white;
 -webkit-transition: .4s;
 transition: .4s;
}

input:checked + .slider {
 background-color: #2196F3;
}

input:focus + .slider {
 box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
 -webkit-transform: translateX(26px);
 -ms-transform: translateX(26px);
 transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
 border-radius: 34px;
}

.slider.round:before {
 border-radius: 50%;
}
 
</style>
<script>
$(document).ready(function(){
    $('.js-example-basic-single').select2({
        theme: "classic"
    });
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script>
            function myFunction() {
  
  var checkBox = document.getElementById("active");
  
  const text = document.getElementById("text");
   


    text.textContent = "New text here";

  
  if (checkBox.checked == true){
    text.textContent = "Formula Active";
    document.getElementById("active").value = 1;
  } else if(checkBox.checked == false) {
    text.textContent = "Formula Not Active";
    document.getElementById("active").value = 0;
  }
}
        </script>
</head>
<body>
    <div>

<br>
</div>
<div class="container mt-2">
<div class="row">
    <div class="col-lg-12 margin-tb">
<div class="pull-left mb-2">
<h2>Create Formula</h2>
</div>
<div class="pull-right">
<br>
</div>
</div>
</div>
@if(session('status'))
<div class="alert alert-success mb-1 mt-1">
{{ session('status') }}
</div>
@endif
<form action="{{ route('fomulas.update',$formula->id') }}" method="POST" enctype="multipart/form-data">
@csrf
<label class="switch">
  <input type="checkbox" id="active" name="active" onclick="myFunction()">
  <span class="slider round"></span>
</label>
<h4 id="text" >Formula Deactivated</h4>


<div class="row">
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Formula Name</strong>
<input type="text" name="name"  class="form-control form-control-sm" value="{{ $formula->name }}">
@error('name')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12">
<div class="form-group">
<strong>Fomula type </strong>
<select name="type"  id="formulaType"      class="form-control form-control-sm" >
@foreach($fomulartypes as $fomulartype)
<option value="{{ $fomulartype->id }}" >{{ $fomulartype->name }}</option>
@endforeach
</select>
@error('type')
<div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
@enderror
</div>
</div>


<br>
<button type="submit" padding-right=5px class="btn btn-primary btn-sm" >Submit</button> 
</div>
</form>
</body>
</html>