<?php
namespace Models;

use \PDO;
use Core\Model;

class Image extends Model
{
	//---------- Model config params ----------//
	/**
	 * Table name
	 *
	 * @var string
	 */
	const TABLE_NAME = 'images';
	
	
	//---------- Model data params ----------//
	
	/**
	 * @var integer
	 */
	protected $review_id;
	
	/**
	 * @var string
	 */
	protected $image_name;
	
	
	//---------- Model Methods ----------//
	
	//---------- Getters -----------//
	
	/**
	 * @return int
	 */
	public function getReviewId(): int
	{
		return $this->review_id;
	}
	
	/**
	 * @return string
	 */
	public function getImageName(): string
	{
		return $this->image_name;
	}
	
	/**
	 * Saving Image
	 *
	 * @param int $reviewId
	 * @param string $imageName
	 *
	 * @return bool
	 */
	public function saveImage(int $reviewId, string $imageName): bool
	{
		$sql = 'INSERT INTO ' . self::TABLE_NAME . ' (review_id, image_name) VALUES (:review_id, :image_name)';
		
		$stmt = $this->getDb()->prepare($sql);
		$stmt->bindParam(':review_id', $reviewId, PDO::PARAM_INT);
		$stmt->bindParam(':image_name', $imageName, PDO::PARAM_STR);
		
		return $stmt->execute();
	}
}
