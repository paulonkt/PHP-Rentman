<?php
namespace Assessment\Availability\Todo;

use Assessment\Availability\EquimentAvailabilityHelper;
use DateTime;
use Exception;

class EquimentAvailabilityHelperAssessment extends EquimentAvailabilityHelper {

	/**
	 * This function checks if a given quantity is available in the passed time frame
	 * @param int      $equipment_id Id of the equipment item
	 * @param int      $quantity How much should be available
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return bool True if available, false otherwise
	 */
	public function isAvailable(int $equipment_id, int $quantity, DateTime $start, DateTime $end) : bool {
		try {
			$arrayPlanned = EquimentAvailabilityHelper::getResultsByRange($equipment_id, $start, $end);
			$qtyPlanned = $arrayPlanned[0]['qty_planned'];
			$stock = $this->availableStockPerEquipment($equipment_id);
			$qtyRequested = $quantity + $qtyPlanned;

			$is_available = TRUE;
			if ($qtyRequested > $stock) {
				$is_available = FALSE;
			}
			return $is_available;

		} catch (Exception $e) {
			exit($e->getMessage()) ;
			return [];
		}
	}

	/**
	 * Calculate all items that are short in the given period
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return array Key/valyue array with as indices the equipment id's and as values the shortages
	 */
	public function getShortages(DateTime $start, DateTime $end) : array {
		try {
			$plannedArray = EquimentAvailabilityHelper::getResultsByRange(0, $start, $end);

			$shortageArray = [];
			foreach ($plannedArray as $equipmentRow) {
				$qtyPlanned = (int)$equipmentRow['qty_planned'];
				$equipmentId = (int)$equipmentRow['equipment_id'];

				$stock = $this->availableStockPerEquipment($equipmentId);

				$qtyShortage = $stock - $qtyPlanned;
				if ($qtyShortage < 0) {
					$shortageArray[$equipmentId] = $qtyShortage;
				}
			}
			return $shortageArray;

		} catch (Exception $e) {
			exit($e->getMessage()) ;
			return [];
		}
	}

}
