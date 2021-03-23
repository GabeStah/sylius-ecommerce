SELECT
  *
FROM
  productcategory
WHERE
  pcategoryurl NOT LIKE "trudesign%"
  AND catstatus = 1
