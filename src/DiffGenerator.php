<?php

/**
 * DiffGenerator.php contains class {@link DiffGenerator}.
 *
 * @author Jeff Stubler
 * @version 0.1
 * @package com.jeffstubler.diffgenerator
 */

/**
 * {@code DiffGenerator} provides a different generator to find deltas between strings broken
 * into different units.
 *
 * @author Jeff Stubler
 * @version 2.0
 * @package com.jeffstubler.diffgenerator
 */

class DiffGenerator {
	private $diffs = array();
	
	public function __construct(array $configuration, $originalString, $newString) {
		$this->breakStrings($configuration, $originalString, $newString, $originalArray, $newArray);
		//$this->setStartAndEndPoints($originalArray, $newArray, $start, $originalEnd, $newEnd);
		$start = 0;
		$originalEnd = count($originalArray) - 1;
		$newEnd = count($newArray) - 1;
		$this->createMatrix($originalArray, $newArray, $start, $originalEnd, $newEnd, $matrix);
		$this->findDiffs($matrix, $originalArray, $newArray, count($originalArray) - 1, count($newArray) - 1);
		
		
		$line = '';
		
		for($i = 0; $i < $originalEnd - $start + 1; $i++) {
			for($j = 0; $j < $newEnd - $start + 1; $j++) {
				$line .= $matrix[$i][$j] . ' ';
			}
			//echo $line . '<br />';
			$line = '';
		}
		//echo '<br />';
		
		for($i = 0; $i < count($originalArray); $i++) {
			//echo 'Offset: ' . $i . '; String: ' . $originalArray[$i]['string'] . '; Hash: ' . $originalArray[$i]['hash'] . '<br />';
		}
		
		//echo '<br />';
		
		for($i = 0; $i < count($newArray); $i++) {
			//echo 'Offset: ' . $i . '; String: ' . $newArray[$i]['string'] . '; Hash: ' . $newArray[$i]['hash'] . '<br />';
		}
		
		//echo '<br />';
		
		foreach($this->diffs as $diff) {
			//print_r($diff);
			//echo '<br />';
		}
	}
	
	public function getDiffs() {
		return $this->diffs;
	}
	
	private function breakStrings(array $configuration, $originalString, $newString, &$originalArray, &$newArray) {
		$originalString = (string) $originalString;
		$newString = (string) $newString;
		
		if(!isset($configuration['type'])) {
			throw new Exception('No configuration type is set');
		} else if(!($configuration['type'] == 'block' || $configuration['type'] == 'delimited')) {
			throw new Exception('Invalid configuration type');
		}
		
		if($configuration['type'] == 'block' && !isset($configuration['size'])) {
			throw new Exception('No block size specified');
		} else if($configuration['size'] < 0 || $configuration['size'] > 256) {
			throw new Exception('Invalid block size specified');
		}
		
		if($configuration['type'] == 'block') {
			$stringPosition = 0;
			$currentArrayEntry = '';
			
			for($currentBlock = 0; $currentBlock < ceil(strlen($originalString) / $configuration['size']); $currentBlock++) {
				for($blockCounter = 0; $blockCounter < $configuration['size']; $blockCounter++) {
					$currentArrayEntry .= substr($originalString, $stringPosition++, 1);
				}
				
				$originalArray[$currentBlock]['string'] = $currentArrayEntry;
				$originalArray[$currentBlock]['hash'] = $this->hash($currentArrayEntry);
				$currentArrayEntry = '';
			}
			
			$stringPosition = 0;
			
			for($currentBlock = 0; $currentBlock < ceil(strlen($newString) / $configuration['size']); $currentBlock++) {
				for($blockCounter = 0; $blockCounter < $configuration['size']; $blockCounter++) {
					$currentArrayEntry .= substr($newString, $stringPosition++, 1);
				}
				
				$newArray[$currentBlock]['string'] = $currentArrayEntry;
				$newArray[$currentBlock]['hash'] = $this->hash($currentArrayEntry);
				$currentArrayEntry = '';
			}
		}
	}
	
	private function createMatrix(array $originalArray, array $newArray, $start, $originalEnd, $newEnd, &$matrix) {
		for($i = 0; $i < $originalEnd - $start; $i++) {
			for($j = 0; $j < $newEnd - $start; $j++) {
				$matrix[$i][$j] = 0;
			}
		}
		
		for($i = 0; $i < $originalEnd - $start + 1; $i++) {
			for($j = 0; $j < $newEnd - $start + 1; $j++) {
				if($originalArray[$i]['hash'] == $newArray[$j]['hash']) {
					$matrix[$i][$j] = 1 + ($i == 0 || $j == 0 ? 0 : $matrix[$i - 1][$j - 1]);
				} else {
					$matrix[$i][$j] = max($i == 0 ? 0 : $matrix[$i - 1][$j], $j == 0 ? 0 : $matrix[$i][$j - 1]);
				}
			}
		}
	}
	
	private function findDiffs(array $matrix, array $originalArray, array $newArray, $i, $j) {
		if($i > 0 && $j > 0 && $originalArray[$i - 1]['hash'] == $newArray[$j - 1]['hash']) {
			$this->findDiffs($matrix, $originalArray, $newArray, $i - 1, $j - 1);
			array_push($this->diffs, $originalArray[$i - 1]['string']);
		} else {
			if($j > 0 && ($i == 0 || $matrix[$i][$j - 1] >= $matrix[$i - 1][$j])) {
				$this->findDiffs($matrix, $originalArray, $newArray, $i, $j - 1);
				array_push($this->diffs, '+' . $newArray[$j - 1]['string']);
			} else if($i > 0 && ($j == 0 || $matrix[$i][$j - 1] < $matrix[$i - 1][$j])) {
				$this->findDiffs($matrix, $originalArray, $newArray, $i - 1, $j);
				array_push($this->diffs, '-' . $originalArray[$i - 1]['string']);
			}
		}
	}
	
	private function setStartAndEndPoints($originalArray, $newArray, &$start, &$originalEnd, &$newEnd) {
		for($start = 0; $start < min(count($originalArray), count($newArray)) && $originalArray[$start]['hash'] == $newArray[$start]['hash']; $start++);
		for($originalEnd = count($originalArray) - 1, $newEnd = count($newArray) - 1; $start < min(count($originalArray), count($newArray)) && $originalArray[$originalEnd]['hash'] == $newArray[$newEnd]['hash']; $originalEnd--, $newEnd--);
		
		//echo 'Start: ' . $start . '<br />';
		//echo 'Original end: ' . $originalEnd . '; New end: ' . $newEnd . '<br /><br />';
	}
	
	private function hash($data) {
		$data = sha1($data);
		$top = hexdec(substr($data, 1, 4));
		$bottom = hexdec(substr($data, 5, 4));
		return $top << 16 | $bottom;
	}

}

?>