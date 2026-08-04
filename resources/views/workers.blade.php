<!doctype html>
<html>
<head>
<!------------------------------------ Local jars in public folder  --------------------------------------------------------->

<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<script src="{{ asset('public/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ asset('public/js/jquery-3.6.1.min.js') }}"></script>
<script src="{{ asset('public/js/select2.min.js') }}"></script>
<link rel="stylesheet" href="{{ asset('public/css/select2.min.css') }}">






<!--------------------------------------------------------------------------------------------------------------------------->
   <title>LaravelTuts.com - Fetch records from MySQL with jQuery AJAX – Laravel 9</title>
   <!-- Meta -->
   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">

   <meta charset="utf-8">
   <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
   <input type='text' id='search' name='search' placeholder='Enter userid 1-27'>
   <input type='button' value='Search' id='but_search'>
   <br/>
   <input type='button' value='Fetch all records' id='but_fetchall'>
 
   <!-- Table -->
   <table border='1' id='empTable' style='border-collapse: collapse;'>
     <thead>
       <tr>
         <th>S.no</th>
         <th>Username</th>
         <th>Name</th>
         <th>Email</th>
       </tr>
     </thead>
     <tbody></tbody>
   </table>
 
   <table>
    <label for="fname">First name:</label>
    <input type="text" id="fname" name="fname"><br><br>
    <label for="lname">Last name:</label>
    <input type="text" id="lname" name="lname"><br><br>
   </table>
   <!-- Script CDN -->

   <!-- Script Local -->
   <!-- <script src="{{asset('js/jquery-3.6.0.min.js')}}"></script> -->
 
   <script type='text/javascript'>
   var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
   $(document).ready(function(){
 
      // Fetch all records
      $('#but_fetchall').click(function(){
 
         // AJAX GET request
         $.ajax({
           url: 'getUsers',
           type: 'get',
           dataType: 'json',
           success: function(response){
 
              createRows(response);
 
           }
         });
      });
 
      // Search by userid
      $('#but_search').click(function(){
         var userid = Number($('#search').val().trim());
 
         if(userid > 0){
 
           // AJAX POST request
           $.ajax({
              url: 'getUserbyid',
              type: 'post',
              data: {_token: CSRF_TOKEN, userid: userid},
              dataType: 'json',
              success: function(response){
                
                 window.alert("Get User Id");
                 createRows(response);
 
              }
              });
            }
 
      });
 

   });
 
   // Create table rows
   function createRows(response){
      var len = 0;
      $('#empTable tbody').empty(); // Empty <tbody>
      if(response['data'] != null){
         len = response['data'].length;
      }
 
      if(len > 1){
        window.alert(len);
        for(var i=0; i<len; i++){
           var id = response['data'][i].id;
           var username = response['data'][i].username;
           var name = response['data'][i].name;
           var email = response['data'][i].email;
 
           var tr_str = "<tr>" +
             "<td align='center'>" + (i+1) + "</td>" +
             "<td align='center'>" + username + "</td>" +
             "<td align='center'>" + name + "</td>" +
             "<td align='center'>" + email + "</td>" +
           "</tr>";
 
           $("#empTable tbody").append(tr_str);
        }
      } else if (len = 1) {
        window.alert("Cool");
        for(var i=0; i<len; i++){
            var id = response['data'][i].id;
           var username = response['data'][i].username;
           var name = response['data'][i].name;
           var email = response['data'][i].email;

           document.getElementById("fname").value = username;
           document.getElementById("lname").value = name;
         
        }

      } else {
         var tr_str = "<tr>" +
           "<td align='center' colspan='4'>No record found.</td>" +
         "</tr>";
 
         $("#empTable tbody").append(tr_str);
      }
   } 
   </script>
</body>
</html>