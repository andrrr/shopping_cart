<?php 
    Final Class datasourceShopping_Cart Extends DataSource
	{

		public function about()
		{
			return array(
				'name' => 'Shopping Cart',
				'author' => array(
				'name' => 'Andrey Lubinov, Mario Butera',
				'website' => false,
				'email' => 'andrey.lubinov@gmail.com, webmaster@mblabs.net'),
				'version' => '1.2.1',
				'description' => '
					<p>Output example:</p> 
					<pre><code>' 
.htmlentities('<shopping-cart items="2" total="381.99" total-weight="150.20">
	<item id="8" num="2" sum="82" weight="20"/>
	<item id="9" num="1" sum="299.99" weight="130.20"/>
</shopping-cart>').
					'</code></pre>
					<p>Data Source is also outputs ids of items as <code>$ds-shopping-cart</code> Parameter Output for filtering in another Data Sources</p>',
				'release-date' => '2011-05-26'
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
			$total_weight = null;
			foreach($s as $key => $value){
				$item = new XMLElement('item', null, array(
					'id' => $key,
					'num' => $value['num'],
					'sum' => $value['sum'],
					'weight' => $value['weight'])
				);
				$xml->appendChild($item);
				$total = $total + $value['sum'];
				$total_weight = $total_weight + $value['weight'];
			}
			
			$xml->setAttributeArray(array(
				'items' => count($s),
				'total' => $total,
				'total-weight' => $total_weight)
			);
			return $xml;
		}
	}
