<?php
   
   error_reporting(0);  

   $conn = new mysqli("localhost", "root", "", "constructionhub");

   if($conn){
     //    echo "Connection Okey!";
   }else{
    echo "Connection error!".mysqli_connect_error();
   }
?>