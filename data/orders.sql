CREATE TABLE IF NOT EXISTS "orders"(
     "id" SERIAL PRIMARY KEY,
     "dish_id" BIGINT NULL,
     "user_id" BIGINT NULL,
     "status" VARCHAR(255) NOT NULL,
     "is_delivery" BOOLEAN NOT NULL,
     "created_at" DATE NOT NULL,
     "updated_at" DATE NULL
);
ALTER TABLE
    "orders" ADD CONSTRAINT "orders_dish_id_foreign" FOREIGN KEY("dish_id") REFERENCES "dishes"("id");
ALTER TABLE
    "orders" ADD CONSTRAINT "orders_user_id_foreign" FOREIGN KEY("user_id") REFERENCES "users"("id");

INSERT INTO public.orders (dish_id,user_id,status,is_delivery,created_at,updated_at) VALUES
	 (2,1,'Deleted',true,'2024-11-11 13:39:26','2024-11-11 14:05:44'),
	 (1,1,'Created',false,'2024-11-02 12:36:58','2024-11-02 12:38:06'),
	 (1,2,'Paid',false,'2024-11-02 12:37:03','2024-11-02 12:38:06'),
	 (9,2,'Created',false,'2024-11-02 12:37:56','2024-11-02 12:38:06');
