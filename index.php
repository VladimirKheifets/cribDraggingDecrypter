<!--

Demo PHP scripts  cribDraggingDecrypter
Version: 3.0, 2026-05-09
Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
Copyright (c) 2026 Vladimir Kheifets All Rights Reserved

One task from GCHQ Christmas Challenge 2024:
(GCHQ - UK's intelligence, security and cyber agency)

PERHAPS READING the start of this substitution cipher will help you solve it

ISKGWIM KSWEBDU BN DFN LBIGSKSE BM IKBCWKBQX W QSWEBDU BDEBLWNBFD NF XFOMFQPBDU NGBM WECBNNSEQX ESPBFOM BDBNBWQQX ODKSWEWAQS CSMMWUS

-->


<html>
<head>
<title>Demo PHP scripts cribDraggingDecrypter</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,
user-scalable=no, user-scalable=0" >
<style>
body{
 font-family: arial;
 font-size: 12pt;
 padding: 20pt 0pt 20pt 20pt;
}
div{
  width:calk(100%-20px);
  overflow-wrap: break-word;
  padding: 5pt 0pt 5pt;
}
p{
    border-bottom: 2px solid black;
    padding-bottom: 20px;
}
</style>
</head>
<body>
<?
require "cribDraggingDecrypterFunc.php";

define("show_details", filter_input(INPUT_GET, "show_details"));
$taskDesc = file("teskDescription.txt", FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

define("englTopRankingSuffixes", file_get_contents("topRankingSuffixes.txt"));
define("englSuffixes", file_get_contents("suffixes.txt"));
define("englPrefixes", file_get_contents("prefixes.txt"));

$varNames = ["hint","encrypted"];
foreach($taskDesc as $i => $item){
    if($i>0)
        ${$varNames[$i-1]."Text"} = $item;
    echo "<div>$item</div>";
}

echo "<br><div><b>Encrypted text:<br></b>$encryptedText</div>";

echo "<pre>";
$start = hrtime(true);

## Step 1  ############################################
$pattern = "/(\p{Lu}+\ )+/";
preg_match_all($pattern, $hintText, $match);
$lenHintLetters = strlen($match[0][0]);

define("lenHintLetters", $lenHintLetters);
define("hintText", strtoupper($hintText));
define("encryptedText", $encryptedText);

$lBeginLen = [0, lenHintLetters];
$S1 = strToLetters( $hintText, $lBeginLen );
$S2 = strToLetters( $encryptedText, $lBeginLen );
$lettersMapping = array_combine($S2, $S1);

echo "<b>Step 1. </b><br>";
if(show_details)
{
  echo <<<HTML

  Words with capital letters were found in the hint text:

  <b>{$match[0][0]}</b>

  In the first $lenHintLetters letters of the hint text were found (without spaces):

  HTML;
  print_r($S1);
  echo "In the first $lenHintLetters letters of the encrypted text were found (without spaces):<br>";
  print_r($S2);
  echo <<<HTML

  These two arrays are combined into a single array.


  HTML;
}
echo "<b>Letter-to-letter Mapping:</b><br>";
print_r($lettersMapping);
$decryptedText = decrypter($encryptedText, $lettersMapping);
echo "<br><b>Decrypted text:</b><br>";
echo "<p>$decryptedText</p>";

## Step 2  ############################################
$wordsEncrypted = strToWords($encryptedText);
echo "<b>Step 2.</b><br>";
suffByRankDecr($lettersMapping);
echo "<b>Letter-to-letter Mapping:</b><br>";
print_r($lettersMapping);
//------------------------------------------------------
$decryptedText = decrypter($encryptedText, $lettersMapping);
echo "<br><b>Decrypted text:</b><br>";
echo "<p>$decryptedText</p>";

## Step 3  ############################################
echo "<b>Step 3.</b><br>";
$wordsDecrypted= strToWords($decryptedText);

foreach ($wordsEncrypted as $wordEncrypted)
{
    $lenWord = strlen($wordEncrypted);
    decryptFromEnglPrefixesListe( $wordEncrypted, $wordDecrypted, $lenWord, $lettersMapping );
    decryptFromEnglSuffixesListe( $wordEncrypted, $wordDecrypted, $lenWord, $lettersMapping );
    decryptWords( $wordEncrypted, $wordDecrypted,  $lettersMapping );
}

echo "<b>Letter-to-letter Mapping:</b><br>";
print_r($lettersMapping);
$decryptedText = decrypter($encryptedText, $lettersMapping);
$wordsDecrypted= preg_split("/\s/", $decryptedText);
echo "<br><b>Decrypted text:</b><br>";
echo "<p>$decryptedText</p>";

######################################################
$end=hrtime(true);
$eta=$end-$start;

echo "<br>Decrypted time: ",($end-$start)/1e+6," ms";
?>
</body>
</HTML>