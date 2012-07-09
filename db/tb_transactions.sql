drop table if exists products;

create table products 
(
	id    		int not null auto_increment,
	machine_id	int not null,
	product_id	int not null,
	buyer_id		int not null,
	datetime		timestamp default CURRENT_TIMESTAMP,			
	
	primary key (id)
) ENGINE = InnoDB; 
