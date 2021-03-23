SELECT
  *
FROM
  productsubcategory
WHERE
  categoryid != (
    SELECT
      catid
    FROM
      productcategory
    WHERE
      pcategoryurl LIKE "trudesign%"
  )
  AND subcaturl != ''
