drop procedure if exists sp_reserve_stock;

/*
  Takes a Machine ID and Hopper ID and reserves a unit of stock.
  
  Also starts to populate the transaction log.
  At the moment we don't know the buyer, so put NULL in the buyer field.
  
  Returns the newly created transaction ID
  
  Stock reservation and new transaction are atomic transaction.
*/

DELIMITER //
CREATE PROCEDURE sp_reserve_stock
(
   IN  machine_id   int,
   IN  hopper_id    int
)
SQL SECURITY DEFINER
BEGIN
    declare err varchar(100);
    declare res_stock int;
    declare product_id int;
    declare price int;
    SET err = '';
    
    main: begin
    
        declare EXIT HANDLER for SQLEXCEPTION, SQLWARNING
        begin
            SET err = 'Error - transaction rollback!';
            rollback;
            SELECT err;
        end;
	    
	    SET res_stock = 0;
	    
        SELECT h.reserved, h.product_id INTO res_stock, product_id
        FROM hoppers h
        WHERE h.machine_id = machine_id
        AND h.hopper_id = hopper_id;
        
        SET res_stock = res_stock + 1;
        
        SELECT p.price INTO price
        FROM products p
        WHERE p.id = product_id;
        
        start transaction;
        
        UPDATE hoppers h
        SET reserved = res_stock
        WHERE h.machine_id = machine_id
        AND h.hopper_id = hopper_id;
        
        INSERT INTO transactions
        VALUES(NULL, machine_id, hopper_id, product_id, price, 0, NULL, "pending");
        
        commit;
        
        SELECT LAST_INSERT_ID() as trans;
    end main;
    
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_reserve_stock TO 'web19-openkiosk'@'%';
