DROP TABLE IF EXISTS "product";
DROP SEQUENCE IF EXISTS product_id_seq1;
CREATE SEQUENCE product_id_seq1 INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "product" (
    "id" integer DEFAULT nextval('product_id_seq1') NOT NULL,
    "name" varying(100) NOT NULL,
    "price" numeric(10,2) NOT NULL,
    "created_at" timestamp NOT NULL,
    "updated_at" timestamp,
    CONSTRAINT "product_pkey" PRIMARY KEY ("id")
) WITH (oids = false);

INSERT INTO "product" ("id", "name", "price", "created_at", "updated_at") VALUES
    (1,	'Produkt ABC',	75.90,	'2023-11-09 12:32:11',	NULL),
    (2,	'Produkt DEF',	105.15,	'2023-11-09 12:32:26',	NULL),
    (3,	'Produkt GHI',	255.99,	'2023-11-09 12:32:40',	NULL),
    (4,	'Produkt XYZ',	101.00,	'2023-11-09 12:32:59',	NULL),
    (5,	'Produkt 1',	15.10,	'2023-11-09 12:34:04',	NULL),
    (6,	'Produkt 2',	51.85,	'2023-11-09 12:34:12',	NULL),
    (7,	'Produkt 3',	84.07,	'2023-11-09 12:34:27',	NULL),
    (8,	'Produkt 4',	99.99,	'2023-11-09 12:34:36',	NULL);
