-- MySQL version  5.7.9
--
-- Host: localhost    Database: review
-- ------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `review`;

USE `review`;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;

CREATE TABLE `reviews` (
  `review_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `theme` varchar(20) NOT NULL,
  `text` varchar(650) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `like` int(10) unsigned NOT NULL DEFAULT 0,
  `dislike` int(10) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`review_id`)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;

CREATE TABLE `images` (
  `review_id` int(10) unsigned NOT NULL,
  `image_name` varchar(15) NOT NULL,
  PRIMARY KEY (`review_id`),
  FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB;


