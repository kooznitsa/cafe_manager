CREATE TABLE IF NOT EXISTS "categories"(
     "id" SERIAL PRIMARY KEY,
     "name" VARCHAR(255) NOT NULL
);

INSERT INTO public.categories (name) VALUES
	 ('Кофе'),
	 ('Чай'),
	 ('Десерты');
