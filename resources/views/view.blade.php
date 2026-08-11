<!DOCTYPE html>
<html lang="en">
<head>
<title>Laravel</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">

<link rel="stylesheet" href="styles.css">
</head>
<body>

<section>

<script>
function myFunc1(ok) {
    document.getElementById('spinnerContainer').style.display = 'block';
    setTimeout(function () {
        document.getElementsByName('myFrame')[0].src = ok;
        document.getElementsByName('myFrame')[0].onload = function () {
            document.getElementById('spinnerContainer').style.display = 'none';
        };
    }, 50);
}
</script>

<style>
#cssmenu,
#cssmenu ul,
#cssmenu li,
#cssmenu a {
  border: none;
  line-height: 1;
  margin: 0;
  padding: 0;
}
#cssmenu {
  height: 37px;
  display: block;
  border: 1px solid;
  border-radius: 5px;
  width: auto;
  border-color: #080808;
  margin: 0;
  padding: 0;
}
#cssmenu > ul {
  list-style: inside none;
  margin: 0;
  padding: 0;
}
#cssmenu > ul > li {
  list-style: inside none;
  float: left;
  display: inline-block;
  position: relative;
  margin: 0;
  padding: 0;
}
#cssmenu.align-center > ul { text-align: center; }
#cssmenu.align-center > ul > li { float: none; margin-left: -3px; }
#cssmenu.align-center ul ul { text-align: left; }
#cssmenu.align-center > ul > li:first-child > a { border-radius: 0; }
#cssmenu > ul > li > a {
  outline: none;
  display: block;
  position: relative;
  text-align: center;
  text-decoration: none;
  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.4);
  font-weight: 700;
  font-size: 13px;
  font-family: Arial, Helvetica, sans-serif;
  border-right: 1px solid #080808;
  color: #ffffff;
  padding: 12px 20px;
}
#cssmenu > ul > li:first-child > a { border-radius: 5px 0 0 5px; }
#cssmenu > ul > li > a:after {
  content: "";
  position: absolute;
  border-right: 1px solid;
  top: -1px;
  bottom: -1px;
  right: -2px;
  z-index: 99;
  border-color: #3c3c3c;
}
#cssmenu ul li.has-sub:hover > a:after { top: 0; bottom: 0; }
#cssmenu > ul > li.has-sub > a:before {
  content: "";
  position: absolute;
  top: 18px;
  right: 6px;
  border: 5px solid transparent;
  border-top: 5px solid #ffffff;
}
#cssmenu > ul > li.has-sub:hover > a:before { top: 19px; }
#cssmenu > ul > li.has-sub:hover > a {
  padding-bottom: 14px;
  z-index: 999;
  border-color: #3f3f3f;
}
#cssmenu ul li.has-sub:hover > ul,
#cssmenu ul li.has-sub:hover > div { display: block; }
#cssmenu > ul > li.has-sub > a:hover,
#cssmenu > ul > li.has-sub:hover > a {
  background: #3f3f3f;
  border-color: #3f3f3f;
}
#cssmenu ul li > ul,
#cssmenu ul li > div {
  display: none;
  width: auto;
  position: absolute;
  top: 38px;
  background: #3f3f3f;
  border-radius: 0 0 5px 5px;
  z-index: 999;
  padding: 10px 0;
}
#cssmenu ul li > ul { width: 200px; }
#cssmenu ul ul ul { position: absolute; }
#cssmenu ul ul li:hover > ul { left: 100%; top: -10px; border-radius: 5px; }
#cssmenu ul li > ul li {
  display: block;
  list-style: inside none;
  position: relative;
  margin: 0;
  padding: 0;
}
#cssmenu ul li > ul li a {
  outline: none;
  display: block;
  position: relative;
  font: 10pt Arial, Helvetica, sans-serif;
  color: #ffffff;
  text-decoration: none;
  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.5);
  margin: 0;
  padding: 8px 20px;
}
#cssmenu,
#cssmenu ul ul > li:hover > a,
#cssmenu ul ul li a:hover {
  background: #3c3c3c;
  background: linear-gradient(top, #3c3c3c 0%, #222222 100%);
}
#cssmenu > ul > li > a:hover { background: #080808; color: #ffffff; }
#cssmenu ul ul a:hover { color: #ffffff; }
#cssmenu > ul > li.has-sub > a:hover:before { border-top: 5px solid #ffffff; }

/* section divider inside dropdown */
#cssmenu ul li > ul li.divider {
  border-top: 1px solid #555;
  margin: 6px 10px;
  padding: 0;
  pointer-events: none;
}
#cssmenu ul li > ul li.section-label > a {
  color: #0ea5e9;
  font-size: 9pt;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .08em;
  padding: 6px 20px 2px;
  cursor: default;
  pointer-events: none;
}

.new-tag {
  background-color: #ff4444;
  color: white;
  font-size: 10px;
  padding: 2px 4px;
  border-radius: 3px;
  margin-left: 5px;
  font-weight: bold;
}
.chem-tag {
  background-color: #6366f1;
  color: white;
  font-size: 10px;
  padding: 2px 4px;
  border-radius: 3px;
  margin-left: 5px;
  font-weight: bold;
}

* { box-sizing: border-box; }
html, body {
  min-height: 100%;
  margin: 0;
  padding: 0;
  overflow-x: hidden;  /* no horizontal scroll bar */
  overflow-y: auto;    /* allow vertical scrolling so content isn't cut off */
}
body { font-family: Arial, Helvetica, sans-serif; }
header {
  background-color: #99D9EA;
  padding: 0px;
  text-align: center;
  font-size: 35px;
  color: #99D9EA;
  height: 150px;
}
nav {
  float: left;
  width: 7%;
  height: 500px;
  background: #ccc;
  padding: 20px;
}
nav ul { list-style-type: none; padding: 0; }
article {
  float: left;
  padding: 0px;
  width: 100%;
  background-color: #f1f1f1;
  min-height: calc(100vh - 37px); /* fills remaining space below the menu, can grow */
}
section:after { content: ""; display: table; clear: both; }
footer {
  background-color: #080808;
  font-size: 15px;
  padding: 5px;
  text-align: center;
  color: white;
  margin-top: auto;
}
.spinner-container {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 1000;
}
.spinner {
  width: 17.6px;
  height: 17.6px;
  border-radius: 17.6px;
  box-shadow: 44px 0px 0 0 rgba(0,0,0,.2), 35.6px 26px 0 0 rgba(0,0,0,.4),
              13.64px 41.8px 0 0 rgba(0,0,0,.6), -13.64px 41.8px 0 0 rgba(0,0,0,.8),
              -35.6px 26px 0 0 #000;
  animation: spinner-b87k6z 1.4s infinite linear;
}
@keyframes spinner-b87k6z { to { transform: rotate(360deg); } }
@media (max-width: 600px) { nav, article { width: 100%; height: auto; } }
</style>

<div id='cssmenu'>
<ul>

  {{-- Home --}}
  <li><a href="#" onclick="myFunc1('subMain.php')"><span>Home</span></a></li>

  {{-- Customers --}}
  <li class='active has-sub'><a href='#'><span>Customers</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('customers/create')"><span>Create</span></a></li>
      <li><a href="#" onclick="myFunc1('customers')"><span>List</span></a></li>
     
    </ul>
  </li>

  {{-- Products --}}
  <li class='active has-sub'><a href='#'><span>Chemical Products</span></a>
    <ul>
      {{-- Plastic / standard products --}}
      <!-- <li class='section-label'><a href='#'><span>Plastic Products</span></a></li>
      <li><a href="#" onclick="myFunc1('porducts/create')"><span>Create</span></a></li>
      <li><a href="#" onclick="myFunc1('porducts?productType=work-In-Progress')"><span>WP List</span></a></li>
      <li><a href="#" onclick="myFunc1('porducts?productType=finished-Product')"><span>List</span></a></li>
      <li><a href="#" onclick="myFunc1('onlineproducts')"><span>Online Products</span></a></li> -->

      {{-- Chemical products --}}
      <li class='divider'></li>
      <!-- <li class='section-label'><a href='#'><span>Chemical Products</span></a></li> -->
      <li><a href="#" onclick="myFunc1('productchemicals')"><span>Create <span class="chem-tag">CHEM</span></span></a></li>
      <li><a href="#" onclick="myFunc1('chemicalproductlist')"><span>List <span class="chem-tag">CHEM</span></span></a></li>
    </ul>
  </li>

  {{-- Orders --}}
  <li class='active has-sub'><a href='#'><span>Orders</span></a>
    <ul>

        <li><a href="#" onclick="myFunc1('chemicalorderlist')"><span>Order list <span class="chem-tag">CHEM</span></span></a></li>
        <li><a href="#" onclick="myFunc1('chemicalorder')"><span>Create Order <span class="chem-tag">CHEM</span></span></a></li>
      <li class='divider'></li>

    </ul>
  </li>

  {{-- Employee --}}
  <!-- <li class='active has-sub'><a href='#'><span>Employee</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('employees/create')"><span>Create</span></a></li>
      <li><a href="#" onclick="myFunc1('employees')"><span>List</span></a></li>
    </ul>
  </li> -->

  {{-- Machine --}}
  <li class='active has-sub'><a href='#'><span>Machine</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('machinery/create')"><span>Create</span></a></li>
      <li><a href="#" onclick="myFunc1('machinery')"><span>List</span></a></li>
      <!-- <li><a href="#" onclick="myFunc1('terminals')"><span>Active Machines</span></a></li> -->
    </ul>
  </li>

  {{-- Users --}}
  <li class='active has-sub'><a href='#'><span>Users</span></a>
    <ul>
      <li><a href='#' onclick="myFunc1('users')"><span>List</span></a></li>
      <li><a href='#' onclick="myFunc1('users/create')"><span>Create</span></a></li>
      <!-- <li class='has-sub'><a href='#'><span>User Details</span></a>
        <ul>
          <li><a href='#' onclick="myFunc1('userdetails')"><span>List</span></a></li>
          <li><a href='#' onclick="myFunc1('userdetails/create')"><span>Create</span></a></li>
        </ul>
      </li> -->
    </ul>
  </li>

  {{-- Job Cards --}}
  <li class='active has-sub'><a href='#'><span>Job Cards</span></a>
    <ul>
      {{-- Plastic job cards --}}
      <!-- <li class='section-label'><a href='#'><span>Plastic Job Cards</span></a></li>
      <li><a href="#" onclick="myFunc1('reactjob')"><span>React Create <span class="new-tag">NEW</span></span></a></li>
      <li><a href="#" onclick="myFunc1('reactjoblist')"><span>React List <span class="new-tag">NEW</span></span></a></li>
      <li><a href="#" onclick="myFunc1('job_cards/create')"><span>Create</span></a></li>
      <li><a href="#" onclick="myFunc1('job_cards')"><span>List</span></a></li> -->

      {{-- Chemical job cards --}}
      <li class='divider'></li>
      <!-- <li class='section-label'><a href='#'><span>Chemical Job Cards</span></a></li> -->
      <li><a href="#" onclick="myFunc1('chemicaljobcard')"><span>Create <span class="chem-tag">CHEM</span></span></a></li>
      <li><a href="#" onclick="myFunc1('chemicaljobcardlist')"><span>List <span class="chem-tag">CHEM</span></span></a></li>
    </ul>
  </li>

  {{-- Production --}}
  <li class='active has-sub'><a href='#'><span>Production</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('allocateproductions')"><span>Production List</span></a></li>
    </ul>
  </li>

  {{-- Stock --}}
  <li class='active has-sub'><a href='#'><span>Stock</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('stocks')"><span>Stock</span></a></li>
      <li><a href="#" onclick="myFunc1('reactstockadjustment')"><span>Stock Adjustment</span></a></li>
      <!-- <li><a href="#" onclick="myFunc1('pricings')"><span>Pricing</span></a></li> -->
    </ul>
  </li>

  {{-- Allocation --}}
  <!-- <li class='active has-sub'><a href='#'><span>Allocation</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('react-app')"><span>Allocation Gantt</span></a></li>
    </ul>
  </li> -->

  {{-- Deliveries --}}
  <li class='active has-sub'><a href='#'><span>Invoices</span></a>
    <ul>
      {{-- Plastic deliveries --}}
      <!-- <li class='section-label'><a href='#'><span>Plastic Deliveries</span></a></li>
      <li><a href="#" onclick="myFunc1('reactdeliverylist')"><span>React List <span class="new-tag">NEW</span></span></a></li>
      <li><a href="#" onclick="myFunc1('reactcreatedelivery')"><span>React Create <span class="new-tag">NEW</span></span></a></li>
      <li><a href="#" onclick="myFunc1('deliveries/show')"><span>Create Invoice/Delivery Note</span></a></li>
      <li><a href="#" onclick="myFunc1('deliveries')"><span>Delivery Note List</span></a></li> -->

      {{-- Chemical deliveries --}}
      <li class='divider'></li>
      <!-- <li class='section-label'><a href='#'><span>Chemical Deliveries</span></a></li> -->

      <li><a href="#" onclick="myFunc1('chemicaldeliverylist')"><span>List <span class="chem-tag">CHEM</span></span></a></li>
      <li><a href="#" onclick="myFunc1('chemicaldeliveries')"><span>Create<span class="chem-tag">CHEM</span></span></a></li>
    </ul>
  </li>





  {{-- Reporting --}}
  <li class='active has-sub'><a href='#'><span>Supply</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('formulas')"><span>Chemical Formulas</span></a></li>
      <li><a href="#" onclick="myFunc1('jobcardReport')"><span>Supply Stock</span></a></li>
      <li><a href="#" onclick="myFunc1('chemicalmaterial')"><span>Raw Material</span></a></li>
    </ul>
  </li>

  {{-- Settings --}}
  <li class='active has-sub'><a href='#'><span>⚙️ Settings</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('settings')"><span>General</span></a></li>
      <li class='divider'></li>
      <li class='has-sub'><a href='#'><span>Type Settings</span></a>
        <ul>
          <li><a href="#" onclick="myFunc1('types/create')"><span>Create</span></a></li>
          <li><a href="#" onclick="myFunc1('types')"><span>List</span></a></li>
        </ul>
      </li>
    </ul>
  </li>



  
  {{-- Barcode --}}
   <li class='active has-sub'><a href='#'><span>About</span></a>
    <ul>
      <li><a href="#" onclick="myFunc1('about')"><span>Company Info</span></a></li>
    </ul>
  </li> 

  {{-- Logout --}}
  <!-- <li class='last'><a href="{{ route('signout') }}"><span>Logout</span></a></li> -->

</ul>
</div>

<article>
  <iframe src="subMain.php" name="myFrame" frameborder="0" style="height: calc(100vh - 37px); width: 100%; display:block; border:0;"></iframe>
</article>

</section>

<div id="spinnerContainer" class="spinner-container">
  <div class="spinner"></div>
</div>

</body>
</html>