<?php
import('arhframe.fct_recur');
class Webmail
{
    public $fileHost = null;
    public $serveur;
    public $protocole;
    public $ssl = FALSE;
    public $port;
    public $login;
    public $mdp;
    public $nameFileSup = null;
    private $notAdress;
    private $mailbox;
    private $headersView;
    private $headers;
    private $content;
    private $partsarray;
    private function connect()
    {
        if ($this->ssl) {
            $this->ssl = '/ssl';
        }
        $this->protocole = '/'. $this->protocole;
        $this->mailbox = imap_open('{'. $this->serveur .':'. $this->port . $this->protocole . $this->ssl .'}', $this->login, $this->mdp);

    }
   public function setNotAdress($adress)
   {
       $this->notAdress = makeArray($adress);
   }
   public function getHeaders($noMail=null)
   {
        if (empty($this->mailbox)) {
            $this->connect();
        }
        $this->headersView = array();
        if (empty($noMail)) {
            $this->headers = imap_headers($this->mailbox);
        } else {
            $this->headers = makeArray($noMail);
           $this->headers = array_flip($this->headers);
        }
       $i=0;
        foreach ($this->headers as $key=>$value) {
            $enTete = imap_headerinfo($this->mailbox, $key+1);
            $from = $enTete->from;
            foreach ($from as $id => $object) {
                $fromname = imap_mime_header_decode($object->personal);
                $fromname = $fromname[0]->text;
                $fromaddress = $object->mailbox . "@" . $object->host;
            }
            $time = strtotime($enTete->date);
            if (date('d/m/Y') == date('d/m/Y', $time)) {
                $date = date('H:i', $time);
            } else {
                $date = date('d/m/Y H:i', $time);
            }
            $subject = imap_mime_header_decode($enTete->Subject);

             if (!in_array($fromaddress, $this->notAdress)) {
                    $this->headersView[$i]['noMail'] = $key+1;
                    $this->headersView[$i]['subject'] = $this->decoding_mail($subject[0]->text);
                    $this->headersView[$i]['from'] = $fromname;
                    $this->headersView[$i]['date'] = $date;
                    $this->headersView[$i]['encoding'] = mb_detect_encoding($subject[0]->text);
                    $i++;
             }
        }

        return $this->headersView;
    }
    private function decoding_mail($string, $encoding=NULL, $attachment=false)
    {
        if ($encoding==3) {
            $string=base64_decode($string);
        }
        if ($encoding==4) {
            $string=quoted_printable_decode($string);
        }
        if (!$attachment) {
            setlocale(LC_CTYPE, 'fr_FR.UTF-8');
            $accent = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $string);

            if (removeaccents($accent) != $accent) {
                $string = $accent;
            }
            $string = htmlentities($string);
        }

        return $string;
    }
    private function attachment($parse, $part, $partno, $noMail)
    {
        $filename='';
        if (count($parse->dparameters)>0) {
            foreach ($parse->dparameters as $dparam) {
                if ((strtoupper($dparam->attribute)=='NAME') ||(strtoupper($dparam->attribute)=='FILENAME')) $filename=$dparam->value;
                }
            }
        if ($filename=='') {
            if (count($parse->parameters)>0) {
                foreach ($parse->parameters as $param) {
                    if ((strtoupper($param->attribute)=='NAME') ||(strtoupper($param->attribute)=='FILENAME')) $filename=$param->value;
                    }
                }
            }
        if ($filename!='') {
            $this->partsarray[$noMail][$partno]['attachment'] = array('filename'=>$filename, 'path'=>$this->nameFileSup . $filename );
                $fp=fopen($this->fileHost . $this->nameFileSup . $filename,"w+");
                fwrite($fp,  $part);
                fclose($fp);
           }
    }
    private function parseparts($parse, $partno, $noMail)
    {
        $part=imap_fetchbody($this->mailbox, $noMail,$partno);
        if ($parse->type!=0 AND $this->fileHost!=null) {
            $part = $this->decoding_mail($part, $parse->encoding, TRUE);
            $this->attachment($parse, $part, $partno, $noMail);
        } elseif ($parse->type==0) {
            $part = $this->decoding_mail($part, $parse->encoding);
            if (strtoupper($parse->subtype)=='PLAIN') {
                $part = nl2br($part);
            }

            $this->partsarray[$noMail][$partno]['text'] = array('type'=>$parse->subtype,'string'=>$part, 'encoding'=>mb_detect_encoding($part));
        }
        if (count($parse->parts)>0) {
            foreach ($parse->parts as $pno=>$parr) {

                $this->parseparts($parr,($partno.'.'.($pno+1)), $noMail);
            }
        }
    }
    public function getContent($noMail)
    {
        if (empty($this->mailbox)) {
            $this->connect();
        }
        $struct=imap_fetchstructure($this->mailbox,$noMail);
        if (count($struct->parts)>0) {
        foreach ($struct->parts as $partno=>$partarr) {
                $this->parseparts($partarr,$partno+1, $noMail);
            }
        } else {
                $text=imap_body($this->mailbox,$noMail);
                $text = $this->decoding_mail($text, $struct->encoding);
                if (strtoupper($struct->subtype)=='PLAIN') {
                        $text=nl2br($text);
                }
                $this->partsarray[$noMail][1]['text']=array('type'=>$struct->subtype,'string'=>$text, 'encoding'=>mb_detect_encoding($text));
        }

        return $this->partsarray;
    }
    public function getNbMail()
    {
        if (empty($this->headersView)) {
            $this->getHeaders();
        }

        return count($this->headersView);
    }
    public function __destruct()
    {
        if (!empty($this->mailbox)) {
            imap_close($this->mailbox);
        }
    }

}
