drop procedure if exists sp_get_machines;

/*
  Returns a list of machines 
*/

DELIMITER //
CREATE PROCEDURE sp_get_machines
(
   
)
SQL SECURITY DEFINER
BEGIN
   
   SELECT m.id, m.name, m.location, m.mac
   FROM machines m;
   
END //
DELIMITER ;

GRANT EXECUTE ON PROCEDURE sp_get_machines TO 'web19-openkiosk'@'%';
