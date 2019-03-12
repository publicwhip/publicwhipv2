This directory contains the structure and data for version 1 of the Public Whip website. The data has had personal
information removed/nullified and has been restricted to just the 'important tables'.

It is automatically loaded into docker whenever the `docker-composer up --build` command is run
(or docker compose is run for the first time).

The data was anonymised/reduced using:
```sql
DELETE FROM pw_dyn_user WHERE user_id NOT IN (
SELECT DISTINCT(user_id) FROM (
SELECT wm.user_id FROM pw_dyn_wiki_motion AS wm
UNION
SELECT al.user_id FROM pw_dyn_auditlog AS al
) AS users);


UPDATE `pw_dyn_auditlog` SET remote_addr=CONCAT('172.16.',FLOOR(RAND() * 253) +1,'.',FLOOR(RAND() * 253) +1),event='- Removed -';

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
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_cache_divinfo > pw_cache_divinfo.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_cache_divwiki > pw_cache_divwiki.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_cache_mpinfo > pw_cache_mpinfo.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_cache_partyinfo > pw_cache_partyinfo.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_cache_whip > pw_cache_whip.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_constituency > pw_constituency.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_division > pw_division.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_dyn_auditlog > pw_dyn_auditlog.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_dyn_user > pw_dyn_user.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_dyn_wiki_motion > pw_dyn_wiki_motion.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_moffice > pw_moffice.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_mp > pw_mp.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_vote > pw_vote.sql
docker exec publicwhip-mariadb /usr/bin/mysqldump -u root --password=root publicwhip-db pw_vote_sortorder > pw_vote_sortorder.sql

```