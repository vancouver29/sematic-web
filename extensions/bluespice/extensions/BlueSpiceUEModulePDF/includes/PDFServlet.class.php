<?php
/**
 * BsPDFServlet.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpiceUEModulePDF
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * UniversalExport BsPDFServlet class.
 * @package BlueSpiceUEModulePDF
 */
class BsPDFServlet {

	/**
	 * Gets a DOMDocument, searches it for files, uploads files and markus to webservice and generated PDF.
	 * @param DOMDocument $oHtmlDOM The source markup
	 * @return string The resulting PDF as bytes
	 */
	public function createPDF( &$oHtmlDOM ) {

		$this->findFiles( $oHtmlDOM );
		$this->uploadFiles();

		//HINT: http://www.php.net/manual/en/class.domdocument.php#96055
		//But: Formated Output is evil because is will destroy formatting in <pre> Tags!
		$sHtmlDOM = $oHtmlDOM->saveXML( $oHtmlDOM->documentElement );

		//Save temporary
		$status = \BsFileSystemHelper::ensureCacheDirectory( 'UEModulePDF' );
		if( !$status->isOK() ) {
			throw new \MWException( $status->getMessage() );
		}
		$sTmpHtmlFile = "{$status->getValue()}/{$this->aParams['document-token']}.html";
		$sTmpPDFFile  = "{$status->getValue()}/{$this->aParams['document-token']}.pdf";
		file_put_contents( $sTmpHtmlFile, $sHtmlDOM );

		$aOptions = array(
			'timeout' => 120,
			'postData' => array(
				'fileType' => '', //Need to stay empty so UploadAsset servlet saves file to document root directory
				'documentToken' => $this->aParams['document-token'],
				'sourceHtmlFile_name' => basename( $sTmpHtmlFile ),
				'sourceHtmlFile' => class_exists( 'CURLFile' ) ? new CURLFile( $sTmpHtmlFile ) : '@'.$sTmpHtmlFile,
				'wikiId' => wfWikiID()
			)
		);

		$config = \BlueSpice\Services::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' );

		if( $config->get( 'TestMode' ) ) {
			$aOptions['postData']['debug'] = "true";
		}

		global $bsgUEModulePDFCURLOptions;
		$aOptions = array_merge_recursive($aOptions, $bsgUEModulePDFCURLOptions);

		Hooks::run( 'BSUEModulePDFCreatePDFBeforeSend', array( $this, &$aOptions, $oHtmlDOM ) );

		$vHttpEngine = Http::$httpEngine;
		Http::$httpEngine = 'curl';
		//HINT: http://www.php.net/manual/en/function.curl-setopt.php#refsect1-function.curl-setopt-notes
		//Upload HTML source
		//TODO: Handle $sResponse
		$sResponse = Http::post(
			$this->aParams['soap-service-url'].'/UploadAsset',
			$aOptions
		);

		//Now do the rendering
		//We re-send the paramters but this time without the file.
		unset( $aOptions['postData']['sourceHtmlFile'] );
		unset( $aOptions['postData']['fileType'] );
		//We do not want the request to be multipart/formdata because that's more difficult to handle on Servlet-side
		$aOptions['postData'] = wfArrayToCgi( $aOptions['postData' ] );
		$vPdfByteArray = Http::post(
			$this->aParams['soap-service-url'].'/RenderPDF',
			$aOptions
		);
		Http::$httpEngine = $vHttpEngine;

		if( $vPdfByteArray == false ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::createPDF: Failed creating "'.$this->aParams['document-token'].'"'
			);
		}

		file_put_contents( $sTmpPDFFile, $vPdfByteArray );

		//Remove temporary file
		if( !$config->get( 'TestMode' ) ) {
			unlink( $sTmpHtmlFile );
			unlink( $sTmpPDFFile );
		}

		return $vPdfByteArray;
	}

	/**
	 * Uploads all files found in the markup by the "findFiles" method.
	 */
	protected function uploadFiles() {
		global $bsgUEModulePDFUploadThreshold;

		foreach( $this->aFiles as $sType => $aFiles ) {
			//Backwards compatibility to old inconistent PDFTemplates (having
			//"STYLESHEET" as type but linking to "stylesheets")
			//TODO: Make conditional?
			if( $sType == 'IMAGE' )      $sType = 'images';
			if( $sType == 'STYLESHEET' ) $sType = 'stylesheets';

			$aPostData = array(
				'fileType'	=> $sType,
				'documentToken' => $this->aParams['document-token'],
				'wikiId'        => wfWikiID()
			);

			$aErrors = array();
			$aPostFiles = array();
			$iCounter = 0;
			$iCurrentUploadSize = 0;
			foreach( $aFiles as $sFileName => $sFilePath ) {
				if( file_exists( $sFilePath) == false ) {
					$aErrors[] = $sFilePath;
					continue;
				}

				$iFileSize = filesize( $sFilePath );
				if( $iCurrentUploadSize >= $bsgUEModulePDFUploadThreshold ) {
					$this->doFilesUpload( array_merge( $aPostFiles , $aPostData), $aErrors );

					//Reset all loop variables
					$aErrors = array();
					$aPostFiles = array();
					$iCounter = 0;
					$iCurrentUploadSize = $iFileSize;
				}

				$aPostFiles['file'.$iCounter.'_name'] = $sFileName;
				$aPostFiles['file'.$iCounter] = class_exists( 'CURLFile' ) ? new CURLFile( $sFilePath ) : '@'.$sFilePath;
				$iCounter++;
				$iCurrentUploadSize += $iFileSize;
			}
			$this->doFilesUpload( array_merge( $aPostFiles, $aPostData ), $aErrors ); //For last iteration contents
		}
	}

	/**
	 *
	 * @var array
	 */
	protected $aParams = array();

	/**
	 *
	 * @var array
	 */
	protected $aFiles  = array();

	/**
	 * The contructor method forthis class.
	 * @param array $aParams The params have to contain the key
	 * 'soap-service-url', with a valid URL to the webservice. They can
	 * contain a key 'soap-connection-options' for the SoapClient constructor
	 * and a key 'resources' with al list of files to upload.
	 * @throws UnexpectedValueException If 'soap-service-url' is not set or the Webservice is not available.
	 */
	public function __construct( &$aParams ) {

		$this->aParams = $aParams;
		$this->aFiles =  $aParams['resources'];

		if ( empty( $this->aParams['soap-service-url'] ) ) {
			throw new UnexpectedValueException( 'soap-service-url-not-set' );
		}

		if( !BsConnectionHelper::urlExists( $this->aParams['soap-service-url'] ) ) {
			throw new UnexpectedValueException( 'soap-service-url-not-valid' );
		}

		//If a slash is last char, remove it.
		if( substr($this->aParams['soap-service-url'], -1) == '/' ) {
			$this->aParams['soap-service-url'] = substr($this->aParams['soap-service-url'], 0, -1);
		}
	}

	/**
	 * Searches the DOM for <img>-Tags and <a> Tags with class 'internal',
	 * resolves the local filesystem path and adds it to $aFiles array.
	 * @param DOMDocument $oHtml The markup to be searched.
	 * @return boolean Well, always true.
	 */
	protected function findFiles( &$oHtml ) {

		//Find all images
		$oImageElements = $oHtml->getElementsByTagName( 'img' );
		foreach( $oImageElements as $oImageElement ) {
			$oFileResolver = new PDFFileResolver( $oImageElement, $this->aParams['webroot-filesystempath'] );

			$sFileName = $oFileResolver->getFileName();
			$sAbsoluteFileSystemPath = $oFileResolver->getAbsoluteFilesystemPath();
			Hooks::run( 'BSUEModulePDFFindFiles', array( $this, $oImageElement, &$sAbsoluteFileSystemPath, &$sFileName, 'images' ) );
			Hooks::run( 'BSUEModulePDFWebserviceFindFiles', array( $this, $oImageElement, &$sAbsoluteFileSystemPath, &$sFileName, 'images' ) );
			$this->aFiles['images'][$sFileName] =  $sAbsoluteFileSystemPath;
		}

		$oDOMXPath = new DOMXPath( $oHtml );

		/*
		 * This is now in template
		//Find all CSS files
		$oLinkElements = $oHtml->getElementsByTagName( 'link' ); // TODO RBV (02.02.11 16:48): Limit to rel="stylesheet" and type="text/css"
		foreach( $oLinkElements as $oLinkElement ) {
			$sHrefUrl = $oLinkElement->getAttribute( 'href' );
			$sHrefFilename           = basename( $sHrefUrl );
			$sAbsoluteFileSystemPath = $this->getFileSystemPath( $sHrefUrl );
			$this->aFiles[ $sAbsoluteFileSystemPath ] = array( $sHrefFilename, 'STYLESHEET' );
			$oLinkElement->setAttribute( 'href', 'stylesheets/'.$sHrefFilename );
		}
		 */

		Hooks::run( 'BSUEModulePDFAfterFindFiles', array( $this, $oHtml, &$this->aFiles, $this->aParams, $oDOMXPath ) );
		return true;
	}

	protected function doFilesUpload( $aPostData, $aErrors = array() ) {
		global $bsgUEModulePDFCURLOptions;
		$aOptions = array(
			'timeout' => 120,
		);
		$aOptions = array_merge_recursive( $aOptions, $bsgUEModulePDFCURLOptions );

		$sType = $aPostData['fileType'];

		if( !empty( $aErrors ) ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::uploadFiles: Error trying to fetch files:'."\n". var_export( $aErrors, true )
			);
		}

		Hooks::run( 'BSUEModulePDFUploadFilesBeforeSend', array( $this, &$aPostData, $sType ) );

		$aOptions['postData'] = $aPostData;

		$vHttpEngine = Http::$httpEngine;
		Http::$httpEngine = 'curl';
		$sResponse = Http::post(
			$this->aParams['soap-service-url'].'/UploadAsset',
			$aOptions
		);
		Http::$httpEngine = $vHttpEngine;

		if( $sResponse != false ) {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::uploadFiles: Successfully added "'.$sType.'"'
			);
			wfDebugLog(
				'BS::UEModulePDF',
				FormatJson::encode( FormatJson::decode($sResponse), true )
			);
		}
		else {
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFServlet::uploadFiles: Failed adding "'.$sType.'"'
			);
		}
	}

	//</editor-fold>
}
