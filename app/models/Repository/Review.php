<?php
namespace Models\Repository;

use \Exception;
use Models\Review as ReviewModel;

class Review extends RepositoryAbstract
{
    /**
     * @return string
     */
    public function getModelName() : string
    {
        return ReviewModel::class;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function countRows() : int
    {
        /** @var ReviewModel $reviewModel */
        $reviewModel = $this->getModelName();

        $query = "SELECT COUNT(*) AS num FROM " . $reviewModel::TABLE_NAME;

        $result = $this->getDb()->query($query);
        $row = $result->fetch();

        return $row['num'];
    }

    /**
     * @param array $themes
     * @return array
     * @throws Exception
     */
    public function getDataByThemes(array $themes) : array
    {
        /** @var ReviewModel $reviewModel */
        $reviewModel = $this->getModelName();

        $data = [];
        $columnName = 'theme';
        $query = "SELECT COUNT(*) AS num FROM " . $reviewModel::TABLE_NAME . " WHERE $columnName = :value";
        $stmt = $this->getDb()->prepare($query);

        foreach ($themes as $value) {
            $params = [':value' => $value];
            $stmt->execute($params);

            $row = $stmt->fetch();
            $data[$value] = $row['num'];
        }

        return $data;
    }

    /**
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function getById(int $id) : array
    {   
        /** @var ReviewModel $reviewModel */
        $reviewModel = $this->getModelName();

        $columnsName = "`review_id`, `username`, `theme`, `text`, `date`, `like`, `dislike`";
        $query = "SELECT " . $columnsName . " FROM " . $reviewModel::TABLE_NAME;

        $condition = " WHERE review_id = :review_id";
        $query .= $condition;
        $params = [':review_id' => $id];

        $stmt = $this->getDb()->prepare($query);
        $stmt->execute($params);

        return $stmt->fetch();
    }
	
	/**
	 * @param array $filters
	 * @return array
	 * @throws Exception
	 */
    public function getAll(array $filters) : array
    {   
        /** @var ReviewModel $reviewModel */
        $reviewModel = $this->getModelName();

        $columnsName = 'r.review_id, r.username, r.theme, r.text, r.date, r.like, r.dislike';
        $join = '';
       
        if (isset($filters['join']) && $filters['join'] == 'images') {

            $columnsName .= ', i.image_name';
            $join = "LEFT JOIN images AS i ON r.review_id = i.review_id";
        }

        $query = "SELECT " . $columnsName . " FROM " . $reviewModel::TABLE_NAME . " AS r ";
        $query .= $join;

        if (!empty($filters['sort_direction'])) {
            $query .= " ORDER BY " . $filters['sort_direction'];
        }

        if (!isset($filters['offset']) || empty($filters['limit'])) {
            throw new Exception('missing offset or limit');
        }

        $query .= " LIMIT " . (int) $filters['offset'] . "," . (int) $filters['limit'];

        $stmt = $this->getDb()->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
