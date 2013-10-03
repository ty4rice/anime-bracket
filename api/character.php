<?php

namespace Api {
	
	use Lib;
	
	class Character extends Lib\Dal {
	
		/**
		 * Object property to table column map
		 */
		protected $_dbMap = array(
			'id' => 'character_id',
			'bracketId' => 'bracket_id',
			'name' => 'character_name',
			'source' => 'character_source'
		);
		
		/**
		 * Database table
		 */
		protected $_dbTable = 'character';
		
		/**
		 * Primary key
		 */
		protected $_dbPrimaryKey = 'id';
	
		/**
		 * Character record ID
		 */
		public $id = 0;
	
		/**
		 * Bracket ID
		 */
		public $bracketId = 0;
	
		/**
		 * Character name
		 */
		public $name = 0;
	
		/**
		 * Character source
		 */
		public $source = 0;
		
		/**
		 * Image path for this character
		 */
		public $image = '';
		
		/**
		 * Constructor
		 */
		public function __construct($character = null) {
			if (is_object($character)) {
				parent::__construct($character);
				$this->image = base_convert($this->id, 10, 36) . '.jpg';
			}
		}

		/**
		 * Override for image
		 */
		public function copyFromDbRow($row) {
			parent::copyFromDbRow($row);
			$this->image = base_convert($this->id, 10, 36) . '.jpg';
		}

		/**
		 * Gets other characters in this bracket with similar names (checks for Japanese and Western naming order)
		 */
		public static function getBySimilarName($name, $bracket) {
			$retVal = null;
			if ($bracket instanceof Bracket) {
				$query = 'SELECT * FROM `character` WHERE bracket_id = :bracketId ';
				$params = [ ':bracketId' => $bracket->id ];
				$name = explode(' ', $name);
				if (count($name) === 2) {
					$params[':nameA'] = $name[0] . '%' . $name[1];
					$params[':nameB'] = $name[1] . '%' . $name[0];
					$query .= 'AND (character_name LIKE :nameA OR character_name LIKE :nameB)';
				} else {
					$params[':name'] = implode('%', $name);
					$query .= 'AND character_name LIKE :name';
				}

				$result = Lib\Db::Query($query, $params);
				if ($result && $result->count) {
					$retVal = [];
					while ($row = Lib\Db::Fetch($result)) {
						$retVal[] = new Character($row);
					}
				}
			}
			return $retVal;
		}

	}

}