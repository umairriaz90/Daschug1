<?php

require_once('pdf/tcpdf/tcpdf_import.php');

/**
 * Allows PDF certificates to be created dynamically
 * by WP Courseware using the fpdf.php library.
 *
 */
class WPCW_Certificate
{
	protected $pdffile; 
	
	/**
	 * Size parameters that store the size of the page.
	 */
	protected $size_width;
	protected $size_height;
	protected $size_name;
	
	/**
	 * Position on x-axis of where the signature starts.
	 * @var Integer
	 */
	protected $signature_X;

	/**
	 * Position on y-axis of line where signature should be.
	 * @var Integer
	 */
	protected $footer_Y;
	
	/**
	 * The length of the line for the footer lines.
	 * @var Integer
	 */
	protected $footer_line_length;
	
	/**
	 * A list of the settings to use for the certificate generation.
	 * @var Array
	 */
	protected $settingsList;
	
	
	
	function __construct($size = 'A4hoch') 
	{
		// Update size variables to allow calculations for distance.
		$this->setSize($size);
		
		// Create basic page layout
		$this->pdffile = new TCPDF('P', 'mm', 'A4hoch', true, 'UTF-8', false);
		$this->pdffile->AddPage();
						
		// Load the certificate settings
		$this->settingsList = TidySettings_getSettings(WPCW_DATABASE_SETTINGS_KEY);	
	}
	
	/**
	 * Given a width, find out the position of the left side of the object to be added.
	 * @param Integer $width The width of the item to position.
	 * @return Integer The x-coordinate of the item to position to center it.
	 */
	function getLeftOfCentre($width)
	{
		return (($this->size_width - $width) / 2);
	}
	
	
	/**
	 * Given a string, write it to the center of the page.
	 * 
	 * @param String $str The string to center.
	 * @param Integer $y_pos The Y-coordinate of the string to position.
	 */
	function centerString($str, $y_pos)
	{
		$str_width = $this->pdffile->GetStringWidth($str);
		$str_x = $this->getLeftOfCentre($str_width);
		
		$this->pdffile->SetXY($str_x, $y_pos);
		$this->pdffile->Cell(0,0, $str, false, false);
	}
	
	/**
	 * Draw a centered line at the specified height.
	 * 
	 * @param Integer $width The width of the line.
	 * @param Integer $y_pos The Y-coordinate of the string to position.
	 */
	function centerLine($width, $y_pos)
	{				
		$x = $this->getLeftOfCentre($width);
		$this->pdffile->Line($x, $y_pos, $x+$width, $y_pos);
	}
	
	
	/**
	 * Set up the internal variables for size.
	 */
	function setSize($size)
	{
		switch ($size)
		{
			// A4 Size
			default:
				$this->size_name 	= 'A4';		
				$this->size_width 	= 297;
				$this->size_height 	= 210;
				// GW 150119
			case 'A4hoch':
				$this->size_name 	= 'A4hoch';		
				$this->size_width 	= 210;
				$this->size_height 	= 297;
				// / GW 150119
			break;
		}
		
		
	}
	
	
	
	
	/**
	 * Generate the certificate PDF.
	 * 
	 * @param String $student The name of the student.
	 * @param String $courseName The name of the course.
	 * @param String $certificateDetails The raw certificate details.
	 * @param String $showMode What type of export to do. ('download' to force a download or 'browser' to do it inline.)
	 */
	function generatePDF($student, $courseName, $certificateDetails, $showMode = 'download')
	{		
		// Do codepage conversions of text used in the certificate.
		$encoding = WPCW_arrays_getValue($this->settingsList, 'certificate_encoding', 'ISO-8859-1');
		
		// GW 150217 Umlaute zerschießen hier alles!
		// GW 150217 $student    = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $student);
		// GW 150114 Umlaute zerschießen hier alles!
		// GW 150114 $courseName = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', $courseName);
		
		// GW 150119 $topLineY = 45;
		// Beginn der gesamten Ausgabe

		$topLineY = 35;
		
		// Set the background image
		$bgType = WPCW_arrays_getValue($this->settingsList, 'cert_background_type', 'use_default');
		$bgImg  = WPCW_arrays_getValue($this->settingsList, 'cert_background_custom_url');
		
		// Disable auto-page-break
		$this->pdffile->SetAutoPageBreak(false, 0);
		
		// Use custom image
		if ($bgType == 'use_custom') {
			if ($bgImg) {
				$this->pdffile->Image($bgImg, 0, 0, $this->size_width, $this->size_height);	
			}
		}
		
		// Use default image
		else {
			$this->pdffile->Image(WPCW_plugin_getPluginDirPath() . 'img/certificates/certificate_bg.jpg', 0, 0, 297, 210, '', '', '', false, 300, '', false, false, 0);
		}


		$topLineY += 3;

		$this->pdffile->SetFont('Helvetica','', 6);
		$this->pdffile->SetXY(145, $topLineY);
		$this->pdffile->Cell(0,0, 'Urlaubs- und Lohnausgleichskasse der Bauwirtschaft', false, false);
		$topLineY += 2;

		$this->pdffile->SetXY(145, $topLineY);
		$this->pdffile->Cell(0,0, 'Zusatzversorgungskasse des Baugewerbes AG', false, false);
		
		$topLineY += 8;

		// Ich habe an der ... teilgenommen
		$this->pdffile->SetFont('Helvetica','B', 16);
		$this->centerString('Ich habe an der Datenschutz-Schulung und dem', $topLineY);
		$topLineY += 6;
		$this->centerString('Verständnistest für Beschäftigte bei SOKA-BAU teilgenommen.', $topLineY);

		$topLineY += 27;

		// Date - the date itself. Centre on the line
		$this->pdffile->SetFont('Helvetica','', 14);
				
		// Use date of completion if available from certificate details
		$completeDate = false;
		if ($certificateDetails && $certificateDetails->cert_generated) {
			$completeDate = strtotime($certificateDetails->cert_generated);
		}		
		
		// Use current date if not available.
		if ($completeDate <= 0) {
			$completeDate = current_time('timestamp');
		}
		
		$date_localFormat = get_option('date_format');
		
		$date_str =date_i18n($date_localFormat, $completeDate);		
		$date_str_kopie = $date_str;
		// GW 150225 Datum doch nich anzeigen $date_str = ",   den " . $date_str;
		$date_str = ",   den ";
		$date_str_len = $this->pdffile->GetStringWidth($date_str);
				
		$this->pdffile->SetXY(70, $topLineY);
		$this->pdffile->Cell(0,0, $date_str, false, false);

		$topLineY += 6;

		// Ort, Datum, Unterschrift
		$three_line_length = 57;
		// Ort	
		$ort_X = 10;
		$this->pdffile->SetFont('Helvetica','', 12);
		$str_N = 'Ort';
		$str_W = $this->pdffile->GetStringWidth($str_N);
		$new_X = $ort_X + ($three_line_length/2) - ($str_W/2);
		$new_X = round($new_X);
		$this->pdffile->SetXY($new_X, $topLineY);
		$this->pdffile->Cell(0, 0, $str_N, false, false, 'L');	
		$this->pdffile->Line($ort_X,$topLineY,$ort_X+$three_line_length,$topLineY);	

		// Datum	
		$datum_X = 77;
		$this->pdffile->SetFont('Helvetica','', 12);
		$str_N = 'Datum';
		$str_W = $this->pdffile->GetStringWidth($str_N);
		$new_X = $datum_X + ($three_line_length/2) - ($str_W/2);
		$new_X = round($new_X);
		$this->pdffile->SetXY($new_X, $topLineY);
		$this->pdffile->Cell(0, 0, $str_N, false, false, 'L');	
		$this->pdffile->Line($datum_X,$topLineY,$datum_X+$three_line_length,$topLineY);	

		// Unterschrift	
		$unterschrift_X = 145;
		$this->pdffile->SetFont('Helvetica','', 12);
		$str_N = 'Unterschrift, Abteilungs-Nr.';
		$str_W = $this->pdffile->GetStringWidth($str_N);
		$new_X = $unterschrift_X + ($three_line_length/2) - ($str_W/2);
		$new_X = round($new_X);
		$this->pdffile->SetXY($new_X, $topLineY);
		$this->pdffile->Cell(0, 0, $str_N, false, false, 'L');	
		$this->pdffile->Line($unterschrift_X,$topLineY,$unterschrift_X+$three_line_length,$topLineY);	       	

		$topLineY += 18;

		// Teilnahme-Zertifikat
		$this->pdffile->SetFont('Helvetica','B', 36);	
		$this->centerString('Teilnahme-Zertifikat', $topLineY);

		// Name des "Studenten"
		$this->pdffile->SetFont('Helvetica','B', 24);	
		
		$topLineY += 26;

		//FullName + LoginName kommen in der Form "Gerd Weyhing|gweyhing|male", also auseinanderschneiden.
		$exp = explode("|",$student);
		$student = $exp[0];	
		$student_username = $exp[1];
		$student_username = "Benutzername: ".$student_username;
		$student_address = $exp[2];

		if ($student_address != "") $student = $student_address." ".$student;

		$this->centerString($student, $topLineY);		

		$topLineY += 10;	

		$this->pdffile->SetFont('Helvetica','B', 16);
		$this->centerString($student_username, $topLineY);	

		$topLineY += 10;	

		// ...Completed...
		$this->pdffile->SetFont('Helvetica','', 16);
		$this->centerString('hat an der', $topLineY);
		
		$topLineY += 10;

		// Titel der Schulung
		$this->pdffile->SetFont('Helvetica','B', 16);
		$this->centerString($courseName, $topLineY);		

		$topLineY += 10;	

		// ...teilgenommen
		$this->pdffile->SetFont('Helvetica','', 16);
		$this->centerString('teilgenommen', $topLineY);

		$topLineY += 10;
		$str = 'und den Verständnistest|erfolgreich|bestanden.';
		$str_W = $this->pdffile->GetStringWidth($str);
		$str_exp = explode("|",$str);

		$this->pdffile->SetFont('Helvetica','', 16);
		$this->pdffile->SetXY(42, $topLineY);
		$this->pdffile->Cell(0,0, $str_exp[0], false, false);

		$this->pdffile->SetFont('Helvetica','B', 17);
		$this->pdffile->SetXY(105, $topLineY-0.4);
		$this->pdffile->Cell(0,0, $str_exp[1], false, false);

		$this->pdffile->SetFont('Helvetica','', 16);
		$this->pdffile->SetXY(138, $topLineY);
		$this->pdffile->Cell(0,0, $str_exp[2], false, false);

		$topLineY += 18;	
		$this->centerString('Es wurden folgende Themenschwerpunkte behandelt:', $topLineY);

		$this->pdffile->SetFont('Helvetica','', 14);
		$topLineY += 10;	
		
		$topLineY += 6;	
		$this->pdffile->SetXY(30, $topLineY);
		$this->pdffile->Cell(0,0, '- Rechtsquellen des Datenschutzes', false, false);

		$topLineY += 6;	
		$this->pdffile->SetXY(30, $topLineY);
		$this->pdffile->Cell(0,0, '- datenschutzrechtliche Grundbegriffe', false, false);

		$topLineY += 6;	
		$this->pdffile->SetXY(30, $topLineY);
		$this->pdffile->Cell(0,0, '- datenschutzrelevante tarifvertragliche Regelungen', false, false);

		$topLineY += 6;	
		$this->pdffile->SetXY(30, $topLineY);
		$this->pdffile->Cell(0,0, '- Datenschutz bei ULAK und ZVK', false, false);
				
		$topLineY += 12;	

		$this->pdffile->SetFont('Helvetica','', 14);

		// Untere Zeile

		$topLineY += 12;

		$date_X = 17;
		$signature_X = 115;
		$two_line_length = 70;

		$str_W = $this->pdffile->GetStringWidth($date_str_kopie);
		$new_X = $date_X + ($two_line_length/2) - ($str_W/2);
		$new_X = round($new_X);
		$this->pdffile->SetXY($new_X, $topLineY);
		$this->pdffile->Cell(0, 0, $date_str_kopie, false, false, 'L');	
		
		// Datum links unten	

		$topLineY += 6;
		
		$this->pdffile->SetFont('Helvetica','', 12);
		$str_N = 'Datum';
		$str_W = $this->pdffile->GetStringWidth($str_N);
		$new_X = $date_X + ($two_line_length/2) - ($str_W/2);
		$new_X = round($new_X);
		$this->pdffile->SetXY($new_X, $topLineY);
		$this->pdffile->Cell(0, 0, $str_N, false, false, 'L');	
		$this->pdffile->Line($date_X,$topLineY,$date_X+$two_line_length,$topLineY);	

		// Unterschrift rechts unten	
		
		$this->render_handleSignature();
		$this->pdffile->SetFont('Helvetica','', 12);
		$str_N = 'Referent';
		$str_W = $this->pdffile->GetStringWidth($str_N);
		$new_X = $signature_X + ($two_line_length/2) - ($str_W/2);
		$new_X = round($new_X);
		$this->pdffile->SetXY($new_X, $topLineY);
		$this->pdffile->Cell(0, 0, $str_N, false, false, 'L');	
		$this->pdffile->Line($signature_X,$topLineY,$signature_X+$two_line_length,$topLineY);	
		
		$topLineY += 5;
				
		//$this->pdffile->SetXY($date_X + (($this->footer_line_length - $date_str_len)/2), $topLineY);
		//$this->pdffile->Cell(0,0, $date_str, false, false);
		
		
		// Signature - signature itself			
		
		
		// Logo - handle rendering a logo if one exists
		$this->render_handleLogo();

		$topLineY += 5;

		// Date - field		
		//$this->pdffile->SetXY($date_X, $topLineY);
		//$this->pdffile->Cell(0, 0, __('Date', 'wp_courseware'), false, false, 'L');		    	
				
		// Signature - field
		//$this->pdffile->SetXY($this->signature_X, $topLineY);
		//$this->pdffile->Cell(0,0, __('Instructor', 'wp_courseware'), false, false, 'L');
	
		// Change output based on what's been specified as a parameter.
		if ('browser' == $showMode) {
			// GW 150120 - war: certificate.pdf (2x)
			$this->pdffile->Output('Teilnahme-Zertifikat.pdf', 'I');
		} else {
			$this->pdffile->Output('Teilnahme-Zertifikat.pdf', 'D');
		}
		
	}
	
	/**
	 * Convert a measurement from pixels to millimetres at 72dpi.
	 * @param Integer $px Measurement in pixels
	 * @return Float Millimetres
	 */
	static function px2mm($px){
	    return $px*25.4/72;
	}
	
	/**
	 * Convert a measurement from millimetres into pixels at 72dpi.
	 * @param Integer $mm Measurement in mm.
	 * @return Float Pixels
	 */
	static function mm2px($mm){
	    return ($mm*72)/25.4;
	}
	
	
	/**
	 * Renders the logo provided by the user.
	 */
	function render_handleLogo()
	{
		$logoShow = WPCW_arrays_getValue($this->settingsList, 'cert_logo_enabled');
		$logoImg = WPCW_arrays_getValue($this->settingsList, 'cert_logo_url');
		
		// No logo to work with, abort.
		if ('cert_logo' != $logoShow || !$logoImg) {
			return;
		}
		
		// Image is fetched using URL, and resized to match the space.
		$logoWidth = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_LOGO_WIDTH_PX);
		$logoHeight = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_LOGO_HEIGHT_PX);
			
		// GW 150119 $this->pdffile->Image($logoImg, $this->getLeftOfCentre($logoWidth), 134, $logoWidth); // Only force width
		$this->pdffile->Image($logoImg, $this->getLeftOfCentre($logoWidth), 224, $logoWidth); // Only force width
	}
	
	
	/**
	 * Renders the signature area for the certificate.
	 */
	function render_handleSignature()
	{

		$topLineY = 257;
		// Have we got a text or image signature?
		$signature = '';
		$signatureType = WPCW_arrays_getValue($this->settingsList, 'cert_signature_type', 'text');
		$signatureImg  = WPCW_arrays_getValue($this->settingsList, 'cert_sig_image_url');
		
		// Get the text for the signature
		if ('text' == $signatureType)
		{
			// Use codepage translation of signature text
			$encoding = WPCW_arrays_getValue($this->settingsList, 'certificate_encoding', 'ISO-8859-1');
			$signature = iconv('UTF-8', $encoding.'//TRANSLIT//IGNORE', WPCW_arrays_getValue($this->settingsList, 'cert_sig_text'));
			
			// Nothing to do, signature is empty
			if (!$signature) {
				return;
			}
			
			// Create the signature
			$signature_len = $this->pdffile->GetStringWidth($signature);
			$this->pdffile->SetXY($this->signature_X + (($this->footer_line_length - $signature_len)/2), $topLineY);
			$this->pdffile->Cell(300, 150, $signature, false, false);
		}
		
		// Image - see if we have anything to use.
		else 
		{
			// No image to work with
			if (!$signatureImg) {
				return;
			}
			
			// Image is fetched using URL, and resized to match the space. We're using
			// an image that's twice the size to get it to scale nicely.
			$sigWidth = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_SIGNATURE_WIDTH_PX);
			$sigHeight = WPCW_Certificate::px2mm(WPCW_CERTIFICATE_SIGNATURE_HEIGHT_PX);
			
			// Only force width
			$this->pdffile->Image($signatureImg, 122, 252, $sigWidth); 
			
		}
				
		
	}
}


?>