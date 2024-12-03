CREATE TABLE IF NOT EXISTS "recipes"(
    "id" SERIAL PRIMARY KEY,
    "dish_id" BIGINT NULL,
    "product_id" BIGINT NULL,
    "amount" FLOAT(53) NOT NULL
);
ALTER TABLE
    "recipes" ADD CONSTRAINT "recipes_dish_id_product_id_unique" UNIQUE("dish_id","product_id");

INSERT INTO public.recipes (dish_id,product_id,amount) VALUES
	 (1,1,150.00),
	 (1,2,20.00),
	 (1,3,100.00),
	 (1,4,50.00),
	 (1,5,15.00),
	 (2,2,50.00),
	 (2,3,10.00),
	 (2,4,250.00),
	 (3,1,150.00);
