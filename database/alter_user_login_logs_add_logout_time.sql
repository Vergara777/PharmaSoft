-- Add logout_time column to user_login_logs table
ALTER TABLE user_login_logs 
ADD COLUMN logout_time DATETIME NULL DEFAULT NULL 
AFTER login_time;

-- Add index for faster queries on logout_time
CREATE INDEX idx_user_login_logs_logout_time ON user_login_logs(logout_time);
