create table ia_variable (
  id int(11) not null auto_increment,
  name varchar(100) not null,
  value varchar(100) not null,
  primary key (id),
  unique key (name)
) charset utf8mb4 collate utf8mb4_general_ci;
