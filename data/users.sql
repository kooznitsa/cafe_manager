CREATE TABLE IF NOT EXISTS "users"(
    "id" SERIAL PRIMARY KEY,
    "name" VARCHAR(255) NOT NULL,
    "password" VARCHAR(255) NOT NULL,
    "email" VARCHAR(255) NOT NULL,
    "address" VARCHAR(255) NULL,
    "roles" JSON NOT NULL,
    "token" VARCHAR(255) NULL,
    "created_at" DATE NULL,
    "updated_at" DATE NOT NULL
);
ALTER TABLE
    "users" ADD CONSTRAINT "users_email_unique" UNIQUE("email");
ALTER TABLE
    "users" ADD CONSTRAINT "users_token_unique" UNIQUE("token");

INSERT INTO public.users (name,"password",email,address,created_at,updated_at,roles,"token") VALUES
	 ('Cat','$2y$13$1cx/u91hSwFy8IF8WUgFsOL5bSzkQlkcnvVqf7Yboyek.FHXnY.Rm','cat@gmail.com','New York','2024-11-13 12:18:38','2024-11-13 12:18:38','{"1":"ROLE_ADMIN"}',NULL),
	 ('Акула','$2y$13$zuwpZeyz.SQlgNLFfS.jgu2KhM/beeqPg.mUC1IuqzFN.JMRXDgV6','akula@gmail.com','Сосновый бор','2024-10-30 11:44:44','2024-11-02 11:29:33','["ROLE_USER"]',NULL),
	 ('Кукушка','$2y$13$m1jpl5RPn3qlf8HOWjIPRONro1dH8Oswor.LSo147.v/Q6tkDOM0W','cuckoo@gmail.com','Сосновый бор','2024-10-30 11:06:57','2024-11-12 10:43:06','["ROLE_USER", "ROLE_ADMIN"]','rRBxfagOkRGGjQzJbNV2YLZvc34='),
	 ('Crocodile','$2y$13$DPYG.Jd.qGtl4iLPcBZCruSn7cuMdCjwqDfn9dZwpqikNbALfDMBK','crocodile@gmail.com','New York','2024-11-14 08:46:14','2024-11-14 08:46:14','{"1":"ROLE_ADMIN"}',NULL),
	 ('Pug','$2y$13$gsy9WeUD6CVw4oG5FWMLCexrY41yd3N9H5IMtsrtUDcFEk4DLEjty','pug@gmail.com','New York','2024-11-14 08:59:11','2024-11-14 08:59:11','{"1":"ROLE_ADMIN"}',NULL);
