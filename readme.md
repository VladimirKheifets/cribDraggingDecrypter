# PHP scripts cribDraggingDecrypter

### Version: 3.0, 2026-05-09

Author: Vladimir Kheifets <vladimir.kheifets@online.de>

Copyright &copy; 2026 Vladimir Kheifets All Rights Reserved

One task from GCHQ Christmas Challenge 2024:
(GCHQ - UK's intelligence, security and cyber agency)

[https://www.gchq.gov.uk/news/gchq-christmas-challenge-2024](https://www.gchq.gov.uk/news/gchq-christmas-challenge-2024)

```

    PERHAPS READING the start of this substitution cipher will help you solve it

    ISKGWIM KSWEBDU BN DFN LBIGSKSE BM IKBCWKBQX W QSWEBDU BDEBLWNBFD NF XFOMFQPBDU NGBM WECBNNSEQX ESPBFOM BDBNBWQQX ODKSWEWAQS CSMMWUS


```
Demo:
[https://www.alto-booking.com/developer/cribDraggingDecrypter/?show_details=1](https://www.alto-booking.com/developer/cribDraggingDecrypter/?show_details=1)


## 1. File index.php
```php
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
```
## 2. PHP-function cribDraggingDecrypter

### File cribDraggingDecrypter.php
```php
    <?PHP

    /*
    PHP scripts  cribDraggingDecrypter functions
    Version: 3.0, 2026-05-09
    Author: Vladimir Kheifets (vladimir.kheifets.@online.de)
    Copyright (c) 2026 Vladimir Kheifets All Rights Reserved
    */

    ########################################################################

    function strToLetters( $str, $iBE=null, $toUnique=false, $toUpper=false ){
        if($iBE)
            $str = substr($str,...$iBE);
        $str = preg_replace("/\s/","",$str);
        if($toUpper)
            $str = strtoupper($str);
        $arr = str_split($str);
        if($toUnique)
            $arr =  array_unique($arr);
        return $arr;
    }

    ########################################################################

    function strToWords($str){
        preg_match_all("/(\p{Lu}{5,})/",substr($str, lenHintLetters), $match);
        return  $match[0];
    }

    ########################################################################

    function suffByRankDecr(&$lettersMapping){
        if(show_details) echo "<p><b>Begin function suffByRankDecr</b><br>";
        $suffLenArr = [3,2];

        $rankSuffInEntText = getSuffFreq($suffLenArr);

        foreach($suffLenArr as $suffLen)
            if(checkRankSuff($rankSuffInEntText[$suffLen], $lettersMapping))
                topRankSuffLettersDecr($rankSuffInEntText[$suffLen], $suffLen, $lettersMapping );

        if(show_details) echo  "<br><b>End function suffByRankDecr</b></p>";
    }

    ########################################################################

    function getSuffFreq($suffLenArr)
    {
        $rankSuffInEntText=[];
        foreach($suffLenArr as $suffLen){
            $suffFreqArr = [];
            $pattern = "/(\p{Lu}{".$suffLen."})(?=\ |\p{Lu}{".$suffLen."}$)/";
            preg_match_all($pattern,encryptedText,$match);

            foreach($match[0] as $suff)
            {
                if(isset($suffFreqArr[$suff]))
                    $suffFreqArr[$suff] += 1;
                else
                    $suffFreqArr[$suff] = 1;
            }
            if(isset($rankSuffInEntText[3]) AND $suffLen)
                unset($suffFreqArr[substr($rankSuffInEntText[3],1)]);
            $rankSuffInEntText[$suffLen] = getStrWithMaxRank($suffFreqArr);

            if(show_details)
                echo "<br><b>Rank of $suffLen-letters suffixes in entcrypted text:</b><br>";
            if(show_details)
                print_r($suffFreqArr);
            if(show_details)
            echo <<<HTML

            <b>Top rank $suffLen-letters suffix in entcrypted text: {$rankSuffInEntText[$suffLen]}</b>


            HTML;
        }
        return $rankSuffInEntText;
    }

    ########################################################################

    function checkRankSuff($rankSuffEnc, $lettersMapping){
        $rankSuffDec = decrypter($rankSuffEnc, $lettersMapping);
        if($rankSuffDec == ".." OR $rankSuffDec == "...") return true;
        $pattern = "/(".$rankSuffDec.")(?=\ )/";
        if(preg_match($pattern, substr(hintText, 0, lenHintLetters)))
        {
            if(show_details)
            echo <<<HTML

            Top rank suffix <b>$rankSuffEnc</b>  has already been decrypted -> <b>$rankSuffDec</b>

            HTML;
            return false;
        }
        else
        {
            return true;
        }
    }

    ########################################################################
    function topRankSuffLettersDecr($rankSuffInDecText,  $suffLen, &$lettersMapping ){

        if(show_details) echo "<br><b>Begin function topRankSuffLettersDecr</b><br>";
        $pattern = "/\p{Lu}{".$suffLen."}/";
        preg_match_all($pattern, englTopRankingSuffixes, $match);
        $topRankSuffEngArr = $match[0];


        if(show_details)
            echo <<<HTML

        <b>Top rank $suffLen-letters suffix in entcrypted text: $rankSuffInDecText</b>


        HTML;

        $undefAllLetters = str_pad("", $suffLen,".");

        foreach($topRankSuffEngArr as $topRankSuffEng)
        {
            if(show_details)
                echo "Top rank $suffLen-letters english suffix: $topRankSuffEng<br>";
            $decTopRankSuffEng = entrypter($topRankSuffEng, $lettersMapping);

            if($decTopRankSuffEng == $undefAllLetters)
            {
               if(show_details)
                    echo <<<HTML

                    -------------------------------

                    Passed inspection english suffix: $topRankSuffEng

                    HTML;


                for ($i=0; $i < $suffLen; $i++) {
                   $ChEntcr = $rankSuffInDecText[$i];
                   $lettersMapping[$ChEntcr] = $topRankSuffEng[$i];
                   if(show_details)
                        echo "Decrypted: $ChEntcr -> {$topRankSuffEng[$i]}<br>";
               }
               if(show_details)
                    echo "<br>-------------------------------<br>";
            }
        }


        if(show_details) echo  "<br><b>End function topRankSuffLettersDecr</b><br>";

    }

    ########################################################################

    function getStrWithMaxRank($strRankArr){
        $tmp = array_flip($strRankArr);
        ksort($tmp);
        return end($tmp);
    }

    ########################################################################
    function  decrypter($encryptedText, $lettersMapping){
        $decryptered = "";
        for ($i=0; $i < strlen($encryptedText); $i++)
        {
            $v = $encryptedText[$i];
            if($v == " ")
                $decryptered .= " ";
            else if(isset($lettersMapping[$v])){
                $decryptered .= $lettersMapping[$v];
            }
            else
               $decryptered .= ".";
        }
        return $decryptered;
    }

    ########################################################################

    function  entrypter($decryptedText, $lettersMapping){
        $entcrypted = "";
        $lettersMappingFl=array_flip($lettersMapping);
        for ($i=0; $i < strlen($decryptedText); $i++)
        {
            $v = $decryptedText[$i];
            if($v == " ")
                $entcrypted .= " ";
            else if(isset($lettersMappingFl[$v])){
                $entcrypted .= $lettersMappingFl[$v];
            }
            else
               $entcrypted .= ".";
        }
        return $entcrypted;
    }

    ########################################################################

    function decryptFromEnglPrefixesListe($wordEnt, &$wordDec, $lenWord, &$lettersMapping){
        $res = show_res($wordEnt, $wordDec, $lettersMapping);
        if( strpos(substr($wordDec,0,5), ".") === false ) return;
        if(show_details){
            echo <<<HTML

            <p><b>Begin function decryptFromEnglPrefixesListe</b>
            $res
            HTML;
        }


        $subPatt = "";
        $allPointsPos=[];
        for($iChr=0; $iChr<5; $iChr++)
            if(strpos($wordDec[$iChr], ".")!== false)
                $allPointsPos[] = $iChr;

        if(isset($allPointsPos[0]))
        {
            $maxPointPos = max($allPointsPos);
            if($maxPointPos>0)
            $subPatt = $maxPointPos==0?"":substr($wordDec,0,$maxPointPos+1);

        }
        else if(show_details AND $subPatt){
        echo <<<HTML
        The positions of the "." characters within the first 5 characters
        of the decrypted word are determined and stored in the
        \$allPointsPos &nbsp;
        HTML;
        print_r($allPointsPos);
        echo "\$maxPointPos = $maxPointPos<br>";
        echo "\$subPatt = $subPatt<br>";
        }

        if($subPatt)
        {
            $pattern = "/(?<=\|)($subPatt)\-/i";

            if(show_details)
                echo "\$pattern = $pattern<br>";

            preg_match_all($pattern, englPrefixes, $match);

            if(isset($match[1]))
            {
                if(count($match[1]) == 1)
                {
                    if(show_details)
                    {
                        echo "<br>\$match[1]  ";
                        print_r($match[1]);
                    }

                    $prefix = $match[1][0];
                    $prefLen = strlen($prefix);
                    if(show_details)
                        echo <<<HTML

                    Тне prefix "<b>$prefix</b>" was found in the list of English prefixes.

                    \$prefLen = $prefLen


                    HTML;

                    for ($j=0; $j < $prefLen; $j++)
                    {
                        if($wordDec[$j] == ".")
                        {
                            $chrEnt = $wordEnt[$j];
                            if(!isset($lettersMapping[$chrEnt])){
                                $lettersMapping[$chrEnt] = substr($prefix, $j, 1);
                                if(show_details)
                                    echo "Decrypted: $chrEnt -> ".substr($prefix, $j, 1)."<br>";
                            }
                            else if(show_details)
                                echo "Previously decrypted: $j  $chrEnt -> ".substr($prefix, $j, 1)."<br>";
                        }
                    }
                }
                else if(show_details)
                    echo "<br>No match found<br>";
            }
        }
        else if(show_details)
            echo "<br>Pattern undefined<br>";

        $res = show_res($wordEnt, $wordDec, $lettersMapping,1);

        if(show_details)
            echo <<<HTML
        $res
        <b>End function decryptFromEnglPrefixesListe</b></p>
        HTML;
    }

    #########################################################

    function decryptFromEnglSuffixesListe($wordEnt, &$wordDec, $lenWord, &$lettersMapping){
        $res = show_res($wordEnt, $wordDec, $lettersMapping);
        if( strpos($wordDec, ".") === false ) return;

        if(show_details){
            echo <<<HTML

            <p><b>Begin function decryptFromEnglSuffixesListe</b>
            $res
            HTML;
        }

        $pattArr = [];
        $point = ".";
        for ($j=3; $j < 5; $j++) {
            $tmp = substr($wordDec,$lenWord-$j,$j);
            if(strpos($tmp, "." ) !== false)
            $pattArr[] = $tmp;
        }
        $subPatt = implode("|",$pattArr);

        if($subPatt)
        {
            $pattern = "/(?<=\-)($subPatt)\|/i";
            if(show_details){
                echo "\$subPatt = $subPatt<br>";
                echo "\$pattern = $pattern<br>";
            }
            preg_match_all($pattern, englSuffixes, $match);
            if(isset($match[1]))
            {
                if(count($match[1]) == 1)
                {
                    $suffix = strrev($match[1][0]);
                    if(show_details)
                        echo "<br>Тне suffix \"<b>{$match[1][0]}</b>\" was found in the list of English suffixes.<br>";
                    $suffixLen = strlen($suffix);
                    $iSend = $lenWord - 1;
                    for ($j=0; $j < $suffixLen; $j++)
                    {
                        $iS =  $iSend - $j;
                        if($wordDec[$iS]==".")
                        {
                            $chrEnt = $wordEnt[$iS];
                            if(!isset($lettersMapping[$chrEnt])){
                                $lettersMapping[$chrEnt] = substr($suffix,$j,1);
                                if(show_details)
                                    echo "Decrypted: $chrEnt -> ".substr($suffix, $j, 1)."<br>";
                            }
                        }
                    }
                }else if(show_details)
                echo "<br>No match found<br>";
            }
            else if(show_details)
                echo "<br>No match found<br>";
        }
        else if(show_details)
            echo "<br>Pattern undefined<br>";

        $res = show_res($wordEnt, $wordDec, $lettersMapping,1);

        if(show_details)
            echo <<<HTML
        $res
        <b>End function decryptFromEnglSuffixesListe</b></p>
        HTML;

    }

    ##############################################################

    function decryptWords($wordEnt, &$wordDec, &$lettersMapping){
        $res = show_res($wordEnt, $wordDec, $lettersMapping);
        if( strpos($wordDec, ".") === false ) return;
        if(show_details){
            echo <<<HTML

           <p><b>Begin function decryptWords</b>
           $res
           HTML;
        }
        $subPatt = substr($wordDec, 0, 4);
        $pattern = "/(?<=\ )($subPatt)/";
        if(show_details){
            echo "\$subPatt = $subPatt<br>";
            echo "\$pattern = $pattern<br>";
        }
        preg_match_all($pattern, hintText, $match);
        if(isset($match[0][0]))
        {
            $wordDec_0_4 = $match[0][0];
            if(show_details)
                echo "<b>$wordDec_0_4</b> match found in \"",
            str_replace($wordDec_0_4,"<u>$wordDec_0_4</u>",hintText),"\"<br>";
            for ($iChr=0; $iChr < 4; $iChr++)
            {
                $chrEnt = $wordEnt[$iChr];
                if(!isset($lettersMapping[$chrEnt])){
                    $lettersMapping[$chrEnt] = $wordDec_0_4[$iChr];
                    if(show_details)
                        echo "Decrypted: $chrEnt -> ",$wordDec_0_4[$iChr],"<br>";
                }
            }
        }
        else if(show_details)
            echo "<br>No match found<br>";

        $res = show_res($wordEnt, $wordDec, $lettersMapping, 1);

        if(show_details)
            echo <<<HTML
        $res
        <b>End function decryptWords</b></p>
        HTML;
    }

    ############################################################
    function show_res($wordEnt, &$wordDec, $lettersMapping, $onlySucces=false){
        $wordDec = decrypter($wordEnt, $lettersMapping);
        $res = strpos($wordDec, ".") === false?"Successfully":"Incompletely";
        if($res == "Incompletely" AND $onlySucces)
            return "";
        else
            return <<<HTML

            Encrypted word "<b>$wordEnt</b>" -> $res decrypted word "<b>$wordDec</b>"

            HTML;
    }
    ############################################################

```
## 3. Files

### File topRankingSuffixes.txt
```

    ES|ED|ING|LY|ER|OR

```
### File suffixes.txt
```

    -ABLE|-AL|-ANCE|-ANT|-ARY|-ATE|-DOM|-EN|-ENCE|-ER|-FUL|-IC|-IFY|-ING|-ISH|-ISM|-IST|-ITY|-IVE|-LESS|-LY|-MENT|-NESS|-OLOGY|-OUS|-SHIP|-SOME|-TION|-TUDE|-Y|

```

### File prefixes.txt
```

    |A-|AB-|AD-|AM-|ANTE-|ANTI-|AUTO-|BELLI-|BENE-|BI-|BIO-|CATA-|CHRON-|CIRCUM-|COM-|CONTRA-|CRED-|DE-|DEM-|DIA-|DIS-|EPI-|EQUI-|EX-|FOR-|FORE-|HOMO-|HYPER-|HYPO-|IN-||INTER-|INTRA-|MAGN-|MAL-|MICRO-|MIS-|MONO-|MOR-|NEO-|NON-|OB-|OMNI-|ORTHO-|OVER-|PAN-|PARA-|PER-|PERI-|PHIL-|POLY-|POST-|PRE-|PRIM-|PRO-|RE-|RETRO-|SEMI-|SUB-|SUPER-|SYM-|TRANS-|ULTRA-|UN-|UNI-|VIS-|

```
## 4. Decryption result
```

    One task from GCHQ Christmas Challenge 2024:
    PERHAPS READING the start of this substitution cipher will help you solve it
    ISKGWIM KSWEBDU BN DFN LBIGSKSE BM IKBCWKBQX W QSWEBDU BDEBLWNBFD NF XFO MFQPBDU NGBM WECBNNSEQX ESPBFOM BDBNBWQQX ODKSWEWAQS CSMMWUS

    Encrypted text:
    ISKGWIM KSWEBDU BN DFN LBIGSKSE BM IKBCWKBQX W QSWEBDU BDEBLWNBFD NF XFO MFQPBDU NGBM WECBNNSEQX ESPBFOM BDBNBWQQX ODKSWEWAQS CSMMWUS
    Step 1.

    Words with capital letters were found in the hint text:

    PERHAPS READING

    In the first 16 letters of the hint text were found (without spaces):
    Array
    (
        [0] => P
        [1] => E
        [2] => R
        [3] => H
        [4] => A
        [5] => P
        [6] => S
        [7] => R
        [8] => E
        [9] => A
        [10] => D
        [11] => I
        [12] => N
        [13] => G
    )
    In the first 16 letters of the encrypted text were found (without spaces):
    Array
    (
        [0] => I
        [1] => S
        [2] => K
        [3] => G
        [4] => W
        [5] => I
        [6] => M
        [7] => K
        [8] => S
        [9] => W
        [10] => E
        [11] => B
        [12] => D
        [13] => U
    )

    These two arrays are combined into a single array.

    Letter-to-letter Mapping:
    Array
    (
        [I] => P
        [S] => E
        [K] => R
        [G] => H
        [W] => A
        [M] => S
        [E] => D
        [B] => I
        [D] => N
        [U] => G
    )

    Decrypted text:
    PERHAPS READING I. N.. .IPHERED IS PRI.ARI.. A .EADING INDI.A.I.N .. ... S...ING .HIS AD.I..ED.. DE.I..S INI.IA... .NREADA..E .ESSAGE

    Step 2.
    Begin function suffByRankDecr

    Rank of 3-letters suffixes in entcrypted text:
    Array
    (
        [WIM] => 1
        [BDU] => 3
        [DFN] => 1
        [KSE] => 1
        [BQX] => 1
        [BFD] => 1
        [XFO] => 1
        [GBM] => 1
        [EQX] => 1
        [FOM] => 1
        [QQX] => 1
        [AQS] => 1
        [SMM] => 1
    )

    Top rank 3-letters suffix in entcrypted text: BDU


    Rank of 2-letters suffixes in entcrypted text:
    Array
    (
        [IM] => 1
        [BN] => 1
        [FN] => 1
        [SE] => 1
        [BM] => 2
        [QX] => 3
        [FD] => 1
        [NF] => 1
        [FO] => 1
        [OM] => 1
        [QS] => 1
        [MW] => 1
    )

    Top rank 2-letters suffix in entcrypted text: QX


    Top rank suffix BDU  has already been decrypted -> ING

    Begin function topRankSuffLettersDecr

    Top rank 2-letters suffix in entcrypted text: QX

    Top rank 2-letters english suffix: ES
    Top rank 2-letters english suffix: ED
    Top rank 2-letters english suffix: IN
    Top rank 2-letters english suffix: LY

    -------------------------------

    Passed inspection english suffix: LY
    Decrypted: Q -> L
    Decrypted: X -> Y

    -------------------------------
    Top rank 2-letters english suffix: ER
    Top rank 2-letters english suffix: OR

    End function topRankSuffLettersDecr

    End function suffByRankDecr

    Letter-to-letter Mapping:
    Array
    (
        [I] => P
        [S] => E
        [K] => R
        [G] => H
        [W] => A
        [M] => S
        [E] => D
        [B] => I
        [D] => N
        [U] => G
        [Q] => L
        [X] => Y
    )

    Decrypted text:
    PERHAPS READING I. N.. .IPHERED IS PRI.ARILY A LEADING INDI.A.I.N .. Y.. S.L.ING .HIS AD.I..EDLY DE.I..S INI.IALLY .NREADA.LE .ESSAGE

    Step 3.

    Begin function decryptFromEnglPrefixesListe

    Encrypted word "LBIGSKSE" -> Incompletely decrypted word ".IPHERED"

    Pattern undefined

    End function decryptFromEnglPrefixesListe


    Begin function decryptFromEnglSuffixesListe

    Encrypted word "LBIGSKSE" -> Incompletely decrypted word ".IPHERED"

    Pattern undefined

    End function decryptFromEnglSuffixesListe


    Begin function decryptWords

    Encrypted word "LBIGSKSE" -> Incompletely decrypted word ".IPHERED"
    $subPatt = .IPH
    $pattern = /(?<=\ )(.IPH)/
    CIPH match found in "PERHAPS READING THE START OF THIS SUBSTITUTION _CIPH_ER WILL HELP YOU SOLVE IT"
    Decrypted: L -> C

    Encrypted word "LBIGSKSE" -> Successfully decrypted word "CIPHERED"

    End function decryptWords


    Begin function decryptFromEnglPrefixesListe

    Encrypted word "IKBCWKBQX" -> Incompletely decrypted word "PRI.ARILY"
    $pattern = /(?<=\|)(PRI.)\-/i

    $match[1]  Array
    (
        [0] => PRIM
    )

    Тне prefix "PRIM" was found in the list of English prefixes.

    $prefLen = 4

    Decrypted: C -> M

    Encrypted word "IKBCWKBQX" -> Successfully decrypted word "PRIMARILY"

    End function decryptFromEnglPrefixesListe


    Begin function decryptFromEnglSuffixesListe

    Encrypted word "BDEBLWNBFD" -> Incompletely decrypted word "INDICA.I.N"
    $subPatt = I.N|.I.N
    $pattern = /(?<=\-)(I.N|.I.N)\|/i

    Тне suffix "TION" was found in the list of English suffixes.
    Decrypted: F -> O
    Decrypted: N -> T

    Encrypted word "BDEBLWNBFD" -> Successfully decrypted word "INDICATION"

    End function decryptFromEnglSuffixesListe


    Begin function decryptFromEnglPrefixesListe

    Encrypted word "MFQPBDU" -> Incompletely decrypted word "SOL.ING"
    $pattern = /(?<=\|)(SOL.)\-/i

    No match found

    End function decryptFromEnglPrefixesListe


    Begin function decryptFromEnglSuffixesListe

    Encrypted word "MFQPBDU" -> Incompletely decrypted word "SOL.ING"
    $subPatt = .ING
    $pattern = /(?<=\-)(.ING)\|/i

    No match found

    End function decryptFromEnglSuffixesListe


    Begin function decryptWords

    Encrypted word "MFQPBDU" -> Incompletely decrypted word "SOL.ING"
    $subPatt = SOL.
    $pattern = /(?<=\ )(SOL.)/
    SOLV match found in "PERHAPS READING THE START OF THIS SUBSTITUTION CIPHER WILL HELP YOU SOLVE IT"
    Decrypted: P -> V

    Encrypted word "MFQPBDU" -> Successfully decrypted word "SOLVING"

    End function decryptWords


    Begin function decryptFromEnglSuffixesListe

    Encrypted word "ESPBFOM" -> Incompletely decrypted word "DEVIO.S"
    $subPatt = O.S|IO.S
    $pattern = /(?<=\-)(O.S|IO.S)\|/i

    Тне suffix "OUS" was found in the list of English suffixes.
    Decrypted: O -> U

    Encrypted word "ESPBFOM" -> Successfully decrypted word "DEVIOUS"

    End function decryptFromEnglSuffixesListe


    Begin function decryptFromEnglSuffixesListe

    Encrypted word "ODKSWEWAQS" -> Incompletely decrypted word "UNREADA.LE"
    $subPatt = .LE|A.LE
    $pattern = /(?<=\-)(.LE|A.LE)\|/i

    Тне suffix "ABLE" was found in the list of English suffixes.
    Decrypted: A -> B

    Encrypted word "ODKSWEWAQS" -> Successfully decrypted word "UNREADABLE"

    End function decryptFromEnglSuffixesListe

    Letter-to-letter Mapping:
    Array
    (
        [I] => P
        [S] => E
        [K] => R
        [G] => H
        [W] => A
        [M] => S
        [E] => D
        [B] => I
        [D] => N
        [U] => G
        [Q] => L
        [X] => Y
        [L] => C
        [C] => M
        [F] => O
        [N] => T
        [P] => V
        [O] => U
        [A] => B
    )

    Decrypted text:
    PERHAPS READING IT NOT CIPHERED IS PRIMARILY A LEADING INDICATION TO YOU SOLVING THIS ADMITTEDLY DEVIOUS INITIALLY UNREADABLE MESSAGE


    Decrypted time: 0.239343 ms

```
