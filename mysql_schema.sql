SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `admin_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `ts` int(10) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `ua` char(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `config` (
  `name` char(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `value` char(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pages` (
  `short_name` char(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `title` char(255) NOT NULL,
  `md` text NOT NULL DEFAULT '',
  `html` text NOT NULL DEFAULT '',
  `ts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `update_ts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `visible` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` char(255) NOT NULL,
  `md` text NOT NULL,
  `html` text NOT NULL,
  `text` text NOT NULL,
  `ts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `update_ts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `visible` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `short_name` char(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `posts_tags` (
  `id` int(11) NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `tag` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `posts_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `visible_posts_count` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `uploads` (
  `id` int(10) UNSIGNED NOT NULL,
  `random_id` char(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `ts` int(10) UNSIGNED NOT NULL,
  `name` char(255) NOT NULL,
  `size` int(10) UNSIGNED NOT NULL,
  `downloads` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `image` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `note` char(255) NOT NULL DEFAULT '',
  `image_w` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `image_h` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`name`);

ALTER TABLE `debug_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pages`
  ADD PRIMARY KEY (`short_name`);

ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_name` (`short_name`),
  ADD KEY ` visible_ts_idx` (`visible`,`ts`);

ALTER TABLE `posts_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `postid_tagid_idx` (`post_id`,`tag_id`);

ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`);

ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `random_id` (`random_id`);

ALTER TABLE `admin_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `debug_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `posts_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `uploads`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
