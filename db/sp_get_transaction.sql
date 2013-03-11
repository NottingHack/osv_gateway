drop procedure if exists sp_get_transaction;

/*
  Takes a transaction ID and returns details 
*/

DELIMITER //
CREATE PROCEDURE sp_get_transaction
(
   IN  trans_id   int
)
SQL SECURITY DEFINER
BEGIN
   
   SELECT t.machine_id, t.hopper_id, t.product_id, t.price, t.status, p.name
   FROM transactions t
   INNER JOIN products p ON p.id = t.product_id
   WHERE t.id = trans_id;
   
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_get_transaction TO 'web19-openkiosk'@'%';
