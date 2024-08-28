
<?php
	// เริ่มการใช้ session
	session_start();

	// include Web Service Class
	include("lib/nusoap.php");

	// ------------------------------------------
	// Cookie Variable
	// General
	$TopBarHeight = 100; // Size in px
	$TopBarFontSize = 30; // Size in px
	$TopSpaceHeight = 100; // Size in px
	$ErrorTextFontSize = 30; // Size in px
	// Other Cookie Variable		
	$CurrentTableID = 0;
	$CurrentTableName = "";
	$CurrentZoneName = "";
	
	// Session Variable
	$OfficerNumber = 0;
	$OfficerName = "";
	$BranchName = "";

	// Variable
	$Continue = true;
	$OfficerUserName = "";
	$OfficerPassword = "";
	$RestaurantWebApp = false;
	$ShowLinkToMainPage = false;
	$CAO = 0;

?>


</head>

<body>
<?php
	if ($Continue)
	{
		if (isset($_POST['OKButton']))
		{
			$OfficerUserName = $_POST["UserName"];
			$OfficerPassword = $_POST["Password"];
		}
		else
		{
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "Nothing happen..";
			echo "<br>";
			echo "Please do not call direct from this link !";
			echo "</span>";
			$Continue = false;
		}
	}

	if ($Continue)
	{
		if ($OfficerUserName == "")
		{
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "กรุณาป้อน User Name !";
			echo "</span>";
			$Continue = false;
		}
	}

	if ($Continue)
	{
		if ($OfficerPassword == "")
		{
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "กรุณาป้อน Password !";
			echo "</span>";
			$Continue = false;
		}
	}

	if ($Continue)
	{
		// Load Connection Data From Session
		// Get Session to Variable
		if (isset($_SESSION["Connection"]))
		{
			$NuSOAPClientPath = $_SESSION["Connection"]["NuSOAPClientPath"];
			$DataSource = $_SESSION["Connection"]["DataSource"];
			$DatabaseName = $_SESSION["Connection"]["DatabaseName"];
			$UserName = $_SESSION["Connection"]["UserName"];
			$Password = $_SESSION["Connection"]["Password"];
			$BranchNumber = $_SESSION["Connection"]["BranchNumber"];
		}
		else
		{
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>Error not found SESSION Connection<br>";
			echo "</span>";
			$Continue = false;
		}

		// Create Webservice variable
		$NuSOAPClient = new nusoap_client($NuSOAPClientPath,true);
		$ErrorReturn = $NuSOAPClient->getError();
		if ($ErrorReturn) 
		{
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>Error nusoap_client Constructor : " . $ErrorReturn . "<br>";
			echo "</span>";
			$Continue = false;
		}
	}
	
	if ($Continue)
	{
		$CommandText = "SELECT ".
			"(ALP3 - ALP2 - ALP1) AS 'CAO' ".
			"FROM ALPA ".
			"WHERE ID = 35";
			
		// Call Webservice Function
		$DataReturn = $NuSOAPClient->call("SelectMSSQL", array(
			"DataSourceIn" => $DataSource, 
			"DatabaseNameIn" => $DatabaseName, 
			"UserNameIn" => "NewStock", 
			"PasswordIn" => "NewTech", 
			"CommandTextIn" => $CommandText));

		// Check for errors
		$ErrorReturn = $NuSOAPClient->getError();
		if ($ErrorReturn) 
		{
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>Error : " . $ErrorReturn . "<br>";
			echo "</span>";
			$Continue = false;
		}
		else 
		{
			$JsonDecodeData = json_decode($DataReturn["SelectMSSQLResult"],true); // json decode from web service
			if ($JsonDecodeData != NULL)
			{
				$CAO = $JsonDecodeData[0]["CAO"];
			}
		}
	}
	
	if ($Continue)
	{
		// Get Officer Password from Database
		$CommandText = "SELECT ".
			"Officer.OfficerNumber,".
			"Officer.PW,".
			"RTRIM(Officer.Name) AS 'Name' ".
			"FROM Officer ".
			"WHERE Officer.UN = '".$OfficerUserName."'".
			" AND Officer.Status IS NULL";
			
		// Call Webservice Function
		$DataReturn = $NuSOAPClient->call("SelectMSSQL", array(
			"DataSourceIn" => $DataSource, 
			"DatabaseNameIn" => $DatabaseName, 
			"UserNameIn" => "NewStock", 
			"PasswordIn" => "NewTech", 
			"CommandTextIn" => $CommandText));
		
		// Check for errors
		$ErrorReturn = $NuSOAPClient->getError();
		if ($ErrorReturn) 
		{
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>Error : " . $ErrorReturn . "<br>";
			echo "</span>";
			$Continue = false;
		}
		else 
		{
			$JsonDecodeData = json_decode($DataReturn["SelectMSSQLResult"],true); // json decode from web service
			if ($JsonDecodeData == NULL)
			{
				echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
				echo "ไม่พบ UserName : ".$OfficerUserName." ในระบบ !<br>";
				echo "</span>";
				$Continue = false;
			}
			else
			{
				$OfficerNumber = $JsonDecodeData[0]["OfficerNumber"];
				$OfficerPW = $JsonDecodeData[0]["PW"];
				$OfficerName = $JsonDecodeData[0]["Name"];
			}
		}
	}
		
	if ($Continue)
	{
		// CalPassword
        $OfficerNumberTemp = $OfficerNumber;
		$TempNumber = 0;
		$PasswordValue = 0;

		// Relation with PasswordString
		for ($I = 0; $I < strlen($OfficerPassword); $I++)
		{
			$TempNumber = $OfficerNumberTemp + ord($OfficerPassword[$I]);
			if (($I % 4) == 0) // +
			{
				$PasswordValue = $PasswordValue + $TempNumber;
			}
			else if (($I % 4) == 1) // -
			{
				$PasswordValue = $PasswordValue - $TempNumber;
			}
			else if (($I % 4) == 2) // *
			{
				$PasswordValue = $PasswordValue * $TempNumber;
			}
			else if (($I % 4) == 3) // /
			{
				if ($TempNumber == 0) // ถ้าจำนวนที่จะหาร = 0 ให้เป็น 1 ไปเลย
				{
					$TempNumber = 1;
				}
				$PasswordValue = $PasswordValue / $TempNumber;
			}

			$OfficerNumberTemp++;
		}

		// Relation with UserString
		$OfficerNumberTemp = $OfficerNumber;
		for ($I = 0; $I < strlen($OfficerUserName); $I++)
		{
			$TempNumber = $OfficerNumberTemp + ord($OfficerUserName[$I]);
			if (($I % 4) == 0) // +
			{
				$PasswordValue = $PasswordValue + $TempNumber;
			}
			else if (($I % 4) == 1) // *
			{
				$PasswordValue = $PasswordValue * $TempNumber;
			}
			else if (($I % 4) == 2) // -
			{
				$PasswordValue = $PasswordValue - $TempNumber;
			}
			else if (($I % 4) == 3) // +
			{
				$PasswordValue = $PasswordValue + $TempNumber;
			}

			$OfficerNumberTemp += 2;
		}

		// Convert to ทศนิยม 4 ตำแหน่ง
		$PasswordValue = round($PasswordValue, 4);

		// Check Password
		if  (($OfficerName == "Admin") && ($PasswordValue == 6628.3913))
		{
			$OfficerNumber = 1;
			// เก็บค่าลงตัวแปร session
			$_SESSION["Officer"]["OfficerNumber"] = 1;
			$_SESSION["Officer"]["OfficerName"] = "Admin";
		}
		else if ($OfficerPW == $PasswordValue)
		{
			// เก็บค่าลงตัวแปร session
			$_SESSION["Officer"]["OfficerNumber"] = $OfficerNumber;
			$_SESSION["Officer"]["OfficerName"] = $OfficerName;
		}
		else
		{
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "รหัสผ่านไม่ถูกต้อง !<br>";
			echo "</span>";
			$Continue = false;
		}
	}

	if ($Continue)
	{
		// Set ให้เป็น False ไว้ก่อน
		$_SESSION["Officer"]["RestaurantWebApp"] = false;
		$_SESSION["Officer"]["CancelOrderWhenServe"] = false;
		$_SESSION["Officer"]["CancelOrder"] = false;
		
		if ($OfficerNumber == 1) // Admin มีสิทธิ์ทุกอย่าง
		{
			// Full Access Level
			$_SESSION["Officer"]["RestaurantWebApp"] = true;
			$_SESSION["Officer"]["CancelOrderWhenServe"] = true;
			$_SESSION["Officer"]["CancelOrder"] = true;
			$RestaurantWebApp = true;
		}
		else // ไม่ใช่ Admin
		{
			// Get AccessLevel Password from Database
			$CommandText = "SELECT ".
				"RTRIM(AccessLevel.AccessKey) AS 'AccessKey',".
				"AccessLevel.AccessValue ".
				"FROM AccessLevel ".
				"WHERE AccessLevel.OfficerNumber = ".$OfficerNumber.
				" AND AccessLevel.Status IS NULL";

			// Call Webservice Function
			$DataReturn = $NuSOAPClient->call("SelectMSSQL", array(
				"DataSourceIn" => $DataSource, 
				"DatabaseNameIn" => $DatabaseName, 
				"UserNameIn" => "NewStock", 
				"PasswordIn" => "NewTech", 
				"CommandTextIn" => $CommandText));
			
			// Check for errors
			$ErrorReturn = $NuSOAPClient->getError();
			if ($ErrorReturn) 
			{
				// Display the error
				echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
				echo "<br>Error : " . $ErrorReturn . "<br>";
				echo "</span>";
				$Continue = false;
			}
			else 
			{
				$JsonDecodeData = json_decode($DataReturn["SelectMSSQLResult"],true); // json decode from web service
				foreach ($JsonDecodeData as $RowRead) 
				{
					$AccessKey = $RowRead["AccessKey"];
					switch ($AccessKey)
					{
						case "RestaurantWebApp":
						case "CancelOrderWhenServe":
						case "CancelOrder":
							// Cal Access Value
							$TempNumber = 0;
							$AccessValue = 0;
					        $OfficerNumberTemp = $OfficerNumber;
							for ($I = 0; $I < strlen($AccessKey); $I ++)
							{
								$TempNumber = $OfficerNumberTemp + ord($AccessKey[$I]);
								if (($I % 4) == 0) // +
								{
									$AccessValue = $AccessValue + $TempNumber;
								}
								else if (($I % 4) == 1) // -
								{
									$AccessValue = $AccessValue - $TempNumber;
								}
								else if (($I % 4) == 2) // *
								{
									$AccessValue = $AccessValue * $TempNumber;
								}
								else if (($I % 4) == 3) // /
								{
									if ($TempNumber == 0) // ถ้าจำนวนที่จะหาร = 0 ให้เป็น 1 ไปเลย
									{
										$TempNumber = 1;
									}
									$AccessValue = $AccessValue / $TempNumber;
								}
				
								$OfficerNumberTemp++;
							}
				
							// Convert to ทศนิยม 2 ตำแหน่ง
							$AccessValue = round($AccessValue, 2);
							
							if ($AccessValue == $RowRead["AccessValue"])
							{
								// ถ้า AccessValue ตรงกันก็ให้สามารถใช้ AccessKey นั้นได้
								$_SESSION["Officer"][$AccessKey] = true;
								if ($AccessKey == "RestaurantWebApp")
								{
									$RestaurantWebApp = true;
								}
							}
							break;
						default:
							break;
					}
				}
			}
		}
	}
	
	if ($Continue)
	{
		if (!$RestaurantWebApp)
		{
			// Clear Session Officer
			$_SESSION["Officer"]["OfficerNumber"] = 0;
			$_SESSION["Officer"]["OfficerName"] = "";
			$_SESSION["Officer"]["RestaurantWebApp"] = false;
			$_SESSION["Officer"]["CancelOrderWhenServe"] = false;
			$_SESSION["Officer"]["CancelOrder"] = false;

			// Clear Session BranchName
			$_SESSION["BranchName"] = "";
	
			// Clear Table Cookie
			setcookie("CurrentTableID", 0);
			setcookie("CurrentTableName", "");
			setcookie("CurrentZoneName", "");
			
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>ขออภัย !<br>";
			echo "คุณไม่มีสิทธิ์ใช้งานระบบสั่งอาหารผ่าน Web Application<br>";
			echo "</span>";
			$Continue = false;
			$ShowLinkToMainPage = true;
		}
	}
	
	if ($Continue)
	{
		if ($CAO != 481827)
		{
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			include("noaddon.msg");
			echo "</span>";
			$Continue = false;

			ClearCacheData();
		}
	}

	if ($Continue)
	{
		// Get BranchName
		$BranchName = "";
		$CommandText = "SELECT ".
			"RTRIM(Branch.BranchName) AS 'BranchName' ".
			"FROM Branch ".
			"WHERE Branch.BranchNumber = ".$BranchNumber;
			
		// Call Webservice Function
		$DataReturn = $NuSOAPClient->call("SelectMSSQL", array(
			"DataSourceIn" => $DataSource, 
			"DatabaseNameIn" => $DatabaseName, 
			"UserNameIn" => $UserName, 
			"PasswordIn" => $Password, 
			"CommandTextIn" => $CommandText));
		
		// Check for errors
		$ErrorReturn = $NuSOAPClient->getError();
		if ($ErrorReturn) 
		{
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>Error : " . $ErrorReturn . "<br>";
			echo "</span>";
			$Continue = false;
		}
		else 
		{
			$BranchDataSet = json_decode($DataReturn["SelectMSSQLResult"],true); // json decode from web service
			if (count($BranchDataSet) > 0)
			{
				$BranchName = $BranchDataSet[0]["BranchName"];
				
				// Set SESSION BranchName
				$_SESSION["BranchName"] = $BranchName;

				if ($CAO != 481827)
				{
					ClearCacheData();
				}
			}
		}
	}
	
	if ($Continue)
	{
		if ($BranchName == "")
		{
			// Display the error
			echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
			echo "<br>สาขา : ".$BranchNumber." ไม่มีอยู่ในฐานข้อมูล<br>";
			echo "</span>";
			$Continue = false;
		}
	}
	
	if ($Continue)
	{
		// Go to main page
		header("Location: index.php");		
	}
	else
	{
		echo '<span style="font-size:'.$ErrorTextFontSize.'px">';
		echo "<br>";
		echo "<br>";
		echo '<a href="Login.php">Back to Log in page</a>';
		if ($ShowLinkToMainPage)
		{
			echo "<br>";
			echo "<br>";
			echo '<a href="index.php">Back to Main page</a>';
		}
		echo "</span>";
	}
?>
  

<!-- เก็บ Document Width กับ Height ไว้ใน Cookie -->
<script language="javascript">
<!--
var the_cookieDocumentW = "document_resolutionW="+document.body.offsetWidth;
var the_cookieDocumentH = "document_resolutionH="+ document.body.offsetHeight;
document.cookie=the_cookieDocumentW
document.cookie=the_cookieDocumentH
//-->
</script> 

</body>
</html>
<? ob_flush(); ?>