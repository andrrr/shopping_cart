<?php 
    Final Class datasourceShopping_Cart Extends DataSource
	{

		public function about()
		{
			return array(
				'name' => 'Shopping Cart',
				'author' => array(
				'name' => 'Andrey Lubinov',
				'website' => '',
				'email' => 'andrey.lubinov@gmail.com'),
				'version' => '1.2',
				'description' => '
					<p>Output example:</p> 
					<pre><code>' 
.htmlentities('<shopping-cart items="2" total="381.99">
	<item id="8" num="2" sum="82" />
	<item id="9" num="1" sum="299.99" />
</shopping-cart>').
					'</code></pre>
					<p>Data Source is also outputs ids of items as <code>$ds-shopping-cart</code> Parameter Output for filtering in another Data Sources</p>',
				'release-date' => '2010-10-17'
			); 
		}
	
		public function grab(&$param_pool)
		{
			$s = $_SESSION[__SYM_COOKIE_PREFIX_ . 'cart'];
			$xml = new XMLElement('shopping-cart');
			if(!is_array($s) || empty($s)) {
				$xml->appendChild(new XMLElement('empty'));
				return $xml;
			}
			$param_pool['ds-shopping-cart'] = implode(',', array_keys($s));
			$total = null;
			
			foreach($s as $key => $value){
				$item = new XMLElement('item', NULL, array(
					'id' => $key,
					'num' => $value['num'],
					'sum' => $value['sum'])
				);
				$xml->appendChild($item);
				$total = $total + $value['sum'];
			}
			
			$xml->setAttributeArray(array(
				'items' => count($s),
				'total' => $total)
			);
			return $xml;
		}
	}