drop procedure if exists sp_get_product;

/*
  Takes a Machine ID and Hopper ID and return product details. 
*/

DELIMITER //
CREATE PROCEDURE sp_get_product
(
   IN  machine_id   int,
   IN  hopper_id    int
)
SQL SECURITY DEFINER
BEGIN
   
   SELECT p.id, p.name, p.price, h.stock
   FROM products p
   INNER JOIN hoppers h ON p.id = h.product_id
   WHERE h.machine_id = machine_id
   AND h.hopper_id = hopper_id;
   
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_get_product TO 'nh-web'@'localhost';
