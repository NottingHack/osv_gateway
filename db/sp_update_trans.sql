drop procedure if exists sp_update_trans;

/*
  Updates transactions to either to vend or complete
*/

DELIMITER //
CREATE PROCEDURE sp_update_trans
(
   IN  trans_id   int,
   IN  status     varchar(10),
   IN  buyer_id   int
)
SQL SECURITY DEFINER
BEGIN
    declare err varchar(100);
    declare machine_id, hopper_id int;
    
    declare EXIT HANDLER for SQLEXCEPTION, SQLWARNING
    begin
        SET err = 'Error - transaction rollback!';
        rollback;
        SELECT err;
    end;
	
	IF status = 'to vend' THEN
	    UPDATE transactions t SET t.status = 'to vend', t.buyer_id = buyer_id WHERE t.id = trans_id;
	    
	    SELECT 'done';
	ELSEIF status = 'complete' THEN
	    start transaction;
            UPDATE transactions t SET t.status = 'complete' WHERE t.id = trans_id;
            SELECT t.machine_id, t.hopper_id INTO machine_id,hopper_id FROM transactions t WHERE t.id = trans_id;
            UPDATE hoppers h SET h.reserved = (h.reserved - 1), h.stock = (h.stock - 1) WHERE h.machine_id = machine_id AND h.hopper_id = hopper_id;
        commit;
        
        SELECT 'done';
	END IF;
    
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_update_trans TO 'nh-web'@'localhost';
