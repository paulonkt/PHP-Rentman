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
			# Get shortages by given equipment id, start and end date
			$arrayPlanned = EquimentAvailabilityHelper::getResultsByRange($equipment_id, $start, $end);
			
			# Get the planned quanqity from query result
			$qtyPlanned = $arrayPlanned[0]['qty_planned'];
			
			/* Check the avilable stock given the equipment id */
			$stock = $this->availableStockPerEquipment($equipment_id);

			/* Sum the quantity provided with the actual stock */
			$qtyRequested = $quantity + $qtyPlanned;
			
			/* By default item is available until the verification */
			$is_available = TRUE;
			
			/* If the quantity requested is greater than how many items in stock set availability to false */
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
			# Get shortages by given start and end date
			$plannedArray = EquimentAvailabilityHelper::getResultsByRange(0, $start, $end);

			$shortageArray = [];
			foreach ($plannedArray as $equipmentRow) {
				# Get values from actual array row
				$qtyPlanned = (int)$equipmentRow['qty_planned'];
				$equipmentId = (int)$equipmentRow['equipment_id'];

				# Check the avilable stock given the equipment id from the plannedArray
				$stock = $this->availableStockPerEquipment($equipmentId);

				# Get the shortage for equipment
				$qtyShortage = $stock - $qtyPlanned;

				# If quanqity is negative add to shortage array
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
