<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');

	Final Class eventShopping_Cart extends Event
	{
		private $_s;
		private $_error = false;
		private $_msg = null;		
		private $_id;
		private $_num;
		private $_price;
		
		public static function about()
		{
			return array(
				'name' => 'Shopping Cart',
				'author' => array('name' => 'Andrey Lubinov',
				'website' => '',
				'email' => 'andrey.lubinov@gmail.com'),
				'version' => '1.2',
				'release-date' => '2010-10-17',
			);
		}

    public static function documentation()
	{
      return '<h3>Event XML example</h3>
<pre><code>'. htmlentities('<shopping-cart action="add|drop|recalc|dropall" result="success|error">
	<msg>Text message</msg>
</shopping-cart>').'
</code></pre>

<h3>Example Front-end Form Markup and GET queries</h3>
<h4>Add one item to a cart:</h4>
<pre><code>'. htmlentities('<form method="post" action="">
	<input type="hidden" name="id" value="42"/>
	<input type="submit" name="cart-action[add]" value="Add item"/>
</form>').'
</code></pre>
<p>GET analogue:</p>
<pre>?cart-action=add&amp;id=42</pre>

<h4>Add five items to a cart:</h4>
<pre><code>'. htmlentities('<form method="post" action="">
	<input type="hidden" name="id" value="42"/>
	<input type="text" name="num" value="5"/>
	<input type="submit" name="cart-action[add]" value="Add item"/>
</form>').'
</code></pre>
<p>GET analogue:</p>
<pre>?cart-action=add&amp;id=42&amp;num=5</pre>

<h4>Drop item from a cart:</h4>
<pre><code>'. htmlentities('<form method="post" action="">
	<input type="hidden" name="id" value="42"/>
	<input type="submit" name="cart-action[drop]" value="Drop item"/>
</form>').'
</code></pre>
<p>GET analogue:</p>
<pre>?cart-action=drop&amp;id=42</pre>

<h4>Update and recalculate item:</h4>
<pre><code>'. htmlentities('<form method="post" action="">
	<input type="hidden" name="id" value="42"/>
	<input type="hidden" name="num" value="5"/>
	<input type="submit" name="cart-action[recalc]" value="Recalculate item"/>
</form>').'
</code></pre>
<p>GET analogue:</p>
<pre>?cart-action=recalc&amp;id=42&amp;num=5</pre>

<h4>Drop all items from a cart:</h4>
<pre><code>'. htmlentities('<form method="post" action="">
	<input type="submit" name="cart-action[dropall]" value="Drop all items"/>
</form>').'
</code></pre>
<p>GET analogue:</p>
<pre>?cart-action=dropall</pre>
';


    }

		public function load()
		{
			if(isset($_REQUEST['cart-action']) && !empty($_REQUEST['cart-action'])){
				return $this->__trigger();
			}
		}
		
		protected function __trigger()
		{
			$xml = new XMLelement('shopping-cart');
			
			if($_GET['cart-action']){
				$action = $_GET['cart-action'];
			} else {
				list($action) = array_keys($_POST['cart-action']);
			}
			
			if(!method_exists($this, $action)) {
				$this->_error = true;
				$this->_msg = __('Unaccepted action');
			}
			
			if(!$this->_error) {
				$this->_s = &$_SESSION[__SYM_COOKIE_PREFIX_ . 'cart'];
				$this->$action();
			}
			
			$xml->setAttributeArray(array('action' => General::sanitize($action), 'result' => $this->_error == true ? 'error' : 'success'));
			$xml->appendChild(new XMLElement('msg', $this->_msg));
			return $xml;
		}
		
		protected function add()
		{
			if(!$this->dataIsValid()) return false;
			$this->_s[$this->_id] = array(
				/*'price' => $this->_price,*/
				'num' => $this->_s[$this->_id]['num'] + $this->_num, 
				'sum' => $this->_s[$this->_id]['sum'] + $this->_price * $this->_num
			);
			return $this->_msg = __('Item added to cart');
		}
		
	    protected function recalc()
		{
			if(!$this->dataIsValid()) return false;
			$this->_s[$this->_id] = array(
				'num' => $this->_num, 
				'sum' => $this->_price * $this->_num
			);
			return $this->_msg = __('Cart is recalculated');
			
		}
		
		protected function drop()
		{
			if(!$this->dataIsValid(true)) return false;
			$filtered = array();
			foreach($this->_s as $k => $v){
				if($k == $this->_id) continue;
				$filtered[$k] = $v;
			}
			$this->_s = $filtered;
			return $this->_msg = __('Item is dropped');
		}
		
		protected function dropAll()
		{
			$this->_s = null;
			return $this->_msg = __('All items are dropped');
		}
		
		protected function dataIsValid($idOnly = false)
		{
			if(empty($_REQUEST['id']) || !ctype_digit($_REQUEST['id'])){
				$this->_error = true;
				$this->_msg = __('ID is not set or is invalid');
				return false;
			}
			$this->_id = $_REQUEST['id'];
			if($idOnly) return true;
			
			// Check which field of this item is of the type 'price':
			$sql = 'SELECT A.`id` FROM `tbl_fields` A, `tbl_entries` B WHERE
				A.`parent_section` = B.`section_id` AND
				A.`type` = \'price\' AND 
				B.`id` = '.$this->_id.';';
			$fieldID = Symphony::Database()->fetchVar('id', 0, $sql);
			
			if(!$this->_price = Symphony::Database()->fetchVar("value", 0, "
					SELECT `value` AS `value` 
					FROM `tbl_entries_data_{$fieldID}` 
					WHERE `entry_id` = {$this->_id} 
					LIMIT 1
				")){
				$this->_error = true;
				$this->_msg = __('Can\'t find price value for this item');
				return false;
			}
			
			if(!empty($_REQUEST['num']) && !ctype_digit($_REQUEST['num'])){
				$this->_error = true;
				$this->_msg = __('Number of items is invalid');
				return false;
			}
			
			$this->_num = empty($_REQUEST['num']) ? 1 : $_REQUEST['num'];
			return true;
		}
	}