<?php
# recursively remove a directory
function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
        if(is_dir($file))
            rrmdir($file);
        else
            unlink($file);
    }
    rmdir($dir);
}
function trim_value(&$value)
{
    $value = trim($value);
}
function echoer($string){
    echo $string;
    flush();
}
function rglob($pattern, $flags = 0)
{
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
        $files = array_merge($files, rglob($dir.'/'.basename($pattern), $flags));
    }

    return $files;
}
function formatDuration($seconds)
{
    if ($seconds < 0.001) {
        return round($seconds * 1000000) . 'μs';
    } else if ($seconds < 1) {
        return round($seconds * 1000, 2) . 'ms';
    }
    $seconds = round($seconds, 2);
    if($seconds>59){
        $seconds = (int)$seconds;
        $seconds = sprintf( "%02.2dm%02.2ds", floor( $seconds / 60 ), $seconds % 60 );
    }else{
        $seconds .= 's';
    }
    
    return $seconds;
}
function array_merge_recursive_distinct ( array &$array1, array &$array2 ){
  $merged = $array1;

  foreach ( $array2 as $key => &$value )
  {
    if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
    }
    else
    {
      $merged [$key] = $value;
    }
  }

  return $merged;
}
function xml_encode($value, $tag = "root")
{
  if( !is_array($value)
        && !is_string($value)
        && !is_bool($value)
        && !is_numeric($value)
        && !is_object($value) ) {
            return false;
    }
     function x2str($xml,$key)
    {
        if (!is_array($xml) && !is_object($xml)) {
            return "<$key>".htmlspecialchars($xml)."</$key>";
        }
        $xml_str="";
        foreach ($xml as $k=>$v) {
            if (is_numeric($k)) {
                $k = "_".$k;
            }
            $xml_str.=x2str($v,$k);
        }

        return "<$key>$xml_str</$key>";
    }

    return simplexml_load_string(x2str($value,$tag))->asXml();
}

function xml_decode($xml)
{
    if (!is_string($xml)) {
        return false;
    }
    $xml = @simplexml_load_string($xml);

    return $xml;
}
function casttoclass($class, $object)
{
  return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . ':"' . $class . '"', serialize($object)));
}
function encodeAccent($text)
{
    $text = htmlentities($text);
    $text = htmlspecialchars_decode($text);

    return $text;
}
/////////////////////////////////////////////////////
/////////Une url devient goo.gl URL/////////
/////////////////////////////////////////////////////
function getShortUrl($longUrl)
{
    $ch = curl_init("https://www.googleapis.com/urlshortener/v1/url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"longUrl": "'. $longUrl .'"}');

    $res = curl_exec($ch);
    $id = json_decode($res, true);
    $id = $id['id'];
    curl_close($ch);

    return $id;
}
function transformUrl($text)
{
     function callbackUrl($matches)
    {
        return getShortUrl($matches[1]);
    }
    $text = preg_replace_callback('#(http(s?)://[^(goo\.gl)](([a-zA-Z0-9]|-|_|/|\.)*))#is', 'callbackUrl', $text);

    return $text;
}
/////////////////////////////////////////////////////
/////////Repare un DOM XML type HTML mal formé/////////
/////////////////////////////////////////////////////
function repairHTML($html)
{
    // hide DOM parsing errors
    libxml_use_internal_errors(true);
    libxml_clear_errors();

    // load the possibly malformed HTML into a DOMDocument
    $dom = new DOMDocument();
    $dom->recover = true;
    $dom->loadHTML('<span id="repair">'. $html .'</span>'); // input UTF-8

    // copy the document content into a new document
    $doc = new DOMDocument();
    foreach ($dom->getElementById('repair')->childNodes as $child)
    $doc->appendChild($doc->importNode($child, true));

    // output the new document as HTML
    $doc->encoding = 'UTF-8'; // output UTF-8
    $doc->formatOutput = false;

    return trim($doc->saveHTML());
}
/////////////////////////////////////////////////////
/////////Enleve certaine valeur d'un tableau/////////
/////////////////////////////////////////////////////
function del_value($tab, $value)
{
    $tabOut = $tab;
    foreach ($tab as $key=>$out) {
        if ($out==$value) {
            unset($tabOut[$key]);
        }
    }

    return $tabOut;

}
/////////////////////////////////////////////////////
/////////REDIMENSION DE TEXTE (COUPAGE)/////////
/////////////////////////////////////////////////////
//coupage avec espace
function couper_texte($texte, $nb_caractere_max, $fin_texte)
{
    $nb_caractere = strlen($texte);
          $texte_couper = NULL;
    if ($nb_caractere > $nb_caractere_max) {
        $texte_array = str_split($texte);
                        $i = $nb_caractere_max;
                        while ($texte_array[$i] != ' ' AND $i<$nb_caractere) {
                            $i++;
                        }
            for ($p=0; $p<$i; $p++) {
                            $texte_couper .= $texte_array[$p];
                        }
        $texte = repairHTML($texte_couper);
                if ($i<$nb_caractere) {
                    $texte .= $fin_texte;
                }
    }

    return $texte;
}
function couper_texte_sec($texte, $nb_caractere_max, $fin_texte=null)
{
    $nb_caractere = strlen($texte);
    if ($nb_caractere > $nb_caractere_max) {
        $texte_array = str_split($texte);
        $texte ='';
        for ($i = 0; $i <= $nb_caractere_max-1; $i++) {
                    $texte .= $texte_array[$i];
        }
        $texte .= $fin_texte;
    }

    return $texte;
}
////////////////////////////////////////////
//////DUREE D'EXECUTION D'UNE PAGE//////
////////////////////////////////////////////
function getmtime()
{
    $temps = microtime(false);
    $temps = explode(' ', $temps);

    return $temps[1] + $temps[0];
}
///////////////////////////////////////////////////////
///CONVERTIT CHAINE AVEC ACCENT -> SANS ACCENT////
//////////////////////////////////////////////////////
function removeaccents($text)
  {
      return strtr($text,
        base64_decode("wMHCw8TF4OHi4+Tl0tPU1dbY8vP09fb4yMnKy+jp6uvH58zNzs/s7e7v2drb3Pn6+/z/0fE="),
        "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn");
  }
///////////////////////////////////////////////////////
////////////////////EXTENSION D'IMAGE//////////////////
//////////////////////////////////////////////////////
function createImgFrom($image)
{
        if (strstr($image, '.jpeg') or strstr($image, '.jpg') or strstr($image, '.JPG') or strstr($image, '.JPEG')) {
        $image = imagecreatefromjpeg($image);
    } elseif (strstr($image, '.png') or strstr($image, '.PNG')) {
        $image = imagecreatefrompng($image);
    } elseif (strstr($image, '.gif') or strstr($image, '.GIF')) {
        $image = imagecreatefromgif($image);
    }

        return $image;
}
///////////////////////////////////////////////////////
//////////////////BYTE CONVERTER///////////////////////
//////////////////////////////////////////////////////
function format_bytes($size)
{
    $units = array(' o', ' Ko', ' Mo', ' Go', ' To');
    for ($i = 0; $size >= 1024 && $i < 4; $i++) {
        $size /= 1024;
    }

    return round($size, 2).$units[$i];
}
/////////////////////////////////////
/////DONNE LE NOM DE LA PAGE//////
/////////////////////////////////////
function nom_page()
{
    $root = $_SERVER['PHP_SELF'];
    $root_path = pathinfo($root);
    $root_name = preg_replace('#\.(php|html|htm)?#', '', $root_path['basename']);

    return $root_name;
}
/////////////////////////////////////////////////////
////POSITION D'UN TEXTE SELON UNE VALEUR DE ARRAY////
/////////////////////////////////////////////////////
function strripos_array($array, $need)
{
    $pos = null;
    foreach ($array as $key => $value) {
        $cond = strripos($need, $value);
        if (!empty($cond)) {
            $pos = $value;
            break;
        }
    }

    return $pos;
}
/////////////////////////////////
/////RECUPERER NOM DE DOMAINE////
/////////////////////////////////
function getDomain($url)
{
   return preg_replace("/^[\w]{2,6}:\/\/([\w\d\.\-]+).*$/","$1",$url);
}
/////////////////////////////////////
/////DONNE  LA PAGE SANS GET//////
/////////////////////////////////////
function page_sget()
{
    $root = $_SERVER['PHP_SELF'];
    $root_path = pathinfo($root);

    return $root_path['basename'];
}
///////////////////////////////
////DONNE L'ADRESSE WEB////
//////////////////////////////
function lien_web()
{
    $root = $_SERVER['PHP_SELF'];
    $root = preg_replace('#\w+\.(php|html|htm)?#', '', $root);
    $lien_web = 'http://'. $_SERVER['SERVER_NAME'] . $root;

    return $lien_web;
}
///////////////////////////////
/////Test connection port//////
///////////////////////////////
function isPortOpen($host, $port)
{
    $f=@fsockopen($host, $port);
    if (is_resource($f)) {
        fclose($f);

        return true;
    }

    return false;
}
///////////////////////////////
///////REDIRECTION//////////
//////////////////////////////
function redirection($new_site)
{
    $page_actuelle = $_SERVER['PHP_SELF'];
    //Si il y a des get
    if ($_GET != NULL) {
        $i=0;
        $array_get = $_GET;
        //on reecrit la meme adresse avec les get
        foreach ($array_get as $cle => $element) {
            if ($i == 0) {
                $page_actuelle = $page_actuelle .'?'. $cle .'='. $element;
            } else {
                $page_actuelle = $page_actuelle .'&amp;'. $cle .'='. $element;
            }
            $i++;
        }
    }
    if (!strstr($new_site, 'http://')) {
        $new_site = 'http://'. $new_site;
    }

    return '<meta http-equiv="refresh" content="0;url='. $new_site . $page_actuelle .'>';
}

//////////////////////////////////////////
/////////CREATION DE MINIATURE////////
//////////////////////////////////////////
function miniature($image, $largeur, $hauteur, $dossier, $image_nom)
{

       $image = createImgFrom($image);

        $hauteur_source = imagesy($image);
        $largeur_source = imagesx($image);

        $miniature = imagecreatetruecolor($largeur,$hauteur);
        $couleurDomin = array();
        for ($i = 0; $i<=$largeur_source; $i++) {
            $color_index = imagecolorat($image, $i, 0);
            $transparentcolor = imagecolorsforindex( $image, $color_index);
            if (!isset($couleurDomin[$transparentcolor['red'].' '.$transparentcolor['green'].' '.$transparentcolor['blue']])) {
                $couleurDomin[$transparentcolor['red'].' '.$transparentcolor['green'].' '.$transparentcolor['blue']] = 0;
            } else {
                $couleurDomin[$transparentcolor['red'].' '.$transparentcolor['green'].' '.$transparentcolor['blue']] ++;
            }
        }
        for ($i = 0; $i<=$hauteur_source; $i++) {
            $color_index = imagecolorat($image, 0, $i);
            $transparentcolor = imagecolorsforindex( $image, $color_index);
            if (!isset($couleurDomin[$transparentcolor['red'].' '.$transparentcolor['green'].' '.$transparentcolor['blue']])) {
                $couleurDomin[$transparentcolor['red'].' '.$transparentcolor['green'].' '.$transparentcolor['blue']] = 0;
            } else {
                $couleurDomin[$transparentcolor['red'].' '.$transparentcolor['green'].' '.$transparentcolor['blue']] ++;
            }
        }
        arsort($couleurDomin);
        $color = explode(' ', key($couleurDomin));
        $image_nom_nouveau = $dossier . $image_nom .'.png';

        $newtransparentcolor = imagecolorallocate(
        $miniature,
        $color[0],
        $color[1],
        $color[2]
        );
        imagefill( $miniature, 0, 0, $newtransparentcolor );
        // On rend l'arrière-plan transparent
        imagecolortransparent($miniature, $black);

            $destination_x = (int) (($largeur/2)-($largeur_source/2));
            $destination_y =  (int) (($hauteur/2)-($hauteur_source/2));

            imagecopyresized($miniature, $image, $destination_x, $destination_y, 0, 0, $largeur_source, $hauteur_source, $largeur_source, $hauteur_source);
            imagepng($miniature, $image_nom_nouveau, 9);

                    return $image_nom_nouveau;
    }
    //miniature redimensionne pour conserver toute l'image et mettre au bon format.
     function thumbnail($image, $maximum, $dossier, $image_nom)
    {
            $image = createImgFrom($image);

            $hauteur = imagesy($image);
            $largeur = imagesx($image);

            $rapport = $maximum / $hauteur ;
            $hauteur_miniature = $hauteur * $rapport;
            $largeur_miniature = $largeur * $rapport;

                    $image_nom_nouveau = $dossier . $image_nom .'.png';
        $miniature = imagecreatetruecolor($largeur_miniature,$hauteur_miniature);
        imagecopyresampled($miniature, $image, 0, 0, 0, 0, $largeur_miniature, $hauteur_miniature, $largeur,$hauteur);
        imagepng($miniature, $image_nom_nouveau, 9);

        return $image_nom_nouveau;
}

/*ma premi�re fonction faite avec difficulter.
fonction pour savoir l'age de quelqu'un*/
function getAge($date_de_naissance)
{
    //$date_de_naissance en format: mm/jj/yyyy
    $date_naissance = explode('/', $date_de_naissance);
    $date_aujourdhui = date('m/d/Y');
    $date_aujourdhui = explode('/', $date_aujourdhui);
    $age = ($date_aujourdhui[2].$date_aujourdhui[0].$date_aujourdhui[1]) - ($date_naissance[2].$date_naissance[0].$date_naissance[1]);

    return (int)($age/10000);
}
/////////////////////////
///DUMP LA BASE SQL////
////////////////////////
function dump_sql($fichier, $delimiteur=';', $bdd)
{
    $ecrire = '-- SQL Dump BY ARTHURH
-- Tous droits reserves
-- http://arhdev.com
--
-- Serveur: '. $_SERVER['SERVER_NAME'] .'
-- Genere le : '. date('d/m/y a H\hi:s') .'
';
    $ecrire .= "\n";
    $ecrire .= "\n";
    $ecrire .= "\n";
    $reponse = $bdd->query("SHOW TABLES");
    $table = array();
    while ($donnees = $reponse->fetch()) {
        $table[] = $donnees[0];
    }
    foreach ($table as $key => $value) {
        $reponse = $bdd->query("SHOW INDEX FROM $value");
        $donnees = $reponse->fetch();
        $ecrire .= '-- STRUCTURE DE `'. $value .'`';
        $ecrire .= "\n";
        $ecrire .= "\n";
        $ecrire .= 'DROP TABLE IF EXISTS `'. $value .'`'. $delimiteur;
        $ecrire .= "\n";
        $ecrire .= 'CREATE TABLE IF NOT EXISTS `'. $value .'` (';

        $i=0;
        $field = array();

        $reponse = $bdd->query("DESCRIBE $value");
        while ($donnees = $reponse->fetch()) {
            $field[] = $donnees['Field'];
            if ($i != 0) {
                $ecrire .= ",";
            }
            $ecrire .= "\n";
            $ecrire .= '`'. $donnees['Field'] .'` ';
            $ecrire .= $donnees['Type'] . ' ';
            if ($donnees['Null'] == 'NO') {
                $ecrire .= 'NOT NULL ';
            } elseif ($donnees['Null'] == 'YES') {
                $ecrire .= 'DEFAULT NULL ';
            }
            if (!empty($donnees['Default'])) {
                $ecrire .= 'DEFAULT \''. $donnees['Default'] .'\' ';
            }
            if (!empty($donnees['Extra'])) {
                $ecrire .= strtoupper($donnees['Extra']) .' ';
                if ($donnees['Extra'] == 'auto_increment') {
                    $ai_col = TRUE;
                } else {
                    $ai_col = FALSE;
                }
            }

            if ($donnees['Key'] == 'PRI') {
                $clef_primaire = $donnees['Field'];
            }
            $i++;
        }

        if (!empty($clef_primaire)) {
            $ecrire .= ',';
            $ecrire .= "\n";
            $ecrire .= 'PRIMARY KEY (`'. $clef_primaire .'`)';
            $ecrire .= "\n";
        } else {
            $ecrire .= "\n";
        }

        $ecrire .= ') ';
        $reponse = $bdd->query("SHOW TABLE STATUS LIKE '$value'");
        $donnees = $reponse->fetch();
        $ecrire .= 'ENGINE='. $donnees['Engine'] .' ';
        $encodage = explode('_', $donnees['Collation']);
        $encodage = $encodage[0];
        $ecrire .= 'DEFAULT CHARSET='. $encodage .' ';
        if ($ai_col) {
            $ecrire .= 'AUTO_INCREMENT='. $donnees['Auto_increment'] .' ';
        }
        $ecrire .= $delimiteur;
        $ecrire .= "\n";
        $ecrire .= "\n";
        if ($donnees['Rows'] > 0) {
            $ecrire .= '-- CONTENU DE `'. $value .'`';
            $ecrire .= "\n";
            $ecrire .= "\n";

            $i = 0;
            $ecrire .= 'INSERT INTO `'. $value .'` (';
            foreach ($field as $clef => $champ) {
                if ($i != 0) {
                    $ecrire .= ', ';
                }
                $ecrire .= '`'. $champ .'`';
                $i++;
            }
            $ecrire .= ') VALUES';
            $ecrire .= "\n";

            $n = 0;
            $reponse = $bdd->query("SELECT * FROM $value");
            while ($donnees = $reponse->fetch()) {
                if ($n != 0) {
                    $ecrire .= ",\n";
                }
                $ecrire .= '(';
                $i = 0;
                foreach ($field as $clef => $champ) {
                    if ($i != 0) {
                        $ecrire .= ', ';
                    }
                    if (is_numeric($donnees[$champ])) {
                        $ecrire .= $donnees[$champ];
                    } elseif ($donnees[$champ] == null) {
                        $ecrire .= 'NULL';
                    } else {
                        $champ_d = $donnees[$champ];
                        $champ_d = str_replace("'", "''", $champ_d);
                        $ecrire .= '\''. addslashes($champ_d) .'\'';
                    }
                    $i++;
                }
                $ecrire .= ')';
                $n++;
            }
            $ecrire .= $delimiteur;
        }
        $ecrire .= "\n";
        $ecrire .= "\n";
        $ecrire .= "-- --------------------------------------------------------------------------";
        $ecrire .= "\n";
        $ecrire .= "\n";

        unset($ai_col);
        unset($clef_primaire);
        $ecrire = str_replace("\\'\\'", "''", $ecrire);
        $ecrire = str_replace("\\''", "''", $ecrire);
        $ecrire = str_replace("''''", "''", $ecrire);
        $ecrire = stripslashes($ecrire);
        $ecrire = str_replace('\\', '\\\\', $ecrire);

    }
    if (is_file($fichier)) {
        unlink($fichier);
    }
    $monfichier = fopen($fichier, "a+");
    fseek($monfichier, 0);
    fputs($monfichier, $ecrire);
    fclose($monfichier);
}

/////////////////////////////////////////
//////PAGE COURANTE ACITVE LIEN///////
////////////////////////////////////////

function selected($myPage)
{
    $pathinfo_fichier = pathinfo($_SERVER['PHP_SELF']);
    $name = explode('.', $pathinfo_fichier['basename']);
    $name = $name[0];
    if ($name != $myPage) {
        return null;
    } else {
        return 'class="selected" ';
    }
  }
function makeArray($value, $forceSymbol=null)
{
    if (is_array($value)) {
        return $value;
    }
    if (empty($forceSymbol)) {
        $symbol = str_split(',;/-|.');
        $use = array();
        $caractere = str_split($value);
        foreach ($caractere as $element) {
            if (in_array($element, $symbol)) {
                if (empty($use[$element])) {
                    $use[$element] = 1;
                } else {
                    $use[$element]++;
                }
            }
        }
    } else {
        $use = array($forceSymbol=>1);
    }
    if (!empty($use)) {
        $use = array_flip($use);
        krsort($use);
        $value = explode(current($use), $value);
        foreach ($value as $key => $string) {
            $value[$key] = trim($string);
        }
    } else {
        $value = array($value);
    }

    return $value;
}

/////////////////////////////////////////////////////
//////RETURN SELF PAGE/////////////////////////////
////////////////////////////////////////////////////
function phpSelf()
{
    $phpself = $_SERVER['PHP_SELF'];
    $i = 0;
    $get = null;
    foreach ($_GET as $key=>$value) {
        if ($i==0) {
            $get .= '?';
        } else {
            $get .= '&';
        }
        $get .= $key .'='. $value;
    }

    return $phpself . $get;
}
