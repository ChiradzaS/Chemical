<!DOCTYPE html>
<html lang="en">
<head>
<title>CSS Template</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>


<style>
* {
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
}

/* Style the header */
header {
    background-color: #666;
    padding: 5px;
    text-align: center;
    font-size: 35px;
    color: white;
    height: 100px;
}

/* Create two columns/boxes that floats next to each other */
nav {
    float: left;
    width: 10%;
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
    padding: 10px;
    width: 90%;
    background-color: #f1f1f1;
    height: 500px; /* only for demonstration, should be removed */
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
    padding: 20px;
    text-align: center;
    color: white;
}

.center {
    display: block;
    margin-left: auto;
    margin-right: auto;
    width: 50%;
}

        /* Your existing CSS styles */

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

/* Responsive layout - makes the two columns/boxes stack on top of each other instead of next to each other, on small screens */
@media (max-width: 600px) {
    nav, article {
        width: 100%;
        height: auto;
    }
}
</style>
</head>
<body>


 <img src="industrial greenery.png" alt="Industrial Greenery" class="center" height="482" width="490">
 
 

</body>
</html>



