alter table ia_parameter_value
  change object_type object_type enum('global', 'round', 'task') not null;
