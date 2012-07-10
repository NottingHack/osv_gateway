drop table if exists hoppers;

create table hoppers 
(
	machine_id  int not null,
	product_id  int not null,
	stock       int,
	
	primary key (machine_id, product_id)
) ENGINE = InnoDB; 
