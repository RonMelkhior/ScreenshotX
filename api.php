<?php
	/*
		This is a small API for ShareX in order to upload your images to your own domain.
	*/

	/*
		---CONFIG---
	*/
	//Security:
		$fileFormName = ""; // File form name in ShareX. Try to make it as random as you can (possibly with a password generator).
		$argumentName = ""; //Argument Name in ShareX. Try to make it as random as you can (possibly with a password generator).
		$argumentValue = ""; //Argument Value in ShareX. Try to make it as random as you can (possibly with a password generator).

	//General:
		$domain = "http://example.com/"; //The URL to later echo back to ShareX. Don't forget the "/" in the end.
		$chars = 3; // Amount of characters filder&folder names should have. The more users you have, the higher the number should be.
		$namingMode = 3; /* Mode for generating folder&file names.
							1 - Generated names will consist of only numbers.
							2 - Generated names will consist of only lowercase letters.
							3 - Generated names will consist of lowercase letters and numbers.
							4 - Generated names will be in an AdjectiveAnimal order. Inspired from gfycat. (txts provided by gfycat, thanks!)
						*/
		$generateFolders = 0; //If you enable this feature, URLS will consist of two random names
							  //(example: example.com/21321/23123.png), this is to prevent "random picture" generators.
							  //The feature works as a bool (0=off 1=on)
	/*
		---END OF CONFIG---
	*/

	function generateFolderName() {
		global $namingMode, $chars;
		$l = 'abcdefghijklmnopqrstuvwxyz';
		$n = '0123456789';

		if ($namingMode == 1) {
			$gf = substr(str_shuffle($n), 0, $chars);
		} else if ($namingMode == 2) {
			$gf = substr(str_shuffle($l), 0, $chars);
		} else if ($namingMode == 3) {
			$gf = substr(str_shuffle($n . $l), 0, $chars);
		} else if ($namingMode == 4) {
			$adjs = file("adjectives.txt"); 
			$amls = file("animals.txt");
			
			$adjsOne = $adjs[rand(0, count($adjs) - 1)];
			$adjsOne = trim(preg_replace('/\s+/', ' ', $adjsOne));
			$adjsOne = ucwords($adjsOne);
			$aml = $amls[rand(0, count($amls) - 1)];
			$aml = trim(preg_replace('/\s+/', ' ', $aml));
			$aml = ucwords($aml);
			$gf = $adjsOne . $aml;
		}

		if (file_exists($gf . '/')) {
			return generateFolderName();
		} else {
			mkdir($gf, 0777);
			return $gf . '/';
		}
	}

	function generateFileName($name) {
		global $namingMode, $chars, $generateFolders;
		$l = 'abcdefghijklmnopqrstuvwxyz';
		$n = '0123456789';

		if ($namingMode == 1) {
			$gn = substr(str_shuffle($n), 0, $chars) . '.' . end(explode(".",$name));
		} else if ($namingMode == 2) {
			$gn = substr(str_shuffle($l), 0, $chars) . '.' . end(explode(".",$name));
		} else if ($namingMode == 3) {
			$gn = substr(str_shuffle($n . $l), 0, $chars) . '.' . end(explode(".",$name));
		} else if ($namingMode == 4) {
			$adjs = file("adjectives.txt"); 
			$amls = file("animals.txt");
			
			$adjsOne = $adjs[rand(0, count($adjs) - 1)];
			$adjsOne = trim(preg_replace('/\s+/', ' ', $adjsOne));
			$adjsOne = ucwords($adjsOne);
			$aml = $amls[rand(0, count($amls) - 1)];
			$aml = trim(preg_replace('/\s+/', ' ', $aml));
			$aml = ucwords($aml);
			$gn = $adjsOne . $aml . '.' . end(explode(".",$name));
		}

		if ($generateFolders == 0) {
			if (file_exists($gn)) {
				return generateFileName($n);
			} else {
				return $gn;
			}
		} else if ($generateFolders == 1) {
			return $gn;
		}
	}

	if (isset($_POST)) {
		if (is_uploaded_file($_FILES[$fileFormName]['tmp_name']) && $_POST[$argumentName] == $argumentValue) {
			$allowedExts = array("gif", "jpeg", "jpg", "png", "PNG", "JPG", "JPEG");
			$temp = explode(".", $_FILES[$fileFormName]["name"]);
			$extension = end($temp);
			if ((($_FILES[$fileFormName]["type"] == "image/gif")
			|| ($_FILES[$fileFormName]["type"] == "image/jpeg")
			|| ($_FILES[$fileFormName]["type"] == "image/jpg")
			|| ($_FILES[$fileFormName]["type"] == "image/pjpeg")
			|| ($_FILES[$fileFormName]["type"] == "image/x-png")
			|| ($_FILES[$fileFormName]["type"] == "image/png"))
			&& in_array($extension, $allowedExts)) {
				if ($_FILES[$fileFormName]["error"] > 0) {
					echo "File type not allowed.";
				} else {
					$un = generateFileName($_FILES[$fileFormName]['name']);
					global $domain;
					if ($generateFolders == 1) {
						$ud = generateFolderName();
						if (move_uploaded_file($_FILES[$fileFormName]["tmp_name"], $ud. $un)) {
							$url = $domain . $ud . $un;
							echo $url;
						}
					} else if ($generateFolders == 0) {
						if (move_uploaded_file($_FILES[$fileFormName]["tmp_name"], $un)) {
							$url = $domain . $un;
							echo $url;
						}
					}
				}
			}
		}
	}
?>