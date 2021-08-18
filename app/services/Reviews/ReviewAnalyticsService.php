<?php
namespace Services\Reviews;

use \Exception;

class ReviewAnalyticsService
{
	const SUMMARY_TITLE_LOVE = 'Клиенты нас любят!';
	const SUMMARY_TITLE_IMPROVE = 'Нам надо совершенствоваться!';
	const SUMMARY_TITLE_CHANGE = 'Пора меняться!';
	const SUMMARY_TITLE_HATE = 'Надо сжечь это место!';

	const THEME_THANKS = 'thanks';
	const THEME_PROPOSAL = 'proposal';
	const THEME_COMPLAINT = 'complaint';

    /**
     * @param array $data
     * @param int $totalRows
     *
     * @return array
     *
     * @throws Exception
     */
    public function prapareAnalytics(array $data, int $totalRows) : array
    {
		if (empty($data) || empty($totalRows)) {
			throw new Exception('No data for analytics!');
		}

	    $percents = [];

	    foreach ($data as $key => $value) {
			$percents[$key] = round(($value * 100) / $totalRows);
		}

		if ($percents[self::THEME_THANKS] >= 70) {
			$title = self::SUMMARY_TITLE_LOVE;
		} else if ($percents[self::THEME_THANKS] >= 50 && $percents[self::THEME_THANKS] <= 70) {
			$title = self::SUMMARY_TITLE_IMPROVE;
		} else if ($percents[self::THEME_THANKS] >= 30 && $percents[self::THEME_THANKS] <= 50) {
			$title = self::SUMMARY_TITLE_CHANGE;
		} else {
			$title = self::SUMMARY_TITLE_HATE;
		}

		return [
			'totalRows'   => $totalRows,
			'title'       => $title,
			'description' => [
				$percents[self::THEME_THANKS] . '% клиентов довольны нашей работой;',
        		$percents[self::THEME_PROPOSAL] . '% клиентов считают, что не все у нас хорошо;',
        		$percents[self::THEME_COMPLAINT] . '% клиентов недовольны нами.',
			],
		];
	}
}
