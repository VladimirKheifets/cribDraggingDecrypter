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
