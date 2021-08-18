<?php

namespace Models;

use \Exception;
use \PDO;
use Core\Model;

class Review extends Model
{
	
	//---------- Model config params ----------//
	/**
	 * Table name
	 *
	 * @var string
	 */
	const TABLE_NAME = 'reviews';
	
	
	//---------- Model data params ----------//
	
	/**
	 * @var integer
	 */
	protected $review_id;
	
	/**
	 * @var string
	 */
	protected $username;
	
	/**
	 * @var string
	 */
	protected $theme;
	
	/**
	 * @var string
	 */
	protected $text;
	
	/**
	 * @var string
	 */
	protected $date;
	
	/**
	 * @var integer
	 */
	protected $like;
	
	/**
	 * @var integer
	 */
	protected $dislike;
	
	
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
	public function getUsername(): string
	{
		return $this->username;
	}
	
	/**
	 * @return string
	 */
	public function getTheme(): string
	{
		return $this->theme;
	}
	
	/**
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}
	
	/**
	 * @return int
	 */
	public function getDate(): int
	{
		return $this->date;
	}
	
	/**
	 * @return int
	 */
	public function getLike(): int
	{
		return $this->like;
	}
	
	/**
	 * @return int
	 */
	public function getDislike(): int
	{
		return $this->dislike;
	}
	
	/**
	 * Save text
	 *
	 * @param array $formData
	 * @return bool
	 */
	public function saveReview(array $formData): bool
	{
		$query = 'INSERT INTO ' . self::TABLE_NAME . ' (username, theme, text) VALUES (:username, :theme, :text)';
		
		$stmt = $this->getDb()->prepare($query);
		$stmt->bindParam(':username', $formData['name'], PDO::PARAM_STR);
		$stmt->bindParam(':theme', $formData['theme'], PDO::PARAM_STR);
		$stmt->bindParam(':text', $formData['text'], PDO::PARAM_STR);
		
		return $stmt->execute();
	}
	
	/**
	 * @param array $params
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function updateLike(array $params): bool
	{
		if (!empty($params['like']) && !empty($params['dislike'])) {
			$query = 'UPDATE ' . self::TABLE_NAME . ' SET ' . $params['like'] . ', ' . $params['dislike'] .
			  ' WHERE review_id = :review_id';
		} elseif (!empty($params['like'])) {
			$query = 'UPDATE ' . self::TABLE_NAME . ' SET ' . $params['like'] . ' WHERE review_id = :review_id';
		} elseif (!empty($params['dislike'])) {
			$query = 'UPDATE ' . self::TABLE_NAME . ' SET ' . $params['dislike'] . ' WHERE review_id = :review_id';
		} else {
			throw new Exception('Error: failed data for "like"!');
		}
		
		$stmt = $this->getDb()->prepare($query);
		$stmt->bindParam(':review_id', $params['review_id'], PDO::PARAM_INT);
		return $stmt->execute();
	}
}
