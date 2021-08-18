<?php
namespace Services\ServiceProviders;

use Core\Storage;
use Services\Validator\Validation;
use Services\DataUpload\FileUpload;
use Services\Reviews\ReviewsService;
use Services\Reviews\ReviewLikeService;
use Services\Reviews\ReviewAnalyticsService;
use Services\ReCaptcha\ReCaptchaService;


class ServiceServiceProvider
{
	/**
     * @param Storage $storage
     */
    public function register(Storage $storage) : void
	{
        $storage->setShared('reviewsService',         new ReviewsService);
        $storage->setShared('validation',             new Validation);
        $storage->setShared('fileUpload',             new FileUpload);
        $storage->setShared('reviewAnalyticsService', new ReviewAnalyticsService);
        $storage->setShared('reviewLikeService',      new ReviewLikeService);
        $storage->setShared('reCaptchaService',       new ReCaptchaService);

        $storage->setShared('reviewFormService', [
            'className' => \Services\Forms\ReviewFormService::class,
            'arguments' => [
                ['name' => 'validation'],
                ['name' => 'fileUpload'],
            ],
        ]);

        $storage->setShared('reviewEntity', [
            'className' => \Services\Entities\Review::class,
            'arguments' => [
                ['name' => 'reviewRepository'],
                ['name' => 'imageRepository'],
                ['name' => 'fileUpload'],
            ],
        ]);
    }
}
