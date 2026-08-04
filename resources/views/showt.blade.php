<!DOCTYPE html>
<html lang="en">
<head>
<title>CSS Template</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">






<!--------------------------------------------------------------------------------------------------------------------------->
<script> 
         function myFunc1(ok) {
             var site = ok;
             document.getElementsByName('myFrame')[0].src = site;   
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
#cssmenu.align-center > ul {
  text-align: center;
}
#cssmenu.align-center > ul > li {
  float: none;
  margin-left: -3px;
}
#cssmenu.align-center ul ul {
  text-align: left;
}
#cssmenu.align-center > ul > li:first-child > a {
  border-radius: 0;
}
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
#cssmenu > ul > li:first-child > a {
  border-radius: 5px 0 0 5px;
}
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
#cssmenu ul li.has-sub:hover > a:after {
  top: 0;
  bottom: 0;
}
#cssmenu > ul > li.has-sub > a:before {
  content: "";
  position: absolute;
  top: 18px;
  right: 6px;
  border: 5px solid transparent;
  border-top: 5px solid #ffffff;
}
#cssmenu > ul > li.has-sub:hover > a:before {
  top: 19px;
}
#cssmenu > ul > li.has-sub:hover > a {
  padding-bottom: 14px;
  z-index: 999;
  border-color: #3f3f3f;
}
#cssmenu ul li.has-sub:hover > ul,
#cssmenu ul li.has-sub:hover > div {
  display: block;
}
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
#cssmenu ul li > ul {
  width: 200px;
}
#cssmenu ul ul ul {
  position: absolute;
}
#cssmenu ul ul li:hover > ul {
  left: 100%;
  top: -10px;
  border-radius: 5px;
}
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
  background: -moz-linear-gradient(top, #3c3c3c 0%, #222222 100%);
  background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #3c3c3c), color-stop(100%, #222222));
  background: -webkit-linear-gradient(top, #3c3c3c 0%, #222222 100%);
  background: -o-linear-gradient(top, #3c3c3c 0%, #222222 100%);
  background: -ms-linear-gradient(top, #3c3c3c 0%, #222222 100%);
  background: linear-gradient(top, #3c3c3c 0%, #222222 100%);
}
#cssmenu > ul > li > a:hover {
  background: #080808;
  color: #ffffff;
}
#cssmenu ul ul a:hover {
  color: #ffffff;
}
#cssmenu > ul > li.has-sub > a:hover:before {
  border-top: 5px solid #ffffff;
}

* {
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
}

/* Style the header */
header {
    background-color: #99D9EA;
    padding: 0px;
    text-align: center;
    font-size: 35px;
    color: #99D9EA;
    height: 150px;
}

/* Create two columns/boxes that floats next to each other */
nav {
    float: left;
    width: 7%;
    height: 500px; /* only for demonstration, should be removed */
    background: #ccc;
    padding: 20px;
}

/* Style the list inside the menu */
nav ul {
    list-style-type: none;
    padding: 0;
}

article {
    float: left;
    padding: 0px;
    width: 100%;
    background-color: #f1f1f1;
    height: 700px; /* only for demonstration, should be removed */
}

/* Clear floats after the columns */
section:after {
    content: "";
    display: table;
    clear: both;
}

/* Style the footer */
footer {
    background-color: #777;
    font-size: 15px;
    padding: 5px;
    text-align: center;
    color: white;
}

/* Responsive layout - makes the two columns/boxes stack on top of each other instead of next to each other, on small screens */
@media (max-width: 600px) {
    nav, article {
        width: 100%;
        height: auto;
    }
}
</style>

<link rel="stylesheet" href="styles.css">
</head>
<body>

<section>
  
   <div id='cssmenu'>
   <ul>
   <li><a href="#" onclick="javascript:myFunc1('subMain.php')"><span>Home</span></a></li>
   <li class='active has-sub'><a href='#'><span>Customers</span></a>
      <ul>
        <li class='last'><a href="#" onclick="javascript:myFunc1('customers/create')"><span>Create</span></a></li>  
        <li class='last'><a href="#" onclick="javascript:myFunc1('customers')"><span>List</span></a></li>
      </ul>
   </li>
   <li class='active has-sub'><a href='#'><span>Types</span></a>
    <ul>
      <li class='last'><a href="#" onclick="javascript:myFunc1('types/create')"><span>Create</span></a></li>   
      <li class='last'><a href="#" onclick="javascript:myFunc1('types')"><span>List</span></a></li>  
    </ul>
   </li>
   <li class='active has-sub'><a href='#'><span>Products</span></a>
      <ul>
        <li class='last'><a href="#" onclick="javascript:myFunc1('porducts/create')"><span>Create</span></a></li>
        <li class='last'><a href="#" onclick="javascript:myFunc1('porducts')"><span>List</span></a></li>  
      </ul>
   </li>
   <li class='active has-sub'><a href='#'><span>Companies</span></a>
      <ul>
        <li class='last'><a href="#" onclick="javascript:myFunc1('companies/create')"><span>Create</span></a></li>   
        <li class='last'><a href="#" onclick="javascript:myFunc1('companies')"><span>List</span></a></li>  
      </ul>
   </li>
   <li><a href="#" onclick="javascript:myFunc1('')"><span>About</span></a></li>
   <li><a href="#" onclick="javascript:myFunc1('')"><span>Contact</span></a></li>
   
   <li>
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
   </li>
   
   <li><a href="#" onclick="javascript:myFunc1('logout.php')"><span>Logout</span></a></li>
   
  </ul>
  </div>
  
  <article><iframe src="subMain.php" name="myFrame" frameborder="0" height="700" width="100%">
           </iframe>
  </article>
</section>


<footer>
  <p>Our mission:</p>
  <p>To try our best to keep our clients happy and satisfied to the best of our ability.</p>
  <p>To be a manufacturer of great quality products at reasonable prices.</p>
</footer>

</body>
</html>
