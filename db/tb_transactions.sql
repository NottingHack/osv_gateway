drop table if exists transactions;

create table transactions
(
	id          int not null auto_increment,
	machine_id  int not null,
	hopper_id   int not null,
	product_id  int not null,
	price       int not null,
	buyer_id    int not null,
	datetime    timestamp default CURRENT_TIMESTAMP,	
	status      varchar(8),	
	
	primary key (id)
) ENGINE = InnoDB; 
