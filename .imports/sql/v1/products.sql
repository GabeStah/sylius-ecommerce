SELECT
  p.*,
  s1.subcatid subcatid1,
  s1.subcatname subcatname1,
  s1.subcaturl subcaturl1,
  s2.subcatid subcatid2,
  s2.subcatname subcatname2,
  s2.subcaturl subcaturl2,
  s3.subcatid subcatid3,
  s3.subcatname subcatname3,
  s3.subcaturl subcaturl3,
  s4.subcatid subcatid4,
  s4.subcatname subcatname4,
  s4.subcaturl subcaturl4
FROM
  (
    SELECT
      p.productnumber,
      MAX(p.prid) AS prid
    FROM
      products p
    GROUP BY
      p.productnumber
  ) p_max
  INNER JOIN products p ON p_max.productnumber = p.productnumber
  AND p_max.prid = p.prid
  LEFT JOIN productsubcategory s1 ON p.psubcategory = s1.subcatid
  LEFT JOIN productsubcategory s2 ON p.psubcategory3 = s2.subcatid
  LEFT JOIN productsubcategory s3 ON p.psubcategory4 = s3.subcatid
  LEFT JOIN productsubcategory s4 ON p.psubcategory5 = s4.subcatid
WHERE
  seourl NOT LIKE '%trudesign%'
  AND prstatus = 1
ORDER BY
  productnumber ASC
