<?php

namespace BlueSpice\UEModulePDF\Hook\BSUniversalExportSpecialPageExecute;

use BlueSpice\UniversalExport\Hook\BSUniversalExportSpecialPageExecute;

class AddModulePDF extends BSUniversalExportSpecialPageExecute {

	protected function doProcess() {
		$this->modules['pdf'] = new \BsExportModulePDF();
		return true;
	}

}
