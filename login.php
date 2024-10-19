<?php 
session_start();
        if(isset($_POST['username'])){
                  include("connection.php");
                  $username = mysqli_real_escape_string($conn,$_POST['username']);
                  $password = mysqli_real_escape_string($conn,md5($_POST['password']));

                  $sql="SELECT * FROM mable
                  WHERE  username ='".$username."'  AND  password ='".$password."' ";

                  $result = mysqli_query($conn,$sql);
				
                  if(mysqli_num_rows($result)==1){

                      $row = mysqli_fetch_array($result);

                      $_SESSION["userid"] = $row["id"];
                      $_SESSION['firstname'] = $row['firstname'];
                      $_SESSION["userlevel"] = $row["userlevel"];

                      if($_SESSION["userlevel"]=="a"){ 
                        Header("Location: admin/admin_page.php");
                      }
                      if($_SESSION["userlevel"]=="m"){
                        
                        Header("Location: user/user_page.php");
                      }
                    }else{
                      echo "<script>";
                          echo "alert(\" user หรือ password ไม่ถูกต้อง\");"; 
                          echo "window.history.back()";
                      echo "</script>";
  
                    }
            }else{
             Header("Location: index.php"); //user & m_password incorrect back to login again
        }
?>