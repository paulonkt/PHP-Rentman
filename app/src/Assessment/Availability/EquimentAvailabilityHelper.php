<?php
namespace Assessment\Availability;

use DateTime;
use PDO;
use PDOException;

abstract class EquimentAvailabilityHelper {

	/**
	 * EquimentAvailabilityHelper constructor.
	 * @param PDO $oDatabaseConnection
	 */
	public function __construct(private PDO $oDatabaseConnection) {
        
	}

	/**
	 * Get the already opened connection to the assessment database
	 * @return PDO
	 */
	public final function getDatabaseConnection() : PDO{
		return $this->oDatabaseConnection;
	}
	
	/**
	 * Get all equipments from database and return an array of equipments
	 * @return array
	 */
	public final function getEquipmentItems() : array{
		$aRows = $this->getDatabaseConnection()->query("SELECT * FROM equipment ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
		return array_column($aRows, null, 'id');
	}

	/**
	 * Get the quantity in stock for given equipment id
	 * @return int
	 */
	public final function availableStockPerEquipment(int $equipment_id) : int {
		try {
			$stmt = $this->getDatabaseConnection()->prepare("SELECT stock FROM equipment WHERE id = :equipment_id");
			$stmt->bindParam(":equipment_id", $equipment_id, PDO::PARAM_INT);
			$stmt->execute();
			$aRows = $stmt->fetch(PDO::FETCH_NUM);
			return $aRows[0];

		} catch (PDOException $e) {
			exit($e->getMessage()) ;
			return [];
		}
	}

	/**
	 * Get the quantity in stock for given equipment id
	 * @return array
	 */
	public final function getResultsByRange(int $equipment_id, DateTime $start, DateTime $end) : array {
		try {
			$arrayDates = EquimentAvailabilityHelper::formatDateTimeToString($start, $end);
			
			$strSql = " SELECT equipment as equipment_id, SUM(quantity) AS qty_planned FROM planning WHERE start < :end AND end > :start ";
			if ($equipment_id > 0) {
				$strSql.= " AND equipment = :equipment_id ";
			}
			$strSql.= " GROUP BY equipment_id ORDER BY equipment_id ASC ";
			$stmt = $this->getDatabaseConnection()->prepare($strSql);
			
			if ($equipment_id > 0) {
				$stmt->bindParam(":equipment_id", $equipment_id, PDO::PARAM_INT);
			}
			$stmt->bindParam(":start", $arrayDates['start'], PDO::PARAM_STR);
			$stmt->bindParam(":end", $arrayDates['end'], PDO::PARAM_STR);
			$stmt->execute();
			#$stmt->debugDumpParams();

			$plannedArray = $stmt->fetchAll(\PDO::FETCH_ASSOC);

			return $plannedArray;

		} catch (PDOException $e) {
			exit($e->getMessage()) ;
			return [];
		}
	}

	/**
	 * Get the quantity in stock for given equipment id
	 * @return int
	 */
	public final function formatDateTimeToString(DateTime $start, DateTime $end) : array {
		$startString = date_format($start, "Y-m-d");
		$endString = date_format($end, "Y-m-d");
		$datesArray = array('start' => $startString, 'end' => $endString);
		return $datesArray;
	}

	/**
	 * This function checks if a given quantity is available in the passed time frame
	 * @param int      $equipment_id Id of the equipment item
	 * @param int      $quantity How much should be available
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return bool True if available, false otherwise
	 */
	abstract public function isAvailable(int $equipment_id, int $quantity, DateTime $start, DateTime $end) : bool;

	/**
	 * Calculate all items that are short in the given period
	 * @param DateTime $start Start of time window
	 * @param DateTime $end End of time window
	 * @return array Key/valyue array with as indices the equipment id's and as values the shortages
	 */
	abstract public function getShortages(DateTime $start, DateTime $end) : array;
}
