alter table ia_file
  modify column timestamp datetime(3) not null default now(3);

alter table ia_textblock
  modify column creation_timestamp datetime(3) not null default now(3);

alter table ia_textblock
  modify column timestamp datetime(3) not null default now(3);

alter table ia_textblock_revision
  modify column creation_timestamp datetime(3) not null default now(3);

alter table ia_textblock_revision
  modify column timestamp datetime(3) not null default now(3);
