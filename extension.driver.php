<?php

	Class extension_shopping_cart extends Extension{
	
		public function about(){
			return array('name' => 'Shopping Cart',
						 'version' => '1.0',
						 'release-date' => '2009-16-12',
						 'author' => array('name' => 'Andrey Lubinov',
								   'website' => 'http://las.com.ua',
								   'email' => 'andrey.lubinov@gmail.com')
				 		);
		}
    
		public function getSubscribedDelegates(){
			return array(
						array(
							'page' => '/blueprints/events/new/',
							'delegate' => 'AppendEventFilter',
							'callback' => 'addFilterToEventEditor'
						),
						array(
							'page' => '/blueprints/events/edit/',
							'delegate' => 'AppendEventFilter',
							'callback' => 'addFilterToEventEditor'
						),
						array(
							'page' => '/frontend/',
							'delegate' => 'EventPostSaveFilter',
							'callback' => 'processEventData'
						),
						array(
							'page' => '/system/preferences/',
							'delegate' => 'AddCustomPreferenceFieldsets',
							'callback' => 'appendPreferences'
						),
			);
		}
		
		
		public function appendPreferences($context){
			require_once(TOOLKIT . '/class.sectionmanager.php');
			
			$price_field_id = $this->_Parent->Configuration->get('field_id', 'shopping_cart');

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', __('Shopping Cart')));
			$div = new XMLElement('div', NULL, array('class' => 'group'));
		
			$label = Widget::Label(__('Price Field'));	

			$sectionManager = new SectionManager($this->_Parent);
			$sections = $sectionManager->fetch(NULL, 'ASC', 'name');

			if(!is_array($sections) || empty($sections)){
				$div->appendChild(new XMLElement('p', __('None Found')));
				$fieldset->appendChild($div);
				return $context['wrapper']->appendChild($fieldset);
			}

			foreach($sections as $section) {
				$sid = $section->get('id');
				$optgroup[$sid]['label'] = $section->get('name');
				$fields = $section->fetchFields();
				foreach($fields as $f){
					$optgroup[$sid]['options'][] = array($f->get('id'), ($f->get('id') == $price_field_id), $f->get('label'));
				}

			}

			$label->appendChild(Widget::Select('settings[shopping_cart][field_id]', $optgroup, array('size' => '10')));
			$div->appendChild($label);
			$fieldset->appendChild($div);
			$context['wrapper']->appendChild($fieldset);
		}


		public function addFilterToEventEditor($context){ 
			$context['options'][] = array('cart-drop-all', @in_array('cart-drop-all', $context['selected']) , __('Cart: Drop All Items'));   
		}
		public function processEventData($context){
			if(!in_array('cart-drop-all', $context['event']->eParamFILTERS)) return;
			$_SESSION[__SYM_COOKIE_PREFIX_ . 'cart'] = null;
		}


		public function install(){
			$this->_Parent->Configuration->set('field_id', null,'shopping_cart');
			return $this->_Parent->saveConfig();
		}
		
		public function uninstall(){
			$this->_Parent->Configuration->remove('shopping_cart');
			return $this->_Parent->saveConfig();
		}
    
	}

