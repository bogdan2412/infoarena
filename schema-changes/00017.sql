drop table ia_acm_round;

alter table ia_round
  modify type enum('classic','archive','user-defined','penalty-round')
  not null default 'user-defined';
