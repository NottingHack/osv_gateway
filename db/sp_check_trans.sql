drop procedure if exists sp_check_trans;

/*
  Checks for old transactions and canceld them.
  
  Also removes stock reservation
*/

DELIMITER //
CREATE PROCEDURE sp_check_trans
(
   
)
SQL SECURITY DEFINER
BEGIN
    declare err varchar(100);
    declare done INT DEFAULT FALSE;
    declare trans_id,machine_id,hopper_id INT;
    declare status VARCHAR(10);
    
    declare cur CURSOR FOR SELECT t.id, t.machine_id, t.hopper_id, t.status FROM transactions t WHERE t.datetime < DATE_SUB(NOW(), INTERVAL 1 day) AND t.status = "pending";
    declare CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET err = '';
    
    OPEN cur;
    
    main: begin
    
        declare EXIT HANDLER for SQLEXCEPTION, SQLWARNING
        begin
            SET err = 'Error - transaction rollback!';
            rollback;
            SELECT err;
        end;
	    
	    read_loop: LOOP
	        FETCH cur INTO trans_id,machine_id,hopper_id,status;
	        IF done THEN
                LEAVE read_loop;
            END IF;
            
            start transaction;
                UPDATE transactions t SET t.status = "cancelled" WHERE t.id = trans_id;
                UPDATE hoppers h SET h.reserved = (h.reserved - 1) WHERE h.machine_id = machine_id AND h.hopper_id = hopper_id;
            commit;
            
	    END LOOP;
        
        SELECT "done" as result;
    end main;
    
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_check_trans TO 'web19-openkiosk'@'%';
