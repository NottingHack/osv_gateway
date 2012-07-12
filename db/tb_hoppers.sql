drop table if exists hoppers;

create table hoppers 
(
	machine_id  int not null,
	hopper_id   int not null,-- 0 - 63, ID set on DIP switch on hopper, unique only within machine
	product_id  int,
	stock       int,
	
	primary key (machine_id, hopper_id)
) ENGINE = InnoDB; 
