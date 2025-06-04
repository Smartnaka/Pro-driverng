CREATE TABLE customers (
     id INT AUTO_INCREMENT PRIMARY KEY,
     first_name VARCHAR(100),
      last_name VARCHAR(100),
      email VARCHAR(100) UNIQUE,
      phone VARCHAR(20),
      password VARCHAR(255),
       created_at TIMESTAMP DEFAULT
       CURRENT_TIMESTAMP

);
