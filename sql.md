ALTER TABLE coaches ADD `goal_type` enum('make','take') NOT NULL DEFAULT 'take';
UPDATE coaches SET goal_type = 'take';
ALTER TABLE shots ADD `goal_type` enum('make','take') NOT NULL DEFAULT 'take';
ALTER TABLE user_goals ADD `goal_type` enum('make','take') NOT NULL DEFAULT 'take';
ALTER TABLE user_shots ADD `goal_type` enum('make','take') NOT NULL DEFAULT 'take';