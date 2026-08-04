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
<meta name="csrf-token" content="{{ csrf_token() }}">


<!--------------------------------------------------------------------------------------------------------------------------->
 
<link rel="stylesheet" href="{{asset('style/style.css')}}" >


    <title>Document</title>
</head>
<body>
  <br>
    <hr>

<h3>Requested Product List</h3>

<hr><br>
<br>
<table class="table table-bordered">
                      <thead>
                        <tr>   
                        
                          <!-- <th>User</th> -->
                          <th>Product</th>
                          <th>Colour </th>
                          <th>Material </th>
                          <th>Bag-Type</th>
                          <th class="text-center" style="width: 300px;">State</th>
                          <th class="text-center" style="width: 300px;">Action</th>
                         
                          

                        </tr>
    </thead>    
                        <tr>

                        @foreach($data as $item)
                        @php $tmpc = $colourtypes[$item->colour] ?? 'none'; @endphp
                        @php $tmpb = $bagtypes[$item->bagType] ?? 'none'; @endphp
                        @php $tmpm = $materialtypes[$item->material] ?? 'none'; @endphp
                        @php $tmpuser = $operators[$item->userId]??''; @endphp
        
                        <tr>

                   
                        <!-- <td>{{ $tmpuser->name ??''}}</td> -->

                          <!-- <td>{{ $item->width}}mm  ({{ $item->gusset}}mm) x  {{ $item->length}}mm  x  {{ $item->micron}}mic </td> -->
                           <td>
                          {{ $item->width}}mm x 
                            @if($item->gusset)
                            ({{ $item->gusset/2 }}mm + {{ $item->gusset/2 }}mm) x 
                            @endif
                            {{ $item->length}}mm x {{ $item->micron}}mic
                            </td>
                          <td>{{ is_string($tmpc) ? $tmpc : $tmpc->name }}</td>
                          <td>{{ is_string($tmpm) ? $tmpm : $tmpm->name }}</td>
                          <td>{{ is_string($tmpb) ? $tmpb : $tmpb->name }}</td>
                          <td class="text-center">                    
                            
                            @if($item->stateId == 1)

                            <span class="badge badge-pill badge-info">requested</span>

                            @elseif($item->stateId == 3)

                            <span class="badge badge-pill badge-danger">rejected</span>

                            @else

                            <span class="badge badge-pill badge-success">approved</span>

                            @endif
                          
                          
                          </td>

                          <td class="text-center">

                        <button type="button" class="btn btn-outline-success" onclick="updateStatus({{$item->id}}, 2);showSpinner();">Approve</button>
                        <button type="button" class="btn btn-outline-danger"  onclick="updateStatus({{$item->id}}, 3); showSpinner();">&nbsp;&nbsp;Reject&nbsp;&nbsp;</button>

                    </td>

                    

                    <script >



function updateStatus(productId, statusId) {

                  //alert(productId);
                  //alert(statusId);

                  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

                  $.ajax({
                      url: "{{ route('changeOnlineProductstate') }}",
                      type: 'POST',
                      data: {
                          _token: CSRF_TOKEN,
                          productId: productId,
                          statusId: statusId
                      },
                      dataType: 'json',
                      success: function(response) {
                          if (response== 1 ) {
                            location.reload();
                            alert('hoyooo');
                              location.reload();
                          } else {
                            location.reload();
                          }
                      },
                      error: function(xhr, status, error) {
                        location.reload();
                      }
                  });
                   
          }



                    </script>





                        </tr>
                        @endforeach

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