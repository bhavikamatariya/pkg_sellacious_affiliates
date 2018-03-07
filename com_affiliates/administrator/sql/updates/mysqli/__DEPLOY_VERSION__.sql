-- v__DEPLOY_VERSION__ Changes

ALTER TABLE `#___affiliates_categories`
	DROP `type`,
	DROP `is_default`,
	DROP `usergroups`,
	DROP `ledgergroup`,

ALTER TABLE `#___affiliates_profiles`
	DROP `cache_state`,
	DROP `currency`;

ALTER TABLE `#___affiliates_profiles`
	ADD `total_visits` INT NOT NULL  AFTER `website`,
	ADD `total_registered` INT NOT NULL  AFTER `total_visits`,
	ADD `total_sales` INT NOT NULL  AFTER `total_registered`;
