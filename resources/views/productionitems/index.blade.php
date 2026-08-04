<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http="refresh" content="30">
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>







<!--------------------------------------------------------------------------------------------------------------------------->

<link rel="stylesheet" href="{{asset('style/style.css')}}">
</head>
<script src="{{ asset('public/js/script.js') }}"></script>
<body>
<div class="container mt-2">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left mb-2">
                <br>
                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <strong>Success! </strong><strong>{{ $message }}</strong>
                </div>
                @endif

                @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible">  
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
                            box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.3);
                        }

                    #hidden_cell {
                    visibility: hidden; /* Or visibility: collapse; */
                    }

                    .alert.alert-success.alert-dismissible {
                        padding: 31px;
                        font-size: 18px;
                    }

                    .alert.alert-danger.alert-dismissible {
                        padding: 31px;
                        font-size: 18px;
                    }
                    .centred {
                        text-align: center;
                        }
                                            </style>

                <style>
                    .alert.alert-success.alert-dismissible,
                    .alert.alert-danger.alert-dismissible {
                        padding: 31px;
                        font-size: 18px;
                    }
                </style>

                @if(isset($productions) && !empty($productions))
                    @foreach($productions as $machineryId => $machine)
                        @php 
                            $tmpMachinetype = $machinetypes[$machineryId];
                            $totalQuantity = 0;
                            $totalExtruding = 0;
                            $totalBagging = 0;

                            foreach ($machine['productions'] as $production) {
                                foreach ($production['items'] as $item) {
                                    $totalQuantity += $item['quantity'];
                                    $tmpProcesstype = $processtypes[$production['details']['processId']] ?? null;
                                    if ($tmpProcesstype->name == 'Extruding') {
                                        $totalExtruding += $item['quantity'];
                                    } elseif ($tmpProcesstype->name == 'Bagging') {
                                        $totalBagging += $item['quantity'];
                                    }
                                }
                            }
                        @endphp

                        <div class="mb-3">

                        <!-- <table class="table">
                            <thead>
                                <tr>
                                <th colspan="4" class="centred">Current Jobcard State</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                <th>Quantity</th>
                                <td>Mark</td>
                                <th>Outstanding</th>
                                <td>@mdo</td>
                                </tr>

                            </tbody>
                            </table> -->
                            <br>
                    

                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>{{ $tmpMachinetype->name }} <h3></h3></th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th>Total Extruding Production: {{ $totalExtruding }} kgs</th>
                                        <th>Total Bagging Production: {{ $totalBagging }} Bales</th>
                                    </tr>
                                </thead>
                            </table>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                       <th>Date Time</th>
                                        <th>Employee</th>
                                        <th>Process</th>
                                        <th>Shift</th>                                   
                                        <th>Items</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($machine['productions'] as $production)
                                    @php 
                                        $tmpuser = $user[$production['details']['userId']] ?? null; 
                                        $tmpShifttype = $shifttypes[$production['details']['shiftId']] ?? null; 
                                        $tmpProcesstype = $processtypes[$production['details']['processId']] ?? null; 
                                    @endphp
                                    <tr>

                                    <td>{{ $production['details']['created_at'] }} </td>
                                        <td><strong>{{ is_object($tmpuser) ? $tmpuser->name : '' }}</strong></td>
                                        <td>{{ $tmpProcesstype->name }}</td>
                                        <td>{{ $tmpShifttype->name }}</td>
                                   
                                        <td>
                                        <ul>
                                            @php
                                            $groupedProducts = [];
                                            foreach ($production['items'] as $item) {
                                                $productName = $porducts[$item['productId']]->name;
                                                if (!isset($groupedProducts[$productName])) {
                                                    $groupedProducts[$productName] = [
                                                        'quantity' => 0,
                                                        'unitType' => $unittypes[$item['unitId']] ?? '',
                                                        'items' => []
                                                    ];
                                                }
                                                $groupedProducts[$productName]['quantity'] += $item['quantity'];
                                                $groupedProducts[$productName]['items'][] = $item;
                                            }
                                            @endphp

                                            @foreach($groupedProducts as $productName => $productData)
                                                <li>
                                                    {{ $productName }} <strong> 
                                                        @if($tmpProcesstype->name == 'Extruding')
                                                            {{ $productData['quantity'] }} Kg
                                                        @else
                                                            {{ $productData['quantity'] }} Bales
                                                        @endif
                                                    </strong>
                                                    <ul>
                                                        @foreach($productData['items'] as $item)
                                                            <!-- <li>
                                                                <span style="margin-right: 10px;">Job: {{ $item['jobcarditemId'] }}</span>
                                                                @if($tmpProcesstype->name == 'Extruding')
                                                                <span style="margin-right: 10px;"><strong>{{ $item['quantity'] }} Kg</strong></span>
                                                                @else
                                                                <span style="margin-right: 10px;"><strong>{{ $item['quantity'] }} Bales</strong></span>
                                                                @endif
                                                                <span style="margin-right: 10px;">{{ $productData['unitType']->name }}</span>
                                                            </li> -->
                                                        @endforeach
                                                    </ul>
                                                </li>
                                            @endforeach
                                        </ul>
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
                    <strong>No production available for this Jobcard</strong> {{ $message }}
                </div>
                @endif
            </div>
        </div>
    </div>

<div>

</body>
</html>
