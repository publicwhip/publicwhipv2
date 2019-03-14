This directory contains the structure and data for version 1 of the Public Whip website. The data has had personal
information removed/nullified and has been restricted to just the 'important tables'.

It is automatically loaded into docker whenever the `docker-composer up --build` command is run
(or docker compose is run for the first time).

The data was anonymised/reduced using:
```sql
# Remove unwanted tables
SELECT CONCAT('DROP TABLE ',GROUP_CONCAT(CONCAT('`publicwhip-db`.`',table_name,'`')),';')
INTO @dropcmd
FROM information_schema.TABLES
WHERE TABLE_SCHEMA='publicwhip-db'
AND (TABLE_NAME LIKE 'phpbb3\_%' OR TABLE_NAME LIKE 'phpbb_%');

PREPARE stmt FROM @dropcmd;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

# Remove private dream mp (policies) entries.
DELETE FROM pw_dyn_dreammp WHERE private=1;
DELETE FROM pw_cache_dreaminfo WHERE dream_id NOT IN (SELECT dream_id FROM pw_dyn_dreammp);
DELETE FROM pw_cache_dreamreal_distance WHERE dream_id NOT IN (SELECT dream_id FROM pw_dyn_dreammp);
DELETE FROM pw_dyn_dreamvote WHERE dream_id NOT IN (SELECT dream_id FROM pw_dyn_dreammp);

# Remove users which are not used anywhere.
DELETE FROM pw_dyn_user WHERE user_id NOT IN (
SELECT DISTINCT(user_id) FROM (
SELECT wm.user_id FROM pw_dyn_wiki_motion AS wm
UNION
SELECT al.user_id FROM pw_dyn_auditlog AS al
UNION
SELECT dm.user_id FROM pw_dyn_dreammp AS dm
) AS users);

# anonymise the auditlog
UPDATE `pw_dyn_auditlog` SET remote_addr=CONCAT('172.16.',FLOOR(RAND() * 253) +1,'.',FLOOR(RAND() * 253) +1),event='- Removed -';

# anonymise the users entries
UPDATE `pw_dyn_user` SET user_name=CONCAT('User ',user_id),
real_name=CONCAT('Real ',user_id,' Name'),
email=CONCAT('user',user_id,'@example.com'),
remote_addr=CONCAT('172.16.',FLOOR(RAND() * 253) +1,'.',FLOOR(RAND() * 253) +1),
confirm_hash='- Removed -',
confirm_return_url='http://www.publicwhip.org.uk/',
reg_date=DATE(DATE_SUB(NOW(), INTERVAL ROUND(RAND(1)*1000) DAY)),
password=MD5('password');
```

Yes, version 1 does store the passwords in MD5(!).

and then exported:

```bash
docker exec -it publicwhip-mariadb /bin/bash


mysqldump -u root --password=root publicwhip-db pw_cache_divinfo > /docker-entrypoint-initdb.d/pw_cache_divinfo.sql
mysqldump -u root --password=root publicwhip-db pw_cache_divwiki > /docker-entrypoint-initdb.d/pw_cache_divwiki.sql
mysqldump -u root --password=root publicwhip-db pw_cache_mpinfo > /docker-entrypoint-initdb.d/pw_cache_mpinfo.sql
mysqldump -u root --password=root publicwhip-db pw_cache_partyinfo > /docker-entrypoint-initdb.d/pw_cache_partyinfo.sql
mysqldump -u root --password=root publicwhip-db pw_cache_whip > /docker-entrypoint-initdb.d/pw_cache_whip.sql
mysqldump -u root --password=root publicwhip-db pw_constituency > /docker-entrypoint-initdb.d/pw_constituency.sql
mysqldump -u root --password=root publicwhip-db pw_division > /docker-entrypoint-initdb.d/pw_division.sql
mysqldump -u root --password=root publicwhip-db pw_dyn_auditlog > /docker-entrypoint-initdb.d/pw_dyn_auditlog.sql
mysqldump -u root --password=root publicwhip-db pw_dyn_user > /docker-entrypoint-initdb.d/pw_dyn_user.sql
mysqldump -u root --password=root publicwhip-db pw_dyn_wiki_motion > /docker-entrypoint-initdb.d/pw_dyn_wiki_motion.sql
mysqldump -u root --password=root publicwhip-db pw_moffice > /docker-entrypoint-initdb.d/pw_moffice.sql
mysqldump -u root --password=root publicwhip-db pw_mp > /docker-entrypoint-initdb.d/pw_mp.sql
mysqldump -u root --password=root publicwhip-db pw_vote > /docker-entrypoint-initdb.d/pw_vote.sql
mysqldump -u root --password=root publicwhip-db pw_vote_sortorder > /docker-entrypoint-initdb.d/pw_vote_sortorder.sql
# Now for the policies
mysqldump -u root --password=root publicwhip-db pw_cache_dreaminfo > /docker-entrypoint-initdb.d/pw_cache_dreaminfo.sql
mysqldump -u root --password=root publicwhip-db pw_cache_dreamreal_distance > /docker-entrypoint-initdb.d/pw_cache_dreamreal_distance.sql
mysqldump -u root --password=root publicwhip-db pw_dyn_dreammp >  /docker-entrypoint-initdb.d/pw_dyn_dreammp.sql
mysqldump -u root --password=root publicwhip-db pw_dyn_dreamvote > /docker-entrypoint-initdb.d/pw_dyn_dreamvote.sql
# and compress them all
gzip /docker-entrypoint-initdb.d/*.sql


```