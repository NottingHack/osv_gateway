drop table if exists products;

create table products 
(
	id    int not null auto_increment,
	name	varchar(255),
	price	int, -- In pence
	
	primary key (id)
) ENGINE = InnoDB; 
