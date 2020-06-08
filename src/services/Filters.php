<?php

namespace Masuga\CpFilters\services;

use Craft;
use Exception;
use craft\helpers\ArrayHelper;
use craft\helpers\FileHelper;
use Masuga\CpFilters\base\Service;

class Filters extends Service
{

	/**
	 * This method converts filter input criteria into query criteria.
	 * @param array $input
	 * @return array
	 */
	public function formatCriteria($input): array
	{
		$criteria = [];
		foreach($input as &$filter) {
			$fieldHandle = $filter['fieldHandle'] ?? null;
			$filterType = $filter['filterType'] ?? null;
			$value = $filter['value'] ?? null;
			$newCriteria = $this->plugin->fieldTypes->fieldCriteria($fieldHandle, $filterType, $value);
			// In case of multiple "relatedTo" parameters, merge them.
			if ( isset($newCriteria['relatedTo']) ) {
				// If we haven't already added the "relatedTo" parameter, we need the "and".
				if ( ! isset($criteria['relatedTo']) ) {
					$criteria['relatedTo'] = ['and'];
				}
				$criteria['relatedTo'] = array_merge($criteria['relatedTo'], [$newCriteria['relatedTo']]);
			// Other types of criteria may just be merged as usual.
			} else {
				$criteria = array_merge($criteria, $newCriteria);
			}
		}
		return $criteria;
	}

	/**
	 * This method converts an array of elements to a CSV file stored in the Craft
	 * temp path.
	 */
	public function generateCsvFile($entries, $basename)
	{
		// Items in the array might be objects, convert the object(s) to an array.
		$arrayContent = ArrayHelper::toArray($entries);
		foreach($arrayContent as $rowIndex => &$record) {
			// Let's add the column names as a row to the CSV array content.
			if ( $rowIndex === 0 ) {
				array_unshift($arrayContent, array_keys($record));
			}
			// There may be array values in each item array. We need to flatten those.
			foreach($record as $fieldName => &$fieldValue) {
				if ( is_array($fieldValue) ) {
					$fieldValue = json_encode($fieldValue);
				}
			}
		}
		$csvContent = $this->arrayToCsv($arrayContent);
		$filePath = Craft::$app->path->getTempPath().'/'.$basename.'.csv';
		FileHelper::writeToFile($filePath, $csvContent);
		return file_exists($filePath) ? $filePath : null;
	}

	/**
	 * This method converts an array of arrays content to a CSV string.
	 * @param array
	 * @return string
	 */
	public function arrayToCsv($arr=[]): string
	{
		ob_start();
		$f = fopen('php://output', 'w') or show_error("Can't open php://output");
		foreach ($arr as &$line) {
			fputcsv($f, $line, ',');
		}
		fclose($f) or show_error("Can't close php://output");
		$csvContent = ob_get_contents();
		ob_end_clean();
		return (string) $csvContent;
	}

}