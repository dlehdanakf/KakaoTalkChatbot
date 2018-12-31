<?php
	class CafeteriaMeal {
		public $corner;
		public $main;
		public $side;

		public function __construct(){
			$this->corner = "(이름없음)";
			$this->main = [];
			$this->side = [];
		}

		public function getMainDish(){
			$r = "";
			foreach($this->main as $e)
				$r .= "🍽 " . $e . "\n";

			return $r;
		}
		public function getSideDish(){
			if(count($this->side) < 1)
				return "";
			
			return ( "👩‍🍳 " . implode(", ", $this->side) . "\n" );
		}
	}