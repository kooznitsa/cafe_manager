CREATE TABLE IF NOT EXISTS "dishes"(
     "id" SERIAL PRIMARY KEY,
     "name" VARCHAR(255) NOT NULL,
     "category_id" BIGINT NULL,
     "price" FLOAT(53) NOT NULL,
     "image" VARCHAR(255) NULL,
     "is_available" BOOLEAN NOT NULL
);
ALTER TABLE
    "dishes" ADD CONSTRAINT "dishes_name_category_id_unique" UNIQUE("name", "category_id");
ALTER TABLE
    "dishes" ADD CONSTRAINT "dishes_category_id_foreign" FOREIGN KEY("category_id") REFERENCES "categories"("id");

INSERT INTO public.dishes (name,category_id,price,image,is_available) VALUES
	 ('Капучино',1,180.00,'coffee__cappuccino.jpg',true),
	 ('Американо',1,170.00,'coffee__americano.jpg',true),
	 ('Эспрессо',1,150.00,'coffee__espresso.jpg',true),
	 ('Флэт-уайт',1,200.00,'coffee__flat_white.jpg',true),
	 ('Черный чай',2,100.00,'tea__black_tea.jpg',true),
	 ('Эрл Грей',2,110.00,'tea__earl_grey.jpg',true),
	 ('Зеленый чай',2,110.00,'tea__green_tea.jpg',true),
	 ('Таежный сбор',2,170.00,'tea__taiga_tea.jpg',true),
	 ('Чизкейк',3,300.00,'dessert__cheesecake.jpg',true),
	 ('Печенье',3,70.00,'dessert__cookie.jpg',true),
	 ('Капкейк',3,250.00,'dessert__cupcake.jpg',true),
	 ('Пончик',3,250.00,'dessert__donut.jpg',true);
