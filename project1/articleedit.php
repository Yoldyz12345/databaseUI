<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg12.php" ?>
<?php include_once ((EW_USE_ADODB) ? "adodb5/adodb.inc.php" : "ewmysql12.php") ?>
<?php include_once "phpfn12.php" ?>
<?php include_once "articleinfo.php" ?>
<?php include_once "userfn12.php" ?>
<?php

//
// Page class
//

$article_edit = NULL; // Initialize page object first

class carticle_edit extends carticle {

	// Page ID
	var $PageID = 'edit';

	// Project ID
	var $ProjectID = "{AD4C364C-5CB3-430F-AF23-10EA2887A04D}";

	// Table name
	var $TableName = 'article';

	// Page object name
	var $PageObjName = 'article_edit';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Methods to clear message
	function ClearMessage() {
		$_SESSION[EW_SESSION_MESSAGE] = "";
	}

	function ClearFailureMessage() {
		$_SESSION[EW_SESSION_FAILURE_MESSAGE] = "";
	}

	function ClearSuccessMessage() {
		$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = "";
	}

	function ClearWarningMessage() {
		$_SESSION[EW_SESSION_WARNING_MESSAGE] = "";
	}

	function ClearMessages() {
		$_SESSION[EW_SESSION_MESSAGE] = "";
		$_SESSION[EW_SESSION_FAILURE_MESSAGE] = "";
		$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = "";
		$_SESSION[EW_SESSION_WARNING_MESSAGE] = "";
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-info ewInfo\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-danger ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p>" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Footer exists, display
			echo "<p>" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}
	var $Token = "";
	var $TokenTimeout = 0;
	var $CheckToken = EW_CHECK_TOKEN;
	var $CheckTokenFn = "ew_CheckToken";
	var $CreateTokenFn = "ew_CreateToken";

	// Valid Post
	function ValidPost() {
		if (!$this->CheckToken || !ew_IsHttpPost())
			return TRUE;
		if (!isset($_POST[EW_TOKEN_NAME]))
			return FALSE;
		$fn = $this->CheckTokenFn;
		if (is_callable($fn))
			return $fn($_POST[EW_TOKEN_NAME], $this->TokenTimeout);
		return FALSE;
	}

	// Create Token
	function CreateToken() {
		global $gsToken;
		if ($this->CheckToken) {
			$fn = $this->CreateTokenFn;
			if ($this->Token == "" && is_callable($fn)) // Create token
				$this->Token = $fn();
			$gsToken = $this->Token; // Save to global variable
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;
		$this->TokenTimeout = ew_SessionTimeoutTime();

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (article)
		if (!isset($GLOBALS["article"]) || get_class($GLOBALS["article"]) == "carticle") {
			$GLOBALS["article"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["article"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'edit', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'article', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect($this->DBID);
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsCustomExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Create form object
		$objForm = new cFormObj();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up current action

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();

		// Check token
		if (!$this->ValidPost()) {
			echo $Language->Phrase("InvalidPostRequest");
			$this->Page_Terminate();
			exit();
		}

		// Process auto fill
		if (@$_POST["ajax"] == "autofill") {
			$results = $this->GetAutoFill(@$_POST["name"], @$_POST["q"]);
			if ($results) {

				// Clean output buffer
				if (!EW_DEBUG_ENABLED && ob_get_length())
					ob_end_clean();
				echo $results;
				$this->Page_Terminate();
				exit();
			}
		}

		// Create Token
		$this->CreateToken();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $gsExportFile, $gTmpImages;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();

		// Export
		global $EW_EXPORT, $article;
		if ($this->CustomExport <> "" && $this->CustomExport == $this->Export && array_key_exists($this->CustomExport, $EW_EXPORT)) {
				$sContent = ob_get_contents();
			if ($gsExportFile == "") $gsExportFile = $this->TableVar;
			$class = $EW_EXPORT[$this->CustomExport];
			if (class_exists($class)) {
				$doc = new $class($article);
				$doc->Text = $sContent;
				if ($this->Export == "email")
					echo $this->ExportEmail($doc->Text);
				else
					$doc->Export();
				ew_DeleteTmpImages(); // Delete temp images
				exit();
			}
		}
		$this->Page_Redirecting($url);

		 // Close connection
		ew_CloseConn();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $FormClassName = "form-horizontal ewForm ewEditForm";
	var $DbMasterFilter;
	var $DbDetailFilter;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Load key from QueryString
		if (@$_GET["publication_id"] <> "") {
			$this->publication_id->setQueryStringValue($_GET["publication_id"]);
		}

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Process form if post back
		if (@$_POST["a_edit"] <> "") {
			$this->CurrentAction = $_POST["a_edit"]; // Get action code
			$this->LoadFormValues(); // Get form values
		} else {
			$this->CurrentAction = "I"; // Default action is display
		}

		// Check if valid key
		if ($this->publication_id->CurrentValue == "")
			$this->Page_Terminate("articlelist.php"); // Invalid key, return to list

		// Validate form if post back
		if (@$_POST["a_edit"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = ""; // Form error, reset action
				$this->setFailureMessage($gsFormError);
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues();
			}
		}
		switch ($this->CurrentAction) {
			case "I": // Get a record to display
				if (!$this->LoadRow()) { // Load record based on key
					if ($this->getFailureMessage() == "") $this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
					$this->Page_Terminate("articlelist.php"); // No matching record, return to list
				}
				break;
			Case "U": // Update
				$sReturnUrl = $this->getReturnUrl();
				if (ew_GetPageName($sReturnUrl) == "articlelist.php")
					$sReturnUrl = $this->AddMasterUrl($this->GetListUrl()); // List page, return to list page with correct master key if necessary
				$this->SendEmail = TRUE; // Send email on update success
				if ($this->EditRow()) { // Update record based on key
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("UpdateSuccess")); // Update success
					$this->Page_Terminate($sReturnUrl); // Return to caller
				} elseif ($this->getFailureMessage() == $Language->Phrase("NoRecord")) {
					$this->Page_Terminate($sReturnUrl); // Return to caller
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Restore form values if update failed
				}
		}

		// Render the record
		$this->RowType = EW_ROWTYPE_EDIT; // Render as Edit
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Set up starting record parameters
	function SetUpStartRec() {
		if ($this->DisplayRecs == 0)
			return;
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET[EW_TABLE_START_REC] <> "") { // Check for "start" parameter
				$this->StartRec = $_GET[EW_TABLE_START_REC];
				$this->setStartRecordNumber($this->StartRec);
			} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
				$PageNo = $_GET[EW_TABLE_PAGE_NO];
				if (is_numeric($PageNo)) {
					$this->StartRec = ($PageNo-1)*$this->DisplayRecs+1;
					if ($this->StartRec <= 0) {
						$this->StartRec = 1;
					} elseif ($this->StartRec >= intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1) {
						$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1;
					}
					$this->setStartRecordNumber($this->StartRec);
				}
			}
		}
		$this->StartRec = $this->getStartRecordNumber();

		// Check if correct start record counter
		if (!is_numeric($this->StartRec) || $this->StartRec == "") { // Avoid invalid start record counter
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} elseif (intval($this->StartRec) > intval($this->TotalRecs)) { // Avoid starting record > total records
			$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to last page first record
			$this->setStartRecordNumber($this->StartRec);
		} elseif (($this->StartRec-1) % $this->DisplayRecs <> 0) {
			$this->StartRec = intval(($this->StartRec-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to page boundary
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Get upload files
	function GetUploadFiles() {
		global $objForm, $Language;

		// Get upload data
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->publication_id->FldIsDetailKey) {
			$this->publication_id->setFormValue($objForm->GetValue("x_publication_id"));
		}
		if (!$this->title->FldIsDetailKey) {
			$this->title->setFormValue($objForm->GetValue("x_title"));
		}
		if (!$this->pages->FldIsDetailKey) {
			$this->pages->setFormValue($objForm->GetValue("x_pages"));
		}
		if (!$this->volume->FldIsDetailKey) {
			$this->volume->setFormValue($objForm->GetValue("x_volume"));
		}
		if (!$this->year->FldIsDetailKey) {
			$this->year->setFormValue($objForm->GetValue("x_year"));
			$this->year->CurrentValue = ew_UnFormatDateTime($this->year->CurrentValue, 5);
		}
		if (!$this->journal_id->FldIsDetailKey) {
			$this->journal_id->setFormValue($objForm->GetValue("x_journal_id"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadRow();
		$this->publication_id->CurrentValue = $this->publication_id->FormValue;
		$this->title->CurrentValue = $this->title->FormValue;
		$this->pages->CurrentValue = $this->pages->FormValue;
		$this->volume->CurrentValue = $this->volume->FormValue;
		$this->year->CurrentValue = $this->year->FormValue;
		$this->year->CurrentValue = ew_UnFormatDateTime($this->year->CurrentValue, 5);
		$this->journal_id->CurrentValue = $this->journal_id->FormValue;
	}

	// Load row based on key values
	function LoadRow() {
		global $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$conn = &$this->Connection();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql, $conn);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->publication_id->setDbValue($rs->fields('publication_id'));
		$this->title->setDbValue($rs->fields('title'));
		$this->pages->setDbValue($rs->fields('pages'));
		$this->volume->setDbValue($rs->fields('volume'));
		$this->year->setDbValue($rs->fields('year'));
		$this->journal_id->setDbValue($rs->fields('journal_id'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->publication_id->DbValue = $row['publication_id'];
		$this->title->DbValue = $row['title'];
		$this->pages->DbValue = $row['pages'];
		$this->volume->DbValue = $row['volume'];
		$this->year->DbValue = $row['year'];
		$this->journal_id->DbValue = $row['journal_id'];
	}

	// Render row values based on field settings
	function RenderRow() {
		global $Security, $Language, $gsLanguage;

		// Initialize URLs
		// Call Row_Rendering event

		$this->Row_Rendering();

		// Common render codes for all row types
		// publication_id
		// title
		// pages
		// volume
		// year
		// journal_id

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

		// publication_id
		$this->publication_id->ViewValue = $this->publication_id->CurrentValue;
		$this->publication_id->ViewCustomAttributes = "";

		// title
		$this->title->ViewValue = $this->title->CurrentValue;
		$this->title->ViewCustomAttributes = "";

		// pages
		$this->pages->ViewValue = $this->pages->CurrentValue;
		$this->pages->ViewCustomAttributes = "";

		// volume
		$this->volume->ViewValue = $this->volume->CurrentValue;
		$this->volume->ViewCustomAttributes = "";

		// year
		$this->year->ViewValue = $this->year->CurrentValue;
		$this->year->ViewValue = ew_FormatDateTime($this->year->ViewValue, 5);
		$this->year->ViewCustomAttributes = "";

		// journal_id
		$this->journal_id->ViewValue = $this->journal_id->CurrentValue;
		$this->journal_id->ViewCustomAttributes = "";

			// publication_id
			$this->publication_id->LinkCustomAttributes = "";
			$this->publication_id->HrefValue = "";
			$this->publication_id->TooltipValue = "";

			// title
			$this->title->LinkCustomAttributes = "";
			$this->title->HrefValue = "";
			$this->title->TooltipValue = "";

			// pages
			$this->pages->LinkCustomAttributes = "";
			$this->pages->HrefValue = "";
			$this->pages->TooltipValue = "";

			// volume
			$this->volume->LinkCustomAttributes = "";
			$this->volume->HrefValue = "";
			$this->volume->TooltipValue = "";

			// year
			$this->year->LinkCustomAttributes = "";
			$this->year->HrefValue = "";
			$this->year->TooltipValue = "";

			// journal_id
			$this->journal_id->LinkCustomAttributes = "";
			$this->journal_id->HrefValue = "";
			$this->journal_id->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_EDIT) { // Edit row

			// publication_id
			$this->publication_id->EditAttrs["class"] = "form-control";
			$this->publication_id->EditCustomAttributes = "";
			$this->publication_id->EditValue = $this->publication_id->CurrentValue;
			$this->publication_id->ViewCustomAttributes = "";

			// title
			$this->title->EditAttrs["class"] = "form-control";
			$this->title->EditCustomAttributes = "";
			$this->title->EditValue = ew_HtmlEncode($this->title->CurrentValue);
			$this->title->PlaceHolder = ew_RemoveHtml($this->title->FldCaption());

			// pages
			$this->pages->EditAttrs["class"] = "form-control";
			$this->pages->EditCustomAttributes = "";
			$this->pages->EditValue = ew_HtmlEncode($this->pages->CurrentValue);
			$this->pages->PlaceHolder = ew_RemoveHtml($this->pages->FldCaption());

			// volume
			$this->volume->EditAttrs["class"] = "form-control";
			$this->volume->EditCustomAttributes = "";
			$this->volume->EditValue = ew_HtmlEncode($this->volume->CurrentValue);
			$this->volume->PlaceHolder = ew_RemoveHtml($this->volume->FldCaption());

			// year
			$this->year->EditAttrs["class"] = "form-control";
			$this->year->EditCustomAttributes = "";
			$this->year->EditValue = ew_HtmlEncode(ew_FormatDateTime($this->year->CurrentValue, 5));
			$this->year->PlaceHolder = ew_RemoveHtml($this->year->FldCaption());

			// journal_id
			$this->journal_id->EditAttrs["class"] = "form-control";
			$this->journal_id->EditCustomAttributes = "";
			$this->journal_id->EditValue = ew_HtmlEncode($this->journal_id->CurrentValue);
			$this->journal_id->PlaceHolder = ew_RemoveHtml($this->journal_id->FldCaption());

			// Edit refer script
			// publication_id

			$this->publication_id->LinkCustomAttributes = "";
			$this->publication_id->HrefValue = "";

			// title
			$this->title->LinkCustomAttributes = "";
			$this->title->HrefValue = "";

			// pages
			$this->pages->LinkCustomAttributes = "";
			$this->pages->HrefValue = "";

			// volume
			$this->volume->LinkCustomAttributes = "";
			$this->volume->HrefValue = "";

			// year
			$this->year->LinkCustomAttributes = "";
			$this->year->HrefValue = "";

			// journal_id
			$this->journal_id->LinkCustomAttributes = "";
			$this->journal_id->HrefValue = "";
		}
		if ($this->RowType == EW_ROWTYPE_ADD ||
			$this->RowType == EW_ROWTYPE_EDIT ||
			$this->RowType == EW_ROWTYPE_SEARCH) { // Add / Edit / Search row
			$this->SetupFieldTitles();
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Validate form
	function ValidateForm() {
		global $Language, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return ($gsFormError == "");
		if (!$this->publication_id->FldIsDetailKey && !is_null($this->publication_id->FormValue) && $this->publication_id->FormValue == "") {
			ew_AddMessage($gsFormError, str_replace("%s", $this->publication_id->FldCaption(), $this->publication_id->ReqErrMsg));
		}
		if (!ew_CheckInteger($this->publication_id->FormValue)) {
			ew_AddMessage($gsFormError, $this->publication_id->FldErrMsg());
		}
		if (!$this->title->FldIsDetailKey && !is_null($this->title->FormValue) && $this->title->FormValue == "") {
			ew_AddMessage($gsFormError, str_replace("%s", $this->title->FldCaption(), $this->title->ReqErrMsg));
		}
		if (!ew_CheckInteger($this->pages->FormValue)) {
			ew_AddMessage($gsFormError, $this->pages->FldErrMsg());
		}
		if (!ew_CheckInteger($this->volume->FormValue)) {
			ew_AddMessage($gsFormError, $this->volume->FldErrMsg());
		}
		if (!$this->year->FldIsDetailKey && !is_null($this->year->FormValue) && $this->year->FormValue == "") {
			ew_AddMessage($gsFormError, str_replace("%s", $this->year->FldCaption(), $this->year->ReqErrMsg));
		}
		if (!ew_CheckDate($this->year->FormValue)) {
			ew_AddMessage($gsFormError, $this->year->FldErrMsg());
		}
		if (!ew_CheckInteger($this->journal_id->FormValue)) {
			ew_AddMessage($gsFormError, $this->journal_id->FldErrMsg());
		}

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsFormError, $sFormCustomError);
		}
		return $ValidateForm;
	}

	// Update record based on key values
	function EditRow() {
		global $Security, $Language;
		$sFilter = $this->KeyFilter();
		$sFilter = $this->ApplyUserIDFilters($sFilter);
		$conn = &$this->Connection();
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = $GLOBALS["EW_ERROR_FN"];
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE)
			return FALSE;
		if ($rs->EOF) {
			$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
			$EditRow = FALSE; // Update Failed
		} else {

			// Save old values
			$rsold = &$rs->fields;
			$this->LoadDbValues($rsold);
			$rsnew = array();

			// publication_id
			// title

			$this->title->SetDbValueDef($rsnew, $this->title->CurrentValue, "", $this->title->ReadOnly);

			// pages
			$this->pages->SetDbValueDef($rsnew, $this->pages->CurrentValue, NULL, $this->pages->ReadOnly);

			// volume
			$this->volume->SetDbValueDef($rsnew, $this->volume->CurrentValue, NULL, $this->volume->ReadOnly);

			// year
			$this->year->SetDbValueDef($rsnew, ew_UnFormatDateTime($this->year->CurrentValue, 5), ew_CurrentDate(), $this->year->ReadOnly);

			// journal_id
			$this->journal_id->SetDbValueDef($rsnew, $this->journal_id->CurrentValue, NULL, $this->journal_id->ReadOnly);

			// Call Row Updating event
			$bUpdateRow = $this->Row_Updating($rsold, $rsnew);
			if ($bUpdateRow) {
				$conn->raiseErrorFn = $GLOBALS["EW_ERROR_FN"];
				if (count($rsnew) > 0)
					$EditRow = $this->Update($rsnew, "", $rsold);
				else
					$EditRow = TRUE; // No field to update
				$conn->raiseErrorFn = '';
				if ($EditRow) {
				}
			} else {
				if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

					// Use the message, do nothing
				} elseif ($this->CancelMessage <> "") {
					$this->setFailureMessage($this->CancelMessage);
					$this->CancelMessage = "";
				} else {
					$this->setFailureMessage($Language->Phrase("UpdateCancelled"));
				}
				$EditRow = FALSE;
			}
		}

		// Call Row_Updated event
		if ($EditRow)
			$this->Row_Updated($rsold, $rsnew);
		$rs->Close();
		return $EditRow;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$url = substr(ew_CurrentUrl(), strrpos(ew_CurrentUrl(), "/")+1);
		$Breadcrumb->Add("list", $this->TableVar, $this->AddMasterUrl("articlelist.php"), "", $this->TableVar, TRUE);
		$PageId = "edit";
		$Breadcrumb->Add("edit", $PageId, $url);
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($article_edit)) $article_edit = new carticle_edit();

// Page init
$article_edit->Page_Init();

// Page main
$article_edit->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$article_edit->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Form object
var CurrentPageID = EW_PAGE_ID = "edit";
var CurrentForm = farticleedit = new ew_Form("farticleedit", "edit");

// Validate form
farticleedit.Validate = function() {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	var $ = jQuery, fobj = this.GetForm(), $fobj = $(fobj);
	if ($fobj.find("#a_confirm").val() == "F")
		return true;
	var elm, felm, uelm, addcnt = 0;
	var $k = $fobj.find("#" + this.FormKeyCountName); // Get key_count
	var rowcnt = ($k[0]) ? parseInt($k.val(), 10) : 1;
	var startcnt = (rowcnt == 0) ? 0 : 1; // Check rowcnt == 0 => Inline-Add
	var gridinsert = $fobj.find("#a_list").val() == "gridinsert";
	for (var i = startcnt; i <= rowcnt; i++) {
		var infix = ($k[0]) ? String(i) : "";
		$fobj.data("rowindex", infix);
			elm = this.GetElements("x" + infix + "_publication_id");
			if (elm && !ew_IsHidden(elm) && !ew_HasValue(elm))
				return this.OnError(elm, "<?php echo ew_JsEncode2(str_replace("%s", $article->publication_id->FldCaption(), $article->publication_id->ReqErrMsg)) ?>");
			elm = this.GetElements("x" + infix + "_publication_id");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($article->publication_id->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_title");
			if (elm && !ew_IsHidden(elm) && !ew_HasValue(elm))
				return this.OnError(elm, "<?php echo ew_JsEncode2(str_replace("%s", $article->title->FldCaption(), $article->title->ReqErrMsg)) ?>");
			elm = this.GetElements("x" + infix + "_pages");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($article->pages->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_volume");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($article->volume->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_year");
			if (elm && !ew_IsHidden(elm) && !ew_HasValue(elm))
				return this.OnError(elm, "<?php echo ew_JsEncode2(str_replace("%s", $article->year->FldCaption(), $article->year->ReqErrMsg)) ?>");
			elm = this.GetElements("x" + infix + "_year");
			if (elm && !ew_CheckDate(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($article->year->FldErrMsg()) ?>");
			elm = this.GetElements("x" + infix + "_journal_id");
			if (elm && !ew_CheckInteger(elm.value))
				return this.OnError(elm, "<?php echo ew_JsEncode2($article->journal_id->FldErrMsg()) ?>");

			// Fire Form_CustomValidate event
			if (!this.Form_CustomValidate(fobj))
				return false;
	}

	// Process detail forms
	var dfs = $fobj.find("input[name='detailpage']").get();
	for (var i = 0; i < dfs.length; i++) {
		var df = dfs[i], val = df.value;
		if (val && ewForms[val])
			if (!ewForms[val].Validate())
				return false;
	}
	return true;
}

// Form_CustomValidate event
farticleedit.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
farticleedit.ValidateRequired = true;
<?php } else { ?>
farticleedit.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<div class="ewToolbar">
<?php $Breadcrumb->Render(); ?>
<?php echo $Language->SelectionForm(); ?>
<div class="clearfix"></div>
</div>
<?php $article_edit->ShowPageHeader(); ?>
<?php
$article_edit->ShowMessage();
?>
<form name="farticleedit" id="farticleedit" class="<?php echo $article_edit->FormClassName ?>" action="<?php echo ew_CurrentPage() ?>" method="post">
<?php if ($article_edit->CheckToken) { ?>
<input type="hidden" name="<?php echo EW_TOKEN_NAME ?>" value="<?php echo $article_edit->Token ?>">
<?php } ?>
<input type="hidden" name="t" value="article">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<div>
<?php if ($article->publication_id->Visible) { // publication_id ?>
	<div id="r_publication_id" class="form-group">
		<label id="elh_article_publication_id" for="x_publication_id" class="col-sm-2 control-label ewLabel"><?php echo $article->publication_id->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></label>
		<div class="col-sm-10"><div<?php echo $article->publication_id->CellAttributes() ?>>
<span id="el_article_publication_id">
<span<?php echo $article->publication_id->ViewAttributes() ?>>
<p class="form-control-static"><?php echo $article->publication_id->EditValue ?></p></span>
</span>
<input type="hidden" data-table="article" data-field="x_publication_id" name="x_publication_id" id="x_publication_id" value="<?php echo ew_HtmlEncode($article->publication_id->CurrentValue) ?>">
<?php echo $article->publication_id->CustomMsg ?></div></div>
	</div>
<?php } ?>
<?php if ($article->title->Visible) { // title ?>
	<div id="r_title" class="form-group">
		<label id="elh_article_title" for="x_title" class="col-sm-2 control-label ewLabel"><?php echo $article->title->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></label>
		<div class="col-sm-10"><div<?php echo $article->title->CellAttributes() ?>>
<span id="el_article_title">
<textarea data-table="article" data-field="x_title" name="x_title" id="x_title" cols="35" rows="4" placeholder="<?php echo ew_HtmlEncode($article->title->getPlaceHolder()) ?>"<?php echo $article->title->EditAttributes() ?>><?php echo $article->title->EditValue ?></textarea>
</span>
<?php echo $article->title->CustomMsg ?></div></div>
	</div>
<?php } ?>
<?php if ($article->pages->Visible) { // pages ?>
	<div id="r_pages" class="form-group">
		<label id="elh_article_pages" for="x_pages" class="col-sm-2 control-label ewLabel"><?php echo $article->pages->FldCaption() ?></label>
		<div class="col-sm-10"><div<?php echo $article->pages->CellAttributes() ?>>
<span id="el_article_pages">
<input type="text" data-table="article" data-field="x_pages" name="x_pages" id="x_pages" size="30" placeholder="<?php echo ew_HtmlEncode($article->pages->getPlaceHolder()) ?>" value="<?php echo $article->pages->EditValue ?>"<?php echo $article->pages->EditAttributes() ?>>
</span>
<?php echo $article->pages->CustomMsg ?></div></div>
	</div>
<?php } ?>
<?php if ($article->volume->Visible) { // volume ?>
	<div id="r_volume" class="form-group">
		<label id="elh_article_volume" for="x_volume" class="col-sm-2 control-label ewLabel"><?php echo $article->volume->FldCaption() ?></label>
		<div class="col-sm-10"><div<?php echo $article->volume->CellAttributes() ?>>
<span id="el_article_volume">
<input type="text" data-table="article" data-field="x_volume" name="x_volume" id="x_volume" size="30" placeholder="<?php echo ew_HtmlEncode($article->volume->getPlaceHolder()) ?>" value="<?php echo $article->volume->EditValue ?>"<?php echo $article->volume->EditAttributes() ?>>
</span>
<?php echo $article->volume->CustomMsg ?></div></div>
	</div>
<?php } ?>
<?php if ($article->year->Visible) { // year ?>
	<div id="r_year" class="form-group">
		<label id="elh_article_year" for="x_year" class="col-sm-2 control-label ewLabel"><?php echo $article->year->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></label>
		<div class="col-sm-10"><div<?php echo $article->year->CellAttributes() ?>>
<span id="el_article_year">
<input type="text" data-table="article" data-field="x_year" data-format="5" name="x_year" id="x_year" placeholder="<?php echo ew_HtmlEncode($article->year->getPlaceHolder()) ?>" value="<?php echo $article->year->EditValue ?>"<?php echo $article->year->EditAttributes() ?>>
</span>
<?php echo $article->year->CustomMsg ?></div></div>
	</div>
<?php } ?>
<?php if ($article->journal_id->Visible) { // journal_id ?>
	<div id="r_journal_id" class="form-group">
		<label id="elh_article_journal_id" for="x_journal_id" class="col-sm-2 control-label ewLabel"><?php echo $article->journal_id->FldCaption() ?></label>
		<div class="col-sm-10"><div<?php echo $article->journal_id->CellAttributes() ?>>
<span id="el_article_journal_id">
<input type="text" data-table="article" data-field="x_journal_id" name="x_journal_id" id="x_journal_id" size="30" placeholder="<?php echo ew_HtmlEncode($article->journal_id->getPlaceHolder()) ?>" value="<?php echo $article->journal_id->EditValue ?>"<?php echo $article->journal_id->EditAttributes() ?>>
</span>
<?php echo $article->journal_id->CustomMsg ?></div></div>
	</div>
<?php } ?>
</div>
<div class="form-group">
	<div class="col-sm-offset-2 col-sm-10">
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("SaveBtn") ?></button>
<button class="btn btn-default ewButton" name="btnCancel" id="btnCancel" type="button" data-href="<?php echo $article_edit->getReturnUrl() ?>"><?php echo $Language->Phrase("CancelBtn") ?></button>
	</div>
</div>
</form>
<script type="text/javascript">
farticleedit.Init();
</script>
<?php
$article_edit->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$article_edit->Page_Terminate();
?>
