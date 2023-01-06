# 1a. Vyčistenie tabuľky 'visitors_data' od starých záznamov
```SQL
DELETE FROM visitors_data
WHERE DATE(visited_at) < SUBDATE(CURDATE(), INTERVAL 365 DAY);
```

# 1b. Vyčistenie tabuľky 'visitors_expires' od neaktuálnych záznamov
```SQL
DELETE FROM visitors_expires
WHERE expires_at < NOW();
```

# 1c. Vyčistenie celej tabuľky 'visitors_statistics'
```SQL
TRUNCATE visitors_statistics;
-- ALTER TABLE visitors_statistics AUTO_INCREMENT=1;
```

# 2. Vytvoriť zoznam všetkých jedinečných záznamov
```SQL
SELECT DISTINCT
    viewable_type, viewable_id, category, is_crawler
FROM visitors_data;
```

# 2b. Vytvoriť zoznam všetkých možností
```SQL
SELECT DISTINCT viewable_type, viewable_id, category, is_crawler 
FROM (
	#1
	SELECT id, viewable_type, viewable_id, category, is_crawler
	FROM visitors_data
	UNION
	#2
	SELECT id, viewable_type, viewable_id, category, NULL
	FROM visitors_data
	UNION
	#3
	SELECT id, viewable_type, viewable_id, NULL, is_crawler
	FROM visitors_data
	UNION
	#4   
	SELECT id, viewable_type, viewable_id, NULL, NULL
	FROM visitors_data
	UNION
	#5
	SELECT id, viewable_type, NULL, category, is_crawler
	FROM visitors_data
	UNION
	#6
	SELECT id, viewable_type, NULL, category, NULL
	FROM visitors_data
	UNION
	#7
	SELECT id, viewable_type, NULL, NULL, is_crawler
	FROM visitors_data
	UNION
	#8   
	SELECT id, viewable_type, NULL, NULL, NULL
	FROM visitors_data
	UNION
	#9
	SELECT id, NULL, NULL, category, is_crawler
	FROM visitors_data
	UNION
	#10
	SELECT id, NULL, NULL, category, NULL
	FROM visitors_data
	UNION
	#11
	SELECT id, NULL, NULL, NULL, is_crawler
	FROM visitors_data
	UNION
	#12
	SELECT id, NULL, NULL, NULL, NULL
	FROM visitors_data
) AS VARIANTS
WHERE id <= 5
ORDER BY viewable_type, viewable_id, category, is_crawler;
```

# 3. Zistenie min a max dátumu pre typ záznamu
```SQL
SELECT
	MIN(visited_at) AS date_from, MAX(visited_at) AS date_to, MAX(id) AS last_id
FROM visitors_data
WHERE
	viewable_type = 'App\\Models\\StaticPage'
	AND
	viewable_id = 73
	AND
	category = 5
	AND
	is_crawler = 0;
```

# 4. Zoznam návštev
```SQL
SELECT DATE_LIST.selected_date, COALESCE(VISIT.visits_count, 0) AS visits_count
FROM
	(SELECT *
	FROM
		(SELECT adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) AS selected_date
		FROM
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
			(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4
		) v
	WHERE selected_date BETWEEN SUBDATE(CURDATE(), INTERVAL 365 DAY) AND CURDATE()
	) AS DATE_LIST
LEFT JOIN
	(SELECT
		DATE(visited_at) AS visits_date,
		COUNT(visited_at) AS visits_count
	FROM `visitors_data`
	WHERE
		viewable_type = 'App\\Models\\StaticPage'
		AND
		viewable_id = 73
		AND
		category = 5
		AND
		is_crawler = 0
	GROUP BY visits_date) AS VISIT
ON DATE_LIST.selected_date = VISIT.visits_date
ORDER BY selected_date DESC;
```

# 5. Kolekcia návštev priamo v JSON formáte
```SQL
SELECT
    CONCAT(
        '[',
        GROUP_CONCAT(
            JSON_OBJECT(
					'date', OUTPUT_DATA.selected_date, 
					'visit_count', COALESCE(OUTPUT_DATA.visitors_data_count, 0)
				)
            ORDER BY OUTPUT_DATA.selected_date DESC
        ),
        ']'
    ) AS OUTPUT_JSON
FROM
	(SELECT DATE_LIST.selected_date, VISIT.visitors_data_count
	FROM
		(SELECT *
		FROM
			(SELECT adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) AS selected_date
			FROM
				(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t0,
				(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t1,
				(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t2,
				(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t3,
				(SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) t4
			) v
		WHERE selected_date BETWEEN SUBDATE(CURDATE(), INTERVAL 365 DAY) AND CURDATE()
		) AS DATE_LIST
	LEFT JOIN
		(SELECT
			DATE(visited_at) AS visits_date,
			COUNT(visited_at) AS visitors_data_count
		FROM `visitors_data`
		WHERE
			viewable_type = 'App\\Models\\StaticPage'
			AND
			viewable_id = 73
			AND
			category = 5
			AND
			is_crawler = 0
		GROUP BY visits_date) AS VISIT
	ON DATE_LIST.selected_date = VISIT.visits_date
	ORDER BY selected_date DESC) AS OUTPUT_DATA;
```

# 6. Krajiny
```SQL
SELECT
	country,
	COUNT(country) AS visitors_country_count
FROM visitors_data
WHERE
	viewable_type = 'App\\Models\\StaticPage'
	AND
	viewable_id = 73
	AND
	category = 5
	AND
	is_crawler = 0
GROUP BY country
ORDER BY visitors_country_count DESC;
```
# 7. Jazyk
```SQL
SELECT
	`language`,
	COUNT(`language`) AS visitors_language_count
FROM visitors_data
WHERE
	viewable_type = 'App\\Models\\StaticPage'
	AND
	viewable_id = 73
	AND
	category = 5
	AND
	is_crawler = 0
GROUP BY `language`
ORDER BY visitors_language_count DESC;
```

# 8. Systém
```SQL
SELECT
	operating_system,
	COUNT(operating_system) AS visitors_operating_system_count
FROM visitors_data
WHERE
	viewable_type = 'App\\Models\\StaticPage'
	AND
	viewable_id = 73
	AND
	category = 5
	AND
	is_crawler = 0
GROUP BY operating_system
ORDER BY visitors_operating_system_count DESC;
```

# 9. Spojenie tabulky modelu s viewable interfacem s traffic tabuľkou
```SQL
SELECT *
FROM `faradetva`.`static_pages` AS VIEWABLE
LEFT JOIN
	(SELECT *
	FROM `faradetva.statistics`.`visitors_traffic`
	WHERE `faradetva.statistics`.`visitors_traffic`.`is_crawler` = 0
	AND `faradetva.statistics`.`visitors_traffic`.`category` IS NULL) AS TRAFFIC
ON VIEWABLE.`id` = TRAFFIC.`viewable_id`
AND VIEWABLE.`deleted_at` IS NULL
AND TRAFFIC.`viewable_type` = 'App\\Models\\StaticPage'
ORDER BY TRAFFIC.`visit_total` DESC
```


<!-- # 9. 
```SQL

```


# 9. 
```SQL

``` -->

