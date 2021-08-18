<?php
namespace Services\Reviews;

use \Exception;

class ReviewLikeService
{
    /**
     * @param array $params
     * 
     * @return array
     * 
     * @throws Exception
     */
    public function prepareDataForLike(array $params) : array
    {
        $like = '';
        $dislike = '';
	    $likeMessage = '';

        if (empty($params['reviewId'])) {
            throw new Exception('Failed data. There is not review id!');
        } 

        if (!empty($params['like'])) {
            $like = $params['like'];

            if ($like == 'add') {
                $assignmentLike = "`like` = `like` + 1";
                $likeMessage = 'Добавлено в полезные отзывы.';
            } elseif ($like == 'ded') {
                $assignmentLike = "`like` = `like` - 1";
                $likeMessage = $dislike ? $likeMessage : 'Удалено из полезных отзывов.';
            } else {
                throw new Exception('Error: failed data for "like"!');
            }
        }

        if (!empty($params['dislike'])) {
            $dislike = $params['dislike'];

            if ($dislike == 'add') {
                $assignmentDislike = 'dislike = dislike + 1';
                $likeMessage = 'Отметка "Не нравится" поставлена.';
            } elseif ($dislike == 'ded') {
                $assignmentDislike = 'dislike = dislike - 1';
                $likeMessage = $like ? $likeMessage : 'Отметка "Не нравится" снята.';
            } else {
                throw new Exception('Error: failed data for "dislike"!');
            }
        }

        return [
            'review_id'   => (int) $params['reviewId'],
            'like'        => $assignmentLike ?? '',
            'dislike'     => $assignmentDislike ?? '',
            'likeMessage' => $likeMessage,
        ];
    }
}
