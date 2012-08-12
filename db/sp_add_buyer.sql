drop procedure if exists sp_add_buyer;

/*
  Takes a name and email and add buyer to database
*/

DELIMITER //
CREATE PROCEDURE sp_add_buyer
(
   IN  name   varchar(255),
   IN  email  varchar(255)
)
SQL SECURITY DEFINER
BEGIN
    declare buyer_id int;
    
    SELECT b.id INTO buyer_id FROM buyers b WHERE b.email = email;
    
    IF buyer_id IS NOT NULL THEN
        SELECT buyer_id AS id;
    ELSE
        INSERT INTO buyers VALUES(NULL, name, email);
        SELECT LAST_INSERT_ID() as id;
    END IF;
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_add_buyer TO 'nh-web'@'localhost';
