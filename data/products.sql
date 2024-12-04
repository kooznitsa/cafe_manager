CREATE TABLE IF NOT EXISTS "products"(
   "id" SERIAL PRIMARY KEY,
   "name" VARCHAR(255) NOT NULL,
   "unit" VARCHAR(255) NOT NULL,
   "amount" FLOAT(53) NOT NULL,
   "created_at" DATE NOT NULL,
   "updated_at" DATE NOT NULL
);
ALTER TABLE
    "products" ADD CONSTRAINT "products_name_unique" UNIQUE("name");

INSERT INTO public.products (name,unit,amount,created_at,updated_at) VALUES
	 ('Молоко','мл',240.00,'2024-11-06 10:29:25','2024-11-21 09:26:11'),
	 ('Корица','г',120.00,'2024-11-07 11:26:50','2024-11-21 09:26:11'),
	 ('Кофе','г',9687.00,'2024-11-07 08:52:30','2024-11-27 14:18:10'),
	 ('Сахар','г',910.00,'2024-11-05 09:32:15','2024-11-27 14:18:10'),
	 ('Вода','мл',5050.00,'2024-11-07 11:26:15','2024-11-27 14:18:10');
