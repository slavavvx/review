<?php
namespace Services\Reviews;

use \Exception;

class ReviewsService
{
    private const LIMIT = 'limit';
    private const CURRENT_PAGE = 'currentPage';

	public const LIMIT_DEFAULT = 7;
    public const CURRENT_PAGE_DEFAULT = 1;
    public const MAX_PAGINATED_PAGES = 5;
    private const LIMITS_PER_PAGE = [7, 14, 21, 70];

    /**
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function prepareData(array $data) : array
    {
    	if (empty($data[self::LIMIT])) {
            $limit = self::LIMIT_DEFAULT;
        } else {
            $limit = filter_var($data[self::LIMIT], FILTER_VALIDATE_INT);
        }

        if (empty($data[self::CURRENT_PAGE])) {
            $currentPage = self::CURRENT_PAGE_DEFAULT;
        } else {
            $currentPage = filter_var($data[self::CURRENT_PAGE], FILTER_VALIDATE_INT);
        }

        if ($currentPage && $limit) {

            if (in_array($limit, self::LIMITS_PER_PAGE)) {

                return [
                    self::LIMIT => $limit,
                    self::CURRENT_PAGE => $currentPage,
                ];
            }
        }

        throw new Exception('Limit or Current page is not correct!');
    }

    /**
     * Replacement English to Russian in form element 'selection'
     * 
     * @param string $theme
     * 
     * @return string
     * 
     */
    public function translateTheme(string $theme) : string
    {
        switch ($theme) {
            case 'thanks': 
                return 'Благодарность';
            case 'proposal':
                return 'Предложение по улучшению сервиса';
            case 'complaint':
                return 'Жалоба';
            default:
                return 'Error';
        }
    }
}
