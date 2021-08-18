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
  `like` int(10) unsigned NOT NULL DEFAULT '0',
  `dislike` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`review_id`)
) DEFAULT CHARSET=utf8 ENGINE=InnoDB AUTO_INCREMENT=9;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,'Anna','thanks','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 16:36:24',0,0),(2,'Alex','proposal','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:02:20',0,0),(3,'Julia','complaint','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:05:54',0,0),(4,'Vasya','proposal','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:06:32',0,0),(5,'Sveta','thanks','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:08:43',0,0),(6,'Юра','proposal','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:11:49',0,0),(7,'Andrey','complaint','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:12:26',0,1),(8,'Лена','thanks','Lorem ipsum dolor sit amet consectetur adipisicing elit. Perferendis consequuntur culpa temporibus numquam nostrum nesciunt deleniti quos amet obcaecati quidem, nulla porro dolorem quod nisi et ratione ea! Architecto, laboriosam.','2021-04-15 17:12:57',1,0);
/*ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Dumping data for table `images`
--

LOCK TABLES `images` WRITE;
/*ALTER TABLE `images` DISABLE KEYS */;
INSERT INTO `images` VALUES (1,'ea3bb3d742.jpg'),(3,'e636934884.jpg'),(6,'bdb7ff55b7.jpg'),(7,'c9ebc1f49b.jpg');
/*ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;
