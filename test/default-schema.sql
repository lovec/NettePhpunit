DROP TABLE IF EXISTS product_order;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS customer;

CREATE TABLE product (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(id)
)   ENGINE=INNODB;

CREATE TABLE customer (
    id INT NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (id)
)   ENGINE=INNODB;

CREATE TABLE product_order (
    id INT NOT NULL AUTO_INCREMENT,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,

    PRIMARY KEY(id),
    FOREIGN KEY (product_id) REFERENCES product(id),
    FOREIGN KEY (customer_id) REFERENCES customer(id)
)   ENGINE=INNODB;