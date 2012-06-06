<?php
	Class fieldPrice extends Field
	{
		public function __construct()
		{
			parent::__construct();
			$this->_name = __('Price');
			$this->_required = true;
			$this->set('required', 'yes');
		}
		
		public function allowDatasourceOutputGrouping()
		{
			return true;
		}
		
		public function allowDatasourceParamOutput()
		{
			return true;
		}
		
		public function groupRecords($records)
		{
			if(!is_array($records) || empty($records)) return;
			$groups = array($this->get('element_name') => array());
			foreach($records as $r){
				$data = $r->getData($this->get('id'));
				$value = General::sanitize($data['value']);
				$handle = Lang::createHandle($value);
				if(!isset($groups[$this->get('element_name')][$handle])){
					$groups[$this->get('element_name')][$handle] = array('attr' => array('handle' => $handle, 'value' => $value),
						'records' => array(), 'groups' => array());
				}
				$groups[$this->get('element_name')][$handle]['records'][] = $r;
			}
			return $groups;
		}
		
		public function displayPublishPanel(&$wrapper, $data=NULL, $flagWithError=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL)
		{
			$value = General::sanitize($data['value']);
			$label = Widget::Label($this->get('label'));
			if($this->get('required') != 'yes') $label->appendChild(new XMLElement('i', __('Optional')));
			$label->appendChild(Widget::Input('fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix, $value));
			$label->appendChild(new XMLElement('em', __('Enter currency in the following format: ####.## (for example: 49.95, 1900, 1899.50)')));
			if($flagWithError != NULL) $wrapper->appendChild(Widget::Error($label, $flagWithError));
			else $wrapper->appendChild($label);
		}
		
		public function isSortable()
		{
			return true;
		}
		
		public function canFilter()
		{
			return true;
		}
		
		public function canImport()
		{
			return true;
		}
		
		public function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC')
		{
			$joins .= "LEFT OUTER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
			$sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`value` $order");
		}
		
		public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false)
		{
			$field_id = $this->get('id');
			
			 if (preg_match('/^range:/i', $data[0])) {

					$field_id = $this->get('id');
					$joins .= " LEFT JOIN `tbl_entries_data_$field_id` AS `t$field_id` ON (`e`.`id` = `t$field_id`.entry_id) ";

					$values = explode('/', trim(substr($data[0], 6)));
					
					# min 
					$where .= (!empty($values[0]) && is_numeric($values[0])) ? 
						" AND `t$field_id`.`value` >= $values[0]" : null;
					# max
					$where .= (!empty($values[1]) && is_numeric($values[1])) ? 
						" AND `t$field_id`.`value` <= $values[1]" : null;

			} elseif (self::isFilterRegex($data[0])) {
				$this->_key++;
				$pattern = str_replace('regexp:', '', $this->cleanValue($data[0]));
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.value REGEXP '{$pattern}'
					)
				";
			} elseif ($andOperation) {
				foreach ($data as $value) {
					$this->_key++;
					$value = $this->cleanValue($value);
					$value = $value;
					$joins .= "
						LEFT JOIN
							`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
							ON (e.id = t{$field_id}_{$this->_key}.entry_id)
					";
					$where .= "
						AND (
							t{$field_id}_{$this->_key}.value = '{$value}'
						)
					";
				}
			} else {
				if (!is_array($data)) $data = array($data);
				
				foreach ($data as &$value) {
					$value = $this->cleanValue($value);
				}
				
				$this->_key++;
				$data = implode("', '", $data);
				$joins .= "
					LEFT JOIN
						`tbl_entries_data_{$field_id}` AS t{$field_id}_{$this->_key}
						ON (e.id = t{$field_id}_{$this->_key}.entry_id)
				";
				$where .= "
					AND (
						t{$field_id}_{$this->_key}.value IN ('{$data}')
					)
				";
			}
			return true;
		}
		
		public function checkPostFieldData($data, &$message, $entry_id=NULL)
		{
			$message = NULL;
			if($this->get('required') == 'yes' && strlen(trim($data)) == 0){
				$message = __("'%s' is a required field.", array($this->get('label')));
				return self::__MISSING_FIELDS__;
			}
			if(!General::validateString($data, '/^\d+(\.\d{2})?$/')){
				$message = __("'%s' contains invalid data. Please check the contents.", array($this->get('label')));
				return self::__INVALID_FIELDS__;
			}
			return self::__OK__;
		}
		
		public function processRawFieldData($data, &$status, &$message=null, $simulate = false, $entry_id = null)
		{
			$status = self::__OK__;
			if (strlen(trim($data)) == 0) return array();
			if(!is_float($data)) $data = number_format($data, 2, '.', '');
			
			return array('value' => $data);
		}
		
		public function canPrePopulate()
		{
			return true;
		}
		
		public function appendFormattedElement(&$wrapper, $data, $encode=false)
		{
			$wrapper->appendChild(
				new XMLElement($this->get('element_name'), $data['value'])
			);
		}
		
		public function commit()
		{
			if(!parent::commit()) return false;
			
			$id = $this->get('id');
			if($id === false) return false;
			
			$fields = array();
			$fields['field_id'] = $id;
			Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '$id' LIMIT 1");
			return Symphony::Database()->insert($fields, 'tbl_fields_' . $this->handle());
		}
		
		public function setFromPOST($postdata)
		{
			parent::setFromPOST($postdata);
		}
		
		public function displaySettingsPanel(&$wrapper, $errors = null)
		{
			parent::displaySettingsPanel($wrapper, $errors);
			$this->appendRequiredCheckbox($wrapper);
			$this->appendShowColumnCheckbox($wrapper);
		}
		
		public function prepareTableValue($data, XMLElement $link = null)
		{
			if (empty($data)) return;
			return $data['value'];
		}
		
		public function displayDatasourceFilterPanel(&$wrapper, $data=NULL, $errors=NULL, $fieldnamePrefix=NULL, $fieldnamePostfix=NULL){
			$wrapper->appendChild(new XMLElement('h4', $this->get('label') . ' <i>'.$this->Name().'</i>'));
			$label = Widget::Label('Value');
			$label->appendChild(Widget::Input('fields[filter]'.($fieldnamePrefix ? '['.$fieldnamePrefix.']' : '').'['.$this->get('id').']'.($fieldnamePostfix ? '['.$fieldnamePostfix.']' : ''), ($data ? General::sanitize($data) : NULL)));
			$wrapper->appendChild($label);

			$wrapper->appendChild(new XMLElement('p', 'To filter by ranges, add <code>range:</code> to the beginning of the filter input and use <code>{$min}/{$max}</code> syntax', array('class' => 'help')));

		}

		public function createTable()
		{
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_" . $this->get('id') . "` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
				  `value` varchar(255) default NULL,
				  PRIMARY KEY  (`id`),
				  KEY `entry_id` (`entry_id`),
				  KEY `value` (`value`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
			);
		}
	}
