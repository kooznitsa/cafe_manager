CREATE TABLE IF NOT EXISTS "purchases"(
    "id" SERIAL PRIMARY KEY,
    "product_id" BIGINT NULL,
    "price" FLOAT(53) NOT NULL,
    "amount" FLOAT(53) NOT NULL,
    "created_at" DATE NOT NULL,
    "updated_at" DATE NULL
);
ALTER TABLE
    "purchases" ADD CONSTRAINT "purchases_product_id_foreign" FOREIGN KEY("product_id") REFERENCES "products"("id");

INSERT INTO public.purchases (product_id,price,amount,created_at,updated_at) VALUES
	 (1,1.00,100.00,'2024-11-06 15:07:31','2024-11-06 15:12:06'),
	 (2,1.00,40.00,'2024-11-07 09:16:31','2024-11-07 09:16:46'),
	 (3,6000.00,2000.00,'2024-11-07 11:27:30','2024-11-07 11:27:30'),
	 (4,1000.00,5000.00,'2024-11-07 11:27:48','2024-11-07 11:27:48'),
	 (2,60.00,300.00,'2024-11-07 11:28:02','2024-11-07 11:28:02'),
	 (2,5000.00,5000.00,'2024-11-07 11:28:23','2024-11-07 11:28:23'),
	 (1,1000.00,1000.00,'2024-11-08 06:48:55','2024-11-08 06:48:55'),
	 (3,10000.00,5000.00,'2024-11-08 08:48:28','2024-11-08 08:48:28'),
	 (5,1.00,30.00,'2024-11-08 13:47:52','2024-11-08 13:50:01');
