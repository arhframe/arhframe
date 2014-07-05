<?php
namespace arthurh\controller;
use arhframe\var_dump as var_dump;
class ArthurhController extends \Controller{
	private $arrayOutput = null;
	public function __construct(){
		parent::__construct();
		$this->arrayOutput['dateNaissance'] = getAge('19/06/1991');
        $this->arrayOutput['isEnglish'] = false;
	}
    public function __before(){
        $lang = $this->getGet()->get('lang');
        if(!empty($lang)){
            $this->changeLang($lang);
        }
        if($this->getLocalization() != 'fr_FR'){
            $this->arrayOutput['isEnglish'] = true;   
        }
    }
    public function indexAction()
    {
    	$this->arrayOutput['selected'] = 'biographie';
        $pdfOn = $this->getRequest()->getGetRequest('pdf');
        if(!empty($pdfOn)){
            $this->getConfig()->config->debug = false;
            //header('Content-type: application/pdf');
            $html2pdf = $this->helper('html2pdf','P','A4','fr');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->WriteHTML($html2pdf->getHtmlFromPage($this->createRenderer('cvpdf.twig', $this->arrayOutput)->getHtml()));
            return $html2pdf->Output('CVPDF.pdf');
        }
        //return $this->render('test.mustache', array('name' => 'arthur'));
        $reponse = $this->render('bio.twig', $this->arrayOutput);
        return $reponse;
    }
    public function realisationAction()
    {
    	$this->arrayOutput['selected'] = 'realisation';
        return $this->render('realisation.twig', $this->arrayOutput);
    }
    public function contactAction()
    {
    	$this->arrayOutput['selected'] = 'contact';
        return $this->render('contact.twig', $this->arrayOutput);
    }
    public function contactPostAction(){
        $form = $this->getForm('contact');
        $this->arrayOutput['selected'] = 'contact';
        if(!$form->validate()){
            return $this->render('contact.twig', $this->arrayOutput);
        }
        $mail = $this->helper('mail');

        $mail->From = 'arthurh@arthurh.fr';
        $mail->FromName = 'ArthurH.fr';
        $mail->AddAddress('arthurh.halet@gmail.com');
        $mail->IsHTML(false);
        $mail->Subject = $this->getPost()->get('subject');

        $mail->Body    = "Adresse mail: ".$this->getPost()->get('yourmail') ."\n\n". $this->getPost()->get('content');

        if(!$mail->Send()) {
           $this->arrayOutput['messageContact'] = 'Le message ne peut être envoyé: '. $mail->ErrorInfo;
        }else{
            $this->arrayOutput['messageContact'] = 'Votre message a été envoyé.';
        }

        return $this->render('contact.twig', $this->arrayOutput);
    }
}
?>
