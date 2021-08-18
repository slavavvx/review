<?php
namespace Models\Repository;

use \Exception;
use \PDO;
use Models\Image as ImageModel;

class Image extends RepositoryAbstract
{
    /**
     * @return string
     */
    public function getModelName() : string
    {
        return ImageModel::class;
    }

    /**
     * @return integer
     * 
     * @throws Exception
     */
    public function countRows() : int
    {
        /** @var ImageModel $imageModel */
        $imageModel = $this->getModelName();

        $query = "SELECT COUNT(*) AS num FROM " . $imageModel::TABLE_NAME;

        $result = $this->getDb()->query($query);

        if (!$result) {
            throw new Exception('Request to count quantity rows is failed');
        }
        $row = $result->fetch(PDO::FETCH_OBJ);

        return $row->num;
    }

    /**
     * @param integer $id
     * 
     * @return array | null
     * 
     * @throws Exception
     */
    public function getById(int $id) : ?array
    {   
        /** @var ImageModel $imageModel */
        $imageModel = $this->getModelName();

        $columnsName = 'review_id, image_name';
        $query = "SELECT " . $columnsName . " FROM " . $imageModel::TABLE_NAME;

        $condition = " WHERE review_id = :review_id";
        $query .= $condition;
        $params = [':review_id' => $id];

        $stmt = $this->getDb()->prepare($query);

        if (!$stmt->execute($params)) {
            throw new Exception('Request failed');
        }

        $row = $stmt->fetch();
        return $row ?: null;
    }
}
